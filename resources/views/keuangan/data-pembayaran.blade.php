@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pembayaran')

@section('page-style')
    <style>
        .payment-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .payment-card:hover {
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
            font-size: 0.70rem;
            font-weight: 800;
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
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            padding: 1rem 1.5rem;
        }

        .table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f4;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .payment-method-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item {
            font-size: 0.875rem;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: ">";
            color: #6c757d;
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }

        /* Payment Method Cards Specific Styles */
        .payment-method-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .payment-method-section h6 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .payment-card.method-card {
            position: relative;
            overflow: hidden;
        }

        .payment-card.method-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--card-color, #6c757d) 0%, transparent 100%);
        }

        .payment-card.method-card.cash-card {
            --card-color: #28a745;
        }

        .payment-card.method-card.transfer-card {
            --card-color: #007bff;
        }

        .payment-card.method-card.ewallet-card {
            --card-color: #ffc107;
        }

        .method-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-sublabel {
            font-size: 0.75rem;
            color: #8898aa;
            margin-top: 0.25rem;
        }

        /* Responsive adjustments for payment method cards */
        @media (max-width: 768px) {
            .payment-method-section {
                padding: 1rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Breadcrumb -->
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
                <a href="/data/pendapatan" class="text-decoration-none">Personal</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Data Pembayaran</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h4 class="fw-bold text-dark mb-2">Data Pembayaran</h4>
                    <p class="text-muted mb-0">Kelola dan pantau data pembayaran customer</p>
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
        <!-- Total Payments -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="payment-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 fw-medium">Total Pembayaran</p>
                        <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($totalPayments ?? 0, 0, ',', '.') }}</h5>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bx bx-money"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Payments -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="payment-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 fw-medium">Pembayaran Hari Ini</p>
                        <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($todayPayments ?? 0, 0, ',', '.') }}</h5>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bx bx-calendar-check"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <a href="/pembayaran/daily" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat detail pembayaran hari ini">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class='bx bx-show'></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Monthly Payments -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="payment-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 fw-medium">Pembayaran Bulan Ini</p>
                        <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($monthlyPayments ?? 0, 0, ',', '.') }}</h5>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bx bx-calendar"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat detail pembayaran bulan ini">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class='bx bx-show'></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="payment-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 fw-medium">Total Transaksi</p>
                        <h5 class="fw-bold text-dark mb-0">{{ number_format($totalTransactions ?? 0, 0, ',', '.') }}</h5>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bx bx-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Method Statistics -->
    <div class="payment-method-section">
        <h6 class="fw-semibold mb-3">
            <i class="bx bx-credit-card me-2"></i>Statistik Metode Pembayaran
        </h6>

        <div class="row g-4">
            <!-- Cash Payments -->
            <div class="col-12 col-sm-4">
                <div class="payment-card method-card cash-card p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label mb-2">Pembayaran Cash</div>
                            <div class="stat-number text-success">{{ number_format($cashPayments ?? 0, 0, ',', '.') }}</div>
                            <div class="stat-sublabel">Total transaksi</div>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bx bx-money"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        @if ($cashPayments == 0)
                            <span class="method-badge bg-secondary bg-opacity-10 text-secondary">
                                <i class="bx bx-info-circle me-1"></i>Belum ada data
                            </span>
                        @else
                            <span class="method-badge bg-success bg-opacity-10 text-success">
                                <i class="bx bx-check-circle me-1"></i>{{ $cashPayments }} Pembayaran
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bank Transfer Payments -->
            <div class="col-12 col-sm-4">
                <div class="payment-card method-card transfer-card p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label mb-2">Transfer Bank</div>
                            <div class="stat-number text-primary">{{ number_format($transferPayments ?? 0, 0, ',', '.') }}
                            </div>
                            <div class="stat-sublabel">Total transaksi</div>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bx bx-transfer"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        @if ($transferPayments == 0)
                            <span class="method-badge bg-secondary bg-opacity-10 text-secondary">
                                <i class="bx bx-info-circle me-1"></i>Belum ada data
                            </span>
                        @else
                            <span class="method-badge bg-danger bg-opacity-10 text-danger">
                                <i class="bx bx-check-circle me-1"></i>{{$transferPayments}} Pembayaran
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- E-Wallet Payments -->
            <div class="col-12 col-sm-4">
                <div class="payment-card method-card ewallet-card p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label mb-2">E-Wallet</div>
                            <div class="stat-number text-warning">{{ number_format($ewalletPayments ?? 0, 0, ',', '.') }}
                            </div>
                            <div class="stat-sublabel">Total transaksi</div>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bx bx-wallet"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        @if ($ewalletPayments > 0)
                            <span class="method-badge bg-success bg-opacity-10 text-success">
                                <i class="bx bx-check-circle me-1"></i>{{ $ewalletPayments }} Pembayaran
                            </span>
                        @else
                            <span class="method-badge bg-secondary bg-opacity-10 text-secondary">
                                <i class="bx bx-info-circle me-1"></i>Belum ada data
                            </span>
                        @endif
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
                <div class="col-12 col-lg-5">
                    <label class="form-label fw-medium text-dark">Pencarian</label>
                    <div class="position-relative">
                        <input type="text" id="searchInput" name="search" value="{{ $search ?? '' }}"
                            placeholder="Cari nama customer, paket, atau metode pembayaran..." class="form-control">
                    </div>
                </div>

                <!-- Payment Method Filter -->
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label fw-medium text-dark">Metode Pembayaran</label>
                    <select id="metodeFilter" name="metode" class="form-select">
                        <option value="">Semua Metode</option>
                        @foreach ($paymentMethods ?? [] as $method)
                            <option value="{{ $method }}" {{ ($metode ?? '') == $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-12 col-md-6 col-lg-4">
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
                </div>
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

    <!-- Data Table -->
    <div class="table-card">
        <div class="p-6 border-bottom">
            <h5 class="fw-semibold text-dark mb-0">Daftar Pembayaran Customer</h5>
        </div>

        <div class="table-responsive p-2">
            <div id="tableContainer">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Customer</th>
                            <th>Paket</th>
                            <th>Jumlah Bayar</th>
                            <th>Tanggal Bayar</th>
                            <th>Metode Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" style="font-size: 14px">
                        @forelse($invoicePay ?? [] as $index => $payment)
                            <tr>
                                <td class="fw-medium">{{ $payments->firstItem() + $index }}</td>
                                <td>
                                    <div>
                                        <div class="fw-medium text-dark">
                                            {{ $payment->invoice->customer->nama_customer ?? 'N/A' }}</div>
                                        <small
                                            class="text-muted">{{ Str::limit($payment->invoice->customer->alamat ?? '', 30) }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-primary">
                                        {{ $payment->invoice->paket->nama_paket ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="fw-semibold text-danger">
                                            Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-calendar text-primary me-2"></i>
                                        {{ \Carbon\Carbon::parse($payment->tanggal_bayar)->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="payment-method-badge bg-info bg-opacity-10 text-primary">
                                        <i class="bx bx-credit-card"></i>
                                        {{ $payment->metode_bayar }}
                                    </span>
                                </td>
                                <td>
                                    @if ($payment->status)
                                        @if ($payment->status->nama_status == 'Sudah Bayar')
                                            <span class="status-badge bg-success bg-opacity-10 text-success">
                                                <i class="bx bx-check-circle"></i>
                                                {{ $payment->status->nama_status }}
                                            </span>
                                        @else
                                            <span class="status-badge bg-secondary bg-opacity-10 text-secondary">
                                                <i class="bx bx-time-five"></i>
                                                {{ $payment->status->nama_status }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="status-badge bg-secondary bg-opacity-10 text-secondary">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-money text-muted" style="font-size: 3rem;"></i>
                                        <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                                        <p class="text-muted mb-0">Belum ada data pembayaran yang tersedia</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if (isset($payments) && $payments->hasPages())
            <div class="p-4 border-top">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div class="text-muted small">
                        Menampilkan {{ $payments->firstItem() ?? 0 }} sampai {{ $payments->lastItem() ?? 0 }}
                        dari {{ $payments->total() ?? 0 }} hasil
                    </div>
                    <div>
                        {{ $payments->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif
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
            const metodeFilter = document.getElementById('metodeFilter');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');

            [metodeFilter, startDate, endDate].forEach(element => {
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
            fetch(`{{ route('pembayaran') }}?${params.toString()}`, {
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
            document.getElementById('metodeFilter').value = '';
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';

            // Redirect to clean URL
            window.location.href = '{{ route('pembayaran') }}';
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
            window.location.href = `{{ route('pembayaran') }}?${params.toString()}`;
        }

        // View payment details
        function viewPayment(paymentId) {
            // You can implement modal or redirect to detail page
            showNotification('Fitur detail pembayaran akan segera tersedia', 'info');
        }

        // View payment proof
        function viewProof(paymentId) {
            // You can implement modal to show payment proof
            showNotification('Fitur lihat bukti pembayaran akan segera tersedia', 'info');
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
@endsection
