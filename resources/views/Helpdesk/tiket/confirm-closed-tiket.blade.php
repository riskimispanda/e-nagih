@extends('layouts.contentNavbarLayout')

@section('title', 'Tutup Tiket Open')
<style>
    .card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-bottom: 1px solid #e9ecef;
        padding: 1.25rem 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .card-header:hover {
        background: linear-gradient(135deg, #f0f2ff 0%, #f8f9ff 100%);
    }
    
    .card-header[aria-expanded="true"] {
        background: linear-gradient(135deg, #696cff 0%, #8592ff 100%);
        color: white;
    }
    
    .card-header[aria-expanded="true"] .card-title,
    .card-header[aria-expanded="true"] .badge {
        color: white;
    }
    
    .collapse-icon {
        transition: transform 0.3s ease;
    }
    
    .card-header[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    
    .info-card {
        display: flex;
        align-items: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }
    
    .info-card:hover {
        background: #f1f3ff;
        transform: translateY(-2px);
    }
    
    .info-icon {
        width: 40px;
        height: 40px;
        background: #696cff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .info-icon i {
        color: white;
        font-size: 1.2rem;
    }
    
    .info-label {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
        font-weight: 500;
    }
    
    .info-value {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0;
    }
    
    .tech-info {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #696cff;
        transition: all 0.2s ease;
    }
    
    .tech-info:hover {
        background: #f1f3ff;
        transform: translateX(5px);
    }
    
    .tech-info i {
        font-size: 1.5rem;
        margin-right: 1rem;
        width: 24px;
        text-align: center;
    }
    
    .connection-info {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .connection-info:hover {
        background: #f8f9ff;
        border-color: #696cff;
    }
    
    .connection-info i {
        font-size: 1.25rem;
        margin-right: 0.75rem;
        color: #696cff;
        width: 20px;
        text-align: center;
    }
    
    .input-group {
        border-radius: 8px;
    }
    
    .input-group-text {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    
    .form-select, .form-control {
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
    }
    
    /* Animation for collapse */
    .collapse.show {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Status badges */
    .status-badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .container-xxl {
            padding: 1rem;
        }
        
        .card-header {
            padding: 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .info-card {
            flex-direction: column;
            text-align: center;
            padding: 1rem 0.5rem;
        }
        
        .info-icon {
            margin-right: 0;
            margin-bottom: 0.5rem;
        }
        
        .tech-info, .connection-info {
            flex-direction: column;
            text-align: center;
            padding: 1rem 0.5rem;
        }
        
        .tech-info i, .connection-info i {
            margin-right: 0;
            margin-bottom: 0.5rem;
        }
        
        .d-flex.justify-content-end {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn {
            width: 100%;
        }

        /* Styling untuk valid state */
        .is-valid {
            border-color: #198754 !important;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }

        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        /* Animation untuk auto-fill feedback */
        @keyframes highlight {
            0% { background-color: rgba(25, 135, 84, 0.1); }
            100% { background-color: transparent; }
        }

        .is-valid {
            animation: highlight 2s ease-in-out;
        }

        /* Styling untuk form text */
        .form-text {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Responsive design untuk toast */
        .toast-container {
            z-index: 1090;
        }

        @media (max-width: 576px) {
            .toast-container {
                padding: 0.5rem !important;
            }
            
            .toast {
                font-size: 0.875rem;
            }
        }
    }
    
    @media (max-width: 576px) {
        .row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
        
        .col-md-6, .col-md-4 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .info-value, .tech-info p, .connection-info p {
            font-size: 0.9rem;
        }
    }
</style>
@section('content')
<!-- Header -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title fw-bold mb-1">Konfirmasi Tiket Open</h4>
                    <p class="text-muted mb-0">Form untuk konfirmasi tiket open pelanggan</p>
                </div>
                <div class="badge bg-label-warning fs-6">
                    <i class="bx bx-category me-1"></i>
                    {{ $kategori->kategori->nama_kategori }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <!-- Detail Tiket - Collapsible Card -->
        <div class="card mb-4">
            <div class="card-header" data-bs-toggle="collapse" data-bs-target="#detailTiketCollapse" aria-expanded="true">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-info-circle me-2"></i>
                        <h5 class="card-title mb-0 fw-semibold">Detail Tiket {{ $kategori->kategori->nama_kategori }}</h5>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="text-end me-3">
                            <small class="text-muted d-block">Dibuat oleh</small>
                            <span class="badge bg-label-info status-badge">{{ $kategori->user->name ?? '-'}}</span>
                        </div>
                        <i class="bx bx-chevron-down collapse-icon"></i>
                    </div>
                </div>
            </div>
            <div class="collapse" id="detailTiketCollapse">
                <div class="card-body">
                    <!-- Informasi Pelanggan -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="bx bx-user"></i>
                                </div>
                                <div class="info-content">
                                    <label class="info-label">Nama Pelanggan</label>
                                    <p class="info-value">{{ $tiket->customer->nama_customer }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="bx bx-map"></i>
                                </div>
                                <div class="info-content">
                                    <label class="info-label">Alamat</label>
                                    <p class="info-value">{{ $tiket->customer->alamat }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="bx bx-phone"></i>
                                </div>
                                <div class="info-content">
                                    <label class="info-label">No Telepon</label>
                                    <p class="info-value">{{ $tiket->customer->no_hp }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="bx bx-map-pin"></i>
                                </div>
                                <div class="info-content">
                                    <label class="info-label">Lokasi</label>
                                    <a href="{{ $tiket->customer->gps }}" target="_blank" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="Lihat di Google Maps" data-bs-placement="bottom">
                                        <i class="bx bx-navigation me-1"></i>Lihat Maps
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Keterangan -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Keterangan Tiket</label>
                        <div class="alert alert-light border">
                            <p class="mb-0">{{ $tiket->keterangan }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Teknis - Collapsible Card -->
        <div class="card mb-4">
            <div class="card-header" data-bs-toggle="collapse" data-bs-target="#infoTeknisCollapse" aria-expanded="true">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-cog me-2"></i>
                        <h5 class="card-title mb-0 fw-semibold">Informasi Teknis</h5>
                    </div>
                    <i class="bx bx-chevron-down collapse-icon"></i>
                </div>
            </div>
            <div class="collapse" id="infoTeknisCollapse">
                <div class="card-body">
                    <div class="row">
                        <!-- Kolom 1 -->
                        <div class="col-md-6">
                            @if($tiket->customer->media->nama_media == 'OLT')
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bxs-devices text-primary"></i>
                                        <div>
                                            <small class="text-muted">Media Koneksi</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->media->nama_media ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-terminal text-primary"></i>
                                        <div>
                                            <small class="text-muted">BTS Server</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->odp->odc->olt->server->lokasi_server ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-sitemap text-success"></i>
                                        <div>
                                            <small class="text-muted">OLT</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->odp->odc->olt->nama_lokasi ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-network-chart text-info"></i>
                                        <div>
                                            <small class="text-muted">ODC</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->odp->odc->nama_odc ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-network-chart text-primary"></i>
                                        <div>
                                            <small class="text-muted">ODP</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->odp->nama_odp ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @elseif($tiket->customer->media->nama_media == 'HTB')
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bxs-devices text-primary"></i>
                                        <div>
                                            <small class="text-muted">Media Koneksi</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->media->nama_media ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-terminal text-primary"></i>
                                        <div>
                                            <small class="text-muted">Access Point</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->transiver ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-terminal text-primary"></i>
                                        <div>
                                            <small class="text-muted">Station</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->receiver ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @elseif($tiket->customer->media->nama_media == 'Wireless')
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bxs-devices text-primary"></i>
                                        <div>
                                            <small class="text-muted">Media Koneksi</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->media->nama_media ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-terminal text-primary"></i>
                                        <div>
                                            <small class="text-muted">Access Point</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->access_point ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-terminal text-primary"></i>
                                        <div>
                                            <small class="text-muted">Station</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->station ?? '-'}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>
                        
                        <!-- Kolom 2 -->
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-server text-danger"></i>
                                        <div>
                                            <small class="text-muted">Router</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->router->nama_router }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-package text-secondary"></i>
                                        <div>
                                            <small class="text-muted">Paket</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->paket->nama_paket }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-hard-hat text-success"></i>
                                        <div>
                                            <small class="text-muted">Teknisi</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->customer->teknisi->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="tech-info">
                                        <i class="bx bx-calendar text-warning"></i>
                                        <div>
                                            <small class="text-muted">Tanggal Open</small>
                                            <p class="mb-0 fw-semibold">{{ $tiket->created_at->format('d F Y, H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informasi Koneksi -->
                    <div class="row mt-4 pt-4 border-top">
                        <div class="col-md-4 mb-3">
                            <div class="connection-info">
                                <i class="bx bx-lock-alt"></i>
                                <div>
                                    <small class="text-muted">Usersecret</small>
                                    <p class="mb-0 text-truncate">{{ $tiket->customer->usersecret }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="connection-info">
                                <i class="bx bx-key"></i>
                                <div>
                                    <small class="text-muted">Password Secret</small>
                                    <p class="mb-0 text-truncate">{{ $tiket->customer->pass_secret }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="connection-info">
                                <i class="bx bx-link-alt"></i>
                                <div>
                                    <small class="text-muted">Jenis Koneksi</small>
                                    <p class="mb-0">{{ $tiket->customer->koneksi->nama_koneksi }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="connection-info">
                                <i class="bx bx-math"></i>
                                <div>
                                    <small class="text-muted">Local Address</small>
                                    <p class="mb-0">{{ $tiket->customer->local_address ?? 'Tidak Tersedia' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="connection-info">
                                <i class="bx bx-network-chart"></i>
                                <div>
                                    <small class="text-muted">Remote Address</small>
                                    <p class="mb-0">{{ $tiket->customer->remote_address ?? 'Tidak Tersedia' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="connection-info">
                                <i class="bx bx-plug"></i>
                                <div>
                                    <small class="text-muted">Remote IP Management</small>
                                    <p class="mb-0">{{ $tiket->customer->remote }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Upgrade/Downgrade -->
        @if($kategori->kategori->nama_kategori == 'Upgrade' || $kategori->kategori->nama_kategori == 'Downgrade')
        <div class="card">
            <div class="card-header" data-bs-toggle="collapse" data-bs-target="#formTutupCollapse" aria-expanded="true">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-edit me-2"></i>
                        <h5 class="card-title mb-0">Form Tutup Tiket {{ $kategori->kategori->nama_kategori }}</h5>
                    </div>
                    <i class="bx bx-chevron-down collapse-icon"></i>
                </div>
            </div>
            <div class="collapse show" id="formTutupCollapse">
                <div class="card-body">
                    <form action="/tutup-tiket/{{ $tiket->id }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Router</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-server"></i></span>
                                    <select class="form-select" name="router" id="router" required>
                                        <option value="" selected disabled>Pilih Router</option>
                                        @forelse ($router as $r)
                                        <option value="{{ $r->id }}">{{ $r->nama_router }}</option>
                                        @empty
                                        <option value="" selected disabled>Tidak ada data router</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Paket Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-package"></i></span>
                                    <select name="paket" id="paket" required class="form-select">
                                        <option value="" selected disabled>Pilih Paket</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Usersecret</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-lock"></i></span>
                                    <input type="text" class="form-control" name="usersecret" placeholder="coba@niscala.net.id" id="usersecret">
                                </div>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Kosongkan jika tidak ada perubahan
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Password Secret</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-key"></i></span>
                                    <input type="text" class="form-control" name="pass_secret" placeholder="coba123" id="pass_secret">
                                </div>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Kosongkan jika tidak ada perubahan
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Local Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-math"></i></span>
                                    <input type="text" class="form-control" name="local_address" placeholder="192.168.1.1" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Remote Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-network-chart"></i></span>
                                    <input type="text" class="form-control" name="remote_address" id="remote_address" placeholder="192.168.1.1" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Remote IP Management</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-plug"></i></span>
                                    <input type="text" class="form-control" name="remote" id="remote" placeholder="192.168.1.1">
                                </div>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Akan terisi otomatis dari Remote Address
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
                                <i class="bx bx-arrow-back me-1"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bx bx-check-circle me-1"></i>Konfirmasi Tutup Tiket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Form Deaktivasi -->
        @if($kategori->kategori->nama_kategori == 'Deaktivasi')
        <div class="card">
            <div class="card-header" data-bs-toggle="collapse" data-bs-target="#formDeaktivasiCollapse" aria-expanded="true">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-power-off me-2"></i>
                        <h5 class="card-title mb-0">Form Konfirmasi Deaktivasi</h5>
                    </div>
                    <i class="bx bx-chevron-down collapse-icon"></i>
                </div>
            </div>
            <div class="collapse show" id="formDeaktivasiCollapse">
                <div class="card-body">
                    <form action="/konfirmasi-tiket/{{ $tiket->id }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">Modem</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bxs-devices"></i></span>
                                    <select name="modem_id" id="" class="form-select" readonly disabled>
                                        <option value="{{ $tiket->customer->perangkat_id }}">{{$tiket->customer->perangkat->nama_perangkat}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">Keterangan Kondisi Modem</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bxs-message"></i></span>
                                    <textarea name="keterangan" class="form-control" id="" cols="30" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">Foto Modem</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bxs-image"></i></span>
                                    <input type="file" name="foto" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">Tanggal Deaktivasi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bxs-calendar"></i></span>
                                    <input type="date" name="tanggal" class="form-control" value="{{ now()->format('Y-m-d') }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="javascript:window.history.back()" class="btn btn-secondary btn-sm"><i class="bx bxs-chevrons-left me-2"></i>Kembali</a>
                                    <button class="btn btn-warning btn-sm" type="submit"><i class="bx bxs-check-circle me-2"></i>Konfirmasi</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Form Gangguan --}}
        @if($kategori->kategori->nama_kategori == 'Gangguan')
        <div class="card">
            <div class="card-header" data-bs-toggle="collapse" data-bs-target="#formDeaktivasiCollapse" aria-expanded="true">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-power-off me-2"></i>
                        <h5 class="card-title mb-0">Form Konfirmasi Gangguan</h5>
                    </div>
                    <i class="bx bx-chevron-down collapse-icon"></i>
                </div>
            </div>
            <div class="collapse show" id="formDeaktivasiCollapse">
                <div class="card-body">
                    <form action="/konfirmasi-tiket-gangguan/{{ $tiket->id }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">Modem Lama</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bxs-devices"></i></span>
                                    <select name="modem_lama_id" class="form-select" readonly disabled>
                                        <option value="{{ $modemLama->perangkat->id }}">{{$modemLama->perangkat->nama_perangkat}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">Modem Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bxs-devices"></i></span>
                                    <select name="modem_baru_id" id="modem-baru" class="form-select" required>
                                        <option value="" selected>Pilih Modem Baru</option>
                                        @foreach ($perangkat as $item)
                                            <option value="{{ $item->id }}" 
                                                    data-stok="{{ $item->jumlah_stok ?? $item->stok_count ?? 0 }}"
                                                    data-mac="{{ $item->mac_address ?? '' }}"
                                                    data-sni="{{ $item->sni ?? '' }}">
                                                {{ $item->nama_perangkat }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="text-muted" id="info-stok">Pilih modem baru jika ganti modem. (Optional)</small>
                            </div>

                            {{-- Form tambahan yang muncul ketika modem dipilih --}}
                            <div id="form-tambahan" style="display: none;">
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label">MAC Address Modem Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bxs-network-chart"></i></span>
                                        <input type="text" name="mac_address" id="mac-address" class="form-control" placeholder="Masukkan MAC Address" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label">SNI Modem Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bxs-barcode"></i></span>
                                        <input type="text" name="sni" id="sni" class="form-control" placeholder="Masukkan SNI" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label class="form-label">Foto Modem</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bxs-image"></i></span>
                                    <input type="file" name="foto" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">Tanggal Konfirmasi Gangguan</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bxs-calendar"></i></span>
                                    <input type="date" name="tanggal" class="form-control" value="{{ now()->format('Y-m-d') }}" readonly>
                                </div>
                            </div>
                            <div class="col-sm-12 mb-3">
                                <label class="form-label">Keterangan Kondisi Modem</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bxs-message"></i></span>
                                    <textarea name="keterangan" class="form-control" cols="30" rows="5" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="javascript:window.history.back()" class="btn btn-secondary btn-sm"><i class="bx bxs-chevrons-left me-2"></i>Kembali</a>
                                    <button class="btn btn-warning btn-sm" type="submit"><i class="bx bxs-check-circle me-2"></i>Konfirmasi</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inisialisasi Tom Select di #paket
        var paketSelect = new TomSelect('#paket', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: 'Pilih Paket',
            allowEmptyOption: false
        });
        
        $('#router').change(function() {
            var routerId = $(this).val();
            if (!routerId) return;
            
            // Show loading state
            paketSelect.disable();
            paketSelect.clearOptions();
            paketSelect.addOption({ value: '', text: 'Memuat paket...', disabled: true });
            paketSelect.refreshOptions(false);
            
            $.ajax({
                url: '/api/paket/by-router/' + routerId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    paketSelect.clearOptions();
                    
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function(item) {
                            paketSelect.addOption({ 
                                value: item.id, 
                                text: item.nama_paket 
                            });
                        });
                        paketSelect.enable();
                    } else {
                        paketSelect.addOption({ 
                            value: '', 
                            text: 'Tidak ada paket tersedia', 
                            disabled: true 
                        });
                    }
                    
                    paketSelect.refreshOptions(false);
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    paketSelect.clearOptions();
                    paketSelect.addOption({ 
                        value: '', 
                        text: 'Error memuat data', 
                        disabled: true 
                    });
                    paketSelect.refreshOptions(false);
                }
            });
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Auto collapse semua section kecuali yang pertama saat halaman load
        setTimeout(function() {
            $('#infoTeknisCollapse').collapse('hide');
            $('#formTutupCollapse').collapse('hide');
            $('#formDeaktivasiCollapse').collapse('hide');
        }, 100);
    });
</script>
{{-- Auto Fill --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const remoteAddressInput = document.getElementById('remote_address');
        const remoteIpInput = document.getElementById('remote');
        
        let manualEdit = false;
        
        remoteAddressInput.addEventListener('input', function() {
            if (!manualEdit) {
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => {
                    remoteIpInput.value = this.value;
                }, 300);
            }
        });
        
        remoteIpInput.addEventListener('input', function() {
            manualEdit = true;
        });
        
        remoteAddressInput.addEventListener('focus', function() {
            manualEdit = false;
        });
    });
</script>

{{-- JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modemBaruSelect = document.getElementById('modem-baru');
        const formTambahan = document.getElementById('form-tambahan');
        const macAddressInput = document.getElementById('mac-address');
        const sniInput = document.getElementById('sni');
        const infoStok = document.getElementById('info-stok');
    
        modemBaruSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value) {
                // Tampilkan form tambahan
                formTambahan.style.display = 'block';
                
                // Isi data MAC Address dan SNI jika ada
                const mac = selectedOption.getAttribute('data-mac');
                const sni = selectedOption.getAttribute('data-sni');
                const stok = selectedOption.getAttribute('data-stok');
                
                macAddressInput.value = mac || '';
                sniInput.value = sni || '';
                
                // Update info stok
                infoStok.textContent = `Stok tersedia: ${stok} unit`;
                infoStok.className = stok > 0 ? 'text-success' : 'text-danger';
                
                // Jika stok 0, beri peringatan
                if (stok == 0) {
                    infoStok.innerHTML += ' <i class="bx bx-error"></i> Stok habis!';
                }
            } else {
                // Sembunyikan form tambahan jika tidak ada pilihan
                formTambahan.style.display = 'none';
                infoStok.textContent = 'Pilih modem baru jika ganti modem. (Optional)';
                infoStok.className = 'text-muted';
            }
        });
    
        // Trigger change event saat pertama kali load jika ada value yang terpilih
        if (modemBaruSelect.value) {
            modemBaruSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection