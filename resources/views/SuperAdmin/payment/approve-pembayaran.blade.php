@extends('layouts.contentNavbarLayout')

@section('title', 'Konfirmasi Pembayaran')

@section('page-style')
    <style>
        .confirmation-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .confirmation-card:hover {
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

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.70rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #0056b3;
            border: 1px solid #99d6ff;
        }

        .status-confirmed {
            background-color: #d1e7dd;
            color: #0a3622;
            border: 1px solid #a3cfbb;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f1aeb5;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            border: none;
            transition: all 0.2s ease;
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

        .priority-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .priority-high {
            background-color: #f8d7da;
            color: #721c24;
        }

        .priority-medium {
            background-color: #fff3cd;
            color: #856404;
        }

        .priority-low {
            background-color: #d1e7dd;
            color: #0a3622;
        }

        .no-data {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 1rem;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="/data/pendapatan" class="text-decoration-none">Personal</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Konfirmasi Pembayaran</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h4 class="fw-bold text-dark mb-2">
                        <i class="bx bx-check-shield me-2 text-primary"></i>Konfirmasi Pembayaran
                    </h4>
                    <p class="text-muted mb-0">Kelola dan konfirmasi request pembayaran dari admin keuangan</p>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="refreshData()" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-refresh me-2"></i>Refresh
                    </button>
                    <button onclick="exportData()" class="btn btn-outline-success btn-sm">
                        <i class="bx bx-download me-2"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Requests -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="confirmation-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 fw-medium">Total Request</p>
                        <h5 class="fw-bold text-dark mb-0">{{ number_format($totalRequests ?? 0, 0, ',', '.') }}</h5>
                        <small class="text-muted">Menunggu konfirmasi</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bx bx-time-five"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Requests -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="confirmation-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 fw-medium">Request Hari Ini</p>
                        <h5 class="fw-bold text-dark mb-0">{{ number_format($todayRequests ?? 0, 0, ',', '.') }}</h5>
                        <small class="text-muted">Request baru</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bx bx-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Amount -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="confirmation-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 fw-medium">Total Nominal</p>
                        <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($pendingAmount ?? 0, 0, ',', '.') }}</h5>
                        <small class="text-muted">Menunggu konfirmasi</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bx bx-money"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Processing Status -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="confirmation-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 fw-medium">Status Sistem</p>
                        <h5 class="fw-bold text-success mb-0">Online</h5>
                        <small class="text-muted">Siap memproses</small>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bx bx-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-card p-4 mb-4">
        <form id="filterForm" method="GET">
            <div class="row g-3">
                <!-- Search Input -->
                <div class="col-12 col-lg-4">
                    <label class="form-label fw-medium text-dark">
                        <i class="bx bx-search me-1"></i>Pencarian
                    </label>
                    <div class="position-relative">
                        <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" name="search" value="{{ $search ?? '' }}"
                            placeholder="Cari nama customer, paket, atau metode pembayaran..."
                            class="form-control search-input">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-12 col-md-6 col-lg-2">
                    <label class="form-label fw-medium text-dark">
                        <i class="bx bx-filter me-1"></i>Status
                    </label>
                    <select id="statusFilter" name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach ($statuses ?? [] as $statusOption)
                            <option value="{{ $statusOption->id }}"
                                {{ ($status ?? '') == $statusOption->id ? 'selected' : '' }}>
                                {{ $statusOption->nama_status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div class="col-12 col-md-6 col-lg-2">
                    <label class="form-label fw-medium text-dark">
                        <i class="bx bx-credit-card me-1"></i>Metode
                    </label>
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
                <div class="col-12 col-lg-4">
                    <label class="form-label fw-medium text-dark">
                        <i class="bx bx-calendar me-1"></i>Periode Tanggal
                    </label>
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

            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-modern">
                            <i class="bx bx-search me-2"></i>Cari
                        </button>
                        <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary btn-modern">
                            <i class="bx bx-reset me-2"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table Section -->
    <div class="table-card">
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-semibold mb-1">
                        <i class="bx bx-list-ul me-2"></i>Request Konfirmasi Pembayaran
                    </h5>
                    <p class="text-muted small mb-0">
                        Menampilkan {{ $paymentRequests->count() }} dari {{ $paymentRequests->total() }} request
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="selectAll()" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-check-square me-1"></i>Pilih Semua
                    </button>
                    <button onclick="bulkApprove()" class="btn btn-success btn-sm" id="bulkApproveBtn"
                        style="display: none;">
                        <i class="bx bx-check me-1"></i>Setujui Terpilih
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive p-2">
            <div id="tableContainer">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                            </th>
                            <th>No</th>
                            <th>Tanggal Request</th>
                            <th>Customer</th>
                            <th>Paket</th>
                            <th>Jumlah Bayar</th>
                            <th>Tunggakan</th>
                            <th>Metode</th>
                            <th>Diajukan Oleh</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($paymentRequests ?? [] as $index => $request)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox"
                                        value="{{ $request->id }}" onchange="updateBulkActions()">
                                </td>
                                <td class="fw-medium">{{ $paymentRequests->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-medium text-dark">
                                        {{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y') }}
                                    </div>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($request->created_at)->format('H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium text-dark">
                                            {{ $request->invoice->customer->nama_customer ?? 'N/A' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ Str::limit($request->invoice->customer->alamat ?? '', 30) }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-primary border">
                                        {{ $request->invoice->paket->nama_paket ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        Rp {{ number_format($request->jumlah_bayar, 0, ',', '.') }}
                                    </div>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($request->tanggal_bayar)->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        Rp {{ number_format($request->invoice->tunggakan, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $methodClass = 'bg-secondary';
                                        $methodIcon = 'bx-credit-card';
                                        $text = 'text-dark';

                                        if (str_contains(strtolower($request->metode_bayar), 'cash')) {
                                            $methodClass = 'bg-success';
                                            $methodIcon = 'bx-money';
                                        } elseif (str_contains(strtolower($request->metode_bayar), 'transfer')) {
                                            $methodClass = 'bg-primary';
                                            $methodIcon = 'bx-transfer';
                                        } elseif (
                                            str_contains(strtolower($request->metode_bayar), 'dana') ||
                                            str_contains(strtolower($request->metode_bayar), 'ovo') ||
                                            str_contains(strtolower($request->metode_bayar), 'gopay')
                                        ) {
                                            $methodClass = 'bg-warning';
                                            $methodIcon = 'bx-wallet';
                                        }
                                    @endphp
                                    <span class="badge {{ $methodClass }} bg-opacity-10 {{ $text }} border">
                                        <i class="bx {{ $methodIcon }} me-1"></i>
                                        {{ $request->metode_bayar }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">
                                        {{ $request->user->name ?? 'System' }}
                                    </div>
                                    <small class="text-muted">Admin Keuangan</small>
                                </td>
                                <td>
                                    @php
                                        $statusClass = 'status-pending';
                                        $statusIcon = 'bx-time';
                                        $statusText = $request->status->nama_status ?? 'Pending';

                                        if ($request->status_id == 8) {
                                            $statusClass = 'status-confirmed';
                                            $statusIcon = 'bx-check-circle';
                                        } elseif ($request->status_id == 2) {
                                            $statusClass = 'status-processing';
                                            $statusIcon = 'bx-loader-circle';
                                        } elseif ($request->status_id == 10) {
                                            $statusClass = 'status-rejected';
                                            $statusIcon = 'bx-x-circle';
                                        }
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">
                                        <i class="bx {{ $statusIcon }} me-1"></i>
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-info action-btn"
                                            onclick="viewDetails({{ $request->id }})" title="Lihat Detail">
                                            <i class="bx bx-show"></i>
                                        </button>
                                        @if ($request->status_id != 8)
                                            <a href="/acc/{{ $request->id }}"
                                                class="btn btn-outline-success action-btn">
                                                <i class="bx bx-check"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger action-btn"
                                                onclick="rejectPayment({{ $request->id }})" title="Tolak">
                                                <i class="bx bx-x"></i>
                                            </button>
                                        @endif
                                        @if ($request->bukti_bayar)
                                            <button class="btn btn-sm btn-outline-warning action-btn"
                                                onclick="viewProof('{{ $request->bukti_bayar }}')" title="Lihat Bukti">
                                                <i class="bx bx-image"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="no-data">
                                    <i class="bx bx-inbox"></i>
                                    <h6 class="mt-2 mb-1">Tidak ada request konfirmasi</h6>
                                    <p class="mb-0">Belum ada request pembayaran yang perlu dikonfirmasi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if ($paymentRequests->hasPages())
            <div class="card-footer bg-light border-top p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Menampilkan {{ $paymentRequests->firstItem() }} sampai {{ $paymentRequests->lastItem() }}
                        dari {{ $paymentRequests->total() }} request
                    </div>
                    <div>
                        {{ $paymentRequests->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay d-none">
        <div class="loading-content">
            <div class="spinner"></div>
            <span>Memproses data...</span>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">
                        <i class="bx bx-info-circle me-2"></i>Detail Request Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Proof Modal -->
    <div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="proofModalLabel">
                        <i class="bx bx-image me-2"></i>Bukti Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" id="proofModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">
                        <i class="bx bx-check-circle me-2 text-success"></i>Konfirmasi Persetujuan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-center">Apakah Anda yakin ingin menyetujui request pembayaran ini?</p>
                    <div class="alert alert-info">
                        <small>
                            <i class="bx bx-info-circle me-1"></i>
                            Setelah disetujui, status pembayaran akan berubah menjadi "Sudah Bayar" dan tidak dapat
                            dibatalkan.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="confirmApproveBtn">
                        <i class="bx bx-check me-1"></i>Ya, Setujui
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="bx bx-x-circle me-2 text-danger"></i>Konfirmasi Penolakan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bx bx-x-circle text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-center">Apakah Anda yakin ingin menolak request pembayaran ini?</p>
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">Alasan Penolakan</label>
                        <textarea class="form-control" id="rejectReason" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <small>
                            <i class="bx bx-warning me-1"></i>
                            Request yang ditolak akan dikembalikan ke admin keuangan untuk diperbaiki.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectBtn">
                        <i class="bx bx-x me-1"></i>Ya, Tolak
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        let isLoading = false;
        let currentPaymentId = null;
        let selectedPayments = [];

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            startPolling();
        });

        // Initialize event listeners
        function initializeEventListeners() {
            // Search input with debounce
            let searchTimeout;
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('filterForm').submit();
                }, 500);
            });

            // Filter changes
            document.getElementById('statusFilter').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });

            document.getElementById('metodeFilter').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });

            document.getElementById('startDate').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });

            document.getElementById('endDate').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });

            // Select all checkbox
            document.getElementById('selectAllCheckbox').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
            });

            // Modal event listeners
            document.getElementById('confirmApproveBtn').addEventListener('click', function() {
                if (currentPaymentId) {
                    processApproval(currentPaymentId);
                }
            });

            document.getElementById('confirmRejectBtn').addEventListener('click', function() {
                if (currentPaymentId) {
                    const reason = document.getElementById('rejectReason').value;
                    processRejection(currentPaymentId, reason);
                }
            });
        }

        // Start polling for real-time updates
        function startPolling() {
            setInterval(() => {
                if (!isLoading) {
                    refreshDataSilently();
                }
            }, 30000); // Poll every 30 seconds
        }

        // Refresh data
        function refreshData() {
            showLoading();
            window.location.reload();
        }

        // Silent refresh without loading indicator
        function refreshDataSilently() {
            // Implementation for silent refresh would go here
            // This could use AJAX to update only the table content
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('metodeFilter').value = '';
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            document.getElementById('filterForm').submit();
        }

        // Export data
        function exportData() {
            showNotification('Fitur export akan segera tersedia', 'info');
        }

        // View payment details
        function viewDetails(paymentId) {
            showLoading();

            // Simulate API call - replace with actual implementation
            setTimeout(() => {
                hideLoading();

                const modalBody = document.getElementById('detailModalBody');
                modalBody.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-3">Informasi Customer</h6>
                                <div class="mb-2">
                                    <small class="text-muted">Nama Customer:</small>
                                    <div class="fw-medium">John Doe</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Alamat:</small>
                                    <div>Jl. Contoh No. 123, Jakarta</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Paket:</small>
                                    <div><span class="badge bg-info">Paket Premium</span></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-3">Informasi Pembayaran</h6>
                                <div class="mb-2">
                                    <small class="text-muted">Jumlah Bayar:</small>
                                    <div class="fw-bold text-success">Rp 150.000</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Tanggal Bayar:</small>
                                    <div>${new Date().toLocaleDateString('id-ID')}</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Metode Bayar:</small>
                                    <div><span class="badge bg-primary">Transfer Bank</span></div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Diajukan Oleh:</small>
                                    <div>Admin Keuangan</div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-semibold mb-3">Keterangan</h6>
                                <p class="text-muted">Pembayaran bulanan untuk periode ${new Date().toLocaleDateString('id-ID')}</p>
                            </div>
                        </div>
                    `;

                const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                modal.show();
            }, 1000);
        }

        // View payment proof
        function viewProof(proofPath) {
            const modalBody = document.getElementById('proofModalBody');

            if (proofPath.toLowerCase().includes('.pdf')) {
                modalBody.innerHTML = `
                        <div class="alert alert-info">
                            <i class="bx bx-file-pdf me-2"></i>File PDF
                        </div>
                        <a href="/storage/${proofPath}" target="_blank" class="btn btn-primary">
                            <i class="bx bx-download me-2"></i>Download PDF
                        </a>
                    `;
            } else {
                modalBody.innerHTML = `
                        <img src="/storage/${proofPath}" class="img-fluid rounded" alt="Bukti Pembayaran"
                             style="max-height: 500px;">
                    `;
            }

            const modal = new bootstrap.Modal(document.getElementById('proofModal'));
            modal.show();
        }

        // Approve payment
        function approvePayment(paymentId) {
            currentPaymentId = paymentId;
            const modal = new bootstrap.Modal(document.getElementById('approveModal'));
            modal.show();
        }

        // Reject payment
        function rejectPayment(paymentId) {
            currentPaymentId = paymentId;
            document.getElementById('rejectReason').value = '';
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        }

        // Process approval
        function processApproval(paymentId) {
            showLoading();

            // Simulate API call - replace with actual implementation
            setTimeout(() => {
                hideLoading();
                bootstrap.Modal.getInstance(document.getElementById('approveModal')).hide();
                showNotification('Pembayaran berhasil disetujui', 'success');
                refreshData();
            }, 2000);
        }

        // Process rejection
        function processRejection(paymentId, reason) {
            if (!reason.trim()) {
                showNotification('Alasan penolakan harus diisi', 'error');
                return;
            }

            showLoading();

            // Simulate API call - replace with actual implementation
            setTimeout(() => {
                hideLoading();
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                showNotification('Pembayaran berhasil ditolak', 'success');
                refreshData();
            }, 2000);
        }

        // Bulk actions
        function selectAll() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            selectAllCheckbox.checked = !selectAllCheckbox.checked;
            selectAllCheckbox.dispatchEvent(new Event('change'));
        }

        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const bulkBtn = document.getElementById('bulkApproveBtn');

            selectedPayments = Array.from(checkboxes).map(cb => cb.value);

            if (selectedPayments.length > 0) {
                bulkBtn.style.display = 'inline-block';
                bulkBtn.innerHTML = `<i class="bx bx-check me-1"></i>Setujui ${selectedPayments.length} Terpilih`;
            } else {
                bulkBtn.style.display = 'none';
            }
        }

        function bulkApprove() {
            if (selectedPayments.length === 0) {
                showNotification('Pilih minimal satu pembayaran untuk disetujui', 'warning');
                return;
            }

            if (confirm(`Apakah Anda yakin ingin menyetujui ${selectedPayments.length} pembayaran yang dipilih?`)) {
                showLoading();

                // Simulate bulk approval - replace with actual implementation
                setTimeout(() => {
                    hideLoading();
                    showNotification(`${selectedPayments.length} pembayaran berhasil disetujui`, 'success');
                    refreshData();
                }, 3000);
            }
        }

        // Utility functions
        function showLoading() {
            isLoading = true;
            document.getElementById('loadingOverlay').classList.remove('d-none');
        }

        function hideLoading() {
            isLoading = false;
            document.getElementById('loadingOverlay').classList.add('d-none');
        }

        function showNotification(message, type = 'info') {
            // Create toast notification
            const toastContainer = document.getElementById('toast-container') || createToastContainer();

            const toastId = 'toast-' + Date.now();
            const iconClass = {
                'success': 'bx-check-circle text-success',
                'error': 'bx-x-circle text-danger',
                'warning': 'bx-error-circle text-warning',
                'info': 'bx-info-circle text-info'
            } [type] || 'bx-info-circle text-info';

            const bgClass = {
                'success': 'bg-success',
                'error': 'bg-danger',
                'warning': 'bg-warning',
                'info': 'bg-info'
            } [type] || 'bg-info';

            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = 'toast align-items-center text-white border-0';
            toast.classList.add(bgClass);
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bx ${iconClass} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;

            toastContainer.appendChild(toast);

            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: 5000
            });
            bsToast.show();

            // Remove toast element after it's hidden
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }

        // Real-time updates simulation
        function simulateNewRequest() {
            // This would be called when new payment requests arrive
            showNotification('Request pembayaran baru diterima', 'info');

            // Update statistics
            const totalElement = document.querySelector('.confirmation-card:first-child h5');
            if (totalElement) {
                const current = parseInt(totalElement.textContent.replace(/\D/g, ''));
                totalElement.textContent = (current + 1).toLocaleString('id-ID');
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + R for refresh
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                refreshData();
            }

            // Escape to close modals
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal.show');
                openModals.forEach(modal => {
                    bootstrap.Modal.getInstance(modal)?.hide();
                });
            }
        });

        // Auto-save search state
        function saveSearchState() {
            const searchData = {
                search: document.getElementById('searchInput').value,
                status: document.getElementById('statusFilter').value,
                metode: document.getElementById('metodeFilter').value,
                startDate: document.getElementById('startDate').value,
                endDate: document.getElementById('endDate').value
            };
            localStorage.setItem('paymentConfirmationSearch', JSON.stringify(searchData));
        }

        function loadSearchState() {
            const saved = localStorage.getItem('paymentConfirmationSearch');
            if (saved) {
                const searchData = JSON.parse(saved);
                document.getElementById('searchInput').value = searchData.search || '';
                document.getElementById('statusFilter').value = searchData.status || '';
                document.getElementById('metodeFilter').value = searchData.metode || '';
                document.getElementById('startDate').value = searchData.startDate || '';
                document.getElementById('endDate').value = searchData.endDate || '';
            }
        }

        // Initialize search state on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSearchState();
        });

        // Save search state on form changes
        document.getElementById('filterForm').addEventListener('submit', function() {
            saveSearchState();
        });
    </script>
@endsection
