@extends('layouts.contentNavbarLayout')
@section('title', 'Request Perubahan Langganan')

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="fw-bold mb-1">Request Perubahan Langganan</h4>
                            <p class="text-muted mb-0">Silakan pilih jenis perubahan langganan yang Anda inginkan</p>
                        </div>
                        <div class="avatar avatar-lg bg-label-primary p-2">
                            <i class="bx bx-package fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="card">
                <div class="card-body">
                    <form id="subscriptionRequestForm" action="#" method="POST">
                        @csrf

                        <!-- Request Type Selection -->
                        <div class="mb-4">
                            <label class="form-label d-flex align-items-center">
                                <i class='bx bx-transfer-alt me-2 text-primary'></i>
                                Jenis Perubahan
                            </label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="request-type-card" onclick="selectRequestType(this, 'upgrade')">
                                        <div class="icon bg-label-success">
                                            <i class='bx bx-up-arrow-alt'></i>
                                        </div>
                                        <h6>Upgrade Paket</h6>
                                        <p>Tingkatkan kecepatan internet Anda</p>
                                        <input type="radio" name="request_type" value="upgrade" class="d-none">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="request-type-card" onclick="selectRequestType(this, 'downgrade')">
                                        <div class="icon bg-label-warning">
                                            <i class='bx bx-down-arrow-alt'></i>
                                        </div>
                                        <h6>Downgrade Paket</h6>
                                        <p>Turunkan paket langganan Anda</p>
                                        <input type="radio" name="request_type" value="downgrade" class="d-none">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="request-type-card" onclick="selectRequestType(this, 'cancel')">
                                        <div class="icon bg-label-danger">
                                            <i class='bx bx-x'></i>
                                        </div>
                                        <h6>Berhenti Berlangganan</h6>
                                        <p>Batalkan langganan internet Anda</p>
                                        <input type="radio" name="request_type" value="cancel" class="d-none">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Subscription Info -->
                        <div class="mb-4">
                            <label class="form-label d-flex align-items-center">
                                <i class='bx bx-info-circle me-2 text-primary'></i>
                                Informasi Langganan Saat Ini
                            </label>
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="subscription-info-item">
                                            <span class="label">Paket</span>
                                            <span class="value">
                                                @if (isset($customer->paket->nama_paket))
                                                    <span
                                                        class="badge bg-label-primary">{{ $customer->paket->nama_paket }}</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Belum berlangganan</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="subscription-info-item">
                                            <span class="label">Harga</span>
                                            <span class="value">
                                                @if (isset($customer->paket->harga))
                                                    Rp {{ number_format($customer->paket->harga, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="subscription-info-item">
                                            <span class="label">Status</span>
                                            <span class="value">
                                                @if (isset($customer->status->nama_status))
                                                    <span
                                                        class="badge bg-label-success">{{ $customer->status->nama_status }}</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Tidak diketahui</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Package Selection (for upgrade/downgrade) -->
                        <div id="newPackageSection" class="mb-4 d-none">
                            <label for="new_package" class="form-label d-flex align-items-center">
                                <i class='bx bx-package me-2 text-primary'></i>
                                Pilih Paket Baru
                            </label>
                            <select class="form-select" id="new_package" name="new_package">
                                <option value="">Pilih paket baru</option>
                                <option value="1">Paket 5 Mbps - Rp 150.000</option>
                                <option value="2">Paket 10 Mbps - Rp 250.000</option>
                                <option value="3">Paket 20 Mbps - Rp 350.000</option>
                                <option value="4">Paket 50 Mbps - Rp 500.000</option>
                            </select>
                            <div class="form-text mt-1">
                                <i class='bx bx-info-circle me-1'></i>
                                Perubahan paket akan diproses dalam 1-3 hari kerja.
                            </div>
                        </div>

                        <!-- Reason for Request -->
                        <div class="mb-4">
                            <label for="reason" class="form-label d-flex align-items-center">
                                <i class='bx bx-comment-detail me-2 text-primary'></i>
                                Alasan Perubahan
                            </label>
                            <textarea class="form-control" id="reason" name="reason" rows="3"
                                placeholder="Jelaskan alasan Anda mengajukan perubahan langganan ini"></textarea>
                        </div>

                        <!-- Preferred Date (for all types) -->
                        <div class="mb-4">
                            <label for="preferred_date" class="form-label d-flex align-items-center">
                                <i class='bx bx-calendar me-2 text-primary'></i>
                                Tanggal Perubahan yang Diinginkan
                            </label>
                            <input type="date" class="form-control" id="preferred_date" name="preferred_date">
                            <div class="form-text mt-1">
                                <i class='bx bx-info-circle me-1'></i>
                                Pilih tanggal minimal 3 hari dari sekarang.
                            </div>
                        </div>

                        <!-- Confirmation for cancellation -->
                        <div id="cancellationConfirmation" class="mb-4 d-none">
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class='bx bx-error-circle me-2'></i>
                                <div>
                                    <strong>Perhatian!</strong> Dengan berhenti berlangganan, layanan internet Anda akan
                                    dihentikan pada tanggal yang ditentukan.
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="confirm_cancellation"
                                            name="confirm_cancellation">
                                        <label class="form-check-label" for="confirm_cancellation">
                                            Saya mengerti dan ingin melanjutkan pemberhentian langganan
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="window.history.back()">
                                <i class='bx bx-x me-1'></i>
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class='bx bx-send me-1'></i>
                                Kirim Permintaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom styles for the request form */
        .request-type-card {
            padding: 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid #e0e0e0;
            background-color: #fff;
            transition: all 0.2s ease;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .request-type-card:hover {
            border-color: #696cff;
            transform: translateY(-2px);
        }

        .request-type-card.selected {
            border-color: #696cff;
            border-width: 2px;
            box-shadow: 0 0.125rem 0.25rem rgba(105, 108, 255, 0.1);
        }

        .request-type-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .request-type-card .icon i {
            font-size: 1.5rem;
        }

        .request-type-card h6 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .request-type-card p {
            color: #697a8d;
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .subscription-info-item {
            display: flex;
            flex-direction: column;
        }

        .subscription-info-item .label {
            font-size: 0.875rem;
            color: #697a8d;
            margin-bottom: 0.25rem;
        }

        .subscription-info-item .value {
            font-weight: 600;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize form elements
            const form = document.getElementById('subscriptionRequestForm');
            const requestTypeCards = document.querySelectorAll('.request-type-card');
            const newPackageSection = document.getElementById('newPackageSection');
            const cancellationConfirmation = document.getElementById('cancellationConfirmation');

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validate form
                const requestType = document.querySelector('input[name="request_type"]:checked');

                if (!requestType) {
                    alert('Silakan pilih jenis perubahan langganan.');
                    return;
                }

                if (requestType.value === 'cancel') {
                    const confirmCancellation = document.getElementById('confirm_cancellation');
                    if (!confirmCancellation.checked) {
                        alert('Anda harus mengonfirmasi pemberhentian langganan untuk melanjutkan.');
                        return;
                    }
                }

                if ((requestType.value === 'upgrade' || requestType.value === 'downgrade') &&
                    !document.getElementById('new_package').value) {
                    alert('Silakan pilih paket baru.');
                    return;
                }

                if (!document.getElementById('reason').value) {
                    alert('Silakan berikan alasan perubahan langganan.');
                    return;
                }

                if (!document.getElementById('preferred_date').value) {
                    alert('Silakan pilih tanggal perubahan yang diinginkan.');
                    return;
                }

                // If all validations pass, submit the form
                alert(
                    'Permintaan perubahan langganan Anda telah dikirim. Tim kami akan menghubungi Anda segera.'
                );
                // form.submit(); // Uncomment this when backend is ready
            });
        });

        // Function to select request type
        function selectRequestType(element, type) {
            // Remove selected class from all cards
            document.querySelectorAll('.request-type-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selected class to clicked card
            element.classList.add('selected');

            // Check the radio button
            element.querySelector('input[type="radio"]').checked = true;

            // Show/hide sections based on selection
            const newPackageSection = document.getElementById('newPackageSection');
            const cancellationConfirmation = document.getElementById('cancellationConfirmation');

            if (type === 'upgrade' || type === 'downgrade') {
                newPackageSection.classList.remove('d-none');
                cancellationConfirmation.classList.add('d-none');
            } else if (type === 'cancel') {
                newPackageSection.classList.add('d-none');
                cancellationConfirmation.classList.remove('d-none');
            }
        }
    </script>
@endsection
