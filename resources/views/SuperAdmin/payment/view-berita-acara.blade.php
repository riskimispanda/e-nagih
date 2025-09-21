@extends('layouts.contentNavbarLayout')
@section('title','Halaman Berita Acara')

@section('content')
<div class="row">
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
                    <a href="javascript:window.history.back()" data-bs-toggle="tooltip" data-bs-placement="top" title="Kembali">
                        <button class="btn btn-secondary btn-sm"><i class="bx bx-arrow-back fs-5 me-2"></i> Kembali</button>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="beritaAcaraTable">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Pelanggan</th>
                                <th>Paket</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @forelse ($data as $item)
                            <tr class="text-center" data-search="{{ strtolower($item->nama_customer . ' ' . $item->alamat . ' ' . $item->paket->nama_paket) }}">
                                <td>{{ $no++ }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->nama_customer }}</div>
                                    <small class="text-muted">
                                        <i class="bx bx-map-pin me-1"></i>
                                        {{ Str::limit($item->alamat, 30) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ $item->paket->nama_paket }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->status_id == 3)
                                    <span class="badge bg-label-success fw-bold">AKTIF</span>
                                    @elseif($item->status_id == 9)
                                    <span class="badge bg-label-danger fw-bold">BLOKIR</span>
                                    @elseif($item->status_id == 16)
                                    <span class="badge bg-label-warning fw-bold">Request BA</span>
                                    @elseif($item->status_id == 17)
                                    <span class="badge bg-label-secondary fw-bold">Aktivasi Sementara</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($item->status_id == 16 || $item->status_id == 17)
                                        <button class="btn btn-outline-danger btn-sm" disabled>
                                            <i class="bx bx-clipboard"></i>
                                        </button>
                                        @else
                                        <a href="/buat-berita-acara/{{ $item->id }}">
                                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Buat BA {{ $item->nama_customer }}">
                                                <i class="bx bx-clipboard"></i>
                                            </button>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada data pelanggan</td>
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