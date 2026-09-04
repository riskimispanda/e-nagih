@extends('layouts.contentNavbarLayout')
@section('title', 'Data ODC')
@section('vendor-style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #mapOdc {
            height: 500px;
            width: 100%;
            border-radius: 0 0 0.75rem 0.75rem;
        }

        .leaflet-container {
            z-index: 1;
            border-radius: 0 0 0.75rem 0.75rem;
        }

        /* ====== PAGE HERO ====== */
        .odc-hero {
            position: relative;
            border-radius: 1rem;
            padding: 1.75rem 1.75rem;
            color: #fff;
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);
            overflow: hidden;
            box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.45);
        }
        .odc-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15), transparent 40%),
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1), transparent 40%);
            pointer-events: none;
        }
        .odc-hero::after {
            content: "";
            position: absolute;
            right: -60px;
            top: -60px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }
        .odc-hero .hero-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            border: 1px solid rgba(255, 255, 255, 0.25);
            position: relative;
            z-index: 1;
        }
        .odc-hero h4 {
            color: #fff;
            position: relative;
            z-index: 1;
        }
        .odc-hero small {
            color: rgba(255, 255, 255, 0.85) !important;
            position: relative;
            z-index: 1;
        }
        .odc-hero .btn {
            position: relative;
            z-index: 1;
            border-radius: 0.6rem;
            padding: 0.55rem 1.1rem;
            font-weight: 500;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }
        .odc-hero .btn-hero-light {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #fff;
            backdrop-filter: blur(6px);
        }
        .odc-hero .btn-hero-light:hover {
            background: rgba(255, 255, 255, 0.28);
            color: #fff;
        }
        .odc-hero .btn-hero-primary {
            background: #fff;
            color: #4f46e5;
            border: 1px solid #fff;
        }
        .odc-hero .btn-hero-primary:hover {
            background: #f8fafc;
            color: #4338ca;
        }

        /* ====== STATS CARDS ====== */
        .stat-card {
            border: none;
            border-radius: 0.9rem;
            background: #fff;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
            transition: all 0.25s ease;
            overflow: hidden;
            position: relative;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        .stat-card .stat-label {
            color: #64748b;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }
        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }
        .stat-card .stat-meta {
            font-size: 0.75rem;
            color: #94a3b8;
        }
        .stat-card::after {
            content: "";
            position: absolute;
            right: -20px;
            bottom: -20px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            opacity: 0.05;
            background: currentColor;
        }
        .stat-icon-primary { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
        .stat-icon-warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
        .stat-icon-info    { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
        .stat-icon-success { background: rgba(16, 185, 129, 0.12); color: #10b981; }

        /* ====== MAIN TABLE CARD ====== */
        .odc-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 22px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }
        .odc-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.1rem 1.5rem;
        }
        .odc-card .card-header h5 {
            font-weight: 700;
            color: #0f172a;
        }
        .search-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.6rem;
            transition: all 0.2s;
        }
        .search-box:focus-within {
            background: #fff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }
        .search-box .input-group-text {
            background: transparent;
            border: none;
            color: #94a3b8;
            padding-left: 0.9rem;
        }
        .search-box .form-control {
            background: transparent;
            border: none;
            box-shadow: none !important;
            padding-left: 0.25rem;
            color: #0f172a;
        }
        .search-box .form-control::placeholder {
            color: #94a3b8;
        }
        .search-box .form-control:focus {
            border: none !important;
        }
        .perpage-select {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 0.6rem;
            color: #475569;
            font-weight: 500;
            padding: 0.45rem 2rem 0.45rem 0.9rem;
            transition: all 0.2s;
        }
        .perpage-select:focus {
            background: #fff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        /* ====== TABLE ====== */
        .table-odc {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-odc thead th {
            background: #f8fafc;
            color: #475569;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 700;
            white-space: nowrap;
            padding: 0.95rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-odc tbody td {
            vertical-align: middle;
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .table-odc tbody tr {
            transition: background-color .18s ease, transform .18s ease;
        }
        .table-odc tbody tr:hover {
            background-color: #f8fafc;
        }
        .table-odc tbody tr:last-child td {
            border-bottom: none;
        }
        .odc-name {
            font-weight: 600;
            color: #0f172a;
            line-height: 1.2;
        }
        .odc-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(99, 102, 241, 0.12));
            color: #4f46e5;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.32rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }
        .pill-warning { background: rgba(245, 158, 11, 0.12); color: #b45309; }
        .pill-info    { background: rgba(99, 102, 241, 0.12); color: #4f46e5; }
        .pill-secondary { background: rgba(148, 163, 184, 0.15); color: #475569; }
        .pill-success { background: rgba(16, 185, 129, 0.12); color: #047857; }
        .pill-danger  { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }

        .icon-action {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            transition: all 0.2s;
        }
        .icon-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
        }
        .icon-action.btn-outline-primary,
        .icon-action.btn-outline-success {
            color: #4f46e5;
        }
        .icon-action.btn-outline-primary:hover,
        .icon-action.btn-outline-success:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.4);
            color: #4338ca;
        }
        .icon-action.btn-outline-warning { color: #d97706; }
        .icon-action.btn-outline-warning:hover {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.4);
            color: #b45309;
        }
        .icon-action.btn-outline-danger { color: #dc2626; }
        .icon-action.btn-outline-danger:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.4);
            color: #b91c1c;
        }

        .action-group {
            display: inline-flex;
            gap: 0.4rem;
            align-items: center;
        }

        /* ====== EMPTY STATE ====== */
        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
        }
        .empty-state .empty-illust {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #6366f1;
            margin-bottom: 1rem;
        }

        /* ====== MODAL ====== */
        .modal-content {
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
        }
        .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 1.1rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        }
        .modal-header .modal-title {
            font-weight: 700;
            color: #0f172a;
        }
        .modal-header .modal-title-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .modal-header .modal-title-avatar.bg-label-primary { background: rgba(99, 102, 241, 0.12); color: #4f46e5; }
        .modal-header .modal-title-avatar.bg-label-warning { background: rgba(245, 158, 11, 0.12); color: #b45309; }
        .modal-header .modal-title-avatar.bg-label-info    { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
        .modal-body {
            padding: 1.5rem;
        }
        .modal-footer {
            border-top: 1px solid #f1f5f9;
            background: #f8fafc;
            padding: 0.85rem 1.25rem;
            gap: 0.5rem;
        }
        .modal-footer .btn {
            border-radius: 0.5rem;
            padding: 0.4rem 0.9rem;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .modal-footer .btn i {
            font-size: 1rem;
        }
        .modal-body .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
        }
        .modal-body .form-control,
        .modal-body .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 0.55rem;
            padding: 0.55rem 0.85rem;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            background: #fff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }
        .modal-body hr {
            margin: 1.5rem 0;
            border-color: #f1f5f9;
            opacity: 1;
        }

        /* ====== SPLITTER MODAL (Kelola Splitter) ====== */
        /* Banner info ODC di atas */
        .splitter-info-banner {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.9rem 1rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(139, 92, 246, 0.08));
            border: 1px solid rgba(99, 102, 241, 0.15);
            border-radius: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .splitter-info-banner .banner-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .splitter-info-banner .banner-text {
            line-height: 1.2;
            min-width: 0;
        }
        .splitter-info-banner .banner-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6366f1;
            font-weight: 700;
        }
        .splitter-info-banner .banner-value {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }

        /* Section header untuk area di dalam modal */
        .splitter-section {
            background: #fafbff;
            border: 1px solid #eef2ff;
            border-radius: 0.85rem;
            padding: 1rem 1.1rem 1.1rem;
            margin-bottom: 1rem;
        }
        .splitter-section-title {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: #4f46e5;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.85rem;
        }
        .splitter-section-title i {
            color: #6366f1;
            font-size: 1.05rem;
        }
        .splitter-section-title .section-pill {
            margin-left: auto;
            background: rgba(99, 102, 241, 0.12);
            color: #4f46e5;
            padding: 0.25rem 0.7rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Tabel di dalam section */
        .splitter-inner-table {
            margin: 0;
        }
        .splitter-inner-table thead th {
            background: #f1f5f9;
            color: #475569;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            padding: 0.65rem 0.85rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .splitter-inner-table tbody td {
            padding: 0.7rem 0.85rem;
            border-top: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }
        .splitter-inner-table tbody tr {
            transition: background 0.15s;
        }
        .splitter-inner-table tbody tr:hover {
            background: #f8fafc;
        }
        .splitter-inner-table code {
            background: rgba(99, 102, 241, 0.08);
            color: #4f46e5;
            padding: 0.2rem 0.5rem;
            border-radius: 0.35rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .splitter-inner-table .row-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 700;
            font-size: 0.75rem;
        }
        .splitter-inner-table .row-action {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #fecaca;
            color: #dc2626;
            transition: all 0.2s;
        }
        .splitter-inner-table .row-action:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: #fca5a5;
            color: #b91c1c;
            transform: translateY(-1px);
        }
        .splitter-inner-table .port-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: rgba(16, 185, 129, 0.1);
            color: #047857;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
        }
        .splitter-inner-table .rasio-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: rgba(99, 102, 241, 0.12);
            color: #4f46e5;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
        }

        /* Tabel wrapper rounded */
        .splitter-table-wrap {
            border-radius: 0.6rem;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        /* Empty state di dalam tabel */
        .splitter-empty {
            padding: 1.75rem 1rem;
            text-align: center;
            color: #94a3b8;
        }
        .splitter-empty i {
            font-size: 2.2rem;
            color: #cbd5e1;
            display: block;
            margin-bottom: 0.4rem;
        }

        /* Form tambah splitter - styling untuk select & input group */
        .splitter-rasio-select-wrap {
            position: relative;
        }
        .splitter-rasio-select-wrap::after {
            content: "\e9b3";
            font-family: 'boxicons' !important;
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6366f1;
            pointer-events: none;
            font-size: 1rem;
        }
        #splitterRasio {
            background: #fff !important;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.6rem;
            padding: 0.7rem 2.5rem 0.7rem 0.9rem;
            font-weight: 600;
            color: #0f172a;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: none !important;
        }
        #splitterRasio:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        /* Styling untuk input-group serial number yang di-generate JS */
        #splitterSerialInputs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }
        #splitterSerialInputs .col-md-6,
        #splitterSerialInputs .col-lg-4,
        #splitterSerialInputs .col-12 {
            padding: 0;
            flex: 1 1 30%;
            min-width: 180px;
        }
        #splitterSerialInputs .input-group {
            border-radius: 0.55rem;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            transition: all 0.2s;
        }
        #splitterSerialInputs .input-group:focus-within {
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
        }
        #splitterSerialInputs .input-group-text {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            font-weight: 700;
            border: none;
            font-size: 0.8rem;
            min-width: 46px;
            justify-content: center;
        }
        #splitterSerialInputs .form-control {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: none;
            border-radius: 0 0.55rem 0.55rem 0;
            font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            padding: 0.55rem 0.85rem;
        }
        #splitterSerialInputs .form-control:focus {
            background: #fff;
            border-color: #6366f1;
            box-shadow: none;
        }
        .serial-hint {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 0.6rem;
        }
        .serial-hint i {
            color: #6366f1;
        }

        /* ====== BREADCRUMB ====== */
        .breadcrumb-item + .breadcrumb-item::before {
            color: #cbd5e1;
        }

        /* ====== PAGINATION ====== */
        .pagination {
            gap: 0.25rem;
        }
        .pagination .page-link {
            border: 1px solid #e2e8f0;
            color: #475569;
            border-radius: 0.5rem !important;
            padding: 0.45rem 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .pagination .page-link:hover {
            background: #eef2ff;
            color: #4f46e5;
            border-color: #c7d2fe;
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }
        .pagination .page-item.disabled .page-link {
            background: #f8fafc;
            color: #cbd5e1;
        }

        /* responsive small screens */
        @media (max-width: 576px) {
            .odc-hero {
                padding: 1.25rem;
            }
            .odc-hero .hero-icon {
                width: 52px;
                height: 52px;
                font-size: 1.4rem;
            }
        }
    </style>
@endsection
@section('vendor-script')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection
@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}"><i class="bx bx-home-alt me-1"></i>Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="javascript:void(0)">NOC</a>
            </li>
            <li class="breadcrumb-item active">Data ODC</li>
        </ol>
    </nav>

    @php
        $totalOdc   = $odc->total();
        $totalOdp   = $odc->getCollection()->sum('odp_count');
        $totalSpl   = $odc->getCollection()->sum('splitter_count');
        $totalPort  = $odc->getCollection()->sum('splitter_capacity');
    @endphp

    {{-- HERO HEADER --}}
    <div class="odc-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 position-relative" style="z-index: 1;">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon">
                    <i class="bx bx-terminal"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1">Data ODC</h4>
                    <small>Kelola dan pantau lokasi Optical Distribution Cabinet</small>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-hero-light" id="btnOpenMap">
                    <i class="bx bx-map-alt me-1"></i>Peta ODC
                </button>
                <button type="button" class="btn btn-hero-primary" data-bs-toggle="modal" data-bs-target="#modalTambahOdc">
                    <i class="bx bx-plus me-1"></i>Tambah ODC
                </button>
            </div>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card text-primary">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon stat-icon-primary">
                        <i class="bx bx-terminal"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total ODC</div>
                        <div class="stat-value">{{ number_format($totalOdc) }}</div>
                        <div class="stat-meta">Lokasi terdaftar</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card text-warning">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon stat-icon-warning">
                        <i class="bx bx-package"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total ODP</div>
                        <div class="stat-value">{{ number_format($totalOdp) }}</div>
                        <div class="stat-meta">Distribusi point</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card" style="color:#6366f1;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon stat-icon-info">
                        <i class="bx bx-git-branch"></i>
                    </div>
                    <div>
                        <div class="stat-label">Splitter</div>
                        <div class="stat-value">{{ number_format($totalSpl) }}</div>
                        <div class="stat-meta">Unit terpasang</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card text-success">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon stat-icon-success">
                        <i class="bx bx-network-chart"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Port</div>
                        <div class="stat-value">{{ number_format($totalPort) }}</div>
                        <div class="stat-meta">Kapasitas splitter</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card odc-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3 py-3">
                    <div>
                        <h5 class="card-title mb-0">Daftar ODC</h5>
                        <small class="text-muted">{{ $odc->total() }} lokasi ODC terdaftar</small>
                    </div>
                    <form action="{{ route('odc') }}" method="GET"
                        class="d-flex flex-wrap align-items-center gap-2">
                        <div class="input-group search-box" style="max-width: 340px;">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" name="search"
                                placeholder="Cari nama ODC / OLT..." value="{{ request('search') }}">
                            @if (request('search'))
                                <a href="{{ route('odc') }}" class="btn btn-outline-secondary" title="Reset pencarian">
                                    <i class="bx bx-x"></i>
                                </a>
                            @else
                                <button class="btn btn-primary" type="submit" title="Cari">
                                    <i class="bx bx-search"></i>
                                </button>
                            @endif
                        </div>
                        <select name="per_page" class="form-select perpage-select w-auto" onchange="this.form.submit()"
                            title="Data per halaman">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / hal</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / hal</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / hal</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 / hal</option>
                        </select>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-odc mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 70px;">No</th>
                                <th>Data ODC</th>
                                <th>Lokasi ODC</th>
                                <th class="text-center">Total ODP</th>
                                <th class="text-center">Splitter</th>
                                <th class="text-center pe-4" style="width: 200px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($odc as $od)
                                <tr>
                                    <td class="ps-4 text-muted fw-semibold">{{ $loop->iteration + ($odc->currentPage() - 1) * $odc->perPage() }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="odc-avatar">
                                                <i class="bx bx-terminal"></i>
                                            </div>
                                            <div>
                                                <div class="odc-name">{{ $od->nama_odc }}</div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                                                    <small class="text-muted d-inline-flex align-items-center gap-1">
                                                        <i class="bx bx-server"></i>
                                                        {{ $od->olt->nama_lokasi ?? '-' }}
                                                    </small>
                                                    @if ($od->redaman)
                                                        <span class="badge bg-label-danger py-0 px-2 font-monospace" style="font-size: 10.5px;">
                                                            <i class="bx bx-broadcast me-1"></i>{{ Str::contains(strtolower($od->redaman), 'db') ? $od->redaman : $od->redaman . ' dBm' }}
                                                        </span>
                                                    @endif
                                                    @if ($od->modemDetail)
                                                        <span class="badge bg-label-info py-0 px-2 font-monospace" style="font-size: 10.5px;" title="Box ODC: {{ $od->modemDetail->perangkat->nama_perangkat ?? '' }}">
                                                            <i class="bx bx-barcode me-1"></i>SN: {{ $od->modemDetail->serial_number ?: ('ID-' . $od->modemDetail->id) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    @php
                                        $gps = $od->gps;
                                        $isLink = $gps && Str::startsWith($gps, ['http://', 'https://']);
                                        $url = $gps ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                                    @endphp
                                    <td>
                                        <div class="action-group">
                                            <a href="javascript:void(0)"
                                                class="icon-action view-map-btn"
                                                data-gps="{{ $od->gps }}" data-nama="{{ $od->nama_odc }}"
                                                data-bs-toggle="tooltip" title="{{ $od->gps ? 'Lihat di Peta' : 'Belum ada koordinat' }}"
                                                data-bs-placement="bottom">
                                                <i class="bx bx-map"></i>
                                            </a>
                                            <a href="{{ $url }}" {{ $url != '#' ? 'target=_blank' : '' }}
                                                class="icon-action {{ $url == '#' ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ $url == '#' ? 'Belum ada koordinat' : 'Buka Google Maps' }}"
                                                data-bs-placement="bottom">
                                                <i class="bx bxl-google"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="pill pill-warning">
                                            <i class="bx bx-package"></i>{{ $od->odp_count }} ODP
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($od->splitter_count > 0)
                                            <span class="pill pill-info">
                                                <i class="bx bx-git-branch"></i>{{ $od->splitter_count }} unit
                                            </span>
                                        @elseif ($od->splitter_rencana)
                                            <span class="pill pill-secondary">
                                                Belum terpasang · {{ $od->splitter_rencana }}
                                            </span>
                                        @else
                                            <span class="pill pill-secondary">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="action-group">
                                            <button type="button"
                                                class="icon-action btn-outline-primary kelola-splitter-btn"
                                                data-id="{{ $od->id }}" data-nama="{{ $od->nama_odc }}"
                                                data-splitters='{{ json_encode($od->splitter_list->map(fn($s) => ['id' => $s->id, 'serial' => $s->serial_number, 'rasio' => $s->rasio])) }}'
                                                data-bs-toggle="tooltip" title="Kelola Splitter" data-bs-placement="bottom">
                                                <i class="bx bx-git-branch"></i>
                                            </button>
                                            <a href="#" class="icon-action btn-outline-warning edit-odc-btn"
                                                data-id="{{ $od->id }}" data-bs-toggle="tooltip" title="Edit ODC"
                                                data-bs-placement="bottom">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <a href="/hapus/odc/{{ $od->id }}"
                                                class="icon-action btn-outline-danger" data-bs-toggle="tooltip"
                                                title="Hapus ODC" data-bs-placement="bottom">
                                                <i class="bx bx-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-illust">
                                                <i class="bx bx-search-alt"></i>
                                            </div>
                                            <h6 class="mb-1 fw-bold">Tidak ada data ditemukan</h6>
                                            <p class="text-muted mb-0">Tidak ada data yang cocok dengan pencarian Anda.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($odc->total() > 0)
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-3 border-top">
                        <small class="text-muted">
                            Menampilkan <strong>{{ $odc->firstItem() }}</strong>–<strong>{{ $odc->lastItem() }}</strong> dari
                            <strong>{{ $odc->total() }}</strong> data
                        </small>
                        {{ $odc->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Tambah ODC --}}
    <div class="modal fade" id="modalTambahOdc" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="modal-title-avatar bg-label-primary">
                            <i class="bx bxs-terminal"></i>
                        </span>
                        <h5 class="modal-title mb-0">Tambah ODC</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/odc/add" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Lokasi OLT <span class="text-danger">*</span></label>
                                <select name="olt" class="form-select">
                                    <option value="" selected disabled>Pilih Lokasi OLT</option>
                                    @foreach ($lokasi as $ol)
                                        <option value="{{ $ol->id }}">{{ $ol->nama_lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nama ODC <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_odc" placeholder="ODC Dondong 2" required />
                            </div>
                            <div class="col-12">
                                <label class="form-label">Rasio Splitter</label>
                                <select name="rasio" class="form-select">
                                    <option value="" selected disabled>Pilih Rasio</option>
                                    <option value="1:4">1:4 (4 port)</option>
                                    <option value="1:8">1:8 (8 port)</option>
                                    <option value="1:16">1:16 (16 port)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Lokasi ODC (GPS)</label>
                                <input type="text" class="form-control" name="gps"
                                    placeholder="https://maps.google.com/... atau -1.0269916,110.48579129">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Redaman (dBm)</label>
                                <input type="text" class="form-control" name="redaman" placeholder="Contoh: -16.5" />
                            </div>
                            <div class="col-12">
                                <div class="p-3 bg-lighter rounded border">
                                    <label class="form-label fw-bold text-primary mb-1"><i class="bx bx-package me-1"></i>Integrasi Logistik (Box ODC)</label>
                                    <select id="modal_odc_perangkat_id" class="form-select mb-2" onchange="onOdcModalPerangkatChange(this, 'modal_odc_sn')">
                                        <option value="">-- Pilih Box ODC dari Logistik (Opsional) --</option>
                                        @foreach ($perangkatOdc ?? [] as $podc)
                                            <option value="{{ $podc->id }}">📦 {{ $podc->nama_perangkat }} (Tersedia: {{ $podc->stok_tersedia }} unit)</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label mb-1">Serial Number (SN)</label>
                                    <select name="modem_detail_id" id="modal_odc_sn" class="form-select font-monospace">
                                        <option value="">-- Tanpa SN / Pilih Nanti --</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">Memilih SN otomatis memotong stok di Logistik.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bx bx-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit ODC --}}
    <div class="modal fade" id="modalEditOdc" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="modal-title-avatar bg-label-warning">
                            <i class="bx bxs-terminal"></i>
                        </span>
                        <h5 class="modal-title mb-0">Edit ODC</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOltForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nama ODC <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_odc" id="edit_nama_lokasi" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Rasio Splitter</label>
                                <select name="rasio" id="edit_rasio_odc" class="form-select">
                                    <option value="" disabled>Pilih Rasio</option>
                                    <option value="1:4">1:4 (4 port)</option>
                                    <option value="1:8">1:8 (8 port)</option>
                                    <option value="1:16">1:16 (16 port)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Lokasi OLT <span class="text-danger">*</span></label>
                                <select name="olt" id="edit_id_server" class="form-select" required>
                                    <option value="" selected disabled>Pilih OLT</option>
                                    @foreach ($lokasi as $o)
                                        <option value="{{ $o->id }}">{{ $o->nama_lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Lokasi ODC (GPS)</label>
                                <input type="text" name="gps"
                                    placeholder="https://maps.google.com/... atau -1.0269916,110.48579129"
                                    class="form-control" id="edit_gps">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Redaman (dBm)</label>
                                <input type="text" name="redaman" placeholder="Contoh: -16.5"
                                    class="form-control" id="edit_redaman_odc">
                            </div>
                            <div class="col-12">
                                <div class="p-3 bg-lighter rounded border">
                                    <label class="form-label fw-bold text-primary mb-1"><i class="bx bx-package me-1"></i>Integrasi Logistik (Box ODC)</label>
                                    <select id="edit_odc_perangkat_id" class="form-select mb-2" onchange="onOdcModalPerangkatChange(this, 'edit_odc_sn')">
                                        <option value="">-- Pilih Box ODC dari Logistik (Opsional) --</option>
                                        @foreach ($perangkatOdc ?? [] as $podc)
                                            <option value="{{ $podc->id }}">📦 {{ $podc->nama_perangkat }} (Tersedia: {{ $podc->stok_tersedia }} unit)</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label mb-1">Serial Number (SN)</label>
                                    <select name="modem_detail_id" id="edit_odc_sn" class="form-select font-monospace">
                                        <option value="">-- Tanpa SN / Pilih Nanti --</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">Memilih SN otomatis memotong stok di Logistik.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Kelola Splitter ODC --}}
    <div class="modal fade" id="modalKelolaSplitter" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="modal-title-avatar bg-label-primary">
                            <i class="bx bx-git-branch"></i>
                        </span>
                        <div>
                            <h5 class="modal-title mb-0">Kelola Splitter</h5>
                            <small class="text-muted d-block" style="font-size: 0.75rem; font-weight: 400;">
                                Daftar & tambah splitter pada ODC
                            </small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Info banner ODC --}}
                    <div class="splitter-info-banner">
                        <div class="banner-icon">
                            <i class="bx bx-terminal"></i>
                        </div>
                        <div class="banner-text flex-grow-1">
                            <div class="banner-label">ODC Aktif</div>
                            <div class="banner-value" id="splitterLokasiNama">-</div>
                        </div>
                    </div>

                    {{-- Section: Daftar Splitter --}}
                    <div class="splitter-section">
                        <div class="splitter-section-title">
                            <i class="bx bx-list-ul"></i>
                            <span>Daftar Splitter Terpasang</span>
                            <span class="section-pill" id="splitterTotal">0 unit</span>
                        </div>
                        <div class="splitter-table-wrap">
                            <table class="table table-hover table-sm align-middle splitter-inner-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 60px;">No</th>
                                        <th>Serial Number</th>
                                        <th class="text-center">Rasio</th>
                                        <th class="text-center">Kapasitas</th>
                                        <th class="text-center" style="width: 70px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="splitterListBody">
                                    <tr>
                                        <td colspan="5" class="splitter-empty">
                                            <i class="bx bx-package"></i>
                                            <div>Belum ada splitter terpasang</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Section: Tambah Splitter --}}
                    <div class="splitter-section">
                        <div class="splitter-section-title">
                            <i class="bx bx-plus-circle"></i>
                            <span>Tambah Splitter Baru</span>
                        </div>
                        <form id="tambahSplitterForm" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Splitter dari Stok Logistik <span class="text-danger">*</span></label>
                                    <select name="logistik_id" id="splitterLogistikId" class="form-select" required>
                                        <option value="" selected disabled>Pilih Perangkat Splitter dari Logistik</option>
                                        @foreach ($perangkatSplitter as $ps)
                                            <option value="{{ $ps->id }}" {{ $ps->stok_tersedia < 1 ? 'disabled' : '' }}>
                                                {{ $ps->nama_perangkat }} (Tersedia: {{ $ps->stok_tersedia }} {{ $ps->kategori->nama_logistik ?? 'Unit' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Rasio Splitter <span class="text-danger">*</span></label>
                                    <div class="splitter-rasio-select-wrap">
                                        <select name="rasio" id="splitterRasio" class="form-select" required>
                                            <option value="" selected disabled>Pilih Rasio</option>
                                            <option value="1:4">1:4 (4 port)</option>
                                            <option value="1:8">1:8 (8 port)</option>
                                            <option value="1:16">1:16 (16 port)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Serial Number Per Port</label>
                                    <div id="splitterSerialInputs">
                                        <div class="col-12">
                                            <div class="serial-hint">
                                                <i class="bx bx-info-circle"></i>
                                                <span>Pilih rasio untuk menampilkan field serial per port</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Tutup
                    </button>
                    <button type="submit" form="tambahSplitterForm" class="btn btn-sm btn-primary">
                        <i class="bx bx-save me-1"></i>Simpan Splitter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Peta ODC --}}
    <div class="modal fade" id="modalMapOdc" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="modal-title-avatar bg-label-info">
                            <i class="bx bx-map-alt"></i>
                        </span>
                        <h5 class="modal-title mb-0">Peta Lokasi ODC</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="mapOdc"></div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted me-auto">* Klik marker untuk melihat info ODC</small>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const perangkatOdcData = @json($perangkatOdc ?? []);

        function onOdcModalPerangkatChange(selectEl, targetSnSelectId, preselectedMdId = null, preselectedSn = '') {
            const snSelect = document.getElementById(targetSnSelectId);
            if (!snSelect) return;
            const devId = selectEl.value;
            const dev = (perangkatOdcData || []).find(d => String(d.id) === String(devId));

            let options = '<option value="">-- Tanpa SN / Pilih Unit Nanti --</option>';
            if (dev && dev.available_serials && dev.available_serials.length > 0) {
                let found = false;
                dev.available_serials.forEach(s => {
                    const isSel = (preselectedMdId && String(s.id) === String(preselectedMdId)) ? 'selected' : '';
                    if (isSel) found = true;
                    options += `<option value="${s.id}" ${isSel}>🔹 SN: ${s.serial_number}${s.mac_address ? ` (${s.mac_address})` : ''}</option>`;
                });
                if (preselectedMdId && !found && preselectedSn) {
                    options += `<option value="${preselectedMdId}" selected>🔹 SN: ${preselectedSn} (Unit Saat Ini)</option>`;
                }
            } else if (preselectedMdId && preselectedSn) {
                options += `<option value="${preselectedMdId}" selected>🔹 SN: ${preselectedSn} (Unit Saat Ini)</option>`;
            } else if (dev) {
                options = '<option value="">⚠️ Stok fisik berseri belum tersedia</option>';
            }
            snSelect.innerHTML = options;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Edit ODC Logic
            document.querySelectorAll('.edit-odc-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    fetch(`/edit/odc/${id}`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('edit_nama_lokasi').value = data.nama_odc || '';
                            document.getElementById('edit_id_server').value = data.lokasi_id || '';
                            document.getElementById('edit_gps').value = data.gps || 'Lokasi belum di atur';
                            document.getElementById('edit_rasio_odc').value = data.rasio || '';
                            document.getElementById('edit_redaman_odc').value = data.redaman || '';
                            document.getElementById('editOltForm').action = `/update/odc/${id}`;

                            const pId = data.modem_detail?.logistik_id || '';
                            const mdId = data.modem_detail_id || '';
                            const snText = data.modem_detail?.serial_number || '';
                            const editPSelect = document.getElementById('edit_odc_perangkat_id');
                            if (editPSelect) {
                                editPSelect.value = pId;
                                onOdcModalPerangkatChange(editPSelect, 'edit_odc_sn', mdId, snText);
                            }

                            var modal = new bootstrap.Modal(document.getElementById('modalEditOdc'));
                            modal.show();
                        });
                });
            });

            // Splitter Logic
            const modalSplitterElement = document.getElementById('modalKelolaSplitter');
            const modalSplitter = modalSplitterElement ? new bootstrap.Modal(modalSplitterElement) : null;

            function portCount(rasio) {
                return rasio ? parseInt(rasio.split(':')[1], 10) : 0;
            }

            function buildSplitterRows(splitters) {
                const tbody = document.getElementById('splitterListBody');
                const total = document.getElementById('splitterTotal');
                if (!tbody) return;
                if (!splitters || splitters.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="5" class="splitter-empty">
                        <i class="bx bx-package"></i>
                        <div>Belum ada splitter terpasang</div>
                    </td></tr>`;
                    if (total) total.textContent = '0 unit';
                    return;
                }
                if (total) total.textContent = splitters.length + ' unit';
                tbody.innerHTML = splitters.map(function (s, i) {
                    const port = portCount(s.rasio);
                    return `<tr>
                        <td class="text-center"><span class="row-num">${i + 1}</span></td>
                        <td><code>${s.serial || '-'}</code></td>
                        <td class="text-center"><span class="rasio-pill"><i class="bx bx-git-branch"></i>${s.rasio || '-'}</span></td>
                        <td class="text-center">${port > 0 ? '<span class="port-badge"><i class="bx bx-network-chart"></i>' + port + ' port</span>' : '<span class="text-muted">-</span>'}</td>
                        <td class="text-center">
                            <button type="button" class="row-action btn-delete-splitter" data-id="${s.id}" data-serial="${s.serial || ''}" title="Hapus ${s.serial || ''}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                }).join('');
            }

            document.querySelectorAll('.kelola-splitter-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = btn.getAttribute('data-id');
                    const nama = btn.getAttribute('data-nama');
                    const splitters = JSON.parse(btn.getAttribute('data-splitters') || '[]');

                    document.getElementById('splitterLokasiNama').textContent = nama;
                    document.getElementById('tambahSplitterForm').action = '/odc/splitter/' + id;
                    document.getElementById('splitterRasio').value = '';
                    buildSplitterSerialInputs();
                    buildSplitterRows(splitters);

                    if (modalSplitter) modalSplitter.show();
                });
            });

            function buildSplitterSerialInputs() {
                const container = document.getElementById('splitterSerialInputs');
                if (!container) return;
                const rasio = document.getElementById('splitterRasio').value;
                const count = portCount(rasio);
                container.innerHTML = '';
                if (count === 0) {
                    container.innerHTML = '<div class="col-12"><div class="serial-hint"><i class="bx bx-info-circle"></i><span>Pilih rasio untuk menampilkan field serial per port</span></div></div>';
                    return;
                }
                for (let i = 1; i <= count; i++) {
                    const col = document.createElement('div');
                    col.className = 'col-md-6 col-lg-4';
                    col.innerHTML = `<div class="input-group input-group-sm">
                        <span class="input-group-text">P${i}</span>
                        <input type="text" class="form-control" name="serial_number[]" placeholder="Serial Port ${i}" required>
                    </div>`;
                    container.appendChild(col);
                }
            }

            document.getElementById('splitterRasio').addEventListener('change', buildSplitterSerialInputs);

            modalSplitterElement.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-delete-splitter');
                if (!btn) return;
                const id = btn.getAttribute('data-id');
                const serial = btn.getAttribute('data-serial');
                if (!confirm('Yakin hapus splitter ' + serial + '?')) return;
                fetch('/splitter/delete/' + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(function () {
                    window.location.reload();
                });
            });

            // MAP LOGIC
            let map;
            let markers = [];
            const modalMapElement = document.getElementById('modalMapOdc');
            const mapModal = new bootstrap.Modal(modalMapElement);

            function initMap() {
                if (!map) {
                    map = L.map('mapOdc').setView([-1.0269916, 110.48579129], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);
                }
            }

            function clearMarkers() {
                markers.forEach(m => map.removeLayer(m));
                markers = [];
            }

            function parseGps(gps) {
                if (!gps) return null;
                gps = gps.trim();

                // Format: "-8.04488, 110.48277"
                const simpleMatch = gps.match(/^(-?\d+\.\d+),\s*(-?\d+\.\d+)$/);
                if (simpleMatch) {
                    return [parseFloat(simpleMatch[1]), parseFloat(simpleMatch[2])];
                }

                // Format: "...?q=-8.04488,110.48277" or "q=loc:-8.04488+110.48277"
                const qMatch = gps.match(/q=(-?\d+\.\d+)(?:,|[+])(-?\d+\.\d+)/) ||
                               gps.match(/q=loc:(-?\d+\.\d+)(?:,|[+])(-?\d+\.\d+)/);
                if (qMatch) {
                    return [parseFloat(qMatch[1]), parseFloat(qMatch[2])];
                }

                // Format: "@-8.04488,110.48277" (Google Maps URL part)
                const atMatch = gps.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
                if (atMatch) {
                    return [parseFloat(atMatch[1]), parseFloat(atMatch[2])];
                }

                // Format DMS: 7°58'42.7"S 110°24'31.8"E or 8° 5'32.33"S110°29'52.76"E
                const dmsMatch = gps.match(/(\d+)\s*°\s*(\d+)\s*'\s*([\d.]+)\s*[^NS]*([NS])[\s,]*(\d+)\s*°\s*(\d+)\s*'\s*([\d.]+)\s*[^EW]*([EW])/i);
                if (dmsMatch) {
                    const lat = (parseInt(dmsMatch[1]) + parseInt(dmsMatch[2]) / 60 + parseFloat(dmsMatch[3]) / 3600) * (dmsMatch[4].toUpperCase() === 'S' ? -1 : 1);
                    const lng = (parseInt(dmsMatch[5]) + parseInt(dmsMatch[6]) / 60 + parseFloat(dmsMatch[7]) / 3600) * (dmsMatch[8].toUpperCase() === 'W' ? -1 : 1);
                    return [lat, lng];
                }

                return null;
            }

            // Open Map for All ODCs
            document.getElementById('btnOpenMap').addEventListener('click', function() {
                mapModal.show();
                setTimeout(() => {
                    initMap();
                    clearMarkers();

                    fetch('{{ route("peta.data") }}?type=odc')
                        .then(res => res.json())
                        .then(data => {
                            const bounds = [];

                            data.forEach(odc => {
                                if (odc.lat && odc.lng) {
                                    const marker = L.marker([odc.lat, odc.lng])
                                        .addTo(map)
                                        .bindPopup(`<b>${odc.nama}</b><br>Tipe: ODC`);
                                    markers.push(marker);
                                    bounds.push([odc.lat, odc.lng]);
                                }
                            });

                            if (bounds.length > 0) {
                                map.fitBounds(bounds);
                            }
                            map.invalidateSize();
                        })
                        .catch(err => {
                            console.error('Error fetching map data:', err);
                            alert('Gagal mengambil data peta. Silakan coba lagi.');
                        });
                }, 300);
            });

            // Open Map for specific ODC
            document.querySelectorAll('.view-map-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const gps = this.getAttribute('data-gps');
                    const nama = this.getAttribute('data-nama');
                    const coords = parseGps(gps);

                    if (!coords) {
                        alert('Koordinat tidak valid atau belum diatur.');
                        return;
                    }

                    mapModal.show();
                    setTimeout(() => {
                        initMap();
                        clearMarkers();

                        const marker = L.marker(coords)
                            .addTo(map)
                            .bindPopup(`<b>${nama}</b><br>Tipe: ODC`)
                            .openPopup();
                        markers.push(marker);

                        map.setView(coords, 16);
                        map.invalidateSize();
                    }, 300);
                });
            });

            // Adjust map size when modal is fully shown
            modalMapElement.addEventListener('shown.bs.modal', function () {
                if (map) {
                    map.invalidateSize();
                }
            });
        });
    </script>
@endsection