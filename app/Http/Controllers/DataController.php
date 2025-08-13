<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Metode;
use App\Models\Pembayaran;
use App\Events\UpdateBaru;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\Month;

class DataController extends Controller
{
    public function pelanggan()
    {
        $customers = Customer::with([
            'status',
            'paket',
            'invoice.status',
            'invoice',
            'getServer',
            'odp.odc.olt'
        ])->whereIn('status_id', [3, 4, 9])
        ->orderBy('created_at', 'desc')
        ->get();
        
        // $coba = Customer::where('status_id', [3, 9])->get();

        $metode = Metode::all();
        $pembayaran = Pembayaran::where('status_id', 6)->get();
        $hariIni = Customer::whereDate('created_at', today())
        ->where('status_id', 3)
        ->count();
        $menunggu = Customer::whereDate('created_at', today())
            ->whereIn('status_id', [1, 2, 5])
            ->count();

        $maintenance = Customer::whereDate('updated_at', today())
        ->where('status_id', 4)
        ->count();
        // dd($maintenance);
        // dd($menunggu);
        // Format data sesuai kebutuhan frontend
        $customerData = $customers->map(function ($customer) {
            $latestInvoice = $customer->invoice->sortByDesc('created_at')->first();
            
            return [
            'id' => $customer->id,
            'nama_customer' => $customer->nama_customer ?? 'Unknown',
            'alamat' => $customer->alamat ?? '',
            'no_hp' => $customer->no_hp ?? '',
            'status_id' => $customer->status_id,
            'status' => [
                'id' => $customer->status->id ?? null,
                'nama_status' => $customer->status->nama_status ?? 'Unknown',
            ],
            'paket' => [
                'id' => $customer->paket->id ?? null,
                'nama_paket' => $customer->paket->nama_paket ?? 'Unknown',
            ],
            'getServer' => [
                'lokasi_server' => $customer->getServer->lokasi_server ?? 'Unknown'
            ],
            'invoice' => $customer->invoice->map(function ($invoice) {
                return [
                'id' => $invoice->id,
                'status' => [
                    'id' => $invoice->status->id ?? null,
                    'nama_status' => $invoice->status->nama_status ?? 'Unknown'
                ],
                'tagihan' => $invoice->tagihan,
                'jatuh_tempo' => $invoice->jatuh_tempo
                ];
            })->values()->toArray(),
            'updated_at' => $customer->updated_at ? $customer->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });
        
        // Check for data changes
        $currentUpdate = md5($customerData->toJson());
        $lastUpdate = cache('last_customer_update');
        
        if (!$lastUpdate || $lastUpdate !== $currentUpdate) {
            $message = 'Data pelanggan berhasil diperbarui';
            event(new UpdateBaru(
            $customerData->toArray(),
            'success',
            $message
            ));
            cache(['last_customer_update' => $currentUpdate], now()->addMinutes(5));
        }
        
        $antrian = Customer::whereIn('status_id', [1, 2, 5])
        ->whereDate('created_at', today())
        ->with('teknisi')
        ->get();
        
        $selesai = Customer::where('status_id', 3)
        ->whereDate('created_at', today())
        ->with('teknisi')
        ->get();

        $bulananInstallasi = Customer::whereIn('status_id', [3, 4])
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
        // dd($bulananInstallasi);
        return view('data.data-pelanggan', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $customers,
            'metode' => $metode,
            'pembayaran' => $pembayaran,
            'hariIni' => $hariIni,
            'menunggu' => $menunggu,
            'maintenance' => $maintenance,
            'customerData' => $customerData->toArray(),
            'antrian' => $antrian,
            'selesai' => $selesai,
            'bulananInstallasi' => $bulananInstallasi,
        ]);
    }

    public function detailAntrianPelanggan($id)
    {
        $customer = Customer::with('router')->findOrFail($id);
        // dd($customer);
        return view('/teknisi/detail-antrian-pelanggan', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $customer,
        ]);
    }

    public function filterPendapatan(Request $request)
    {
        try {
            // Get filter parameters
            $search = $request->get('search');
            $bulan = $request->get('bulan');

            // For AJAX requests, don't set default - respect the exact value sent

            // Build query for invoices with relationships
            $query = \App\Models\Invoice::with(['customer', 'paket', 'status'])
                ->orderBy('created_at', 'desc')
                ->whereIn('status_id', [1, 7]); // Exclude 'Dibatalkan' status

            // Apply search filter
            if ($search) {
                $query->whereHas('customer', function($q) use ($search) {
                    $q->where('nama_customer', 'like', '%' . $search . '%');
                })->orWhereHas('paket', function($q) use ($search) {
                    $q->where('nama_paket', 'like', '%' . $search . '%');
                });
            }

            // Apply month filter - only filter by month if bulan is not empty (when "Semua Bulan" is selected, bulan will be empty)
            if ($bulan && $bulan !== '' && $bulan !== null) {
                $query->whereMonth('jatuh_tempo', $bulan);
            }

            // Clone the query before pagination to get all filtered invoices for statistics
            $allFilteredQuery = clone $query;
            $invoices = $query->paginate(10);

            // Calculate statistics based on filter
            if ($bulan && $bulan !== '' && $bulan !== null) {
                // When month filter is applied, calculate from filtered invoices
                $allFilteredInvoices = $allFilteredQuery->get();

                $totalRevenue = 0;
                $pendingRevenue = 0;
                $totalInvoices = 0;

                foreach ($allFilteredInvoices as $invoice) {
                    // Count total invoices with status_id = 7 (Belum Bayar)
                    if ($invoice->status_id == 7) {
                        $totalInvoices++;
                    }

                    // Calculate based on status_id
                    if ($invoice->status_id == 8) { // Sudah Bayar
                        // For paid invoices: add tagihan + tambahan - tunggakan to total revenue
                        $totalRevenue += ($invoice->tagihan + $invoice->tambahan - $invoice->tunggakan);
                    } elseif ($invoice->status_id == 7) { // Belum Bayar
                        // For unpaid invoices: add tagihan + tambahan + tunggakan to pending revenue
                        $pendingRevenue += ($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan);
                    }
                }
            } else {
                // When no month filter (Semua Bulan), calculate from all invoices with status_id 8 (Sudah Bayar)
                $totalRevenue = \App\Models\Invoice::where('status_id', 8)
                    ->sum('tagihan') + \App\Models\Invoice::where('status_id', 8)
                    ->sum('tambahan') - \App\Models\Invoice::where('status_id', 8)
                    ->sum('tunggakan');

                // Calculate pending revenue from status_id 7 (Belum Bayar)
                $pendingRevenue = \App\Models\Invoice::where('status_id', 7)
                    ->sum('tagihan') + \App\Models\Invoice::where('status_id', 7)
                    ->sum('tambahan') + \App\Models\Invoice::where('status_id', 7)
                    ->sum('tunggakan');

                $totalInvoices = \App\Models\Invoice::where('status_id', 7)->count();
            }

            // Calculate monthly revenue from Pembayaran based on selected month or all if no month filter
            if ($bulan && $bulan !== '' && $bulan !== null) {
                $monthlyRevenue = \App\Models\Pembayaran::whereMonth('tanggal_bayar', $bulan)
                    ->whereYear('tanggal_bayar', \Carbon\Carbon::now()->year)
                    ->sum('jumlah_bayar');
            } else {
                $monthlyRevenue = \App\Models\Pembayaran::sum('jumlah_bayar');
            }

            // Return JSON response with updated data
            return response()->json([
                'success' => true,
                'data' => [
                    'invoices' => $invoices->items(),
                    'pagination' => [
                        'current_page' => $invoices->currentPage(),
                        'last_page' => $invoices->lastPage(),
                        'per_page' => $invoices->perPage(),
                        'total' => $invoices->total(),
                        'from' => $invoices->firstItem(),
                        'to' => $invoices->lastItem(),
                    ],
                    'statistics' => [
                        'totalRevenue' => $totalRevenue,
                        'monthlyRevenue' => $monthlyRevenue,
                        'pendingRevenue' => $pendingRevenue,
                        'totalInvoices' => $totalInvoices,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

}