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

    {{-- Download (RX) --}}
    <div class="col-sm-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="fw-bold"><i class="bx bx-download me-2"></i>Download</h5>
                <p id="rx" class="fw-semibold fs-4 text-primary">0 bps</p>
            </div>
        </div>
    </div>

    {{-- Upload (TX) --}}
    <div class="col-sm-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="fw-bold"><i class="bx bx-download me-2"></i>Upload</h5>
                <p id="tx" class="fw-semibold fs-4 text-success">0 bps</p>
            </div>
        </div>
    </div>

    {{-- Uptime --}}
    <div class="col-sm-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="fw-bold">Uptime</h5>
                <p id="uptime" class="fw-semibold fs-4 text-secondary">-</p>
            </div>
        </div>
    </div>

    {{-- Status --}}
    <div class="col-sm-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="fw-bold">Status</h5>
                <p id="status" class="fw-semibold fs-4 text-secondary">-</p>
            </div>
        </div>
    </div>

    {{-- Chart Download/Upload --}}
    <div class="col-sm-12 mt-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="card-title fw-bold">Upload & Download</h6>
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
        if (bits === 0) return '0 Bps';
        const sizes = ['bps', 'Kbps', 'Mbps', 'Gbps'];
        const i = Math.floor(Math.log(bits) / Math.log(1000));
        return (bits / Math.pow(1000, i)).toFixed(2) + ' ' + sizes[i];
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
                    tension: 0.3
                },
                {
                    label: 'Upload (bps)',
                    data: uploadData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.3
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
                document.getElementById("rx").textContent = formatBits(data.tx);
                document.getElementById("tx").textContent = formatBits(data.rx);
                document.getElementById("uptime").textContent = data.uptime ?? '-';
                document.getElementById("status").textContent = data.status ?? '-';

                const now = new Date().toLocaleTimeString();
                labels.push(now);
                downloadData.push(data.tx); // TX → Download
                uploadData.push(data.rx);   // RX → Upload

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
    setInterval(loadTraffic, 2000);
});
</script>