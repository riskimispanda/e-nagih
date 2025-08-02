<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Metode;
use App\Models\Pembayaran;
use App\Events\UpdateBaru;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        
        return view('data.data-pelanggan', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $customers,
            'metode' => $metode,
            'pembayaran' => $pembayaran,
            'customerData' => $customerData->toArray(),
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

            // Apply month filter only if bulan is not empty (when "Semua Bulan" is selected, bulan will be empty)
            if ($bulan && $bulan !== '' && $bulan !== null) {
                $query->whereMonth('jatuh_tempo', $bulan);
            }

            // Clone the query before pagination to get all filtered invoices for statistics
            $allFilteredQuery = clone $query;
            $invoices = $query->paginate(10);

            // Calculate statistics
            if ((!$bulan || $bulan === '' || $bulan === null) && (!$search || $search === '')) {
                // When no filters applied (Semua Bulan + no search), return original statistics like KeuanganController
                $totalRevenue = \App\Models\Invoice::whereHas('status', function($q) {
                    $q->where('nama_status', 'Sudah Bayar');
                })->sum('tagihan') + \App\Models\Invoice::whereHas('status', function($q) {
                    $q->where('nama_status', 'Sudah Bayar');
                })->sum('tambahan') - \App\Models\Invoice::whereHas('status', function($q) {
                    $q->where('nama_status', 'Belum Bayar');
                })->sum('tunggakan');

                $monthlyRevenue = \App\Models\Pembayaran::sum('jumlah_bayar');

                $pendingRevenue = \App\Models\Invoice::whereHas('status', function($q) {
                    $q->where('nama_status', 'Belum Bayar');
                })->sum('tagihan') + \App\Models\Invoice::whereHas('status', function($q) {
                    $q->where('nama_status', 'Belum Bayar');
                })->sum('tambahan') + \App\Models\Invoice::whereHas('status', function($q) {
                    $q->where('nama_status', 'Belum Bayar');
                })->sum('tunggakan');

                $totalInvoices = \App\Models\Invoice::where('status_id', 7)->count();
            } else {
                // When filters are applied, calculate from filtered data
                $allFilteredInvoices = $allFilteredQuery->get();

                $totalRevenue = 0;
                $monthlyRevenue = 0;
                $pendingRevenue = 0;
                $totalInvoices = 0;

                foreach ($allFilteredInvoices as $invoice) {
                    // Count total invoices with status_id = 7 (Belum Bayar)
                    if ($invoice->status_id == 7) {
                        $totalInvoices++;
                    }

                    // Calculate based on status
                    if ($invoice->status && $invoice->status->nama_status == 'Sudah Bayar') {
                        // For paid invoices: add tagihan + tambahan - tunggakan to total revenue
                        $totalRevenue += ($invoice->tagihan + $invoice->tambahan - $invoice->tunggakan);
                    } elseif ($invoice->status && $invoice->status->nama_status == 'Belum Bayar') {
                        // For unpaid invoices: add tagihan + tambahan + tunggakan to pending revenue
                        $pendingRevenue += ($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan);
                    }
                }

                // Calculate Monthly Revenue (Jumlah Pembayaran) from Pembayaran table with same filters
                $invoiceIds = $allFilteredInvoices->pluck('id')->toArray();
                if (!empty($invoiceIds)) {
                    $monthlyRevenue = \App\Models\Pembayaran::whereIn('invoice_id', $invoiceIds)->sum('jumlah_bayar');
                }
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