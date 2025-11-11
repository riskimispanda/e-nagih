@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pendapatan')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('page-style')
<style>
    /* Tambahkan di section style */
    .form-select {
        border-radius: 8px;
        border: 1px solid #d0d7de;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        outline: none;
    }
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
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: black;
        padding: 1rem 1.5rem;
    }

    .table td {
        padding: 1rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f4;
        font-size: 12px;
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

    /* Loading Overlay Styles */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-content {
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #dc3545;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-text {
        font-size: 1rem;
        color: #333;
        margin: 0;
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
                    <h5 id="totalRevenueValue" class="fw-bold text-dark mb-3">Rp {{ number_format($pembayaran ?? 0, 0, ',', '.') }}</h5>
                    <small class="text-muted">Total pendapatan langganan tahun ini</small>
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
                        <h5 id="monthlyRevenueValue" class="fw-bold text-dark mb-0">Rp {{ number_format($monthlyRevenue ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
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
                    <h5 id="pendingRevenueValue" class="fw-bold text-dark mb-0">Rp {{ number_format($pendingRevenue ?? 0, 0, ',', '.') }}
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
                    <p class="text-muted small mb-1 fw-medium">Jumlah Pelanggan</p>
                    <h5 id="totalInvoicesValue" class="fw-bold text-dark mb-0">{{ number_format($totalInvoices ?? 0, 0, ',', '.') }}</h5>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bx bxs-user"></i>
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
                <select name="bulan" id="bulan" class="form-select">
                    <option value="">Semua Bulan</option>
                    @php
                    $currentMonth = date('n'); // Bulan sekarang (1-12)
                    @endphp
                    @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ (isset($bulan) && $bulan == $i) ? 'selected' : (($bulan === null || $bulan === '') && $i == $currentMonth ? 'selected' : '') }}>
                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                    </option>
                    @endfor
                </select>
            </div>


            <!-- Status filter removed as requested -->

            <!-- Date Range -->

        </div>

        <!-- Filter action buttons removed as requested -->
    </form>
</div>

<!-- Data Table Personal -->
<div class="table-card mb-5" id="invoiceTable">
    <div class="p-6 border-bottom">
        <h5 class="fw-bold text-dark mb-0" id="invoice-title">
            Daftar Invoice Customer
            @php
                if (!empty($bulan)) {
                    // Create a Carbon instance from the month number and format it
                    echo ' - ' . \Carbon\Carbon::create()->month($bulan)->translatedFormat('F');
                }
            @endphp
        </h5>
        <small class="fw-semibold badge bg-danger bg-opacity-10 text-danger mt-3 mb-3">Estimasi Pendapatan Bulan {{ date('M') }} : Rp {{ number_format($tes ?? 0, 0, ',', '.') }}</small>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 p-4 border-bottom">
        <div class="d-flex flex-column flex-sm-row gap-2">
            <a href="/manual/invoice" class="btn btn-outline-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Kirim Manual Invoice Global">
                <i class="bx bx-message"></i>
            </a>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="export-dropdown">
                <button class="btn btn-secondary btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-file me-1"></i>
                    Export Excel
                </button>
                <ul class="dropdown-menu dropdown-menu-scrollable" style="max-height: 300px; overflow-y: auto;">
                    <!-- Export Semua Data -->
                    <li><h6 class="dropdown-header">Export Semua Data</h6></li>
                    <li>
                        <a class="dropdown-item" href="/unpaid">
                            <i class="bx bx-download"></i>
                            Semua Pelanggan
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <!-- Export Berdasarkan Bulan -->
                    <li><h6 class="dropdown-header">Export Berdasarkan Bulan</h6></li>
                    @php
                        $months = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                        $currentYear = date('Y');
                    @endphp
                    @foreach($months as $num => $name)
                    <li>
                        <a class="dropdown-item" href="{{ route('unpaid.bulan', ['month' => $num, 'year' => $currentYear]) }}">
                            <i class="bx bx-calendar"></i>
                            {{ $name }} {{ $currentYear }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
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
                        <th>Tunggakan</th>
                        <th>Sisa Saldo</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody" style="font-size: 14px;">
                    @forelse($invoices ?? [] as $index => $invoice)
                    <tr class="@if($invoice->customer->trashed()) bg-danger bg-opacity-10 @endif">
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
                                @if($invoice->customer->trashed())
                                <span class="badge bg-label-danger mt-2">
                                    Deaktivasi
                                </span>
                                @endif
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
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-plus-circle text-warning me-2"></i>
                                    <span class="fw-semibold text-warning">
                                        Rp {{ number_format($invoice->tunggakan, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-wallet text-success me-2"></i>
                                    <span class="fw-semibold text-dark">
                                        Rp {{ number_format($invoice->saldo, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>
                            <td style="font-size: 14px;">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-calendar text-danger me-2"></i>
                                    {{ \Carbon\Carbon::parse($invoice->jatuh_tempo)->locale('id')->translatedFormat('F') }}
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
                                @elseif($invoice->status->nama_status == 'Menunggu')
                                <span class="status-badge bg-warning bg-opacity-10 text-warning">
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
                                    @if ($invoice->status && $invoice->status->nama_status == 'Belum Bayar')
                                        <button class="action-btn bg-success bg-opacity-10 text-success btn-sm
                                            @if($invoice->customer->trashed()) disabled @endif"
                                            data-bs-target="#konfirmasiPembayaran{{ $invoice->id }}"
                                            data-bs-toggle="modal"
                                            @if($invoice->customer->trashed()) disabled @endif>
                                            <i class="bx bx-money"></i>
                                        </button>
                                        <a href="/riwayatPembayaran/{{ $invoice->customer_id }}"
                                            class="action-btn btn-sm bg-secondary bg-opacity-10 text-secondary
                                            @if($invoice->customer->trashed()) disabled @endif"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="bottom"
                                            title="History Pembayaran {{ $invoice->customer->nama_customer ?? 'Not Found' }}">
                                            <i class="bx bx-history"></i>
                                        </a>
                                    @endif

                                    <a href="/kirim/invoice/{{ $invoice->id }}"
                                        class="action-btn bg-warning bg-opacity-10 text-warning btn-sm
                                        @if($invoice->customer->trashed()) disabled @endif"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        title="Kirim Invoice"
                                        @if($invoice->customer->trashed()) onclick="return false;" @endif>
                                        <i class="bx bx-message"></i>
                                    </a>

                                    <form action="{{ url('/tripay/sync-payment/'.$invoice->id) }}" method="POST" style="display:inline">
                                        @csrf
                                        <button type="submit"
                                            class="action-btn bg-danger bg-opacity-10 text-danger btn-sm
                                            @if($invoice->customer->trashed()) disabled @endif"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="bottom"
                                            title="Sync Payment"
                                            @if($invoice->customer->trashed()) disabled @endif>
                                            <i class="bx bx-cart"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
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
        <div class="p-4 border-top">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <div class="text-muted small">
                    Menampilkan data {{ $invoices->firstItem() ?? 1 }} sampai {{ $invoices->lastItem() ?? ($invoices->count() ?? 0) }} dari {{ number_format($invoices->total() ?? $invoices->count() ?? 0, 0, ',', '.') }} data
                </div>
                <div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-center mb-0">
                            @if ($invoices->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">‹ Previous</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link ajax-pagination" href="#" data-page="{{ $invoices->currentPage() - 1 }}">‹ Previous</a>
                                </li>
                            @endif

                            @php
                                $start = max(1, $invoices->currentPage() - 2);
                                $end = min($invoices->lastPage(), $invoices->currentPage() + 2);
                            @endphp

                            @if ($start > 1)
                                <li class="page-item">
                                    <a class="page-link ajax-pagination" href="#" data-page="1">1</a>
                                </li>
                                @if ($start > 2)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $invoices->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link">{{ $i }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link ajax-pagination" href="#" data-page="{{ $i }}">{{ $i }}</a>
                                    </li>
                                @endif
                            @endfor

                            @if ($end < $invoices->lastPage())
                                @if ($end < $invoices->lastPage() - 1)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link ajax-pagination" href="#" data-page="{{ $invoices->lastPage() }}">{{ $invoices->lastPage() }}</a>
                                </li>
                            @endif

                            @if ($invoices->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link ajax-pagination" href="#" data-page="{{ $invoices->currentPage() + 1 }}">Next ›</a>
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
    </div>
</div>


{{-- Modal Konfirmasi --}}
{{-- Modal Konfirmasi --}}
@foreach ($invoices as $invoice)
<div class="modal fade" id="konfirmasiPembayaran{{ $invoice->id }}" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title mb-6" id="modalCenterTitle">
                    <i class="bx bx-wallet me-2 text-danger"></i>
                    Konfirmasi Pembayaran <span class="text-danger fw-bold">{{$invoice->customer->nama_customer}}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/request/pembayaran/{{ $invoice->id }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="text" name="invoice_id" value="{{ $invoice->id }}" hidden>

                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Tanggal Jatuh Tempo</label>
                            <input type="date" class="form-control" value="{{ \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('Y-m-d') }}" readonly>
                        </div>
                    </div>

                    <!-- TAMBAHKAN FIELD TIPE PEMBAYARAN DI SINI -->
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Tipe Pembayaran</label>
                            <select name="tipe_pembayaran" class="form-select" required>
                                <option value="">Pilih Tipe Pembayaran</option>
                                <option value="reguler">Pembayaran Reguler</option>
                                <option value="diskon">Pembayaran Diskon</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-4 col-lg-4">
                            <label class="form-label mb-2">Tagihan</label>
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input pilihan"
                                       name="bayar[]"
                                       value="tagihan"
                                       data-amount="{{ $invoice->tagihan ?? 0 }}"
                                       data-id="{{ $invoice->id }}">
                                <label class="form-check-label">
                                    Rp {{ number_format($invoice->tagihan ?? 0, 0, ',', '.') }}
                                </label>
                            </div>
                        </div>

                        <div class="col mb-4 col-lg-4">
                            <label class="form-label mb-2">Biaya Tambahan</label>
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input pilihan"
                                       name="bayar[]"
                                       value="tambahan"
                                       data-amount="{{ $invoice->tambahan ?? 0 }}"
                                       data-id="{{ $invoice->id }}">
                                <label class="form-check-label">
                                    Rp {{ number_format($invoice->tambahan ?? 0, 0, ',', '.') }}
                                </label>
                            </div>
                        </div>

                        <div class="col mb-4 col-lg-4">
                            <label class="form-label mb-2">Tunggakan</label>
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input pilihan"
                                       name="bayar[]"
                                       value="tunggakan"
                                       data-amount="{{ $invoice->tunggakan ?? 0 }}"
                                       data-id="{{ $invoice->id }}">
                                <label class="form-check-label">
                                    Rp {{ number_format($invoice->tunggakan ?? 0, 0, ',', '.') }}
                                </label>
                            </div>
                        </div>
                        <div class="col mb-4 col-lg-4">
                            <label class="form-label mb-2">Sisa Saldo</label>
                            <div class="form-check">
                                <input type="checkbox"
                                    class="form-check-input pilihan"
                                    name="saldo"
                                    value="{{ $invoice->saldo }}"
                                    data-amount="{{ $invoice->saldo ?? 0 }}"
                                    data-id="{{ $invoice->id }}"
                                    data-type="saldo">
                                <label class="form-check-label">
                                    Rp {{ number_format($invoice->saldo ?? 0, 0, ',', '.') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-4 col-lg-12">
                            <label class="form-label">Total</label>
                            <input type="text" id="total{{ $invoice->id }}" class="form-control" name="total" value="Rp 0" readonly>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col mb-4 col-lg-6">
                            <label class="form-label">Jumlah Bayar</label>
                            <input type="text" class="form-control" id="revenueAmount{{ $invoice->id }}" name="revenueAmount" oninput="formatRupiah(this, {{ $invoice->id }})" placeholder="Masukkan jumlah bayar" required>
                            <input type="text" hidden id="raw{{ $invoice->id }}" name="jumlah_bayar">
                        </div>
                        <div class="col mb-4 col-lg-6">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="metode_id" class="form-select">
                                <option value="" selected disabled>Pilih Metode Pembayaran</option>
                                @foreach ($metode as $item)
                                <option value="{{ $item->nama_metode }}">{{$item->nama_metode}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-4 col-lg-12">
                            <label class="form-label">Bukti Pembayaran</label>
                            <input type="file" class="form-control" name="bukti_pembayaran">
                        </div>
                    </div>
                </div>

                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-danger btn-sm" id="submitBtn{{ $invoice->id }}">
                        <i class="bx bx-send me-1"></i>Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- Modal Detail Invoice --}}
@foreach ($invoices as $invoice)
<div class="modal fade" id="detailInvoice{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-sm">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bx bx-receipt text-primary fs-4"></i>
                    <span>Detail Invoice - {{ $invoice->customer->nama_customer }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <hr>
            <div class="modal-body mb-3">
                <div class="row gy-3 gx-4">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Customer</label>
                        <div class="d-flex align-items-center gap-2 fs-6">
                            <i class="bx bx-user text-primary"></i>
                            <span class="text-dark" id="customerName">{{$invoice->customer->nama_customer}}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Paket</label>
                        <div class="d-flex align-items-center gap-2 fs-6">
                            <i class="bx bx-package text-primary"></i>
                            <span class="text-dark" id="packageName">{{$invoice->paket->nama_paket}}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Tagihan</label>
                        <div class="d-flex align-items-center gap-2 fs-6">
                            <i class="bx bx-money text-primary"></i>
                            <span class="text-dark" id="invoiceAmount">Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Tagihan Tambahan</label>
                        <div class="d-flex align-items-center gap-2 fs-6">
                            <i class="bx bx-plus-circle text-primary"></i>
                            <span class="text-dark" id="additionalAmount">Rp {{ number_format($invoice->tambahan, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Sisa Saldo</label>
                        <div class="d-flex align-items-center gap-2 fs-6">
                            <i class="bx bx-wallet text-primary"></i>
                            <span class="text-dark" id="balanceAmount">Rp {{ number_format($invoice->saldo, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Jatuh Tempo</label>
                        <div class="d-flex align-items-center gap-2 fs-6">
                            <i class="bx bx-calendar text-primary"></i>
                            <span class="text-dark" id="dueDate">{{ date('d-m-Y', strtotime($invoice->jatuh_tempo)) }}</span>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-muted">Status</label>
                        <div class="d-flex align-items-center gap-2 fs-6">
                            <i class="bx bx-check-circle text-primary"></i>
                            <span class="text-dark" id="invoiceStatus">{{ $invoice->status->nama_status }}</span>
                        </div>
                    </div>

                </div>
            </div>
            <hr>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Custom Date Range -->
<div class="modal fade" id="customDateModal" tabindex="-1" aria-labelledby="customDateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('unpaid.range') }}" method="GET">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customDateModalLabel">Export Invoice Belum Bayar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="startDate" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="startDate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="endDate" class="form-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="endDate" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Export</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- Tambahkan ini di bagian bawah content, sebelum loading overlay -->
<div id="ajaxModalsContainer"></div>
<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay d-none">
    <div class="loading-content">
        <div class="spinner"></div>
        <p id="loadingText" class="loading-text">sabar, semua butuh proses</p>
    </div>
</div>


{{-- Javascript --}}
<script>
function showLoadingOverlay(message) {
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingText = document.getElementById('loadingText');
    if (loadingOverlay && loadingText) {
        loadingText.textContent = message;
        loadingOverlay.classList.remove('d-none');
    }
}
</script>
<script>
let searchTimeout;
let isLoading = false;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    initializeFilters();
    initializeEntriesPerPage();
    initializePendapatanFilter();
    initializeTooltips();

    // Load data otomatis dengan filter bulan default
    loadDataWithAjax(getCurrentPerPage(), 1);
});

// Initialize search functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Reset ke page 1 saat search
                loadDataWithAjax(getCurrentPerPage(), 1);
            }, 800);
        });
    }
}

// Initialize filter functionality
function initializeFilters() {
    const bulanSelect = document.getElementById('bulan');

    if (bulanSelect) {
        bulanSelect.addEventListener('change', function() {
            // Reset ke page 1 saat filter berubah
            loadDataWithAjax(getCurrentPerPage(), 1);
        });
    }
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

// Initialize pendapatan filter (jika ada)
function initializePendapatanFilter() {
    const pendapatanElement = document.getElementById('pendapatan');

    if (pendapatanElement) {
        pendapatanElement.addEventListener('change', function() {
            const selectedValue = this.value;
            const invoiceTable = document.getElementById('invoiceTable');
            const revenueTable = document.getElementById('revenueTable');

            if (invoiceTable && revenueTable) {
                if (selectedValue === 'langganan') {
                    invoiceTable.style.display = 'block';
                    revenueTable.style.display = 'none';
                } else if (selectedValue === 'lain-lain') {
                    invoiceTable.style.display = 'none';
                    revenueTable.style.display = 'block';
                } else if (selectedValue === 'semua') {
                    invoiceTable.style.display = 'block';
                    revenueTable.style.display = 'block';
                } else {
                    invoiceTable.style.display = 'none';
                    revenueTable.style.display = 'none';
                }
            }
        });
    }
}

// Initialize Bootstrap tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (typeof bootstrap !== 'undefined' && tooltipTriggerList.length > 0) {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
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
    fetch(`{{ route('pendapatan.ajax') }}?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }).catch(() => {
                throw new Error(`HTTP error! status: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateTableWithAjax(data.data.invoices, data.data.pagination);
            updateStatistics(data.data.statistics);
            updatePaginationWithAjax(data.data.pagination, perPage);

            // Update title with selected month
            const bulanSelect = document.getElementById('bulan');
            const invoiceTitle = document.getElementById('invoice-title');
            if (bulanSelect && invoiceTitle) {
                const selectedOption = bulanSelect.options[bulanSelect.selectedIndex];
                const monthName = selectedOption.text;
                const monthValue = selectedOption.value;

                if (monthValue && monthValue !== '') {
                    invoiceTitle.innerHTML = `Daftar Invoice Customer - ${monthName}`;
                } else {
                    invoiceTitle.innerHTML = 'Daftar Invoice Customer';
                }
            }

            // GENERATE MODAL DARI DATA AJAX
            generateModalsFromAjaxData(data.data.invoices);

            const totalVisible = perPage === 'all' ? data.data.invoices.length : Math.min(parseInt(perPage), data.data.invoices.length);
            showNotification(`Menampilkan ${totalVisible} entri`, 'success');
        } else {
            showNotification(data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        showNotification('Terjadi kesalahan saat memuat data: ' + error.message, 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

// GENERATE MODAL DARI DATA AJAX
function generateModalsFromAjaxData(invoices) {
    const modalContainer = document.getElementById('ajaxModalsContainer');
    if (!modalContainer) return;

    // Hapus modal lama
    modalContainer.innerHTML = '';

    // Generate modal baru untuk setiap invoice
    invoices.forEach(invoice => {
        const modalHTML = createModalHTML(invoice);
        modalContainer.innerHTML += modalHTML;
    });

    // Re-initialize modal event listeners
    initializeModalEventListeners();
}

// CREATE MODAL HTML DARI DATA INVOICE
function createModalHTML(invoice) {
    const jatuhTempo = invoice.jatuh_tempo ? new Date(invoice.jatuh_tempo).toISOString().split('T')[0] : '';

    return `
    <div class="modal fade" id="konfirmasiPembayaran${invoice.id}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title mb-6">
                        <i class="bx bx-wallet me-2 text-danger"></i>
                        Konfirmasi Pembayaran <span class="text-danger fw-bold">${invoice.customer?.nama_customer || 'N/A'}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/request/pembayaran/${invoice.id}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="invoice_id" value="${invoice.id}">

                        <div class="row">
                            <div class="col mb-4">
                                <label class="form-label">Tanggal Jatuh Tempo</label>
                                <input type="date" class="form-control" value="${jatuhTempo}" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col mb-4">
                                <label class="form-label">Tipe Pembayaran</label>
                                <select name="tipe_pembayaran" class="form-select" required>
                                    <option value="">Pilih Tipe Pembayaran</option>
                                    <option value="reguler">Pembayaran Reguler</option>
                                    <option value="diskon">Pembayaran Diskon</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col mb-4 col-lg-4">
                                <label class="form-label mb-2">Tagihan</label>
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input pilihan"
                                           name="bayar[]"
                                           value="tagihan"
                                           data-amount="${invoice.tagihan || 0}"
                                           data-id="${invoice.id}">
                                    <label class="form-check-label">
                                        Rp ${formatNumber(invoice.tagihan || 0)}
                                    </label>
                                </div>
                            </div>

                            <div class="col mb-4 col-lg-4">
                                <label class="form-label mb-2">Biaya Tambahan</label>
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input pilihan"
                                           name="bayar[]"
                                           value="tambahan"
                                           data-amount="${invoice.tambahan || 0}"
                                           data-id="${invoice.id}">
                                    <label class="form-check-label">
                                        Rp ${formatNumber(invoice.tambahan || 0)}
                                    </label>
                                </div>
                            </div>

                            <div class="col mb-4 col-lg-4">
                                <label class="form-label mb-2">Tunggakan</label>
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input pilihan"
                                           name="bayar[]"
                                           value="tunggakan"
                                           data-amount="${invoice.tunggakan || 0}"
                                           data-id="${invoice.id}">
                                    <label class="form-check-label">
                                        Rp ${formatNumber(invoice.tunggakan || 0)}
                                    </label>
                                </div>
                            </div>
                            <div class="col mb-4 col-lg-4">
                                <label class="form-label mb-2">Sisa Saldo</label>
                                <div class="form-check">
                                    <input type="checkbox"
                                        class="form-check-input pilihan"
                                        name="saldo"
                                        value="${invoice.saldo || 0}"
                                        data-amount="${invoice.saldo || 0}"
                                        data-id="${invoice.id}"
                                        data-type="saldo">
                                    <label class="form-check-label">
                                        Rp ${formatNumber(invoice.saldo || 0)}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-4 col-lg-12">
                                <label class="form-label">Total</label>
                                <input type="text" id="total${invoice.id}" class="form-control" name="total" value="Rp 0" readonly>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col mb-4 col-lg-6">
                                <label class="form-label">Jumlah Bayar</label>
                                <input type="text" class="form-control" id="revenueAmount${invoice.id}" name="revenueAmount" oninput="formatRupiah(this, ${invoice.id})" placeholder="Masukkan jumlah bayar" required>
                                <input type="hidden" id="raw${invoice.id}" name="jumlah_bayar">
                            </div>
                            <div class="col mb-4 col-lg-6">
                                <label class="form-label">Metode Pembayaran</label>
                                <select name="metode_id" class="form-select">
                                    <option value="" selected disabled>Pilih Metode Pembayaran</option>
                                    ${generateMetodeOptions()}
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col mb-4 col-lg-12">
                                <label class="form-label">Bukti Pembayaran</label>
                                <input type="file" class="form-control" name="bukti_pembayaran">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bx bx-send me-1"></i>Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    `;
}

// GENERATE METODE OPTIONS (gunakan data dari PHP yang sudah ada di page)
function generateMetodeOptions() {
    // Anda bisa mengembalikan string kosong, atau jika perlu bisa ditambahkan logic
    // untuk generate options dari data yang sudah ada.
    let optionsHTML = '';
    @foreach ($metode as $item)
        optionsHTML += `<option value="{{ $item->nama_metode }}">{{ $item->nama_metode }}</option>`;
    @endforeach
    return optionsHTML;
}

// INITIALIZE MODAL EVENT LISTENERS
function initializeModalEventListeners() {
    // Re-initialize checkbox calculations untuk modal yang baru
    document.querySelectorAll(".pilihan").forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            const invoiceId = this.getAttribute("data-id");
            recalcTotalModal(invoiceId);
        });
    });
}

// RECALC TOTAL UNTUK MODAL (VERSI UPDATE)
function recalcTotalModal(invoiceId) {
    let total = 0;

    const checkedItems = document.querySelectorAll('.pilihan[data-id="' + invoiceId + '"]:checked:not([data-type="saldo"])');
    checkedItems.forEach(function (item) {
        const amount = parseInt(item.getAttribute("data-amount")) || 0;
        total += amount;
    });

    const saldoCb = document.querySelector('.pilihan[data-id="' + invoiceId + '"][data-type="saldo"]');
    if (saldoCb && saldoCb.checked) {
        const saldoAmount = parseInt(saldoCb.getAttribute("data-amount")) || parseInt(saldoCb.value) || 0;
        total = Math.max(total - saldoAmount, 0);
    }

    const totalInput = document.getElementById("total" + invoiceId);
    if (totalInput) totalInput.value = "Rp " + (total || 0).toLocaleString("id-ID");
}

// Di function updateTableWithAjax
function updateTableWithAjax(invoices, pagination) {
    const tableBody = document.getElementById('tableBody');

    if (!tableBody) return;

    if (invoices.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                        <p class="text-muted mb-0">Belum ada data invoice yang tersedia</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    let tableHTML = '';
    invoices.forEach((invoice, index) => {
        let rowNumber;
        if (pagination) {
            const startIndex = ((pagination.current_page - 1) * pagination.per_page) + 1;
            rowNumber = startIndex + index;
        } else {
            rowNumber = index + 1;
        }

        // PERBAIKAN: Safe access untuk nested properties
        const customer = invoice.customer || {};
        const customerName = customer.nama_customer || 'N/A';
        const customerAddress = customer.alamat || '';
        const isCustomerDeleted = customer.deleted_at !== null && customer.deleted_at !== undefined;

        const paket = invoice.paket || {};
        const packageName = paket.nama_paket || 'N/A';

        const status = invoice.status || {};

        tableHTML += `
            <tr class="${isCustomerDeleted ? 'bg-danger bg-opacity-10' : ''}">
                <td class="fw-medium">${rowNumber}</td>
                <td>
                    <div>
                        <div class="fw-medium text-dark">${customerName}</div>
                        <small class="text-muted">${customerAddress.substring(0, 30)}${customerAddress.length > 30 ? '...' : ''}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-info bg-opacity-10 text-primary">
                        ${packageName}
                    </span>
                    ${isCustomerDeleted ? '<span class="badge bg-label-danger mt-2">Deaktivasi</span>' : ''}
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="bx bx-money text-secondary me-2"></i>
                        <span class="fw-bold text-secondary">
                            Rp ${formatNumber(invoice.tagihan || 0)}
                        </span>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="bx bx-plus-circle text-warning me-2"></i>
                        <span class="fw-semibold text-warning">
                            Rp ${formatNumber(invoice.tambahan || 0)}
                        </span>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="bx bx-plus-circle text-warning me-2"></i>
                        <span class="fw-semibold text-warning">
                            Rp ${formatNumber(invoice.tunggakan || 0)}
                        </span>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="bx bx-wallet text-success me-2"></i>
                        <span class="fw-semibold text-dark">
                            Rp ${formatNumber(invoice.saldo || 0)}
                        </span>
                    </div>
                </td>
                <td style="font-size: 14px;">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-calendar text-danger me-2"></i>
                        ${formatDate(invoice.jatuh_tempo)}
                    </div>
                </td>
                <td>
                    ${getStatusBadge(status)}
                </td>
                <td>
                    <div class="d-flex gap-2">
                        ${getActionButtons(invoice, customer)}
                    </div>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = tableHTML;
    initializeTooltips();
}

// Update pagination with AJAX data
function updatePaginationWithAjax(pagination, perPage) {
    const paginationContainer = document.querySelector('.p-4.border-top');

    if (!paginationContainer) return;

    if (perPage === 'all') {
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
        paginationContainer.style.display = 'none';
        return;
    }

    paginationContainer.style.display = 'block';

    const fromRecord = pagination.from || 0;
    const toRecord = pagination.to || 0;
    const totalRecords = pagination.total || 0;

    let paginationHTML = `
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
            <div class="text-muted small">
                Menampilkan data ${fromRecord} sampai ${toRecord} dari ${formatNumber(totalRecords)} data
            </div>
            <div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
    `;

    if (pagination.current_page > 1) {
        paginationHTML += `<li class="page-item">
            <a class="page-link ajax-pagination" href="#" data-page="${pagination.current_page - 1}">‹ Previous</a>
        </li>`;
    } else {
        paginationHTML += `<li class="page-item disabled">
            <span class="page-link">‹ Previous</span>
        </li>`;
    }

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

    paginationContainer.innerHTML = paginationHTML;

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
    return entriesSelect ? entriesSelect.value : 25;
}

// Update statistics cards
function updateStatistics(stats) {
    const totalRevenueElement = document.getElementById('totalRevenueValue');
    if (totalRevenueElement) {
        totalRevenueElement.textContent = `Rp ${formatNumber(stats.totalRevenue || 0)}`;
    }

    const monthlyRevenueElement = document.getElementById('monthlyRevenueValue');
    if (monthlyRevenueElement) {
        monthlyRevenueElement.textContent = `Rp ${formatNumber(stats.monthlyRevenue || 0)}`;
    }

    const pendingRevenueElement = document.getElementById('pendingRevenueValue');
    if (pendingRevenueElement) {
        pendingRevenueElement.textContent = `Rp ${formatNumber(stats.pendingRevenue || 0)}`;
    }

    const totalInvoicesElement = document.getElementById('totalInvoicesValue');
    if (totalInvoicesElement) {
        totalInvoicesElement.textContent = formatNumber(stats.totalInvoices || 0);
    }
}

// Helper function to format numbers
function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

// Helper function to format dates
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        month: 'long',
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
    } else if (status.nama_status === 'Belum Bayar') {
        return `
            <span class="status-badge bg-danger bg-opacity-10 text-danger">
                <i class="bx bx-x-circle"></i>
                ${status.nama_status}
            </span>
        `;
    } else if (status.nama_status === 'Menunggu') {
        return `
            <span class="status-badge bg-warning bg-opacity-10 text-warning">
                <i class="bx bx-time-five"></i>
                ${status.nama_status}
            </span>
        `;
    }

    return `<span class="status-badge bg-secondary bg-opacity-10 text-secondary">${status.nama_status}</span>`;
}

// Helper function to get action buttons
function getActionButtons(invoice) {
    let buttons = '';

    if (invoice.status && invoice.status.nama_status === 'Belum Bayar') {
        buttons += `
            <button class="action-btn bg-success bg-opacity-10 text-success btn-sm
                ${invoice.customer?.trashed ? 'disabled' : ''}"
                data-bs-target="#konfirmasiPembayaran${invoice.id}"
                data-bs-toggle="modal"
                ${invoice.customer?.trashed ? 'disabled' : ''}>
                <i class="bx bx-money"></i>
            </button>
            <a href="/riwayatPembayaran/${invoice.customer_id}"
               class="action-btn btn-sm bg-secondary bg-opacity-10 text-secondary
               ${invoice.customer?.trashed ? 'disabled' : ''}"
               data-bs-toggle="tooltip"
               data-bs-placement="bottom"
               title="History Pembayaran">
                <i class="bx bx-history"></i>
            </a>
        `;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    buttons += `
        <a href="/kirim/invoice/${invoice.id}"
           class="action-btn bg-warning bg-opacity-10 text-warning btn-sm
           ${invoice.customer?.trashed ? 'disabled' : ''}"
           data-bs-toggle="tooltip"
           data-bs-placement="bottom"
           title="Kirim Invoice"
           ${invoice.customer?.trashed ? 'onclick="return false;"' : ''}>
            <i class="bx bx-message"></i>
        </a>
        <form action="/tripay/sync-payment/${invoice.id}" method="POST" style="display:inline">
            <input type="hidden" name="_token" value="${csrfToken}">
            <button type="submit"
                class="action-btn bg-danger bg-opacity-10 text-danger btn-sm
                ${invoice.customer?.trashed ? 'disabled' : ''}"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                title="Sync Payment"
                ${invoice.customer?.trashed ? 'disabled' : ''}>
                <i class="bx bx-cart"></i>
            </button>
        </form>
    `;

    return buttons;
}

// Show loading overlay
function showLoading() {
    isLoading = true;
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingText = document.getElementById('loadingText');

    if (loadingOverlay && loadingText) {
        // Reset to default text when used by other functions
        loadingText.textContent = 'Memuat data...';
        loadingOverlay.classList.remove('d-none');
    }
}

// Hide loading overlay
function hideLoading() {
    isLoading = false;
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingText = document.getElementById('loadingText');

    if (loadingOverlay && loadingText) {
        // Reset to default text when used by other functions
        loadingText.textContent = 'Memuat data...';
        loadingOverlay.classList.add('d-none');
    }
}

// Show notification
function showNotification(message, type = 'info') {
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

    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Handle pagination clicks
document.addEventListener('click', function(e) {
    if (e.target.closest('.ajax-pagination')) {
        e.preventDefault();
        const link = e.target.closest('.ajax-pagination');
        const page = link.getAttribute('data-page');
        loadDataWithAjax(getCurrentPerPage(), page);
    }
});

// Format input as Rupiah currency
function formatRupiah(el, id) {
    let angka = el.value.replace(/[^0-9]/g, '');
    let number = parseInt(angka, 10) || 0;

    el.value = number.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    });

    const rawInput = document.getElementById('raw' + id);
    if (rawInput) {
        rawInput.value = number;
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// GANTI kode event listener SweetAlert yang lama dengan ini:

// Event delegation untuk handle tombol konfirmasi
document.addEventListener('click', function(e) {
    // Cek apakah yang diklik adalah tombol submit dalam modal konfirmasi
    if (e.target && (e.target.matches('button[type="submit"]') || 
                     e.target.closest('button[type="submit"]'))) {

        const button = e.target.matches('button[type="submit"]') ? e.target : e.target.closest('button[type="submit"]');
        const form = button.closest('form');

        // Pastikan ini adalah form konfirmasi pembayaran
        if (form && form.action.includes('/request/pembayaran/')) {
            e.preventDefault();
            
            const modal = form.closest('.modal');
            const invoiceId = modal.id.replace('konfirmasiPembayaran', '');
            
            // Ambil data dari form
            const customerNameElement = modal.querySelector('.modal-title .fw-bold');
            const customerName = customerNameElement ? customerNameElement.textContent : 'Customer';
            
            const paymentPeriodEl = form.querySelector('input[type="date"]');
            const paymentPeriod = paymentPeriodEl ? 
                new Date(paymentPeriodEl.value).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : 
                'N/A';

            const paymentAmountInput = form.querySelector(`#raw${invoiceId}`);
            const paymentAmount = paymentAmountInput ? paymentAmountInput.value : '0';

            // Ambil Tipe Pembayaran
            const paymentTypeSelect = form.querySelector('select[name="tipe_pembayaran"]');
            const paymentType = paymentTypeSelect ? paymentTypeSelect.options[paymentTypeSelect.selectedIndex].text : 'N/A';

            // Ambil Paket Langganan dari baris tabel
            const packageName = button.closest('tr')?.querySelector('.badge.bg-opacity-10.text-primary')?.textContent.trim() || 'N/A';
            // Hitung komponen pembayaran
            let billAmount = 0;
            let additionalAmount = 0;
            let arrearsAmount = 0;
            let balanceUsed = 0;

            const tagihanCheckbox = form.querySelector('input[value="tagihan"]:checked');
            if (tagihanCheckbox) billAmount = parseFloat(tagihanCheckbox.dataset.amount) || 0;

            const tambahanCheckbox = form.querySelector('input[value="tambahan"]:checked');
            if (tambahanCheckbox) additionalAmount = parseFloat(tambahanCheckbox.dataset.amount) || 0;

            const tunggakanCheckbox = form.querySelector('input[value="tunggakan"]:checked');
            if (tunggakanCheckbox) arrearsAmount = parseFloat(tunggakanCheckbox.dataset.amount) || 0;

            const saldoCheckbox = form.querySelector('input[name="saldo"]:checked');
            if (saldoCheckbox) balanceUsed = parseFloat(saldoCheckbox.dataset.amount) || 0;

            // Fungsi format Rupiah
            const toRupiah = (num) => `Rp ${parseInt(num || 0).toLocaleString('id-ID')}`;

            // Konten konfirmasi
            const confirmationHtml = `
                <div class="text-start p-3">
                    <p><strong>Nama Pelanggan:</strong> ${customerName}</p>
                    <p><strong>Tipe Pembayaran:</strong> ${paymentType}</p>
                    <p><strong>Periode Bayar:</strong> ${paymentPeriod}</p>
                    <hr>
                    <p><strong>Tagihan:</strong> ${toRupiah(billAmount)}</p>
                    <p><strong>Tambahan:</strong> ${toRupiah(additionalAmount)}</p>
                    <p><strong>Tunggakan:</strong> ${toRupiah(arrearsAmount)}</p>
                    <p><strong>Saldo Digunakan:</strong> ${toRupiah(balanceUsed)}</p>
                    <hr>
                    <p class="fs-5 fw-bold"><strong>Jumlah Bayar:</strong> ${toRupiah(paymentAmount)}</p>
                </div>
            `;

            // Tampilkan SweetAlert
            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                html: confirmationHtml,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Proses Pembayaran!',
                cancelButtonText: 'Batal',
                topLayer: true,
                customClass: {
                    popup: 'sweetalert-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoadingOverlay('Memproses pembayaran...');
                    // Submit form setelah konfirmasi
                    form.submit();
                }
            });
        }
    }
});

// Fungsi untuk menghitung ulang total pada modal
function recalcTotal(invoiceId) {
    let total = 0;
    const modal = document.getElementById(`konfirmasiPembayaran${invoiceId}`);
    if (!modal) return;

    // 1. Jumlahkan semua item yang dipilih (kecuali saldo)
    modal.querySelectorAll('.pilihan:checked:not([data-type="saldo"])').forEach(item => {
        total += parseFloat(item.dataset.amount) || 0;
    });

    // 2. Kurangi dengan saldo jika dipilih
    const saldoCheckbox = modal.querySelector('.pilihan[data-type="saldo"]:checked');
    if (saldoCheckbox) {
        const saldoAmount = parseFloat(saldoCheckbox.dataset.amount) || 0;
        total = Math.max(0, total - saldoAmount);
    }

    // 3. Tampilkan total
    const totalInput = modal.querySelector(`#total${invoiceId}`);
    if (totalInput) {
        totalInput.value = `Rp ${total.toLocaleString('id-ID')}`;
    }
}
</script>
@endsection