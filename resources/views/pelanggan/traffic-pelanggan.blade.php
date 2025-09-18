@extends('layouts.contentNavbarLayout')
@section('title', 'Traffic Pelanggan')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title fw-bold">
                    Traffic Pelanggan {{ $pelanggan->nama_customer }}
                </h4>
            </div>
        </div>
    </div>

    {{-- Download (Realtime RX) --}}
    <div class="col-sm-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="fw-bold"><i class="bx bx-download me-2"></i>Download Sekarang</h6>
                <p id="rx" class="fw-semibold fs-4 text-primary">0 bps</p>
            </div>
        </div>
    </div>

    {{-- Upload (Realtime TX) --}}
    <div class="col-sm-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="fw-bold"><i class="bx bx-upload me-2"></i>Upload Sekarang</h6>
                <p id="tx" class="fw-semibold fs-4 text-success">0 bps</p>
            </div>
        </div>
    </div>

    {{-- Total Download --}}
    <div class="col-sm-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="fw-bold">Total Download</h6>
                <p id="total_rx" class="fw-semibold fs-5 text-primary">0 B</p>
            </div>
        </div>
    </div>

    {{-- Total Upload --}}
    <div class="col-sm-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="fw-bold">Total Upload</h6>
                <p id="total_tx" class="fw-semibold fs-5 text-success">0 B</p>
            </div>
        </div>
    </div>

    {{-- Info Teknis --}}
    <div class="col-sm-12 mt-3">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="fw-bold">Informasi Koneksi</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2">
                        <p class="fw-bold">Uptime</p>
                        <p id="uptime">-</p>
                    </div>
                    <div class="col-md-2">
                        <p class="fw-bold">Status</p>
                        <p id="status">-</p>
                    </div>
                    <div class="col-md-2">
                        <p class="fw-bold">IP Remote</p>
                        <p id="ip_remote">-</p>
                    </div>
                    <div class="col-md-2">
                        <p class="fw-bold">IP Local</p>
                        <p id="ip_local">-</p>
                    </div>
                    <div class="col-md-2">
                        <p class="fw-bold">MAC</p>
                        <p id="mac">-</p>
                    </div>
                    <div class="col-md-2">
                        <p class="fw-bold">Profile</p>
                        <p id="profile">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Download/Upload --}}
    <div class="col-sm-12 mt-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between">
                <h6 class="card-title fw-bold text-uppercase">Grafik Upload & Download</h6>
                <a href="javascript:window.history.back()" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <canvas id="trafficChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function formatBits(bits) {
        if (bits === 0) return '0 bps';
        const sizes = ['bps', 'Kbps', 'Mbps', 'Gbps'];
        const i = Math.floor(Math.log(bits) / Math.log(1000));
        return (bits / Math.pow(1000, i)).toFixed(2) + ' ' + sizes[i];
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
    }

    let downloadData = [];
    let uploadData = [];
    let labels = [];

    const ctx = document.getElementById('trafficChart').getContext('2d');
    const trafficChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Download (bps)',
                    data: downloadData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Upload (bps)',
                    data: uploadData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatBits(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return formatBits(value); }
                    }
                }
            }
        }
    });

    function loadTraffic() {
        fetch("{{ route('pelanggan-traffic-data', $pelanggan->id) }}")
            .then(res => res.json())
            .then(data => {
                // Update realtime
                document.getElementById("rx").textContent = formatBits(data.tx);
                document.getElementById("tx").textContent = formatBits(data.rx);

                // Update total
                document.getElementById("total_rx").textContent = formatBytes(data.total_tx);
                document.getElementById("total_tx").textContent = formatBytes(data.total_rx);

                // Update info teknis
                document.getElementById("uptime").textContent = data.uptime ?? '-';
                document.getElementById("status").textContent = data.status ?? '-';
                document.getElementById("ip_remote").textContent = data.ip_remote ?? '-';
                document.getElementById("ip_local").textContent = data.ip_local ?? '-';
                document.getElementById("mac").textContent = data.mac_address ?? '-';
                document.getElementById("profile").textContent = data.profile ?? '-';

                // Tambah data ke grafik
                const now = new Date().toLocaleTimeString();
                labels.push(now);
                downloadData.push(data.rx); // RX → Download
                uploadData.push(data.tx);   // TX → Upload

                if (labels.length > 20) {
                    labels.shift();
                    downloadData.shift();
                    uploadData.shift();
                }

                trafficChart.update();
            })
            .catch(err => console.error("❌ Gagal ambil traffic:", err));
    }

    loadTraffic();
    setInterval(loadTraffic, 1000);
});
</script>