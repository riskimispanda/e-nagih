<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Pembayaran;
use Carbon\Carbon;
use App\Models\Pengeluaran;
use App\Models\Pendapatan;
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
              'pendapatan' => $pembayaran->sum('jumlah_bayar') + $pendapatan->sum('jumlah_pendapatan'),
              'pengeluaran' => $pengeluaran->sum('jumlah_pengeluaran')
          ]
      ]);
    }

}
