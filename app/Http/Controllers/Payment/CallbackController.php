<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Facades\Response;
use App\Models\Pembayaran;
use App\Models\Kas;
use App\Models\Customer;
use App\Services\MikrotikServices;
use App\Services\ChatServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CallbackController extends Controller
{
    protected $privateKey;

    public function __construct()
    {
        $this->privateKey = config('tripay.private_key');
    }

    public function handle(Request $request)
    {
        Log::info('Callback Tripay diterima', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            $json = $request->getContent();
            $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
            $event = $request->server('HTTP_X_CALLBACK_EVENT');

            // Validasi input
            if (empty($json) || empty($callbackSignature) || empty($event)) {
                return $this->jsonError('Missing required callback data');
            }

            // Validasi Signature
            $signature = hash_hmac('sha256', $json, $this->privateKey);

            if (!hash_equals($signature, (string) $callbackSignature)) {
                Log::warning('Invalid callback signature', [
                    'expected' => $signature,
                    'received' => $callbackSignature
                ]);
                return $this->jsonError('Invalid signature');
            }

            // Validasi Event
            if ($event !== 'payment_status') {
                return $this->jsonError('Unrecognized callback event, no action was taken');
            }

            // Decode JSON
            $data = json_decode($json);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON from Tripay', [
                    'json_error' => json_last_error_msg(),
                    'raw_data' => $json
                ]);
                return $this->jsonError('Invalid JSON data sent by Tripay');
            }

            // Validasi data yang diperlukan
            if (!isset($data->reference) || !isset($data->status)) {
                return $this->jsonError('Missing required fields in callback data');
            }

            // Ambil invoice berdasarkan reference Tripay
            $invoice = Invoice::where('reference', $data->reference)->first();
            if (!$invoice) {
                Log::warning('Invoice not found', ['reference' => $data->reference]);
                return $this->jsonError('No invoice found: ' . $data->reference);
            }

            // Cek apakah sudah diproses (untuk menghindari duplicate processing)
            if ($invoice->status_id == 8) { // Sudah lunas
                Log::info('Invoice already processed', ['reference' => $data->reference]);
                return Response::json(['success' => true, 'message' => 'Already processed']);
            }

            // Proses berdasarkan status pembayaran
            switch (strtoupper((string) $data->status)) {
                case 'PAID':
                    $this->handlePaid($invoice, $data);
                    break;

                case 'EXPIRED':
                    $invoice->update(['status_id' => 7]);
                    Log::info('Invoice expired', ['reference' => $data->reference]);
                    break;

                case 'FAILED':
                    $invoice->update(['status_id' => 10]);
                    Log::info('Payment failed', ['reference' => $data->reference]);
                    break;

                default:
                    Log::warning('Unrecognized payment status', [
                        'status' => $data->status,
                        'reference' => $data->reference
                    ]);
                    return $this->jsonError('Unrecognized payment status');
            }

            Log::info('Tripay Callback Success', [
                'reference' => $data->reference,
                'status' => $data->status
            ]);
            
            return Response::json([
                'success' => true,
                'reference' => $data->reference
            ]);

        } catch (Exception $e) {
            Log::error('Callback processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->jsonError('Internal server error');
        }
    }

    protected function jsonError(string $message)
    {
        return Response::json([
            'success' => false,
            'message' => $message,
        ], 400);
    }
    
    protected function handlePaid($invoice, $data)
    {
        try {
            DB::beginTransaction();

            $totalBayar = $invoice->tagihan + $invoice->tambahan + $invoice->tunggakan;

            // Metode Bayar
            $metodeBayar = $data->payment_method
                ?? $data->payment_name
                ?? $invoice->metode_bayar
                ?? 'By Tripay';

            // Update status invoice menjadi lunas
            $invoice->update([
                'status_id' => 8,
                'reference' => $data->reference ?? $invoice->reference,
                'metode_bayar' => $metodeBayar
            ]);

            // Ambil data customer dengan eager loading
            $customer = Customer::with('paket')->find($invoice->customer_id);
            if (!$customer) {
                throw new Exception('Customer not found');
            }

            // Cek apakah customer sedang diblokir, lalu buka blokir
            if ($customer->status_id == 9) {
                try {
                    $mikrotik = new MikrotikServices();
                    $client = MikrotikServices::connect($customer->router);
                    $mikrotik->removeActiveConnections($client, $customer->usersecret);
                    $mikrotik->unblokUser($client, $customer->usersecret, $customer->paket->paket_name);

                    // Update status customer menjadi aktif
                    $customer->update(['status_id' => 3]);
                    
                    Log::info('Customer unblocked', ['customer_id' => $customer->id]);
                } catch (Exception $e) {
                    Log::error('Failed to unblock customer', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage()
                    ]);
                    // Jangan throw exception, biarkan proses pembayaran tetap lanjut
                }
            }

            // Simpan data pembayaran (hapus duplikasi invoice_id)
            $pembayaran = Pembayaran::create([
                'invoice_id' => $invoice->id,
                'jumlah_bayar' => $totalBayar,
                'tanggal_bayar' => now(),
                'metode_bayar' => $metodeBayar,
                'keterangan' => 'Pembayaran Paket Langganan Via ' . $metodeBayar . ' dari: ' . $invoice->customer->nama_customer,
                'status_id' => 8,
            ]);

            // Simpan ke kas
            Kas::create([
                'tanggal_kas' => now(),
                'debit' => $totalBayar,
                'kas_id' => 1,
                'status_id' => 3,
                'keterangan' => 'Pembayaran langganan dari ' . $customer->nama_customer . ' via ' . $metodeBayar,
            ]);

            // Generate invoice bulan depan
            $this->generateNextMonthInvoice($invoice, $customer);

            DB::commit();

            // Kirim notifikasi WhatsApp (di luar transaction untuk menghindari rollback jika gagal)
            try {
                $pembayaran->load('invoice.customer');
                $chat = new ChatServices();
                $chat->pembayaranBerhasil($customer->no_hp, $pembayaran);
            } catch (Exception $e) {
                Log::error('Failed to send WhatsApp notification', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Payment processed successfully', [
                'invoice_id' => $invoice->id,
                'customer_id' => $customer->id,
                'amount' => $totalBayar
            ]);

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Payment processing failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function generateNextMonthInvoice($invoice, $customer)
    {
        // Hitung bulan depan
        $jatuhTempo = $invoice->jatuh_tempo;
        if (!$jatuhTempo) {
            Log::warning('No due date found in invoice', ['invoice_id' => $invoice->id]);
            return;
        }

        $bulanDepan = \Carbon\Carbon::parse($jatuhTempo)->addMonthsNoOverflow(1);
        
        // Cek apakah invoice bulan depan sudah ada
        $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
            ->whereMonth('jatuh_tempo', $bulanDepan->month)
            ->whereYear('jatuh_tempo', $bulanDepan->year)
            ->exists();

        if (!$sudahAda) {
            $nextInvoice = Invoice::create([
                'customer_id' => $invoice->customer_id,
                'tagihan' => $customer->paket->harga,
                'paket_id' => $customer->paket_id,
                'tambahan' => 0,
                'status_id' => 7, // Belum bayar
                'jatuh_tempo' => $bulanDepan->copy()->endOfMonth()->setTime(23, 59, 59),
                'tanggal_blokir' => $invoice->tanggal_blokir,
                'metode_bayar' => $invoice->metode_bayar,
            ]);

            Log::info('Next month invoice created', [
                'invoice_id' => $nextInvoice->id,
                'customer_id' => $customer->id,
                'due_date' => $bulanDepan->copy()->endOfMonth()
            ]);
        }
    }
}