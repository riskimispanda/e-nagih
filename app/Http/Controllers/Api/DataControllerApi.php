<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\Pembayaran;
use Carbon\Carbon;
use App\Models\Pengeluaran;
use App\Models\Pendapatan;
use App\Models\Customer;

class DataControllerApi extends Controller
{
  public function getInvoicePaid(Request $request)
  {
      $month = $request->input('month', Carbon::now()->month);
      $year = $request->input('year', Carbon::now()->year);

      $invoicePaid = Invoice::with('customer:id,nama_customer,paket_id,alamat,no_hp','paket')
          ->select('id', 'merchant_ref', 'customer_id', 'tagihan', 'jatuh_tempo')
          ->whereMonth('jatuh_tempo', $month)
          ->whereYear('jatuh_tempo', $year)
          ->where('status_id', 8)
          ->whereHas('customer')
          ->get();

      // Set locale ke Indonesia
      Carbon::setLocale('id');

      //Transform data invoice
      $formattedInvoice = $invoicePaid->map(function($invoice) {
          return [
              'id' => $invoice->id,
              'merchant_ref' => $invoice->merchant_ref,
              'customer_name' => $invoice->customer->nama_customer,
              'no_hp' => $invoice->customer->no_hp,
              'alamat' => $invoice->customer->alamat,
              'tagihan' => $invoice->tagihan,
              'paket' => $invoice->customer->paket->nama_paket,
              'jatuh_tempo' => Carbon::parse($invoice->jatuh_tempo)->translatedFormat('F'), // 'F' untuk nama bulan lengkap
          ];
      });

      return response()->json([
          'success' => true,
          'data' => $formattedInvoice,
          'count' => $invoicePaid->count()
      ]);
  }

  public function getInvoiceUnpaid(Request $request)
  {
      $month = $request->input('month', Carbon::now()->month);
      $year = $request->input('year', Carbon::now()->year);

      $invoiceUnpaid = Invoice::with('customer:id,nama_customer,paket_id,no_hp,alamat')
          ->select('id', 'merchant_ref', 'customer_id', 'tagihan', 'jatuh_tempo')
          ->whereMonth('jatuh_tempo', $month)
          ->whereYear('jatuh_tempo', $year)
          ->where('status_id', 7)
          ->whereHas('customer')
          ->get();

      // Set locale ke Indonesia
      Carbon::setLocale('id');

      //Transform data invoice
      $formattedInvoice = $invoiceUnpaid->map(function($invoice) {
          return [
              'id' => $invoice->id,
              'merchant_ref' => $invoice->merchant_ref,
              'customer_name' => $invoice->customer->nama_customer,
              'no_hp' => $invoice->customer->no_hp,
              'alamat' => $invoice->customer->alamat,
              'paket' => $invoice->customer->paket->nama_paket,
              'tagihan' => $invoice->tagihan,
              'jatuh_tempo' => Carbon::parse($invoice->jatuh_tempo)->translatedFormat('F'), // 'F' untuk nama bulan lengkap
          ];
      });

      return response()->json([
          'success' => true,
          'data' => $formattedInvoice,
          'count' => $invoiceUnpaid->count()
      ]);
  }

  public function historyPayment()
  {
    //Ambil history pembayaran yang sudah lunas
    $history = Pembayaran::with([
            'invoice.customer:id,nama_customer',
            'invoice.paket:id,nama_paket',
            'invoice:id,customer_id,paket_id,merchant_ref,tagihan'
        ])
        ->select('id', 'invoice_id', 'tanggal_bayar', 'jumlah_bayar', 'metode_bayar', 'created_at')
        ->where('status_id', 8)
        ->whereHas('invoice.customer')
        ->whereHas('invoice.paket')
        ->latest('id')
        ->get();

    $formattedHistory = $history->map(function($payment){
        return [
            'id' => $payment->id,
            'customer_name' => $payment->invoice->customer->nama_customer,
            'paket' => $payment->invoice->paket->nama_paket,
            'reference' => $payment->invoice->merchant_ref,
            'tagihan' => $payment->invoice->tagihan,
            'jumlah_bayar' => $payment->jumlah_bayar,
            'metode_pembayaran' => $payment->metode_bayar,
            'tanggal_bayar' => $payment->tanggal_bayar
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $formattedHistory,
        'count' => $history->count()
    ]);
  }

  public function getMonthlyPayment()
  {
    $pembayaran = Pembayaran::whereMonth('tanggal_bayar', Carbon::now()->month)->whereYear('tanggal_bayar', Carbon::now()->year)->get();
    $pendapatan = Pendapatan::whereMonth('tanggal', Carbon::now()->month)->whereYear('tanggal', Carbon::now()->year)->get();
    $pengeluaran = Pengeluaran::whereMonth('tanggal_pengeluaran', Carbon::now()->month)->whereYear('tanggal_pengeluaran', Carbon::now()->year)->get();
    return response()->json([
        'success' => true,
        'data' => [
              'pendapatan' => $pembayaran->sum('jumlah_bayar'),
            'pengeluaran' => $pengeluaran->sum('jumlah_pengeluaran')
        ]
    ]);
  }

  public function getCustomerAll()
  {
    $customer = Customer::with('status','paket','agen','teknisi','getServer','odp.odc.olt')->orderBy('tanggal_selesai', 'desc')->get();
    $customerAll = $customer->map(function($pelanggan){
        return [
            'name' => $pelanggan->nama_customer,
            'paket' => $pelanggan->paket->nama_paket,
            'status' => $pelanggan->status->nama_status,
            'agen' => $pelanggan->agen->name ?? 'Tidak Ada Agen',
            'alamat' => $pelanggan->alamat,
            'bts' => $pelanggan->odp?->odc?->olt?->server?->lokasi_server,
            'olt' => $pelanggan->odp?->odc?->olt?->nama_lokasi,
            'odc' => $pelanggan->odp?->odc?->nama_odc,
            'odp' => $pelanggan->odp?->nama_odp,
            'teknisi' => $pelanggan->teknisi->name ?? 'Teknisi Kosong',
            'tanggal_installasi' => Carbon::parse($pelanggan->tanggal_selesai)->locale('id')->translatedFormat('d-M-Y H:i:s') ?? 'Belum Di Close'
        ];
    });

    return response()->json([
      'success' => true,
      'data' => $customerAll,
      'count' => $customerAll->count()
    ]);

  }


  public function debugging(Request $request)
  {
      $bulan = $request->input('month', Carbon::now()->month);
      $tahun = $request->input('year', Carbon::now()->year);
      $agen_id = $request->input('agen_id');

      // 1. Total customer aktif wajib bayar (status 3, 4, 9, paket != 11, deleted_at is null)
      $customerAktifQuery = Customer::whereIn('status_id', [3, 4, 9])
          ->whereNot('paket_id', 11)
          ->whereNull('deleted_at');
      if ($agen_id) {
          $customerAktifQuery->where('agen_id', $agen_id);
      }
      $totalCustomerAktif = $customerAktifQuery->count();

      // 1. Cek customer isolir dari invoice (Global / All Time)
      $customerIsolirInvoiceQuery = Customer::where('status_id', 9)
          ->whereNull('deleted_at')
          ->whereNot('paket_id', 11)
          ->whereHas('invoice', function ($query) {
              $query->where('status_id', 7);
          });
      if ($agen_id) {
          $customerIsolirInvoiceQuery->where('agen_id', $agen_id);
      }
      $totalCustomerIsolirInvoice = $customerIsolirInvoiceQuery->count();

      $customerIsolirInvoiceList = $customerIsolirInvoiceQuery->with(['paket', 'invoice' => function ($query) {
          $query->where('status_id', 7)
              ->orderBy('jatuh_tempo', 'asc');
      }])->get()->map(function($c) {
          $invoice = $c->invoice->first();
          return [
              'id' => $c->id,
              'nama' => $c->nama_customer,
              'status_id' => $c->status_id,
              'paket' => $c->paket->nama_paket ?? 'N/A',
              'harga_paket' => $c->paket->harga ?? 0,
              'tagihan_invoice' => $invoice ? $invoice->tagihan : 0,
              'jatuh_tempo' => $invoice ? Carbon::parse($invoice->jatuh_tempo)->format('Y-m-d') : null,
          ];
      });

      // 2. OPSI A: Berdasarkan Bulan Tagihan (Jatuh Tempo)
      // Pelanggan lunas (Sudah Bayar) untuk tagihan bulan ini
      $paidOptionAQuery = Invoice::where('status_id', 8)
          ->whereMonth('jatuh_tempo', $bulan)
          ->whereYear('jatuh_tempo', $tahun)
          ->whereHas('customer', function ($query) use ($agen_id) {
              $query->whereNull('deleted_at')
                  ->whereIn('status_id', [3, 4, 9])
                  ->whereNot('paket_id', 11);
              if ($agen_id) {
                  $query->where('agen_id', $agen_id);
              }
          });
      $totalSudahBayar = $paidOptionAQuery->distinct('customer_id')->count('customer_id');

      // Belum Bayar - Aktif (status 3, 4)
      $unpaidAktifQuery = Invoice::where('status_id', 7)
          ->whereMonth('jatuh_tempo', $bulan)
          ->whereYear('jatuh_tempo', $tahun)
          ->whereHas('customer', function ($query) use ($agen_id) {
              $query->whereNull('deleted_at')
                  ->whereIn('status_id', [3, 4])
                  ->whereNot('paket_id', 11);
              if ($agen_id) {
                  $query->where('agen_id', $agen_id);
              }
          });
      $totalBelumBayarAktif = $unpaidAktifQuery->distinct('customer_id')->count('customer_id');

      // Belum Bayar - Isolir (status 9)
      $unpaidIsolirQuery = Invoice::where('status_id', 7)
          ->whereMonth('jatuh_tempo', $bulan)
          ->whereYear('jatuh_tempo', $tahun)
          ->whereHas('customer', function ($query) use ($agen_id) {
              $query->whereNull('deleted_at')
                  ->where('status_id', 9)
                  ->whereNot('paket_id', 11);
              if ($agen_id) {
                  $query->where('agen_id', $agen_id);
              }
          });
      $totalBelumBayarIsolir = $unpaidIsolirQuery->distinct('customer_id')->count('customer_id');

      // Belum Terbit (Komplemen)
      $totalBelumTerbit = $totalCustomerAktif - ($totalSudahBayar + $totalBelumBayarAktif + $totalBelumBayarIsolir);

      // Gabungan total Belum Bayar (untuk kompatibilitas visual jika diperlukan)
      $totalUnpaidOptionA = $totalBelumBayarAktif + $totalBelumBayarIsolir + $totalBelumTerbit;

      // 3. OPSI B: Berdasarkan Bulan Transaksi (Tanggal Bayar)
      // Pelanggan lunas berdasarkan transaksi di bulan ini
      $paidOptionBQuery = Pembayaran::whereMonth('tanggal_bayar', $bulan)
          ->whereYear('tanggal_bayar', $tahun)
          ->whereHas('invoice.customer', function ($query) use ($agen_id) {
              $query->whereNull('deleted_at')
                  ->whereIn('status_id', [3, 4, 9])
                  ->whereNot('paket_id', 11);
              if ($agen_id) {
                  $query->where('agen_id', $agen_id);
              }
          });

      // Salin query sebelum melakukan join
      $paidOptionB = clone $paidOptionBQuery;
      $paidOptionBCount = $paidOptionB->join('invoice', 'pembayaran.invoice_id', '=', 'invoice.id')
          ->distinct('invoice.customer_id')
          ->count('invoice.customer_id');

      // 4. Statistik tambahan dari tabel customer (sama persis dengan DataController.php)
      // Pelanggan Aktif (Status 3 = Aktif, 4 = Maintenance, termasuk Paket 11 Fasum)
      $pelangganAktifQuery = Customer::whereIn('status_id', [3, 4])->whereNot('paket_id', 11)->whereNull('deleted_at');
      if ($agen_id) {
          $pelangganAktifQuery->where('agen_id', $agen_id);
      }
      $pelangganAktif = $pelangganAktifQuery->count();

      // Pelanggan Non-Aktif (Status 9 = Isolir, termasuk Paket 11 Fasum)
      $pelangganNonAktifQuery = Customer::where('status_id', 9)->whereNot('paket_id', 11)
          ->whereNull('deleted_at')
          ->orderBy('updated_at', 'desc');
      if ($agen_id) {
          $pelangganNonAktifQuery->where('agen_id', $agen_id);
      }
      $pelangganNonAktif = $pelangganNonAktifQuery->count();

      // Pelanggan Paket Fasum (Paket 11)
      $pelangganFasumQuery = Customer::where('paket_id', 11)->whereIn('status_id', [3,4,9])
          ->whereNull('deleted_at');
      if ($agen_id) {
          $pelangganFasumQuery->where('agen_id', $agen_id);
      }
      $pelangganFasum = $pelangganFasumQuery->count();

      // Total data pelanggan (Status 3, 4, 9)
      $allDataQuery = Customer::whereIn('status_id', [3, 4, 9])->whereNot('paket_id', 11)
          ->whereNull('deleted_at');
      if ($agen_id) {
          $allDataQuery->where('agen_id', $agen_id);
      }
      $allData = $allDataQuery->count();

      // Analisis ketidakcocokan (Paket Fasum dengan status_id di luar [3, 4, 9])
      $fasumDiluarStatusQuery = Customer::where('paket_id', 11)
          ->whereNotIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at');
      $fasumDiluarStatusCount = $fasumDiluarStatusQuery->count();
      $fasumDiluarStatusList = $fasumDiluarStatusQuery->get(['id', 'nama_customer', 'status_id'])->map(function($c) {
          return [
              'id' => $c->id,
              'nama' => $c->nama_customer,
              'status_id' => $c->status_id
          ];
      });

      return response()->json([
          'success' => true,
          'filter' => [
              'bulan' => (int)$bulan,
              'tahun' => (int)$tahun,
              'agen_id' => $agen_id ? (int)$agen_id : null
          ],
          'summary' => [
              'total_pelanggan_aktif_wajib_bayar' => $totalCustomerAktif,
              'total_customer_isolir_invoice' => $totalCustomerIsolirInvoice,
              'opsi_a_jatuh_tempo' => [
                  'sudah_bayar_bulan_ini' => $totalSudahBayar,
                  'belum_bayar_aktif' => $totalBelumBayarAktif,
                  'belum_bayar_isolir' => $totalBelumBayarIsolir,
                  'belum_terbit' => $totalBelumTerbit,
                  'total_terdata' => $totalSudahBayar + $totalBelumBayarAktif + $totalBelumBayarIsolir + $totalBelumTerbit,
                  'selisih_dari_total_aktif' => $totalCustomerAktif - ($totalSudahBayar + $totalBelumBayarAktif + $totalBelumBayarIsolir + $totalBelumTerbit)
              ],
              'opsi_b_tanggal_bayar' => [
                  'sudah_bayar_bulan_ini' => $paidOptionBCount,
                  'keterangan' => 'Jumlah unik customer yang bayar riil di bulan ini'
              ]
          ],
          'customer_status_stats' => [
              'aktif' => $pelangganAktif,
              'non_aktif' => $pelangganNonAktif,
              'paket_fasum' => $pelangganFasum,
              'total_aktif_dan_non_aktif' => $allData
          ],
          'customer_isolir_dari_invoice' => [
              'count' => $totalCustomerIsolirInvoice,
              'pelanggan' => $customerIsolirInvoiceList
          ],
          'analisis_discrepancy' => [
              'jumlah_fasum_status_diluar_3_4_9' => $fasumDiluarStatusCount,
              'pelanggan' => $fasumDiluarStatusList,
              'penjelasan' => 'Pelanggan Fasum ini dihitung di paket_fasum karena paket_id=11, tetapi TIDAK dihitung di total_aktif_dan_non_aktif karena status_id-nya di luar [3, 4, 9] (misal status 1, 2, atau 5).'
          ]
      ]);
  }

  public function detailCounting(Request $request)
  {
      $bulan = $request->input('month', Carbon::now()->month);
      $tahun = $request->input('year', Carbon::now()->year);
      $agen_id = $request->input('agen_id');

      // Helper function to format customer details
      $formatCustomer = function ($customer, $invoice = null) {
          return [
              'id' => $customer->id,
              'nama' => $customer->nama_customer,
              'no_hp' => $customer->no_hp,
              'status_id' => $customer->status_id,
              'status_name' => $customer->status->nama_status ?? 'N/A',
              'paket' => $customer->paket->nama_paket ?? 'N/A',
              'harga_paket' => $customer->paket->harga ?? 0,
              'tagihan_invoice' => $invoice ? $invoice->tagihan : null,
              'jatuh_tempo' => $invoice ? Carbon::parse($invoice->jatuh_tempo)->format('Y-m-d') : null,
          ];
      };

      // 1. Total customer aktif wajib bayar
      $customerAktifQuery = Customer::with('status', 'paket')
          ->whereIn('status_id', [3, 4, 9])
          ->whereNot('paket_id', 11)
          ->whereNull('deleted_at');
      if ($agen_id) {
          $customerAktifQuery->where('agen_id', $agen_id);
      }
      $allActiveCustomers = $customerAktifQuery->get();

      // 2. Sudah Bayar (Opsi A)
      $paidInvoices = Invoice::where('status_id', 8)
          ->whereMonth('jatuh_tempo', $bulan)
          ->whereYear('jatuh_tempo', $tahun)
          ->whereHas('customer', function ($query) use ($agen_id) {
              $query->whereNull('deleted_at')
                  ->whereIn('status_id', [3, 4, 9])
                  ->whereNot('paket_id', 11);
              if ($agen_id) {
                  $query->where('agen_id', $agen_id);
              }
          })
          ->with(['customer.status', 'customer.paket'])
          ->get();

      $sudahBayarList = $paidInvoices->map(function ($invoice) use ($formatCustomer) {
          return $formatCustomer($invoice->customer, $invoice);
      });

      $sudahBayarCustomerIds = $paidInvoices->pluck('customer_id')->toArray();

      // 3. Belum Bayar - Invoice Terbit (Aktif: status 3, 4)
      $unpaidInvoicesAktif = Invoice::where('status_id', 7)
          ->whereMonth('jatuh_tempo', $bulan)
          ->whereYear('jatuh_tempo', $tahun)
          ->whereHas('customer', function ($query) use ($agen_id) {
              $query->whereNull('deleted_at')
                  ->whereIn('status_id', [3, 4])
                  ->whereNot('paket_id', 11);
              if ($agen_id) {
                  $query->where('agen_id', $agen_id);
              }
          })
          ->with(['customer.status', 'customer.paket'])
          ->get();

      $belumBayarAktifList = $unpaidInvoicesAktif->map(function ($invoice) use ($formatCustomer) {
          return $formatCustomer($invoice->customer, $invoice);
      });

      $belumBayarAktifCustomerIds = $unpaidInvoicesAktif->pluck('customer_id')->toArray();

      // 3b. Belum Bayar - Invoice Terbit (Isolir: status 9)
      $unpaidInvoicesIsolir = Invoice::where('status_id', 7)
          ->whereMonth('jatuh_tempo', $bulan)
          ->whereYear('jatuh_tempo', $tahun)
          ->whereHas('customer', function ($query) use ($agen_id) {
              $query->whereNull('deleted_at')
                  ->where('status_id', 9)
                  ->whereNot('paket_id', 11);
              if ($agen_id) {
                  $query->where('agen_id', $agen_id);
              }
          })
          ->with(['customer.status', 'customer.paket'])
          ->get();

      $belumBayarIsolirList = $unpaidInvoicesIsolir->map(function ($invoice) use ($formatCustomer) {
          return $formatCustomer($invoice->customer, $invoice);
      });

      $belumBayarIsolirCustomerIds = $unpaidInvoicesIsolir->pluck('customer_id')->toArray();

      // 4. Belum Terbit (Komplemen)
      $excludeIds = array_merge($sudahBayarCustomerIds, $belumBayarAktifCustomerIds, $belumBayarIsolirCustomerIds);
      
      $belumTerbitList = $allActiveCustomers->filter(function ($customer) use ($excludeIds) {
          return !in_array($customer->id, $excludeIds);
      })->values()->map(function ($customer) use ($formatCustomer) {
          return $formatCustomer($customer);
      });

      return response()->json([
          'success' => true,
          'filter' => [
              'bulan' => (int)$bulan,
              'tahun' => (int)$tahun,
              'agen_id' => $agen_id ? (int)$agen_id : null
          ],
          'summary_counts' => [
              'total_pelanggan_aktif_wajib_bayar' => $allActiveCustomers->count(),
              'sudah_bayar' => $sudahBayarList->count(),
              'belum_bayar_aktif' => $belumBayarAktifList->count(),
              'belum_bayar_isolir' => $belumBayarIsolirList->count(),
              'belum_terbit' => $belumTerbitList->count(),
          ],
          'details' => [
              'sudah_bayar' => $sudahBayarList,
              'belum_bayar_aktif' => $belumBayarAktifList,
              'belum_bayar_isolir' => $belumBayarIsolirList,
              'belum_terbit' => $belumTerbitList,
              'total_pelanggan_aktif_wajib_bayar' => $allActiveCustomers->map(function ($customer) use ($formatCustomer) {
                  return $formatCustomer($customer);
              })
          ]
      ]);
  }

  public function generateFasumInvoices()
  {
      try {
          DB::beginTransaction();

          // Get customers paket 11 tanpa invoice sama sekali
          $customersFasum = Customer::whereNull('deleted_at')
              ->whereIn('status_id', [3, 4, 9])
              ->whereDoesntHave('invoice')
              ->get();

          $fasumWithoutInvoice = Customer::whereIn('status_id', [3,4,9])->where('paket_id', 11)->whereDoesntHave('invoice')->whereNull('deleted_at')->count();


          $generatedInvoices = [];
          $errors = [];
          $successCount = 0;

          foreach ($customersFasum as $customer) {
              try {
                  // Create invoice data
                  $invoiceData = [
                      'customer_id' => $customer->id,
                      'paket_id' => $customer->paket_id,
                      'status_id' => 7, // Belum bayar
                      'tagihan' => $customer->paket ? $customer->paket->harga : 0,
                      'jatuh_tempo' => Carbon::now()->endOfMonth(),
                      'reference' => 'INV-' . Carbon::now()->format('Ym') . '-' . str_pad($customer->id, 6, '0', STR_PAD_LEFT),
                      'merchant_ref' => uniqid('inv_', true),
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                  ];

                  // Insert invoice
                  $invoice = Invoice::create($invoiceData);
                  $generatedInvoices[] = [
                      'invoice_id' => $invoice->id,
                      'customer_id' => $customer->id,
                      'nama_customer' => $customer->nama_customer,
                      'no_hp' => $customer->no_hp,
                      'paket_name' => $customer->paket ? $customer->paket->nama_paket : 'Paket Tidak Ditemukan',
                      'tagihan' => $invoice->tagihan,
                      'jatuh_tempo' => $invoice->jatuh_tempo->format('Y-m-d'),
                      'reference' => $invoice->reference,
                      'merchant_ref' => $invoice->merchant_ref,
                      'status' => 'Generated'
                  ];
                  $successCount++;

              } catch (\Exception $e) {
                  $errors[] = "Error generate invoice untuk {$customer->nama_customer}: " . $e->getMessage();
                  DB::rollBack();
                  return response()->json([
                      'success' => false,
                      'message' => 'Terjadi error saat generate invoice paket Fasum',
                      'error' => $e->getMessage(),
                      'errors' => $errors
                  ], 500);
              }
          }

          DB::commit();

          return response()->json([
              'success' => true,
              'message' => 'Berhasil generate invoice untuk customer paket Fasum',
              'summary' => [
                  'total_customers' => $customersFasum->count(),
                  'success_count' => $successCount,
                  'error_count' => count($errors),
                  'generated_at' => Carbon::now()->toDateTimeString()
              ],
              'fasum' => $fasumWithoutInvoice,
              'generated_invoices' => $generatedInvoices,
              'errors' => $errors
          ]);

      } catch (\Exception $e) {
          DB::rollBack();
          return response()->json([
              'success' => false,
              'message' => 'Terjadi error sistem',
              'error' => $e->getMessage()
          ], 500);
      }
  }

  public function checkInvoicesWithoutPayment()
  {
    try {
      $customerStatusFilter = [3, 4, 9];
      $bulan = request('bulan', Carbon::now()->month);
      $tahun = request('tahun', Carbon::now()->year);

      // Invoice dengan status 8 tapi tidak ada riwayat pembayaran
      $invoicesWithoutPayment = Invoice::with(['customer', 'pembayaran'])
        ->where('status_id', 8)
        ->whereHas('customer', function ($query) use ($customerStatusFilter) {
          $query->whereNull('deleted_at')
            ->whereIn('status_id', $customerStatusFilter)
            ->whereNot('paket_id', 11);
        })
        ->whereMonth('jatuh_tempo', $bulan)
        ->whereYear('jatuh_tempo', $tahun)
        ->whereDoesntHave('pembayaran')
        ->get();

      // Detail invoice yang bermasalah
      $problematicInvoices = $invoicesWithoutPayment->map(function ($invoice) {
        return [
          'id' => $invoice->id,
          'customer_id' => $invoice->customer_id,
          'customer_name' => $invoice->customer->nama_customer ?? 'N/A',
          'customer_status' => $invoice->customer->status_id ?? 'N/A',
          'nomor_invoice' => $invoice->nomor_invoice,
          'jatuh_tempo' => $invoice->jatuh_tempo,
          'total_tagihan' => $invoice->total_tagihan,
          'status_id' => $invoice->status_id,
          'created_at' => $invoice->created_at,
          'updated_at' => $invoice->updated_at
        ];
      });

      // Statistik
      $totalInvoicesStatus8 = Invoice::where('status_id', 8)
        ->whereHas('customer', function ($query) use ($customerStatusFilter) {
          $query->whereNull('deleted_at')
            ->whereIn('status_id', $customerStatusFilter)
            ->whereNot('paket_id', 11);
        })
        ->whereMonth('jatuh_tempo', $bulan)
        ->whereYear('jatuh_tempo', $tahun)
        ->count();

      $totalInvoicesWithPayment = Invoice::where('status_id', 8)
        ->whereHas('customer', function ($query) use ($customerStatusFilter) {
          $query->whereNull('deleted_at')
            ->whereIn('status_id', $customerStatusFilter)
            ->whereNot('paket_id', 11);
        })
        ->whereMonth('jatuh_tempo', $bulan)
        ->whereYear('jatuh_tempo', $tahun)
        ->whereHas('pembayaran')
        ->count();

        $totalCustomerRef = Invoice::where('status_id', 8)
            ->whereHas('customer', function ($query) use ($customerStatusFilter) {
                $query->whereNull('deleted_at')
                      ->whereIn('status_id', $customerStatusFilter)
                      ->whereNot('paket_id', 11);
            })
            ->whereHas('pembayaran', function ($query) {
                $currentMonth = Carbon::now()->month;
                $query->whereMonth('tanggal_bayar', $currentMonth)
                      ->whereYear('tanggal_bayar', Carbon::now()->year);
            })
            ->distinct('customer_id')
            ->count('customer_id');

        $customerAktif = Customer::where('status_id', 3)->whereNot('paket_id', 11)->count();
        $customerNonAktif = Customer::where('status_id', 9)->count();

      return response()->json([
        'success' => true,
        'data' => [
          'period' => [
            'bulan' => $bulan,
            'tahun' => $tahun
          ],
          'statistics' => [
            'total_invoice_status_8' => $totalInvoicesStatus8,
            'invoice_with_payment' => $totalInvoicesWithPayment,
            'invoice_without_payment' => $invoicesWithoutPayment->count(),
            'persentase_bermasalah' => $totalInvoicesStatus8 > 0
              ? round(($invoicesWithoutPayment->count() / $totalInvoicesStatus8) * 100, 2)
              : 0
          ],
          'problematic_invoices' => $problematicInvoices,
          'totalWithRef' => $totalCustomerRef,
          'customerAktif' => $customerAktif,
          'customerNonAktif'=> $customerNonAktif
        ]
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengecek invoice',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function analyzeCustomerInvoiceGap()
  {
    try {
      $bulan = request('bulan', Carbon::now()->month);
      $tahun = request('tahun', Carbon::now()->year);

      // Customer aktif (base reference)
      $customerAktif = Customer::whereIn('status_id', [3,4,9])
        ->whereNull('deleted_at')
        ->whereNot('paket_id', 11)
        ->count();

      // TotalWithRef (invoice dengan pembayaran bulan ini)
      $totalWithRef = Invoice::where('status_id', 8)
        ->whereHas('customer', function ($query) {
          $query->whereNull('deleted_at')
            ->whereIn('status_id', [3,4,9])
            ->whereNot('paket_id', 11);
        })
        ->whereHas('pembayaran', function ($query) use ($bulan) {
          $currentMonth = $bulan ?? Carbon::now()->month;
          $query->whereMonth('tanggal_bayar', $currentMonth)
            ->whereYear('tanggal_bayar', Carbon::now()->year);
        })
        ->distinct('customer_id')
        ->count('customer_id');

      // Analisis gap
      $customersWithPayment = Invoice::where('status_id', 8)
        ->whereHas('customer', function ($query) {
          $query->whereNull('deleted_at')
            ->whereIn('status_id', [3,4,9])
            ->whereNot('paket_id', 11);
        })
        ->whereHas('pembayaran', function ($query) use ($bulan) {
          $currentMonth = $bulan ?? Carbon::now()->month;
          $query->whereMonth('tanggal_bayar', $currentMonth)
            ->whereYear('tanggal_bayar', Carbon::now()->year);
        })
        ->pluck('customer_id')
        ->unique();

      $customersWithoutPaymentThisMonth = Customer::whereIn('status_id', [3,4,9])
        ->whereNull('deleted_at')
        ->whereNot('paket_id', 11)
        ->whereNotIn('id', $customersWithPayment)
        ->get(['id', 'nama_customer', 'status_id', 'paket_id']);

      // Analisis detail customer tanpa pembayaran
      $analysisDetails = $customersWithoutPaymentThisMonth->map(function ($customer) use ($bulan, $tahun) {
        $hasInvoiceThisMonth = Invoice::where('customer_id', $customer->id)
          ->whereMonth('jatuh_tempo', $bulan)
          ->whereYear('jatuh_tempo', $tahun)
          ->exists();

        $hasUnpaidInvoice = Invoice::where('customer_id', $customer->id)
          ->where('status_id', 7)
          ->whereMonth('jatuh_tempo', $bulan)
          ->whereYear('jatuh_tempo', $tahun)
          ->exists();

        return [
          'customer_id' => $customer->id,
          'nama' => $customer->nama_customer ?? 'Tidak Ada Nama',
          'status_id' => $customer->status_id,
          'paket_id' => $customer->paket_id,
          'has_invoice_this_month' => $hasInvoiceThisMonth,
          'has_unpaid_invoice' => $hasUnpaidInvoice,
          'issue_type' => $hasUnpaidInvoice ? 'belum_bayar' : ($hasInvoiceThisMonth ? 'status_tidak_sesuai' : 'tidak_ada_invoice')
        ];
      });

      // Kategorisasi masalah
      $problemCategories = $analysisDetails->groupBy('issue_type')->mapWithKeys(function ($group, $key) {
        return [$key => $group->count()];
      });

      return response()->json([
        'success' => true,
        'data' => [
          'period' => ['bulan' => $bulan, 'tahun' => $tahun],
          'summary' => [
            'customer_aktif' => $customerAktif,
            'total_with_ref' => $totalWithRef,
            'gap' => $customerAktif - $totalWithRef,
            'gap_percentage' => $customerAktif > 0 ? round(($customerAktif - $totalWithRef) / $customerAktif * 100, 2) : 0
          ],
          'problem_breakdown' => $problemCategories,
          'affected_customers' => $analysisDetails->values()
        ]
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menganalisis gap',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function fixInvoicePaymentStatus()
  {
    try {
      $bulan = request('bulan', Carbon::now()->month);
      $tahun = request('tahun', Carbon::now()->year);
      $fixType = request('fix_type', 'unpaid_to_paid'); // unpaid_to_paid, paid_to_unpaid

      $fixedCount = 0;

      if ($fixType === 'unpaid_to_paid') {
        // Cari customer yang sudah bayar tapi invoice masih status 7
        $invoicesToFix = Invoice::where('status_id', 7)
          ->whereHas('customer', function ($query) {
            $query->whereNull('deleted_at')
              ->whereIn('status_id', [3,4,9])
              ->whereNot('paket_id', 11);
          })
          ->whereHas('pembayaran', function ($query) use ($bulan) {
            $query->whereMonth('tanggal_bayar', $bulan)
              ->whereYear('tanggal_bayar', Carbon::now()->year);
          })
          ->whereMonth('jatuh_tempo', $bulan)
          ->whereYear('jatuh_tempo', $tahun);

        $fixedCount = $invoicesToFix->update(['status_id' => 8]);

      } elseif ($fixType === 'paid_to_unpaid') {
        // Cari invoice status 8 tapi tidak ada pembayaran tercatat
        $invoicesToFix = Invoice::where('status_id', 8)
          ->whereHas('customer', function ($query) {
            $query->whereNull('deleted_at')
              ->whereIn('status_id', [3,4,9])
              ->whereNot('paket_id', 11);
          })
          ->whereDoesntHave('pembayaran')
          ->whereMonth('jatuh_tempo', $bulan)
          ->whereYear('jatuh_tempo', $tahun);

        $fixedCount = $invoicesToFix->update(['status_id' => 7]);
      }

      return response()->json([
        'success' => true,
        'message' => "Berhasil memperbaiki $fixedCount invoice dengan tipe $fixType",
        'data' => [
          'fixed_count' => $fixedCount,
          'fix_type' => $fixType,
          'period' => ['bulan' => $bulan, 'tahun' => $tahun]
        ]
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat memperbaiki status invoice',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function analyzeStatusMismatchCustomers()
  {
    try {
      $bulan = request('bulan', Carbon::now()->month);
      $tahun = request('tahun', Carbon::now()->year);

      // 8 customer spesifik yang bermasalah dari analisis sebelumnya
      $problematicCustomerIds = [12268, 13119, 13263, 13269, 13519, 13580, 13602, 13606];

      // Investigasi spesifik 8 customer ini
      $specificCustomers = Customer::whereIn('id', $problematicCustomerIds)
        ->with(['invoice' => function ($query) use ($bulan, $tahun) {
          $query->whereMonth('jatuh_tempo', $bulan)
            ->whereYear('jatuh_tempo', $tahun)
            ->with(['pembayaran']);
        }])
        ->get();

      $detailedAnalysis = $specificCustomers->map(function ($customer) use ($bulan, $tahun) {
        $invoices = $customer->invoice;
        $hasUnpaid = $invoices->contains('status_id', 7);
        $hasPaid = $invoices->contains('status_id', 8);
        $hasOtherStatus = $invoices->contains(function ($invoice) {
          return !in_array($invoice->status_id, [7, 8]);
        });

        return [
          'customer_id' => $customer->id,
          'nama' => $customer->nama_customer ?? 'Tidak Ada Nama',
          'customer_status_id' => $customer->status_id,
          'paket_id' => $customer->paket_id,
          'invoices_this_month' => $invoices->map(function ($invoice) {
            return [
              'invoice_id' => $invoice->id,
              'nomor_invoice' => $invoice->nomor_invoice,
              'status_id' => $invoice->status_id,
              'jatuh_tempo' => $invoice->jatuh_tempo,
              'total_tagihan' => $invoice->total_tagihan,
              'pembayaran_count' => $invoice->pembayaran->count(),
              'created_at' => $invoice->created_at,
              'updated_at' => $invoice->updated_at
            ];
          }),
          'analysis' => [
            'has_unpaid_invoice' => $hasUnpaid,
            'has_paid_invoice' => $hasPaid,
            'has_other_status' => $hasOtherStatus,
            'total_invoices' => $invoices->count(),
            'has_pembayaran_records' => $invoices->sum(function ($invoice) {
              return $invoice->pembayaran->count();
            })
          ],
          'issue_type' => $this->determineIssueType($customer, $invoices)
        ];
      });

      return response()->json([
        'success' => true,
        'data' => [
          'period' => ['bulan' => $bulan, 'tahun' => $tahun],
          'investigated_customers' => $problematicCustomerIds,
          'summary' => [
            'total_investigated' => $specificCustomers->count(),
            'found_customers' => $detailedAnalysis->count()
          ],
          'detailed_analysis' => $detailedAnalysis->values()
        ]
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menganalisis status mismatch',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  private function determineIssueType($customer, $invoices)
  {
    $hasUnpaid = $invoices->contains('status_id', 7);
    $hasPaid = $invoices->contains('status_id', 8);
    $hasOtherStatus = $invoices->contains(function ($invoice) {
      return !in_array($invoice->status_id, [7, 8]);
    });

    $hasPembayaran = $invoices->sum(function ($invoice) {
      return $invoice->pembayaran->count();
    }) > 0;

    if ($hasOtherStatus) {
      return [
        'type' => 'invoice_status_anomaly',
        'description' => 'Invoice memiliki status selain 7/8',
        'severity' => 'high'
      ];
    } elseif ($invoices->count() === 0) {
      return [
        'type' => 'no_invoice',
        'description' => 'Customer tidak memiliki invoice bulan ini',
        'severity' => 'medium'
      ];
    } elseif (!$hasUnpaid && !$hasPaid && $hasPembayaran) {
      return [
        'type' => 'payment_without_invoice_status',
        'description' => 'Ada pembayaran tapi invoice status tidak sesuai',
        'severity' => 'high'
      ];
    } elseif ($hasUnpaid && $hasPembayaran) {
      return [
        'type' => 'unpaid_with_payment',
        'description' => 'Invoice masih unpaid tapi sudah ada pembayaran',
        'severity' => 'medium'
      ];
    } else {
      return [
        'type' => 'normal',
        'description' => 'Status normal',
        'severity' => 'low'
      ];
    }
  }

  private function analyzeIssue($customer, $invoices)
  {
    $statusIds = $invoices->pluck('status_id')->unique();

    if ($statusIds->count() === 1) {
      $singleStatus = $statusIds->first();

      if ($singleStatus === 1) {
        return [
          'type' => 'status_draft',
          'description' => 'Invoice masih dalam status draft (1)',
          'severity' => 'medium',
          'recommendation' => 'Periksa apakah invoice ini sengaja dibuat draft atau ada kesalahan sistem'
        ];
      } elseif ($singleStatus === 2) {
        return [
          'type' => 'status_pending',
          'description' => 'Invoice dalam status pending (2)',
          'severity' => 'medium',
          'recommendation' => 'Invoice mungkin menunggu proses atau approval'
        ];
      } elseif ($singleStatus === 9) {
        return [
          'type' => 'status_cancelled',
          'description' => 'Invoice dibatalkan (9)',
          'severity' => 'low',
          'recommendation' => 'Invoice dibatalkan, mungkin perlu dibuat baru'
        ];
      } else {
        return [
          'type' => 'status_unknown',
          'description' => "Status tidak dikenal: {$singleStatus}",
          'severity' => 'high',
          'recommendation' => 'Perlu investigasi manual status invoice ini'
        ];
      }
    } elseif ($statusIds->count() > 1) {
      return [
        'type' => 'multiple_statuses',
        'description' => 'Customer memiliki multiple invoice dengan status berbeda: ' . implode(', ', $statusIds->toArray()),
        'severity' => 'medium',
        'recommendation' => 'Periksa apakah ada duplicate invoice atau proses overlap'
      ];
    }

    return [
      'type' => 'unknown',
      'description' => 'Tidak dapat mengidentifikasi masalah',
      'severity' => 'high',
      'recommendation' => 'Perlu investigasi manual lengkap'
    ];
  }

  public function verifyFix()
  {
    try {
      $bulan = 12; $tahun = 2025;

      // Setelah fix: gunakan query yang paling akurat
      $customerTanpaFasumFixed = Invoice::where('status_id', 8)
        ->whereHas('customer', function ($query) {
          $query->whereNull('deleted_at')
            ->whereIn('status_id', [3,4,9])
            ->whereNot('paket_id', 11);
        })
        ->whereHas('pembayaran', function ($query) use ($bulan) {
          $query->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', Carbon::now()->year);
        })
        ->distinct('customer_id')
        ->count('customer_id');

      // invoicePaids - standardisasi ke tanggal_bayar
      $invoicePaids = Invoice::where('status_id', 8)
        ->whereHas('customer', function ($query) {
          $query->whereNull('deleted_at')
            ->whereIn('status_id', [3,4,9])
            ->whereNot('paket_id', 11);
        })
        ->whereHas('pembayaran', function ($query) use ($bulan) {
          $query->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', Carbon::now()->year);
        })
        ->distinct('customer_id')
        ->count('customer_id');

        $fix = Invoice::where('status_id', 7)
          ->whereHas('customer', function ($query) {
            $query->whereNull('deleted_at')
              ->whereIn('status_id', [3,4,9])
              ->whereNot('paket_id', 11);
          })
          ->whereHas('pembayaran', function ($query) use ($bulan) {
            $query->whereMonth('tanggal_bayar', $bulan)
              ->whereYear('tanggal_bayar', Carbon::now()->year);
          })
          ->distinct('customer_id')
          ->get();

      // Customer status 3 yang sudah bayar
      $customerStatus3Paid = Customer::where('status_id', 3)
        ->whereNot('paket_id', 11)
        ->whereNull('deleted_at')
        ->whereHas('invoice', function ($query) use ($bulan) {
          $query->where('status_id', 8)
            ->whereMonth('jatuh_tempo', $bulan);
        })
        ->count();

      // Customer status 3 yang belum bayar
      $customerStatus3Unpaid = Customer::whereIn('status_id', [3, 4])
        ->whereNot('paket_id', 11)
        ->whereNull('deleted_at')
        ->whereHas('invoice', function ($query) use ($bulan) {
          $query->where('status_id', 7)
            ->whereMonth('jatuh_tempo', $bulan);
        })
        ->count();

      $isFixed = ($customerTanpaFasumFixed === $invoicePaids);

      $pelAktif = Customer::whereNot('paket_id', 11)->whereIn('status_id', [3, 4])->whereNull('deleted_at')->count();

      $debug = Invoice::where('status_id', 7)->whereMonth('jatuh_tempo', Carbon::now()->month)->whereYear('jatuh_tempo', Carbon::now()->year)
              ->whereHas('customer', function ($q) {
                $q->whereIn('status_id', [3,4,9])->whereNull('deleted_at')->whereNot('paket_id', 11);
              })->distinct('customer_id')->count('customer_id');

      return response()->json([
        'success' => true,
        'debug' => $fix,
        'pelAktif' => $pelAktif,
        'pelAktifUnpaid' => $debug,
        'verification' => [
          'customerTanpaFasum_fixed' => $customerTanpaFasumFixed,
          'invoicePaids' => $invoicePaids,
          'customerStatus3Paid' => $customerStatus3Paid,
          'customerStatus3Unpaid' => $customerStatus3Unpaid,
          'gap' => $customerTanpaFasumFixed - $invoicePaids,
          'is_fixed' => $isFixed,
          'status' => $isFixed ? '✅ BERHASIL DIPERBAIKI' : '❌ MASIH ADA GAP'
        ],
        'explanation' => [
          'customerTanpaFasum_fixed' => 'Invoice status 8 + Customer aktif + Pembayaran aktual (tanggal_bayar)',
          'invoicePaids' => 'Invoice status 8 + Customer aktif + Pembayaran aktual (tanggal_bayar)',
          'gap_reason' => $isFixed ? '✅ KEDUANYA menggunakan tanggal_bayar - konsisten!' : '❌ Masih ada perbedaan filter'
        ]
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error verifikasi fix',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function analyzeGap42()
  {
    try {
      $bulan = 12; $tahun = 2025;

      // Customer aktif (base)
      $customerAktif = Customer::whereIn('status_id', [3,4])
        ->whereNot('paket_id', 11)
        ->whereNull('deleted_at')
        ->count();

      // Customer aktif yang sudah bayar (customerTanpaFasum)
      $customerPaid = Invoice::where('status_id', 8)
        ->whereHas('customer', function ($query) {
          $query->whereNull('deleted_at')
            ->whereIn('status_id', [3,4,9])
            ->whereNot('paket_id', 11);
        })
        ->whereHas('pembayaran', function ($query) use ($bulan) {
          $query->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', Carbon::now()->year);
        })
        ->distinct('customer_id')
        ->count('customer_id');

      // Fasum dengan invoice
      $fasumWithInvoice = Customer::where('paket_id', 11)
        ->whereHas('invoice')
        ->whereNull('deleted_at')
        ->count();

      // Gap 42 - cari customer aktif yang tidak bayar
      $gap42Customers = Customer::whereIn('status_id', [3,4])
        ->whereNot('paket_id', 11)
        ->whereNull('deleted_at')
        ->whereDoesntHave('invoice', function ($query) use ($bulan) {
          $query->where('status_id', 8)
            ->whereHas('pembayaran', function ($paymentQuery) use ($bulan) {
              $paymentQuery->whereMonth('tanggal_bayar', $bulan)
                ->whereYear('tanggal_bayar', Carbon::now()->year);
            });
        })
        ->with(['invoice' => function ($query) use ($bulan) {
          $query->whereMonth('jatuh_tempo', $bulan);
        }])
        ->get(['id', 'nama_customer', 'status_id', 'paket_id']);

      $detailedAnalysis = $gap42Customers->map(function ($customer) use ($bulan) {
        $invoices = $customer->invoice;
        $hasUnpaidInvoice = $invoices->contains('status_id', 7);
        $hasOtherStatusInvoice = $invoices->contains(function ($invoice) {
          return !in_array($invoice->status_id, [7, 8]);
        });

        return [
          'customer_id' => $customer->id,
          'nama' => $customer->nama_customer ?? 'No Name',
          'status_id' => $customer->status_id,
          'paket_id' => $customer->paket_id,
          'invoices_this_month' => $invoices->count(),
          'has_unpaid_invoice' => $hasUnpaidInvoice,
          'has_other_status_invoice' => $hasOtherStatusInvoice,
          'issue_type' => $hasUnpaidInvoice ? 'belum_bayar' :
                        ($hasOtherStatusInvoice ? 'status_anomali' : 'tidak_ada_invoice')
        ];
      });

      // Kategorisasi
      $categories = $detailedAnalysis->groupBy('issue_type')->mapWithKeys(function ($group, $key) use ($detailedAnalysis) {
        return [$key => [
          'count' => $group->count(),
          'percentage' => round(($group->count() / $detailedAnalysis->count()) * 100, 1)
        ]];
      });

      return response()->json([
        'success' => true,
        'data' => [
          'summary' => [
            'customerAktif' => $customerAktif,
            'customerPaid' => $customerPaid,
            'fasumWithInvoice' => $fasumWithInvoice,
            'gap42' => $customerAktif - $customerPaid - $fasumWithInvoice,
            'gap42_verified' => $gap42Customers->count()
          ],
          'gap_breakdown' => $categories,
          'affected_customers' => $detailedAnalysis->values()
        ]
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error analyzing gap 42',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function updateInvoice()
  {
      try {
          // Hitung dulu berapa invoice yang akan diupdate
          $invoiceCount = Invoice::whereHas('customer', function($query) {
              $query->where('status_id', 9);
          })->count();

          if ($invoiceCount === 0) {
              return response()->json([
                  'success' => false,
                  'message' => 'Tidak ada invoice ditemukan untuk customer dengan status_id = 9'
              ], 404);
          }

          // Lakukan update
          $updatedCount = Invoice::whereHas('customer', function($query) {
              $query->where('status_id', 9);
          })->update(['paket_id' => 9]);

          return response()->json([
              'success' => true,
              'message' => "Berhasil mengupdate $updatedCount invoice",
              'data' => [
                  'updated_count' => $updatedCount
              ]
          ]);

      } catch (\Exception $e) {
          return response()->json([
              'success' => false,
              'message' => 'Terjadi kesalahan saat mengupdate invoice',
              'error' => $e->getMessage()
          ], 500);
      }
  }


  // Fungsi alternatif: Hanya menghitung yang BERINVOICE
  public function verifyFixSimplified()
  {
      try {
          $bulan = 12;
          $tahun = 2025;

          // Pelanggan Aktif YANG BERINVOICE bulan ini
          $pelAktifDenganInvoice = Customer::whereNot('paket_id', 11)
              ->whereIn('status_id', [3, 4])
              ->whereNull('deleted_at')
              ->whereHas('invoice', function ($query) use ($bulan, $tahun) {
                  $query->whereMonth('jatuh_tempo', $bulan)
                      ->whereYear('jatuh_tempo', $tahun);
              })
              ->count();

          // Invoice Paid (bayar kapan saja, yang penting invoice jatuh tempo bulan ini)
          $invoicePaidsAll = Invoice::where('status_id', 8)
              ->whereMonth('jatuh_tempo', $bulan)
              ->whereYear('jatuh_tempo', $tahun)
              ->whereHas('customer', function ($query) {
                  $query->whereNull('deleted_at')
                      ->whereIn('status_id', [3, 4])
                      ->whereNot('paket_id', 11);
              })
              ->distinct('customer_id')
              ->count('customer_id');

          // Customer Unpaid (status 3 atau 4)
          $customerUnpaid = Customer::whereIn('status_id', [3, 4])
              ->whereNot('paket_id', 11)
              ->whereNull('deleted_at')
              ->whereHas('invoice', function ($query) use ($bulan, $tahun) {
                  $query->where('status_id', 7)
                      ->whereMonth('jatuh_tempo', $bulan)
                      ->whereYear('jatuh_tempo', $tahun);
              })
              ->count();

          $totalTerhitung = $invoicePaidsAll + $customerUnpaid;
          $isFixed = ($totalTerhitung === $pelAktifDenganInvoice);

          return response()->json([
              'success' => true,
              'summary' => [
                  'pelanggan_dengan_invoice' => $pelAktifDenganInvoice,
                  'invoice_paid_all' => $invoicePaidsAll,
                  'customer_unpaid' => $customerUnpaid,
                  'total_terhitung' => $totalTerhitung,
                  'gap' => $pelAktifDenganInvoice - $totalTerhitung,
                  'is_fixed' => $isFixed
              ],
              'formula' => [
                  'rumus' => "{$invoicePaidsAll} + {$customerUnpaid} = {$totalTerhitung}",
                  'target' => "{$pelAktifDenganInvoice}",
                  'status' => $isFixed ? '✅ BERHASIL!' : "❌ Gap: " . ($pelAktifDenganInvoice - $totalTerhitung)
              ]
          ]);

      } catch (\Exception $e) {
          return response()->json([
              'success' => false,
              'error' => $e->getMessage()
          ], 500);
      }
  }

}
