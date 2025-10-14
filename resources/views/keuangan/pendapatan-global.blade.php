@extends('layouts.contentNavbarLayout')

@section('title', 'Pendapatan Global')

@section('content')
<!-- Tailwind CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<!-- Font Awesome CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

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
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        border-radius: 0.5rem;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f4f6;
        border-top: 4px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .table-wrapper {
        position: relative;
        min-height: 200px;
    }

    /* Custom scrollbar for table */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Hover effects */
    .card-hover:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Gradient backgrounds for cards */
    .gradient-subscription {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .gradient-non-subscription {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .gradient-expense {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .gradient-profit {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }
</style>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="card-header mb-8">
            <h1 class="card-title text-3xl font-bold text-gray-900">Pendapatan Global</h1>
            <p class="card-subtitle mt-2 text-sm text-gray-600">Dashboard lengkap untuk monitoring keuangan perusahaan</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Pendapatan Langganan -->
            <div class="bg-white rounded-2xl shadow-lg card-hover overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pendapatan Langganan</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2" id="totalSubscription">
                                {{ 'Rp ' . number_format($summaryData['totalSubscription'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="gradient-subscription rounded-xl p-3">
                            <i class="fas fa-wallet text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>Pendapatan dari Langganan</span>
                    </div>
                </div>
            </div>

            <!-- Total Pendapatan Non-Langganan -->
            <div class="bg-white rounded-2xl shadow-lg card-hover overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pendapatan Non-Langganan</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2" id="totalNonSubscription">
                                {{ 'Rp ' . number_format($summaryData['totalNonSubscription'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="gradient-non-subscription rounded-xl p-3">
                            <i class="fas fa-building text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-amber-600">
                        <i class="fas fa-chart-line mr-1"></i>
                        <span>Pendapatan dari non langganan</span>
                    </div>
                </div>
            </div>

            <!-- Total Pengeluaran -->
            <div class="bg-white rounded-2xl shadow-lg card-hover overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pengeluaran</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ 'Rp ' . number_format($pengeluaran, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="gradient-expense rounded-xl p-3">
                            <i class="bx bx-trending-down text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-red-600">
                        <i class="fas fa-arrow-down mr-1"></i>
                        <span>Pengeluaran Perusahaan</span>
                    </div>
                </div>
            </div>

            <!-- Total Laba/Rugi -->
            <div class="bg-white rounded-2xl shadow-lg card-hover overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Laba/Rugi</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2" id="totalProfitLoss">
                                {{ 'Rp ' . number_format($summaryData['totalProfitLoss'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="gradient-profit rounded-xl p-3">
                            <i class="bx bx-trending-up text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-blue-600">
                        <i class="fas fa-calculator mr-1"></i>
                        <span>Profit/Loss Perusahaan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Export Card -->
        <div class="bg-white rounded-2xl shadow-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Filter & Export</h3>
            </div>
            <div class="p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex-1">
                        <label for="globalYearFilter" class="block text-sm font-medium text-gray-700 mb-2">
                            Filter Tahun
                        </label>
                        <select id="globalYearFilter" class="w-full lg:w-64 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                            <option value="all">Semua Tahun</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="exportToExcel()" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                            <i class="fas fa-file-excel mr-2"></i>
                            Export Excel
                        </button>
                        <button onclick="exportToPDF()" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Export PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white rounded-2xl shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Laporan Keuangan Bulanan</h3>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800" id="yearBadge">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Tahun {{ $selectedYear }}
                </span>
            </div>
            
            <div class="table-wrapper">
                <div class="loading-overlay" id="loadingOverlay" style="display: none;">
                    <div class="loading-spinner"></div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="mainTable">
                        <thead class="bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs fw-bold text-white uppercase tracking-wider bg-gray-900">
                                    <i class="bx bx-checkbox-checked fs-4 me-1"></i>Kategori
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Jan
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Feb
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Mar
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Apr
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Mei
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Jun
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Jul
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Agu
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Sep
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Okt
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Nov
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                    Des
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider bg-gray-900">
                                    Total Tahun
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="mainTableBody">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
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

// Get color class based on amount
function getAmountColor(amount) {
    if (amount > 0) return 'text-green-600 font-semibold';
    if (amount < 0) return 'text-red-600 font-semibold';
    return 'text-gray-500';
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
            data: currentFinancialData.subscription,
            icon: 'bx bxs-wallet fs-5',
            color: 'text-green-600'
        },
        {
            name: 'Pendapatan Non-Langganan',
            data: currentFinancialData.nonSubscription,
            icon: 'bx bx-building fs-5',
            color: 'text-amber-600'
        },
        {
            name: 'Total Pendapatan',
            data: currentFinancialData.totalRevenue,
            icon: 'bx bx-trending-up fs-5',
            color: 'text-green-600'
        },
        {
            name: 'Pengeluaran',
            data: currentFinancialData.operationalCost,
            icon: 'bx bx-trending-down fs-5',
            color: 'text-red-600'
        },
        {
            name: 'Laba/Rugi Bersih',
            data: currentFinancialData.profitLoss,
            icon: 'bx bxs-chart fs-5',
            color: 'text-purple-600'
        }
    ];
    
    categories.forEach(category => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 transition-colors';
        
        // Category name cell
        const categoryCell = document.createElement('td');
        categoryCell.className = 'px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900';
        categoryCell.innerHTML = `
            <div class="flex items-center">
                <i class="${category.icon} ${category.color} mr-2 text-sm"></i>
                ${category.name}
            </div>
        `;
        row.appendChild(categoryCell);
        
        // Monthly data cells
        category.data.forEach(amount => {
            const cell = document.createElement('td');
            cell.className = `px-3 py-3 whitespace-nowrap text-sm text-center ${getAmountColor(amount)}`;
            cell.textContent = formatCurrency(amount);
            row.appendChild(cell);
        });
        
        // Total cell
        const totalCell = document.createElement('td');
        totalCell.className = 'px-4 py-3 whitespace-nowrap text-sm font-bold text-center bg-gray-800 text-white';
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
            document.getElementById('yearBadge').innerHTML = `
                <i class="fas fa-calendar-alt mr-2"></i>
                Tahun ${currentSelectedYear}
            `;
        } else {
            console.error('Error loading data:', data.message);
            showNotification('Gagal memuat data. Silakan coba lagi.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat memuat data.', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
        type === 'error' ? 'bg-red-500 text-white' : 
        type === 'success' ? 'bg-green-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Export functions
function exportToExcel() {
    const table = document.getElementById('mainTable');
    const workbook = XLSX.utils.table_to_book(table, { sheet: 'Laporan Keuangan Global' });
    XLSX.writeFile(workbook, `Laporan_Keuangan_Global_${currentSelectedYear}.xlsx`);
    showNotification('File Excel berhasil diunduh!', 'success');
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
            fillColor: [75, 85, 99],
            textColor: [255, 255, 255],
            fontSize: 8,
            fontStyle: 'bold'
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
    showNotification('File PDF berhasil diunduh!', 'success');
}

// Event listeners
document.getElementById('globalYearFilter').addEventListener('change', function() {
    const selectedYear = this.value;
    if (selectedYear !== 'all') {
        loadDataForYear(selectedYear);
    } else {
        showNotification('Fitur semua tahun akan segera tersedia!', 'info');
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTable();
    updateSummaryCards();
});
</script>

@endsection