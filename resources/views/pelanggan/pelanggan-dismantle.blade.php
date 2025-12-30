@extends('layouts/contentNavbarLayout')
@section('title', 'Pelanggan Dismantle')

<!-- Tailwind CSS CDN -->
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

<!-- Custom Styles -->
<style>
    /* Animasi dan transisi */
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Hover effects */
    .hover-lift {
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    /* Gradient backgrounds */
    .gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .gradient-success {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    }

    .gradient-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    /* Glass morphism effect */
    .glass {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

@section('content')

<div class="row">
  <div class="col-sm-12">
    <div class="min-h-screen">
      <!-- Header Section -->
      <div class="mb-8 fade-in">
        <div class="glass rounded-2xl p-6 hover-lift">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-3xl fw-bold text-gray-800 flex items-center mb-2">
                Data Pelanggan Dismantle
              </h4>
              <p class="text-gray-600">Daftar pelanggan yang telah dilakukan dismantle</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Main Table Card -->
      <div class="glass rounded-2xl shadow-xl fade-in" style="animation-delay: 0.4s">
        <div class="p-6">
          <!-- Table Controls -->
          <div class="flex flex-col md:flex-row justify-between items-center mb-2 space-y-4 md:space-y-0">
            <div class="w-full md:w-96">
              <div class="relative">
                <i class="bx bx-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text"
                       id="tableSearch"
                       placeholder="Cari nama pelanggan, paket, atau agen..."
                       class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
              </div>
            </div>
            <div class="page-size">
                <label>Tampilkan:</label>
                <select class="w-full pl-2 pr-2 py-2 border border-gray-200 rounded" id="pageSizeSelect" onchange="changePageSize()">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-body">
            <!-- Minimal Table -->
            <div class="overflow-x-auto">
              <table class="w-full" id="dismantleTable">
                <thead>
                  <tr class="border-b border-gray-100">
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-400">No</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-400">Pelanggan</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-400">Paket</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-400">History Pembayaran</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-400">Installasi</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-400">Dismantle</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-400">Dibuat</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-400">Diclose</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-400">Status</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-400">Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pelanggan as $index => $item)
                  <tr class="border-b border-gray-50 hover:bg-gray-25">
                    <td class="px-3 py-3 text-sm text-gray-600">{{$index + 1}}</td>
                    <td class="px-3 py-3">
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-900">{{$item->nama_customer ?? 'N/A'}}</span>
                            @if($item->no_hp)
                                <span class="text-xs text-gray-400">{{$item->alamat}}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-3 py-3">
                        <span class="text-xs fw-bold text-blue-800 bg-blue-200 px-2 py-1 rounded">
                            {{$item->paket?->nama_paket ?? 'N/A'}}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="/riwayatPembayaran/{{ $item->id }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="History Pembayaran {{ $item->nama_customer }}" data-bs-placement="bottom">
                            <i class="bx bx-history"></i>
                        </a>
                    </td>
                    <td class="px-3 py-3 text-sm text-gray-600">
                        @if($item->tanggal_selesai)
                            {{\Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y')}}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-3 text-sm text-gray-600">
                        @if($item->tiket->isNotEmpty() && $item->tiket->where('status_id', 3)->first())
                            {{\Carbon\Carbon::parse($item->tiket->where('status_id', 3)->first()->tanggal_selesai)->format('d M Y')}}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-3 fw-bold text-sm text-gray-600">
                        @if($item->tiket->isNotEmpty() && $item->tiket->where('status_id', 3)->first())
                            {{$item->tiket->where('status_id', 3)->first()->user?->name ?? '-'}}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-3 fw-bold text-sm text-gray-600">
                        @if($item->tiket->isNotEmpty() && $item->tiket->where('status_id', 3)->first())
                            {{$item->tiket->where('status_id', 3)->first()->teknisi?->name ?? '-'}}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-3">
                        <span class="text-xs fw-bold text-red-800 bg-red-200 px-2 py-1 rounded">
                            <i class="bx bx-check-circle me-1 fs-6"></i>Dismantled
                        </span>
                    </td>
                    <td class="px-3 py-3 fw-bold text-sm text-gray-600">
                        @if($item->tiket->isNotEmpty() && $item->tiket->where('status_id', 3)->first())
                            {{$item->tiket->where('status_id', 3)->first()->keterangan ?? '-'}}
                        @else
                            -
                        @endif
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="9" class="px-3 py-8 text-center text-sm text-gray-400">
                        Tidak ada data
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <!-- Pagination Controls -->
            <div class="flex flex-col md:flex-row justify-between items-center mt-6 pt-6 border-t border-gray-200 space-y-4 md:space-y-0">
                <div class="text-sm text-gray-600">
                    Menampilkan <span id="showingStart" class="font-medium text-gray-900">1</span> -
                    <span id="showingEnd" class="font-medium text-gray-900">10</span>
                    dari <span id="totalRecords" class="font-medium text-gray-900">{{ $pelanggan->count() }}</span> data
                </div>

                <div class="flex items-center space-x-4">
                    <div class="flex space-x-1" id="pagination">
                        <!-- Pagination buttons will be generated here -->
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

<!-- Backup initialization script -->
<script>
console.log('Backup script - checking DataTable');
console.log('jQuery:', typeof $);
console.log('Table:', $('#dismantleTable').length);
console.log('DataTable plugin:', typeof $.fn.DataTable);

setTimeout(function() {
    console.log('Timeout fired - checking conditions...');
    console.log('jQuery available:', typeof $ !== 'undefined');
    console.log('Table exists:', $('#dismantleTable').length > 0);
    console.log('DataTable plugin loaded:', typeof $.fn.DataTable !== 'undefined');
    console.log('Already DataTable?', $.fn.DataTable.isDataTable('#dismantleTable'));

    if (typeof $ !== 'undefined' && $('#dismantleTable').length > 0 && typeof $.fn.DataTable !== 'undefined') {
        console.log('All conditions met - forcing DataTable creation');

        try {
            var table = $('#dismantleTable').DataTable({
                searching: true,
                paging: true,
                ordering: true,
                info: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data"
                },
                dom: 'Blfrtip',
                buttons: [
                    { extend: 'excel', text: 'Excel', className: 'btn btn-success btn-sm' },
                    { extend: 'pdf', text: 'PDF', className: 'btn btn-danger btn-sm' },
                    { extend: 'print', text: 'Cetak', className: 'btn btn-primary btn-sm' }
                ],
                initComplete: function() {
                    console.log('DataTable successfully initialized!');
                    console.log('Search wrapper:', $('.dataTables_filter').length);
                    console.log('Search input:', $('.dataTables_filter input').length);
                }
            });
            console.log('DataTable object created:', table);
        } catch(error) {
            console.error('Error creating DataTable:', error);
        }
    } else {
        console.log('Conditions not met for DataTable creation');
    }
}, 3000);
</script>

<script>
// Custom Table Functionality
let currentPage = 1;
let pageSize = 10;
let filteredData = [];

// Initialize table
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('#dismantleTable tbody tr');
    const allData = Array.from(tableRows).map(row => ({
        element: row,
        text: row.textContent.toLowerCase()
    }));

    filteredData = allData;
    updateTable();

    // Search functionality
    document.getElementById('tableSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();

        if (searchTerm === '') {
            filteredData = allData;
        } else {
            filteredData = allData.filter(row =>
                row.text.includes(searchTerm)
            );
        }

        currentPage = 1;
        updateTable();
    });
});

function updateTable() {
    const tableRows = document.querySelectorAll('#dismantleTable tbody tr');

    // Hide all rows first
    tableRows.forEach(row => row.style.display = 'none');

    // Calculate pagination
    const startIndex = (currentPage - 1) * pageSize;
    const endIndex = Math.min(startIndex + pageSize, filteredData.length);

    // Show filtered rows for current page
    for (let i = startIndex; i < endIndex; i++) {
        filteredData[i].element.style.display = '';
    }

    // Update info
    document.getElementById('showingStart').textContent =
        filteredData.length > 0 ? startIndex + 1 : 0;
    document.getElementById('showingEnd').textContent = endIndex;
    document.getElementById('totalRecords').textContent = filteredData.length;

    // Update pagination
    updatePagination();
}

function updatePagination() {
    const totalPages = Math.ceil(filteredData.length / pageSize);
    const paginationContainer = document.getElementById('pagination');

    let paginationHTML = '';

    // Previous button
    paginationHTML += `<button class="px-3 py-1 text-sm border border-gray-300 rounded-l-lg hover:bg-gray-50 transition-colors ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}"
        onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
        <i class="bx bx-chevron-left"></i>
    </button>`;

    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === currentPage;
        paginationHTML += `<button class="px-3 py-1 text-sm border ${isActive ? 'bg-blue-500 text-white border-blue-500' : 'border-gray-300 hover:bg-gray-50'} transition-colors"
            onclick="goToPage(${i})">${i}</button>`;
    }

    // Next button
    paginationHTML += `<button class="px-3 py-1 text-sm border border-gray-300 rounded-r-lg hover:bg-gray-50 transition-colors ${currentPage === totalPages || totalPages === 0 ? 'opacity-50 cursor-not-allowed' : ''}"
        onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}>
        <i class="bx bx-chevron-right"></i>
    </button>`;

    paginationContainer.innerHTML = paginationHTML;
}

function goToPage(page) {
    const totalPages = Math.ceil(filteredData.length / pageSize);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        updateTable();
    }
}

function changePageSize() {
    pageSize = parseInt(document.getElementById('pageSizeSelect').value);
    currentPage = 1;
    updateTable();
}

// Export functions
function exportToExcel() {
    alert('Export Excel - Fitur ini memerlukan library tambahan');
}

function exportToPDF() {
    alert('Export PDF - Fitur ini memerlukan library tambahan');
}

console.log('Custom table functionality initialized');
</script>
