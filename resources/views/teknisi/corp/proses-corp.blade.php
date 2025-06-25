@extends('layouts.contentNavbarLayout')

@section('title', 'Proses Installasi Corp')

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard">Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/teknisi/antrian">Antrian</a>
        </li>
        <li class="breadcrumb-item active">Proses</li>
    </ol>
</nav>

<div class="row mb-5">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Proses Installasi Perusahaan</h4>
                <p class="card-subtitle">Halaman Proses Installasi</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informasi Perusahaan</h5>
                <small class="badge bg-danger bg-opacity-10 text-danger float-end">Priority</small>
            </div>
            <hr class="my-1">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6 mb-6">
                        <label class="form-label" for="basic-icon-default-fullname">Nama Perusahaan</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-fullname2" class="input-group-text"><i class="bx bx-buildings"></i></span>
                            <input type="text" class="form-control" value="{{ $corp->nama_perusahaan }}" readonly>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-6">
                        <label class="form-label" for="basic-icon-default-company">Nama PIC</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2" class="input-group-text"><i class="bx bx-user"></i></span>
                            <input type="text" class="form-control" value="{{ $corp->nama_pic }}" readonly>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-6">
                        <label class="form-label" for="basic-icon-default-email">Nomor Hp</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-phone"></i></span>
                            <input type="text" class="nomor-hp form-control" value="{{ $corp->no_hp }}" readonly>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-6">
                        <label class="form-label">Admin</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <input type="text" class="form-control" value="{{ $corp->usr->name }}" readonly>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-6">
                        <label class="form-label" for="basic-icon-default-message">Alamat</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-message2" class="input-group-text"><i class="bx bx-compass"></i></span>
                            <input type="text" class="form-control" value="{{ $corp->alamat }}" readonly>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-6">
                        <label class="form-label">Foto Identitas</label>
                        <div class="input-group">
                            <a href="{{ asset($corp->foto) }}" class="btn btn-secondary btn-sm" target="_blank">
                                <i class="bx bx-image"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-6">
                        <label class="form-label">Lokasi / GPS</label>
                        <div class="input-group">
                            <a href="{{ $corp->gps }}" class="btn btn-secondary btn-sm" target="_blank">
                                <i class="bx bx-pin"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-6">
                        <label class="form-label">Tanggal Registrasi</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="date" class="form-control" value="{{ $corp->tanggal }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl">
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Konfigurasi Jaringan</h5>
                <small class="badge bg-danger bg-opacity-10 text-danger float-end">Priority</small>
            </div>
            <hr class="my-1">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12 mb-6">
                        <label class="form-label">IP Address</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-link"></i></span>
                            <input type="text" class="form-control" value="{{ $corp->ip_address }}" readonly>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-6">
                        <label class="form-label">Speed Internet</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-tachometer"></i></span>
                            <input type="text" class="form-control" value="{{ $corp->speed }}" readonly>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-6">
                        <label class="form-label">Paket Langganan</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-package"></i></span>
                            <input type="text" class="form-control" value="{{ $corp->paket->nama_paket }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card">
            <div class="card-header">
                <small class="badge bg-danger bg-opacity-10 text-danger float-end">Priority</small>
                <h5 class="card-title">Konfirmasi Installasi</h5>
                <p class="card-subtitle">Konfigurasi Jaringan Di Pelanggan</p>
            </div>
            <hr class="my-1">
            <form action="/confirm/corp/{{ $corp->id }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Foto Tempat</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-image"></i></span>
                                <input type="file" class="form-control" name="foto_tempat">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Foto Perangkat</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-image"></i></span>
                                <input type="file" class="form-control" name="foto_perangkat">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Nilai Redaman</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-box"></i></span>
                                <input type="text" class="form-control" name="redaman" placeholder="100bp">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Panjang Kabel</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-cable-car"></i></span>
                                <input type="text" class="form-control" name="kabel" placeholder="100 meter">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 mb-4">
                            <label class="form-label">Perangkat</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-devices"></i></span>
                                <select name="perangkat" id="devices" class="form-select">
                                    <option value="" selected disabled>Pilih Perangkat</option>
                                    @foreach ($dev as $item)
                                    <option value="{{ $item->id }}">{{$item->nama_perangkat}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-4">
                            <label class="form-label">Seri Perangkat</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-devices"></i></span>
                                <input type="text" class="form-control" name="seri" placeholder="1234657bhs">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-4">
                            <label class="form-label">Mac Address</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-devices"></i></span>
                                <input type="text" class="form-control" name="mac" placeholder="XX:XX:XX:XX:XX:XX">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Media Koneksi</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-cable-car"></i></span>
                                <input type="text" class="form-control" name="media" placeholder="Fiber Optik">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Server (OLT)</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-server"></i></span>
                                <input type="text" class="form-control" name="olt" placeholder="Server 1">
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-1">
                <div class="card-footer d-flex gap-2 float-end">
                    <button type="button" class="btn btn-secondary btn-sm">Kembali</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Devices
    new TomSelect('#devices', {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
</script>


@endsection