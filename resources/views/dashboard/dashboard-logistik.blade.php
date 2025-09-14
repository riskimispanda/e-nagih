@extends('layouts.contentNavbarLayout')

@section('title', 'Dashboard - Logistik')

@section('content')
<div class="row">
    <div class="col-xxl-12 mb-6 order-0">
        <div class="card">
            <div class="d-flex align-items-start row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h4 class="card-title fw-bold mb-3">Dashboard Logistik</h4>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-6">
                        <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template-free/demo/assets/img/illustrations/man-with-laptop.png" height="175" class="scaleX-n1-rtl" alt="View Badge User">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-3 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <i class="bx bx-package fs-3 text-info"></i>
                            </div>
                        </div>
                        <p class="mb-1 fw-semibold">Total Aset</p>
                        <h4 class="card-title mb-3">{{$perangkat->count()}}</h4>
                        <small class="fw-medium"> Rp {{ number_format($perangkat->sum('harga'), 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <i class="bx bx-check fs-3 text-success"></i>
                            </div>
                        </div>
                        <p class="mb-1 fw-semibold">Terpakai</p>
                        <h4 class="card-title mb-3">{{$terpakai}}</h4>
                        <small class="fw-medium"></small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <i class="bx bx-check fs-3 text-success"></i>
                            </div>
                        </div>
                        <p class="mb-1 fw-semibold">Tersedia</p>
                        <h4 class="card-title mb-3">{{$tersedia}}</h4>
                        <small class="fw-medium"></small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <i class="bx bx-x fs-3 text-danger"></i>
                            </div>
                        </div>
                        <p class="mb-1 fw-semibold">Rusak</p>
                        <h4 class="card-title mb-3">0</h4>
                        <small class="fw-medium"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection