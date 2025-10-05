@extends('layouts.contentNavbarLayout')

@section('title', 'Data Antrian NOC')

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
    
    .btn-info {
        background-color: rgba(0, 207, 232, 0.16);
        color: #00cfe8;
        border: none;
    }
    
    .btn-info:hover {
        background-color: rgba(0, 207, 232, 0.24);
        color: #00cfe8;
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
        overflow: hidden;
        max-height: 90vh;
        animation: modalFadeIn 0.3s ease;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .modal-dialog {
        margin: 1.75rem auto;
        transition: all 0.3s ease;
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
    
    .modal-header .btn-close {
        padding: 0.75rem;
        margin: -0.75rem -0.75rem -0.75rem auto;
        background-color: transparent;
        border-radius: 50%;
        transition: all 0.2s ease;
        opacity: 0.7;
    }
    
    .modal-header .btn-close:hover {
        background-color: rgba(0, 0, 0, 0.05);
        opacity: 1;
    }
    
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
        padding: 1.25rem 1.5rem;
        background-color: #f8f8fb;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
    
    /* Form validation styles */
    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #ea5455;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23ea5455' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23ea5455' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    
    .form-control.is-valid,
    .form-select.is-valid {
        border-color: #28c76f;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328c76f' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: #ea5455;
    }
    
    /* Input group with icons */
    .input-group-text {
        background-color: #f8f8fb;
        border-color: #d9dee3;
        color: #697a8d;
    }
    
    .input-group:focus-within .input-group-text {
        border-color: #696cff;
        color: #696cff;
    }
    
    .input-group:focus-within .form-control,
    .input-group:focus-within .form-select {
        border-color: #696cff;
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
    
    /* Detail modal styles */
    .detail-item {
        margin-bottom: 1.25rem;
    }
    
    .detail-label {
        display: block;
        font-size: 0.75rem;
        color: #a1acb8;
        margin-bottom: 0.25rem;
        font-weight: 500;
    }
    
    .detail-value {
        font-size: 0.875rem;
        color: #566a7f;
        font-weight: 500;
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
    }
    
    .avatar-lg {
        width: 3.5rem;
        height: 3.5rem;
    }
    
    .avatar-initial {
        font-size: 1.5rem;
        font-weight: 500;
        color: #696cff;
    }
    
    .bg-primary-subtle {
        background-color: rgba(105, 108, 255, 0.16) !important;
    }
    
    .connection-status-card {
        background-color: #f8f8fb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-top: 0.5rem;
    }
    
    .connection-status-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .connection-indicator {
        display: inline-flex;
        align-items: center;
        font-size: 0.875rem;
        font-weight: 500;
        color: #28c76f;
    }
    
    .connection-indicator i {
        margin-right: 0.375rem;
        font-size: 1.1rem;
    }
    
    .connection-status-body {
        padding-top: 0.5rem;
    }
    
    /* Timeline styles */
    .timeline {
        position: relative;
        padding-left: 1.5rem;
        margin-top: 0.5rem;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    
    .timeline-point {
        position: absolute;
        left: -1.5rem;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1rem;
        z-index: 1;
    }
    
    .timeline-content {
        padding-left: 0.5rem;
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
            border-radius: 0.5rem;
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
        
        /* Detail modal responsive styles */
        .detail-item {
            margin-bottom: 1rem;
        }
        
        .avatar-lg {
            width: 2.75rem;
            height: 2.75rem;
        }
        
        .avatar-initial {
            font-size: 1.25rem;
        }
        
        .timeline {
            padding-left: 1.25rem;
        }
        
        .timeline-point {
            left: -1.25rem;
            width: 1.75rem;
            height: 1.75rem;
            font-size: 0.875rem;
        }
        
        .timeline-item {
            padding-bottom: 1.25rem;
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
        
        /* Improve form layout on mobile */
        .input-group {
            flex-wrap: nowrap;
        }
        
        .input-group-text {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .input-group-text i {
            font-size: 1rem;
        }
        
        /* Make buttons more touch-friendly */
        .btn {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }
    
    @media (max-width: 575.98px) {
        .modal-dialog.modal-lg {
            max-width: 100%;
            margin: 0;
        }
        
        .modal-content {
            border-radius: 0;
            height: 100vh;
            max-height: 100vh;
        }
        
        .modal-body {
            padding: 0.75rem;
            max-height: calc(100vh - 110px);
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
        
        /* Improve form layout on small screens */
        .col-md-6 {
            width: 100%;
        }
        
        /* Make the modal fullscreen on very small devices */
        @media (max-height: 500px) {
            .modal-dialog {
                margin: 0;
                height: 100%;
            }
            
            .modal-content {
                height: 100vh;
                border-radius: 0;
            }
            
            .modal-body {
                max-height: calc(100vh - 110px);
            }
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
        <li class="breadcrumb-item active">Data Antrian NOC</li>
    </ol>
</nav>

<div class="row">
    <div class="col-12">
        
        {{-- Perusahaan --}}
        <div class="card">
            <div class="card-header mb-5">
                <div class="card-header-elements">
                    <div>
                        <h5 class="card-title">Data Antrian Pelanggan Perusahaan</h5>
                        <p class="card-subtitle">Daftar seluruh antrian pelanggan perusahaan</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="text" class="search-input me-2" placeholder="Cari pelanggan..."
                        id="searchCustomer">
                    </div>
                </div>
            </div>
            <div class="card-body mb-3">
                <div class="table-responsive">
                    <table class="table table-hover" id="corpTable">
                        <thead class="table-dark text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Nama Perusahaan</th>
                                <th width="20%">Nama PIC</th>
                                <th width="10%">Paket</th>
                                <th width="10%">Lokasi</th>
                                <th width="10%">Tanggal Registrasi</th>
                                <th width="10%">Status</th>
                                <th width="10%">Aksi</th>
                                <th width="10%">Admins</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($perusahaan as $index => $item)
                            <tr data-id="{{ $item->id }}" data-name="{{ $item->nama_perusahaan }}"
                                data-date="{{ $item->tanggal }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $item->nama_perusahaan }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $item->nama_pic }}</span>
                                        <small class="nomor-hp text-muted">
                                            {{ $item->no_hp ? $item->no_hp : 'No. HP tidak tersedia' }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        {{ $item->paket->paket_name ?? 'Belum ada paket' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ $item->gps }}" target="_blank" class="btn btn-action btn-maps"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Lihat di Google Maps">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger"
                                    data-status-id="1">{{$item->status->nama_status}}</span>
                                </td>
                                <td>
                                    <a href="/perusahaan/{{ $item->id }}"
                                        class="btn btn-action btn-info me-1" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Proses">
                                        <i class="bx bx-info-circle"></i>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        {{ $item->usr->name }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class='bx bx-calendar-check text-secondary mb-2'
                                        style="font-size: 2rem;"></i>
                                        <h6 class="mb-1">Tidak ada data antrian</h6>
                                        <p class="text-muted mb-0">Belum ada pelanggan perusahaan yang terdaftar</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            {{ $perusahaan->links('pagination::bootstrap-5') }}
            </div>
        </div>
        
        {{-- Personal --}}
        <div class="card">
            <div class="card-header mb-5">
                <div class="card-header-elements">
                    <div>
                        <h5 class="card-title">Data Antrian Pelanggan Personal</h5>
                        <p class="card-subtitle">Daftar seluruh antrian pelanggan personal</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="text" class="search-input me-2" placeholder="Cari pelanggan..."
                        id="searchCustomer">
                    </div>
                </div>
            </div>
            <div class="card-body mb-3">
                <div class="table-responsive">
                    <table class="table table-hover" id="customerTable">
                        <thead class="table-dark text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Nama Pelanggan</th>
                                <th width="20%">Alamat</th>
                                <th width="10%">Paket</th>
                                <th width="10%">Lokasi</th>
                                <th width="15%">Tanggal Registrasi</th>
                                <th width="10%">Status</th>
                                <th width="10%">Aksi</th>
                                <th width="10%">Sales</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($customer as $index => $item)
                            <tr data-id="{{ $item->id }}" data-name="{{ $item->nama_customer }}"
                                data-date="{{ $item->created_at->timestamp }}">
                                <td>{{ $index + 1 }}</td>
                                <td data-email="{{ $item->email }}">
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $item->nama_customer }}</span>
                                        <small class="nomor-hp text-muted">
                                            {{ $item->no_hp ? $item->no_hp : 'No. HP tidak tersedia' }}
                                        </small>
                                    </div>
                                </td>
                                <td>{{ $item->alamat }}</td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        {{ $item->paket->paket_name ?? 'Belum ada paket' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                    $gps = $item->gps;
                                    $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                    $url = $isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps);
                                    @endphp
                                    
                                    <a href="{{ $url }}" target="_blank" class="btn btn-action btn-maps"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Lihat di Google Maps">
                                    <i class="bx bx-map"></i>
                                </a>
                            </td>
                            <td>{{ $item->created_at->format('d M Y') }}</td>
                            <td>
                                @if ($item->status_id == 1)
                                <span class="badge badge-status badge-waiting"
                                data-status-id="1">Menunggu</span>
                                @elseif ($item->status_id == 2)
                                <span class="badge badge-status badge-progress"
                                data-status-id="2">Proses</span>
                                @elseif ($item->status_id == 3)
                                <span class="badge badge-status badge-completed"
                                data-status-id="3">Selesai</span>
                                @elseif ($item->status_id == 4)
                                <span class="badge badge-status badge-maintenance"
                                data-status-id="4">Maintenance</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="/noc/proses-antrian/{{ $item->id }}"
                                        class="btn btn-action btn-info me-1" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Proses">
                                        <i class="bx bx-info-circle"></i>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    {{ $item->agen->name ?? '-'}}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-calendar-check text-secondary mb-2'
                                    style="font-size: 2rem;"></i>
                                    <h6 class="mb-1">Tidak ada data antrian</h6>
                                    <p class="text-muted mb-0">Belum ada pelanggan personal yang terdaftar</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $customer->links('pagination::bootstrap-5') }}
        </div>
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
        
        // Detail modal functionality
        var detailButtons = document.querySelectorAll('.btn-info');
        detailButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var customerId = this.getAttribute('data-id');
                var customerName = this.getAttribute('data-name');
                var customerEmail = this.getAttribute('data-email');
                var customerPhone = this.getAttribute('data-phone');
                var customerAddress = this.getAttribute('data-address');
                var customerGps = this.getAttribute('data-gps');
                var customerStatus = this.getAttribute('data-status');
                var customerIdentitas = this.getAttribute('data-identitas') || 'KTP';
                var customerNoIdentitas = this.getAttribute('data-no-identitas') || '';
                var customerRouterId = this.getAttribute('data-router-id') || '';
                var customerKoneksiId = this.getAttribute('data-koneksi-id') || '';
                var customerPaketId = this.getAttribute('data-paket-id') || '';
                var customerUserSecret = this.getAttribute('data-usersecret') || '';
                var customerMacAddress = this.getAttribute('data-mac-address') || '';
                var customerCreatedAt = this.getAttribute('data-created-at');
                var customerAdmin = this.getAttribute('data-admin');
                var customerPaket = this.getAttribute('data-paket');
                
                // Set customer initials
                var initials = customerName.split(' ').map(function(n) {
                    return n[0];
                }).join('').substring(0, 2).toUpperCase();
                document.getElementById('detail-customer-initial').textContent = initials;
                
                // Set customer name and ID
                document.getElementById('detail-customer-name').textContent = customerName;
                document.getElementById('detail-customer-id').textContent = 'ID: ' + customerId;
                
                // Set customer status
                var statusBadge = document.getElementById('detail-customer-status');
                statusBadge.textContent = getStatusText(customerStatus);
                statusBadge.className = 'badge badge-status ' + getStatusClass(customerStatus);
                
                // Set personal information
                document.getElementById('detail-customer-email').textContent = customerEmail ||
                'Tidak tersedia';
                document.getElementById('detail-customer-phone').textContent = customerPhone ===
                'No. HP tidak tersedia' ? 'Tidak tersedia' : customerPhone;
                document.getElementById('detail-customer-address').textContent =
                customerAddress || 'Tidak tersedia';
                document.getElementById('detail-customer-identity-type').textContent =
                customerIdentitas;
                document.getElementById('detail-customer-identity-number').textContent =
                customerNoIdentitas || 'Tidak tersedia';
                document.getElementById('detail-customer-location').textContent = customerGps ||
                'Tidak tersedia';
                document.getElementById('detail-view-map-btn').href = customerGps;
                document.getElementById('detail-customer-registration-date').textContent =
                customerCreatedAt;
                document.getElementById('detail-customer-admin').textContent = customerAdmin;
                
                // Set connection information
                document.getElementById('detail-customer-package').textContent = customerPaket;
                
                // Get connection type name from koneksi_id
                var connectionType = 'Tidak tersedia';
                if (customerKoneksiId) {
                    var koneksiSelect = document.getElementById('edit_koneksi');
                    if (koneksiSelect) {
                        var selectedOption = koneksiSelect.querySelector('option[value="' +
                        customerKoneksiId + '"]');
                        if (selectedOption) {
                            connectionType = selectedOption.textContent;
                        }
                    }
                }
                document.getElementById('detail-customer-connection-type').textContent =
                connectionType;
                
                // Get router name from router_id
                var routerName = 'Tidak tersedia';
                if (customerRouterId) {
                    var routerSelect = document.getElementById('edit_router');
                    if (routerSelect) {
                        var selectedOption = routerSelect.querySelector('option[value="' +
                        customerRouterId + '"]');
                        if (selectedOption) {
                            routerName = selectedOption.textContent;
                        }
                    }
                }
                document.getElementById('detail-customer-router').textContent = routerName;
                
                // Set device information
                document.getElementById('detail-customer-device').textContent =
                'Tidak tersedia'; // This would need to be added to the data attributes if available
                document.getElementById('detail-customer-usersecret').textContent =
                customerUserSecret || 'Tidak tersedia';
                document.getElementById('detail-customer-mac-address').textContent =
                customerMacAddress || 'Tidak tersedia';
                
                // Set up the edit button in the detail modal
                document.getElementById('btn-edit-from-detail').addEventListener('click',
                function() {
                    // This will close the detail modal and open the edit modal with the same data
                    var detailModal = bootstrap.Modal.getInstance(document
                    .getElementById('modalDetail'));
                    detailModal.hide();
                });
            });
        });
        
        // Helper functions for status
        function getStatusText(statusId) {
            switch (statusId) {
                case '1':
                return 'Menunggu';
                case '2':
                return 'Proses';
                case '3':
                return 'Selesai';
                case '4':
                return 'Maintenance';
                default:
                return 'Tidak diketahui';
            }
        }
        
        function getStatusClass(statusId) {
            switch (statusId) {
                case '1':
                return 'badge-waiting';
                case '2':
                return 'badge-progress';
                case '3':
                return 'badge-completed';
                case '4':
                return 'badge-maintenance';
                default:
                return 'badge-waiting';
            }
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
                
                // Get additional data from data attributes
                var customerIdentitas = this.getAttribute('data-identitas') || 'KTP';
                var customerNoIdentitas = this.getAttribute('data-no-identitas') || '';
                var customerRouterId = this.getAttribute('data-router-id') || '';
                var customerKoneksiId = this.getAttribute('data-koneksi-id') || '';
                var customerPaketId = this.getAttribute('data-paket-id') || '';
                var customerUserSecret = this.getAttribute('data-usersecret') || '';
                var customerMacAddress = this.getAttribute('data-mac-address') || '';
                
                // Set values in the personal info form
                document.getElementById('edit_customer_id_personal').value = customerId;
                document.getElementById('edit_nama_customer').value = customerName;
                document.getElementById('edit_email').value = customerEmail;
                document.getElementById('edit_no_hp').value = customerPhone ===
                'No. HP tidak tersedia' ? '' : customerPhone;
                document.getElementById('edit_alamat').value = customerAddress;
                document.getElementById('edit_gps').value = customerGps;
                document.getElementById('edit_status').value = customerStatus;
                document.getElementById('edit_identitas').value = customerIdentitas;
                document.getElementById('edit_no_identitas').value = customerNoIdentitas;
                
                // Update the view map button
                var viewMapBtn = document.getElementById('view_map_btn');
                viewMapBtn.href = customerGps;
                
                // Set values in the connection info form
                document.getElementById('edit_customer_id').value = customerId;
                if (customerRouterId) document.getElementById('edit_router').value =
                customerRouterId;
                if (customerKoneksiId) document.getElementById('edit_koneksi').value =
                customerKoneksiId;
                if (customerPaketId) document.getElementById('edit_paket').value =
                customerPaketId;
                document.getElementById('edit_usersecret').value = customerUserSecret;
                document.getElementById('edit_mac_address').value = customerMacAddress;
                
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
        
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            var passwordInput = document.getElementById('edit_password');
            var icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            }
        });
        
        // Form validation
        function validateForm(formId) {
            var form = document.getElementById(formId);
            var isValid = true;
            
            // Check required fields
            form.querySelectorAll('[required]').forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                    
                    // Create error message if it doesn't exist
                    var errorDiv = field.parentNode.querySelector('.invalid-feedback');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = 'Bidang ini wajib diisi';
                        field.parentNode.appendChild(errorDiv);
                    }
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });
            
            // Email validation
            var emailField = form.querySelector('input[type="email"]');
            if (emailField && emailField.value.trim()) {
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailField.value)) {
                    emailField.classList.add('is-invalid');
                    isValid = false;
                    
                    var errorDiv = emailField.parentNode.querySelector('.invalid-feedback');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = 'Format email tidak valid';
                        emailField.parentNode.appendChild(errorDiv);
                    } else {
                        errorDiv.textContent = 'Format email tidak valid';
                    }
                }
            }
            
            return isValid;
        }
        
        // Form submission with validation
        document.getElementById('personalForm').addEventListener('submit', function(e) {
            if (!validateForm('personalForm')) {
                e.preventDefault();
            }
        });
        
        document.getElementById('connectionForm').addEventListener('submit', function(e) {
            if (!validateForm('connectionForm')) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection
