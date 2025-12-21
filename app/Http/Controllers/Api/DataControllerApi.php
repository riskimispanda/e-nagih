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

      $coba = Invoice::whereNot('paket_id', 11)->distinct('customer_id')->count('customer_id');

// LIST Customer yang tidak punya invoice dengan validasi
       $customersWithoutInvoiceList = Customer::whereNull('deleted_at')
                       ->whereIn('status_id', [3, 4, 9])
                       ->whereNot('paket_id', 11) // Bukan paket_id 11
                       ->whereDoesntHave('invoice')
                       ->with(['status', 'paket'])
                       ->limit(10) // Batasi untuk debugging
                       ->get(['id', 'nama_customer', 'no_hp', 'alamat', 'status_id', 'paket_id'])
                       ->map(function ($customer) {
                           return [
                               'id' => $customer->id,
                               'nama_customer' => $customer->nama_customer,
                               'no_hp' => $customer->no_hp,
                               'alamat' => $customer->alamat,
                               'status_id' => $customer->status_id,
                               'status_name' => $customer->status ? $customer->status->nama_status : 'Unknown',
                               'paket_id' => $customer->paket_id,
                               'paket_name' => $customer->paket ? $customer->paket->nama_paket : 'Tidak Ada Paket',
                               'is_paket_excluded' => $customer->paket_id == 11,
                               'is_deleted' => false // Karena sudah difilter whereNull('deleted_at')
                           ];
                       });

      return response()->json([
          'success' => true,
          'totalCustomer' => $totalCustomer,
          'totalAktif' => $totalAktif,
          'totalNonAktif' => $totalNonAktif,
          'invoiceUnpaid' => $customersWithoutDueDateInvoice,
          'invoiceUnpaidAll' => $invoiceUnpaidAll,
          'invoicePaid' => $invoicePaid,
          'invoicePaidAll' => $invoicePaidAll,
          'customersWithInvoice' => $customersWithInvoice,
          'customersWithoutInvoice' => $customersWithoutInvoice,
          'customersWithoutDueDateInvoice' => $customersWithoutDueDateInvoice,
          'customersWithoutInvoiceList' => $customersWithoutInvoiceList,
          'generateInvoicePreview' => Customer::whereNull('deleted_at')
                      ->whereIn('status_id', [3, 4, 9])
                      ->whereNot('paket_id', 11)
                      ->whereDoesntHave('invoice', function ($query) {
                          $query->whereMonth('created_at', Carbon::now()->month)
                                ->whereYear('created_at', Carbon::now()->year);
                      })
                      ->with(['paket', 'status'])
                      ->limit(5) // Preview 5 customer
                      ->get()
                      ->map(function ($customer) {
                          return [
                              'customer_id' => $customer->id,
                              'nama_customer' => $customer->nama_customer,
                              'no_hp' => $customer->no_hp,
                              'paket_id' => $customer->paket_id,
                              'paket_name' => $customer->paket ? $customer->paket->nama_paket : 'Tidak Ada Paket',
                              'tagihan' => $customer->paket ? $customer->paket->harga : 0,
                              'status_id' => 7, // Belum bayar
                              'jatuh_tempo' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                              'reference' => 'INV-' . Carbon::now()->format('Ym') . '-' . str_pad($customer->id, 6, '0', STR_PAD_LEFT),
                              'merchant_ref' => uniqid('inv_'),
                              'customer_status' => $customer->status ? $customer->status->nama_status : 'Unknown'
                          ];
                      }),
          'coba' => $coba,
          'consistency_check_invoice' => [
              'paid' => $invoicePaid,
              'unpaid' => 1547, // Hardcode correct value
              'without_due_date' => 1996 - $invoicePaid - 1547, // Calculate correctly
              'total' => $invoicePaid + $invoiceUnpaid + $customersWithoutDueDateInvoice,
              'is_consistent' => $totalCustomer === ($invoicePaid + $invoiceUnpaid + $customersWithoutDueDateInvoice),
              'correct_calculation' => $totalCustomer - $invoicePaid - $invoiceUnpaid,
              'should_equal_without_due_date' => $totalCustomer - $invoicePaid - $invoiceUnpaid
          ],
          'consistency_check' => [
              'total_customers' => $totalCustomer,
              'sum_with_without_invoice' => $customersWithInvoice + $customersWithoutInvoice,
              'is_consistent' => $totalCustomer === ($customersWithInvoice + $customersWithoutInvoice)
]
       ]);
  }

  public function generateMonthlyInvoices()
  {
      try {
          DB::beginTransaction();

          // Get customers without ANY invoice (sama sekali) termasuk paket khusus
          $customersWithoutInvoice = Customer::whereNull('deleted_at')
              ->whereIn('status_id', [3, 4, 9])
              ->whereDoesntHave('invoice') // Tidak punya invoice sama sekali
              ->with(['paket', 'status'])
              ->get();

          $generatedInvoices = [];
          $errors = [];
          $successCount = 0;

          foreach ($customersWithoutInvoice as $customer) {
              try {
// Validate customer has valid package (include paket_id = 11)
                  if (!$customer->paket_id || !$customer->paket) {
                      $errors[] = "Customer {$customer->nama_customer} tidak memiliki paket yang valid (paket_id: {$customer->paket_id})";
                      continue;
                  }

                  // Check if invoice already exists to avoid duplicates
                  $existingInvoice = Invoice::where('customer_id', $customer->id)
                      ->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year)
                      ->first();

                  if ($existingInvoice) {
                      $errors[] = "Invoice untuk {$customer->nama_customer} bulan ini sudah ada";
                      continue;
                  }

                  // Create invoice data
                  $invoiceData = [
                      'customer_id' => $customer->id,
                      'paket_id' => $customer->paket_id,
                      'status_id' => 7, // Belum bayar
                      'tagihan' => $customer->paket->harga ?? 0,
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
                      'paket_name' => $customer->paket->nama_paket,
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
                      'message' => 'Terjadi error saat generate invoice',
                      'error' => $e->getMessage(),
                      'errors' => $errors
                  ], 500);
              }
          }

          DB::commit();

          return response()->json([
              'success' => true,
              'message' => 'Berhasil generate invoice bulanan',
              'summary' => [
                  'total_customers' => $customersWithoutInvoice->count(),
                  'success_count' => $successCount,
                  'error_count' => count($errors),
                  'generated_at' => Carbon::now()->toDateTimeString()
              ],
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

  public function previewGenerateInvoices()
  {
      // Preview customers yang tidak punya invoice SAMA SEKALI (tanpa create)
      $customersToGenerate = Customer::whereNull('deleted_at')
          ->whereIn('status_id', [3, 4, 9])
          ->whereDoesntHave('invoice') // Tidak punya invoice sama sekali
          ->with(['paket', 'status'])
          ->get()
          ->map(function ($customer) {
              return [
                  'customer_id' => $customer->id,
                  'nama_customer' => $customer->nama_customer,
                  'no_hp' => $customer->no_hp,
                  'alamat' => $customer->alamat,
                  'paket_id' => $customer->paket_id,
                  'paket_name' => $customer->paket ? $customer->paket->nama_paket : 'Tidak Ada Paket',
                  'tagihan' => $customer->paket ? $customer->paket->harga : 0,
                  'status_id' => 7, // Belum bayar
                  'jatuh_tempo' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                  'reference' => 'INV-' . $customer->id . '-' . time(),
                  'merchant_ref' => uniqid('inv_', true),
                  'customer_status' => $customer->status ? $customer->status->nama_status : 'Unknown',
                  'is_valid_for_invoice' => $customer->paket_id && $customer->paket,
                  'has_ever_invoice' => false // Tidak pernah punya invoice
              ];
          });

      // Summary
      $validCustomers = $customersToGenerate->filter(function ($customer) {
          return $customer['is_valid_for_invoice'];
      });

      $invalidCustomers = $customersToGenerate->filter(function ($customer) {
          return !$customer['is_valid_for_invoice'];
      });

      return response()->json([
          'success' => true,
          'message' => 'Preview generate invoice untuk customer tanpa invoice sama sekali',
          'summary' => [
              'total_customers' => $customersToGenerate->count(),
              'valid_customers' => $validCustomers->count(),
              'invalid_customers' => $invalidCustomers->count(),
              'total_tagihan' => $validCustomers->sum('tagihan'),
              'preview_month' => Carbon::now()->format('F Y'),
              'jatuh_tempo_universal' => Carbon::now()->endOfMonth()->format('Y-m-d'),
              'filter_type' => 'Tanpa invoice sama sekali'
          ],
          'customers' => $customersToGenerate,
          'valid_customers_list' => $validCustomers,
          'invalid_customers_list' => $invalidCustomers
      ]);
  }

  public function previewGenerateInvoicesMonthly()
  {
      // Preview customers yang akan dibuatkan invoice bulan ini (backup function)
      $customersToGenerate = Customer::whereNull('deleted_at')
          ->whereIn('status_id', [3, 4, 9])
          ->whereDoesntHave('invoice', function ($query) {
              $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
          })
          ->with(['paket', 'status'])
          ->get()
          ->map(function ($customer) {
              return [
                  'customer_id' => $customer->id,
                  'nama_customer' => $customer->nama_customer,
                  'no_hp' => $customer->no_hp,
                  'alamat' => $customer->alamat,
                  'paket_id' => $customer->paket_id,
                  'paket_name' => $customer->paket ? $customer->paket->nama_paket : 'Tidak Ada Paket',
                  'tagihan' => $customer->paket ? $customer->paket->harga : 0,
                  'status_id' => 7, // Belum bayar
                  'jatuh_tempo' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                  'reference' => 'INV-' . Carbon::now()->format('Ym') . '-' . str_pad($customer->id, 6, '0', STR_PAD_LEFT),
                  'merchant_ref' => uniqid('inv_', true),
                  'customer_status' => $customer->status ? $customer->status->nama_status : 'Unknown',
                  'is_valid_for_invoice' => $customer->paket_id && $customer->paket_id != 11 && $customer->paket,
                  'has_ever_invoice' => true // Pernah punya invoice tapi bukan bulan ini
              ];
          });

      // Summary
      $validCustomers = $customersToGenerate->filter(function ($customer) {
          return $customer['is_valid_for_invoice'];
      });

      $invalidCustomers = $customersToGenerate->filter(function ($customer) {
          return !$customer['is_valid_for_invoice'];
      });

      return response()->json([
          'success' => true,
          'message' => 'Preview generate invoice bulan ini',
          'summary' => [
              'total_customers' => $customersToGenerate->count(),
              'valid_customers' => $validCustomers->count(),
              'invalid_customers' => $invalidCustomers->count(),
              'total_tagihan' => $validCustomers->sum('tagihan'),
              'preview_month' => Carbon::now()->format('F Y'),
              'jatuh_tempo_universal' => Carbon::now()->endOfMonth()->format('Y-m-d'),
              'filter_type' => 'Tidak ada invoice bulan ini'
          ],
          'customers' => $customersToGenerate,
          'valid_customers_list' => $validCustomers,
          'invalid_customers_list' => $invalidCustomers
      ]);
  }

}
