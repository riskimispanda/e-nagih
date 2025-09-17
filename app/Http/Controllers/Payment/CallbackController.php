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

            // Decode JSON ke array
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON from Tripay', [
                    'json_error' => json_last_error_msg(),
                    'raw_data' => $json
                ]);
                return $this->jsonError('Invalid JSON data sent by Tripay');
            }

            // Validasi field penting
            $payload = $data;

            if (!isset($payload['reference']) || !isset($payload['status'])) {
                Log::info('Missing required fields in callback data', ['payload' => $payload]);
                return $this->jsonError('Missing required fields in callback data');
            }

            $reference   = $payload['reference'];     // ID unik dari Tripay
            $merchantRef = $payload['merchant_ref'];  // ID invoice lokal
            $status      = strtoupper((string) $payload['status']);


            // Ambil invoice berdasarkan merchant_ref (pastikan kolom sesuai DB: merchant_ref / kode_invoice)
            $invoice = Invoice::where('merchant_ref', $merchantRef)->first();
            if (!$invoice) {
                Log::warning('Invoice not found', [
                    'merchant_ref' => $merchantRef,
                    'reference' => $reference,
                    'Nama Customer' => $payload['customer_name'] ?? null,
                ]);
                return $this->jsonError('No invoice found: ' . $merchantRef);
            }

            // Cek apakah sudah diproses
            if ($invoice->status_id == 8) {
                Log::info('Invoice already processed', ['reference' => $reference]);
                return Response::json(['success' => true, 'message' => 'Already processed']);
            }

            // Proses status pembayaran
            switch ($status) {
                case 'PAID':
                    $this->handlePaid($invoice, (object) $data);
                    Log::info('Invoice marked as paid', ['reference' => $reference, 'Nama Customer' => $invoice->customer->nama_customer]);
                    break;

                case 'EXPIRED':
                    $invoice->update(['status_id' => 7]);
                    Log::info('Invoice expired', ['reference' => $reference]);
                    break;

                case 'FAILED':
                    $invoice->update(['status_id' => 10]);
                    Log::info('Payment failed', ['reference' => $reference]);
                    break;

                default:
                    Log::warning('Unrecognized payment status', [
                        'status' => $status,
                        'reference' => $reference
                    ]);
                    return $this->jsonError('Unrecognized payment status');
            }

            Log::info('Tripay Callback Success', [
                'reference' => $reference,
                'status' => $status
            ]);

            return Response::json([
                'success' => true,
                'reference' => $reference
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
                'status_id'   => 8,
                'reference'   => $data->reference ?? $invoice->reference,
                'metode_bayar' => $metodeBayar
            ]);

            // Ambil data customer dengan eager loading
            $customer = Customer::with('paket')->find($invoice->customer_id);
            if (!$customer) {
                throw new Exception('Customer not found');
            }

            // Jika customer diblokir, buka blokir
            if ($customer->status_id == 9) {
                try {
                    $mikrotik = new MikrotikServices();
                    $client = MikrotikServices::connect($customer->router);
                    $mikrotik->removeActiveConnections($client, $customer->usersecret);
                    $mikrotik->unblokUser($client, $customer->usersecret, $customer->paket->paket_name);

                    $customer->update(['status_id' => 3]);

                    Log::info('Customer unblocked', ['customer_id' => $customer->id]);
                } catch (Exception $e) {
                    Log::error('Failed to unblock customer', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Simpan pembayaran
            $pembayaran = Pembayaran::create([
                'invoice_id' => $invoice->id,
                'jumlah_bayar' => $totalBayar,
                'tanggal_bayar' => now(),
                'metode_bayar' => $metodeBayar,
                'keterangan' => 'Pembayaran Paket Langganan Via ' . $metodeBayar . ' dari: ' . $invoice->customer->nama_customer,
                'status_id' => 8,
                'saldo' => $invoice->saldo
            ]);

            // Simpan ke kas
            Kas::create([
                'tanggal_kas' => now(),
                'debit' => $totalBayar,
                'kas_id' => 1,
                'status_id' => 3,
                'keterangan' => 'Pembayaran langganan dari ' . $invoice->customer->nama_customer . ' via ' . $metodeBayar,
                'pengeluaran_id' => null,
            ]);

            // Buat invoice bulan depan
            $this->generateNextMonthInvoice($invoice, $customer);

            activity('payment')
                ->performedOn($invoice)
                ->log('Pembayaran berhasil diproses untuk ' . $customer->nama_customer . ' sebesar Rp ' . number_format($totalBayar, 0, ',', '.') . ' via ' . $metodeBayar);

            DB::commit();

            // Kirim notifikasi WhatsApp
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
        $jatuhTempo = $invoice->jatuh_tempo;
        if (!$jatuhTempo) {
            Log::warning('No due date found in invoice', ['invoice_id' => $invoice->id]);
            return;
        }

        $bulanDepan = \Carbon\Carbon::parse($jatuhTempo)->addMonthsNoOverflow(1);

        $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
            ->whereMonth('jatuh_tempo', $bulanDepan->month)
            ->whereYear('jatuh_tempo', $bulanDepan->year)
            ->exists();

        // Generate Merchant Reference
        $merchant = 'INV-' . $invoice->customer_id . '-' . time();

        if (!$sudahAda) {
            $nextInvoice = Invoice::create([
                'customer_id' => $invoice->customer_id,
                'tagihan' => $customer->paket->harga,
                'paket_id' => $customer->paket_id,
                'tambahan' => 0,
                'merchant_ref' => $merchant,
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