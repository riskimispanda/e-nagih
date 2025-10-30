@extends('layouts.contentNavbarLayout')

@section('title', 'Blokir Setting')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title fw-bold">Pengaturan</h5>
                <small class="card-subtitle text-muted">Pengaturan Global</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <small class="text-light fw-medium">Pengaturan</small>
                        <div class="accordion mt-4" id="accordionExample">
                            <div class="card accordion-item active">
                                <h2 class="accordion-header" id="headingOne">
                                    <button type="button" class="accordion-button bg-primary text-white fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionOne" aria-expanded="true" aria-controls="accordionOne">
                                    <i class="bx bx-calendar me-1"></i>
                                        Update Tanggal Blokir
                                    </button>
                                </h2>
                                <div id="accordionOne" class="accordion-collapse collapse " data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <form action="/sett/blokir" method="POST">
                                            @csrf
                                            @php
                                                // Ambil bulan dan tahun saat ini
                                                $bulan = date('m');
                                                $tahun = date('Y');
                                                // Hitung jumlah hari dalam bulan saat ini
                                                $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
                                            @endphp
                                            <div class="row mb-4">
                                                <label class="form-label mb-2 mt-4 fw-semibold">Tanggal Blokir</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                                    <select name="tanggal_blokir" class="form-select">
                                                        <option value="">Pilih Tanggal</option>
                                                        @for ($i = 1; $i <= $jumlahHari; $i++)
                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-flex float-end mb-4">
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bx bx-save me-1"></i>Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-light fw-medium">Pengaturan</small>
                        <div class="accordion mt-4" id="accordionExample">
                            <div class="card accordion-item active">
                                <h2 class="accordion-header mb-5" id="headingTwo">
                                    <button type="button" class="accordion-button bg-primary text-white fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionTwo" aria-expanded="true" aria-controls="accordionTwo">
                                    <i class="bx bx-bot me-1"></i>
                                        Bot Chat Setting
                                    </button>
                                </h2>
                                <div id="accordionTwo" class="accordion-collapse collapse " data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <a href="/visual" class="btn btn-warning btn-sm">
                                            <i class="bx bx-cog me-1"></i>
                                            Setting Bot
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-light fw-medium">Import Data</small>
                        <div class="accordion mt-4" id="accordionExample">
                            <div class="card accordion-item active">
                                <h2 class="accordion-header mb-5" id="headingThree">
                                    <button type="button" class="accordion-button bg-primary text-white fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionThree" aria-expanded="true" aria-controls="accordionTwo">
                                    <i class="bx bx-download me-1"></i>
                                        Import Data Customer
                                    </button>
                                </h2>
                                <div id="accordionThree" class="accordion-collapse collapse " data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <form action="/customer/import" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <label class="form-label">Import Data</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bx bx-file"></i></span>
                                                        <input type="file" name="file" class="form-control" required>
                                                    </div>
                                                    <small class="text-muted">File: xlsx, xls, csv</small>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="d-flex justify-content-end">
                                                    <button type="submit" class="btn btn-warning btn-sm"><i class="bx bx-file me-1"></i>Import</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-light fw-medium">Import Khusus Paket Fasum</small>
                        <div class="accordion mt-4" id="accordionExample">
                            <div class="card accordion-item active">
                                <h2 class="accordion-header mb-5" id="headingFour">
                                    <button type="button" class="accordion-button bg-primary text-white fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionFour" aria-expanded="true" aria-controls="accordionTwo">
                                    <i class="bx bx-download me-1"></i>
                                        Import Data Customer Khusus Paket Fasum
                                    </button>
                                </h2>
                                <div id="accordionFour" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <form action="/customer/import/khusus" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <label class="form-label">Import Data</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bx bx-file"></i></span>
                                                        <input type="file" name="file" class="form-control" required>
                                                    </div>
                                                    <small class="text-muted">File: xlsx, xls, csv</small>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="d-flex justify-content-end">
                                                    <button type="submit" class="btn btn-warning btn-sm"><i class="bx bx-file me-1"></i>Import</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-light fw-medium">Import Upgrade</small>
                        <div class="accordion mt-4" id="accordionExample">
                            <div class="card accordion-item active">
                                <h2 class="accordion-header mb-5" id="headingFive">
                                    <button type="button" class="accordion-button bg-primary text-white fw-bold" data-bs-toggle="collapse" data-bs-target="#accordionFive" aria-expanded="true" aria-controls="accordionTwo">
                                    <i class="bx bx-download me-1"></i>
                                        Import Data Upgrade
                                    </button>
                                </h2>
                                <div id="accordionFive" class="accordion-collapse collapse " data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <form action="/import-upgrade" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <label class="form-label">Import Data</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bx bx-file"></i></span>
                                                        <input type="file" name="file" class="form-control" required>
                                                    </div>
                                                    <small class="text-muted">File: xlsx, xls, csv</small>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="d-flex justify-content-end">
                                                    <button type="submit" class="btn btn-warning btn-sm"><i class="bx bx-file me-1"></i>Import</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="d-flex justify-content-start mb-4">
                                <a href="/hapus/dataImport" method="GET">
                                    <button class="btn btn-danger btn-sm"><i class="bx bx-trash me-1"></i>Hapus Data Import</button>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="d-flex justify-content-start mb-4">
                                <a href="/cek/dataImport" method="GET">
                                    <button class="btn btn-primary btn-sm"><i class="bx bx-search me-1"></i>Cek Import Data</button>
                                </a>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
</div>


@endsection