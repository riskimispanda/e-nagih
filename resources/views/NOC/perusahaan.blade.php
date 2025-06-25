@extends('layouts.contentNavbarLayout')

@section('title', 'Proses Perusahaan')

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb breadcrumb-custom-icon">
        <li class="breadcrumb-item">
            <a href="/dashboard">Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/data/antrian-noc">Data Antrian NOC</a>
        </li>
        <li class="breadcrumb-item active">Data Perusahaan</li>
    </ol>
</nav>

<div class="row">
    <div class="col-sm-12">
        <div class="card mb-5">
            <div class="card-header bg-white bg-opacity-10">
                <h4 class="card-title">Detail Perusahaan</h4>
                <p class="card-subtitle text-muted">Informasi Instalasi Perusahaan</p>
            </div>
        </div>
        
        <div class="row mb-12 g-6">
            <div class="col-md">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-5">
                        <h5 class="mb-0">Informasi Administrasi</h5>
                        <small class="badge bg-danger bg-opacity-10 text-danger float-end">Priority</small>
                    </div>
                    <div class="card-body">
                        <div class="row ">
                            <div class="col-sm-6 mb-6">
                                <label class="form-label" for="basic-icon-default-fullname">Nama Perusahaan</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-building"></i></span>
                                    <input type="text" class="form-control" value="{{ $corp->nama_perusahaan }}" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-6">
                                <label class="form-label" for="basic-default-company">Nama PIC</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="icon-base bx bx-user"></i></span>
                                    <input type="text" class="form-control" id="basic-default-company" value="{{ $corp->nama_pic }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-6">
                                <label class="form-label" for="basic-default-email">Nomor Hp</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="icon-base bx bx-phone"></i></span>
                                    <input type="text" id="nomor-hp" class="form-control" value="{{ $corp->no_hp }}" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-6">
                                <label class="form-label" for="basic-default-phone">Alamat Perusahaan</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="icon-base bx bx-map"></i></span>
                                    <input type="text" class="form-control phone-mask" value="{{ $corp->alamat }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-sm-6 mb-2">
                                <label class="form-label" for="basic-default-phone">Foto Identitas</label>
                                <div>
                                    <a href="{{ asset($corp->foto) }}" target="_blank" class="btn btn-secondary btn-sm">
                                        <i class="bx bx-camera"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Lokasi / Gps</label>
                                <div>
                                    <a href="{{ $corp->gps }}" target="_blank" class="btn btn-secondary btn-sm">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-sm-4 mb-2">
                                <label class="form-label">Paket Langganan</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="icon-base bx bx-wifi"></i></span>
                                    <input type="text" class="form-control" value="{{ $corp->paket->nama_paket }}" readonly>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Speed Internet</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="icon-base bx bx-tachometer"></i></span>
                                    <input type="text" class="form-control" value="{{ $corp->speed }}" readonly>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Harga Langganan</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="icon-base bx bx-money"></i></span>
                                    <input id="harga" type="text" class="form-control" value="{{ $corp->harga }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-2">
                                <label class="form-label">Status</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="icon-base bx bx-hourglass"></i></span>
                                    <input type="text" class="form-control" value="{{ $corp->status->nama_status }}" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Tanggal Registrasi</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="icon-base bx bx-calendar-alt"></i></span>
                                    <input type="date" class="form-control" value="{{ date($corp->tanggal) }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-5">
                        <h5 class="mb-0">Konfigurasi Jaringan</h5>
                        <small class="float-end badge bg-danger bg-opacity-10 text-danger">Priority</small>
                    </div>
                    <div class="card-body">
                        <form action="/update/corp/{{ $corp->id }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row border-bottom mb-6">
                                <div class="col-sm-12 mb-6">
                                    <label class="form-label" for="basic-icon-default-fullname">IP Address</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-link"></i></span>
                                        <input type="text" class="form-control" name="ip" placeholder="192.168.0.100">
                                    </div>
                                </div>
                                <div class="col-sm-12 mb-6">
                                    <label class="form-label">Teknisi</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base bx bx-user"></i></span>
                                        <select name="teknisi_id" id="search" class="form-select">
                                            <option value="" selected disabled>Pilih Teknisi</option>
                                            @foreach ($teknisi as $item)
                                                <option value="{{ $item->id }}">{{$item->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bx bx-save me-2"></i>Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function formatToLocalNumber(number) {
        if (!number) return 'No. HP tidak tersedia';
        number = number.replace(/\D/g, ''); // hapus non-digit
        if (number.startsWith('62')) {
            return '0' + number.slice(2);
        }
        return number;
    }
    
    window.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('nomor-hp');
        if (input) {
            input.value = formatToLocalNumber(input.value);
        }
    });
</script>
<script>
    function formatRupiah(number) {
        const angka = parseInt(number.replace(/\D/g, ''));
        return isNaN(angka) ? 'Rp 0' : 'Rp ' + angka.toLocaleString('id-ID');
    }
    
    window.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('harga');
        if (input && input.value) {
            input.value = formatRupiah(input.value);
        }
    });
</script>

<script>
    new TomSelect("#search", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
</script>

@endsection