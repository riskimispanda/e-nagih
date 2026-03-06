@extends('layouts/contentNavbarLayout')

@section('title', 'WhatsApp Log History')

@section('vendor-style')
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* basic override for datatable tailwind if needed */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      padding: 0.25rem 0.5rem;
      margin: 0 0.125rem;
      border-radius: 0.25rem;
      background: transparent !important;
      border: 1px solid #e5e7eb !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: #3b82f6 !important;
      color: white !important;
      border-color: #3b82f6 !important;
    }

    .dataTables_wrapper {
      padding: 1rem;
      background: white;
    }

    table.dataTable.no-footer {
      border-bottom: 1px solid #e5e7eb;
    }
  </style>
@endsection

@section('content')
  <div class="mb-6 grid grid-cols-2 lg:grid-cols-6 gap-4">
    <!-- Total Card -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 flex flex-col justify-center items-center">
      <div class="text-gray-500 mb-1 text-[10px] font-bold uppercase tracking-wider">Total</div>
      <div class="text-2xl font-bold text-gray-800">{{ number_format($counts['total']) }}</div>
    </div>

    <!-- Pending Card -->
    <div class="bg-yellow-50 rounded-lg p-4 shadow-sm border border-yellow-100 flex flex-col justify-center items-center">
      <div class="text-yellow-600 mb-1 text-[10px] font-bold uppercase tracking-wider">
        <i class="fas fa-clock mr-1"></i> Pending
      </div>
      <div class="text-2xl font-bold text-yellow-700">{{ number_format($counts['pending']) }}</div>
    </div>

    <!-- Sent Card -->
    <div class="bg-blue-50 rounded-lg p-4 shadow-sm border border-blue-100 flex flex-col justify-center items-center">
      <div class="text-blue-600 mb-1 text-[10px] font-bold uppercase tracking-wider">
        <i class="fas fa-paper-plane mr-1"></i> Sent
      </div>
      <div class="text-2xl font-bold text-blue-700">{{ number_format($counts['sent']) }}</div>
    </div>

    <!-- Delivered Card -->
    <div class="bg-purple-50 rounded-lg p-4 shadow-sm border border-purple-100 flex flex-col justify-center items-center">
      <div class="text-purple-600 mb-1 text-[10px] font-bold uppercase tracking-wider">
        <i class="fas fa-check-double mr-1"></i> Delivered
      </div>
      <div class="text-2xl font-bold text-purple-700">{{ number_format($counts['delivered']) }}</div>
    </div>

    <!-- Read Card -->
    <div
      class="bg-emerald-50 rounded-lg p-4 shadow-sm border border-emerald-100 flex flex-col justify-center items-center">
      <div class="text-emerald-600 mb-1 text-[10px] font-bold uppercase tracking-wider">
        <i class="fas fa-check-double mr-1 text-emerald-500"></i> Read
      </div>
      <div class="text-2xl font-bold text-emerald-700">{{ number_format($counts['read']) }}</div>
    </div>

    <!-- Failed Card -->
    <div class="bg-red-50 rounded-lg p-4 shadow-sm border border-red-100 flex flex-col justify-center items-center">
      <div class="text-red-500 mb-1 text-[10px] font-bold uppercase tracking-wider">
        <i class="fas fa-times-circle mr-1"></i> Failed
      </div>
      <div class="text-2xl font-bold text-red-700">{{ number_format($counts['failed']) }}</div>
    </div>
  </div>

  <div class="p-6 bg-white rounded-lg shadow-sm">
    <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
      <div>
        <h2 class="text-2xl font-semibold text-gray-800">WhatsApp Broadcast & Logs</h2>
        <p class="text-sm text-gray-500">Histori Pengiriman Pesan Sistem (Local & Qontak)</p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Filter Status -->
        <select id="filterStatus"
          class="bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block p-2 font-medium">
          <option value="all">Semua Status</option>
          <option value="sent">Status: Sent</option>
          <option value="delivered">Status: Delivered</option>
          <option value="read">Status: Read</option>
          <option value="failed">Status: Failed</option>
          <option value="pending">Status: Pending</option>
        </select>

        <button id="btn-sync"
          class="px-4 py-2 text-sm font-medium text-white transition-colors bg-emerald-600 border border-transparent rounded-md hover:bg-emerald-700 shadow-sm whitespace-nowrap">
          <i class="fas fa-cloud-download-alt mr-2"></i> Sync Status Qontak
        </button>

        <button id="btn-refresh"
          class="px-4 py-2 text-sm font-medium text-blue-600 transition-colors bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 shadow-sm whitespace-nowrap">
          <i class="fas fa-sync-alt mr-2"></i> Refresh Tabel
        </button>
      </div>
    </div>

    <!-- Table Container -->
    <div class="w-full overflow-x-auto">
      <table id="whatsLogTable" class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3">Tanggal Waktu</th>
            <th scope="col" class="px-6 py-3">Target Penerima</th>
            <th scope="col" class="px-6 py-3">Tipe/Kategori</th>
            <th scope="col" class="px-6 py-3">Isi Pesan / Keterangan</th>
            <th scope="col" class="px-6 py-3">Status</th>
            <th scope="col" class="px-6 py-3">API Broadcast ID</th>
          </tr>
        </thead>
        <tbody>
          <!-- Populated by DataTables via AJAX -->
        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('vendor-script')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
@endsection

@section('page-script')
  <script>
    let table;

    // Gunakan skrip tanpa jQuery (Vanilla JS) agar tahan terhadap bug jQuery ganda dari Master Layout
    document.addEventListener("DOMContentLoaded", function () {

      // Cek dulu apakah DataTable sudah available untuk di-execute
      if (typeof $.fn.DataTable !== 'undefined') {
        initDatatable();
      } else {
        // Jika belum available dari CDN atas, tempel manual dan tunggu 500ms
        const dtScript = document.createElement('script');
        dtScript.src = 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js';
        document.body.appendChild(dtScript);
        setTimeout(initDatatable, 600);
      }

      function initDatatable() {
        table = $('#whatsLogTable').DataTable({
          processing: true,
          serverSide: false, // Kita fetch sekali limit (client-side render) untuk optimasi kecepatan
          ajax: {
            url: "{{ route('qontak.api.whats-log-data') }}",
            type: 'GET',
            data: function (d) {
              d.status = $('#filterStatus').val(); // Bawa query status filter ke controller
            },
            error: function (xhr, error, thrown) {
              alert('Gagal mengambil data dari server. Error: ' + xhr.statusText);
            }
          },
          order: [[0, 'desc']], // Default sort berdasarkan Tanggal Waktu (Kolom 0) menurun
          columns: [
            {
              data: 'created_at',
              render: function (data) {
                if (!data) return '-';
                const date = new Date(data);
                return `<span class="font-medium text-gray-800">${date.toLocaleString('id-ID', {
                  day: '2-digit', month: 'short', year: 'numeric',
                  hour: '2-digit', minute: '2-digit'
                })}</span>`;
              }
            },
            {
              data: null,
              render: function (data) {
                return `<div class="font-medium text-gray-900">${data.nama_customer || 'Guest / Terhapus'}</div>
                                                  <div class="text-xs text-gray-500 mt-1"><i class="fab fa-whatsapp text-green-500 mr-1"></i> ${data.no_tujuan || '-'}</div>`;
              }
            },
            {
              data: 'jenis_pesan',
              render: function (data) {
                let formatted = data ? data.replace(/_/g, ' ').toUpperCase() : '-';
                return `<span class="px-2 py-1 text-[10px] font-bold text-gray-600 bg-gray-100 border border-gray-200 rounded">
                                                      ${formatted}
                                                  </span>`;
              }
            },
            {
              data: 'pesan',
              render: function (data, type, row) {
                let text = data || '-';
                if (row.error_message) {
                  text += `<div class="mt-2 text-xs text-red-500 p-1.5 bg-red-50 rounded"><i class="fas fa-exclamation-triangle mr-1"></i> ${row.error_message}</div>`;
                }
                return `<div class="max-w-xs whitespace-normal break-words">${text}</div>`;
              }
            },
            {
              data: 'status_pengiriman',
              render: function (data) {
                let badgeClass = '';
                let icon = '';
                switch (data) {
                  case 'sent':
                  case 'done':
                    badgeClass = 'bg-blue-100 text-blue-800 border-blue-200';
                    icon = 'fa-check';
                    break;
                  case 'delivered':
                    badgeClass = 'bg-purple-100 text-purple-800 border-purple-200';
                    icon = 'fa-check-double';
                    break;
                  case 'read':
                    badgeClass = 'bg-green-100 text-green-800 border-green-200';
                    icon = 'fa-check-double text-green-600';
                    break;
                  case 'failed':
                  case 'error':
                    badgeClass = 'bg-red-100 text-red-800 border-red-200';
                    icon = 'fa-times';
                    break;
                  case 'pending':
                  case 'todo':
                    badgeClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                    icon = 'fa-clock';
                    break;
                  default:
                    badgeClass = 'bg-gray-100 text-gray-800 border-gray-200';
                    icon = 'fa-info-circle';
                }
                return `<span class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold border rounded-full ${badgeClass}">
                                                      <i class="fas ${icon} mr-1.5"></i> ${data.charAt(0).toUpperCase() + data.slice(1)}
                                                  </span>`;
              }
            },
            {
              data: 'qontak_broadcast_id',
              render: function (data) {
                if (!data) return '<span class="text-gray-400 italic text-xs bg-gray-50 px-2 py-1 rounded">Local Server</span>';
                // Ambil sebagian karakter id agar UI tabel tidak meledak lebarnya
                let displayId = data.substr(data.length - 12).toUpperCase();
                return `<span class="text-xs font-mono text-gray-600 bg-gray-100 px-2 py-1 rounded border select-all" title="${data}">...${displayId}</span>`;
              }
            }
          ],
          language: {
            search: "Cari Data:",
            lengthMenu: "Tampil _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ log",
            infoEmpty: "Menampilkan 0 - 0 dari 0 log",
            emptyTable: "Belum ada log pesan yang tersimpan",
            paginate: {
              first: "Awal",
              last: "Akhir",
              next: "Lanjut",
              previous: "Mundur"
            }
          }
        });

        // BIND FILTER STATUS REFRESH PADA DATATABLES
        const filterEl = document.getElementById('filterStatus');
        if (filterEl) {
          filterEl.addEventListener('change', function () {
            if (table) table.ajax.reload();
          });
        }

        // BIND TOMBOL SYNC MANUAL
        const btnSync = document.getElementById('btn-sync');
        if (btnSync) {
          btnSync.addEventListener('click', function () {
            const originalContent = btnSync.innerHTML;
            btnSync.disabled = true;
            btnSync.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Syncing...';

            fetch("{{ route('qontak.api.sync-logs') }}", {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify({ limit: 50 })
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  alert(data.message);
                  if (table) table.ajax.reload();
                } else {
                  alert('Gagal: ' + data.message);
                }
              })
              .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat sinkronisasi.');
              })
              .finally(() => {
                btnSync.disabled = false;
                btnSync.innerHTML = originalContent;
              });
          });
        }

        // BIND TOMBOL REFRESH MANUAL
        const btnRef = document.getElementById('btn-refresh');
        if (btnRef) {
          btnRef.addEventListener('click', function () {
            if (table) table.ajax.reload();
          });
        }

      } // penutup block initDatatable()
    }); // penutup DOMContentLoaded
  </script>
@endsection