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
    public function getPaymentInstructions($code)
    {
        $apiKey = config('tripay.api_key');

        // Determine if we're in production or sandbox mode
        $baseUrl = env('APP_ENV') === 'production'
            ? 'https://tripay.co.id/api/'
            : 'https://tripay.co.id/api-sandbox/';

        $payload = ['code' => $code];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $baseUrl . 'payment/instruction?' . http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        // Log error if any
        if ($error) {
            \Log::error('Error fetching payment instructions from Tripay: ' . $error);
            return ['success' => false, 'message' => $error];
        }

        return json_decode($response, true);
    }

    /**
     * Get transaction details by reference
     *
     * @param string $reference Merchant reference
     * @return array Transaction details
     */
    public function getTransactionDetails($reference)
    {
        $apiKey = config('tripay.api_key');

        // Determine if we're in production or sandbox mode
        $baseUrl = env('APP_ENV') === 'production'
            ? 'https://tripay.co.id/api/'
            : 'https://tripay.co.id/api-sandbox/';

        $payload = ['reference' => $reference];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $baseUrl . 'transaction/detail?' . http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        // Log error if any
        if ($error) {
            \Log::error('Error fetching transaction details from Tripay: ' . $error);
            return ['success' => false, 'message' => $error];
        }

        return json_decode($response, true);
    }

    public function getPaymentChannels()
    {
        $apiKey = config('tripay.api_key');

        // Determine if we're in production or sandbox mode
        $baseUrl = env('APP_ENV') === 'production'
            ? 'https://tripay.co.id/api/'
            : 'https://tripay.co.id/api-sandbox/';

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $baseUrl . 'merchant/payment-channel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        // Log error if any
        if ($error) {
            \Log::error('Error fetching payment channels from Tripay: ' . $error);
        }

        // Decode the JSON response
        $decodedResponse = json_decode($response, true);

        // Check if the response was successful and contains data
        if (isset($decodedResponse['success']) && $decodedResponse['success'] && isset($decodedResponse['data'])) {
            return $decodedResponse['data'];
        }

        // Log error if response is not as expected
        if ($response) {
            \Log::warning('Unexpected response from Tripay payment channels: ' . $response);
        }

        return [];
    }

    public function createTransaction($invoice, $method)
    {
        $apiKey = config('tripay.api_key');
        $privateKey = config('tripay.private_key');
        $merchantCode = config('tripay.merchant_code');
        // Determine if we're in production or sandbox mode
        $baseUrl = env('APP_ENV') === 'production'
            ? 'https://tripay.co.id/api/'
            : 'https://tripay.co.id/api-sandbox/';

        // Include the invoice ID in the merchant reference for easier tracking
        $merchantRef = 'INV-' . $invoice->id . '-' . time();

        // Calculate signature

        // Prepare customer data
        $customer = [
            'name' => $invoice->customer->nama_customer,
            'email' => $invoice->customer->email ?? 'customer@example.com',
            'phone' => $invoice->customer->no_hp ?? $invoice->customer->no_telp ?? '08123456789',
        ];

        $tagihan = $invoice->tagihan;
        $tambahan = $invoice->tambahan ?? 0;
        $totalTagihan = $tagihan + $tambahan;
        // Prepare item details
        $items = [
            [
                'name' => 'Tagihan Internet - ' . date('F Y'),
                'price' => $invoice->tagihan,
                'quantity' => 1,
                'subtotal' => $invoice->tagihan,
            ]
        ];
        if($tambahan > 0)
        {
            $items[] = [
                'name' => 'Tambahan Panjang Kabel',
                'price' => $invoice->tambahan,
                'quantity' => 1,
                'subtotal' => $invoice->tambahan,
            ];
        }
        $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $totalTagihan, $privateKey);
        
        // Prepare payload
        $payload = [
            'method' => $method,
            'merchant_ref' => $merchantRef,
            'amount' => $totalTagihan,
            'customer_name' => $customer['name'],
            'customer_email' => $customer['email'],
            'customer_phone' => $customer['phone'],
            'order_items' => $items,
            'callback_url' => url('/payment/callback'),
            'return_url' => url('/data/invoice/' . $invoice->customer->nama_customer),
            'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
            'signature' => $signature
        ];

        // Log the payload for debugging
        \Log::info('Tripay transaction payload', ['payload' => $payload]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $baseUrl . 'transaction/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        // Log error if any
        if ($error) {
            \Log::error('Error creating Tripay transaction: ' . $error);
        }

        $decodedResponse = json_decode($response, true);

        // Log the response for debugging
        \Log::info('Tripay transaction response', ['response' => $decodedResponse]);

        // If transaction was created successfully, store the reference in the invoice
        if (isset($decodedResponse['success']) && $decodedResponse['success'] && isset($decodedResponse['data'])) {
            try {
                // Update the invoice with the reference and merchant_ref
                $invoice->reference = $decodedResponse['data']['reference'] ?? null;
                $invoice->merchant_ref = $merchantRef;
                $invoice->metode_bayar = $method;
                $invoice->save();

                \Log::info('Updated invoice with Tripay reference', [
                    'invoice_id' => $invoice->id,
                    'reference' => $invoice->reference,
                    'merchant_ref' => $invoice->merchant_ref,
                    'metode_bayar' => $invoice->metode_bayar
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to update invoice with Tripay reference', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        // dd($decodedResponse);
        return $decodedResponse;
    }
}
