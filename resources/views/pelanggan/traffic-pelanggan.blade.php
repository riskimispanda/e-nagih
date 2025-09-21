@extends('layouts.contentNavbarLayout')
@section('title', 'Traffic Pelanggan')

@section('page-style')
<style>
    /* Sneat Template Styling */
    .card {
        border: 1px solid #d9dee3;
        box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
    }
    
    .card:hover {
        box-shadow: 0 4px 12px 0 rgba(67, 89, 113, 0.16);
        transform: translateY(-2px);
    }
    
    .card-header {
        background: transparent;
        border-bottom: 1px solid #d9dee3;
        padding: 1.5rem 1.5rem 1rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Stats Cards */
    .stats-card {
        position: relative;
        overflow: hidden;
    }
    
    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--card-color);
        opacity: 0.8;
    }
    
    .stats-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        margin-bottom: 0.75rem;
    }
    
    .stats-value {
        font-size: 1.5rem;
        font-weight: 600;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }
    
    .stats-label {
        font-size: 0.8125rem;
        color: #a1acb8;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    
    .stats-subtitle {
        font-size: 0.75rem;
        color: #a1acb8;
        margin-top: 0.25rem;
    }
    
    /* Network Info Cards */
    .info-item {
        background: #f8f9fa;
        border-radius: 0.375rem;
        padding: 1rem;
        text-align: center;
        border: 1px solid #e7eaf3;
        transition: all 0.2s ease-in-out;
    }
    
    .info-item:hover {
        background: #fff;
        border-color: #696cff;
        transform: translateY(-1px);
    }
    
    .info-item i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .info-item .info-label {
        font-size: 0.75rem;
        color: #a1acb8;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 0.25rem;
    }
    
    .info-item .info-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: #566a7f;
    }
    
    /* Loading States */
    .loading-spinner {
        width: 1rem;
        height: 1rem;
        border: 2px solid #e7eaf3;
        border-top: 2px solid #696cff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        display: inline-block;
        margin-right: 0.5rem;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Chart Container */
    .chart-wrapper {
        position: relative;
        height: 400px;
    }
    
    /* Header Gradient */
    .header-gradient {
        background: linear-gradient(135deg, #696cff 0%, #5a67d8 100%);
        color: white;
    }
    
    /* Status Colors */
    .text-online { color: #71dd37 !important; }
    .text-offline { color: #8592a3 !important; }
    .text-error { color: #ff3e1d !important; }
    
    /* Responsive */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .stats-icon {
            width: 2.25rem;
            height: 2.25rem;
            font-size: 1rem;
        }
        
        .stats-value {
            font-size: 1.25rem;
        }
        
        .chart-wrapper {
            height: 300px;
        }
    }
    
    /* Animation */
    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Status indicator */
    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
        animation: pulse 2s infinite;
    }
    
    .status-indicator.online {
        background-color: #71dd37;
    }
    
    .status-indicator.offline {
        background-color: #8592a3;
        animation: none;
    }
    
    .status-indicator.error {
        background-color: #ff3e1d;
        animation: none;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(113, 221, 55, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(113, 221, 55, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(113, 221, 55, 0);
        }
    }
</style>
@endsection

@section('content')
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        <div class="mb-3 mb-md-0">
                            <h4 class="fw-bold mb-2">
                                <i class="bx bx-wifi me-2"></i>Traffic Monitor
                            </h4>
                            <h5 class="mb-1 opacity-90">{{ $pelanggan->nama_customer }}</h5>
                            <p class="mb-0 opacity-75">
                                <i class="bx bx-time me-1"></i>Real-time network traffic monitoring
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <button id="autoRefreshBtn" class="btn btn-warning btn-sm" onclick="toggleAutoRefresh()">
                                <i class="bx bx-refresh me-1"></i>
                                <span id="autoRefreshText">Auto Refresh: ON</span>
                            </button>
                            <a href="javascript:window.history.back()" class="btn btn-light btn-sm">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Traffic Statistics -->
    <div class="row g-4 mb-4">
        <!-- Download Speed -->
        <div class="col-xl-4 col-md-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="bx bx-download"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stats-label">Download Speed</div>
                            <div id="rx" class="stats-value text-primary">
                                <span class="loading-spinner"></span>0 bps
                            </div>
                            <div class="stats-subtitle">Real-time</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Speed -->
        <div class="col-xl-4 col-md-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="bx bx-upload"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stats-label">Upload Speed</div>
                            <div id="tx" class="stats-value text-success">
                                <span class="loading-spinner"></span>0 bps
                            </div>
                            <div class="stats-subtitle">Real-time</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Connection Status -->
        <div class="col-xl-4 col-md-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bx bx-signal-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stats-label">Status Koneksi</div>
                            <div id="status" class="stats-value text-warning">
                                <span class="status-indicator offline"></span>Checking...
                            </div>
                            <div id="uptime" class="stats-subtitle">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Information -->
    <div class="row g-4 mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-network-chart text-primary me-2"></i>Informasi Jaringan
                    </h5>
                </div>
                <div class="card-body mt-5">
                    <!-- Connection Details -->
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="info-item">
                                <i class="bx bx-globe text-primary"></i>
                                <div class="info-label">Customer IP</div>
                                <div id="customer_ip" class="info-value">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <i class="bx bx-server text-success"></i>
                                <div class="info-label">Local IP</div>
                                <div id="ip_local" class="info-value">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <i class="bx bx-chip text-info"></i>
                                <div class="info-label">MAC Address</div>
                                <div id="mac" class="info-value">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <i class="bx bx-package text-warning"></i>
                                <div class="info-label">Profile</div>
                                <div id="profile" class="info-value">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Traffic Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-line-chart text-primary me-2"></i>Real-time Traffic Chart
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="trafficChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let autoRefresh = true;
    let refreshInterval;
    
    // Utility functions
    function formatBits(bits) {
        if (bits === 0) return '0 bps';
        const sizes = ['bps', 'Kbps', 'Mbps', 'Gbps'];
        const i = Math.floor(Math.log(bits) / Math.log(1000));
        return (bits / Math.pow(1000, i)).toFixed(2) + ' ' + sizes[i];
    }

    function getStatusClass(status) {
        const classes = {
            'online': 'text-online',
            'success': 'text-online',
            'offline': 'text-offline',
            'estimated': 'text-warning',
            'error': 'text-error'
        };
        return classes[status] || 'text-muted';
    }

    function removeLoadingSpinners() {
        const spinners = document.querySelectorAll('.loading-spinner');
        spinners.forEach(spinner => spinner.remove());
    }

    // Chart setup
    let downloadData = [];
    let uploadData = [];
    let labels = [];
    let maxDataPoints = 30; // Show last 30 data points

    const ctx = document.getElementById('trafficChart').getContext('2d');
    const trafficChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Download Speed',
                    data: downloadData,
                    borderColor: '#696cff',
                    backgroundColor: 'rgba(105, 108, 255, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#696cff',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Upload Speed',
                    data: uploadData,
                    borderColor: '#71dd37',
                    backgroundColor: 'rgba(113, 221, 55, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#71dd37',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: { 
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 14,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(67, 89, 113, 0.95)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#696cff',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatBits(context.raw);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: '#eceef1',
                        borderColor: '#d9dee3'
                    },
                    ticks: {
                        color: '#a1acb8',
                        font: {
                            size: 12
                        },
                        maxTicksLimit: 10
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#eceef1',
                        borderColor: '#d9dee3'
                    },
                    ticks: {
                        color: '#a1acb8',
                        font: {
                            size: 12
                        },
                        callback: function(value) { 
                            return formatBits(value); 
                        }
                    }
                }
            },
            animation: {
                duration: 750,
                easing: 'easeInOutQuart'
            }
        }
    });

    function loadTraffic() {
        fetch("{{ route('pelanggan-traffic-data', $pelanggan->id) }}")
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('Traffic data received:', data);
                
                // Remove loading spinners on first successful load
                removeLoadingSpinners();
                
                // Update traffic stats (correct mapping: rx = download, tx = upload)
                document.getElementById("rx").textContent = formatBits(data.tx || 0);
                document.getElementById("tx").textContent = formatBits(data.rx || 0);

                // Update connection info
                document.getElementById("uptime").textContent = data.uptime || '-';
                
                const statusElement = document.getElementById("status");
                const status = data.status || 'offline';
                statusElement.innerHTML = `<span class="status-indicator ${status === 'online' ? 'online' : status === 'offline' ? 'offline' : 'error'}"></span>${status.charAt(0).toUpperCase() + status.slice(1)}`;
                statusElement.className = `stats-value ${getStatusClass(status)}`;
                
                // Update network info
                document.getElementById("ip_local").textContent = data.ip_local || '-';
                document.getElementById("mac").textContent = data.mac_address || '-';
                document.getElementById("profile").textContent = data.profile || '-';
                document.getElementById("customer_ip").textContent = data.ip_remote || '-';

                // Update chart data
                const now = new Date().toLocaleTimeString('en-US', { 
                    hour12: false,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                
                labels.push(now);
                downloadData.push(data.tx || 0); // rx = download
                uploadData.push(data.rx || 0);   // tx = upload

                // Keep only the last maxDataPoints
                if (labels.length > maxDataPoints) {
                    labels.shift();
                    downloadData.shift();
                    uploadData.shift();
                }

                // Update chart with smooth animation
                trafficChart.update('active');
                
                // Add fade-in effect to updated elements
                document.getElementById("rx").classList.add('fade-in');
                document.getElementById("tx").classList.add('fade-in');
                
                // Remove fade-in class after animation
                setTimeout(() => {
                    document.getElementById("rx").classList.remove('fade-in');
                    document.getElementById("tx").classList.remove('fade-in');
                }, 300);
            })
            .catch(err => {
                console.error("Failed to load traffic data:", err);
                
                // Show error state
                removeLoadingSpinners();
                document.getElementById("rx").textContent = "Error";
                document.getElementById("tx").textContent = "Error";
                
                const statusElement = document.getElementById("status");
                statusElement.innerHTML = '<span class="status-indicator error"></span>Error';
                statusElement.className = 'stats-value text-error';
            });
    }

    // Global functions
    window.toggleAutoRefresh = function() {
        autoRefresh = !autoRefresh;
        const text = document.getElementById('autoRefreshText');
        const button = document.getElementById('autoRefreshBtn');
        
        if (autoRefresh) {
            text.textContent = 'Auto Refresh: ON';
            button.className = 'btn btn-warning btn-sm'; // Warning color when ON
            startAutoRefresh();
        } else {
            text.textContent = 'Auto Refresh: OFF';
            button.className = 'btn btn-secondary btn-sm'; // Secondary color when OFF
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
    };

    function startAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        refreshInterval = setInterval(loadTraffic, 1000); // Update every 1 second
    }

    // Initialize
    console.log('Initializing traffic monitor...');
    loadTraffic();
    
    if (autoRefresh) {
        startAutoRefresh();
    }
    
    // Handle page visibility change to pause/resume when tab is not active
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        } else if (autoRefresh) {
            startAutoRefresh();
        }
    });
});
</script>
@endsection