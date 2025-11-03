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
            <strong>{{ $selectedMonthName ?? 'Bulan Sekarang' }}</strong>.
            Termasuk pelanggan yang sudah dihapus (ditandai dengan strikethrough).
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
        <div class="card mb-2">
            <div class="card-header modern-card-header">
                <h4 class="card-title fw-bold">Data Pembayaran Pelanggan - Periode {{ $selectedMonthName ?? 'Periode Sekarang' }}</h4>
                <small class="card-subtitle text-muted">
                    Daftar Pembayaran tagihan periode {{ strtolower($selectedMonthName ?? 'bulan sekarang') }} 
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
                    Menampilkan data untuk periode <strong>{{ $selectedMonthName ?? 'Bulan Sekarang' }}</strong>
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
                            ({{ $invoices->total() }} invoice periode {{ strtolower($selectedMonthName ?? 'sekarang') }})
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
            $totalRows = $invoices->count();
            @endphp
            <span class="text-muted" id="searchResults">
                Menampilkan 
                <span class="fw-bold text-primary" id="visibleCount">{{ $totalRows }}</span>
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
        {{ $invoices->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- Container for modals loaded via AJAX --}}
<div id="modal-container"></div>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let searchTimeout;
        const searchInput = document.getElementById('searchCustomer');
        const tableBody = document.querySelector('#customerTable tbody');
        const paginationContainer = document.getElementById('pagination-container');
        const visibleCountEl = document.getElementById('visibleCount');
        const totalCountEl = document.getElementById('totalCount');

        function fetchData(page = 1, search = '', month = '') {
            const url = new URL("{{ route('data-pelanggan-agen-search') }}");
            url.searchParams.append('page', page);
            if (search) url.searchParams.append('search', search);
            if (month) url.searchParams.append('month', month);

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

                // Place modals into the container
                const modalContainer = document.getElementById('modal-container');
                modalContainer.innerHTML = data.table_html;

                // Update pagination
                paginationContainer.innerHTML = data.pagination_html;

                // Update statistics
                updateStatisticsCards(data.statistics);

                // Update counts
                visibleCountEl.textContent = data.visible_count;
                totalCountEl.textContent = data.total_count;

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
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = searchInput.value;
                const month = document.getElementById('bulan').value;
                fetchData(1, searchTerm, month);
            }, 500); // Debounce
        });

        // Month filter handler
        document.getElementById('bulan').addEventListener('change', function() {
            const searchTerm = searchInput.value;
            const month = this.value;
            fetchData(1, searchTerm, month);
        });

        // Pagination handler
        document.addEventListener('click', function(e) {
            if (e.target.matches('.pagination a')) {
                e.preventDefault();
                const page = new URL(e.target.href).searchParams.get('page');
                const searchTerm = searchInput.value;
                const month = document.getElementById('bulan').value;
                fetchData(page, searchTerm, month);
            }
        });

        // Reset filters handler
        document.getElementById('resetFilters').addEventListener('click', function() {
            searchInput.value = '';
            document.getElementById('statusTagihan').value = '';
            // Set month to current month
            const currentMonth = new Date().getMonth() + 1;
            document.getElementById('bulan').value = String(currentMonth).padStart(2, '0');
            fetchData(1, '', String(currentMonth).padStart(2, '0'));
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

    });
</script>
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
                const isDeleted = row.getAttribute('data-customer-deleted') === 'true';
                
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
                        // Show rows that are not "Sudah Bayar" AND customer is not deleted
                        matchesStatus = statusTagihan !== 'Sudah Bayar' && !isDeleted;
                    } else if (selectedStatus === 'Sudah Bayar') {
                        // Show only rows that are "Sudah Bayar" (both active and deleted customers)
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
                    <td colspan="12" class="text-center py-5">
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
    document.addEventListener('change', function(event) {
        if (event.target.matches('.pilihan')) {
            const invoiceId = event.target.getAttribute('data-id');
            if (invoiceId) {
                recalcTotal(invoiceId);
            }
        }
    });
</script>
@endsection