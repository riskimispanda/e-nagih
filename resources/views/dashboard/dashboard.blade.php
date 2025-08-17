@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/dashboards-analytics.js')
@endsection

@section('content')
    <div class="row">
        <div class="col-xxl-12 mb-6 order-0">
            <div class="card">
                <div class="d-flex align-items-start row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h4 class="card-title text-primary mb-3 fw-bold" style="text-transform: uppercase">Selamat Datang {{ $users->name }} 🎉</h4>
                            <p class="card-text"><b>E-Nagih</b> adalah aplikasi berbasis web yang dirancang untuk mempermudah proses pencatatan, pengelolaan, dan penagihan pembayaran pelanggan.</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-6">
                            <img src="{{ asset('assets/img/illustrations/man-with-laptop.png') }}" height="175" class="scaleX-n1-rtl" alt="View Badge User">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-md-4 order-1">
            <div class="row">
                <div class="col-lg-4 col-md-12 col-6 mb-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                <div class="avatar flex-shrink-0 bg-warning bg-opacity-10 d-flex align-items-center justify-content-center rounded">
                                    <i class="bx bx-user text-warning fw-bold"></i>
                                </div>
                            </div>
                            <p class="mb-1">Total Pelanggan</p>
                            <h4 class="card-title mb-3">{{$paket}}</h4>
                            <small class="text-success fw-medium"><i class='bx bx-up-arrow-alt'></i>{{$newCustomer->count()}} baru</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 col-6 mb-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                <div class="avatar flex-shrink-0 bg-info bg-opacity-10 d-flex align-items-center justify-content-center rounded">
                                    <i class="bx bx-wallet text-info fw-bold"></i>
                                </div>
                            </div>
                            <p class="mb-1">Total Pendapatan</p>
                            <h4 class="card-title mb-3">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 col-6 mb-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                <div class="avatar flex-shrink-0 bg-danger bg-opacity-10 d-flex align-items-center justify-content-center rounded">
                                    <i class="bx bx-cart text-danger fw-bold"></i>
                                </div>
                            </div>
                            <p class="mb-1">Total Pengeluaran</p>
                            <h4 class="card-title mb-3">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Transactions -->
        <div class="col-md-6 col-lg-4 order-2 mb-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Transaksi Baru</h5>
                </div>
                <div class="card-body pt-4 border-bottom">
                    <ul class="p-0 m-0">
                        @foreach ($pembayaran->take(5) as $transaksi)
                        <li class="d-flex align-items-center mb-6">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="User" class="rounded">
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="fw-bold d-block">{{$transaksi->invoice->customer->nama_customer}}</small>
                                    <h6 class="fw-semibold mb-0 badge bg-warning bg-opacity-10 text-warning">{{$transaksi->metode_bayar}}</h6>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 badge bg-danger bg-opacity-10 text-danger">Rp {{ number_format($transaksi->jumlah_bayar, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    {{-- Tombol View All --}}
                    <div class="text-center mt-3">
                        <a href="/data/pembayaran" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Lihat Transaksi" data-bs-placement="top">
                            View All
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Transactions -->
    </div>
@endsection
