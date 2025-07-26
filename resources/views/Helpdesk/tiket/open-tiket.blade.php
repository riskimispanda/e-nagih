@extends('layouts.contentNavbarLayout')

@section('title', 'Form Open Tiket')

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a class="text-decoration-none" href="/">Home</a></li>
        <li class="breadcrumb-item"><a class="text-decoration-none" href="/tiket-open">Tiket Open</a></li>
        <li class="breadcrumb-item active" aria-current="page">Form Open Tiket</li>
    </ol>
</nav>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title fw-bold">Form Open Tiket - {{ $customer->nama_customer }}</h4>
                <small class="card-subtitle">Formulir untuk membuka tiket</small>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Pelanggan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="mb-6 col-sm-6">
                                <label class="form-label" for="basic-icon-default-fullname">Nama Customer</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
                                    <input type="text" class="form-control" value="{{ $customer->nama_customer }}" readonly>
                                </div>
                            </div>
                            <div class="mb-6 col-sm-6">
                                <label class="form-label" for="basic-icon-default-company">Alamat</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ $customer->alamat }}" readonly>
                                    @php
                                    $gps = $customer->gps;
                                    $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                    $url = $isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps);
                                    @endphp
                                    <span class="input-group-text">
                                        <a href="{{ $url }}" target="_blank" data-bs-toggle="tooltip" title="Lihat di Google Maps" data-bs-placement="top">
                                            <i class='bx bx-map text-danger'></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-6 col-sm-6">
                                <label class="form-label" for="basic-icon-default-email">Email</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="icon-base bx bx-envelope"></i></span>
                                    <input type="text" id="basic-icon-default-email" class="form-control" value="{{ $customer->email }}" readonly>
                                </div>
                            </div>
                            <div class="mb-6 col-sm-6">
                                <label class="form-label" for="basic-icon-default-phone">No HP</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-phone2" class="input-group-text"><i class="icon-base bx bx-phone"></i></span>
                                    <input type="text" id="basic-icon-default-phone" class="form-control phone-mask" value="{{ $customer->no_hp }}" readonly>
                                </div>
                            </div>
                            <div class="mb-6 col-sm-6">
                                <label class="form-label" for="basic-icon-default-phone">Router</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-phone2" class="input-group-text"><i class="icon-base bx bx-server"></i></span>
                                    <input type="text" id="basic-icon-default-phone" class="form-control phone-mask" value="{{ $customer->router->nama_router }}" readonly>
                                </div>
                            </div>
                            <div class="mb-6 col-sm-6">
                                <label class="form-label" for="basic-icon-default-phone">Paket Langganan</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-phone2" class="input-group-text"><i class="icon-base bx bx-package"></i></span>
                                    <input type="text" id="basic-icon-default-phone" class="form-control phone-mask" value="{{ $customer->paket->nama_paket }}" readonly>
                                </div>
                            </div>
                            <div class="mb-6 col-sm-6">
                                <label class="form-label" for="basic-icon-default-phone">Server</label>
                                <div class="input-group">
                                    <input type="text" id="basic-icon-default-phone" class="form-control phone-mask" value="{{ $customer->odp->odc->olt->server->lokasi_server }}" readonly>
                                    <span class="input-group-text">
                                        <a href="{{ $url }}" target="_blank" data-bs-toggle="tooltip" title="Lihat di Google Maps" data-bs-placement="top">
                                            <i class='bx bx-map text-danger'></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-6 col-sm-6">
                                <label class="form-label" for="basic-icon-default-phone">OLT</label>
                                <div class="input-group">
                                    <input type="text" id="basic-icon-default-phone" class="form-control phone-mask" value="{{ $customer->odp->odc->olt->nama_lokasi }}" readonly>
                                    <span class="input-group-text">
                                        <a href="{{ $url }}" target="_blank" data-bs-toggle="tooltip" title="Lihat di Google Maps" data-bs-placement="top">
                                            <i class='bx bx-map text-danger'></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-6 col-sm-6">
                                <label class="form-label" for="basic-icon-default-phone">ODC</label>
                                <div class="input-group">
                                    <input type="text" id="basic-icon-default-phone" class="form-control phone-mask" value="{{ $customer->odp->odc->nama_odc ?? '-' }}" readonly>
                                    <span class="input-group-text">
                                        <a href="{{ $url }}" target="_blank" data-bs-toggle="tooltip" title="Lihat di Google Maps" data-bs-placement="top">
                                            <i class='bx bx-map text-danger'></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-6 col-sm-6">
                                <label class="form-label" for="basic-icon-default-phone">ODP</label>
                                <div class="input-group">
                                    <input type="text" id="basic-icon-default-phone" class="form-control phone-mask" value="{{ $customer->odp->nama_odp ?? '-' }}" readonly>
                                    <span class="input-group-text">
                                        <a href="{{ $url }}" target="_blank" data-bs-toggle="tooltip" title="Lihat di Google Maps" data-bs-placement="top">
                                            <i class='bx bx-map text-danger'></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Gangguan</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-sm-12 mb-6">
                                    <label class="form-label">Kategori Tiket Open</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-category"></i></span>
                                        <select name="kategori" id="kategori" class="form-select" required>
                                            <option value="" selected disabled>Pilih Kategori</option>
                                            @foreach($kategori as $item)
                                                <option value="{{ $item->id }}">{{ $item->nama_kategori }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 mb-6">
                                    <label class="form-label">Keterangan</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-detail"></i></span>
                                        <textarea name="keterangan" cols="30" rows="3" class="form-control" required></textarea>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <label class="form-label">Foto</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-image"></i></span>
                                        <input type="file" class="form-control" name="foto">
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-6">
                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bx bx-file me-1"></i>Kirim</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    new TomSelect("#kategori", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
</script>

@endsection