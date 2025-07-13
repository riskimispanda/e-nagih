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
                                
                                <div id="accordionOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
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
                                <div id="accordionTwo" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
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
                </div>
            </div>
        </div>
    </div>
</div>


@endsection