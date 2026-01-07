@extends('layouts.contentNavbarLayout')

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
        font-size: 0.8rem;
    }
    
    .table-minimalist th {
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
        padding: 0.75rem;
        border-bottom: 2px solid #e9ecef;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    
    .table-minimalist td {
        padding: 0.75rem;
        border-bottom: 1px solid #f8f9fa;
        color: #4a5568;
        transition: all 0.2s ease;
        vertical-align: middle;
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
        padding: 0.35rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 500;
        border-radius: 6px;
        border: 1px solid;
        white-space: nowrap;
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
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #6c757d;
        transition: all 0.2s ease;
        margin: 0 0.125rem;
        font-size: 0.8rem;
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
        min-width: 120px;
    }
    
    .customer-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.125rem;
        font-size: 0.8rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .customer-phone {
        font-size: 0.7rem;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Search & Controls */
    .search-input {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        width: 100%;
        max-width: 200px;
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
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* Empty State */
    .empty-state {
        padding: 2rem 1rem;
        text-align: center;
        color: #6c757d;
    }
    
    .empty-state-icon {
        font-size: 2.5rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    
    .empty-state-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    /* ==================== */
    /* RESPONSIVE DESIGN - TABLET & MOBILE */
    /* ==================== */

    @media (max-width: 1200px) {
        .table-minimalist {
            font-size: 0.75rem;
        }
        
        .table-minimalist th,
        .table-minimalist td {
            padding: 0.6rem 0.5rem;
        }
        
        .btn-icon {
            width: 28px;
            height: 28px;
            font-size: 0.7rem;
        }
        
        .badge-minimal {
            padding: 0.25rem 0.4rem;
            font-size: 0.65rem;
        }
    }

    @media (max-width: 992px) {
        /* Hide less important columns on tablet */
        .table-minimalist th:nth-child(3),
        .table-minimalist td:nth-child(3),
        .table-minimalist th:nth-child(4),
        .table-minimalist td:nth-child(4) {
            display: none;
        }
        
        .card-header-elements {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .card-header-elements .d-flex {
            width: 100%;
            justify-content: space-between;
        }
        
        .search-input {
            max-width: 180px;
        }
    }

    @media (max-width: 768px) {
        .table-minimalist {
            font-size: 0.7rem;
        }
        
        .table-minimalist th,
        .table-minimalist td {
            padding: 0.5rem 0.3rem;
        }
        
        /* Hide more columns on smaller tablets */
        .table-minimalist th:nth-child(5),
        .table-minimalist td:nth-child(5) {
            display: none;
        }
        
        .customer-info {
            min-width: 100px;
        }
        
        .customer-name {
            font-size: 0.75rem;
        }
        
        .customer-phone {
            font-size: 0.65rem;
        }
        
        .btn-icon {
            width: 26px;
            height: 26px;
            margin: 0 0.1rem;
        }
        
        .search-input {
            max-width: 150px;
            font-size: 0.75rem;
        }
        
        .card-header {
            padding: 1rem;
        }
        
        .card-title {
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        /* Mobile - Keep table layout but make it horizontally scrollable */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table-minimalist {
            min-width: 600px; /* Force horizontal scroll on very small screens */
            font-size: 0.7rem;
        }
        
        .table-minimalist th,
        .table-minimalist td {
            padding: 0.4rem 0.3rem;
        }
        
        /* Show only essential columns on very small screens */
        .table-minimalist th:nth-child(1),
        .table-minimalist td:nth-child(1),
        .table-minimalist th:nth-child(2),
        .table-minimalist td:nth-child(2),
        .table-minimalist th:nth-child(3),
        .table-minimalist td:nth-child(3),
        .table-minimalist th:nth-child(4),
        .table-minimalist td:nth-child(4),
        .table-minimalist th:nth-child(5),
        .table-minimalist td:nth-child(5),
        .table-minimalist th:nth-child(6),
        .table-minimalist td:nth-child(6),
        .table-minimalist th:nth-child(7),
        .table-minimalist td:nth-child(7) {
            display: table-cell;
        }
        
        /* Hide additional columns */
        .table-minimalist th:nth-child(8),
        .table-minimalist td:nth-child(8) {
            display: none;
        }
        
        .customer-info {
            min-width: 80px;
        }
        
        .customer-name {
            font-size: 0.7rem;
        }
        
        .customer-phone {
            font-size: 0.6rem;
        }
        
        .btn-icon {
            width: 24px;
            height: 24px;
            font-size: 0.65rem;
        }
        
        .badge-minimal {
            padding: 0.2rem 0.3rem;
            font-size: 0.6rem;
        }
        
        .search-input {
            max-width: 120px;
            font-size: 0.7rem;
            padding: 0.4rem 0.6rem;
        }
        
        .filter-container {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .month-filter {
            min-width: 100%;
            font-size: 0.8rem;
        }
        
        .card-header-elements .d-flex {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .card-header-elements .d-flex > * {
            width: 100%;
        }
        
        .search-input {
            max-width: 100%;
            margin-right: 0 !important;
        }
        
        .pagination-container {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }

    @media (max-width: 400px) {
        .table-minimalist {
            min-width: 550px;
            font-size: 0.65rem;
        }
        
        .table-minimalist th,
        .table-minimalist td {
            padding: 0.35rem 0.25rem;
        }
        
        .btn-icon {
            width: 22px;
            height: 22px;
        }
    }

    /* Text truncation */
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.3;
        max-width: 200px;
    }

    /* Ensure table maintains proper layout */
    .table-minimalist {
        table-layout: auto;
    }
    
    .table-minimalist th,
    .table-minimalist td {
        /* white-space: nowrap; */
    }
    
    .table-minimalist td:first-child,
    .table-minimalist th:first-child {
        width: 60px;
    }
    
    .table-minimalist td:last-child,
    .table-minimalist th:last-child {
        width: 100px;
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
                            @for ($i = 1; $i <= 12; $i++)
                                @php
                                    $namaBulan = \Carbon\Carbon::create()->month($i)->locale('id')->monthName;
                                @endphp
                                <option value="{{ $i }}" {{ $i == $currentMonth ? 'selected' : '' }}>
                                    {{ ucfirst($namaBulan) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="d-flex align-items-center">
                        <label for="yearFilter" class="form-label mb-0 me-2 fw-semibold">Tahun:</label>
                        <select class="month-filter" id="yearFilter">
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                    {{ $year }}
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
                                        {{ $item->alamat }}
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
                                    <span class="badge bg-label-info">
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
                
                <!-- Di bagian Priority Section pagination -->
                @if(auth()->user()->roles_id != 4 && $corpPerPage > 0 && method_exists($corp, 'hasPages') && $corp->hasPages())
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
                        Menampilkan semua {{ count($corp) }} data
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
                                        {{ $item->alamat }}
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
                                    <span class="badge bg-label-info">
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
                
                <!-- Di bagian Waiting Section pagination -->
                @if(auth()->user()->roles_id != 4 && $waitingPerPage > 0 && method_exists($data, 'hasPages') && $data->hasPages())
                <div class="pagination-container">
                    <div class="pagination-info">
                        Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} data
                    </div>
                    <div>
                        {{ $data->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
                @elseif($waitingPerPage == 0)
                <div class="pagination-container">
                    <div class="pagination-info">
                        Menampilkan semua {{ count($data) }} data
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
                                        {{ $item->alamat }}
                                    </span>
                                </td>
                                <td class="text-center" data-label="Lokasi">
                                    <a href="{{ $item->gps }}" target="_blank" class="btn btn-icon" 
                                       data-bs-toggle="tooltip" title="Lihat di Google Maps">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </td>
                                <td data-label="Tanggal">
                                    <span class="badge bg-label-info">
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
                                         <a href="/batalkan/{{ $item->id }}" class="btn btn-icon btn-complete bg-label-danger me-1 @if($item->status_id == 3) disabled @endif" data-bs-toggle="tooltip" title="Batalkan" data-bs-placement="bottom">
                                            <i class="bx bx-x"></i>
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
                
                <!-- Di bagian Progress Section pagination -->
                @if(auth()->user()->roles_id != 4 && $progressPerPage > 0 && method_exists($progressData, 'hasPages') && $progressData->hasPages())
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
                        Menampilkan semua {{ count($progressData) }} data
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
        
        // Month and Year filter functionality
        const monthFilter = document.getElementById('monthFilter');
        const yearFilter = document.getElementById('yearFilter');
        const resetFilter = document.getElementById('resetFilter');
        
        function applyFilters() {
            const selectedMonth = monthFilter.value;
            const selectedYear = yearFilter.value;
            const url = new URL(window.location.href);
            
            // Set month filter
            if (selectedMonth) {
                url.searchParams.set('month', selectedMonth);
            } else {
                url.searchParams.delete('month');
            }
            
            // Set year filter
            if (selectedYear) {
                url.searchParams.set('year', selectedYear);
            } else {
                url.searchParams.delete('year');
            }
            
            // Reset to page 1 when filtering
            url.searchParams.set('corp_page', '1');
            url.searchParams.set('waiting_page', '1');
            url.searchParams.set('progress_page', '1');
            
            window.location.href = url.toString();
        }
        
        monthFilter.addEventListener('change', applyFilters);
        yearFilter.addEventListener('change', applyFilters);
        
        resetFilter.addEventListener('click', function() {
            const url = new URL(window.location.href);
            url.searchParams.delete('month');
            url.searchParams.delete('year');
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