
@extends('layouts.contentNavbarLayout')

@section('title', 'Transaksi Kas Besar')
@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/kas" class="text-decoration-none">
            </i>Data Kas
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="#" active>Transaksi Kas Besar</a>
    </li>
</ol>
</nav>
<div class="row">
    
    <div class="col-12">
        <div class="card-header mb-4">
            <h4 class="card-title fw-bold">Transaksi Kas Besar</h4>
            <small class="text-muted">Record Transaksi Kas Besar</small>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="row mb-5">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="form-label mb-2">Tanggal</label>
                            <input type="date" class="form-control">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="" class="form-label mb-2">Search</label>
                            <input type="text" class="form-control" placeholder="Cari Transaksi">
                        </div>
                    </div>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Debit</th>
                                <th>Kredit</th>
                                <th>Admin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($kas as $item)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_kas)->translatedFormat('d F Y') }}</td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        {{ $item->keterangan }}
                                    </span>
                                </td>
                                <td>Rp. {{number_format($item->debit, 0, ',', '.')}}</td>
                                <td>Rp. {{number_format($item->kredit, 0, ',', '.')}}</td>
                                <td>
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="badge bg-danger bg-opacity-10 text-danger mb-1">
                                            <strong>{{ strtoupper(optional($item->user)->name ?? '-')}} </strong>
                                        </span>
                                        <small class="text-muted">{{ $item->user->roles->name ?? '-'}}</small>
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-info btn-sm mb-2" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Detail">
                                        <i class="bx bx-info-circle"></i>
                                    </a>
                                    <a href="#" class="btn btn-warning btn-sm mb-2" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                </td>
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
                <div class="mt-5">
                    {{ $kas->links() }}
                </div>
            </div>
        </div>
        
    </div>
</div>

@endsection