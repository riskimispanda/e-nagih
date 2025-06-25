@extends('layouts.contentNavbarLayout')
@section('title', 'Detail Antrian')

@section('vendor-style')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-light: rgba(105, 108, 255, 0.1);
            --primary-lighter: rgba(105, 108, 255, 0.05);
            --secondary-color: #8592a3;
            --success-color: #71dd37;
            --info-color: #03c3ec;
            --warning-color: #ffab00;
            --danger-color: #ff3e1d;
            --dark-color: #233446;
            --light-color: #f9fafb;
            --border-color: #eaeaec;
            --text-color: #566a7f;
            --text-muted: #a1acb8;
            --body-bg: #f5f5f9;
        }

        /* Layout & Container */
        .page-container {
            background-color: var(--body-bg);
            border-radius: 1rem;
            padding: 0.5rem;
            margin-bottom: 1.5rem;
        }

        /* Minimalist Card styles */
        .card {
            border-radius: 0.75rem;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.05) !important;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }

        .card-header-elements {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .card-title {
            margin-bottom: 0;
            font-weight: 600;
            color: #566a7f;
        }

        .card-subtitle {
            color: #a1acb8;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        /* Avatar styles */
        .avatar-md {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-light);
            color: var(--primary-color);
            border-radius: 4px;
            font-size: 1.25rem;
        }

        /* Info section styles */
        .info-section {
            padding: 1.25rem;
        }

        .info-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .info-value {
            font-weight: 400;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        /* Badge styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-badge i {
            margin-right: 0.375rem;
            font-size: 0.875rem;
        }

        .status-badge.badge-waiting {
            background-color: rgba(255, 171, 0, 0.1);
            color: #ffab00;
        }

        .status-badge.badge-active {
            background-color: rgba(113, 221, 55, 0.1);
            color: #71dd37;
        }

        .status-badge.badge-inactive {
            background-color: rgba(133, 146, 163, 0.1);
            color: #8592a3;
        }

        /* Button styles */
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: var(--text-color);
            background-color: white;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }

        .btn-action i {
            font-size: 1rem;
        }

        .btn-maps {
            color: #03c3ec;
            border-color: rgba(3, 195, 236, 0.2);
        }

        .btn-maps:hover {
            background-color: rgba(3, 195, 236, 0.1);
            color: #03c3ec;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-buttons .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 400;
            border-radius: 4px;
        }

        .action-buttons .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }

        .action-buttons .btn-outline-secondary {
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }

        .action-buttons .btn i {
            font-size: 1rem;
        }

        /* Alert styles */
        .alert {
            border-radius: 4px;
            border: none;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: rgba(113, 221, 55, 0.1);
            color: #2b7d0a;
        }

        .alert-danger {
            background-color: rgba(255, 62, 29, 0.1);
            color: #b42318;
        }

        /* Modal styles */
        .modal-content {
            border-radius: 4px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem;
            background-color: #f8f9fa;
        }

        .modal-title {
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .modal-body {
            padding: 1rem;
        }

        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem;
        }

        /* Form styles */
        .form-label {
            font-weight: 400;
            color: var(--text-color);
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .form-control,
        .form-select {
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            font-size: 0.875rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }

        .form-text {
            color: var(--text-muted);
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        /* Page header */
        .page-header {
            margin-bottom: 1rem;
        }

        .page-title {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
            font-size: 1.25rem;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .card-header-elements {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-buttons {
                margin-top: 1rem;
                width: 100%;
            }

            .action-buttons .btn {
                flex: 1;
            }
        }
    </style>
@endsection

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item"><a href="/helpdesk/data-antrian">Data Antrian Helpdesk</a></li>
        <li class="breadcrumb-item">Detail</li>
    </ol>
</nav>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-elements">
                        <div>
                            <h5 class="card-title">Detail Antrian</h5>
                            <p class="card-subtitle">Informasi Antrian Pelanggan {{$corp->nama_perusahaan}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Company Information Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Informasi Perusahaan</h5>
                </div>
                <div class="info-section">
                    <div class="info-item">
                        <div class="info-label">Nama Perusahaan</div>
                        <div class="info-value">{{ $corp->nama_perusahaan }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $corp->email ?? 'Tidak tersedia' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nomor HP</div>
                        <div class="info-value">
                            {{ $corp->no_hp }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Alamat</div>
                        <div class="info-value">{{ $corp->alamat }}</div>
                    </div>
                    <div class="info-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="info-label">Titik Lokasi</div>
                                <div class="info-value">Lihat di Google Maps</div>
                            </div>
                            <a href="{{ $corp->gps }}" target="_blank" class="btn btn-action btn-maps">
                                <i class="bx bx-map"></i>
                            </a>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nomor Identitas</div>
                        <div class="info-value">{{ $corp->no_identitas ?? 'Tidak tersedia' }}</div>
                    </div>
                    @if ($corp->identitas)
                        <div class="info-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="info-label">Foto KTP</div>
                                    <div class="info-value">Lihat foto identitas</div>
                                </div>
                                <a href="{{ asset($corp->identitas) }}" target="_blank" class="btn btn-action">
                                    <i class="bx bx-image"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Service Information Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Informasi Layanan</h5>
                </div>
                <div class="info-section">
                    <div class="info-item">
                        <div class="info-label">Paket Layanan</div>
                        <div class="info-value">
                            {{ $corp->paket->nama_paket ?? 'Belum ada paket' }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @php
                                $statusClass = 'badge-waiting';
                                $statusIcon = 'bx-time';

                                if ($corp->status) {
                                    if (strtolower($corp->status->nama_status) == 'aktif') {
                                        $statusClass = 'badge-active';
                                        $statusIcon = 'bx-check-circle';
                                    } elseif (strtolower($corp->status->nama_status) == 'tidak aktif') {
                                        $statusClass = 'badge-inactive';
                                        $statusIcon = 'bx-x-circle';
                                    }
                                }
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                <i class="bx {{ $statusIcon }}"></i>
                                {{ $corp->status->nama_status ?? 'Menunggu' }}
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tanggal Pendaftaran</div>
                        <div class="info-value">{{ $corp->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Petugas Input Data</div>
                        <div class="info-value">
                            {{ $corp->usr->name ?? 'Belum ditugaskan' }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Teknisi</div>
                        <div class="info-value">
                            {{ $corp->teknisi->name ?? 'Menunggu Assigment NOC' }}
                        </div>
                    </div>
                    @if ($corp->lokasi)
                        <div class="info-item">
                            <div class="info-label">Lokasi</div>
                            <div class="info-value">{{ $corp->lokasi->nama_lokasi }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-end">
                <div class="action-buttons">
                    <a href="/helpdesk/data-antrian" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back"></i>
                        <span>Kembali</span>
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEdit">
                        <i class="bx bx-edit"></i>
                        <span>Edit Data</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
