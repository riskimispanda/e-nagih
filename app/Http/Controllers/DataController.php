<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Metode;
use App\Models\Pembayaran;
use App\Events\UpdateBaru;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\Month;
use App\Models\Paket;
use App\Models\Router;
use App\Models\Server as BTS;
use App\Models\Lokasi;
use App\Models\ODC;
use App\Models\ODP;
use App\Models\Koneksi;
use App\Models\MediaKoneksi;
use App\Models\Perangkat;
use App\Models\Invoice;
use App\Services\MikrotikServices;
use App\Imports\CustomerImport;
use App\Imports\CustomerKhusus;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ModemDetail;
use App\Models\User;

class DataController extends Controller
{
    public function pelanggan(Request $request)
    {
        // Get search and sort parameters
        $search = $request->get('search');
        $sort = $request->get('sort', 'default');
        $perPage = $request->get('per_page', 10);
        $perPage = $perPage > 500 ? 500 : $perPage;

        // Build base query
        $query = Customer::with([
            'status',
            'paket',
            'invoice.status',
            'invoice',
            'getServer',
            'odp.odc.olt'
        ])->whereIn('status_id', [3, 4, 9, 16, 17])
            ->orderBy('tanggal_selesai', 'desc');

        $import = Customer::where('cek', 'Imported')->count();
        $countAgen = User::where('roles_id', 6)->count();
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_customer', 'like', '%' . $search . '%')
                  ->orWhere('alamat', 'like', '%' . $search . '%')
                  ->orWhere('no_hp', 'like', '%' . $search . '%')
                    ->orWhereHas('paket', function ($p) use ($search) {
                        $p->where('nama_paket', 'like', '%' . $search . '%');
                    })
                  ->orWhereHas('odp.odc.olt', function($p) use ($search) {
                        $p->where('nama_odp', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('agen', function ($p) use ($search) {
                        $p->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Apply sorting
        switch ($sort) {
            case 'name-asc':
                $query->orderBy('nama_customer', 'asc');
                break;
            case 'name-desc':
                $query->orderBy('nama_customer', 'desc');
                break;
            case 'status-active':
                $query->orderByRaw('CASE WHEN status_id = 3 THEN 0 ELSE 1 END');
                break;
            case 'status-inactive':
                $query->orderByRaw('CASE WHEN status_id = 9 THEN 0 ELSE 1 END');
                break;
            case 'package':
                $query->join('pakets', 'customers.paket_id', '=', 'pakets.id')
                      ->orderBy('pakets.nama_paket', 'asc')
                      ->select('customers.*');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Get paginated results
        $customers = $query->paginate($perPage)->appends($request->query());

        $customers->getCollection()->transform(function ($customer) {
            $invoiceIds = $customer->invoice->pluck('id');
            $lastPembayaran = Pembayaran::whereIn('invoice_id', $invoiceIds)
                ->latest('tanggal_bayar')
                ->first();

            $customer->last_pembayaran = $lastPembayaran?->tanggal_bayar
                ? \Carbon\Carbon::parse($lastPembayaran->tanggal_bayar)->locale('id')->isoFormat('dddd, D-MMMM-Y')
                : '-';

            return $customer;
        });
        // Get statistics data (independent of pagination)
        $totalCustomers = Customer::whereIn('status_id', [3, 4, 9])->count();
        $totalActive = Customer::where('status_id', 3)->count();
        $totalInactive = Customer::where('status_id', 9)->count();
        
        $metode = Metode::all();
        $pembayaran = Pembayaran::where('status_id', 6)->get();
        $hariIni = Customer::whereDate('tanggal_selesai', today())
            ->where('status_id', 3)
            ->count();
        // $hariIni = Customer::where('status_id', 3)->count();

        $menunggu = Customer::whereDate('created_at', today())
            ->whereIn('status_id', [1, 2, 5])
            ->count();
        // $menunggu = Customer::whereIn('status_id', [1, 2, 3])->count();

        $maintenance = Customer::whereDate('updated_at', today())
            ->where('status_id', 4)
            ->get();

        $antrian = Customer::whereIn('status_id', [1, 2, 5])
            ->whereDate('created_at', today())
            ->with('teknisi')
            ->get();
        
        $selesai = Customer::where('status_id', 3)
            ->whereDate('tanggal_selesai', today())
            ->with('teknisi')
            ->get();
        

        $instalasiBulanan = Customer::where('status_id', 3)
            ->whereMonth('tanggal_selesai', now()->month)
            ->whereYear('tanggal_selesai', now()->year)
            ->with('teknisi')
            ->get();

        $bulananInstallasi = Customer::whereIn('status_id', [3, 4])
            ->whereMonth('tanggal_selesai', now()->month)
            ->whereYear('tanggal_selesai', now()->year)
            ->count();

        $nonAktif = Customer::where('status_id', 9)->get();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $customers->items(),
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                    'from' => $customers->firstItem(),
                    'to' => $customers->lastItem(),
                    'links' => $customers->links()->render()
                ]
            ]);
        }

        // Format data for WebSocket (only for non-AJAX requests)
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
        
        // Check for data changes (only for non-AJAX requests)
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
            'hariIni' => $hariIni,
            'menunggu' => $menunggu,
            'maintenance' => $maintenance,
            'customerData' => $customerData->toArray(),
            'antrian' => $antrian,
            'selesai' => $selesai,
            'bulananInstallasi' => $bulananInstallasi,
            'installasiBulanan' => $instalasiBulanan,
            'nonAktif' => $nonAktif,
            'importData' => $import,
            'countAgen' => $countAgen
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
            $query = Invoice::with(['customer', 'paket', 'status'])
                ->orderBy('created_at', 'desc')
                ->whereIn('status_id', [1, 7]);


            // Apply search filter
            if ($search) {
                $query->whereHas('customer', function($q) use ($search) {
                    $q->where('nama_customer', 'like', '%' . $search . '%');
                })->orWhereHas('paket', function($q) use ($search) {
                    $q->where('nama_paket', 'like', '%' . $search . '%');
                });
            }

            $perki = Invoice::whereIn('status_id', [7, 8]);

            // Apply month filter
            if ($bulan && $bulan !== '' && $bulan !== null) {
                $query->whereMonth('jatuh_tempo', $bulan);
            }

            // Clone query sebelum pagination
            $allFilteredQuery = clone $query;
            $invoices = $query->paginate(10);

            if ($bulan) {
                $perki->whereMonth('jatuh_tempo', $bulan);
            }
            
            // Ambil semua data untuk kalkulasi
            $allInvoices = $perki->with('paket')->get();
            
            // Hitung estimasi
            $perkiraanPendapatan = $allInvoices->sum(fn($inv) => $inv->tagihan ?? 0);
            $tambahan           = $allInvoices->sum(fn($inv) => $inv->tambahan ?? 0);
            $tunggakan          = $allInvoices->sum(fn($inv) => $inv->tunggakan ?? 0);
            $saldo              = $allInvoices->sum(fn($inv) => $inv->saldo ?? 0);
            
            $estimasi = $perkiraanPendapatan + $tambahan + $tunggakan - $saldo;

            // === Statistik ===
            if ($bulan && $bulan !== '' && $bulan !== null) {
                $allFilteredInvoices = $allFilteredQuery->get();

                $totalRevenue = 0;
                $pendingRevenue = 0;
                $totalInvoices = 0;

                foreach ($allFilteredInvoices as $invoice) {
                    if ($invoice->status_id == 7) {
                        $totalInvoices++;
                    }

                    if ($invoice->status_id == 8) { // Sudah Bayar
                        $totalRevenue += ($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan);
                    } elseif ($invoice->status_id == 7) { // Belum Bayar
                        $pendingRevenue += ($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan);
                    }
                }
            } else {
                $totalRevenue = Pembayaran::sum('jumlah_bayar');

                $pendingRevenue = Invoice::where('status_id', 7)
                    ->sum('tagihan') + Invoice::where('status_id', 7)
                    ->sum('tambahan') + Invoice::where('status_id', 7)
                    ->sum('tunggakan') - Invoice::where('status_id', 7)->sum('saldo');

                $totalInvoices = Invoice::where('status_id', 7)->count();
            }

            // Monthly revenue dari Pembayaran
            if ($bulan && $bulan !== '' && $bulan !== null) {
                $monthlyRevenue = Pembayaran::whereMonth('tanggal_bayar', $bulan)
                    ->whereYear('tanggal_bayar', \Carbon\Carbon::now()->year)
                    ->sum('jumlah_bayar');
            } else {
                $monthlyRevenue = Pembayaran::sum('jumlah_bayar');
            }

            // Return JSON response
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
                        'perkiraan' => $estimasi,
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


    public function editPelanggan($id)
    {
        $pelanggan = Customer::with('paket','odp.odc.olt')->findOrFail($id);
        $paket = Paket::whereNot('nama_paket', 'ISOLIREBILLING')->get();
        $bts = BTS::all();
        $router = Router::all();
        $olt = Lokasi::all();
        $odc = ODC::all();
        $odp = ODP::all();
        $koneksi = Koneksi::all();
        $media = MediaKoneksi::all();
        $perangkat = Perangkat::all();
        $agen = User::where('roles_id', 6)->get();

        return view('/pelanggan/edit',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'pelanggan' => $pelanggan,
            'paket' => $paket,
            'router' => $router,
            'bts' => $bts,
            'olt' => $olt,
            'odc' => $odc,
            'odp' => $odp,
            'koneksi' => $koneksi,
            'media' => $media,
            'perangkat' => $perangkat,
            'agen' => $agen,
        ]);
    }

    public function updatePelanggan(Request $request, $id)
    {
        $invoice = Invoice::where('customer_id', $id)->latest()->first();

        DB::beginTransaction();

        try {
            $pelanggan = Customer::findOrFail($id);

            // Data request langsung diassign ke pelanggan
            $data = [
                'nama_customer' => $request->nama,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'gps' => $request->gps,
                'no_identitas' => $request->no_identitas,
                'router_id' => $request->router,
                'paket_id' => $request->paket,
                'lokasi_id' => $request->odp,
                'access_point' => $request->access_point,
                'koneksi_id' => $request->koneksi,
                'media_id' => $request->media,
                'perangkat_id' => $request->perangkat,
                'local_address' => $request->local_address,
                'remote_address' => $request->remote_address,
                'remote' => $request->remote,
                'seri_perangkat' => $request->seri,
                'mac_address' => $request->mac,
                'usersecret' => $request->usersecret,
                'pass_secret' => $request->pass_secret,
                'agen_id' => $request->agen_id,
            ];

            // Update langsung tanpa filter
            $pelanggan->update($data);

            // Tentukan router & paket (selalu ambil dari request)
            $router = Router::findOrFail($request->router);
            $paket  = Paket::findOrFail($request->paket);

            // Update invoice
            if ($invoice) {
                $invoice->update([
                    'paket_id' => $paket->id,
                    'tagihan'  => $paket->harga,
                ]);
            }

            // Update profile di MikroTik
            $client = MikrotikServices::connect($router);
            MikrotikServices::UpgradeDowngrade($client, $pelanggan->usersecret, $paket->paket_name);
            MikrotikServices::removeActiveConnections($client, $pelanggan->usersecret);

            Log::info('Success update profile Pelanggan: ' . $pelanggan->nama_customer . '-' . $pelanggan->usersecret . '-' . $paket->paket_name);

            // Update Data Modem
            ModemDetail::updateOrCreate(
                ['customer_id' => $pelanggan->id], // kondisi cek
                [
                    'logistik_id'   => $request->perangkat,
                    'serial_number' => $pelanggan->seri_perangkat,
                    'mac_address'   => $pelanggan->mac_address,
                    'status_id'     => 13
                ]
            );

            DB::commit();

            return redirect('/data/pelanggan')->with('success', 'Data pelanggan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update pelanggan: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memperbarui data.');
        }
    }



    public function Import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $data = $request->file('file');

        $import = Excel::import(new CustomerImport, $data);
        Log::info('Berhasil Import Data Customer', ['import' => $import]);
        return back()->with('success', 'Data Customer berhasil diimport!');
    }

    public function ImportKhusus(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $data = $request->file('file');

        $import = Excel::import(new CustomerKhusus, $data);
        Log::info('Berhasil Import Data Customer Khusus', ['import' => $import]);
        return back()->with('success', 'Data Customer Khusus berhasil diimport!');
    }

    public function hapusImport()
    {
        try {
            $invoice = Invoice::where('cek', 'Imported')->delete();
            $deletedRows = Customer::where('cek', 'Imported')->delete();
            $modem = ModemDetail::where('cek', 'Imported')->delete();

            Log::info('Berhasil menghapus data import sementara.', ['deleted_rows' => $deletedRows, 'deleted_invoice' => $invoice, 'deleted_modem' => $modem]);

            return redirect('/setting')->with('success', 'Data import sementara berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data import sementara: ' . $e->getMessage());
            return redirect('/data/pelanggan')->with('error', 'Terjadi kesalahan saat menghapus data import sementara.');
        }
    }
}