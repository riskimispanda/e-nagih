@extends('layouts.contentNavbarLayout')

@section('title', 'Rencana Anggaran Biaya')
<style>
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
        
    /* .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3) !important;
    } */
    
    .card.bg-info {
        background: linear-gradient(135deg, #0396FF 0%, #0D47A1 100%) !important;
    }
    
    .card.bg-warning {
        background: linear-gradient(135deg, #FFC107 0%, #FF6F00 100%) !important;
    }
    
    .card.bg-danger {
        background: linear-gradient(135deg, #FF4B2B 0%, #FF416C 100%) !important;
    }
    
    .card-body {
        position: relative;
        overflow: hidden;
    }
    
    /* .card-body::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100%;
        background: rgba(255,255,255,0.1);
        transform: skewX(-15deg) translateX(50px);
    } */
    
    .bx {
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }
    
    .display-6 {
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        letter-spacing: -0.5px;
    }
    
    .card-text {
        opacity: 0.9;
    }
    
    @media (max-width: 768px) {
        .col-sm-4 {
            margin-bottom: 1.5rem;
        }
    }
    
    .card > .card-header {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
        border-bottom: 2px solid #e9ecef;
        padding: 1.5rem 2rem;
        position: relative;
        margin-bottom: 2rem !important;
    }
    
    .card > .card-header::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 80px;
        height: 2px;
        background: linear-gradient(to right, #0396FF, #0D47A1);
    }
    
    .card > .card-header .card-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
        letter-spacing: -0.5px;
        position: relative;
        padding-left: 1rem;
    }
    
    .card > .card-header .card-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 24px;
        background: linear-gradient(to bottom, #0396FF, #0D47A1);
        border-radius: 4px;
    }
    
    /* Table Styling */
    .transaction-table {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
    }
    
    .table thead th {
        border: none;
        padding: 15px 20px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: white;
    }
    
    .table tbody td {
        padding: 15px 20px;
        vertical-align: middle;
        border-color: #f1f5f9;
        color: #475569;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8fafc;
        transition: all 0.2s ease;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn-action {
        padding: 6px 10px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
    }
    
    .amount-cell {
        font-family: 'Monaco', monospace;
        font-weight: 500;
    }
    
    .debit-amount {
        color: #10b981;
    }
    
    .kredit-amount {
        color: #ef4444;
    }
    /* Enhanced Modern Design Styles */
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --card-shadow: 0 4px 25px rgba(0, 0, 0, 0.06);
        --hover-shadow: 0 8px 35px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
        --border-radius: 1rem;
    }
    
    /* Card Enhancements */
    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        overflow: hidden;
        background: #ffffff;
    }
    
    /* .card:hover {
    box-shadow: var(--hover-shadow);
    transform: translateY(-2px);
    } */
    
    /* Enhanced Header Design */
    .header-with-pattern {
        background: var(--primary-gradient);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        padding: 2.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .header-with-pattern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        opacity: 0.1;
        animation: patternMove 20s linear infinite;
    }
    
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    @keyframes patternMove {
        0% { transform: translate(0, 0); }
        100% { transform: translate(60px, 60px); }
    }
    
    /* Enhanced Header Content */
    .header-content {
        position: relative;
        z-index: 2;
    }
    
    .header-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .header-content:hover .header-icon {
        transform: scale(1.05) rotate(-5deg);
        background: rgba(255, 255, 255, 0.25);
    }
    
    /* Enhanced Stats Display */
    .stats-summary {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 1rem 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .summary-item {
        padding: 0.5rem 1rem;
        transition: var(--transition);
        border-right: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .summary-item:last-child {
        border-right: none;
    }
    
    .summary-item:hover {
        transform: translateY(-2px);
    }
    
    /* Enhanced Search Bar */
    .search-container {
        position: relative;
        z-index: 2;
    }
    
    .search-container .input-group {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .search-container .input-group:focus-within {
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        transform: translateY(-1px);
    }
    
    .search-container .form-control {
        border: none;
        padding: 0.75rem 1rem;
        background: transparent;
    }
    
    /* Enhanced User Avatar */
    .avatar-initial {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--primary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
        text-transform: uppercase;
        transition: var(--transition);
    }
    
    tr:hover .avatar-initial {
        transform: scale(1.05) rotate(-3deg);
    }
    
    /* Enhanced Role Badges */
    .role-badge {
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        transition: var(--transition);
    }
    
    .role-badge.role-admin {
        background-color: #fee2e2;
        color: #ef4444;
    }
    
    .role-badge.role-teknisi {
        background-color: #dcfce7;
        color: #10b981;
    }
    
    .role-badge.role-logistik {
        background-color: #fef3c7;
        color: #f59e0b;
    }
    
    .role-badge.role-default {
        background-color: #e0e7ff;
        color: #6366f1;
    }
    
    /* Enhanced Action Buttons */
    .action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        border-radius: 8px;
        transition: var(--transition);
        margin: 0 0.2rem;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
    }
    
    .action-btn i {
        font-size: 1rem;
    }
    
    /* Enhanced Modal/Offcanvas */
    .offcanvas {
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        border: none;
        box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
    }
    
    .offcanvas-header {
        background: var(--primary-gradient);
        padding: 2rem;
        border-bottom: none;
    }
    
    .offcanvas-body {
        padding: 2rem;
    }
    
    /* Form Controls */
    .form-control, .form-select {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        border-color: #e2e8f0;
        transition: var(--transition);
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    
    .form-text {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .header-with-pattern {
            padding: 1.5rem;
        }
        
        .stats-summary {
            display: none;
        }
        
        .search-container {
            width: 100%;
            margin-top: 1rem;
        }
    }
</style>
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card-header mb-5">
            <h4 class="card-title fw-bold">Rencana Anggaran Biaya</h4>
            <small class="text-muted">Dokumentasi perencanaan anggaran proyek</small>
        </div>
        <div class="card mb-5 p-4">
            <div class="row mb-4">
                <div class="col-sm-4">
                    <label class="form-label mb-2"><i class="bx bx-calendar me-1 text-primary"></i>Filter Tahun</label>
                    <select id="filter-tahun" class="form-select">
                        <option value="">-- Semua Tahun --</option>
                        @for ($i = date('Y'); $i <= date('Y') + 5; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label mb-2"><i class="bx bx-calendar me-1 text-primary"></i>Filter Bulan</label>
                    <select id="filter-bulan" class="form-select">
                        <option value="">-- Semua Bulan --</option>
                        @foreach ([
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ] as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label mb-2"><i class="bx bx-briefcase me-1 text-primary"></i>Filter Kegiatan</label>
                    <select id="filter-kegiatan" class="form-select">
                        <option value="">-- Semua Kegiatan --</option>
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 mb-2">
                    <div class="card bg-danger text-white shadow rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class='bx bx-wallet-alt fs-3 me-2'></i>
                                <h6 class="card-title text-white mb-0 fw-bold">Total Saldo</h6>
                            </div>
                            <p class="card-text display-8 fw-bold mb-2" id="saldo">Rp 0</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 mb-2">
                    <div class="card bg-warning text-white shadow rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class='bx bx-wallet-alt fs-3 me-2'></i>
                                <h6 class="card-title text-white mb-0 fw-bold">Total Anggaran</h6>
                            </div>
                            <p id="pagu-tahun" class="card-text display-8 fw-bold mb-2">Rp.0</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 mb-2">
                    <div class="card bg-success text-white shadow rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class='bx bx-check-circle fs-3 me-2'></i>
                                <h6 class="card-title text-white mb-0 fw-bold">Anggaran Terealisasi</h6>
                            </div>
                            <p id="anggaran-terealisasi" class="card-text display-8 fw-bold mb-2">Rp.0</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 mb-2">
                    <div class="card bg-info text-white shadow rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class='bx bx-calculator fs-3 me-2'></i>
                                <h6 class="card-title text-white mb-0 fw-bold">Sisa Anggaran</h6>
                            </div>
                            <p id="sisa-anggaran" class="card-text display-8 fw-bold mb-2">Rp.0</p>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-start">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pills" data-bs-toggle="offcanvas" data-bs-target="#pagu">
                    <i class="bx bx-plus me-1 m-0"></i>
                    Tambah
                </button>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-title border-bottom mb-5">
                    <h6 class="text-muted">RAB Record Table</h6>
                </div>
                <div class="table-responsive" id="result-container">
                    @include('rab.partials.data-table', ['data' => $data])
                </div>
                <div class="d-flex justify-content-start mt-3 align-items-center gap-2">
                    <span>Halaman:</span>
                    <select id="paginationDropdown" class="form-select form-select-sm" style="width: auto;">
                        @for ($i = 1; $i <= $data->lastPage(); $i++)
                            <option value="{{ $i }}" {{ $i == $data->currentPage() ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                    <span>dari {{ $data->lastPage() }} halaman</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="pagu" aria-labelledby="offcanvasBothLabel">
    <div class="offcanvas-header" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-bottom: none;">
        <div class="d-flex align-items-center">
            <div class="me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(255, 255, 255, 0.2); border-radius: 8px;">
                <i class='bx bx-money text-white fs-5'></i>
            </div>
            <div>
                <h5 id="offcanvasBothLabel" class="offcanvas-title text-white mb-0 fw-semibold">Tambah Pagu Anggaran</h5>
                <small class="text-white-50">Rencana Anggaran Biaya</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" style="padding: 1.5rem;">
        <form action="/rab/store" method="POST" id="addUserForm">
            @csrf
            <div class="mb-4">
                <label class="form-label fw-medium mb-2" for="user_name">
                    <i class='bx bx-money me-1'></i>Jumlah Anggaran
                </label>
                <input oninput="formatAndSync(this)" type="text" class="form-control mb-2" id="pagu"
                placeholder="Rp. 1000.000.000" required>
                <input hidden type="text" class="form-control" id="generate" oninput="this.value = formatNumber($this.value)" name="pagu">
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-medium mb-2" for="user_email">
                    <i class='bx bx-calendar me-1'></i>Tahun Anggaran
                </label>
                <select name="tahun" id="tahun" class="form-select mb-2" required>
                    @for ($i = date('Y'); $i <= date('Y') + 5; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-medium mb-2" for="bulan">
                    <i class="bx bx-calendar me-1"></i>Bulan Anggaran
                </label>
                <select name="bulan" id="bulan" class="form-select mb-2" required>
                    <option value="" disabled selected>Pilih Bulan</option>
                    @foreach ([
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ] as $num => $nama)
                    <option value="{{ $num }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-medium mb-2">
                    <i class="bx bx-detail me-1"></i>Kegiatan Anggaran
                </label>
                <input type="text" name="kegiatan" class="form-control" placeholder="Belanja Logistik" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-medium mb-2">
                    <i class="bx bx-cart me-1"></i>Jumlah Item
                </label>
                <input type="number" name="item" class="form-control" placeholder="100" min="1">
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-medium mb-2">
                    <i class="bx bx-clipboard me-1"></i>Keterangan
                </label>
                <textarea name="keterangan" cols="30" rows="5" class="form-control" required></textarea>
            </div>
            
            <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="offcanvas">
                    <i class='bx bx-x me-1'></i>Batal
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark fw-semibold" id="detailModalLabel">
                    <i class='bx bx-detail me-2 text-muted'></i>Detail RAB
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="detailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Ajax --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function formatRupiah(angka) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        
        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return 'Rp ' + rupiah;
    }
    
    function cleanRupiah(rupiah) {
        return rupiah.replace(/[^,\d]/g, '').replace(',', '');
    }
    
    // Gabungkan: saat input diketik, format ke rupiah + sync ke input kedua
    function formatAndSync(el) {
        el.value = formatRupiah(el.value);
        document.getElementById('generate').value = cleanRupiah(el.value);
    }
</script>
<script>
    // Function to format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount || 0);
    }
    
    // Load kegiatan options on page load
    async function loadKegiatanOptions() {
        try {
            const response = await fetch('{{ route("rab-kegiatan") }}');
            const kegiatan = await response.json();
            
            const select = document.getElementById('filter-kegiatan');
            
            // Clear existing options except the first one
            while (select.children.length > 1) {
                select.removeChild(select.lastChild);
            }
            
            // Add kegiatan options
            kegiatan.forEach(item => {
                const option = document.createElement('option');
                option.value = item;
                option.textContent = item;
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading kegiatan options:', error);
        }
    }
    
    // Real-time filter function using fetch
    async function performFilter() {
        const bulan = document.getElementById('filter-bulan').value;
        const tahun = document.getElementById('filter-tahun').value;
        const kegiatan = document.getElementById('filter-kegiatan').value;
        
        console.log('Filter changed:', { bulan, tahun, kegiatan });
        
        try {
            const params = new URLSearchParams();
            if (bulan) params.append('bulan', bulan);
            if (tahun) params.append('tahun', tahun);
            if (kegiatan) params.append('kegiatan', kegiatan);
            
            const response = await fetch(`{{ route("rab-filter") }}?${params.toString()}`);
            const data = await response.json();
            
            console.log('Response received:', data);
            
            // Update table
            document.getElementById('result-container').innerHTML = data.html;
            
            // Update ALL cards with formatted currency
            document.getElementById('saldo').textContent = formatCurrency(data.saldo);
            document.getElementById('pagu-tahun').textContent = formatCurrency(data.total);
            document.getElementById('anggaran-terealisasi').textContent = formatCurrency(data.terealisasi);
            document.getElementById('sisa-anggaran').textContent = formatCurrency(data.sisa);
            
            console.log('All cards updated:', {
                saldo: data.saldo,
                total: data.total,
                terealisasi: data.terealisasi,
                sisa: data.sisa
            });
        } catch (error) {
            console.error('Filter Error:', error);
            alert('Gagal mengambil data');
        }
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial values from server data
        document.getElementById('saldo').textContent = formatCurrency({{ $total }});
        document.getElementById('pagu-tahun').textContent = formatCurrency({{ $totalAnggaran ?? 0 }});
        document.getElementById('anggaran-terealisasi').textContent = formatCurrency({{ $totalTerealisasi ?? 0 }});
        document.getElementById('sisa-anggaran').textContent = formatCurrency({{ $sisaAnggaran ?? 0 }});
        
        // Load kegiatan options
        loadKegiatanOptions();
        
        // Add event listeners for filters
        document.getElementById('filter-bulan').addEventListener('change', performFilter);
        document.getElementById('filter-tahun').addEventListener('change', performFilter);
        document.getElementById('filter-kegiatan').addEventListener('change', performFilter);

        console.log('Initial data loaded:', {
            total: {{ $totalAnggaran ?? 0 }},
            terealisasi: {{ $totalTerealisasi ?? 0 }},
            sisa: {{ $sisaAnggaran ?? 0 }}
        });
    });
    
    // Handle detail button click using event delegation
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.btn-detail')) {
            const button = e.target.closest('.btn-detail');
            const rabId = button.getAttribute('data-id');
            
            console.log('Detail button clicked for RAB ID:', rabId);
            
            // Show loading state
            document.getElementById('detailContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-secondary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat detail RAB...</p>
                </div>
            `;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
            
            try {
                // Fetch detail data
                const response = await fetch(`/rab/detail/${rabId}`);
                const data = await response.json();
                
                console.log('Detail response:', data);
                
                if (!data.success) {
                    document.getElementById('detailContent').innerHTML = `
                        <div class="text-center py-4">
                            <i class="bx bx-error-circle text-danger fs-1"></i>
                            <p class="text-danger mt-2">${data.message || 'Gagal memuat detail RAB'}</p>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    `;
                    return;
                }
                
                const rab = data.rab;
                const bulanNama = data.bulanNama;
                
                // Build minimalist detail content
                const detailHtml = `
                    <div class="row g-4">
                        <!-- Basic Information -->
                        <div class="col-12">
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="text-dark mb-3">Informasi Dasar</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Kegiatan</small>
                                            <span class="fw-medium">${rab.kegiatan}</span>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Periode</small>
                                            <span class="fw-medium">${bulanNama} ${rab.tahun_anggaran}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Jumlah Item</small>
                                            <span class="fw-medium">${rab.item || '-'}</span>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Status</small>
                                            ${rab.status_id == 11 ? 
                                                '<span class="badge bg-success">'+rab.status.nama_status+'</span>' : 
                                                rab.status_id == 12 ? 
                                                '<span class="badge bg-danger">'+rab.status.nama_status+'</span>' : 
                                                '<span class="badge bg-warning">'+rab.status.nama_status+'</span>'
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Budget Information -->
                        <div class="col-12">
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="text-dark mb-3">Informasi Anggaran</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <small class="text-muted d-block">Total Anggaran</small>
                                            <div class="fw-bold text-dark">${formatCurrency(rab.jumlah_anggaran)}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <small class="text-muted d-block">Terealisasi</small>
                                            <div class="fw-bold text-success">${formatCurrency(data.totalTerealisasi)}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-light rounded">
                                            <small class="text-muted d-block">Sisa Anggaran</small>
                                            <div class="fw-bold text-primary">${formatCurrency(data.sisaAnggaran)}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">Progress Realisasi</small>
                                        <small class="text-muted">${(data.totalTerealisasi / rab.jumlah_anggaran * 100).toFixed(1)}%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: ${(data.totalTerealisasi / rab.jumlah_anggaran * 100).toFixed(1)}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="col-12">
                            <h6 class="text-dark mb-3">Keterangan</h6>
                            <div class="p-3 bg-light rounded">
                                <p class="mb-0 text-muted">${rab.keterangan || 'Tidak ada keterangan'}</p>
                            </div>
                        </div>
                        
                        <!-- Admin Info -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                <span>Dibuat oleh: <strong>${rab.usr.name}</strong></span>
                                <span>Total Pengeluaran: ${data.pengeluaranCount || 0} item (${data.approvedPengeluaranCount || 0} disetujui)</span>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('detailContent').innerHTML = detailHtml;
            } catch (error) {
                console.error('Error fetching detail:', error);
                
                let errorMessage = 'Gagal memuat detail RAB';
                if (error.response && error.response.status === 404) {
                    errorMessage = 'Data RAB tidak ditemukan';
                } else if (error.response && error.response.status === 500) {
                    errorMessage = 'Terjadi kesalahan server';
                }
                
                document.getElementById('detailContent').innerHTML = `
                    <div class="text-center py-4">
                        <i class="bx bx-error-circle text-danger fs-1"></i>
                        <p class="text-danger mt-2">${errorMessage}</p>
                        <small class="text-muted d-block">RAB ID: ${rabId}</small>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" data-bs-dismiss="modal">Tutup</button>
                    </div>
                `;
            }
        }
    });
    
</script>
{{-- Pagination dropdown --}}
<script>
    document.getElementById('paginationDropdown').addEventListener('change', function () {
        let page = this.value;
        let url = new URL(window.location.href);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
    });
</script>

@endsection