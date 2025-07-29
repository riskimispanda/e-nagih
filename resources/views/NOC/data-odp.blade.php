@extends('layouts.contentNavbarLayout')
@section('title', 'Data ODP')
@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Data ODP</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Lokasi ODP</h4>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modalTambahOdp">
                        <i class="bx bxs-add-to-queue me-2"></i>Tambah ODP
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Data ODP</th>
                                    <th>Lokasi ODP</th>
                                    <th>Total Pelanggan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($odp as $index => $od)
                                    <tr class="text-uppercase">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="fw-semibold">
                                            <i class="bx bx-terminal me-1 text-primary"></i>{{$od->nama_odp}}
                                        </td>
                                        @php
                                            $gps = $od->gps;
                                            $isLink = $gps && Str::startsWith($gps, ['http://', 'https://']);
                                            $url = $gps ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                                        @endphp

                                        <td class="text-center">
                                            <a href="{{ $url }}" {{ $url != '#' ? 'target=_blank' : '' }} data-bs-toggle="tooltip" title="Lokasi ODP" data-bs-placement="bottom">
                                                <i class="bx bx-map {{ $url == '#' ? 'text-muted' : 'text-primary' }}"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">
                                                {{ $customer->where('lokasi_id', $od->id)->count() }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="#" class="edit-odp-btn" data-id="{{ $od->id }}" data-bs-toggle="tooltip" title="Edit ODP" data-bs-placement="bottom">
                                                    <i class="bx bx-edit text-warning"></i>
                                                </a>|
                                                <a href="/hapus/odp/{{ $od->id }}" data-bs-toggle="tooltip" title="Hapus ODP" data-bs-placement="bottom">
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
    <div class="modal fade" id="modalTambahOdp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-terminal me-2"></i>
                    <h5 class="modal-title">Tambah ODP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/odp/add" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODC</label>
                                <select name="odc" id="" class="form-select mb-3">
                                    <option value="" selected disabled>Pilih Lokasi ODC</option>
                                    @foreach ($lokasi as $ol)
                                        <option value="{{ $ol->id }}">{{ $ol->nama_odc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Nama ODP</label>
                                <input type="text" class="form-control mb-3" name="nama_odp" id="nama_odp" placeholder="ODP Dondong 2" required />
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODP</label>
                                <input type="text" class="form-control" name="gps" id="lokasi_odp" placeholder="GPS Lokasi ODP" required />
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
    <div class="modal fade" id="modalEditOdp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-terminal me-2"></i>
                    <h5 class="modal-title">Edit ODP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOdpForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Nama ODP</label>
                                <input type="text" class="form-control mb-3" name="nama_odp" id="edit_nama_odp" required>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODC</label>
                                <select name="odc" id="edit_odc" class="form-select mb-3" required>
                                    <option value="" selected disabled>Pilih ODC</option>
                                    @foreach ($lokasi as $o)
                                        <option value="{{ $o->id }}">{{ $o->nama_odc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODP</label>
                                <input type="text" name="gps" placeholder="https://maps.google.com/... atau -1.0269916,110.48579129" class="form-control" id="edit_gps">
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

    <script>
        $(document).ready(function() {
            $('.edit-odp-btn').click(function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                $.ajax({
                    url: '/edit/odp/' + id,
                    type: 'GET',
                    success: function(data) {
                        $('#edit_nama_odp').val(data.nama_odp);
                        $('#edit_odc').val(data.odc_id);
                        $('#edit_gps').val(data.gps);
                        $('#editOdpForm').attr('action', '/update/odp/' + id);
                        $('#modalEditOdp').modal('show');
                    }
                });
            });
        });
    </script>
@endsection
