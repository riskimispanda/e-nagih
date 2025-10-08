@extends('layouts.contentNavbarLayout')

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
        <div class="col-sm-12 mb-2">
            @if($todaySchedules->count())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6 class="fw-bold text-danger mb-0">📅 Jadwal Hari Ini:</h6>
                    <ul class="mb-0">
                        @foreach($todaySchedules as $schedule)
                            <li>
                                {{ $schedule->title }}
                                @if($schedule->time_type === 'specific')
                                ({{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->locale('id')->isoFormat('H:mm') : '' }}
                                - {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->locale('id')->isoFormat('H:mm') : '' }})
                                @else
                                    (Seharian)
                                @endif
                                <b>By: {{ $schedule->user ? $schedule->user->name : 'Unknown' }}</b>
                            </li>
                        @endforeach
                    </ul>              
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
        <div class="col-xxl-12 mb-6 order-0">
            <div class="card">
                <div class="d-flex align-items-start row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h4 class="card-title text-primary mb-3 fw-bold" style="text-transform: uppercase">Selamat Datang {{ $users->name }} 🎉</h4>
                            <p class="card-text"><b>NBilling</b> adalah aplikasi berbasis web yang dirancang untuk mempermudah proses pencatatan, pengelolaan, dan penagihan pembayaran pelanggan.</p>
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
        <div class="col-lg-12 col-md-12 order-1">
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
                <div class="col-lg-3 col-md-12 col-6 mb-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                <div class="avatar flex-shrink-0 bg-success bg-opacity-10 d-flex align-items-center justify-content-center rounded">
                                    <i class="bx bxs-wallet text-success fw-bold"></i>
                                </div>
                            </div>
                            <p class="mb-1">Pelanggan Lunas</p>
                            <h4 class="card-title mb-3">Rp {{ number_format($pelangganLunas, 0, ',', '.') }}</h4>
                            <small class="badge bg-label-success"><i class="bx bxs-user me-1 fs-6"></i>{{ $countLunas }} Invoice</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-12 col-6 mb-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                <div class="avatar flex-shrink-0 bg-danger bg-opacity-10 d-flex align-items-center justify-content-center rounded">
                                    <i class="bx bxs-cart text-danger fw-bold"></i>
                                </div>
                            </div>
                            <p class="mb-1">Pelanggan Belum Lunas</p>
                            <h4 class="card-title mb-3">Rp {{ number_format($pelangganBelumLunas, 0, ',', '.') }}</h4>
                            <small class="badge bg-label-danger"><i class="bx bxs-user me-1 fs-6"></i>{{ $countBelumLunas }} Invoice</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-12 col-6 mb-6">
                    <a href="/tiket-open" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Halaman Tiket Open">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0 bg-warning bg-opacity-10 d-flex align-items-center justify-content-center rounded">
                                        <i class="bx bx-card text-warning fw-bold"></i>
                                    </div>
                                </div>
                                <p class="mb-1">Tiket Open</p>
                                <h4 class="card-title mb-3">{{$open}}</h4>
                                <small class="badge bg-label-warning"><i class="bx bx-card me-1 fs-6"></i>{{ $open }} Tiket</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-12 col-6 mb-6">
                    <a href="/tiket-closed" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Halaman Tiket Closed">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                    <div class="avatar flex-shrink-0 bg-success bg-opacity-10 d-flex align-items-center justify-content-center rounded">
                                        <i class="bx bxs-card text-success fw-bold"></i>
                                    </div>
                                </div>
                                <p class="mb-1">Tiket Closed</p>
                                <h4 class="card-title mb-3">{{$closed}}</h4>
                                <small class="badge bg-label-success"><i class="bx bxs-card me-1 fs-6"></i>{{ $closed }} Tiket</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title fw-bold">Mapping Customer</h4>
                    <small class="card-subtitle">Peta mapping untuk melihat lokasi server, olt, odc, odp, dan customer</small>
                </div>
            </div>
            <div class="card">
                <div id="map" style="height: 520px;"></div>
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

<!-- Tambahkan Boxicons dan Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const map = L.map('map').setView([-9.5, 110.5], 10);
    const bounds = L.latLngBounds();

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
        attribution: '© Panda'
    }).addTo(map);
    

    const colors = {
        server: 'red',
        olt: 'orange',
        odc: 'green',
        odp: 'blue',
        customer: 'purple'
    };

    const icons = {
        server: 'bx-server fw-bold',
        olt: 'bx-terminal fw-bold',
        odc: 'bx-terminal fw-bold',
        odp: 'bx-terminal fw-bold',
        customer: 'bx-user fw-bold'
    };

    const data = await fetch("{{ route('peta.data') }}").then(res => res.json());

    const nodes = {
        server: {},
        olt: {},
        odc: {},
        odp: {},
        customer: {}
    };

    const linesDrawn = [];

    data.forEach(item => {
        const latlng = [item.lat, item.lng];
        bounds.extend(latlng);
        nodes[item.jenis][item.id] = item;

        const customIcon = L.divIcon({
            className: '',
            html: `<i class='bx ${icons[item.jenis]}' style="font-size: 24px; color: ${colors[item.jenis]};"></i>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const marker = L.marker(latlng, { icon: customIcon }).addTo(map);

        const detailPopup = `
            <b>${item.jenis.toUpperCase()}</b><br>
            Nama: ${item.nama}<br>
            Koordinat: ${item.lat}, ${item.lng}<br>
        `;

        marker.on('mouseover', function () {
            marker.bindPopup(detailPopup).openPopup();
        });

        marker.on('mouseout', function () {
            map.closePopup();
        });

        marker.on('click', function () {
            drawConnections(item);
        });
    });

    function drawConnections(item) {
        linesDrawn.forEach(line => map.removeLayer(line));
        linesDrawn.length = 0;

        const connection = [];

        if (item.jenis === 'customer') {
            const odp = nodes.odp[item.odp_id];
            const odc = odp ? nodes.odc[odp.odc_id] : null;
            const olt = odc ? nodes.olt[odc.olt_id] : null;
            const server = olt ? nodes.server[olt.server_id] : null;

            if (odp) connection.push([item, odp]);
            if (odc) connection.push([odp, odc]);
            if (olt) connection.push([odc, olt]);
            if (server) connection.push([olt, server]);

        } else if (item.jenis === 'odp') {
            const odc = nodes.odc[item.odc_id];
            const olt = odc ? nodes.olt[odc.olt_id] : null;
            const server = olt ? nodes.server[olt.server_id] : null;

            if (odc) connection.push([item, odc]);
            if (olt) connection.push([odc, olt]);
            if (server) connection.push([olt, server]);

        } else if (item.jenis === 'odc') {
            const olt = nodes.olt[item.olt_id];
            const server = olt ? nodes.server[olt.server_id] : null;

            if (olt) connection.push([item, olt]);
            if (server) connection.push([olt, server]);

        } else if (item.jenis === 'olt') {
            const server = nodes.server[item.server_id];
            if (server) connection.push([item, server]);
        }

        connection.forEach(([child, parent]) => {
            if (child && parent) {
                const line = L.polyline([
                    [child.lat, child.lng],
                    [parent.lat, parent.lng]
                ], {
                    color: colors[child.jenis] || 'gray',
                    weight: 3,
                    opacity: 0.8
                }).addTo(map);
                linesDrawn.push(line);
            }
        });
    }

    map.fitBounds(bounds);

    // Legend
    const legend = L.control({ position: "bottomright" });
    legend.onAdd = function () {
        const div = L.DomUtil.create("div", "info legend");
        const types = Object.keys(colors);
        div.innerHTML += `<strong>Legenda</strong><br>`;
        types.forEach(key => {
            div.innerHTML += `
                <i class='bx ${icons[key]}' style="color:${colors[key]};font-size:16px;"></i>
                ${key.charAt(0).toUpperCase() + key.slice(1)}<br>`;
        });
        return div;
    };
    legend.addTo(map);
});
</script>
