@extends('layouts.contentNavbarLayout')
@section('title', 'History Tiket Open')


@push('page-style')
<style>
    /* Minimalist Table Styles */
    .table-hover tbody tr:hover {
        background-color: #f8f9ff; /* A light purple-blue tint on hover */
        box-shadow: 0 2px 8px rgba(105, 108, 255, 0.1);
        transform: translateY(-1px);
        transition: all 0.2s ease-out;
    }
    .table th {
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    /* Customer Info Card Styles */
    .card-customer-info {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #e9ecef;
    }
    .info-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #566a7f;
    }
    .info-item i {
        font-size: 1.1rem;
        color: #696cff;
    }
    .info-item .fw-semibold {
        color: #344767;
    }
    /* Collapse Icon Animation */
    .collapse-icon {
        transition: transform 0.3s ease;
    }
    .card-header[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .card-header {
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/tiket-open">Tiket Open</a>
        </li>
        <li class="breadcrumb-item active">Riwayat Tiket</li>
    </ol>
</nav>
<div class="row">
    <!-- Customer Information Card -->
    <div class="col-12 mb-3">
        <div class="card card-customer-info">
            <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#customerInfoCollapse" aria-expanded="true">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bx bx-user-circle me-2"></i>Informasi Pelanggan {{ $customer->nama_customer ?? '-' }}
                </h5>
                <i class="bx bx-chevron-down fs-4 collapse-icon"></i>
            </div>
            <div class="collapse show" id="customerInfoCollapse">
                <div class="card-body">
                    <div class="p-4 bg-label-primary text-warning rounded">
                        {{-- *Informasi Pribadi --}}
                        <div class="row">
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-user"></i>
                                    <div>
                                        <small>Nama Pelanggan</small>
                                        <p class="fw-semibold mb-0">{{ $customer->nama_customer }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-map"></i>
                                    <div>
                                        <small>Alamat</small>
                                        <p class="fw-semibold mb-0">{{ $customer->alamat }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-phone"></i>
                                    <div>
                                        <small>No. HP</small>
                                        <p class="fw-semibold mb-0">{{ $customer->no_hp }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-package"></i>
                                    <div>
                                        <small>Paket Langganan</small>
                                        <p class="fw-semibold mb-0">{{ $customer->paket->nama_paket }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- *Informasi Server --}}
                        {{-- !Media OLT --}}
                        @if($customer->media_id == 3)
                        <div class="row">
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-terminal"></i>
                                    <div>
                                        <small>BTS Server</small>
                                        <p class="fw-semibold mb-0">{{ $customer->odp->odc->olt->server->lokasi_server ?? 'Tidak Ada' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-terminal"></i>
                                    <div>
                                        <small>OLT</small>
                                        <p class="fw-semibold mb-0">{{ $customer->odp->odc->olt->nama_lokasi ?? 'Tidak Ada' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-terminal"></i>
                                    <div>
                                        <small>ODC</small>
                                        <p class="fw-semibold mb-0">{{ $customer->odp->odc->nama_odc ?? 'Tidak Ada' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-terminal"></i>
                                    <div>
                                        <small>ODP</small>
                                        <p class="fw-semibold mb-0">{{ $customer->odp->nama_odp ?? 'Tidak Ada' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        {{-- !Media HTB --}}
                        @if($customer->media_id == 2)
                        <div class="row">
                            <div class="col-md-6 col-lg-6 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-terminal"></i>
                                    <div>
                                        <small>Transiver</small>
                                        <p class="fw-semibold mb-0">{{ $customer->transiver ?? 'Tidak Ada' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-terminal"></i>
                                    <div>
                                        <small>Reciver</small>
                                        <p class="fw-semibold mb-0">{{ $customer->receiver ?? 'Tidak Ada' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        {{-- !Media Wireless --}}
                        @if($customer->media_id == 1)
                        <div class="row">
                            <div class="col-md-6 col-lg-6 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-terminal"></i>
                                    <div>
                                        <small>Access Point</small>
                                        <p class="fw-semibold mb-0">{{ $customer->access_point ?? 'Tidak Ada' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-terminal"></i>
                                    <div>
                                        <small>Station</small>
                                        <p class="fw-semibold mb-0">{{ $customer->station ?? 'Tidak Ada' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- *Informasi Jenis Koneksi--}}
                        <div class="row">
                            <div class="col-sm-6 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-wifi"></i>
                                    <div>
                                        <small>Jenis Koneksi</small>
                                        <p class="fw-semibold mb-0">{{ $customer->koneksi->nama_koneksi ?? 'Tidak ada'}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-wifi"></i>
                                    <div>
                                        <small>Perangkat</small>
                                        <p class="fw-semibold mb-0">{{ $customer->perangkat->nama_perangkat ?? 'Tidak ada' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-wifi"></i>
                                    <div>
                                        <small>Mac Address</small>
                                        <p class="fw-semibold mb-0">{{ $customer->mac_address ?? 'Tidak ada'}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3 mb-3">
                                <div class="info-item">
                                    <i class="bx bx-wifi"></i>
                                    <div>
                                        <small>Serial Number</small>
                                        <p class="fw-semibold mb-0">{{ $customer->seri_perangkat ?? 'Tidak ada'}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket History Table -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold"><i class="bx bx-history me-2"></i>Riwayat Tiket {{$customer->nama_customer ?? '-'}}</h5>
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Kembali
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr class="fw-bold">
                            <th>No</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                            <th>Tanggal Dibuat</th>
                            <th class="text-center">Status</th>
                            <th>Ditutup Pada</th>
                            <th>Dibuat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="badge bg-label-primary">{{ $ticket->kategori->nama_kategori }}</span></td>
                            <td>{{ Str::limit($ticket->keterangan, 50) }}</td>
                            <td>
                                <span class="badge bg-label-warning">
                                    {{ $ticket->created_at->translatedFormat('d M Y, H:i') }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($ticket->status_id == 3)
                                    <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Selesai</span>
                                @else
                                    <span class="badge bg-label-warning"><i class="bx bx-time-five me-1"></i>Menunggu</span>
                                @endif
                            </td>
                            <td>
                                @if($ticket->tanggal_selesai)
                                    <span class="badge bg-label-info">
                                        {{ \Carbon\Carbon::parse($ticket->updated_at)->translatedFormat('d M Y H:i') }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $ticket->user->name }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bx bx-info-circle fs-1 text-muted mb-2"></i>
                                <h6 class="text-muted">Tidak Ada Riwayat Tiket</h6>
                                <p class="text-muted mb-0">Belum ada tiket yang pernah dibuat untuk pelanggan ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection