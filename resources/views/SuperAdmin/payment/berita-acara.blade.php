@extends('layouts.contentNavbarLayout')
@section('title','Berita Acara')

@section('content')
<div class="row">
    <div class="col-lg-6 col-md-12 col-6 mb-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                    <div class="avatar flex-shrink-0 bg-warning bg-opacity-10 rounded d-flex justify-content-center align-items-center" style="width:50px; height:50px;">
                        <i class="bx bx-user fs-4 text-warning"></i>
                    </div>
                </div>
                <p class="mb-1 fw-bold">Jumlah Pelanggan</p>
                <h4 class="card-title mb-3">{{$countCustomer}}</h4>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-12 col-6 mb-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                    <div class="avatar flex-shrink-0 bg-info bg-opacity-10 rounded d-flex justify-content-center align-items-center" style="width:50px; height:50px;">
                        <i class="bx bx-clipboard fs-4 text-info"></i>
                    </div>
                </div>
                <p class="mb-1 fw-bold">Berita Acara</p>
                <h4 class="card-title mb-3">{{ $countBerita }}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title fw-bold mb-1">Data Berita Acara</h4>
                <small class="card-subtitle text-muted">Kelola data pelanggan</small>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <div class="input-group" style="width: 300px;">
                        <span class="input-group-text"><i class="bx bx-search-alt"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari pelanggan...">
                    </div>
                    <button class="btn btn-outline-secondary" type="button" id="refreshBtn">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-start mb-4">
                    <a href="/customer-berita-acara" data-bs-toggle="tooltip" data-bs-placement="top" title="Buat Berita Acara">
                        <button class="btn btn-outline-danger btn-sm"><i class="bx bx-clipboard me-2"></i>Buat BA</button>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="beritaAcaraTable">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Pelanggan</th>
                                <th>Paket</th>
                                <th>Status Koneksi</th>
                                <th>Status Tagihan</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @forelse ($data as $item)
                            <tr class="text-center" data-search="{{ strtolower($item->customer->nama_customer . ' ' . $item->customer->alamat . ' ' . $item->customer->paket->nama_paket) }}">
                                <td>{{ $no++ }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->customer->nama_customer }}</div>
                                    <small class="text-muted">
                                        <i class="bx bx-map-pin me-1"></i>
                                        {{ Str::limit($item->customer->alamat, 30) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ $item->customer->paket->nama_paket }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->customer->status_id == 3)
                                    <span class="badge bg-label-success fw-bold">AKTIF</span>
                                    @elseif($item->customer->status_id == 9)
                                    <span class="badge bg-label-danger fw-bold">BLOKIR</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->invoice->status_id == 7)
                                    <span class="badge bg-label-danger fw-bold">Belum Bayar</span>
                                    @elseif($item->invoice->status_id == 8)
                                    <span class="badge bg-label-success fw-bold">Sudah Bayar</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-info fw-bold">{{ $item->tanggal_ba->format('d-M-Y H:i:s') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-warning fw-bold">{{ $item->tanggal_selesai_ba->format('d-M-Y H:i:s') }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="/preview/berita-acara/{{ $item->customer->id }}">
                                            <button class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" title="Lihat Berita Acara">
                                                <i class="bx bx-info-circle"></i>
                                            </button>
                                        </a>
                                        <a href="/hapus-berita-acara/{{ $item->id }}">
                                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" title="Hapus Berita Acara">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                        <i class="bx bx-user-x fs-1 text-muted mb-2"></i>
                                        <span class="fw-semibold text-secondary">Tidak ada data pelanggan</span>
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

@section('page-script')
<script>
    $(document).ready(function() {
        // Search functionality
        $('#searchInput').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            const rows = $('#beritaAcaraTable tbody tr');
            
            rows.each(function() {
                const row = $(this);
                const searchData = row.data('search');
                
                if (searchData.includes(searchTerm)) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        });
        
        // Refresh button
        $('#refreshBtn').on('click', function() {
            $('#searchInput').val('');
            $('#beritaAcaraTable tbody tr').show();
        });
    });
</script>
@endsection