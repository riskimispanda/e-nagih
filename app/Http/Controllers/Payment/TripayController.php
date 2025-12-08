<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\TripayServices;
use App\Models\Invoice;
use App\Models\Pembayaran;
use App\Models\Kas;
use App\Services\ChatServices;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Services\MikrotikServices;

class TripayController extends Controller
{
    /**
     * Get payment instructions for a specific payment method
     *
     * @param string $code Payment method code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentInstructions($code)
    {
        $tripay = new TripayServices();
        $instructions = $tripay->getPaymentInstructions($code);

        return response()->json($instructions);
    }

    public function getPaymentChannels()
    {
        $tripay = new TripayServices();
        $channels = $tripay->getPaymentChannels();

        return response()->json($channels);
    }

    public function showPaymentPage($id)
    {
        // Get the invoice
        $invoice = Invoice::findOrFail($id);

        // Get payment channels
        $tripay = new TripayServices();
        $channels = $tripay->getPaymentChannels();

        // Filter channels to only include the ones we want to display
        $allowedChannels = [
            'BRIVA',
            'BCAVA',
            'ALFAMART',
            'INDOMARET',
            'ALFAMIDI',
            'OVO',
            'QRIS',
            'QRIS2',
            'DANA',
            'SHOPEEPAY'
        ];

        $filteredChannels = array_filter($channels, function($channel) use ($allowedChannels) {
            return in_array($channel['code'], $allowedChannels);
        });

        return view('/pelanggan/payment/invoice', [
            'invoice' => $invoice,
            'channels' => $filteredChannels
        ]);
    }

    /**
     * Show payment detail page
     *
     * @param string $reference Transaction reference
     * @return \Illuminate\View\View
     */
    public function showPaymentDetail($reference)
    {
        try {
            // Find the invoice by reference
            $invoice = Invoice::with(['customer', 'paket', 'status'])
                ->where('reference', $reference)
                ->firstOrFail();

            // Try to get transaction details from Tripay API
            $transaction = null;
            try {
                $tripay = new TripayServices();
                $transactionDetails = $tripay->getTransactionDetails($reference);

                if (isset($transactionDetails['success']) && $transactionDetails['success']) {
                    $transaction = $transactionDetails['data'];
                }
            } catch (\Exception $e) {
                Log::error('Error getting transaction details for payment detail page', [
                    'reference' => $reference,
                    'error' => $e->getMessage()
                ]);
            }

            // If no transaction from API, try to get from session
            if (!$transaction && session('last_transaction_data')) {
                $sessionData = session('last_transaction_data');
                if ($sessionData['reference'] === $reference) {
                    $transaction = $sessionData;
                }
            }

            return view('pelanggan.payment.detial-payment', [
                'invoice' => $invoice,
                'transaction' => $transaction,
                'users' => auth()->user(),
                'roles' => auth()->user()->roles,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in showPaymentDetail', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('payment.show', 1)
                ->with('error', 'Transaksi tidak ditemukan atau telah kedaluwarsa.');
        }
    }

    /**
     * Process payment for an invoice - Create transaction and redirect to payment detail
     *
     * @param Request $request
     * @param int $id Invoice ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPayment(Request $request, $id)
    {
        // dd('Masuk Tripay',$id , $request->all());
        // Validate the request
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            // Get the invoice
            $invoice = Invoice::findOrFail($id);

            // Check if invoice is already paid
            if ($invoice->status_id == 8) { // Assuming 8 is the paid status
                return redirect()->back()->with('info', 'Invoice ini sudah dibayar.');
            }

            // Create a new TripayServices instance
            $tripay = new TripayServices();

            // Process the payment
            $paymentMethod = $request->payment_method;
            // Log the payment attempt
            Log::info('Payment attempt', [
                'invoice_id' => $id,
                'payment_method' => $paymentMethod,
                'amount' => $invoice->tagihan + $invoice->tambahan - $invoice->saldo
            ]);

            // Create transaction in Tripay
            $transaction = $tripay->createTransaction($invoice, $paymentMethod);

            // Check if transaction was created successfully
            if (isset($transaction['success']) && $transaction['success'] && isset($transaction['data'])) {
                // Store the transaction reference and data in session for later use
                session([
                    'last_transaction_reference' => $transaction['data']['reference'],
                    'last_transaction_data' => $transaction['data']
                ]);

                // Update invoice with reference and merchant_ref
                $invoice->reference = $transaction['data']['reference'];
                $invoice->merchant_ref = $transaction['data']['merchant_ref'];
                $invoice->save();

                // Redirect directly to Tripay checkout URL for immediate payment processing
                if (isset($transaction['data']['checkout_url'])) {
                    return redirect($transaction['data']['checkout_url']);
                }

                // Fallback: if no checkout_url, redirect to payment detail page
                return redirect()->route('payment.detail', $transaction['data']['reference'])
                    ->with('success', 'Transaksi berhasil dibuat. Silakan lanjutkan pembayaran.');
            }

            // Log the error
            Log::error('Payment processing error', [
                'invoice_id' => $id,
                'payment_method' => $paymentMethod,
                'response' => $transaction
            ]);

            // If there was an error, redirect back with error message
            $errorMessage = isset($transaction['message'])
                ? 'Error: ' . $transaction['message']
                : 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.';

            return redirect()->back()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Exception in payment processing', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment callback from Tripay
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentCallback(Request $request)
    {
        try {
            Log::info('Payment callback received', [
                'headers' => $request->headers->all(),
                'body'    => $request->getContent(),
                'all'     => $request->all(),
            ]);

            $isSandbox         = env('APP_ENV') !== 'production';
            $callbackSignature = $request->header('X-Callback-Signature');
            $privateKey        = config('tripay.private_key');

            $data        = null;
            $invoiceId   = null;
            $reference   = null;
            $merchantRef = null;
            $paymentStatus = null;

            // === TEST MODE ===
            if ($request->has('test_mode')) {
                $data        = (object) $request->all();
                $invoiceId   = $request->input('invoice_id');
                $reference   = $request->input('reference');
                $merchantRef = $request->input('merchant_ref');
                $paymentStatus = 'PAID';
            } else {
                $json = $request->getContent();

                if (empty($json)) {
                    $data        = (object) $request->all();
                    $reference   = $request->input('reference');
                    $merchantRef = $request->input('merchant_ref');
                    $paymentStatus = $request->input('status');
                } else {
                    // ✅ Validasi Signature (hanya di production)
                    if ($callbackSignature && !$isSandbox) {
                        $signature = hash_hmac('sha256', $json, $privateKey);
                        if ($signature !== $callbackSignature) {
                            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
                        }
                    }

                    $data = json_decode($json);

                    // ✅ Ambil data dari payload Tripay
                    if (isset($data->payload)) {
                        $reference     = $data->payload->reference ?? null;
                        $merchantRef   = $data->payload->merchant_ref ?? null;
                        $paymentStatus = $data->payload->status ?? null;
                    } else {
                        $reference     = $data->reference ?? null;
                        $merchantRef   = $data->merchant_ref ?? null;
                        $paymentStatus = $data->status ?? null;
                    }
                }
            }

            // === Cari Invoice ===
            $invoice = null;
            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
            }
            if (!$invoice && $merchantRef) {
                $invoice = Invoice::where('merchant_ref', $merchantRef)->first();
            }
            if (!$invoice && $reference) {
                $invoice = Invoice::where('reference', $reference)->first();
            }
            if (!$invoice && $merchantRef) {
                $parts = explode('-', $merchantRef);
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $invoice = Invoice::find($parts[1]);
                    if ($invoice) {
                        if (!$invoice->reference && $reference) {
                            $invoice->reference = $reference;
                        }
                        if (!$invoice->merchant_ref && $merchantRef) {
                            $invoice->merchant_ref = $merchantRef;
                        }
                        $invoice->save();
                    }
                }
            }

            if (!$invoice) {
                Log::warning('Invoice not found in callback', [
                    'reference'   => $reference,
                    'merchantRef' => $merchantRef,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            // === Proses status pembayaran ===
            if (strtoupper($paymentStatus) === 'PAID') {
                if ($invoice->status_id == 8) {
                    return response()->json(['success' => true, 'message' => 'Invoice already paid']);
                }

                $metodePembayaran = $data->payment_method ?? 'Tripay';
                $namaMetode       = $data->payment_name ?? $metodePembayaran;

                // Tandai lunas
                $invoice->status_id    = 8;
                $invoice->metode_bayar = $namaMetode;
                $invoice->reference    = $reference ?? $invoice->reference;

                // ✅ update merchant_ref kalau belum ada
                if (!$invoice->merchant_ref && $merchantRef) {
                    $invoice->merchant_ref = $merchantRef;
                }

                $invoice->save();

                Log::info('Invoice marked as paid via Tripay callback', [
                    'invoice_id'     => $invoice->id,
                    'payment_status' => $paymentStatus,
                    'amount'         => $invoice->tagihan
                ]);

                // Simpan ke tabel pembayaran
                $pembayaran = Pembayaran::create([
                    'invoice_id'    => $invoice->id,
                    'user_id'       => $invoice->customer->user_id ?? null,
                    'jumlah_bayar'  => $invoice->tagihan,
                    'tanggal_bayar' => now(),
                    'metode_bayar'  => $namaMetode,
                    'keterangan'    => 'Pembayaran otomatis via ' . $namaMetode,
                    'status_id'     => 8,
                ]);

                Log::info('Payment record created', [
                    'payment_id' => $pembayaran->id,
                    'invoice_id' => $invoice->id,
                    'amount'     => $invoice->tagihan
                ]);

                // Catat kas
                Kas::create([
                    'debit'         => $invoice->tagihan,
                    'keterangan'    => 'Pembayaran invoice #' . $invoice->id . ' oleh ' . ($invoice->customer->nama_customer ?? 'Pelanggan'),
                    'tanggal_kas'   => now(),
                    'kas_id'        => 1,
                    'user_id'       => $invoice->customer->user_id ?? null,
                    'pembayaran_id' => $pembayaran->id,
                    'status_id'     => 3
                ]);

                // === Buat invoice baru bulan depan ===
                $tanggalJatuhTempoLama = \Carbon\Carbon::parse($invoice->jatuh_tempo);
                $tanggalAwal       = $tanggalJatuhTempoLama->copy()->addMonthsNoOverflow()->startOfMonth();
                $tanggalJatuhTempo = $tanggalAwal->copy()->endOfMonth();

                $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
                    ->whereMonth('jatuh_tempo', $tanggalJatuhTempo->month)
                    ->whereYear('jatuh_tempo', $tanggalJatuhTempo->year)
                    ->exists();

                if (!$sudahAda) {
                    Invoice::create([
                        'customer_id'    => $invoice->customer_id,
                        'paket_id'       => $invoice->paket_id,
                        'tagihan'        => $invoice->customer->paket->harga,
                        'tambahan'       => 0,
                        'saldo'          => 0,
                        'status_id'      => 7, // Belum bayar
                        'created_at'     => $tanggalAwal,
                        'updated_at'     => $tanggalAwal,
                        'jatuh_tempo'    => $tanggalJatuhTempo,
                        'tanggal_blokir' => $tanggalJatuhTempo->copy()->addDays(3),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran berhasil dan invoice berikutnya dibuat',
                    'data'    => [
                        'invoice_id'     => $invoice->id,
                        'payment_status' => $paymentStatus,
                        'amount'         => $invoice->tagihan
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Status pembayaran bukan PAID'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in paymentCallback', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error'
            ], 500);
        }
    }



    /**
     * Show the callback tester form
     */
    public function showCallbackTester()
    {
        // Get all invoices for testing
        $invoices = Invoice::where('status_id', '!=', 8)->get();

        return view('payment.callback-tester', [
            'invoices' => $invoices,
            'callbackUrl' => route('payment.callback'),
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
        ]);
    }

    /**
     * Process the callback test
     */
    public function processCallbackTest(Request $request)
    {
        // dd($request->all());
        // Validate the request but don't use 'exists' validation to avoid table name issues
        $request->validate([
            'invoice_id' => 'required',
        ]);

        $invoice = Invoice::find($request->invoice_id);

        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found');
        }

        try {
            // Update the invoice status directly
            $invoice->status_id = 8; // Paid status
            $invoice->save();


            Log::info('Invoice marked as paid via test callback', ['invoice_id' => $invoice->id]);

            // Check if the request is coming from the invoice page
            $referer = request()->headers->get('referer');
            if (strpos($referer, 'payment/invoice') !== false) {
                // If coming from invoice page, redirect to the same page to show updated status
                return redirect()->route('payment.show', $invoice->id)->with('success', 'Pembayaran berhasil! Invoice #' . $invoice->id . ' telah ditandai sebagai lunas.');
            }

            // Otherwise, redirect back to the callback tester page
            return redirect()->back()->with('success', 'Callback test successful! Invoice #' . $invoice->id . ' marked as paid.');
        } catch (\Exception $e) {
            Log::error('Error in callback test', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Callback test failed: ' . $e->getMessage());
        }
    }

    /**
     * Simulate sandbox payment completion
     * This method simulates what Tripay would send in a real callback
     */
    public function simulateSandboxPayment($invoiceId)
    {
        try {
            $invoice = Invoice::find($invoiceId);

            if (!$invoice) {
                Log::error('Invoice not found for sandbox simulation', ['invoice_id' => $invoiceId]);
                return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
            }

            // Ensure invoice has reference and merchant_ref
            if (!$invoice->reference) {
                $invoice->reference = 'SANDBOX-' . $invoice->id . '-' . time();
            }
            if (!$invoice->merchant_ref) {
                $invoice->merchant_ref = 'INV-' . $invoice->id . '-' . time();
            }
            $invoice->save();

            // Create a simulated Tripay callback payload that matches real Tripay format
            $simulatedPayload = [
                'reference' => $invoice->reference,
                'merchant_ref' => $invoice->merchant_ref,
                'payment_method' => 'SANDBOX',
                'payment_method_code' => 'SANDBOX',
                'total_amount' => $invoice->tagihan,
                'fee_merchant' => 0,
                'fee_customer' => 0,
                'total_fee' => 0,
                'amount_received' => $invoice->tagihan,
                'is_closed_payment' => 1,
                'status' => 'PAID',
                'paid_at' => time(),
                'note' => 'Sandbox payment simulation',
                'test_mode' => true
            ];

            Log::info('Simulating sandbox payment', [
                'invoice_id' => $invoiceId,
                'reference' => $invoice->reference,
                'merchant_ref' => $invoice->merchant_ref,
                'amount' => $invoice->tagihan
            ]);

            // Create a request object with the simulated data
            $request = new \Illuminate\Http\Request();
            $request->merge($simulatedPayload);

            // Call the callback handler
            $response = $this->paymentCallback($request);

            return $response;
        } catch (\Exception $e) {
            Log::error('Error in sandbox payment simulation', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Simulation failed: ' . $e->getMessage(),
                'error_code' => 'SIMULATION_ERROR'
            ], 500);
        }
    }

    /**
     * Simulate payment by reference (for testing payment detail page)
     */
    public function simulatePaymentByReference($reference)
    {
        try {
            $invoice = Invoice::where('reference', $reference)->first();

            if (!$invoice) {
                Log::error('Invoice not found for reference simulation', ['reference' => $reference]);
                return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
            }

            return $this->simulateSandboxPayment($invoice->id);
        } catch (\Exception $e) {
            Log::error('Error in reference payment simulation', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Simulation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Tripay test callback specifically
     * This method is designed to work with Tripay's test callback feature
     */
    public function handleTripayTestCallback(Request $request)
    {
        try {
            Log::info('Tripay test callback received', [
                'all_data' => $request->all(),
                'headers' => $request->headers->all(),
                'body' => $request->getContent(),
                'method' => $request->method(),
                'url' => $request->url()
            ]);

            // Get data from request (Tripay test sends as form data)
            $reference = $request->input('reference');
            $merchantRef = $request->input('merchant_ref');
            $status = $request->input('status', 'PAID'); // Default to PAID for test
            $amount = $request->input('amount');

            if (!$reference && !$merchantRef) {
                Log::error('No reference or merchant_ref in test callback');
                return response()->json([
                    'success' => false,
                    'message' => 'Missing reference or merchant_ref'
                ], 400);
            }

            // Find invoice
            $invoice = null;

            if ($reference) {
                $invoice = Invoice::where('reference', $reference)->first();
                Log::info('Looking for invoice by reference', ['reference' => $reference, 'found' => $invoice ? 'yes' : 'no']);
            }

            if (!$invoice && $merchantRef) {
                $invoice = Invoice::where('merchant_ref', $merchantRef)->first();
                Log::info('Looking for invoice by merchant_ref', ['merchant_ref' => $merchantRef, 'found' => $invoice ? 'yes' : 'no']);
            }

            // Try to extract invoice ID from merchant_ref pattern
            if (!$invoice && $merchantRef) {
                $parts = explode('-', $merchantRef);
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $extractedId = $parts[1];
                    $invoice = Invoice::find($extractedId);
                    Log::info('Extracted invoice ID from merchant_ref', [
                        'merchant_ref' => $merchantRef,
                        'extracted_id' => $extractedId,
                        'found' => $invoice ? 'yes' : 'no'
                    ]);
                }
            }

            if (!$invoice) {
                Log::error('Invoice not found in test callback', [
                    'reference' => $reference,
                    'merchant_ref' => $merchantRef
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found',
                    'debug' => [
                        'reference' => $reference,
                        'merchant_ref' => $merchantRef
                    ]
                ], 404);
            }

            // Update invoice status if payment is successful
            if ($status === 'PAID') {
                if ($invoice->status_id != 8) {
                    $invoice->status_id = 8; // Mark as paid
                    $invoice->save();

                    Log::info('Invoice marked as paid via Tripay test callback', [
                        'invoice_id' => $invoice->id,
                        'reference' => $reference,
                        'merchant_ref' => $merchantRef,
                        'amount' => $amount
                    ]);
                } else {
                    Log::info('Invoice already paid', ['invoice_id' => $invoice->id]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Test callback processed successfully',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'reference' => $reference,
                    'merchant_ref' => $merchantRef,
                    'status' => $status,
                    'invoice_status' => $invoice->status_id == 8 ? 'paid' : 'pending'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Tripay test callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test callback failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check payment status from Tripay API and update invoice accordingly
     */
    public function checkPaymentStatus($invoiceId)
    {
        try {
            $invoice = Invoice::find($invoiceId);

            if (!$invoice) {
                return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
            }

            if (!$invoice->reference) {
                return response()->json(['success' => false, 'message' => 'No Tripay reference found for this invoice'], 400);
            }

            // Get transaction details from Tripay
            $tripay = new TripayServices();
            $transactionDetails = $tripay->getTransactionDetails($invoice->reference);

            Log::info('Checking payment status from Tripay API', [
                'invoice_id' => $invoiceId,
                'reference' => $invoice->reference,
                'response' => $transactionDetails
            ]);

            if (isset($transactionDetails['success']) && $transactionDetails['success'] && isset($transactionDetails['data'])) {
                $tripayData = $transactionDetails['data'];
                $status = $tripayData['status'] ?? 'UNKNOWN';

                // Update invoice status based on Tripay status
                if ($status === 'PAID') {
                    if ($invoice->status_id != 8) {
                        $invoice->status_id = 8; // Paid status
                        $invoice->save();

                        Log::info('Invoice status updated to PAID from Tripay API check', [
                            'invoice_id' => $invoiceId,
                            'tripay_status' => $status
                        ]);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment confirmed as PAID',
                        'status' => 'PAID',
                        'invoice_status' => 'paid'
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment status checked',
                        'status' => $status,
                        'invoice_status' => $invoice->status_id == 8 ? 'paid' : 'pending'
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get transaction details from Tripay',
                    'response' => $transactionDetails
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error checking payment status', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error checking payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function syncPayment(Request $request, $invoiceId)
    {
        $invoice = Invoice::with('customer', 'paket')->find($invoiceId);

        if (!$invoice) {
            return redirect()->back()->with('error', 'Merchant Ref atau Reference tidak match!!!');
        }

        $apiKey = config('tripay.api_key');
        $url = "https://tripay.co.id/api/transaction/detail?reference={$invoice->reference}";

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept'        => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $trx  = $data['data'] ?? null;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghubungi Tripay: ' . $e->getMessage());
        }

        if (!$trx || empty($trx['status'])) {
            return redirect()->back()->with('error', 'Tidak bisa mendapatkan status transaksi dari Tripay');
        }
        // dd($trx);
        // Update reference jika berbeda
        if (
            $invoice->merchant_ref !== ($trx['merchant_ref'] ?? $invoice->merchant_ref) ||
            $invoice->reference    !== ($trx['reference'] ?? $invoice->reference)
        ) {

            $invoice->merchant_ref = $trx['merchant_ref'] ?? $invoice->merchant_ref;
            $invoice->reference    = $trx['reference'] ?? $invoice->reference;
            $invoice->save();

            Log::info('Invoice reference/merchant_ref diperbarui dari Tripay', [
                'invoice_id' => $invoice->id,
                'merchant_ref' => $invoice->merchant_ref,
                'reference'    => $invoice->reference,
            ]);
        }

        $status = strtoupper($trx['status']);

        // Hanya proses jika pembayaran PAID
        if ($status === 'PAID' && $invoice->status_id != 8) {
            $invoice->update([
                'status_id'    => 8,
                'metode_bayar' => $trx['payment_method'] ?? 'Tripay',
            ]);

            // Buat pembayaran
            $pembayaran = Pembayaran::create([
                'invoice_id'   => $invoice->id,
                'status_id'    => 8,
                'jumlah_bayar' => $trx['amount_received'] ?? 0,
                'metode_bayar' => $trx['payment_method'] ?? 'Tripay',
                'keterangan'   => 'Pembayaran dari pelanggan: ' . $invoice->customer->nama_customer . ' Via ' . $trx['payment_method'],
                'tanggal_bayar' => !empty($trx['paid_at']) ? Carbon::createFromTimestamp($trx['paid_at']) : now(),
            ]);

            // Catat kas
            Kas::create([
                'debit'       => $trx['amount_received'] ?? 0,
                'kas_id'      => 1,
                'tanggal_kas' => !empty($trx['paid_at']) ? Carbon::createFromTimestamp($trx['paid_at']) : now(),
                'keterangan'  => 'Pembayaran dari pelanggan: ' . $invoice->customer->nama_customer . ' Via ' . $trx['payment_method'],
                'status_id'   => 3,
            ]);

            // Generate invoice bulan depan
            $jatuhTempo = $invoice->jatuh_tempo;
            if ($jatuhTempo) {
                $bulanDepan = Carbon::parse($jatuhTempo)->addMonthNoOverflow();

                $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
                    ->whereMonth('jatuh_tempo', $bulanDepan->month)
                    ->whereYear('jatuh_tempo', $bulanDepan->year)
                    ->exists();

                if (!$sudahAda) {
                    $nextInvoice = Invoice::create([
                        'customer_id'    => $invoice->customer_id,
                        'tagihan'        => $invoice->paket->harga ?? 0,
                        'paket_id'       => $invoice->customer->paket_id,
                        'tambahan'       => 0,
                        'status_id'      => 7, // Belum bayar
                        'jatuh_tempo'    => $bulanDepan->endOfMonth()->setTime(23, 59, 59),
                        'tanggal_blokir' => $invoice->tanggal_blokir,
                        'metode_bayar'   => $invoice->metode_bayar,
                    ]);

                    Log::info('Invoice bulan depan dibuat', [
                        'invoice_id' => $nextInvoice->id,
                        'customer_id' => $invoice->customer_id,
                    ]);
                }
            }

            // Unblock user jika masih terblokir
            if ($invoice->customer->status_id == 9) {
                try {
                    $mikrotik = new MikrotikServices();
                    $client   = MikrotikServices::connect($invoice->customer->router);

                    $mikrotik->removeActiveConnections($client, $invoice->customer->usersecret);
                    $mikrotik->unblokUser($client, $invoice->customer->usersecret, $invoice->customer->paket->paket_name);

                    $invoice->customer->update(['status_id' => 3]);

                    Log::info('Customer berhasil di-unblock', ['customer_id' => $invoice->customer->id]);
                } catch (\Exception $e) {
                    Log::error('Gagal unblock customer', [
                        'customer_id' => $invoice->customer->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Kirim WA notif
            try {
                $chat = new ChatServices();
                $chat->pembayaranBerhasil($invoice->customer->no_hp, $pembayaran);
            } catch (\Exception $e) {
                Log::error('Gagal kirim notifikasi WhatsApp', [
                    'customer_id' => $invoice->customer->id,
                    'error' => $e->getMessage()
                ]);
            }

            activity('payment')
                ->performedOn($invoice)
                ->log('Pembayaran berhasil disinkronisasi via button IPN Tripay oleh: ' . auth()->user()->name);

            return redirect()->back()->with('success', 'Invoice berhasil disinkronisasi dari Tripay');
        }

        return redirect()->back()->with('info', 'Status invoice sudah sesuai atau belum dibayar di Tripay');
    }
}
