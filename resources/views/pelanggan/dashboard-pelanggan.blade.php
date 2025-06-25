@extends('layouts/contentNavbarLayout')
@section('title', 'Dashboard Pelanggan')

@section('vendor-style')
    <style>
        /* Custom styles for dashboard */
        .welcome-card {
            border: none;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
        }

        .welcome-card .card-body {
            background: linear-gradient(135deg, #696cff 0%, #8897ff 100%);
            padding: 2rem;
        }

        .info-card {
            border: none;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 0.125rem 0.5rem rgba(161, 172, 184, 0.2);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(161, 172, 184, 0.3);
        }

        .info-card .card-body {
            padding: 1.5rem;
        }

        .info-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            margin-right: 1rem;
            background-color: rgba(105, 108, 255, 0.16);
        }

        .info-icon i {
            font-size: 1.5rem;
            color: #696cff;
        }

        .info-item {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
            background-color: #f8f9fa;
        }

        .info-item:hover {
            background-color: #f0f1f5;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-size: 0.75rem;
            color: #8592a3;
            margin-bottom: 0.25rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-weight: 600;
            color: #566a7f;
            font-size: 0.95rem;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 0.375rem;
            font-weight: 500;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
        }

        .quick-action {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 1.25rem;
            border-radius: 0.5rem;
            background-color: #fff;
            border: 1px solid #eaeaec;
            transition: all 0.2s ease;
            text-align: center;
            height: 100%;
        }

        .quick-action:hover {
            background-color: rgba(105, 108, 255, 0.08);
            border-color: rgba(105, 108, 255, 0.5);
            transform: translateY(-3px);
        }

        .quick-action i {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            color: #696cff;
        }

        .quick-action span {
            font-weight: 500;
            color: #566a7f;
        }

        @media (max-width: 767.98px) {
            .welcome-card .card-body {
                padding: 1.5rem;
            }

            .info-card .card-body {
                padding: 1.25rem;
            }
        }

        /* Custom styles for payment methods */
        .payment-method-item {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .payment-method-item:hover {
            border-color: #696cff !important;
            background-color: rgba(105, 108, 255, 0.08);
        }

        .payment-method-item.border-primary {
            border-width: 2px !important;
        }

        /* Payment Details Styles */
        #backToMethodsBtn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        #selectedPaymentLogo img {
            max-height: 30px;
            max-width: 100px;
            object-fit: contain;
        }

        .payment-details-header {
            position: relative;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
        }

        .payment-summary-card {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        /* Invoice Summary Styles */
        .invoice-summary {
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 0.125rem 0.375rem rgba(161, 172, 184, 0.12);
            padding: 1.5rem;
            border: 1px solid rgba(105, 108, 255, 0.15);
            position: relative;
            overflow: hidden;
        }

        .invoice-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(180deg, #696cff 0%, #8897ff 100%);
            border-radius: 4px 0 0 4px;
        }

        .invoice-header {
            position: relative;
            border-bottom: 1px dashed rgba(105, 108, 255, 0.2);
            padding-bottom: 1rem;
        }

        .invoice-number {
            margin-top: 0.5rem;
        }

        .invoice-details {
            padding-top: 0.5rem;
        }

        .invoice-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .invoice-item:last-child {
            border-bottom: none;
        }

        .invoice-label {
            color: #8592a3;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }

        .invoice-value {
            color: #566a7f;
        }

        .invoice-total {
            border-top: 2px solid rgba(105, 108, 255, 0.2);
        }

        .invoice-total-label {
            font-weight: 600;
            color: #566a7f;
        }

        .invoice-total-value {
            font-size: 1.25rem;
        }

        /* Highlight effect for real-time updates */
        .bg-light-primary {
            background-color: rgba(105, 108, 255, 0.16) !important;
            animation: pulse-highlight 2s ease;
        }

        @keyframes pulse-highlight {
            0% {
                background-color: rgba(105, 108, 255, 0.05);
            }

            50% {
                background-color: rgba(105, 108, 255, 0.2);
            }

            100% {
                background-color: rgba(105, 108, 255, 0);
            }
        }

        @media (max-width: 576px) {
            .invoice-summary {
                padding: 1.25rem;
            }

            .invoice-label,
            .invoice-value {
                font-size: 0.8125rem;
            }

            .invoice-total-value {
                font-size: 1.125rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <!-- Welcome Card -->
        <div class="col-12 mb-4">
            <div class="welcome-card card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-white mb-1">Selamat Datang, {{ Auth::user()->name }}! 👋</h4>
                            <p class="text-white text-opacity-75 mb-0">Kelola informasi langganan Anda dengan mudah
                                menggunakan <strong>E-Nagih</strong></p>
                        </div>
                        <div class="col-md-4 d-none d-md-block text-end">
                            <img src="{{ asset(Auth::user()->profile) ?? asset('assets/img/avatars/default.png') }}"
                                height="140" alt="View Badge User"
                                data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                data-app-light-img="illustrations/man-with-laptop-light.png"
                                style="border-radius: 50%; object-fit: cover; width: 140px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-12 mb-4">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Aksi Cepat</h5>
                    <hr class="my-2 mb-5">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <a href="{{ url('/customer/pengaduan') }}" class="quick-action">
                                <i class="bx bx-message-square-dots"></i>
                                <span>Buat Pengaduan</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="/data/invoice/{{ $customer->nama_customer }}" class="quick-action">
                                <i class="bx bx-credit-card"></i>
                                <span>Bayar Tagihan</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ url('/customer/history') }}" class="quick-action">
                                <i class="bx bx-history"></i>
                                <span>Riwayat Transaksi</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="#" class="quick-action">
                                <i class="bx bx-help-circle"></i>
                                <span>Bantuan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="col-md-6 mb-4">
            <div class="card info-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="info-icon">
                            <i class="bx bx-user"></i>
                        </div>
                        <h5 class="card-title mb-0">Informasi Pelanggan</h5>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Nama</div>
                        <div class="info-value">{{ Auth::user()->name }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ Auth::user()->email }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Bergabung Sejak</div>
                        <div class="info-value">{{ Auth::user()->created_at->format('d M Y') }}</div>
                    </div>

                    <div class="mt-3 text-end">
                        <a href="#" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-edit me-1"></i> Edit Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Info Card -->
        <div class="col-md-6 mb-4">
            <div class="card info-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="info-icon">
                            <i class="bx bx-package"></i>
                        </div>
                        <h5 class="card-title mb-0">Informasi Langganan</h5>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Paket</div>
                        <div class="info-value mt-2" id="paket-container">
                            @if (isset($invoice->paket->nama_paket))
                                <span class="badge bg-label-primary me-1">{{ $invoice->paket->nama_paket }}</span>
                            @else
                                <span class="badge bg-label-secondary">Belum berlangganan</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value mt-2" id="status-container">
                            @if (isset($customer->status))
                                @if ($customer->status_id == 1)
                                    <span class="badge bg-label-warning">
                                        <i class="bx bx-time me-1"></i>Menunggu
                                    </span>
                                @elseif ($customer->status_id == 2)
                                    <span class="badge bg-label-info">
                                        <i class="bx bx-loader me-1"></i>On Progress
                                    </span>
                                @elseif ($customer->status_id == 3)
                                    <span class="badge bg-label-success">
                                        <i class="bx bx-check me-1"></i>Aktif
                                    </span><br>
                                    @if (isset($invoice->jatuh_tempo))
                                        @php
                                            $jatuhTempo = \Carbon\Carbon::parse($invoice->jatuh_tempo);
                                            $today = \Carbon\Carbon::now();
                                            // diffInDays hanya menghitung selisih hari tanpa memperhatikan masa depan/lampau
                                            // Gunakan diffInDays dengan parameter true untuk mendapatkan selisih hari yang tepat
                                            $diffInDays = $today->diffInDays($jatuhTempo, false);
                                            $isJatuhTempoMendatang = $jatuhTempo->greaterThan($today);
                                        @endphp
                                        @if ($diffInDays > 0 && $diffInDays <= 5)
                                            <small class="text-muted">*Akan di blokir pada tanggal
                                                {{ $jatuhTempo->format('d M Y') }}</small>
                                        @endif
                                    @endif
                                @elseif($customer->status_id == 5)
                                    <span class="badge bg-label-info">
                                        <i class="bx bx-loader me-1"></i>Assigment
                                    </span>
                                @elseif($customer->status_id == 9)
                                    <span class="badge bg-label-danger">
                                        <i class="bx bx-x me-1"></i>Blokir
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-label-secondary">
                                    <i class="bx bx-minus me-1"></i>Belum Berlangganan
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            Status Tagihan
                        </div>
                        <div class="info-value d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center mt-2">
                                @if (isset($invoice->status->nama_status))
                                    <span class="badge bg-label-warning">
                                        <i class="bx bx-x me-1"></i>{{ $invoice->status->nama_status }}
                                    </span>
                                @else
                                    <span class="badge bg-label-danger">
                                        <i class="bx bx-minus me-1"></i>Belum Berlangganan
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Jatuh Tempo</div>
                        <div class="info-value mt-2">
                            {{ isset($invoice->jatuh_tempo) ? \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d M Y') : '-' }}
                        </div>
                    </div>

                    <div class="mt-3 text-end">
                        <a href="{{ url('/customer/request') }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-refresh me-1"></i> Request Downgrade / Upgrade Langganan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Summary -->
        <div class="col-12 mb-4">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Ringkasan Penggunaan</h5>
                    <hr class="my-2 mb-5">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-wifi text-primary me-2"></i>
                                        <span>Penggunaan Bandwidth</span>
                                    </div>
                                    <small class="text-muted">75%</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" style="width: 75%;" aria-valuenow="75"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted d-block mt-1">75 GB dari 100 GB</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-time text-primary me-2"></i>
                                        <span>Masa Aktif</span>
                                    </div>
                                    <small class="text-muted">40%</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" style="width: 40%;" aria-valuenow="40"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted d-block mt-1">12 hari tersisa dari 30 hari</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Log when Echo connection is established
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing Echo connection...');

            // Check if Echo is properly initialized
            if (typeof Echo !== 'undefined') {
                console.log('Echo is available and ready to use');

                // Set up the channel and event listener
                Echo.window('updates-data')
                    .listen('.data.updated', function(e) {
                        console.log('Received update:', e);

                        // Function to add highlight effect to updated elements
                        const highlightUpdate = (element) => {
                            if (!element) return;

                            // Remove any existing highlight class first (in case of multiple rapid updates)
                            element.classList.remove('bg-light-primary');

                            // Force a reflow to ensure the animation runs again
                            void element.offsetWidth;

                            // Add highlight class
                            element.classList.add('bg-light-primary');

                            // No need to manually remove the class as the animation will fade out
                            // But we'll still remove it after animation completes for cleanliness
                            setTimeout(() => {
                                element.classList.remove('bg-light-primary');
                            }, 2000);
                        };

                        // Update package information if available
                        if (e.paket) {
                            const paketContainer = document.getElementById('paket-container');
                            if (paketContainer) {
                                if (e.paket.nama_paket) {
                                    paketContainer.innerHTML =
                                        `<span class="badge bg-label-primary me-1">${e.paket.nama_paket}</span>`;
                                } else {
                                    paketContainer.innerHTML =
                                        `<span class="badge bg-label-secondary">Belum berlangganan</span>`;
                                }

                                // Highlight the updated element
                                highlightUpdate(paketContainer.closest('.info-item'));
                            }
                        }

                        // Update status information if available
                        if (e.customer && typeof e.customer.status_id !== 'undefined') {
                            const statusContainer = document.getElementById('status-container');
                            if (statusContainer) {
                                let statusHTML = '';

                                if (e.customer.status_id == 1) {
                                    statusHTML = `
                                        <span class="badge bg-label-warning">
                                            <i class="bx bx-time me-1"></i>Menunggu
                                        </span>
                                    `;
                                } else if (e.customer.status_id == 2) {
                                    statusHTML = `
                                        <span class="badge bg-label-info">
                                            <i class="bx bx-loader me-1"></i>On Progress
                                        </span>
                                    `;
                                } else if (e.customer.status_id == 3) {
                                    statusHTML = `
                                        <span class="badge bg-label-success">
                                            <i class="bx bx-check me-1"></i>Aktif
                                        </span>
                                    `;

                                    // Add jatuh tempo information if available
                                    if (e.invoice && e.invoice.jatuh_tempo) {
                                        const jatuhTempo = new Date(e.invoice.jatuh_tempo);
                                        const today = new Date();
                                        const diffDays = Math.ceil((jatuhTempo - today) / (1000 * 60 * 60 *
                                            24));

                                        if (diffDays <= 0) {
                                            statusHTML += `<br><span class="badge bg-label-danger mt-1">
                                                <i class="bx bx-error-circle me-1"></i>Jatuh Tempo
                                            </span>`;
                                        } else if (diffDays <= 3) {
                                            statusHTML += `<br><span class="badge bg-label-warning mt-1">
                                                <i class="bx bx-calendar-exclamation me-1"></i>Jatuh Tempo ${diffDays} Hari Lagi
                                            </span>`;
                                        } else {
                                            statusHTML += `<br><small class="text-muted">
                                                Jatuh tempo: ${jatuhTempo.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}
                                            </small>`;
                                        }
                                    }
                                }

                                statusContainer.innerHTML = statusHTML;

                                // Highlight the updated element
                                highlightUpdate(statusContainer.closest('.info-item'));
                            }
                        }

                        // Show notification to user
                        if (e.message) {
                            // Check if we have Toastr or other notification library
                            if (typeof toastr !== 'undefined') {
                                toastr.info(e.message, 'Pembaruan Data');
                            } else {
                                // Create a simple notification
                                const notification = document.createElement('div');
                                notification.className = 'alert alert-info alert-dismissible fade show';
                                notification.innerHTML = `
                                    <i class="bx bx-info-circle me-1"></i>
                                    ${e.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                `;

                                // Insert at the top of the page
                                const container = document.querySelector('.container-xxl');
                                if (container && container.firstChild) {
                                    container.insertBefore(notification, container.firstChild);

                                    // Auto remove after 5 seconds
                                    setTimeout(() => {
                                        notification.remove();
                                    }, 5000);
                                }
                            }
                        }
                    })
                    .subscribed(() => {
                        console.log('Successfully subscribed to updates-data channel');
                    })
                    .error((error) => {
                        console.error('Error with Echo connection:', error);
                    });
            } else {
                console.error('Echo is not available. Check Laravel Echo configuration.');
            }
        });
    </script>
@endsection
