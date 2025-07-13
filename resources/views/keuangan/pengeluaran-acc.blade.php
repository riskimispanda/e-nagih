@extends('layouts.contentNavbarLayout')

@section('title', 'Request Hapus Pengeluaran')

@section('content')

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard" class="text-decoration-none">Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/pengeluaran/global" class="text-decoration-none">Pengeluaran Global</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Data Request Hapus Pengeluaran</li>
    </ol>
</nav>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom mb-4">
                <h4 class="card-title fw-bold">Request Hapus Pengeluaran</h4>
                <small class="card-subtitle text-muted">Kelola dan pantau data pengeluaran perusahaan</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="pengeluaranTable">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jenis Pengeluaran</th>
                                <th>Alasan</th>
                                <th>Jumlah</th>
                                <th>Admin</th>
                                <th>Status</th>
                                @if(auth()->user()->roles_id == 1)
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengeluarans as $key => $pengeluaran)
                            <tr class="text-center">
                                <td>{{ $key + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($pengeluaran->tanggal_pengeluaran)->format('d-M-Y') }}</td>
                                <td>{{ $pengeluaran->jenis_pengeluaran }}</td>
                                <td>{{ $pengeluaran->alasan }}</td>
                                <td data-amount="{{ $pengeluaran->jumlah_pengeluaran }}">
                                    Rp {{ number_format($pengeluaran->jumlah_pengeluaran, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        {{ $pengeluaran->user->name }}
                                    </span>
                                </td>
                                <td>
                                    @if ($pengeluaran->status_id == 1)
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Menunggu Konfirmasi</span>
                                    @elseif ($pengeluaran->status_id == 2)
                                    <span class="badge bg-success bg-opacity-10 text-success">Approved</span>
                                    @elseif ($pengeluaran->status_id == 3)
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Tolak</span>
                                    @endif
                                </td>
                                @if(auth()->user()->roles_id == 1)
                                <td>
                                    <a href="/konfirmasi/hapus/pengeluaran/{{ $pengeluaran->id }}" data-bs-toggle="tooltip" title="Konfirmasi" data-bs-placement="bottom">
                                        <button class="btn btn-success btn-sm mb-1">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </a>
                                    <a href="/tolak/hapus/pengeluaran/{{ $pengeluaran->id }}" data-bs-toggle="tooltip" title="Tolak" data-bs-placement="bottom">
                                        <button class="btn btn-danger btn-sm mb-1">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    </a>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                        <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                                        <p class="text-muted mb-0">Belum ada Transaksi</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection