@extends('layouts.contentNavbarLayout')

@section('title', 'Pendapatan Global')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

<style>
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        border-radius: 0.375rem;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f4f6;
        border-top: 4px solid #696cff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .table-wrapper {
        position: relative;
    }

    /* Amount cell styling */
    .amount-cell {
        font-family: 'Inter', sans-serif;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    .summary-cards {
        margin-bottom: 1.5rem;
    }

    /* Clean table styling */
    .category-cell {
        font-weight: 600;
    }

    .total-cell {
        font-weight: 700;
        background-color: #343a40 !important;
        color: white !important;
    }

    /* Dark header for total column */
    .total-header {
        background-color: #212529 !important;
        color: white !important;
    }
</style>

<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Dashboard /</span> Pendapatan Global
</h4>

<!-- Summary Cards -->
<div class="row summary-cards">
    <div class="col-lg-4 col-md-6 col-12 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar bg-label-success rounded d-flex justify-content-center align-items-center" style="width:50px; height:50px;">
                        <i class="bx bx-trending-up fs-4 bx-sm text-success"></i>
                    </div>                    
                </div>
                <span class="fw-medium d-block mb-1">Total Pendapatan Langganan</span>
                <h5 class="card-title fw-bold mb-2" id="totalSubscription">{{ 'Rp ' . number_format($summaryData['totalSubscription'], 0, ',', '.') }}</h5>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-12 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar bg-label-danger rounded d-flex justify-content-center align-items-center" style="width:50px; height:50px;">
                        <i class="bx bx-dollar fs-4 bx-sm text-danger"></i>
                    </div>
                </div>
                <span class="fw-medium d-block mb-1">Total Pendapatan Non-Langganan</span>
                <h5 class="card-title fw-bold mb-2" id="totalNonSubscription">{{ 'Rp ' . number_format($summaryData['totalNonSubscription'], 0, ',', '.') }}</h5>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-12 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar bg-label-info rounded d-flex justify-content-center align-items-center" style="width:50px; height:50px;">
                        <i class="bx bx-calculator fs-4 bx-sm text-info"></i>
                    </div>
                </div>
                <span class="fw-medium d-block mb-1">Total Laba/Rugi</span>
                <h5 class="card-title fw-bold mb-2" id="totalProfitLoss">{{ 'Rp ' . number_format($summaryData['totalProfitLoss'], 0, ',', '.') }}</h5>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Export -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Filter & Export</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="globalYearFilter" class="form-label">Filter Tahun</label>
                <select id="globalYearFilter" class="form-select">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                    <option value="all">Semua Tahun</option>
                </select>
            </div>
            <div class="col-md-8 d-flex align-items-end mb-3">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success btn-sm" onclick="exportToExcel()">
                        <i class="bx bx-file me-1"></i>Export Excel
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="exportToPDF()">
                        <i class="bx bx-file-pdf me-1"></i>Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Laporan Keuangan Bulanan</h5>
        <small class="badge bg-label-info" id="yearBadge">Tahun {{ $selectedYear }}</small>
    </div>
    
    <div class="table-wrapper">
        <div class="loading-overlay" id="loadingOverlay" style="display: none;">
            <div class="loading-spinner"></div>
        </div>
        
        <div class="table-responsive text-nowrap">
            <table class="table table-hover" id="mainTable">
                <thead class="table-dark text-center fw-bold">
                    <tr class="fw-bold">
                        <th class="fw-bold">Kategori</th>
                        <th class="fw-bold text-center">Jan</th>
                        <th class="fw-bold text-center">Feb</th>
                        <th class="fw-bold text-center">Mar</th>
                        <th class="fw-bold text-center">Apr</th>
                        <th class="fw-bold text-center">Mei</th>
                        <th class="fw-bold text-center">Jun</th>
                        <th class="fw-bold text-center">Jul</th>
                        <th class="fw-bold text-center">Agu</th>
                        <th class="fw-bold text-center">Sep</th>
                        <th class="fw-bold text-center">Okt</th>
                        <th class="fw-bold text-center">Nov</th>
                        <th class="fw-bold text-center">Des</th>
                        <th class="fw-bold text-center total-header">Total Tahun</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0" id="mainTableBody">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Data from controller
let currentFinancialData = @json($financialData);
let currentSummaryData = @json($summaryData);
let currentSelectedYear = {{ $selectedYear }};

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

// Format currency
function formatCurrency(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

// Update table with current data
function updateTable() {
    const tbody = document.getElementById('mainTableBody');
    
    // Clear existing rows
    tbody.innerHTML = '';
    
    // Create rows for each category
    const categories = [
        {
            name: 'Pendapatan Langganan',
            data: currentFinancialData.subscription
        },
        {
            name: 'Pendapatan Non-Langganan',
            data: currentFinancialData.nonSubscription
        },
        {
            name: 'Total Pendapatan',
            data: currentFinancialData.totalRevenue
        },
        {
            name: 'Pengeluaran',
            data: currentFinancialData.operationalCost
        },
        {
            name: 'Laba/Rugi Bersih',
            data: currentFinancialData.profitLoss
        }
    ];
    
    categories.forEach(category => {
        const row = document.createElement('tr');
        
        // Category name cell
        const categoryCell = document.createElement('td');
        categoryCell.className = 'category-cell bg-label-warning text-dark text-center';
        categoryCell.textContent = category.name;
        row.appendChild(categoryCell);
        
        // Monthly data cells
        category.data.forEach(amount => {
            const cell = document.createElement('td');
            cell.className = 'amount-cell text-center';
            cell.textContent = formatCurrency(amount);
            row.appendChild(cell);
        });
        
        // Total cell with dark background
        const totalCell = document.createElement('td');
        totalCell.className = 'amount-cell text-center total-cell';
        const total = category.data.reduce((sum, amount) => sum + amount, 0);
        totalCell.textContent = formatCurrency(total);
        row.appendChild(totalCell);
        
        tbody.appendChild(row);
    });
}

// Update summary cards
function updateSummaryCards() {
    document.getElementById('totalSubscription').textContent = formatCurrency(currentSummaryData.totalSubscription);
    document.getElementById('totalNonSubscription').textContent = formatCurrency(currentSummaryData.totalNonSubscription);
    document.getElementById('totalProfitLoss').textContent = formatCurrency(currentSummaryData.totalProfitLoss);
}

// Show loading overlay
function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

// Hide loading overlay
function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

// Load data for selected year
function loadDataForYear(year) {
    if (year === currentSelectedYear.toString()) {
        return; // No need to reload if same year
    }
    
    showLoading();
    
    // Make AJAX request to get data for selected year
    fetch(`{{ route('keuangan.getGlobalPendapatanData') }}?year=${year}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentFinancialData = data.data.financialData;
            currentSummaryData = data.data.summaryData;
            currentSelectedYear = data.data.selectedYear;
            
            updateTable();
            updateSummaryCards();
            
            // Update year badge
            document.getElementById('yearBadge').textContent = `Tahun ${currentSelectedYear}`;
        } else {
            console.error('Error loading data:', data.message);
            // Use Sneat's toast notification if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal memuat data. Silakan coba lagi.',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            } else {
                alert('Gagal memuat data. Silakan coba lagi.');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat memuat data.',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        } else {
            alert('Terjadi kesalahan saat memuat data.');
        }
    })
    .finally(() => {
        hideLoading();
    });
}

// Export functions
function exportToExcel() {
    const table = document.getElementById('mainTable');
    const workbook = XLSX.utils.table_to_book(table, { sheet: 'Laporan Keuangan Global' });
    XLSX.writeFile(workbook, `Laporan_Keuangan_Global_${currentSelectedYear}.xlsx`);
    
    // Show success message
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'File Excel berhasil diunduh.',
            timer: 2000,
            showConfirmButton: false,
            topLayer: true
        });
    }
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    
    doc.setFontSize(16);
    doc.text(`Laporan Keuangan Global - Tahun ${currentSelectedYear}`, 14, 22);
    
    doc.autoTable({
        html: '#mainTable',
        startY: 30,
        theme: 'grid',
        headStyles: { 
            fillColor: [105, 108, 255],
            textColor: [255, 255, 255],
            fontSize: 8
        },
        bodyStyles: {
            fontSize: 7
        },
        columnStyles: {
            0: { cellWidth: 40 },
            1: { cellWidth: 18 },
            2: { cellWidth: 18 },
            3: { cellWidth: 18 },
            4: { cellWidth: 18 },
            5: { cellWidth: 18 },
            6: { cellWidth: 18 },
            7: { cellWidth: 18 },
            8: { cellWidth: 18 },
            9: { cellWidth: 18 },
            10: { cellWidth: 18 },
            11: { cellWidth: 18 },
            12: { cellWidth: 18 },
            13: { cellWidth: 25 }
        }
    });
    
    doc.save(`Laporan_Keuangan_Global_${currentSelectedYear}.pdf`);
    
    // Show success message
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'File PDF berhasil diunduh.',
            timer: 2000,
            showConfirmButton: false,
            topLayer: true
        });
    }
}

// Event listeners
document.getElementById('globalYearFilter').addEventListener('change', function() {
    const selectedYear = this.value;
    if (selectedYear !== 'all') {
        loadDataForYear(selectedYear);
    } else {
        // Handle "all years" case if needed
        console.log('All years selected - implement if needed');
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTable();
    updateSummaryCards();
});
</script>

@endsection