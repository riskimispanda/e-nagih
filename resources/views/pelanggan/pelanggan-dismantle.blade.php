@extends('layouts/contentNavbarLayout')
@section('title', 'Pelanggan Dismantle')

<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

<style>
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .glass {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .pagination-btn {
        min-width: 36px;
        text-align: center;
    }

    .search-wrapper {
        position: relative;
    }
    .search-wrapper .bx-search {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1rem;
    }
    .search-wrapper input {
        padding-left: 32px;
    }

    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }
    .empty-state .bx {
        font-size: 3rem;
        color: #d1d5db;
        margin-bottom: 12px;
    }
</style>

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Header -->
    <div class="mb-4 fade-in">
      <div class="card bg-white border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="flex-shrink-0">
              <span class="badge bg-label-danger p-2" style="font-size: 1.5rem;">
                <i class="bx bx-power-off"></i>
              </span>
            </div>
            <div>
              <h4 class="fw-bold mb-1">Data Pelanggan Dismantle</h4>
              <p class="text-muted mb-0">Daftar pelanggan yang telah dilakukan dismantle</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm fade-in">
      <div class="card-body px-0 py-0">
        <!-- Controls -->
        <div class="flex flex-col md:flex-row justify-between items-center px-4 py-4 gap-2 border-b border-gray-200">
          <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-gray-500 uppercase ">Cari:</label>
            <div class="search-wrapper">
              <input type="text" id="tableSearch" class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full" placeholder="Nama pelanggan, paket, atau agen...">
            </div>
          </div>
          <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-gray-500 uppercase">Tampilkan:</label>
            <select id="pageSizeSelect" class="border border-gray-300 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full" id="dismantleTable">
            <thead>
              <tr class="bg-gray-100 border-b border-gray-200">
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-12">No</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase ">Pelanggan</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase ">Paket</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase  w-20">History</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase ">Installasi</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase ">Dismantle</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase ">Dibuat</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase ">Teknisi</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase ">Status</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase ">Keterangan</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase w-24">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse($pelanggan as $index => $item)
              @php
                $tiketSelesai = $item->tiket->where('status_id', 3)->first();
              @endphp
              <tr class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ $index + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-900">{{ $item->nama_customer ?? 'N/A' }}</span>
                    @if($item->alamat)
                      <span class="text-xs text-gray-400 truncate max-w-40" title="{{ $item->alamat }}">{{ $item->alamat }}</span>
                    @endif
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">
                    {{ $item->paket?->nama_paket ?? 'N/A' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <a href="/riwayatPembayaran/{{ $item->id }}" class="inline-flex items-center justify-center w-8 h-8 rounded border bg-yellow-400 text-dark hover:bg-yellow-400 hover:text-dark transition-colors" data-bs-toggle="tooltip" title="History Pembayaran {{ $item->nama_customer }}">
                    <i class="bx bx-folder-open text-sm"></i>
                  </a>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 text-center whitespace-nowrap">
                  @if($item->tanggal_selesai)
                    {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                  @else
                    <span class="text-gray-300">&mdash;</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 text-center whitespace-nowrap">
                  @if($tiketSelesai)
                    {{ \Carbon\Carbon::parse($tiketSelesai->tanggal_selesai)->format('d/m/Y') }}
                  @else
                    <span class="text-gray-300">&mdash;</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">
                  @if($tiketSelesai)
                    {{ $tiketSelesai->user?->name ?? '-' }}
                  @else
                    <span class="text-gray-300">&mdash;</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">
                  @if($tiketSelesai)
                    {{ $tiketSelesai->teknisi?->name ?? '-' }}
                  @else
                    <span class="text-gray-300">&mdash;</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-red-200 text-red-800">
                    <i class="bx bx-check-circle mr-1"></i>Dismantled
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                  @if($tiketSelesai && $tiketSelesai->keterangan)
                    <span class="truncate block max-w-32" title="{{ $tiketSelesai->keterangan }}">{{ Str::limit($tiketSelesai->keterangan, 30) }}</span>
                  @else
                    <span class="text-gray-300">&mdash;</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-center">
                  <button onclick="restoreCustomer({{ $item->id }})"
                    class="inline-flex items-center justify-center w-8 h-8 rounded border text-white bg-red-500 hover:bg-red-500 transition-colors duration-150"
                    title="Kembalikan data pelanggan ini">
                    <i class="bx bx-undo text-sm"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="11">
                  <div class="empty-state">
                    <i class="bx bx-box"></i>
                    <p class="text-gray-400 mb-0">Tidak ada data dismantle</p>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Footer -->
        <div class="flex flex-col md:flex-row justify-between items-center px-4 py-3 border-t border-gray-200 gap-2">
          <div class="text-sm text-gray-500" id="tableInfo">
            Menampilkan 1 - 10 dari {{ $pelanggan->count() }} data
          </div>
          <div class="flex items-center gap-1" id="pagination"></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

<script>
var tableData = [];
var filteredData = [];
var currentPage = 1;
var pageSize = 10;

document.addEventListener('DOMContentLoaded', function() {
    var rows = document.querySelectorAll('#dismantleTable tbody tr');
    if (rows.length === 1 && rows[0].querySelector('.empty-state')) return;

    tableData = Array.from(rows).map(function(row) {
        return { el: row, text: row.textContent.toLowerCase() };
    });

    filteredData = [].concat(tableData);
    renderTable();

    document.getElementById('tableSearch').addEventListener('input', function() {
        var q = this.value.toLowerCase();
        filteredData = tableData.filter(function(row) {
            return row.text.indexOf(q) !== -1;
        });
        currentPage = 1;
        renderTable();
    });

    document.getElementById('pageSizeSelect').addEventListener('change', function() {
        pageSize = parseInt(this.value);
        currentPage = 1;
        renderTable();
    });
});

function renderTable() {
    var total = filteredData.length;
    var totalPages = Math.ceil(total / pageSize) || 1;
    if (currentPage > totalPages) currentPage = totalPages;

    tableData.forEach(function(d) { d.el.style.display = 'none'; });

    var start = (currentPage - 1) * pageSize;
    var end = Math.min(start + pageSize, total);
    for (var i = start; i < end; i++) {
        filteredData[i].el.style.display = '';
    }

    document.getElementById('tableInfo').textContent =
        'Menampilkan ' + (total > 0 ? start + 1 : 0) + ' - ' + end + ' dari ' + total + ' data';

    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    var el = document.getElementById('pagination');
    var html = '';

    var baseBtn = 'inline-flex items-center justify-center w-8 h-8 text-sm rounded border transition-colors duration-150';
    var activeBtn = 'bg-blue-600 text-white border-blue-600';
    var inactiveBtn = 'border-gray-300 text-gray-600 hover:bg-gray-100';
    var disabledBtn = 'border-gray-200 text-gray-300 cursor-not-allowed';

    html += '<button class="' + baseBtn + ' ' + (currentPage <= 1 ? disabledBtn : inactiveBtn) + '" onclick="goToPage(' + (currentPage - 1) + ')" ' +
        (currentPage <= 1 ? 'disabled' : '') + '><i class="bx bx-chevron-left"></i></button>';

    var maxVisible = 5;
    var startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    var endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    for (var i = startPage; i <= endPage; i++) {
        html += '<button class="' + baseBtn + ' ' + (i === currentPage ? activeBtn : inactiveBtn) + '" onclick="goToPage(' + i + ')">' + i + '</button>';
    }

    html += '<button class="' + baseBtn + ' ' + (currentPage >= totalPages ? disabledBtn : inactiveBtn) + '" onclick="goToPage(' + (currentPage + 1) + ')" ' +
        (currentPage >= totalPages ? 'disabled' : '') + '><i class="bx bx-chevron-right"></i></button>';

    el.innerHTML = html;
}

function goToPage(page) {
    var totalPages = Math.ceil(filteredData.length / pageSize) || 1;
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTable();
}
</script>
