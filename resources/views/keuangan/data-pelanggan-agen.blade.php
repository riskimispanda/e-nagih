@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pelanggan Agen')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endsection

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
    
    .table-responsive {
        border-radius: 0;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        padding: 1rem 1.5rem;
    }
    
    .table td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
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
        $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        @endphp

        <!-- Info Alert -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bx bx-calendar me-2"></i>
            <strong>Periode Aktif:</strong> Data ditampilkan menggunakan DataTables dengan AJAX loading.
            Gunakan filter untuk menyaring data sesuai kebutuhan Anda.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
        <!-- Header Card -->
        <div class="card mb-3">
            <div class="card-header modern-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title fw-bold mb-1">Data Invoice Pelanggan Agen {{ $agen->name }}</h4>
                        <small class="card-subtitle text-muted">Daftar invoice pelanggan yang terdaftar di bawah agen {{ $agen->name }}</small>
                    </div>
                    <div class="text-end d-flex align-items-center gap-2">
                        <span class="badge bg-danger bg-opacity-10 text-danger fs-6 px-3 py-2">
                            <i class="bx bx-user me-1"></i><span id="totalCustomerBadge">{{ $totalPelanggan }}</span> Pelanggan
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-12 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i>
                        <span id="statsIndicator">Memuat data...</span>
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
                                <div class="stats-number text-success" id="totalPaid">Rp 0</div>
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
                                <div class="stats-number text-danger" id="totalUnpaid">Rp 0</div>
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
                                <div class="stats-number text-primary" id="totalAmount">Rp 0</div>
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
                        <div class="col-md-5 mb-2">
                            <label class="form-label">Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" id="searchInput" class="form-control" placeholder="Cari nama pelanggan, alamat...">
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Filter Periode Bulan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <select class="form-select" id="filterMonth">
                                    <option value="">Semua Bulan</option>
                                    @foreach($monthNames as $monthNum => $monthName)
                                    <option value="{{ $monthNum }}">
                                        {{ $monthName }} {{ now()->year }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Status Tagihan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-filter"></i></span>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="Belum Bayar">Belum Bayar</option>
                                    <option value="Sudah Bayar">Sudah Bayar</option>
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
                <!-- Table Controls (Length & Filter) -->
                <div id="tableControls" class="mb-3"></div>

                <!-- Table Responsive -->
                <div class="table-responsive">
                    <table id="customerTable" class="table table-hover" style="font-size: 14px; width:100%">
                        <thead class="table-dark text-center">
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
                                <th>Status Customer</th>
                                <th>Admin / Agen</th>                                
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination (Outside table-responsive) -->
                <div id="tablePagination" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection

@section('page-script')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    const agenId = {{ $agen->id }};
    let dataTable;

    jQuery(document).ready(function($) {
        // Initialize DataTable
        dataTable = $('#customerTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": `/agen/pelanggan/${agenId}/ajax`,
                "type": "GET",
                "data": function(d) {
                    d.month = $('#filterMonth').val();
                    d.status = $('#filterStatus').val();
                }
            },
            "columns": [
                { 
                    "data": null, 
                    "render": function (data, type, row, meta) { 
                        return meta.row + 1; 
                    }, 
                    "className": "text-center" 
                },
                { 
                    "data": 1,
                    "className": "text-start"
                },
                { 
                    "data": 2,
                    "className": "text-start"
                },
                { 
                    "data": 3,
                    "className": "text-center"
                },
                { 
                    "data": 4,
                    "className": "text-end"
                },
                { 
                    "data": 5,
                    "className": "text-center"
                },
                { 
                    "data": 6,
                    "className": "text-center"
                },
                { 
                    "data": 7,
                    "className": "text-center"
                },
                { 
                    "data": 8,
                    "className": "text-center"
                },
                { 
                    "data": 9,
                    "className": "text-center"
                },
                { 
                    "data": 10,
                    "className": "text-center"
                }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            },
            "dom": '<"row"<"col-sm-12 col-md-6 mb-3"l>>' +
                   't' +
                   '<"row"<"col-sm-12 col-md-7"p>>',
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "drawCallback": function() {
                updateStatistics();
                initializeTooltips();
            }
        });

        // Event listeners untuk filter
        $('#filterMonth').on('change', function() {
            dataTable.ajax.reload();
        });

        $('#filterStatus').on('change', function() {
            dataTable.ajax.reload();
        });

        // Event listener untuk search dengan debounce
        let searchTimeout;
        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                dataTable.search($('#searchInput').val()).draw();
            }, 500);
        });
    });

    function updateStatistics() {
        if (!dataTable) return;
        
        let totalPaid = 0;
        let totalUnpaid = 0;
        let totalAmount = 0;

        const rows = dataTable.rows({ search: 'applied' }).data();
        
        rows.each(function(row) {
            // row[4] is tagihan (Rp X)
            const tagihanText = row[4];
            const tagihan = parseInt(tagihanText.replace(/[^0-9]/g, '')) || 0;
            
            // row[3] is status (HTML with badge)
            const statusHtml = row[3];
            
            totalAmount += tagihan;
            
            if (statusHtml.includes('bg-success')) {
                totalPaid += tagihan;
            } else if (statusHtml.includes('bg-danger')) {
                totalUnpaid += tagihan;
            }
        });

        document.getElementById('totalPaid').textContent = formatCurrency(totalPaid);
        document.getElementById('totalUnpaid').textContent = formatCurrency(totalUnpaid);
        document.getElementById('totalAmount').textContent = formatCurrency(totalAmount);
        document.getElementById('statsIndicator').textContent = `Menampilkan ${rows.length} invoice`;
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
</script>
@endsection
