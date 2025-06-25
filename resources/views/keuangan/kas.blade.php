@extends('layouts.contentNavbarLayout')

@section('title', 'Kas')

<style>
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.3) !important;
    }
    
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
    
    .card-body::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100%;
        background: rgba(255,255,255,0.1);
        transform: skewX(-15deg) translateX(50px);
    }
    
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
</style>

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-5">
            <div class="card-header border-bottom mb-5">
                <h4 class="card-title">Data Kas</h4>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-sm-4 mb-2">
                        <div class="card bg-danger text-white shadow rounded-3">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class='bx bx-wallet-alt fs-1 me-2'></i>
                                    <h5 class="card-title text-white mb-0 fw-bold">Saldo Kas</h5>
                                </div>
                                <p class="card-text display-6 fw-bold mb-2">Rp.{{ number_format(0 ?: $saldo, 0, ',', '.') }}</p>
                                <div class="d-flex align-items-center mt-3">
                                    <i class='bx bx-calendar fs-5 me-2'></i>
                                    <p class="card-text mb-0">Last Updated: {{ date('d M Y', strtotime($tanggal->tanggal_kas)) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <a href="/transaksi/kas-besar" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat Transaksi">
                            <div class="card bg-info text-white shadow rounded-3">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class='bx bx-wallet-alt fs-1 me-2'></i>
                                        <h5 class="card-title text-white mb-0 fw-bold">Kas Besar</h5>
                                    </div>
                                    <p class="card-text display-6 fw-bold mb-2">Rp.{{ number_format(0 ?: $jumlah, 0, ',', '.') }}</p>
                                    <div class="d-flex align-items-center mt-3">
                                        <i class='bx bx-calendar fs-5 me-2'></i>
                                        <p class="card-text mb-0">Last Updated: {{ date('d M Y', strtotime($tanggal->tanggal_kas)) }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <a href="/transaksi/kas-kecil" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat Transaksi">
                            <div class="card bg-warning text-white shadow rounded-3">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class='bx bx-wallet-alt fs-1 me-2'></i>
                                        <h5 class="card-title text-white mb-0 fw-bold">Kas Kecil</h5>
                                    </div>
                                    <p class="card-text display-6 fw-bold mb-2">Rp. {{number_format($totalKasKecil, 0, ',', '.')}}</p>
                                    <div class="d-flex align-items-center mt-3">
                                        <i class='bx bx-calendar fs-5 me-2'></i>
                                        <p class="card-text mb-0">Last Updated: {{ date('d M Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header border-bottom mb-5">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Data Transaksi</h4>
                    <button class="btn btn-outline-primary btn-sm" data-bs-target="#addTransactionModal" data-bs-toggle="modal">
                        <i class="bx bx-plus me-1"></i>
                        Tambah
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-2">
                        <label class="form-label mb-2 fw-bold">Tahun</label>
                        <select class="form-select mb-5" id="yearFilter">
                            @for ($year = date('Y'); $year >= 2020; $year--)
                            <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label mb-2 fw-bold">Bulan</label>
                        <select class="form-select mb-5" id="monthFilter">
                            @for ($month = 1; $month <= 12; $month++)
                            <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label mb-2 fw-bold">Tanggal</label>
                        <select class="form-select mb-5" id="dayFilter">
                            @for ($day = 1; $day <= 31; $day++)
                            <option value="{{ $day }}">{{ $day }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="table-responsive transaction-table">
                    <table class="table table-hover">
                        <thead class="text-center table-dark">
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Debit</th>
                                <th>Kredit</th>
                                <th>Admin</th>
                            </tr>
                        </thead>
                        <tbody class="text-center" style="font-size: 14px">
                            @forelse ($kas as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_kas)->translatedFormat('d F Y') }}</td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 text-dark">
                                        {{ $item->keterangan }}
                                    </span>
                                </td>
                                <td class="amount-cell debit-amount">{{ $item->debit ? 'Rp.' . number_format($item->debit, 0, ',', '.') : '-' }}</td>
                                <td class="amount-cell kredit-amount">{{ $item->kredit ? 'Rp.' . number_format($item->kredit, 0, ',', '.') : '-' }}</td>
                                <td>
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="badge bg-danger bg-opacity-10 text-danger mb-1">
                                            <strong>{{ strtoupper(optional($item->user)->name ?? '-')}} </strong>
                                        </span>
                                        <small class="text-muted">{{ $item->user->roles->name ?? '-'}}</small>
                                    </div>
                                </td>
                                <tr>
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
                        <div class="mt-5">
                            {{ $kas->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Transaction Modal -->
        <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="text-primary fs-4">
                            <i class="bx bx-plus me-1"></i>
                        </span>
                        <h5 class="modal-title" id="addTransactionModalLabel">Tambah Kas Kecil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <hr>
                    <form action="/tambah/kas/kecil" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="transactionDate" class="form-label"
                                >Tanggal Transaksi</label>
                                <input type="date" class="form-control" id="transactionDate" required name="tanggal">
                            </div>
                            <div class="mb-3">
                                <label for="transactionAmount" class="form-label">Jumlah Deposit</label>
                                <input type="text" class="form-control" id="transactionAmount" oninput="formatAndSync(this)" required placeholder="Masukkan jumlah deposit">
                                <input hidden type="text" class="form-control" id="generate" oninput="this.value = formatNumber($this.value)" name="jumlah">
                            </div>
                        </div>
                        <hr>
                        <div class="modal-footer gap-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });
            document.querySelectorAll('.btn-action').forEach(button => {
                button.addEventListener('click', function () {
                    const action = this.getAttribute('title').toLowerCase();
                    if (action === 'edit') {
                        // Handle edit action
                        alert('Edit action triggered');
                    } else if (action === 'delete') {
                        // Handle delete action
                        if (confirm('Are you sure you want to delete this transaction?')) {
                            alert('Transaction deleted');
                        }
                    }
                });
            });
            document.querySelector('.btn-primary').addEventListener('click', function () {
                const date = document.getElementById('transactionDate').value;
                const description = document.getElementById('transactionDescription').value;
                const amount = document.getElementById('transactionAmount').value;
                const type = document.getElementById('transactionType').value;
                const category = document.getElementById('transactionCategory').value;
                const notes = document.getElementById('transactionNotes').value;
                
                if (date && description && amount && type && category) {
                    alert(`Transaction added: ${description} - ${amount} (${type})`);
                    $('#addTransactionModal').modal('hide');
                } else {
                    alert('Please fill in all required fields.');
                }
            });
        </script>
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
        @endsection