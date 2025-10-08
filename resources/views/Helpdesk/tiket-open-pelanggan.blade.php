@extends('layouts.contentNavbarLayout')

@section('title', 'Tiket Open')
<style>
    /* Modern Card Styling */
    .modern-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .modern-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-bottom: 1px solid #e9ecef;
        padding: 1.25rem 1.5rem;
    }
    
    .modern-card-footer {
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
    }
    
    /* Modern Table Container */
    .modern-table-container {
        overflow-x: auto;
    }
    
    .modern-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    /* Table Header */
    .modern-table-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .modern-table-header th {
        border: none;
        padding: 1rem 0.75rem;
        font-weight: 600;
        color: white;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table-header-content {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .table-col-customer .table-header-content,
    .table-col-contact .table-header-content {
        justify-content: flex-start;
    }
    
    /* Table Body */
    .modern-table-body {
        background: white;
    }
    
    .modern-table-row {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .modern-table-row:hover {
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.1);
    }
    
    .modern-table-row:last-child {
        border-bottom: none;
    }
    
    .modern-table-row td {
        padding: 1rem 0.75rem;
        border: none;
        vertical-align: middle;
    }
    
    /* Column Specific Styling */
    .table-col-number {
        width: 60px;
        text-align: center;
    }
    
    .table-number {
        background: #f8f9fa;
        border-radius: 8px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #667eea;
        margin: 0 auto;
    }
    
    .table-col-customer {
        min-width: 250px;
    }
    
    .customer-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .customer-avatar .avatar-initial {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    
    .customer-details {
        flex: 1;
    }
    
    .customer-name {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }
    
    .customer-address {
        color: #718096;
        font-size: 0.825rem;
        margin: 0;
        line-height: 1.3;
    }
    
    .table-col-contact {
        min-width: 160px;
    }
    
    .contact-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
    }
    
    .contact-number {
        font-weight: 500;
        color: #2d3748;
    }
    
    .table-col-location {
        width: 80px;
    }
    
    .btn-location {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: white;
        color: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .btn-location:hover {
        background: #667eea;
        color: white;
        transform: scale(1.1);
        border-color: #667eea;
    }
    
    .table-col-status {
        width: 120px;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .status-active {
        background: rgba(72, 187, 120, 0.1);
        color: #48bb78;
        border: 1px solid rgba(72, 187, 120, 0.2);
    }
    
    .status-maintenance {
        background: rgba(245, 101, 101, 0.1);
        color: #f56565;
        border: 1px solid rgba(245, 101, 101, 0.2);
    }
    
    .table-col-action {
        width: 80px;
    }
    
    .btn-action {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .btn-open {
        background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
        color: white;
        border: none;
    }
    
    .btn-open:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(245, 101, 101, 0.3);
    }
    
    .btn-locked {
        background: #f7fafc;
        color: #a0aec0;
        border: 1px solid #e2e8f0;
    }
    
    /* Pagination Modern */
    .modern-pagination .pagination {
        margin: 0;
    }
    
    .modern-pagination .page-link {
        border: none;
        border-radius: 8px;
        margin: 0 2px;
        color: #718096;
        font-weight: 500;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
    }
    
    .modern-pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transform: scale(1.05);
    }
    
    .modern-pagination .page-link:hover {
        background: #667eea;
        color: white;
        transform: translateY(-1px);
    }
    
    /* Empty State */
    .empty-state {
        padding: 3rem 1rem;
    }
    
    /* Card Hover Effects */
    .card-hover {
        transition: all 0.3s ease;
    }
    
    .card-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    /* Clear button styling */
    .input-group .btn-outline-secondary {
        border-left: none;
        border-color: #d9dee3;
        display: none;
    }
    
    .input-group .btn-outline-secondary:hover {
        background-color: #f8f9fa;
        color: #697a8d;
    }
    
    /* Loading state for search */
    .search-loading {
        position: relative;
    }
    
    .search-loading::after {
        content: '';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        display: none;
    }
    
    @keyframes spin {
        0% { transform: translateY(-50%) rotate(0deg); }
        100% { transform: translateY(-50%) rotate(360deg); }
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .modern-table-container {
            border-radius: 0;
        }
        
        .modern-table {
            min-width: 800px;
        }
        
        .modern-card-header {
            padding: 1rem;
        }
        
        .modern-card-footer {
            padding: 1rem;
        }
        
        .modern-card-footer .d-flex {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .table-info {
            order: 2;
        }
        
        .modern-pagination {
            order: 1;
        }
    }
    
    /* Scrollbar Styling */
    .modern-table-container::-webkit-scrollbar {
        height: 6px;
    }
    
    .modern-table-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    
    .modern-table-container::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 3px;
    }
    
    .modern-table-container::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
</style>
@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title fw-bold">Tiket Open</h4>
                    <small class="card-subtitle text-muted">Daftar tiket yang sedang terbuka</small>
                </div>
                <a href="/tiket-barang" class="btn btn-danger">
                    <i class="bx bx-package me-2"></i>Tiket Barang Keluar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card card-hover h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h6 class="card-title text-muted mb-2">Total Data Pelanggan</h6>
                        <h3 class="text-primary mb-0">{{ $customer->total() ?? $customer->count() }}</h3>
                    </div>
                    <div class="avatar">
                        <div class="avatar-initial bg-label-primary rounded-circle">
                            <i class="bx bxs-user"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card card-hover h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h6 class="card-title text-muted mb-2">Tiket Aktif</h6>
                        <h3 class="text-danger mb-0">
                            {{ $customer->where('status.nama_status', 'Maintenance')->count() }}
                        </h3>
                    </div>
                    <div class="avatar">
                        <div class="avatar-initial bg-label-danger rounded-circle">
                            <i class="bx bxs-wrench"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card card-hover h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h6 class="card-title text-muted mb-2">Tiket Closed</h6>
                        <h3 class="text-success mb-0">
                            {{ $customer->where('status.nama_status', 'Maintenance')->count() }}
                        </h3>
                    </div>
                    <div class="avatar">
                        <div class="avatar-initial bg-label-success rounded-circle">
                            <i class="bx bxs-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modern Table -->
<div class="row">
    <div class="col-12">
        <div class="card modern-card">
            <div class="card-header modern-card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="card-title fw-semibold">Daftar Pelanggan</h5>
                    <span class="badge bg-label-primary">{{ $customer->total() ?? $customer->count() }} Data</span>
                </div>
                <div class="row mt-2">
                    <div class="col-sm-4 mb-3">
                        <form id="searchForm" method="GET" action="{{ url()->current() }}">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" name="search" id="searchInput" 
                                       value="{{ $search }}" placeholder="Cari nama, alamat, no HP...">
                                @if($search)
                                <a href="{{ url()->current() }}" class="btn btn-outline-secondary" type="button">
                                    <i class="bx bx-x"></i>
                                </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-2">
                        <form id="perPageForm" method="GET" action="{{ url()->current() }}">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-list-ul"></i></span>
                                <select name="per_page" id="perPageSelect" class="form-select" onchange="this.form.submit()">
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                    <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>Semua</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="modern-table-container table-responsive">
                    <table class="modern-table">
                        <thead class="modern-table-header">
                            <tr>
                                <th class="table-col-number">
                                    <div class="table-header-content">
                                        <span>No</span>
                                    </div>
                                </th>
                                <th class="table-col-customer">
                                    <div class="table-header-content">
                                        <i class="bx bx-user me-2"></i>
                                        <span>Pelanggan</span>
                                    </div>
                                </th>
                                <th class="table-col-customer">
                                    <div class="table-header-content">
                                        <i class="bx bx-package me-2"></i>
                                        <span>Paket</span>
                                    </div>
                                </th>
                                <th class="table-col-contact">
                                    <div class="table-header-content">
                                        <i class="bx bx-phone me-2"></i>
                                        <span>No HP</span>
                                    </div>
                                </th>
                                <th class="table-col-location text-center">
                                    <div class="table-header-content">
                                        <i class="bx bx-map me-2"></i>
                                        <span>Lokasi</span>
                                    </div>
                                </th>
                                <th class="table-col-status text-center">
                                    <div class="table-header-content">
                                        <i class="bx bx-stats me-2"></i>
                                        <span>Status</span>
                                    </div>
                                </th>
                                <th class="table-col-action text-center">
                                    <div class="table-header-content">
                                        <i class="bx bx-cog me-2"></i>
                                        <span>Aksi</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="modern-table-body">
                            @php
                                $startNumber = ($customer->currentPage() - 1) * $customer->perPage() + 1;
                            @endphp
                            @forelse ($customer as $item)
                            <tr class="modern-table-row">
                                <td class="table-col-number">
                                    <div class="table-number">
                                        {{ $startNumber++ }}
                                    </div>
                                </td>
                                
                                <td class="table-col-customer">
                                    <div class="customer-info">
                                        <div class="customer-avatar">
                                            <div class="avatar-initial bg-label-primary rounded-circle">
                                                <i class="bx bx-user"></i>
                                            </div>
                                        </div>
                                        <div class="customer-details">
                                            <h6 class="customer-name">{{ $item->nama_customer }}</h6>
                                            <p class="customer-address">{{ Str::limit($item->alamat, 35) }}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="table-col-contact">
                                    <div class="contact-info">
                                        <i class="bx bx-package text-muted"></i>
                                        <span class="badge bg-label-warning">{{ $item->paket->nama_paket ?? '-' }}</span>
                                    </div>
                                </td>
                                
                                <td class="table-col-contact">
                                    <div class="contact-info">
                                        <i class="bx bx-phone text-muted"></i>
                                        <span class="contact-number">{{ $item->no_hp }}</span>
                                    </div>
                                </td>
                                
                                <td class="table-col-location text-center">
                                    @php
                                    $gps = $item->gps;
                                    $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                    $url = $isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps);
                                    @endphp
                                    <a href="{{ $url }}" target="_blank" class="btn btn-location" data-bs-toggle="tooltip" title="Buka di Google Maps" data-bs-placement="bottom">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </td>
                                
                                <td class="table-col-status text-center">
                                    @if ($item->status->nama_status == 'Maintenance')
                                    <div class="badge bg-label-danger">
                                        <i class="bx bx-wrench"></i>
                                        <span>Maintenance</span>
                                    </div>
                                    @else
                                    <div class="badge bg-label-success">
                                        <i class="bx bx-check"></i>
                                        <span>Aktif</span>
                                    </div>
                                    @endif
                                </td>
                                
                                <td class="table-col-action text-center">
                                    @if($item->status->nama_status != 'Maintenance')
                                    <a href="/open-tiket/{{ $item->id }}" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" title="Buka Tiket" data-bs-placement="bottom">
                                        <i class="bx bx-lock-open-alt"></i>
                                    </a>
                                    @else
                                    <button class="btn btn-action btn-locked" disabled data-bs-toggle="tooltip" title="Sedang Diproses" data-bs-placement="bottom">
                                        <i class="bx bx-lock"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bx bx-inbox fs-1 text-muted mb-3"></i>
                                        <h5 class="text-muted fw-semibold">Tidak ada data</h5>
                                        <p class="text-muted mb-0">Tidak ada data yang sama dengan search</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            @if($customer instanceof \Illuminate\Pagination\LengthAwarePaginator && $customer->hasPages())
            <div class="card-footer modern-card-footer mt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="table-info">
                        @if($perPage == 'all')
                            Menampilkan <strong>{{ $customer->count() }}</strong> data
                        @else
                            Menampilkan <strong>{{ $customer->firstItem() ?? 0 }} - {{ $customer->lastItem() ?? 0 }}</strong> 
                            dari <strong>{{ $customer->total() }}</strong> data
                        @endif
                    </div>
                    <div class="modern-pagination">
                        {{ $customer->appends(['search' => $search, 'per_page' => $perPage])->onEachSide(1)->links('pagination::simple-bootstrap-5') }}
                    </div>
                </div>
            </div>
            @endif
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
        
        // Add scroll indicator for mobile
        const tableContainer = document.querySelector('.modern-table-container');
        
        function checkScroll() {
            if (tableContainer) {
                const hasHorizontalScroll = tableContainer.scrollWidth > tableContainer.clientWidth;
                if (hasHorizontalScroll) {
                    tableContainer.style.paddingBottom = '10px';
                } else {
                    tableContainer.style.paddingBottom = '0';
                }
            }
        }
        
        checkScroll();
        window.addEventListener('resize', checkScroll);
        
        // Auto submit search form dengan debounce
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        const debouncedSearch = debounce(function() {
            searchForm.submit();
        }, 500);
        
        if (searchInput) {
            searchInput.addEventListener('input', debouncedSearch);
        }
    });
</script>
@endsection