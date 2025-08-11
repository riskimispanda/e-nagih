@extends('layouts.contentNavbarLayout')

@section('title', 'Detail Pelanggan')

@section('vendor-style')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-light: rgba(105, 108, 255, 0.1);
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

        /* Avatar styles */
        .avatar-md {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-light);
            color: var(--primary-color);
            border-radius: 50%;
            font-size: 1.25rem;
        }

        /* Card styles */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            box-shadow: 0 0.125rem 0.25rem rgba(161, 172, 184, 0.15);
            margin-bottom: 1.5rem;
            background-color: #fff;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
        }

        .card-title {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 1.25rem;
        }

        .card-subtitle {
            color: var(--text-muted);
            font-size: 0.8125rem;
            margin-top: 0.25rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        .card-footer {
            background-color: transparent;
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
        }

        /* Inner card styles */
        .inner-card {
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            box-shadow: none;
            height: 100%;
            background-color: #fff;
        }

        .inner-card .card-body {
            padding: 1rem;
        }

        .inner-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        /* Custom tab styles */
        .custom-tab-nav {
            border-radius: 0.75rem;
            overflow: hidden;
            background-color: var(--body-bg);
            border: none;
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.08);
        }

        .custom-tab-nav .nav-link {
            border-radius: 0;
            padding: 1rem 1.5rem;
            font-weight: 500;
            color: var(--text-color);
            border: none;
            transition: all 0.2s ease;
        }

        .custom-tab-nav .nav-link.active {
            background-color: #fff;
            color: var(--primary-color);
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
        }

        .custom-tab-nav .nav-link i {
            font-size: 1.25rem;
            margin-right: 0.5rem;
        }

        /* Info item styles */
        .info-item {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            background-color: var(--light-color);
            transition: all 0.2s ease;
        }

        .info-item:hover {
            background-color: #f0f1f5;
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        /* Badge styles */
        .badge {
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 0.375rem;
        }

        .bg-label-primary {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }

        .bg-label-warning {
            background-color: rgba(255, 171, 0, 0.1);
            color: var(--warning-color);
        }

        /* Form styles */
        .form-control,
        .form-select {
            border-radius: 0.375rem;
            padding: 0.5rem 0.875rem;
            border-color: var(--border-color);
            color: var(--text-color);
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem var(--primary-light);
        }

        .form-label {
            color: var(--text-color);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        /* Button styles */
        .btn {
            border-radius: 0.375rem;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #5f62e6;
            border-color: #5f62e6;
        }

        .btn-outline-secondary {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-outline-secondary:hover {
            background-color: var(--secondary-color);
            color: #fff;
        }

        /* Progress indicator */
        .progress-indicator {
            display: flex;
            margin-bottom: 2rem;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .progress-step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 1rem;
            width: 100%;
            height: 2px;
            background-color: var(--border-color);
            left: 50%;
            z-index: 0;
        }

        .progress-step.active:not(:last-child):after {
            background-color: var(--primary-color);
        }

        .step-icon {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: var(--border-color);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            position: relative;
            z-index: 1;
        }

        .progress-step.active .step-icon {
            background-color: var(--primary-color);
            color: #fff;
        }

        .step-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .progress-step.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Responsive adjustments */
        @media (max-width: 991.98px) {

            .card-body,
            .card-header,
            .card-footer {
                padding: 1.25rem;
            }

            .inner-card .card-body {
                padding: 2rem;
            }

            .custom-tab-nav .nav-link {
                padding: 0.75rem 1rem;
            }

            h5.card-title {
                font-size: 1rem;
            }

            .d-flex.align-items-center.justify-content-between {
                flex-direction: column;
                align-items: flex-start !important;
            }

            nav[aria-label="breadcrumb"] {
                margin-top: 0.5rem;
            }

            .breadcrumb {
                font-size: 0.75rem;
            }
        }

        @media (max-width: 767.98px) {

            .card-body,
            .card-header,
            .card-footer {
                padding: 1rem;
            }

            .inner-card .card-body {
                padding: 0.875rem;
            }

            .info-item {
                padding: 0.75rem;
            }

            .info-value {
                font-size: 0.875rem;
                word-break: break-word;
            }

            .btn {
                padding: 0.4rem 1rem;
            }

            .avatar-md {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }

            .custom-tab-nav .nav-link i {
                display: none;
            }

            .inner-card-header {
                margin-bottom: 1rem;
                padding-bottom: 0.5rem;
            }
        }

        @media (max-width: 575.98px) {
            .card-footer {
                flex-direction: column;
                gap: 0.5rem;
            }

            .card-footer .btn {
                width: 100%;
                justify-content: center;
            }

            .progress-indicator {
                flex-direction: column;
                gap: 1rem;
            }

            .progress-step:not(:last-child):after {
                width: 2px;
                height: 1.5rem;
                top: 2rem;
                left: 50%;
                transform: translateX(-50%);
            }
        }
    </style>
@endsection

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/teknisi/antrian">Antrian Instalasi</a></li>
        <li class="breadcrumb-item active">Detail Pelanggan</li>
    </ol>
</nav>
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Detail Pelanggan</h5>
                <small class="card-subtitle text-muted">Informasi lengkap pelanggan</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row g-4">
                    <!-- data Information -->
                    <div class="col-12 col-sm-6">
                        <div class="inner-card">
                            <div class="card-body">
                                <div class="inner-card-header">
                                    <div class="avatar-md me-3">
                                        <i class='bx bx-user'></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Data Pelanggan</h6>
                                        <small class="text-muted">Informasi pribadi pelanggan</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class='bx bx-user me-2'></i>Nama
                                                Pelanggan</label>
                                            <input type="text" class="form-control"
                                                value="{{ $data->nama_customer }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class='bx bx-phone me-2'></i>Nomor
                                                Telepon</label>
                                            <input type="text" class="form-control" value="{{ $data->no_hp }}"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class='bx bx-id-card me-2'></i>Nomor
                                                Identitas</label>
                                            <input type="text" class="form-control"
                                                value="{{ $data->no_identitas }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class='bx bx-home me-2'></i>Alamat</label>
                                            <input type="text" class="form-control" value="{{ $data->alamat }}"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups col-sm-4">
                                            <label class="form-label"><i class='bx bx-image me-2'></i>Foto
                                                Identitas</label>
                                            <a href="{{ asset($data->identitas) }}" target="_blank"
                                                class="btn btn-outline-primary btn-sm form-control">
                                                <i class='bx bx-image'></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups col-sm-4">
                                            <label class="form-label"><i class='bx bx-map-pin me-2'></i>Titik
                                                Lokasi</label>
                                            <a href="{{ asset($data->gps) }}" target="_blank"
                                                class="btn btn-outline-primary btn-sm form-control">
                                                <i class='bx bx-map'></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="input-groups">
                                            <label class="form-label"><i class='bx bx-calendar me-2'></i>Tanggal
                                                Registrasi</label>
                                            <input type="text" class="form-control"
                                                value="{{ $data->created_at->format('d F Y, H:i') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="col-12 col-sm-6">
                        <div class="inner-card">
                            <div class="card-body">
                                <div class="inner-card-header">
                                    <div class="avatar-md me-3">
                                        <i class='bx bx-link'></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Konfigurasi Awal</h6>
                                        <small class="text-muted">Informasi Konfigurasi Awal</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class="bx bx-network-chart me-2"></i>Router</label>
                                            <input type="text" class="form-control" value="{{ $data->router->nama_router }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class="bx bx-link-alt me-2"></i>Jenis Koneksi</label>
                                            <input type="text" class="form-control" value="{{ $data->koneksi->nama_koneksi }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class="bx bx-lock me-2"></i>User Secret</label>
                                            <input type="text" class="form-control" value="{{ $data->usersecret }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class="bx bx-key me-2"></i>Password</label>
                                            <input type="text" class="form-control" value="{{ $data->pass_secret }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class="bx bx-math me-2"></i>Local Address</label>
                                            <input type="text" class="form-control" value="{{ $data->local_address ?? 'Tidak Tersedia' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class="bx bx-network-chart me-2"></i>Remote Address</label>
                                            <input type="text" class="form-control" value="{{ $data->remote_address ?? 'Tidak Tersedia' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class="bx bx-package me-2"></i>Paket Langganan</label>
                                            <input name="paket_id" type="text" class="form-control" value="{{ $data->paket->nama_paket }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        <div class="input-groups">
                                            <label class="form-label"><i class="bx bx-money me-2"></i>Harga</label>
                                            <input name="harga" type="text" class="form-control" value="Rp {{ number_format($data->paket->harga, 0, ',', '.') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection