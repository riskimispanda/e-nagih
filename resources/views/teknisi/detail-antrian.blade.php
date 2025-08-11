@extends('layouts/contentNavbarLayout')

@section('title', 'Konfirmasi Instalasi')

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
            font-size: 1rem;
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

    <div class="row">
        <div class="col-sm-12">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap">
                <div>
                    <h4 class="fw-bold mb-0">Detail Antrian Instalasi</h4>
                    <p class="text-muted mb-0">Konfirmasi dan konfigurasi instalasi pelanggan</p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/teknisi/antrian">Antrian</a></li>
                        <li class="breadcrumb-item active text-primary">Detail Antrian</li>
                    </ol>
                </nav>
            </div>

            <div class="card col-sm-12">
                <div class="card-header d-flex align-items-center justify-content-between mb-5">
                    <div>
                        <h5 class="card-title">
                            <i class='bx bx-user-check me-2 text-primary'></i>Informasi Pelanggan
                        </h5>
                        <p class="card-subtitle">Detail data pelanggan yang akan diinstalasi</p>
                    </div>
                    <span class="badge bg-label-primary">Menunggu Konfirmasi</span>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Customer Information -->
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
                                                <label class="form-label"><i class='bx bx-user me-2'></i>Nama Pelanggan</label>
                                                <input type="text" class="form-control" value="{{ $customer->nama_customer }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class='bx bx-phone me-2'></i>Nomor Telepon</label>
                                                <input type="text" class="form-control" value="{{ $customer->no_hp }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class='bx bx-id-card me-2'></i>Nomor Identitas</label>
                                                <input type="text" class="form-control" value="{{ $customer->no_identitas }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class='bx bx-home me-2'></i>Alamat</label>
                                                <input type="text" class="form-control" value="{{ $customer->alamat }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups col-sm-4">
                                                <label class="form-label"><i class='bx bx-image me-2'></i>Foto Identitas</label>
                                                <a href="{{ asset($customer->identitas) }}" target="_blank" class="btn btn-outline-primary btn-sm form-control">
                                                    <i class='bx bx-image'></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="input-groups">
                                                <label class="form-label"><i class='bx bx-calendar me-2'></i>Tanggal Registrasi</label>
                                                <input type="text" class="form-control" value="{{ $customer->created_at->format('d F Y, H:i') }}" readonly>
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
                                                <label class="form-label"><i
                                                        class="bx bx-network-chart me-2"></i>Router</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $customer->router->nama_router }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class="bx bx-link-alt me-2"></i>Jenis
                                                    Koneksi</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $customer->koneksi->nama_koneksi }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class="bx bx-lock me-2"></i>User
                                                    Secret</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $customer->usersecret }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class="bx bx-key me-2"></i>Password</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $customer->pass_secret }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class="bx bx-math me-2"></i>Local
                                                    Address</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $customer->local_address ?? 'Tidak Tersedia' }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class="bx bx-network-chart me-2"></i>Remote
                                                    Address</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $customer->remote_address ?? 'Tidak Tersedia' }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class="bx bx-plug me-2"></i>Remote
                                                    IP Management</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $customer->remote ?? 'Tidak Tersedia' }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class="bx bx-package me-2"></i>Paket
                                                    Langganan</label>
                                                <input name="paket_id" type="text" class="form-control"
                                                    value="{{ $customer->paket->nama_paket }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <div class="input-groups">
                                                <label class="form-label"><i class="bx bx-money me-2"></i>Harga</label>
                                                <input name="harga" type="text" class="form-control"
                                                    value="Rp {{ number_format($customer->paket->harga, 0, ',', '.') }}"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card" id="pills-detail" role="tabpanel">
                <div class="card-header d-flex align-items-center justify-content-between mb-5">
                    <div>
                        <h5 class="card-title">
                            <i class='bx bx-network-chart me-2 text-primary'></i>Detail Jaringan
                        </h5>
                        <p class="card-subtitle">Konfigurasi perangkat dan dokumentasi instalasi</p>
                    </div>
                    <span class="badge bg-label-warning">Perlu Konfigurasi</span>
                </div>
                <div class="card-body">
                    <form action="/teknisi/konfirmasi/{{ $customer->id }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">
                            <!-- Documentation Section -->
                            <div class="col-12 col-lg-5">
                                <div class="inner-card">
                                    <div class="card-body">
                                        <div class="inner-card-header">
                                            <div class="avatar-md me-3">
                                                <i class='bx bx-image'></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Dokumentasi</h6>
                                                <small class="text-muted">Foto lokasi dan identitas</small>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label"><i class="bx bx-image me-2"></i>Foto Rumah/Lokasi</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" accept="image/*" name="foto_rumah">
                                                <button class="btn btn-outline-primary" type="button">
                                                    <i class='bx bx-upload'></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">Max 2MB (JPG, JPEG, PNG)</small>
                                        </div>

                                        <div class="col-sm-12 mb-4">
                                            <div class="input-groups col-sm-12">
                                                <label class="form-label"><i class='bx bx-map-pin me-2'></i>Titik Lokasi</label>
                                                <input type="text" name="gps" value="{{ $customer->gps ?? 'Belum Ada Koordinat'}}" class="form-control" required placeholder="https://maps.google.com/... atau -1.0269916,110.48579129">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label"><i class="bx bx-image me-2"></i>Foto Perangkat</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" accept="image/*" name="foto_perangkat">
                                                <button class="btn btn-outline-primary" type="button">
                                                    <i class='bx bx-upload'></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">Max 2MB (JPG, JPEG, PNG)</small>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label"><i class="bx bx-link me-2"></i>Panjang Kabel</label>
                                            <input type="text" name="panjang_kabel" class="form-control" placeholder="Contoh: 10m">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label"><i class="bx bx-radio me-2"></i>Nilai Redam</label>
                                            <input type="text" name="redaman" class="form-control" placeholder="Contoh: 50db">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Network Configuration Section -->
                            <div class="col-12 col-lg-7">
                                <div class="inner-card">
                                    <div class="card-body">
                                        <div class="inner-card-header">
                                            <div class="avatar-md me-3">
                                                <i class='bx bx-server'></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Konfigurasi Jaringan</h6>
                                                <small class="text-muted">Detail perangkat jaringan</small>
                                            </div>
                                        </div>

                                        <div class="row g-3 mb-3">

                                            <div class="col-12 col-sm-6 mb-2">
                                                <label class="form-label"><i class="bx bx-server me-2"></i>Server BTS</label>
                                                <select id="server" name="lokasi_id" class="form-select" required>
                                                    <option value="" selected disabled>Pilih Server BTS
                                                    </option>
                                                    @foreach ($server as $item)
                                                        <option value="{{ $item->id }}">
                                                            {{ $item->lokasi_server }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-12 col-sm-6 mb-2">
                                                <label class="form-label"><i class="bx bx-devices me-2"></i>Modem</label>
                                                <select name="modem" class="form-select" required id="modem">
                                                    <option value="" selected disabled>Pilih Modem
                                                    </option>
                                                    @foreach ($modem as $item)
                                                        <option value="{{ $item->id }}">
                                                            {{ $item->nama_perangkat }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                        </div>

                                        <div class="row g-3">
                                            <div class="col-12 col-sm-6 mb-2">
                                                <label class="form-label"><i class="bx bx-laptop me-2"></i>Serial Number Modem</label>
                                                <input type="text" name="serial_number" class="form-control" placeholder="Masukkan S/N" required>
                                            </div>

                                            <div class="col-12 col-sm-6 mb-2">
                                                <label class="form-label"><i class="bx bx-phone me-2"></i>Mac Address Modem</label>
                                                <input type="text" name="mac_address" class="form-control" placeholder="XX:XX:XX:XX:XX:XX" required>
                                            </div>
                                            <hr class="my-2">
                                            <div class="col-sm-12">
                                                <div class="input-groups">
                                                    <label class="form-label"><i class="bx bx-disc me-2"></i>Pilih Media</label>
                                                    <select name="media_id" id="media" class="form-select" required>
                                                        <option value="" selected disabled>Pilih Media</option>
                                                        @foreach ($media as $item)
                                                            <option value="{{ $item->id }}">
                                                                {{ $item->nama_media }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <hr class="my-2 mt-5">
                                        </div>
                                        <div class="row g-3 mt-2 mb-2" id="olt_id" style="display: none;">
                                            <div class="col-12 col-sm-4 mb-2">
                                                <label class="form-label"><i class="bx bx-sitemap me-2"></i>OLT</label>
                                                <select id="olt" name="olt" class="form-select">
                                                    <option value="" selected disabled>Pilih OLT</option>
                                                </select>
                                            </div>

                                            <div class="col-12 col-sm-4 mb-2">
                                                <label class="form-label"><i class="bx bx-sitemap me-2"></i>ODC</label>
                                                <select id="odc" name="odc" class="form-select">
                                                    <option value="" selected disabled>Pilih ODC</option>
                                                </select>
                                            </div>

                                            <div class="col-12 col-sm-4 mb-2">
                                                <label class="form-label"><i class="bx bx-sitemap me-2"></i>ODP</label>
                                                <select id="odp" name="odp" class="form-select">
                                                    <option value="" selected disabled>Pilih ODP</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row g-3 mt-2 mb-2" id="htb" style="display: none;">
                                            <div class="col-12 col-sm-6 mb-2">
                                                <label class="form-label"><i class="bx bx-sitemap me-2"></i>Transiver</label>
                                                <input type="text" name="transiver" class="form-control">
                                            </div>
                                            <div class="col-12 col-sm-6 mb-2">
                                                <label class="form-label"><i class="bx bx-sitemap me-2"></i>Receiver</label>
                                                <input type="text" name="receiver" class="form-control">
                                            </div>
                                        </div>
                                        <div class="row g-3 mt-2 mb-2" id="wireless" style="display: none;">
                                            <div class="col-12 col-sm-6 mb-2">
                                                <label class="form-label"><i class="bx bx-sitemap me-2"></i>Access Point</label>
                                                <input type="text" name="access_point" class="form-control">
                                            </div>
                                            <div class="col-12 col-sm-6 mb-2">
                                                <label class="form-label"><i class="bx bx-sitemap me-2"></i>Station</label>
                                                <input type="text" name="station" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer float-end">
                            <button type="submit"
                                class="btn btn-outline-primary btn-sm d-flex align-items-center float-end">
                                <i class='bx bx-check-circle me-2'></i>
                                Konfirmasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#media').on('change', function() {
                const mediaId = $(this).val();
                if (mediaId == 1) {
                    $('#olt_id').hide();
                    $('#htb').show();
                    $('#wireless').hide();
                } else if (mediaId == 2) {
                    $('#olt_id').hide();
                    $('#htb').hide();
                    $('#wireless').show();
                } else if (mediaId == 3) {
                    $('#olt_id').show();
                    $('#htb').hide();
                    $('#wireless').hide();
                }
            });
        });
    </script>

    {{-- <script>
        $('#server').on('change', function() {
            const serverId = $(this).val();

            fetch(`/api/olt/by-server/${serverId}`)
                .then(res => res.json())
                .then(data => {
                    const $olt = $('#olt');
                    $olt.empty();
                    $olt.append('<option value="">Pilih OLT</option>');
                    data.forEach(item => {
                        $olt.append(`<option value="${item.id}">${item.nama_lokasi}</option>`);
                    });
                });
        });

        $('#olt').on('change', function() {
            const oltId = $(this).val();

            fetch(`/api/odc/by-olt/${oltId}`)
                .then(res => res.json())
                .then(data => {
                    const $odc = $('#odc');
                    $odc.empty();
                    $odc.append('<option value="">Pilih ODC</option>');
                    data.forEach(item => {
                        $odc.append(`<option value="${item.id}">${item.nama_odc}</option>`);
                    });
                });
        });

        $('#odc').on('change', function() {
            const odcId = $(this).val();

            fetch(`/api/odp/by-odc/${odcId}`)
                .then(res => res.json())
                .then(data => {
                    const $odp = $('#odp');
                    $odp.empty();
                    $odp.append('<option value="">Pilih ODP</option>');
                    data.forEach(item => {
                        $odp.append(`<option value="${item.id}">${item.nama_odp}</option>`);
                    });
                });
        });
    </script> --}}
    <script>
        let tomOlt, tomOdc, tomOdp;
    
        $('#server').on('change', function () {
            const serverId = $(this).val();
    
            fetch(`/api/olt/by-server/${serverId}`)
                .then(res => res.json())
                .then(data => {
                    const $olt = $('#olt');
                    $olt.empty().append('<option value="">Pilih OLT</option>');
                    data.forEach(item => {
                        $olt.append(`<option value="${item.id}">${item.nama_lokasi}</option>`);
                    });
    
                    if (tomOlt) tomOlt.destroy();
                    tomOlt = new TomSelect('#olt', { create: false });
    
                    $('#olt_id').show(); // Tampilkan dropdown setelah pilih server
                });
        });
    
        $('#olt').on('change', function () {
            const oltId = $(this).val();
    
            fetch(`/api/odc/by-olt/${oltId}`)
                .then(res => res.json())
                .then(data => {
                    const $odc = $('#odc');
                    $odc.empty().append('<option value="">Pilih ODC</option>');
                    data.forEach(item => {
                        $odc.append(`<option value="${item.id}">${item.nama_odc}</option>`);
                    });
    
                    if (tomOdc) tomOdc.destroy();
                    tomOdc = new TomSelect('#odc', { create: false });
                });
        });
    
        $('#odc').on('change', function () {
            const odcId = $(this).val();
    
            fetch(`/api/odp/by-odc/${odcId}`)
                .then(res => res.json())
                .then(data => {
                    const $odp = $('#odp');
                    $odp.empty().append('<option value="">Pilih ODP</option>');
                    data.forEach(item => {
                        $odp.append(`<option value="${item.id}">${item.nama_odp}</option>`);
                    });
    
                    if (tomOdp) tomOdp.destroy();
                    tomOdp = new TomSelect('#odp', { create: false });
                });
        });
    </script>

    <script>
        // Server BTS
        new TomSelect("#server", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        // Media
        new TomSelect("#modem", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });


    </script>
@endsection
