@extends('layouts.contentNavbarLayout')
@section('title', 'Detail Pelanggan')

@section('page-style')
<style>
    /* Modern Minimalist Design System */
    :root {
        --primary: #6366f1;
        --primary-light: #a5b4fc;
        --primary-dark: #4f46e5;
        --secondary: #64748b;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #06b6d4;
        --surface: #ffffff;
        --surface-variant: #f8fafc;
        --surface-hover: #f1f5f9;
        --on-surface: #1e293b;
        --on-surface-variant: #64748b;
        --on-surface-muted: #94a3b8;
        --outline: #e2e8f0;
        --outline-variant: #f1f5f9;
        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --radius: 12px;
        --radius-sm: 8px;
        --radius-lg: 16px;
        --spacing: 1rem;
        --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        --font-mono: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
    }
    
    /* Base Styles */
    body {
        background: #f8fafc;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: var(--on-surface);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
    }
    
    /* Modern Breadcrumb */
    .custom-breadcrumb {
        background: var(--surface);
        border: 1px solid var(--outline);
        border-radius: var(--radius);
        padding: 0.75rem 1.25rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        backdrop-filter: blur(10px);
        position: relative;
        overflow: hidden;
    }
    
    .custom-breadcrumb::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--primary);
    }
    
    .breadcrumb-item {
        display: inline-flex;
        align-items: center;
        font-size: 0.875rem;
        color: var(--on-surface-variant);
        transition: var(--transition);
    }
    
    .breadcrumb-item a {
        color: inherit;
        text-decoration: none;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .breadcrumb-item a:hover {
        background: var(--surface-hover);
        color: var(--primary);
        transform: translateX(-2px);
    }
    
    .breadcrumb-item.active {
        color: var(--primary);
        font-weight: 500;
    }
    
    .breadcrumb-item:not(:last-child)::after {
        content: '›';
        margin: 0 0.75rem;
        color: var(--on-surface-muted);
        font-size: 1.125rem;
    }
    
    /* Customer Profile Card */
    .customer-profile {
        background: var(--surface);
        border: 1px solid var(--outline);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
        overflow: hidden;
        position: relative;
        transition: var(--transition);
    }
    
    .customer-profile:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }
    
    .profile-header {
        background: var(--surface-variant);
        padding: 2rem;
        border-bottom: 1px solid var(--outline);
        position: relative;
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary);
    }
    
    .profile-header h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--on-surface);
        margin: 0 0 0.25rem 0;
        letter-spacing: -0.025em;
    }
    
    .profile-header .subtitle {
        color: var(--on-surface-variant);
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .customer-info {
        padding: 2rem;
    }
    
    /* Modern Badge System */
    .badge-custom {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 0.875rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: var(--transition);
    }
    
    .bg-success {
        background: var(--success);
        color: white;
        box-shadow: 0 2px 8px rgb(16 185 129 / 0.25);
    }
    
    .bg-danger {
        background: var(--danger);
        color: white;
        box-shadow: 0 2px 8px rgb(239 68 68 / 0.25);
    }
    
    .bg-label-primary {
        background: var(--primary);
        color: white;
        box-shadow: 0 2px 8px rgb(99 102 241 / 0.25);
    }
    
    .bg-label-danger {
        background: var(--danger);
        color: white;
        box-shadow: 0 2px 8px rgb(239 68 68 / 0.25);
    }
    
    .bg-label-success {
        background: var(--success);
        color: white;
        box-shadow: 0 2px 8px rgb(16 185 129 / 0.25);
    }
    
    /* Information Groups */
    .info-group {
        margin-bottom: 1.5rem;
    }
    
    .info-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--on-surface-variant);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    
    .info-value {
        background: var(--surface-variant);
        border: 1px solid var(--outline);
        border-radius: var(--radius);
        padding: 1rem;
        font-size: 0.875rem;
        color: var(--on-surface);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        font-family: var(--font-mono);
        font-weight: 500;
    }
    
    .info-value::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background: var(--primary);
        transform: scaleY(0);
        transition: var(--transition);
    }
    
    .info-value:hover {
        background: var(--surface-hover);
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: var(--shadow);
    }
    
    .info-value:hover::before {
        transform: scaleY(1);
    }
    
    /* Collapsible Cards */
    .collapsible-card {
        background: var(--surface);
        border: 1px solid var(--outline);
        border-radius: var(--radius);
        margin-bottom: 1rem;
        overflow: hidden;
        transition: var(--transition);
        position: relative;
    }
    
    .collapsible-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 0;
        background: var(--primary);
        transition: height 0.3s ease;
        z-index: 1;
    }
    
    .collapsible-card.active::before {
        height: 100%;
    }
    
    .collapsible-card:hover {
        box-shadow: var(--shadow);
        transform: translateY(-1px);
    }
    
    .collapsible-header {
        padding: 1.25rem 1.5rem;
        background: var(--surface);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: var(--transition);
        border-bottom: 1px solid transparent;
        position: relative;
        z-index: 2;
    }
    
    .collapsible-header:hover {
        background: var(--surface-hover);
    }
    
    .collapsible-card.active .collapsible-header {
        background: var(--surface-variant);
        border-bottom-color: var(--outline);
    }
    
    .collapsible-header > div:first-child {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        color: var(--on-surface);
    }
    
    .collapsible-header i:first-child {
        font-size: 1.25rem;
        color: var(--primary);
    }
    
    .toggle-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--surface-variant);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        color: var(--on-surface-variant);
    }
    
    .collapsible-card.active .toggle-icon {
        background: var(--primary);
        color: white;
        transform: rotate(180deg);
    }
    
    .collapsible-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .collapsible-card.active .collapsible-body {
        max-height: 2000px;
    }
    
    .collapsible-body-inner {
        padding: 1.5rem;
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.2s ease, transform 0.2s ease;
        transition-delay: 0.1s;
    }
    
    .collapsible-card.active .collapsible-body-inner {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Modern Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: var(--radius);
        border: none;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }
    
    .btn-outline-primary {
        background: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
    }
    
    .btn-outline-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--primary);
        transition: left 0.3s ease;
        z-index: -1;
    }
    
    .btn-outline-primary:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgb(99 102 241 / 0.25);
    }
    
    .btn-outline-primary:hover::before {
        left: 0;
    }
    
    /* Customer Avatar Enhancement */
    .customer-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
        margin: 0 auto 1rem;
        box-shadow: 0 4px 16px rgb(99 102 241 / 0.2);
        transition: var(--transition);
        border: 3px solid white;
        position: relative;
        overflow: hidden;
    }
    
    .customer-avatar::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: rotate(45deg);
        transition: var(--transition);
        opacity: 0;
    }
    
    .customer-avatar:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 8px 24px rgb(99 102 241 / 0.3);
    }
    
    .customer-avatar:hover::before {
        opacity: 1;
        animation: shimmer 1.5s ease-in-out;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }
    
    /* Enhanced Responsive Design */
    @media (max-width: 768px) {
        .profile-header,
        .customer-info {
            padding: 1.5rem;
        }
        
        .collapsible-header {
            padding: 1rem 1.25rem;
        }
        
        .collapsible-body-inner {
            padding: 1.25rem;
        }
        
        .customer-avatar {
            width: 70px;
            height: 70px;
            font-size: 1.5rem;
        }
        
        .info-value {
            padding: 0.875rem;
            font-size: 0.8125rem;
        }
    }
    
    /* Micro-interactions */
    .info-value,
    .collapsible-header,
    .btn {
        position: relative;
    }
    
    .info-value:active {
        transform: scale(0.98);
    }
    
    .collapsible-header:active {
        transform: scale(0.99);
    }
    
    .btn:active {
        transform: translateY(0) scale(0.98);
    }
    
    /* Focus states for accessibility */
    .collapsible-header:focus,
    .btn:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }
    
    /* Loading states */
    .loading {
        position: relative;
        overflow: hidden;
    }
    
    .loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    /* Enhanced notification styles */
    #update-notification {
        background: var(--surface) !important;
        border: 1px solid var(--outline) !important;
        color: var(--on-surface) !important;
        backdrop-filter: blur(10px);
        box-shadow: var(--shadow-lg) !important;
    }
    
    /* Glassmorphism effect for cards */
    .customer-profile,
    .collapsible-card {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    
    /* Improved spacing and typography */
    h5 {
        font-weight: 700;
        color: var(--on-surface);
        letter-spacing: -0.025em;
    }
    
    /* Enhanced hover states */
    .collapsible-card:hover .collapsible-header i:first-child {
        transform: scale(1.1);
        color: var(--primary-dark);
    }
    
    /* Smooth page transitions */
    .fade-in {
        animation: fadeIn 0.6s ease-out;
    }
    
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background-color: rgba(99, 102, 241, 0.2);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
        z-index: 0;
    }
    
    @keyframes ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('content')
<div class="row fade-in">
    <div class="col-12">
        <nav class="custom-breadcrumb" aria-label="breadcrumb">
            <div class="breadcrumb-item">
                <a href="{{ url('/dashboard') }}">
                    <i class="bx bx-home-alt"></i> Dashboard
                </a>
            </div>
            <div class="breadcrumb-item">
                <a href="{{ url('/data/pelanggan') }}?tab=pelanggan">
                    <i class="bx bx-group"></i> Daftar Pelanggan
                </a>
            </div>
            <div class="breadcrumb-item active" aria-current="page">
                <i class="bx bx-user"></i> Detail Pelanggan
            </div>
        </nav>
        
        <div class="customer-profile">
            <div class="profile-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3>Detail Pelanggan</h3>
                        <p class="subtitle">Teknisi:
                            {{ strtoupper($customer->teknisi->name ?? '000') }}</p>
                        </div>
                        <div>
                            <span class="badge bg-{{ $customer->status_id == 3 ? 'success' : 'danger' }} badge-custom">
                                <i class="bx {{ $customer->status_id == 3 ? 'bx-check-circle' : 'bx-x-circle' }}"></i>
                                {{ $customer->status_id == 3 ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="customer-info">
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <div class="customer-avatar">
                                {{ strtoupper(substr($customer->nama_customer, 0, 2)) }}
                            </div>
                            <h5 class="mb-4">{{ $customer->nama_customer }}</h5>
                            <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
                                <span class="badge bg-label-primary badge-custom">
                                    <i class="bx bx-user"></i> Pelanggan
                                </span>
                                @if (isset($customer->paket->nama_paket))
                                <span class="badge bg-label-danger badge-custom">
                                    <i class="bx bx-wifi"></i> {{ $customer->paket->nama_paket }}
                                </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-8 mt-5">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <span class="info-label">
                                            <i class="bx bx-user-circle text-primary"></i> Nama Pelanggan
                                        </span>
                                        <div class="info-value">{{ $customer->nama_customer }}</div>
                                    </div>
                                </div>
                                
                                @if (isset($customer->email))
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <span class="info-label">
                                            <i class="bx bx-envelope text-primary"></i> Email
                                        </span>
                                        <div class="info-value">{{ $customer->email ?? 'Tidak ada' }}</div>
                                    </div>
                                </div>
                                @endif
                                
                                @if (isset($customer->telepon))
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <span class="info-label">
                                            <i class="bx bx-phone text-primary"></i> Telepon
                                        </span>
                                        <div class="info-value">{{ $customer->telepon ?? 'Tidak ada' }}</div>
                                    </div>
                                </div>
                                @endif
                                
                                @if (isset($customer->alamat))
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <span class="info-label">
                                            <i class="bx bx-map-pin text-primary"></i> Alamat
                                        </span>
                                        <div class="info-value">{{ $customer->alamat ?? 'Tidak ada' }}</div>
                                    </div>
                                </div>
                                @endif
                                @if (isset($customer->media_id))
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <span class="info-label">
                                            <i class="bx bx-network-chart text-primary"></i> Media Koneksi
                                        </span>
                                        <div class="info-value">{{ $customer->media->nama_media ?? 'Tidak ada' }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr class="mb-5">
                    <!-- Collapsible Cards Section -->
                    <div class="row mt-4">
                        <div class="col-12 mb-3">
                            <h5 class="text-primary fw-semibold mb-3">
                                <i class="bx bx-info-circle me-1"></i> Informasi Detail
                            </h5>
                        </div>
                        
                        <div class="col-lg-6 mb-3">
                            <!-- Informasi Pribadi Pelanggan (Collapsible) -->
                            <div class="collapsible-card active" id="personal-info">
                                <div class="collapsible-header" onclick="toggleCollapse('personal-info')">
                                    <div>
                                        <i class="bx bx-user-circle"></i> Informasi Pribadi
                                    </div>
                                    <i class="bx bx-chevron-down toggle-icon"></i>
                                </div>
                                <div class="collapsible-body">
                                    <div class="collapsible-body-inner">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-map-alt text-primary"></i> Titik Lokasi
                                                    </span>
                                                    <a href="{{ $customer->gps }}" target="_blank"
                                                        class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                                                        <i class="bx bx-map"></i> Lihat Lokasi
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-phone text-primary"></i> No Telepon
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->no_hp ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-id-card text-primary"></i> No Identitas
                                                    </span>
                                                    <div class="info-value">{{ $customer->no_identitas ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-image text-primary"></i> Foto KTP
                                                    </span>
                                                    <a href="{{ asset($customer->identitas) }}"
                                                        class="btn btn-outline-primary d-flex align-items-center justify-content-center"
                                                        target="_blank">
                                                        <i class="bx bx-image-alt"></i> Lihat Foto
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 mb-3">
                            <!-- Informasi Teknis (Collapsible) -->
                            <div class="collapsible-card" id="technical-info">
                                <div class="collapsible-header" onclick="toggleCollapse('technical-info')">
                                    <div>
                                        <i class="bx bx-cog"></i> Informasi Teknis
                                    </div>
                                    <i class="bx bx-chevron-down toggle-icon"></i>
                                </div>
                                <div class="collapsible-body">
                                    <div class="collapsible-body-inner">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-broadcast text-primary"></i> Server
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->odp->odc->olt->server->lokasi_server ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-broadcast text-primary"></i> OLT
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->odp->odc->olt->nama_lokasi ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-broadcast text-primary"></i> ODC
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->odp->odc->nama_odc ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-broadcast text-primary"></i> ODP
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->odp->nama_odp ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-package text-primary"></i> Paket
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->paket->nama_paket ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-network-chart text-primary"></i> Local Address
                                                    </span>
                                                    <div class="info-value">{{ $customer->local_address ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-globe text-primary"></i> Remote Address
                                                    </span>
                                                    <div class="info-value">{{ $customer->remote_address ?? 'Tidak ada' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-user text-primary"></i> User Secret
                                                    </span>
                                                    <div class="info-value">{{ $customer->usersecret ?? 'Tidak ada' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-lock-alt text-primary"></i> Pass Secret
                                                    </span>
                                                    <div class="info-value">{{ $customer->pass_secret ?? 'Tidak ada' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-map-pin text-primary"></i> Lokasi Perangkat
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->alamat ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-wifi text-primary"></i> Status Koneksi
                                                    </span>
                                                    <div class="info-value">
                                                        @if ($customer->status_id)
                                                        @if ($customer->status_id == 3)
                                                        <span class="badge bg-label-success">
                                                            <i class="bx bx-check-circle"></i> Aktif
                                                        </span>
                                                        @elseif($customer->status_id == 8)
                                                        <span class="badge bg-label-danger">
                                                            <i class="bx bx-x-circle"></i> Nonaktif
                                                        </span>
                                                        @endif
                                                        @else
                                                        Tidak ada
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 mb-3">
                            <!-- Informasi Tagihan (Collapsible) -->
                            <div class="collapsible-card" id="billing-info">
                                <div class="collapsible-header" onclick="toggleCollapse('billing-info')">
                                    <div>
                                        <i class="bx bx-credit-card"></i> Informasi Tagihan
                                    </div>
                                    <i class="bx bx-chevron-down toggle-icon"></i>
                                </div>
                                <div class="collapsible-body">
                                    <div class="collapsible-body-inner">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-money text-primary"></i> Biaya Bulanan
                                                    </span>
                                                    <div class="info-value">
                                                        @if (isset($invoice->paket->harga))
                                                        <strong>Rp
                                                            {{ number_format($customer->paket->harga, 0, ',', '.') }}</strong>
                                                            @else
                                                            Tidak ada
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <span class="info-label">
                                                            <i class="bx bx-calendar text-primary"></i> Tanggal Jatuh
                                                            Tempo
                                                        </span>
                                                        <div class="info-value">
                                                            {{ isset($invoice->jatuh_tempo) ? \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d M Y') : 'Tidak ada' }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <span class="info-label">
                                                            <i class="bx bx-check-shield text-primary"></i> Status
                                                            Pembayaran
                                                        </span>
                                                        <div class="info-value">
                                                            @if (optional($invoice)->status_id == 7)
                                                            <span class="badge bg-label-danger">
                                                                <i class="bx bx-x-circle"></i> Belum Bayar
                                                            </span>
                                                            @elseif(optional($invoice)->status_id == 8)
                                                            <span class="badge bg-label-success">
                                                                <i class="bx bx-check-circle"></i> Sudah Bayar
                                                            </span>
                                                            @else
                                                            Tidak ada
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <span class="info-label">
                                                            <i class="bx bx-credit-card-front text-primary"></i> Metode
                                                            Pembayaran
                                                        </span>
                                                        <div class="info-value">
                                                            {{ $customer->metode_pembayaran ?? 'Tidak ada' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <a href="/riwayatPembayaran/{{ $customer->id }}" class="btn btn-outline-primary">
                                                    <i class="bx bx-receipt"></i> Lihat Riwayat Pembayaran
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if (isset($customer->created_at) || isset($customer->updated_at))
                            <div class="col-lg-6 mb-3">
                                <!-- Informasi Sistem (Collapsible) -->
                                <div class="collapsible-card" id="system-info">
                                    <div class="collapsible-header" onclick="toggleCollapse('system-info')">
                                        <div>
                                            <i class="bx bx-info-circle"></i> Informasi Sistem
                                        </div>
                                        <i class="bx bx-chevron-down toggle-icon"></i>
                                    </div>
                                    <div class="collapsible-body">
                                        <div class="collapsible-body-inner">
                                            <div class="row">
                                                @if (isset($customer->created_at))
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <span class="info-label">
                                                            <i class="bx bx-calendar-plus text-primary"></i>
                                                            Terdaftar Pada
                                                        </span>
                                                        <div class="info-value">
                                                            {{ $customer->created_at->format('d M Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                @if (isset($customer->updated_at))
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <span class="info-label">
                                                            <i class="bx bx-calendar-check text-primary"></i>
                                                            Tanggal Instalasi
                                                        </span>
                                                        <div class="info-value">
                                                            {{ date('d M Y', strtotime($customer->tanggal_selesai)) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <span class="info-label">
                                                            <i class="bx bx-hash text-primary"></i> ID Pelanggan
                                                        </span>
                                                        <div class="info-value">{{ $customer->id }}</div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <span class="info-label">
                                                            <i class="bx bx-user-check text-primary"></i> Status
                                                            Pelanggan
                                                        </span>
                                                        <div class="info-value">
                                                            @if ($customer->status_id)
                                                            @if ($customer->status_id == 3)
                                                            <span class="badge bg-label-success">
                                                                <i class="bx bx-check-circle"></i> Aktif
                                                            </span>
                                                            @elseif($customer->status_id == 9)
                                                            <span class="badge bg-label-danger">
                                                                <i class="bx bx-x-circle"></i> Nonaktif
                                                            </span>
                                                            @endif
                                                            @else
                                                            Tidak ada
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced JavaScript for Collapsible Functionality -->
        <script>
            function toggleCollapse(id) {
                const card = document.getElementById(id);
                const wasActive = card.classList.contains('active');
                
                // Add ripple effect
                addRippleEffect(event, card.querySelector('.collapsible-header'));
                
                // Toggle the active state
                card.classList.toggle('active');
                
                // If we're opening the card, scroll it into view
                if (!wasActive && card.classList.contains('active')) {
                    setTimeout(() => {
                        card.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    }, 150);
                }
                
                // Update accessibility attributes
                const header = card.querySelector('.collapsible-header');
                if (header) {
                    header.setAttribute('aria-expanded', card.classList.contains('active'));
                }
            }
            
            function addRippleEffect(e, element) {
                if (!element || !e) return;
                
                const existingRipples = element.querySelectorAll('.ripple-effect');
                existingRipples.forEach(ripple => ripple.remove());
                
                const ripple = document.createElement('span');
                ripple.classList.add('ripple-effect');
                
                const rect = element.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${e.clientX - rect.left - size/2}px`;
                ripple.style.top = `${e.clientY - rect.top - size/2}px`;
                
                element.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize collapsible sections
                const collapsibles = document.querySelectorAll('.collapsible-header');
                collapsibles.forEach(header => {
                    header.setAttribute('tabindex', '0');
                    header.setAttribute('role', 'button');
                    header.setAttribute('aria-expanded', header.parentElement.classList.contains('active'));
                    
                    header.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.click();
                        }
                    });
                });
                
                // Add staggered animation to cards
                const cards = document.querySelectorAll('.collapsible-card');
                cards.forEach((card, index) => {
                    card.style.animationDelay = `${index * 0.1}s`;
                    card.classList.add('fade-in');
                });
                
                // Enhanced breadcrumb animations
                const breadcrumbItems = document.querySelectorAll('.breadcrumb-item');
                breadcrumbItems.forEach((item, index) => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(10px)';
                    item.style.transition = `all 0.3s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s`;
                    
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 100);
                });
            });
        </script>
        
        <!-- Customer Data Polling Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const customerId = {{ $customer->id }};
                let lastUpdated = null;
                
                const statusBadge = document.querySelector('.badge.bg-success, .badge.bg-danger');
                const paketBadge = document.querySelector('.badge.bg-label-danger');
                
                function updateCustomerData(data) {
                    if (!data || !data.customer) return;
                    
                    if (statusBadge) {
                        const isActive = data.customer.status_id == 3;
                        statusBadge.className = `badge bg-${isActive ? 'success' : 'danger'} badge-custom`;
                        
                        const iconElement = statusBadge.querySelector('i');
                        if (iconElement) {
                            iconElement.className = `bx ${isActive ? 'bx-check-circle' : 'bx-x-circle'}`;
                        }
                        
                        statusBadge.innerHTML = statusBadge.innerHTML.replace(
                        /Aktif|Nonaktif/,
                        isActive ? 'Aktif' : 'Nonaktif'
                        );
                    }
                    
                    if (paketBadge && data.customer.paket) {
                        const paketText = paketBadge.textContent.trim();
                        if (!paketText.includes(data.customer.paket.nama_paket)) {
                            paketBadge.innerHTML = `<i class="bx bx-wifi"></i> ${data.customer.paket.nama_paket}`;
                        }
                    }
                    
                    const customerProfile = document.querySelector('.customer-profile');
                    if (customerProfile) {
                        customerProfile.style.transition = 'box-shadow 0.3s ease';
                        customerProfile.style.boxShadow = '0 0 20px rgba(99, 102, 241, 0.3)';
                        setTimeout(() => {
                            customerProfile.style.boxShadow = '';
                        }, 1000);
                    }
                    
                    lastUpdated = data.last_updated;
                    showUpdateNotification();
                }
                
                function showUpdateNotification() {
                    let notification = document.getElementById('update-notification');
                    
                    if (!notification) {
                        notification = document.createElement('div');
                        notification.id = 'update-notification';
                        notification.style.cssText = `
                            position: fixed;
                            bottom: 20px;
                            right: 20px;
                            background: var(--surface);
                            border: 1px solid var(--outline);
                            color: var(--on-surface);
                            padding: 12px 16px;
                            border-radius: var(--radius);
                            box-shadow: var(--shadow-lg);
                            z-index: 9999;
                            transform: translateY(100px);
                            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                            font-size: 0.875rem;
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            backdrop-filter: blur(10px);
                        `;
                        
                        document.body.appendChild(notification);
                    }
                    
                    notification.innerHTML = `
                        <i class='bx bx-refresh-alt' style='font-size: 1.1rem; color: var(--primary);'></i>
                        Data pelanggan diperbarui
                    `;
                    
                    setTimeout(() => {
                        notification.style.transform = 'translateY(0)';
                        
                        setTimeout(() => {
                            notification.style.transform = 'translateY(100px)';
                        }, 3000);
                    }, 100);
                }
                
                function pollForUpdates() {
                    fetch(`/api/customers/${customerId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!lastUpdated || lastUpdated !== data.last_updated) {
                            updateCustomerData(data);
                        }
                    })
                    .catch(error => {
                        console.error('Error polling for updates:', error);
                    })
                    .finally(() => {
                        setTimeout(pollForUpdates, 30000);
                    });
                }
                
                pollForUpdates();
            });
        </script>
        @endsection