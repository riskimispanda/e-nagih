@extends('layouts.contentNavbarLayout')

@section('title', 'Data Logistik & Inventori')

@section('vendor-style')
{{-- Tailwind CSS CDN --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            50: '#eff6ff',
            100: '#dbeafe',
            200: '#bfdbfe',
            500: '#3b82f6',
            600: '#2563eb',
            700: '#1d4ed8',
          },
        },
        fontFamily: {
          sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
        },
        boxShadow: {
          'card': '0 2px 8px -2px rgba(15, 23, 42, 0.06), 0 12px 24px -6px rgba(15, 23, 42, 0.08)',
          'card-hover': '0 8px 16px -4px rgba(15, 23, 42, 0.08), 0 20px 32px -8px rgba(15, 23, 42, 0.12)',
        },
      },
    },
  }
</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>

<style>
  body { font-family: 'Inter', sans-serif; }

  /* Custom Scrollbar */
  .custom-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
  .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 9999px; }
  .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
  .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

  /* Tab Navigation Styling */
  #logistikTabs .nav-link {
    color: #64748b;
    background-color: transparent;
    border: 1px solid transparent;
    border-radius: 0.75rem;
    padding: 0.55rem 1rem;
    font-weight: 600;
    font-size: 0.8125rem;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }
  #logistikTabs .nav-link:hover {
    background-color: #f8fafc;
    color: #1e293b;
  }
  #logistikTabs .nav-link.active {
    color: #2563eb;
    background-color: #eff6ff;
    border-color: #bfdbfe;
    box-shadow: 0 1px 3px rgba(37, 99, 235, 0.1);
  }

  /* Table Row Smooth Animation */
  .tr-smooth {
    transition: all 0.18s ease-in-out;
  }
  .tr-smooth:hover {
    background-color: #f8fafc !important;
  }

  /* Category Badge Color Scheme */
  .cat-badge-modem    { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
  .cat-badge-kabel    { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
  .cat-badge-splitter { background: #fffbeb; color: #b45309; border-color: #fde68a; }
  .cat-badge-odp      { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
  .cat-badge-odc      { background: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }
  .cat-badge-olt      { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
  .cat-badge-default  { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

  /* Modal Customizations */
  .modal-content {
    border: none;
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  }
</style>
@endsection

@section('content')
<div class="space-y-5 max-w-[1500px] mx-auto pb-8">

  <!-- ================= BREADCRUMB ================= -->
  <nav aria-label="breadcrumb" class="mb-1">
    <ol class="flex items-center gap-1.5 text-xs text-slate-500 mb-0 p-0 list-none">
      <li>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 font-medium transition-colors no-underline">
          <i class="bx bx-home-alt text-sm"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="text-slate-300">/</li>
      <li class="text-slate-500 font-medium">Logistik</li>
      <li class="text-slate-300">/</li>
      <li class="text-blue-600 font-semibold">Data Logistik & Inventori</li>
    </ol>
  </nav>

  <!-- ================= HERO HEADER BANNER ================= -->
  <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-800 p-5 sm:p-6 text-white shadow-lg shadow-blue-900/20">
    <!-- Ambient Glow Backgrounds -->
    <div class="absolute -right-10 -top-10 h-56 w-56 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-10 -bottom-10 h-56 w-56 rounded-full bg-indigo-400/20 blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div class="space-y-1">
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/15 backdrop-blur-md text-[11px] font-semibold tracking-wide uppercase border border-white/20 text-blue-100 mb-1">
          <i class="bx bx-package text-xs"></i> Warehouse & Asset Management
        </div>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white m-0">Data Logistik & Stok Perangkat</h1>
        <p class="text-xs sm:text-sm text-blue-100/90 max-w-2xl m-0 leading-relaxed">
          Pusat monitoring ketersediaan perangkat jaringan, status pemakaian pelanggan, pemeliharaan (maintenance), dan tracking aset.
        </p>
      </div>

      <!-- Hero Action Buttons -->
      <div class="flex flex-wrap items-center gap-2.5 pt-1 lg:pt-0">
        <button type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-blue-700 hover:bg-blue-50 text-xs font-bold transition-all shadow-sm hover:shadow active:scale-95 cursor-pointer border-0 outline-none" data-bs-toggle="modal" data-bs-target="#perangkat">
          <i class="bx bx-plus-circle text-base"></i>
          <span>Tambah Stok</span>
        </button>
        <button type="button" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-semibold backdrop-blur-md border border-white/30 transition-all active:scale-95 cursor-pointer outline-none" data-bs-toggle="modal" data-bs-target="#kategori">
          <i class="bx bx-category text-base"></i>
          <span>Tambah Kategori</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ================= MAIN INVENTORY CONTAINER ================= -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
    
    <!-- Top Control Bar (Search & Tabs) -->
    <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/70 flex flex-col gap-3.5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        
        <!-- Search Input with Icon -->
        <div class="relative w-full sm:w-80">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
            <i class="bx bx-search text-base"></i>
          </span>
          <input type="text" id="search" class="w-full rounded-xl border border-slate-300 bg-white pl-9 pr-3.5 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-xs" placeholder="Cari nama, kategori, MAC, atau SN...">
        </div>

        <!-- Refresh / Quick Filter Controls -->
        <div class="flex items-center gap-2 self-end sm:self-auto">
          <button type="button" onclick="location.reload()" title="Segarkan Data" class="px-3 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition-all flex items-center gap-1.5 shadow-xs cursor-pointer">
            <i class="bx bx-refresh text-sm"></i>
            <span>Refresh</span>
          </button>
        </div>
      </div>

      <!-- Navigation Tabs (Pill style with badge counts) -->
      <div class="overflow-x-auto custom-scroll -mx-1 px-1 pb-1">
        <ul class="flex items-center gap-2 min-w-max" id="logistikTabs" role="tablist">
          <li role="presentation">
            <button class="nav-link inline-flex items-center gap-2 active cursor-pointer" id="global-tab" data-bs-toggle="tab" data-bs-target="#global-pane" type="button" role="tab" aria-selected="true">
              <i class="bx bx-package text-base"></i>
              <span>Stok Global Master</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200/70 text-slate-700">{{ $perangkat->count() }}</span>
            </button>
          </li>
          <li role="presentation">
            <button class="nav-link inline-flex items-center gap-2 cursor-pointer" id="available-tab" data-bs-toggle="tab" data-bs-target="#available-pane" type="button" role="tab" aria-selected="false">
              <i class="bx bx-check-shield text-base text-emerald-600"></i>
              <span>Siap Pakai / Tersedia</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">{{ count($availableDevices) }}</span>
            </button>
          </li>
          <li role="presentation">
            <button class="nav-link inline-flex items-center gap-2 cursor-pointer" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance-pane" type="button" role="tab" aria-selected="false">
              <i class="bx bx-wrench text-base text-amber-600"></i>
              <span>Dalam Maintenance</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">{{ count($maintenanceDevices) }}</span>
            </button>
          </li>
          <li role="presentation">
            <button class="nav-link inline-flex items-center gap-2 cursor-pointer" id="damaged-tab" data-bs-toggle="tab" data-bs-target="#damaged-pane" type="button" role="tab" aria-selected="false">
              <i class="bx bx-x-circle text-base text-rose-600"></i>
              <span>Barang Rusak / Afkir</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">{{ count($damagedDevices) }}</span>
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- ================= TAB CONTENT PANES ================= -->
    <div class="tab-content p-0" id="logistikTabContent">

      <!-- ================= 1. TAB: STOK GLOBAL ================= -->
      <div class="tab-pane fade show active" id="global-pane" role="tabpanel" aria-labelledby="global-tab">
        <div class="overflow-x-auto custom-scroll">
          <table id="dataTable" class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10.5px]">
                <th class="px-4 py-3.5 text-center w-12">No.</th>
                <th class="px-4 py-3.5">Nama Perangkat & Kategori</th>
                <th class="px-3 py-3.5 text-center">Tersedia</th>
                <th class="px-3 py-3.5 text-center">Terpakai</th>
                <th class="px-3 py-3.5 text-center">Maintenance</th>
                <th class="px-3 py-3.5 text-center">Rusak</th>
                <th class="px-4 py-3.5 text-right">Harga Satuan</th>
                <th class="px-4 py-3.5 text-right">Total Nilai Aset</th>
                <th class="px-4 py-3.5 text-center w-24">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
              @forelse ($perangkat as $p)
              @php
                $kategoriLabel = $p->kategori->nama_logistik ?? '-';
                $isKabel = strtolower($kategoriLabel) == 'kabel';
                $isSerial = in_array(strtolower($kategoriLabel), ['modem','tenda','sfp','olt','odp','odc','htb','splitter']);
                $totalHarga = $isKabel ? $p->harga : ($p->harga * $p->jumlah_stok);

                $catLower = strtolower($kategoriLabel);
                $catClass = 'cat-badge-default';
                if (str_contains($catLower, 'modem') || str_contains($catLower, 'tenda')) $catClass = 'cat-badge-modem';
                elseif (str_contains($catLower, 'kabel')) $catClass = 'cat-badge-kabel';
                elseif (str_contains($catLower, 'splitter')) $catClass = 'cat-badge-splitter';
                elseif (str_contains($catLower, 'odp')) $catClass = 'cat-badge-odp';
                elseif (str_contains($catLower, 'odc')) $catClass = 'cat-badge-odc';
                elseif (str_contains($catLower, 'olt')) $catClass = 'cat-badge-olt';
              @endphp
              <tr class="device-row searchable-row tr-smooth">
                <td class="px-4 py-3 text-center text-slate-400 font-semibold row-number">{{ $loop->iteration }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-base flex-shrink-0">
                      <i class="bx bx-chip"></i>
                    </div>
                    <div class="min-w-0">
                      <div class="font-bold text-slate-800 text-[12.5px] leading-tight truncate">{{ $p->nama_perangkat }}</div>
                      <div class="flex items-center gap-1.5 mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase tracking-wider {{ $catClass }}">
                          {{ $kategoriLabel }}
                        </span>
                        <span class="text-[10.5px] text-slate-400">Total: {{ number_format($p->jumlah_stok) }} {{ $isKabel ? 'm' : 'unit' }}</span>
                      </div>
                    </div>
                  </div>
                </td>
                
                <!-- Tersedia -->
                <td class="px-3 py-3 text-center">
                  <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 min-w-[70px]">
                    {{ number_format($p->stok_tersedia) }} {{ $isKabel ? 'm' : 'unit' }}
                  </span>
                </td>

                <!-- Terpakai -->
                <td class="px-3 py-3 text-center">
                  <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 min-w-[70px]">
                    {{ number_format($p->stok_terpakai) }} {{ $isKabel ? 'm' : 'unit' }}
                  </span>
                </td>

                <!-- Maintenance -->
                <td class="px-3 py-3 text-center">
                  @if($isSerial && $p->stok_maintenance > 0)
                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 min-w-[60px]">
                      {{ $p->stok_maintenance }} unit
                    </span>
                  @else
                    <span class="text-slate-300 font-mono">-</span>
                  @endif
                </td>

                <!-- Rusak -->
                <td class="px-3 py-3 text-center">
                  @if($isSerial && $p->stok_rusak > 0)
                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 min-w-[60px]">
                      {{ $p->stok_rusak }} unit
                    </span>
                  @else
                    <span class="text-slate-300 font-mono">-</span>
                  @endif
                </td>

                <!-- Harga Satuan -->
                <td class="px-4 py-3 text-right font-medium text-slate-600 font-mono">
                  Rp {{ number_format($p->harga, 0, ',', '.') }}
                </td>

                <!-- Total Nilai Aset -->
                <td class="px-4 py-3 text-right font-bold text-slate-900 font-mono text-[12.5px]">
                  Rp {{ number_format($totalHarga, 0, ',', '.') }}
                </td>

                <!-- Aksi -->
                <td class="px-4 py-3 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <a href="/edit-logistik/{{ $p->id }}" data-bs-toggle="tooltip" title="Edit Rincian Stok" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 flex items-center justify-center transition-all">
                      <i class="bx bx-edit text-sm"></i>
                    </a>
                    <button type="button" onclick="hapusLogistik({{ $p->id }})" data-bs-toggle="tooltip" title="Hapus Perangkat" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center transition-all border-0 cursor-pointer">
                      <i class="bx bx-trash text-sm"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @empty
              <tr id="noDataResults">
                <td colspan="9" class="px-4 py-16 text-center text-slate-400 bg-white">
                  <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-2 text-2xl">
                    <i class="bx bx-package"></i>
                  </div>
                  <div class="font-bold text-slate-700 text-sm">Belum Ada Data Perangkat</div>
                  <div class="text-xs text-slate-400 mt-0.5">Tambahkan stok perangkat baru melalui tombol di atas</div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination Global -->
        <div id="pagination-global" class="px-4 py-3 bg-slate-50/70 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
          <div class="flex items-center gap-2">
            <span>Tampilkan</span>
            <select class="page-size-select rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 cursor-pointer shadow-xs" data-target="global">
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <span>baris</span>
            <span class="text-slate-300 mx-1">|</span>
            <span id="info-global" class="font-medium text-slate-600">Menampilkan data...</span>
          </div>
          <div id="nav-global" class="flex items-center gap-1"></div>
        </div>
      </div>

      <!-- ================= 2. TAB: SIAP PAKAI / TERSEDIA ================= -->
      <div class="tab-pane fade" id="available-pane" role="tabpanel" aria-labelledby="available-tab">
        <div class="overflow-x-auto custom-scroll">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10.5px]">
                <th class="px-4 py-3.5 text-center w-12">No.</th>
                <th class="px-4 py-3.5">Nama Perangkat</th>
                <th class="px-4 py-3.5">Kategori</th>
                <th class="px-4 py-3.5">MAC Address</th>
                <th class="px-4 py-3.5">Serial Number (SN)</th>
                <th class="px-3 py-3.5 text-center">Status</th>
                <th class="px-4 py-3.5 text-center w-36">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
              @forelse ($availableDevices as $dev)
              <tr class="searchable-row tr-smooth">
                <td class="px-4 py-3 text-center text-slate-400 font-semibold row-number">{{ $loop->iteration }}</td>
                <td class="px-4 py-3 font-bold text-slate-800 text-[12px]">{{ $dev->perangkat->nama_perangkat ?? '-' }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase tracking-wider bg-slate-50 text-slate-600 border-slate-200">
                    {{ $dev->perangkat->kategori->nama_logistik ?? '-' }}
                  </span>
                </td>
                <td class="px-4 py-3 font-mono text-slate-600">
                  {{ $dev->mac_address ?: '-' }}
                </td>
                <td class="px-4 py-3 font-mono font-semibold text-blue-700">
                  {{ $dev->serial_number ?: '-' }}
                </td>
                <td class="px-3 py-3 text-center">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <button type="button" onclick="pindahkanKeMaintenance({{ $dev->id }})" data-bs-toggle="tooltip" title="Kirim ke Maintenance / Perbaikan" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 text-[11px] font-bold border border-amber-200 transition-all cursor-pointer">
                    <i class="bx bx-wrench"></i>
                    <span>Maintenance</span>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="px-4 py-16 text-center text-slate-400 bg-white">
                  <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-2 text-2xl">
                    <i class="bx bx-check-shield"></i>
                  </div>
                  <div class="font-bold text-slate-700 text-sm">Tidak Ada Unit Siap Pakai di Gudang</div>
                  <div class="text-xs text-slate-400 mt-0.5">Semua unit sedang terpakai atau dalam proses perbaikan</div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination Available -->
        <div id="pagination-available" class="px-4 py-3 bg-slate-50/70 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
          <div class="flex items-center gap-2">
            <span>Tampilkan</span>
            <select class="page-size-select rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 cursor-pointer shadow-xs" data-target="available">
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <span>baris</span>
            <span class="text-slate-300 mx-1">|</span>
            <span id="info-available" class="font-medium text-slate-600">Menampilkan data...</span>
          </div>
          <div id="nav-available" class="flex items-center gap-1"></div>
        </div>
      </div>

      <!-- ================= 3. TAB: MAINTENANCE ================= -->
      <div class="tab-pane fade" id="maintenance-pane" role="tabpanel" aria-labelledby="maintenance-tab">
        <div class="overflow-x-auto custom-scroll">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10.5px]">
                <th class="px-4 py-3.5 text-center w-12">No.</th>
                <th class="px-4 py-3.5">Nama Perangkat</th>
                <th class="px-4 py-3.5">Kategori</th>
                <th class="px-4 py-3.5">MAC Address</th>
                <th class="px-4 py-3.5">Serial Number (SN)</th>
                <th class="px-3 py-3.5 text-center">Status</th>
                <th class="px-4 py-3.5 text-center w-48">Tindakan Perbaikan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
              @forelse ($maintenanceDevices as $dev)
              <tr class="searchable-row tr-smooth">
                <td class="px-4 py-3 text-center text-slate-400 font-semibold row-number">{{ $loop->iteration }}</td>
                <td class="px-4 py-3 font-bold text-slate-800 text-[12px]">{{ $dev->perangkat->nama_perangkat ?? '-' }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase tracking-wider bg-slate-50 text-slate-600 border-slate-200">
                    {{ $dev->perangkat->kategori->nama_logistik ?? '-' }}
                  </span>
                </td>
                <td class="px-4 py-3 font-mono text-slate-600">{{ $dev->mac_address ?: '-' }}</td>
                <td class="px-4 py-3 font-mono font-semibold text-amber-700">{{ $dev->serial_number ?: '-' }}</td>
                <td class="px-3 py-3 text-center">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Maintenance
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button type="button" onclick="selesaiPerbaikan({{ $dev->id }})" title="Perbaikan Berhasil & Kembalikan ke Gudang" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[11px] font-bold border border-emerald-200 transition-all cursor-pointer">
                      <i class="bx bx-check-circle"></i>
                      <span>Selesai</span>
                    </button>
                    <button type="button" onclick="afkirBarang({{ $dev->id }})" title="Nyatakan Rusak Total / Afkir" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-800 text-[11px] font-bold border border-rose-200 transition-all cursor-pointer">
                      <i class="bx bx-x-circle"></i>
                      <span>Afkir</span>
                    </button>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="px-4 py-16 text-center text-slate-400 bg-white">
                  <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-2 text-2xl">
                    <i class="bx bx-wrench"></i>
                  </div>
                  <div class="font-bold text-slate-700 text-sm">Tidak Ada Perangkat Dalam Maintenance</div>
                  <div class="text-xs text-slate-400 mt-0.5">Seluruh perangkat berada dalam kondisi prima atau aktif</div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination Maintenance -->
        <div id="pagination-maintenance" class="px-4 py-3 bg-slate-50/70 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
          <div class="flex items-center gap-2">
            <span>Tampilkan</span>
            <select class="page-size-select rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 cursor-pointer shadow-xs" data-target="maintenance">
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <span>baris</span>
            <span class="text-slate-300 mx-1">|</span>
            <span id="info-maintenance" class="font-medium text-slate-600">Menampilkan data...</span>
          </div>
          <div id="nav-maintenance" class="flex items-center gap-1"></div>
        </div>
      </div>

      <!-- ================= 4. TAB: BARANG RUSAK / AFKIR ================= -->
      <div class="tab-pane fade" id="damaged-pane" role="tabpanel" aria-labelledby="damaged-tab">
        <div class="overflow-x-auto custom-scroll">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10.5px]">
                <th class="px-4 py-3.5 text-center w-12">No.</th>
                <th class="px-4 py-3.5">Nama Perangkat</th>
                <th class="px-4 py-3.5">Kategori</th>
                <th class="px-4 py-3.5">MAC Address</th>
                <th class="px-4 py-3.5">Serial Number (SN)</th>
                <th class="px-3 py-3.5 text-center">Status</th>
                <th class="px-4 py-3.5 text-center w-28">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
              @forelse ($damagedDevices as $dev)
              <tr class="searchable-row tr-smooth">
                <td class="px-4 py-3 text-center text-slate-400 font-semibold row-number">{{ $loop->iteration }}</td>
                <td class="px-4 py-3 font-bold text-slate-800 text-[12px]">{{ $dev->perangkat->nama_perangkat ?? '-' }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase tracking-wider bg-slate-50 text-slate-600 border-slate-200">
                    {{ $dev->perangkat->kategori->nama_logistik ?? '-' }}
                  </span>
                </td>
                <td class="px-4 py-3 font-mono text-slate-600">{{ $dev->mac_address ?: '-' }}</td>
                <td class="px-4 py-3 font-mono font-semibold text-rose-700">{{ $dev->serial_number ?: '-' }}</td>
                <td class="px-3 py-3 text-center">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Rusak / Afkir
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <button type="button" onclick="hapusBarangRusak({{ $dev->id }})" title="Hapus Permanen dari Database" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 text-[11px] font-bold transition-all border border-slate-200 cursor-pointer">
                    <i class="bx bx-trash"></i>
                    <span>Buang</span>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="px-4 py-16 text-center text-slate-400 bg-white">
                  <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-2 text-2xl">
                    <i class="bx bx-x-circle"></i>
                  </div>
                  <div class="font-bold text-slate-700 text-sm">Tidak Ada Barang Rusak / Afkir</div>
                  <div class="text-xs text-slate-400 mt-0.5">Inventori tercatat bersih dari unit afkir</div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination Damaged -->
        <div id="pagination-damaged" class="px-4 py-3 bg-slate-50/70 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
          <div class="flex items-center gap-2">
            <span>Tampilkan</span>
            <select class="page-size-select rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 cursor-pointer shadow-xs" data-target="damaged">
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <span>baris</span>
            <span class="text-slate-300 mx-1">|</span>
            <span id="info-damaged" class="font-medium text-slate-600">Menampilkan data...</span>
          </div>
          <div id="nav-damaged" class="flex items-center gap-1"></div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ==================================================================================== -->
<!-- MODAL: TAMBAH STOK PERANGKAT (Responsive Tailwind & Bootstrap UI)                   -->
<!-- ==================================================================================== -->
<div class="modal fade" tabindex="-1" id="perangkat" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-2xl border-0 overflow-hidden shadow-2xl">
      
      <!-- Modal Header (Tanpa Tombol X) -->
      <div class="py-4 px-5 bg-gradient-to-r from-blue-700 to-indigo-700 text-white flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl flex-shrink-0">
          <i class='bx bx-chip'></i>
        </div>
        <div>
          <h5 class="text-base font-bold text-white mb-0 leading-tight">Tambah Stok Logistik</h5>
          <p class="text-[11px] text-blue-100/90 mt-0.5 mb-0">Tambahkan unit perangkat baru ke inventori gudang</p>
        </div>
      </div>

      <!-- Modal Body Form -->
      <div class="p-5 sm:p-6 bg-white">
        <form action="/logistik/store" method="POST" id="addDeviceForm" class="space-y-4">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
            
            <!-- Nama Perangkat -->
            <div class="md:col-span-8 space-y-1">
              <label class="block text-xs font-bold text-slate-700" for="nama_perangkat">
                <i class='bx bx-chip text-blue-600 mr-1'></i>Nama Perangkat <span class="text-rose-500">*</span>
              </label>
              <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-xs" id="nama_perangkat" placeholder="Contoh: Router TP-Link AC1200 / Splitter 1:4" name="nama_perangkat" required>
              <span class="block text-[10.5px] text-slate-400">Nama lengkap atau tipe perangkat spesifik</span>
            </div>

            <!-- Kategori -->
            <div class="md:col-span-4 space-y-1">
              <label class="block text-xs font-bold text-slate-700" for="kategori_id">
                <i class='bx bx-category text-blue-600 mr-1'></i>Kategori <span class="text-rose-500">*</span>
              </label>
              <select name="kategori_id" id="kategori_id" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-xs cursor-pointer" required>
                <option value="" selected disabled>Pilih Kategori</option>
                @foreach ($kategori as $kate)
                  <option value="{{ $kate->id }}" data-nama="{{ $kate->nama_logistik }}">{{ $kate->nama_logistik }}</option>
                @endforeach
              </select>
              <span class="block text-[10.5px] text-slate-400">Kelompok kategori logistik</span>
            </div>

            <!-- Jumlah Stok -->
            <div class="md:col-span-6 space-y-1">
              <label class="block text-xs font-bold text-slate-700" for="jumlah_stok">
                <i class='bx bx-package text-blue-600 mr-1'></i>Jumlah Stok Unit/Meter <span class="text-rose-500">*</span>
              </label>
              <input name="jumlah_stok" type="number" id="jumlah_stok" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-xs" placeholder="Contoh: 10" min="1" required>
              <span class="block text-[10.5px] text-slate-400">Total kuantitas barang yang masuk</span>
            </div>

            <!-- Harga Satuan -->
            <div class="md:col-span-6 space-y-1">
              <label class="block text-xs font-bold text-slate-700" for="harga-satuan">
                <i class='bx bx-money text-blue-600 mr-1'></i>Harga Satuan (Rp) <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                <input name="harga" type="text" id="harga-satuan" class="w-full rounded-xl border border-slate-300 bg-white pl-9 pr-3.5 py-2 text-xs font-bold text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-xs" placeholder="150.000" oninput="formatRupiah(this)" required>
              </div>
              <span class="block text-[10.5px] text-slate-400">Harga per unit / per meter</span>
            </div>
          </div>

          <!-- Dynamic Serial / MAC Address Section -->
          <div id="serialFields" style="display:none;" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-800 mb-0 flex items-center gap-1.5" id="serialFieldLabel">
                <i class='bx bx-barcode text-blue-600 text-sm'></i> Detail Unit (Serial & MAC)
              </label>
              <span class="text-[10.5px] text-slate-500 font-medium" id="serialFieldHint">Input identitas per unit</span>
            </div>

            <div id="deviceUnits" class="space-y-2 max-h-60 overflow-y-auto custom-scroll pr-1">
              <div class="device-unit flex items-center gap-2">
                <input type="hidden" name="is_rusak[]" value="0">
                <div class="flex-1 sn-col">
                  <input type="text" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-100 transition" name="serial_number[]" placeholder="Serial Number (SN)">
                </div>
                <div class="flex-1 mac-col">
                  <input type="text" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-100 transition" name="mac_address[]" placeholder="MAC Address">
                </div>
                <div class="w-10 flex-shrink-0 flex items-center justify-center">
                  <button type="button" class="unit-rstatus w-[62px] h-8 rounded-lg border text-[10px] font-bold transition-all cursor-pointer flex items-center justify-center gap-1 border-slate-200 bg-slate-50 text-slate-600 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-700" title="Klik untuk menandai unit rusak" data-status="ok">
                    <i class="bx bx-check-circle text-sm"></i> OK
                  </button>
                </div>
                <div class="w-8 flex-shrink-0">
                  <button type="button" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center justify-center transition-all add-unit cursor-pointer" title="Tambah unit">
                    <i class="bx bx-plus text-base"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-between pt-1 border-t border-slate-200">
              <span class="text-[10.5px] text-slate-500 font-medium">Unit rusak akan langsung masuk daftar <strong class="text-rose-600">Rusak / Afkir</strong></span>
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-rose-50 text-rose-700 text-[11px] font-bold border border-rose-200" id="rusakSummary">Rusak: <span id="rusakCount">0</span> unit</span>
            </div>
          </div>

          <!-- Jumlah Rusak (untuk kategori non-serial: kabel/dropcore, dsb) -->
          <div id="jumlahRusakField" style="display:none;" class="p-4 rounded-xl bg-rose-50/60 border border-rose-200 space-y-1.5">
            <label class="block text-xs font-bold text-rose-800 flex items-center gap-1.5" for="jumlah_rusak">
              <i class='bx bx-x-circle text-rose-600'></i> Jumlah Unit Rusak
            </label>
            <input type="number" id="jumlah_rusak" class="w-full rounded-xl border border-rose-300 bg-white px-3.5 py-2 text-xs font-bold text-rose-900 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition" placeholder="Contoh: 3" min="0" value="0">
            <span class="block text-[10.5px] text-rose-700">Banyak unit yang rusak saat barang masuk. Stok tersedia akan dikurangi otomatis.</span>
          </div>
          <input type="hidden" name="jumlah_rusak" id="jumlah_rusak_input" value="0">

          <!-- Dynamic Splitter Ratio Section -->
          <div id="ratioField" style="display:none;" class="p-4 rounded-xl bg-amber-50/70 border border-amber-200 space-y-1.5">
            <label class="block text-xs font-bold text-amber-900 flex items-center gap-1.5" for="rasio">
              <i class='bx bx-git-repo-forked text-amber-600'></i> Rasio Output Splitter <span class="text-rose-500">*</span>
            </label>
            <select name="rasio" id="rasio" class="w-full rounded-xl border border-amber-300 bg-white px-3.5 py-2 text-xs font-bold text-amber-950 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition">
              <option value="" selected disabled>Pilih Rasio Splitter</option>
              <option value="1:2">1 : 2 (2 Output Port)</option>
              <option value="1:4">1 : 4 (4 Output Port)</option>
              <option value="1:8">1 : 8 (8 Output Port)</option>
              <option value="1:16">1 : 16 (16 Output Port)</option>
              <option value="1:32">1 : 32 (32 Output Port)</option>
            </select>
            <span class="block text-[10.5px] text-amber-800">Rasio pembagian optik untuk perhitungan topologi port.</span>
          </div>

          <!-- Modal Footer Buttons -->
          <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
            <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all cursor-pointer border-0 outline-none" data-bs-dismiss="modal">
              Batal
            </button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow cursor-pointer border-0 outline-none flex items-center gap-1.5">
              <i class="bx bx-plus"></i>
              <span>Tambah Stok</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ==================================================================================== -->
<!-- MODAL: TAMBAH KATEGORI (Responsive Tailwind & Bootstrap UI)                         -->
<!-- ==================================================================================== -->
<div class="modal fade" tabindex="-1" id="kategori" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-2xl border-0 overflow-hidden shadow-2xl">
      
      <!-- Modal Header (Tanpa Tombol X) -->
      <div class="py-4 px-5 bg-gradient-to-r from-blue-700 to-indigo-700 text-white flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl flex-shrink-0">
          <i class='bx bx-category'></i>
        </div>
        <div>
          <h5 class="text-base font-bold text-white mb-0 leading-tight">Tambah Kategori Logistik</h5>
          <p class="text-[11px] text-blue-100/90 mt-0.5 mb-0">Tambahkan kategori klasifikasi barang baru</p>
        </div>
      </div>

      <!-- Modal Body Form -->
      <div class="p-5 sm:p-6 bg-white">
        <form action="/add-kategori-logistik" method="POST" class="space-y-4">
          @csrf
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700" for="nama_kategori">
              <i class='bx bx-tag text-blue-600 mr-1'></i>Nama Kategori <span class="text-rose-500">*</span>
            </label>
            <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-xs" id="nama_kategori" placeholder="Contoh: Splitter, ODP, Modem, SFP, Dropcore" name="nama_logistik" required>
            <span class="block text-[10.5px] text-slate-400">Nama kategori baru untuk pengelompokan stok</span>
          </div>

          <!-- Modal Footer Buttons -->
          <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
            <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all cursor-pointer border-0 outline-none" data-bs-dismiss="modal">
              Batal
            </button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow cursor-pointer border-0 outline-none flex items-center gap-1.5">
              <i class="bx bx-plus"></i>
              <span>Simpan Kategori</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="/assets/js/logistik.js"></script>
@endpush
