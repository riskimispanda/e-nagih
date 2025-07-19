@extends('layouts.contentNavbarLayout')

@section('title', 'Data Antrian')

@section('vendor-style')
    <style>
        /* Card styles */
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

        /* Table styles */
        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern th {
            background-color: #f5f5f9;
            font-weight: 600;
            color: #566a7f;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }

        .table-modern td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            color: #697a8d;
        }

        .table-modern tbody tr {
            transition: all 0.2s ease;
        }

        .table-modern tbody tr:hover {
            background-color: rgba(105, 108, 255, 0.04);
        }

        /* Badge styles */
        .badge-status {
            padding: 0.35rem 0.7rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 0.375rem;
        }

        .badge-waiting {
            background-color: rgba(255, 171, 0, 0.16) !important;
            color: #ffab00 !important;
        }

        .badge-progress {
            background-color: rgba(105, 108, 255, 0.16) !important;
            color: #696cff !important;
        }

        .badge-completed {
            background-color: rgba(40, 199, 111, 0.16) !important;
            color: #28c76f !important;
        }

        .badge-maintenance {
            background-color: rgba(234, 84, 85, 0.16) !important;
            color: #ea5455 !important;
        }

        /* Button styles */
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
            margin: 0 0.125rem;
        }

        .btn-maps {
            background-color: #f5f5f9;
            color: #697a8d;
            border: none;
        }

        .btn-maps:hover {
            background-color: #e1e1e9;
            color: #566a7f;
        }

        .btn-edit {
            background-color: rgba(105, 108, 255, 0.16);
            color: #696cff;
            border: none;
        }

        .btn-edit:hover {
            background-color: rgba(105, 108, 255, 0.24);
            color: #696cff;
        }

        .btn-delete {
            background-color: rgba(234, 84, 85, 0.16);
            color: #ea5455;
            border: none;
        }

        .btn-delete:hover {
            background-color: rgba(234, 84, 85, 0.24);
            color: #ea5455;
        }

        /* Search and filter styles */
        .search-input {
            border-radius: 0.375rem;
            border: 1px solid #d9dee3;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            width: 100%;
            max-width: 250px;
        }

        .search-input:focus {
            border-color: #696cff;
            box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
            outline: none;
        }

        /* Modal styles */
        .modal-content {
            border: none;
            box-shadow: 0 0.25rem 1.5rem rgba(0, 0, 0, 0.15);
            border-radius: 0.75rem;
            /* overflow: hidden; */
            max-height: 90vh;
        }

        .modal-dialog {
            margin: 1.75rem auto;
        }

        .modal-dialog-centered {
            display: flex;
            align-items: center;
            min-height: calc(100% - 3.5rem);
        }

        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            background-color: #f8f8fb;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            display: flex;
            align-items: center;
        }

        .modal .btn-close {
            position: relative;
            top: 10px;
            right: 10px;
            z-index: 1056;
        }

        /* .modal-header .btn-close:hover {
            background-color: rgba(0, 0, 0, 0.05);
            opacity: 1;
        } */

        .modal-title {
            font-weight: 600;
            color: #566a7f;
            display: flex;
            align-items: center;
            margin-right: auto;
        }

        .modal-title i {
            font-size: 1.25rem;
            margin-right: 0.75rem;
            color: #696cff;
            background-color: rgba(105, 108, 255, 0.1);
            padding: 0.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            max-height: calc(90vh - 130px);
        }

        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 0.5rem 1.5rem;
            background-color: #f8f8fb;
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-weight: 500;
            color: #566a7f;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control,
        .form-select {
            border-radius: 0.375rem;
            border: 1px solid #d9dee3;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            width: 100%;
            background-color: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #696cff;
            box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #b4b7bd;
        }

        .form-text {
            font-size: 0.75rem;
            color: #a1acb8;
            margin-top: 0.25rem;
            display: block;
        }

        .modal-tabs {
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: 1rem;
        }

        .modal-tabs .nav-item {
            margin-bottom: 0.5rem;
        }

        .modal-tabs .nav-link {
            padding: 0.75rem 1.25rem;
            color: #697a8d;
            font-weight: 500;
            border: none;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .modal-tabs .nav-link i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .modal-tabs .nav-link.active {
            color: #696cff;
            background-color: rgba(105, 108, 255, 0.1);
            font-weight: 600;
        }

        .modal-tabs .nav-link:hover:not(.active) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .tab-content {
            padding-top: 0.5rem;
        }

        .tab-pane {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .card-header-elements {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-input {
                max-width: 100%;
                margin-bottom: 0.5rem;
            }

            .table-modern th,
            .table-modern td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }

            .table-modern th:nth-child(3),
            .table-modern td:nth-child(3) {
                display: none;
            }

            /* Modal responsive styles */
            .modal-dialog {
                margin: 0.5rem;
                width: auto;
            }

            .modal-content {
                max-height: 95vh;
            }

            .modal-body {
                max-height: calc(95vh - 120px);
                padding: 1rem;
            }

            .modal-header,
            .modal-footer {
                padding: 1rem;
            }

            .modal-title {
                font-size: 1rem;
            }

            .modal-title i {
                font-size: 1rem;
                padding: 0.375rem;
                margin-right: 0.5rem;
            }

            .modal-tabs {
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 0.75rem;
                margin-bottom: 1rem;
                -webkit-overflow-scrolling: touch;
                -ms-overflow-style: -ms-autohiding-scrollbar;
            }

            .modal-tabs::-webkit-scrollbar {
                height: 4px;
            }

            .modal-tabs::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.2);
                border-radius: 4px;
            }

            .modal-tabs .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .row.g-3 {
                row-gap: 0.75rem !important;
            }
        }

        @media (max-width: 575.98px) {
            .modal-dialog.modal-lg {
                max-width: 100%;
            }

            .modal-body {
                padding: 0.75rem;
            }

            .modal-header,
            .modal-footer {
                padding: 0.75rem;
            }

            .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }

            .form-control,
            .form-select {
                padding: 0.5rem 0.75rem;
            }

            .form-label {
                margin-bottom: 0.375rem;
                font-size: 0.875rem;
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
            <li class="breadcrumb-item active">Data Antrian Helpdesk</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-header">
                    <div class="card-header-elements">
                        <div>
                            <h5 class="card-title">Data Antrian Pelanggan Baru</h5>
                            <p class="card-subtitle">Daftar seluruh antrian pelanggan</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalAdd">
                                <i class='bx bx-plus-circle me-1'></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @if(auth()->user()->roles_id == 7)
            {{-- Perusahaan --}}
            <div class="card">
                <div class="card-header mb-5">
                    <div class="card-header-elements">
                        <div>
                            <h5 class="card-title">Data Antrian Pelanggan Corporate</h5>
                            <p class="card-subtitle">Daftar Antrian Pelanggan Perusahaan</p>
                        </div>
                    </div>
                </div>
                <div class="card-body mb-3">
                    <div class="table-responsive">
                        <table class="table table-modern" id="corpTable">
                            <thead class="text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Nama Perusahaan</th>
                                    <th width="20%">Nama PIC</th>
                                    <th width="10%">Paket</th>
                                    <th width="10%">Lokasi</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                @php
                                    $no = 1;
                                @endphp
                                @forelse ($perusahaan as $item)
                                    <tr>
                                        <td>{{$no++}}</td>
                                        <td>
                                            {{ $item->nama_perusahaan}}
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ $item->nama_pic }}</span>
                                                <small class="nomor-hp" data-no="{{ $item->no_hp }}">
                                                    {{ $item->no_hp ? $item->no_hp : 'No. HP tidak tersedia' }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                                {{ $item->paket->nama_paket }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ $item->gps }}" target="_blank" class="btn btn-action btn-maps"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Lihat di Google Maps">
                                                <i class="bx bx-map"></i>
                                            </a>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                                {{ $item->status->nama_status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/corp/detail/{{ $item->id }}">
                                                <button type="button" class="btn btn-action btn-edit"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detail">
                                                    <i class="bx bx-info-circle"></i>
                                                </button>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                            <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                                            <p class="text-muted mb-0">Belum ada Data Perusahaan</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            {{-- Pelanggan --}}
            <div class="card">
                <div class="card-header mb-5">
                    <div class="card-header-elements">
                        <div>
                            <h5 class="card-title">Data Antrian Pelanggan Personal</h5>
                            <p class="card-subtitle">Daftar Antrian Pelanggan Personal</p>
                        </div>
                        <div class="d-flex align-items-center col-sm-4">
                            <input type="text" class="form-control me-2" placeholder="Cari pelanggan..."
                                id="searchCustomer">
                        </div>
                    </div>
                </div>
                {{-- Table Pelanggan Personal --}}
                <div class="card-body mb-3">
                    <div class="table-responsive">
                        <table class="table table-modern" id="customerTable">
                            <thead class="text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Nama Pelanggan</th>
                                    <th width="20%">Alamat</th>
                                    <th width="10%">Paket</th>
                                    <th width="10%">Lokasi</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Aksi</th>
                                    <th width="10%">Agen</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                @forelse ($customer as $c)
                                    <tr data-id="{{ $c->id }}" data-name="{{ $c->nama_customer }}"
                                        data-date="{{ $c->created_at->timestamp }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td data-email="{{ $c->email }}">
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ $c->nama_customer }}</span>
                                                <small class="nomor-hp" data-no="{{ $c->no_hp }}">
                                                    {{ $c->no_hp ? $c->no_hp : 'No. HP tidak tersedia' }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>{{ $c->alamat }}</td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ $c->paket->nama_paket ?? 'Belum ada paket' }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $gps = $c->gps;
                                                $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                                $url = $isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps);
                                            @endphp
                                        
                                            <a href="{{ $url }}" target="_blank" class="btn btn-action btn-maps"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Lihat di Google Maps">
                                                <i class="bx bx-map"></i>
                                            </a>
                                        </td>                                        
                                        <td>{{ \Carbon\Carbon::parse($c->created_at)->translatedFormat('d F Y') }}</td>
                                        <td>
                                            <span class="badge badge-status badge-waiting">
                                                {{ $c->status->nama_status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/helpdesk/detail-antrian/{{ $c->id }}">
                                                <button type="button" class="btn btn-action btn-edit"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detail">
                                                    <i class="bx bx-info-circle"></i>
                                                </button>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger text-white">
                                                {{ $c->agen->name }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                            <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                                            <p class="text-muted mb-0">Belum ada Data Pelanggan Personal</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex mt-5">
                        {{ $customer->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Add --}}
    <div class="modal fade mt-2" id="modalAdd" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-user-plus"></i> Tambah Pelanggan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/helpdesk/store" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Jenis Pelanggan --}}
                    <div class="modal-body border-bottom" id="tipe">
                        <div class="col-sm-12 form-group">
                            <label class="form-label">Jenis Pelanggan</label>
                            <select name="jenis_pelanggan" id="jenis" class="form-select" onchange="tampilForm()">
                                <option selected disabled value="">Pilih Jenis</option>
                                @if(auth()->user()->roles_id == 7)
                                <option value="Perusahaan">Perusahaan</option>
                                @endif
                                <option value="Personal">Personal</option>
                            </select>
                        </div>
                    </div>

                    {{-- Pelanggan --}}
                    <div class="modal-body" id="pelanggan" style="display: none;">
                        <div class="row p-2">
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="add_nama_customer">Nama <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                                    <input type="text" name="nama_customer" class="form-control" id="add_nama_customer"
                                        placeholder="Masukkan nama pelanggan" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="add_no_hp">No. HP <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                    <input type="text" name="no_hp" class="form-control" id="add_no_hp"
                                        placeholder="08xxxxxxxxxx" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="add_email">Email <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" id="add_email"
                                        placeholder="contoh@email.com" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="add_email">No. Identitas <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx  bx-credit-card-front"></i></span>
                                    <input type="text" name="no_identitas" class="form-control" id="add_email"
                                        placeholder="1234567890" required>
                                </div>
                            </div>
                            <div class="col-12 form-group">
                                <label class="form-label" for="add_alamat">Alamat <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-home"></i></span>
                                    <textarea name="alamat" id="add_alamat" class="form-control" rows="2" placeholder="Masukkan alamat lengkap"
                                        required></textarea>
                                </div>
                            </div>
                            <div class="col-12 form-group">
                                <label class="form-label" for="add_gps">Titik Lokasi <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-map"></i></span>
                                    <input type="text" name="gps" class="form-control" id="add_gps"
                                        placeholder="https://maps.google.com/..." required>
                                </div>
                                <small class="form-text">Masukkan link Google Maps lokasi pelanggan</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="add_foto_ktp">Foto KTP</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-image"></i></span>
                                    <input type="file" name="identitas_file" class="form-control" id="add_foto_ktp">
                                </div>
                                <small class="form-text">Format: JPG, PNG, PDF (Maks. 2MB)</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="add_paket">Paket <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-package"></i></span>
                                    <select name="paket_id" id="add_paket" class="form-select" required>
                                        <option value="" selected disabled>Pilih Paket</option>
                                        @foreach ($paket as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama_paket }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label" for="add_foto_ktp">Tanggal Registrasi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-calendar-alt"></i></span>
                                    <input type="date" name="tanggal_reg" class="form-control" id="add_foto_ktp">
                                </div>
                                <small class="form-text">Tanggal Registrasi</small>
                            </div>
                            @if(auth()->user()->roles_id == 7)
                            <div class="col-md-6 form-group">
                                <label class="form-label">Agen</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                                    <select id="agen" name="agen" class="form-select">
                                        <option value="">-- Pilih Agen --</option>
                                        @foreach ($agen as $item)
                                            <option value="{{ $item->id }}">{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="form-text">Boleh kosong</small>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Perusahaan --}}
                    <div class="modal-body" id="perusahaan" style="display: none;">
                        <div class="row p-2">
                            <div class="col-sm-6 form-group">
                                <label class="form-label">Nama PIC<strong class="text-danger"> *</strong></label>
                                <input type="text" class="form-control" name="nama_pic" placeholder="Adit" required>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label class="form-label">Nama Perusahaan<strong class="text-danger"> *</strong></label>
                                <input type="text" class="form-control" name="nama_perusahaan" placeholder="Niscala" required>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label class="form-label">Nomor Hp<strong class="text-danger"> *</strong></label>
                                <input type="text" class="form-control" name="no_hp" placeholder="08123456789" required>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label class="form-label">Titik Lokasi / Google Maps<strong class="text-danger"> *</strong></label>
                                <input type="text" class="form-control" name="gps" placeholder="https://goolge.com" required>
                            </div>
                            <div class="col-sm-12 form-group">
                                <label class="form-label">Alamat<strong class="text-danger"> *</strong></label>
                                <textarea name="alamat" id="" cols="20" rows="4" class="form-control" required></textarea>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label class="form-label">Foto</label>
                                <input type="file" class="form-control" name="foto">
                                <small class="text-muted">Ukuran File 2MB (JPG, JPEG, PNG)</small>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label class="form-label">Harga Custom<strong class="text-danger"> *</strong></label>
                                <input type="text" class="form-control" id="harga" oninput="formatRupiah(this)" placeholder="Rp. 0" required>
                                <input type="text" class="form-control" name="harga" id="harga_real" hidden>
                            </div>
                            <div class="col-sm-4 form-group">
                                <label class="form-label">Paket Langganan<strong class="text-danger"> *</strong></label>
                                <select name="paket" class="form-select" required>
                                    <option value="" selected disabled>Pilih Paket</option>
                                    @foreach ($corp as $item)
                                        <option value="{{ $item->id }}">{{$item->nama_paket}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4 form-group">
                                <label class="form-label">Speed Internet<strong class="text-danger"> *</strong></label>
                                <input type="text" name="speed" class="form-control" placeholder="100Mbps">
                            </div>
                            <div class="col-sm-4 form-group">
                                <label class="form-label">Tanggal Registrasi<strong class="text-danger"> *</strong></label>
                                <input type="date" class="form-control" name="tanggal" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="batalForm()">
                            <i class="bx bx-x"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-outline-warning btn-sm">
                            <i class="bx bx-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Search functionality
            document.getElementById('searchCustomer').addEventListener('keyup', function() {
                searchTable('customerTable', this.value);
            });

            function searchTable(tableId, query) {
                var table = document.getElementById(tableId);
                var rows = table.getElementsByTagName('tr');

                query = query.toLowerCase();

                for (var i = 1; i < rows.length; i++) {
                    var found = false;
                    var cells = rows[i].getElementsByTagName('td');

                    for (var j = 0; j < cells.length; j++) {
                        var cellText = cells[j].innerText.toLowerCase();
                        if (cellText.indexOf(query) > -1) {
                            found = true;
                            break;
                        }
                    }

                    if (found) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }

            // Sorting functionality
            var sortLinks = document.querySelectorAll('.sort-item');
            sortLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var sortType = this.getAttribute('data-sort');
                    sortTable(sortType);
                });
            });

            function sortTable(sortType) {
                var table = document.getElementById('customerTable');
                var tbody = table.querySelector('tbody');
                var rows = Array.from(tbody.querySelectorAll('tr'));

                // Skip the empty state row if it exists
                rows = rows.filter(row => !row.querySelector('td[colspan="8"]'));

                if (rows.length === 0) return;

                rows.sort(function(a, b) {
                    if (sortType === 'default') {
                        return 0; // No sorting, keep original order
                    } else if (sortType === 'name-asc') {
                        return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
                    } else if (sortType === 'name-desc') {
                        return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
                    } else if (sortType === 'date-asc') {
                        return parseInt(a.getAttribute('data-date')) - parseInt(b.getAttribute(
                            'data-date'));
                    } else if (sortType === 'date-desc') {
                        return parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute(
                            'data-date'));
                    }
                    return 0;
                });

                // Update row numbers after sorting
                rows.forEach(function(row, index) {
                    row.querySelector('td:first-child').textContent = index + 1;
                });

                // Clear and re-append rows
                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }

                rows.forEach(function(row) {
                    tbody.appendChild(row);
                });

                // Update dropdown button text
                var buttonText = 'Urutkan';
                if (sortType === 'name-asc') buttonText = 'Nama (A-Z)';
                else if (sortType === 'name-desc') buttonText = 'Nama (Z-A)';
                else if (sortType === 'date-asc') buttonText = 'Tanggal (Terlama)';
                else if (sortType === 'date-desc') buttonText = 'Tanggal (Terbaru)';

                document.getElementById('sortDropdown').innerHTML = '<i class="bx bx-sort me-1"></i> ' + buttonText;
            }

            // Edit modal functionality
            var editButtons = document.querySelectorAll('.btn-edit');
            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var row = this.closest('tr');
                    var customerId = row.getAttribute('data-id');
                    var customerName = row.getAttribute('data-name');
                    var customerEmail = row.querySelector('td:nth-child(2)').getAttribute(
                        'data-email');
                    var customerPhone = row.querySelector('td:nth-child(2)').querySelector('small')
                        .textContent.trim();
                    var customerAddress = row.querySelector('td:nth-child(3)').textContent.trim();
                    var customerGps = row.querySelector('td:nth-child(5) a').getAttribute('href');
                    var customerStatus = row.querySelector('td:nth-child(7) span').getAttribute(
                        'data-status-id');

                    // Set values in the edit modal
                    document.getElementById('edit_customer_id').value = customerId;
                    document.getElementById('edit_nama_customer').value = customerName;
                    document.getElementById('edit_email').value = customerEmail;
                    document.getElementById('edit_no_hp').value = customerPhone ===
                        'No. HP tidak tersedia' ? '' : customerPhone;
                    document.getElementById('edit_alamat').value = customerAddress;
                    document.getElementById('edit_gps').value = customerGps;
                    document.getElementById('edit_status').value = customerStatus;

                    // Show the modal
                    var editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
                    editModal.show();
                });
            });

            // Delete confirmation
            var deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    if (confirm('Apakah Anda yakin ingin menghapus data pelanggan ini?')) {
                        var customerId = this.closest('tr').getAttribute('data-id');
                        // Here you would typically submit a form or make an AJAX request to delete the customer
                        console.log('Deleting customer with ID: ' + customerId);
                    }
                });
            });

            // Tab navigation in modals
            document.querySelectorAll('.modal-tabs .nav-link').forEach(function(tab) {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    var tabId = this.getAttribute('data-bs-target');
                    document.querySelectorAll('.modal-tabs .nav-link').forEach(function(t) {
                        t.classList.remove('active');
                    });
                    this.classList.add('active');

                    document.querySelectorAll('.tab-pane').forEach(function(pane) {
                        pane.classList.remove('show', 'active');
                    });
                    document.querySelector(tabId).classList.add('show', 'active');
                });
            });
        });
    </script>
    <script>
            function tampilForm() {
            const jenis = document.getElementById('jenis').value;
            const personalForm = document.getElementById('pelanggan');
            const perusahaanForm = document.getElementById('perusahaan');

            if (jenis === 'Perusahaan') {
                // Tampilkan form perusahaan
                perusahaanForm.style.display = 'block';
                personalForm.style.display = 'none';

                // Nonaktifkan input personal
                [...personalForm.querySelectorAll("input, select, textarea")].forEach(el => el.disabled = true);

                // Aktifkan input perusahaan
                [...perusahaanForm.querySelectorAll("input, select, textarea")].forEach(el => el.disabled = false);

                // Sembunyikan pemilihan jenis
                document.getElementById('tipe').style.display = 'none';

            } else if (jenis === 'Personal') {
                // Tampilkan form personal
                personalForm.style.display = 'block';
                perusahaanForm.style.display = 'none';

                // Aktifkan input personal
                [...personalForm.querySelectorAll("input, select, textarea")].forEach(el => el.disabled = false);

                // Nonaktifkan input perusahaan
                [...perusahaanForm.querySelectorAll("input, select, textarea")].forEach(el => el.disabled = true);

                // Sembunyikan pemilihan jenis
                document.getElementById('tipe').style.display = 'none';
            }
        }

        function batalForm() {
            document.getElementById('tipe').style.display = 'block';
            document.getElementById('pelanggan').style.display = 'none';
            document.getElementById('perusahaan').style.display = 'none';

            // Kosongkan pilihan jenis pelanggan
            document.getElementById('jenis').value = "";

            // Nonaktifkan semua input di bawah
            [...document.querySelectorAll("#pelanggan input, #pelanggan select, #pelanggan textarea")].forEach(el => el.disabled = true);
            [...document.querySelectorAll("#perusahaan input, #perusahaan select, #perusahaan textarea")].forEach(el => el.disabled = true);
        }

        // Saat modal ditutup, reset form
        document.addEventListener('DOMContentLoaded', function () {
            const modalAdd = document.getElementById('modalAdd');
            modalAdd.addEventListener('hidden.bs.modal', function () {
                batalForm();
            });
        });

        // Format Rupiah
        function formatRupiah(el) {
            let angka = el.value.replace(/[^,\d]/g, "").toString();
            let split = angka.split(",");
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? "." : "";
                rupiah += separator + ribuan.join(".");
            }

            rupiah = split[1] !== undefined ? rupiah + "," + split[1] : rupiah;
            el.value = "Rp " + rupiah;

            // Simpan nilai angka murni ke input hidden
            let angka_murni = el.value.replace(/[^0-9]/g, "");
            document.getElementById("harga_real").value = angka_murni;
        }

    </script>
    <script>
        new TomSelect("#agen", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    </script>
    <script>
        function formatToLocalNumber(number) {
            if (!number) return 'No. HP tidak tersedia';
            number = number.replace(/\D/g, ''); // hapus non-digit
            if (number.startsWith('62')) {
                return '0' + number.slice(2);
            }
            return number;
        }
    
        document.querySelectorAll('.nomor-hp').forEach(el => {
            const raw = el.dataset.no;
            el.textContent = formatToLocalNumber(raw);
        });
    </script>
@endsection
