@extends('layouts.contentNavbarLayout')

@section('title', 'Tiket Closed')
<style>
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 0.125rem 0.75rem rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.2s ease;
    }
    
    .card:hover {
        box-shadow: 0 0.25rem 1.5rem rgba(0, 0, 0, 0.08);
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e9ecef;
        padding: 1.5rem;
    }
    
    .card-title {
        font-weight: 600;
        color: #566a7f;
        margin-bottom: 0.25rem;
    }
    
    .card-subtitle {
        color: #a1acb8;
        font-size: 0.875rem;
    }
    
    .table thead th {
        background-color: #f7f7f8;
        color: #566a7f;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        padding: 0.875rem 1rem;
        vertical-align: middle;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f1f1;
    }
    
    .table tbody tr:last-child {
        border-bottom: none;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
    }
    
    .table td {
        padding: 1rem;
        vertical-align: middle;
        border: none;
    }
    
    .customer-avatar {
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: rgba(105, 108, 255, 0.08);
        color: #696cff;
        font-size: 1.125rem;
    }
    
    .customer-name {
        font-weight: 600;
        color: #566a7f;
        margin-bottom: 0.25rem;
        font-size: 0.9375rem;
    }
    
    .customer-address {
        font-size: 0.8125rem;
        color: #a1acb8;
        margin: 0;
        line-height: 1.4;
    }
    
    .btn-action {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        border: 1px solid #e2e8f0;
        background-color: white;
        font-size: 1.125rem;
    }
    
    .btn-action:hover:not(.disabled) {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
    }
    
    .btn-action.btn-maps {
        color: #696cff;
    }
    
    .btn-action.btn-maps:hover:not(.disabled) {
        background-color: #696cff;
        color: white;
        border-color: #696cff;
    }
    
    .btn-action.btn-process {
        color: #ffab00;
    }
    
    .btn-action.btn-process:hover {
        background-color: #ffab00;
        color: white;
        border-color: #ffab00;
    }
    
    .btn-action.btn-done {
        color: #a1acb8;
        background-color: #f7f7f8;
        cursor: not-allowed;
    }
    
    .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.375rem 0.625rem;
    }
    
    .card-footer {
        background-color: #fff;
        border-top: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
    }
    
    .pagination {
        margin-bottom: 0;
    }
    
    .pagination .page-link {
        border-radius: 0.375rem;
        margin: 0 0.125rem;
        border: 1px solid #d9dee3;
        color: #697a8d;
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .pagination .page-link:hover {
        background-color: #f5f5f5;
        border-color: #d9dee3;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #696cff;
        border-color: #696cff;
    }
    
    /* PERBAIKAN PENJALANAN FILTER DAN SEARCH */
    .filter-search-container {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: nowrap;
    }
    
    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-shrink: 0;
    }
    
    .filter-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }
    
    .filter-item .form-label {
        margin-bottom: 0;
        font-weight: 500;
        color: #566a7f;
        font-size: 0.875rem;
    }
    
    .filter-item .input-group {
        width: auto;
        min-width: 150px;
    }
    
    .filter-item .form-select {
        min-width: 140px;
    }
    
    .search-form {
        min-width: 300px;
        max-width: 400px;
    }
    
    .search-form .input-group {
        width: 100%;
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #d9dee3;
        color: #a1acb8;
    }
    
    .form-control, .form-select {
        border: 1px solid #d9dee3;
        background-color: #f8f9fa;
    }
    
    .form-control:focus, .form-select:focus {
        box-shadow: none;
        border-color: #696cff;
        background-color: #fff;
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
        .filter-search-container {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        
        .filter-group {
            justify-content: space-between;
            width: 100%;
        }
        
        .search-form {
            min-width: 100%;
            max-width: 100%;
        }
    }
    
    @media (max-width: 768px) {
        .card-header {
            padding: 1.25rem;
        }
        
        .filter-group {
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
        }
        
        .filter-item {
            justify-content: space-between;
        }
        
        .filter-item .input-group {
            min-width: 100%;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .table td, .table th {
            padding: 0.75rem 0.5rem;
        }
        
        .customer-avatar {
            width: 2rem;
            height: 2rem;
            font-size: 1rem;
        }
        
        .btn-action {
            width: 2rem;
            height: 2rem;
            font-size: 1rem;
        }
    }
    
    @media (max-width: 576px) {
        .filter-item {
            flex-direction: column;
            align-items: stretch;
            gap: 0.25rem;
        }
        
        .filter-item .form-label {
            text-align: left;
        }
    }
</style>
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header mb-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div class="d-flex flex-column">
                        <h4 class="card-title mb-1">Tiket Closed</h4>
                        <p class="card-subtitle mb-0 text-muted">Daftar tiket yang sedang dalam proses atau telah selesai</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="filter-search-container">
                    <!-- Filter Group (Bulan) -->
                    <div class="filter-group">
                        <div class="filter-item">
                            <label class="form-label mb-0">Bulan:</label>
                            <select id="monthFilter" class="form-select">
                                <option value="all" {{ !$selectedMonth ? 'selected' : '' }}>Semua</option>
                                @foreach($months as $num => $name)
                                    <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Search Form -->
                    <div class="filter-item">
                        <label class="form-label mb-0">Search:</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" name="search" id="searchInput" value="{{ $search ?? '' }}" placeholder="Cari nama atau alamat...">
                        </div>
                        <input type="hidden" name="month" id="hiddenMonthInput" value="{{ $selectedMonth ?? '' }}">
                    </div>                        
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4">No</th>
                                <th>Pelanggan</th>
                                <th>No HP</th>
                                <th class="text-center">Lokasi</th>
                                <th>Keterangan</th>
                                <th class="text-center">Status</th>
                                <th>Kategori</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="ticketTableBody">
                            @forelse ($customer as $item)
                                <tr class="position-relative">
                                    <td class="ps-4 fw-medium">{{ $customer->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="customer-avatar">
                                                    <i class="bx bx-user"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="customer-name mb-1">{{ $item->customer->nama_customer ?? '-' }}</h6>
                                                <p class="customer-address mb-0">{{ Str::limit($item->customer->alamat ?? '-', 30) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-nowrap">{{ $item->customer->no_hp ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $gps = $item->customer->gps ?? null;
                                            $url = $gps ? (Str::startsWith($gps, ['http://', 'https://']) ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                                        @endphp
                                        <a href="{{ $url }}" target="_blank"
                                            class="btn btn-sm btn-action btn-maps {{ !$gps ? 'disabled' : '' }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $gps ? 'Lihat di Google Maps' : 'Lokasi tidak tersedia' }}">
                                            <i class="bx bx-map"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;" 
                                              data-bs-toggle="tooltip" title="{{ $item->keterangan }}">
                                            {{ $item->keterangan }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($item->status_id == 6)
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Menunggu</span>
                                        @elseif($item->status_id == 3)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Selesai</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                            {{ $item->kategori->nama_kategori }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        @if ($item->status_id == 3)
                                            <button class="btn btn-sm btn-action btn-done" disabled data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Tiket sudah selesai">
                                                <i class="bx bx-check-double"></i>
                                            </button>
                                        @else
                                            <a href="/tiket-open/{{ $item->id }}"
                                                class="btn btn-sm btn-action btn-outline-warning" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Proses & Tutup Tiket">
                                                <i class="bx bx-wrench"></i>
                                            </a>
                                            <a href="/cancel-tiket/{{ $item->id }}" class="btn btn-outline-danger btn-action btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Cancel">
                                                <i class="bx bx-x"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="bx bx-inbox fs-1 text-muted mb-3"></i>
                                            <h5 class="text-muted">Tidak ada data tiket</h5>
                                            <p class="text-muted mb-0">Tidak ada tiket yang cocok dengan pencarian Anda.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($customer->hasPages())
                <div class="card-footer border-top-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan {{ $customer->firstItem() }} - {{ $customer->lastItem() }} dari {{ $customer->total() }} entri
                        </div>
                        <div>
                            {{ $customer->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const monthFilter = document.getElementById('monthFilter');
    const hiddenMonthInput = document.getElementById('hiddenMonthInput');
    const searchForm = document.getElementById('searchForm');
    let searchTimeout;

    function fetchData(page = 1) {
        const search = searchInput.value;
        const month = monthFilter.value;
        const url = new URL(window.location.href);
        url.searchParams.set('search', search);
        url.searchParams.set('month', month);
        url.searchParams.set('page', page);
        url.searchParams.set('ajax', 1);

        // Update hidden input for form submission
        hiddenMonthInput.value = month;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newTableBody = doc.getElementById('ticketTableBody');
            const newPagination = doc.querySelector('.card-footer');

            if (newTableBody) {
                document.getElementById('ticketTableBody').innerHTML = newTableBody.innerHTML;
            }
            
            const paginationContainer = document.querySelector('.card-footer');
            if (paginationContainer && newPagination) {
                paginationContainer.innerHTML = newPagination.innerHTML;
            } else if (paginationContainer && !newPagination) {
                paginationContainer.remove();
            }
            
            // Re-initialize tooltips
            initializeTooltips();
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    // Event listener untuk input pencarian
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchData(1);
            }, 500);
        });
    }

    // Event listener untuk filter bulan
    if (monthFilter) {
        monthFilter.addEventListener('change', function () {
            fetchData(1);
        });
    }

    // Event listener untuk pagination (delegasi event)
    document.addEventListener('click', function(e) {
        if (e.target.matches('.pagination a, .pagination a *')) {
            e.preventDefault();
            const pageLink = e.target.closest('a');
            const url = new URL(pageLink.href);
            const page = url.searchParams.get('page');
            fetchData(page);
        }
    });

    // Mencegah submit form tradisional saat menekan Enter di search input
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchData(1);
        });
    }

    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Inisialisasi tooltips saat halaman pertama kali dimuat
    initializeTooltips();
});
</script>