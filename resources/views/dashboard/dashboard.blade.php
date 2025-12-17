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
<!-- Tambahkan Boxicons dan Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

<style>
    .toggle-icon {
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 18px;
    }

    .toggle-icon:hover {
        transform: scale(1.2);
    }

    .dots-placeholder {
        font-size: 24px;
        letter-spacing: 3px;
        color: #6c757d;
    }

    /* Custom scrollbar untuk transaksi */
    .transaction-list::-webkit-scrollbar {
        width: 4px;
    }

    .transaction-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .transaction-list::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .transaction-list::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@section('content')
    <div class="space-y-6">
        <!-- Alert Jadwal Hari Ini -->
        @if($todaySchedules->count())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-red-800 mb-2">📅 Jadwal Hari Ini</h4>
                        <ul class="text-sm text-red-700 space-y-1">
                            @foreach($todaySchedules as $schedule)
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-1.5 h-1.5 bg-red-500 rounded-full mt-1.5 mr-2"></span>
                                    <span>
                                        {{ $schedule->title }}
                                        @if($schedule->time_type === 'specific')
                                            ({{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->locale('id')->isoFormat('H:mm') : '' }}
                                            - {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->locale('id')->isoFormat('H:mm') : '' }})
                                        @else
                                            (Seharian)
                                        @endif
                                        <span class="font-medium">By: {{ $schedule->user ? $schedule->user->name : 'Unknown' }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex h-8 w-8" data-bs-dismiss="alert" aria-label="Close">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- Welcome Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-7/12 p-6 md:p-8">
                    <h1 class="text-2xl md:text-3xl fw-bold text-gray-900 mb-4">
                        Selamat Datang {{ $users->name }} 🎉
                    </h1>
                    <p class="text-gray-600 text-lg leading-relaxed">
                        <span class="fw-bold text-blue-600">NBilling</span> adalah aplikasi berbasis web yang dirancang untuk mempermudah proses pencatatan, pengelolaan, dan penagihan pembayaran pelanggan.
                    </p>
                </div>
                <div class="md:w-5/12 flex justify-center md:justify-end p-4">
                    <img src="{{ asset('assets/img/illustrations/man-with-laptop.png') }}" alt="Welcome Illustration" class="w-64 md:w-72 scaleX-n1-rtl">
                </div>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Pelanggan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-yellow-50 rounded-xl">
                        <i class="bx bx-user text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-gray-600 text-sm font-medium mb-1">Total Pelanggan</p>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{$paket}}</h3>
                <div class="flex items-center text-sm text-green-600 font-medium">
                    <i class='bx bx-up-arrow-alt mr-1'></i>
                    <span>{{$newCustomer->count()}} baru</span>
                </div>
            </div>

            <!-- Total Pendapatan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <i class="bx bx-wallet text-blue-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-gray-600 text-sm font-medium mb-1">Total Pendapatan</p>
                <h3 class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
            </div>

            <!-- Total Pengeluaran -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-red-50 rounded-xl">
                        <i class="bx bx-cart text-red-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-gray-600 text-sm font-medium mb-1">Total Pengeluaran</p>
                <h3 class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
            </div>

            <!-- Laba/Rugi -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-50 rounded-xl">
                        <i class="bx bxs-wallet text-green-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-gray-600 text-sm font-medium mb-1">Laba/Rugi</p>
                <h3 class="text-2xl font-bold text-gray-900">Rp {{ number_format($laba, 0, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Status Pelanggan & Tiket -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Pelanggan Lunas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-50 rounded-xl">
                        <i class="bx bxs-wallet text-green-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-gray-600 text-sm font-medium mb-1">Pelanggan Lunas</p>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-2xl font-bold text-gray-900 amount-text" id="lunas-amount">
                        <span class="nominal-text">Rp {{ number_format($pelangganLunas, 0, ',', '.') }}</span>
                        <span class="dots-placeholder hidden text-gray-400 text-xl">•••••••</span>
                    </h3>
                    <button class="text-gray-400 hover:text-green-600 transition-colors duration-200 toggle-icon" data-target="lunas-card">
                        <i class="bx bx-show text-xl"></i>
                    </button>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-2">
                    <i class="bx bxs-user mr-1"></i>{{ $countLunas }} Pelanggan Lunas (MONTHLY)
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="bx bxs-user mr-1"></i>{{ $countInvoiceAllPaid }} Pelanggan Lunas (YEARLY)
                </span>
            </div>

            <!-- Pelanggan Belum Lunas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-red-50 rounded-xl">
                        <i class="bx bxs-cart text-red-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-gray-600 text-sm font-medium mb-1">Pelanggan Belum Lunas</p>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-2xl font-bold text-gray-900 amount-text" id="belum-lunas-amount">
                        <span class="nominal-text">Rp {{ number_format($pelangganBelumLunas, 0, ',', '.') }}</span>
                        <span class="dots-placeholder hidden text-gray-400 text-xl">•••••••</span>
                    </h3>
                    <button class="text-gray-400 hover:text-green-600 transition-colors duration-200 toggle-icon" data-target="belum-lunas-card">
                        <i class="bx bx-show text-xl"></i>
                    </button>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-2">
                    <i class="bx bxs-user mr-1"></i>{{ $countInvoiceAllUnPaid }} Pelanggan Belum Lunas (MONTHLY)
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <i class="bx bxs-user mr-1"></i>{{ $countBelumLunas }} Pelanggan Belum Lunas (YEARLY)
                </span>
            </div>

            <!-- Tiket Open -->
            <a href="/tiket-open" class="block transform hover:scale-105 transition-transform duration-200" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Halaman Tiket Open">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-yellow-50 rounded-xl">
                            <i class="bx bx-card text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Tiket Open</p>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{$open}}</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <i class="bx bx-card mr-1"></i>{{ $open }} Tiket
                    </span>
                </div>
            </a>

            <!-- Tiket Closed -->
            <a href="/tiket-closed" class="block transform hover:scale-105 transition-transform duration-200" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Halaman Tiket Closed">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-green-50 rounded-xl">
                            <i class="bx bxs-card text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm font-medium mb-1">Tiket Closed</p>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{$closed}}</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="bx bxs-card mr-1"></i>{{ $closed }} Tiket
                    </span>
                </div>
            </a>
        </div>

        <!-- Mapping Section -->
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xl fw-bold text-gray-900 mb-2">Mapping Customer</h3>
                <p class="text-gray-600">Peta mapping untuk melihat lokasi server, olt, odc, odp, dan customer</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div id="map" class="h-96 lg:h-[520px] w-full rounded-lg"></div>
            </div>
        </div>

        <!-- Transactions & Bottom Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Transactions -->
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h5 class="text-lg fw-semibold text-gray-900">Transaksi Baru</h5>
                    <p class="text-grey-600">Transaksi baru-baru ini</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach ($pembayaran->take(5) as $transaksi)
                        <div class="flex items-center space-x-4 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="Wallet" class="w-6 h-6">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{$transaksi->invoice->customer->nama_customer}}
                                </p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{$transaksi->metode_bayar}}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Rp {{ number_format($transaksi->jumlah_bayar, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Tombol View All --}}
                    <div class="text-center mt-6 pt-4 border-t border-gray-200">
                        <a href="/data/pembayaran" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200" data-bs-toggle="tooltip" title="Lihat Transaksi" data-bs-placement="top">
                            View All
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional content can go here in the remaining 2/3 space -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h5 class="text-lg fw-semibold text-gray-900 mb-4">Ringkasan Performa</h5>
                <p class="text-gray-600">Area tambahan untuk grafik atau informasi lainnya...</p>
                <!-- Tambahkan konten tambahan di sini -->
            </div>
        </div>
    </div>
@endsection
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

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
                <div class="p-2">
                    <b class="text-sm">${item.jenis.toUpperCase()}</b><br>
                    <span class="text-xs">Nama: ${item.nama}</span><br>
                    <span class="text-xs">Koordinat: ${item.lat}, ${item.lng}</span>
                </div>
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
            const div = L.DomUtil.create("div", "bg-white p-4 rounded-lg shadow-lg border border-gray-200");
            const types = Object.keys(colors);
            div.innerHTML = `<h4 class="font-semibold text-gray-900 mb-2">Legenda</h4>`;
            types.forEach(key => {
                div.innerHTML += `
                    <div class="flex items-center space-x-2 mb-1">
                        <i class='bx ${icons[key]}' style="color:${colors[key]};font-size:16px;"></i>
                        <span class="text-sm text-gray-700">${key.charAt(0).toUpperCase() + key.slice(1)}</span>
                    </div>`;
            });
            return div;
        };
        legend.addTo(map);
    });

    // Toggle visibility untuk card amount
    document.addEventListener('DOMContentLoaded', function() {
        initializeCardStates();

        document.querySelectorAll('.toggle-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                toggleCardVisibility(target, this);
            });
        });

        function toggleCardVisibility(target, icon) {
            const cardElement = document.querySelector(`[data-target="${target}"]`).closest('.bg-white');
            const nominalText = cardElement.querySelector('.nominal-text');
            const dotsPlaceholder = cardElement.querySelector('.dots-placeholder');
            const iconElement = icon.querySelector('i');

            if (nominalText.classList.contains('hidden')) {
                // Show - tampilkan teks asli
                nominalText.classList.remove('hidden');
                dotsPlaceholder.classList.add('hidden');
                iconElement.classList.remove('bx-show');
                iconElement.classList.add('bx-hide');
                icon.classList.remove('text-gray-400');
                icon.classList.add('text-green-600');

                localStorage.setItem(`card-${target}-hidden`, 'false');
            } else {
                // Hide - tampilkan dots
                nominalText.classList.add('hidden');
                dotsPlaceholder.classList.remove('hidden');
                iconElement.classList.remove('bx-hide');
                iconElement.classList.add('bx-show');
                icon.classList.remove('text-green-600');
                icon.classList.add('text-gray-400');

                localStorage.setItem(`card-${target}-hidden`, 'true');
            }
        }

        function initializeCardStates() {
            const cards = [
                { target: 'lunas-card' },
                { target: 'belum-lunas-card' }
            ];

            cards.forEach(card => {
                const isHidden = localStorage.getItem(`card-${card.target}-hidden`) === 'true';
                const icon = document.querySelector(`[data-target="${card.target}"]`);

                if (icon) {
                    const cardElement = icon.closest('.bg-white');
                    const nominalText = cardElement.querySelector('.nominal-text');
                    const dotsPlaceholder = cardElement.querySelector('.dots-placeholder');
                    const iconElement = icon.querySelector('i');

                    if (isHidden) {
                        nominalText.classList.add('hidden');
                        dotsPlaceholder.classList.remove('hidden');
                        iconElement.classList.remove('bx-hide');
                        iconElement.classList.add('bx-show');
                        icon.classList.remove('text-green-600');
                        icon.classList.add('text-gray-400');
                    } else {
                        nominalText.classList.remove('hidden');
                        dotsPlaceholder.classList.add('hidden');
                        iconElement.classList.remove('bx-show');
                        iconElement.classList.add('bx-hide');
                        icon.classList.remove('text-gray-400');
                        icon.classList.add('text-green-600');
                    }
                }
            });
        }
    });
</script>
