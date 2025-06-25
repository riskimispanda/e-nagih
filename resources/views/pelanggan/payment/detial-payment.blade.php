@extends('layouts.contentNavbarLayout')

@section('title', 'Detail Pembayaran')

@section('page-style')
    <style>
        /* Modern Payment Detail Styles */
        .container-fluid {
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 1.5rem;
        }

        .card {
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-body {
            border-radius: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .avatar-lg {
            width: 60px;
            height: 60px;
        }

        .avatar-sm {
            width: 32px;
            height: 32px;
        }

        .bg-light-primary {
            background-color: rgba(105, 108, 255, 0.1) !important;
        }

        .bg-light-info {
            background-color: rgba(3, 195, 236, 0.1) !important;
        }

        .bg-light-success {
            background-color: rgba(113, 221, 55, 0.1) !important;
        }

        .bg-light-warning {
            background-color: rgba(255, 171, 0, 0.1) !important;
        }

        .badge {
            border-radius: 8px;
            font-weight: 500;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .table-borderless td {
            padding: 0.75rem 0;
            border: none;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .text-primary {
            color: #696cff !important;
        }

        .text-info {
            color: #03c3ec !important;
        }

        .text-success {
            color: #71dd37 !important;
        }

        .text-warning {
            color: #ffab00 !important;
        }

        .shadow-sm {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }

            .d-flex.gap-2 {
                justify-content: center;
            }

            .col-md-6 {
                margin-bottom: 1rem;
            }

            .avatar-lg {
                width: 50px;
                height: 50px;
            }
        }

        /* Print styles */
        @media print {

            .btn,
            .d-flex.gap-2 {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }

            .container-fluid {
                background-color: white !important;
            }
        }

        /* Loading animation */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Status indicators */
        .bg-success {
            background-color: #71dd37 !important;
        }

        .bg-warning {
            background-color: #ffab00 !important;
        }

        .bg-danger {
            background-color: #ff3e1d !important;
        }

        .bg-info {
            background-color: #03c3ec !important;
        }

        /* Custom spacing */
        .g-3>* {
            padding: 0.75rem;
        }

        /* Enhanced card styling */
        .card-title {
            color: #566a7f;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* Better table styling */
        .table td {
            vertical-align: middle;
        }

        /* Enhanced alert styling */
        .alert-info {
            background-color: rgba(3, 195, 236, 0.1);
            color: #0c7cd5;
            border: 1px solid rgba(3, 195, 236, 0.2);
        }

        /* Improved button spacing */
        .d-grid.gap-2 {
            gap: 0.75rem !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold text-primary mb-1">Detail Pembayaran</h4>
                        <p class="text-muted mb-0">Informasi lengkap transaksi pembayaran</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
                            <i class="bx bx-printer me-1"></i>
                            Cetak
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Payment Details -->
            <div class="col-lg-8 col-md-12 mb-4">
                <!-- Payment Status Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Status Pembayaran</h5>
                            @php
                                $statusClass = 'warning';
                                $statusIcon = 'bx-time';
                                $statusText = 'Menunggu Pembayaran';

                                if (isset($invoice) && $invoice->status_id == 8) {
                                    $statusClass = 'success';
                                    $statusIcon = 'bx-check-circle';
                                    $statusText = 'Pembayaran Berhasil';
                                } elseif (isset($transaction) && isset($transaction['status'])) {
                                    switch ($transaction['status']) {
                                        case 'PAID':
                                            $statusClass = 'success';
                                            $statusIcon = 'bx-check-circle';
                                            $statusText = 'Pembayaran Berhasil';
                                            break;
                                        case 'EXPIRED':
                                            $statusClass = 'danger';
                                            $statusIcon = 'bx-x-circle';
                                            $statusText = 'Pembayaran Kedaluwarsa';
                                            break;
                                        case 'FAILED':
                                            $statusClass = 'danger';
                                            $statusIcon = 'bx-x-circle';
                                            $statusText = 'Pembayaran Gagal';
                                            break;
                                    }
                                }
                            @endphp
                            <span class="badge bg-{{ $statusClass }} fs-6 px-3 py-2">
                                <i class="bx {{ $statusIcon }} me-1"></i>
                                {{ $statusText }}
                            </span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-light-primary rounded me-3">
                                        <i class="bx bx-receipt text-primary"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">No. Referensi</small>
                                        <span
                                            class="fw-medium">{{ $transaction['reference'] ?? ($invoice->reference ?? 'TRX-' . time()) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-light-info rounded me-3">
                                        <i class="bx bx-credit-card text-info"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Metode Pembayaran</small>
                                        <span class="fw-medium">{{ $transaction['payment_method'] ?? 'QRIS' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-light-success rounded me-3">
                                        <i class="bx bx-money text-success"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Total Pembayaran</small>
                                        <span class="fw-bold text-success fs-5">Rp
                                            {{ number_format($invoice->tagihan ?? 150000, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-light-warning rounded me-3">
                                        <i class="bx bx-calendar text-warning"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Tanggal Transaksi</small>
                                        <span
                                            class="fw-medium">{{ isset($transaction['created_at']) ? \Carbon\Carbon::parse($transaction['created_at'])->format('d M Y, H:i') : \Carbon\Carbon::now()->format('d M Y, H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Details Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Detail Transaksi</h5>

                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">No. Invoice</td>
                                        <td class="fw-medium">INV-{{ $invoice->id ?? '001' }}-{{ date('Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Merchant Reference</td>
                                        <td class="fw-medium">
                                            {{ $transaction['merchant_ref'] ?? 'INV-' . ($invoice->id ?? '001') . '-' . time() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Paket Layanan</td>
                                        <td class="fw-medium">{{ $invoice->paket->nama_paket ?? 'Premium 20 Mbps' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Periode Tagihan</td>
                                        <td class="fw-medium">
                                            {{ \Carbon\Carbon::parse($invoice->created_at ?? now())->format('F Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Jatuh Tempo</td>
                                        <td class="fw-medium">
                                            {{ \Carbon\Carbon::parse($invoice->jatuh_tempo ?? now()->addDays(30))->format('d M Y') }}
                                        </td>
                                    </tr>
                                    @if (isset($transaction['expired_time']))
                                        <tr>
                                            <td class="text-muted">Batas Waktu Pembayaran</td>
                                            <td class="fw-medium">
                                                {{ \Carbon\Carbon::createFromTimestamp($transaction['expired_time'])->format('d M Y, H:i') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions (if pending) -->
                @if ((!isset($invoice) || $invoice->status_id != 8) && (!isset($transaction) || $transaction['status'] != 'PAID'))
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">
                                <i class="bx bx-info-circle text-info me-2"></i>
                                Instruksi Pembayaran
                            </h5>

                            <div class="alert alert-info border-0 bg-light-info">
                                <div class="d-flex">
                                    <i class="bx bx-info-circle text-info me-2 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading mb-2">Cara Pembayaran
                                            {{ $transaction['payment_method'] ?? 'QRIS' }}</h6>
                                        <div id="payment-instructions">
                                            @if (isset($transaction['payment_method']) && $transaction['payment_method'] == 'QRIS')
                                                <ol class="mb-0 ps-3">
                                                    <li>Buka aplikasi e-wallet atau mobile banking yang mendukung QRIS</li>
                                                    <li>Pilih menu "Scan QR" atau "Bayar dengan QR"</li>
                                                    <li>Scan kode QR yang tersedia</li>
                                                    <li>Pastikan nominal pembayaran sesuai</li>
                                                    <li>Konfirmasi pembayaran</li>
                                                </ol>
                                            @else
                                                <ol class="mb-0 ps-3">
                                                    <li>Login ke aplikasi atau website bank Anda</li>
                                                    <li>Pilih menu transfer atau pembayaran</li>
                                                    <li>Masukkan nomor virtual account yang tertera</li>
                                                    <li>Pastikan nominal pembayaran sesuai</li>
                                                    <li>Konfirmasi pembayaran</li>
                                                </ol>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (isset($transaction['qr_url']) || isset($transaction['virtual_account']))
                                <div class="text-center mt-4">
                                    @if (isset($transaction['qr_url']))
                                        <div class="mb-3">
                                            <img src="{{ $transaction['qr_url'] }}" alt="QR Code" class="img-fluid"
                                                style="max-width: 200px;">
                                        </div>
                                    @endif

                                    @if (isset($transaction['virtual_account']))
                                        <div class="bg-light p-3 rounded">
                                            <small class="text-muted d-block">Nomor Virtual Account</small>
                                            <h4 class="fw-bold text-primary mb-0">{{ $transaction['virtual_account'] }}
                                            </h4>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 col-md-12">
                <!-- Customer Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Informasi Pelanggan</h5>

                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg bg-primary rounded-circle me-3">
                                <span
                                    class="text-white fw-bold">{{ substr($invoice->customer->nama_customer ?? 'John Doe', 0, 2) }}</span>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $invoice->customer->nama_customer ?? 'John Doe' }}</h6>
                                <small class="text-muted">ID: {{ $invoice->customer->id ?? '001' }}</small>
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <small class="text-muted d-block">Email</small>
                                    <span
                                        class="fw-medium">{{ $invoice->customer->email ?? 'customer@example.com' }}</span>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">No. Telepon</small>
                                    <span
                                        class="fw-medium">{{ $invoice->customer->no_hp ?? ($invoice->customer->no_telp ?? '08123456789') }}</span>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Alamat</small>
                                    <span
                                        class="fw-medium">{{ $invoice->customer->alamat ?? 'Alamat tidak tersedia' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Aksi Cepat</h5>

                        <div class="d-grid gap-2">
                            @if ((!isset($invoice) || $invoice->status_id != 8) && (!isset($transaction) || $transaction['status'] != 'PAID'))
                                @if (isset($transaction['checkout_url']))
                                    <a href="{{ $transaction['checkout_url'] }}" class="btn btn-success btn-lg">
                                        <i class="bx bx-credit-card me-1"></i>
                                        Lanjutkan Pembayaran
                                    </a>
                                @endif

                                @if (env('APP_ENV') !== 'production' && isset($transaction['reference']))
                                    <button type="button" class="btn btn-warning"
                                        onclick="simulatePayment('{{ $transaction['reference'] }}')">
                                        <i class="bx bx-test-tube me-1"></i>
                                        Simulasi Pembayaran (Sandbox)
                                    </button>
                                @endif

                                <button type="button" class="btn btn-primary" onclick="checkPaymentStatus()">
                                    <i class="bx bx-refresh me-1"></i>
                                    Cek Status Pembayaran
                                </button>
                                <a href="{{ route('payment.show', $invoice->id ?? 1) }}" class="btn btn-outline-primary">
                                    <i class="bx bx-credit-card me-1"></i>
                                    Bayar Ulang
                                </a>
                            @endif

                            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="bx bx-printer me-1"></i>
                                Cetak Detail
                            </button>

                            <a href="{{ route('invoice', $invoice->customer->nama_customer ?? 'customer') }}"
                                class="btn btn-outline-info">
                                <i class="bx bx-file-blank me-1"></i>
                                Lihat Invoice
                            </a>

                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection



@section('page-script')
    <script>
        // Check payment status function
        function checkPaymentStatus() {
            const button = event.target;
            const originalText = button.innerHTML;

            // Add loading state
            button.classList.add('btn-loading');
            button.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Mengecek Status...';
            button.disabled = true;

            // Get invoice ID from the current page or URL
            const invoiceId = {{ $invoice->id ?? 1 }};

            // Make AJAX request to check payment status
            fetch(`/payment/check-status/${invoiceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showToast('Status pembayaran berhasil diperbarui!', 'success');

                        // Reload page after a short delay to show updated status
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.message || 'Gagal mengecek status pembayaran', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat mengecek status pembayaran', 'error');
                })
                .finally(() => {
                    // Remove loading state
                    button.classList.remove('btn-loading');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
        }

        // Toast notification function
        function showToast(message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className =
                `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
            <i class="bx bx-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

            // Add to page
            document.body.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 5000);
        }

        // Auto-refresh payment status for pending payments
        document.addEventListener('DOMContentLoaded', function() {
            const statusBadge = document.querySelector('.badge');
            const isPending = statusBadge && (
                statusBadge.textContent.includes('Menunggu') ||
                statusBadge.classList.contains('bg-warning')
            );

            if (isPending) {
                // Check status every 30 seconds for pending payments
                setInterval(() => {
                    checkPaymentStatusSilently();
                }, 30000);
            }
        });

        // Silent status check (no loading indicators)
        function checkPaymentStatusSilently() {
            const invoiceId = {{ $invoice->id ?? 1 }};

            fetch(`/payment/check-status/${invoiceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.status === 'PAID') {
                        // Payment completed, reload page
                        showToast('Pembayaran berhasil! Halaman akan dimuat ulang...', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Silent status check error:', error);
                });
        }

        // Print functionality
        function printPage() {
            window.print();
        }

        // Copy reference number to clipboard
        function copyReference() {
            const referenceText = document.querySelector('[data-reference]');
            if (referenceText) {
                navigator.clipboard.writeText(referenceText.textContent).then(() => {
                    showToast('Nomor referensi berhasil disalin!', 'success');
                });
            }
        }

        // Simulate payment for sandbox testing
        function simulatePayment(reference) {
            if (!reference) {
                showToast('Reference tidak ditemukan', 'error');
                return;
            }

            const button = event.target;
            const originalText = button.innerHTML;

            // Add loading state
            button.classList.add('btn-loading');
            button.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Memproses Simulasi...';
            button.disabled = true;

            // Make AJAX request to simulate payment
            fetch(`/payment/simulate-by-reference/${reference}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Simulasi pembayaran berhasil! Halaman akan dimuat ulang...', 'success');

                        // Reload page after a short delay to show updated status
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showToast(data.message || 'Gagal melakukan simulasi pembayaran', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat melakukan simulasi pembayaran', 'error');
                })
                .finally(() => {
                    // Remove loading state
                    button.classList.remove('btn-loading');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
        }

        // Enhanced responsive behavior
        function handleResize() {
            const isMobile = window.innerWidth < 768;
            const cards = document.querySelectorAll('.card');

            cards.forEach(card => {
                if (isMobile) {
                    card.style.marginBottom = '1rem';
                } else {
                    card.style.marginBottom = '';
                }
            });
        }

        // Listen for window resize
        window.addEventListener('resize', handleResize);

        // Initial call
        handleResize();
    </script>
@endsection
