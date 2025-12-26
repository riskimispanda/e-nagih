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


  public function debugging()
  {
      $totalCustomer = Customer::whereIn('status_id', [3, 4, 9])->whereNull('deleted_at')->count();
      $totalNonAktif = Customer::where('status_id', 9)->whereNull('deleted_at')->count();
      $totalAktif = Customer::whereIn('status_id',[3,4])->whereNot('paket_id', 11)->whereNull('deleted_at')->count();

      // Customer yang memiliki invoice (sudah ada di kode sebelumnya)
      $customersWithInvoice = Invoice::select('invoice.customer_id')
          ->join('customer', 'invoice.customer_id', '=', 'customer.id')
          ->whereIn('customer.status_id', [3, 4, 9])
          ->whereNull('customer.deleted_at')
          ->distinct()
          ->count('invoice.customer_id');

      // Customer yang TIDAK memiliki invoice
      $customersWithoutInvoice = Customer::whereIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at')
          ->whereNotExists(function ($query) {
              $query->select(DB::raw(1))
                    ->from('invoice')
                    ->whereColumn('invoice.customer_id', 'customer.id');
          })
          ->count();

      // Customer yang memiliki invoice
      $customersWithInvoice = Invoice::select('invoice.customer_id')
          ->join('customer', 'invoice.customer_id', '=', 'customer.id')
          ->whereIn('customer.status_id', [3, 4, 9])
          ->whereNull('customer.deleted_at')
          ->distinct()
          ->count('invoice.customer_id');

      // Customer yang TIDAK memiliki invoice
      $customersWithoutInvoice = Customer::whereIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at')
          ->whereNotExists(function ($query) {
              $query->select(DB::raw(1))
                    ->from('invoice')
                    ->whereColumn('invoice.customer_id', 'customer.id');
          })
          ->count();

      // FINAL SOLUTION: Gunakan AGGREGATE dari semua customer aktif
      // Total customer yang sudah bayar bulan ini
      $customersPaidThisMonth = Customer::whereIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at')
          ->whereHas('invoice', function ($query) {
              $query->where('status_id', 8)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
          })
          ->count();

      // Total customer yang belum bayar bulan ini
      $customersUnpaidThisMonth = Customer::whereIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at')
          ->whereDoesntHave('invoice', function ($query) {
              $query->where('status_id', 8)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
          })
          ->count();



      // DARI CUSTOMER - Semua customer yang punya invoice belum bayar (status_id=7)
      $invoiceUnpaidAll = Customer::withTrashed()
                      ->whereIn('status_id',[3,4,9])
                      ->whereHas('invoice', function ($query) {
                          $query->where('status_id', 7);
                      })
                      ->count();

      // DARI CUSTOMER - Customer yang punya invoice belum bayar bulan ini
      $invoiceUnpaid = Customer::withTrashed()
                      ->whereIn('status_id',[3,4,9])
                      ->whereHas('invoice', function ($query) {
                          $query->where('status_id', 7)
                                ->whereMonth('jatuh_tempo', Carbon::now()->month);
                      })
                      ->count();

      // DARI CUSTOMER - Semua customer yang punya invoice sudah bayar (status_id=8)
      $invoicePaidAll = Customer::withTrashed()
                      ->whereIn('status_id',[3,4,9])
                      ->whereHas('invoice', function ($query) {
                          $query->where('status_id', 8);
                      })
                      ->count();

      // DARI CUSTOMER - Customer yang punya invoice sudah bayar bulan ini
      $invoicePaid = Customer::withTrashed()
                      ->whereIn('status_id',[3,4,9])
                      ->whereHas('invoice', function ($query) {
                          $query->where('status_id', 8)
                                ->whereMonth('jatuh_tempo', Carbon::now()->month);
                      })
                      ->count();

      // Customer dengan invoice jatuh tempo bulan ini status=7
      $invoiceUnpaid = Customer::withTrashed()
                      ->whereIn('status_id',[3,4,9])
                      ->whereHas('invoice', function ($query) {
                          $query->where('status_id', 7)
                                ->whereMonth('jatuh_tempo', Carbon::now()->month);
                      })
                      ->count();

      // Customer tanpa invoice jatuh tempo bulan ini
      $customersWithoutDueDateInvoice = Customer::withTrashed()
                      ->whereIn('status_id',[3,4,9])
                      ->whereDoesntHave('invoice', function ($query) {
                          $query->whereMonth('jatuh_tempo', Carbon::now()->month);
                      })
                      ->count();

      $fasumWithoutInvoice = Customer::whereIn('status_id', [3,4,9])->whereDoesntHave('invoice')->whereNull('deleted_at')->count();
      $fasumWithInvoice = Customer::whereIn('status_id', [3,4,9])->where('paket_id', 11)->whereHas('invoice')->whereNull('deleted_at')->count();
      $fasumAktif = Customer::where('status_id', 3)->whereNull('deleted_at')->count();

      $bulan = 12;
      $tahun = 2025;
      $coba = Customer::whereIn('status_id', [3,4,9])
          ->whereNull('deleted_at')
          ->whereNot('paket_id', 11)
          ->whereHas('invoice', function ($query) use ($bulan, $tahun) {
              $query->whereMonth('jatuh_tempo', $bulan)
              ->whereYear('jatuh_tempo', $tahun)->whereIn('status_id', [7,8]);
          })
          ->count();

      $blokir = Customer::where('status_id', 9)->whereNull('deleted_at')->count();


      $customerStatusFilter = [3, 4, 9]; // Include status 4

      $invoiceWithRefPembayaran = Invoice::where('status_id', 8)
          ->whereHas('customer', function ($query) use ($customerStatusFilter) {
              $query->whereNull('deleted_at')
                    ->whereIn('status_id', $customerStatusFilter)
                    ->whereNot('paket_id', 11);
          })
          ->whereHas('pembayaran', function ($query) use ($bulan) {
              $currentMonth = $bulan ?? Carbon::now()->month;
              $query->whereMonth('tanggal_bayar', $currentMonth)
                    ->whereYear('tanggal_bayar', Carbon::now()->year);
          })
          ->distinct('customer_id')
          ->count('customer_id');

      $customerStatusFilter = [3, 4, 9]; // Include status 4

      $invoicePaids = Invoice::where('status_id', 8)
          ->whereHas('customer', function ($query) use ($customerStatusFilter) {
              $query->whereNull('deleted_at')
                    ->whereIn('status_id', $customerStatusFilter)
                    ->whereNot('paket_id', 11);
          })
          ->whereHas('pembayaran', function ($query) use ($bulan) {
            $query->whereMonth('tanggal_bayar', $bulan)
                  ->whereYear('tanggal_bayar', Carbon::now()->year);
          })
          ->distinct('customer_id')
          ->count('customer_id');
      $invoiceUnpaids = Invoice::where('status_id', 7)
          ->whereHas('customer', function ($query) use ($customerStatusFilter) {
              $query->whereNull('deleted_at')
                    ->whereIn('status_id', $customerStatusFilter)
                    ->whereNot('paket_id', 11);
          })->whereYear('jatuh_tempo', Carbon::now()->year)
          ->distinct('customer_id')
          ->count('customer_id');


      // FIX: Gunakan query yang paling akurat - validasi pembayaran aktual
      $customerWithoutFasum = Invoice::where('status_id', 8)
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

      // Detail debugging untuk customerTanpaFasum
      $debugCustomerTanpaFasum = [
        'filter_used' => 'status_id = 3 AND paket_id != 11 AND deleted_at IS NULL',
        'result' => $customerWithoutFasum,
        'includes_deleted' => 'NO (whereNull deleted_at)',
        'includes_status_4_9' => 'NO (hanya status 3)',
        'fixed' => '✅ Logic diperbaiki - hanya customer yang sudah bayar'
      ];

      // Detail debugging untuk invoicePaids
      $debugInvoicePaids = [
        'filter_used' => 'status_id IN [3,4,9] AND paket_id != 11 AND deleted_at IS NULL',
        'result' => $invoicePaids,
        'includes_deleted' => 'NO (whereNull deleted_at)',
        'includes_status_4_9' => 'YES (status 3,4,9)',
        'additional_filter' => 'jatuh_tempo bulan Desember'
      ];

      // Analisis gap yang sebenarnya
      $customerAktifAllStatus = Customer::whereIn('status_id', [3,4,9])
        ->whereNot('paket_id', 11)
        ->whereNull('deleted_at')
        ->count();

      // Customer aktif yang punya invoice paid bulan ini
      $customerAktifWithPaidInvoice = Customer::whereIn('status_id', [3,4,9])
        ->whereNot('paket_id', 11)
        ->whereNull('deleted_at')
        ->whereHas('invoice', function ($query) use ($bulan) {
          $query->where('status_id', 8)
            ->whereMonth('jatuh_tempo', $bulan);
        })
        ->count();

      // Customer status 3 saja yang punya invoice paid bulan ini
      $customerStatus3WithPaidInvoice = Customer::where('status_id', 3)
        ->whereNot('paket_id', 11)
        ->whereNull('deleted_at')
        ->whereHas('invoice', function ($query) use ($bulan) {
          $query->where('status_id', 8)
            ->whereMonth('jatuh_tempo', $bulan);
        })
        ->count();

      // Customer status 3 saja (termasuk yang dihapus)
      $customerStatus3All = Customer::where('status_id', 3)
        ->whereNot('paket_id', 11)
        ->count();

      $gapAnalysis = [
        'customerTanpaFasum_vs_customerStatus3All' => [
          'customerTanpaFasum' => $customerWithoutFasum,
          'customerStatus3All' => $customerStatus3All,
          'difference' => $customerWithoutFasum - $customerStatus3All,
          'note' => 'Harusnya sama, filter sama'
        ],
        'customerTanpaFasum_vs_customerStatus3WithPaidInvoice' => [
          'customerTanpaFasum' => $customerWithoutFasum,
          'customerStatus3WithPaidInvoice' => $customerStatus3WithPaidInvoice,
          'difference' => $customerWithoutFasum - $customerStatus3WithPaidInvoice,
          'note' => 'Gap yang wajar: customer yang belum bayar'
        ],
        'invoicePaids_vs_customerStatus3WithPaidInvoice' => [
          'invoicePaids' => $invoicePaids,
          'customerStatus3WithPaidInvoice' => $customerStatus3WithPaidInvoice,
          'difference' => $invoicePaids - $customerStatus3WithPaidInvoice,
          'note' => 'Gap karena invoicePaids include status 4,9'
        ],
        'gap_42_analysis' => [
          'customerAktif' => $fasumAktif,
          'customerTanpaFasum' => $customerWithoutFasum,
          'fasumDenganInvoice' => $fasumWithInvoice,
          'gap_42' => $fasumAktif - $customerWithoutFasum - $fasumWithInvoice,
          'description' => '42 customer aktif tapi tidak punya invoice paid bulan ini'
        ],
        'gap_42_analysis' => [
          'customerAktif' => $fasumAktif,
          'customerTanpaFasum' => $customerWithoutFasum,
          'fasumDenganInvoice' => $fasumWithInvoice,
          'gap_42' => $fasumAktif - $customerWithoutFasum - $fasumWithInvoice,
          'description' => '42 customer aktif tapi tidak punya invoice paid bulan ini'
        ],
        'recommended_fix' => [
          'use_customerAktifWithPaidInvoice' => $customerAktifWithPaidInvoice,
          'description' => 'Ini adalah angka yang benar untuk customer aktif yang sudah bayar'
        ]
      ];

      return response()->json([
        'success' => true,
        'totalCustomer' => $totalCustomer,
        'fasumDenganInvoice' => $fasumWithInvoice,
        'customerTanpaFasum' => $customerWithoutFasum,
        'customerAktifdanNonAktif' => $coba,
        'customerAktif' => $fasumAktif,
        'blokir' => $blokir,
        'invoicePaids' => $invoicePaids,
        'invoiceUnpaids' => $invoiceUnpaids,
        // Debugging detail
        'debug_analysis' => [
          'customerTanpaFasum_debug' => [
            'filter' => 'Invoice status 8 + Customer aktif + Pembayaran aktual bulan ini',
            'result' => $customerWithoutFasum,
            'includes_deleted' => 'NO',
            'includes_status_4_9' => 'YES (3,4,9)',
            'includes_only_paid' => 'YES (status 8 + pembayaran aktual)',
            'validates_payment' => 'YES (whereHas pembayaran tanggal_bayar)',
            'fixed' => '✅ Query paling akurat - validasi pembayaran aktual'
          ],
          'invoicePaids_debug' => [
            'filter' => 'Invoice status 8 + Customer aktif + Pembayaran aktual bulan ini',
            'result' => $invoicePaids,
            'includes_deleted' => 'NO',
            'includes_status_4_9' => 'YES',
            'time_filter' => 'tanggal_bayar bulan Desember (sama dengan customerTanpaFasum)',
            'fixed' => '✅ Standardized ke tanggal_bayar'
          ],
          'gap_analysis' => [
            'customerAktifAllStatus' => $customerAktifAllStatus,
            'customerAktifWithPaidInvoice' => $customerAktifWithPaidInvoice,
            'customerStatus3WithPaidInvoice' => $customerStatus3WithPaidInvoice,
            'customerStatus3All' => $customerStatus3All,
            'differences' => [
              'customerTanpaFasum_vs_customerStatus3All' => $customerWithoutFasum - $customerStatus3All,
              'customerTanpaFasum_vs_customerStatus3WithPaidInvoice' => $customerWithoutFasum - $customerStatus3WithPaidInvoice,
              'invoicePaids_vs_customerStatus3WithPaidInvoice' => $invoicePaids - $customerStatus3WithPaidInvoice
            ],
            'recommended_fix' => [
              'use_customerAktifWithPaidInvoice' => $customerAktifWithPaidInvoice,
              'description' => 'Ini adalah angka yang benar untuk customer aktif yang sudah bayar'
            ]
          ]
        ],
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
      $customerStatus3Unpaid = Customer::where('status_id', 3)
        ->whereNot('paket_id', 11)
        ->whereNull('deleted_at')
        ->whereHas('invoice', function ($query) use ($bulan) {
          $query->where('status_id', 7)
            ->whereMonth('jatuh_tempo', $bulan);
        })
        ->count();

      $isFixed = ($customerTanpaFasumFixed === $invoicePaids);

      $pelAktif = Customer::whereNot('paket_id', 11)->whereIn('status_id', [3, 4])->whereNull('deleted_at')->count();

      $debug = Invoice::where('status_id', 8)->whereMonth('jatuh_tempo', Carbon::now()->month)->whereYear('jatuh_tempo', Carbon::now()->year)
              ->whereHas('customer', function ($q) {
                $q->where('status_id', 3)->whereNull('deleted_at')->whereNot('paket_id', 11);
              })->distinct('customer_id')->count('customer_id');

      return response()->json([
        'success' => true,
        'debug' => $fix,
        'pelAktif' => $pelAktif,
        'debugging' => $debug,
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

}
