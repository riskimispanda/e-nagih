@extends('layouts.contentNavbarLayout')
@section('title', 'Data Pelanggan Agen')

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.min.css">

<style>
  /* DataTable-style Pagination */
  .datatable-pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 0;
    flex-wrap: wrap;
    gap: 1rem;
  }

  .datatable-info {
    color: #6c757d;
    font-size: 0.875rem;
  }

  .datatable-info strong {
    color: #495057;
    font-weight: 600;
  }

  .datatable-pagination {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 0.25rem;
  }

  .datatable-pagination .page-item {
    margin: 0;
  }

  .datatable-pagination .page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    color: #495057;
    background-color: #fff;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    text-decoration: none;
  }

  .datatable-pagination .page-link:hover {
    background-color: #f8f9fa;
    border-color: #667eea;
    color: #667eea;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .datatable-pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: #fff;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
  }

  .datatable-pagination .page-item.disabled .page-link {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #adb5bd;
    cursor: not-allowed;
    opacity: 0.6;
  }

  .datatable-pagination .page-link i {
    font-size: 1.1rem;
  }

  /* Table Controls Section */
  .table-controls {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  .table-controls .controls-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .table-controls .badge {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
    font-weight: 600;
  }

  /* Responsive Pagination */
  @media (max-width: 576px) {
    .datatable-pagination-wrapper {
      flex-direction: column;
      align-items: stretch;
      padding: 1rem 0;
      gap: 0.75rem;
    }

    .datatable-info {
      width: 100%;
      text-align: center;
      font-size: 0.8rem;
    }

    .datatable-pagination {
      width: 100%;
      justify-content: center;
      flex-wrap: wrap;
      gap: 0.15rem;
    }

    .datatable-pagination .page-link {
      padding: 0.35rem 0.5rem;
      min-width: 32px;
      font-size: 0.75rem;
    }

    .datatable-pagination .page-link i {
      font-size: 0.9rem;
    }

    /* Hide some page numbers on very small screens */
    .datatable-pagination .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
      display: none;
    }
  }

  @media (max-width: 768px) {
    .datatable-pagination-wrapper {
      padding: 1rem 0.5rem;
    }

    .datatable-pagination {
      flex-wrap: wrap;
      gap: 0.2rem;
    }

    .datatable-pagination .page-link {
      padding: 0.4rem 0.65rem;
      min-width: 36px;
      font-size: 0.8rem;
    }
  }


  <style>.search-highlight {
    background-color: #fff3cd;
    padding: 2px 4px;
    border-radius: 3px;
  }

  .modern-table {
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    background: white;
  }

  .modern-table thead th {
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%) !important;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 1rem 0.75rem !important;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .modern-table tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
  }

  .modern-table tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  }

  .modern-table tbody td {
    padding: 0.875rem 0.75rem;
    vertical-align: middle;
  }

  .status-badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    display: inline-block;
    min-width: 90px;
    text-align: center;
  }

  .search-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }

  .input-group .btn {
    border-color: #ced4da;
  }

  .input-group .input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
    color: #6c757d;
  }

  .form-select:focus,
  .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }

  .filter-active {
    border-color: #667eea !important;
    background-color: #f8f9ff !important;
  }

  #filterIndicator {
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% {
      opacity: 1;
    }

    50% {
      opacity: 0.7;
    }

    100% {
      opacity: 1;
    }
  }

  .customer-row {
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
  }

  .customer-row[data-status-tagihan="Sudah Bayar"] {
    background-color: #f8fff8;
    border-left-color: #28a745;
  }

  .customer-row[data-status-tagihan="Belum Bayar"] {
    background-color: #fff8f8;
    border-left-color: #dc3545;
  }

  .customer-row:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
  }

  .invoice-overdue {
    animation: blink 2s infinite;
  }

  @keyframes blink {

    0%,
    50% {
      opacity: 1;
    }

    51%,
    100% {
      opacity: 0.7;
    }
  }

  /* Statistics Cards Styling */
  .statistics-card {
    transition: all 0.3s ease;
    border-radius: 0.75rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  }

  .statistics-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
  }

  .statistics-card .card-body {
    padding: 1.5rem;
  }

  .statistics-card .avatar-initial {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .statistics-card .avatar-initial i {
    font-size: 1.5rem;
  }

  .statistics-card h4 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
  }

  .statistics-card .progress {
    background-color: rgba(0, 0, 0, 0.1);
  }

  .statistics-card .progress-bar {
    transition: width 0.6s ease;
  }

  /* Month filter styling */
  #bulan {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
  }

  #bulan option {
    padding: 0.5rem;
    font-size: 0.9rem;
  }

  .month-with-data {
    font-weight: 600;
    background-color: #f8fff8;
  }

  .month-no-data {
    color: #6c757d;
    background-color: #f8f9fa;
  }

  .month-indicator {
    font-size: 0.8rem;
    margin-top: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
  }

  /* Modal Konfirmasi Pembayaran Styling */
  .modal-content {
    border-radius: 0.75rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  }

  .modal-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 0.75rem 0.75rem 0 0;
  }

  .modal-header .modal-title {
    color: white;
  }

  .modal-header .btn-close {
    filter: invert(1);
  }

  .payment-info-card {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-radius: 0.5rem;
    padding: 1rem;
    border: 1px solid #e1f5fe;
  }

  .total-payment {
    background: linear-gradient(135deg, #e8f5e8 0%, #f0fff0 100%);
    border: 2px solid #28a745;
    border-radius: 0.5rem;
    padding: 0.75rem;
  }

  .form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
  }

  .form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
  }

  /* Styling untuk customer yang di-soft delete */
  .customer-deleted {
    background: linear-gradient(45deg, #fff3f3 0%, #fff8f8 100%) !important;
    position: relative;
  }

  .customer-deleted .customer-name,
  .customer-deleted .customer-address,
  .customer-deleted .nomor-hp {
    opacity: 0.7;
    text-decoration: line-through;
  }

  .deleted-badge {
    background-color: #dc3545 !important;
    color: white !important;
  }

  /* Enhanced Filter Styling */
  .form-group {
    position: relative;
  }

  .form-group .form-label {
    color: #495057;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    font-weight: 500;
  }

  .form-group .form-label i {
    font-size: 1rem;
    color: #667eea;
    margin-right: 0.25rem;
  }

  .form-group .form-select {
    border-radius: 0.5rem;
    border: 1px solid #ced4da;
    transition: all 0.3s ease;
    background-color: #fff;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }

  .form-group .form-select:hover {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.1);
  }

  .form-group .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }

  .month-indicator {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 0.75rem;
    border: 1px solid #e9ecef;
    font-size: 0.8rem;
  }

  .month-indicator .border-top {
    border-color: #dee2e6 !important;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .form-group .form-label {
      font-size: 0.8rem;
    }

    .form-group .form-select {
      padding: 0.625rem 0.875rem;
      font-size: 0.85rem;
    }

    .month-indicator {
      padding: 0.5rem;
      font-size: 0.75rem;
    }

    .modern-table thead th {
      font-size: 0.75rem;
      padding: 0.75rem 0.5rem !important;
    }

    .modern-table tbody td {
      padding: 0.75rem 0.5rem;
      font-size: 0.85rem;
    }
  }

  /* DataTables Enhanced Styling */
  table.dataTable {
    border-collapse: collapse !important;
    width: 100% !important;
  }

  table.dataTable thead th {
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%) !important;
    color: white !important;
    border-bottom: 2px solid #667eea !important;
  }

  table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
  table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
    background-color: #667eea;
  }

  /* DataTable sorting icons */
  table.dataTable thead .sorting:before,
  table.dataTable thead .sorting_asc:before,
  table.dataTable thead .sorting_desc:before,
  table.dataTable thead .sorting:after,
  table.dataTable thead .sorting_asc:after,
  table.dataTable thead .sorting_desc:after {
    opacity: 0.6;
  }

  table.dataTable thead .sorting:hover:before,
  table.dataTable thead .sorting:hover:after {
    opacity: 1;
  }

  /* Loading overlay */
  .loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(4px);
  }

  .loading-spinner {
    background: white;
    padding: 2rem;
    border-radius: 0.75rem;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  }

  /* Table wrapper for better scrolling */
  .table-wrapper {
    position: relative;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* Smooth fade-in animation */
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .fade-in {
    animation: fadeIn 0.3s ease-out;
  }
</style>

@section('content')
  <div class="row">
    <div class="col-sm-12">
      {{-- Success Message --}}
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bx bx-check-circle me-2"></i>
          <strong>Berhasil!</strong> {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      {{-- Error Message --}}
      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bx bx-error-circle me-2"></i>
          <strong>Error!</strong> {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      {{-- Validation Errors --}}
      @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <i class="bx bx-error-alt me-2"></i>
          <strong>Peringatan!</strong> Terdapat kesalahan input:
          <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bx bx-info-circle me-2"></i>
        <strong>Informasi:</strong> Tampilan ini menunjukkan data pelanggan
        @if($selectedMonth !== 'all' && $selectedYear !== 'all')
          untuk periode <strong>{{ $selectedMonthName }} {{ $selectedYear }}</strong>
        @elseif($selectedMonth !== 'all')
          untuk periode <strong>{{ $selectedMonthName }}</strong>
        @elseif($selectedYear !== 'all')
          untuk tahun <strong>{{ $selectedYear }}</strong>
        @else
          untuk <strong>semua periode</strong>
        @endif.
        Termasuk pelanggan yang sudah dihapus (ditandai dengan strikethrough).
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>

      <div class="card mb-2">
        <div class="card-header modern-card-header">
          <h4 class="card-title fw-bold">Data Pembayaran Pelanggan
            @if($selectedMonth !== 'all' && $selectedYear !== 'all')
              - Periode {{ $selectedMonthName }} {{ $selectedYear }}
            @elseif($selectedMonth !== 'all')
              - Periode {{ $selectedMonthName }}
            @elseif($selectedYear !== 'all')
              - Tahun {{ $selectedYear }}
            @else
              - Semua Periode
            @endif
          </h4>
          <small class="card-subtitle text-muted">
            Daftar Pembayaran tagihan
            @if($selectedMonth !== 'all' && $selectedYear !== 'all')
              periode {{ strtolower($selectedMonthName) }} {{ $selectedYear }}
            @elseif($selectedMonth !== 'all')
              periode {{ strtolower($selectedMonthName) }}
            @elseif($selectedYear !== 'all')
              tahun {{ $selectedYear }}
            @else
              semua periode
            @endif
            (termasuk pelanggan yang dihapus)
          </small>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-12 mb-3">
          <div class="alert alert-primary alert-dismissible fade show" role="alert">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Statistik Periode:</strong>
            Menampilkan data untuk
            @if($selectedMonth !== 'all' && $selectedYear !== 'all')
              periode <strong>{{ $selectedMonthName }} {{ $selectedYear }}</strong>
            @elseif($selectedMonth !== 'all')
              periode <strong>{{ $selectedMonthName }}</strong>
            @elseif($selectedYear !== 'all')
              tahun <strong>{{ $selectedYear }}</strong>
            @else
              <strong>semua periode</strong>
            @endif
            - Termasuk pelanggan yang sudah dihapus dengan status "Sudah Bayar"
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card border-success statistics-card">
            <div class="card-body text-center">
              <div class="d-flex align-items-center justify-content-center mb-2">
                <div class="avatar avatar-md me-2">
                  <div class="avatar-initial bg-success rounded">
                    <i class="bx bx-check-circle text-white"></i>
                  </div>
                </div>
                <div>
                  <h6 class="card-title mb-0 text-success">Sudah Bayar</h6>
                  <small class="text-muted" id="stat-paid-count">{{ $statistics['count_paid'] ?? 0 }} Invoice</small>
                </div>
              </div>
              <h4 class="text-success mb-1" id="stat-paid-amount">Rp
                {{ number_format($statistics['total_paid'] ?? 0, 0, ',', '.') }}
              </h4>
              <div class="progress mb-2" style="height: 6px;">
                <div class="progress-bar bg-success" id="stat-paid-progress" role="progressbar"
                  style="width: {{ $statistics['percentage_paid'] ?? 0 }}%"
                  aria-valuenow="{{ $statistics['percentage_paid'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted" id="stat-paid-percentage">{{ $statistics['percentage_paid'] ?? 0 }}% dari
                total</small>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card border-danger statistics-card">
            <div class="card-body text-center">
              <div class="d-flex align-items-center justify-content-center mb-2">
                <div class="avatar avatar-md me-2">
                  <div class="avatar-initial bg-danger rounded">
                    <i class="bx bx-x-circle text-white"></i>
                  </div>
                </div>
                <div>
                  <h6 class="card-title mb-0 text-danger">Belum Bayar</h6>
                  <small class="text-muted" id="stat-unpaid-count">{{ $statistics['count_unpaid'] ?? 0 }} Invoice</small>
                </div>
              </div>
              <h4 class="text-danger mb-1" id="stat-unpaid-amount">Rp
                {{ number_format($statistics['total_unpaid'] ?? 0, 0, ',', '.') }}
              </h4>
              <div class="progress mb-2" style="height: 6px;">
                <div class="progress-bar bg-danger" id="stat-unpaid-progress" role="progressbar"
                  style="width: {{ $statistics['percentage_unpaid'] ?? 0 }}%"
                  aria-valuenow="{{ $statistics['percentage_unpaid'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted" id="stat-unpaid-percentage">{{ $statistics['percentage_unpaid'] ?? 0 }}% dari
                total</small>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card border-primary statistics-card">
            <div class="card-body text-center">
              <div class="d-flex align-items-center justify-content-center mb-2">
                <div class="avatar avatar-md me-2">
                  <div class="avatar-initial bg-primary rounded">
                    <i class="bx bx-receipt text-white"></i>
                  </div>
                </div>
                <div>
                  <h6 class="card-title mb-0 text-primary">Total Keseluruhan</h6>
                  <small class="text-muted" id="stat-total-count">{{ $statistics['count_total'] ?? 0 }} Invoice</small>
                </div>
              </div>
              <h4 class="text-primary mb-1" id="stat-total-amount">Rp
                {{ number_format($statistics['total_amount'] ?? 0, 0, ',', '.') }}
              </h4>
              <div class="progress mb-2" style="height: 6px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 100%" aria-valuenow="100"
                  aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted">100% dari periode ini</small>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <div class="search-container">
            <h6 class="mb-3 fw-bold text-dark">
              <i class="bx bx-search me-2"></i>Filter & Pencarian Data
              <small class="text-muted fw-normal ms-2" id="filter-info">
                @if($selectedMonth !== 'all' && $selectedYear !== 'all')
                  ({{ $invoices->total() }} invoice periode {{ $selectedMonthName }} {{ $selectedYear }})
                @elseif($selectedMonth !== 'all')
                  ({{ $invoices->total() }} invoice periode {{ $selectedMonthName }})
                @elseif($selectedYear !== 'all')
                  ({{ $invoices->total() }} invoice tahun {{ $selectedYear }})
                @else
                  ({{ $invoices->total() }} invoice semua periode)
                @endif
              </small>
            </h6>
            <div class="row mb-4">
              <div class="col-lg-4 col-md-12 mb-3">
                <div class="form-group">
                  <label class="form-label fw-semibold">
                    <i class="bx bx-search me-2"></i>Nama Pelanggan
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="bx bx-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Cari nama, alamat, atau nomor HP..."
                      aria-label="Cari pelanggan..." id="searchCustomer"
                      title="Ketik untuk mencari berdasarkan nama, alamat, atau nomor HP">
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-12 mb-3">
                <div class="form-group">
                  <label class="form-label fw-semibold">
                    <i class="bx bx-filter me-2"></i>Status Tagihan
                  </label>
                  <select name="status_tagihan" id="statusTagihan" class="form-select"
                    title="Filter berdasarkan status pembayaran tagihan">
                    <option value="" selected>Semua Status</option>
                    <option value="Belum Bayar">Belum Bayar</option>
                    <option value="Sudah Bayar">Sudah Bayar</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-4 col-md-12 mb-3">
                <div class="form-group">
                  <label class="form-label fw-semibold">
                    <i class="bx bx-list-ol me-2"></i>Tampilkan
                  </label>
                  <select name="per_page" id="perPage" class="form-select"
                    title="Jumlah data yang ditampilkan per halaman">
                    <option value="10" @if($invoices->perPage() == 10) selected @endif>10 data</option>
                    <option value="25" @if($invoices->perPage() == 25) selected @endif>25 data</option>
                    <option value="50" @if($invoices->perPage() == 50) selected @endif>50 data</option>
                    <option value="100" @if($invoices->perPage() == 100) selected @endif>100 data</option>
                    <option value="all" @if($invoices->perPage() == -1) selected @endif>Semua data</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-lg-6 col-md-12 mb-3">
                <div class="form-group">
                  <label class="form-label fw-semibold">
                    <i class="bx bx-calendar me-2"></i>Periode Tahun
                  </label>
                  <select name="tahun" id="tahun" class="form-select"
                    title="Filter berdasarkan tahun jatuh tempo tagihan">
                    <option value="all">Semua Tahun</option>
                    @php
                      $currentYear = date('Y');
                      $startYear = $currentYear - 5;
                    @endphp
                    @for ($year = $currentYear; $year >= $startYear; $year--)
                      <option value="{{ $year }}" {{ ($selectedYear ?? $currentYear) == $year ? 'selected' : '' }}>
                        {{ $year }}
                      </option>
                    @endfor
                  </select>
                </div>
              </div>
              <div class="col-lg-6 col-md-12 mb-3">
                <div class="form-group">
                  <label class="form-label fw-semibold">
                    <i class="bx bx-calendar-event me-2"></i>Periode Bulan
                  </label>
                  <select name="bulan" id="bulan" class="form-select"
                    title="Filter berdasarkan bulan jatuh tempo tagihan">
                    <option value="all">Semua Bulan</option>
                    @php
                      $allMonths = [
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember'
                      ];
                    @endphp
                    @foreach ($allMonths as $monthNum => $monthName)
                      <option value="{{ $monthNum }}" {{ $selectedMonth == $monthNum ? 'selected' : '' }}
                        class="{{ isset($availableMonths[$monthNum]) ? 'month-with-data' : 'month-no-data' }}">
                        @if(isset($availableMonths[$monthNum]))
                          ● {{ $monthName }}
                        @else
                          ○ {{ $monthName }}
                        @endif
                      </option>
                    @endforeach
                  </select>
                  <div class="month-indicator mt-2">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                      <div class="me-3">
                        <span class="text-success me-1">●</span>
                        <small class="text-muted">Bulan dengan data</small>
                      </div>
                      <div>
                        <span class="text-muted me-1">○</span>
                        <small class="text-muted">Bulan tanpa data</small>
                      </div>
                    </div>
                    @if(isset($availableMonths) && count($availableMonths) > 0)
                      <div class="mt-2 pt-2 border-top">
                        <small class="text-muted">
                          <strong>{{ count($availableMonths) }}</strong> bulan tersedia:
                          {{ implode(', ', array_values($availableMonths)) }}
                        </small>
                      </div>
                    @else
                      <div class="mt-2 pt-2 border-top">
                        <small class="text-warning">
                          <i class="bx bx-warning me-1"></i>
                          Belum ada data invoice untuk agen ini
                        </small>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <hr class="my-2 mb-4">

    {{-- Table Controls --}}
    <div class="table-controls">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="controls-left">
          @php
            $totalRows = $invoices->total();
          @endphp
          <div>
            <i class="bx bx-data text-primary me-1"></i>
            <span class="text-muted" id="searchResults">
              Menampilkan
              <strong class="text-primary" id="visibleCount">{{ $invoices->count() }}</strong>
              dari
              <strong class="text-dark" id="totalCount">{{ $totalRows }}</strong>
              data
            </span>
          </div>
          <span class="badge bg-info" id="filterIndicator" style="display: none;">
            <i class="bx bx-filter-alt me-1"></i>Filter Aktif
          </span>
        </div>
        <div>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
            <i class="bx bx-refresh me-1"></i>Reset Filter
          </button>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-body">
        <div class="table-wrapper mb-2">
          <table class="min-w-full bg-white shadow-lg rounded-lg overflow-hidden modern-table" id="customerTable">
            <thead class="bg-gray-800 text-white text-center font-bold">
              <tr>
                <th class="px-4 py-3" style="min-width: 50px;">No</th>
                <th class="px-4 py-3" style="min-width: 150px;">Nama</th>
                <th class="px-4 py-3" style="min-width: 200px;">Alamat</th>
                <th class="px-4 py-3" style="min-width: 120px;">Telp.</th>
                <th class="px-4 py-3" style="min-width: 120px;">Paket</th>
                <th class="px-4 py-3" style="min-width: 120px;">Tagihan</th>
                <th class="px-4 py-3" style="min-width: 130px;">Status Tagihan</th>
                <th class="px-4 py-3" style="min-width: 120px;">Jatuh Tempo</th>
                <th class="px-4 py-3" style="min-width: 120px;">Tanggal Bayar</th>
                <th class="px-4 py-3" style="min-width: 100px;">Aksi</th>
                <th class="px-4 py-3" style="min-width: 130px;">Status Customer</th>
                <th class="px-4 py-3" style="min-width: 150px;">Keterangan</th>
              </tr>
            </thead>
            <tbody class="text-center" id="customerTableBody">
              {{-- Data will be rendered by DataTables --}}
            </tbody>
          </table>
        </div>
      </div>
      {{-- Pagination --}}
      <div id="paginationContainer">
        @if($invoices->hasPages())
          {{ $invoices->links('vendor.pagination.datatable') }}
        @endif
      </div>
    </div>
  </div>

  {{-- Container for modals loaded via AJAX --}}
  <div id="modal-container">
    @include('agen.partials.payment-modal', ['invoices' => $invoices])
  </div>

  {{-- Loading overlay --}}
  <div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="loading-spinner">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Memuat data...</p>
    </div>
  </div>
  {{-- DataTables JS (jQuery is already loaded by the layout) --}}
  <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>

  {{-- DataTables --}}

  <script>
    // Ensure jQuery and DataTables are loaded
    (function () {
      'use strict';

      // Configuration
      const config = {
        searchRoute: "{{ route('data-pelanggan-agen-search') }}",
        selectedMonth: '{{ $selectedMonth }}',
        selectedYear: '{{ $selectedYear }}',
        initialPerPage: '{{ $invoices->perPage() == -1 ? "all" : $invoices->perPage() }}'
      };

      let searchTimeout;
      let dataTable = null;

      // Initialize when document is ready
      $(document).ready(function () {
        console.log('Document ready - jQuery version:', $.fn.jquery);
        console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');

        // Initialize filters
        initializeFilters();

        // Load initial data
        loadInitialData();

        // Setup event listeners
        setupEventListeners();
      });

      function initializeFilters() {
        $('#bulan').val(config.selectedMonth);
        $('#tahun').val(config.selectedYear);
        $('#perPage').val(config.initialPerPage);
      }

      function loadInitialData() {
        // Use current month and year as default
        fetchData(1, '', config.selectedMonth, config.initialPerPage, '', config.selectedYear);
      }

      function setupEventListeners() {
        // Search input with debounce
        $('#searchCustomer').on('input', function () {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => {
            fetchData(1, $(this).val(), $('#bulan').val(), $('#perPage').val(), $('#statusTagihan').val(), $('#tahun').val());
          }, 500);
        });

        // Filter changes
        $('#bulan, #tahun, #statusTagihan, #perPage').on('change', function () {
          fetchData(1, $('#searchCustomer').val(), $('#bulan').val(), $('#perPage').val(), $('#statusTagihan').val(), $('#tahun').val());
        });

        // Reset filters
        $('#resetFilters').on('click', function () {
          resetFilters();
        });
      }

      function fetchData(page = 1, search = '', month = '', perPage = '10', status = '', year = '') {
        // Show loading
        showLoading(true);

        const url = new URL(config.searchRoute);
        url.searchParams.append('page', page);
        if (search) url.searchParams.append('search', search);
        if (month) url.searchParams.append('month', month);
        if (year) url.searchParams.append('year', year);
        if (perPage === 'all') {
          url.searchParams.append('per_page', '10000');
        } else {
          url.searchParams.append('per_page', perPage);
        }
        if (status) url.searchParams.append('status', status);

        fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then(data => {
            updateTableContent(data);
            updateStatistics(data.statistics);
            updateCounts(data.visible_count, data.total_count);
            initializeDataTable(perPage);
            showLoading(false);
          })
          .catch(error => {
            console.error('Error fetching data:', error);
            showError();
            showLoading(false);
          });
      }

      function updateTableContent(data) {
        // Batch DOM updates using requestAnimationFrame for better performance
        requestAnimationFrame(() => {
          // Update table body
          $('#customerTableBody').html(data.table_html);

          // Update modals
          $('#modal-container').html(data.modals_html);

          // Update dropdowns to match current filter state
          if (data.selected_year) {
            const yearValue = data.selected_year === 'Semua Tahun' ? 'all' : data.selected_year;
            $('#tahun').val(yearValue);
          }
          if (data.selected_month_name) {
            const monthMap = {
              'Januari': '01', 'Februari': '02', 'Maret': '03', 'April': '04',
              'Mei': '05', 'Juni': '06', 'Juli': '07', 'Agustus': '08',
              'September': '09', 'Oktober': '10', 'November': '11', 'Desember': '12'
            };
            const monthValue = data.selected_month_name === 'Semua Periode' ? 'all' : monthMap[data.selected_month_name];
            if (monthValue) $('#bulan').val(monthValue);
          }

          // Update pagination if available
          if (data.pagination_html) {
            $('#paginationContainer').html(data.pagination_html);
            setupPaginationLinks();
          }
        });
      }

      function updateStatistics(stats) {
        if (!stats) return;

        // Update paid statistics
        $('#stat-paid-count').text(stats.count_paid + ' Invoice');
        $('#stat-paid-amount').text('Rp ' + formatNumber(stats.total_paid || 0));
        $('#stat-paid-progress').css('width', stats.percentage_paid + '%');
        $('#stat-paid-percentage').text(stats.percentage_paid + '% dari total');

        // Update unpaid statistics
        $('#stat-unpaid-count').text(stats.count_unpaid + ' Invoice');
        $('#stat-unpaid-amount').text('Rp ' + formatNumber(stats.total_unpaid || 0));
        $('#stat-unpaid-progress').css('width', stats.percentage_unpaid + '%');
        $('#stat-unpaid-percentage').text(stats.percentage_unpaid + '% dari total');

        // Update total statistics
        $('#stat-total-count').text(stats.count_total + ' Invoice');
        $('#stat-total-amount').text('Rp ' + formatNumber(stats.total_amount || 0));
      }

      function updateCounts(visible, total) {
        $('#visibleCount').text(visible);
        $('#totalCount').text(total);
      }

      function initializeDataTable(perPage) {
        // Check if DataTables is available
        if (typeof $.fn.DataTable === 'undefined') {
          console.warn('DataTables library not loaded. Table will work without DataTables features.');
          initializeTooltips();
          return;
        }

        // Only initialize once, then just refresh data
        if (dataTable === null) {
          try {
            dataTable = $('#customerTable').DataTable({
              responsive: true,
              paging: false, // Server-side pagination
              searching: false, // Server-side search
              info: false, // Custom info display
              ordering: true, // Enable sorting
              order: [[7, 'desc']], // Default sort by Jatuh Tempo
              columnDefs: [
                { orderable: false, targets: [0, 9, 10, 11] }, // Disable sorting on No, Aksi, Status Customer, Keterangan
                { className: 'text-center', targets: '_all' }
              ],
              language: {
                emptyTable: "Tidak ada data yang tersedia",
                zeroRecords: "Tidak ditemukan data yang sesuai",
                loadingRecords: "Memuat data...",
                processing: "Memproses..."
              },
              drawCallback: function () {
                initializeTooltips();
                // Add fade-in animation to rows
                $('#customerTableBody tr').addClass('fade-in');
              },
              autoWidth: false,
              scrollX: true,
              scrollCollapse: true
            });

            console.log('✅ DataTable initialized successfully');
          } catch (error) {
            console.error('❌ Error initializing DataTable:', error);
            console.warn('Continuing without DataTables features...');
            initializeTooltips();
          }
        } else {
          // Just redraw existing DataTable with new data
          try {
            dataTable.draw(false); // false = stay on current page
            console.log('✅ DataTable refreshed');
          } catch (error) {
            console.warn('⚠️ Error refreshing DataTable:', error);
          }
        }
      }

      function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
      }

      function resetFilters() {
        // Get current month and year
        const currentDate = new Date();
        const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
        const currentYear = String(currentDate.getFullYear());

        $('#searchCustomer').val('');
        $('#statusTagihan').val('');
        $('#perPage').val('10');
        $('#bulan').val(currentMonth);
        $('#tahun').val(currentYear);

        fetchData(1, '', currentMonth, '10', '', currentYear);
      }

      function showLoading(show) {
        if (show) {
          $('#loadingOverlay').fadeIn(200);
        } else {
          $('#loadingOverlay').fadeOut(200);
        }
      }

      function showError() {
        $('#customerTableBody').html(
          '<tr><td colspan="12" class="text-center py-5 text-danger">' +
          '<i class="bx bx-error-circle bx-lg mb-2"></i><br>' +
          'Gagal memuat data. Silakan coba lagi.' +
          '</td></tr>'
        );
      }

      function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
      }

      function setupPaginationLinks() {
        // Handle pagination link clicks
        $('#paginationContainer').off('click', 'a.page-link').on('click', 'a.page-link', function (e) {
          e.preventDefault();

          const url = $(this).attr('href');
          if (!url || url === '#') return;

          // Extract page number from URL
          const urlParams = new URLSearchParams(url.split('?')[1]);
          const page = urlParams.get('page') || 1;

          // Fetch data for the selected page
          fetchData(
            page,
            $('#searchCustomer').val(),
            $('#bulan').val(),
            $('#perPage').val(),
            $('#statusTagihan').val(),
            $('#tahun').val()
          );

          // Scroll to top of table
          $('html, body').animate({
            scrollTop: $('#customerTable').offset().top - 100
          }, 300);
        });
      }

      // Make functions available globally if needed
      window.DataPelangganAgen = {
        fetchData: fetchData,
        resetFilters: resetFilters
      };
    })();
  </script>

  <script>
    // Payment form handling
    (function () {
      'use strict';

      // Format Rupiah
      window.formatRupiah = function (el, id) {
        let angka = el.value.replace(/[^0-9]/g, '');
        let number = parseInt(angka, 10) || 0;

        if (number > 0) {
          el.value = number.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
          });
        } else {
          el.value = '';
        }

        const rawInput = document.getElementById('raw' + id);
        if (rawInput) {
          rawInput.value = number;
        }
      };

      // Setup payment forms on document ready
      document.addEventListener('DOMContentLoaded', function () {
        setupPaymentForms();
      });

      // Re-setup when modal content changes
      $(document).on('shown.bs.modal', '.modal', function () {
        setupPaymentForms();
      });

      function setupPaymentForms() {
        const paymentForms = document.querySelectorAll('form[action*="/request/pembayaran/agen/"]');

        paymentForms.forEach(function (form) {
          // Remove existing listeners to prevent duplicates
          const newForm = form.cloneNode(true);
          form.parentNode.replaceChild(newForm, form);

          newForm.addEventListener('submit', function (e) {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
              submitButton.disabled = true;
              submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Mengirim...';

              setTimeout(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bx bx-send me-1"></i>Kirim Request';
              }, 10000);
            }
          });
        });
      }
    })();
  </script>

  <script>
    // Payment modal calculation with event delegation
    (function () {
      'use strict';

      function toRupiah(n) {
        return "Rp " + (n || 0).toLocaleString("id-ID");
      }

      function recalcTotal(invoiceId) {
        let total = 0;

        // Sum checked components
        document.querySelectorAll(`#konfirmasiPembayaran${invoiceId} .pilihan:checked:not([data-type="saldo"])`).forEach(function (item) {
          const amount = parseInt(item.getAttribute("data-amount")) || 0;
          total += amount;
        });

        // Subtract saldo if checked
        const saldoCb = document.querySelector(`#konfirmasiPembayaran${invoiceId} .pilihan[data-type="saldo"]`);
        if (saldoCb && saldoCb.checked) {
          const saldoAmount = parseInt(saldoCb.getAttribute("data-amount")) || parseInt(saldoCb.value) || 0;
          total = Math.max(total - saldoAmount, 0);
        }

        // Update total display
        const totalInput = document.getElementById("total" + invoiceId);
        if (totalInput) {
          totalInput.value = toRupiah(total);
        }
      }

      // Event delegation for checkbox changes
      document.addEventListener('change', function (event) {
        if (event.target.matches('.pilihan')) {
          const invoiceId = event.target.getAttribute('data-id');
          if (invoiceId) {
            recalcTotal(invoiceId);
          }
        }
      });

      // Make function globally available
      window.recalcTotal = recalcTotal;
    })();
  </script>
@endsection
