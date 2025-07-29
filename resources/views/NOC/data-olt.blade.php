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
                        <table class="table table-hover">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Data OLT</th>
                                    <th>Lokasi OLT</th>
                                    <th>Total ODC</th>
                                    <th>Total ODP</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lokasi as $index => $olt)
                                    <tr class="text-uppercase">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="fw-semibold">
                                            <i class="bx bx-terminal me-1 text-primary"></i>{{ $olt->nama_lokasi }}
                                        </td>
                                        @php
                                            $gps = $olt->gps;
                                            $isLink = $gps && Str::startsWith($gps, ['http://', 'https://']);
                                            $url = $gps ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                                        @endphp

                                        <td class="text-center">
                                            <a href="{{ $url }}" {{ $url != '#' ? 'target=_blank' : '' }} data-bs-toggle="tooltip" title="Lokasi OLT" data-bs-placement="bottom">
                                                <i class="bx bx-map {{ $url == '#' ? 'text-muted' : 'text-primary' }}"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">
                                                {{ $odc->where('lokasi_id', $olt->id)->count() }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">
                                                {{ $odp->where('odc_id', $olt->id)->count() }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="#" class="edit-olt-btn" data-id="{{ $olt->id }}" data-bs-toggle="tooltip" title="Edit OLT" data-bs-placement="bottom">
                                                    <i class="bx bx-edit text-warning"></i>
                                                </a>|
                                                <a href="/hapus/olt/{{ $olt->id }}" data-bs-toggle="tooltip" title="Hapus OLT" data-bs-placement="bottom">
                                                    <i class="bx bx-trash text-danger"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data</td>
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
                                <label class="form-label">Nama OLT</label>
                                <input type="text" class="form-control mb-3" name="olt" required>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Server</label>
                                <select name="lokasi_server" id="" class="form-select mb-3">
                                    <option value="" selected disabled>Pilih Server</option>
                                    @foreach ($server as $s)
                                        <option value="{{ $s->id }}">{{ $s->lokasi_server }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi OLT</label>
                                <input type="text" class="form-control" name="gps" required placeholder="https://maps.google.com/... atau -1.0269916,110.48579129">
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
    <div class="modal fade" id="modalEditOlt" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-terminal me-2"></i>
                    <h5 class="modal-title">Edit OLT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOltForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Nama OLT</label>
                                <input type="text" class="form-control mb-3" name="nama_lokasi" id="edit_nama_lokasi" required>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Server</label>
                                <select name="id_server" id="edit_id_server" class="form-select mb-3" required>
                                    <option value="" selected disabled>Pilih Server</option>
                                    @foreach ($server as $s)
                                        <option value="{{ $s->id }}">{{ $s->lokasi_server }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi OLT</label>
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
            document.querySelectorAll('.edit-olt-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    fetch(`/edit/olt/${id}`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('edit_nama_lokasi').value = data.nama_lokasi || '';
                            document.getElementById('edit_id_server').value = data.id_server || '';
                            document.getElementById('edit_gps').value = data.gps || 'Lokasi belum di atur';
                            document.getElementById('editOltForm').action = `/update/olt/${id}`;
                            var modal = new bootstrap.Modal(document.getElementById('modalEditOlt'));
                            modal.show();
                        });
                });
            });
        });
    </script>
@endsection
