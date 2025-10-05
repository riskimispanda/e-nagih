@extends('layouts/contentNavbarLayout')

@section('title', 'Antrian Instalasi')

@section('vendor-style')
<style>
    /* Base Styles */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        background: #fff;
    }
    
    .card-header {
        background: transparent;
        border-bottom: 1px solid #f0f0f0;
        padding: 1.5rem;
    }
    
    .card-title {
        font-weight: 600;
        color: #2c3e50;
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
    }
    
    .card-subtitle {
        color: #6c757d;
        font-size: 0.875rem;
    }

    /* Filter Section */
    .filter-container {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .month-filter {
        min-width: 160px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        background: #fff;
    }

    /* Table Styles - Minimalist */
    .table-minimalist {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
        background: #fff;
    }
    
    .table-minimalist th {
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
        font-size: 0.8rem;
        padding: 1rem;
        border-bottom: 2px solid #e9ecef;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table-minimalist td {
        padding: 1rem;
        border-bottom: 1px solid #f8f9fa;
        color: #4a5568;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .table-minimalist tbody tr {
        background: #fff;
        transition: all 0.2s ease;
    }
    
    .table-minimalist tbody tr:hover {
        background: #f8f9ff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* Badge Styles */
    .badge-minimal {
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 6px;
        border: 1px solid;
    }
    
    .badge-waiting {
        background: rgba(255, 171, 0, 0.1);
        color: #e6a800;
        border-color: rgba(255, 171, 0, 0.2);
    }
    
    .badge-progress {
        background: rgba(105, 108, 255, 0.1);
        color: #696cff;
        border-color: rgba(105, 108, 255, 0.2);
    }
    
    .badge-completed {
        background: rgba(40, 199, 111, 0.1);
        color: #28c76f;
        border-color: rgba(40, 199, 111, 0.2);
    }
    
    .badge-priority {
        background: rgba(255, 76, 81, 0.1);
        color: #ff4c51;
        border-color: rgba(255, 76, 81, 0.2);
    }

    /* Button Styles */
    .btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #6c757d;
        transition: all 0.2s ease;
        margin: 0 0.125rem;
    }
    
    .btn-icon:hover {
        background: #f8f9fa;
        color: #495057;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .btn-process {
        background: rgba(255, 171, 0, 0.1);
        color: #e6a800;
        border-color: rgba(255, 171, 0, 0.2);
    }
    
    .btn-complete {
        background: rgba(40, 199, 111, 0.1);
        color: #28c76f;
        border-color: rgba(40, 199, 111, 0.2);
    }

    /* Customer Info */
    .customer-info {
        display: flex;
        flex-direction: column;
    }
    
    .customer-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.125rem;
    }
    
    .customer-phone {
        font-size: 0.75rem;
        color: #6c757d;
    }

    /* Search & Controls */
    .search-input {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        width: 100%;
        max-width: 240px;
        transition: all 0.2s ease;
    }
    
    .search-input:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.1);
        outline: none;
    }

    /* Pagination */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        padding: 1.5rem 0 0;
        border-top: 1px solid #f0f0f0;
    }
    
    .pagination-info {
        color: #6c757d;
        font-size: 0.875rem;
        font-weight: 500;
    }

    /* Empty State */
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: #6c757d;
    }
    
    .empty-state-icon {
        font-size: 3rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    
    .empty-state-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    /* ==================== */
    /* RESPONSIVE DESIGN */
    /* ==================== */

    @media (max-width: 1024px) {
        .table-minimalist th:nth-child(4),
        .table-minimalist td:nth-child(4),
        .table-minimalist th:nth-child(5),
        .table-minimalist td:nth-child(5) {
            display: none;
        }
    }

    @media (max-width: 768px) {
        /* Card Header */
        .card-header-elements {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .card-header-elements .d-flex {
            flex-direction: column;
            width: 100%;
        }
        
        .card-header-elements .d-flex > * {
            margin-bottom: 0.75rem;
            width: 100%;
        }
        
        .search-input {
            max-width: 100%;
            margin-right: 0 !important;
        }

        /* Filter */
        .filter-container {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .month-filter {
            min-width: 100%;
        }

        /* Table Mobile Layout */
        .table-minimalist {
            display: block;
        }
        
        .table-minimalist thead {
            display: none;
        }
        
        .table-minimalist tbody,
        .table-minimalist tr,
        .table-minimalist td {
            display: block;
            width: 100%;
        }
        
        .table-minimalist tr {
            margin-bottom: 1rem;
            border: 1px solid #f0f0f0;
            border-radius: 12px;
            padding: 1rem;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        
        .table-minimalist td {
            padding: 0.75rem 0.5rem;
            border: none;
            position: relative;
            padding-left: 45%;
            display: flex;
            align-items: center;
            min-height: 44px;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .table-minimalist td:last-child {
            border-bottom: none;
            justify-content: center;
            padding: 1rem 0.5rem;
        }
        
        .table-minimalist td::before {
            content: attr(data-label);
            position: absolute;
            left: 0.5rem;
            width: 40%;
            font-weight: 600;
            color: #495057;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Hide less important columns on mobile */
        .table-minimalist td:nth-child(3),
        .table-minimalist td:nth-child(4),
        .table-minimalist td:nth-child(5) {
            display: none;
        }

        /* Customer info mobile optimization */
        .customer-info {
            width: 100%;
        }
        
        .customer-name {
            font-size: 0.9rem;
        }
        
        .customer-phone {
            font-size: 0.7rem;
        }

        /* Badge mobile optimization */
        .badge-minimal {
            padding: 0.3rem 0.6rem;
            font-size: 0.7rem;
        }

        /* Button mobile optimization */
        .btn-icon {
            width: 40px;
            height: 40px;
            margin: 0 0.25rem;
        }

        /* Pagination mobile */
        .pagination-container {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .table-minimalist td {
            padding-left: 50%;
            min-height: 40px;
            font-size: 0.8rem;
        }
        
        .table-minimalist td::before {
            width: 45%;
            font-size: 0.7rem;
        }
        
        .table-minimalist tr {
            padding: 0.75rem;
        }
        
        .btn-icon {
            width: 36px;
            height: 36px;
        }
        
        .card {
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding: 1.25rem;
        }
    }

    /* Text truncation */
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
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
                        <i class='bx bx-reset me-1'></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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
                    <table class="table table-minimalist" id="corpTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Perusahaan</th>
                                <th>Alamat</th>
                                <th>Lokasi</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($corp as $item)
                            <tr>
                                <td class="text-center fw-semibold text-muted" data-label="No">
                                    {{ $loop->iteration + ($corp->currentPage() - 1) * $corp->perPage() }}
                                </td>
                                <td data-label="Perusahaan">
                                    <div class="customer-info">
                                        <span class="customer-name">{{ $item->nama_perusahaan }}</span>
                                    </div>
                                </td>
                                <td data-label="Alamat">
                                    <span class="text-truncate-2" title="{{ $item->alamat }}">
                                        {{ Str::limit($item->alamat, 50) }}
                                    </span>
                                </td>
                                <td class="text-center" data-label="Lokasi">
                                    @php
                                        $gps = $item->gps;
                                        $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                        $url = $gps 
                                            ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) 
                                            : '#';
                                    @endphp
                                    <a href="{{ $url }}" 
                                    target="_blank" 
                                    class="btn btn-icon {{ $gps ? '' : 'disabled' }}" 
                                    data-bs-toggle="tooltip" 
                                    title="{{ $gps ? 'Lihat di Google Maps' : 'Lokasi tidak tersedia' }}">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </td>
                                <td data-label="Tanggal">
                                    <span class="text-muted">
                                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="badge badge-minimal badge-priority">
                                        {{ $item->status->nama_status }}
                                    </span>
                                </td>
                                <td data-label="Aksi">
                                    <div class="d-flex justify-content-center">
                                        @if (auth()->user()->roles_id == 5)
                                        <a href="/corp/proses/{{ $item->id }}" class="btn btn-icon btn-process me-1" 
                                           data-bs-toggle="tooltip" title="Proses Instalasi">
                                            <i class="bx bx-hard-hat"></i>
                                        </a>
                                        @endif
                                        <a href="/teknisi/detail-antrian/{{ $item->id }}" class="btn btn-icon" 
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
                                        <p class="card-subtitle">Semua pelanggan priority telah diproses</p>
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
                        {{ $corp->withQueryString()->onEachSide(1)->links() }}
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
                    <table class="table table-minimalist" id="waitingTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pelanggan</th>
                                <th>Alamat</th>
                                <th>Lokasi</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $waitingCount = 0; @endphp
                            @foreach ($data as $key => $item)
                            @if ($item->status_id == '5')
                            @php $waitingCount++; @endphp
                            <tr>
                                <td class="text-center fw-semibold text-muted" data-label="No">
                                    {{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}
                                </td>
                                <td data-label="Pelanggan">
                                    <div class="customer-info">
                                        <span class="customer-name">{{ $item->nama_customer }}</span>
                                        <small class="customer-phone">{{ $item->no_hp ?? 'No. HP tidak tersedia' }}</small>
                                    </div>
                                </td>
                                <td data-label="Alamat">
                                    <span class="text-truncate-2" title="{{ $item->alamat }}">
                                        {{ Str::limit($item->alamat, 50) }}
                                    </span>
                                </td>
                                <td class="text-center" data-label="Lokasi">
                                    @php
                                        $gps = $item->gps;
                                        $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                        $url = $gps 
                                            ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) 
                                            : '#';
                                    @endphp
                                    <a href="{{ $url }}" 
                                    target="_blank" 
                                    class="btn btn-icon {{ $gps ? '' : 'disabled' }}" 
                                    data-bs-toggle="tooltip" 
                                    title="{{ $gps ? 'Lihat di Google Maps' : 'Lokasi tidak tersedia' }}">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </td>
                                <td data-label="Tanggal">
                                    <span class="text-muted">
                                        {{ $item->created_at->format('d M Y') }}
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="badge badge-minimal badge-waiting">
                                        {{ $item->status->nama_status }}
                                    </span>
                                </td>
                                <td data-label="Aksi">
                                    <div class="d-flex justify-content-center">
                                        @if (auth()->user()->roles->name == 'Teknisi')
                                        <a href="/teknisi/selesai/{{ $item->id }}" class="btn btn-icon btn-process me-1" 
                                           data-bs-toggle="tooltip" title="Proses Instalasi">
                                            <i class="bx bx-hard-hat"></i>
                                        </a>
                                        @elseif(auth()->user()->roles->name == 'NOC')
                                        <a href="/edit/antrian/{{ $item->id }}/noc" class="btn btn-icon btn-complete me-1" 
                                           data-bs-toggle="tooltip" title="Edit Detail">
                                            <i class="bx bx-pencil"></i>
                                        </a>
                                        @endif
                                        <a href="/teknisi/detail-antrian/{{ $item->id }}" class="btn btn-icon" 
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
                                        <p class="card-subtitle">Semua pelanggan telah diproses</p>
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
                        {{ $data->withQueryString()->onEachSide(1)->links() }}
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
                    <table class="table table-minimalist" id="progressTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pelanggan</th>
                                <th>Alamat</th>
                                <th>Lokasi</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                                <th>Teknisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $progressCount = 0; @endphp
                            @foreach ($progressData as $key => $item)
                            @if (auth()->user()->id == $item->teknisi_id or auth()->user()->roles->name == 'NOC' or auth()->user()->roles->name == 'Super Admin')
                            @if ($item->status_id == '2' or $item->status_id == '3')
                            @php $progressCount++; @endphp
                            <tr>
                                <td class="text-center fw-semibold text-muted" data-label="No">
                                    {{ $loop->iteration + ($progressData->currentPage() - 1) * $progressData->perPage() }}
                                </td>
                                <td data-label="Pelanggan">
                                    <div class="customer-info">
                                        <span class="customer-name">{{ $item->nama_customer }}</span>
                                        <small class="customer-phone">{{ $item->no_hp ?? 'No. HP tidak tersedia' }}</small>
                                    </div>
                                </td>
                                <td data-label="Alamat">
                                    <span class="text-truncate-2" title="{{ $item->alamat }}">
                                        {{ Str::limit($item->alamat, 40) }}
                                    </span>
                                </td>
                                <td class="text-center" data-label="Lokasi">
                                    <a href="{{ $item->gps }}" target="_blank" class="btn btn-icon" 
                                       data-bs-toggle="tooltip" title="Lihat di Google Maps">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </td>
                                <td data-label="Tanggal">
                                    <span class="text-muted">
                                        {{ $item->created_at->format('d M Y') }}
                                    </span>
                                </td>
                                <td data-label="Status">
                                    @if($item->status_id == 2)
                                    <span class="badge badge-minimal badge-progress">
                                        {{ $item->status->nama_status }}
                                    </span>
                                    @elseif($item->status_id == 3)
                                    <span class="badge badge-minimal badge-completed">
                                        {{ $item->status->nama_status }}
                                    </span>
                                    @endif
                                </td>
                                <td data-label="Aksi">
                                    <div class="d-flex justify-content-center">
                                        @if(auth()->user()->roles->name == 'Teknisi')
                                        <a href="/teknisi/selesai/{{ $item->id }}/print" 
                                            class="btn btn-icon btn-complete me-1 @if($item->status_id == 3) disabled @endif" 
                                            data-bs-toggle="tooltip" 
                                            title="@if($item->status_id == 3) Sudah Selesai @else Selesaikan Instalasi @endif"
                                            @if($item->status_id == 3) aria-disabled="true" tabindex="-1" @endif>
                                             <i class="bx bx-check-circle"></i>
                                         </a>
                                        @elseif(auth()->user()->roles->name == 'NOC')
                                        <a href="/edit/antrian/{{ $item->id }}/noc" class="btn btn-icon btn-complete me-1" 
                                           data-bs-toggle="tooltip" title="Edit Detail">
                                            <i class="bx bx-pencil"></i>
                                        </a>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center" data-label="Teknisi">
                                    <span class="badge bg-label-warning">
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
                                        <p class="card-subtitle">Belum ada pelanggan yang sedang diproses</p>
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
                        {{ $progressData->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
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
    });
</script>

@endsection