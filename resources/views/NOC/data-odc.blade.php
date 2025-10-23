@extends('layouts.contentNavbarLayout')
@section('title', 'Data ODC')
@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Data ODC</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Lokasi ODC</h4>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modalTambahOdc">
                        <i class="bx bxs-add-to-queue me-2"></i>Tambah ODC
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-4">
                            <form action="{{ route('odc') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan nama ODC atau nama OLT..." value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Data ODC</th>
                                    <th>Lokasi ODC</th>
                                    <th>Total ODP</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($odc as $od)
                                    <tr class="text-uppercase">
                                        <td class="text-center">{{ $loop->iteration + ($odc->currentPage() - 1) * $odc->perPage() }}</td>
                                        <td class="fw-semibold">
                                            <i class="bx bx-terminal me-1 text-primary"></i>{{ $od->nama_odc }}
                                        </td>
                                        @php
                                            $gps = $od->gps;
                                            $isLink = $gps && Str::startsWith($gps, ['http://', 'https://']);
                                            $url = $gps ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                                        @endphp

                                        <td class="text-center">
                                            <a href="{{ $url }}" {{ $url != '#' ? 'target=_blank' : '' }} data-bs-toggle="tooltip" title="Lokasi ODC" data-bs-placement="bottom">
                                                <i class="bx bx-map {{ $url == '#' ? 'text-muted' : 'text-primary' }}"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">
                                                {{ $od->odp_count }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="#" class="edit-odc-btn" data-id="{{ $od->id }}" data-bs-toggle="tooltip" title="Edit ODC" data-bs-placement="bottom">
                                                    <i class="bx bx-edit text-warning"></i>
                                                </a>|
                                                <a href="/hapus/odc/{{ $od->id }}" data-bs-toggle="tooltip" title="Hapus ODC" data-bs-placement="bottom">
                                                    <i class="bx bx-trash text-danger"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data yang cocok dengan pencarian Anda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if ($odc->hasPages())
                            <div class="d-flex justify-content-end mt-3">
                                {{ $odc->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="modalTambahOdc" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-terminal me-2"></i>
                    <h5 class="modal-title">Tambah ODC</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/odc/add" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi OLT</label>
                                <select name="olt" id="" class="form-select mb-3">
                                    <option value="" selected disabled>Pilih Lokasi OLT</option>
                                    @foreach ($lokasi as $ol)
                                        <option value="{{ $ol->id }}">{{ $ol->nama_lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Nama ODC</label>
                                <input type="text" class="form-control mb-3" name="nama_odc" placeholder="ODC Dondong 2" required />
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODC</label>
                                <input type="text" class="form-control" name="gps" placeholder="https://maps.google.com/... atau -1.0269916,110.48579129">
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

    {{-- Modal Edit --}}
    <div class="modal fade" id="modalEditOdc" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-terminal me-2"></i>
                    <h5 class="modal-title">Edit ODC</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOltForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Nama ODC</label>
                                <input type="text" class="form-control mb-3" name="nama_odc" id="edit_nama_lokasi" required>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi OLT</label>
                                <select name="olt" id="edit_id_server" class="form-select mb-3" required>
                                    <option value="" selected disabled>Pilih OLT</option>
                                    @foreach ($lokasi as $o)
                                        <option value="{{ $o->id }}">{{ $o->nama_lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODC</label>
                                <input type="text" name="gps" placeholder="https://maps.google.com/... atau -1.0269916,110.48579129" class="form-control" id="edit_gps">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.edit-odc-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    fetch(`/edit/odc/${id}`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('edit_nama_lokasi').value = data.nama_odc || '';
                            document.getElementById('edit_id_server').value = data.lokasi_id || '';
                            document.getElementById('edit_gps').value = data.gps || 'Lokasi belum di atur';
                            document.getElementById('editOltForm').action = `/update/odc/${id}`;
                            var modal = new bootstrap.Modal(document.getElementById('modalEditOdc'));
                            modal.show();
                        });
                });
            });
        });
    </script>
@endsection
