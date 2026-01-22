<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Pembayaran;
use App\Models\Customer;
use DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\BeritaAcara;

class CustomerControllerApi extends Controller
{
  public function getInvoiceCustomer($id)
  {
    // Ambil semua invoice berdasarkan customer_id
    $invoices = Invoice::with([
      'customer:id,nama_customer',
      'paket:id,nama_paket',
      'status:id,nama_status'
    ])
      ->select('id', 'merchant_ref', 'customer_id', 'paket_id', 'status_id', 'tagihan', 'jatuh_tempo', 'created_at')
      ->where('customer_id', $id)
      ->orderBy('jatuh_tempo', 'desc')
      ->whereHas('customer')
      ->get();

    // Cek jika tidak ada invoice
    if ($invoices->isEmpty()) {
      return response()->json([
        'success' => false,
        'message' => 'Invoice tidak ditemukan untuk customer ini',
        'data' => []
      ], 404);
    }

    // Transform data invoice
    $formattedInvoices = $invoices->map(function ($invoice) {
      return [
        'id' => $invoice->id,
        'merchant_ref' => $invoice->merchant_ref,
        'customer_name' => $invoice->customer->nama_customer,
        'paket' => $invoice->paket ? $invoice->paket->nama_paket : null,
        'status' => $invoice->status ? $invoice->status->nama_status : null,
        'tagihan' => $invoice->tagihan,
        'jatuh_tempo' => $invoice->jatuh_tempo,
        'created_at' => $invoice->created_at
      ];
    });

    return response()->json([
      'success' => true,
      'data' => $formattedInvoices,
      'count' => $invoices->count()
    ]);
  }

  public function paymentHistory($id)
  {
    // Ambil history pembayaran berdasarkan customer_id
    $payments = Pembayaran::with([
      'invoice.customer:id,nama_customer',
      'invoice.paket:id,nama_paket',
      'invoice:id,customer_id,paket_id,merchant_ref,tagihan'
    ])
      ->select('id', 'invoice_id', 'tanggal_bayar', 'jumlah_bayar', 'metode_bayar', 'created_at')
      ->where('status_id', 8)
      ->whereHas('invoice', function ($query) use ($id) {
        $query->where('customer_id', $id);
      })
      ->whereHas('invoice.customer')
      ->whereHas('invoice.paket')
      ->orderBy('tanggal_bayar', 'desc')
      ->get();

    // Cek jika tidak ada payment
    if ($payments->isEmpty()) {
      return response()->json([
        'success' => false,
        'message' => 'Riwayat pembayaran tidak ditemukan untuk customer ini',
        'data' => []
      ], 404);
    }

    // Transform data payment
    $formattedPayments = $payments->map(function ($payment) {
      return [
        'id' => $payment->id,
        'customer_name' => $payment->invoice->customer->nama_customer,
        'paket' => $payment->invoice->paket->nama_paket,
        'merchant_ref' => $payment->invoice->merchant_ref,
        'tagihan' => $payment->invoice->tagihan,
        'jumlah_bayar' => $payment->jumlah_bayar,
        'metode_bayar' => $payment->metode_bayar,
        'tanggal_bayar' => $payment->tanggal_bayar,
        'created_at' => $payment->created_at
      ];
    });

    return response()->json([
      'success' => true,
      'data' => $formattedPayments,
      'count' => $payments->count()
    ]);
  }

  public function prosesOtomatisPembayaran($id)
  {
    $invoice = Invoice::with('customer', 'paket')->findOrFail($id);

    DB::beginTransaction();
    try {
      // Ambil nilai awal
      $saldoAwal = $invoice->saldo;
      $tagihanAwal = $invoice->tagihan;
      $tambahanAwal = $invoice->tambahan;
      $tunggakanAwal = $invoice->tunggakan;

      // Gunakan saldo untuk membayar (prioritas: tambahan -> tunggakan -> tagihan)
      $saldoTersisa = $saldoAwal;
      $newTambahan = $tambahanAwal;
      $newTunggakan = $tunggakanAwal;
      $newTagihan = $tagihanAwal;

      // Bayar tambahan dulu
      if ($saldoTersisa > 0 && $newTambahan > 0) {
        if ($saldoTersisa >= $newTambahan) {
          $saldoTersisa -= $newTambahan;
          $newTambahan = 0;
        } else {
          $newTambahan -= $saldoTersisa;
          $saldoTersisa = 0;
        }
      }

      // Bayar tunggakan
      if ($saldoTersisa > 0 && $newTunggakan > 0) {
        if ($saldoTersisa >= $newTunggakan) {
          $saldoTersisa -= $newTunggakan;
          $newTunggakan = 0;
        } else {
          $newTunggakan -= $saldoTersisa;
          $saldoTersisa = 0;
        }
      }

      // Bayar tagihan
      if ($saldoTersisa > 0 && $newTagihan > 0) {
        if ($saldoTersisa >= $newTagihan) {
          $saldoTersisa -= $newTagihan;
          $newTagihan = 0;
        } else {
          $newTagihan -= $saldoTersisa;
          $saldoTersisa = 0;
        }
      }

      // Hitung berapa saldo yang terpakai
      $saldoTerpakai = $saldoAwal - $saldoTersisa;
      $saldoBaru = $saldoTersisa;

      // Tentukan status invoice
      $totalSisa = $newTagihan + $newTambahan + $newTunggakan;
      $statusInvoice = ($totalSisa == 0) ? 8 : 7; // 8 = lunas, 7 = belum lunas

      // Update Invoice - SELALU update
      $invoice->update([
        'tagihan' => $newTagihan,
        'tambahan' => $newTambahan,
        'tunggakan' => $newTunggakan,
        'saldo' => $saldoBaru,
        'status_id' => 8,
      ]);

      Log::info('Invoice berhasil diupdate', [
        'invoice_id' => $invoice->id,
        'tagihan' => $newTagihan,
        'tambahan' => $newTambahan,
        'tunggakan' => $newTunggakan,
        'saldo' => $saldoBaru,
        'status' => $statusInvoice
      ]);

      // Buat Invoice Bulan Depan (hanya jika tagihan sudah lunas/0)
      $invoiceBaru = null;
      if ($invoice->status_id == 8) {
        $customer = $invoice->customer;
        $tanggalJatuhTempoLama = Carbon::parse($invoice->jatuh_tempo);
        $tanggalAwal = $tanggalJatuhTempoLama->copy()->addMonthsNoOverflow()->startOfMonth();
        $tanggalJatuhTempo = $tanggalAwal->copy()->endOfMonth();

        // Cek apakah sudah ada invoice bulan depan
        $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
          ->whereMonth('jatuh_tempo', $tanggalJatuhTempo->month)
          ->whereYear('jatuh_tempo', $tanggalJatuhTempo->year)
          ->exists();

        if (!$sudahAda) {
          // Generate Merchant Reference
          $merchant = 'INV-' . $customer->id . '-' . time();

          $invoiceBaru = Invoice::create([
            'customer_id' => $invoice->customer_id,
            'paket_id' => $customer->paket_id,
            'tagihan' => $customer->paket->harga,
            'tambahan' => 0,
            'tunggakan' => $newTunggakan, // Sisa tunggakan pindah ke bulan depan
            'saldo' => $saldoBaru,
            'status_id' => 7,
            'merchant_ref' => $merchant,
            'created_at' => $tanggalAwal,
            'updated_at' => $tanggalAwal,
            'jatuh_tempo' => $tanggalJatuhTempo,
            'tanggal_blokir' => $invoice->tanggal_blokir,
          ]);

          Log::info('Invoice bulan depan berhasil dibuat', [
            'invoice_id' => $invoiceBaru->id,
            'customer_id' => $customer->id,
            'jatuh_tempo' => $tanggalJatuhTempo,
            'tagihan' => $customer->paket->harga,
            'tunggakan' => $newTunggakan
          ]);
        }
      }

      // Update Status Customer jika diblokir dan sudah lunas
      if ($invoice->customer->status_id == 9 && $totalSisa == 0) {
        try {
          $mikrotik = new MikrotikServices();
          $client = MikrotikServices::connect($invoice->customer->router);
          $mikrotik->unblokUser($client, $invoice->customer->usersecret, $invoice->customer->paket->paket_name);
          $mikrotik->removeActiveConnections($client, $invoice->customer->usersecret);

          $invoice->customer->update(['status_id' => 3]);

          Log::info('Customer berhasil di unblock', ['customer_id' => $invoice->customer->id]);
        } catch (Exception $e) {
          Log::error('Failed to unblock customer', [
            'customer_id' => $invoice->customer->id,
            'error' => $e->getMessage()
          ]);
        }
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Invoice berhasil diproses',
        'data' => [
          'invoice_id' => $invoice->id,
          'saldo_terpakai' => $saldoTerpakai,
          'saldo_sisa' => $saldoBaru,
          'tagihan_sisa' => $newTagihan,
          'tambahan_sisa' => $newTambahan,
          'tunggakan_sisa' => $newTunggakan,
          'total_sisa' => $totalSisa,
          'status' => $statusInvoice == 8 ? 'Lunas' : 'Belum Lunas',
          'invoice_baru' => $invoiceBaru ? [
            'id' => $invoiceBaru->id,
            'jatuh_tempo' => $invoiceBaru->jatuh_tempo,
            'tagihan' => $invoiceBaru->tagihan,
            'tunggakan' => $invoiceBaru->tunggakan
          ] : null
        ]
      ], 200);

    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Gagal memproses invoice otomatis: ' . $e->getMessage(), [
        'line' => $e->getLine(),
        'file' => $e->getFile()
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Gagal memproses invoice: ' . $e->getMessage()
      ], 500);
    }
  }

  public function checkInvoiceWithoutPayment()
  {
    try {
      // Cari invoice dengan status 8 (sudah bayar) tapi tidak ada pembayaran tercatat
      $invoicesWithoutPayment = Invoice::with(['customer', 'pembayaran'])
        ->where('status_id', 8)
        ->whereDoesntHave('pembayaran')
        ->whereMonth('jatuh_tempo', Carbon::now()->month)
        ->orderBy('created_at', 'desc')
        ->get();

      if ($invoicesWithoutPayment->isEmpty()) {
        return response()->json([
          'success' => true,
          'message' => 'Tidak ada invoice status 8 tanpa pembayaran',
          'data' => [
            'total_invoices' => 0,
            'invoices' => []
          ]
        ]);
      }

      // Transform data untuk response
      $formattedInvoices = $invoicesWithoutPayment->map(function ($invoice) {
        return [
          'invoice_id' => $invoice->id,
          'merchant_ref' => $invoice->merchant_ref,
          'customer_id' => $invoice->customer_id,
          'customer_name' => $invoice->customer ? $invoice->customer->nama_customer : 'N/A',
          'customer_status_id' => $invoice->customer ? $invoice->customer->status_id : 'N/A',
          'paket_id' => $invoice->paket_id,
          'paket_name' => $invoice->paket ? $invoice->paket->nama_paket : 'N/A',
          'tagihan' => $invoice->tagihan,
          'tambahan' => $invoice->tambahan,
          'tunggakan' => $invoice->tunggakan,
          'saldo' => $invoice->saldo,
          'status_id' => $invoice->status_id,
          'jatuh_tempo' => $invoice->jatuh_tempo,
          'created_at' => $invoice->created_at,
          'updated_at' => $invoice->updated_at,
          'issue_type' => 'invoice_status_8_tanpa_pembayaran',
          'severity' => 'high',
          'recommendation' => 'Perlu investigasi - invoice status 8 tapi tidak ada catatan pembayaran'
        ];
      });

      // Statistik tambahan
      $totalTagihan = $invoicesWithoutPayment->sum('tagihan');
      $totalTambahan = $invoicesWithoutPayment->sum('tambahan');
      $totalTunggakan = $invoicesWithoutPayment->sum('tunggakan');
      $totalSaldo = $invoicesWithoutPayment->sum('saldo');

      // Group by customer untuk analisis
      $customerBreakdown = $invoicesWithoutPayment->groupBy('customer_id')
        ->mapWithKeys(function ($group, $customerId) {
          $customer = $group->first()->customer;
          return [
            $customerId => [
              'customer_name' => $customer ? $customer->nama_customer : 'N/A',
              'customer_status_id' => $customer ? $customer->status_id : 'N/A',
              'invoice_count' => $group->count(),
              'total_tagihan' => $group->sum('tagihan'),
              'total_tambahan' => $group->sum('tambahan'),
              'total_tunggakan' => $group->sum('tunggakan'),
              'total_saldo' => $group->sum('saldo')
            ]
          ];
        });

      return response()->json([
        'success' => true,
        'message' => 'Ditemukan invoice status 8 tanpa pembayaran',
        'data' => [
          'summary' => [
            'total_invoices' => $invoicesWithoutPayment->count(),
            'total_customers_affected' => $customerBreakdown->count(),
            'total_tagihan' => $totalTagihan,
            'total_tambahan' => $totalTambahan,
            'total_tunggakan' => $totalTunggakan,
            'total_saldo' => $totalSaldo,
            'total_value' => $totalTagihan + $totalTambahan + $totalTunggakan
          ],
          'customer_breakdown' => $customerBreakdown,
          'problematic_invoices' => $formattedInvoices->values()
        ]
      ]);

    } catch (\Exception $e) {
      Log::error('Error checking invoice without payment: ' . $e->getMessage(), [
        'line' => $e->getLine(),
        'file' => $e->getFile()
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengecek invoice tanpa pembayaran',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function fixInvoiceWithoutPayment()
  {
    try {
      // Cari invoice status 8 tanpa pembayaran
      $invoicesToFix = Invoice::where('status_id', 8)
        ->whereDoesntHave('pembayaran')
        ->get();

      if ($invoicesToFix->isEmpty()) {
        return response()->json([
          'success' => true,
          'message' => 'Tidak ada invoice yang perlu diperbaiki',
          'data' => [
            'fixed_count' => 0
          ]
        ]);
      }

      $fixedCount = 0;
      $errors = [];

      DB::beginTransaction();
      try {
        foreach ($invoicesToFix as $invoice) {
          try {
            // Update status invoice menjadi 7 (belum bayar)
            $invoice->update(['status_id' => 7]);
            $fixedCount++;

            Log::info('Invoice status diperbaiki', [
              'invoice_id' => $invoice->id,
              'customer_id' => $invoice->customer_id,
              'old_status' => 8,
              'new_status' => 7
            ]);

          } catch (\Exception $e) {
            $errors[] = [
              'invoice_id' => $invoice->id,
              'error' => $e->getMessage()
            ];
            Log::error('Gagal memperbaiki invoice', [
              'invoice_id' => $invoice->id,
              'error' => $e->getMessage()
            ]);
          }
        }

        DB::commit();

        return response()->json([
          'success' => true,
          'message' => "Berhasil memperbaiki {$fixedCount} invoice",
          'data' => [
            'fixed_count' => $fixedCount,
            'error_count' => count($errors),
            'errors' => $errors
          ]
        ]);

      } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
      }

    } catch (\Exception $e) {
      Log::error('Error fixing invoice without payment: ' . $e->getMessage(), [
        'line' => $e->getLine(),
        'file' => $e->getFile()
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat memperbaiki invoice',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function testInvoice()
  {
    $totalCustomer = Customer::whereNull('deleted_at')->whereIn('status_id', [3, 4, 9])->count();
    $customerAktif = Customer::whereNull('deleted_at')->whereIn('status_id', [3, 4])->count();
    $customerNonAktif = Customer::whereNull('deleted_at')->where('status_id', 9)->whereNot('paket_id', 11)->count();
    $customerFasum = Customer::whereNull('deleted_at')->whereIn('status_id', [3, 4])->where('paket_id', 11)->count();
    $customerBA = BeritaAcara::count();

    $invoiceFromCustomer = Customer::whereIn('status_id', [3, 4, 9])->whereNull('deleted_at')->whereNot('paket_id', 11)
      ->whereHas('invoice', function ($q) {
        $q->whereMonth('jatuh_tempo', Carbon::now()->month)->whereNot('paket_id', 11);
      })->count();
    $invoiceFromCustomerPaid = Invoice::whereHas('customer', function ($q) {
      $q->whereIn('status_id', [3, 4])
        ->whereNull('deleted_at')
        ->whereNot('paket_id', 11);
    })
      ->where('status_id', 8)
      ->whereMonth('jatuh_tempo', Carbon::now()->month)
      ->whereNot('paket_id', 11)
      ->distinct('customer_id')
      ->count('customer_id');
    $invoiceFromCustomerPaidGet = Customer::whereIn('status_id', [3, 4, 9])->whereNull('deleted_at')->whereNot('paket_id', 11)
      ->whereHas('invoice', function ($q) {
        $q->where('status_id', 8)->whereMonth('jatuh_tempo', Carbon::now()->month)->whereNot('paket_id', 11);
      })->get();
    $invoiceFromCustomerUnpaidGet = Customer::whereIn('status_id', [3, 4, 9])->whereNull('deleted_at')->whereNot('paket_id', 11)
      ->whereHas('invoice', function ($q) {
        $q->where('status_id', 7)->whereMonth('jatuh_tempo', Carbon::now()->month)->whereNot('paket_id', 11);
      })->get();
    $invoiceFromCustomerUnpaid = Customer::whereIn('status_id', [3, 4, 9])->whereNull('deleted_at')->whereNot('paket_id', 11)
      ->whereHas('invoice', function ($q) {
        $q->where('status_id', 7)->whereMonth('jatuh_tempo', Carbon::now()->month)->whereNot('paket_id', 11);
      })->count();


    $withPayment = Invoice::whereHas('customer', function ($q) {
      $q->whereIn('status_id', [3, 4, 9])
        ->whereNot('paket_id', 11)->withTrashed();
    })
      ->whereHas('pembayaran')
      ->where('status_id', 8)
      ->whereMonth('jatuh_tempo', Carbon::now()->month)
      ->distinct('customer_id')
      ->count('customer_id');

    // Tanpa pembayaran
    $withoutPayment = Invoice::whereHas('customer', function ($q) {
      $q->whereIn('status_id', [3, 4, 9])
        ->whereNot('paket_id', 11)->withTrashed();
    })
      ->whereDoesntHave('pembayaran')
      ->where('status_id', 7)
      ->whereMonth('jatuh_tempo', Carbon::now()->month)
      ->distinct('customer_id')
      ->count('customer_id');

    $invMonthly = Invoice::whereIn('status_id', [7, 8])->whereMonth('jatuh_tempo', Carbon::now()->month)
      ->distinct('customer_id')->count();

    $formatPaid = $invoiceFromCustomerPaidGet->map(function ($customer) {
      return [
        'id' => $customer->id,
        'name' => $customer->nama_customer
      ];
    });

    $formatUnpaid = $invoiceFromCustomerUnpaidGet->map(function ($customer) {
      return [
        'id' => $customer->id,
        'name' => $customer->nama_customer
      ];
    });

    // $customerFix = $customerAktif->map(function ($customer) {
    //     return [
    //         'id' => $customer->id,
    //         'name' => $customer->nama_customer
    //     ];
    // });

    return response()->json([
      'success' => true,
      'totalCustomer' => $totalCustomer,
      'customerAktif' => $customerAktif,
      'customerNonAktif' => $customerNonAktif,
      'fasum' => $customerFasum,
      'customerBA' => $customerBA,
      'totalInvoiceCustomer' => $invoiceFromCustomer,
      'totalInvoicePaid' => $invoiceFromCustomerPaid,
      'totalInvoiceUnpaid' => $invoiceFromCustomerUnpaid,
      'data-paid' => [
        'countPaid' => $formatPaid->count()
      ],
      'data-unpaid' => [
        'countUnpaid' => $formatUnpaid->count()
      ],
      'withPayment' => $withPayment,
      'withoutPayment' => $withoutPayment,
      'invMonth' => $invMonthly
    ]);
  }

  public function getInvoicesByMonthOptimized()
  {
    // Query untuk UNPAID invoices
    $monthlyUnpaidData = Invoice::selectRaw('MONTH(jatuh_tempo) as month, COUNT(DISTINCT customer_id) as unpaid_count')
      ->whereHas('customer', function ($q) {
        $q->whereIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at')
          ->whereNot('paket_id', 11);
      })
      ->where('status_id', 7)
      ->whereYear('jatuh_tempo', 2025)
      ->whereNot('paket_id', 11)
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    // Query untuk PAID invoices
    $monthlyPaidData = Invoice::selectRaw('MONTH(jatuh_tempo) as month, COUNT(DISTINCT customer_id) as paid_count')
      ->whereHas('customer', function ($q) {
        $q->whereIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at')
          ->whereNot('paket_id', 11);
      })
      ->where('status_id', 8)
      ->whereYear('jatuh_tempo', 2025)
      ->whereNot('paket_id', 11)
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    // Gabungkan data untuk semua 12 bulan
    $completeData = collect(range(1, 12))->map(function ($month) use ($monthlyUnpaidData, $monthlyPaidData) {
      $unpaid = $monthlyUnpaidData->firstWhere('month', $month);
      $paid = $monthlyPaidData->firstWhere('month', $month);

      return [
        'month' => $month,
        'month_name' => Carbon::create(2025, $month, 1)->format('F'),
        'unpaid_count' => $unpaid ? $unpaid->unpaid_count : 0,
        'paid_count' => $paid ? $paid->paid_count : 0,
        'total_count' => ($unpaid ? $unpaid->unpaid_count : 0) + ($paid ? $paid->paid_count : 0)
      ];
    });

    return response()->json([
      'success' => true,
      'year' => 2025,
      'monthly_invoices' => $completeData,
      'summary' => [
        'total_unpaid_2025' => $completeData->sum('unpaid_count'),
        'total_paid_2025' => $completeData->sum('paid_count'),
        'grand_total_2025' => $completeData->sum('total_count')
      ]
    ]);
  }


  public function findMissingCustomers()
  {
    try {
      $bulan = Carbon::now()->month;
      $tahun = Carbon::now()->year;

      // 1. Pelanggan Aktif (target)
      $pelAktif = Customer::whereNot('paket_id', 11)
        ->whereIn('status_id', [3, 4])
        ->whereNull('deleted_at')
        ->pluck('id');

      // 2. Customer yang sudah masuk hitungan invoicePaids (status 8 + ada pembayaran)
      $invoicePaidsCustomers = Invoice::where('status_id', 8)
        ->whereHas('customer', function ($query) {
          $query->whereNull('deleted_at')
            ->whereIn('status_id', [3, 4, 9])
            ->whereNot('paket_id', 11);
        })
        ->whereHas('pembayaran', function ($query) use ($bulan, $tahun) {
          $query->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', $tahun);
        })
        ->distinct()
        ->pluck('customer_id');

      $countAllInvoice = Invoice::distinct('customer_id')->whereNot('paket_id', 11)
        ->whereHas('customer', function ($q) {
          $q->whereNull('deleted_at')->whereIn('status_id', [3, 4, 9])->whereNot('paket_id', 11);
        })
        ->count('customer_id');

      // 3. Customer yang masuk hitungan customerStatus3Unpaid
      $customerStatus3UnpaidIds = Customer::where('status_id', 3)
        ->whereNot('paket_id', 11)
        ->whereNull('deleted_at')
        ->whereHas('invoice', function ($query) use ($bulan) {
          $query->where('status_id', 7)
            ->whereMonth('jatuh_tempo', $bulan);
        })
        ->pluck('id');

      // 4. Gabungkan customer yang sudah terhitung
      $countedCustomers = $invoicePaidsCustomers->merge($customerStatus3UnpaidIds)->unique();

      // 5. Temukan customer yang TIDAK terhitung (12 data yang hilang)
      $missingCustomers = $pelAktif->diff($countedCustomers);

      // 6. Ambil detail customer yang hilang
      $missingDetails = Customer::whereIn('id', $missingCustomers)
        ->with([
          'invoice' => function ($q) use ($bulan) {
            $q->whereMonth('jatuh_tempo', $bulan)
              ->with('pembayaran');
          }
        ])
        ->get()
        ->map(function ($customer) use ($bulan) {
          $invoices = $customer->invoice;

          return [
            'customer_id' => $customer->id,
            'nama' => $customer->nama_customer,
            'status_id' => $customer->status_id,
            'paket_id' => $customer->paket_id,
            'invoice_bulan_ini' => $invoices->map(function ($inv) {
              return [
                'id' => $inv->id,
                'status_id' => $inv->status_id,
                'jatuh_tempo' => $inv->jatuh_tempo,
                'ada_pembayaran' => $inv->pembayaran->count() > 0,
                'pembayaran' => $inv->pembayaran->map(function ($p) {
                  return [
                    'tanggal_bayar' => $p->tanggal_bayar,
                    'bulan' => date('m', strtotime($p->tanggal_bayar))
                  ];
                })
              ];
            }),
            'kemungkinan_alasan' => $this->analyzeWhyMissing($customer, $bulan)
          ];
        });

      return response()->json([
        'success' => true,
        'summary' => [
          'total_pelanggan_aktif' => $pelAktif->count(),
          'terhitung_invoicePaids' => $invoicePaidsCustomers->count(),
          'terhitung_customerStatus3Unpaid' => $customerStatus3UnpaidIds->count(),
          'total_terhitung' => $countedCustomers->count(),
          'tidak_terhitung' => $missingCustomers->count(),
          'rumus' => $invoicePaidsCustomers->count() . ' + ' . $customerStatus3UnpaidIds->count() . ' = ' . $countedCustomers->count() . ' (seharusnya ' . $pelAktif->count() . ')'
        ],
        'missing_customers' => $missingDetails,
        'total_all_invoice' => $countAllInvoice,
        'breakdown' => [
          'status_4_tanpa_invoice' => $this->countByScenario($missingDetails, 'status_4_no_invoice'),
          'invoice_status_selain_7_8' => $this->countByScenario($missingDetails, 'invoice_other_status'),
          'pembayaran_bulan_lain' => $this->countByScenario($missingDetails, 'payment_other_month'),
          'tidak_ada_invoice_sama_sekali' => $this->countByScenario($missingDetails, 'no_invoice')
        ]
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => $e->getMessage()
      ], 500);
    }
  }

  private function analyzeWhyMissing($customer, $bulan)
  {
    $reasons = [];

    // Cek apakah customer status 4
    if ($customer->status_id == 4) {
      $reasons[] = "Status 4 (bukan status 3) - tidak masuk customerStatus3Unpaid";
    }

    // Cek invoice bulan ini
    $invoicesBulanIni = $customer->invoice->filter(function ($inv) use ($bulan) {
      return date('m', strtotime($inv->jatuh_tempo)) == $bulan;
    });

    if ($invoicesBulanIni->isEmpty()) {
      $reasons[] = "Tidak ada invoice jatuh tempo bulan " . $bulan;
    } else {
      foreach ($invoicesBulanIni as $inv) {
        if ($inv->status_id == 7) {
          $reasons[] = "Ada invoice status 7 (unpaid) tapi tidak masuk hitungan customerStatus3Unpaid karena status customer bukan 3";
        } elseif ($inv->status_id == 8) {
          if ($inv->pembayaran->isEmpty()) {
            $reasons[] = "Invoice status 8 tapi tidak ada pembayaran";
          } else {
            $pembayaranBulanLain = $inv->pembayaran->filter(function ($p) use ($bulan) {
              return date('m', strtotime($p->tanggal_bayar)) != $bulan;
            });
            if ($pembayaranBulanLain->isNotEmpty()) {
              $reasons[] = "Invoice status 8 tapi pembayaran di bulan lain";
            }
          }
        } else {
          $reasons[] = "Invoice dengan status " . $inv->status_id . " (bukan 7 atau 8)";
        }
      }
    }

    return $reasons;
  }

  private function countByScenario($missingDetails, $scenario)
  {
    return $missingDetails->filter(function ($detail) use ($scenario) {
      $reasons = implode(' ', $detail['kemungkinan_alasan']);

      switch ($scenario) {
        case 'status_4_no_invoice':
          return str_contains($reasons, 'Status 4') && str_contains($reasons, 'Tidak ada invoice');
        case 'invoice_other_status':
          return str_contains($reasons, 'Invoice dengan status') && !str_contains($reasons, '7') && !str_contains($reasons, '8');
        case 'payment_other_month':
          return str_contains($reasons, 'pembayaran di bulan lain');
        case 'no_invoice':
          return str_contains($reasons, 'Tidak ada invoice jatuh tempo');
        default:
          return false;
      }
    })->count();
  }


}
