@extends('layouts.contentNavbarLayout')

@section('title', 'Dashboard Logistik & Pergudangan')

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
</style>
@endsection

@section('content')
@php
    $totalPerangkat = $perangkat->count();
    $totalNilaiInventaris = $perangkat->sum(fn($item) => $item->harga * $item->jumlah_stok);
    $utilisasiPersen = $tersedia > 0 ? floor(($terpakai / $tersedia) * 100) : 0;
    $damagedPersen = $damagedDevices > 0 ? floor(($damagedDevices / max($totalPerangkat, 1)) * 100) : 0;
    $maintenancePersen = $maintenanceDevices > 0 ? floor(($maintenanceDevices / max($damagedDevices, 1)) * 100) : 0;

    $totalTersedia = $perangkat->sum('stok_tersedia');
    $totalTerpakai = $perangkat->sum('stok_terpakai');
    $totalRusak = $perangkat->sum('stok_rusak');
    $totalMaintenance = $perangkat->sum('stok_maintenance');
    $totalStokKeseluruhan = $totalTersedia + $totalTerpakai + $totalRusak + $totalMaintenance;

    $chartLabels = $damagedByCategory->pluck('kategori')->toJson();
    $chartData = $damagedByCategory->pluck('count')->toJson();
    $inventoryLabels = json_encode(['Tersedia', 'Terpasang', 'Rusak', 'Maintenance']);
    $inventoryData = json_encode([$totalTersedia, $totalTerpakai, $totalRusak, $totalMaintenance]);
@endphp

<div class="space-y-6">

    <!-- ======================================================================== -->
    <!-- TOP HEADER / ACTION BAR (CLEAN SOLID BASE COLORS)                         -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Left Info -->
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Modul Pergudangan & Inventaris</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight leading-tight m-0 flex items-center gap-2.5">
                    <i class="bx bx-package text-indigo-600 text-2xl"></i> Dashboard Logistik
                </h1>
                <p class="text-slate-500 text-xs sm:text-sm m-0">
                    Monitoring ketersediaan perangkat, alokasi pelanggan, status barang rusak, dan perbaikan perangkat.
                </p>
            </div>

            <!-- Right Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="/data/logistik" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all shadow-xs no-underline">
                    <i class="bx bx-list-ul text-base text-slate-500"></i> Kelola Data Logistik
                </a>
                <a href="/pengadaan" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-blue-200 bg-blue-50/70 hover:bg-blue-100 text-blue-700 font-semibold text-xs transition-all shadow-xs no-underline">
                    <i class="bx bx-cart-add text-base text-blue-600"></i> Pengadaan Barang
                </a>
                <button type="button" onclick="exportInventoryData()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all shadow-xs cursor-pointer">
                    <i class="bx bx-download text-base text-slate-500"></i> Export Aset
                </button>
                <button type="button" onclick="location.reload()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-all cursor-pointer border-0">
                    <i class="bx bx-refresh text-base"></i> Segarkan
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- 6 KPI SUMMARY CARDS (CLEAN & STRUCTURED)                                -->
    <!-- ======================================================================== -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        
        <!-- Total Aset -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Aset</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bx bx-box"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $totalPerangkat }}</div>
                <div class="text-[11px] font-medium text-slate-500 mt-0.5 truncate" title="Rp {{ number_format($totalNilaiInventaris, 0, ',', '.') }}">
                    Rp {{ number_format($totalNilaiInventaris, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Tersedia -->
        <a href="/logistik/tersedia" class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between no-underline group hover:border-emerald-300">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Tersedia</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-emerald-100 transition-colors">
                    <i class="bx bx-check-circle"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $totalTersedia }}</div>
                <div class="text-[11px] font-medium text-emerald-600 mt-0.5 flex items-center gap-1">
                    <i class="bx bx-trending-up text-xs"></i> Siap dialokasikan
                </div>
            </div>
        </a>

        <!-- Terpasang -->
        <a href="/logistik/terpakai" class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between no-underline group hover:border-blue-300">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Terpasang</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-blue-100 transition-colors">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $totalTerpakai }}</div>
                <div class="text-[11px] font-medium text-blue-600 mt-0.5">
                    {{ $totalStokKeseluruhan > 0 ? floor(($totalTerpakai / $totalStokKeseluruhan) * 100) : 0 }}% dari total stok
                </div>
            </div>
        </a>

        <!-- Rusak -->
        <a href="/logistik/barang-rusak" class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between no-underline group hover:border-rose-300">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Barang Rusak</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-rose-100 transition-colors">
                    <i class="bx bx-x-circle"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $damagedDevices }}</div>
                <div class="text-[11px] font-medium text-rose-600 mt-0.5">
                    {{ $damagedDevices > 0 ? $damagedPersen . '% rasio kendala' : 'Tidak ada kendala' }}
                </div>
            </div>
        </a>

        <!-- Dismantle -->
        <a href="/logistik/dismantle" class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between no-underline group hover:border-purple-300">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600">Dismantle</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-purple-100 transition-colors">
                    <i class="bx bx-archive-in"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $dismantleCount }}</div>
                <div class="text-[11px] font-medium text-purple-600 mt-0.5">
                    Perangkat ditarik teknisi
                </div>
            </div>
        </a>

        <!-- Maintenance -->
        <a href="/logistik/maintenance" class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between no-underline group hover:border-amber-300">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Maintenance</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-amber-100 transition-colors">
                    <i class="bx bx-wrench"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $maintenanceDevices }}</div>
                <div class="text-[11px] font-medium text-amber-600 mt-0.5">
                    {{ $availableForRepair }} unit siap servis
                </div>
            </div>
        </a>

    </div>

    <!-- ======================================================================== -->
    <!-- CHARTS SECTION: KATEGORI & STATUS INVENTARIS                             -->
    <!-- ======================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Bar Chart: Distribusi per Kategori -->
        <div class="lg:col-span-7 bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-base font-bold">
                        <i class="bx bx-bar-chart-alt-2"></i>
                    </div>
                    <h2 class="text-sm font-bold text-slate-800 m-0">Distribusi Stok per Kategori</h2>
                </div>
                <span class="text-[11px] font-semibold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-200">
                    Jumlah Fisik
                </span>
            </div>
            <div class="relative flex-1 w-full" style="min-height: 250px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart: Komposisi Status Inventaris -->
        <div class="lg:col-span-5 bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold">
                        <i class="bx bx-pie-chart-alt-2"></i>
                    </div>
                    <h2 class="text-sm font-bold text-slate-800 m-0">Status Kondisi Inventaris</h2>
                </div>
                <span class="text-[11px] font-semibold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-200">
                    {{ $totalStokKeseluruhan }} Total Unit
                </span>
            </div>
            <div class="relative flex-1 w-full flex items-center justify-center" style="min-height: 250px;">
                <canvas id="inventoryChart"></canvas>
            </div>
        </div>

    </div>

    <!-- ======================================================================== -->
    <!-- INVENTORY TABLE SECTION                                                  -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Table Header & Filter Toolbar -->
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
                    <i class="bx bx-layer"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 m-0 leading-tight">Inventaris Perangkat Terkini</h3>
                    <p class="text-xs text-slate-500 m-0 mt-0.5">Daftar stok fisik barang, status alokasi, dan estimasi nilai aset</p>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="relative">
                    <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="searchInventory" placeholder="Cari nama perangkat..."
                           class="pl-9 pr-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 bg-white placeholder-slate-400 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all w-44 sm:w-56">
                </div>

                <div class="relative">
                    <select id="categoryFilter"
                            class="pl-3 pr-8 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none focus:border-indigo-500 transition-all cursor-pointer">
                        <option value="">Semua Kategori</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="inventoryTable">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Perangkat</th>
                        <th class="py-3 px-3">Kategori</th>
                        <th class="py-3 px-3 text-center">Tersedia</th>
                        <th class="py-3 px-3 text-center">Terpasang</th>
                        <th class="py-3 px-3 text-center">Rusak</th>
                        <th class="py-3 px-3 text-center">Maintenance</th>
                        <th class="py-3 px-4 text-right">Nilai Total</th>
                        <th class="py-3 px-4 min-w-[140px]">Komposisi Stok</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse ($perangkat as $index => $p)
                    @php
                        $kategori = $p->kategori->nama_logistik ?? 'Lainnya';
                        $unitSatuan = ($kategori == 'Kabel') ? 'Meter' : 'Unit';
                        $totalStok = ($p->stok_tersedia ?? 0) + ($p->stok_terpakai ?? 0) + ($p->stok_rusak ?? 0) + ($p->stok_maintenance ?? 0);
                        $pctTersedia = $totalStok > 0 ? round(($p->stok_tersedia / $totalStok) * 100) : 0;
                        $pctTerpakai = $totalStok > 0 ? round(($p->stok_terpakai / $totalStok) * 100) : 0;
                        $pctRusak = $totalStok > 0 ? round(($p->stok_rusak / $totalStok) * 100) : 0;
                        $pctMaintenance = $totalStok > 0 ? round(($p->stok_maintenance / $totalStok) * 100) : 0;
                    @endphp
                    <tr class="device-row table-hover-row transition-colors">
                        <td class="py-3 px-4 text-center font-medium text-slate-400 row-number">{{ $index + 1 }}</td>
                        
                        <!-- Nama Perangkat -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-sm shrink-0">
                                    <i class="bx bx-chip"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $p->nama_perangkat }}</div>
                                    <div class="text-[10px] text-slate-400">Harga Satuan: Rp {{ number_format($p->harga, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Kategori -->
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/80">
                                {{ $kategori }}
                            </span>
                        </td>

                        <!-- Tersedia -->
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                {{ number_format($p->stok_tersedia ?? 0, 0, ',', '.') }} <span class="text-[10px] font-normal ml-1">{{ $unitSatuan }}</span>
                            </span>
                        </td>

                        <!-- Terpasang -->
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                {{ number_format($p->stok_terpakai ?? 0, 0, ',', '.') }}
                            </span>
                        </td>

                        <!-- Rusak -->
                        <td class="py-3 px-3 text-center">
                            @if(($p->stok_rusak ?? 0) > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                {{ $p->stok_rusak }}
                            </span>
                            @else
                            <span class="text-slate-300 font-medium">-</span>
                            @endif
                        </td>

                        <!-- Maintenance -->
                        <td class="py-3 px-3 text-center">
                            @if(($p->stok_maintenance ?? 0) > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                {{ $p->stok_maintenance }}
                            </span>
                            @else
                            <span class="text-slate-300 font-medium">-</span>
                            @endif
                        </td>

                        <!-- Total Nilai -->
                        <td class="py-3 px-4 text-right font-bold text-slate-800">
                            Rp {{ number_format($p->harga * $p->jumlah_stok, 0, ',', '.') }}
                        </td>

                        <!-- Komposisi Stok Progress -->
                        <td class="py-3 px-4">
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden flex" title="Tersedia: {{ $p->stok_tersedia }}, Terpasang: {{ $p->stok_terpakai }}, Rusak: {{ $p->stok_rusak }}, Maint: {{ $p->stok_maintenance }}">
                                <div style="width:{{ $pctTersedia }}%" class="bg-emerald-500 h-full"></div>
                                <div style="width:{{ $pctTerpakai }}%" class="bg-blue-500 h-full"></div>
                                <div style="width:{{ $pctRusak }}%" class="bg-rose-500 h-full"></div>
                                <div style="width:{{ $pctMaintenance }}%" class="bg-amber-500 h-full"></div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                                <span>Total: <strong class="text-slate-700">{{ number_format($totalStok, 0, ',', '.') }}</strong></span>
                                <span class="text-emerald-600 font-medium">{{ $pctTersedia }}% ada</span>
                            </div>
                        </td>

                        <!-- Aksi Buttons -->
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="/edit-logistik/{{ $p->id }}" class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 flex items-center justify-center text-sm transition-colors" title="Edit Data">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <a href="/data/logistik" class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-blue-600 flex items-center justify-center text-sm transition-colors" title="Lihat Rincian">
                                    <i class="bx bx-show"></i>
                                </a>
                                <button type="button" onclick="deleteDevice({{ $p->id }})" class="w-7 h-7 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center text-sm transition-colors cursor-pointer" title="Hapus Aset">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            <i class="bx bx-package text-3xl text-slate-300 block mb-2"></i>
                            <div class="font-medium text-slate-600">Belum ada aset logistik yang tercatat</div>
                            <div class="text-xs text-slate-400 mt-0.5">Tambahkan data perangkat melalui menu Data Logistik</div>
                        </td>
                    </tr>
                    @endforelse
                    <tr id="inventoryNoMatchRow" style="display: none;">
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            <i class="bx bx-search text-3xl text-slate-300 block mb-2"></i>
                            <div class="font-medium text-slate-600">Tidak ada perangkat yang sesuai</div>
                            <div class="text-xs text-slate-400 mt-0.5">Coba ubah kata kunci pencarian atau filter kategori</div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200 text-xs font-bold text-slate-700">
                        <td class="py-3.5 px-4" colspan="3">
                            <span class="text-slate-500 uppercase tracking-wider text-[11px]">Total Keseluruhan ({{ $totalPerangkat }} Aset)</span>
                        </td>
                        <td class="py-3.5 px-3 text-center text-emerald-700 font-extrabold">{{ number_format($totalTersedia, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-3 text-center text-blue-700 font-extrabold">{{ number_format($totalTerpakai, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-3 text-center text-rose-700 font-extrabold">{{ number_format($totalRusak, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-3 text-center text-amber-700 font-extrabold">{{ number_format($totalMaintenance, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right text-slate-900 font-black">
                            Rp {{ number_format($totalNilaiInventaris, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4" colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Inventory Pagination -->
        <div id="inventoryPagination" class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span>Tampilkan</span>
                <select id="inventoryPageSize" class="rounded-xl border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 cursor-pointer shadow-2xs transition-all">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>baris per halaman</span>
                <span class="text-slate-300 mx-1">|</span>
                <span id="inventoryPaginationInfo" class="font-medium text-slate-600">
                    Menampilkan data...
                </span>
            </div>
            <div id="inventoryPaginationNav" class="flex items-center gap-1"></div>
        </div>

    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --------------------------------------------------------------------------
    // Chart 1: Distribusi Stok per Kategori (Bar Chart)
    // --------------------------------------------------------------------------
    const categoryCanvas = document.getElementById('categoryChart');
    const catLabels = {!! $chartLabels !!};
    const catData = {!! $chartData !!};

    if (categoryCanvas && catData && catData.length > 0) {
        new Chart(categoryCanvas, {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Jumlah Stok',
                    data: catData,
                    backgroundColor: '#4f46e5',
                    borderRadius: 8,
                    barThickness: 32,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 10,
                        backgroundColor: '#1e293b',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 12 },
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '600' }, color: '#64748b' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 11 }, color: '#94a3b8', stepSize: 5 }
                    }
                }
            }
        });
    }

    // --------------------------------------------------------------------------
    // Chart 2: Komposisi Status Inventaris (Doughnut Chart)
    // --------------------------------------------------------------------------
    const inventoryCanvas = document.getElementById('inventoryChart');
    const invLabels = {!! $inventoryLabels !!};
    const invData = {!! $inventoryData !!};

    if (inventoryCanvas && invData) {
        new Chart(inventoryCanvas, {
            type: 'doughnut',
            data: {
                labels: invLabels,
                datasets: [{
                    data: invData,
                    backgroundColor: ['#10b981', '#3b82f6', '#ef4444', '#f59e0b'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            padding: 16,
                            font: { size: 11, weight: '600' },
                            color: '#475569'
                        }
                    },
                    tooltip: {
                        padding: 10,
                        backgroundColor: '#1e293b',
                        cornerRadius: 8
                    }
                }
            }
        });
    }

    // --------------------------------------------------------------------------
    // Table Pagination, Search & Category Filter
    // --------------------------------------------------------------------------
    const searchInput = document.getElementById('searchInventory');
    const categoryFilter = document.getElementById('categoryFilter');
    const pageSizeSelect = document.getElementById('inventoryPageSize');
    const paginationInfo = document.getElementById('inventoryPaginationInfo');
    const paginationNav = document.getElementById('inventoryPaginationNav');
    const noMatchRow = document.getElementById('inventoryNoMatchRow');
    const rows = Array.from(document.querySelectorAll('.device-row'));

    let paginationState = {
        page: 1,
        pageSize: pageSizeSelect ? parseInt(pageSizeSelect.value, 10) : 10
    };

    if (categoryFilter && rows.length > 0) {
        const categories = new Set();
        rows.forEach(row => {
            const catEl = row.querySelector('td:nth-child(3) span');
            if (catEl) categories.add(catEl.textContent.trim());
        });

        categories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat;
            categoryFilter.appendChild(opt);
        });
    }

    function createPageBtn(pageNumber, text, isActive, isDisabled) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = text !== undefined ? text : pageNumber;

        if (isActive) {
            btn.className = 'w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-xs flex items-center justify-center';
        } else {
            btn.className = 'w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 font-semibold text-xs transition cursor-pointer flex items-center justify-center shadow-2xs disabled:opacity-40 disabled:cursor-not-allowed';
        }

        if (isDisabled) {
            btn.disabled = true;
        } else if (!isActive) {
            btn.addEventListener('click', function() {
                paginationState.page = pageNumber;
                renderInventoryPagination();
            });
        }
        return btn;
    }

    function renderInventoryPagination() {
        const searchVal = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const catVal = (categoryFilter ? categoryFilter.value : '').toLowerCase().trim();

        // 1. Filter rows
        const filteredRows = rows.filter(row => {
            const text = row.textContent.toLowerCase();
            const catEl = row.querySelector('td:nth-child(3) span');
            const rowCat = catEl ? catEl.textContent.trim().toLowerCase() : '';

            const matchSearch = !searchVal || text.includes(searchVal);
            const matchCat = !catVal || rowCat === catVal;

            return matchSearch && matchCat;
        });

        const totalItems = filteredRows.length;
        const pageSize = paginationState.pageSize;
        const totalPages = Math.max(1, Math.ceil(totalItems / pageSize));

        if (paginationState.page > totalPages) paginationState.page = totalPages;
        if (paginationState.page < 1) paginationState.page = 1;

        const startIndex = (paginationState.page - 1) * pageSize;
        const endIndex = startIndex + pageSize;

        // 2. Hide all device rows first
        rows.forEach(r => r.style.display = 'none');

        // 3. Show and renumber current page slice
        filteredRows.slice(startIndex, endIndex).forEach((r, idx) => {
            r.style.display = '';
            const numCell = r.querySelector('.row-number');
            if (numCell) {
                numCell.textContent = startIndex + idx + 1;
            }
        });

        // 4. Handle Empty / No Match State
        if (noMatchRow) {
            noMatchRow.style.display = (totalItems === 0 && rows.length > 0) ? '' : 'none';
        }

        // 5. Update info text
        if (paginationInfo) {
            if (totalItems === 0) {
                paginationInfo.textContent = 'Tidak ada perangkat yang cocok';
            } else if (totalItems === rows.length) {
                const displayEnd = Math.min(endIndex, totalItems);
                paginationInfo.textContent = `Menampilkan ${startIndex + 1} - ${displayEnd} dari ${totalItems} perangkat`;
            } else {
                const displayEnd = Math.min(endIndex, totalItems);
                paginationInfo.textContent = `Menampilkan ${startIndex + 1} - ${displayEnd} dari ${totalItems} perangkat (difilter dari ${rows.length} total)`;
            }
        }

        // 6. Render Nav Buttons
        if (paginationNav) {
            paginationNav.innerHTML = '';

            if (totalItems === 0) return;

            // Prev Button
            const prevBtn = createPageBtn(paginationState.page - 1, '<i class="bx bx-chevron-left text-sm"></i>', false, paginationState.page === 1);
            prevBtn.title = 'Halaman Sebelumnya';
            paginationNav.appendChild(prevBtn);

            // Page numbers algorithm with ellipsis
            const maxButtons = 5;
            let startPage = Math.max(1, paginationState.page - 2);
            let endPage = Math.min(totalPages, startPage + maxButtons - 1);
            if (endPage - startPage < maxButtons - 1) {
                startPage = Math.max(1, endPage - maxButtons + 1);
            }

            if (startPage > 1) {
                paginationNav.appendChild(createPageBtn(1, '1', paginationState.page === 1, false));
                if (startPage > 2) {
                    const dots = document.createElement('span');
                    dots.className = 'w-6 text-center text-slate-400 text-xs font-mono select-none';
                    dots.textContent = '...';
                    paginationNav.appendChild(dots);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationNav.appendChild(createPageBtn(i, i.toString(), i === paginationState.page, false));
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dots = document.createElement('span');
                    dots.className = 'w-6 text-center text-slate-400 text-xs font-mono select-none';
                    dots.textContent = '...';
                    paginationNav.appendChild(dots);
                }
                paginationNav.appendChild(createPageBtn(totalPages, totalPages.toString(), totalPages === paginationState.page, false));
            }

            // Next Button
            const nextBtn = createPageBtn(paginationState.page + 1, '<i class="bx bx-chevron-right text-sm"></i>', false, paginationState.page === totalPages);
            nextBtn.title = 'Halaman Selanjutnya';
            paginationNav.appendChild(nextBtn);
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            paginationState.page = 1;
            renderInventoryPagination();
        });
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            paginationState.page = 1;
            renderInventoryPagination();
        });
    }

    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function() {
            paginationState.pageSize = parseInt(this.value, 10);
            paginationState.page = 1;
            renderInventoryPagination();
        });
    }

    // Initial render
    renderInventoryPagination();
});

function deleteDevice(id) {
    if (confirm('Apakah Anda yakin ingin menghapus perangkat ini dari database logistik?')) {
        window.location.href = '/logistik/hapus/' + id;
    }
}

function exportInventoryData() {
    window.location.href = '/api/inventory/export';
}
</script>
@endpush
