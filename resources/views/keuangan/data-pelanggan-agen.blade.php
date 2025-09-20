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
    
    .btn-group .btn {
        margin: 0 2px;
    }
    
    .empty-state-row td {
        padding: 3rem 1rem;
    }
    
    .avatar-sm {
        width: 3rem;
        height: 3rem;
        min-width: 3rem;
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
    
    .stats-card-warning::before {
        background: linear-gradient(90deg, #ffc107, #fd7e14, #ffc107);
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
    
    .stats-trend {
        display: flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .stats-trend i {
        font-size: 0.875rem;
        margin-right: 0.25rem;
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
        
        .avatar-sm {
            width: 2.5rem;
            height: 2.5rem;
            min-width: 2.5rem;
        }
        
        .stats-icon-wrapper {
            width: 3rem;
            height: 3rem;
        }
        
        .stats-icon-wrapper i {
            font-size: 1.25rem;
        }
        
        .stats-number {
            font-size: 1.25rem;
        }
        
        .stats-label {
            font-size: 0.8rem;
        }
        
        .stats-trend {
            font-size: 0.7rem;
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
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        
        $currentMonthNum = now()->format('m');
        $currentMonthName = $monthNames[$currentMonthNum];
        
        $displayPeriod = 'Bulan Ini (' . $currentMonthName . ' ' . now()->year . ')';
        if(request()->has('month')) {
            $monthParam = request()->get('month');
            if($monthParam == 'all') {
                $displayPeriod = 'Semua Periode';
            } elseif($monthParam != $currentMonthNum && isset($monthNames[$monthParam])) {
                $displayPeriod = $monthNames[$monthParam] . ' ' . now()->year;
            }
        }

        // Tambahkan informasi status filter
        $selectedStatus = request()->get('status', '');
        $displayStatus = '';
        if($selectedStatus) {
            $displayStatus = ' - ' . $selectedStatus;
        }
        @endphp
        


        <!-- Info Alert -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bx bx-calendar me-2"></i>
            <strong>Periode Aktif:</strong> Menampilkan data invoice untuk <strong>{{ $displayPeriod }}</strong>.
            @if(!request()->has('month') || (request()->has('month') && request()->get('month') == now()->format('Y-m')))
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
                        <h4 class="card-title fw-bold mb-1">Data Invoice Pelanggan Agen {{ $agen->name }} - {{ $displayPeriod }}{{ $displayStatus }}</h4>
                        <small class="card-subtitle text-muted">Daftar invoice pelanggan periode {{ $displayPeriod }}{{ $displayStatus }} yang terdaftar di bawah agen {{ $agen->name }}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-danger bg-opacity-10 text-danger fs-6 px-3 py-2">
                            <i class="bx bx-receipt me-1"></i>{{ $invoices->total() }} Invoice
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
                        <span id="statsIndicator">Menampilkan total dari semua data ({{ $invoices->total() }} invoice)</span>
                    </small>
                    <small class="text-muted" id="filterInfo" style="display: none;">
                        <span class="badge bg-info bg-opacity-10 text-info">
                            <i class="bx bx-filter me-1"></i>Data Terfilter
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
                                <div class="stats-number text-success" id="totalPaid">{{ 'Rp ' . number_format($totalPaid, 0, ',', '.') }}</div>
                                <div class="stats-label">Total Sudah Bayar</div>
                                <div class="stats-trend">
                                    <i class="bx bx-trending-up text-success"></i>
                                    <span class="text-success">Lunas</span>
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
                                <div class="stats-number text-danger" id="totalUnpaid">{{ 'Rp ' . number_format($totalUnpaid, 0, ',', '.') }}</div>
                                <div class="stats-label">Total Belum Bayar</div>
                                <div class="stats-trend">
                                    <i class="bx bx-trending-down text-danger"></i>
                                    <span class="text-danger">Pending</span>
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
                                <div class="stats-number text-primary" id="totalAmount">{{ 'Rp ' . number_format($totalAmount, 0, ',', '.') }}</div>
                                <div class="stats-label">Total Keseluruhan</div>
                                <div class="stats-trend">
                                    <i class="bx bx-bar-chart-alt-2 text-primary"></i>
                                    <span class="text-primary">Summary</span>
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
                                    @php
                                    $selectedMonth = request()->get('month', $currentMonthNum);
                                    @endphp
                                    <option value="all" {{ $selectedMonth == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                                    @foreach($monthNames as $monthNum => $monthName)
                                    <option value="{{ $monthNum }}" {{ $selectedMonth == $monthNum ? 'selected' : '' }}>
                                        {{ $monthName }} {{ now()->year }}
                                        @if($monthNum == $currentMonthNum) (Bulan Ini) @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                Secara default menampilkan invoice bulan {{ $currentMonthName }}
                            </small>
                        </div>
                        <div class="col-md-4 mb-2">
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
                                <th>Alamat</th>
                                <th>Status Tagihan</th>
                                <th>Total Tagihan</th>
                                <th>Jatuh Tempo</th>
                                <th>Metode Bayar</th>
                                <th>Tanggal Pembayaran</th>
                                <th>Bukti Pembayaran</th>
                                <th>Admin / Agen</th>                                
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @php $rowNumber = ($invoices->currentPage() - 1) * $invoices->perPage() + 1; @endphp
                            @forelse ($invoices as $invoice)
                            <tr class="customer-row" data-id="{{ $invoice->customer->id }}"
                                data-nama="{{ strtolower($invoice->customer->nama_customer) }}"
                                data-alamat="{{ strtolower($invoice->customer->alamat) }}"
                                data-jatuh-tempo="{{ $invoice->jatuh_tempo ? $invoice->jatuh_tempo : '' }}"
                                data-tagihan="{{ $invoice->tagihan ?? 0 }}"
                                data-status="{{ $invoice->status ? $invoice->status->nama_status : '' }}">
                                <td class="text-center">{{ $rowNumber++ }}</td>
                                <td class="customer-name fw-bold">{{ $invoice->customer->nama_customer }}</td>
                                <td class="customer-address">{{ $invoice->customer->alamat }}</td>
                                <td>
                                    @if($invoice->status)
                                    <span class="badge
                                            @if($invoice->status->id == 1) bg-info bg-opacity-10 text-info
                                            @elseif($invoice->status->id == 8) bg-success bg-opacity-10 text-success
                                            @elseif($invoice->status->id == 7) bg-danger bg-opacity-10 text-danger
                                            @else bg-secondary bg-opacity-10 text-secondary
                                            @endif">
                                    {{ $invoice->status->nama_status }}
                                </span>
                                @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Tidak Ada Status</span>
                                @endif
                            </td>
                            <td>Rp {{ number_format($invoice->tagihan ?? 0, 0, ',', '.') }}</td>
                            <td>
                                @if($invoice->jatuh_tempo)
                                @php
                                try {
                                    $jatuhTempo = \Carbon\Carbon::parse($invoice->jatuh_tempo);
                                    $isOverdue = $jatuhTempo->isPast() && $invoice->status && $invoice->status->nama_status != 'Sudah Bayar';
                                } catch (\Exception $e) {
                                    $jatuhTempo = null;
                                    $isOverdue = false;
                                }
                                @endphp
                                @if($jatuhTempo)
                                <span class="badge {{ $isOverdue ? 'bg-danger bg-opacity-10 text-danger' : ($invoice->status && $invoice->status->nama_status == 'Sudah Bayar' ? 'bg-success bg-opacity-10 text-success' : 'bg-info bg-opacity-10 text-info') }}">
                                    {{ $jatuhTempo->format('d M Y') }}
                                    @if($isOverdue)
                                    @elseif($invoice->status && $invoice->status->nama_status == 'Sudah Bayar')
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
                                @if($invoice->pembayaran->isNotEmpty())
                                    @foreach ($invoice->pembayaran as $item)
                                        <span class="badge bg-info">{{$item->metode_bayar}}</span>
                                    @endforeach
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                @if($invoice->pembayaran->isNotEmpty())
                                    @foreach ($invoice->pembayaran as $item)
                                        <span class="badge bg-info">{{ \Carbon\Carbon::parse($item->tanggal_bayar.' '.\Carbon\Carbon::parse($item->created_at)->format('H:i:s'))->format('d-m-Y H:i:s') }}</span>
                                    @endforeach
                                @else
                                <span>-</span>
                                @endif
                            </td>
                            <td>
                                @if($invoice->pembayaran->isNotEmpty())
                                    @foreach ($invoice->pembayaran as $item)
                                    <a href="{{ $item->bukti_bayar ? asset('storage/' . $item->bukti_bayar) : '#' }}"
                                        target="_blank"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        title="{{ $item->bukti_bayar ? 'Lihat Bukti' : 'Bukti Tidak Ditemukan' }}">
                                         <i class="bx bx-info-circle text-info"></i>
                                     </a>                                     
                                    @endforeach
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                @if ($invoice->pembayaran->isNotEmpty())
                                    @foreach ($invoice->pembayaran as $pembayaran)
                                        @if ($pembayaran->user)
                                            <span class="fw-bold badge bg-warning bg-opacity-10 text-warning" style="text-transform: uppercase;">
                                                {{ $pembayaran->user->name }} / {{ $pembayaran->user->roles->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">By Tripay</span>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="fw-bold">-</span>
                                @endif
                            </td>                            
                        </tr>
                        @empty
                        <tr class="empty-state-row">
                            <td colspan="10" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-dark mt-3 mb-2">Tidak ada data invoice</h5>
                                    <p class="text-muted mb-0">Belum ada invoice untuk pelanggan di bawah agen {{ $agen->name }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($invoices->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $invoices->appends(request()->all())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchName = document.getElementById('searchName');
        const filterMonth = document.getElementById('filterMonth');
        const filterStatus = document.getElementById('filterStatus');
        const customerRows = document.querySelectorAll('.customer-row');
        const emptyStateRow = document.querySelector('.empty-state-row');
        const statsIndicator = document.getElementById('statsIndicator');
        const filterInfo = document.getElementById('filterInfo');
        
        // Store original totals from server
        const originalTotals = {
            paid: {{ $totalPaid }},
            unpaid: {{ $totalUnpaid }},
            total: {{ $totalAmount }}
        };
        
        function filterTable() {
            const nameQuery = searchName.value.toLowerCase();
            const monthQuery = filterMonth.value;
            const statusQuery = filterStatus.value;

            let visibleRows = 0;
            let totalPaid = 0;
            let totalUnpaid = 0;
            let totalAmount = 0;

            // Check if any filters are applied (only for client-side filters)
            const hasClientFilters = nameQuery;

            customerRows.forEach(row => {
                const name = row.dataset.nama || '';
                const alamat = row.dataset.alamat || '';
                const jatuhTempo = row.dataset.jatuhTempo || '';
                const tagihan = parseFloat(row.dataset.tagihan || 0);
                const status = row.dataset.status || '';

                // Check name match (client-side filter)
                const matchesName = name.includes(nameQuery) || alamat.includes(nameQuery);

                // For month and status filters, we rely on server-side filtering
                // So we only apply client-side name filtering
                if (matchesName) {
                    row.style.display = '';
                    visibleRows++;

                    // Only calculate filtered statistics if client-side filters are applied
                    if (hasClientFilters) {
                        totalAmount += tagihan;

                        if (status === 'Sudah Bayar') {
                            totalPaid += tagihan;
                        } else {
                            totalUnpaid += tagihan;
                        }
                    }
                } else {
                    row.style.display = 'none';
                }
            });

            // Update statistics cards and indicators
            if (hasClientFilters) {
                // Show filtered totals for client-side filtering
                updateStatistics(totalPaid, totalUnpaid, totalAmount);
                statsIndicator.textContent = `Menampilkan total dari data terfilter (${visibleRows} invoice)`;
                filterInfo.style.display = 'inline-block';
            } else {
                // Show original totals from all data (server-side filtered)
                updateStatistics(originalTotals.paid, originalTotals.unpaid, originalTotals.total);
                statsIndicator.textContent = `Menampilkan total dari semua data ({{ $invoices->total() }} invoice)`;
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
        
        function updateStatistics(paid, unpaid, total) {
            document.getElementById('totalPaid').textContent = formatCurrency(paid);
            document.getElementById('totalUnpaid').textContent = formatCurrency(unpaid);
            document.getElementById('totalAmount').textContent = formatCurrency(total);
        }
        
        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }
        
        // Add event listeners
        searchName.addEventListener('input', filterTable);
        // Note: filterMonth and filterStatus are handled server-side via onchange functions

        // ESC key to reset filters
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchName.value = '';
                // Reset server-side filters by redirecting
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.delete('month');
                currentUrl.searchParams.delete('status');
                window.location.href = currentUrl.toString();
            }
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initial display with original totals
        filterTable();
    });
    
    // Function untuk filter berdasarkan bulan (server-side)
    function filterByMonth() {
        const monthSelect = document.getElementById('filterMonth');
        const statusSelect = document.getElementById('filterStatus');
        const selectedMonth = monthSelect.value;
        const selectedStatus = statusSelect.value;

        // Tampilkan loading indicator
        const tableBody = document.querySelector('#customerTable tbody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="text-dark mt-3 mb-2">Memuat data...</h5>
                        <p class="text-muted mb-0">Sedang mengambil data invoice untuk periode yang dipilih</p>
                    </div>
                </td>
            </tr>
        `;

        // Buat URL dengan parameter bulan dan pertahankan parameter status
        const currentUrl = new URL(window.location.href);

        // Set month parameter
        currentUrl.searchParams.set('month', selectedMonth);

        // Pertahankan status parameter
        if (selectedStatus && selectedStatus !== '') {
            currentUrl.searchParams.set('status', selectedStatus);
        }

        // Redirect ke URL dengan parameter bulan dan status
        window.location.href = currentUrl.toString();
    }

    // Function untuk filter berdasarkan status tagihan (server-side)
    function filterByStatus() {
        const statusSelect = document.getElementById('filterStatus');
        const monthSelect = document.getElementById('filterMonth');
        const selectedStatus = statusSelect.value;
        const selectedMonth = monthSelect.value;

        // Tampilkan loading indicator
        const tableBody = document.querySelector('#customerTable tbody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="text-dark mt-3 mb-2">Memuat data...</h5>
                        <p class="text-muted mb-0">Sedang mengambil data invoice untuk status yang dipilih</p>
                    </div>
                </td>
            </tr>
        `;

        // Buat URL dengan parameter status dan pertahankan parameter month
        const currentUrl = new URL(window.location.href);

        // Set status parameter
        if (selectedStatus && selectedStatus !== '') {
            currentUrl.searchParams.set('status', selectedStatus);
        } else {
            currentUrl.searchParams.delete('status');
        }

        // Pertahankan month parameter
        if (selectedMonth && selectedMonth !== '') {
            currentUrl.searchParams.set('month', selectedMonth);
        }

        // Redirect ke URL dengan parameter status dan month
        window.location.href = currentUrl.toString();
    }
</script>

@endsection