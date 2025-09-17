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

        $merchantRef = $invoice->merchant_ref ?: 'INV-' . $invoice->customer_id . '-' . time();

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
            'merchant_ref'   => $invoice->merchant_ref ?? $merchantRef,
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
                $finalMerchantRef = $invoice->fresh()->merchant_ref;
                \Log::info('Invoice updated with Tripay reference', [
                    'invoice_id'    => $invoice->id,
                    'reference'     => $invoice->reference,
                    'merchant_ref'  => $finalMerchantRef,
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
        DB::beginTransaction();

        try {
            $reference   = $data['reference'] ?? null;
            $merchantRef = $data['merchant_ref'] ?? null;
            $status      = strtoupper($data['status'] ?? '');
            $metodeBayar = $data['payment_method'] ?? $data['payment_name'] ?? 'Tripay';

            // Ambil invoice berdasarkan merchant_ref
            $invoice = Invoice::where('merchant_ref', $merchantRef)
                ->orWhere('reference', $reference)
                ->first();

            if (!$invoice) {
                Log::warning('Invoice not found', $data);
                return ['success' => false, 'message' => 'Invoice tidak ditemukan'];
            }

            // Jika sudah lunas, skip
            if ($invoice->status_id == 8) {
                return ['success' => true, 'message' => 'Invoice sudah dibayar'];
            }

            if ($status === 'PAID') {
                // Update invoice
                $invoice->update([
                    'status_id' => 8,
                    'reference' => $reference ?? $invoice->reference,
                    'metode_bayar' => $metodeBayar,
                ]);

                // Ambil customer
                $customer = Customer::with('paket')->find($invoice->customer_id);
                if (!$customer) throw new Exception('Customer tidak ditemukan');

                // Buka blokir jika diblokir
                if ($customer->status_id == 9) {
                    try {
                        $mikrotik = new MikrotikServices();
                        $client = MikrotikServices::connect($customer->router);
                        $mikrotik->removeActiveConnections($client, $customer->usersecret);
                        $mikrotik->unblokUser($client, $customer->usersecret, $customer->paket->paket_name);

                        $customer->update(['status_id' => 3]);
                    } catch (Exception $e) {
                        Log::error('Gagal buka blokir customer', ['error' => $e->getMessage()]);
                    }
                }

                $totalBayar = $invoice->tagihan + $invoice->tambahan + $invoice->tunggakan;

                // Simpan pembayaran
                $pembayaran = Pembayaran::create([
                    'invoice_id' => $invoice->id,
                    'jumlah_bayar' => $totalBayar,
                    'tanggal_bayar' => now(),
                    'metode_bayar' => $metodeBayar,
                    'keterangan' => 'Pembayaran via ' . $metodeBayar . ' untuk ' . $customer->nama_customer,
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

                // Buat invoice bulan depan
                self::generateNextMonthInvoice($invoice, $customer);

                DB::commit();

                // Kirim WA notifikasi
                try {
                    $pembayaran->load('invoice.customer');
                    $chat = new ChatServices();
                    $chat->pembayaranBerhasil($customer->no_hp, $pembayaran);
                } catch (Exception $e) {
                    Log::error('Gagal kirim WA notifikasi', ['error' => $e->getMessage()]);
                }

                return ['success' => true, 'message' => 'Invoice berhasil dibayar'];
            }

            return ['success' => false, 'message' => 'Status bukan PAID'];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal proses payment', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected static function generateNextMonthInvoice($invoice, $customer)
    {
        $jatuhTempo = $invoice->jatuh_tempo;
        if (!$jatuhTempo) return;

        $bulanDepan = \Carbon\Carbon::parse($jatuhTempo)->addMonthsNoOverflow(1);

        $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
            ->whereMonth('jatuh_tempo', $bulanDepan->month)
            ->whereYear('jatuh_tempo', $bulanDepan->year)
            ->exists();

        if (!$sudahAda) {
            Invoice::create([
                'customer_id' => $invoice->customer_id,
                'paket_id' => $customer->paket_id,
                'tagihan' => $customer->paket->harga,
                'tambahan' => 0,
                'status_id' => 7,
                'jatuh_tempo' => $bulanDepan->copy()->endOfMonth()->setTime(23, 59, 59),
                'tanggal_blokir' => $bulanDepan->copy()->endOfMonth()->addDays(3),
                'metode_bayar' => $invoice->metode_bayar,
            ]);
        }
    }
}
