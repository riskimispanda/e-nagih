<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Terisolir</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --danger-color: #dc2626;
            --warning-color: #f59e0b;
            --success-color: #059669;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--gray-50);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: var(--gray-800);
            margin: 0;
            padding: 0;
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content-wrapper {
            flex: 1;
            padding: 2rem 1rem;
        }

        .isolir-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .isolir-header {
            background: var(--danger-color);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .isolir-header .icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .isolir-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
            letter-spacing: -0.025em;
        }

        .isolir-header p {
            font-size: 1.125rem;
            margin: 0;
            opacity: 0.9;
        }

        .alert-modern {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-modern .icon {
            color: var(--warning-color);
            font-size: 1.25rem;
        }

        .info-section {
            padding: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title .icon {
            width: 40px;
            height: 40px;
            background: var(--gray-100);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.125rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .info-card {
            background: var(--gray-50);
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid var(--gray-200);
            width: 100%;
        }

        .detail-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--gray-600);
            font-weight: 500;
        }

        .detail-value {
            font-weight: 600;
            color: var(--gray-900);
        }

        .detail-value.danger {
            color: var(--danger-color);
        }

        .payment-steps {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }

        .payment-step {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .step-number {
            width: 24px;
            height: 24px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .payment-methods {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            color: var(--gray-700);
        }

        .payment-method .icon {
            color: var(--primary-color);
            width: 16px;
        }

        .btn-primary {
            background: var(--primary-color);
            border: 1px solid var(--primary-color);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            transition: background-color 0.2s ease;
        }

        .btn-primary:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        .contact-section {
            padding: 1.5rem;
        }

        .contact-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .contact-item:last-child {
            border-bottom: none;
        }

        .contact-icon {
            width: 48px;
            height: 48px;
            background: var(--gray-100);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .contact-info h5 {
            margin: 0 0 0.25rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .contact-info p {
            margin: 0;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .footer {
            background: white;
            border-top: 1px solid var(--gray-200);
            padding: 1.5rem 0;
            text-align: center;
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .text-center {
            text-align: center;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        @media (max-width: 767px) {
            .content-wrapper {
                padding: 1rem;
            }

            .isolir-header {
                padding: 1.5rem;
            }

            .info-section {
                padding: 1.5rem;
            }

            .isolir-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="content-wrapper">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- Main Isolir Card -->
                        <div class="isolir-card">
                            <div class="isolir-header">
                                <div class="icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h1>Layanan Internet Anda Terisolir</h1>
                                <p>Mohon maaf atas ketidaknyamanan ini</p>
                            </div>

                            <div class="info-section">
                                <div class="alert-modern">
                                    <i class="fas fa-info-circle icon"></i>
                                    <div>
                                        <strong>Status Layanan:</strong> Terisolir karena pembayaran tertunggak
                                    </div>
                                </div>
                                <!-- Payment Instructions -->
                                <div class="info-card">
                                    <div class="section-title">
                                        <div class="icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        Cara Pembayaran
                                    </div>
                                    <ul class="payment-steps">
                                        <li class="payment-step">
                                            <div class="step-number">1</div>
                                            <div>
                                                <div>Lakukan pembayaran melalui metode berikut:</div>
                                                <ul class="payment-methods">
                                                    <li class="payment-method">
                                                        <i class="fas fa-university icon"></i>
                                                        Transfer Bank
                                                    </li>
                                                    <li class="payment-method">
                                                        <i class="fas fa-store icon"></i>
                                                        Minimarket
                                                    </li>
                                                    <li class="payment-method">
                                                        <i class="fas fa-wallet icon"></i>
                                                        E-Wallet
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="payment-step">
                                            <div class="step-number">2</div>
                                            <div>Konfirmasi pembayaran Anda</div>
                                        </li>
                                        <li class="payment-step">
                                            <div class="step-number">3</div>
                                            <div>Layanan akan aktif kembali dalam 1x24 jam</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Card -->
                        <div class="isolir-card">
                            <div class="contact-section">
                                <div class="section-title">
                                    <div class="icon">
                                        <i class="fas fa-headset"></i>
                                    </div>
                                    Butuh Bantuan?
                                </div>

                                <ul class="contact-list">
                                    <li class="contact-item">
                                        <div class="contact-icon">
                                            <i class="fas fa-phone-alt"></i>
                                        </div>
                                        <div class="contact-info">
                                            <h5>Hubungi Call Center</h5>
                                            <p>{{ $contact->phone ?? '0800-1234-5678' }} (24/7)</p>
                                        </div>
                                    </li>
                                    <li class="contact-item">
                                        <div class="contact-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="contact-info">
                                            <h5>Email</h5>
                                            <p>{{ $contact->email ?? 'support@example.com' }}</p>
                                        </div>
                                    </li>
                                    <li class="contact-item">
                                        <div class="contact-icon">
                                            <i class="fab fa-whatsapp"></i>
                                        </div>
                                        <div class="contact-info">
                                            <h5>WhatsApp</h5>
                                            <p>{{ $contact->whatsapp ?? '+62 812-3456-7890' }}</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                © {{ date('Y') }} {{ $company->name ?? 'E-Nagih' }}. Hak Cipta Dilindungi.
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Login Berhasil',
            text: '{{ session('success') }}',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            topLayer: true,
            animation: true
        });
    @endif

    @if (session('toast_success'))
        Swal.fire({
            icon: 'success',
            toast: true,
            position: 'top-end',
            text: '{{ session('toast_success') }}',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            showCloseButton: true,
            customClass: {
                popup: 'colored-toast mini-toast',
            },
            background: 'white',
            opacity: 0.8,
            color: '#000000',
            topLayer: true,
            animation: true
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            topLayer: true,
            animation: true
        });
    @endif

    @if (session('toast_error'))
        Swal.fire({
            icon: 'error',
            toast: true,
            position: 'top-end',
            text: '{{ session('toast_error') }}',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            topLayer: true,
            animation: true
        });
    @endif
</script>
