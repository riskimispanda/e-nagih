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
      $totalAktif = Customer::whereIn('status_id',[3,4])->whereNull('deleted_at')->count();

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

// Total customer yang sudah bayar bulan ini (ada invoice status_id = 8 bulan ini)
      $totalInvoicePaid = Invoice::where('status_id', 8)
          ->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year)
          ->whereHas('customer', function ($query) {
              $query->whereIn('status_id', [3, 4, 9])
                    ->whereNull('deleted_at');
          })
          ->distinct('customer_id')
          ->count('customer_id');

      // Total customer yang sudah bayar bulan ini (dihitung dari customer)
      $customersPaidThisMonth = Customer::whereIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at')
          ->whereHas('invoice', function ($query) {
              $query->where('status_id', 8)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
          })
          ->count();

      // Total customer yang belum bayar bulan ini (dihitung dari customer)
      $customersUnpaidThisMonth = Customer::whereIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at')
          ->whereDoesntHave('invoice', function ($query) {
              $query->where('status_id', 8)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
          })
          ->count();

      // Total customer yang belum bayar bulan ini
      // = customer aktif - customer yang sudah bayar bulan ini
      $totalInvoiceUnpaid = $totalCustomer - $totalInvoicePaid;

      // Total transaksi yang konsisten dengan totalCustomer
      // Customer yang sudah bayar bulan ini (sama seperti totalInvoicePaid)
      $totalTransactions = Customer::whereIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at')
          ->whereHas('invoice', function ($query) {
              $query->where('status_id', 8)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
          })
          ->count();

      return response()->json([
          'success' => true,
          'totalCustomer' => $totalCustomer,
          'totalAktif' => $totalAktif,
          'totalNonAktif' => $totalNonAktif,
          'customersWithInvoice' => $customersWithInvoice,
          'customersWithoutInvoice' => $customersWithoutInvoice,
          'totalInvoicePaid' => $totalInvoicePaid,
          'totalInvoiceUnpaid' => $totalInvoiceUnpaid,
          'totalTransaksi' => $totalTransactions,
          'customer_based_calculation' => [
              'customers_paid_this_month' => $customersPaidThisMonth,
              'customers_unpaid_this_month' => $customersUnpaidThisMonth,
              'sum_customer_based' => $customersPaidThisMonth + $customersUnpaidThisMonth,
              'is_customer_consistent' => $totalCustomer === ($customersPaidThisMonth + $customersUnpaidThisMonth)
          ],
          'comparison' => [
              'invoice_based_paid' => $totalInvoicePaid,
              'customer_based_paid' => $customersPaidThisMonth,
              'paid_difference' => $totalInvoicePaid - $customersPaidThisMonth,
              'invoice_based_unpaid' => $totalInvoiceUnpaid,
              'customer_based_unpaid' => $customersUnpaidThisMonth,
              'unpaid_difference' => $totalInvoiceUnpaid - $customersUnpaidThisMonth
          ],
          'debug_info' => [
              'total_customer' => $totalCustomer,
              'customers_with_invoice' => $customersWithInvoice,
              'customers_without_invoice' => $customersWithoutInvoice,
              'paid_this_month' => $totalInvoicePaid,
              'unpaid_calculation' => $totalCustomer - $totalInvoicePaid,
              'month' => Carbon::now()->month,
              'year' => Carbon::now()->year
          ],
          'consistency_check' => [
              'total_customers' => $totalCustomer,
              'sum_with_without_invoice' => $customersWithInvoice + $customersWithoutInvoice,
              'is_consistent' => $totalCustomer === ($customersWithInvoice + $customersWithoutInvoice)
          ]
      ]);
  }

}
