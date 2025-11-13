@extends('layouts.contentNavbarLayout')

@section('title', 'Non Langganan')

<style>
    .revenue-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .revenue-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    
    .search-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }
    
    .table-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        overflow: hidden;
    }
    
    .search-input {
        border-radius: 8px;
        border: 1px solid #d0d7de;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .search-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        outline: none;
    }
    
    .btn-modern {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        border: none;
    }
    
    .btn-modern:hover {
        transform: translateY(-1px);
    }
    
    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .action-btn {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        border: none;
        transition: all 0.2s ease;
    }
    
    .action-btn:hover {
        transform: translateY(-1px);
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .loading-content {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .spinner {
        width: 24px;
        height: 24px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #0d6efd;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        
        100% {
            transform: rotate(360deg);
        }
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
    
    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
</style>

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <i class="bx bx-home-alt me-1"></i>Dashboard
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="#" class="text-decoration-none">Keuangan</a>
        </li>
    </ol>
</nav>

<!-- Header Section -->
<div class="row mb-4">
    <div class="col-12">
        <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-2">Data Pendapatan Non Langganan</h4>
            <p class="text-muted mb-0">Kelola dan pantau data pendapatan perusahaan</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="refreshData()" class="btn btn-outline-danger btn-sm">
                <i class="bx bx-refresh me-2"></i>
                Refresh
            </button>
        </div>
    </div>
</div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <!-- Total Revenue -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="revenue-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1 fw-medium">Total Pendapatan</p>
                    <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($jumlah ?? 0, 0, ',', '.') }}</h5>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bx bx-trending-up"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="revenue-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1 fw-medium">Jumlah Pendapatan Harian</p>
                    <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($jumlahDaily ?? 0, 0, ',', '.') }}
                    </h5>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bx bx-calendar"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4">
        <div class="revenue-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1 fw-medium">Jumlah Pendapatan Bulanan</p>
                    <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($jumlahMonthly ?? 0, 0, ',', '.') }}
                    </h5>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bx bx-calendar"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Payment Methods Statistics -->
<div class="row g-4 mb-4">
    <!-- Cash Payments -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="revenue-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1 fw-medium">Pembayaran Cash</p>
                    <h5 class="fw-bold text-dark mb-0">{{ $cashCount ?? 0 }}</h5>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bx bx-money"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bank Transfer -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="revenue-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1 fw-medium">Transfer Bank</p>
                    <h5 class="fw-bold text-dark mb-0">{{ $transferCount ?? 0 }}</h5>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-primary">
                    <i class="bx bx-credit-card"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- E-Wallet -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="revenue-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1 fw-medium">E-Wallet</p>
                    <h5 class="fw-bold text-dark mb-0">{{ $ewalletCount ?? 0 }}</h5>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bx bx-wallet"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="search-card p-4 mb-4">
    <form id="filterForm">
        <div class="row g-3">
            <!-- Search Input -->
            <div class="col-12 col-lg-5">
                <label class="form-label fw-medium text-dark">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="searchInput" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari nama customer atau paket..." class="form-control">
                </div>
            </div>
            
            <div class="col-12 col-lg-5">
                <label class="form-label fw-medium text-dark">Bulan</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                    <select name="month" id="monthFilter" class="form-select">
                        <option value="">Semua Bulan</option>
                        @php
                            \Carbon\Carbon::setLocale('id');
                            $currentMonth = date('n'); // Bulan sekarang (1-12)
                            $selectedMonth = request()->get('month', $currentMonth);
                        @endphp
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ (isset($month) && $month == $i) ? 'selected' : (($month === null || $month === '') && $i == $currentMonth ? 'selected' : '') }}>
                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

        </div>
        
        <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
            <button type="button" onclick="applyFilters()" class="btn btn-outline-warning btn-modern btn-sm">
                <i class="bx bx-filter-alt me-2"></i>
                Terapkan Filter
            </button>
            <button type="button" onclick="clearFilters()" class="btn btn-outline-secondary btn-modern">
                <i class="bx bx-x me-2"></i>
                Reset Filter
            </button>
        </div>
    </form>
</div>

<div class="table-card" id="revenueTable">
    <div class="p-6 border-bottom">
        <h5 class="fw-semibold text-dark mb-0">Pendapatan Lain-lain</h5>
    </div>
    
    <div class="p-4 border-bottom d-flex justify-content-start">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRevenueModal">
            <i class="bx bx-plus me-2"></i>
            Tambah
        </button>
    </div>
    
    <div class="table-responsive p-2">
        <div id="tableContainer">
            <table class="table table-hover" style="font-size: 14px">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Jumlah Pendapatan</th>
                        <th>Jenis Pendapatan</th>
                        <th>Deskripsi</th>
                        <th>Tanggal</th>
                        <th>Metode Bayar</th>
                        <th>Admin</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @php
                    $no = 1;
                    @endphp
                    @forelse($pendapatan ?? [] as $index => $revenue)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bx bx-money text-dark me-2"></i>
                                <span class="fw-semibold text-dark">
                                    Rp {{ number_format($revenue->jumlah_pendapatan, 0, ',', '.') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info">
                                {{ $revenue->jenis_pendapatan }}
                            </span>
                        </td>
                        <td>
                            <div class="text-muted">
                                {{ Str::limit($revenue->deskripsi, 50) }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bx bx-calendar text-primary me-2"></i>
                                {{ \Carbon\Carbon::parse($revenue->tanggal)->format('d/m/Y') }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bx bx-credit-card text-secondary me-2"></i>
                                {{ $revenue->metode_bayar }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bx bx-user text-primary me-2"></i>
                                {{ $revenue->user->name ?? 'N/A' }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button type="button" onclick="editPendapatan({{ $revenue->id }})"
                                    class="action-btn bg-warning bg-opacity-10 text-warning btn-sm" title="Edit">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button type="button" onclick="confirmDeletePendapatan({{ $revenue->id }}, '{{ $revenue->jenis_pendapatan }}')"
                                    class="action-btn bg-danger bg-opacity-10 text-danger btn-sm" title="Hapus">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                                <p class="text-muted mb-0">Belum ada data pendapatan lain-lain yang tersedia</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $pendapatan->links() }}
        </div>
    </div>
</div>


<!-- Add Revenue Modal -->
<div class="modal fade" id="addRevenueModal" tabindex="-1" aria-labelledby="addRevenueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRevenueModalLabel">Tambah Pendapatan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRevenueForm" action="/tambah/pendapatan" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="revenueAmount" class="form-label">Jumlah Pendapatan</label>
                        <input type="text" class="form-control" id="revenueAmount" name="jumlah_pendapatan"
                        placeholder="Masukkan jumlah pendapatan" oninput="formatRupiah(this)" required>
                        <input type="hidden" name="jumlah_pendapatan_raw" id="revenueAmountRaw">
                    </div>
                    <div class="mb-3">
                        <label for="revenueType" class="form-label">Jenis Pendapatan</label>
                        <input type="text" class="form-control" id="revenueType" name="jenis_pendapatan" placeholder="Masukkan jenis pendapatan" required>
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Metode Pembayaran</label>
                        <select class="form-select" id="paymentMethod" name="metode_bayar" required>
                            <option value="" disabled selected>Pilih metode pembayaran</option>
                            @foreach ($metode as $method)
                            <option value="{{ $method->nama_metode }}">{{ $method->nama_metode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Pembayaran</label>
                        <input type="file" class="form-control" name="bukti_pembayaran"
                        placeholder="Masukkan URL bukti pembayaran (opsional)">
                    </div>
                    <div class="mb-3">
                        <label for="revenueDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="revenueDescription" name="deskripsi"
                        placeholder="Masukkan deskripsi pendapatan" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="revenueDate" class="form-label">Tanggal Pendapatan</label>
                        <input type="date" class="form-control" id="revenueDate" name="tanggal"
                        value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Revenue Modal -->
<div class="modal fade" id="editRevenueModal" tabindex="-1" aria-labelledby="editRevenueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRevenueModalLabel">Edit Pendapatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRevenueForm" method="POST" enctype="multipart/form-data" onsubmit="prepareEditFormData(event)">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editRevenueAmount" class="form-label">Jumlah Pendapatan</label>
                        <input type="text" class="form-control" id="editRevenueAmount" name="jumlah_pendapatan"
                        placeholder="Masukkan jumlah pendapatan" oninput="formatRupiah(this)" required>
                        <input type="hidden" name="jumlah_pendapatan_raw" id="editRevenueAmountRaw">
                    </div>
                    <div class="mb-3">
                        <label for="editRevenueType" class="form-label">Jenis Pendapatan</label>
                        <input type="text" class="form-control" id="editRevenueType" name="jenis_pendapatan" placeholder="Masukkan jenis pendapatan">
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentMethod" class="form-label">Metode Pembayaran</label>
                        <select class="form-select" id="editPaymentMethod" name="metode_bayar" required>
                            <option value="" disabled selected>Pilih metode pembayaran</option>
                            @foreach ($metode as $method)
                            <option value="{{ $method->nama_metode }}">{{ $method->nama_metode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Pembayaran</label>
                        <input type="file" class="form-control" name="bukti_pembayaran" accept="image/*">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah bukti pembayaran</small>
                    </div>
                    <div class="mb-3">
                        <label for="editRevenueDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="editRevenueDescription" name="deskripsi"
                        placeholder="Masukkan deskripsi pendapatan" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editRevenueDate" class="form-label">Tanggal Pendapatan</label>
                        <input type="date" class="form-control" id="editRevenueDate" name="tanggal" required>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger bg-opacity-10 border-danger">
                <h5 class="modal-title text-danger" id="deleteConfirmModalLabel">
                    <i class="bx bx-error-circle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Apakah Anda yakin ingin menghapus pendapatan <strong id="deleteItemName"></strong>?</p>
                <p class="text-muted small mt-2">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deletePendapatan()">
                    <i class="bx bx-trash me-2"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="loading-overlay d-none">
    <div class="loading-content">
        <div class="spinner"></div>
        <span class="text-dark">Memuat data...</span>
    </div>
</div>
@endsection
@section('page-script')
<script>
    let currentDeleteId = null;

    // Format input as Rupiah currency
    function formatRupiah(input) {
        let rawValue = input.value.replace(/\D/g, '');
        if (rawValue) {
            input.value = parseInt(rawValue).toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            });
        } else {
            input.value = '';
        }
        
        // Update the hidden raw value input
        const hiddenFieldId = input.id === 'revenueAmount' ? 'revenueAmountRaw' : 'editRevenueAmountRaw';
        document.getElementById(hiddenFieldId).value = rawValue;
    }
    
    // Edit Pendapatan Function
    function editPendapatan(id) {
        showLoading();
        
        fetch(`/api/pendapatan/${id}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Gagal memuat data');
                }
                
                // Fill form with data
                document.getElementById('editRevenueAmount').value = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(data.data.jumlah_pendapatan);
                
                document.getElementById('editRevenueAmountRaw').value = data.data.jumlah_pendapatan;
                document.getElementById('editRevenueType').value = data.data.jenis_pendapatan;
                document.getElementById('editPaymentMethod').value = data.data.metode_bayar;
                document.getElementById('editRevenueDescription').value = data.data.deskripsi || '';
                document.getElementById('editRevenueDate').value = data.data.tanggal;
                
                // Update form action
                document.getElementById('editRevenueForm').action = `/api/pendapatan/${id}`;
                
                hideLoading();
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editRevenueModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                showErrorNotification('Terjadi kesalahan saat memuat data pendapatan: ' + error.message);
            });
    }
    
    // Confirm Delete Function
    function confirmDeletePendapatan(id, jenisName) {
        currentDeleteId = id;
        document.getElementById('deleteItemName').textContent = jenisName;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }
    
    // Delete Pendapatan Function
    function deletePendapatan() {
        if (!currentDeleteId) return;
        
        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menghapus...';
        
        fetch(`/api/pendapatan/${currentDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Gagal menghapus data');
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
            modal.hide();
            
            // Show success message
            showSuccessNotification(data.message || 'Data pendapatan berhasil dihapus');
            
            // Reset delete id
            currentDeleteId = null;
            
            // Reload table after 1.5 seconds
            setTimeout(() => {
                applyFilters();
            }, 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
            showErrorNotification('Terjadi kesalahan saat menghapus data: ' + error.message);
        });
    }
    
    // Success Notification Function
    function showSuccessNotification(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            <i class="bx bx-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('main');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto dismiss after 4 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 4000);
    }

    // Error Notification Function
    function showErrorNotification(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            <i class="bx bx-error-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('main');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Add new functions for search
    function applyFilters() {
        showLoading();
        const search = document.getElementById('searchInput').value;
        const month = document.getElementById('monthFilter').value;

        // Create URL with parameters
        const params = new URLSearchParams({
            search: search,
            month: month
        });

        fetch(`/pendapatan/non-langganan/search?${params.toString()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('tableBody').innerHTML = data.html;
                hideLoading();
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                alert('Terjadi kesalahan saat memuat data');
            })
            .finally(() => {
                hideLoading();
            });
    }

    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('d-none');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('d-none');
    }

    function clearFilters() {
        document.getElementById('searchInput').value = ''; // Clear search input
        document.getElementById('monthFilter').value = ''; // Reset month filter to "Semua Bulan"
        applyFilters();
    }

    // Add debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Event listeners
    document.getElementById('searchInput').addEventListener('input', debounce(() => {
        applyFilters();
    }, 500));

    document.getElementById('monthFilter').addEventListener('change', () => {
        applyFilters();
    });

    // Prevent form submission on enter
    document.getElementById('filterForm').addEventListener('submit', (e) => {
        e.preventDefault();
        applyFilters();
    });

    // Prepare Edit Form Data Before Submit
    function prepareEditFormData(event) {
        event.preventDefault();
        
        // Get the raw value from hidden field
        const rawValue = document.getElementById('editRevenueAmountRaw').value;
        
        // Make sure hidden field has value
        if (!rawValue) {
            showErrorNotification('Jumlah pendapatan tidak valid');
            return;
        }
        
        // Use FormData to handle file uploads properly
        const form = document.getElementById('editRevenueForm');
        const formData = new FormData(form);
        
        // Replace the formatted field with raw value
        formData.set('jumlah_pendapatan', rawValue);
        
        // Get the action URL
        const action = form.action;
        
        // Show loading
        showLoading();
        
        // Submit using fetch
        fetch(action, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Gagal memperbarui data');
            }
            
            hideLoading();
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editRevenueModal'));
            modal.hide();
            
            // Show success message
            showSuccessNotification(data.message || 'Data pendapatan berhasil diperbarui');
            
            // Reload table after 1.5 seconds
            setTimeout(() => {
                applyFilters();
            }, 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
            showErrorNotification('Terjadi kesalahan saat memperbarui data: ' + error.message);
        });
    }
</script>
@endsection