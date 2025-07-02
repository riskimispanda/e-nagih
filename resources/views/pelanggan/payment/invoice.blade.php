<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Minimal Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        /* Essential payment method styles */
        .payment-method {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .payment-method:hover {
            transform: translateY(-2px);
            border-color: #198754 !important;
        }
        
        .payment-method.active {
            background: #e8f5e9;
            border-color: #198754 !important;
        }
        
        .payment-method-icon {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        
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
            }
            .no-print {
                display: none !important;
            }
        }
        
        @keyframes btn-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        .btn-pulse {
            animation: btn-pulse 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row g-4">
            <!-- Invoice Column -->
            <div class="col-lg-8">
                <div class="card shadow-sm print-area">
                    <div class="card-header bg-white py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <img src="/assets/nagih.svg" alt="Logo" class="me-2" style="width: 120px">
                                    <div>
                                        <h5 class="mb-1">Niscala Network</h5>
                                        <p class="mb-0 small">Temanggung, Jetis, Saptosari<br>
                                            Gunungkidul, DIYogyakarta</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                    <h4 class="mb-1">Invoice #{{ $invoice->id }}</h4>
                                    <p class="mb-1">Tanggal: {{ date('d M Y') }}</p>
                                    <p class="mb-2">Jatuh Tempo: {{ date('d M Y', strtotime($invoice->jatuh_tempo)) }}</p>
                                    <span class="badge {{ $invoice->status->id == 8 ? 'bg-success' : 'bg-warning' }}">
                                        {{ $invoice->status->nama_status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <!-- Customer Details -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Tagihan Kepada:</h6>
                                    <p class="mb-1">{{ $invoice->customer->nama_customer }}</p>
                                    <p class="mb-1">{{ $invoice->customer->alamat }}</p>
                                    <p class="mb-1">{{ $invoice->customer->email }}</p>
                                    <p class="mb-0">{{ $invoice->customer->no_hp }}</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <h6 class="text-muted">Detail Tagihan:</h6>
                                    <p class="mb-1">Paket: {{ $invoice->paket->nama_paket }}</p>
                                    <p class="mb-1">Lokasi: {{ $invoice->customer->lokasi->nama_lokasi ?? 'N/A' }}</p>
                                    <p class="mb-0">Periode: {{ date('M Y', strtotime($invoice->created_at)) }}</p>
                                </div>
                            </div>
                            
                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
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
                                    
                                    <!-- Summary -->
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
                                                        <span class="invoice-text">Biaya Tambahan:</span>
                                                        <span class="invoice-value">Rp {{ number_format($invoice->tambahan, 0, ',', '.') }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="invoice-text">Sisa Saldo:</span>
                                                        <span class="invoice-value">- Rp
                                                            {{ number_format($invoice->saldo, 0, ',', '.') }}</span>
                                                        </div>
                                                        <hr class="my-2">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="invoice-text fw-medium">Total:</span>
                                                            <span class="invoice-value fw-semibold">Rp
                                                                {{ number_format($invoice->tagihan + $invoice->tambahan - $invoice->saldo, 0, ',', '.') }}</span>
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
                                    </div>
                                    
                                    <!-- Payment Methods Column -->
                                    <div class="col-lg-4 no-print">
                                        @if ($invoice->status->id != 8)
                                        <div class="sticky-top">
                                            
                                            <!-- Payment Methods -->
                                            <div class="card shadow-sm">
                                                <div class="card-header bg-white">
                                                    <h5 class="card-title mb-0">Pilih Metode Pembayaran</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="payment-methods-list">
                                                        @foreach ($channels as $channel)
                                                        <div class="payment-method border rounded p-3 mb-2" 
                                                        onclick="selectPaymentMethod(this, '{{ $channel['code'] }}')"
                                                        data-code="{{ $channel['code'] }}">
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $channel['icon_url'] }}" 
                                                            alt="{{ $channel['name'] }}"
                                                            class="payment-method-icon me-3">
                                                            <div>
                                                                <div class="fw-semibold">{{ $channel['name'] }}</div>
                                                                <div class="small text-muted">{{ $channel['group'] }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                
                                                <form action="{{ route('tripay.payment', $invoice->id) }}" 
                                                    method="POST" 
                                                    id="payment-form">
                                                    @csrf
                                                    <input type="hidden" name="payment_method" id="payment_method">
                                                    <button type="submit" class="btn btn-success w-100 mt-3" id="pay-button" disabled>
                                                        <i class="bx bx-credit-card"></i> Bayar Sekarang
                                                    </button>
                                                </form>
                                                
                                                <div class="alert alert-warning mt-3 mb-0">
                                                    <strong>Catatan:</strong> Pembayaran akan diproses secara otomatis.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
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
                                    m.style.backgroundColor = '';
                                });
                                
                                // Add active class and visual feedback
                                element.classList.add('active');
                                element.style.backgroundColor = '#e8f5e9';
                                
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
                            
                            // Check if table is scrollable
                            document.addEventListener('DOMContentLoaded', function() {
                                const tables = document.querySelectorAll('.invoice-table');
                                tables.forEach(table => {
                                    if (table.scrollWidth > table.clientWidth) {
                                        table.setAttribute('data-scrollable', 'true');
                                    }
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    