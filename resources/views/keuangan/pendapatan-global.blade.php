@extends('layouts.contentNavbarLayout')

@section('title', 'Pendapatan Global')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
<style>
    .custom-card {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        margin: 20px 0;
        background: #fff;
        border: none;
    }
    .filter-section {
        padding: 20px;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }
    .year-filter {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        outline: none;
        min-width: 140px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .year-filter:focus {
        border-color: #63b3ed;
        box-shadow: 0 0 0 2px rgba(99, 179, 237, 0.2);
    }
    .search-input {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        outline: none;
        min-width: 200px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .search-input:focus {
        border-color: #63b3ed;
        box-shadow: 0 0 0 2px rgba(99, 179, 237, 0.2);
    }
    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 14px;
    }
    .custom-table th {
        background-color: black;
        font-weight: bold;
        text-align: center;
        padding: 16px;
        border-bottom: 2px solid #edf2f7;
        color: white;
        transition: background-color 0.2s;
        position: sticky;
        top: 0;
        z-index: 10;
        min-width: 120px;
    }
    .custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #edf2f7;
        color: #2d3748;
        transition: all 0.2s;
        text-align: center;;
    }
    .custom-table tbody tr:hover {
        background-color: #f7fafc;
    }
    .table-container {
        margin: 0;
        overflow-x: auto;
        border-radius: 0 0 12px 12px;
    }
    .text-right {
        text-align: right;
    }
    .nama-column {
        white-space: nowrap;
        min-width: 200px;
    }
    .amount-cell {
        font-family: monospace;
        font-size: 13px;
    }
    .hidden-row {
        display: none;
    }
    .modern-card-header {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
        padding: 25px 30px;
        border-bottom: 1px solid #edf2f7;
        margin-bottom: 10px;
    }
    
    .modern-card-header .card-title {
        color: #2d3748;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    
    .modern-card-header .card-text {
        color: #718096;
        font-size: 0.95rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .header-icon {
        color: #4299e1;
        margin-right: 5px;
    }
    
    .section-header {
        background: #f8fafc;
        padding: 20px 25px;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-header .title {
        color: #1a365d;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-header .badge {
        background: #ebf5ff;
        color: #2b6cb0;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .export-buttons {
        margin-left: auto;
        display: flex;
        gap: 10px;
    }
    
    .export-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }
    
    .export-btn.excel {
        background-color: #0d6d3c;
        color: white;
    }
    
    .export-btn.pdf {
        background-color: #dc2626;
        color: white;
    }
    
    .export-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    
    @media (max-width: 768px) {
        .custom-table {
            font-size: 13px;
        }
        .custom-table td, .custom-table th {
            padding: 12px 10px;
        }
        .filter-section {
            padding: 15px;
        }
        .modern-card-header {
            padding: 20px;
        }
        .export-buttons {
            width: 100%;
            justify-content: stretch;
        }
        .export-btn {
            flex: 1;
            justify-content: center;
        }
    }
</style>

<div class="modern-card-header">
    <h5 class="card-title">
        <i class="bx bx-line-chart header-icon"></i>
        Pendapatan Global Pertahun
    </h5>
    <p class="card-text">
        <i class='bx bx-info-circle header-icon'></i>
        Lihat pendapatan global berdasarkan tahun dan nama
    </p>
</div>

<div class="card custom-card">

    <div class="section-header rounded-top">
        <h5 class="title">
            <i class='bx bx-bar-chart-square'></i>
            Pendapatan Langganan Global
        </h5>
    </div>

    <div class="filter-section">
        <div class="form-group">
            <label for="yearFilter" class="mb-2">Filter by Tahun:</label>
            <select id="yearFilter" class="input-group year-filter">
                <option value="all">Semua Tahun</option>
                @for($year = date('Y'); $year >= 2020; $year--)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endfor
            </select>
        </div>

        <div class="form-group col-md-2">
            <label for="paketFilter" class="mb-2">Filter by Paket:</label>
            <select id="paketFilter" class="input-group year-filter">
                <option value="all">Semua Paket</option>
                @foreach($pakets as $paket)
                    @if($paket->nama_paket != 'ISOLIREBILLING')
                        <option value="{{ $paket->nama_paket }}">{{ $paket->nama_paket ?? 'Tidak Diketahui'}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-4">
            <label for="nameSearch" class="mb-2">Cari Nama or Alamat:</label>
            <input type="text" id="nameSearch" class="input-group search-input" placeholder="Search ...">
        </div>
        
        <div class="export-buttons">
            <button class="btn btn-primary btn-sm excel" onclick="exportToExcel()">
                <i class='bx bx-file me-2'></i>
                Export To Excel
            </button>
        </div>
    </div>
    
    <div class="table-container p-4">
        <table class="custom-table hover">
            <thead>
                <tr>
                    <th style="width: 60px">No</th>
                    <th class="nama-column">Nama</th>
                    <th class="alamat-column">Alamat</th>
                    <th class="paket">Paket</th>
                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                        <th>{{ $bulan }}</th>
                    @endforeach
                    <th>Total Per Tahun</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($formatted as $lang)
                    <tr data-year="{{ date('Y') }}" class="table-row">
                        <td>{{$no++}}</td>
                        <td class="nama-column">{{$lang['nama']}}</td>
                        <td class="alamat-column">{{$lang['alamat'] ?? 'Unknown'}}</td>
                        <td class="paket"><span class="badge bg-warning bg-opacity-50 text-dark">{{ $lang['paket'] }}</span></td>
                        @foreach ($lang['bulan'] as $jumlah)
                            <td class="text-right amount-cell">
                                Rp {{ number_format((float)$jumlah, 0, ',', '.') }}
                            </td>
                        @endforeach
                        <td class="text-right amount-cell bg-warning text-dark fw-bold">
                            Rp {{ number_format((float)$lang['total'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
                <tr class="table-row bg-warning bg-opacity-50 text-dark">
                    <td colspan="4" style="text-align: center;"><strong>Total Per Bulan:</strong></td>
                    @for ($i = 1; $i <= 12; $i++)
                        <td class="fw-bold">Rp {{ number_format($bulanTotals[$i] ?? 0, 0, ',', '.') }}</td>
                    @endfor
                    <td class="bg-dark text-white"><strong>Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-start">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-modern">
                @if($formatted->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class='bx bx-chevron-left'></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $formatted->previousPageUrl() }}" rel="prev">
                            <i class='bx bx-chevron-left'></i>
                        </a>
                    </li>
                @endif

                @foreach($formatted->getUrlRange(1, $formatted->lastPage()) as $page => $url)
                    <li class="page-item {{ $formatted->currentPage() == $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach

                @if($formatted->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $formatted->nextPageUrl() }}" rel="next">
                            <i class='bx bx-chevron-right'></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class='bx bx-chevron-right'></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
        <style>
            .pagination-modern {
                gap: 5px;
            }
            .pagination-modern .page-link {
                border-radius: 6px;
                padding: 8px 16px;
                color: #4a5568;
                border: 1px solid #e2e8f0;
                transition: all 0.2s;
            }
            .pagination-modern .page-item.active .page-link {
                background-color: #3182ce;
                border-color: #3182ce;
                color: white;
            }
            .pagination-modern .page-link:hover:not(.disabled) {
                background-color: #edf2f7;
                color: #2d3748;
                border-color: #cbd5e0;
            }
            .pagination-modern .page-item.disabled .page-link {
                background-color: #f7fafc;
                color: #a0aec0;
                border-color: #edf2f7;
            }
        </style>
    </div>
</div>

<div class="card custom-card">
    <div class="section-header rounded-top">
        <h5 class="title">
            <i class='bx bx-bar-chart-square'></i>
            Pendapatan Non Langganan Global
        </h5>
    </div>

    <div class="filter-section">
        <label for="yearFilter">Filter Tahun:</label>
        <select id="tahun" class="year-filter">
            <option value="all">Semua Tahun</option>
            @for($year = date('Y'); $year >= 2020; $year--)
                <option value="{{ $year }}">{{ $year }}</option>
            @endfor
        </select>
        <label for="nameSearch">Jenis Pendapatan:</label>
        <input type="text" id="nama" class="search-input" placeholder="Ketik Jenis Pendapatan ...">
        
        <div class="export-buttons">
            <button class="export-btn excel">
                <i class='bx bx-file'></i>
                Export Excel
            </button>
        </div>
    </div>

    <div class="table-container p-4 mb-5">
        <table class="custom-table hover">
            <thead>
                <tr>
                    <th style="width: 60px">No</th>
                    <th class="nama-column">Jenis Pendapatan</th>
                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                        <th>{{ $bulan }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($nonFormattedData as $lang)
                    <tr data-year="{{ date('Y') }}" class="table-rows">
                        <td>{{$no++}}</td>
                        <td class="nama-column">{{$lang['nama']}}</td>
                        @foreach ($lang['bulan'] as $jumlah)
                            <td class="text-right amount-cell">
                                Rp {{ number_format($jumlah, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
// Langganan Global Filter
function filterPaket() {
    const selectedPaket = document.getElementById('paketFilter').value;
    const rows = document.querySelectorAll('.table-row');
    
    rows.forEach(row => {
        const paket = row.querySelector('.paket:nth-child(4)').textContent; // Assuming paket is in the 4th column
        if (selectedPaket === 'all' || paket === selectedPaket) {
            row.classList.remove('hidden-row');
        } else {
            row.classList.add('hidden-row');
        }
    });
}
document.getElementById('paketFilter').addEventListener('change', filterPaket);

function calculateColumnTotals() {
    const visibleRows = Array.from(document.querySelectorAll('.table-row')).filter(row => 
        !row.classList.contains('hidden-row') && !row.classList.contains('bg-warning')
    );
    
    const totals = new Array(13).fill(0); // 12 months + total column
    
    visibleRows.forEach(row => {
        const cells = row.querySelectorAll('.amount-cell');
        cells.forEach((cell, index) => {
            const value = parseInt(cell.textContent.replace(/[^\d]/g, '')) || 0;
            totals[index] += value;
        });
    });
    
    return totals;
}

function updateTotalsRow(totals) {
    const totalsRow = document.querySelector('.table-row.bg-warning');
    const totalCells = totalsRow.querySelectorAll('td:not(:first-child)');
    
    totals.forEach((total, index) => {
        if (totalCells[index]) {
            totalCells[index].innerHTML = `<strong>Rp ${total.toLocaleString('id-ID')}</strong>`;
        }
    });
}

function filterTable() {
    const selectedYear = document.getElementById('yearFilter').value;
    const searchText = document.getElementById('nameSearch').value.toLowerCase();
    
    const rows = document.querySelectorAll('.table-row:not(.bg-warning)');
    
    rows.forEach(row => {
        const nama = row.querySelector('.nama-column').textContent.toLowerCase();
        const alamat = row.querySelector('.alamat-column').textContent.toLowerCase();
        const yearMatch = selectedYear === 'all' || row.dataset.year === selectedYear;
        const nameMatch = nama.includes(searchText) || alamat.includes(searchText);
        
        if (yearMatch && nameMatch) {
            row.classList.remove('hidden-row');
        } else {
            row.classList.add('hidden-row');
        }
    });

    const totals = calculateColumnTotals();
    updateTotalsRow(totals);
}

function exportToExcel() {
    const table = document.querySelector('.custom-table');
    const workbook = XLSX.utils.table_to_book(table, { sheet: 'Pendapatan Langganan Global' });
    XLSX.writeFile(workbook, 'Pendapatan_Langganan_Global.xlsx'); // Updated filename to match previous edit
}
document.querySelector('.export-btn.excel').addEventListener('click', exportToExcel); // Added event listener for Excel export

// Non Langganan Global Filter
function filterTableNonLang() {
    const selectedYear = document.getElementById('tahun').value;
    const searchText = document.getElementById('nama').value.toLowerCase();
    const rows = document.querySelectorAll('.table-rows');
    
    rows.forEach(row => {
        const nama = row.querySelector('.nama-column').textContent.toLowerCase();
        const yearMatch = selectedYear === 'all' || row.dataset.year === selectedYear;
        const nameMatch = nama.includes(searchText);
        
        if (yearMatch && nameMatch) {
            row.classList.remove('hidden-row');
        } else {
            row.classList.add('hidden-row');
        }
    });
}
document.getElementById('tahun').addEventListener('change', filterTableNonLang);
document.getElementById('nama').addEventListener('input', filterTableNonLang);

document.getElementById('yearFilter').addEventListener('change', filterTable);
document.getElementById('nameSearch').addEventListener('input', filterTable);
</script>
@endsection