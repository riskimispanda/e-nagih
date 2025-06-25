@extends('layouts/contentNavbarLayout')

@section('title', 'Data Antrian')

@section('content')
    <!-- Basic Layout & Basic with Icons -->
    <div class="row">
        <!-- Basic Layout -->
        <div class="col-xxl">
            <div class="card mb-6">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Data Antrian</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 mb-5">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#tambah">
                                <i class="bx bxs-add-to-queue icon-sm me-2"></i> Tambah
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Telepon</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customer as $c)
                                    <tr>
                                        <td>{{ $c->nama_customer }}</td>
                                        <td>{{ $c->no_hp }}</td>
                                        <td class="text-center">
                                            <a href="{{ $c->gps }}" target="_blank" class="btn btn-success btn-sm">
                                                <i class="bx bxs-map"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            @if ($c->status_id == 1)
                                                <span class="badge text-bg-warning me-2">Menunggu</span>
                                            @elseif ($c->status_id == 2)
                                                <span class="badge text-bg-primary me-2">On Progress</span>
                                            @elseif ($c->status_id == 3)
                                                <span class="badge text-bg-success me-2">Selesai</span>
                                            @elseif ($c->status_id == 4)
                                                <span class="badge text-bg-danger me-2">Maintenance</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#tambah">
                                                <i class="bx bxs-edit icon-sm"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm">
                                                <i class="bx bxs-trash icon-sm"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- modal --}}
    <div class="modal fade" id="tambah" tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title mb-5 text-white" id="modalScrollableTitle">Antrian Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/customer/store" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6">
                            <label class="form-label" for="basic-default-fullname">Nama Pelanggan</label>
                            <input type="text" name="nama_customer" class="form-control" id="basic-default-fullname"
                                placeholder="Paijo">
                        </div>
                        <div class="mb-6">
                            <label class="form-label" for="basic-default-email">Nomor Telepon</label>
                            <input type="text" name="no_hp" id="basic-default-email" class="form-control"
                                placeholder="082635471627">
                        </div>
                        <div class="mb-6">
                            <label class="form-label" for="basic-default-phone">Email</label>
                            <input type="text" name="email" id="basic-default-phone" class="form-control phone-mask"
                                placeholder="paijoter@gmail.com">
                        </div>
                        <div class="mb-6">
                            <label class="form-label" for="basic-default-phone">Nomor Identitas</label>
                            <input type="text" name="no_identitas" id="basic-default-phone"
                                class="form-control phone-mask" placeholder="658 799 8941">
                        </div>
                        <div class="mb-6">
                            <label class="form-label">Foto Identitas</label>
                            <input type="file" name="identitas" class="form-control" id="basic-default-file">
                            <small class="text-muted">JPG, PNG, PDF (Max 2MB)</small>
                        </div>
                        <div class="mb-6">
                            <label class="form-label">Alamat</label>
                            <input type="text" name="alamat" class="form-control" id="basic-default-message"
                                placeholder="Jl. Raya No. 123, Jakarta">
                        </div>
                        <div class="mb-6">
                            <label class="form-label">Google Maps</label>
                            <input type="text" name="gps" class="form-control" id="basic-default-message"
                                placeholder="https://goo.gl/maps/xyz123">
                        </div>
                        <div class="mb-6">
                            <label class="form-label">Tanggal Registrasi</label>
                            <input type="date" name="created_at" class="form-control flatpickr-basic"
                                id="basic-default-date" placeholder="YYYY-MM-DD" />
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection
