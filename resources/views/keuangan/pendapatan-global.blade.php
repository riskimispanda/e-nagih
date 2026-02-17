@extends('layouts.contentNavbarLayout')

@section('title', 'Pendapatan Global')

@section('content')
  <!-- Tailwind CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <!-- Font Awesome CDN -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

  <style>
    body {
      font-family: 'Inter', sans-serif;
    }

    .loading-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(2px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 50;
      border-radius: 0.75rem;
    }

    .loading-spinner {
      width: 30px;
      height: 30px;
      border: 3px solid #e5e7eb;
      border-top: 3px solid #3b82f6;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    /* Minimalist Scrollbar */
    .overflow-x-auto::-webkit-scrollbar {
      height: 6px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
      background: transparent;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
      background: #e5e7eb;
      border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
      background: #d1d5db;
    }
  </style>

  <div class="min-h-screen bg-gray-50 font-sans">

    <!-- Colored Header Section -->
    <div class="bg-blue-500 pb-24 pt-8 px-4 sm:px-6 lg:px-8 shadow-md">
      <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-white tracking-tight">Pendapatan Global</h1>
          <p class="mt-2 text-blue-100 text-sm">Monitoring keuangan komprehensif & real-time</p>
        </div>
        <div class="flex items-center space-x-3">
          <span
            class="inline-flex items-center px-4 py-2 bg-indigo-500/50 backdrop-blur-sm border border-indigo-400 rounded-full text-sm font-medium text-white shadow-sm"
            id="yearBadge">
            <i class="far fa-calendar-alt mr-2 text-indigo-100"></i>
            Tahun {{ $selectedYear }}
          </span>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16">

      <!-- Metric Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Pendapatan Langganan -->
        <div onclick="openStatsModal()"
          class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-emerald-500 transform hover:-translate-y-1 transition-all duration-300 cursor-pointer">
          <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-emerald-50 rounded-lg">
              <i class="fas fa-wallet text-emerald-600 text-xl"></i>
            </div>
          </div>
          <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Pendapatan Langganan</h3>
            <p class="text-2xl font-bold text-gray-800 tracking-tight" id="totalSubscription">
              {{ 'Rp ' . number_format($summaryData['totalSubscription'], 0, ',', '.') }}
            </p>
          </div>
        </div>

        <!-- Pendapatan Non-Langganan -->
        <div
          class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-amber-500 transform hover:-translate-y-1 transition-all duration-300">
          <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-amber-50 rounded-lg">
              <i class="fas fa-coins text-amber-600 text-xl"></i>
            </div>
          </div>
          <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Non-Langganan</h3>
            <p class="text-2xl font-bold text-gray-800 tracking-tight" id="totalNonSubscription">
              {{ 'Rp ' . number_format($summaryData['totalNonSubscription'], 0, ',', '.') }}
            </p>
          </div>
        </div>

        <!-- Pengeluaran -->
        <div
          class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-rose-500 transform hover:-translate-y-1 transition-all duration-300">
          <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-rose-50 rounded-lg">
              <i class="fas fa-arrow-down text-rose-600 text-xl"></i>
            </div>
          </div>
          <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Pengeluaran</h3>
            <p class="text-2xl font-bold text-gray-800 tracking-tight">
              {{ 'Rp ' . number_format($pengeluaran, 0, ',', '.') }}
            </p>
          </div>
        </div>

        <!-- Laba/Rugi -->
        <div
          class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-indigo-500 transform hover:-translate-y-1 transition-all duration-300">
          <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-indigo-50 rounded-lg">
              <i class="fas fa-chart-pie text-indigo-600 text-xl"></i>
            </div>
          </div>
          <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Laba Bersih</h3>
            <p class="text-2xl font-bold text-gray-800 tracking-tight" id="totalProfitLoss">
              {{ 'Rp ' . number_format($summaryData['totalProfitLoss'], 0, ',', '.') }}
            </p>
          </div>
        </div>
      </div>

      <!-- Controls Section -->
      <div
        class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-8 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="relative group">
          <select id="globalYearFilter"
            class="appearance-none bg-white border border-gray-200 text-gray-700 py-2.5 pl-4 pr-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm font-medium shadow-sm transition-all cursor-pointer hover:border-blue-400">
            @foreach($availableYears as $year)
              <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                Tahun {{ $year }}
              </option>
            @endforeach
            <option value="all">Semua Tahun</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
            <i class="fas fa-chevron-down text-xs"></i>
          </div>
        </div>

        <div class="flex gap-3">
          <button onclick="exportToExcel()"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
            <i class="fas fa-file-excel mr-2 text-green-600"></i>
            Excel
          </button>
          <button onclick="exportToPDF()"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
            <i class="fas fa-file-pdf mr-2 text-rose-600"></i>
            PDF
          </button>
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden relative min-h-[300px]">
        <div class="loading-overlay" id="loadingOverlay" style="display: none;">
          <div class="loading-spinner"></div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse" id="mainTable">
            <thead>
              <tr class="text-xs font-semibold tracking-wide text-white uppercase border-b border-blue-500 bg-blue-500">
                <th class="px-6 py-4 rounded-tl-lg">Kategori</th>
                <th class="px-4 py-4 text-center">Jan</th>
                <th class="px-4 py-4 text-center">Feb</th>
                <th class="px-4 py-4 text-center">Mar</th>
                <th class="px-4 py-4 text-center">Apr</th>
                <th class="px-4 py-4 text-center">Mei</th>
                <th class="px-4 py-4 text-center">Jun</th>
                <th class="px-4 py-4 text-center">Jul</th>
                <th class="px-4 py-4 text-center">Agu</th>
                <th class="px-4 py-4 text-center">Sep</th>
                <th class="px-4 py-4 text-center">Okt</th>
                <th class="px-4 py-4 text-center">Nov</th>
                <th class="px-4 py-4 text-center">Des</th>
                <th class="px-6 py-4 text-right bg-indigo-700/50 rounded-tr-lg">Total</th>
              </tr>
            </thead>
            <tbody id="mainTableBody" class="divide-y divide-gray-100">
              <!-- Content via JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- Statistics Modal (Simplified Flexbox Centering) -->
    <div id="statsModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Flex Container for Centering -->
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">

            <!-- Background Overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closeStatsModal()"></div>

            <!-- Modal Panel -->
            <div class="relative inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold leading-6 text-gray-900" id="modal-title">
                        Perbandingan Pendapatan Langganan
                    </h3>
                    <button type="button" onclick="closeStatsModal()" class="text-gray-400 bg-transparent hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="mt-2">
                    <!-- Chart Container -->
                    <div id="revenueChart" class="w-full h-96"></div>
                </div>

                <!-- Footer -->
                <div class="mt-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeStatsModal()" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
  </div>

  <!-- ApexCharts CDN -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <script>
    // Data from controller
    let currentFinancialData = @json($financialData);
    let currentSummaryData = @json($summaryData);
    let currentSelectedYear = {{ $selectedYear }};

    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    // Format currency
    function formatCurrency(amount) {
      if (amount === 0) return '-';
      // Gunakan notasi pendek untuk di tabel jika angka terlalu besar, atau full format
      // Disini tetap full format tapi dengan tampilan bersih
      return new Intl.NumberFormat('id-ID').format(amount);
    }

    function formatCurrencyFull(amount) {
      return 'Rp ' + amount.toLocaleString('id-ID');
    }

    function getAmountColor(amount) {
      if (amount > 0) return 'text-emerald-600 font-medium';
      if (amount < 0) return 'text-rose-600 font-medium';
      return 'text-gray-400';
    }

    function updateTable() {
      const tbody = document.getElementById('mainTableBody');
      tbody.innerHTML = '';

      const categories = [
        {
          name: 'Pendapatan Langganan',
          data: currentFinancialData.subscription,
          icon: 'fas fa-wallet',
          color: 'text-emerald-500'
        },
        {
          name: 'Pendapatan Non-Langganan',
          data: currentFinancialData.nonSubscription,
          icon: 'fas fa-coins',
          color: 'text-amber-500'
        },
        {
          name: 'Total Pendapatan',
          data: currentFinancialData.totalRevenue,
          icon: 'fas fa-chart-line',
          color: 'text-blue-500',
          isTotal: true
        },
        {
          name: 'Pengeluaran',
          data: currentFinancialData.operationalCost,
          icon: 'fas fa-arrow-down',
          color: 'text-rose-500'
        },
        {
          name: 'Laba Bersih',
          data: currentFinancialData.profitLoss,
          icon: 'fas fa-piggy-bank',
          color: 'text-purple-500',
          isHighlight: true
        }
      ];

      categories.forEach(category => {
        const row = document.createElement('tr');
        row.className = category.isHighlight ? 'bg-gray-50/50' : 'hover:bg-gray-50/30 transition-colors';

        // Category Name
        const categoryCell = document.createElement('td');
        categoryCell.className = 'px-6 py-4 whitespace-nowrap';

        let iconBgColor = 'bg-gray-100';
        if (category.color.includes('emerald')) iconBgColor = 'bg-emerald-50';
        else if (category.color.includes('amber')) iconBgColor = 'bg-amber-50';
        else if (category.color.includes('rose')) iconBgColor = 'bg-rose-50';
        else if (category.color.includes('blue')) iconBgColor = 'bg-blue-50';
        else if (category.color.includes('indigo')) iconBgColor = 'bg-indigo-50';

        categoryCell.innerHTML = `
                                <div class="flex items-center">
                                    <div class="p-2 ${iconBgColor} rounded-lg mr-3 shadow-sm">
                                        <i class="${category.icon} ${category.color} text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">${category.name}</span>
                                </div>
                            `;
        row.appendChild(categoryCell);

        // Monthly Data
        category.data.forEach(amount => {
          const cell = document.createElement('td');
          cell.className = `px-4 py-4 whitespace-nowrap text-xs text-center ${getAmountColor(amount)}`;
          cell.textContent = formatCurrency(amount);
          row.appendChild(cell);
        });

        // Total (Yearly)
        const totalCell = document.createElement('td');
        totalCell.className = 'px-6 py-4 whitespace-nowrap text-right font-bold text-sm bg-gray-50/50 text-gray-800';
        const total = category.data.reduce((sum, amount) => sum + amount, 0);
        totalCell.textContent = formatCurrencyFull(total);
        row.appendChild(totalCell);

        tbody.appendChild(row);
      });
    }

    function updateSummaryCards() {
      // Gunakan animasi counting sederhana atau update langsung
      document.getElementById('totalSubscription').textContent = formatCurrencyFull(currentSummaryData.totalSubscription);
      document.getElementById('totalNonSubscription').textContent = formatCurrencyFull(currentSummaryData.totalNonSubscription);
      document.getElementById('totalProfitLoss').textContent = formatCurrencyFull(currentSummaryData.totalProfitLoss);
    }

    function showLoading() {
      document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
      document.getElementById('loadingOverlay').style.display = 'none';
    }

    function loadDataForYear(year) {
      if (year === currentSelectedYear.toString()) return;

      showLoading();

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

            document.getElementById('yearBadge').innerHTML = `
                                      <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                                      Tahun ${currentSelectedYear}
                                  `;
          } else {
            alert('Gagal memuat data');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan koneksi');
        })
        .finally(() => {
          hideLoading();
        });
    }

    // Simple export logic maintained from previous version but styled
    function exportToExcel() {
      // Implementasi sederhana menggunakan table HTML yang ada
      // Untuk hasil production grade, sebaiknya generate data array manual agar format rapi
      const table = document.getElementById('mainTable');
      const workbook = XLSX.utils.table_to_book(table, { sheet: "Laporan Keuangan" });
      XLSX.writeFile(workbook, `Laporan_Keuangan_Global_${currentSelectedYear}.xlsx`);
    }

    function exportToPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF('l', 'mm', 'a4');

      doc.setFontSize(14);
      doc.text(`Laporan Keuangan Global - Tahun ${currentSelectedYear}`, 14, 20);

      doc.autoTable({
        html: '#mainTable',
        startY: 30,
        theme: 'plain',
        headStyles: {
          fillColor: [249, 250, 251], /* gray-50 */
          textColor: [55, 65, 81], /* gray-700 */
          fontStyle: 'bold',
          lineWidth: 0.1,
          lineColor: [229, 231, 235] /* gray-200 */
        },
        bodyStyles: {
          lineWidth: 0.1,
          lineColor: [243, 244, 246] /* gray-100 */
        },
        styles: {
          fontSize: 8,
          cellPadding: 3
        }
      });

      doc.save(`Laporan_Keuangan_Global_${currentSelectedYear}.pdf`);
    }

    let chart = null;

    function openStatsModal() {
      document.getElementById('statsModal').classList.remove('hidden');
      loadComparisonData();
    }

    function closeStatsModal() {
      document.getElementById('statsModal').classList.add('hidden');
    }

    function loadComparisonData() {
      // Show loading state in chart container if needed

      const currentYear = currentSelectedYear;
      const lastYear = currentYear - 1;

      fetch(`{{ route('keuangan.getComparisonData') }}?year=${currentYear}&compare_year=${lastYear}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderChart(data.data);
        } else {
          alert('Gagal memuat data perbandingan');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memuat data grafik');
      });
    }

    function renderChart(data) {
      const options = {
        series: [{
          name: `Tahun ${data.currentYear}`,
          data: data.currentYearData
        }, {
          name: `Tahun ${data.lastYear}`,
          data: data.lastYearData
        }],
        chart: {
          height: 350,
          type: 'line',
          toolbar: {
            show: false
          },
          zoom: {
            enabled: false
          }
        },
        colors: ['#10B981', '#6B7280'], // Emerald-500 for current year, Gray-500 for last year
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth',
          width: 3
        },
        title: {
          text: 'Tren Pendapatan Langganan Bulanan',
          align: 'left',
          style: {
             fontFamily: 'Inter, sans-serif'
          }
        },
        grid: {
          borderColor: '#f1f1f1',
        },
        xaxis: {
          categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        },
        yaxis: {
          labels: {
            formatter: function (value) {
              return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumSignificantDigits: 3
              }).format(value);
            }
          }
        },
        tooltip: {
          y: {
            formatter: function (value) {
              return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
              }).format(value);
            }
          }
        },
        legend: {
          position: 'top'
        }
      };

      if (chart) {
        chart.destroy();
      }

      chart = new ApexCharts(document.querySelector("#revenueChart"), options);
      chart.render();
    }

    document.getElementById('globalYearFilter').addEventListener('change', function () {
      const val = this.value;
      if (val !== 'all') {
        loadDataForYear(val);
      } else {
        alert('Filter semua tahun belum tersedia');
      }
    });

    document.addEventListener('DOMContentLoaded', function () {
      updateTable();
      updateSummaryCards();
    });
  </script>
@endsection
