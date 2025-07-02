<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\TripayServices;
use App\Models\Invoice;

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
        $allowedChannels = ['BRIVA', 'MANDIRIVA', 'BNIVA', 'QRIS', 'DANA', 'GOPAY', 'OVO'];
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
                \Log::error('Error getting transaction details for payment detail page', [
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
            \Log::error('Error in showPaymentDetail', [
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
            \Log::info('Payment attempt', [
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
            \Log::error('Payment processing error', [
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
            \Log::error('Exception in payment processing', [
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
            // Log the callback request for debugging
            \Log::info('Payment callback received', [
                'headers' => $request->headers->all(),
                'body' => $request->getContent(),
                'all' => $request->all(),
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'url' => $request->url(),
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'user_agent' => $request->header('User-Agent')
            ]);

            // Determine if we're in sandbox mode
            $isSandbox = env('APP_ENV') !== 'production';

            // Get the callback signature
            $callbackSignature = $request->header('X-Callback-Signature');

            // Initialize variables
            $data = null;
            $invoiceId = null;
            $reference = null;
            $merchantRef = null;

            // Check if this is a test mode request first
            if ($request->has('test_mode')) {
                \Log::info('Processing test mode callback');
                $data = (object) $request->all();
                $invoiceId = $request->input('invoice_id');
                $reference = $request->input('reference');
                $merchantRef = $request->input('merchant_ref');
            } else {
                // Get the JSON data for normal Tripay callbacks
                $json = $request->getContent();

                // Check if we have JSON content
                if (empty($json)) {
                    \Log::info('No JSON content, trying form data (likely from Tripay test)');
                    // Try to get data from request parameters (for form submissions or Tripay test)
                    $data = (object) $request->all();
                    $reference = $request->input('reference');
                    $merchantRef = $request->input('merchant_ref');

                    // Log all available data for debugging
                    \Log::info('Form data received', [
                        'reference' => $reference,
                        'merchant_ref' => $merchantRef,
                        'status' => $request->input('status'),
                        'amount' => $request->input('amount'),
                        'all_data' => $request->all()
                    ]);
                } else {
                    \Log::info('Processing JSON callback data');

                    // Validate the callback signature if provided and not in test mode
                    if ($callbackSignature && !$isSandbox) {
                        $privateKey = config('tripay.private_key');
                        $signature = hash_hmac('sha256', $json, $privateKey);

                        \Log::info('Signature validation', [
                            'expected' => $signature,
                            'received' => $callbackSignature,
                            'match' => ($signature === $callbackSignature)
                        ]);

                        if ($signature !== $callbackSignature) {
                            \Log::warning('Invalid signature in production mode');
                            return response()->json([
                                'success' => false,
                                'message' => 'Invalid signature',
                            ], 400);
                        }
                    } elseif ($isSandbox) {
                        \Log::info('Sandbox mode - skipping signature validation');
                    }

                    // Decode the JSON
                    $data = json_decode($json);

                    if (!$data) {
                        \Log::error('Failed to decode JSON data', ['json' => $json]);

                        // Fallback to form data if JSON decode fails
                        \Log::info('JSON decode failed, falling back to form data');
                        $data = (object) $request->all();
                        $reference = $request->input('reference');
                        $merchantRef = $request->input('merchant_ref');
                    } else {
                        $reference = $data->reference ?? null;
                        $merchantRef = $data->merchant_ref ?? null;
                    }
                }
            }

            // Now find the invoice using the extracted data
            \Log::info('Looking for invoice', [
                'reference' => $reference,
                'merchant_ref' => $merchantRef,
                'invoice_id' => $invoiceId
            ]);

            // Try to find invoice by different methods
            $invoice = null;

            // Method 1: Direct invoice_id (for test mode)
            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    \Log::info('Found invoice by direct ID', ['invoice_id' => $invoiceId]);
                }
            }

            // Method 2: Find by reference
            if (!$invoice && $reference) {
                $invoice = Invoice::where('reference', $reference)->first();
                if ($invoice) {
                    \Log::info('Found invoice by reference', ['reference' => $reference, 'invoice_id' => $invoice->id]);
                }
            }

            // Method 3: Find by merchant_ref
            if (!$invoice && $merchantRef) {
                $invoice = Invoice::where('merchant_ref', $merchantRef)->first();
                if ($invoice) {
                    \Log::info('Found invoice by merchant_ref', ['merchant_ref' => $merchantRef, 'invoice_id' => $invoice->id]);
                }
            }

            // Method 4: Extract invoice ID from merchant_ref pattern
            if (!$invoice && $merchantRef) {
                $parts = explode('-', $merchantRef);
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $extractedId = $parts[1];
                    $invoice = Invoice::find($extractedId);
                    if ($invoice) {
                        \Log::info('Found invoice by extracting ID from merchant_ref', [
                            'merchant_ref' => $merchantRef,
                            'extracted_id' => $extractedId,
                            'invoice_id' => $invoice->id
                        ]);

                        // Update invoice with reference data if missing
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

            // Method 5: Try to get transaction details from Tripay API
            if (!$invoice && $reference && !$request->has('test_mode')) {
                try {
                    $tripay = new TripayServices();
                    $transactionDetails = $tripay->getTransactionDetails($reference);

                    if (isset($transactionDetails['success']) && $transactionDetails['success'] && isset($transactionDetails['data']['merchant_ref'])) {
                        $apiMerchantRef = $transactionDetails['data']['merchant_ref'];
                        $parts = explode('-', $apiMerchantRef);
                        if (isset($parts[1]) && is_numeric($parts[1])) {
                            $extractedId = $parts[1];
                            $invoice = Invoice::find($extractedId);
                            if ($invoice) {
                                \Log::info('Found invoice via Tripay API', [
                                    'reference' => $reference,
                                    'api_merchant_ref' => $apiMerchantRef,
                                    'invoice_id' => $invoice->id
                                ]);

                                // Update invoice with reference data
                                $invoice->reference = $reference;
                                $invoice->merchant_ref = $apiMerchantRef;
                                $invoice->save();
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error fetching transaction details from Tripay API', [
                        'reference' => $reference,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Final check - if no invoice found, return error
            if (!$invoice) {
                \Log::warning('Invoice not found after all methods', [
                    'reference' => $reference,
                    'merchant_ref' => $merchantRef,
                    'invoice_id' => $invoiceId,
                    'request_data' => $request->all()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found',
                    'debug_info' => [
                        'reference' => $reference,
                        'merchant_ref' => $merchantRef,
                        'invoice_id' => $invoiceId
                    ]
                ], 404);
            }

            // Get payment status from data
            $paymentStatus = null;

            // Try to get status from different possible sources
            if (isset($data->status)) {
                $paymentStatus = $data->status;
            } elseif ($request->has('status')) {
                $paymentStatus = $request->input('status');
            }

            // For test mode, always consider it as PAID
            if ($request->has('test_mode')) {
                $paymentStatus = 'PAID';
            }

            \Log::info('Processing payment status', [
                'invoice_id' => $invoice->id,
                'current_status_id' => $invoice->status_id,
                'payment_status' => $paymentStatus,
                'test_mode' => $request->has('test_mode'),
                'is_sandbox' => $isSandbox,
                'reference' => $reference,
                'merchant_ref' => $merchantRef
            ]);

            // Update the invoice status based on the payment status
            if ($paymentStatus === 'PAID' || $request->has('test_mode')) {
                // Check if invoice is already paid
                if ($invoice->status_id == 8) {
                    \Log::info('Invoice already marked as paid', [
                        'invoice_id' => $invoice->id,
                        'reference' => $reference
                    ]);

                    $responseMessage = 'Invoice already paid';
                } else {
                    // Update the invoice status to paid (status_id 8 is for paid)
                    $invoice->status_id = 8;
                    $invoice->save();

                    \Log::info('Invoice successfully marked as paid', [
                        'invoice_id' => $invoice->id,
                        'customer' => $invoice->customer->nama_customer ?? 'Unknown',
                        'amount' => $invoice->tagihan,
                        'reference' => $reference,
                        'merchant_ref' => $merchantRef,
                        'payment_status' => $paymentStatus
                    ]);

                    $responseMessage = 'Payment processed successfully';

                    // TODO: Add notification logic here
                    // Example: event(new InvoicePaid($invoice));
                    // Example: Mail::to($invoice->customer->email)->send(new PaymentConfirmation($invoice));
                }
            } else {
                \Log::info('Payment status not PAID', [
                    'invoice_id' => $invoice->id,
                    'status' => $paymentStatus ?? 'unknown',
                    'reference' => $reference
                ]);

                // Handle different payment statuses
                switch ($paymentStatus) {
                    case 'EXPIRED':
                        \Log::info('Payment expired', ['invoice_id' => $invoice->id, 'reference' => $reference]);
                        $responseMessage = 'Payment expired';
                        break;
                    case 'FAILED':
                        \Log::info('Payment failed', ['invoice_id' => $invoice->id, 'reference' => $reference]);
                        $responseMessage = 'Payment failed';
                        break;
                    case 'UNPAID':
                        \Log::info('Payment still unpaid', ['invoice_id' => $invoice->id, 'reference' => $reference]);
                        $responseMessage = 'Payment still pending';
                        break;
                    default:
                        \Log::info('Unknown payment status', ['invoice_id' => $invoice->id, 'status' => $paymentStatus, 'reference' => $reference]);
                        $responseMessage = 'Payment status: ' . ($paymentStatus ?? 'unknown');
                }
            }

            // Return success response
            return response()->json([
                'success' => true,
                'message' => $responseMessage,
                'data' => [
                    'invoice_id' => $invoice->id,
                    'reference' => $reference,
                    'merchant_ref' => $merchantRef,
                    'payment_status' => $paymentStatus,
                    'invoice_status' => $invoice->status_id == 8 ? 'paid' : 'pending',
                    'amount' => $invoice->tagihan
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error processing payment callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'reference' => $reference ?? 'unknown',
                'merchant_ref' => $merchantRef ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
                'error_code' => 'CALLBACK_ERROR'
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


            \Log::info('Invoice marked as paid via test callback', ['invoice_id' => $invoice->id]);

            // Check if the request is coming from the invoice page
            $referer = request()->headers->get('referer');
            if (strpos($referer, 'payment/invoice') !== false) {
                // If coming from invoice page, redirect to the same page to show updated status
                return redirect()->route('payment.show', $invoice->id)
                    ->with('success', 'Pembayaran berhasil! Invoice #' . $invoice->id . ' telah ditandai sebagai lunas.');
            }

            // Otherwise, redirect back to the callback tester page
            return redirect()->back()->with('success', 'Callback test successful! Invoice #' . $invoice->id . ' marked as paid.');
        } catch (\Exception $e) {
            \Log::error('Error in callback test', ['error' => $e->getMessage()]);
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
                \Log::error('Invoice not found for sandbox simulation', ['invoice_id' => $invoiceId]);
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

            \Log::info('Simulating sandbox payment', [
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
            \Log::error('Error in sandbox payment simulation', [
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
                \Log::error('Invoice not found for reference simulation', ['reference' => $reference]);
                return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
            }

            return $this->simulateSandboxPayment($invoice->id);
        } catch (\Exception $e) {
            \Log::error('Error in reference payment simulation', [
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
            \Log::info('Tripay test callback received', [
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
                \Log::error('No reference or merchant_ref in test callback');
                return response()->json([
                    'success' => false,
                    'message' => 'Missing reference or merchant_ref'
                ], 400);
            }

            // Find invoice
            $invoice = null;

            if ($reference) {
                $invoice = Invoice::where('reference', $reference)->first();
                \Log::info('Looking for invoice by reference', ['reference' => $reference, 'found' => $invoice ? 'yes' : 'no']);
            }

            if (!$invoice && $merchantRef) {
                $invoice = Invoice::where('merchant_ref', $merchantRef)->first();
                \Log::info('Looking for invoice by merchant_ref', ['merchant_ref' => $merchantRef, 'found' => $invoice ? 'yes' : 'no']);
            }

            // Try to extract invoice ID from merchant_ref pattern
            if (!$invoice && $merchantRef) {
                $parts = explode('-', $merchantRef);
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $extractedId = $parts[1];
                    $invoice = Invoice::find($extractedId);
                    \Log::info('Extracted invoice ID from merchant_ref', [
                        'merchant_ref' => $merchantRef,
                        'extracted_id' => $extractedId,
                        'found' => $invoice ? 'yes' : 'no'
                    ]);
                }
            }

            if (!$invoice) {
                \Log::error('Invoice not found in test callback', [
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

                    \Log::info('Invoice marked as paid via Tripay test callback', [
                        'invoice_id' => $invoice->id,
                        'reference' => $reference,
                        'merchant_ref' => $merchantRef,
                        'amount' => $amount
                    ]);
                } else {
                    \Log::info('Invoice already paid', ['invoice_id' => $invoice->id]);
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
            \Log::error('Error in Tripay test callback', [
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

            \Log::info('Checking payment status from Tripay API', [
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

                        \Log::info('Invoice status updated to PAID from Tripay API check', [
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
            \Log::error('Error checking payment status', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error checking payment status: ' . $e->getMessage()
            ], 500);
        }
    }
}
