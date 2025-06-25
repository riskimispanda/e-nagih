@extends('layouts/contentNavbarLayout')

@section('title', 'Konfirmasi Instalasi')

@section('vendor-style')
    <style>
        /* Avatar styles */
        .avatar-md {
            width: 42px;
            height: 42px;
        }
    </style>
@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/teknisi/antrian">Antrian</a></li>
                    <li class="breadcrumb-item active">Detail Antrian</li>
                </ol>
            </nav>
            <div class="mt-2">
                <ul class="nav nav-pills mb-5 mt-5" id="pills-tab" role="tablist">
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link active px-4 py-2 rounded-pill d-flex align-items-center"
                            id="pills-customer-tab" data-bs-toggle="pill" data-bs-target="#pills-customer" type="button"
                            role="tab">
                            <span class="badge rounded-circle bg-white text-primary me-2">1</span>
                            Informasi Pelanggan
                        </button>
                    </li>
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link px-4 py-2 rounded-pill d-flex align-items-center" id="pills-detail-tab"
                            data-bs-toggle="pill" data-bs-target="#pills-detail" type="button" role="tab">
                            <span class="badge rounded-circle bg-white text-primary me-2">2</span>
                            Detail Informasi
                        </button>
                    </li>
                </ul>
                <div class="tab-content bg-info bg-opacity-10" id="pills-tabContent">
                    <div class="tab-pane fade show active col-sm-12" id="pills-customer" role="tabpanel">
                        <div class="card-header">
                            <h4 class="mb-5">Konfirmasi Pemasangan</h4>
                        </div>
                        <div class="row g-4 p-4">
                            <div class="col-md-12">
                                <div class="card border-0 shadow mb-4 overflow-hidden">
                                    <div class="card-header bg-primary bg-opacity-10 py-3">
                                        <h5 class="card-title mb-0 text-primary">
                                            <i class='bx bx-user-circle me-2'></i>Detail Pelanggan
                                        </h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div
                                                    class="d-flex align-items-center mb-4 p-3 rounded-3 bg-light bg-opacity-50">
                                                    <div
                                                        class="avatar avatar-md bg-primary bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                        <i class='bx bx-user text-primary fs-4'></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block mb-1">Nama Pelanggan</small>
                                                        <span class="fw-bold fs-5">{{ $customer->nama_customer }}</span>
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex align-items-center mb-4 p-3 rounded-3 bg-light bg-opacity-50">
                                                    <div
                                                        class="avatar avatar-md bg-primary bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                        <i class='bx bx-phone text-primary fs-4'></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block mb-1">No. HP</small>
                                                        <span class="fw-bold fs-5">{{ $customer->no_hp }}</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center p-3 rounded-3 bg-light bg-opacity-50">
                                                    <div
                                                        class="avatar avatar-md bg-primary bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                        <i class='bx bx-id-card text-primary fs-4'></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block mb-1">No. Identitas</small>
                                                        <span class="fw-bold fs-5">{{ $customer->no_identitas }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div
                                                    class="d-flex align-items-center mb-4 p-3 rounded-3 bg-light bg-opacity-50">
                                                    <div
                                                        class="avatar avatar-md bg-primary bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                        <i class='bx bx-map text-primary fs-4'></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block mb-1">Alamat</small>
                                                        <span class="fw-bold fs-5">{{ $customer->alamat }}</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center p-3 rounded-3 bg-light bg-opacity-50">
                                                    <div
                                                        class="avatar avatar-md bg-primary bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                        <i class='bx bx-calendar text-primary fs-4'></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block mb-1">Tanggal Registrasi</small>
                                                        <span
                                                            class="fw-bold fs-5">{{ $customer->created_at->format('d-m-Y') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary"
                                onclick="nextTab('pills-detail-tab')">Next</button>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-detail" role="tabpanel">
                        <div class="card-header">
                            <h4 class="mb-5">Konfirmasi Pemasangan</h4>
                        </div>
                        <div class="card gap-4 p-4">
                            <form>
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-bold mb-2">Foto Rumah</label>
                                        <input type="file" class="form-control">
                                        <span class="text-muted">Max 2MB (JPG, JPEG, PNG)</span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-bold mb-2">Foto Identitas</label>
                                        <input type="file" class="form-control">
                                        <span class="text-muted">Max 2MB (JPG, JPEG, PNG)</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-bold mb-2">OLT</label>
                                        <select name="olt" class="form-select mb-2">
                                            <option value="" selected disabled>Pilih OLT</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-bold mb-2">ODC</label>
                                        <select name="odc" class="form-select mb-2">
                                            <option value="" selected disabled>Pilih ODC</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-bold mb-2">ODP</label>
                                        <select name="ODP" class="form-select mb-2">
                                            <option value="" selected disabled>Pilih ODP</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label fw-bold mb-2">ONT</label>
                                        <input type="text" class="form-control" placeholder="paijo-cc">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary"
                                        onclick="nextTab('pills-customer-tab')">Previous</button>
                                    <button type="button" class="btn btn-primary"
                                        onclick="nextTab('pills-confirm-tab')">Next</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function nextTab(tabId) {
            const triggerEl = document.querySelector('#' + tabId);
            const tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    </script>

@endsection
