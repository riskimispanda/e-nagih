@extends('layouts.contentNavbarLayout')
@section('title', 'Data Server')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Data Server</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Data Server</h4>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modalTambahOlt">
                        <i class="bx bxs-add-to-queue me-2"></i>Tambah Server
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Lokasi Server</th>
                                    <th>Maps</th>
                                    <th>IP Address</th>
                                    <th width="20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($server as $index => $s)
                                    <tr class="text-uppercase">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="fw-semibold">
                                            <i class="bx bx-server me-1 text-primary"></i>{{ $s->lokasi_server }}
                                        </td>
                                        @php
                                            $gps = $s->gps;
                                            $isLink = $gps && Str::startsWith($gps, ['http://', 'https://']);
                                            $url = $gps ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                                        @endphp

                                        <td class="text-center">
                                            <a href="{{ $url }}" {{ $url != '#' ? 'target=_blank' : '' }} data-bs-toggle="tooltip" title="Lokasi Server" data-bs-placement="bottom">
                                                <i class="bx bx-map {{ $url == '#' ? 'text-muted' : 'text-primary' }}"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-ip">
                                                <i class="bx bx-globe me-1 text-primary"></i>{{ $s->ip_address }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="#" class="edit-server-btn" data-id="{{ $s->id }}" data-bs-toggle="tooltip" title="Edit Server" data-bs-placement="bottom">
                                                    <i class="bx bx-edit text-warning"></i>
                                                </a>|
                                                <a href="/hapus/server/{{ $s->id }}" data-bs-toggle="tooltip" title="Hapus Server" data-bs-placement="bottom">
                                                    <i class="bx bx-trash text-danger"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data</td>
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
                    <i class="bx bxs-server me-2"></i>
                    <h5 class="modal-title">Tambah Server</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/server/add" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Nama Server</label>
                                <input type="text" class="form-control mb-2" name="lokasi_server" required placeholder="Server Tlogosudang">
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">IP Address</label>
                                <input type="text" class="form-control mb-2" name="ip" required placeholder="192.168.1.1">
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi Server</label>
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

    {{-- Modal Edit Server --}}
    <div class="modal fade" id="modalEditServer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-server me-2"></i>
                    <h5 class="modal-title">Edit Server</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editServerForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi Server</label>
                                <input type="text" class="form-control mb-2" name="lokasi_server" id="edit_lokasi_server" required>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">IP Address</label>
                                <input type="text" class="form-control" name="ip_address" id="edit_ip_address" required>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi Server</label>
                                <input type="text" class="form-control" name="gps" id="edit_gps">
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
        document.querySelectorAll('.edit-server-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                fetch(`/edit/server/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('edit_lokasi_server').value = data.lokasi_server || '';
                        document.getElementById('edit_ip_address').value = data.ip_address || '';
                        document.getElementById('edit_gps').value = data.gps || 'Belum ada GPS';
                        document.getElementById('editServerForm').action = `/update/server/${id}`;
                        var modal = new bootstrap.Modal(document.getElementById('modalEditServer'));
                        modal.show();
                    });
            });
        });
    });
    </script>
@endsection
