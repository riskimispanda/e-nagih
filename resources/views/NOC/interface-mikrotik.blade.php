@extends('layouts.contentNavbarLayout')

@section('title', 'Interface Mikrotik')

@section('page-style')
<style>
    .traffic-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid #e9ecef;
        overflow: hidden;
        height: 100%;
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .status-online {
        background-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
    }

    .status-offline {
        background-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
    }

    .metric-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        border: 1px solid #e9ecef;
    }

    .metric-value {
        font-size: 1.25rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
    }

    .metric-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .chart-container {
        position: relative;
        height: 400px;
        padding: 1rem;
    }

    .refresh-indicator {
        display: inline-flex;
        align-items: center;
        font-size: 0.875rem;
        color: #6c757d;
    }

    .refresh-indicator .spinner-border {
        width: 1rem;
        height: 1rem;
        margin-right: 0.5rem;
    }
</style>
@endsection

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/profile/paket">Router & Profile</a>
        </li>
        <li class="breadcrumb-item active">Server Mikrotik</li>
    </ol>
</nav>

<!-- Traffic Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="metric-card">
            <div class="metric-value" id="currentRx">0 bps</div>
            <div class="metric-label">Download Saat Ini</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="metric-card">
            <div class="metric-value" id="currentTx">0 bps</div>
            <div class="metric-label">Upload Saat Ini</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="metric-card">
            <div class="metric-value" id="maxRx">0 bps</div>
            <div class="metric-label">Max Download</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="metric-card">
            <div class="metric-value" id="maxTx">0 bps</div>
            <div class="metric-label">Max Upload</div>
        </div>
    </div>
</div>

<!-- Main Traffic Chart -->
<div class="row">
    <div class="col-12">
        <div class="card traffic-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">
                        <span class="status-indicator status-online" id="connectionStatus"></span>
                        Realtime Traffic Monitoring
                    </h5>
                    <p class="card-subtitle text-muted mb-0">Monitor bandwidth usage secara real-time</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="refresh-indicator" id="refreshStatus">
                        <span class="spinner-border text-primary" role="status" aria-hidden="true"></span>
                        Memperbarui...
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-cog"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="changeRefreshRate(1000)">1 detik</a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeRefreshRate(3000)">3 detik</a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeRefreshRate(5000)">5 detik</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="clearChart()">Reset Chart</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="chart-container">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('trafficChart').getContext('2d');
    let refreshInterval = 3000;
    let refreshTimer;
    let maxRxValue = 0;
    let maxTxValue = 0;

    // Chart configuration with improved styling
    let chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Download (Rx)',
                data: [],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#28a745',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2
            }, {
                label: 'Upload (Tx)',
                data: [],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#007bff',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 0
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12,
                            family: 'Public Sans'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatBytes(context.parsed.y) + '/s';
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Waktu',
                        font: {
                            size: 12,
                            family: 'Public Sans'
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        maxTicksLimit: 10,
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Kecepatan',
                        font: {
                            size: 12,
                            family: 'Public Sans'
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return formatBytes(value) + '/s';
                        },
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });

    // Format bytes function
    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // Update chart function
    function updateChart() {
        const refreshStatus = document.getElementById('refreshStatus');
        refreshStatus.innerHTML = '<span class="spinner-border text-primary" role="status" aria-hidden="true"></span> Memperbarui...';

        fetch(`/noc/interface/{{ $router_id }}/realtime`)
            .then(response => response.json())
            .then(data => {
                let now = new Date().toLocaleTimeString();

                // Limit data points to 30 for better performance
                if (chart.data.labels.length > 30) {
                    chart.data.labels.shift();
                    chart.data.datasets[0].data.shift();
                    chart.data.datasets[1].data.shift();
                }

                chart.data.labels.push(now);
                chart.data.datasets[0].data.push(data.tx);
                chart.data.datasets[1].data.push(data.rx);
                chart.update('none');

                // Update current values
                document.getElementById('currentRx').textContent = formatBytes(data.tx) + '/s';
                document.getElementById('currentTx').textContent = formatBytes(data.rx) + '/s';

                // Update max values
                if (data.tx > maxRxValue) {
                    maxRxValue = data.tx;
                    document.getElementById('maxRx').textContent = formatBytes(maxRxValue) + '/s';
                }
                if (data.rx > maxTxValue) {
                    maxTxValue = data.rx;
                    document.getElementById('maxTx').textContent = formatBytes(maxTxValue) + '/s';
                }

                // Update connection status
                const statusIndicator = document.getElementById('connectionStatus');
                statusIndicator.className = 'status-indicator status-online';

                refreshStatus.innerHTML = '<i class="bx bx-check-circle text-success"></i> Diperbarui';
                setTimeout(() => {
                    refreshStatus.innerHTML = '<i class="bx bx-time text-muted"></i> Menunggu...';
                }, 1000);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                const statusIndicator = document.getElementById('connectionStatus');
                statusIndicator.className = 'status-indicator status-offline';

                const refreshStatus = document.getElementById('refreshStatus');
                refreshStatus.innerHTML = '<i class="bx bx-error-circle text-danger"></i> Error';
            });
    }

    // Change refresh rate function
    function changeRefreshRate(rate) {
        refreshInterval = rate;
        clearInterval(refreshTimer);
        refreshTimer = setInterval(updateChart, refreshInterval);
    }

    // Clear chart function
    function clearChart() {
        chart.data.labels = [];
        chart.data.datasets[0].data = [];
        chart.data.datasets[1].data = [];
        chart.update();
        maxRxValue = 0;
        maxTxValue = 0;
        document.getElementById('maxRx').textContent = '0 B/s';
        document.getElementById('maxTx').textContent = '0 B/s';
    }

    // Initialize
    refreshTimer = setInterval(updateChart, refreshInterval);
    updateChart(); // Initial load
</script>
@endsection
