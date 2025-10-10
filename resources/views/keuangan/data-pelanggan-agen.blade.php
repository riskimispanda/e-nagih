@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pelanggan Agen')

<style>
    .search-container {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
    }
    
    .modern-table {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .modern-table thead th {
        background: #343a40;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
    }
    
    .modern-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #e9ecef;
    }
    
    .modern-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .modern-table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border: none;
    }
    
    .customer-name {
        color: #495057;
        font-weight: 600;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-weight: 500;
    }
    
    .empty-state-row td {
        padding: 3rem 1rem;
    }
    
    /* Enhanced Statistics Cards */
    .stats-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    }
    
    .stats-card-success::before {
        background: linear-gradient(90deg, #28a745, #20c997, #28a745);
    }
    
    .stats-card-danger::before {
        background: linear-gradient(90deg, #dc3545, #fd7e14, #dc3545);
    }
    
    .stats-card-primary::before {
        background: linear-gradient(90deg, #007bff, #6f42c1, #007bff);
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }
    
    .stats-icon-wrapper {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .stats-icon-wrapper i {
        font-size: 1.5rem;
        color: white;
    }
    
    .stats-number {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }
    
    .stats-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .modern-table {
            font-size: 0.875rem;
        }
        
        .modern-table thead th,
        .modern-table tbody td {
            padding: 0.75rem 0.5rem;
        }
        
        .search-container {
            padding: 1rem;
        }
        
        .stats-card {
            margin-bottom: 1rem;
        }
    }
</style>

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard" class="text-decoration-none">Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/corp/pendapatan" class="text-decoration-none">Langganan</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/data-agen" class="text-decoration-none">Data Agen</a>
        </li>
        <li class="breadcrumb-item active">Data Pelanggan Agen</li>
    </ol>
</nav>

<div class="row">
    <div class="col-12">
        @php
        // Mapping bulan dalam bahasa Indonesia
        $monthNames = [
            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $currentMonthNum = now()->month;
        $currentMonthName = $monthNames[$currentMonthNum];

        // Handle display period
        $displayPeriod = 'Bulan Ini (' . $currentMonthName . ' ' . now()->year . ')';
        $selectedMonth = request()->get('month', $currentMonthNum);

        if($selectedMonth == 'all') {
            $displayPeriod = 'Semua Periode';
        } elseif(isset($monthNames[$selectedMonth])) {
            $displayPeriod = $monthNames[$selectedMonth] . ' ' . now()->year;
        }

        // Tambahkan informasi status filter
        $selectedStatus = request()->get('status', '');
        $displayStatus = '';
        if($selectedStatus) {
            $displayStatus = ' - ' . $selectedStatus;
        }

        // Set selected values untuk dropdown
        $selectedMonthDropdown = request()->get('month', $currentMonthNum);
        $selectedStatusDropdown = request()->get('status', '');
        $selectedPerPage = request()->get('per_page', 10);
        @endphp

        <!-- Info Alert -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bx bx-calendar me-2"></i>
            <strong>Periode Aktif:</strong> Menampilkan data invoice untuk <strong>{{ $displayPeriod }}</strong>.
            @if($selectedMonth == $currentMonthNum)
            Secara default sistem menampilkan invoice bulan sekarang. Gunakan filter bulan untuk melihat periode lain.
            @else
            Gunakan filter bulan untuk mengubah periode tampilan data.
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
        <!-- Header Card -->
        <div class="card mb-3">
            <div class="card-header modern-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title fw-bold mb-1">Data Pelanggan Agen {{ $agen->name }} - {{ $displayPeriod }}{{ $displayStatus }}</h4>
                        <small class="card-subtitle text-muted">Daftar pelanggan periode {{ $displayPeriod }}{{ $displayStatus }} yang terdaftar di bawah agen {{ $agen->name }}</small>
                    </div>
                    <div class="text-end d-flex align-items-center gap-2">
                        <span class="badge bg-danger bg-opacity-10 text-danger fs-6 px-3 py-2">
                            <i class="bx bx-user me-1"></i>{{ $customers->total() }} Pelanggan
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <!-- Filter Status Indicator -->
            <div class="col-12 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i>
                        <span id="statsIndicator">Total berdasarkan filter periode dan status</span>
                    </small>
                    <small class="text-muted" id="filterInfo" style="display: none;">
                        <span class="badge bg-info bg-opacity-10 text-info">
                            <i class="bx bx-search me-1"></i>Pencarian Aktif
                        </span>
                    </small>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stats-card stats-card-success h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon-wrapper bg-success">
                                <i class="bx bx-check-circle"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <!-- HAPUS id="totalPaid" karena tidak di-update oleh JS -->
                                <div class="stats-number text-success">{{ 'Rp ' . number_format($totalPaid, 0, ',', '.') }}</div>
                                <div class="stats-label">Total Sudah Bayar</div>
                                <div class="stats-trend">
                                    <i class="bx bx-trending-up text-success"></i>
                                    <span class="text-success">Berdasarkan filter</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stats-card stats-card-danger h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon-wrapper bg-danger">
                                <i class="bx bx-x-circle"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <!-- HAPUS id="totalUnpaid" -->
                                <div class="stats-number text-danger">{{ 'Rp ' . number_format($totalUnpaid, 0, ',', '.') }}</div>
                                <div class="stats-label">Total Belum Bayar</div>
                                <div class="stats-trend">
                                    <i class="bx bx-trending-down text-danger"></i>
                                    <span class="text-danger">Berdasarkan filter</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stats-card stats-card-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon-wrapper bg-primary">
                                <i class="bx bx-calculator"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <!-- HAPUS id="totalAmount" -->
                                <div class="stats-number text-primary">{{ 'Rp ' . number_format($totalAmount, 0, ',', '.') }}</div>
                                <div class="stats-label">Total Keseluruhan</div>
                                <div class="stats-trend">
                                    <i class="bx bx-bar-chart-alt-2 text-primary"></i>
                                    <span class="text-primary">Berdasarkan filter</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search and Filter Card -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="search-container">
                    <h6 class="mb-3 fw-bold text-dark">
                        <i class="bx bx-search me-2"></i>Filter & Pencarian Data
                    </h6>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Nama Pelanggan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" class="form-control" id="searchName" placeholder="Cari nama pelanggan...">
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Filter Periode Bulan
                                <small class="text-primary">(Default: {{ $currentMonthName }})</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <select class="form-select" id="filterMonth" onchange="filterByMonth()">
                                    <option value="all" {{ $selectedMonthDropdown == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                                    @foreach($monthNames as $monthNum => $monthName)
                                    <option value="{{ $monthNum }}" {{ $selectedMonthDropdown == $monthNum ? 'selected' : '' }}>
                                        {{ $monthName }} {{ now()->year }}
                                        @if($monthNum == $currentMonthNum) (Bulan Ini) @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                Pilih "Semua Bulan" untuk menampilkan data dari semua periode
                            </small>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Status Tagihan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-filter"></i></span>
                                <select class="form-select" id="filterStatus" onchange="filterByStatus()">
                                    @php
                                        $selectedStatus = request()->get('status', '');
                                    @endphp
                                    <option value="" {{ $selectedStatus == '' ? 'selected' : '' }}>Semua Status</option>
                                    <option value="Belum Bayar" {{ $selectedStatus == 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                                    <option value="Sudah Bayar" {{ $selectedStatus == 'Sudah Bayar' ? 'selected' : '' }}>Sudah Bayar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Tampilkan Data</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-list-ul"></i></span>
                                <select class="form-select" id="entriesPerPage" onchange="changeEntriesPerPage()">
                                    @php
                                        $selectedPerPage = request()->get('per_page', 10);
                                    @endphp
                                    <option value="10" {{ $selectedPerPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $selectedPerPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $selectedPerPage == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $selectedPerPage == 100 ? 'selected' : '' }}>100</option>
                                    <option value="all" {{ $selectedPerPage == 'all' ? 'selected' : '' }}>Semua</option>
                                </select>
                            </div>
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                Jumlah data per halaman
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Table Card -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table modern-table" id="customerTable">
                        <thead class="table-dark text-center fw-bold">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>Paket</th>
                                <th>Alamat</th>
                                <th>Status Tagihan</th>
                                <th>Total Tagihan</th>
                                <th>Jatuh Tempo</th>
                                <th>Metode Bayar</th>
                                <th>Tanggal Pembayaran</th>
                                <th>Bukti Pembayaran</th>
                                <th>Status Customer</th>
                                <th>Admin / Agen</th>                                
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @php 
                                $rowNumber = ($customers->currentPage() - 1) * $customers->perPage() + 1;
                            @endphp
                            
                            @forelse ($customers as $customer)
                                @php
                                    $latestInvoice = $customer->invoice->first();
                                    $latestPembayaran = $latestInvoice ? $latestInvoice->pembayaran->first() : null;
                                @endphp
                        
                                <tr class="customer-row" 
                                    data-id="{{ $customer->id }}"
                                    data-nama="{{ strtolower($customer->nama_customer) }}"
                                    data-alamat="{{ strtolower($customer->alamat) }}">
                                    
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="customer-name fw-bold">{{ $customer->nama_customer }}</td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ $customer->paket->nama_paket ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="customer-address">{{ $customer->alamat }}</td>
                                    <td>
                                        @if($latestInvoice && $latestInvoice->status)
                                        <span class="badge
                                                @if($latestInvoice->status->nama_status == 'Sudah Bayar') bg-success bg-opacity-10 text-success
                                                @elseif($latestInvoice->status->nama_status == 'Belum Bayar') bg-danger bg-opacity-10 text-danger
                                                @else bg-secondary bg-opacity-10 text-secondary
                                                @endif">
                                        {{ $latestInvoice->status->nama_status }}
                                    </span>
                                    @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Tidak Ada Invoice</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latestInvoice)
                                        Rp {{ number_format($latestInvoice->tagihan + $latestInvoice->tambahan, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($latestInvoice && $latestInvoice->jatuh_tempo)
                                    @php
                                    try {
                                        $jatuhTempo = \Carbon\Carbon::parse($latestInvoice->jatuh_tempo);
                                        $isOverdue = $jatuhTempo->isPast() && $latestInvoice->status && $latestInvoice->status->nama_status != 'Sudah Bayar';
                                    } catch (\Exception $e) {
                                        $jatuhTempo = null;
                                        $isOverdue = false;
                                    }
                                    @endphp
                                    @if($jatuhTempo)
                                    <span class="badge {{ $isOverdue ? 'bg-danger bg-opacity-10 text-danger' : ($latestInvoice->status && $latestInvoice->status->nama_status == 'Sudah Bayar' ? 'bg-success bg-opacity-10 text-success' : 'bg-info bg-opacity-10 text-info') }}">
                                        {{ $jatuhTempo->format('d M Y') }}
                                        @if($isOverdue)
                                        <i class="bx bx-time-five ms-1"></i>
                                        @endif
                                    </span>
                                    @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Invalid Date</span>
                                    @endif
                                    @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latestPembayaran)
                                        <span class="badge bg-info">{{ $latestPembayaran->metode_bayar }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($latestPembayaran)
                                        <span class="badge bg-info">
                                            {{ \Carbon\Carbon::parse($latestPembayaran->tanggal_bayar)->format('d-m-Y H:i:s') }}
                                        </span>
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latestPembayaran && $latestPembayaran->bukti_bayar)
                                        <a href="{{ asset('storage/' . $latestPembayaran->bukti_bayar) }}" target="_blank"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat Bukti">
                                            <i class="bx bx-image text-primary"></i>
                                        </a>                                     
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($customer->trashed())
                                    <span class="badge bg-label-danger fw-bold">Deaktivasi</span>
                                    @else
                                    <span class="badge bg-label-success fw-bold">Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($latestPembayaran && $latestPembayaran->user)
                                        <span class="fw-bold badge bg-warning bg-opacity-10 text-warning" style="text-transform: uppercase;">
                                            {{ $latestPembayaran->user->name }} / {{ $latestPembayaran->user->roles->name ?? '-' }}
                                        </span>
                                    @elseif($latestPembayaran)
                                        <span class="badge bg-secondary">By Tripay</span>
                                    @else
                                        <span class="fw-bold">-</span>
                                    @endif
                                </td>                            
                            </tr>
                        @empty
                        <tr class="empty-state-row">
                            <td colspan="12" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bx bx-user-x text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-dark mt-3 mb-2">Tidak ada data pelanggan</h5>
                                    <p class="text-muted mb-0">Belum ada pelanggan untuk agen {{ $agen->name }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($customers->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $customers->appends(request()->all())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchName = document.getElementById('searchName');
        const customerRows = document.querySelectorAll('.customer-row');
        const emptyStateRow = document.querySelector('.empty-state-row');
        const statsIndicator = document.getElementById('statsIndicator');
        const filterInfo = document.getElementById('filterInfo');
        
        function filterTable() {
            const nameQuery = searchName.value.toLowerCase();
            let visibleRows = 0;
    
            customerRows.forEach(row => {
                const name = row.dataset.nama || '';
                const alamat = row.dataset.alamat || '';
    
                // Check name match (client-side filter)
                const matchesName = name.includes(nameQuery) || alamat.includes(nameQuery);
    
                if (matchesName) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
    
            // UPDATE: TIDAK ADA PERUBAHAN STATISTICS - biarkan tetap dari server
            // Statistics TIDAK di-update sama sekali
            
            // Update indicator saja
            if (nameQuery) {
                statsIndicator.textContent = `Menampilkan ${visibleRows} dari {{ $customers->total() }} pelanggan`;
                filterInfo.style.display = 'inline-block';
            } else {
                statsIndicator.textContent = `Menampilkan {{ $customers->total() }} pelanggan`;
                filterInfo.style.display = 'none';
            }
            
            // Show/hide empty state
            if (emptyStateRow) {
                if (visibleRows === 0 && customerRows.length > 0) {
                    emptyStateRow.style.display = '';
                    emptyStateRow.querySelector('h5').textContent = 'Tidak ada data yang cocok';
                    emptyStateRow.querySelector('p').textContent = 'Coba ubah kriteria pencarian Anda';
                } else {
                    emptyStateRow.style.display = 'none';
                }
            }
        }
        
        // HAPUS function updateStatistics sama sekali
        
        // Add event listeners
        searchName.addEventListener('input', filterTable);
    
        // ESC key to reset filters
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchName.value = '';
                filterTable();
            }
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initial display
        filterTable();
    });
    
    // Function untuk filter berdasarkan bulan (server-side)
    function filterByMonth() {
        const monthSelect = document.getElementById('filterMonth');
        const statusSelect = document.getElementById('filterStatus');
        const selectedMonth = monthSelect.value;
        const selectedStatus = statusSelect.value;
    
        // Tampilkan loading indicator
        showLoadingIndicator();
    
        const currentUrl = new URL(window.location.href);
    
        if (selectedMonth === 'all') {
            currentUrl.searchParams.set('month', 'all');
        } else {
            currentUrl.searchParams.set('month', selectedMonth);
        }
    
        if (selectedStatus && selectedStatus !== '') {
            currentUrl.searchParams.set('status', selectedStatus);
        } else {
            currentUrl.searchParams.delete('status');
        }
    
        // Reset ke page 1
        currentUrl.searchParams.delete('page');
    
        window.location.href = currentUrl.toString();
    }
    
    // Function untuk filter berdasarkan status tagihan (server-side)
    function filterByStatus() {
        const statusSelect = document.getElementById('filterStatus');
        const monthSelect = document.getElementById('filterMonth');
        const selectedStatus = statusSelect.value;
        const selectedMonth = monthSelect.value;
    
        // Tampilkan loading indicator
        showLoadingIndicator();
    
        const currentUrl = new URL(window.location.href);
    
        if (selectedStatus && selectedStatus !== '') {
            currentUrl.searchParams.set('status', selectedStatus);
        } else {
            currentUrl.searchParams.delete('status');
        }
    
        if (selectedMonth && selectedMonth !== 'all') {
            currentUrl.searchParams.set('month', selectedMonth);
        } else {
            currentUrl.searchParams.set('month', 'all');
        }
    
        // Reset ke page 1
        currentUrl.searchParams.delete('page');
    
        window.location.href = currentUrl.toString();
    }
    
    // Function untuk mengubah jumlah data per halaman (server-side)
    function changeEntriesPerPage() {
        const entriesSelect = document.getElementById('entriesPerPage');
        const monthSelect = document.getElementById('filterMonth');
        const statusSelect = document.getElementById('filterStatus');
        const selectedPerPage = entriesSelect.value;
        const selectedMonth = monthSelect.value;
        const selectedStatus = statusSelect.value;
    
        // Tampilkan loading indicator
        showLoadingIndicator();
    
        const currentUrl = new URL(window.location.href);
    
        currentUrl.searchParams.set('per_page', selectedPerPage);
    
        if (selectedMonth && selectedMonth !== 'all') {
            currentUrl.searchParams.set('month', selectedMonth);
        } else {
            currentUrl.searchParams.set('month', 'all');
        }
    
        if (selectedStatus && selectedStatus !== '') {
            currentUrl.searchParams.set('status', selectedStatus);
        } else {
            currentUrl.searchParams.delete('status');
        }
    
        // Reset ke page 1
        currentUrl.searchParams.delete('page');
    
        window.location.href = currentUrl.toString();
    }
    
    // Helper function untuk loading indicator
    function showLoadingIndicator() {
        const tableBody = document.querySelector('#customerTable tbody');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="text-dark mt-3 mb-2">Memuat data...</h5>
                            <p class="text-muted mb-0">Sedang mengambil data terbaru</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    }
    </script>

@endsection