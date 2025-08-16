@extends('layouts.contentNavbarLayout')
@section('title', 'Data Pelanggan Agen')

@section('page-style')
<style>
    .search-highlight {
        background-color: #fff3cd;
        padding: 2px 4px;
        border-radius: 3px;
    }
    
    .modern-table {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
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
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
    
    .invoice-overdue {
        animation: blink 2s infinite;
    }
    
    @keyframes blink {
        0%, 50% { opacity: 1; }
        51%, 100% { opacity: 0.7; }
    }
    
    /* Statistics Cards Styling */
    .statistics-card {
        transition: all 0.3s ease;
        border-radius: 0.75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .statistics-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
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
        background-color: rgba(0,0,0,0.1);
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
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
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
</style>
@endsection

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
            <strong>Informasi:</strong> Tampilan ini menunjukkan data pelanggan untuk periode
            <strong>{{ $currentMonthName ?? 'Bulan Sekarang' }}</strong>.
            Gunakan filter periode bulan untuk melihat data bulan lain atau pilih "Semua Bulan" untuk melihat semua periode.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="card mb-2">
            <div class="card-header modern-card-header">
                <h4 class="card-title fw-bold">Data Pembayaran Pelanggan - Periode {{ $selectedMonthName ?? 'Periode Sekarang' }}</h4>
                <small class="card-subtitle text-muted">Daftar Pembayaran tagihan periode {{ strtolower($selectedMonthName ?? 'bulan sekarang') }}</small>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-12 mb-3">
                <div class="alert alert-primary alert-dismissible fade show" role="alert">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Statistik Periode:</strong>
                    Menampilkan data untuk periode <strong>{{ $selectedMonthName ?? 'Bulan Sekarang' }}</strong>
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
                            aria-valuenow="{{ $statistics['percentage_paid'] ?? 0 }}"
                            aria-valuemin="0" aria-valuemax="100"></div>
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
                            aria-valuenow="{{ $statistics['percentage_unpaid'] ?? 0 }}"
                            aria-valuemin="0" aria-valuemax="100"></div>
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
                            <div class="progress-bar bg-primary" role="progressbar"
                            style="width: 100%"
                            aria-valuenow="100"
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
                        <small class="text-muted fw-normal">
                            ({{ $customers->total() }} pelanggan, {{ $customers->sum(function($customer) { return $customer->invoice->count(); }) }} invoice periode {{ strtolower($selectedMonthName ?? 'sekarang') }})
                        </small>
                    </h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 mb-2">
                            <div class="row">
                                <div class="col-sm-12">
                                    <label class="form-label">Nama Pelanggan</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Cari nama, alamat, atau nomor HP..."
                                        aria-label="Cari pelanggan..." aria-describedby="button-addon2" id="searchCustomer"
                                        title="Ketik untuk mencari berdasarkan nama, alamat, atau nomor HP">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <div class="row">
                                <div class="col-sm-12">
                                    <label class="form-label">Status Tagihan</label>
                                    <select name="status_tagihan" id="statusTagihan" class="form-select"
                                    title="Filter berdasarkan status pembayaran tagihan">
                                    <option value="" selected>Semua Status</option>
                                    <option value="Belum Bayar">Belum Bayar</option>
                                    <option value="Sudah Bayar">Sudah Bayar</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-4 mb-2">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Periode Bulan</label>
                                <select name="bulan" id="bulan" class="form-select"
                                title="Filter berdasarkan bulan jatuh tempo tagihan">
                                <option value="all">Semua Bulan</option>
                                @php
                                $allMonths = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
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
                        <div class="month-indicator">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-success me-2">●</span>
                                    <small>Bulan dengan data</small>
                                </div>
                                <div>
                                    <span class="text-muted me-2">○</span>
                                    <small>Bulan tanpa data</small>
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
    <hr class="my-2 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            @php
            $totalRows = $customers->sum(function($customer) {
                return $customer->invoice->count() > 0 ? $customer->invoice->count() : 1;
            });
            @endphp
            <span class="text-muted" id="searchResults">
                Menampilkan <span class="fw-bold text-primary" id="visibleCount">{{ $totalRows }}</span>
                dari <span class="fw-bold" id="totalCount">{{ $totalRows }}</span> data
            </span>
            <span class="badge bg-info ms-2" id="filterIndicator" style="display: none;">
                <i class="bx bx-filter-alt me-1"></i>Filter Aktif
            </span>
            @if(config('app.debug'))
            <small class="text-muted ms-2">
                ({{ $customers->count() }} pelanggan, {{ $customers->sum(function($customer) { return $customer->invoice->count(); }) }} invoices)
            </small>
            @endif
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
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody class="text-center">
                @php $rowNumber = 1; @endphp
                @forelse ($customers as $customer)
                @if($customer->invoice->isNotEmpty())
                @foreach($customer->invoice as $invoice)
                <tr class="customer-row text-center" data-id="{{ $customer->id }}"
                    data-tagihan="{{ $invoice->status ? ($invoice->status->nama_status == 'Sudah Bayar' ? '0' : $invoice->tagihan ?? '0') : '0' }}"
                    data-customer-id="{{ $customer->id }}"
                    data-invoice-id="{{ $invoice->id }}"
                    data-tagihan-tambahan="{{ $invoice->tambahan ?? '' }}"
                    data-status-tagihan="{{ $invoice->status ? $invoice->status->nama_status : 'N/A' }}"
                    data-jatuh-tempo="{{ $invoice->jatuh_tempo ?? '' }}"
                    data-bulan-tempo="{{ $invoice->jatuh_tempo ? (function() use ($invoice) { try { return \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('F'); } catch (\Exception $e) { return ''; } })() : '' }}">
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td class="customer-name">{{ $customer->nama_customer }}</td>
                    <td class="customer-address">{{ $customer->alamat }}</td>
                    <td class="nomor-hp">{{ $customer->no_hp }}</td>
                    <td>
                        <span class="badge bg-warning bg-opacity-10 status-badge text-warning">
                            {{ $customer->paket->nama_paket }}
                        </span>
                        @if($customer->status_id == 3)
                        <small class="badge bg-success bg-opacity-10 text-success mt-1">Aktif</small>
                        @elseif($customer->status_id == 9)
                        <small class="badge bg-danger bg-opacity-10 text-danger mt-1">Non Aktif</small>
                        @endif
                    </td>
                    <td>
                        Rp {{ number_format($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan - $invoice->saldo?? 0, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if ($invoice->status)
                        <span class="badge
                                            bg-{{ $invoice->status->nama_status == 'Sudah Bayar' ? 'success' : 'danger' }}
                                            bg-opacity-10
                                            {{ $invoice->status->nama_status == 'Sudah Bayar' ? 'text-success' : 'text-danger' }}
                                            status-badge">
                        {{ $invoice->status->nama_status }}
                    </span>
                    @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary status-badge">N/A</span>
                    @endif
                </td>
                <td>
                    @if ($invoice->jatuh_tempo)
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
                    <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-info' }} bg-opacity-10 {{ $isOverdue ? 'text-danger' : 'text-info' }}">
                        {{ $jatuhTempo->format('d M Y') }}
                        @if($isOverdue)
                        <br><small>Terlambat</small>
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
                    <span class="badge bg-success">
                        @if($invoice->pembayaran()->exists())
                        {{ ($invoice->pembayaran()->latest()->first()->created_at->format('d M Y h:m:s')) }}
                        @else
                        -
                        @endif                            
                    </span>
                </td>                    
                <td>
                    @if($invoice->status && $invoice->status->nama_status != 'Sudah Bayar')
                    <button class="btn btn-outline-success btn-sm mb-1"
                    data-bs-target="#konfirmasiPembayaran{{ $invoice->id }}"
                    data-bs-toggle="modal"
                    title="Request Pembayaran">
                    <i class="bx bx-money"></i>
                </button>
                @else
                <span class="btn btn-outline-secondary btn-sm mb-1 disabled"
                data-bs-toggle="tooltip" data-bs-placement="bottom"
                title="Sudah Dibayar">
                <i class="bx bx-check"></i>
            </span>
            @endif
        </td>
        <td>
            {{ $invoice->pembayaran->first()->keterangan ?? '-' }}
        </td>
    </tr>
    @endforeach
    @else
    <tr class="customer-row text-center" data-id="{{ $customer->id }}"
        data-status-tagihan="N/A"
        data-bulan-tempo="">
        <td class="text-center">{{ $rowNumber++ }}</td>
        <td class="customer-name">{{ $customer->nama_customer }}</td>
        <td class="customer-address">{{ $customer->alamat }}</td>
        <td class="nomor-hp">{{ $customer->no_hp }}</td>
        <td>
            <span class="badge bg-warning bg-opacity-10 status-badge text-warning">
                {{ $customer->paket->nama_paket }}
            </span>
            @if($customer->status_id == 3)
            <small class="badge bg-success bg-opacity-10 text-success mt-1">Aktif</small>
            @elseif($customer->status_id == 9)
            <small class="badge bg-danger bg-opacity-10 text-danger mt-1">Non Aktif</small>
            @endif
        </td>
        <td class="text-center">
            <span class="badge bg-secondary bg-opacity-10 text-secondary status-badge">Tidak Ada Invoice</span>
        </td>
        <td>
            <span class="badge bg-secondary bg-opacity-10 text-secondary">N/A</span>
        </td>
        <td>
            <a href="/detail-pelanggan/{{ $customer->id }}"
                class="btn btn-outline-info btn-sm mb-1"
                data-bs-toggle="tooltip" data-bs-placement="bottom"
                title="Detail Pelanggan">
                <i class="bx bx-show"></i>
            </a>
        </td>
    </tr>
    @endif
    @empty
    <tr>
        <td colspan="11" class="text-center py-5">
            <div class="d-flex flex-column align-items-center">
                <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                <p class="text-muted mb-0">Belum ada Data Pelanggan</p>
            </div>
        </td>
    </tr>
    @endforelse
</tbody>
</table>
</div>
<div class="d-flex justify-content-center">
    {{ $customers->links() }}
</div>
</div>
</div>
</div>
</div>

{{-- Modal Konfirmasi Pembayaran --}}
@foreach ($customers as $customer)
@if($customer->invoice->isNotEmpty())
@foreach($customer->invoice as $invoice)
@if($invoice->status && $invoice->status->nama_status != 'Sudah Bayar')
<div class="modal fade" id="konfirmasiPembayaran{{ $invoice->id }}" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title mb-6" id="modalCenterTitle">
                    <i class="bx bx-wallet me-2 text-success"></i>
                    Konfirmasi Pembayaran
                    <span class="text-dark fw-bold">{{ $customer->nama_customer }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/request/pembayaran/agen/{{ $invoice->id }}" method="POST" enctype="multipart/form-data" id="paymentForm{{ $invoice->id }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                    <!-- Customer Info -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="payment-info-card">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-user-circle me-2 text-primary fs-4"></i>
                                    <div>
                                        <strong class="text-dark">{{ $customer->nama_customer }}</strong><br>
                                        <small class="text-muted">{{ $customer->alamat }} | {{ $customer->no_hp }}</small><br>
                                        <small class="text-muted">Paket: <span class="text-primary fw-bold">{{ $customer->paket->nama_paket }}</span></small><br>
                                        <small class="text-muted">Harga Paket: <span class="text-primary fw-bold">Rp {{ number_format($customer->paket->harga, 0, ',', '.') }}</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tanggal Jatuh Tempo -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="jatuhTempo" class="form-label">Tanggal Jatuh Tempo</label>
                            <input type="date" class="form-control"
                            value="{{ \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('Y-m-d') }}"
                            readonly>
                        </div>
                    </div>
                    
                    <!-- Detail Tagihan -->
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Tagihan Pokok</label>
                            <input type="text" class="form-control"
                            value="Rp {{ number_format($invoice->tagihan ?? 0, 0, ',', '.') }}"
                            readonly>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Biaya Tambahan</label>
                            <input type="text" class="form-control"
                            value="Rp {{ number_format($invoice->tambahan ?? 0, 0, ',', '.') }}"
                            readonly>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Tunggakan</label>
                            <input type="text" class="form-control"
                            value="Rp {{ number_format($invoice->tunggakan ?? 0, 0, ',', '.') }}"
                            readonly>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Sisa Saldo</label>
                            <input type="text" class="form-control"
                            value="Rp {{ number_format($invoice->saldo ?? 0, 0, ',', '.') }}"
                            readonly>
                        </div>
                    </div>
                    
                    <!-- Total -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-bold text-success">Total yang Harus Dibayar</label>
                            <div class="total-payment">
                                <input type="text" name="total" class="form-control fw-bold text-success border-0 bg-transparent fs-5"
                                value="Rp {{ number_format(($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan - $invoice->saldo) ?? 0, 0, ',', '.') }}"
                                readonly>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Input Pembayaran -->
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Jumlah Bayar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control"
                            id="revenueAmount{{ $invoice->id }}"
                            name="revenueAmount"
                            oninput="formatRupiah(this, {{ $invoice->id }})"
                            placeholder="Masukkan jumlah bayar">
                            <input type="hidden" id="raw{{ $invoice->id }}" name="jumlah_bayar" value="0">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="metode_id" class="form-select">
                                <option value="" selected disabled>Pilih Metode Pembayaran</option>
                                <option value="Cash">Cash</option>
                                <option value="Transfer Bank">Transfer</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Bukti Pembayaran -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Bukti Pembayaran</label>
                            <input type="file" class="form-control" name="bukti_pembayaran" accept="image/*">
                            <small class="text-muted">Upload foto bukti pembayaran (opsional)</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success btn-sm" id="submitBtn{{ $invoice->id }}">
                        <i class="bx bx-send me-1"></i>Kirim Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endif
@endforeach

@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchCustomer');
        const statusSelect = document.getElementById('statusTagihan');
        const bulanSelect = document.getElementById('bulan');
        const customerTable = document.getElementById('customerTable');
        const customerRows = customerTable.querySelectorAll('.customer-row');
        
        // Function to filter table rows
        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const selectedStatus = statusSelect.value;
            const selectedBulan = bulanSelect.value;
            
            
            
            customerRows.forEach(function(row, index) {
                const customerName = row.querySelector('.customer-name').textContent.toLowerCase();
                const customerAddress = row.querySelector('.customer-address').textContent.toLowerCase();
                const customerPhone = row.querySelector('.nomor-hp').textContent.toLowerCase();
                
                // Get data attributes for filtering
                const statusTagihan = row.getAttribute('data-status-tagihan') || 'N/A';
                const bulanTempo = row.getAttribute('data-bulan-tempo') || '';
                
                
                
                // Check search term match (name, address, or phone)
                const matchesSearch = searchTerm === '' ||
                customerName.includes(searchTerm) ||
                customerAddress.includes(searchTerm) ||
                customerPhone.includes(searchTerm);
                
                // Check status filter
                let matchesStatus = true;
                if (selectedStatus && selectedStatus !== '') {
                    if (selectedStatus === 'Belum Bayar') {
                        // Show rows that are not "Sudah Bayar" (including N/A, Belum Bayar, etc.)
                        matchesStatus = statusTagihan !== 'Sudah Bayar';
                    } else if (selectedStatus === 'Sudah Bayar') {
                        // Show only rows that are "Sudah Bayar"
                        matchesStatus = statusTagihan === 'Sudah Bayar';
                    }
                }
                
                // Bulan filter is now handled server-side, so always match
                let matchesBulan = true;
                
                // Show/hide row based on all filters
                if (matchesSearch && matchesStatus && matchesBulan) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update row numbers for visible rows
            updateRowNumbers();
            
            // Update search results counter
            updateSearchCounter();
            
            // Show/hide empty state
            toggleEmptyState();
        }
        
        // Function to update row numbers for visible rows
        function updateRowNumbers() {
            const visibleRows = Array.from(customerRows).filter(row => row.style.display !== 'none');
            visibleRows.forEach(function(row, index) {
                const numberCell = row.querySelector('td:first-child');
                if (numberCell) {
                    numberCell.textContent = index + 1;
                }
            });
        }
        
        // Function to update search results counter
        function updateSearchCounter() {
            const visibleRows = Array.from(customerRows).filter(row => row.style.display !== 'none');
            const totalRows = customerRows.length;
            
            document.getElementById('visibleCount').textContent = visibleRows.length;
            document.getElementById('totalCount').textContent = totalRows;
            
            // Show/hide filter indicator and add visual feedback
            const hasActiveFilters = searchInput.value.trim() !== '' ||
            statusSelect.value !== '' ||
            (bulanSelect.value !== '' && bulanSelect.value !== 'all');
            const filterIndicator = document.getElementById('filterIndicator');
            
            if (hasActiveFilters) {
                filterIndicator.style.display = 'inline-block';
                // Add visual feedback to active filters
                if (searchInput.value.trim() !== '') {
                    searchInput.classList.add('filter-active');
                } else {
                    searchInput.classList.remove('filter-active');
                }
                if (statusSelect.value !== '') {
                    statusSelect.classList.add('filter-active');
                } else {
                    statusSelect.classList.remove('filter-active');
                }
                if (bulanSelect.value !== '' && bulanSelect.value !== 'all') {
                    bulanSelect.classList.add('filter-active');
                } else {
                    bulanSelect.classList.remove('filter-active');
                }
            } else {
                filterIndicator.style.display = 'none';
                searchInput.classList.remove('filter-active');
                statusSelect.classList.remove('filter-active');
                bulanSelect.classList.remove('filter-active');
            }
        }
        
        // Function to show/hide empty state
        function toggleEmptyState() {
            const visibleRows = Array.from(customerRows).filter(row => row.style.display !== 'none');
            const tbody = customerTable.querySelector('tbody');
            let emptyRow = tbody.querySelector('.empty-state-row');
            
            if (visibleRows.length === 0) {
                // Hide all customer rows
                customerRows.forEach(row => row.style.display = 'none');
                
                // Show empty state if not exists
                if (!emptyRow) {
                    emptyRow = document.createElement('tr');
                    emptyRow.className = 'empty-state-row';
                    emptyRow.innerHTML = `
                    <td colspan="11" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-dark mt-3 mb-2">Tidak ada hasil</h5>
                            <p class="text-muted mb-0">Tidak ditemukan data yang sesuai dengan pencarian</p>
                        </div>
                    </td>
                `;
                    tbody.appendChild(emptyRow);
                }
                emptyRow.style.display = '';
            } else {
                // Hide empty state
                if (emptyRow) {
                    emptyRow.style.display = 'none';
                }
            }
        }
        
        // Function to reset all filters
        function resetAllFilters() {
            // Reset to current month (default behavior)
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('search');
            currentUrl.searchParams.delete('month'); // This will default to current month
            window.location.href = currentUrl.toString();
        }
        
        // Add event listeners
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Update URL with search parameter while keeping month filter
                const currentUrl = new URL(window.location.href);
                const searchValue = this.value.trim();
                
                if (searchValue) {
                    currentUrl.searchParams.set('search', searchValue);
                } else {
                    currentUrl.searchParams.delete('search');
                }
                
                // Use history.replaceState for search to avoid too many history entries
                window.history.replaceState({}, '', currentUrl.toString());
                
                // Also filter table for immediate feedback
                filterTable();
                
                // Update statistics
                updateStatistics();
            }, 500); // Debounce for 500ms
        });
        
        statusSelect.addEventListener('change', function() {
            filterTable();
            // Update statistics when status filter changes
            updateStatistics();
        });
        
        bulanSelect.addEventListener('change', function() {
            // Show loading state
            showLoadingState();
            
            // Update statistics immediately for better UX
            updateStatistics();
            
            // Reload page with new month filter
            const selectedMonth = this.value;
            const currentUrl = new URL(window.location.href);
            
            if (selectedMonth === 'all') {
                currentUrl.searchParams.delete('month');
            } else {
                currentUrl.searchParams.set('month', selectedMonth);
            }
            
            // Keep search parameter if exists
            const searchValue = searchInput.value.trim();
            if (searchValue) {
                currentUrl.searchParams.set('search', searchValue);
            } else {
                currentUrl.searchParams.delete('search');
            }
            
            // Delay page reload to show statistics update first
            setTimeout(() => {
                window.location.href = currentUrl.toString();
            }, 300);
        });
        
        // Function to show loading state on statistics cards
        function showLoadingState() {
            const statisticsCards = document.querySelectorAll('.statistics-card h4');
            statisticsCards.forEach(card => {
                if (card.textContent.includes('Rp')) {
                    card.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';
                }
            });
        }
        
        // Function to update statistics via AJAX
        function updateStatistics() {
            const searchValue = searchInput.value.trim();
            const statusValue = statusSelect.value;
            const monthValue = bulanSelect.value;
            
            // Show loading state
            showLoadingState();
            
            // Prepare parameters
            const params = new URLSearchParams();
            if (searchValue) params.append('search', searchValue);
            if (statusValue) params.append('status', statusValue);
            if (monthValue && monthValue !== 'all') params.append('month', monthValue);
            
            // Make AJAX request
            fetch(`/agen/data-pelanggan/statistics?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStatisticsCards(data.statistics);
                }
            })
            .catch(error => {
                console.error('Error updating statistics:', error);
                // Restore original values on error
                location.reload();
            });
        }
        
        // Function to update statistics cards with new data
        function updateStatisticsCards(statistics) {
            // Update Sudah Bayar card
            const paidCard = document.querySelector('.border-success .card-body');
            if (paidCard) {
                const paidAmount = paidCard.querySelector('h4');
                const paidCount = paidCard.querySelector('small');
                const paidProgress = paidCard.querySelector('.progress-bar');
                const paidPercentage = paidCard.querySelector('.text-muted:last-child');
                
                if (paidAmount) paidAmount.textContent = `Rp ${formatNumber(statistics.total_paid || 0)}`;
                if (paidCount) paidCount.textContent = `${statistics.count_paid || 0} Invoice`;
                if (paidProgress) paidProgress.style.width = `${statistics.percentage_paid || 0}%`;
                if (paidPercentage) paidPercentage.textContent = `${statistics.percentage_paid || 0}% dari total`;
            }
            
            // Update Belum Bayar card
            const unpaidCard = document.querySelector('.border-danger .card-body');
            if (unpaidCard) {
                const unpaidAmount = unpaidCard.querySelector('h4');
                const unpaidCount = unpaidCard.querySelector('small');
                const unpaidProgress = unpaidCard.querySelector('.progress-bar');
                const unpaidPercentage = unpaidCard.querySelector('.text-muted:last-child');
                
                if (unpaidAmount) unpaidAmount.textContent = `Rp ${formatNumber(statistics.total_unpaid || 0)}`;
                if (unpaidCount) unpaidCount.textContent = `${statistics.count_unpaid || 0} Invoice`;
                if (unpaidProgress) unpaidProgress.style.width = `${statistics.percentage_unpaid || 0}%`;
                if (unpaidPercentage) unpaidPercentage.textContent = `${statistics.percentage_unpaid || 0}% dari total`;
            }
            
            // Update Total card
            const totalCard = document.querySelector('.border-primary .card-body');
            if (totalCard) {
                const totalAmount = totalCard.querySelector('h4');
                const totalCount = totalCard.querySelector('small');
                
                if (totalAmount) totalAmount.textContent = `Rp ${formatNumber(statistics.total_amount || 0)}`;
                if (totalCount) totalCount.textContent = `${statistics.count_total || 0} Invoice`;
            }
        }
        
        // Helper function to format numbers
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
        
        // Reset filters button
        document.getElementById('resetFilters').addEventListener('click', function() {
            resetAllFilters();
        });
        
        // Add clear search functionality
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filterTable();
            }
        });
        
        // Add search icon and clear button to search input
        const searchContainer = searchInput.parentElement;
        if (searchContainer.classList.contains('input-group')) {
            // Add search icon
            const searchIcon = document.createElement('span');
            searchIcon.className = 'input-group-text';
            searchIcon.innerHTML = '<i class="bx bx-search"></i>';
            searchContainer.insertBefore(searchIcon, searchInput);
            
            // Add clear button
            const clearButton = document.createElement('button');
            clearButton.className = 'btn btn-outline-secondary';
            clearButton.type = 'button';
            clearButton.innerHTML = '<i class="bx bx-x"></i>';
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                filterTable();
                searchInput.focus();
            });
            searchContainer.appendChild(clearButton);
        }
        
        // Initialize search input from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');
        if (searchParam) {
            searchInput.value = searchParam;
        }
        
        // Initialize counter on page load
        updateSearchCounter();
        
        // Initialize statistics update if there are active filters
        const hasActiveFilters = searchInput.value.trim() !== '' || statusSelect.value !== '';
        if (hasActiveFilters) {
            updateStatistics();
        }
    });
    
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
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Setting up payment forms...');
        
        // Find all payment forms
        const paymentForms = document.querySelectorAll('form[action*="/request/pembayaran/agen/"]');
        console.log('Found', paymentForms.length, 'payment forms');
        
        // Setup each form
        paymentForms.forEach((form, index) => {
            console.log('Setting up form', index + 1);
            
            // Add submit event listener
            form.addEventListener('submit', function(e) {
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
@endsection