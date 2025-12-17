<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Modern Custom CSS -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-light: #e3f2fd;
            --primary-dark: #0b5ed7;
            --secondary-color: #6c757d;
            --accent-color: #198754;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        /* Invoice Card Styling */
        .invoice-card {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            background: white;
            transition: var(--transition);
        }

        .invoice-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: white;
            color: #333;
            padding: 1.5rem;
            border-bottom: 2px solid var(--primary-light);
        }

        .card-header h4 {
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--primary-color);
        }

        .card-header p {
            margin-bottom: 0.25rem;
            color: var(--secondary-color);
        }

        .badge {
            font-size: 0.85rem;
            padding: 0.5em 1em;
            border-radius: 50px;
            font-weight: 600;
        }

        .badge.bg-success {
            background: linear-gradient(90deg, var(--accent-color) 0%, #20c997 100%) !important;
        }

        .badge.bg-warning {
            background: linear-gradient(90deg, var(--warning-color) 0%, #ffca2c 100%) !important;
        }

        /* Icon Colors */
        .icon-primary {
            color: var(--primary-color);
        }

        .icon-secondary {
            color: var(--secondary-color);
        }

        .icon-accent {
            color: var(--accent-color);
        }

        .icon-warning {
            color: var(--warning-color);
        }

        .icon-info {
            color: var(--info-color);
        }

        .icon-white {
            color: white;
        }

        /* Table Styling */
        .invoice-table {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .invoice-table thead {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        }

        .invoice-table thead th {
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            border: none;
        }

        .invoice-table tbody td {
            vertical-align: middle;
            padding: 1rem;
            border-color: #f1f5f9;
        }

        .invoice-table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.03);
        }

        /* Summary Section */
        .summary-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #dee2e6;
        }

        .summary-total {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
            border-bottom: none;
            padding-top: 1rem;
            margin-top: 0.5rem;
            border-top: 2px solid #dee2e6;
        }

        /* Payment Methods */
        .payment-card {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            background: white;
        }

        .payment-card .card-header {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-bottom: none;
        }

        .payment-card .card-header h5 {
            color: white;
        }

        .payment-method {
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: white;
        }

        .payment-method:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-color);
        }

        .payment-method.active {
            background: var(--primary-light);
            border-color: var(--primary-color);
        }

        .payment-method-icon {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
            background: white;
            padding: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        /* Buttons */
        .btn-pay {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
        }

        .btn-pay:disabled {
            background: #6c757d;
            box-shadow: none;
            transform: none;
            cursor: not-allowed;
        }

        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        /* Footer */
        .invoice-footer {
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 1.5rem;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        /* Company Header */
        .company-header {
            display: flex;
            align-items: center;
        }

        .company-logo {
            max-width: 180px;
            height: auto;
            margin-right: 1rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .company-info h5 {
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--primary-color);
        }

        .company-info p {
            margin-bottom: 0;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }

        /* Animations */
        @keyframes btn-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }

        .btn-pulse {
            animation: btn-pulse 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .company-header {
                flex-direction: column;
                text-align: center;
            }

            .company-logo {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            .payment-method {
                padding: 0.75rem;
            }

            .payment-method-icon {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row g-4">
            <!-- Invoice Column -->
            <div class="col-lg-8">
                <div class="invoice-card print-area">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="company-header">
                                    <img src="/assets/logo_new.png" alt="Logo" class="company-logo">
                                    <div class="company-info">
                                        <h5 class="text-dark">Niscala Network Media</h5>
                                        <p>Temanggung, Jetis, Saptosari<br>
                                        Gunungkidul, DIYogyakarta</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <h4 class="mb-1 text-dark">Invoice #{{ $invoice->id }}</h4>
                                <p class="mb-1"><i class="far fa-calendar icon-primary me-1"></i> Tanggal: {{ date('d M Y') }}</p>
                                <p class="mb-2"><i class="far fa-clock icon-primary me-1"></i> Jatuh Tempo: {{ date('d M Y', strtotime($invoice->jatuh_tempo)) }}</p>
                                <span class="badge {{ $invoice->status->id == 8 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $invoice->status->nama_status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Customer Details -->
                        <div class="row mb-4 pb-3 border-bottom">
                            <div class="col-md-6 mb-4">
                                <h6 class="text-muted mb-3"><i class="fas fa-user icon-primary me-2"></i>Tagihan Kepada:</h6>
                                <div class="ps-3">
                                    <p class="mb-1 fw-semibold">{{ $invoice->customer->nama_customer }}</p>
                                    <p class="mb-1 text-muted">{{ $invoice->customer->alamat }}</p>
                                    <p class="mb-1 text-muted"><i class="fas fa-envelope icon-secondary me-1"></i>{{ $invoice->customer->email }}</p>
                                    <p class="mb-0 text-muted"><i class="fas fa-phone icon-secondary me-1"></i>{{ $invoice->customer->no_hp }}</p>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h6 class="text-muted mb-3"><i class="fas fa-info-circle icon-primary me-2"></i>Detail Tagihan:</h6>
                                <div class="ps-3">
                                    <p class="mb-1 fw-semibold">Paket: {{ $invoice->paket->nama_paket }}</p>
                                    <p class="mb-0 text-muted">Periode: {{ date('F', strtotime($invoice->created_at)) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive mb-4">
                            <table class="table invoice-table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Deskripsi</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-wifi icon-accent me-2"></i>
                                                <span>{{ $invoice->paket->nama_paket }} - Layanan Internet</span>
                                            </div>
                                        </td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}</td>
                                        <td class="text-center">1</td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="row">
                            <div class="col-md-8 col-12 mb-md-0 mb-3">
                                <div class="d-flex align-items-start mb-2">
                                    <i class="fas fa-user-shield icon-info me-2 mt-1"></i>
                                    <div>
                                        <span class="fw-bold">By Sistem:</span> NBilling
                                    </div>
                                </div>
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-comment icon-info me-2 mt-1"></i>
                                    <div>
                                        Terima kasih atas kepercayaan Anda menggunakan layanan kami.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-5">
                                <div class="summary-card">
                                    <div class="summary-item">
                                        <span class="fw-semibold">Subtotal:</span>
                                        <span>Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="fw-semibold">Biaya Tambahan:</span>
                                        <span>Rp {{ number_format($invoice->tambahan, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="fw-semibold">Sisa Saldo:</span>
                                        <span class="text-danger">- Rp {{ number_format($invoice->saldo, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="fw-semibold">Tunggakan:</span>
                                        <span class="text-danger">+ Rp {{ number_format($invoice->tunggakan, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="summary-item summary-total">
                                        <span>Total:</span>
                                        <span>Rp {{ number_format($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan - $invoice->saldo, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Footer -->
                        <div class="invoice-footer">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-sticky-note icon-warning me-2 mt-1"></i>
                                <div>
                                    <span class="fw-bold">Catatan:</span>
                                    Mohon lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari pemutusan layanan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Column -->
            <div class="col-lg-4 no-print">
                @if ($invoice->status->id != 8)
                <div class="sticky-top" style="top: 20px;">

                    <!-- Payment Methods -->
                    <div class="payment-card fade-in">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-credit-card icon-white me-2"></i>Pilih Metode Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="payment-methods-list">
                                @foreach ($channels as $channel)
                                <div class="payment-method" onclick="selectPaymentMethod(this, '{{ $channel['code'] }}')" data-code="{{ $channel['code'] }}">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $channel['icon_url'] }}" alt="{{ $channel['name'] }}" class="payment-method-icon me-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $channel['name'] }}</div>
                                            <div class="small text-muted">{{ $channel['group'] }}</div>
                                        </div>
                                        <i class="fas fa-check-circle icon-primary ms-2 d-none"></i>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <form action="{{ route('tripay.payment', $invoice->id) }}" method="POST" id="payment-form">
                                @csrf
                                <input type="hidden" name="payment_method" id="payment_method">
                                <div class="d-flex justify-content-center">
                                    <button type="submit" class="btn btn-pay w-50 text-center text-white mt-3" id="pay-button" disabled>
                                        <i class="fas fa-credit-card icon-white me-2"></i> Bayar Sekarang
                                    </button>
                                </div>
                            </form>

                            <div class="alert alert-warning mt-3 mb-0">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle icon-warning me-2 mt-1"></i>
                                    <div>
                                        <strong>Catatan:</strong> Pembayaran akan diproses secara otomatis.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="payment-card fade-in">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-check-circle icon-primary mb-3" style="font-size: 3rem;"></i>
                        <h5 class="mb-2">Pembayaran Berhasil</h5>
                        <p class="text-muted">Invoice ini telah dibayar lunas.</p>
                        <!-- Print Button -->
                        <div class="mt-2">
                          <a href="/print-kwitansi/{{ $invoice->id }}">
                            <button class="btn btn-outline-primary">
                                <i class="fas fa-print me-2"></i> Cetak PDF
                            </button>
                          </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5 no-print">
        <div class="container text-center">
            ©
            <script>
                document.write(new Date().getFullYear())
            </script>,
            made with <i class="fas fa-heart text-danger mx-1"></i> by <a href="https://www.instagram.com/riskimispanda_/" target="_blank" class="text-white">Panda 🐼</a>
        </div>
    </footer>
</body>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            m.querySelector('.fa-check-circle').classList.add('d-none');
        });

        // Add active class and visual feedback
        element.classList.add('active');
        element.querySelector('.fa-check-circle').classList.remove('d-none');

        // Update hidden input
        document.getElementById('payment_method').value = code;

        // Enable pay button and add visual feedback
        const payButton = document.getElementById('pay-button');
        payButton.disabled = false;
        payButton.classList.add('btn-pulse');

        // Remove pulse effect after animation
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
                        notification.className = 'alert alert-warning mt-3 fade-in';
                        notification.innerHTML =
                        '<i class="fas fa-info-circle me-2"></i> Silakan pilih metode pembayaran terlebih dahulu';

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
</html>
