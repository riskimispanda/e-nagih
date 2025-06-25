@extends('layouts.contentNavbarLayout')

@section('title', 'Pembayaran Daily')
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
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="/corp/pendapatan" class="text-decoration-none">Langganan</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/data/pendapatan" class="text-decoration-none">Personal</a>
        </li>
        <li class="breadcrumb-item" aria-current="page">
            <a href="/data/pembayaran" class="text-decoration-none">Data Pembayaran</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Pembayaran Harian</li>
    </ol>
</nav>

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

<div class="table-card">
    <div class="p-6 border-bottom">
        <h5 class="fw-semibold text-dark mb-0">Total Pembayaran Harian</h5>
    </div>

    <div class="table-responsive p-2">
        <div id="tableContainer">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Cash</th>
                        <th>Transfer</th>
                        <th>E-Wallet</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($daily ?? [] as $index => $payment)
                        <tr>
                            <td>
                                <div>
                                    <div class="fw-medium text-dark">
                                        <i class="bx bx-calendar text-primary"></i>
                                        {{ \Carbon\Carbon::parse($payment->date)->format('d F Y') ?? 'N/A' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-primary">
                                    Rp {{ number_format($payment->cash_total, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="fw-semibold text-danger">
                                        Rp {{ number_format($payment->transfer_total, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    Rp {{ number_format($payment->ewallet_total, 0, ',', '.') }}
                                </div>
                            </td>
                            <td>
                                <span class="payment-method-badge bg-info bg-opacity-10 text-primary">
                                    <i class="bx bx-wallet"></i>
                                    Rp {{ number_format($payment->total, 0, ',', '.') }}
                                </span>
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
                <tfoot class="table-warning">
                    <tr class="fw-bold">
                        <td>Total Keseluruhan</td>
                        <td>Rp {{ number_format($daily->sum('cash_total'), 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($daily->sum('transfer_total'), 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($daily->sum('ewallet_total'), 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($daily->sum('total'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
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
@endsection