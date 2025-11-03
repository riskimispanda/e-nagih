@extends('layouts.contentNavbarLayout')

@section('title', 'Pengeluaran Global')
<style>
    /* Modal Responsive Styles */
    .modal-content {
        border: none;
        box-shadow: 0 0.25rem 1.5rem rgba(0, 0, 0, 0.15);
        border-radius: 0.75rem;
        overflow: hidden;
        animation: modalFadeIn 0.3s ease;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .modal-dialog {
        margin: 1rem auto;
        transition: all 0.3s ease;
    }
    
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }
    
    .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    
    .modal-footer {
        background-color: #f8f9fa;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1rem 1.5rem;
    }
    
    /* Mobile Responsive */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }
        
        .modal-content {
            border-radius: 0.5rem;
        }
        
        .modal-header,
        .modal-footer {
            padding: 1rem;
        }
        
        .modal-body {
            padding: 1rem;
            max-height: calc(100vh - 150px);
        }
        
        .modal-title {
            font-size: 1.1rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    }
    
    /* Form Enhancements */
    .form-label {
        color: #566a7f;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .form-control,
    .form-select {
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        padding: 0.625rem 0.875rem;
        font-size: 0.9375rem;
        transition: all 0.2s ease;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }
    
    .input-group-text {
        background-color: #f5f5f9;
        border: 1px solid #d9dee3;
        color: #566a7f;
        font-weight: 500;
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Button Styles */
    .btn-primary {
        background-color: #696cff;
        border-color: #696cff;
        box-shadow: 0 0.125rem 0.25rem rgba(105, 108, 255, 0.4);
    }
    
    .btn-primary:hover {
        background-color: #5f61e6;
        border-color: #5f61e6;
        transform: translateY(-1px);
        box-shadow: 0 0.25rem 0.5rem rgba(105, 108, 255, 0.4);
    }
    
    .btn-outline-secondary {
        color: #8592a3;
        border-color: #8592a3;
    }
    
    .btn-outline-secondary:hover {
        background-color: #8592a3;
        border-color: #8592a3;
        color: #fff;
    }
    
    /* Scrollbar Styling */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .modal-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            
            <!-- Card Header -->
            <div class="card-header border-bottom mb-4">
                <h4 class="card-title fw-bold">Data Pengeluaran</h4>
                <small class="card-subtitle text-muted">Kelola dan pantau data pengeluaran perusahaan</small>
            </div>
            
            <div class="card-body">
                
                <!-- Filter -->
                <div class="row mb-4 g-3">
                    <div class="col-sm-4">
                        <label class="form-label">Filter Bulan</label>
                        <select name="month" id="monthFilter" class="form-select">
                            <option value="all" {{ request('month') == 'all' || !request('month') ? 'selected' : '' }}>Semua Bulan</option>
                            <option value="1" {{ request('month') == '1' ? 'selected' : '' }}>Januari</option>
                            <option value="2" {{ request('month') == '2' ? 'selected' : '' }}>Februari</option>
                            <option value="3" {{ request('month') == '3' ? 'selected' : '' }}>Maret</option>
                            <option value="4" {{ request('month') == '4' ? 'selected' : '' }}>April</option>
                            <option value="5" {{ request('month') == '5' ? 'selected' : '' }}>Mei</option>
                            <option value="6" {{ request('month') == '6' ? 'selected' : '' }}>Juni</option>
                            <option value="7" {{ request('month') == '7' ? 'selected' : '' }}>Juli</option>
                            <option value="8" {{ request('month') == '8' ? 'selected' : '' }}>Agustus</option>
                            <option value="9" {{ request('month') == '9' ? 'selected' : '' }}>September</option>
                            <option value="10" {{ request('month') == '10' ? 'selected' : '' }}>Oktober</option>
                            <option value="11" {{ request('month') == '11' ? 'selected' : '' }}>November</option>
                            <option value="12" {{ request('month') == '12' ? 'selected' : '' }}>Desember</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="kategoriFilter" class="form-label">Kategori</label>
                        <select class="form-select" id="kategoriFilter">
                            <option value="" selected>Semua Kategori</option>
                            @foreach ($kategoriPengeluaran as $kategori)
                                <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>
                                    {{ ucfirst($kategori) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="searchInput" class="form-label">Search</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari...">
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="row mb-5 g-3">
                    
                    <div class="col-lg-6 col-md-6">
                        <div class="card shadow-sm border-0 bg-info hover-shadow" style="transition: all 0.3s ease;">
                            <div class="card-body p-3 p-sm-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-info bg-opacity-25 rounded-2 p-2">
                                        <i class="bx bx-money fs-3 text-white"></i>
                                    </div>
                                    <h6 class="card-title mb-0 text-white fw-bold ms-2">Total Saldo</h6>
                                </div>
                                <h3 class="mb-1 text-white fw-bold" id="totalPengeluaran">
                                    Rp {{ number_format($total, 0, ',', '.') }}
                                </h3>
                                <small class="text-white text-opacity-85">Seluruh saldo Pendapatan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="card shadow-sm border-0 bg-secondary hover-shadow" style="transition: all 0.3s ease;">
                            <div class="card-body p-3 p-sm-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-secondary bg-opacity-25 rounded-2 p-2">
                                        <i class="bx bx-calendar fs-3 text-white"></i>
                                    </div>
                                    <h6 class="card-title mb-0 text-white fw-bold ms-2">Saldo Bulan Ini</h6>
                                </div>
                                <h3 class="mb-1 text-white fw-bold" id="totalPengeluaran">
                                    Rp {{ number_format($saldoBulanIni, 0, ',', '.') }}
                                </h3>
                                <small class="text-white text-opacity-85">Saldo Pendapatan Bulan {{date('m')}}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 bg-primary hover-shadow" style="transition: all 0.3s ease;">
                            <div class="card-body p-3 p-sm-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-primary bg-opacity-25 rounded-2 p-2">
                                        <i class="bx bx-wallet fs-3 text-white"></i>
                                    </div>
                                    <h6 class="card-title mb-0 text-white fw-bold ms-2">Total Pengeluaran</h6>
                                </div>
                                <h3 class="mb-1 text-white fw-bold" id="totalPengeluaran">
                                    Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                                </h3>
                                <small class="text-white text-opacity-85">Seluruh pengeluaran</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 bg-success hover-shadow" style="transition: all 0.3s ease;">
                            <div class="card-body p-3 p-sm-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-success bg-opacity-25 rounded-2 p-2">
                                        <i class="bx bx-calendar fs-3 text-white"></i>
                                    </div>
                                    <h6 class="card-title mb-0 text-white fw-bold ms-2">Hari Ini</h6>
                                </div>
                                <h3 class="mb-1 text-white fw-bold" id="totalMasuk">
                                    Rp {{ number_format($dailyPengeluaran, 0, ',', '.') }}
                                </h3>
                                <small class="text-white text-opacity-85">Pengeluaran hari ini</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 bg-warning hover-shadow" style="transition: all 0.3s ease;">
                            <div class="card-body p-3 p-sm-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-warning bg-opacity-25 rounded-2 p-2">
                                        <i class="bx bx-line-chart fs-3 text-white"></i>
                                    </div>
                                    <h6 class="card-title mb-0 text-white fw-bold ms-2">Bulan Ini</h6>
                                </div>
                                <h3 class="mb-1 text-white fw-bold" id="totalPengeluaranBersih">
                                    Rp {{ number_format($mounthlyPengeluaran, 0, ',', '.') }}
                                </h3>
                                <small class="text-white text-opacity-85">Pengeluaran bulan ini</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="/request/hapus/pengeluaran" data-bs-toggle="tooltip" title="Request Konfirmasi" data-bs-placement="bottom"> 
                            <div class="card shadow-sm border-0 bg-danger hover-shadow" style="transition: all 0.3s ease;">
                                <div class="card-body p-3 p-sm-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-md bg-danger bg-opacity-25 rounded-2 p-2">
                                            <i class="bx bx-line-chart fs-3 text-white"></i>
                                        </div>
                                        <h6 class="card-title mb-0 text-white fw-bold ms-2">Request Konfirmasi</h6>
                                    </div>
                                    <h3 class="mb-1 text-white fw-bold" id="totalPengeluaranBersih">
                                        {{ $totalRequest }}
                                    </h3>
                                    <small class="text-white text-opacity-85">Total Request Hapus Pengeluaran</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <hr>
                <!-- Table -->
                <div class="d-flex justify-content-between mb-4 gap-4">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="col-sm-4 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalScrollable">
                                    <i class="bx bx-plus"></i>Tambah
                                </button>
                            </div>
                            <div class="col-sm-4">
                                <!-- Dropdown Export Excel -->
                                <div class="dropdown">
                                    <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-export me-1"></i> Export Excel
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.all') }}">Semua Data</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '1']) }}">Januari</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '2']) }}">Februari</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '3']) }}">Maret</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '4']) }}">April</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '5']) }}">Mei</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '6']) }}">Juni</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '7']) }}">Juli</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '8']) }}">Agustus</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '9']) }}">September</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '10']) }}">Oktober</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '11']) }}">November</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pengeluaran.export.month', ['month' => '12']) }}">Desember</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="pengeluaranTable">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jenis Pengeluaran</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                                <th>Jenis Kas</th>
                                <th>Status</th>
                                <th>Admin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengeluarans as $key => $pengeluaran)
                            <tr class="text-center">
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ \Carbon\Carbon::parse($pengeluaran->tanggal_pengeluaran)->format('d-M-Y') }}
                                    </span>
                                </td>
                                <td>{{ $pengeluaran->jenis_pengeluaran }}</td>
                                <td>{{ $pengeluaran->keterangan }}</td>
                                <td data-amount="{{ $pengeluaran->jumlah_pengeluaran }}">
                                    <span class="badge bg-label-warning">
                                        Rp {{ number_format($pengeluaran->jumlah_pengeluaran, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary">
                                        {{ $pengeluaran->kas->jenis_kas ?? '-'}}
                                    </span>
                                </td>
                                <td>
                                    @if ($pengeluaran->status_id == 1)
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Menunggu Konfirmasi Penghapusan</span>
                                    @elseif ($pengeluaran->status_id == 2)
                                    <span class="badge bg-success bg-opacity-10 text-success">Approved</span>
                                    @elseif ($pengeluaran->status_id == 3)
                                    <span class="badge bg-success bg-opacity-10 text-success">Berhasil</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        {{ $pengeluaran->user->name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="/edit-pengeluaran/{{ $pengeluaran->id }}">
                                            <button class="btn btn-outline-warning btn-sm mb-1" title="Edit" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#deletePengeluaran{{ $pengeluaran->id }}" title="Hapus" data-bs-placement="bottom">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
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
                <!-- Menjadi ini: -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div id="customPaginationInfo" class="text-muted small">
                        Menampilkan {{ $pengeluarans->count() }} dari {{ $pengeluarans->total() }} records
                    </div>
                    <div class="pagination-container">
                        {{ $pengeluarans->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div> <!-- card-body -->
        </div> <!-- card -->
    </div> <!-- col -->
</div> <!-- row -->

{{-- Modal Add Pengeluaran --}}
<div class="modal fade" id="modalScrollable" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title" id="modalScrollableTitle">Tambah Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/pengeluaran/tambah" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body border-bottom border-top mt-2 mb-2">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label class="form-label">Rencana Anggaran Biaya</label>
                            <select name="rab_id" id="select-rab" class="form-select">
                                <option value="">Pilih RAB</option>
                                @foreach ($rab as $item)
                                <option value="{{ $item->id }}" data-anggaran="{{ $item->jumlah_anggaran }}">
                                    {{ $item->kegiatan }}
                                    {{ $item->item ? "({$item->item} item" : '' }}
                                    {{ $item->item && $item->keterangan ? ' | ' : '' }}
                                    {{ $item->keterangan ? "Ket: {$item->keterangan}" : '' }}
                                    {{ $item->item ? ')' : '' }}
                                    {{ $item->tahun_anggaran ? " | Tahun: {$item->tahun_anggaran}" : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label for="kasSelect" class="form-label fw-medium">Jenis Kas</label>
                            <select name="kas_id" id="kasSelect" class="form-select">
                                <option value="" selected disabled>Pilih Jenis Kas</option>
                                @foreach ($kas as $item)
                                <option value="{{ $item->id }}">{{$item->jenis_kas}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-4" id="jumlah-item-group" style="display: none;">
                                <label class="form-label fw-medium mb-2">
                                    <i class="bx bx-cart me-1"></i>Jumlah Item
                                </label>
                                <input type="number" name="item" class="form-control" placeholder="100">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-4" id="anggaran-info" style="display: none;">
                                <label class="form-label fw-medium mb-2">
                                    <i class="bx bx-money me-1"></i>Anggaran RAB
                                </label>
                                <input type="text" id="anggaran-amount" class="form-control" value="Rp 0" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label for="tanggalPengeluaran" class="form-label fw-medium">Tanggal</label>
                            <input type="date" class="form-control" id="tanggalPengeluaran" required name="tanggalPengeluaran">
                        </div>
                        <div class="col-sm-6">
                            <label for="jenisPengeluaran" class="form-label fw-medium">Jenis Pengeluaran</label>
                            <input name="jenisPengeluaran" type="text" class="form-control" id="jenisPengeluaran" placeholder="Contoh: Operasional, Gaji, Lainnya" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label for="keterangan" class="form-label fw-medium">Keterangan</label>
                            <textarea name="keterangan" class="form-control" id="keterangan" rows="3" placeholder="Masukkan keterangan pengeluaran..." required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label for="jumlahPengeluaran" class="form-label fw-medium">Jumlah</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" required placeholder="Masukkan jumlah pengeluaran" id="jumlahPengeluaran" oninput="formatRupiah(this)">
                            </div>
                            <input name="jumlahPengeluaran" type="text" class="form-control mt-1" id="jumlahPengeluaranNumeric" readonly placeholder="0" hidden>
                        </div>
                        <div class="col-sm-6">
                            <label for="metodePengeluaran" class="form-label fw-medium">Metode Pengeluaran</label>
                            <select class="form-select" id="metodePengeluaran" required name="metodePengeluaran">
                                <option selected disabled>Pilih Metode</option>
                                @foreach ($metodes as $metode)
                                <option value="{{ $metode->nama_metode }}">{{ $metode->nama_metode }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label for="buktiPengeluaran" class="form-label fw-medium">Bukti Pengeluaran</label>
                            <input name="buktiPengeluaran" type="file" class="form-control" id="buktiPengeluaran" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                Format file: JPG, PNG, PDF. Maksimal ukuran 2MB.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
{{-- Modal Request Hapus Pengeluaran --}}
@foreach ($pengeluarans as $pengeluaran)
<div class="modal fade" id="deletePengeluaran{{ $pengeluaran->id }}" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <input type="text" hidden value="{{ $pengeluaran->id }}">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title" id="modalScrollableTitle"><i class="bx bx-trash me-1 text-danger"></i>Hapus Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/pengeluaran/hapus/{{ $pengeluaran->id }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body border-bottom border-top mt-2 mb-2">
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label class="form-label fw-medium">Alasan<span class="text-danger">*</span></label>
                            <textarea name="alasan" class="form-control" id="alasan" rows="3" placeholder="Masukkan alasan ingin menghapus pengeluaran..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer gap-2 mt-6">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm">Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    function formatRupiah(input) {
        let value = input.value.replace(/\D/g, '');
        const numericInput = document.getElementById('jumlahPengeluaranNumeric');
        if (numericInput) {
            numericInput.value = value;
        }
        if (value !== '') {
            value = parseInt(value);
            input.value = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
        } else {
            if (numericInput) {
                numericInput.value = '';
            }
        }
    }
</script>

@section('page-script')
<script>
    $(document).ready(function() {
        // Initialize TomSelect
        new TomSelect('#select-rab',{
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        let searchTimeout;

        // Fungsi untuk memuat data pengeluaran
        function loadPengeluaran(page = 1) {
            $.ajax({
                url: '{{ route("pengeluaran.ajax-filter") }}',
                type: 'GET',
                data: {
                    month: $('#monthFilter').val(),
                    kategori: $('#kategoriFilter').val(),
                    search: $('#searchInput').val(),
                    page: page
                },
                success: function(response) {
                    // Update tabel dan pagination
                    $('#pengeluaranTable tbody').html(response.table);
                    $('.pagination-container').html(response.pagination);
                    $('#customPaginationInfo').text(`Menampilkan ${response.count} dari ${response.total} records`);
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                }
            });
        }

        // Event listener untuk semua filter
        $('#monthFilter, #kategoriFilter').on('change', function() {
            loadPengeluaran(1);
        });

        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                loadPengeluaran(1);
            }, 500); // Debounce to avoid too many requests
        });
        
        // Event listener untuk pagination
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            loadPengeluaran(page);
        });
    });
</script>
@endsection
