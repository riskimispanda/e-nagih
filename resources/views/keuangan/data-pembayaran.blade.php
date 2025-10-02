@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pembayaran')

@section('page-style')
<style>
    .payment-type-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        width: fit-content;
    }

    .payment-info-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.5rem;
        border: 1px solid #e9ecef;
    }

    .payment-type-label {
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        display: inline-block;
        text-align: center;
    }

    .payment-type-icon {
        font-size: 1rem;
    }

    .badge-sm {
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
    }

    .bg-outline-success {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        border: 1px solid #198754;
    }

    .bg-outline-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid #ffc107;
    }

    .payment-display-compact {
        font-size: 0.875rem;
    }
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
        text-transform: uppercase;
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
        font-size: 1.45rem;
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
    {{-- <div class="col-12 col-sm-6 col-lg-3">
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
    </div> --}}
    
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
    
    {{-- Request Edit Pembayaran --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="/requestEdit/pembayaran" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat Daftar Request">
            <div class="payment-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 fw-medium">Request Edit</p>
                        <h5 class="fw-bold text-dark mb-0">{{$editPembayaran}}</h5>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bx bx-refresh"></i>
                    </div>
                </div>
            </div>
        </a>
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
                        <div class="stat-label mb-2 fw-bold">Pembayaran Cash</div>
                        <div class="stat-number text-success mb-4">Rp {{ number_format($cashPayments ?? 0, 0, ',', '.') }}</div>
                        <div class="stat-sublabel">Total Transaksi</div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bx bx-money"></i>
                    </div>
                </div>
                <div class="mt-2">
                    @if ($cashPayments == 0)
                    <span class="method-badge bg-secondary bg-opacity-10 text-secondary">
                        <i class="bx bx-info-circle me-1"></i>Belum ada data
                    </span>
                    @else
                    <span class="method-badge bg-success bg-opacity-10 text-success">
                        <i class="bx bx-check-circle me-1"></i>{{ $cashCount }} Pembayaran
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
                        <div class="stat-label mb-2 fw-bold">Transfer Bank</div>
                        <div class="stat-number text-primary mb-3">Rp {{ number_format($transferPayments ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="stat-sublabel">Total transaksi</div>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bx bx-transfer"></i>
                    </div>
                </div>
                <div class="mt-2">
                    @if ($transferPayments == 0)
                    <span class="method-badge bg-secondary bg-opacity-10 text-secondary">
                        <i class="bx bx-info-circle me-1"></i>Belum ada data
                    </span>
                    @else
                    <span class="method-badge bg-danger bg-opacity-10 text-danger">
                        <i class="bx bx-check-circle me-1"></i>{{$transferCount}} Pembayaran
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
                        <div class="stat-label mb-2 fw-bold">E-Wallet</div>
                        <div class="stat-number text-warning mb-3">Rp {{ number_format($ewalletPayments ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="stat-sublabel">Total transaksi</div>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bx bx-wallet"></i>
                    </div>
                </div>
                <div class="mt-2">
                    @if ($ewalletPayments > 0)
                    <span class="method-badge bg-success bg-opacity-10 text-success">
                        <i class="bx bx-check-circle me-1"></i>{{ $ewalletCount }} Pembayaran
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
    
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 p-4 border-bottom">
        <div class="d-flex flex-column flex-sm-row gap-2">
            <div class="dropdown">
                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-export me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="#" onclick="exportData('harian')">
                            <i class="bx bx-file me-1"></i>Harian
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="exportData('bulanan')">
                            <i class="bx bx-file me-1"></i>Bulanan
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="showExportModal()">
                            <i class="bx bx-calendar me-1"></i>Custom Range
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 text-muted small">Tampilkan:</label>
            <select id="entriesPerPage" class="form-select form-select-sm" style="width: auto;">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">Semua</option>
            </select>
            <span class="text-muted small">Entri</span>
        </div>
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
                        <th>Admin / Agen</th>
                        <th>Aksi</th>
                        <th>Keterangan</th>
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
                                <div class="d-flex justify-content-between">
                                    <i class="bx bx-calendar text-primary me-1"></i>
                                    {{ \Carbon\Carbon::parse($payment->tanggal_bayar.' '.\Carbon\Carbon::parse($payment->created_at)->format('H:i:s'))->format('d-m-Y H:i:s') }}
                                </div>
                            </td>
                            <td>
                                <div class="payment-method-stack">
                                    <div class="payment-method-badge bg-info bg-opacity-10 text-primary">
                                        <i class="bx bx-credit-card me-1"></i>
                                        {{ $payment->metode_bayar }}
                                    </div>
                                    <small class="payment-type-text text-muted mt-1">
                                        {{ $payment->tipe_pembayaran == 'reguler' ? 'Pembayaran Reguler' : 'Pembayaran Diskon' }}
                                    </small>
                                </div>
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
                            <td>
                                @if ($payment->user)
                                <span class="status-badge bg-danger bg-opacity-10 text-danger fw-bold">
                                    {{ $payment->user->name }}
                                </span>
                                @else
                                <span class="status-badge bg-info bg-opacity-10 text-primary fw-bold">Tripay</span>
                                @endif
                            </td>
                            <td>
                                @if($payment->status_id == 8)
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="btn btn-outline-warning btn-sm text-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $payment->id }}">
                                        <i class="bx bx-pencil"></i>
                                    </a>
                                    <a href="/kirim-ulang/{{ $payment->id }}" class="btn btn-outline-danger btn-sm text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Kirim Notifikasi Pembayaran Berhasil">
                                        <i class="bx bx-message"></i>
                                    </a>
                                </div>
                                @else
                                <button class="btn btn-outline-warning btn-sm text-warning" disabled>
                                    <i class="bx bx-pencil"></i>
                                </button>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold">
                                    <small>{{$payment->keterangan}}</small>
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
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
        <div class="p-4 border-top" id="paginationContainer">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <div class="text-muted small">
                    Menampilkan {{ $payments->firstItem() ?? 0 }} sampai {{ $payments->lastItem() ?? 0 }}
                    dari {{ $payments->total() ?? 0 }} hasil
                </div>
                <div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-center mb-0">
                            {{-- Previous Page Link --}}
                            @if ($payments->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">‹ Previous</span>
                            </li>
                            @else
                            <li class="page-item">
                                <a class="page-link ajax-pagination-old" href="#" data-page="{{ $payments->currentPage() - 1 }}">‹ Previous</a>
                            </li>
                            @endif
                            
                            {{-- Pagination Elements --}}
                            @php
                            $start = max(1, $payments->currentPage() - 2);
                            $end = min($payments->lastPage(), $payments->currentPage() + 2);
                            @endphp
                            
                            {{-- First Page --}}
                            @if ($start > 1)
                            <li class="page-item">
                                <a class="page-link ajax-pagination-old" href="#" data-page="1">1</a>
                            </li>
                            @if ($start > 2)
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                            @endif
                            
                            {{-- Page Numbers --}}
                            @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $payments->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $i }}</span>
                            </li>
                            @else
                            <li class="page-item">
                                <a class="page-link ajax-pagination-old" href="#" data-page="{{ $i }}">{{ $i }}</a>
                            </li>
                            @endif
                            @endfor
                            
                            {{-- Last Page --}}
                            @if ($end < $payments->lastPage())
                            @if ($end < $payments->lastPage() - 1)
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link ajax-pagination-old" href="#" data-page="{{ $payments->lastPage() }}">{{ $payments->lastPage() }}</a>
                            </li>
                            @endif
                            
                            {{-- Next Page Link --}}
                            @if ($payments->hasMorePages())
                            <li class="page-item">
                                <a class="page-link ajax-pagination-old" href="#" data-page="{{ $payments->currentPage() + 1 }}">Next ›</a>
                            </li>
                            @else
                            <li class="page-item disabled">
                                <span class="page-link">Next ›</span>
                            </li>
                            @endif
                        </ul>
                    </nav>
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
    
    {{-- Modal Edit Pembayaran --}}
@foreach ($payments as $pembayaran)
<div class="modal fade" id="editModal{{ $pembayaran->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pembayaran {{ $pembayaran->invoice->customer->nama_customer }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/edit/pembayaran/{{ $pembayaran->id }}" method="POST" class="editPembayaranForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Nama Customer</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" class="form-control" value="{{ $pembayaran->invoice->customer->nama_customer ?? '-' }}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Total Tagihan</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-money"></i></span>
                                <input type="text" class="form-control" value="Rp {{ number_format($pembayaran->invoice->tagihan + $pembayaran->invoice->tunggakan + $pembayaran->invoice->tambahan - $pembayaran->invoice->saldo ?? 0, 0, ',', '.') }}" readonly>
                            </div>
                        </div>
                        
                        <!-- TAMBAHKAN FIELD TIPE PEMBAYARAN DI SINI -->
                        <div class="col-sm-12 mb-2">
                            <label class="form-label">Tipe Pembayaran</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-category"></i></span>
                                <select name="tipe_pembayaran" class="form-select" required>
                                    <option value="reguler" {{ $pembayaran->tipe_pembayaran == 'reguler' ? 'selected' : '' }}>Pembayaran Reguler</option>
                                    <option value="diskon" {{ $pembayaran->tipe_pembayaran == 'diskon' ? 'selected' : '' }}>Pembayaran Diskon</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-sm-12 mb-2">
                            <label class="form-label">Jumlah Bayar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-money"></i></span>
                                <input type="text" name="jumlah" id="jumlah{{ $pembayaran->id }}" class="form-control" value="{{ $pembayaran->jumlah_bayar ? 'Rp '.number_format($pembayaran->jumlah_bayar,0,',','.') : 'Rp 0' }}" oninput="formatRupiahEdit(this, {{ $pembayaran->id }})">
                                <input type="hidden" name="jumlahRaw" id="jumlahRaw{{ $pembayaran->id }}" value="{{ $pembayaran->jumlah_bayar ?? 0 }}">
                            </div>
                        </div>
                        
                        <div class="col-sm-12 mb-2">
                            <label class="form-label">Keterangan Edit</label>
                            <textarea name="keterangan" class="form-control" cols="10" rows="3" placeholder="Masukkan alasan edit pembayaran..."></textarea>
                        </div>
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-warning btn-sm" type="submit">
                        <i class="bx bx-pencil me-2"></i>
                        Request Edit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
    <!-- Modal Export -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Data Custom Range</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Rentang Tanggal</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="date" id="exportStartDate" class="form-control" title="Tanggal Mulai">
                                </div>
                                <div class="col-6">
                                    <input type="date" id="exportEndDate" class="form-control" title="Tanggal Akhir">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="exportCustomRange()">Export</button>
                </div>
            </div>
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
            initializeEntriesPerPage();
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
        
        // Initialize entries per page functionality
        function initializeEntriesPerPage() {
            const entriesSelect = document.getElementById('entriesPerPage');
            
            if (entriesSelect) {
                entriesSelect.addEventListener('change', function() {
                    const selectedValue = this.value;
                    loadDataWithAjax(selectedValue);
                });
            }
        }
        
        // Load data using AJAX with entries per page
        function loadDataWithAjax(perPage = 25, page = 1) {
            if (isLoading) return;
            
            showLoading();
            
            // Get current filter values
            const formData = new FormData(document.getElementById('filterForm'));
            const params = new URLSearchParams();
            
            // Add form data to params
            for (let [key, value] of formData.entries()) {
                if (value.trim() !== '') {
                    params.append(key, value);
                }
            }
            
            // Add per_page and page parameters
            params.append('per_page', perPage);
            if (page > 1) {
                params.append('page', page);
            }
            
            // Make AJAX request
            fetch(`{{ route('pembayaran.ajax') }}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateTableWithAjax(data.data.payments, data.data.pagination);
                    updateStatistics(data.data.statistics);
                    updatePaginationWithAjax(data.data.pagination, perPage);
                    
                    // Show notification
                    const totalVisible = perPage === 'all' ? data.data.payments.length : Math.min(parseInt(perPage), data.data.payments.length);
                    showNotification(`Menampilkan ${totalVisible} entri`, 'success');
                } else {
                    showNotification(data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat memuat data', 'error');
            })
            .finally(() => {
                hideLoading();
            });
        }
        
        // Update table with AJAX data
        function updateTableWithAjax(payments, pagination) {
            const tableBody = document.getElementById('tableBody');
            
            if (!tableBody) return;
            
            if (payments.length === 0) {
                tableBody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-money text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                            <p class="text-muted mb-0">Belum ada data pembayaran yang tersedia</p>
                        </div>
                    </td>
                </tr>
            `;
                return;
            }
            
            let tableHTML = '';
            payments.forEach((payment, index) => {
                let rowNumber;
                if (pagination) {
                    const startIndex = ((pagination.current_page - 1) * pagination.per_page) + 1;
                    rowNumber = startIndex + index;
                } else {
                    rowNumber = index + 1;
                }
                
                tableHTML += `
                <tr>
                    <td class="fw-medium">${rowNumber}</td>
                    <td>
                        <div>
                            <div class="fw-medium text-dark">${payment.invoice?.customer?.nama_customer || 'N/A'}</div>
                            <small class="text-muted">${payment.invoice?.customer?.alamat ? payment.invoice.customer.alamat.substring(0, 30) + (payment.invoice.customer.alamat.length > 30 ? '...' : '') : ''}</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info bg-opacity-10 text-primary">
                            ${payment.invoice?.paket?.nama_paket || 'N/A'}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="fw-semibold text-danger">
                                Rp ${formatNumber(payment.jumlah_bayar || 0)}
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex justify-content-between">
                            <i class="bx bx-calendar text-primary me-1"></i>
                            ${formatDateTime(payment.tanggal_bayar, payment.created_at)}
                        </div>
                    </td>
                    <td>
                        <span class="payment-method-badge bg-info bg-opacity-10 text-primary">
                            <i class="bx bx-credit-card"></i>
                            ${payment.metode_bayar}
                        </span>
                    </td>
                    <td>
                        ${getStatusBadge(payment.status)}
                    </td>
                    <td>
                        ${getUserBadge(payment.user)}
                    </td>
                    <td>
                        ${getActionButtons(payment)}
                    </td>
                    <td>
                        <span class="fw-bold">
                            <small>${payment.keterangan || ''}</small>
                        </span>
                    </td>
                </tr>
            `;
            });
            
            tableBody.innerHTML = tableHTML;
        }
        
        // Update pagination with AJAX data
        function updatePaginationWithAjax(pagination, perPage) {
            const paginationContainer = document.querySelector('.p-4.border-top');
            
            if (!paginationContainer) return;
            
            if (perPage === 'all') {
                // Show info for all entries but hide pagination controls
                paginationContainer.style.display = 'block';
                const totalRecords = pagination ? pagination.total : 0;
                paginationContainer.innerHTML = `
                <div class="d-flex justify-content-center">
                    <div class="text-muted small">
                        Menampilkan semua ${formatNumber(totalRecords)} data
                    </div>
                </div>
            `;
                return;
            }
            
            if (!pagination) {
                // Hide pagination when no pagination data
                paginationContainer.style.display = 'none';
                return;
            }
            
            // Show pagination container
            paginationContainer.style.display = 'block';
            
            // Generate pagination info
            const fromRecord = pagination.from || 0;
            const toRecord = pagination.to || 0;
            const totalRecords = pagination.total || 0;
            
            // Generate pagination HTML with info
            let paginationHTML = `
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <div class="text-muted small">
                    Menampilkan ${fromRecord} sampai ${toRecord} dari ${formatNumber(totalRecords)} hasil
                </div>
                <div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-center mb-0">
        `;
            
            // Previous button
            if (pagination.current_page > 1) {
                paginationHTML += `<li class="page-item">
                <a class="page-link ajax-pagination" href="#" data-page="${pagination.current_page - 1}">‹ Previous</a>
            </li>`;
                } else {
                    paginationHTML += `<li class="page-item disabled">
                <span class="page-link">‹ Previous</span>
            </li>`;
                    }
                    
                    // Page numbers
                    const startPage = Math.max(1, pagination.current_page - 2);
                    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
                    
                    if (startPage > 1) {
                        paginationHTML += `<li class="page-item">
                <a class="page-link ajax-pagination" href="#" data-page="1">1</a>
            </li>`;
                            if (startPage > 2) {
                                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                            }
                        }
                        
                        for (let i = startPage; i <= endPage; i++) {
                            if (i === pagination.current_page) {
                                paginationHTML += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
                                } else {
                                    paginationHTML += `<li class="page-item">
                    <a class="page-link ajax-pagination" href="#" data-page="${i}">${i}</a>
                </li>`;
                                    }
                                }
                                
                                if (endPage < pagination.last_page) {
                                    if (endPage < pagination.last_page - 1) {
                                        paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                                    }
                                    paginationHTML += `<li class="page-item">
                <a class="page-link ajax-pagination" href="#" data-page="${pagination.last_page}">${pagination.last_page}</a>
            </li>`;
                                    }
                                    
                                    // Next button
                                    if (pagination.current_page < pagination.last_page) {
                                        paginationHTML += `<li class="page-item">
                <a class="page-link ajax-pagination" href="#" data-page="${pagination.current_page + 1}">Next ›</a>
            </li>`;
                                        } else {
                                            paginationHTML += `<li class="page-item disabled">
                <span class="page-link">Next ›</span>
            </li>`;
                                            }
                                            
                                            paginationHTML += `
                        </ul>
                    </nav>
                </div>
            </div>
        `;
                                            
                                            // Update pagination container
                                            paginationContainer.innerHTML = paginationHTML;
                                            
                                            // Add event listeners to pagination links
                                            const paginationLinks = paginationContainer.querySelectorAll('.ajax-pagination');
                                            paginationLinks.forEach(link => {
                                                link.addEventListener('click', function(e) {
                                                    e.preventDefault();
                                                    const page = this.getAttribute('data-page');
                                                    loadDataWithAjax(getCurrentPerPage(), page);
                                                });
                                            });
                                        }
                                        
                                        // Get current per page value
                                        function getCurrentPerPage() {
                                            const entriesSelect = document.getElementById('entriesPerPage');
                                            return entriesSelect ? entriesSelect.value : 10;
                                        }
                                        
                                        // Update statistics cards
                                        function updateStatistics(stats) {
                                            // Update statistics if needed
                                            console.log('Statistics updated:', stats);
                                        }
                                        
                                        // Helper function to format numbers
                                        function formatNumber(number) {
                                            return new Intl.NumberFormat('id-ID').format(number);
                                        }
                                        
                                        // Helper function to format date and time
                                        function formatDateTime(tanggalBayar, createdAt) {
                                            if (!tanggalBayar || !createdAt) return '';
                                            
                                            const date = new Date(tanggalBayar + ' ' + new Date(createdAt).toTimeString().split(' ')[0]);
                                            return date.toLocaleDateString('id-ID', {
                                                day: '2-digit',
                                                month: '2-digit',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit'
                                            });
                                        }
                                        
                                        // Helper function to get status badge
                                        function getStatusBadge(status) {
                                            if (!status) return '<span class="status-badge bg-secondary bg-opacity-10 text-secondary">N/A</span>';
                                            
                                            if (status.nama_status === 'Sudah Bayar') {
                                                return `
                <span class="status-badge bg-success bg-opacity-10 text-success">
                    <i class="bx bx-check-circle"></i>
                    ${status.nama_status}
                </span>
            `;
                                            } else {
                                                return `
                <span class="status-badge bg-secondary bg-opacity-10 text-secondary">
                    <i class="bx bx-time-five"></i>
                    ${status.nama_status}
                </span>
            `;
                                            }
                                        }
                                        
                                        // Helper function to get user badge
                                        function getUserBadge(user) {
                                            if (user) {
                                                return `
                <span class="status-badge bg-danger bg-opacity-10 text-danger fw-bold">
                    ${user.name}
                </span>
            `;
                                            } else {
                                                return `
                <span class="status-badge bg-info bg-opacity-10 text-primary fw-bold">Tripay</span>
            `;
                                            }
                                        }
                                        
                                        // Helper function to get action buttons
                                        function getActionButtons(payment) {
                                            if (payment.status_id == 8) {
                                                return `
                <div class="d-flex">
                    <a class="btn btn-outline-warning btn-sm text-warning" 
                       data-bs-toggle="modal" 
                       data-bs-target="#editModal${payment.id}">
                        <i class="bx bx-pencil"></i>
                    </a>
                </div>
            `;
                                            } else {
                                                return `
                <button class="btn btn-outline-warning btn-sm text-warning" disabled>
                    <i class="bx bx-pencil"></i>
                </button>
            `;
                                            }
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
                                        
                                        // Handle pagination clicks - Updated to use AJAX
                                        document.addEventListener('click', function(e) {
                                            // Handle old pagination (Laravel generated)
                                            if (e.target.closest('.ajax-pagination-old')) {
                                                e.preventDefault();
                                                const link = e.target.closest('.ajax-pagination-old');
                                                const page = link.getAttribute('data-page');
                                                
                                                // Use AJAX pagination with current per_page setting
                                                loadDataWithAjax(getCurrentPerPage(), page);
                                            }
                                            
                                            // Handle new pagination (AJAX generated) - already handled in updatePaginationWithAjax
                                            // This is for fallback compatibility
                                            if (e.target.closest('.pagination a:not(.ajax-pagination):not(.ajax-pagination-old)')) {
                                                e.preventDefault();
                                                const link = e.target.closest('.pagination a');
                                                const url = new URL(link.href);
                                                const page = url.searchParams.get('page') || 1;
                                                
                                                // Use AJAX pagination with current per_page setting
                                                loadDataWithAjax(getCurrentPerPage(), page);
                                            }
                                        });
                                    </script>
                                    
                                    <script>
                                        // Fungsi format rupiah
                                        function formatRupiah(angka, prefix = "Rp ") {
                                            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                                            split = number_string.split(','),
                                            sisa = split[0].length % 3,
                                            rupiah = split[0].substr(0, sisa),
                                            ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                                            
                                            if (ribuan) {
                                                let separator = sisa ? '.' : '';
                                                rupiah += separator + ribuan.join('.');
                                            }
                                            
                                            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                                            return prefix + rupiah;
                                        }
                                        
                                        document.addEventListener('DOMContentLoaded', function() {
                                            // Loop semua input jumlah
                                            document.querySelectorAll('[id^="jumlah"]').forEach(function(jumlahInput) {
                                                let id = jumlahInput.id.replace('jumlah','');
                                                let jumlahRaw = document.getElementById('jumlahRaw' + id);
                                                if (!jumlahRaw) return;
                                                
                                                // Inisialisasi nilai awal
                                                let initialValue = jumlahRaw.value || '0';
                                                jumlahInput.value = formatRupiah(initialValue);
                                                jumlahRaw.value = initialValue;
                                                
                                                // Event listener saat mengetik
                                                jumlahInput.addEventListener('input', function() {
                                                    let value = this.value.replace(/\D/g,'');
                                                    jumlahRaw.value = value;
                                                    this.value = formatRupiah(value);
                                                });
                                            });
                                            
                                            // SweetAlert konfirmasi sebelum submit form
                                            document.querySelectorAll('form.editPembayaranForm').forEach(function(form) {
                                                form.addEventListener('submit', function(e) {
                                                    e.preventDefault(); // cegah submit default
                                                    
                                                    Swal.fire({
                                                        title: 'Apakah kamu yakin?',
                                                        text: "Data pembayaran akan diperbarui!",
                                                        icon: 'warning',
                                                        showCancelButton: true,
                                                        confirmButtonColor: '#3085d6',
                                                        cancelButtonColor: '#d33',
                                                        confirmButtonText: 'Ya, update sekarang!',
                                                        topLayer: true
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            form.submit();
                                                        }
                                                    });
                                                });
                                            });
                                            
                                            // SweetAlert flash message
                                            @if(session('success'))
                                            Swal.fire({
                                                title: "Sukses!",
                                                text: "{{ session('success') }}",
                                                icon: "success",
                                                timer: 2000,
                                                showConfirmButton: false,
                                                topLayer: true
                                            });
                                            @elseif(session('error'))
                                            Swal.fire({
                                                title: "Gagal!",
                                                text: "{{ session('error') }}",
                                                icon: "error",
                                                timer: 3000,
                                                showConfirmButton: false,
                                                topLayer: true
                                            });
                                            @endif
                                        });
                                    </script>
                                    
                                    <script>
                                        function showExportModal() {
                                            const modal = new bootstrap.Modal(document.getElementById('exportModal'));
                                            modal.show();
                                        }
                                        
                                        function exportData(filter) {
                                            window.location.href = `{{ route('pembayaran.export', '') }}/${filter}`;
                                        }
                                        
                                        function exportCustomRange() {
                                            const startDate = document.getElementById('exportStartDate').value;
                                            const endDate = document.getElementById('exportEndDate').value;
                                            
                                            if (!startDate || !endDate) {
                                                showNotification('Silakan pilih rentang tanggal', 'error');
                                                return;
                                            }
                                            
                                            if (new Date(startDate) > new Date(endDate)) {
                                                showNotification('Tanggal mulai tidak boleh lebih besar dari tanggal akhir', 'error');
                                                return;
                                            }
                                            
                                            const url = new URL(`{{ route('pembayaran.export', 'custom') }}`);
                                            url.searchParams.append('start_date', startDate);
                                            url.searchParams.append('end_date', endDate);
                                            
                                            window.location.href = url.toString();
                                            
                                            // Tutup modal
                                            const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
                                            modal.hide();
                                        }
                                    </script>
                                    
                                    @endsection
                                    