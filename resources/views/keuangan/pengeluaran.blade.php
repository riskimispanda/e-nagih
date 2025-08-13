@extends('layouts.contentNavbarLayout')

@section('title', 'Pengeluaran Global')
<style>
    /* Modal Responsive Styles */
    .modal-content {
        border: none;
        box-shadow: 0 0.25rem 1.5rem rgba(0, 0, 0, 0.15);
        border-radius: 0.75rem;
        overflow: hidden;
        animation: modalFadeIn 0.3s ease;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .modal-dialog {
        margin: 1rem auto;
        transition: all 0.3s ease;
    }
    
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }
    
    .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    
    .modal-footer {
        background-color: #f8f9fa;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1rem 1.5rem;
    }
    
    /* Mobile Responsive */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }
        
        .modal-content {
            border-radius: 0.5rem;
        }
        
        .modal-header,
        .modal-footer {
            padding: 1rem;
        }
        
        .modal-body {
            padding: 1rem;
            max-height: calc(100vh - 150px);
        }
        
        .modal-title {
            font-size: 1.1rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    }
    
    /* Form Enhancements */
    .form-label {
        color: #566a7f;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .form-control,
    .form-select {
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        padding: 0.625rem 0.875rem;
        font-size: 0.9375rem;
        transition: all 0.2s ease;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }
    
    .input-group-text {
        background-color: #f5f5f9;
        border: 1px solid #d9dee3;
        color: #566a7f;
        font-weight: 500;
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Button Styles */
    .btn-primary {
        background-color: #696cff;
        border-color: #696cff;
        box-shadow: 0 0.125rem 0.25rem rgba(105, 108, 255, 0.4);
    }
    
    .btn-primary:hover {
        background-color: #5f61e6;
        border-color: #5f61e6;
        transform: translateY(-1px);
        box-shadow: 0 0.25rem 0.5rem rgba(105, 108, 255, 0.4);
    }
    
    .btn-outline-secondary {
        color: #8592a3;
        border-color: #8592a3;
    }
    
    .btn-outline-secondary:hover {
        background-color: #8592a3;
        border-color: #8592a3;
        color: #fff;
    }
    
    /* Scrollbar Styling */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .modal-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            
            <!-- Card Header -->
            <div class="card-header border-bottom mb-4">
                <h4 class="card-title fw-bold">Data Pengeluaran</h4>
                <small class="card-subtitle text-muted">Kelola dan pantau data pengeluaran perusahaan</small>
            </div>
            
            <div class="card-body">
                
                <!-- Filter -->
                <div class="row mb-4 g-3">
                    <div class="col-md-3">
                        <label for="startDate" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="startDate">
                    </div>
                    <div class="col-md-3">
                        <label for="endDate" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="endDate">
                    </div>
                    <div class="col-md-3">
                        <label for="kategoriFilter" class="form-label">Kategori</label>
                        <select class="form-select" id="kategoriFilter">
                            <option value="">Semua Kategori</option>
                            <option value="operasional">Operasional</option>
                            <option value="gaji">Gaji</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="searchInput" class="form-label">Pencarian</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari...">
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="row mb-5 g-3">

                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 bg-info hover-shadow" style="transition: all 0.3s ease;">
                            <div class="card-body p-3 p-sm-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-info bg-opacity-25 rounded-2 p-2">
                                        <i class="bx bx-money fs-3 text-white"></i>
                                    </div>
                                    <h6 class="card-title mb-0 text-white fw-bold ms-2">Total Saldo</h6>
                                </div>
                                <h3 class="mb-1 text-white fw-bold" id="totalPengeluaran">
                                    Rp {{ number_format($total, 0, ',', '.') }}
                                </h3>
                                <small class="text-white text-opacity-85">Seluruh saldo Pendapatan</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 bg-primary hover-shadow" style="transition: all 0.3s ease;">
                            <div class="card-body p-3 p-sm-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-primary bg-opacity-25 rounded-2 p-2">
                                        <i class="bx bx-wallet fs-3 text-white"></i>
                                    </div>
                                    <h6 class="card-title mb-0 text-white fw-bold ms-2">Total Pengeluaran</h6>
                                </div>
                                <h3 class="mb-1 text-white fw-bold" id="totalPengeluaran">
                                    Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                                </h3>
                                <small class="text-white text-opacity-85">Seluruh pengeluaran</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 bg-success hover-shadow" style="transition: all 0.3s ease;">
                            <div class="card-body p-3 p-sm-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-success bg-opacity-25 rounded-2 p-2">
                                        <i class="bx bx-calendar fs-3 text-white"></i>
                                    </div>
                                    <h6 class="card-title mb-0 text-white fw-bold ms-2">Hari Ini</h6>
                                </div>
                                <h3 class="mb-1 text-white fw-bold" id="totalMasuk">
                                    Rp {{ number_format($dailyPengeluaran, 0, ',', '.') }}
                                </h3>
                                <small class="text-white text-opacity-85">Pengeluaran hari ini</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 bg-warning hover-shadow" style="transition: all 0.3s ease;">
                            <div class="card-body p-3 p-sm-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-warning bg-opacity-25 rounded-2 p-2">
                                        <i class="bx bx-line-chart fs-3 text-white"></i>
                                    </div>
                                    <h6 class="card-title mb-0 text-white fw-bold ms-2">Bulan Ini</h6>
                                </div>
                                <h3 class="mb-1 text-white fw-bold" id="totalPengeluaranBersih">
                                    Rp {{ number_format($mounthlyPengeluaran, 0, ',', '.') }}
                                </h3>
                                <small class="text-white text-opacity-85">Pengeluaran bulan ini</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="/request/hapus/pengeluaran" data-bs-toggle="tooltip" title="Request Konfirmasi" data-bs-placement="bottom"> 
                            <div class="card shadow-sm border-0 bg-danger hover-shadow" style="transition: all 0.3s ease;">
                                <div class="card-body p-3 p-sm-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-md bg-danger bg-opacity-25 rounded-2 p-2">
                                            <i class="bx bx-line-chart fs-3 text-white"></i>
                                        </div>
                                        <h6 class="card-title mb-0 text-white fw-bold ms-2">Request Konfirmasi</h6>
                                    </div>
                                    <h3 class="mb-1 text-white fw-bold" id="totalPengeluaranBersih">
                                        {{ $totalRequest }}
                                    </h3>
                                    <small class="text-white text-opacity-85">Total Request Hapus Pengeluaran</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <hr>
                
                <!-- Table -->
                <div class="d-flex justify-content-start mb-4">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalScrollable">
                                    <i class="bx bx-plus"></i>Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="pengeluaranTable">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jenis Pengeluaran</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Admin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengeluarans as $key => $pengeluaran)
                            <tr class="text-center">
                                <td>{{ $key + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($pengeluaran->tanggal_pengeluaran)->format('d-M-Y') }}</td>
                                <td>{{ $pengeluaran->jenis_pengeluaran }}</td>
                                <td>{{ $pengeluaran->keterangan }}</td>
                                <td data-amount="{{ $pengeluaran->jumlah_pengeluaran }}">
                                    Rp {{ number_format($pengeluaran->jumlah_pengeluaran, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if ($pengeluaran->status_id == 1)
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Menunggu Konfirmasi Penghapusan</span>
                                    @elseif ($pengeluaran->status_id == 2)
                                    <span class="badge bg-success bg-opacity-10 text-success">Approved</span>
                                    @elseif ($pengeluaran->status_id == 3)
                                    <span class="badge bg-success bg-opacity-10 text-success">Berhasil</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        {{ $pengeluaran->user->name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="#">
                                            <button class="btn btn-outline-warning btn-sm mb-1" title="Edit" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#deletePengeluaran{{ $pengeluaran->id }}" title="Hapus" data-bs-placement="bottom">
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
                                        <p class="text-muted mb-0">Belum ada Transaksi</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div> <!-- card-body -->
        </div> <!-- card -->
    </div> <!-- col -->
</div> <!-- row -->

{{-- Modal Add Pengeluaran --}}
<div class="modal fade" id="modalScrollable" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title" id="modalScrollableTitle">Tambah Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/pengeluaran/tambah" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body border-bottom border-top mt-2 mb-2">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label class="form-label">Rencana Anggaran Biaya</label>
                            <select name="rab_id" id="select-rab" class="form-select">
                                <option value="">Pilih RAB</option>
                                @foreach ($rab as $item)
                                <option value="{{ $item->id }}" data-anggaran="{{ $item->jumlah_anggaran }}">
                                    {{ $item->kegiatan }}
                                    {{ $item->item ? "({$item->item} item" : '' }}
                                    {{ $item->item && $item->keterangan ? ' | ' : '' }}
                                    {{ $item->keterangan ? "Ket: {$item->keterangan}" : '' }}
                                    {{ $item->item ? ')' : '' }}
                                    {{ $item->tahun_anggaran ? " | Tahun: {$item->tahun_anggaran}" : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label for="kasSelect" class="form-label fw-medium">Jenis Kas</label>
                            <select name="kas_id" id="kasSelect" class="form-select">
                                <option value="" selected disabled>Pilih Jenis Kas</option>
                                @foreach ($kas as $item)
                                <option value="{{ $item->id }}">{{$item->jenis_kas}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-4" id="jumlah-item-group" style="display: none;">
                                <label class="form-label fw-medium mb-2">
                                    <i class="bx bx-cart me-1"></i>Jumlah Item
                                </label>
                                <input type="number" name="item" class="form-control" placeholder="100">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-4" id="anggaran-info" style="display: none;">
                                <label class="form-label fw-medium mb-2">
                                    <i class="bx bx-money me-1"></i>Anggaran RAB
                                </label>
                                <input type="text" id="anggaran-amount" class="form-control" value="Rp 0" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label for="tanggalPengeluaran" class="form-label fw-medium">Tanggal</label>
                            <input type="date" class="form-control" id="tanggalPengeluaran" required name="tanggalPengeluaran">
                        </div>
                        <div class="col-sm-6">
                            <label for="jenisPengeluaran" class="form-label fw-medium">Jenis Pengeluaran</label>
                            <input name="jenisPengeluaran" type="text" class="form-control" id="jenisPengeluaran" placeholder="Contoh: Operasional, Gaji, Lainnya" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label for="keterangan" class="form-label fw-medium">Keterangan</label>
                            <textarea name="keterangan" class="form-control" id="keterangan" rows="3" placeholder="Masukkan keterangan pengeluaran..." required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label for="jumlahPengeluaran" class="form-label fw-medium">Jumlah</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" required placeholder="Masukkan jumlah pengeluaran" id="jumlahPengeluaran" oninput="formatRupiah(this)">
                            </div>
                            <input name="jumlahPengeluaran" type="text" class="form-control mt-1" id="jumlahPengeluaranNumeric" readonly placeholder="0" hidden>
                        </div>
                        <div class="col-sm-6">
                            <label for="metodePengeluaran" class="form-label fw-medium">Metode Pengeluaran</label>
                            <select class="form-select" id="metodePengeluaran" required name="metodePengeluaran">
                                <option selected disabled>Pilih Metode</option>
                                @foreach ($metodes as $metode)
                                <option value="{{ $metode->nama_metode }}">{{ $metode->nama_metode }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label for="buktiPengeluaran" class="form-label fw-medium">Bukti Pengeluaran</label>
                            <input name="buktiPengeluaran" type="file" class="form-control" id="buktiPengeluaran" required accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                Format file: JPG, PNG, PDF. Maksimal ukuran 2MB.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
{{-- Modal Request Hapus Pengeluaran --}}
@foreach ($pengeluarans as $pengeluaran)
<div class="modal fade" id="deletePengeluaran{{ $pengeluaran->id }}" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <input type="text" hidden value="{{ $pengeluaran->id }}">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title" id="modalScrollableTitle"><i class="bx bx-trash me-1 text-danger"></i>Hapus Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/pengeluaran/hapus/{{ $pengeluaran->id }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body border-bottom border-top mt-2 mb-2">
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <label class="form-label fw-medium">Alasan<span class="text-danger">*</span></label>
                            <textarea name="alasan" class="form-control" id="alasan" rows="3" placeholder="Masukkan alasan ingin menghapus pengeluaran..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer gap-2 mt-6">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm">Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach


<script>
    // Function to format currency and update numeric input
    function formatRupiah(input) {
        // Remove non-digit characters
        let value = input.value.replace(/\D/g, '');

        // Update numeric input
        const numericInput = document.getElementById('jumlahPengeluaranNumeric');
        if (numericInput) {
            numericInput.value = value; // Update numeric input with raw number
        }

        // Convert to number and format for display
        if (value !== '') {
            value = parseInt(value);
            input.value = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
        } else {
            if (numericInput) {
                numericInput.value = ''; // Clear numeric input if empty
            }
        }
    }
    
    
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize sorting
        document.querySelectorAll('th').forEach(header => {
            header.addEventListener('click', () => {
                const table = header.closest('table');
                const index = Array.from(header.parentElement.children).indexOf(header);
                sortTable(table, index);
            });
        });
        
        // Calculate total
        function calculateTotal() {
            const amounts = Array.from(document.querySelectorAll('td[data-amount]'))
            .map(td => parseFloat(td.dataset.amount) || 0);
            const total = amounts.reduce((sum, amount) => sum + amount, 0);
            document.getElementById('totalPengeluaran').textContent = 
            `Rp ${total.toLocaleString('id-ID')}`;
        }
        
        // Search and filter functionality
        const searchInput = document.getElementById('searchInput');
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        const kategoriFilter = document.getElementById('kategoriFilter');
        
        [searchInput, startDate, endDate, kategoriFilter].forEach(element => {
            element.addEventListener('change', filterTable);
        });
        
        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const start = startDate.value ? new Date(startDate.value) : null;
            const end = endDate.value ? new Date(endDate.value) : null;
            const kategori = kategoriFilter.value;
            
            const rows = document.querySelectorAll('#pengeluaranTable tbody tr');
            rows.forEach(row => {
                const dateCell = row.cells[0].textContent;
                const jenisCell = row.cells[1].textContent.toLowerCase();
                const keteranganCell = row.cells[2].textContent.toLowerCase();
                const amountCell = row.cells[3].textContent.replace(/[^0-9]/g, '');
                
                const date = new Date(dateCell);
                const isDateInRange = (!start || date >= start) && (!end || date <= end);
                const isKategoriMatch = !kategori || jenisCell.includes(kategori);
                const isSearchMatch = jenisCell.includes(searchValue) || 
                keteranganCell.includes(searchValue) ||
                amountCell.includes(searchValue);
                
                if (isDateInRange && isKategoriMatch && isSearchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            calculateTotal();
        }
    });
</script>

@section('page-script')
<script>
    $(document).ready(function() {
        console.log('Pengeluaran modal script loaded');

        // Additional event listener for jumlah pengeluaran to ensure numeric field is updated
        $('#jumlahPengeluaran').on('input', function() {
            const numericInput = $('#jumlahPengeluaranNumeric');
            let value = this.value.replace(/[^0-9]/g, ''); // Remove non-numeric characters

            console.log('Input value:', this.value);
            console.log('Numeric value:', value);

            if (numericInput.length) {
                numericInput.val(value);
                console.log('Numeric input updated:', numericInput.val());
            }
        });

        // RAB Selection Handler
        $('#select-rab').on('change', function () {
            console.log('RAB selection changed');
            const selectedOption = $(this).find('option:selected');
            const anggaranValue = selectedOption.data('anggaran');

            console.log('Selected RAB ID:', $(this).val());
            console.log('Anggaran value:', anggaranValue);

            if ($(this).val()) {
                // Tampilkan form jumlah item dan anggaran
                $('#jumlah-item-group').slideDown(300);
                $('#anggaran-info').slideDown(300);

                // Format dan tampilkan jumlah anggaran
                if (anggaranValue && anggaranValue > 0) {
                    const formattedAnggaran = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(anggaranValue);

                    $('#anggaran-amount').val(formattedAnggaran);
                    console.log('Anggaran displayed:', formattedAnggaran);
                } else {
                    $('#anggaran-amount').val('Rp 0');
                    console.log('No anggaran value found');
                }
            } else {
                // Sembunyikan form dan info anggaran
                $('#jumlah-item-group').slideUp(300);
                $('#anggaran-info').slideUp(300);
                $('#anggaran-amount').val('Rp 0');
                console.log('RAB selection cleared');
            }
        });

        // Reset form when modal is closed
        $('#modalScrollable').on('hidden.bs.modal', function () {
            $('#select-rab').val('').trigger('change');
            $('#anggaran-info').hide();
            $('#jumlah-item-group').hide();
            $('#anggaran-amount').val('Rp 0');
            console.log('Modal closed, form reset');
        });
    });
</script>
@endsection
