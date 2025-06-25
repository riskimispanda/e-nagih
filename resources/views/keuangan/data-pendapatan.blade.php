@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pendapatan')

@section('page-style')
<style>
    .revenue-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .revenue-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    
    .search-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }
    
    .table-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        overflow: hidden;
    }
    
    .search-input {
        border-radius: 8px;
        border: 1px solid #d0d7de;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .search-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        outline: none;
    }
    
    .btn-modern {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        border: none;
    }
    
    .btn-modern:hover {
        transform: translateY(-1px);
    }
    
    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .action-btn {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        border: none;
        transition: all 0.2s ease;
    }
    
    .action-btn:hover {
        transform: translateY(-1px);
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .loading-content {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .spinner {
        width: 24px;
        height: 24px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #0d6efd;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        
        100% {
            transform: rotate(360deg);
        }
    }
    
    .table-responsive {
        border-radius: 0;
    }
    
    .table th {
        background-color: whitesmoke;
        border-bottom: 2px solid #dee2e6;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: black;
        padding: 1rem 1.5rem;
    }
    
    .table td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f4;
        font-size: 13px;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
</style>
@endsection

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <i class="bx bx-home-alt me-1"></i>Dashboard
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="/corp/pendapatan" class="text-decoration-none">Langganan</a>
        </li>
        <li class="breadcrumb-item">
            <a href="#" class="text-decoration-none">Personal</a>
        </li>
    </ol>
</nav>

<!-- Header Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-2">Data Pendapatan Langganan</h4>
                <p class="text-muted mb-0">Kelola dan pantau data pendapatan perusahaan</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="refreshData()" class="btn btn-outline-danger btn-sm">
                    <i class="bx bx-refresh me-2"></i>
                    Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <!-- Total Revenue -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="revenue-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1 fw-medium">Total Pendapatan</p>
                    <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h5>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bx bx-trending-up"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue -->
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="/data/pembayaran" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat detail pembayaran">
            <div class="revenue-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 fw-medium">Jumlah Pembayaran</p>
                        <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($monthlyRevenue ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bx bx-calendar"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <!-- Pending Revenue -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="revenue-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1 fw-medium">Pendapatan Tertunda</p>
                    <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($pendingRevenue ?? 0, 0, ',', '.') }}
                    </h5>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bx bx-time"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Invoices -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="revenue-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1 fw-medium">Total Invoice</p>
                    <h5 class="fw-bold text-dark mb-0">{{ number_format($totalInvoices ?? 0, 0, ',', '.') }}</h5>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bx bx-receipt"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="search-card p-4 mb-4">
    <form id="filterForm">
        <div class="row g-3">
            <!-- Search Input -->
            <div class="col-12 col-lg-4">
                <label class="form-label fw-medium text-dark">Pencarian</label>
                <div class="position-relative">
                    <input type="text" id="searchInput" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari nama customer atau paket..." class="form-control">
                </div>
            </div>
            
            @php
            \Carbon\Carbon::setLocale('id');
            @endphp
            
            <div class="col-12 col-lg-4">
                <label class="form-label">Bulan</label>
                <select name="bulan" id="bulan" class="form-select" onchange="filterBulan()">
                    <option value="">Semua Bulan</option>
                    @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">
                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                    </option>
                    @endfor
                </select>
            </div>
            
            
            <!-- Status Filter -->
            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label fw-medium text-dark">Status</label>
                <select id="statusFilter" name="status" class="form-select">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions ?? [] as $statusOption)
                    <option value="{{ $statusOption->id }}"
                        {{ ($status ?? '') == $statusOption->id ? 'selected' : '' }}>
                        {{ $statusOption->nama_status }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Date Range -->
            {{-- <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label fw-medium text-dark">Periode Tanggal</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="date" id="startDate" name="start_date" value="{{ $startDate ?? '' }}"
                        class="form-control" title="Tanggal Mulai">
                    </div>
                    <div class="col-6">
                        <input type="date" id="endDate" name="end_date" value="{{ $endDate ?? '' }}"
                        class="form-control" title="Tanggal Akhir">
                    </div>
                </div>
            </div> --}}
        </div>
        
        <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
            <button type="button" onclick="applyFilters()" class="btn btn-outline-warning btn-modern btn-sm">
                <i class="bx bx-filter-alt me-2"></i>
                Terapkan Filter
            </button>
            <button type="button" onclick="clearFilters()" class="btn btn-outline-secondary btn-modern">
                <i class="bx bx-x me-2"></i>
                Reset Filter
            </button>
        </div>
    </form>
</div>

<!-- Data Table Personal -->
<div class="table-card mb-5" id="invoiceTable">
    <div class="p-6 border-bottom">
        <h5 class="fw-semibold text-dark mb-0">Daftar Invoice Customer</h5>
    </div>
    
    <div class="table-responsive p-3">
        <div id="tableContainer">
            <table class="table table-hover">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Customer</th>
                        <th>Paket</th>
                        <th>Tagihan</th>
                        <th>Tagihan Tambahan</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody" style="font-size: 14px;">
                    @forelse($invoices ?? [] as $index => $invoice)
                    <tr>
                        <td class="fw-medium">{{ $invoices->firstItem() + $index }}</td>
                        <td>
                            <div>
                                <div class="fw-medium text-dark">
                                    {{ $invoice->customer->nama_customer ?? 'N/A' }}</div>
                                    <small
                                    class="text-muted">{{ Str::limit($invoice->customer->alamat ?? '', 30) }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-primary">
                                    {{ $invoice->paket->nama_paket ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-money text-secondary me-2"></i>
                                    <span class="fw-bold text-secondary">
                                        Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-plus-circle text-warning me-2"></i>
                                    <span class="fw-semibold text-warning">
                                        Rp {{ number_format($invoice->tambahan, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>
                            <td style="font-size: 14px;">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-calendar text-danger me-2"></i>
                                    {{ \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d/m/Y') }}
                                </div>
                            </td>
                            <td>
                                @if ($invoice->status)
                                @if ($invoice->status->nama_status == 'Sudah Bayar')
                                <span class="status-badge bg-success bg-opacity-10 text-success">
                                    <i class="bx bx-check-circle"></i>
                                    {{ $invoice->status->nama_status }}
                                </span>
                                @elseif($invoice->status->nama_status == 'Belum Bayar')
                                <span class="status-badge bg-danger bg-opacity-10 text-danger">
                                    <i class="bx bx-x-circle"></i>
                                    {{ $invoice->status->nama_status }}
                                </span>
                                @else
                                <span class="status-badge bg-secondary bg-opacity-10 text-secondary">
                                    <i class="bx bx-time-five"></i>
                                    {{ $invoice->status->nama_status }}
                                </span>
                                @endif
                                @else
                                <span class="status-badge bg-secondary bg-opacity-10 text-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button onclick="viewInvoice({{ $invoice->id }})"
                                        class="action-btn bg-info bg-opacity-10 text-primary btn-sm">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    @if ($invoice->status && $invoice->status->nama_status == 'Belum Bayar')
                                    <button onclick="processPayment({{ $invoice->id }})"
                                        class="action-btn bg-success bg-opacity-10 text-success btn-sm">
                                        <i class="bx bx-money"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                                    <p class="text-muted mb-0">Belum ada data invoice yang tersedia</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        @if (isset($invoices) && $invoices->hasPages())
        <div class="p-4 border-top">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <div>
                    {{ $invoices->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>


<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay d-none">
    <div class="loading-content">
        <div class="spinner"></div>
        <span class="text-dark">Memuat data...</span>
    </div>
</div>
@endsection

@section('page-script')
<script>
    // Filter Bulan
    function filterBulan() {
        if (isLoading) return;
        showLoading();
        
        const bulan = $('#bulan').val();
        const params = new URLSearchParams(window.location.search);
        
        if (bulan) {
            params.set('bulan', bulan);
        } else {
            params.delete('bulan');
        }
        
        $.ajax({
            url: '{{ route("pendapatan") }}',
            method: 'GET',
            data: params.toString(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                // Update table content
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');
                
                // Update table container
                const newTableContainer = $(doc).find('#tableContainer').html();
                $('#tableContainer').html(newTableContainer);
                
                // Update pagination if exists
                const newPagination = $(doc).find('.p-4.border-top').html();
                if (newPagination) {
                    $('.p-4.border-top').html(newPagination);
                }
                
                // Update URL without page reload
                window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat memuat data', 'error');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    let searchTimeout;
    let isLoading = false;
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        initializeSearch();
        initializeFilters();
    });
    
    // Initialize search functionality
    function initializeSearch() {
        const searchInput = document.getElementById('searchInput');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    applyFilters();
                }, 500); // Debounce search for 500ms
            });
        }
    }
    
    // Initialize filter functionality
    function initializeFilters() {
        const statusFilter = document.getElementById('statusFilter');
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        
        [statusFilter, startDate, endDate].forEach(element => {
            if (element) {
                element.addEventListener('change', applyFilters);
            }
        });
    }
    
    // Apply filters and search
    function applyFilters() {
        if (isLoading) return;
        
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams();
        
        for (let [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                params.append(key, value);
            }
        }
        
        // Show loading
        showLoading();
        
        // Make AJAX request
        fetch(`{{ route('pendapatan') }}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Parse the response and update the table
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update table content
            const newTableContainer = doc.querySelector('#tableContainer');
            const currentTableContainer = document.querySelector('#tableContainer');
            
            if (newTableContainer && currentTableContainer) {
                currentTableContainer.innerHTML = newTableContainer.innerHTML;
            }
            
            // Update pagination if exists
            const newPagination = doc.querySelector('.p-4.border-top');
            const currentPagination = document.querySelector('.p-4.border-top');
            
            if (newPagination && currentPagination) {
                currentPagination.innerHTML = newPagination.innerHTML;
            }
            
            // Update URL without page reload
            const url = new URL(window.location);
            for (let [key, value] of params.entries()) {
                url.searchParams.set(key, value);
            }
            
            // Remove empty parameters
            for (let key of url.searchParams.keys()) {
                if (!params.has(key)) {
                    url.searchParams.delete(key);
                }
            }
            
            window.history.pushState({}, '', url);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat memuat data', 'error');
        })
        .finally(() => {
            hideLoading();
        });
    }
    
    // Clear all filters
    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
        
        // Redirect to clean URL
        window.location.href = '{{ route('pendapatan') }}';
    }
    
    // Refresh data
    function refreshData() {
        if (isLoading) return;
        
        showLoading();
        
        // Get current filters
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams();
        
        for (let [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                params.append(key, value);
            }
        }
        
        // Reload with current filters
        window.location.href = `{{ route('pendapatan') }}?${params.toString()}`;
    }
    
    // View invoice details
    function viewInvoice(invoiceId) {
        // You can implement modal or redirect to detail page
        window.location.href = `/invoice/${invoiceId}`;
    }
    
    // Process payment
    function processPayment(invoiceId) {
        if (confirm('Apakah Anda yakin ingin memproses pembayaran ini?')) {
            // You can implement payment processing logic here
            window.location.href = `/payment/${invoiceId}`;
        }
    }
    
    // Show loading overlay
    function showLoading() {
        isLoading = true;
        document.getElementById('loadingOverlay').classList.remove('d-none');
    }
    
    // Hide loading overlay
    function hideLoading() {
        isLoading = false;
        document.getElementById('loadingOverlay').classList.add('d-none');
    }
    
    // Show notification
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `position-fixed top-0 end-0 p-3`;
        notification.style.zIndex = '9999';
        notification.style.marginTop = '20px';
        notification.style.marginRight = '20px';
        
        const alertClass = type === 'error' ? 'alert-danger' :
        type === 'success' ? 'alert-success' : 'alert-primary';
        
        notification.innerHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="bx ${
                        type === 'error' ? 'bx-error' :
                        type === 'success' ? 'bx-check' :
                        'bx-info-circle'
                    } me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Handle pagination clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const link = e.target.closest('.pagination a');
            const url = new URL(link.href);
            
            // Get current form data
            const formData = new FormData(document.getElementById('filterForm'));
            
            // Add form data to pagination URL
            for (let [key, value] of formData.entries()) {
                if (value.trim() !== '') {
                    url.searchParams.set(key, value);
                }
            }
            
            showLoading();
            window.location.href = url.toString();
        }
    });
</script>

<script>
    // Format input as Rupiah currency
    function formatRupiah(input) {
        let value = input.value.replace(/\D/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            });
        } else {
            value = '';
        }
        input.value = value;
        // document.getElementById('revenueAmountRaw').value = value;
        document.getElementById('revenueAmountRaw').value = value.replace(/[^0-9]/g, '');
        
    }
    
    document.getElementById('pendapatan').addEventListener('change', function() {
        const selectedValue = this.value;
        if( selectedValue === 'langganan'){
            document.getElementById('invoiceTable').style.display = 'block';
            document.getElementById('revenueTable').style.display = 'none';
        } else if (selectedValue === 'lain-lain') {
            document.getElementById('invoiceTable').style.display = 'none';
            document.getElementById('revenueTable').style.display = 'block';
        } else if (selectedValue === 'semua') {
            document.getElementById('invoiceTable').style.display = 'block';
            document.getElementById('revenueTable').style.display = 'block';
        } else {
            document.getElementById('invoiceTable').style.display = 'none';
            document.getElementById('revenueTable').style.display = 'none';
        }
    });
    
</script>
@endsection