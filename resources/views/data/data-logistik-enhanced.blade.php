@extends('layouts.contentNavbarLayout')

@section('title', 'Data Logistik')

@section('styles')
    <style>
        /* Modern, clean design styles */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem 1.5rem 1rem;
        }

        .header-with-pattern {
            background-color: #ffffff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%233b82f6' fill-opacity='0.03'%3E%3Cpath opacity='.5' d='M96 95h4v1h-4v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9zm-1 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9z'/%3E%3Cpath d='M6 5V0H5v5H0v1h5v94h1V6h94V5H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            padding: 1.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h4 {
            font-weight: 600;
            color: #333;
            margin-bottom: 0;
            font-size: 1.25rem;
        }

        /* Header icon styling */
        .header-icon {
            width: 52px;
            height: 52px;
            background-color: rgba(59, 130, 246, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.1);
        }

        .header-content {
            transition: all 0.3s ease;
        }

        .header-content:hover .header-icon {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        /* Statistics summary styling */
        .stats-summary {
            padding-right: 1.5rem;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }

        .summary-item {
            padding: 0.25rem 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Search input styling */
        .search-container {
            min-width: 280px;
        }

        .search-container .input-group {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }

        .search-container .input-group:focus-within {
            box-shadow: 0 3px 12px rgba(59, 130, 246, 0.12);
        }

        .search-container .input-group-text {
            border-color: #e2e8f0;
            color: #94a3b8;
        }

        .form-control {
            border: 1px solid #e2e8f0;
            box-shadow: none;
            transition: all 0.2s ease;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: none;
        }

        .btn-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Enhanced Modern Table styling */
        .table-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            position: relative;
        }

        .table-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4, #10b981);
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            position: relative;
        }

        .table th {
            color: #ffffff;
            font-weight: 700;
            padding: 1.5rem 1.25rem;
            border: none;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            position: relative;
            white-space: nowrap;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .table th:first-child {
            border-top-left-radius: 16px;
        }

        .table th:last-child {
            border-top-right-radius: 16px;
        }

        .table tbody tr {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid #f1f5f9;
            position: relative;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            z-index: 1;
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .table td {
            padding: 1.5rem 1.25rem;
            vertical-align: middle;
            border: none;
            color: #334155;
            font-size: 0.95rem;
            position: relative;
        }

        .table tbody tr:nth-child(even) {
            background-color: rgba(248, 250, 252, 0.4);
        }

        .table-hover tbody tr {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .table-hover tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        /* Enhanced row styling */
        .device-row {
            position: relative;
        }

        .device-row::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: transparent;
            transition: all 0.3s ease;
        }

        .device-row:hover::before {
            background: linear-gradient(180deg, #3b82f6, #8b5cf6);
        }

        /* Device name styling */
        .device-name {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .device-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }

        .device-row:hover .device-icon {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        .device-info h6 {
            margin: 0;
            font-weight: 700;
            color: #1e293b;
            font-size: 1rem;
            line-height: 1.2;
        }

        .device-info small {
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* Row number styling */
        .row-number {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 3px 8px rgba(100, 116, 139, 0.3);
            transition: all 0.3s ease;
        }

        .device-row:hover .row-number {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.4);
        }

        /* Enhanced badges */
        .status-badge {
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.025em;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .status-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .status-badge.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .status-badge.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .status-badge.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .usage-badge {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 3px 8px rgba(139, 92, 246, 0.3);
            transition: all 0.3s ease;
        }

        .usage-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        }

        /* Price styling */
        .price-display {
            font-weight: 800;
            font-size: 1.1rem;
            color: #059669;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .price-main {
            font-size: 1.2rem;
            background: linear-gradient(135deg, #059669, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .price-label {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 2px;
        }

        /* Enhanced action buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            font-size: 1.1rem;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn.edit {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .action-btn.edit:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
            color: white;
        }

        .action-btn.delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .action-btn.delete:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
            color: white;
        }

        /* Breadcrumb styling */
        .breadcrumb {
            padding: 0.5rem 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .breadcrumb-item a:hover {
            color: #3b82f6;
        }

        .breadcrumb-item.active {
            color: #3b82f6;
            font-weight: 500;
        }

        /* No results message */
        #noResults td {
            padding: 2rem;
            color: #64748b;
            font-style: italic;
        }

        /* Badge styling */
        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.025em;
        }

        .badge.bg-label-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .badge.bg-label-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .badge.bg-label-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .header-with-pattern {
                padding: 1.25rem;
            }

            .header-content .d-flex {
                flex-direction: column;
                text-align: center;
            }

            .header-icon {
                margin-bottom: 0.75rem;
                margin-right: 0 !important;
            }

            .stats-summary {
                display: block !important;
                border-right: none;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                padding-bottom: 1rem;
                margin-bottom: 1rem;
                padding-right: 0;
            }

            .search-container {
                min-width: unset;
                width: 100%;
            }

            .d-flex.flex-column.flex-md-row {
                gap: 1rem !important;
            }

            .device-name {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .device-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .table th,
            .table td {
                padding: 1rem 0.75rem;
                font-size: 0.875rem;
            }

            .action-btn {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }

            .status-badge,
            .usage-badge {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }

            .price-display {
                font-size: 1rem;
            }

            .price-main {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 1rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }

            .action-btn {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }

            .header-with-pattern {
                padding: 1rem;
            }

            .summary-item {
                text-align: center;
            }

            .device-icon {
                width: 36px;
                height: 36px;
                font-size: 0.9rem;
            }

            .row-number {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }
        }

        /* Offcanvas responsive improvements */
        @media (max-width: 576px) {
            .offcanvas-start {
                width: 100% !important;
            }
        }

        /* Form improvements */
        .form-label {
            color: #374151;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.1);
        }

        .form-text {
            color: #6b7280;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .input-group-text {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #6b7280;
            font-weight: 500;
        }

        /* Loading state for buttons */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Smooth transitions */
        * {
            transition: all 0.2s ease;
        }

        /* Custom scrollbar for offcanvas */
        .offcanvas-body::-webkit-scrollbar {
            width: 6px;
        }

        .offcanvas-body::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .offcanvas-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .offcanvas-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">

            <nav class="breadcrumb-nav">
                <ul class="breadcrumb breadcrumb-transparent breadcrumb-style2 mb-3">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/data">Data</a></li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">Logistik</li>
                </ul>
            </nav>

            <div class="card">
                <div class="card-header header-with-pattern">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 py-2">
                        <div class="header-content">
                            <div class="d-flex align-items-center">
                                <div class="header-icon me-3 d-flex align-items-center justify-content-center">
                                    <i class='bx bx-package fs-3 text-primary'></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-semibold">Data Logistik</h4>
                                    <p class="text-muted mb-0 small">Manajemen stok perangkat dan inventori</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="stats-summary me-4 d-none d-lg-block">
                                <div class="d-flex gap-4">
                                    <div class="summary-item">
                                        <span class="text-muted small d-block">Total Stok</span>
                                        <span class="fw-semibold">{{ $perangkat->sum('stok_tersedia') }} Unit</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="text-muted small d-block">Total Nilai</span>
                                        <span class="fw-semibold text-primary">
                                            Rp {{ number_format($perangkat->sum('harga') ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="search-container">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class='bx bx-search text-muted'></i>
                                    </span>
                                    <input type="text" id="search" class="form-control border-start-0 ps-0"
                                        placeholder="Cari perangkat..." aria-label="Search" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasBoth">
                            <i class="bx bx-plus me-1"></i> Tambah Stok
                        </button>
                    </div>

                    <div class="table-responsive">
                        <div class="table-container">
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No.</th>
                                        <th width="35%">Nama Perangkat</th>
                                        <th width="20%">Stok Tersedia</th>
                                        <th width="20%">Terpakai</th>
                                        <th width="25%">Total Harga</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($perangkat) > 0)
                                        @foreach ($perangkat as $p)
                                            <tr class="device-row">
                                                <td>
                                                    <div class="row-number">{{ $loop->iteration }}</div>
                                                </td>
                                                <td>
                                                    <div class="device-name">
                                                        <div class="device-icon">
                                                            <i class='bx bx-chip'></i>
                                                        </div>
                                                        <div class="device-info">
                                                            <h6>{{ $p->nama_perangkat }}</h6>
                                                            <small>Perangkat Logistik</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="status-badge {{ $p->stok_tersedia > 10 ? 'success' : ($p->stok_tersedia > 5 ? 'warning' : 'danger') }}">
                                                        {{ $p->stok_tersedia }} Unit
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="usage-badge">
                                                        {{ $p->customer_count }} Unit
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="price-display">
                                                        <div class="price-main">Rp {{ number_format($p->harga, 0, ',', '.') }}</div>
                                                        <div class="price-label">Total Harga</div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="action-buttons">
                                                        <a href="" class="action-btn edit" data-bs-toggle="tooltip" title="Edit Stok" data-bs-placement="bottom">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <a href="" class="action-btn delete" data-bs-toggle="tooltip" title="Hapus Stok" data-bs-placement="bottom">
                                                            <i class="bx bx-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr id="noDataResults">
                                            <td colspan="6" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class='bx bx-package text-muted mb-2' style="font-size: 2rem;"></i>
                                                    <div>Tidak ada data perangkat ditemukan</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modern Offcanvas Modal --}}
    <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasBoth"
        aria-labelledby="offcanvasBothLabel">
        <div class="offcanvas-header"
            style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-bottom: none;">
            <div class="d-flex align-items-center">
                <div class="me-3 d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px; background-color: rgba(255, 255, 255, 0.2); border-radius: 8px;">
                    <i class='bx bx-plus text-white fs-5'></i>
                </div>
                <div>
                    <h5 id="offcanvasBothLabel" class="offcanvas-title text-white mb-0 fw-semibold">Tambah Stok Logistik
                    </h5>
                    <small class="text-white-50">Tambahkan perangkat baru ke inventori</small>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body" style="padding: 1.5rem;">
            <form action="/logistik/store" method="POST" id="addDeviceForm">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-medium" for="nama_perangkat">
                        <i class='bx bx-chip me-1 text-muted'></i>Nama Perangkat
                    </label>
                    <input type="text" class="form-control" id="nama_perangkat"
                        placeholder="Contoh: Router TP-Link AC1200" name="nama_perangkat" required>
                    <div class="form-text">Masukkan nama perangkat yang akan ditambahkan</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium" for="jumlah_stok">
                        <i class='bx bx-package me-1 text-muted'></i>Jumlah Stok
                    </label>
                    <input name="jumlah_stok" type="number" id="jumlah_stok" class="form-control" placeholder="100"
                        min="1" required>
                    <div class="form-text">Masukkan jumlah unit yang tersedia</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium" for="harga-satuan">
                        <i class='bx bx-money me-1 text-muted'></i>Harga Satuan
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input name="harga" type="text" id="harga-satuan" class="form-control"
                            placeholder="150.000" oninput="formatRupiah(this)" required>
                    </div>
                    <div class="form-text">Masukkan harga per unit perangkat</div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="offcanvas">
                        <i class='bx bx-x me-1'></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bx bx-plus me-1"></i>Tambah Stok
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('search');
            const dataTable = document.getElementById('dataTable');
            const tableRows = dataTable.querySelectorAll('tbody tr.device-row');

            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let resultsFound = false;

                // If search is empty, show all rows
                if (searchTerm === '') {
                    tableRows.forEach(row => {
                        row.style.display = '';
                    });

                    // Remove no results message if it exists
                    const noResultsRow = document.getElementById('noResults');
                    if (noResultsRow) {
                        noResultsRow.remove();
                    }
                    return;
                }

                // Filter rows based on search term
                tableRows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();

                    if (rowText.includes(searchTerm)) {
                        row.style.display = '';
                        resultsFound = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Add no results message if needed
                if (!resultsFound) {
                    if (!document.getElementById('noResults')) {
                        const tbody = dataTable.querySelector('tbody');
                        const noResultsRow = document.createElement('tr');
                        noResultsRow.id = 'noResults';
                        noResultsRow.innerHTML = `
                            <td colspan="6" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-search text-muted mb-2' style="font-size: 2rem;"></i>
                                    <div>Tidak ada perangkat yang cocok dengan pencarian</div>
                                    <small class="text-muted mt-1">Coba dengan kata kunci lain</small>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(noResultsRow);
                    }
                } else {
                    const noResultsRow = document.getElementById('noResults');
                    if (noResultsRow) {
                        noResultsRow.remove();
                    }
                }
            }

            // Event listeners for search
            searchInput.addEventListener('keyup', function(e) {
                performSearch();
            });

            searchInput.addEventListener('input', function() {
                performSearch();
            });

            // Format Rupiah function
            window.formatRupiah = function(input) {
                let value = input.value.replace(/[^\d]/g, '');
                if (value !== '') {
                    value = parseInt(value);
                    value = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    input.value = value;
                }
            };

            // Form validation
            const addDeviceForm = document.getElementById('addDeviceForm');
            if (addDeviceForm) {
                addDeviceForm.addEventListener('submit', function(e) {
                    const namaPerangkat = document.getElementById('nama_perangkat').value.trim();
                    const jumlahStok = document.getElementById('jumlah_stok').value;
                    const harga = document.getElementById('harga-satuan').value.trim();

                    if (!namaPerangkat || !jumlahStok || !harga) {
                        e.preventDefault();
                        alert('Mohon lengkapi semua field yang diperlukan');
                        return false;
                    }

                    if (parseInt(jumlahStok) < 1) {
                        e.preventDefault();
                        alert('Jumlah stok harus minimal 1');
                        return false;
                    }
                });
            }

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection