@extends('layouts.contentNavbarLayout')

@section('title', 'Pendapatan dari Perusahaan')

<style>
    .card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        transition: all 0.3s ease-in-out;
    }
</style>

@section('content')
<div class="row">
    <!--/ Total Revenue -->
    <div class="col-sm-12">
        <div class="card mb-5">
            <div class="card-header">
                <h4 class="card-title fw-bold">Pendapatan Langganan</h4>
                <small class="card-subtitle">Personal dan Perusahaan</small>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <h5 class="card-title fw-bold mb-0">
                                <span class="badge bg-danger bg-opacity-10 text-danger"><i class="bx bx-money"></i></span>
                                Pendapatan Langganan
                            </h5>
                        </div>
                        <h4 class="card-title fw-bold mb-3">Rp {{ number_format($corp + $personal, 0, ',', '.') }}</h4>
                        <p class="text-muted mb-1">Total Revenue</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-4">
                <a href="" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat Detail Pelanggan Perusahaan">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                <h5 class="card-title fw-bold mb-0">
                                    <span class="badge bg-info bg-opacity-10 text-info"><i class="bx bx-buildings"></i></span>
                                    Pelanggan Perusahaan
                                </h5>
                            </div>
                            <h4 class="card-title fw-bold mb-3">Rp {{ number_format($corp, 0, ',', '.') }}</h4>
                            <p class="text-muted mb-1">Revenue dari Pelanggan Perusahaan</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-4 mb-4">
                <a href="/data/pendapatan" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat Detail Pelanggan Personal">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                <h5 class="card-title fw-bold mb-0">
                                    <span class="badge bg-warning bg-opacity-10 text-warning"><i class="bx bx-user"></i></span>
                                    Pelanggan Personal
                                </h5>
                            </div>
                            <h4 class="card-title fw-bold mb-3">Rp {{ number_format($personal, 0, ',', '.') }}</h4>
                            <p class="text-muted mb-1">Revenue dari Pelanggan Personal</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-4 mb-4">
                <a href="/data-agen" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat Detail Data Agen">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                <h5 class="card-title fw-bold mb-0">
                                    <span class="badge bg-success bg-opacity-10 text-success"><i class="bx bx-hard-hat"></i></span>
                                    Data Agen
                                </h5>
                            </div>
                            <h4 class="card-title fw-bold mb-3">{{$agen}}</h4>
                            <p class="text-muted mb-1">Table Data Agen</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection