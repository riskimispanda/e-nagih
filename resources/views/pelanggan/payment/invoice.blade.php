@extends('layouts.contentNavbarLayout')

@section('title', 'Invoice')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection



<style>
    /* Base styles */
    .invoice-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .invoice-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        border: none;
        overflow: hidden;
    }

    .invoice-header {
        padding: 2rem;
        border-bottom: 1px solid #f5f5f9;
    }

    .invoice-body {
        padding: 2rem;
    }

    .invoice-footer {
        padding: 1.5rem 2rem;
        background-color: #fcfcfd;
        border-top: 1px solid #f5f5f9;
    }

    /* Typography */
    .invoice-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #566a7f;
        margin-bottom: 0.5rem;
    }

    .invoice-subtitle {
        font-size: 0.875rem;
        color: #697a8d;
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .invoice-text {
        font-size: 0.875rem;
        color: #697a8d;
        margin-bottom: 0.5rem;
    }

    .invoice-value {
        font-weight: 500;
        color: #566a7f;
    }

    /* Status badge */
    .status-badge {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 500;
        border-radius: 2px;
    }

    .status-paid {
        background-color: rgba(113, 221, 55, 0.16);
        color: #71DD37;
    }

    .status-pending {
        background-color: rgba(255, 171, 0, 0.16);
        color: #FFAB00;
    }

    /* Table styles */
    .invoice-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .invoice-table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #a1acb8;
        font-weight: 500;
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid #f0f2f4;
    }

    .invoice-table td {
        padding: 1.25rem;
        vertical-align: top;
        border-bottom: 1px solid #f0f2f4;
        color: #697a8d;
    }

    .invoice-table tr:last-child td {
        border-bottom: none;
    }

    /* Payment methods */
    .payment-method {
        transition: all 0.2s ease;
        border: 1px solid #eaeaec;
        border-radius: 6px;
        padding: 0.75rem 1rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .payment-method:hover {
        background-color: rgba(105, 108, 255, 0.04);
        border-color: rgba(105, 108, 255, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    }

    .payment-method.active {
        background-color: rgba(105, 108, 255, 0.16);
        border-color: #696cff;
    }

    .payment-method.active::before {
        content: "✓";
        position: absolute;
        top: 8px;
        right: 8px;
        width: 20px;
        height: 20px;
        background: #696cff;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    /* Buttons */
    .btn-action {
        border-radius: 6px;
        font-weight: 500;
        padding: 0.5rem 1.25rem;
        transition: all 0.2s ease;
    }

    /* Button pulse effect */
    @keyframes btn-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    .btn-pulse {
        animation: btn-pulse 1s;
    }

    /* Print styles - Optimized for single page */
    @media print {

        /* Page setup */
        @page {
            size: A4;
            margin: 0.5in;
        }

        /* Hide non-essential elements */
        body * {
            visibility: hidden;
        }

        .invoice-card,
        .invoice-card * {
            visibility: visible;
        }

        .no-print {
            display: none !important;
        }

        /* Hide Tailwind elements that shouldn't be printed */
        .print\\:hidden {
            display: none !important;
        }

        /* Main container adjustments */
        .invoice-card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
            max-width: 100% !important;
            box-shadow: none !important;
            border: 1px solid #eaeaec;
            font-size: 12px !important;
            line-height: 1.3 !important;
        }

        /* Header adjustments */
        .invoice-header {
            padding: 12px !important;
            border-bottom: 1px solid #f5f5f9;
        }

        /* Fix header layout for print */
        .invoice-header .row {
            display: flex !important;
            flex-wrap: nowrap !important;
            margin: 0 !important;
        }

        .invoice-header .col-md-6 {
            flex: 0 0 50% !important;
            max-width: 50% !important;
            padding: 0 6px !important;
            margin-bottom: 0 !important;
        }

        /* Ensure right alignment is maintained */
        .text-md-end {
            text-align: right !important;
        }

        /* Fix flexbox alignment */
        .d-flex {
            display: flex !important;
        }

        .align-items-center {
            align-items: center !important;
        }

        .justify-content-between {
            justify-content: space-between !important;
        }

        /* Body adjustments */
        .invoice-body {
            padding: 12px !important;
        }

        /* Footer adjustments */
        .invoice-footer {
            padding: 8px 12px !important;
            background-color: #fcfcfd;
            border-top: 1px solid #f5f5f9;
        }

        /* Typography adjustments */
        .invoice-title {
            font-size: 16px !important;
            margin-bottom: 4px !important;
            display: block !important;
        }

        .invoice-subtitle {
            font-size: 12px !important;
            margin-bottom: 6px !important;
        }

        .invoice-text {
            font-size: 11px !important;
            margin-bottom: 3px !important;
            display: block !important;
        }

        /* Fix header right side elements positioning */
        .invoice-header .col-md-6:last-child {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-end !important;
            justify-content: flex-start !important;
        }

        .invoice-header .col-md-6:last-child>* {
            margin-bottom: 3px !important;
            text-align: right !important;
        }

        /* Status badge specific positioning */
        .status-badge {
            display: inline-block !important;
            margin-top: 4px !important;
            padding: 2px 6px !important;
            font-size: 10px !important;
        }

        /* Prevent elements from breaking layout */
        .invoice-header .invoice-title,
        .invoice-header .invoice-text,
        .invoice-header .status-badge {
            white-space: nowrap !important;
            overflow: visible !important;
        }

        /* Fix specific header elements */
        .invoice-header .mt-2 {
            margin-top: 4px !important;
            align-self: flex-end !important;
        }

        /* Ensure proper spacing in header */
        .invoice-header .mb-md-0 {
            margin-bottom: 0 !important;
        }

        .invoice-header .mb-4 {
            margin-bottom: 8px !important;
        }

        /* Logo adjustments */
        .print-logo,
        img {
            max-width: 50px !important;
            max-height: 50px !important;
            width: 50px !important;
            height: auto !important;
        }

        /* Table adjustments */
        .invoice-table {
            font-size: 11px !important;
        }

        .invoice-table th {
            padding: 6px 8px !important;
            font-size: 10px !important;
        }

        .invoice-table td {
            padding: 6px 8px !important;
            font-size: 11px !important;
        }

        /* Row and column adjustments */
        .row {
            margin-bottom: 8px !important;
        }

        .mb-4 {
            margin-bottom: 8px !important;
        }

        .mb-3 {
            margin-bottom: 6px !important;
        }

        .mb-2 {
            margin-bottom: 4px !important;
        }

        .mb-1 {
            margin-bottom: 2px !important;
        }

        /* Summary box adjustments */
        .bg-lighter {
            padding: 8px !important;
        }

        /* Status badge adjustments */
        .status-badge {
            padding: 2px 6px !important;
            font-size: 10px !important;
        }

        /* Ensure content fits in one page */
        .invoice-container {
            page-break-inside: avoid;
            max-height: 100vh;
            overflow: hidden;
        }

        /* Additional spacing reductions */
        .py-2 {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }

        .p-3 {
            padding: 6px !important;
        }

        .my-2 {
            margin-top: 4px !important;
            margin-bottom: 4px !important;
        }

        .mt-2 {
            margin-top: 4px !important;
        }

        /* Table responsive adjustments */
        .table-responsive {
            margin-bottom: 8px !important;
        }

        /* Force color printing */
        body {
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        /* Compact layout for print - exclude header columns */
        .invoice-body .col-md-6,
        .invoice-body .col-md-4,
        .invoice-body .col-md-8 {
            margin-bottom: 6px !important;
        }

        /* Specific fixes for header layout */
        .invoice-header .row>.col-md-6 {
            margin-bottom: 0 !important;
            padding-right: 6px !important;
            padding-left: 6px !important;
        }

        /* Force header elements to stay in position */
        .invoice-header .col-md-6:first-child {
            float: left !important;
            width: 50% !important;
        }

        .invoice-header .col-md-6:last-child {
            float: right !important;
            text-align: right !important;
            width: 50% !important;
        }

        /* Clearfix for header row */
        .invoice-header .row::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Override any conflicting flex properties in header */
        .invoice-header .row {
            display: block !important;
        }

        .invoice-header .col-md-6 {
            display: block !important;
            box-sizing: border-box !important;
        }

        /* Reduce line height for better space utilization */
        * {
            line-height: 1.2 !important;
        }
    }
</style>

@section('content')
    <div class="row">
        <div class="invoice-container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible mb-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="row">
                <!-- Invoice -->
                <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-4">
                    <div class="card invoice-card">
                        <!-- Invoice Header -->
                        <div class="invoice-header bg-lighter py-2">
                            <div class="row">
                                <div class="col-md-6 mb-md-0 mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="{{ asset('assets/nagih.svg') }}" alt="Logo" width="60"
                                            class="me-2 print-logo">
                                        <span class="invoice-title"></span>
                                    </div>
                                    <div class="invoice-text">Temanggung, Jetis, Saptosari</div>
                                    <div class="invoice-text">Gunungkidul, DIYogyakarta, Indonesia</div>
                                    <div class="invoice-text">+62 (123) 456 7891</div>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="invoice-title">INVOICE #{{ $invoice->id }}</div>
                                    <div class="invoice-text">
                                        <span class="text-muted">Tanggal:</span>
                                        <span class="invoice-value">{{ \Carbon\Carbon::now()->format('d M Y') }}</span>
                                    </div>
                                    <div class="invoice-text">
                                        <span class="text-muted">Jatuh Tempo:</span>
                                        <span
                                            class="invoice-value">{{ \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d M Y') }}</span>
                                    </div>
                                    <div class="mt-2">
                                        <span
                                            class="status-badge {{ $invoice->status->id == 8 ? 'status-paid' : 'status-pending' }}">
                                            {{ $invoice->status->nama_status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Invoice Body -->
                        <div class="invoice-body">
                            <div class="row mb-4">
                                <div class="col-md-6 mb-md-0 mb-3">
                                    <div class="invoice-subtitle">Tagihan Kepada</div>
                                    <div class="invoice-text fw-medium">{{ $invoice->customer->nama_customer }}</div>
                                    <div class="invoice-text">{{ $invoice->customer->alamat }}</div>
                                    <div class="invoice-text">{{ $invoice->customer->email }}</div>
                                    <div class="invoice-text">{{ $invoice->customer->no_hp }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="invoice-subtitle">Detail Tagihan</div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="invoice-text">Paket Internet:</span>
                                        <span class="invoice-value">{{ $invoice->paket->nama_paket }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="invoice-text">Lokasi:</span>
                                        <span
                                            class="invoice-value">{{ $invoice->customer->lokasi->nama_lokasi ?? 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="invoice-text">Periode:</span>
                                        <span
                                            class="invoice-value">{{ \Carbon\Carbon::parse($invoice->created_at)->format('M Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Table -->
                            <div class="table-responsive mb-4">
                                <table class="invoice-table">
                                    <thead>
                                        <tr>
                                            <th>Deskripsi</th>
                                            <th class="text-end">Harga</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $invoice->paket->nama_paket }} - Layanan Internet</td>
                                            <td class="text-end">Rp
                                                {{ number_format($invoice->paket->harga, 0, ',', '.') }}</td>
                                            <td class="text-center">1</td>
                                            <td class="text-end">Rp
                                                {{ number_format($invoice->paket->harga, 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Invoice Summary -->
                            <div class="row">
                                <div class="col-md-8 col-12 mb-md-0 mb-3">
                                    <div class="invoice-text mb-1">
                                        <span class="fw-medium">Admin:</span> Admin
                                    </div>
                                    <div class="invoice-text">
                                        Terima kasih atas kepercayaan Anda menggunakan layanan kami.
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="bg-lighter p-3 rounded">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="invoice-text">Subtotal:</span>
                                            <span class="invoice-value">Rp
                                                {{ number_format($invoice->tagihan, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="invoice-text">Diskon:</span>
                                            <span class="invoice-value">Rp 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="invoice-text">Biaya Tambahan:</span>
                                            <span class="invoice-value">Rp
                                                {{ number_format($invoice->tambahan, 0, ',', '.') }}</span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="invoice-text fw-medium">Total:</span>
                                            <span class="invoice-value fw-semibold">Rp
                                                {{ number_format($invoice->tagihan + $invoice->tambahan, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Footer -->
                        <div class="invoice-footer">
                            <div class="invoice-text">
                                <span class="fw-medium">Catatan:</span>
                                Mohon lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari pemutusan layanan.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Actions -->
                <div class="col-xl-3 col-md-4 col-12 no-print">
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            <button class="btn btn-primary btn-action d-block w-100 mb-2" id="print-invoice">
                                <i class="bx bx-printer me-1"></i> Print
                            </button>

                            <a href="{{ url('/data/invoice/' . $invoice->customer->nama_customer) }}"
                                class="btn btn-outline-secondary btn-action d-block w-100">
                                <i class="bx bx-chevron-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>

                    @if ($invoice->status->id != 8)
                        <div class="card mb-3">
                            <div class="card-body p-3">
                                <div class="invoice-subtitle mb-4">Metode Pembayaran</div>
                                <div class="payment-methods-list">
                                    @foreach ($channels as $channel)
                                        <div class="payment-method d-flex align-items-center mb-2 p-3 rounded border cursor-pointer"
                                            data-code="{{ $channel['code'] }}"
                                            onclick="selectPaymentMethod(this, '{{ $channel['code'] }}')">
                                            <img src="{{ $channel['icon_url'] }}" alt="{{ $channel['name'] }}"
                                                height="24" class="me-2">
                                            <div>
                                                <div class="invoice-text mb-0 fw-medium">{{ $channel['name'] }}</div>
                                                <div class="small text-muted">{{ $channel['group'] }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <form action="{{ route('tripay.payment', $invoice->id) }}" method="POST" class="mt-3"
                                    id="payment-form">
                                    @csrf
                                    <input type="hidden" name="payment_method" id="payment_method"
                                        value="{{ $channels[0]['code'] }}">
                                    <button type="submit" class="btn btn-success btn-action d-block w-100"
                                        id="pay-button">
                                        <i class="bx bx-credit-card me-1"></i> Bayar Sekarang
                                    </button>
                                </form>

                                <!-- Payment callback information -->
                                <div class="mt-3 small text-muted">
                                    <p class="mb-1">Setelah pembayaran, sistem akan otomatis memperbarui status invoice
                                        Anda melalui callback ke <code>{{ route('payment.callback') }}</code></p>
                                </div>

                                <!-- Direct test payment option - Only visible to admins -->
                                @if (auth()->user()->roles_id == 8 || auth()->user()->roles_id == 2)
                                    <div class="mt-3 border-top pt-3">
                                        <p class="mb-2 fw-medium">Admin Testing Options:</p>
                                        <form action="{{ route('payment.callback.test') }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-info">
                                                <i class="bx bx-test-tube me-1"></i> Simulate Paid Callback
                                            </button>
                                        </form>

                                        <a href="{{ route('payment.direct.test', $invoice->id) }}" target="_blank"
                                            class="btn btn-sm btn-outline-success ms-1">
                                            <i class="bx bx-check-circle me-1"></i> Direct Test
                                        </a>

                                        <a href="{{ route('payment.fallback') }}?test_mode=1&invoice_id={{ $invoice->id }}"
                                            target="_blank" class="btn btn-sm btn-outline-primary ms-1">
                                            <i class="bx bx-link-alt me-1"></i> Test Fallback
                                        </a>

                                        <a href="{{ route('payment.callback.tester') }}" target="_blank"
                                            class="btn btn-sm btn-outline-secondary ms-1">
                                            <i class="bx bx-cog me-1"></i> Callback Tester
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('page-script')
    <script>
        /**
         * Handle payment method selection
         * @param {HTMLElement} element - The payment method element
         * @param {string} code - The payment method code
         */
        function selectPaymentMethod(element, code) {
            // Remove active class from all methods
            document.querySelectorAll('.payment-method').forEach(m => {
                m.classList.remove('active');
            });

            // Add active class to selected method
            element.classList.add('active');

            // Set the value in the hidden input
            document.getElementById('payment_method').value = code;

            // Highlight the pay button
            const payButton = document.getElementById('pay-button');
            payButton.classList.add('btn-pulse');

            // Remove the highlight after a short delay
            setTimeout(() => {
                payButton.classList.remove('btn-pulse');
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Print invoice button
            const printButton = document.getElementById('print-invoice');
            if (printButton) {
                printButton.addEventListener('click', function() {
                    window.print();
                });
            }

            // Payment form validation
            const paymentForm = document.getElementById('payment-form');
            if (paymentForm) {
                paymentForm.addEventListener('submit', function(e) {
                    const paymentMethod = document.getElementById('payment_method').value;
                    if (!paymentMethod) {
                        e.preventDefault();
                        // Use a more user-friendly notification instead of alert
                        const paymentMethodsList = document.querySelector('.payment-methods-list');
                        if (paymentMethodsList) {
                            // Add a gentle shake animation to the payment methods list
                            paymentMethodsList.classList.add('shake');
                            setTimeout(() => {
                                paymentMethodsList.classList.remove('shake');
                            }, 500);

                            // Show a notification
                            const notification = document.createElement('div');
                            notification.className = 'alert alert-warning mt-3';
                            notification.innerHTML =
                                '<i class="bx bx-info-circle me-2"></i> Silakan pilih metode pembayaran terlebih dahulu';

                            // Insert the notification before the submit button
                            const submitButton = paymentForm.querySelector('button[type="submit"]');
                            if (submitButton) {
                                submitButton.parentNode.insertBefore(notification, submitButton);

                                // Remove the notification after 3 seconds
                                setTimeout(() => {
                                    notification.remove();
                                }, 3000);
                            }
                        }
                        return false;
                    }
                });
            }

            // Add a CSS class for the shake animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                    20%, 40%, 60%, 80% { transform: translateX(5px); }
                }
                .shake {
                    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
@endsection
