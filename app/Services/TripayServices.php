<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Pembayaran;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Kas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class TripayServices
{
    /**
     * Get payment instructions for a specific payment method
     *
     * @param string $code Payment method code (e.g., 'BRIVA', 'MANDIRIVA')
     * @return array Payment instructions
     */
    public function getPaymentInstructions(string $code): array
    {
        $apiKey  = config('tripay.api_key');
        $baseUrl = rtrim(config('tripay.base_url'), '/');
        $url     = $baseUrl . '/payment/instruction?code=' . urlencode($code);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        curl_close($curl);

        if ($error) {
            \Log::error("Tripay Payment Instruction Error: $error");
            return [
                'success' => false,
                'message' => $error,
                'data'    => null
            ];
        }

        $result = json_decode($response, true);

        if (!($result['success'] ?? false)) {
            \Log::warning('Tripay Instruction Response Error: ' . $response);
        }

        return $result;
    }


    /**
     * Get transaction details by reference
     *
     * @param string $reference Merchant reference
     * @return array Transaction details
     */
    public function getTransactionDetails(string $reference): array
    {
        $apiKey  = config('tripay.api_key');
        $baseUrl = rtrim(config('tripay.base_url'), '/');
        $url     = $baseUrl . '/transaction/detail?reference=' . urlencode($reference);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        curl_close($curl);

        if ($error) {
            \Log::error("Tripay Transaction Detail Error: $error");
            return [
                'success' => false,
                'message' => $error,
                'data'    => null
            ];
        }

        $result = json_decode($response, true);

        if (!($result['success'] ?? false)) {
            \Log::warning("Tripay Transaction Detail Failed: " . $response);
        }

        return $result;
    }


    public function getPaymentChannels(): array
    {
        $apiKey  = config('tripay.api_key');
        $baseUrl = config('tripay.base_url'); // ambil dari config/tripay.php

        $url = rtrim($baseUrl, '/') . '/merchant/payment-channel';

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        curl_close($curl);

        if ($error) {
            \Log::error('Tripay Payment Channel Error: ' . $error);
            return [];
        }

        $result = json_decode($response, true);

        if ($result['success'] ?? false) {
            return $result['data'] ?? [];
        }

        \Log::warning('Tripay Payment Channel Unexpected Response: ' . $response);
        return [];
    }


    public function createTransaction($invoice, $method): array
    {
        $apiKey       = config('tripay.api_key');
        $privateKey   = config('tripay.private_key');
        $merchantCode = config('tripay.merchant_code');
        $baseUrl      = rtrim(config('tripay.base_url'), '/');

        $merchantRef = 'INV-' . $invoice->id . '-' . time();

        $customer = [
            'name'  => $invoice->customer->nama_customer,
            'email' => $invoice->customer->email ?? 'customer@example.com',
            'phone' => $invoice->customer->no_hp ?? $invoice->customer->no_telp ?? '08123456789',
        ];

        // Jika Ada Saldo
        $tagihan  = $invoice->tagihan;
        $tambahan = $invoice->tambahan ?? 0;
        $saldo    = $invoice->saldo ?? 0;
        $tunggakan = $invoice->tunggakan ?? 0;

        $items = [];

        // Item utama
        $items[] = [
            'name'     => 'Tagihan Internet - ' . date('F Y'),
            'price'    => $tagihan,
            'quantity' => 1,
            'subtotal' => $tagihan,
        ];

        // Tambahan jika ada
        if ($tambahan > 0) {
            $items[] = [
                'name'     => 'Tambahan Panjang Kabel',
                'price'    => $tambahan,
                'quantity' => 1,
                'subtotal' => $tambahan,
            ];
        }

        // Tunggakan jika ada
        if ($tunggakan > 0) {
            $items[] = [
                'name'     => 'Tunggakan Bulan Sebelumnya',
                'price'    => $tunggakan,
                'quantity' => 1,
                'subtotal' => $tunggakan,
            ];
        }

        // Hitung total dari item, lalu dikurangi saldo
        $itemsTotal = array_sum(array_column($items, 'subtotal'));
        $totalAmount = max($itemsTotal - $saldo, 0);

        $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $totalAmount, $privateKey);

        $payload = [
            'method'         => $method,
            'merchant_ref'   => $merchantRef,
            'amount'         => $totalAmount,
            'customer_name'  => $customer['name'],
            'customer_email' => $customer['email'],
            'customer_phone' => $customer['phone'],
            'order_items'    => $items,
            'callback_url'   => url('/payment/callback'),
            'return_url'     => url('/payment/invoice/' . $invoice->id),
            'expired_time'   => time() + (24 * 60 * 60),
            'signature'      => $signature,
        ];

        \Log::info('Tripay transaction payload', ['payload' => $payload]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $baseUrl . '/transaction/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        curl_close($curl);

        if ($error) {
            \Log::error('Error creating Tripay transaction: ' . $error);
            return ['success' => false, 'message' => $error];
        }

        $decoded = json_decode($response, true);
        \Log::info('Tripay transaction response', ['response' => $decoded]);

        if ($decoded['success'] ?? false) {
            try {
                $invoice->update([
                    'reference'     => $decoded['data']['reference'] ?? null,
                    'merchant_ref'  => $merchantRef,
                    'metode_bayar'  => $method,
                ]);

                \Log::info('Invoice updated with Tripay reference', [
                    'invoice_id'    => $invoice->id,
                    'reference'     => $invoice->reference,
                    'merchant_ref'  => $merchantRef,
                    'metode_bayar'  => $method,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to update invoice', [
                    'invoice_id' => $invoice->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        return $decoded;
    }


    public static function replayPayment(array $data)
    {
        $reference = $data['reference'] ?? null;
        $merchantRef = $data['merchant_ref'] ?? null;
        $status = $data['status'] ?? null;
        $paymentMethod = $data['payment_method'] ?? 'Tripay';
        $paymentName = $data['payment_name'] ?? $paymentMethod;

        $invoice = Invoice::where('reference', $reference)
            ->orWhere('merchant_ref', $merchantRef)
            ->first();

        if (!$invoice) {
            Log::warning('Invoice not found', ['reference' => $reference, 'merchant_ref' => $merchantRef]);
            return false;
        }

        if ($status !== 'PAID') {
            Log::info('Payment status not PAID', ['reference' => $reference, 'status' => $status]);
            return false;
        }

        if ($invoice->status_id == 8) {
            Log::info('Invoice already paid', ['invoice_id' => $invoice->id]);
            return true;
        }

        DB::transaction(function () use ($invoice, $paymentName) {
            // Update invoice jadi lunas
            $invoice->update([
                'status_id' => 8,
                'metode_bayar' => $paymentName,
            ]);

            // Simpan pembayaran
            $pembayaran = Pembayaran::create([
                'invoice_id' => $invoice->id,
                'jumlah_bayar' => $invoice->tagihan,
                'tanggal_bayar' => now(),
                'metode_bayar' => $paymentName,
                'status_id' => 8,
            ]);

            // Catat kas
            Kas::create([
                'tanggal_kas' => now(),
                'debit' => $invoice->tagihan,
                'kas_id' => 1,
                'pembayaran_id' => $pembayaran->id,
                'status_id' => 3,
                'keterangan' => 'Pembayaran invoice #' . $invoice->id,
            ]);

            // Buat invoice bulan depan
            $jatuhTempo = Carbon::parse($invoice->jatuh_tempo)->addMonthsNoOverflow()->endOfMonth();
            $existsNext = Invoice::where('customer_id', $invoice->customer_id)
                ->whereMonth('jatuh_tempo', $jatuhTempo->month)
                ->whereYear('jatuh_tempo', $jatuhTempo->year)
                ->exists();

            if (!$existsNext) {
                Invoice::create([
                    'customer_id' => $invoice->customer_id,
                    'paket_id' => $invoice->paket_id,
                    'tagihan' => $invoice->paket->harga,
                    'status_id' => 7,
                    'jatuh_tempo' => $jatuhTempo,
                    'tanggal_blokir' => $jatuhTempo->copy()->addDays(3),
                ]);
            }
        });

        Log::info('Payment processed via TripayService', ['invoice_id' => $invoice->id]);
        return true;
    }
}
