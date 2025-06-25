@extends('layouts.contentNavbarLayout')
@section('title', 'OLT Data')
@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Data OLT</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Lokasi OLT</h4>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modalTambahOlt">
                        <i class="bx bxs-add-to-queue me-2"></i>Tambah OLT
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Data OLT</th>
                                    <th width="5%">Total ODC</th>
                                    <th width="5%">Total ODP</th>
                                    <th width="20%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse ($lokasi as $index => $olt)
                                    <tr class="text-uppercase">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $olt->nama_lokasi }}</strong></td>
                                        <td class="text-center">
                                            {{ $odc->where('lokasi_id', $olt->id)->count() }}
                                        </td>
                                        <td class="text-center">
                                            {{ $odp->where('odc_id', $olt->id)->count() }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-warning btn-sm">
                                                <i class="bx bx-edit-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="modalTambahOlt" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-terminal me-2"></i>
                    <h5 class="modal-title">Tambah OLT
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/olt/add" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Nama Lokasi OLT</label>
                                <input type="text" class="form-control mb-3" name="olt" required>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi Server</label>
                                <select name="lokasi_server" id="" class="form-select">
                                    <option value="" selected disabled>Lokasi Server</option>
                                    @foreach ($server as $s)
                                        <option value="{{ $s->id }}">{{ $s->lokasi_server }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
