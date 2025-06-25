@extends('layouts.contentNavbarLayout')

@section('title', 'Data Antrian')

@section('content')

    <div class="row g-4">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="page-header">
                            <h4 class="page-title">Proses Antrian</h4>
                            <p class="page-subtitle text-muted">
                                Terdaftar pada {{ $customer->created_at->format('d M Y, H:i') }} Oleh
                                {{ $customer->agen->name }}
                            </p>
                        </div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="/data/antrian-noc">Antrian</a></li>
                                <li class="breadcrumb-item active">Proses Antrian</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Antrian</h5>
                    <span class="badge bg-label-warning rounded-pill">Menunggu Proses Assigment</span>
                </div>
                <hr class="my-1">
                <div class="card-body demo-vertical-spacing demo-only-element">
                    <div class="row">
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Nama Pelanggan</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon11"><i class="bx bx-user"></i></span>
                                <input type="text" class="form-control" readonly value="{{ $customer->nama_customer }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon11"><i class="bx bx-phone"></i></span>
                                <input type="text" class="form-control" readonly value="{{ $customer->no_hp }}">
                            </div>
                        </div>
                        <div class="col-sm-12 mb-4">
                            <label class="form-label">Alamat</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon11"><i class="bx bxs-map"></i></span>
                                <textarea type="text" class="form-control" readonly>{{ $customer->alamat }}</textarea>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Titik lokasi</label>
                            <div class="input-group">
                                <a href="{{ $customer->gps }}" target="_blank" class="btn btn-outline-warning btn-sm">
                                    <span><i class="bx bx-map"></i></span>
                                    Lihat di Google Maps
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Foto KTP</label>
                            <div class="input-group">
                                <a href="{{ asset($customer->identitas) }}" target="_blank"
                                    class="btn btn-outline-warning btn-sm">
                                    <span><i class="bx bx-image"></i></span>
                                    Lihat Foto KTP
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Paket</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon11"><i class="bx bx-package"></i></span>
                                <input type="text" class="form-control" readonly
                                    value="{{ $customer->paket->nama_paket }}" name="paket_id">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Tagihan</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon11"><i class="bx bx-money"></i></span>
                                <input type="text" class="form-control" readonly
                                    value="Rp. {{ number_format($customer->paket->harga, 0, ',', '.') }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Status</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon11"><i class="bx bx-check-circle"></i></span>
                                <input type="text" class="form-control" readonly
                                    value="{{ $customer->status->nama_status }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Tanggal Registrasi</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon11"><i class="bx bx-calendar"></i></span>
                                <input type="text" class="form-control" readonly
                                    value="{{ $customer->created_at->format('d-m-Y') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Merged -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Form Assigment</h5>
                    <span class="badge bg-label-info rounded-pill">Assigment</span>
                </div>
                <hr class="my-1">
                <div class="card-body demo-vertical-spacing demo-only-element">
                    <form action="/noc/assign/{{ $customer->id }}" method="post">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-sm-6 mb-4">
                                <label class="form-label">Router</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon11"><i
                                            class="bx bx-network-chart"></i></span>
                                    <select name="router_id" class="form-select">
                                        <option value="" selected disabled>Pilih Router</option>
                                        @foreach ($router as $r)
                                            <option value="{{ $r->id }}">{{ $r->nama_router }}</option>
                                        @endforeach
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-4">
                                <label class="form-label">Koneksi</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="koneksi-addon"><i
                                            class="bx bx-link-alt"></i></span>
                                    <select name="koneksi_id" class="form-select" id="koneksi"
                                        onchange="togglePPPoEFields()">
                                        <option value="" selected disabled>Pilih Koneksi</option>
                                        @foreach ($koneksi as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_koneksi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-4">
                                <label class="form-label">User Secret</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon11"><i class="bx bx-lock"></i></span>
                                    <input type="text" class="form-control" name="usersecret"
                                        placeholder="coba@niscala.com">
                                </div>
                            </div>
                            <div class="col-sm-6 mb-4">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon11"><i class="bx bx-key"></i></span>
                                    <input type="text" class="form-control" name="password" placeholder="coba123">
                                </div>
                            </div>
                            <hr class="my-1 mb-3 mt-5">
                            <div class="col-sm-6 mb-4 pppoe-field" style="display: none">
                                <label class="form-label">Local Address</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon11"><i class="bx bx-math"></i></span>
                                    <input type="text" class="form-control" name="local_address"
                                        placeholder="192.168.1.1">
                                </div>
                            </div>
                            <div class="col-sm-6 mb-4 pppoe-field" style="display: none">
                                <label class="form-label">Remote Address</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon11"><i
                                            class="bx bx-devices"></i></span>
                                    <input type="text" class="form-control" name="remote_address"
                                        placeholder="10.10.10.1">
                                </div>
                            </div>
                            <hr class="my-1 pppoe-field mb-2" style="display: none">
                        </div>
                        <div class="col-sm-12 mt-3">
                            <button type="submit" class="btn btn-outline-info btn-sm float-end">
                                <i class='bx bx-save me-1'></i>
                                Assigment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePPPoEFields() {
            var connectionType = document.getElementById('koneksi').value;
            var pppoeFields = document.getElementsByClassName('pppoe-field');

            for (var i = 0; i < pppoeFields.length; i++) {
                pppoeFields[i].style.display = (connectionType === '3') ? 'block' : 'none';
            }
        }
    </script>

@endsection
