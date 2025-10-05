@extends('layouts/contentNavbarLayout')

@section('title', 'Antrian Instalasi')

@section('vendor-style')
<style>
    /* Card styles */
    .card {
        border-radius: 0.75rem;
        box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.05) !important;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        padding: 1.25rem 1.5rem;
    }
    
    .card-header-elements {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .card-title {
        margin-bottom: 0.25rem;
        font-weight: 600;
        color: #566a7f;
        font-size: 1.125rem;
    }
    
    .card-subtitle {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0;
    }
    
    /* Filter styles */
    .filter-container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 0.5rem 0;
    }
    
    .month-filter {
        min-width: 180px;
        border-radius: 0.5rem;
        border: 1px solid #d9dee3;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        background-color: #fff;
    }
    
    .month-filter:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.1);
        outline: none;
    }
    
    /* Table styles */
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }
    
    .table-modern th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 0.875rem 1rem;
        vertical-align: middle;
        border-bottom: 2px solid #e9ecef;
    }
    
    .table-modern td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
        color: #4a4a4a;
        font-size: 0.875rem;
    }
    
    .table-modern tbody tr {
        transition: all 0.2s ease;
        background-color: #fff;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8f9ff;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    /* Badge styles */
    .badge-status {
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.5rem;
        letter-spacing: 0.3px;
    }
    
    .badge-waiting {
        background-color: rgba(255, 171, 0, 0.12) !important;
        color: #e6a800 !important;
        border: 1px solid rgba(255, 171, 0, 0.2);
    }
    
    .badge-progress {
        background-color: rgba(105, 108, 255, 0.12) !important;
        color: #696cff !important;
        border: 1px solid rgba(105, 108, 255, 0.2);
    }
    
    .badge-completed {
        background-color: rgba(40, 199, 111, 0.12) !important;
        color: #28c76f !important;
        border: 1px solid rgba(40, 199, 111, 0.2);
    }
    
    .badge-priority {
        background-color: rgba(255, 76, 81, 0.12) !important;
        color: #ff4c51 !important;
        border: 1px solid rgba(255, 76, 81, 0.2);
    }
    
    /* Button styles */
    .btn-action {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        margin: 0 0.125rem;
        border: 1px solid transparent;
    }
    
    .btn-maps {
        background-color: #f8f9fa;
        color: #6c757d;
        border-color: #e9ecef;
    }
    
    .btn-maps:hover {
        background-color: #e9ecef;
        color: #495057;
        transform: translateY(-1px);
    }
    
    .btn-process {
        background-color: rgba(255, 171, 0, 0.12);
        color: #e6a800;
        border-color: rgba(255, 171, 0, 0.2);
    }
    
    .btn-process:hover {
        background-color: rgba(255, 171, 0, 0.2);
        color: #cc9600;
        transform: translateY(-1px);
    }
    
    .btn-complete {
        background-color: rgba(40, 199, 111, 0.12);
        color: #28c76f;
        border-color: rgba(40, 199, 111, 0.2);
    }
    
    .btn-complete:hover {
        background-color: rgba(40, 199, 111, 0.2);
        color: #24b363;
        transform: translateY(-1px);
    }
    
    /* Search and filter styles */
    .search-input {
        border-radius: 0.5rem;
        border: 1px solid #d9dee3;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        width: 100%;
        max-width: 280px;
        transition: all 0.2s ease;
    }
    
    .search-input:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.1);
        outline: none;
    }
    
    /* Pagination styles */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        padding: 1.25rem 0;
        border-top: 1px solid #f0f0f0;
    }
    
    .pagination-info {
        color: #6c757d;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    /* Status indicator */
    .status-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 0.5rem;
    }
    
    .status-waiting { background-color: #ffab00; }
    .status-progress { background-color: #696cff; }
    .status-completed { background-color: #28c76f; }
    
    /* Customer info styles */
    .customer-info {
        display: flex;
        flex-direction: column;
    }
    
    .customer-name {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.125rem;
    }
    
    .customer-phone {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    /* Empty state styles */
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }
    
    .empty-state-icon {
        font-size: 3rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    
    .empty-state-title {
        color: #6c757d;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .empty-state-text {
        color: #adb5bd;
        font-size: 0.875rem;
    }
    
    /* Section spacing */
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f8f9fa;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-header-elements {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .search-input {
            max-width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .filter-container {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .month-filter {
            min-width: 100%;
        }
        
        .table-modern th,
        .table-modern td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8125rem;
        }
        
        .pagination-container {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        /* Hide less important columns on mobile */
        .table-modern th:nth-child(3),
        .table-modern td:nth-child(3) {
            display: none;
        }
    }

    /* Per page dropdown styles */
.per-page-dropdown .dropdown-toggle::after {
    margin-left: 0.5rem;
}

/* Responsive adjustments untuk dropdown */
@media (max-width: 768px) {
    .card-header-elements .d-flex {
        flex-direction: column;
        width: 100%;
    }
    
    .card-header-elements .d-flex > * {
        margin-bottom: 0.5rem;
        width: 100%;
    }
    
    .search-input {
        margin-right: 0 !important;
    }
}
    
    @media (max-width: 576px) {
        .table-modern th:nth-child(4),
        .table-modern td:nth-child(4),
        .table-modern th:nth-child(5),
        .table-modern td:nth-child(5) {
            display: none;
        }
        
        .btn-action {
            width: 32px;
            height: 32px;
            margin: 0.125rem;
        }
    }
</style>
@endsection

@section('content')
<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3">Filter Data</h6>
                <div class="filter-container">
                    <div class="d-flex align-items-center">
                        <label for="monthFilter" class="form-label mb-0 me-2 fw-semibold">Bulan:</label>
                        <select class="month-filter" id="monthFilter">
                            <option value="">Semua Bulan</option>
                            @foreach ($months as $month)
                                <option value="{{ $month['value'] }}" 
                                    {{ $month['value'] == $currentMonth ? 'selected' : '' }}>
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilter">
                        <i class='bx bx-reset me-1'></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Priority Section -->
<!-- Priority Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-header-elements">
                    <div>
                        <h5 class="card-title">
                            <i class='bx bx-star text-warning me-2'></i>
                            Antrian Priority
                        </h5>
                        <p class="card-subtitle">Pelanggan priority yang menunggu untuk diproses</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="text" class="search-input me-2" placeholder="Cari perusahaan..." id="searchCorp">
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class='bx bx-filter me-1'></i> Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Semua</a></li>
                                <li><a class="dropdown-item" href="#">Terbaru</a></li>
                                <li><a class="dropdown-item" href="#">Terlama</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class='bx bx-list-ul me-1'></i> {{ $corpPerPage == 0 ? 'Semua' : $corpPerPage }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item per-page-item" href="#" data-page="10" data-table="corp">10</a></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="25" data-table="corp">25</a></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="50" data-table="corp">50</a></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="100" data-table="corp">100</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="0" data-table="corp">Semua</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-modern" id="corpTable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Nama Perusahaan</th>
                                <th width="25%">Alamat</th>
                                <th width="10%">Lokasi</th>
                                <th width="15%">Tanggal Registrasi</th>
                                <th width="10%">Status</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($corp as $item)
                            <tr>
                                <td class="text-center fw-semibold text-muted">
                                    {{ $loop->iteration + ($corp->currentPage() - 1) * $corp->perPage() }}
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name">{{ $item->nama_perusahaan }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-truncate-2-lines" title="{{ $item->alamat }}">
                                        {{ Str::limit($item->alamat, 50) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $gps = $item->gps;
                                        $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                        $url = $gps 
                                            ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) 
                                            : '#';
                                    @endphp
                                    <a href="{{ $url }}" 
                                    target="_blank" 
                                    class="btn btn-action btn-maps {{ $gps ? '' : 'disabled' }}" 
                                    data-bs-toggle="tooltip" 
                                    title="{{ $gps ? 'Lihat di Google Maps' : 'Lokasi tidak tersedia' }}">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </td>
                                <td>
                                    <span class="fw-medium text-primary">
                                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-status badge-priority">
                                        <i class='bx bx-time me-1'></i>
                                        {{ $item->status->nama_status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        @if (auth()->user()->roles_id == 5)
                                        <a href="/corp/proses/{{ $item->id }}" class="btn btn-action btn-process me-1" 
                                           data-bs-toggle="tooltip" title="Proses Instalasi">
                                            <i class="bx bx-hard-hat"></i>
                                        </a>
                                        @endif
                                        <a href="/teknisi/detail-antrian/{{ $item->id }}" class="btn btn-action btn-maps" 
                                           data-bs-toggle="tooltip" title="Lihat Detail">
                                            <i class="bx bx-info-circle"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class='bx bx-calendar-check empty-state-icon'></i>
                                        <h6 class="empty-state-title">Tidak ada antrian priority</h6>
                                        <p class="empty-state-text">Semua pelanggan priority telah diproses</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($corp->hasPages() && $corpPerPage > 0)
                <div class="pagination-container">
                    <div class="pagination-info">
                        Menampilkan {{ $corp->firstItem() }} - {{ $corp->lastItem() }} dari {{ $corp->total() }} data
                    </div>
                    <div>
                        {{ $corp->withQueryString()->links() }}
                    </div>
                </div>
                @elseif($corpPerPage == 0)
                <div class="pagination-container">
                    <div class="pagination-info">
                        Menampilkan semua {{ $corp->total() }} data
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Waiting Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-header-elements">
                    <div>
                        <h5 class="card-title">
                            <i class='bx bx-time-five text-info me-2'></i>
                            Antrian Menunggu
                        </h5>
                        <p class="card-subtitle">Daftar pelanggan yang menunggu untuk diproses</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="text" class="search-input me-2" placeholder="Cari pelanggan..." id="searchWaiting">
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class='bx bx-filter me-1'></i> Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Semua</a></li>
                                <li><a class="dropdown-item" href="#">Terbaru</a></li>
                                <li><a class="dropdown-item" href="#">Terlama</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class='bx bx-list-ul me-1'></i> {{ $waitingPerPage == 0 ? 'Semua' : $waitingPerPage }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item per-page-item" href="#" data-page="10" data-table="waiting">10</a></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="25" data-table="waiting">25</a></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="50" data-table="waiting">50</a></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="100" data-table="waiting">100</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="0" data-table="waiting">Semua</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-modern" id="waitingTable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Pelanggan</th>
                                <th width="25%">Alamat</th>
                                <th width="10%">Lokasi</th>
                                <th width="15%">Tanggal Registrasi</th>
                                <th width="10%">Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $waitingCount = 0; @endphp
                            @foreach ($data as $key => $item)
                            @if ($item->status_id == '5')
                            @php $waitingCount++; @endphp
                            <tr>
                                <td class="text-center fw-semibold text-muted">
                                    {{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name">{{ $item->nama_customer }}</span>
                                        <small class="customer-phone">{{ $item->no_hp ?? 'No. HP tidak tersedia' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-truncate-2-lines" title="{{ $item->alamat }}">
                                        {{ Str::limit($item->alamat, 50) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $gps = $item->gps;
                                        $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                        $url = $gps 
                                            ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) 
                                            : '#';
                                    @endphp
                                    <a href="{{ $url }}" 
                                    target="_blank" 
                                    class="btn btn-action btn-maps {{ $gps ? '' : 'disabled' }}" 
                                    data-bs-toggle="tooltip" 
                                    title="{{ $gps ? 'Lihat di Google Maps' : 'Lokasi tidak tersedia' }}">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </td>
                                <td>
                                    <span class="fw-medium text-primary">
                                        {{ $item->created_at->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-status badge-waiting">
                                        <span class="status-indicator status-waiting"></span>
                                        {{ $item->status->nama_status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        @if (auth()->user()->roles->name == 'Teknisi')
                                        <a href="/teknisi/selesai/{{ $item->id }}" class="btn btn-action btn-process me-1" 
                                           data-bs-toggle="tooltip" title="Proses Instalasi">
                                            <i class="bx bx-hard-hat"></i>
                                        </a>
                                        @elseif(auth()->user()->roles->name == 'NOC')
                                        <a href="/edit/antrian/{{ $item->id }}/noc" class="btn btn-action btn-complete me-1" 
                                           data-bs-toggle="tooltip" title="Edit Detail">
                                            <i class="bx bx-pencil"></i>
                                        </a>
                                        @endif
                                        <a href="/teknisi/detail-antrian/{{ $item->id }}" class="btn btn-action btn-maps" 
                                           data-bs-toggle="tooltip" title="Lihat Detail">
                                            <i class="bx bx-info-circle"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                            @if ($waitingCount == 0)
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class='bx bx-user-check empty-state-icon'></i>
                                        <h6 class="empty-state-title">Tidak ada antrian menunggu</h6>
                                        <p class="empty-state-text">Semua pelanggan telah diproses</p>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if($data->hasPages() && $waitingPerPage > 0)
                <div class="pagination-container">
                    <div class="pagination-info">
                        Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} data
                    </div>
                    <div>
                        {{ $data->withQueryString()->links() }}
                    </div>
                </div>
                @elseif($waitingPerPage == 0)
                <div class="pagination-container">
                    <div class="pagination-info">
                        Menampilkan semua {{ $data->total() }} data
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Progress Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-header-elements">
                    <div>
                        <h5 class="card-title">
                            <i class='bx bx-loader-circle text-primary me-2'></i>
                            Instalasi Dalam Proses
                        </h5>
                        <p class="card-subtitle">Pelanggan yang sedang dalam proses instalasi</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="text" class="search-input me-2" placeholder="Cari pelanggan..." id="searchProgress">
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class='bx bx-filter me-1'></i> Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Semua</a></li>
                                <li><a class="dropdown-item" href="#">Terbaru</a></li>
                                <li><a class="dropdown-item" href="#">Terlama</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class='bx bx-list-ul me-1'></i> {{ $progressPerPage == 0 ? 'Semua' : $progressPerPage }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item per-page-item" href="#" data-page="10" data-table="progress">10</a></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="25" data-table="progress">25</a></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="50" data-table="progress">50</a></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="100" data-table="progress">100</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item per-page-item" href="#" data-page="0" data-table="progress">Semua</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-modern" id="progressTable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Pelanggan</th>
                                <th width="20%">Alamat</th>
                                <th width="10%">Lokasi</th>
                                <th width="15%">Tanggal Registrasi</th>
                                <th width="10%">Status</th>
                                <th width="10%">Aksi</th>
                                <th width="10%">Teknisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $progressCount = 0; @endphp
                            @foreach ($progressData as $key => $item)
                            @if (auth()->user()->id == $item->teknisi_id or auth()->user()->roles->name == 'NOC' or auth()->user()->roles->name == 'Super Admin')
                            @if ($item->status_id == '2' or $item->status_id == '3')
                            @php $progressCount++; @endphp
                            <tr>
                                <td class="text-center fw-semibold text-muted">
                                    {{ $loop->iteration + ($progressData->currentPage() - 1) * $progressData->perPage() }}
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name">{{ $item->nama_customer }}</span>
                                        <small class="customer-phone">{{ $item->no_hp ?? 'No. HP tidak tersedia' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-truncate-2-lines" title="{{ $item->alamat }}">
                                        {{ Str::limit($item->alamat, 40) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ $item->gps }}" target="_blank" class="btn btn-action btn-maps" 
                                       data-bs-toggle="tooltip" title="Lihat di Google Maps">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </td>
                                <td>
                                    <span class="fw-medium badge bg-label-info text-primary">
                                        {{ $item->created_at->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->status_id == 2)
                                    <span class="badge bg-label-warning">
                                        <span class="status-indicator bg-warning"></span>
                                        {{ $item->status->nama_status }}
                                    </span>
                                    @elseif($item->status_id == 3)
                                    <span class="badge bg-label-success">
                                        <span class="status-indicator bg-success"></span>
                                        {{ $item->status->nama_status }}
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        @if(auth()->user()->roles->name == 'Teknisi')
                                        <a href="/teknisi/selesai/{{ $item->id }}/print" class="btn btn-action btn-complete me-1" 
                                           data-bs-toggle="tooltip" title="Selesaikan Instalasi">
                                            <i class="bx bx-check-circle"></i>
                                        </a>
                                        @elseif(auth()->user()->roles->name == 'NOC')
                                        <a href="/edit/antrian/{{ $item->id }}/noc" class="btn btn-action btn-complete me-1" 
                                           data-bs-toggle="tooltip" title="Edit Detail">
                                            <i class="bx bx-pencil"></i>
                                        </a>
                                        @else
                                        -
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-danger border">
                                        {{ strtoupper($item->teknisi->name ?? '-') }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                            @endif
                            @endforeach
                            
                            @if ($progressCount == 0)
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class='bx bx-hard-hat empty-state-icon'></i>
                                        <h6 class="empty-state-title">Tidak ada instalasi dalam proses</h6>
                                        <p class="empty-state-text">Belum ada pelanggan yang sedang diproses</p>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if($progressData->hasPages() && $progressPerPage > 0)
                <div class="pagination-container">
                    <div class="pagination-info">
                        Menampilkan {{ $progressData->firstItem() }} - {{ $progressData->lastItem() }} dari {{ $progressData->total() }} data
                    </div>
                    <div>
                        {{ $progressData->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
                @elseif($progressPerPage == 0)
                <div class="pagination-container">
                    <div class="pagination-info">
                        Menampilkan semua {{ $progressData->total() }} data
                    </div>
                </div>
                @endif
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
        
        // Month filter functionality
        const monthFilter = document.getElementById('monthFilter');
        const resetFilter = document.getElementById('resetFilter');
        
        monthFilter.addEventListener('change', function() {
            const selectedMonth = this.value;
            const url = new URL(window.location.href);
            
            if (selectedMonth) {
                url.searchParams.set('month', selectedMonth);
            } else {
                url.searchParams.delete('month');
            }
            
            // Reset to page 1 when filtering
            url.searchParams.set('corp_page', '1');
            url.searchParams.set('waiting_page', '1');
            url.searchParams.set('progress_page', '1');
            
            window.location.href = url.toString();
        });
        
        resetFilter.addEventListener('click', function() {
            const url = new URL(window.location.href);
            url.searchParams.delete('month');
            // Reset semua page ke 1
            url.searchParams.set('corp_page', '1');
            url.searchParams.set('waiting_page', '1');
            url.searchParams.set('progress_page', '1');
            window.location.href = url.toString();
        });
        
        // Per page functionality
        document.querySelectorAll('.per-page-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const perPage = this.getAttribute('data-page');
                const table = this.getAttribute('data-table');
                const url = new URL(window.location.href);
                
                // Set per page parameter berdasarkan tabel
                if (table === 'corp') {
                    url.searchParams.set('corp_per_page', perPage);
                    url.searchParams.set('corp_page', '1'); // Reset ke halaman 1
                } else if (table === 'waiting') {
                    url.searchParams.set('waiting_per_page', perPage);
                    url.searchParams.set('waiting_page', '1'); // Reset ke halaman 1
                } else if (table === 'progress') {
                    url.searchParams.set('progress_per_page', perPage);
                    url.searchParams.set('progress_page', '1'); // Reset ke halaman 1
                }
                
                window.location.href = url.toString();
            });
        });
        
        // Search functionality
        document.getElementById('searchCorp').addEventListener('keyup', function() {
            searchTable('corpTable', this.value);
        });
        
        document.getElementById('searchWaiting').addEventListener('keyup', function() {
            searchTable('waitingTable', this.value);
        });
        
        document.getElementById('searchProgress').addEventListener('keyup', function() {
            searchTable('progressTable', this.value);
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
        
        // Add CSS for text truncation
        const style = document.createElement('style');
        style.textContent = `
            .text-truncate-2-lines {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                line-height: 1.4;
            }
        `;
        document.head.appendChild(style);
    });
</script>

@endsection