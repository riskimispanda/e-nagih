@extends('layouts.contentNavbarLayout')

@section('title', 'Laporan')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

<style>
    .card{
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .card:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.3) !important;
    }
</style>

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title fw-bold">Laporan Keuangan</h4>
                <small class="card-subtitle text-muted">Laporan Keuangan Perusahaan</small>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 mb-3">
                        <label for="yearFilter" class="form-label">Tahun</label>
                        <select class="form-select" id="yearFilter" onchange="loadLaporanData()">
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="monthFilter" class="form-label">Bulan</label>
                        <select class="form-select" id="monthFilter" onchange="loadLaporanData()">
                            <option value="all">Semua Bulan</option>
                            <option value="01" {{ date('m') == '01' ? 'selected' : '' }}>Januari</option>
                            <option value="02" {{ date('m') == '02' ? 'selected' : '' }}>Februari</option>
                            <option value="03" {{ date('m') == '03' ? 'selected' : '' }}>Maret</option>
                            <option value="04" {{ date('m') == '04' ? 'selected' : '' }}>April</option>
                            <option value="05" {{ date('m') == '05' ? 'selected' : '' }}>Mei</option>
                            <option value="06" {{ date('m') == '06' ? 'selected' : '' }}>Juni</option>
                            <option value="07" {{ date('m') == '07' ? 'selected' : '' }}>Juli</option>
                            <option value="08" {{ date('m') == '08' ? 'selected' : '' }}>Agustus</option>
                            <option value="09" {{ date('m') == '09' ? 'selected' : '' }}>September</option>
                            <option value="10" {{ date('m') == '10' ? 'selected' : '' }}>Oktober</option>
                            <option value="11" {{ date('m') == '11' ? 'selected' : '' }}>November</option>
                            <option value="12" {{ date('m') == '12' ? 'selected' : '' }}>Desember</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-flex gap-2 justify-content-end align-items-center">
                            <small class="text-muted" id="lastUpdateTime">Terakhir diperbarui: -</small>
                            <button class="btn btn-warning btn-sm" onclick="loadLaporanData()" id="refreshBtn">
                                <i class="bx bx-refresh"></i> Refresh Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4" id="summaryCards">
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-success bg-opacity-10 text-success">
                                    <i class="bx bx-credit-card"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mb-1">Pendapatan Langganan</p>
                        <h5 class="card-title mb-3 fw-semibold" id="totalSubscription">Rp 0</h5>
                        <small class="text-success fw-medium" id="subscriptionGrowth">
                            <i class="bx bx-up-arrow-alt"></i> 0%
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-info bg-opacity-10 text-info">
                                    <i class="bx bx-wallet-alt"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mb-1">Pendapatan Non-Langganan</p>
                        <h5 class="card-title mb-3 fw-semibold" id="totalNonSubscription">Rp 0</h5>
                        <small class="text-info fw-medium" id="nonSubscriptionGrowth">
                            <i class="bx bx-up-arrow-alt"></i> 0%
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-danger bg-opacity-10 text-danger">
                                    <i class="bx bx-trending-down"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mb-1">Total Pengeluaran</p>
                        <h5 class="card-title mb-3 fw-semibold" id="totalExpenses">Rp 0</h5>
                        <small class="text-danger fw-medium" id="expensesGrowth">
                            <i class="bx bx-down-arrow-alt"></i> 0%
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-warning bg-opacity-10 text-warning">
                                    <i class="bx bx-trending-up"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mb-1">Laba/Rugi</p>
                        <h5 class="card-title mb-3 fw-semibold" id="profitLoss">Rp 0</h5>
                        <small class="text-warning fw-medium" id="profitLossGrowth">
                            <i class="bx bx-up-arrow-alt"></i> 0%
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-warning bg-opacity-10 text-warning">
                                    <i class="bx bx-credit-card"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mb-1">Total Saldo Kas</p>
                        <h5 class="card-title mb-3 fw-semibold" id="totalKasSaldo">Rp 0</h5>
                        <small class="text-warning fw-medium">
                            <i class="bx bx-credit-card"></i> Saldo Aktif
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-info bg-opacity-10 text-info">
                                    <i class="bx bx-user"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mb-1">Total Pelanggan</p>
                        <h5 class="card-title mb-3 fw-semibold" id="totalCustomers">0</h5>
                        <h6 class="card-title mb-3 fw-semibold" id="totalPendapatan">Rp. 0</h6>
                        <small class="text-info fw-medium">
                            <i class="bx bx-user"></i> Pelanggan Aktif
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-success bg-opacity-10 text-success">
                                    <i class="bx bx-check-circle"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mb-1">Pelanggan Lunas</p>
                        <h5 class="card-title mb-3 fw-semibold" id="paidCustomers">0</h5>
                        <h6 class="card-title mb-3 fw-semibold" id="pelangganLunas">Rp 0</h6>
                        <small class="text-success fw-medium">
                            <i class="bx bx-calendar"></i> Bulan <span id="currentMonthName"></span>
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-danger bg-opacity-10 text-danger">
                                    <i class="bx bx-x-circle"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mb-1">Pelanggan Belum Lunas</p>
                        <h5 class="card-title mb-3 fw-semibold" id="unpaidCustomers">0</h5>
                        <h6 class="card-title mb-3 fw-semibold" id="pelangganBelumLunas">Rp 0</h6>
                        <small class="text-danger fw-medium">
                            <i class="bx bx-calendar"></i> Bulan <span id="currentMonthName2"></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Difference Statistics -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0 fw-bold">Selisih Pendapatan Bulanan</h5>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-warning bg-opacity-10 text-warning">
                                    <i class="bx bx-calendar"></i>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <h4 class="mb-0 me-2 fw-semibold" id="monthlyRevenueDifference">Rp 0</h4>
                            <small class="text-muted" id="monthlyRevenuePercentage">0%</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="d-block text-muted">Bulan Ini</span>
                                <span class="fw-semibold" id="currentMonthRevenue">Rp 0</span>
                            </div>
                            <div>
                                <span class="d-block text-muted">Bulan Lalu</span>
                                <span class="fw-semibold" id="prevMonthRevenue">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0 fw-bold">Selisih Pendapatan Tahunan</h5>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-warning bg-opacity-10 text-warning">
                                    <i class="bx bx-line-chart"></i>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <h4 class="mb-0 me-2 fw-semibold" id="yearlyRevenueDifference">Rp 0</h4>
                            <small class="text-muted" id="yearlyRevenuePercentage">0%</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="d-block text-muted">Tahun Ini</span>
                                <span class="fw-semibold" id="currentYearRevenue">Rp 0</span>
                            </div>
                            <div>
                                <span class="d-block text-muted">Tahun Lalu</span>
                                <span class="fw-semibold" id="prevYearRevenue">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <!-- Revenue Trend Chart -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tren Pendapatan Bulanan</h5>
                        <small class="text-muted">Perbandingan pendapatan langganan dan non-langganan</small>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <!-- Cash Flow Chart -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Arus Kas</h5>
                        <small class="text-muted">Debit vs Kredit</small>
                    </div>
                    <div class="card-body">
                        <canvas id="cashFlowChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- RAB Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Realisasi Anggaran (RAB)</h5>
                        <small class="text-muted">Perbandingan anggaran vs realisasi</small>
                    </div>
                    <div class="card-body">
                        <canvas id="rabChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="row mb-4">
            <!-- Pendapatan Non-Langganan Table -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Pendapatan Non-Langganan</h5>
                            <small class="text-muted">Data pendapatan di luar langganan</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#pendapatanTableCollapse" aria-expanded="true">
                                <i class="bx bx-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="collapse show" id="pendapatanTableCollapse">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="pendapatanTable">
                                    <thead class="table-dark fw-bold text-center">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Jenis Pendapatan</th>
                                            <th>Deskripsi</th>
                                            <th>Metode Bayar</th>
                                            <th>Jumlah</th>
                                            <th>Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pendapatanTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pembayaran Langganan Table -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Pembayaran Langganan</h5>
                            <small class="text-muted">Data pembayaran pelanggan</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#pembayaranTableCollapse" aria-expanded="true">
                                <i class="bx bx-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="collapse show" id="pembayaranTableCollapse">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="pembayaranTable">
                                    <thead class="table-dark fw-bold text-center">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Pelanggan</th>
                                            <th>Paket</th>
                                            <th>Metode Bayar</th>
                                            <th>Jumlah</th>
                                            <th>Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pembayaranTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RAB Table -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Rencana Anggaran Biaya (RAB)</h5>
                            <small class="text-muted">Data anggaran dan realisasi</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#rabTableCollapse" aria-expanded="true">
                                <i class="bx bx-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="collapse show" id="rabTableCollapse">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="rabTable">
                                    <thead class="table-dark fw-bold text-center">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Keterangan</th>
                                            <th>Anggaran</th>
                                            <th>Realisasi</th>
                                            <th>Sisa</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rabTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kas Table -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Arus Kas</h5>
                            <small class="text-muted">Data keluar masuk kas</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#kasTableCollapse" aria-expanded="true">
                                <i class="bx bx-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="collapse show" id="kasTableCollapse">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="kasTable">
                                    <thead class="table-dark fw-bold text-center">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Keterangan</th>
                                            <th>Debit</th>
                                            <th>Kredit</th>
                                            <th>Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody id="kasTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengeluaran Table -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Pengeluaran</h5>
                            <small class="text-muted">Data pengeluaran perusahaan</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#pengeluaranTableCollapse" aria-expanded="true">
                                <i class="bx bx-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="collapse show" id="pengeluaranTableCollapse">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="pengeluaranTable">
                                    <thead class="table-dark fw-bold text-center">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Kategori</th>
                                            <th>Keterangan</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pengeluaranTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let revenueChart, cashFlowChart, rabChart;

    let autoRefreshInterval;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts and load data
        loadLaporanData();

        // Set up auto-refresh every 3 seconds (3000 milliseconds)
        autoRefreshInterval = setInterval(function() {
            loadLaporanData(true);
        }, 3000);
    });

    // Clean up interval when page is unloaded
    window.addEventListener('beforeunload', function() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    });

    // Format currency
    function formatRupiah(angka) {
        return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
    }

    // Load data from API
    async function loadLaporanData(silent = false) {
        try {
            const year = document.getElementById('yearFilter').value;
            const month = document.getElementById('monthFilter').value;
            const response = await fetch(`/laporan/data?year=${year}&month=${month}`);

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            updateSummaryCards(data.summary);
            renderCharts(data.charts);
            populateTables(data.tables);

            // Update last refresh time
            updateLastRefreshTime();
        } catch (error) {
            console.error('Error loading data:', error);
            if (!silent) {
                alert('Gagal memuat data laporan. Silakan coba lagi.');
            }
        }
    }

    // Update last refresh time display
    function updateLastRefreshTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('lastUpdateTime').textContent = `Terakhir diperbarui: ${timeString}`;
    }

    // Update summary cards
    function updateSummaryCards(summary) {
        document.getElementById('totalSubscription').textContent = formatRupiah(summary.totalSubscription);
        document.getElementById('totalNonSubscription').textContent = formatRupiah(summary.totalNonSubscription);
        document.getElementById('totalExpenses').textContent = formatRupiah(summary.totalExpenses);
        document.getElementById('profitLoss').textContent = formatRupiah(summary.profitLoss);

        // Update additional cards
        document.getElementById('totalKasSaldo').textContent = formatRupiah(summary.totalKasSaldo);
        document.getElementById('totalCustomers').textContent = summary.totalCustomers;
        document.getElementById('totalPendapatan').textContent = formatRupiah(summary.totalPendapatan);
        document.getElementById('paidCustomers').textContent = summary.paidCustomers;
        document.getElementById('pelangganLunas').textContent = formatRupiah(summary.pelangganLunas);
        document.getElementById('unpaidCustomers').textContent = summary.unpaidCustomers;
        document.getElementById('pelangganBelumLunas').textContent = formatRupiah(summary.pelangganBelumLunas);

        // Update current month name for customer statistics
        const currentMonthName = getCurrentMonthName();
        document.getElementById('currentMonthName').textContent = currentMonthName;
        document.getElementById('currentMonthName2').textContent = currentMonthName;

        // Update growth indicators
        updateGrowthIndicator('subscriptionGrowth', summary.growth.subscription);
        updateGrowthIndicator('nonSubscriptionGrowth', summary.growth.nonSubscription);
        updateGrowthIndicator('expensesGrowth', summary.growth.expenses);
        updateGrowthIndicator('profitLossGrowth', summary.growth.profit);

        // Update revenue difference statistics
        updateRevenueDifferenceStats(summary);
    }

    // Get month name in Indonesian
    function getCurrentMonthName() {
        const months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        const monthFilter = document.getElementById('monthFilter').value;
        if (monthFilter === 'all') {
            const currentMonth = new Date().getMonth();
            return months[currentMonth];
        } else {
            // Convert "01" to 0, "02" to 1, etc.
            const monthIndex = parseInt(monthFilter, 10) - 1;
            return months[monthIndex];
        }
    }

    function updateRevenueDifferenceStats(summary) {
        // Monthly revenue difference
        const monthlyDiff = summary.monthlyRevenueDifference;
        const monthlyPercentage = summary.prevMonthRevenue > 0 ?
            ((monthlyDiff / summary.prevMonthRevenue) * 100).toFixed(1) : 0;

        document.getElementById('monthlyRevenueDifference').textContent = formatRupiah(monthlyDiff);
        document.getElementById('monthlyRevenuePercentage').textContent =
            (monthlyDiff >= 0 ? '+' : '') + monthlyPercentage + '%';
        document.getElementById('monthlyRevenuePercentage').className =
            monthlyDiff >= 0 ? 'text-success' : 'text-danger';

        document.getElementById('currentMonthRevenue').textContent = formatRupiah(summary.currentMonthRevenue);
        document.getElementById('prevMonthRevenue').textContent = formatRupiah(summary.prevMonthRevenue);

        // Yearly revenue difference
        const yearlyDiff = summary.yearlyRevenueDifference;
        const prevYearRevenue = summary.totalRevenue - yearlyDiff;
        const yearlyPercentage = prevYearRevenue > 0 ?
            ((yearlyDiff / prevYearRevenue) * 100).toFixed(1) : 0;

        document.getElementById('yearlyRevenueDifference').textContent = formatRupiah(yearlyDiff);
        document.getElementById('yearlyRevenuePercentage').textContent =
            (yearlyDiff >= 0 ? '+' : '') + yearlyPercentage + '%';
        document.getElementById('yearlyRevenuePercentage').className =
            yearlyDiff >= 0 ? 'text-success' : 'text-danger';

        document.getElementById('currentYearRevenue').textContent = formatRupiah(summary.totalRevenue);
        document.getElementById('prevYearRevenue').textContent = formatRupiah(prevYearRevenue);
    }

    function updateGrowthIndicator(elementId, growth) {
        const element = document.getElementById(elementId);
        const isPositive = growth >= 0;
        const icon = isPositive ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt';
        const colorClass = isPositive ? 'text-success' : 'text-danger';

        element.className = `fw-medium ${colorClass}`;
        element.innerHTML = `<i class="bx ${icon}"></i> ${Math.abs(growth)}%`;
    }

    // Render charts
    function renderCharts(chartData) {
        renderRevenueChart(chartData.monthly);
        renderCashFlowChart(chartData.cashFlow);
        renderRabChart(chartData.rab);
    }

    function renderRevenueChart(monthlyData) {
        const ctx = document.getElementById('revenueChart').getContext('2d');

        if (revenueChart) {
            revenueChart.destroy();
        }

        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
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
                }, {
                    label: 'Pengeluaran',
                    data: monthlyData.expenses,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
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
                                return formatRupiah(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCashFlowChart(cashFlowData) {
        const ctx = document.getElementById('cashFlowChart').getContext('2d');

        if (cashFlowChart) {
            cashFlowChart.destroy();
        }

        cashFlowChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Debit (Masuk)', 'Kredit (Keluar)'],
                datasets: [{
                    data: [cashFlowData.debit, cashFlowData.kredit],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    function renderRabChart(rabData) {
        const ctx = document.getElementById('rabChart').getContext('2d');

        if (rabChart) {
            rabChart.destroy();
        }

        rabChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: rabData.categories,
                datasets: [{
                    label: 'Anggaran',
                    data: rabData.budget,
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                    borderColor: '#007bff',
                    borderWidth: 1
                }, {
                    label: 'Realisasi',
                    data: rabData.realization,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: '#28a745',
                    borderWidth: 1
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
                                return formatRupiah(value);
                            }
                        }
                    }
                }
            }
        });
    }

    // Populate tables with data
    function populateTables(tableData) {
        populatePendapatanTable(tableData.nonSubscription);
        populatePembayaranTable(tableData.subscription);
        populateRabTable(tableData.rab);
        populateKasTable(tableData.kas);
        populatePengeluaranTable(tableData.expenses);
    }

    function populatePendapatanTable(data) {
        const tableBody = document.getElementById('pendapatanTableBody');
        tableBody.innerHTML = '';

        if (data && data.length > 0) {
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="text-center">${formatDate(item.tanggal)}</td>
                    <td class="text-center">
                        <span class="badge bg-warning bg-opacity-10 text-warning">${item.jenis_pendapatan}</span>
                    </td>
                    <td class="text-center">${item.deskripsi}</td>
                    <td class="text-center"><span class="badge bg-info bg-opacity-10 text-info">${item.metode_bayar}</span></td>
                    <td class="text-center">${formatRupiah(item.jumlah_pendapatan)}</td>
                    <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger">${item.user ? item.user.name : '-'}</span></td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>';
        }
    }

    function populatePembayaranTable(data) {
        const tableBody = document.getElementById('pembayaranTableBody');
        tableBody.innerHTML = '';

        if (data && data.length > 0) {
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="text-center">${formatDate(item.tanggal_bayar)}</td>
                    <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning">${item.invoice && item.invoice.customer ? item.invoice.customer.nama_customer : '-'}</span></td>
                    <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning">${item.invoice && item.invoice.paket ? item.invoice.paket.nama_paket : '-'}</span></td>
                    <td class="text-center"><span class="badge bg-info bg-opacity-10 text-info">${item.metode_bayar}</span></td>
                    <td class="text-center">${formatRupiah(item.jumlah_bayar)}</td>
                    <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger">${item.user ? item.user.name : '-'}</span></td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>';
        }
    }

    function populateRabTable(data) {
        const tableBody = document.getElementById('rabTableBody');
        tableBody.innerHTML = '';

        if (data && data.length > 0) {
            data.forEach(item => {
                const realization = item.realization || 0;
                const percentage = item.jumlah_anggaran > 0 ? Math.round((realization / item.jumlah_anggaran) * 100) : 0;
                const remaining = item.jumlah_anggaran - realization;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="text-center">${formatDate(item.created_at)}</td>
                    <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning">${item.keterangan}</span></td>
                    <td class="text-center">${formatRupiah(item.jumlah_anggaran)}</td>
                    <td class="text-center">${formatRupiah(realization)}</td>
                    <td class="text-center">${formatRupiah(remaining)}</td>
                    <td class="text-center">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar ${percentage > 100 ? 'bg-danger' : 'bg-success'}"
                                role="progressbar"
                                style="width: ${Math.min(percentage, 100)}%;"
                                aria-valuenow="${percentage}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                        <small>${percentage}%</small>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>';
        }
    }

    function populateKasTable(data) {
        const tableBody = document.getElementById('kasTableBody');
        tableBody.innerHTML = '';

        if (data && data.length > 0) {
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="text-center">${formatDate(item.tanggal_kas)}</td>
                    <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning">${item.keterangan}</span></td>
                    <td class="text-center">${item.debit > 0 ? formatRupiah(item.debit) : '-'}</td>
                    <td class="text-center">${item.kredit > 0 ? formatRupiah(item.kredit) : '-'}</td>
                    <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger">${item.user ? item.user.name : '-'}</span></td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>';
        }
    }

    function populatePengeluaranTable(data) {
        const tableBody = document.getElementById('pengeluaranTableBody');
        tableBody.innerHTML = '';

        if (data && data.length > 0) {
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="text-center">${formatDate(item.tanggal_pengeluaran)}</td>
                    <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning">${item.kategori || '-'}</span></td>
                    <td class="text-center">${item.keterangan}</td>
                    <td class="text-center">${formatRupiah(item.jumlah_pengeluaran)}</td>
                    <td class="text-center">
                        <span class="badge ${item.status && item.status.nama_status === 'Disetujui' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning'}">
                            ${item.status ? item.status.nama_status : 'Pending'}
                        </span>
                    </td>
                    <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger">${item.user ? item.user.name : '-'}</span></td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>';
        }
    }

    // Helper function to format dates
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }
</script>
@endsection