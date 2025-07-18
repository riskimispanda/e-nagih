<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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

        $tagihan  = $invoice->tagihan;
        $tambahan = $invoice->tambahan ?? 0;
        $saldo    = $invoice->saldo ?? 0;

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


}
