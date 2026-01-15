@extends('layouts.contentNavbarLayout')
@section('title', 'Data Pelanggan Agen')

  {{-- DataTables Bootstrap 5 CSS --}}
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.min.css">
  <style>
    .search-highlight {
      background-color: #fff3cd;
      padding: 2px 4px;
      border-radius: 3px;
    }

    .modern-table {
      border-radius: 0.5rem;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .modern-table thead th {
      background: #343a40;
      border: none;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
    }

    .modern-table tbody tr {
      transition: all 0.3s ease;
    }

    .modern-table tbody tr:hover {
      background-color: #f8f9fa;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .status-badge {
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.5rem 0.75rem;
      border-radius: 0.5rem;
    }

    .search-container {
      background: #f8f9fa;
      border-radius: 0.5rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      border: 1px solid #e9ecef;
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
    }

    .form-group .form-label i {
      font-size: 1rem;
      color: #667eea;
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
    }

    /* DataTables Integration Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing {
      display: none !important; /* Hide default DataTables controls */
    }

    .dataTables_wrapper .dataTables_paginate {
      display: none !important; /* Use custom pagination */
    }

    /* Ensure table styling is preserved */
    table.dataTable {
      border-collapse: collapse !important;
    }

    table.dataTable thead th {
      background: #343a40 !important;
      color: white !important;
    }

    /* Responsive DataTables */
    table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
    table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
      background-color: #667eea;
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
                  <small class="text-muted">{{ $statistics['count_paid'] ?? 0 }} Invoice</small>
                </div>
              </div>
              <h4 class="text-success mb-1">Rp {{ number_format($statistics['total_paid'] ?? 0, 0, ',', '.') }}</h4>
              <div class="progress mb-2" style="height: 6px;">
                <div class="progress-bar bg-success" role="progressbar"
                  style="width: {{ $statistics['percentage_paid'] ?? 0 }}%"
                  aria-valuenow="{{ $statistics['percentage_paid'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted">{{ $statistics['percentage_paid'] ?? 0 }}% dari total</small>
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
                  <small class="text-muted">{{ $statistics['count_unpaid'] ?? 0 }} Invoice</small>
                </div>
              </div>
              <h4 class="text-danger mb-1">Rp {{ number_format($statistics['total_unpaid'] ?? 0, 0, ',', '.') }}</h4>
              <div class="progress mb-2" style="height: 6px;">
                <div class="progress-bar bg-danger" role="progressbar"
                  style="width: {{ $statistics['percentage_unpaid'] ?? 0 }}%"
                  aria-valuenow="{{ $statistics['percentage_unpaid'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted">{{ $statistics['percentage_unpaid'] ?? 0 }}% dari total</small>
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
                  <small class="text-muted">{{ $statistics['count_total'] ?? 0 }} Invoice</small>
                </div>
              </div>
              <h4 class="text-primary mb-1">Rp {{ number_format($statistics['total_amount'] ?? 0, 0, ',', '.') }}</h4>
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
              <small class="text-muted fw-normal ms-2">
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
                      $startYear = $currentYear - 5; // Show last 5 years
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
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
      <div class="d-flex align-items-center gap-3">
        <div>
          @php
            $totalRows = $invoices->total();
          @endphp
          <span class="text-muted" id="searchResults">
            Menampilkan
            <span class="fw-bold text-primary" id="visibleCount">{{ $invoices->count() }}</span>
            dari
            <span class="fw-bold" id="totalCount">{{ $totalRows }}</span> data
          </span>
          <span class="badge bg-info ms-2" id="filterIndicator" style="display: none;">
            <i class="bx bx-filter-alt me-1"></i>Filter Aktif
          </span>
          @if(config('app.debug'))
            <small class="text-muted ms-2">
              ({{ $invoices->count() }} invoices)
            </small>
          @endif
        </div>
      </div>
      <div>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
          <i class="bx bx-refresh me-1"></i>Reset Filter
        </button>
      </div>
    </div>
    <div class="table-responsive mb-2">
      <table class="table modern-table" id="customerTable">
        <thead class="table-dark text-center fw-bold">
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Alamat</th>
            <th>Telp.</th>
            <th>Paket</th>
            <th>Tagihan</th>
            <th>Status Tagihan</th>
            <th>Jatuh Tempo</th>
            <th>Tanggal Bayar</th>
            <th>Aksi</th>
            <th>Status Customer</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody class="text-center">
          @include('agen.partials.customer-table-rows', ['invoices' => $invoices])
        </tbody>
      </table> <!-- Tag penutup table yang sebelumnya hilang -->
    </div>
    <div class="d-flex justify-content-center" id="pagination-container">
      @if($invoices->perPage() != -1)
        {{ $invoices->links('pagination::bootstrap-5') }}
      @endif
    </div>
  </div>

  {{-- Container for modals loaded via AJAX --}}
  <div id="modal-container">@include('agen.partials.payment-modal', ['invoices' => $invoices])</div>
  {{-- DataTables Bootstrap 5 JS --}}
  <script src="https://cdn.datatables.net/2.3.6/js/dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/2.3.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.min.js"></script>

  <script>
    // Wait for jQuery to be loaded
    (function checkJQuery() {
      if (typeof jQuery === 'undefined') {
        setTimeout(checkJQuery, 50);
        return;
      }

      // jQuery is loaded, now initialize DataTables
      $(document).ready(function () {
        let searchTimeout;
        let dataTable; // DataTables instance
        const searchInput = document.getElementById('searchCustomer');
        const tableBody = document.querySelector('#customerTable tbody');
        const paginationContainer = document.getElementById('pagination-container');
        const visibleCountEl = document.getElementById('visibleCount');
        const totalCountEl = document.getElementById('totalCount');

        // Initialize DataTables
        function initDataTable() {
          // Check if DataTables is loaded
          if (typeof $.fn.DataTable === 'undefined') {
            console.error('DataTables is not loaded!');
            return;
          }

          if ($.fn.DataTable.isDataTable('#customerTable')) {
            $('#customerTable').DataTable().destroy();
          }

          dataTable = $('#customerTable').DataTable({
            responsive: true,
            paging: false, // We use custom pagination
            searching: false, // We use custom search
            info: false,
            ordering: true,
            order: [[7, 'desc']], // Sort by Jatuh Tempo column (index 7) descending
            columnDefs: [
              { orderable: false, targets: [9, 10] } // Disable sorting on Aksi and Status Customer columns
            ],
            language: {
              emptyTable: "Tidak ada data yang tersedia",
              zeroRecords: "Tidak ditemukan data yang sesuai"
            },
            drawCallback: function() {
              // Re-initialize tooltips after table redraw
              var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
              var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
              });
            }
          });
        }

        // Initialize on page load
        initDataTable();

      function fetchData(page = 1, search = '', month = '', perPage = '10', status = '', year = '') {
        const url = new URL("{{ route('data-pelanggan-agen-search') }}");
        url.searchParams.append('page', page);
        if (search) url.searchParams.append('search', search);
        if (month) url.searchParams.append('month', month);
        if (year) url.searchParams.append('year', year);
        if (perPage) url.searchParams.append('per_page', perPage);
        if (status) url.searchParams.append('status', status);

        // Add loading indicator
        tableBody.innerHTML = `<tr><td colspan="12" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>`;

        fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(response => response.json())
          .then(data => {
            // Update table content
            tableBody.innerHTML = data.table_html;
            // Update modals
            document.getElementById('modal-container').innerHTML = data.modals_html;

            // Update pagination
            if (perPage === 'all') {
              paginationContainer.innerHTML = ''; // Sembunyikan pagination jika 'Semua'
            } else {
              paginationContainer.innerHTML = data.pagination_html;
            }

            // Update statistics
            updateStatisticsCards(data.statistics);

            // Update counts
            visibleCountEl.textContent = data.visible_count;
            totalCountEl.textContent = data.total_count;

            // Reinitialize DataTables after content update
            initDataTable();

            // Re-initialize tooltips for new content
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl);
            });
          })
          .catch(error => {
            console.error('Error fetching data:', error);
            tableBody.innerHTML = `<tr><td colspan="12" class="text-center py-5 text-danger">Gagal memuat data. Silakan coba lagi.</td></tr>`;
          });
      }

      // Search input handler
      searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          const searchTerm = searchInput.value;
          const month = document.getElementById('bulan').value;
          const year = document.getElementById('tahun').value;
          const perPage = document.getElementById('perPage').value;
          const status = document.getElementById('statusTagihan').value;
          fetchData(1, searchTerm, month, perPage, status, year);
        }, 500); // Debounce
      });

      // Month filter handler
      document.getElementById('bulan').addEventListener('change', function () {
        const searchTerm = searchInput.value;
        const month = this.value;
        const year = document.getElementById('tahun').value;
        const perPage = document.getElementById('perPage').value;
        const status = document.getElementById('statusTagihan').value;
        fetchData(1, searchTerm, month, perPage, status, year);
      });

      // Year filter handler
      document.getElementById('tahun').addEventListener('change', function () {
        const searchTerm = searchInput.value;
        const month = document.getElementById('bulan').value;
        const year = this.value;
        const perPage = document.getElementById('perPage').value;
        const status = document.getElementById('statusTagihan').value;
        fetchData(1, searchTerm, month, perPage, status, year);
      });

      // Status filter handler
      document.getElementById('statusTagihan').addEventListener('change', function () {
        const searchTerm = searchInput.value;
        const month = document.getElementById('bulan').value;
        const year = document.getElementById('tahun').value;
        const perPage = document.getElementById('perPage').value;
        const status = this.value;
        fetchData(1, searchTerm, month, perPage, status, year);
      });

      // Per page filter handler
      document.getElementById('perPage').addEventListener('change', function () {
        const searchTerm = searchInput.value;
        const month = document.getElementById('bulan').value;
        const year = document.getElementById('tahun').value;
        const perPage = this.value;
        const status = document.getElementById('statusTagihan').value;
        fetchData(1, searchTerm, month, perPage, status, year);
      });

      // Pagination handler
      document.addEventListener('click', function (e) {
        const paginationLink = e.target.closest('.pagination a');
        if (paginationLink) {
          e.preventDefault();
          const page = new URL(paginationLink.href).searchParams.get('page');
          const searchTerm = searchInput.value;
          const month = document.getElementById('bulan').value;
          const year = document.getElementById('tahun').value;
          const perPage = document.getElementById('perPage').value;
          const status = document.getElementById('statusTagihan').value;
          fetchData(page, searchTerm, month, perPage, status, year);
        }
      });

      // Reset filters handler
      document.getElementById('resetFilters').addEventListener('click', function () {
        searchInput.value = '';
        document.getElementById('statusTagihan').value = '';
        document.getElementById('perPage').value = '10';
        // Set month to current month
        const currentMonth = new Date().getMonth() + 1;
        const currentYear = new Date().getFullYear();
        document.getElementById('bulan').value = String(currentMonth).padStart(2, '0');
        document.getElementById('tahun').value = String(currentYear);
        fetchData(1, '', String(currentMonth).padStart(2, '0'), '10', '', String(currentYear));
      });

      function updateStatisticsCards(statistics) {
        const paidCard = document.querySelector('.border-success .card-body');
        if (paidCard) {
          paidCard.querySelector('h4').textContent = `Rp ${formatNumber(statistics.total_paid || 0)}`;
          paidCard.querySelector('small').textContent = `${statistics.count_paid || 0} Invoice`;
          paidCard.querySelector('.progress-bar').style.width = `${statistics.percentage_paid || 0}%`;
          paidCard.querySelector('.text-muted:last-child').textContent = `${statistics.percentage_paid || 0}% dari total`;
        }

        const unpaidCard = document.querySelector('.border-danger .card-body');
        if (unpaidCard) {
          unpaidCard.querySelector('h4').textContent = `Rp ${formatNumber(statistics.total_unpaid || 0)}`;
          unpaidCard.querySelector('small').textContent = `${statistics.count_unpaid || 0} Invoice`;
          unpaidCard.querySelector('.progress-bar').style.width = `${statistics.percentage_unpaid || 0}%`;
          unpaidCard.querySelector('.text-muted:last-child').textContent = `${statistics.percentage_unpaid || 0}% dari total`;
        }

        const totalCard = document.querySelector('.border-primary .card-body');
        if (totalCard) {
          totalCard.querySelector('h4').textContent = `Rp ${formatNumber(statistics.total_amount || 0)}`;
          totalCard.querySelector('small').textContent = `${statistics.count_total || 0} Invoice`;
        }
      }

      function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
      }

      }); // End of $(document).ready
    })(); // End of checkJQuery IIFE
  </script>
  <script>
    // Format input as Rupiah currency
    function formatRupiah(el, id) {
      // Ambil hanya angka dari input
      let angka = el.value.replace(/[^0-9]/g, '');
      let number = parseInt(angka, 10) || 0;

      // Format tampilan dengan Rupiah
      if (number > 0) {
        el.value = number.toLocaleString('id-ID', {
          style: 'currency',
          currency: 'IDR',
          minimumFractionDigits: 0
        });
      } else {
        el.value = '';
      }

      // Simpan nilai bersih ke input hidden untuk dikirim ke server
      const rawInput = document.getElementById('raw' + id);
      if (rawInput) {
        rawInput.value = number;
        console.log('Raw value set for invoice ' + id + ':', number); // Debug log
      } else {
        console.error('Raw input not found for ID:', id); // Debug log
      }
    }

    // Setup payment forms
    document.addEventListener('DOMContentLoaded', function () {
      console.log('Setting up payment forms...');

      // Find all payment forms
      const paymentForms = document.querySelectorAll('form[action*="/request/pembayaran/agen/"]');
      console.log('Found', paymentForms.length, 'payment forms');

      // Setup each form
      paymentForms.forEach((form, index) => {
        console.log('Setting up form', index + 1);

        // Add submit event listener
        form.addEventListener('submit', function (e) {
          console.log('Payment form submitted!');

          // Show loading state
          const submitButton = this.querySelector('button[type="submit"]');
          if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Mengirim...';

            // Re-enable after 10 seconds as fallback
            setTimeout(() => {
              submitButton.disabled = false;
              submitButton.innerHTML = '<i class="bx bx-send me-1"></i>Kirim Request';
            }, 10000);
          }

          // Log form data for debugging
          const formData = new FormData(this);
          console.log('Submitting form data:');
          for (let [key, value] of formData.entries()) {
            console.log('-', key, ':', value);
          }
        });
      });
    });
  </script>

  <script>
    // --- Payment Modal Calculation Logic with Event Delegation ---
    function toRupiah(n) {
      return "Rp " + (n || 0).toLocaleString("id-ID");
    }

    function recalcTotal(invoiceId) {
      let total = 0;

      // 1) Sum checked components (tagihan, tambahan, tunggakan)
      document.querySelectorAll(`#konfirmasiPembayaran${invoiceId} .pilihan:checked:not([data-type="saldo"])`).forEach(function (item) {
        const amount = parseInt(item.getAttribute("data-amount")) || 0;
        total += amount;
      });

      // 2) If 'saldo' is checked, subtract it from the total
      const saldoCb = document.querySelector(`#konfirmasiPembayaran${invoiceId} .pilihan[data-type="saldo"]`);
      if (saldoCb && saldoCb.checked) {
        const saldoAmount = parseInt(saldoCb.getAttribute("data-amount")) || parseInt(saldoCb.value) || 0;
        total = Math.max(total - saldoAmount, 0); // Ensure total doesn't go below zero
      }

      // Display the new total
      const totalInput = document.getElementById("total" + invoiceId);
      if (totalInput) totalInput.value = toRupiah(total);
    }

    // Use event delegation on the document to handle clicks on '.pilihan' checkboxes
    document.addEventListener('change', function (event) {
      if (event.target.matches('.pilihan')) {
        const invoiceId = event.target.getAttribute('data-id');
        if (invoiceId) {
          recalcTotal(invoiceId);
        }
      }
    });
  </script>
@endsection
