@extends('layouts.contentNavbarLayout')

@section('title', 'Rencana Anggaran Biaya')

@section('page-style')
  <!-- Tailwind CSS CDN with Preflight Disabled -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      corePlugins: {
        preflight: false
      }
    };
  </script>
  <style>
    /* ====== Kartu ringkasan (hover lift ala dashboard) ====== */
    .kpi-card {
      transition: transform .2s cubic-bezier(.4, 0, .2, 1), box-shadow .2s cubic-bezier(.4, 0, .2, 1),
        border-color .15s ease, background-color .15s ease;
    }

    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .05), 0 4px 6px -4px rgba(0, 0, 0, .025);
    }

    /* ====== Select dengan panah kustom ====== */
    .filter-select {
      -webkit-appearance: none;
      appearance: none;
      padding-right: 2rem !important;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 0.7rem center;
      background-size: 0.85rem;
    }

    /* ====== Tabel DataTables ====== */
    #rabTable.dataTable {
      width: 100% !important;
      margin: 0 !important;
    }

    #rabTable.dataTable thead th {
      padding: .75rem 1rem;
      font-size: .69rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .04em;
      color: #64748b;
      background-color: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
      vertical-align: middle;
    }

    #rabTable.dataTable thead th.sorting,
    #rabTable.dataTable thead th.sorting_asc,
    #rabTable.dataTable thead th.sorting_desc {
      padding-right: 1.75rem;
    }

    #rabTable.dataTable tbody td {
      padding: .85rem 1rem;
      font-size: .78rem;
      color: #334155;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: middle;
    }

    #rabTable.dataTable.no-footer {
      border-bottom: none;
    }

    #rabTable.dataTable tbody tr:hover {
      background-color: #f8fafc;
    }

    #rabTable .amount-cell {
      font-variant-numeric: tabular-nums;
      white-space: nowrap;
    }

    /* ======
       Kontrol DataTables (length, search, info, pagination).
       Di-scope dengan #rabTable_wrapper karena CSS bawaan plugin memakai
       selector .dataTables_wrapper ... (specificity lebih tinggi),
       background berupa gradient, dan color dengan !important.
       ====== */
    #rabTable_wrapper .dataTables_length select,
    #rabTable_wrapper .dataTables_filter input {
      border: 1px solid #e2e8f0 !important;
      border-radius: .75rem !important;
      background: #fff !important;
      padding: .5rem .75rem !important;
      font-size: .75rem !important;
      font-weight: 600;
      color: #334155 !important;
      outline: none;
      transition: border-color .2s ease, box-shadow .2s ease;
    }

    #rabTable_wrapper .dataTables_filter input {
      min-width: 220px;
      margin-left: .5rem;
    }

    #rabTable_wrapper .dataTables_length select:focus,
    #rabTable_wrapper .dataTables_filter input:focus {
      border-color: #6366f1 !important;
      box-shadow: 0 0 0 .2rem rgba(99, 102, 241, .15);
    }

    #rabTable_wrapper .dataTables_length label,
    #rabTable_wrapper .dataTables_filter label {
      font-size: .75rem;
      font-weight: 600;
      color: #64748b;
    }

    #rabTable_wrapper .dataTables_info {
      float: none;
      padding-top: 0;
      font-size: .75rem;
      color: #64748b;
    }

    #rabTable_wrapper .dataTables_paginate {
      float: none;
      padding-top: 0;
      display: flex;
      align-items: center;
      justify-content: flex-end;
      flex-wrap: wrap;
      gap: 2px;
    }

    #rabTable_wrapper .dataTables_paginate .paginate_button {
      box-sizing: border-box;
      display: inline-flex !important;
      align-items: center;
      justify-content: center;
      min-width: 2rem;
      height: 2rem;
      margin: 0 !important;
      padding: 0 .5rem !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: .75rem !important;
      background: #fff !important;
      color: #334155 !important;
      font-size: .75rem;
      font-weight: 600;
      line-height: 1;
      text-align: center;
      text-decoration: none !important;
      cursor: pointer;
      box-shadow: none !important;
      transition: background-color .15s ease, border-color .15s ease;
    }

    #rabTable_wrapper .dataTables_paginate .paginate_button:hover {
      background: #f1f5f9 !important;
      border-color: #cbd5e1 !important;
      color: #1e293b !important;
    }

    #rabTable_wrapper .dataTables_paginate .paginate_button:active {
      background: #eef2ff !important;
      box-shadow: none !important;
    }

    #rabTable_wrapper .dataTables_paginate .paginate_button.current,
    #rabTable_wrapper .dataTables_paginate .paginate_button.current:hover {
      background: #4f46e5 !important;
      border-color: #4f46e5 !important;
      color: #fff !important;
    }

    #rabTable_wrapper .dataTables_paginate .paginate_button.disabled,
    #rabTable_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    #rabTable_wrapper .dataTables_paginate .paginate_button.disabled:active {
      background: #f8fafc !important;
      border-color: #f1f5f9 !important;
      color: #cbd5e1 !important;
      cursor: not-allowed;
      box-shadow: none !important;
    }

    #rabTable_wrapper .dataTables_paginate .paginate_button i {
      font-size: .95rem;
      line-height: 1;
    }

    #rabTable_wrapper .dataTables_paginate .ellipsis {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 2rem;
      height: 2rem;
      padding: 0 !important;
      color: #94a3b8;
      font-size: .75rem;
      font-weight: 700;
      user-select: none;
    }

    .dataTables_processing {
      border: 1px solid #e2e8f0 !important;
      border-radius: .75rem !important;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .08) !important;
      font-size: .75rem !important;
      font-weight: 600;
      color: #475569 !important;
      background: #fff !important;
    }

    /* ====== Modal & offcanvas ====== */
    .app-modal .modal-content,
    .app-offcanvas.offcanvas {
      border: 1px solid #e2e8f0;
      border-radius: 1rem;
      overflow: hidden;
    }

    .app-offcanvas.offcanvas {
      border-radius: 0 1rem 1rem 0;
    }

    .app-modal .modal-header,
    .app-offcanvas .offcanvas-header {
      padding: 1rem 1.25rem;
    }

    .app-modal .modal-body {
      padding: 1.25rem;
    }

    @media (max-width: 767.98px) {

      #rabTable_wrapper .dataTables_length,
      #rabTable_wrapper .dataTables_filter,
      #rabTable_wrapper .dataTables_info {
        width: 100%;
        text-align: center;
        float: none;
      }

      #rabTable_wrapper .dataTables_filter input {
        width: 100%;
        min-width: 0;
      }

      #rabTable_wrapper .dataTables_paginate {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
@endsection

@section('content')
  <div class="space-y-6">

    <!-- ======================================================================== -->
    <!-- HEADER                                                                   -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
            <span>Modul Keuangan &middot; Rencana Anggaran Biaya</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight leading-tight m-0 flex items-center gap-2.5">
            <i class="bx bx-calculator text-indigo-600 text-2xl"></i> Rencana Anggaran Biaya
          </h1>
          <p class="text-slate-500 text-xs sm:text-sm m-0">
            Dokumentasi perencanaan anggaran, realisasi, dan sisa saldo per periode.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <button type="button" data-bs-toggle="offcanvas" data-bs-target="#pagu"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition-colors cursor-pointer border-0">
            <i class="bx bx-plus text-base"></i> Tambah Pagu
          </button>
        </div>
      </div>
    </div>

    <!-- ======================================================================== -->
    <!-- KPI CARDS                                                                -->
    <!-- ======================================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

      <!-- Total Saldo -->
      <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between gap-2 mb-2">
          <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Total Saldo</span>
          <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
            <i class="bx bx-wallet-alt"></i>
          </div>
        </div>
        <div>
          <div id="saldo" class="text-lg font-extrabold text-slate-800 tracking-tight">Rp 0</div>
          <div class="text-[11px] font-medium text-rose-600 mt-0.5">Pendapatan − pengeluaran</div>
        </div>
      </div>

      <!-- Total Anggaran -->
      <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between gap-2 mb-2">
          <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Total Anggaran</span>
          <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
            <i class="bx bx-money"></i>
          </div>
        </div>
        <div>
          <div id="pagu-tahun" class="text-lg font-extrabold text-slate-800 tracking-tight">Rp 0</div>
          <div class="text-[11px] font-medium text-amber-600 mt-0.5">Pagu periode terfilter</div>
        </div>
      </div>

      <!-- Anggaran Terealisasi -->
      <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between gap-2 mb-2">
          <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Anggaran Terealisasi</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
            <i class="bx bx-check-circle"></i>
          </div>
        </div>
        <div>
          <div id="anggaran-terealisasi" class="text-lg font-extrabold text-slate-800 tracking-tight">Rp 0</div>
          <div class="text-[11px] font-medium text-emerald-600 mt-0.5">Pengeluaran disetujui</div>
        </div>
      </div>

      <!-- Sisa Anggaran -->
      <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between gap-2 mb-2">
          <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Sisa Anggaran</span>
          <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
            <i class="bx bx-calculator"></i>
          </div>
        </div>
        <div>
          <div id="sisa-anggaran" class="text-lg font-extrabold text-slate-800 tracking-tight">Rp 0</div>
          <div class="text-[11px] font-medium text-blue-600 mt-0.5">Anggaran − terealisasi</div>
        </div>
      </div>

    </div>

    <!-- ======================================================================== -->
    <!-- FILTER PERIODE                                                           -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
      <div class="flex items-center justify-between gap-2.5 mb-4 pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
            <i class="bx bx-filter-alt"></i>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-800 m-0 leading-tight">Filter Periode</h3>
            <p class="text-xs text-slate-500 m-0 mt-0.5">Kartu ringkasan & tabel mengikuti filter di bawah ini</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="min-w-0">
          <label for="filter-tahun" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Tahun</label>
          <select id="filter-tahun" class="filter-select w-full h-10 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all cursor-pointer">
            <option value="">-- Semua Tahun --</option>
            @for ($i = date('Y') - 5; $i <= date('Y') + 5; $i++)
              <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
            @endfor
          </select>
        </div>

        <div class="min-w-0">
          <label for="filter-bulan" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Bulan</label>
          <select id="filter-bulan" class="filter-select w-full h-10 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all cursor-pointer">
            <option value="">-- Semua Bulan --</option>
            @foreach ([
              '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
              '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
              '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ] as $key => $value)
              <option value="{{ $key }}" {{ $key == date('m') ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
          </select>
        </div>

        <div class="min-w-0">
          <label for="filter-kegiatan" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Kegiatan</label>
          <select id="filter-kegiatan" class="filter-select w-full h-10 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all cursor-pointer">
            <option value="">-- Semua Kegiatan --</option>
            <!-- Options will be loaded dynamically -->
          </select>
        </div>
      </div>
    </div>

    <!-- ======================================================================== -->
    <!-- TABEL RAB                                                                -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
            <i class="bx bx-table"></i>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-800 m-0 leading-tight">RAB Record Table</h3>
            <p class="text-xs text-slate-500 m-0 mt-0.5">Daftar rencana anggaran beserta realisasi dan sisanya</p>
          </div>
        </div>
      </div>

      <div class="p-5">
        <table class="w-full text-left border-collapse" id="rabTable" style="font-size: 14px; width: 100%;">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200/80">
              <th>No</th>
              <th>Bulan &amp; Tahun</th>
              <th>Keterangan</th>
              <th>Kegiatan</th>
              <th>Jumlah Anggaran</th>
              <th>Anggaran Terealisasi</th>
              <th>Sisa Anggaran</th>
              <th>Status</th>
              <th>Admin</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

  </div>

  {{-- Offcanvas Tambah Pagu --}}
  <div class="offcanvas offcanvas-start app-offcanvas" data-bs-scroll="true" tabindex="-1" id="pagu" aria-labelledby="offcanvasBothLabel">
    <div class="offcanvas-header" style="background: linear-gradient(135deg, #4f46e5 0%, #2563eb 60%, #0ea5e9 100%);">
      <div class="d-flex align-items-center">
        <div class="me-3 d-flex align-items-center justify-content-center"
          style="width: 42px; height: 42px; background-color: rgba(255, 255, 255, .18); border-radius: 12px;">
          <i class='bx bx-money text-white fs-5'></i>
        </div>
        <div>
          <h5 id="offcanvasBothLabel" class="offcanvas-title text-white mb-0 fw-semibold">Tambah Pagu Anggaran</h5>
          <small class="text-white-50">Rencana Anggaran Biaya</small>
        </div>
      </div>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" style="padding: 1.5rem;">
      <form action="/rab/store" method="POST" id="addUserForm">
        @csrf
        <div class="mb-4">
          <label class="form-label fw-medium mb-2" for="pagu-input">
            <i class='bx bx-money me-1'></i>Jumlah Anggaran
          </label>
          <input oninput="formatAndSync(this)" type="text" class="form-control mb-2" id="pagu-input"
            placeholder="Rp. 1000.000.000" required>
          <input hidden type="text" class="form-control" id="generate" oninput="this.value = formatNumber($this.value)" name="pagu">
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium mb-2" for="tahun">
            <i class='bx bx-calendar me-1'></i>Tahun Anggaran
          </label>
          <select name="tahun" id="tahun" class="form-select mb-2" required>
            @for ($i = date('Y'); $i <= date('Y') + 5; $i++)
              <option value="{{ $i }}">{{ $i }}</option>
            @endfor
          </select>
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium mb-2" for="bulan">
            <i class="bx bx-calendar me-1"></i>Bulan Anggaran
          </label>
          <select name="bulan" id="bulan" class="form-select mb-2" required>
            <option value="" disabled selected>Pilih Bulan</option>
            @foreach ([
              '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
              '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
              '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ] as $num => $nama)
              <option value="{{ $num }}">{{ $nama }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium mb-2">
            <i class="bx bx-detail me-1"></i>Kegiatan Anggaran
          </label>
          <input type="text" name="kegiatan" class="form-control" placeholder="Belanja Logistik" required>
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium mb-2">
            <i class="bx bx-cart me-1"></i>Jumlah Item
          </label>
          <input type="number" name="item" class="form-control" placeholder="100" min="1">
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium mb-2">
            <i class="bx bx-clipboard me-1"></i>Keterangan
          </label>
          <textarea name="keterangan" cols="30" rows="5" class="form-control" required></textarea>
        </div>

        <div class="d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="offcanvas">
            <i class='bx bx-x me-1'></i>Batal
          </button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bx bx-plus me-1"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Detail Modal --}}
  <div class="modal fade app-modal" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header border-bottom">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
              <i class="bx bx-detail"></i>
            </div>
            <h5 class="modal-title m-0" id="detailModalLabel">Detail RAB</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="detailContent">
            <!-- Content will be loaded here -->
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
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

  <script>
    let dataTable;

    // Function to format currency
    function formatCurrency(amount) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
      }).format(amount || 0);
    }

    // Load kegiatan options on page load
    async function loadKegiatanOptions() {
      try {
        const response = await fetch('{{ route("rab-kegiatan") }}');
        const kegiatan = await response.json();

        const select = document.getElementById('filter-kegiatan');

        // Clear existing options except the first one
        while (select.children.length > 1) {
          select.removeChild(select.lastChild);
        }

        // Add kegiatan options
        kegiatan.forEach(item => {
          const option = document.createElement('option');
          option.value = item;
          option.textContent = item;
          select.appendChild(option);
        });
      } catch (error) {
        console.error('Error loading kegiatan options:', error);
      }
    }

    // Function to re-initialize Bootstrap tooltips
    function initTooltips() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }

    // Chip classes (Tailwind) untuk kolom tabel
    function chip(text, tone) {
      const tones = {
        rose: 'bg-rose-50 text-rose-700 border-rose-200/60',
        emerald: 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
        amber: 'bg-amber-50 text-amber-700 border-amber-200/60',
        slate: 'bg-slate-100 text-slate-700 border-slate-200/80'
      };
      const cls = tones[tone] || tones.slate;
      return `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border whitespace-nowrap ${cls}">${text}</span>`;
    }

    // Initialize page
    loadKegiatanOptions();

    // Initialize DataTable with server-side processing
    dataTable = $('#rabTable').DataTable({
      dom: '<"flex flex-wrap items-center justify-between gap-3 mb-4"<"dt-length"l><"dt-search"f>><"overflow-x-auto"t><"flex flex-wrap items-center justify-between gap-3 mt-4"<"dt-info"i><"dt-pagination"p>>',
      processing: true,
      serverSide: true,
      ajax: {
        url: '{{ route("rab-filter") }}',
        type: 'GET',
        data: function (d) {
          d.bulan = $('#filter-bulan').val();
          d.tahun = $('#filter-tahun').val();
          d.kegiatan = $('#filter-kegiatan').val();
        },
        dataSrc: function (json) {
          // Update stats cards dynamically
          $('#saldo').text(formatCurrency(json.saldo));
          $('#pagu-tahun').text(formatCurrency(json.total));
          $('#anggaran-terealisasi').text(formatCurrency(json.terealisasi));
          $('#sisa-anggaran').text(formatCurrency(json.sisa));

          return json.data;
        },
        error: function (xhr, error, code) {
          console.error('Error loading data:', error);
        }
      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
        {
          data: 'bulan_tahun',
          name: 'bulan_tahun',
          className: 'text-center',
          render: function (data) {
            return chip(data, 'rose');
          }
        },
        { data: 'keterangan', name: 'keterangan' },
        { data: 'kegiatan', name: 'kegiatan' },
        {
          data: 'jumlah_anggaran',
          name: 'jumlah_anggaran',
          className: 'amount-cell text-right font-bold text-slate-800',
          render: function (data) {
            return formatCurrency(data);
          }
        },
        {
          data: 'terealisasi',
          name: 'terealisasi',
          className: 'amount-cell text-right font-semibold text-emerald-700',
          render: function (data) {
            return formatCurrency(data);
          }
        },
        {
          data: 'sisa',
          name: 'sisa',
          className: 'amount-cell text-right font-semibold text-blue-700',
          render: function (data) {
            return formatCurrency(data);
          }
        },
        {
          data: 'status_nama',
          name: 'status_nama',
          className: 'text-center',
          render: function (data, type, row) {
            let tone = 'amber';
            if (row.status_id == 11) tone = 'emerald';
            else if (row.status_id == 12) tone = 'rose';

            return chip(data, tone);
          }
        },
        {
          data: 'admin_nama',
          name: 'admin_nama',
          className: 'text-center',
          render: function (data) {
            return chip(data, 'slate');
          }
        },
        {
          data: 'id',
          name: 'action',
          orderable: false,
          searchable: false,
          className: 'text-center',
          render: function (data, type, row) {
            return `
                <div class="flex items-center justify-center gap-1.5">
                    <button type="button" class="btn-detail w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-200 text-blue-600 inline-flex items-center justify-center text-sm transition-colors cursor-pointer"
                        data-id="${data}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="bottom"
                        title="Detail RAB">
                        <i class="bx bx-show"></i>
                    </button>
                    <a href="/edit-rab/${data}" class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-amber-50 hover:border-amber-200 text-amber-600 inline-flex items-center justify-center text-sm transition-colors no-underline"
                        data-bs-toggle="tooltip" title="Edit RAB" data-bs-placement="bottom">
                        <i class="bx bx-pencil"></i>
                    </a>
                    <button type="button" class="btnDelete w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-rose-600 inline-flex items-center justify-center text-sm transition-colors cursor-pointer"
                        data-url="/delete-rab/${data}"
                        data-bs-toggle="tooltip" title="Hapus RAB" data-bs-placement="bottom">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            `;
          }
        }
      ],
      pagingType: 'full_numbers',
      drawCallback: function () {
        initTooltips();

        // Sembunyikan kontrol pagination bila hanya ada satu halaman.
        const api = this.api();
        $(api.table().container()).find('.dt-pagination').toggle(api.page.info().pages > 1);
      },
      language: {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ entri",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
        infoFiltered: "(disaring dari _MAX_ total entri)",
        paginate: {
          first: '<i class="bx bx-chevrons-left"></i>',
          last: '<i class="bx bx-chevrons-right"></i>',
          next: '<i class="bx bx-chevron-right"></i>',
          previous: '<i class="bx bx-chevron-left"></i>'
        },
        aria: {
          paginate: {
            first: 'Halaman pertama',
            last: 'Halaman terakhir',
            next: 'Halaman berikutnya',
            previous: 'Halaman sebelumnya'
          }
        }
      }
    });

    // Add event listeners for custom filters
    $('#filter-bulan, #filter-tahun, #filter-kegiatan').on('change', function () {
      dataTable.ajax.reload();
    });

    // Handle detail button click using event delegation
    document.addEventListener('click', async function (e) {
      if (e.target.closest('.btn-detail')) {
        const button = e.target.closest('.btn-detail');
        const rabId = button.getAttribute('data-id');

        // Show loading state
        document.getElementById('detailContent').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat detail RAB...</p>
            </div>
        `;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        modal.show();

        try {
          // Fetch detail data
          const response = await fetch(`/rab/detail/${rabId}`);
          const data = await response.json();

          if (!data.success) {
            document.getElementById('detailContent').innerHTML = `
                <div class="text-center py-4">
                    <i class="bx bx-error-circle text-rose-500 fs-1"></i>
                    <p class="text-rose-600 mt-2 mb-3">${data.message || 'Gagal memuat detail RAB'}</p>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            `;
            return;
          }

          const rab = data.rab;
          const bulanNama = data.bulanNama;

          const statusChip = rab.status_id == 11
            ? chip(rab.status.nama_status, 'emerald')
            : rab.status_id == 12
              ? chip(rab.status.nama_status, 'rose')
              : chip(rab.status.nama_status, 'amber');

          const progress = rab.jumlah_anggaran > 0
            ? (data.totalTerealisasi / rab.jumlah_anggaran * 100).toFixed(1)
            : 0;

          // Build detail content
          const detailHtml = `
                <div class="space-y-4">
                    <!-- Informasi Dasar -->
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-base"><i class="bx bx-info-circle"></i></div>
                            <h6 class="text-sm font-bold text-slate-800 m-0">Informasi Dasar</h6>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Kegiatan</div>
                                <div class="text-xs font-semibold text-slate-800">${rab.kegiatan}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Periode</div>
                                <div class="text-xs font-semibold text-slate-800">${bulanNama} ${rab.tahun_anggaran}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Jumlah Item</div>
                                <div class="text-xs font-semibold text-slate-800">${rab.item || '-'}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Status</div>
                                <div>${statusChip}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Anggaran -->
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-base"><i class="bx bx-wallet"></i></div>
                            <h6 class="text-sm font-bold text-slate-800 m-0">Informasi Anggaran</h6>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 text-center">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Total Anggaran</div>
                                <div class="text-sm font-extrabold text-slate-800">${formatCurrency(rab.jumlah_anggaran)}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 text-center">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Terealisasi</div>
                                <div class="text-sm font-extrabold text-emerald-700">${formatCurrency(data.totalTerealisasi)}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 text-center">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Sisa Anggaran</div>
                                <div class="text-sm font-extrabold text-blue-700">${formatCurrency(data.sisaAnggaran)}</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Progress Realisasi</span>
                                <span class="text-[11px] font-bold text-slate-600">${progress}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-emerald-500 h-full" style="width: ${progress}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-base"><i class="bx bx-clipboard"></i></div>
                            <h6 class="text-sm font-bold text-slate-800 m-0">Keterangan</h6>
                        </div>
                        <p class="text-xs text-slate-500 m-0">${rab.keterangan || 'Tidak ada keterangan'}</p>
                    </div>

                    <!-- Info tambahan -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-slate-400">
                        <span>Dibuat oleh: <strong class="text-slate-600">${rab.usr ? rab.usr.name : '-'}</strong></span>
                        <span>Total Pengeluaran: ${data.pengeluaranCount || 0} item (${data.approvedPengeluaranCount || 0} disetujui)</span>
                    </div>
                </div>
            `;

          document.getElementById('detailContent').innerHTML = detailHtml;
        } catch (error) {
          console.error('Error fetching detail:', error);

          let errorMessage = 'Gagal memuat detail RAB';
          if (error.response && error.response.status === 404) {
            errorMessage = 'Data RAB tidak ditemukan';
          } else if (error.response && error.response.status === 500) {
            errorMessage = 'Terjadi kesalahan server';
          }

          document.getElementById('detailContent').innerHTML = `
                <div class="text-center py-4">
                    <i class="bx bx-error-circle text-rose-500 fs-1"></i>
                    <p class="text-rose-600 mt-2 mb-1">${errorMessage}</p>
                    <small class="text-muted d-block mb-3">RAB ID: ${rabId}</small>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            `;
        }
      }

      // Handle delete button click using event delegation
      if (e.target.closest('.btnDelete')) {
        const button = e.target.closest('.btnDelete');
        const url = button.getAttribute('data-url');

        Swal.fire({
          title: 'Apakah Anda yakin?',
          text: "Data RAB akan dihapus permanen!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#e11d48',
          cancelButtonColor: '#64748b',
          confirmButtonText: 'Ya, Hapus!',
          cancelButtonText: 'Batal',
          customClass: {
            container: 'my-swal-container'
          }
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = url;
          }
        });
      }
    });
  </script>
@endsection
