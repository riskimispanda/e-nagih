@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pelanggan')

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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css" />
  <style>
    /* KPI card hover (ala dashboard logistik) */
    .kpi-card {
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.025);
    }

    .table-hover-row:hover td {
      background-color: #f8fafc;
    }

    /* Bootstrap collapse vs utility `.collapse` Tailwind (visibility: collapse) */
    #filterCollapse.collapse.show {
      visibility: visible;
    }

    /* Tom Select (filter agen) */
    .ts-wrapper {
      width: 100% !important;
      height: auto !important;
      border: 0 !important;
      padding: 0 !important;
      background: transparent !important;
      box-shadow: none !important;
      border-radius: 0 !important;
    }

    .ts-wrapper .ts-control {
      height: 2.5rem;
      border: 1px solid #e2e8f0 !important;
      border-radius: 0.75rem !important;
      padding: 0 2rem 0 0.75rem !important;
      font-size: 0.75rem !important;
      font-weight: 600;
      color: #334155;
      box-shadow: none !important;
      background-color: #ffffff !important;
      display: flex;
      align-items: center;
    }

    .ts-wrapper.focus .ts-control {
      border-color: #6366f1 !important;
      box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15) !important;
    }

    /* Panah konsisten untuk Tom Select single */
    .ts-wrapper.single .ts-control {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") !important;
      background-repeat: no-repeat !important;
      background-position: right 0.7rem center !important;
      background-size: 0.85rem !important;
    }

    .ts-wrapper .ts-dropdown {
      border: 1px solid #e2e8f0;
      border-radius: 0.75rem;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
      font-size: 0.75rem;
      font-weight: 600;
      overflow: hidden;
    }

    .ts-wrapper .ts-dropdown .option.active {
      background-color: #eef2ff;
      color: #4f46e5;
    }

    /* Select dengan panah kustom (filter panel) */
    .filter-select {
      -webkit-appearance: none;
      appearance: none;
      padding-right: 2rem !important;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 0.7rem center;
      background-size: 0.85rem;
    }

    /* Export dropdown */
    .export-menu {
      border: 1px solid #e2e8f0;
      border-radius: 0.75rem;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
      padding: 0.375rem;
      max-height: 340px;
      overflow-y: auto;
    }

    .export-menu .dropdown-item {
      border-radius: 0.5rem;
      padding: 0.5rem 0.75rem;
      font-size: 0.75rem;
      font-weight: 600;
      color: #334155;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      white-space: normal;
    }

    .export-menu .dropdown-item:hover {
      background-color: #f1f5f9;
      color: #4f46e5;
    }

    .export-menu .dropdown-header {
      font-size: 0.65rem;
      font-weight: 800;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: #94a3b8;
      padding: 0.5rem 0.75rem 0.25rem;
    }

    .export-menu .dropdown-divider {
      border-top-color: #f1f5f9;
      margin: 0.375rem 0;
    }

    /* Modal polish */
    .app-modal .modal-content {
      border: 1px solid #e2e8f0;
      border-radius: 1rem;
      overflow: hidden;
    }

    .app-modal .modal-header {
      border-bottom: 1px solid #f1f5f9;
      padding: 1rem 1.25rem;
    }

    .app-modal .modal-title {
      font-size: 0.95rem;
      font-weight: 700;
      color: #1e293b;
    }

    .app-modal .modal-body {
      padding: 1.25rem;
    }
  </style>
@endsection

@section('content')
  @php
    $isFilterActive = request()->filled('search') || request()->filled('agen_id') || (request()->filled('sort') && request('sort') !== 'default') || (request()->filled('per_page') && request('per_page') !== '10');

    // Nomor halaman untuk pagination (maks. 5 tombol + elipsis)
    $currentPage = $data->currentPage();
    $lastPage = $data->lastPage();
    if ($lastPage <= 7) {
      $paginationPages = range(1, $lastPage);
    } else {
      $paginationPages = [1];
      if ($currentPage > 4) {
        $paginationPages[] = '...';
      }
      foreach (range(max(2, $currentPage - 1), min($lastPage - 1, $currentPage + 1)) as $p) {
        $paginationPages[] = $p;
      }
      if ($currentPage < $lastPage - 3) {
        $paginationPages[] = '...';
      }
      $paginationPages[] = $lastPage;
    }
  @endphp

  <div class="space-y-6">

    <!-- ======================================================================== -->
    <!-- HEADER                                                                   -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Modul Data Pelanggan</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight leading-tight m-0 flex items-center gap-2.5">
            <i class="bx bx-group text-indigo-600 text-2xl"></i> Data Pelanggan
          </h1>
          <p class="text-slate-500 text-xs sm:text-sm m-0">
            Monitoring data pelanggan, status layanan, instalasi, tiket, dan pembayaran.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100">
            <i class="bx bx-user"></i> {{ $allData }} Pelanggan
          </span>
        </div>
      </div>
    </div>

    <!-- ======================================================================== -->
    <!-- DATA GLOBAL (KPI CARDS)                                                  -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
      <div class="flex items-center justify-between gap-2.5 mb-4 pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
            <i class="bx bx-stats"></i>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-800 m-0 leading-tight">Data Global</h3>
            <p class="text-xs text-slate-500 m-0 mt-0.5">Ringkasan pelanggan, instalasi, tiket, dan agen</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">

        <!-- Jumlah Pelanggan -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Jumlah Pelanggan</span>
            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
              <i class="bx bx-group"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $allData }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-0.5">Total pelanggan terdaftar</div>
          </div>
        </div>

        <!-- Pelanggan Aktif -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Pelanggan Aktif</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
              <i class="bx bx-check-circle"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $pelangganAktif }}</div>
            <div class="text-[11px] font-medium text-emerald-600 mt-0.5">Layanan berjalan normal</div>
          </div>
        </div>

        <!-- Pelanggan Non-Aktif -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between cursor-pointer group hover:border-rose-300"
          data-bs-toggle="modal" data-bs-target="#blokir">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Non-Aktif</span>
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-rose-100 transition-colors">
              <i class="bx bx-x-circle"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $nonAktif->count() }}</div>
            <div class="text-[10px] font-medium text-slate-400 mt-0.5 truncate" title="Tanggal Isolir : {{ $dateBlokir->tanggal_blokir }}">
              Isolir: {{ $dateBlokir->tanggal_blokir }} &middot; 00:01:00 WIB
            </div>
          </div>
        </div>

        <!-- Pelanggan Dismantle -->
        <a href="/pelanggan-dismantle" data-bs-toggle="tooltip" title="Pelanggan Dismantle"
          class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between no-underline group hover:border-purple-300">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600">Dismantle</span>
            <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-purple-100 transition-colors">
              <i class="bx bx-archive-in"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $dismantle }}</div>
            <div class="text-[11px] font-medium text-purple-600 mt-0.5">Perangkat ditarik teknisi</div>
          </div>
        </a>

        <!-- List Tiket -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between cursor-pointer group hover:border-amber-300"
          data-bs-toggle="modal" data-bs-target="#maintenance">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">List Tiket</span>
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-amber-100 transition-colors">
              <i class="bx bx-wrench"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $maintenance->count() }}</div>
            <div class="text-[11px] font-medium text-amber-600 mt-0.5">Tiket open berjalan</div>
          </div>
        </div>

        <!-- Installasi Hari Ini -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between cursor-pointer group hover:border-emerald-300"
          data-bs-toggle="modal" data-bs-target="#selesai">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Installasi Hari Ini</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-emerald-100 transition-colors">
              <i class="bx bx-check-circle"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $hariIni }}</div>
            <div class="text-[11px] font-medium text-emerald-600 mt-0.5">Selesai hari ini</div>
          </div>
        </div>

        <!-- Antrian Hari Ini -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between cursor-pointer group hover:border-amber-300"
          data-bs-toggle="modal" data-bs-target="#antrian">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Antrian Hari Ini</span>
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-amber-100 transition-colors">
              <i class="bx bx-hourglass"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $menunggu }}</div>
            <div class="text-[11px] font-medium text-amber-600 mt-0.5">Menunggu / on progress</div>
          </div>
        </div>

        <!-- Instalasi Bulan Ini -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between cursor-pointer group hover:border-blue-300"
          data-bs-toggle="modal" data-bs-target="#bulanan">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Instalasi {{ now()->translatedFormat('F Y') }}</span>
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-blue-100 transition-colors">
              <i class="bx bx-calendar"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $bulananInstallasi }}</div>
            <div class="text-[11px] font-medium text-blue-600 mt-0.5">Total bulan berjalan</div>
          </div>
        </div>

        <!-- Pelanggan Fasum -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between cursor-pointer group hover:border-slate-300"
          data-bs-toggle="modal" data-bs-target="#gagal">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pelanggan Fasum</span>
            <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-slate-200 transition-colors">
              <i class="bx bx-package"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $fasum }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-0.5">Fasilitas umum</div>
          </div>
        </div>

        <!-- Data Agen -->
        <a href="/data-agen" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat Data Agen"
          class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between no-underline group hover:border-blue-300">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Data Agen</span>
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-blue-100 transition-colors">
              <i class="bx bx-user-check"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $countAgen }}</div>
            <div class="text-[11px] font-medium text-blue-600 mt-0.5">Agen terdaftar</div>
          </div>
        </a>

        <!-- Data Import -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between cursor-pointer group hover:border-slate-300"
          data-bs-toggle="modal" data-bs-target="#gagal">
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Data Import</span>
            <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-slate-200 transition-colors">
              <i class="bx bx-import"></i>
            </div>
          </div>
          <div>
            <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $importData }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-0.5">Data hasil import</div>
          </div>
        </div>

      </div>
    </div>

    <!-- ======================================================================== -->
    <!-- DAFTAR PELANGGAN                                                         -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

      <!-- Card Header -->
      <div class="p-5 border-b border-slate-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
              <i class="bx bx-list-ul"></i>
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-800 m-0 leading-tight">Daftar Pelanggan</h3>
              <p class="text-xs text-slate-500 m-0 mt-0.5">Kelola data pelanggan, status layanan, dan pembayaran</p>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2.5">
            <button type="button"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border font-semibold text-xs transition-all cursor-pointer {{ $isFilterActive ? 'border-indigo-200 bg-indigo-50/70 hover:bg-indigo-100 text-indigo-700' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-700' }}"
              data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="{{ $isFilterActive ? 'true' : 'false' }}"
              aria-controls="filterCollapse">
              <i class="bx bx-filter-alt text-base"></i> Filter
              @if($isFilterActive)
                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-md bg-indigo-600 text-white text-[10px] font-bold">Aktif</span>
              @endif
            </button>
            <button type="button" id="btn-tutorial-sort" data-bs-toggle="tooltip" data-bs-placement="top" title="Panduan Penggunaan"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-all cursor-pointer border-0">
              <i class="bx bx-help-circle text-base"></i> Panduan
            </button>
          </div>
        </div>
      </div>

      <!-- Card Body -->
      <div class="p-5">

        <!-- Success/Error Messages -->
        @if (session('success'))
          <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 !rounded-2xl !border !border-emerald-200 !bg-emerald-50 !text-emerald-800" role="alert">
            <i class="bx bx-check-circle text-lg"></i>
            <span class="text-xs font-semibold">{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if (session('error'))
          <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 !rounded-2xl !border !border-rose-200 !bg-rose-50 !text-rose-800" role="alert">
            <i class="bx bx-error-circle text-lg"></i>
            <span class="text-xs font-semibold">{{ session('error') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if ($errors->any())
          <div class="alert alert-danger alert-dismissible fade show !rounded-2xl !border !border-rose-200 !bg-rose-50 !text-rose-800" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
              <i class="bx bx-error-circle text-lg"></i>
              <span class="text-xs font-bold">Terjadi kesalahan:</span>
            </div>
            <ul class="mb-0 text-xs">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        <!-- Collapse Container for Filters -->
        <div class="collapse {{ $isFilterActive ? 'show' : '' }} mt-4" id="filterCollapse">
          <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
              <!-- Pencarian -->
              <div class="md:col-span-2 min-w-0">
                <label for="searchCustomer" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Pencarian</label>
                <div class="flex items-stretch h-10 rounded-xl border border-slate-200 bg-white overflow-hidden transition-all focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/15">
                  <input type="text" id="searchCustomer" placeholder="Cari pelanggan, alamat, hp, atau usersecret..."
                    class="flex-1 min-w-0 px-3 text-xs font-medium text-slate-700 bg-transparent border-0 outline-none placeholder-slate-400">
                  <button type="button" id="searchButton" title="Cari"
                    class="shrink-0 px-4 bg-indigo-600 hover:bg-indigo-700 text-white border-0 cursor-pointer transition-colors">
                    <i class="bx bx-search text-base"></i>
                  </button>
                </div>
              </div>

              <!-- Agen -->
              <div class="min-w-0">
                <label for="agenSelect" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Agen</label>
                <select id="agenSelect"
                  class="filter-select w-full h-10 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all cursor-pointer">
                  <option value="">Semua Agen</option>
                  @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}" {{ (isset($agen_id) && $agen_id == $agent->id) ? 'selected' : '' }}>
                      {{ $agent->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- Urutkan Berdasarkan -->
              <div id="tutorial-sort-container" class="min-w-0">
                <label for="sortSelect" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Urutkan Berdasarkan</label>
                <select id="sortSelect"
                  class="filter-select w-full h-10 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all cursor-pointer">
                  <option value="default">Default</option>
                  <option value="name-asc">Nama A-Z</option>
                  <option value="name-desc">Nama Z-A</option>
                  <option value="status-active">Status Aktif</option>
                  <option value="status-inactive">Status Non-Aktif</option>
                  <option value="maintenance">Maintenance</option>
                </select>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mt-4 pt-4 border-t border-slate-200/70">
              <!-- Data per Halaman -->
              <div class="min-w-0">
                <label for="pageSize" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Data per Halaman</label>
                <div class="flex items-center gap-2.5">
                  <select id="pageSize"
                    class="filter-select h-10 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all cursor-pointer">
                    @foreach([10, 25, 50, 100, 250] as $size)
                      <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                        {{ $size }}
                      </option>
                    @endforeach
                    <option value="{{ $data->total() }}" {{ request('per_page') == $data->total() ? 'selected' : '' }}>
                      Semua</option>
                  </select>
                  <span class="text-[11px] font-medium text-slate-400 whitespace-nowrap">
                    dari <span class="font-bold text-slate-600">{{ number_format($data->total(), 0, ',', '.') }}</span> data
                  </span>
                </div>
              </div>

              <!-- Export Dropdown -->
              <div class="flex sm:justify-end">
                <div class="dropdown export-dropdown">
                  <button type="button"
                    class="inline-flex items-center gap-1.5 h-10 px-3.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all cursor-pointer"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-file text-base text-emerald-600"></i>
                    Export Excel
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end export-menu" style="min-width: 230px;">
                    <li>
                      <h6 class="dropdown-header">Export Semua Data</h6>
                    </li>
                    <li>
                      <a class="dropdown-item" href="/semua">
                        <i class="bx bx-download"></i>
                        Semua Pelanggan
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="/aktif">
                        <i class="bx bx-check-circle"></i>
                        Pelanggan Aktif
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="/nonaktif">
                        <i class="bx bx-x-circle"></i>
                        Pelanggan Non-Aktif
                      </a>
                    </li>
                    <li>
                      <hr class="dropdown-divider">
                    </li>
                    <li>
                      <h6 class="dropdown-header">Export Berdasarkan Paket</h6>
                    </li>
                    @php
                      $pakets = \App\Models\Paket::orderBy('nama_paket')->get();
                    @endphp
                    @forelse($pakets as $paket)
                      <li>
                        <a class="dropdown-item" href="/paket/{{ $paket->id }}">
                          <i class="bx bx-package"></i>
                          {{ $paket->nama_paket }}
                        </a>
                      </li>
                    @empty
                      <li>
                        <span class="dropdown-item text-muted">
                          <i class="bx bx-info-circle"></i>
                          Tidak ada paket tersedia
                        </span>
                      </li>
                    @endforelse
                    @if($pakets->count() > 0)
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li>
                        <a class="dropdown-item" href="/ringkasan-per-paket">
                          <i class="bx bx-chart"></i>
                          Ringkasan Per Paket
                        </a>
                      </li>
                    @endif
                    <li>
                      <hr class="dropdown-divider">
                    </li>
                    <li>
                      <h6 class="dropdown-header">Export Berdasarkan Bulan</h6>
                    </li>
                    @php
                      $months = [
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember'
                      ];
                      $currentYear = date('Y');
                    @endphp

                    @foreach($months as $num => $name)
                      <li>
                        <a class="dropdown-item"
                          href="{{ route('export.bulan', ['month' => $num, 'year' => $currentYear]) }}">
                          <i class="bx bx-calendar"></i>
                          {{ $name }} {{ $currentYear }}
                        </a>
                      </li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- WebSocket Connection Status (hidden) -->
        <div class="row mb-3" style="display: none">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Status Koneksi WebSocket</h5>
                <div id="status" class="alert alert-info">Menghubungkan ke server WebSocket...</div>
                <div id="messages" class="small text-muted" style="max-height: 150px; overflow-y: auto;"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto mb-2 mt-4">
          <table class="w-full min-w-[1080px] text-left border-collapse" id="customerTable">
            <thead>
              <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                <th class="py-3 px-4 w-12 text-center">No</th>
                <th class="py-3 px-4 text-center w-40">Aksi</th>
                <th class="py-3 px-4">Nama</th>
                <th class="py-3 px-3">Telp.</th>
                <th class="py-3 px-3 text-center">PIC</th>
                <th class="py-3 px-3 text-center">ODP</th>
                <th class="py-3 px-3 text-center">Paket</th>
                <th class="py-3 px-3 text-center">Status</th>
                <th class="py-3 px-3 text-center">Tanggal Installasi</th>
                <th class="py-3 px-3 text-center">History Pembayaran</th>
                <th class="py-3 px-3 text-center">Pembayaran Terakhir</th>
                <th class="py-3 px-4 text-center">Remote IP</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs text-slate-700" id="customerTableBody">
              @foreach ($data as $item)
                <tr class="customer-row table-hover-row transition-colors" data-id="{{ $item->id }}"
                  data-tagihan="{{ $item->invoice->isNotEmpty() && $item->invoice->first()->status ? ($item->invoice->first()->status->nama_status == 'Sudah Bayar' ? '0' : $item->invoice->first()->tagihan ?? '0') : '0' }}"
                  data-customer-id="{{ $item->id }}"
                  data-invoice-id="{{ $item->invoice->isNotEmpty() ? $item->invoice->first()->id : '' }}"
                  data-tagihan-tambahan="{{ $item->invoice->isNotEmpty() ? $item->invoice->first()->tambahan : '' }}">
                  <td class="py-3 px-4 text-center font-medium text-slate-400">
                    {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                  </td>

                  <!-- Aksi -->
                  <td class="py-3 px-4">
                    <div class="flex flex-wrap items-center justify-center gap-1.5">
                      @if (auth()->user()->roles_id == 1 || auth()->user()->roles_id == 2 || auth()->user()->roles_id == 4)
                        <a href="/blokir/{{ $item->id }}"
                          class="blokir-customer-btn w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-rose-600 flex items-center justify-center text-sm transition-colors"
                          data-bs-toggle="tooltip" data-bs-placement="top" title="Blokir Pelanggan">
                          <i class="bx bx-block"></i>
                        </a>
                        <a href="/unblokir/{{ $item->id }}"
                          class="unblokir-customer-btn w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-amber-50 hover:border-amber-200 text-amber-600 flex items-center justify-center text-sm transition-colors"
                          data-bs-toggle="tooltip" data-bs-placement="top" title="Aktifkan Pelanggan">
                          <i class="bx bx-check-circle"></i>
                        </a>
                      @endif
                      <a href="/detail-pelanggan/{{ $item->id }}"
                        class="detail-customer-btn w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-200 text-blue-600 flex items-center justify-center text-sm transition-colors"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Detail Pelanggan">
                        <i class="bx bx-show"></i>
                      </a>
                      <a href="/edit-pelanggan/{{ $item->id }}"
                        class="detail-customer-btn w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 flex items-center justify-center text-sm transition-colors"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Pelanggan">
                        <i class="bx bx-pencil"></i>
                      </a>
                    </div>
                  </td>

                  <!-- Nama -->
                  <td class="py-3 px-4 customer-name">
                    <div class="font-bold text-slate-800">{{ $item->nama_customer }}</div>
                    <div class="text-[10px] text-slate-400">{{ $item->alamat }}</div>
                  </td>

                  <!-- Telp -->
                  <td class="py-3 px-3 nomor-hp whitespace-nowrap text-slate-600">{{ $item->no_hp }}</td>

                  <!-- PIC -->
                  <td class="py-3 px-3 text-center font-semibold text-slate-700">{{ $item->agen->name ?? '-' }}</td>

                  <!-- ODP -->
                  <td class="py-3 px-3 text-center">
                    @if($item->media_id == 3 && isset($item->odp))
                      @php
                        $gps = $item->odp->gps;
                        $isLink = $gps && Str::startsWith($gps, ['http://', 'https://']);
                        $url = $gps ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                      @endphp
                      <a href="{{ $url }}" {{ $url != '#' ? 'target=_blank' : '' }}
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 no-underline">
                        <i class="bx bx-map-pin text-sm"></i>{{ $item->odp->nama_odp ?? '-' }}
                      </a>
                    @else
                      <span class="text-slate-300 font-medium">-</span>
                    @endif
                  </td>

                  <!-- Paket -->
                  <td class="py-3 px-3 text-center nama-paket">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                      {{ $item->paket->nama_paket ?? '-' }}
                    </span>
                  </td>

                  <!-- Status -->
                  <td class="py-3 px-3 text-center">
                    @if ($item->status_id == 3)
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 whitespace-nowrap">
                        <i class="bx bx-check-circle text-sm"></i> Aktif
                      </span>
                    @elseif($item->status_id == 9)
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60 whitespace-nowrap uppercase">
                        <i class="bx bx-block text-sm"></i> Isolire Billing
                      </span>
                    @elseif($item->status_id == 4)
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 whitespace-nowrap uppercase">
                        <i class="bx bx-wrench text-sm"></i> Maintenance
                      </span>
                    @else
                      <span class="text-slate-300 font-medium">-</span>
                    @endif
                  </td>

                  <!-- Tanggal Installasi -->
                  <td class="py-3 px-3 text-center">
                    @if($item->tanggal_selesai == null)
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                        <i class="bx bx-import text-sm"></i> Import data
                      </span>
                    @else
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 whitespace-nowrap">
                        {{ is_numeric($item->tanggal_selesai) ? '-' : (\Carbon\Carbon::parse($item->tanggal_selesai)->format('d-F-Y')) }}
                      </span>
                    @endif
                  </td>

                  <!-- History Pembayaran -->
                  <td class="py-3 px-3 text-center">
                    <a href="/riwayatPembayaran/{{ $item->id }}"
                      class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-200 text-blue-600 inline-flex items-center justify-center text-sm transition-colors"
                      data-bs-toggle="tooltip" title="History Pembayaran {{ $item->nama_customer }}"
                      data-bs-placement="top">
                      <i class="bx bx-history"></i>
                    </a>
                  </td>

                  <!-- Pembayaran Terakhir -->
                  <td class="py-3 px-3 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/80 whitespace-nowrap">
                      {{ $item->last_pembayaran ?? '-' }}
                    </span>
                  </td>

                  <!-- Remote IP -->
                  <td class="py-3 px-4">
                    <div class="flex items-center justify-center gap-1.5">
                      <a href="http://{{ $item->remote ?? '#' }}" target="_blank"
                        class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-amber-50 hover:border-amber-200 text-amber-600 flex items-center justify-center text-sm transition-colors"
                        data-bs-toggle="tooltip" title="Akses Remote" data-bs-placement="bottom">
                        <i class="bx bx-cloud"></i>
                      </a>
                      <a href="/traffic-pelanggan/{{ $item->id }}"
                        class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-200 text-blue-600 flex items-center justify-center text-sm transition-colors"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Traffic {{ $item->nama_customer }}">
                        <i class="bx bx-chart"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              @endforeach

              <!-- Total Pendapatan -->
              <tr class="bg-slate-50 border-t-2 border-slate-200">
                <td colspan="12" class="py-3.5 px-4">
                  <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                    <i class="bx bx-wallet text-emerald-600"></i> Total Pendapatan:
                    <span class="font-black text-emerald-700">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</span>
                  </span>
                </td>
              </tr>
            </tbody>
          </table>

          @if(empty($item))
            <div class="d-flex flex-column align-items-center justify-content-center py-5" id="noDataMessage">
              <div class="mb-3">
                <i class="bx bx-user-x" style="font-size: 4rem; color: #94a3b8;"></i>
              </div>
              <h5 class="text-slate-500 font-semibold mb-2">Belum Ada Pelanggan</h5>
              <p class="text-xs text-slate-400">Saat ini belum ada data pelanggan yang terdaftar.</p>
            </div>
          @endif
          <div class="hidden text-center py-8" id="noResults">
            <p class="m-0 text-sm text-slate-400">Tidak ada data pelanggan yang sesuai dengan pencarian.</p>
          </div>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500" id="paginationContainer">
          <div>
            Menampilkan
            <span class="font-bold text-slate-700">{{ $data->firstItem() ?? 0 }}</span> -
            <span class="font-bold text-slate-700">{{ $data->lastItem() ?? 0 }}</span>
            dari <span class="font-bold text-slate-700">{{ number_format($data->total(), 0, ',', '.') }}</span> data
          </div>

          @if($data->hasPages())
            <div class="flex items-center gap-1">
              @if($data->onFirstPage())
                <span class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-300 flex items-center justify-center opacity-50">
                  <i class="bx bx-chevron-left text-sm"></i>
                </span>
              @else
                <a href="{{ $data->url($data->currentPage() - 1) }}" title="Halaman Sebelumnya"
                  class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 font-semibold text-xs transition flex items-center justify-center no-underline">
                  <i class="bx bx-chevron-left text-sm"></i>
                </a>
              @endif

              @foreach($paginationPages as $p)
                @if($p === '...')
                  <span class="w-6 text-center text-slate-400 text-xs font-mono select-none">...</span>
                @elseif($p == $data->currentPage())
                  <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-sm flex items-center justify-center">{{ $p }}</span>
                @else
                  <a href="{{ $data->url($p) }}"
                    class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 font-semibold text-xs transition flex items-center justify-center no-underline">{{ $p }}</a>
                @endif
              @endforeach

              @if($data->hasMorePages())
                <a href="{{ $data->url($data->currentPage() + 1) }}" title="Halaman Selanjutnya"
                  class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 font-semibold text-xs transition flex items-center justify-center no-underline">
                  <i class="bx bx-chevron-right text-sm"></i>
                </a>
              @else
                <span class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-300 flex items-center justify-center opacity-50">
                  <i class="bx bx-chevron-right text-sm"></i>
                </span>
              @endif
            </div>
          @endif
        </div>

      </div>
    </div>

  </div>

  {{-- Modal Antrian --}}
  <div class="modal fade app-modal" id="antrian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
              <i class="bx bx-hourglass"></i>
            </div>
            <h5 class="modal-title">Antrian Pelanggan hari ini</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                  <th class="py-2.5 px-4 text-center w-12">No</th>
                  <th class="py-2.5 px-4">Nama Pelanggan</th>
                  <th class="py-2.5 px-3 text-center">Status</th>
                  <th class="py-2.5 px-3 text-center">Teknisi</th>
                  <th class="py-2.5 px-3 text-center">Tanggal Registrasi</th>
                  <th class="py-2.5 px-3 text-center">Tanggal Selesai</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                @forelse ($antrian as $item)
                  <tr class="table-hover-row transition-colors">
                    <td class="py-3 px-4 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4">
                      <div class="font-bold text-slate-800">{{ $item->nama_customer }}</div>
                      <div class="text-[10px] text-slate-400">{{ $item->alamat }}</div>
                    </td>
                    <td class="py-3 px-3 text-center">
                      @if ($item->status_id == 1)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">Menunggu</span>
                      @elseif($item->status_id == 2)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">On Progress</span>
                      @elseif($item->status_id == 5)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">Assigment</span>
                      @endif
                    </td>
                    <td class="py-3 px-3 text-center font-semibold text-slate-700">{{ $item->teknisi->name ?? '-' }}</td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 whitespace-nowrap">
                        {{ $item->created_at ?? '-' }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 whitespace-nowrap">
                        {{ $item->tanggal_selesai ?? '-' }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="py-10 text-center text-slate-400">
                      <i class="bx bx-hourglass text-3xl text-slate-300 block mb-2"></i>
                      <div class="font-medium text-slate-600">Tidak ada data antrian</div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Selesai --}}
  <div class="modal fade app-modal" id="selesai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
              <i class="bx bx-check-circle"></i>
            </div>
            <h5 class="modal-title">Installasi hari ini</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                  <th class="py-2.5 px-4 text-center w-12">No</th>
                  <th class="py-2.5 px-4">Nama Pelanggan</th>
                  <th class="py-2.5 px-3 text-center">Status</th>
                  <th class="py-2.5 px-3 text-center">Teknisi</th>
                  <th class="py-2.5 px-3 text-center">Tanggal Registrasi</th>
                  <th class="py-2.5 px-3 text-center">Tanggal Selesai</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                @forelse ($selesai as $item)
                  <tr class="table-hover-row transition-colors">
                    <td class="py-3 px-4 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4">
                      <div class="font-bold text-slate-800">{{ $item->nama_customer }}</div>
                      <div class="text-[10px] text-slate-400">{{ $item->alamat }}</div>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Selesai</span>
                    </td>
                    <td class="py-3 px-3 text-center font-semibold text-slate-700">{{ $item->teknisi->name ?? '-' }}</td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 whitespace-nowrap">
                        {{ $item->created_at ?? '-' }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 whitespace-nowrap">
                        {{ $item->updated_at ?? '-' }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="py-10 text-center text-slate-400">
                      <i class="bx bx-check-circle text-3xl text-slate-300 block mb-2"></i>
                      <div class="font-medium text-slate-600">Belum ada instalasi hari ini</div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Bulanan --}}
  <div class="modal fade app-modal" id="bulanan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
              <i class="bx bx-calendar"></i>
            </div>
            <h5 class="modal-title">Instalasi Bulan ini</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                  <th class="py-2.5 px-4 text-center w-12">No</th>
                  <th class="py-2.5 px-4">Nama Pelanggan</th>
                  <th class="py-2.5 px-3 text-center">Status</th>
                  <th class="py-2.5 px-3 text-center">Teknisi</th>
                  <th class="py-2.5 px-3 text-center">Tanggal Registrasi</th>
                  <th class="py-2.5 px-3 text-center">Tanggal Selesai</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                @forelse ($installasiBulanan as $item)
                  <tr class="table-hover-row transition-colors">
                    <td class="py-3 px-4 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4">
                      <div class="font-bold text-slate-800">{{ $item->nama_customer }}</div>
                      <div class="text-[10px] text-slate-400">{{ $item->alamat }}</div>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Selesai</span>
                    </td>
                    <td class="py-3 px-3 text-center font-semibold text-slate-700">{{ $item->teknisi->name ?? '-' }}</td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($item->created_at)->locale('id')->translatedFormat('d F Y') }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($item->tanggal_selesai)->locale('id')->translatedFormat('d F Y') }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="py-10 text-center text-slate-400">
                      <i class="bx bx-calendar text-3xl text-slate-300 block mb-2"></i>
                      <div class="font-medium text-slate-600">Belum ada instalasi bulan ini</div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Pelanggan Blokir --}}
  <div class="modal fade app-modal" id="blokir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
              <i class="bx bx-block"></i>
            </div>
            <h5 class="modal-title">Pelanggan Isolire Billing</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                  <th class="py-2.5 px-4 text-center w-12">No</th>
                  <th class="py-2.5 px-4">Nama Pelanggan</th>
                  <th class="py-2.5 px-3 text-center">No HP</th>
                  <th class="py-2.5 px-3 text-center">Paket</th>
                  <th class="py-2.5 px-3 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                @forelse ($nonAktif as $item)
                  <tr class="table-hover-row transition-colors">
                    <td class="py-3 px-4 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4">
                      <div class="font-bold text-slate-800">{{ $item->nama_customer }}</div>
                      <div class="text-[10px] text-slate-400">{{ $item->alamat }}</div>
                    </td>
                    <td class="py-3 px-3 text-center text-slate-600">{{ $item->no_hp }}</td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                        {{ $item->paket->nama_paket }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60 uppercase">
                        <i class="bx bx-block text-sm"></i> Isolire Billing
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="py-10 text-center text-slate-400">
                      <i class="bx bx-block text-3xl text-slate-300 block mb-2"></i>
                      <div class="font-medium text-slate-600">Tidak ada pelanggan terisolir</div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Pelanggan Maintenance --}}
  <div class="modal fade app-modal" id="maintenance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
              <i class="bx bx-wrench"></i>
            </div>
            <h5 class="modal-title">List Tiket Open</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                  <th class="py-2.5 px-4 text-center w-12">No</th>
                  <th class="py-2.5 px-4">Nama Pelanggan</th>
                  <th class="py-2.5 px-3 text-center">No HP</th>
                  <th class="py-2.5 px-3 text-center">Paket</th>
                  <th class="py-2.5 px-3 text-center">Status Tiket</th>
                  <th class="py-2.5 px-3 text-center">Kategori Tiket</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                @forelse ($maintenance as $item)
                  <tr class="table-hover-row transition-colors">
                    <td class="py-3 px-4 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4">
                      <div class="font-bold text-slate-800">{{ $item->customer->nama_customer }}</div>
                      <div class="text-[10px] text-slate-400">{{ $item->customer->alamat }}</div>
                    </td>
                    <td class="py-3 px-3 text-center text-slate-600">{{ $item->customer->no_hp }}</td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                        {{ $item->customer->paket->nama_paket }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                        {{ $item->status->nama_status }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/80">
                        {{ $item->kategori->nama_kategori }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="py-10 text-center text-slate-400">
                      <i class="bx bx-wrench text-3xl text-slate-300 block mb-2"></i>
                      <div class="font-medium text-slate-600">Tidak ada tiket open</div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Pelanggan Dismantle --}}
  <div class="modal fade app-modal" id="dismantle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
              <i class="bx bx-archive-in"></i>
            </div>
            <h5 class="modal-title">Pelanggan Dismantle</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                  <th class="py-2.5 px-4 text-center w-12">No</th>
                  <th class="py-2.5 px-4">Nama Pelanggan</th>
                  <th class="py-2.5 px-3 text-center">No HP</th>
                  <th class="py-2.5 px-3 text-center">Paket</th>
                  <th class="py-2.5 px-3 text-center">Tanggal Dismantle</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                @forelse ($dismantleGet as $item)
                  <tr class="table-hover-row transition-colors">
                    <td class="py-3 px-4 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4">
                      <div class="font-bold text-slate-800">{{ $item->nama_customer }}</div>
                      <div class="text-[10px] text-slate-400">{{ $item->alamat }}</div>
                    </td>
                    <td class="py-3 px-3 text-center text-slate-600">{{ $item->no_hp }}</td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                        {{ $item->paket->nama_paket }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/80 whitespace-nowrap">
                        {{ $item->deleted_at ? $item->deleted_at->translatedFormat('d F Y') : 'N/A' }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="py-10 text-center text-slate-400">
                      <i class="bx bx-archive-in text-3xl text-slate-300 block mb-2"></i>
                      <div class="font-medium text-slate-600">Tidak ada pelanggan dismantle</div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection


@section('page-script')
  <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // DOM Elements
      const searchInput = document.getElementById('searchCustomer');
      const searchButton = document.getElementById('searchButton');
      const sortSelect = document.getElementById('sortSelect');
      const pageSizeSelect = document.getElementById('pageSize');
      const agenSelect = document.getElementById('agenSelect');

      // Set current values from URL parameters
      const urlParams = new URLSearchParams(window.location.search);
      searchInput.value = urlParams.get('search') || '';
      sortSelect.value = urlParams.get('sort') || 'default';
      pageSizeSelect.value = urlParams.get('per_page') || '10';
      if (agenSelect) {
        agenSelect.value = urlParams.get('agen_id') || '';
      }

      // Function to update URL and reload page
      function updatePage() {
        const params = new URLSearchParams();

        if (searchInput.value.trim()) {
          params.set('search', searchInput.value.trim());
        }

        if (sortSelect.value !== 'default') {
          params.set('sort', sortSelect.value);
        }

        if (pageSizeSelect.value !== '10') {
          params.set('per_page', pageSizeSelect.value);
        }

        if (agenSelect && agenSelect.value) {
          params.set('agen_id', agenSelect.value);
        }

        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.location.href = newUrl;
      }

      // Search functionality
      function performSearch() {
        updatePage();
      }

      // Event Listeners
      searchButton.addEventListener('click', performSearch);

      searchInput.addEventListener('keyup', function (event) {
        if (event.key === 'Enter') {
          performSearch();
        }
      });

      // Real-time search with debounce
      let searchTimeout;
      searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          if (this.value.trim() === '' && urlParams.get('search')) {
            updatePage();
          }
        }, 500);
      });

      // Sort dropdown change
      sortSelect.addEventListener('change', updatePage);

      // Page size change
      pageSizeSelect.addEventListener('change', updatePage);

      // Initialize Tom Select for Agent Filter
      if (agenSelect) {
        if (typeof TomSelect !== 'undefined') {
          new TomSelect('#agenSelect', {
            create: false,
            allowEmptyOption: true,
            onChange: function (value) {
              updatePage();
            }
          });
        } else {
          agenSelect.addEventListener('change', updatePage);
        }
      }

      // Initialize tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Tutorial functionality setup using driver.js
      if (typeof window.driver !== 'undefined') {
        const driver = window.driver.js.driver;
        const driverObj = driver({
          showProgress: true,
          nextBtnText: 'Lanjut &rarr;',
          prevBtnText: '&larr; Kembali',
          doneBtnText: 'Selesai',
          steps: [
            { element: '#tutorial-sort-container', popover: { title: 'Fitur Pengurutan', description: 'Gunakan fitur ini untuk mengurutkan daftar pelanggan berdasarkan berbagai kriteria yang Anda inginkan.', side: "bottom", align: 'start' } },
            { element: '#sortSelect', popover: { title: 'Pilih Kriteria', description: 'Klik dropdown ini untuk memilih kriteria pengurutan seperti Default (waktu selesai pengerjaan), Nama (A-Z/Z-A), atau Status Pelanggan Aktif/Non-Aktif.', side: "bottom", align: 'start' } }
          ]
        });

        document.getElementById('btn-tutorial-sort').addEventListener('click', function () {
          driverObj.drive();
        });
      }
    });
  </script>

  <script>
    // Function to update customer counters (total, active, non-active)
    function updateCustomerCounters() {
      const tableBody = document.querySelector('#customerTable tbody');
      if (!tableBody) return;

      const allRows = tableBody.querySelectorAll('tr.customer-row');
      const counts = {
        total: allRows.length,
        active: 0,
        nonActive: 0,
        pembayaran: 0
      };

      allRows.forEach(row => {
        const statusCell = row.querySelector('td:nth-child(7)');
        if (statusCell && statusCell.textContent.trim().includes('Aktif')) {
          counts.active++;
        } else {
          counts.nonActive++;
        }

        // Increment pembayaran count based on some condition
        const paymentCell = row.querySelector('td:nth-child(8)'); // Assuming payment status is in the 8th column
        if (paymentCell && paymentCell.textContent.trim() === 'Belum Dibayar') {
          counts.pembayaran++;
        }
      });

      // Update badges using a more robust method
      const updateBadge = (selector, value) => {
        const badge = document.querySelector(selector);
        if (badge) badge.textContent = value;
      };

      updateBadge('.data-card.primary-card .badge.bg-primary', counts.total);
      updateBadge('.data-card.success-card .badge.bg-success', counts.active);
      updateBadge('.data-card.danger-card .badge.bg-danger', counts.nonActive);
    }

    document.addEventListener('DOMContentLoaded', function () {
      // Initialize state management
      const state = {
        lastUpdate: null,
        updateQueue: new Set(),
        isUpdating: false
      };

      // Initialize customer counters

      // Improved real-time updates handling
      window.Echo.channel('updates-data')
        .listen('.data.updated', handleRealTimeUpdate);

      // Call updateCustomerCounters after handling real-time updates
      handleRealTimeUpdate(e); // Call the function to update counters after handling real-time updates

      function handleRealTimeUpdate(e) {
        console.log('Received real-time update:', e);

        showNotification(e.notification);

        if (e.data) {
          const dataArray = Array.isArray(e.data) ? e.data : [e.data];
          queueDataUpdate(dataArray);
        }
      }

      function showNotification(notification) {
        if (!notification) return;

        Swal.fire({
          text: notification.message,
          icon: notification.type,
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          topLayer: true,
          animation: true,
          customClass: {
            popup: 'animated fadeInDown'
          }
        });
      }
    });
  </script>

@endsection
