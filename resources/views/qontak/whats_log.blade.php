@extends('layouts/contentNavbarLayout')

@section('title', 'WhatsApp Log History')

@section('vendor-style')
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* Styling DataTables Controls */
    .dataTables_wrapper {
      padding: 1.5rem 0;
      background: transparent;
    }

    .dataTables_length {
      margin-bottom: 1.25rem;
      float: left;
    }

    .dataTables_length label {
      color: #64748b;
      font-size: 0.875rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .dataTables_length select {
      border: 1px solid #e2e8f0;
      border-radius: 0.375rem;
      padding: 0.375rem 1.75rem 0.375rem 0.75rem;
      font-size: 0.875rem;
      outline: none;
      transition: all 0.2s;
      background-color: #f8fafc;
      color: #334155;
      font-weight: 500;
      cursor: pointer;
    }

    .dataTables_length select:focus {
      border-color: #3b82f6;
      background-color: #ffffff;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .dataTables_filter {
      margin-bottom: 1.25rem;
      float: right;
    }

    .dataTables_filter label {
      color: #64748b;
      font-size: 0.875rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .dataTables_filter input {
      border: 1px solid #e2e8f0;
      border-radius: 0.375rem;
      padding: 0.375rem 0.75rem;
      font-size: 0.875rem;
      outline: none;
      transition: all 0.2s;
      background-color: #f8fafc;
      color: #334155;
      width: 200px;
    }

    .dataTables_filter input:focus {
      border-color: #3b82f6;
      background-color: #ffffff;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
      width: 240px;
    }

    .dataTables_info {
      padding-top: 1.25rem;
      font-size: 0.875rem;
      color: #64748b;
      float: left;
      font-weight: 500;
    }

    .dataTables_paginate {
      padding-top: 1.25rem;
      float: right;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
      display: inline-block;
      padding: 0.375rem 0.75rem !important;
      margin: 0 0.125rem;
      border-radius: 0.375rem !important;
      background: #ffffff !important;
      border: 1px solid #e2e8f0 !important;
      color: #475569 !important;
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.15s ease;
      text-decoration: none !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background: #f1f5f9 !important;
      color: #0f172a !important;
      border-color: #cbd5e1 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
      background: #3b82f6 !important;
      color: white !important;
      border-color: #3b82f6 !important;
      box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
      background: #f8fafc !important;
      color: #94a3b8 !important;
      border-color: #e2e8f0 !important;
      cursor: not-allowed;
    }

    /* Table Custom styles */
    #whatsLogTable {
      width: 100% !important;
      border-collapse: separate;
      border-spacing: 0;
      border-radius: 0.5rem;
      overflow: hidden;
      border: 1px solid #e2e8f0;
    }

    #whatsLogTable thead th {
      background-color: #f8fafc;
      border-bottom: 2px solid #e2e8f0;
      color: #334155;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.05em;
      padding: 0.875rem 1rem !important;
    }

    #whatsLogTable tbody td {
      padding: 0.875rem 1rem !important;
      border-bottom: 1px solid #e2e8f0;
      vertical-align: middle;
    }

    #whatsLogTable tbody tr {
      transition: background-color 0.15s ease;
    }

    #whatsLogTable tbody tr:hover {
      background-color: #f8fafc;
    }

    #whatsLogTable tbody tr:last-child td {
      border-bottom: none;
    }

    /* Toast Notification for Clipboard */
    .toast-copy {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background-color: #0f172a;
      color: #ffffff;
      padding: 0.75rem 1.25rem;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      z-index: 9999;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transform: translateY(1rem);
      opacity: 0;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      pointer-events: none;
    }

    .toast-copy.show {
      transform: translateY(0);
      opacity: 1;
    }
  </style>
@endsection

@section('content')
  <div class="mb-6 grid grid-cols-2 lg:grid-cols-6 gap-4">
    <!-- Total Card -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col justify-center items-center transition-transform hover:-translate-y-0.5 duration-200">
      <div class="text-gray-500 mb-1 text-[10px] font-bold uppercase tracking-wider">Total</div>
      <div class="text-2xl font-bold text-gray-800" id="stat-total">{{ number_format($counts['total']) }}</div>
    </div>

    <!-- Pending Card -->
    <div class="bg-amber-50/50 rounded-xl p-4 shadow-sm border border-amber-100 flex flex-col justify-center items-center transition-transform hover:-translate-y-0.5 duration-200">
      <div class="text-amber-600 mb-1 text-[10px] font-bold uppercase tracking-wider">
        <i class="fas fa-clock mr-1"></i> Pending
      </div>
      <div class="text-2xl font-bold text-amber-700" id="stat-pending">{{ number_format($counts['pending']) }}</div>
    </div>

    <!-- Sent Card -->
    <div class="bg-blue-50/50 rounded-xl p-4 shadow-sm border border-blue-100 flex flex-col justify-center items-center transition-transform hover:-translate-y-0.5 duration-200">
      <div class="text-blue-600 mb-1 text-[10px] font-bold uppercase tracking-wider">
        <i class="fas fa-paper-plane mr-1"></i> Sent
      </div>
      <div class="text-2xl font-bold text-blue-700" id="stat-sent">{{ number_format($counts['sent']) }}</div>
    </div>

    <!-- Delivered Card -->
    <div class="bg-purple-50/50 rounded-xl p-4 shadow-sm border border-purple-100 flex flex-col justify-center items-center transition-transform hover:-translate-y-0.5 duration-200">
      <div class="text-purple-600 mb-1 text-[10px] font-bold uppercase tracking-wider">
        <i class="fas fa-check-double mr-1"></i> Delivered
      </div>
      <div class="text-2xl font-bold text-purple-700" id="stat-delivered">{{ number_format($counts['delivered']) }}</div>
    </div>

    <!-- Read Card -->
    <div class="bg-emerald-50/50 rounded-xl p-4 shadow-sm border border-emerald-100 flex flex-col justify-center items-center transition-transform hover:-translate-y-0.5 duration-200">
      <div class="text-emerald-600 mb-1 text-[10px] font-bold uppercase tracking-wider">
        <i class="fas fa-check-double mr-1 text-emerald-500"></i> Read
      </div>
      <div class="text-2xl font-bold text-emerald-700" id="stat-read">{{ number_format($counts['read']) }}</div>
    </div>

    <!-- Failed Card -->
    <div class="bg-rose-50/50 rounded-xl p-4 shadow-sm border border-rose-100 flex flex-col justify-center items-center transition-transform hover:-translate-y-0.5 duration-200">
      <div class="text-rose-500 mb-1 text-[10px] font-bold uppercase tracking-wider">
        <i class="fas fa-times-circle mr-1"></i> Failed
      </div>
      <div class="text-2xl font-bold text-rose-700" id="stat-failed">{{ number_format($counts['failed']) }}</div>
    </div>
  </div>

  <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">WhatsApp Broadcast & Logs</h2>
        <p class="text-sm text-gray-500 mt-1">Histori Pengiriman Pesan Sistem (Local & Qontak)</p>
      </div>

      <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
        <!-- Filter Status -->
        <select id="filterStatus"
          class="bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block p-2 font-medium cursor-pointer">
          <option value="all" {{ ($filters['status'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua Status</option>
          <option value="sent" {{ ($filters['status'] ?? '') == 'sent' ? 'selected' : '' }}>Status: Sent</option>
          <option value="delivered" {{ ($filters['status'] ?? '') == 'delivered' ? 'selected' : '' }}>Status: Delivered</option>
          <option value="read" {{ ($filters['status'] ?? '') == 'read' ? 'selected' : '' }}>Status: Read</option>
          <option value="failed" {{ ($filters['status'] ?? '') == 'failed' ? 'selected' : '' }}>Status: Failed</option>
          <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Status: Pending</option>
        </select>

        <!-- Filter Bulan -->
        <select id="filterMonth"
          class="bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block p-2 font-medium cursor-pointer">
          <option value="all" {{ ($filters['month'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua Bulan</option>
          @foreach([
              '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
              '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
              '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
          ] as $mVal => $mName)
              <option value="{{ $mVal }}" {{ ($filters['month'] ?? 'all') == $mVal ? 'selected' : '' }}>{{ $mName }}</option>
          @endforeach
        </select>

        <!-- Filter Tahun -->
        <select id="filterYear"
          class="bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block p-2 font-medium cursor-pointer">
          <option value="all" {{ ($filters['year'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua Tahun</option>
          @for ($y = date('Y') + 1; $y >= 2023; $y--)
              <option value="{{ $y }}" {{ ($filters['year'] ?? 'all') == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
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

    // Toast function for copying
    function showToastCopy(message) {
      let toast = document.getElementById('toast-copy');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast-copy';
        toast.className = 'toast-copy';
        toast.innerHTML = '<i class="fas fa-check-circle text-green-400"></i> <span class="msg"></span>';
        document.body.appendChild(toast);
      }
      toast.querySelector('.msg').innerText = message;
      toast.classList.add('show');
      setTimeout(() => {
        toast.classList.remove('show');
      }, 2000);
    }
    window.showToastCopy = showToastCopy;

    document.addEventListener("DOMContentLoaded", function () {

      // Cek dulu apakah DataTable sudah available untuk di-execute
      if (typeof $.fn.DataTable !== 'undefined') {
        initDatatable();
      } else {
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
              d.status = $('#filterStatus').val();
              d.month = $('#filterMonth').val();
              d.year = $('#filterYear').val();
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
                const datePart = date.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const timePart = date.toLocaleString('id-ID', { hour: '2-digit', minute: '2-digit' });
                return `
                  <div class="font-medium text-slate-800">${datePart}</div>
                  <div class="text-xs text-slate-400 mt-0.5"><i class="far fa-clock mr-1 text-[10px]"></i> ${timePart}</div>
                `;
              }
            },
            {
              data: null,
              render: function (data) {
                return `
                  <div class="font-semibold text-slate-800">${data.nama_customer || 'Guest / Terhapus'}</div>
                  <div class="text-xs text-slate-500 mt-0.5"><i class="fab fa-whatsapp text-green-500 mr-1"></i>${data.no_tujuan || '-'}</div>
                `;
              }
            },
            {
              data: 'jenis_pesan',
              render: function (data) {
                let formatted = data ? data.replace(/_/g, ' ').toUpperCase() : '-';
                return `<span class="px-2 py-0.5 text-[10px] font-bold tracking-wider text-slate-600 bg-slate-100 border border-slate-200 rounded-md">
                          ${formatted}
                        </span>`;
              }
            },
            {
              data: 'pesan',
              render: function (data, type, row) {
                let text = data || '-';
                if (row.error_message) {
                  text += `<div class="mt-2 text-xs text-red-500 p-2 bg-red-50 rounded-lg"><i class="fas fa-exclamation-triangle mr-1"></i> ${row.error_message}</div>`;
                }
                return `<div class="max-w-xs whitespace-normal break-words leading-relaxed text-slate-600 font-medium">${text}</div>`;
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
                    badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                    icon = 'fa-check';
                    break;
                  case 'delivered':
                    badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                    icon = 'fa-check-double';
                    break;
                  case 'read':
                    badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                    icon = 'fa-check-double text-emerald-600';
                    break;
                  case 'failed':
                  case 'error':
                    badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                    icon = 'fa-times';
                    break;
                  case 'pending':
                  case 'todo':
                    badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                    icon = 'fa-clock';
                    break;
                  default:
                    badgeClass = 'bg-slate-50 text-slate-700 border-slate-200';
                    icon = 'fa-info-circle';
                }
                return `<span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold border rounded-full ${badgeClass}">
                          <i class="fas ${icon} mr-1.5"></i> ${data.charAt(0).toUpperCase() + data.slice(1)}
                        </span>`;
              }
            },
            {
              data: 'qontak_broadcast_id',
              render: function (data) {
                if (!data) return '<span class="text-gray-400 italic text-xs bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100">Local Server</span>';
                let displayId = data.substr(data.length - 12).toUpperCase();
                return `
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs font-mono text-gray-600 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100 select-all" title="${data}">...${displayId}</span>
                    <button class="text-gray-400 hover:text-blue-500 copy-btn p-1 transition-colors" data-id="${data}" title="Salin ID Lengkap">
                      <i class="far fa-copy text-[11px]"></i>
                    </button>
                  </div>
                `;
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

        // Copy to clipboard handler using event delegation
        $('#whatsLogTable').on('click', '.copy-btn', function () {
          const id = $(this).attr('data-id');
          if (!id) return;

          navigator.clipboard.writeText(id).then(() => {
            showToastCopy("ID berhasil disalin!");
          }).catch(err => {
            console.error("Gagal menyalin ID:", err);
          });
        });

        // BIND FILTER STATUS, BULAN, TAHUN REFRESH PADA DATATABLES
        ['filterStatus', 'filterMonth', 'filterYear'].forEach(id => {
          const el = document.getElementById(id);
          if (el) {
            el.addEventListener('change', function () {
              if (table) table.ajax.reload();
            });
          }
        });

        // Fungsi Helper untuk Update Angka di Card Statistik
        function updateStatCards(stats) {
          if (!stats) return;

          const fields = ['total', 'pending', 'sent', 'delivered', 'read', 'failed'];
          fields.forEach(field => {
            const el = document.getElementById('stat-' + field);
            if (el && stats[field] !== undefined) {
              el.innerText = new Intl.NumberFormat('id-ID').format(stats[field]);
            }
          });
        }

        // Re-sync stats setiap kali tabel reload data
        $('#whatsLogTable').on('xhr.dt', function (e, settings, json, xhr) {
          if (json && json.stats) {
            updateStatCards(json.stats);
          }
        });

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
              body: JSON.stringify({
                limit: 100,
                status: $('#filterStatus').val(),
                month: $('#filterMonth').val(),
                year: $('#filterYear').val()
              })
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

      }
    });
  </script>
@endsection