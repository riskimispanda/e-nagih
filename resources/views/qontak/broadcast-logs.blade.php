@extends('layouts/contentNavbarLayout')

@section('title', 'Data Pelanggan')

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
  rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: {
            50: '#f8fafc',
            100: '#f1f5f9',
            200: '#e2e8f0',
            300: '#cbd5e1',
            400: '#94a3b8',
            500: '#64748b',
            600: '#475569',
            700: '#334155',
            800: '#1e293b',
            900: '#0f172a',
          },
          accent: {
            500: '#6366f1',
            600: '#4f46e5',
          }
        },
        fontFamily: {
          sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
        }
      }
    }
  }
</script>
<style>
  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background-color: #f8fafc;
    color: #1e293b;
  }

  .status-badge {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: capitalize;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .status-active {
    background: #dcfce7;
    color: #15803d;
  }

  .status-inactive {
    background: #fee2e2;
    color: #b91c1c;
  }

  .status-pending {
    background: #fef3c7;
    color: #b45309;
  }

  .status-other {
    background: #f1f5f9;
    color: #64748b;
  }

  .custom-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  .custom-table th {
    background: #f1f5f9;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    padding: 12px 24px;
    border-bottom: 1px solid #e2e8f0;
  }

  .custom-table td {
    padding: 16px 24px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
  }

  .card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  }

  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.2s;
    gap: 8px;
  }

  .btn-primary {
    background: #1e293b;
    color: white;
  }

  .btn-primary:hover {
    background: #0f172a;
  }

  .btn-secondary {
    background: white;
    border: 1px solid #e2e8f0;
    color: #475569;
  }

  .btn-secondary:hover {
    background: #f8fafc;
  }

  /* TomSelect Custom Styles */
  .ts-control {
    border-radius: 8px !important;
    border: 1px solid #e2e8f0 !important;
    background-color: #f8fafc !important;
    padding: 8px 12px !important;
    font-size: 14px !important;
    min-height: 40px !important;
    box-shadow: none !important;
  }

  .ts-control input {
    font-size: 14px !important;
  }

  .ts-dropdown {
    border-radius: 8px !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1) !important;
    margin-top: 4px !important;
    z-index: 2000 !important;
    background-color: white !important;
  }

  .ts-dropdown .active {
    background-color: #f1f5f9 !important;
    color: #0f172a !important;
  }

  .ts-wrapper.focus .ts-control {
    border-color: #6366f1 !important;
    ring: 2px rgba(99, 102, 241, 0.2) !important;
  }
</style>

@section('content')
  <div class="max-w-[1400px] mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Data Pelanggan</h1>
        <p class="text-xs font-medium text-slate-500 mt-1">Manajemen data pelanggan dan status layanan dari database</p>
      </div>
      <div class="flex items-center gap-3">
        <button onclick="openMaintenanceModal()"
          class="btn bg-yellow-200 text-yellow-800 hover:bg-yellow-100 hover:text-yellow-800">
          <i class="fas fa-tools"></i> Maintenance
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-6 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-slate-500 uppercase">Paket</label>
          <select id="mainFilterPaket" placeholder="Search Paket...">
            <option value="">All Paket</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-slate-500 uppercase">OLT</label>
          <select id="mainFilterOlt" placeholder="Search OLT...">
            <option value="">All OLT</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-slate-500 uppercase">ODC</label>
          <select id="mainFilterOdc" placeholder="Search ODC...">
            <option value="">All ODC</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-slate-500 uppercase">ODP</label>
          <select id="mainFilterOdp" placeholder="Search ODP...">
            <option value="">All ODP</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end mt-4 gap-2">
        <button onclick="resetFilters()" class="btn btn-secondary h-9 px-4">
          <i class="fas fa-undo"></i> Reset Filters
        </button>
        <button onclick="openBroadcastModal()" class="btn bg-success text-white h-9 px-6">
          <i class="fas fa-paper-plane"></i> Send Broadcast
        </button>
      </div>
    </div>

    <!-- Main Table -->
    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="custom-table">
          <thead>
            <tr>
              <th class="w-10 text-center"><input type="checkbox" id="selectAllCustomers"
                  onclick="toggleSelectAllCustomers(this)" class="rounded border-slate-300"></th>
              <th class="w-20 text-center">ID</th>
              <th>Pelanggan</th>
              <th>Alamat</th>
              <th>ODP</th>
              <th>Paket</th>
            </tr>
          </thead>
          <tbody id="customerTableBody">
            <!-- Loaded via JS -->
          </tbody>
        </table>
      </div>

      <!-- Loading & Empty States -->
      <div id="tableStates"></div>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer"
      class="mt-6 flex flex-col sm:flex-row justify-between items-center py-4 px-4 bg-white rounded-xl border border-slate-100 shadow-sm">
      <div class="flex items-center gap-6 mb-4 sm:mb-0">
        <div class="text-sm text-slate-500">
          <span id="paginationInfo">Memuat data...</span>
        </div>
        <div class="flex items-center gap-2 border-l border-slate-200 pl-6">
          <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tampilkan</label>
          <select id="limitFilter" onchange="loadCustomers(1)"
            class="h-8 px-2 text-xs font-semibold border border-slate-200 rounded-lg bg-slate-50/50 focus:ring-2 focus:ring-accent-500/20 outline-none">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
      </div>
      <nav aria-label="Pagination">
        <ul class="flex items-center gap-1" id="pagination">
          <!-- Loaded via JS -->
        </ul>
      </nav>
    </div>
  </div>

  <script>
    let currentPage = 1;
    let tsPaket, tsOlt, tsOdc, tsOdp;

    document.addEventListener('DOMContentLoaded', function () {
      initTomSelects();
      loadPackages();
      loadMainOlts();
      loadCustomers();
    });

    function initTomSelects() {
      const config = {
        create: false,
        allowEmptyOption: true,
        maxOptions: 1000,
        render: {
          no_results: function (data, escape) {
            return '<div class="no-results p-2 text-xs text-slate-400">No results found for "' + escape(data.input) + '"</div>';
          }
        }
      };

      tsPaket = new TomSelect('#mainFilterPaket', {
        ...config,
        onChange: function (value) {
          loadCustomers(1);
        }
      });

      tsOlt = new TomSelect('#mainFilterOlt', {
        ...config,
        onChange: function (value) {
          loadMainOdcs();
          loadCustomers(1);
        }
      });

      tsOdc = new TomSelect('#mainFilterOdc', {
        ...config,
        onChange: function (value) {
          loadMainOdps();
          loadCustomers(1);
        }
      });

      tsOdp = new TomSelect('#mainFilterOdp', {
        ...config,
        onChange: function (value) {
          loadCustomers(1);
        }
      });
    }

    function updateTSOptions(ts, items, placeholder) {
      if (!ts) return;
      const val = ts.getValue();
      ts.clearOptions();
      ts.addOption({ value: '', text: placeholder });
      if (items && items.length > 0) {
        items.forEach(it => {
          ts.addOption({ value: it.id, text: it.name });
        });
      }
      ts.refreshOptions(false);
      ts.setValue(val, true); // Try to restore value if still exists
    }

    function loadCustomers(page = 1) {
      const tbody = document.getElementById('customerTableBody');
      const states = document.getElementById('tableStates');
      const info = document.getElementById('paginationInfo');

      tbody.classList.add('opacity-40');
      states.innerHTML = `
                  <div class="flex flex-col items-center py-20">
                    <div class="w-8 h-8 border-4 border-slate-200 border-t-accent-600 rounded-full animate-spin mb-3"></div>
                    <p class="text-sm font-medium text-slate-500">Memuat data pelanggan...</p>
                  </div>
                `;

      const params = {
        per_page: document.getElementById('limitFilter').value,
        page: page,
        paket_id: tsPaket ? tsPaket.getValue() : '',
        olt_id: tsOlt ? tsOlt.getValue() : '',
        odc_id: tsOdc ? tsOdc.getValue() : '',
        odp_id: tsOdp ? tsOdp.getValue() : ''
      };

      fetch(`/data/customer?${new URLSearchParams(params)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(res => res.json())
        .then(data => {
          tbody.classList.remove('opacity-40');
          states.innerHTML = '';

          if (data.success) {
            renderTable(data.data);
            updatePagination(data.pagination, page);
            currentPage = page;
          } else {
            states.innerHTML = `<div class="p-8 text-center text-red-500">${data.message || 'Gagal memuat data'}</div>`;
          }
        })
        .catch(err => {
          tbody.classList.remove('opacity-40');
          states.innerHTML = `<div class="p-8 text-center text-red-500">Error: ${err.message}</div>`;
        });
    }

    function renderTable(customers) {
      const tbody = document.getElementById('customerTableBody');
      tbody.innerHTML = '';

      if (!customers || customers.length === 0) {
        tbody.innerHTML = `
                    <tr>
                      <td colspan="6" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center opacity-40">
                          <i class="fas fa-users text-4xl mb-3 text-blue-600 text-bold"></i>
                          <p class="font-semibold">Data tidak ditemukan</p>
                          <p class="text-sm">Coba sesuaikan pencarian Anda</p>
                        </div>
                      </td>
                    </tr>
                  `;
        return;
      }

      customers.forEach(customer => {
        const statusId = parseInt(customer.status_id);
        let statusClass = 'status-other';
        if (statusId === 3) statusClass = 'status-active';
        else if (statusId === 9) statusClass = 'status-inactive';
        else if (statusId === 4) statusClass = 'status-pending';

        const row = document.createElement('tr');
        row.className = 'hover:bg-slate-50/50 transition-colors';
        row.innerHTML = `
                    <td class="text-center"><input type="checkbox" class="customer-row-checkbox rounded border-slate-300" value="${customer.id}" onchange="updateSelectAllState()"></td>
                    <td class="text-center"><code class="text-[11px] font-mono text-slate-400">#${customer.id}</code></td>
                    <td>
                      <div class="font-semibold text-slate-900">${customer.nama_customer || 'N/A'}</div>
                      <div class="text-xs text-slate-500 mt-0.5"><i class="fas fa-phone mr-1 text-[10px]"></i> ${customer.no_hp || '-'}</div>
                    </td>
                    <td class="max-w-[250px] truncate text-xs text-slate-600">${customer.alamat || '-'}</td>
                    <td>
                      <div class="text-[11px] font-bold text-slate-700 uppercase">${customer.odp?.nama_odp || '-'}</div>
                      <div class="text-[9px] text-slate-400 mt-0.5">${customer.odp?.odc?.nama_odc || '-'} / ${customer.odp?.odc?.olt?.nama_lokasi || '-'}</div>
                    </td>
                    <td>
                      <span class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                        <i class="fas fa-box"></i> ${customer.paket?.nama_paket || 'N/A'}
                      </span>
                    </td>
                  `;
        tbody.appendChild(row);
      });
    }

    function updatePagination(pagination, page) {
      const info = document.getElementById('paginationInfo');
      const list = document.getElementById('pagination');

      if (pagination) {
        info.innerHTML = `Menampilkan <span class="font-semibold text-slate-900">${pagination.from || 0}-${pagination.to || 0}</span> dari <span class="font-semibold text-slate-900">${pagination.total}</span> pelanggan`;

        list.innerHTML = '';
        if (pagination.last_page <= 1) return;

        const btnClass = "inline-flex items-center justify-center w-8 h-8 text-xs font-semibold rounded-lg transition-all";
        const activeClass = "bg-slate-900 text-white";
        const inactiveClass = "text-slate-500 hover:bg-slate-100";

        // Simple prev/next logic
        if (page > 1) {
          list.innerHTML += `<li><button onclick="loadCustomers(${page - 1})" class="${btnClass} ${inactiveClass}"><i class="fas fa-chevron-left"></i></button></li>`;
        }

        let start = Math.max(1, page - 2);
        let end = Math.min(pagination.last_page, start + 4);
        if (end - start < 4) start = Math.max(1, end - 4);

        for (let i = start; i <= end; i++) {
          list.innerHTML += `<li><button onclick="loadCustomers(${i})" class="${btnClass} ${i === page ? activeClass : inactiveClass}">${i}</button></li>`;
        }

        if (page < pagination.last_page) {
          list.innerHTML += `<li><button onclick="loadCustomers(${page + 1})" class="${btnClass} ${inactiveClass}"><i class="fas fa-chevron-right"></i></button></li>`;
        }
      }
    }

    // --- Main Filter Logic ---
    function loadPackages() {
      fetch('/paket/data?per_page=100')
        .then(res => res.json())
        .then(data => {
          if (tsPaket) {
            const items = data.success ? data.data.map(it => ({ id: it.id, name: it.nama_paket })) : [];
            updateTSOptions(tsPaket, items, 'All Paket');
          }
        });
    }

    function loadMainOlts() {
      fetch('/maintenance/olts')
        .then(res => res.json())
        .then(data => {
          updateTSOptions(tsOlt, data.success ? data.data : [], 'All OLT');
        });
    }

    function loadMainOdcs() {
      const oltId = tsOlt ? tsOlt.getValue() : '';

      // Clear children
      if (tsOdc) {
        tsOdc.clearOptions();
        tsOdc.addOption({ value: '', text: 'All ODC' });
        tsOdc.setValue('', true);
        tsOdc.refreshOptions(false);
      }
      if (tsOdp) {
        tsOdp.clearOptions();
        tsOdp.addOption({ value: '', text: 'All ODP' });
        tsOdp.setValue('', true);
        tsOdp.refreshOptions(false);
      }

      if (!oltId) return;
      fetch(`/maintenance/odcs?olt_id=${oltId}`)
        .then(res => res.json())
        .then(data => {
          updateTSOptions(tsOdc, data.success ? data.data : [], 'All ODC');
        });
    }

    function loadMainOdps() {
      const odcId = tsOdc ? tsOdc.getValue() : '';

      if (tsOdp) {
        tsOdp.clearOptions();
        tsOdp.addOption({ value: '', text: 'All ODP' });
        tsOdp.setValue('', true);
        tsOdp.refreshOptions(false);
      }

      if (!odcId) return;
      fetch(`/maintenance/odps?odc_id=${odcId}`)
        .then(res => res.json())
        .then(data => {
          updateTSOptions(tsOdp, data.success ? data.data : [], 'All ODP');
        });
    }

    function resetFilters() {
      document.getElementById('limitFilter').value = '25';
      if (tsPaket) tsPaket.setValue('');
      if (tsOlt) tsOlt.setValue('');
      if (tsOdc) tsOdc.setValue('');
      if (tsOdp) tsOdp.setValue('');
      loadCustomers(1);
    }

    // --- Checkbox Logic ---
    function toggleSelectAllCustomers(source) {
      const checkboxes = document.querySelectorAll('.customer-row-checkbox');
      checkboxes.forEach(cb => cb.checked = source.checked);
    }

    function updateSelectAllState() {
      const selectAll = document.getElementById('selectAllCustomers');
      const checkboxes = document.querySelectorAll('.customer-row-checkbox');
      const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
      if (selectAll) selectAll.checked = allChecked;
    }

    // --- Maintenance Logic ---
    let allMaintenanceCustomers = [];
    let filteredMaintenanceCustomers = [];
    let selectedCustomerData = new Map();
    let maintenancePageSize = 50;
    let maintenanceCurrentPage = 1;

    function openBroadcastModal() {
      const selectedCheckboxes = document.querySelectorAll('.customer-row-checkbox:checked');
      if (selectedCheckboxes.length === 0) {
        showToast('Pilih pelanggan terlebih dahulu melalui checkbox di tabel', 'warning');
        return;
      }

      // Update Filter Summary
      const summary = document.getElementById('broadcastFilterSummary');
      const getTSLabel = (ts, defaultText) => {
        if (!ts) return defaultText;
        const val = ts.getValue();
        if (!val) return defaultText;
        return ts.options[val]?.text || defaultText;
      };

      const filters = [
        { label: 'Paket', value: getTSLabel(tsPaket, 'All Paket') },
        { label: 'OLT', value: getTSLabel(tsOlt, 'All OLT') },
        { label: 'ODC', value: getTSLabel(tsOdc, 'All ODC') },
        { label: 'ODP', value: getTSLabel(tsOdp, 'All ODP') }
      ];

      summary.innerHTML = filters.map(f => `
        <div class="flex flex-col">
          <span class="text-[9px] text-indigo-400 font-bold uppercase tracking-tight">${f.label}</span>
          <span class="text-[10px] text-indigo-700 font-semibold truncate">${f.value || 'All'}</span>
        </div>
      `).join('');

      document.getElementById('selectedCountText').innerText = `${selectedCheckboxes.length} Pelanggan Terpilih`;
      document.getElementById('broadcastTemplate').value = '';
      document.getElementById('broadcastModal').classList.remove('hidden');
    }

    function closeBroadcastModal() {
      document.getElementById('broadcastModal').classList.add('hidden');
    }

    function confirmTemplateBroadcast() {
      const template = document.getElementById('broadcastTemplate').value;
      if (!template) {
        showToast('Silakan pilih template terlebih dahulu', 'warning');
        return;
      }

      const selectedCheckboxes = document.querySelectorAll('.customer-row-checkbox:checked');
      const customers = [];
      
      // We need to fetch the customer data for selected IDs
      // For now, we'll collect the IDs and then send them in chunks like maintenance
      selectedCheckboxes.forEach(cb => {
        const row = cb.closest('tr');
        const name = row.querySelector('div.font-semibold').innerText;
        const number = row.querySelector('div.text-xs').innerText.replace(/[^0-9]/g, '');
        customers.push({ id: cb.value, name: name, number: number });
      });

      if (confirm(`Kirim broadcast template "${template}" ke ${customers.length} pelanggan?`)) {
        startTemplateBroadcastProcess(customers, template);
      }
    }

    async function startTemplateBroadcastProcess(recipients, template) {
      const chunks = [];
      const chunkSize = 20;
      for (let i = 0; i < recipients.length; i += chunkSize) {
        chunks.push(recipients.slice(i, i + chunkSize));
      }

      // Show progress in modal
      const progressContainer = document.getElementById('broadcastProgressContainer');
      const progressBar = document.getElementById('broadcastProgressBar');
      const progressCount = document.getElementById('broadcastProgressCount');
      const progressPercent = document.getElementById('broadcastProgressPercent');
      const statusText = document.getElementById('broadcastStatusText');
      const btnConfirm = document.querySelector('button[onclick="confirmTemplateBroadcast()"]');
      const btnCancel = document.querySelector('button[onclick="closeBroadcastModal()"]');

      progressContainer.classList.remove('hidden');
      btnConfirm.disabled = true;
      btnConfirm.classList.add('opacity-50', 'cursor-not-allowed');
      btnCancel.classList.add('hidden');

      let totalSent = 0;
      let totalFailed = 0;

      for (let i = 0; i < chunks.length; i++) {
        const progress = Math.round(((i + 1) / chunks.length) * 100);
        const processedCount = Math.min((i + 1) * chunkSize, recipients.length);
        
        // Update UI
        progressBar.style.width = `${progress}%`;
        progressCount.innerText = `${processedCount}/${recipients.length}`;
        progressPercent.innerText = `${progress}%`;
        statusText.innerText = `Mengirim chunk ${i + 1} dari ${chunks.length}...`;

        try {
          const response = await fetch('/qontak/broadcast/template', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              recipients: chunks[i],
              template_name: template
            })
          });

          const result = await response.json();
          totalSent += (result.sent_count || chunks[i].length);
        } catch (error) {
          console.error('Broadcast error:', error);
          totalFailed += chunks[i].length;
        }
      }

      // Reset and close
      statusText.innerText = 'Pengiriman selesai!';
      statusText.classList.remove('animate-pulse');
      
      setTimeout(() => {
        closeBroadcastModal();
        progressContainer.classList.add('hidden');
        btnConfirm.disabled = false;
        btnConfirm.classList.remove('opacity-50', 'cursor-not-allowed');
        btnCancel.classList.remove('hidden');
        
        showToast(result.message || `Selesai! Berhasil: ${totalSent}, Gagal: ${totalFailed}`, totalFailed > 0 ? 'warning' : 'success');
        
        // Uncheck all
        document.getElementById('selectAllCustomers').checked = false;
        toggleSelectAllCustomers(document.getElementById('selectAllCustomers'));
      }, 1500);
    }

    function openMaintenanceModal() {
      const m = document.getElementById('sendMaintenanceModal');
      m.classList.remove('hidden');
      document.getElementById('maintenanceSearch').value = '';
      document.getElementById('filterMaintenanceOlt').value = '';
      document.getElementById('filterMaintenanceOdc').value = '';
      document.getElementById('filterMaintenanceOdp').value = '';
      selectedCustomerData.clear();
      maintenanceCurrentPage = 1;
      loadMaintenanceOlts();
      renderMaintenanceRecipientsV2();
    }

    function loadMaintenanceOlts() {
      fetch('/maintenance/olts')
        .then(res => res.json())
        .then(data => {
          const select = document.getElementById('filterMaintenanceOlt');
          select.innerHTML = '<option value="">All OLT</option>';
          if (data.success) {
            data.data.forEach(it => {
              select.innerHTML += `<option value="${it.id}">${it.name}</option>`;
            });
          }
        });
    }

    function loadMaintenanceOdcs() {
      const oltId = document.getElementById('filterMaintenanceOlt').value;
      const select = document.getElementById('filterMaintenanceOdc');
      select.innerHTML = '<option value="">All ODC</option>';
      document.getElementById('filterMaintenanceOdp').innerHTML = '<option value="">All ODP</option>';
      if (!oltId) { renderMaintenanceRecipientsV2(); return; }
      fetch(`/maintenance/odcs?olt_id=${oltId}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            data.data.forEach(it => {
              select.innerHTML += `<option value="${it.id}">${it.name}</option>`;
            });
          }
          renderMaintenanceRecipientsV2();
        });
    }

    function loadMaintenanceOdps() {
      const odcId = document.getElementById('filterMaintenanceOdc').value;
      const select = document.getElementById('filterMaintenanceOdp');
      select.innerHTML = '<option value="">All ODP</option>';
      if (!odcId) { renderMaintenanceRecipientsV2(); return; }
      fetch(`/maintenance/odps?odc_id=${odcId}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            data.data.forEach(it => {
              select.innerHTML += `<option value="${it.id}">${it.name}</option>`;
            });
          }
          renderMaintenanceRecipientsV2();
        });
    }

    function closeMaintenanceModal() {
      document.getElementById('sendMaintenanceModal').classList.add('hidden');
    }

    function renderMaintenanceRecipientsV2() {
      const overlay = document.getElementById('maintenanceLoadingOverlay');
      overlay.classList.remove('hidden');
      document.getElementById('maintenanceLoadingText').innerHTML = '<div class="flex flex-col items-center gap-3"><div class="w-8 h-8 border-4 border-slate-200 border-t-accent-600 rounded-full animate-spin"></div><p class="text-sm font-semibold text-slate-600">Loading customers...</p></div>';

      const oltId = document.getElementById('filterMaintenanceOlt')?.value || '';
      const odcId = document.getElementById('filterMaintenanceOdc')?.value || '';
      const odpId = document.getElementById('filterMaintenanceOdp')?.value || '';

      fetch(`/maintenance-customers?olt_id=${oltId}&odc_id=${odcId}&odp_id=${odpId}`)
        .then(res => res.json())
        .then(data => {
          overlay.classList.add('hidden');
          allMaintenanceCustomers = (data && data.success && Array.isArray(data.data)) ? data.data : [];
          filteredMaintenanceCustomers = [...allMaintenanceCustomers];
          maintenanceCurrentPage = 1;
          displayMaintenanceCustomers();
        })
        .catch(err => {
          overlay.classList.add('hidden');
          showToast('Failed to load customers', 'error');
        });
    }

    function displayMaintenanceCustomers() {
      const tbody = document.getElementById('maintenanceRecipientsTableBody');
      const totalEl = document.getElementById('maintenanceTotal');
      const pageDisplay = document.getElementById('currentPageDisplay');
      const btnPrev = document.getElementById('btnPrevPage');
      const btnNext = document.getElementById('btnNextPage');

      const totalItems = filteredMaintenanceCustomers.length;
      const totalPages = Math.ceil(totalItems / maintenancePageSize) || 1;

      if (maintenanceCurrentPage > totalPages) maintenanceCurrentPage = totalPages;
      if (maintenanceCurrentPage < 1) maintenanceCurrentPage = 1;

      const start = (maintenanceCurrentPage - 1) * maintenancePageSize;
      const end = start + maintenancePageSize;
      const items = filteredMaintenanceCustomers.slice(start, end);

      totalEl.textContent = `${totalItems} total customers`;
      pageDisplay.textContent = `Page ${maintenanceCurrentPage} / ${totalPages}`;
      btnPrev.disabled = maintenanceCurrentPage === 1;
      btnNext.disabled = maintenanceCurrentPage === totalPages;

      if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">No customers found</td></tr>';
        updateSelectedUI();
        return;
      }

      tbody.innerHTML = items.map(it => {
        const isSelected = selectedCustomerData.has(String(it.id));
        const status = (it.status || 'never').toLowerCase();

        let badgeClass = 'bg-slate-100 text-slate-500';
        if (status.includes('sent') || status.includes('done')) badgeClass = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
        if (status.includes('fail')) badgeClass = 'bg-red-50 text-red-600 border border-red-100';

        return `
                                                      <tr class="transition-colors ${isSelected ? 'bg-accent-50/30' : 'hover:bg-slate-50/50'}">
                                                        <td class="px-6 py-3"><input type="checkbox" class="customer-checkbox rounded" data-id="${it.id}" data-name="${it.name}" data-number="${it.number}" ${isSelected ? 'checked' : ''} onchange="toggleCustomerSelection(this)"></td>
                                                        <td class="px-6 py-3">
                                                          <div class="text-xs font-semibold text-slate-900">${it.name}</div>
                                                          <div class="text-[10px] text-slate-400">ID: ${it.id}</div>
                                                        </td>
                                                        <td class="px-6 py-3 text-xs font-mono text-slate-600">${it.number}</td>
                                                        <td class="px-6 py-3"><span class="status-badge ${badgeClass}">${status}</span></td>
                                                      </tr>
                                                    `;
      }).join('');

      updateSelectedUI();
    }

    function changeMaintenancePage(delta) {
      maintenanceCurrentPage += delta;
      displayMaintenanceCustomers();
    }

    function toggleCustomerSelection(checkbox) {
      const id = String(checkbox.getAttribute('data-id'));
      const name = checkbox.getAttribute('data-name');
      const number = checkbox.getAttribute('data-number');
      if (checkbox.checked) selectedCustomerData.set(id, { id, name, number });
      else selectedCustomerData.delete(id);
      updateSelectedUI();
      checkbox.closest('tr').classList.toggle('bg-accent-50/30', checkbox.checked);
    }
    function filterMaintenanceCustomers() {
      const query = document.getElementById('maintenanceSearch').value.toLowerCase();
      filteredMaintenanceCustomers = allMaintenanceCustomers.filter(c =>
        (c.name && c.name.toLowerCase().includes(query)) ||
        (c.number && String(c.number).includes(query)) ||
        (c.id && String(c.id).includes(query))
      );
      maintenanceCurrentPage = 1;
      displayMaintenanceCustomers();
    }

    function toggleAllMaintenance(source) {
      if (source.checked) {
        // Select ALL customers from the currently filtered list (across all pages)
        filteredMaintenanceCustomers.forEach(it => {
          selectedCustomerData.set(String(it.id), {
            id: String(it.id),
            name: it.name,
            number: it.number
          });
        });
      } else {
        // Deselect everything
        selectedCustomerData.clear();
      }

      // Re-render current page to show the checked states
      displayMaintenanceCustomers();
    }

    function updateSelectedUI() {
      const selectedCount = selectedCustomerData.size;
      const countEl = document.getElementById('maintenanceSelectedCount');
      const btn = document.getElementById('btnBroadcastMaintenance');

      if (countEl) countEl.textContent = `${selectedCount} selected`;
      if (btn) btn.disabled = selectedCount === 0;

      // Update Select All checkbox state for the current page
      const pageCheckboxes = document.querySelectorAll('.customer-checkbox');
      const allCheckedOnPage = pageCheckboxes.length > 0 && Array.from(pageCheckboxes).every(cb => cb.checked);
      const selectAll = document.getElementById('selectAllMaintenance');
      if (selectAll) selectAll.checked = allCheckedOnPage;
    }

    function sendMaintenanceBroadcast() {
      const recipients = Array.from(selectedCustomerData.values());

      if (recipients.length === 0) {
        showToast('Please select at least one customer', 'warning');
        return;
      }

      if (!confirm(`Are you sure you want to send maintenance broadcast to ${recipients.length} customers?`)) {
        return;
      }

      const btn = document.getElementById('btnBroadcastMaintenance');
      const loadingOverlay = document.getElementById('maintenanceLoadingOverlay');
      const loadingText = document.getElementById('maintenanceLoadingText');

      if (btn) btn.disabled = true;
      if (loadingOverlay) {
        loadingOverlay.classList.remove('hidden');
      }

      // Chunking logic to prevent timeouts
      const chunkSize = 20;
      const chunks = [];
      for (let i = 0; i < recipients.length; i += chunkSize) {
        chunks.push(recipients.slice(i, i + chunkSize));
      }

      let totalSent = 0;
      let totalSkipped = 0;
      let totalFailed = 0;

      async function processChunks() {
        for (let i = 0; i < chunks.length; i++) {
          const chunk = chunks[i];
          const progress = Math.round(((i) / chunks.length) * 100);

          if (loadingText) {
            loadingText.innerHTML = `
                                                            <div class="w-64">
                                                              <div class="text-center mb-2 font-bold text-slate-900">Sending Broadcast...</div>
                                                              <div class="w-full bg-slate-200 rounded-full h-1.5 mb-1">
                                                                <div class="bg-accent-600 h-1.5 rounded-full transition-all duration-300" style="width: ${progress}%"></div>
                                                              </div>
                                                              <div class="flex justify-between text-xs text-gray-500">
                                                                <span>Progress: ${progress}%</span>
                                                                <span>Chunk ${i + 1}/${chunks.length}</span>
                                                              </div>
                                                              <div class="mt-2 text-xs text-center text-gray-400">Processing ${totalSent + totalSkipped + totalFailed}/${recipients.length} recipients</div>
                                                            </div>
                                                          `;
          }

          try {
            const response = await fetch('/maintenance/broadcast', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify({ recipients: chunk })
            });

            const data = await response.json();

            if (data.success) {
              const results = data.data || [];
              totalSent += results.filter(r => r.sent).length;
              totalSkipped += results.filter(r => r.status === 'already_sent').length;
              totalFailed += results.filter(r => !r.sent && r.status !== 'already_sent').length;
            } else {
              totalFailed += chunk.length;
            }
          } catch (err) {
            console.error('Chunk error:', err);
            totalFailed += chunk.length;
          }
        }

        // Final finish
        if (loadingOverlay) loadingOverlay.classList.add('hidden');
        if (btn) btn.disabled = false;

        let msg = `Broadcast complete. Sent: ${totalSent}.`;
        if (totalSkipped > 0) msg += ` Skipped: ${totalSkipped}.`;
        if (totalFailed > 0) msg += ` Failed: ${totalFailed}.`;

        showToast(msg, totalFailed > 0 ? 'warning' : 'success');

        selectedCustomerData.clear();
        renderMaintenanceRecipientsV2();
      }

      processChunks();
    }

    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      const bgClass = type === 'success' ? 'bg-green-500' : (type === 'warning' ? 'bg-yellow-500' : 'bg-red-500');
      const iconClass = type === 'success' ? 'fa-check-circle' : (type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle');

      toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-2xl z-[100000] text-white animate-slide-up flex items-center ${bgClass}`;
      toast.innerHTML = `
                                                      <i class="fas ${iconClass} mr-3 text-xl"></i>
                                                      <span class="font-medium">${message}</span>
                                                    `;
      document.body.appendChild(toast);
      setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
        setTimeout(() => toast.remove(), 500);
      }, 4000);
    }
  </script>
  <!-- Maintenance modal (Modernized) -->
  <div id="sendMaintenanceModal" class="hidden fixed inset-0 z-[9999] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px]" onclick="closeMaintenanceModal()"></div>
      <div
        class="relative bg-white rounded-xl shadow-xl w-full max-w-4xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-900">Maintenance Broadcast</h3>
            <p class="text-[11px] text-slate-400">Select customers and send maintenance notifications</p>
          </div>
          <button onclick="closeMaintenanceModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
          <!-- Filter Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">OLT</label>
              <select id="filterMaintenanceOlt" onchange="loadMaintenanceOdcs()"
                class="w-full h-9 px-3 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-500/20 bg-slate-50/50 font-semibold">
                <option value="">All OLT</option>
              </select>
            </div>
            <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ODC</label>
              <select id="filterMaintenanceOdc" onchange="loadMaintenanceOdps()"
                class="w-full h-9 px-3 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-500/20 bg-slate-50/50 font-semibold">
                <option value="">All ODC</option>
              </select>
            </div>
            <div class="space-y-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ODP</label>
              <select id="filterMaintenanceOdp" onchange="renderMaintenanceRecipientsV2()"
                class="w-full h-9 px-3 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-500/20 bg-slate-50/50 font-semibold">
                <option value="">All ODP</option>
              </select>
            </div>
          </div>

          <!-- Actions Bar -->
          <div class="flex flex-col md:flex-row items-center gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
            <div class="relative flex-1 w-full">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
              <input type="text" id="maintenanceSearch" onkeyup="filterMaintenanceCustomers()"
                class="w-full h-10 pl-9 pr-3 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent-500/20"
                placeholder="Search by name or phone...">
            </div>
            <div class="flex items-center gap-4 flex-shrink-0">
              <div class="text-right">
                <div id="maintenanceSelectedCount" class="text-xs font-bold text-slate-900">0 selected</div>
                <div id="maintenanceTotal" class="text-[10px] text-slate-400 uppercase font-bold tracking-tighter">0 total
                  customers</div>
              </div>
              <button id="btnBroadcastMaintenance" onclick="sendMaintenanceBroadcast()"
                class="btn btn-accent h-10 px-6 disabled:opacity-50" disabled>
                <i class="fas fa-paper-plane"></i> Send Broadcast
              </button>
            </div>
          </div>

          <!-- Recipients Table -->
          <div class="card overflow-hidden border-slate-100">
            <div class="max-h-96 overflow-y-auto">
              <table class="custom-table w-full">
                <thead class="sticky top-0 z-10 bg-slate-100 shadow-sm">
                  <tr>
                    <th class="w-10 px-4 py-3"><input type="checkbox" id="selectAllMaintenance"
                        onclick="toggleAllMaintenance(this)" class="rounded border-slate-300"></th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Status</th>
                  </tr>
                </thead>
                <tbody id="maintenanceRecipientsTableBody" class="divide-y divide-slate-50">
                  <!-- JS Injection -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- Footer/Pagination -->
          <div class="flex items-center justify-between pt-4">
            <div id="maintenancePagination" class="flex items-center gap-2">
              <button onclick="changeMaintenancePage(-1)" id="btnPrevPage"
                class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-30">
                <i class="fas fa-chevron-left text-[10px]"></i>
              </button>
              <span id="currentPageDisplay"
                class="text-xs font-bold text-slate-600 px-3 py-1 bg-slate-50 rounded-lg border border-slate-100 uppercase tracking-wider">Page
                1 / 1</span>
              <button onclick="changeMaintenancePage(1)" id="btnNextPage"
                class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-30">
                <i class="fas fa-chevron-right text-[10px]"></i>
              </button>
            </div>
            <button onclick="closeMaintenanceModal()" class="btn btn-secondary h-9">Close</button>
          </div>
        </div>

        <!-- Progress Overlay -->
        <div id="maintenanceLoadingOverlay"
          class="hidden absolute inset-0 bg-white/80 backdrop-blur-[2px] z-[60] flex items-center justify-center">
          <div id="maintenanceLoadingText" class="w-full max-w-sm px-8">
            <!-- Dynamic Content -->
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Broadcast Template Modal -->
  <div id="broadcastModal" class="hidden fixed inset-0 z-[9999] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBroadcastModal()"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h3 class="font-bold text-slate-900">Send Template Broadcast</h3>
            <p class="text-[11px] text-slate-400">Pilih template promosi untuk dikirim</p>
          </div>
          <button onclick="closeBroadcastModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>
        
        <div class="p-6 space-y-6">
          <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 flex flex-col gap-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                <i class="fas fa-users"></i>
              </div>
              <div>
                <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Target Pelanggan</div>
                <div id="selectedCountText" class="text-sm font-bold text-indigo-900">0 Pelanggan Terpilih</div>
              </div>
            </div>
            <div id="broadcastFilterSummary" class="grid grid-cols-2 gap-2 pt-2 border-t border-indigo-100/50">
              <!-- JS injection -->
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider ml-1">Pilih Template Promosi</label>
            <select id="broadcastTemplate" class="w-full h-11 px-4 text-sm border border-slate-200 rounded-xl focus:ring-4 focus:ring-accent-500/10 focus:border-accent-500 outline-none transition-all bg-slate-50/50 font-semibold">
              <option value="">-- Pilih Template --</option>
              <option value="promosi_10mbps_120">promosi_10mbps_120</option>
              <option value="promosi_10mbps_115">promosi_10mbps_115</option>
              <option value="promosi_20mbps_165">promosi_20mbps_165</option>
              <option value="promosi_30mbps_185">promosi_30mbps_185</option>
              <option value="promosi_40mbps_225">promosi_40mbps_225</option>
              <option value="promosi_50mbps_275">promosi_50mbps_275</option>
            </select>
          </div>

          <!-- Progress Section -->
          <div id="broadcastProgressContainer" class="hidden space-y-3 pt-2">
            <div class="flex justify-between items-end">
              <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sending Progress</div>
              <div class="text-right">
                <span id="broadcastProgressCount" class="text-sm font-bold text-slate-700">0/0</span>
                <span id="broadcastProgressPercent" class="text-[10px] font-bold text-accent-600 ml-1">0%</span>
              </div>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200/50">
              <div id="broadcastProgressBar" class="bg-accent-600 h-full rounded-full transition-all duration-500 w-0 shadow-[0_0_8px_rgba(99,102,241,0.4)]"></div>
            </div>
            <div id="broadcastStatusText" class="text-[10px] text-center text-slate-400 font-medium animate-pulse italic">Memulai pengiriman...</div>
          </div>

          <div class="flex gap-3 pt-2">
            <button onclick="closeBroadcastModal()" class="flex-1 h-11 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50 transition-all">
              Batal
            </button>
            <button onclick="confirmTemplateBroadcast()" class="flex-[2] h-11 bg-success text-white rounded-xl font-bold shadow-lg shadow-green-500/20 hover:bg-green-600 transition-all flex items-center justify-center gap-2">
              <i class="fas fa-paper-plane"></i>
              Kirim Broadcast
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="loadingToast" class="hidden fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[10000]">
    <div class="bg-white/90 backdrop-blur-md p-8 rounded-3xl shadow-2xl border border-white flex flex-col items-center min-w-[200px]">
      <div class="w-12 h-12 border-4 border-slate-100 border-t-accent-600 rounded-full animate-spin mb-4"></div>
      <div id="loadingToastText" class="text-sm font-bold text-slate-800">Memproses...</div>
    </div>
  </div>
@endsection