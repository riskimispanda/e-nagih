<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Pembayaran;

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
      $formattedInvoices = $invoices->map(function($invoice) {
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
          ->whereHas('invoice', function($query) use ($id) {
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
      $formattedPayments = $payments->map(function($payment) {
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

}
