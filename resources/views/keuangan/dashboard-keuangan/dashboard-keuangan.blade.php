@extends('layouts.contentNavbarLayout')

@section('title', 'Dashboard Keuangan')

@section('page-style')
    <style>
        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--card-accent, #0d6efd);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .chart-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e9ecef;
            overflow: hidden;
            height: 100%;
        }

        .metric-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.75rem;
        }

        .metric-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .metric-change {
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .quick-action-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .quick-action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            text-decoration: none;
            color: inherit;
        }

        .recent-activity-item {
            padding: 1rem;
            border-bottom: 1px solid #f1f3f4;
            transition: all 0.2s ease;
        }

        .recent-activity-item:hover {
            background-color: #f8f9fa;
        }

        .recent-activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
            font-weight: 600;
            color: #6c757d;
        }

        .page-header {
            background: #fff;
            border-radius: 16px;
            color: white;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .metric-value {
                font-size: 1.5rem;
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" class="text-decoration-none">
                    <i class="bx bx-home-alt me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard Keuangan</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="page-header p-4 mb-5">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="fw-bold mb-2">Dashboard Keuangan</h3>
                <p class="mb-0 opacity-75 text-muted">Pantau performa keuangan dan analisis pendapatan secara real-time
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button onclick="refreshDashboard()" class="btn btn-light btn-sm">
                    <i class="bx bx-refresh me-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row g-4 mb-5">
        <!-- Total Revenue -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card p-4" style="--card-accent: #28a745;">
                <div class="d-flex flex-column h-100">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bx bx-trending-up"></i>
                    </div>
                    <div class="metric-label">Total Pendapatan</div>
                    <div class="metric-value text-success" id="totalRevenue">Rp
                        {{ number_format($totalFull ?? 0, 0, ',', '.') }}</div>
                    <div class="metric-change text-success mt-auto">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>+12.5% dari bulan lalu</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card p-4" style="--card-accent: #007bff;">
                <div class="d-flex flex-column h-100">
                    <div class="stat-icon bg-info bg-opacity-10 text-primary">
                        <i class="bx bx-calendar"></i>
                    </div>
                    <div class="metric-label">Pendapatan Non Langganan</div>
                    <div class="metric-value text-primary" id="monthlyRevenue">Rp
                        {{ number_format($nonSubs ?? 0, 0, ',', '.') }}</div>
                    <div class="metric-change text-primary mt-auto">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>+8.2% dari target</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card p-4" style="--card-accent: #ffc107;">
                <div class="d-flex flex-column h-100">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bx bx-time"></i>
                    </div>
                    <div class="metric-label">Pendapatan Langganan</div>
                    <div class="metric-value text-warning" id="pendingPayments">Rp
                        {{ number_format($subs ?? 0, 0, ',', '.') }}</div>
                    <div class="metric-change text-warning mt-auto">
                        <i class="bx bx-down-arrow-alt"></i>
                        <span>-5.1% dari minggu lalu</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card p-4" style="--card-accent: #17a2b8;">
                <div class="d-flex flex-column h-100">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bx bx-receipt"></i>
                    </div>
                    <div class="metric-label">Total Transaksi</div>
                    <div class="metric-value text-info" id="totalTransactions">
                        {{ number_format($totalTransactions ?? 0, 0, ',', '.') }}</div>
                    <div class="metric-change text-info mt-auto">
                        <i class="bx bx-up-arrow-alt"></i>
                        <span>+15 transaksi hari ini</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics Row -->
    <div class="row g-4 mb-5">
        <!-- Revenue Chart -->
        <div class="col-12 col-lg-8">
            <div class="chart-card">
                <div class="p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-semibold text-dark mb-1">Tren Pendapatan</h5>
                            <p class="text-muted small mb-0">Grafik pendapatan langganan & non-langganan</p>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                6 Bulan
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="updateChartPeriod('3')">3 Bulan</a></li>
                                <li><a class="dropdown-item" href="#" onclick="updateChartPeriod('6')">6 Bulan</a></li>
                                <li><a class="dropdown-item" href="#" onclick="updateChartPeriod('12')">12 Bulan</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <canvas id="revenueChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Methods Distribution -->
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="p-4 border-bottom">
                    <h5 class="fw-semibold text-dark mb-1">Metode Pembayaran</h5>
                    <p class="text-muted small mb-0">Distribusi metode pembayaran</p>
                </div>
                <div class="p-4">
                    <canvas id="paymentMethodChart" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions and Recent Activity Row -->
    <div class="row g-4 mb-5">
        <!-- Quick Actions -->
        <div class="col-12 col-lg-6">
            <div class="dashboard-card">
                <div class="p-4 border-bottom">
                    <h5 class="fw-semibold text-dark mb-1">Aksi Cepat</h5>
                    <p class="text-muted small mb-0">Navigasi cepat ke fitur keuangan</p>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('pendapatan') }}" class="quick-action-card d-block p-3 text-center">
                                <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-2">
                                    <i class="bx bx-trending-up"></i>
                                </div>
                                <h6 class="fw-semibold text-dark mb-1">Data Pendapatan</h6>
                                <small class="text-muted">Kelola invoice & tagihan</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('pembayaran') }}" class="quick-action-card d-block p-3 text-center">
                                <div class="stat-icon bg-danger bg-opacity-10 text-danger mx-auto mb-2">
                                    <i class="bx bx-money"></i>
                                </div>
                                <h6 class="fw-semibold text-dark mb-1">Data Pembayaran</h6>
                                <small class="text-muted">Riwayat pembayaran</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" class="quick-action-card d-block p-3 text-center">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-2">
                                    <i class="bx bx-file-blank"></i>
                                </div>
                                <h6 class="fw-semibold text-dark mb-1">Buat Invoice</h6>
                                <small class="text-muted">Invoice baru</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" class="quick-action-card d-block p-3 text-center">
                                <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto mb-2">
                                    <i class="bx bx-bar-chart-alt-2"></i>
                                </div>
                                <h6 class="fw-semibold text-dark mb-1">Laporan</h6>
                                <small class="text-muted">Analisis keuangan</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-12 col-lg-6">
            <div class="dashboard-card">
                <div class="p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-semibold text-dark mb-1">Aktivitas Terbaru</h5>
                            <p class="text-muted small mb-0">Transaksi dan pembayaran terbaru</p>
                        </div>
                        <a href="{{ route('pembayaran') }}" class="btn btn-outline-primary btn-sm">
                            Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="recent-activities" id="recentActivities">
                    <!-- Activities will be loaded here -->
                    <div class="recent-activity-item">
                        <div class="d-flex align-items-center">
                            <div class="activity-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="bx bx-check-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-semibold text-dark mb-1">Pembayaran Diterima</h6>
                                        <p class="text-muted small mb-0">Customer ABC - Paket Premium</p>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold text-success">+Rp 150.000</div>
                                        <small class="text-muted">2 menit lalu</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="recent-activity-item">
                        <div class="d-flex align-items-center">
                            <div class="activity-icon bg-info bg-opacity-10 text-info me-3">
                                <i class="bx bx-file-blank"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-semibold text-dark mb-1">Invoice Dibuat</h6>
                                        <p class="text-muted small mb-0">Customer XYZ - Paket Basic</p>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold text-primary">Rp 75.000</div>
                                        <small class="text-muted">15 menit lalu</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="recent-activity-item">
                        <div class="d-flex align-items-center">
                            <div class="activity-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="bx bx-time"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-semibold text-dark mb-1">Pembayaran Tertunda</h6>
                                        <p class="text-muted small mb-0">Customer DEF - Jatuh tempo hari ini</p>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold text-warning">Rp 100.000</div>
                                        <small class="text-muted">1 jam lalu</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Monthly Summary -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="dashboard-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold text-dark mb-0">Ringkasan Bulan Ini</h6>
                    <i class="bx bx-calendar text-primary"></i>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="fw-bold text-success h5 mb-1" id="monthlyPaid">{{ $monthlyPaid ?? 0 }}</div>
                            <small class="text-muted">Sudah Bayar</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="fw-bold text-danger h5 mb-1" id="monthlyUnpaid">{{ $monthlyUnpaid ?? 0 }}</div>
                            <small class="text-muted">Belum Bayar</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Packages -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="dashboard-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold text-dark mb-0">Paket Terpopuler</h6>
                    <i class="bx bx-package text-info"></i>
                </div>
                <hr>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Paket Premium</span>
                        <span class="badge bg-success bg-opacity-10 text-success">45%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Paket Basic</span>
                        <span class="badge bg-info bg-opacity-10 text-info">35%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Paket Standard</span>
                        <span class="badge bg-warning bg-opacity-10 text-warning">20%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="dashboard-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold text-dark mb-0">Status Pembayaran</h6>
                    <i class="bx bx-pie-chart-alt text-warning"></i>
                </div>
                <hr>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Lunas</span>
                        <span class="fw-semibold text-success" id="paidPercentage">{{ $paidPercentage ?? 0 }}%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Tertunda</span>
                        <span class="fw-semibold text-warning"
                            id="pendingPercentage">{{ $pendingPercentage ?? 0 }}%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Overdue</span>
                        <span class="fw-semibold text-danger"
                            id="overduePercentage">{{ $overduePercentage ?? 0 }}%</span>
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
        // Get the data from PHP
        const monthlyData = @json($monthlyData);

        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.labels,
                datasets: [{
                    label: 'Pendapatan Langganan',
                    data: monthlyData.subscription,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Pendapatan Non-Langganan',
                    data: monthlyData.nonSubscription,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                }
            }
        });

        // Function to update chart period
        window.updateChartPeriod = async function(months) {
            try {
                const response = await fetch(`/dashboard-keuangan/revenue-data?months=${months}`);
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                
                revenueChart.data.labels = data.labels;
                revenueChart.data.datasets[0].data = data.subscription;
                revenueChart.data.datasets[1].data = data.nonSubscription;
                revenueChart.update();
            } catch (error) {
                console.error('Error updating chart:', error);
                alert('Gagal memperbarui data grafik');
            }
        };
    });
</script>
@endsection
