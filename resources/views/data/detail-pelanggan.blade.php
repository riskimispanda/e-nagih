@extends('layouts.contentNavbarLayout')
@section('title', 'Detail Pelanggan')

@section('page-style')
<style>
    /* Modern Minimalist Styles with Collapsible Sections */
    :root {
        --primary-color: #696cff;
        --primary-light: #f0f1ff;
        --primary-dark: #5157ff;
        --secondary-color: #8592a3;
        --success-color: #71dd37;
        --danger-color: #ff3e1d;
        --warning-color: #ffab00;
        --info-color: #03c3ec;
        --background-color: #f5f5f9;
        --card-bg: #ffffff;
        --text-primary: #566a7f;
        --text-secondary: #8592a3;
        --text-muted: #a1acb8;
        --border-color: rgba(0, 0, 0, 0.05);
        --shadow-sm: 0 2px 6px rgba(67, 89, 113, 0.06);
        --shadow-md: 0 4px 14px rgba(67, 89, 113, 0.1);
        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
        --transition-fast: 0.2s ease;
        --transition-normal: 0.3s ease;
        --spacing-xs: 0.5rem;
        --spacing-sm: 0.75rem;
        --spacing-md: 1rem;
        --spacing-lg: 1.5rem;
        --spacing-xl: 2rem;
    }
    
    /* Base Styles */
    body {
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        background-color: var(--background-color);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23696cff' fill-opacity='0.03'%3E%3Cpath opacity='.5' d='M96 95h4v1h-4v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9zm-1 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9z'/%3E%3Cpath d='M6 5V0H5v5H0v1h5v94h1V6h94V5H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        color: var(--text-primary);
        line-height: 1.5;
    }
    
    /* Customer Profile Card */
    .customer-profile {
        background-color: var(--card-bg);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: var(--spacing-lg);
        overflow: hidden;
        transition: all var(--transition-fast);
        border: 1px solid var(--border-color);
        position: relative;
    }
    
    .customer-profile::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(to right, var(--primary-color), var(--primary-light));
        opacity: 0.8;
        z-index: 1;
    }
    
    .customer-profile:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    
    .profile-header {
        background-color: rgba(105, 108, 255, 0.04);
        padding: var(--spacing-lg);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        position: relative;
        border-bottom: 1px solid var(--border-color);
    }
    
    .profile-header h3 {
        margin: 0;
        font-weight: 800;
        font-size: 1.30rem;
        color: var(--primary-dark);
    }
    
    .profile-header .subtitle {
        color: var(--text-secondary);
        margin-top: 0.25rem;
        font-size: 0.875rem;
        opacity: 0.8;
    }
    
    .customer-info {
        padding: var(--spacing-lg);
    }
    
    /* Information Groups */
    .info-group {
        margin-bottom: var(--spacing-md);
        position: relative;
    }
    
    .info-label {
        font-weight: 500;
        color: var(--text-secondary);
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        font-size: 0.95rem;
        color: var(--text-primary);
        background-color: rgba(105, 108, 255, 0.03);
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--radius-sm);
        border-left: 2px solid var(--primary-color);
        transition: all var(--transition-fast);
        word-break: break-word;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
    }
    
    .info-value::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, rgba(105, 108, 255, 0.03), transparent);
        opacity: 0;
        transition: opacity var(--transition-fast);
    }
    
    .info-value:hover {
        background-color: var(--primary-light);
        border-left-width: 3px;
        transform: translateX(2px);
        box-shadow: 0 2px 5px rgba(105, 108, 255, 0.1);
    }
    
    .info-value:hover::after {
        opacity: 1;
    }
    
    /* Customer Avatar */
    .customer-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: var(--primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: var(--primary-color);
        margin: 0 auto var(--spacing-md);
        box-shadow: 0 3px 10px rgba(105, 108, 255, 0.15);
        transition: transform var(--transition-fast), box-shadow var(--transition-fast);
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.9);
    }
    
    .customer-avatar:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 5px 15px rgba(105, 108, 255, 0.25);
    }
    
    .customer-avatar img {
        object-fit: cover;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-lg);
    }
    
    .action-buttons .btn {
        flex: 1;
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--radius-sm);
        font-weight: 500;
        font-size: 0.875rem;
        transition: all var(--transition-fast);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-outline-primary {
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        background-color: transparent;
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(105, 108, 255, 0.2);
    }
    
    .btn-outline-primary i {
        font-size: 1.1rem;
    }
    
    /* Enhanced Collapsible Card Styles */
    .collapsible-card {
        background-color: var(--card-bg);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        margin-bottom: var(--spacing-md);
        overflow: hidden;
        transition: all var(--transition-normal);
        border: 1px solid var(--border-color);
        position: relative;
    }
    
    .collapsible-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    
    .collapsible-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 0;
        background: linear-gradient(to bottom, var(--primary-color), var(--primary-light));
        transition: height var(--transition-normal);
        border-radius: 4px 0 0 4px;
    }
    
    .collapsible-card.active::before {
        height: 100%;
    }
    
    .collapsible-header {
        padding: var(--spacing-md) var(--spacing-lg);
        background-color: var(--card-bg);
        font-weight: 600;
        color: var(--text-primary);
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all var(--transition-fast);
        border-bottom: 1px solid transparent;
        position: relative;
        z-index: 1;
    }
    
    .collapsible-header:hover {
        background-color: rgba(105, 108, 255, 0.03);
    }
    
    .collapsible-header:active {
        background-color: var(--primary-light);
    }
    
    .collapsible-header i {
        margin-right: var(--spacing-sm);
        font-size: 1.2rem;
        color: var(--primary-color);
        transition: all var(--transition-fast);
    }
    
    .collapsible-card.active .collapsible-header i {
        color: var(--primary-dark);
    }
    
    .collapsible-header .toggle-icon {
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        color: var(--text-muted);
        font-size: 1.1rem;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: rgba(105, 108, 255, 0.05);
    }
    
    .collapsible-card.active .toggle-icon {
        background-color: var(--primary-light);
        color: var(--primary-color);
    }
    
    .collapsible-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s cubic-bezier(0, 1, 0, 1);
        padding: 0 var(--spacing-lg);
        background-color: var(--card-bg);
    }
    
    .collapsible-body-inner {
        padding: var(--spacing-lg) 0;
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity var(--transition-fast), transform var(--transition-fast);
        transition-delay: 0.05s;
    }
    
    .collapsible-card.active .collapsible-body-inner {
        opacity: 1;
        transform: translateY(0);
    }
    
    .collapsible-card.active .collapsible-header {
        border-bottom: 1px solid var(--border-color);
        background-color: rgba(105, 108, 255, 0.03);
    }
    
    .collapsible-card.active .toggle-icon {
        transform: rotate(180deg);
    }
    
    .collapsible-card.active .collapsible-body {
        max-height: 2000px;
        /* Larger value to accommodate all content */
        transition: max-height 0.35s cubic-bezier(0.5, 0, 1, 0);
    }
    
    /* Badges */
    .badge-custom {
        padding: 0.35rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: var(--radius-sm);
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .bg-label-primary {
        background-color: var(--primary-light);
        color: var(--primary-color);
    }
    
    .bg-label-success {
        background-color: rgba(113, 221, 55, 0.15);
        color: var(--success-color);
    }
    
    .bg-label-danger {
        background-color: rgba(255, 62, 29, 0.15);
        color: var(--danger-color);
    }
    
    /* Modern Breadcrumb Navigation */
    .custom-breadcrumb {
        margin-bottom: var(--spacing-md);
        padding: var(--spacing-sm) var(--spacing-md);
        background-color: var(--card-bg);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        position: relative;
        overflow: hidden;
        border: 1px solid var(--border-color);
        transition: all var(--transition-fast);
        background-image: linear-gradient(to right, rgba(105, 108, 255, 0.02), transparent);
    }
    
    .custom-breadcrumb::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, var(--primary-color), var(--primary-light));
        opacity: 0.8;
    }
    
    .custom-breadcrumb::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, transparent, rgba(105, 108, 255, 0.03));
        opacity: 0;
        transition: opacity var(--transition-fast);
    }
    
    .custom-breadcrumb:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    
    .custom-breadcrumb:hover::after {
        opacity: 1;
    }
    
    .breadcrumb-item {
        display: inline-flex;
        align-items: center;
        font-size: 0.875rem;
        color: var(--text-secondary);
        padding: 0.25rem 0.5rem;
        position: relative;
        transition: all var(--transition-fast);
        z-index: 1;
    }
    
    .breadcrumb-item a {
        color: var(--text-secondary);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        background-color: transparent;
        position: relative;
        overflow: hidden;
    }
    
    .breadcrumb-item a::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(105, 108, 255, 0.08);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.25s ease;
        z-index: -1;
        border-radius: var(--radius-sm);
    }
    
    .breadcrumb-item a:hover {
        color: var(--primary-color);
        transform: translateX(-2px);
    }
    
    .breadcrumb-item a:hover::before {
        transform: scaleX(1);
    }
    
    .breadcrumb-item.active {
        color: var(--primary-color);
        font-weight: 500;
        background-color: rgba(105, 108, 255, 0.08);
        border-radius: var(--radius-sm);
        padding: 0.25rem 0.5rem;
        box-shadow: 0 1px 3px rgba(105, 108, 255, 0.1);
    }
    
    .breadcrumb-item i {
        font-size: 1.1rem;
        margin-right: 0.35rem;
        transition: transform 0.2s ease;
    }
    
    .breadcrumb-item a:hover i {
        transform: translateX(-2px);
    }
    
    .breadcrumb-item:not(:last-child)::after {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        border-top: 1.5px solid var(--text-muted);
        border-right: 1.5px solid var(--text-muted);
        transform: rotate(45deg);
        margin: 0 0.5rem;
        opacity: 0.7;
        transition: transform 0.2s ease;
    }
    
    .breadcrumb-item:hover:not(:last-child)::after {
        transform: rotate(45deg) scale(1.2);
        border-color: var(--primary-color);
    }
    
    @media (max-width: 576px) {
        .custom-breadcrumb {
            padding: var(--spacing-xs) var(--spacing-sm);
        }
        
        .breadcrumb-item {
            font-size: 0.8rem;
        }
    }
    
    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .collapsible-card {
            margin-bottom: var(--spacing-md);
        }
    }
    
    @media (max-width: 767.98px) {
        .action-buttons {
            flex-direction: column;
        }
        
        .customer-avatar {
            width: 70px;
            height: 70px;
            font-size: 1.5rem;
        }
        
        .profile-header {
            padding: var(--spacing-md);
        }
        
        .customer-info {
            padding: var(--spacing-md);
        }
        
        .collapsible-header {
            padding: var(--spacing-sm) var(--spacing-md);
        }
    }
    
    @media (max-width: 575.98px) {
        .row>[class*="col-"] {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        .customer-avatar {
            width: 60px;
            height: 60px;
        }
        
        .info-value {
            font-size: 0.9rem;
            padding: 0.6rem 0.8rem;
        }
        
        .collapsible-header {
            font-size: 0.95rem;
        }
    }
    
    /* Ripple effect styles */
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background-color: rgba(105, 108, 255, 0.1);
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
</style>
@endsection

@section('content')
<div class="row">
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
                                <i class="bx {{ $customer->status_id == 3 ? 'bx-check-circle' : 'bx-x-circle' }} me-1"></i>
                                {{ $customer->status_id == 3 ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="customer-info">
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <h5 class="mb-4">{{ $customer->nama_customer }}</h5>
                            <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
                                <span class="badge bg-label-primary badge-custom">
                                    <i class="bx bx-user me-1"></i> Pelanggan
                                </span>
                                @if (isset($customer->paket->nama_paket))
                                <span class="badge bg-label-danger badge-custom">
                                    <i class="bx bx-wifi me-1"></i> {{ $customer->paket->nama_paket }}
                                </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-8 mt-5">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <span class="info-label">
                                            <i class="bx bx-user-circle text-primary me-1"></i> Nama Pelanggan
                                        </span>
                                        <div class="info-value">{{ $customer->nama_customer }}</div>
                                    </div>
                                </div>
                                
                                @if (isset($customer->email))
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <span class="info-label">
                                            <i class="bx bx-envelope text-primary me-1"></i> Email
                                        </span>
                                        <div class="info-value">{{ $customer->email ?? 'Tidak ada' }}</div>
                                    </div>
                                </div>
                                @endif
                                
                                @if (isset($customer->telepon))
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <span class="info-label">
                                            <i class="bx bx-phone text-primary me-1"></i> Telepon
                                        </span>
                                        <div class="info-value">{{ $customer->telepon ?? 'Tidak ada' }}</div>
                                    </div>
                                </div>
                                @endif
                                
                                @if (isset($customer->alamat))
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <span class="info-label">
                                            <i class="bx bx-map-pin text-primary me-1"></i> Alamat
                                        </span>
                                        <div class="info-value">{{ $customer->alamat ?? 'Tidak ada' }}</div>
                                    </div>
                                </div>
                                @endif
                                @if (isset($customer->media_id))
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <span class="info-label">
                                            <i class="bx bx-network-chart text-primary me-1"></i> Media Koneksi
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
                                                        <i class="bx bx-map-alt text-primary me-1"></i> Titik Lokasi
                                                    </span>
                                                    <a href="{{ $customer->gps }}" target="_blank"
                                                        class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                                                        <i class="bx bx-map me-1"></i> Lihat Lokasi
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-phone text-primary me-1"></i> No Telepon
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->no_hp ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-id-card text-primary me-1"></i> No Identitas
                                                    </span>
                                                    <div class="info-value">{{ $customer->no_identitas ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-image text-primary me-1"></i> Foto KTP
                                                    </span>
                                                    <a href="{{ asset($customer->identitas) }}"
                                                        class="btn btn-outline-primary d-flex align-items-center justify-content-center"
                                                        target="_blank">
                                                        <i class="bx bx-image-alt me-1"></i> Lihat Foto
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
                                                        <i class="bx bx-broadcast text-primary me-1"></i> BTS Server
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->getServer->lokasi_server ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-package text-primary me-1"></i> Paket
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->paket->nama_paket ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-network-chart text-primary me-1"></i> Local Address
                                                    </span>
                                                    <div class="info-value">{{ $customer->local_address ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-globe text-primary me-1"></i> Remote Address
                                                    </span>
                                                    <div class="info-value">{{ $customer->remote_address ?? 'Tidak ada' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-user text-primary me-1"></i> User Secret
                                                    </span>
                                                    <div class="info-value">{{ $customer->usersecret ?? 'Tidak ada' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-lock-alt text-primary me-1"></i> Pass Secret
                                                    </span>
                                                    <div class="info-value">{{ $customer->pass_secret ?? 'Tidak ada' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-map-pin text-primary me-1"></i> Lokasi Perangkat
                                                    </span>
                                                    <div class="info-value">
                                                        {{ $customer->alamat ?? 'Tidak ada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <span class="info-label">
                                                        <i class="bx bx-wifi text-primary me-1"></i> Status Koneksi
                                                    </span>
                                                    <div class="info-value">
                                                        @if ($customer->status_id)
                                                        @if ($customer->status_id == 3)
                                                        <span class="badge bg-label-success">
                                                            <i class="bx bx-check-circle me-1"></i> Aktif
                                                        </span>
                                                        @elseif($customer->status_id == 8)
                                                        <span class="badge bg-label-danger">
                                                            <i class="bx bx-x-circle me-1"></i> Nonaktif
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
                                                        <i class="bx bx-money text-primary me-1"></i> Biaya Bulanan
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
                                                            <i class="bx bx-calendar text-primary me-1"></i> Tanggal Jatuh
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
                                                            <i class="bx bx-check-shield text-primary me-1"></i> Status
                                                            Pembayaran
                                                        </span>
                                                        <div class="info-value">
                                                            @if ($invoice->status_id)
                                                            @if ($invoice->status_id == 7)
                                                            <span class="badge bg-label-danger">
                                                                <i class="bx bx-x-circle me-1"></i> Belum Bayar
                                                            </span>
                                                            @elseif($invoice->status_id == 8)
                                                            <span class="badge bg-label-success">
                                                                <i class="bx bx-check-circle me-1"></i> Sudah Bayar
                                                            </span>
                                                            @endif
                                                            @else
                                                            Tidak ada
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <span class="info-label">
                                                            <i class="bx bx-credit-card-front text-primary me-1"></i> Metode
                                                            Pembayaran
                                                        </span>
                                                        <div class="info-value">
                                                            {{ $customer->metode_pembayaran ?? 'Tidak ada' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <a href="#" class="btn btn-outline-primary">
                                                    <i class="bx bx-receipt me-1"></i> Lihat Riwayat Pembayaran
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
                                                            <i class="bx bx-calendar-plus text-primary me-1"></i>
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
                                                            <i class="bx bx-calendar-check text-primary me-1"></i>
                                                            Tanggal Instalasi
                                                        </span>
                                                        <div class="info-value">
                                                            {{ $customer->updated_at->format('d M Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <span class="info-label">
                                                            <i class="bx bx-hash text-primary me-1"></i> ID Pelanggan
                                                        </span>
                                                        <div class="info-value">{{ $customer->id }}</div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="info-group">
                                                        <span class="info-label">
                                                            <i class="bx bx-user-check text-primary me-1"></i> Status
                                                            Pelanggan
                                                        </span>
                                                        <div class="info-value">
                                                            @if ($customer->status_id)
                                                            @if ($customer->status_id == 3)
                                                            <span class="badge bg-label-success">
                                                                <i class="bx bx-check-circle me-1"></i> Aktif
                                                            </span>
                                                            @elseif($customer->status_id == 9)
                                                            <span class="badge bg-label-danger">
                                                                <i class="bx bx-x-circle me-1"></i> Nonaktif
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
                
                // Optional: Close other cards when opening a new one (accordion behavior)
                if (!wasActive) {
                    document.querySelectorAll('.collapsible-card.active').forEach(activeCard => {
                        // Don't close the card we're about to open
                        if (activeCard.id !== id) {
                            activeCard.classList.remove('active');
                            const header = activeCard.querySelector('.collapsible-header');
                            if (header) {
                                header.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });
                }
                
                // Toggle the active state
                card.classList.toggle('active');
                
                // If we're opening the card, scroll it into view
                if (!wasActive && card.classList.contains('active')) {
                    // Add a slight delay for a smoother animation
                    setTimeout(() => {
                        // Scroll the card into view with a smooth animation
                        card.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    }, 150);
                }
                
                // Add ripple effect when clicking
                addRippleEffect(event, card.querySelector('.collapsible-header'));
                
                // Update accessibility attributes
                const header = card.querySelector('.collapsible-header');
                if (header) {
                    header.setAttribute('aria-expanded', card.classList.contains('active'));
                }
            }
            
            // Add a ripple effect to the header when clicked
            function addRippleEffect(e, element) {
                if (!element || !e) return;
                
                // Remove any existing ripples
                const existingRipples = element.querySelectorAll('.ripple-effect');
                existingRipples.forEach(ripple => ripple.remove());
                
                // Create new ripple
                const ripple = document.createElement('span');
                ripple.classList.add('ripple-effect');
                
                const rect = element.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height) * 1.5; // Larger ripple
                
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${e.clientX - rect.left - size/2}px`;
                ripple.style.top = `${e.clientY - rect.top - size/2}px`;
                
                element.appendChild(ripple);
                
                // Remove the ripple after animation completes
                setTimeout(() => {
                    ripple.remove();
                }, 700);
            }
            
            // Initialize all collapsible sections
            document.addEventListener('DOMContentLoaded', function() {
                // First section is open by default (already has 'active' class)
                const firstCard = document.querySelector('.collapsible-card');
                if (firstCard && !document.querySelector('.collapsible-card.active')) {
                    firstCard.classList.add('active');
                }
                
                // Add keyboard accessibility and event listeners
                const collapsibles = document.querySelectorAll('.collapsible-header');
                collapsibles.forEach(header => {
                    // Set accessibility attributes
                    header.setAttribute('tabindex', '0');
                    header.setAttribute('role', 'button');
                    header.setAttribute('aria-expanded', header.parentElement.classList.contains('active'));
                    
                    // Add aria-controls attribute
                    const parentId = header.parentElement.id;
                    if (parentId) {
                        header.setAttribute('aria-controls', `${parentId}-content`);
                        
                        // Add ID to the content section for accessibility
                        const content = header.nextElementSibling;
                        if (content) {
                            content.id = `${parentId}-content`;
                        }
                    }
                    
                    // Handle keyboard interactions
                    header.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.click();
                        }
                    });
                    
                    // Add hover effect
                    header.addEventListener('mouseenter', function() {
                        this.classList.add('hover');
                    });
                    
                    header.addEventListener('mouseleave', function() {
                        this.classList.remove('hover');
                    });
                });
                
                // Add subtle hover effects to buttons and cards
                document.querySelectorAll('.btn, .info-value').forEach(element => {
                    element.addEventListener('mouseenter', function() {
                        this.style.transition = 'all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                    });
                });
            });
        </script>
        
        <style>
            /* Ripple effect styles */
            .collapsible-header {
                position: relative;
                overflow: hidden;
            }
            
            .ripple-effect {
                position: absolute;
                border-radius: 50%;
                background-color: rgba(105, 108, 255, 0.15);
                transform: scale(0);
                animation: ripple 0.7s cubic-bezier(0.4, 0, 0.2, 1);
                pointer-events: none;
                z-index: 0;
            }
            
            @keyframes ripple {
                to {
                    transform: scale(2.5);
                    opacity: 0;
                }
            }
        </style>
        
        <!-- Initialize tooltips, popovers, and breadcrumb enhancements -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Add smooth scrolling to all links
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('href');
                        if (targetId !== '#') {
                            const target = document.querySelector(targetId);
                            if (target) {
                                target.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }
                        }
                    });
                });
                
                // Enhance breadcrumb with subtle animations
                const breadcrumbItems = document.querySelectorAll('.breadcrumb-item');
                
                // Add staggered entrance animation
                breadcrumbItems.forEach((item, index) => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(10px)';
                    item.style.transition = `all 0.3s ease ${index * 0.1}s`;
                    
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 100);
                });
                
                // Add hover effect to breadcrumb container
                const breadcrumb = document.querySelector('.custom-breadcrumb');
                if (breadcrumb) {
                    breadcrumb.addEventListener('mouseenter', function() {
                        breadcrumbItems.forEach((item, index) => {
                            if (!item.classList.contains('active')) {
                                item.style.transform = `translateX(${index * 2}px)`;
                            }
                        });
                    });
                    
                    breadcrumb.addEventListener('mouseleave', function() {
                        breadcrumbItems.forEach(item => {
                            item.style.transform = 'translateX(0)';
                        });
                    });
                }
            });
        </script>
        
        <!-- Customer Data Polling Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Get customer ID from the current page
                const customerId = {{ $customer->id }};
                let lastUpdated = null;
                
                // Elements that will be updated with polling data
                const statusBadge = document.querySelector('.badge.bg-success, .badge.bg-danger');
                const paketBadge = document.querySelector('.badge.bg-label-danger');
                
                // Function to update UI with new data
                function updateCustomerData(data) {
                    if (!data || !data.customer) return;
                    
                    // Update status badge
                    if (statusBadge) {
                        const isActive = data.customer.status_id == 3;
                        statusBadge.className = `badge bg-${isActive ? 'success' : 'danger'} badge-custom`;
                        
                        const iconElement = statusBadge.querySelector('i');
                        if (iconElement) {
                            iconElement.className = `bx ${isActive ? 'bx-check-circle' : 'bx-x-circle'} me-1`;
                        }
                        
                        statusBadge.innerHTML = statusBadge.innerHTML.replace(
                        /Aktif|Nonaktif/,
                        isActive ? 'Aktif' : 'Nonaktif'
                        );
                    }
                    
                    // Update paket badge if it exists and data has changed
                    if (paketBadge && data.customer.paket) {
                        const paketText = paketBadge.textContent.trim();
                        if (!paketText.includes(data.customer.paket.nama_paket)) {
                            paketBadge.innerHTML = `<i class="bx bx-wifi me-1"></i> ${data.customer.paket.nama_paket}`;
                        }
                    }
                    
                    // Update any other fields that might have changed
                    const infoValues = document.querySelectorAll('.info-value');
                    infoValues.forEach(element => {
                        // Check if this is a field we want to update
                        const label = element.previousElementSibling;
                        if (label && label.textContent.includes('Paket')) {
                            if (data.customer.paket) {
                                element.textContent = data.customer.paket.nama_paket;
                            }
                        } else if (label && label.textContent.includes('Status')) {
                            if (data.customer.status) {
                                element.textContent = data.customer.status.nama_status;
                            }
                        }
                    });
                    
                    // Add a subtle flash effect to indicate updated data
                    const customerProfile = document.querySelector('.customer-profile');
                    if (customerProfile) {
                        customerProfile.style.transition = 'box-shadow 0.3s ease';
                        customerProfile.style.boxShadow = '0 0 15px rgba(105, 108, 255, 0.5)';
                        setTimeout(() => {
                            customerProfile.style.boxShadow = '';
                        }, 1000);
                    }
                    
                    // Update last updated timestamp
                    lastUpdated = data.last_updated;
                    
                    // Show a small notification that data was updated
                    showUpdateNotification();
                }
                
                // Function to show a small notification when data is updated
                function showUpdateNotification() {
                    // Check if notification already exists
                    let notification = document.getElementById('update-notification');
                    
                    if (!notification) {
                        // Create notification element
                        notification = document.createElement('div');
                        notification.id = 'update-notification';
                        notification.style.position = 'fixed';
                        notification.style.bottom = '20px';
                        notification.style.right = '20px';
                        notification.style.backgroundColor = 'var(--primary-color)';
                        notification.style.color = 'white';
                        notification.style.padding = '10px 15px';
                        notification.style.borderRadius = 'var(--radius-md)';
                        notification.style.boxShadow = 'var(--shadow-md)';
                        notification.style.zIndex = '9999';
                        notification.style.transform = 'translateY(100px)';
                        notification.style.transition = 'transform 0.3s ease';
                        notification.style.fontSize = '0.875rem';
                        notification.style.display = 'flex';
                        notification.style.alignItems = 'center';
                        notification.style.gap = '8px';
                        
                        document.body.appendChild(notification);
                    }
                    
                    // Update notification content
                    notification.innerHTML = `
                    <i class='bx bx-refresh-alt' style='font-size: 1.1rem;'></i>
                    Data pelanggan diperbarui
                `;
                    
                    // Show notification
                    setTimeout(() => {
                        notification.style.transform = 'translateY(0)';
                        
                        // Hide after 3 seconds
                        setTimeout(() => {
                            notification.style.transform = 'translateY(100px)';
                        }, 3000);
                    }, 100);
                }
                
                // Function to poll for updates
                function pollForUpdates() {
                    fetch(`/api/customers/${customerId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Only update UI if data has changed
                        if (!lastUpdated || lastUpdated !== data.last_updated) {
                            updateCustomerData(data);
                        }
                    })
                    .catch(error => {
                        console.error('Error polling for updates:', error);
                    })
                    .finally(() => {
                        // Poll again after delay
                        setTimeout(pollForUpdates, 30000); // Poll every 30 seconds
                    });
                }
                
                // Start polling
                pollForUpdates();
            });
        </script>
        @endsection
        