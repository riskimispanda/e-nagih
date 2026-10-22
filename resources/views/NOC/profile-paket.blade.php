@extends('layouts.contentNavbarLayout')

@section('title', 'Profile Paket')

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

    /* Loading overlay visibility handled without fighting Bootstrap's d-none */
    #loading-overlay {
        display: none;
    }
    #loading-overlay.is-visible {
        display: flex;
    }

    /* Custom scrollbar for horizontally scrollable tables */
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: rgba(241, 245, 249, 0.6);
        border-radius: 99px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 99px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Modal fine-tuning on top of Bootstrap's modal behaviour */
    #modalTambahPaket .modal-content,
    #modalTambahRouter .modal-content,
    #modalEditRouter .modal-content,
    #modalEditPaket .modal-content {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        overflow: hidden;
    }
    #modalTambahPaket .modal-header,
    #modalTambahRouter .modal-header,
    #modalEditRouter .modal-header,
    #modalEditPaket .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 16px 20px;
        align-items: flex-start;
    }
    #modalTambahPaket .modal-body,
    #modalTambahRouter .modal-body,
    #modalEditRouter .modal-body,
    #modalEditPaket .modal-body {
        padding: 18px 20px;
    }
    #modalTambahPaket .modal-footer,
    #modalTambahRouter .modal-footer,
    #modalEditRouter .modal-footer,
    #modalEditPaket .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 12px 20px;
        background: #f8fafc;
        gap: 8px;
    }
</style>
@endsection

@section('content')
@php
    // Preserve all existing query parameters (except the router page) on pagination links
    $router->appends(request()->except('router_page'));

    $routerItems = $router->getCollection();
    $routerOnline = $routerItems->filter(fn ($r) => str_contains((string) $r->status_koneksi, 'Online'))->count();
    $cpuValues = $routerItems->pluck('cpu_load')->filter(fn ($v) => $v !== null);
    $avgCpu = $cpuValues->isNotEmpty() ? (int) round($cpuValues->avg()) : null;
    $paketTotal = (is_object($paket) && method_exists($paket, 'total')) ? $paket->total() : null;

    $routerStartPage = max(1, $router->currentPage() - 2);
    $routerEndPage = min($router->lastPage(), $routerStartPage + 4);
    if ($routerEndPage - $routerStartPage < 4) {
        $routerStartPage = max(1, $routerEndPage - 4);
    }
@endphp

<div class="space-y-6">

    <!-- ======================================================================== -->
    <!-- TOP HEADER / ACTION BAR                                                  -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Left Info -->
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Modul NOC & Manajemen Layanan</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight leading-tight m-0 flex items-center gap-2.5">
                    <i class="bx bx-network-chart text-indigo-600 text-2xl"></i> Manajemen Router & Profile Paket
                </h1>
                <p class="text-slate-500 text-xs sm:text-sm m-0">
                    Kelola daftar router, pantau status koneksi, serta atur profile paket langganan pelanggan.
                </p>
            </div>

            <!-- Right Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" data-bs-toggle="modal" data-bs-target="#modalTambahRouter"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all shadow-sm cursor-pointer">
                    <i class="bx bx-plus-circle text-base text-slate-500"></i> Tambah Router
                </button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#modalTambahPaket"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs transition-all shadow-sm cursor-pointer">
                    <i class="bx bx-package text-base text-indigo-600"></i> Tambah Paket
                </button>
                <button type="button" onclick="location.reload()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-all border-0 cursor-pointer">
                    <i class="bx bx-refresh text-base"></i> Segarkan
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- 4 KPI SUMMARY CARDS                                                      -->
    <!-- ======================================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Total Router -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Router</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bx bx-server"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $router->total() }}</div>
                <div class="text-[11px] font-medium text-slate-500 mt-0.5">Router terdaftar di sistem</div>
            </div>
        </div>

        <!-- Router Online -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Router Online</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bx bx-wifi"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $routerOnline }}</div>
                <div class="text-[11px] font-medium text-emerald-600 mt-0.5 flex items-center gap-1">
                    <i class="bx bx-check-circle text-xs"></i> dari {{ $routerItems->count() }} router di halaman ini
                </div>
            </div>
        </div>

        <!-- Rata-rata CPU Load -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Rata-rata CPU</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bx bx-chip"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $avgCpu !== null ? $avgCpu . '%' : '—' }}</div>
                @if ($avgCpu !== null)
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                    <div class="h-full rounded-full {{ $avgCpu >= 80 ? 'bg-rose-500' : ($avgCpu >= 60 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                         style="width: {{ min($avgCpu, 100) }}%"></div>
                </div>
                @else
                <div class="text-[11px] font-medium text-slate-400 mt-0.5">Data beban CPU belum tersedia</div>
                @endif
            </div>
        </div>

        <!-- Total Paket -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Total Paket</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bx bx-package"></i>
                </div>
            </div>
            <div>
                <div id="kpiTotalPaket" class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $paketTotal ?? '—' }}</div>
                <div class="text-[11px] font-medium text-blue-600 mt-0.5">Profile paket langganan</div>
            </div>
        </div>

    </div>

    <!-- ======================================================================== -->
    <!-- ROUTER TABLE SECTION                                                     -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        <!-- Table Header -->
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
                    <i class="bx bx-server"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800 m-0 leading-tight">Data Router</h2>
                    <p class="text-xs text-slate-500 m-0 mt-0.5">Daftar seluruh router yang terdaftar beserta status koneksinya</p>
                </div>
            </div>
            <span class="text-[11px] font-semibold text-slate-500 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-200 self-start md:self-auto">
                {{ $router->total() }} Router
            </span>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Router</th>
                        <th class="py-3 px-3">IP Address</th>
                        <th class="py-3 px-3 text-center">Port</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 min-w-[150px]">CPU Load</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse ($router as $index => $r)
                    @php
                        $isOnline = str_contains((string) $r->status_koneksi, 'Online');
                        $cpu = $r->cpu_load;
                        $cpuBarClass = $cpu === null ? '' : ($cpu >= 80 ? 'bg-rose-500' : ($cpu >= 60 ? 'bg-amber-500' : ($cpu >= 40 ? 'bg-sky-500' : 'bg-emerald-500')));
                        $cpuTextClass = $cpu === null ? '' : ($cpu >= 80 ? 'text-rose-600' : ($cpu >= 60 ? 'text-amber-600' : ($cpu >= 40 ? 'text-sky-600' : 'text-emerald-600')));
                    @endphp
                    <tr class="table-hover-row transition-colors">
                        <td class="py-3 px-4 text-center font-medium text-slate-400">{{ $router->firstItem() + $index }}</td>

                        <!-- Nama Router -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base shrink-0">
                                    <i class="bx bx-hdd"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $r->nama_router }}</div>
                                    <div class="text-[10px] text-slate-400">Router ID #{{ $r->id }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- IP Address -->
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold font-mono bg-slate-100 text-slate-700 border border-slate-200/80">
                                {{ $r->ip_address }}
                            </span>
                        </td>

                        <!-- Port -->
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200/60">
                                {{ $r->port }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="py-3 px-3 text-center">
                            @if ($isOnline)
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Online
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Offline
                            </span>
                            @endif
                        </td>

                        <!-- CPU Load -->
                        <td class="py-3 px-4">
                            @if ($cpu !== null)
                            <div class="min-w-[110px]">
                                <div class="flex items-center justify-between text-[10px] font-semibold mb-1">
                                    <span class="text-slate-400">CPU</span>
                                    <span class="{{ $cpuTextClass }}">{{ $cpu }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="{{ $cpuBarClass }} h-full rounded-full" style="width: {{ min($cpu, 100) }}%"></div>
                                </div>
                            </div>
                            @else
                            <span class="text-slate-300 font-medium">-</span>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="/interface/{{ $r->id }}"
                                   class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-blue-50 text-blue-600 flex items-center justify-center text-sm transition-colors no-underline"
                                   title="Buka Server">
                                    <i class="bx bx-server"></i>
                                </a>
                                <button type="button" onclick="editRouter({{ $r->id }})"
                                        class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-amber-50 text-amber-600 flex items-center justify-center text-sm transition-colors cursor-pointer"
                                        title="Edit Router">
                                    <i class="bx bx-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="bx bx-server text-3xl text-slate-300 block mb-2"></i>
                            <div class="font-medium text-slate-600">Belum ada router yang terdaftar</div>
                            <div class="text-xs text-slate-400 mt-0.5">Tambahkan router melalui tombol Tambah Router di atas</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Router Pagination -->
        <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <span>
                Menampilkan <strong class="text-slate-700">{{ $router->firstItem() ?? 0 }}</strong> - <strong class="text-slate-700">{{ $router->lastItem() ?? 0 }}</strong>
                dari <strong class="text-slate-700">{{ $router->total() }}</strong> router
            </span>

            @if ($router->hasPages())
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if ($router->onFirstPage())
                <span class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-300 flex items-center justify-center opacity-50 cursor-not-allowed">
                    <i class="bx bx-chevron-left text-sm"></i>
                </span>
                @else
                <a href="{{ $router->previousPageUrl() }}" title="Halaman Sebelumnya"
                   class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors no-underline shadow-sm">
                    <i class="bx bx-chevron-left text-sm"></i>
                </a>
                @endif

                {{-- First page + ellipsis --}}
                @if ($routerStartPage > 1)
                <a href="{{ $router->url(1) }}" class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 flex items-center justify-center font-semibold text-xs transition-colors no-underline shadow-sm">1</a>
                @if ($routerStartPage > 2)
                <span class="w-6 text-center text-slate-400 text-xs font-mono select-none">...</span>
                @endif
                @endif

                {{-- Page numbers --}}
                @foreach ($router->getUrlRange($routerStartPage, $routerEndPage) as $page => $url)
                    @if ($page == $router->currentPage())
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-sm flex items-center justify-center">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 flex items-center justify-center font-semibold text-xs transition-colors no-underline shadow-sm">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Last page + ellipsis --}}
                @if ($routerEndPage < $router->lastPage())
                @if ($routerEndPage < $router->lastPage() - 1)
                <span class="w-6 text-center text-slate-400 text-xs font-mono select-none">...</span>
                @endif
                <a href="{{ $router->url($router->lastPage()) }}" class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 flex items-center justify-center font-semibold text-xs transition-colors no-underline shadow-sm">{{ $router->lastPage() }}</a>
                @endif

                {{-- Next --}}
                @if ($router->hasMorePages())
                <a href="{{ $router->nextPageUrl() }}" title="Halaman Selanjutnya"
                   class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors no-underline shadow-sm">
                    <i class="bx bx-chevron-right text-sm"></i>
                </a>
                @else
                <span class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-300 flex items-center justify-center opacity-50 cursor-not-allowed">
                    <i class="bx bx-chevron-right text-sm"></i>
                </span>
                @endif
            </div>
            @endif
        </div>

    </div>

    <!-- ======================================================================== -->
    <!-- PROFILE PAKET TABLE SECTION                                              -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        <!-- Header & Toolbar -->
        <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
                    <i class="bx bx-package"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 m-0 leading-tight">Profile Paket Langganan</h3>
                    <p class="text-xs text-slate-500 m-0 mt-0.5">Daftar profile paket, harga langganan, dan jumlah pelanggan terdaftar</p>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="relative">
                    <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="paket-search-input" placeholder="Cari nama paket..."
                           class="pl-9 pr-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 bg-white placeholder-slate-400 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all w-44 sm:w-56">
                </div>

                <div class="relative">
                    <select id="paket-page-size"
                            class="pl-3 pr-8 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white outline-none focus:border-indigo-500 transition-all cursor-pointer">
                        <option value="10" selected>10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>
                </div>

                <button type="button" data-bs-toggle="modal" data-bs-target="#modalTambahPaket"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition-all shadow-sm border-0 cursor-pointer">
                    <i class="bx bx-plus text-base"></i> Tambah Paket
                </button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Paket</th>
                        <th class="py-3 px-3">Nama Profile</th>
                        <th class="py-3 px-3">Nama Router</th>
                        <th class="py-3 px-3 text-right">Harga</th>
                        <th class="py-3 px-3 text-center">Jumlah Pelanggan</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody id="paket-table-body" class="divide-y divide-slate-100 text-xs text-slate-700">
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="bx bx-loader-alt bx-spin text-3xl text-indigo-300 block mb-2"></i>
                            <div class="font-medium text-slate-500">Memuat data paket...</div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200 text-xs font-bold text-slate-700">
                        <td class="py-3.5 px-4" colspan="4">
                            <span class="text-slate-500 uppercase tracking-wider text-[11px]">Total Halaman Ini</span>
                        </td>
                        <td class="py-3.5 px-3 text-right text-slate-900 font-black" id="paket-total-harga">Rp 0</td>
                        <td class="py-3.5 px-3 text-center text-amber-700 font-extrabold" id="paket-total-pelanggan">0</td>
                        <td class="py-3.5 px-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Paket Pagination -->
        <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span id="paket-total-data" class="font-medium text-slate-600">Total Data: 0</span>
                <span class="text-slate-300 mx-1">|</span>
                <span id="paket-showing-data">Menampilkan 0 - 0 dari 0 data</span>
            </div>
            <div id="paket-pagination" class="flex items-center gap-1"></div>
        </div>

    </div>

</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 items-center justify-center" style="background: rgba(15, 23, 42, 0.25); z-index: 9999;">
    <div class="bg-white rounded-2xl shadow-xl px-5 py-4 flex items-center gap-3 border border-slate-200">
        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span class="text-sm font-semibold text-slate-700">Memuat data...</span>
    </div>
</div>

{{-- Modal Tambah Paket --}}
<div class="modal fade" id="modalTambahPaket" tabindex="-1" aria-labelledby="modalTambahPaketLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base shrink-0">
                        <i class="bx bx-plus"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-sm font-bold text-slate-800 m-0 leading-tight" id="modalTambahPaketLabel">Tambah Paket Langganan</h5>
                        <p class="text-[11px] text-slate-500 m-0 mt-0.5">Lengkapi data profile paket di bawah ini</p>
                    </div>
                </div>
                <button type="button" class="btn-close opacity-60 hover:opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/tambah/paket" method="POST">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Router <span class="text-rose-500">*</span></label>
                        <select name="router_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                            <option value="" selected disabled>Pilih Router</option>
                            @foreach ($router as $r)
                            <option value="{{ $r->id }}">{{ $r->nama_router }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Paket <span class="text-rose-500">*</span></label>
                        <input type="text" id="nama_paket" name="nama_paket" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Profile <span class="text-rose-500">*</span></label>
                        <input type="text" id="paket_nama" name="profile_name" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                        <p class="text-[11px] text-rose-500 font-semibold m-0 mt-1.5 flex items-center gap-1">
                            <i class="bx bx-info-circle"></i> Harus sesuai dengan nama profile paket di Mikrotik
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Harga Paket Langganan <span class="text-rose-500">*</span></label>
                        <input type="text" id="harga" name="harga" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                        <input hidden type="text" id="hargaRaw" name="hargaRaw">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all cursor-pointer" data-bs-dismiss="modal">
                        <i class="bx bx-x text-base"></i> Batal
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition-all shadow-sm border-0 cursor-pointer">
                        <i class="bx bx-save text-base"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Tambah Router --}}
<div class="modal fade" id="modalTambahRouter" tabindex="-1" aria-labelledby="modalTambahRouterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base shrink-0">
                        <i class="bx bx-plus"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-sm font-bold text-slate-800 m-0 leading-tight" id="modalTambahRouterLabel">Tambah Router</h5>
                        <p class="text-[11px] text-slate-500 m-0 mt-0.5">Daftarkan router baru ke dalam sistem</p>
                    </div>
                </div>
                <button type="button" class="btn-close opacity-60 hover:opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/tambah/router" method="POST">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Router <span class="text-rose-500">*</span></label>
                        <input type="text" id="nama_router" name="nama_router" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">IP Address Router <span class="text-rose-500">*</span></label>
                        <input type="text" id="ip_address" name="ip_address" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Port <span class="text-rose-500">*</span></label>
                        <input type="text" id="port" name="port" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Username <span class="text-rose-500">*</span></label>
                        <input type="text" id="username" name="username" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all cursor-pointer" data-bs-dismiss="modal">
                        <i class="bx bx-x text-base"></i> Batal
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition-all shadow-sm border-0 cursor-pointer">
                        <i class="bx bx-save text-base"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Router --}}
<div class="modal fade" id="modalEditRouter" tabindex="-1" aria-labelledby="modalEditRouterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base shrink-0">
                        <i class="bx bx-edit"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-sm font-bold text-slate-800 m-0 leading-tight" id="modalEditRouterLabel">Edit Router</h5>
                        <p class="text-[11px] text-slate-500 m-0 mt-0.5">Perbarui data koneksi router</p>
                    </div>
                </div>
                <button type="button" class="btn-close opacity-60 hover:opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRouterForm" method="POST">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Router <span class="text-rose-500">*</span></label>
                        <input type="text" id="edit_nama_router" name="nama_router" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">IP Address Router <span class="text-rose-500">*</span></label>
                        <input type="text" id="edit_ip_address" name="ip_address" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Port <span class="text-rose-500">*</span></label>
                        <input type="text" id="edit_port" name="port" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Username <span class="text-rose-500">*</span></label>
                        <input type="text" id="edit_username" name="username" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                        <input type="password" id="edit_password" name="password" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all cursor-pointer" data-bs-dismiss="modal">
                        <i class="bx bx-x text-base"></i> Batal
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition-all shadow-sm border-0 cursor-pointer">
                        <i class="bx bx-save text-base"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Paket --}}
<div class="modal fade" id="modalEditPaket" tabindex="-1" aria-labelledby="modalEditPaketLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base shrink-0">
                        <i class="bx bx-edit"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-sm font-bold text-slate-800 m-0 leading-tight" id="modalEditPaketLabel">Edit Paket Langganan</h5>
                        <p class="text-[11px] text-slate-500 m-0 mt-0.5">Perbarui data profile paket langganan</p>
                    </div>
                </div>
                <button type="button" class="btn-close opacity-60 hover:opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPaketForm" method="POST">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Router <span class="text-rose-500">*</span></label>
                        <select name="router_id" id="edit_router_id" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                            <option value="" selected disabled>Pilih Router</option>
                            @foreach ($router as $r)
                            <option value="{{ $r->id }}">{{ $r->nama_router }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Paket <span class="text-rose-500">*</span></label>
                        <input type="text" id="edit_nama_paket" name="nama_paket" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Profile <span class="text-rose-500">*</span></label>
                        <input type="text" id="edit_profile_name" name="profile_name" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                        <p class="text-[11px] text-rose-500 font-semibold m-0 mt-1.5 flex items-center gap-1">
                            <i class="bx bx-info-circle"></i> Harus sesuai dengan nama profile paket di Mikrotik
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Harga Paket Langganan <span class="text-rose-500">*</span></label>
                        <input type="text" id="edit_harga" name="harga" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all">
                        <input hidden type="text" id="edit_hargaRaw" name="hargaRaw">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all cursor-pointer" data-bs-dismiss="modal">
                        <i class="bx bx-x text-base"></i> Batal
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition-all shadow-sm border-0 cursor-pointer">
                        <i class="bx bx-save text-base"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // --------------------------------------------------------------------------
    // Helpers
    // --------------------------------------------------------------------------
    function showPaketLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.classList.add('is-visible');
    }

    function hidePaketLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.classList.remove('is-visible');
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (ch) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
        });
    }

    function formatRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(value) || 0);
    }

    // --------------------------------------------------------------------------
    // Price formatting for add modal
    // --------------------------------------------------------------------------
    const hargaInput = document.getElementById('harga');
    const hargaRawInput = document.getElementById('hargaRaw');
    const editHargaInput = document.getElementById('edit_harga');
    const editHargaRawInput = document.getElementById('edit_hargaRaw');

    if (hargaInput && hargaRawInput) {
        hargaInput.addEventListener('input', function () {
            const cleanValue = this.value.replace(/[^0-9]/g, '');
            hargaRawInput.value = cleanValue;
            this.value = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(cleanValue);
        });
    }

    if (editHargaInput && editHargaRawInput) {
        editHargaInput.addEventListener('input', function () {
            const cleanValue = this.value.replace(/[^0-9]/g, '');
            editHargaRawInput.value = cleanValue;
            this.value = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(cleanValue);
        });
    }

    // --------------------------------------------------------------------------
    // Edit Paket
    // --------------------------------------------------------------------------
    function editPaket(id) {
        try {
            showPaketLoading();

            $.ajax({
                url: `/edit/paket/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    try {
                        $('#edit_nama_paket').val(data.nama_paket || '');
                        $('#edit_profile_name').val(data.paket_name || '');
                        $('#edit_router_id').val(data.router_id || '');

                        const harga = data.harga || 0;
                        $('#edit_harga').val(new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        }).format(harga));
                        $('#edit_hargaRaw').val(harga);

                        $('#editPaketForm').attr('action', `/update/paket/${id}`);

                        hidePaketLoading();
                        $('#modalEditPaket').modal('show');
                    } catch (e) {
                        console.error('Error populating paket modal:', e);
                        hidePaketLoading();
                        alert('Terjadi kesalahan saat mengisi form: ' + e.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.group('AJAX Error - Paket');
                    console.error('Status:', status);
                    console.error('Error Message:', error);
                    console.error('HTTP Status Code:', xhr.status);
                    console.error('Response Text:', xhr.responseText);
                    console.error('Request URL:', this.url);
                    console.groupEnd();

                    hidePaketLoading();
                    alert(`Gagal mengambil data paket.\nStatus: ${xhr.status}\nPesan: ${error}`);
                }
            });
        } catch (e) {
            console.error('Function error:', e);
            hidePaketLoading();
            alert('Terjadi kesalahan: ' + e.message);
        }
    }

    window.editPaket = editPaket;

    // --------------------------------------------------------------------------
    // Edit Router
    // --------------------------------------------------------------------------
    function editRouter(id) {
        fetch(`/edit/router/${id}`)
            .then(response => {
                if (!response.ok) {
                    console.group('Fetch Error - Router Data');
                    console.error('HTTP Status Code:', response.status);
                    console.error('Status Text:', response.statusText);
                    console.error('Request URL:', response.url);
                    console.groupEnd();
                    throw new Error(`HTTP ${response.status} - ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('edit_nama_router').value = data.nama_router;
                document.getElementById('edit_ip_address').value = data.ip_address;
                document.getElementById('edit_port').value = data.port;
                document.getElementById('edit_username').value = data.username;
                document.getElementById('edit_password').value = data.password;

                document.getElementById('editRouterForm').action = `/update/router/${id}`;

                const modal = new bootstrap.Modal(document.getElementById('modalEditRouter'));
                modal.show();
            })
            .catch(error => {
                console.error('Gagal ambil data router:', error);
                alert('Terjadi kesalahan saat mengambil data router');
            });
    }

    window.editRouter = editRouter;

    // --------------------------------------------------------------------------
    // Paket table: fetch, render, pagination & search
    // --------------------------------------------------------------------------
    let paketCurrentPage = 1;
    let paketSearchTerm = '';
    let paketPerPage = 10;
    let paketSearchTimeout;
    let paketListById = {};

    function fetchPaketData(page = 1, search = '') {
        showPaketLoading();

        const url = new URL("{{ route('ajax.paket') }}", window.location.origin);
        url.searchParams.set('page', page);
        url.searchParams.set('per_page', paketPerPage);
        if (search) {
            url.searchParams.set('search', search);
        }

        fetch(url.toString(), { headers: { 'Accept': 'application/json' } })
            .then(res => {
                if (!res.ok) {
                    console.group('Fetch Error - Paket Data');
                    console.error('HTTP Status Code:', res.status);
                    console.error('Status Text:', res.statusText);
                    console.error('Request URL:', res.url);
                    console.groupEnd();
                    throw new Error(`HTTP ${res.status} - ${res.statusText}`);
                }
                return res.json();
            })
            .then(data => {
                // Jika halaman saat ini menjadi kosong setelah penghapusan, mundur ke halaman terakhir yang valid
                if ((data.data || []).length === 0 && (data.total || 0) > 0 && page > 1) {
                    const fallbackPage = Math.max(1, Math.min(page - 1, data.last_page || page - 1));
                    fetchPaketData(fallbackPage, search);
                    return;
                }

                renderPaketTable(data);
                renderPaketPagination(data);
                updatePaketSummary(data);
                hidePaketLoading();
            })
            .catch((err) => {
                console.error('Gagal memuat data paket:', err);
                const tbody = document.getElementById('paket-table-body');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="bx bx-error-circle text-3xl text-rose-300 block mb-2"></i>
                                <div class="font-medium text-slate-600">Gagal memuat data paket</div>
                                <div class="text-xs text-slate-400 mt-0.5 mb-3">Periksa koneksi Anda lalu coba lagi</div>
                                <button type="button" onclick="goToPaketPage(1); return false;"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-[11px] transition-all cursor-pointer">
                                    <i class="bx bx-refresh"></i> Coba Lagi
                                </button>
                            </td>
                        </tr>`;
                }
                const nav = document.getElementById('paket-pagination');
                if (nav) nav.innerHTML = '';
                const totalData = document.getElementById('paket-total-data');
                const showingData = document.getElementById('paket-showing-data');
                if (totalData) totalData.textContent = 'Total Data: 0';
                if (showingData) showingData.textContent = 'Menampilkan 0 dari 0 data';
                hidePaketLoading();
            });
    }

    function renderPaketTable(data) {
        const tbody = document.getElementById('paket-table-body');
        if (!tbody) return;

        let html = '';
        let totalHarga = 0;
        let totalPelanggan = 0;

        paketListById = {};

        if (data.data && data.data.length > 0) {
            data.data.forEach((item, idx) => {
                paketListById[item.id] = item;

                const isIsolir = item.nama_paket === 'ISOLIREBILLING';
                const badgeClass = isIsolir
                    ? 'bg-rose-50 text-rose-700 border-rose-200/60'
                    : 'bg-indigo-50 text-indigo-700 border-indigo-200/60';
                const customerCount = item.customer ? item.customer.length : 0;
                const routerName = item.router ? (item.router.nama_router || '-') : '-';

                totalHarga += Number(item.harga) || 0;
                totalPelanggan += customerCount;

                html += `
                    <tr class="table-hover-row transition-colors">
                        <td class="py-3 px-4 text-center font-medium text-slate-400">${(data.from || 0) + idx}</td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border ${badgeClass}">
                                ${escapeHtml(item.nama_paket || '-')}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold font-mono bg-slate-50 text-slate-600 border border-slate-200/80">
                                ${escapeHtml(item.paket_name || '-')}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-1.5">
                                <i class="bx bx-server text-sm text-slate-400"></i>
                                <span class="font-medium text-slate-700">${escapeHtml(routerName)}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-slate-800 whitespace-nowrap">
                            ${formatRupiah(item.harga)}
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                ${customerCount}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="#" onclick="editPaket(${item.id}); return false;"
                                   class="w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-amber-50 text-amber-600 flex items-center justify-center text-sm transition-colors no-underline"
                                   title="Edit Paket">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <button type="button" onclick="deletePaket(${item.id}); return false;"
                                        class="w-7 h-7 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center text-sm transition-colors cursor-pointer"
                                        title="Hapus Paket">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
            });
        } else {
            html = `
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400">
                        <i class="bx bx-package text-3xl text-slate-300 block mb-2"></i>
                        <div class="font-medium text-slate-600">Tidak ada data paket yang ditemukan</div>
                        <div class="text-xs text-slate-400 mt-0.5">Coba ubah kata kunci pencarian Anda</div>
                    </td>
                </tr>`;
        }

        tbody.innerHTML = html;

        const totalHargaEl = document.getElementById('paket-total-harga');
        const totalPelangganEl = document.getElementById('paket-total-pelanggan');
        if (totalHargaEl) totalHargaEl.textContent = formatRupiah(totalHarga);
        if (totalPelangganEl) totalPelangganEl.textContent = totalPelanggan;
    }

    function updatePaketSummary(data) {
        const totalData = document.getElementById('paket-total-data');
        const showingData = document.getElementById('paket-showing-data');
        const kpiTotal = document.getElementById('kpiTotalPaket');

        if (totalData) totalData.textContent = `Total Data: ${data.total || 0}`;
        if (showingData) {
            showingData.textContent = (data.total || 0) > 0
                ? `Menampilkan ${data.from || 0} - ${data.to || 0} dari ${data.total} data`
                : 'Menampilkan 0 dari 0 data';
        }
        if (kpiTotal) kpiTotal.textContent = data.total || 0;
    }

    function paketPageButton(page, label, active, disabled, title) {
        if (disabled) {
            return `<span class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-300 flex items-center justify-center opacity-50 cursor-not-allowed" title="${title}">${label}</span>`;
        }
        if (active) {
            return `<span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-sm flex items-center justify-center">${label}</span>`;
        }
        return `<button type="button" onclick="goToPaketPage(${page}); return false;" title="${title}"
                        class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 font-semibold text-xs transition-colors flex items-center justify-center shadow-sm cursor-pointer">${label}</button>`;
    }

    function renderPaketPagination(data) {
        const nav = document.getElementById('paket-pagination');
        if (!nav) return;

        nav.innerHTML = '';

        const totalPages = Number(data.last_page) || 0;
        if (totalPages <= 1) return;

        const currentPage = Number(data.current_page) || 1;
        const maxButtons = 5;
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + maxButtons - 1);
        if (endPage - startPage < maxButtons - 1) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }

        let html = '';

        // Previous
        html += paketPageButton(currentPage - 1, '<i class="bx bx-chevron-left text-sm"></i>', false, currentPage === 1, 'Halaman Sebelumnya');

        // First page + ellipsis
        if (startPage > 1) {
            html += paketPageButton(1, '1', currentPage === 1, false, 'Halaman 1');
            if (startPage > 2) {
                html += `<span class="w-6 text-center text-slate-400 text-xs font-mono select-none">...</span>`;
            }
        }

        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            html += paketPageButton(i, String(i), i === currentPage, false, `Halaman ${i}`);
        }

        // Last page + ellipsis
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span class="w-6 text-center text-slate-400 text-xs font-mono select-none">...</span>`;
            }
            html += paketPageButton(totalPages, String(totalPages), currentPage === totalPages, false, `Halaman ${totalPages}`);
        }

        // Next
        html += paketPageButton(currentPage + 1, '<i class="bx bx-chevron-right text-sm"></i>', false, currentPage === totalPages, 'Halaman Selanjutnya');

        nav.innerHTML = html;
    }

    function goToPaketPage(page) {
        paketCurrentPage = page;
        fetchPaketData(page, paketSearchTerm);
    }

    window.goToPaketPage = goToPaketPage;

    // --------------------------------------------------------------------------
    // Hapus paket (hanya dari database, tidak menyentuh Mikrotik)
    // --------------------------------------------------------------------------
    function deletePaket(id) {
        const item = paketListById[id] || {};
        const namaPaket = item.nama_paket || 'Paket ini';

        if (typeof Swal === 'undefined') {
            if (window.confirm(`Hapus paket "${namaPaket}" dari database? Profile paket di Mikrotik tidak akan diubah.`)) {
                performDeletePaket(id);
            }
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Hapus Paket?',
            html: `Paket <strong>${escapeHtml(namaPaket)}</strong> akan dihapus <strong>hanya dari tabel paket</strong>.<br>
                   <span class="text-xs text-slate-500">Data pelanggan, invoice, dan perusahaan tidak ikut dihapus, tetapi relasinya ke paket ini akan menjadi tidak valid. Profile di Mikrotik juga tidak diubah.</span>`,
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                performDeletePaket(id);
            }
        });
    }

    window.deletePaket = deletePaket;

    function performDeletePaket(id) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        showPaketLoading();

        fetch(`/hapus/paket/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfMeta ? csrfMeta.getAttribute('content') : '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(async (res) => {
                let data = {};
                try {
                    data = await res.json();
                } catch (e) {
                    data = {};
                }

                hidePaketLoading();

                if (!res.ok || data.success === false) {
                    const message = data.message || 'Paket gagal dihapus. Silakan coba lagi.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tidak Dapat Menghapus',
                            text: message
                        });
                    } else {
                        alert(message);
                    }
                    return;
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message || 'Paket berhasil dihapus dari database.',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                }

                fetchPaketData(paketCurrentPage, paketSearchTerm);
            })
            .catch((err) => {
                console.error('Gagal menghapus paket:', err);
                hidePaketLoading();

                const message = 'Gagal menghapus paket. Periksa koneksi Anda lalu coba lagi.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan',
                        text: message
                    });
                } else {
                    alert(message);
                }
            });
    }

    window.performDeletePaket = performDeletePaket;

    // --------------------------------------------------------------------------
    // Filters & initialization
    // --------------------------------------------------------------------------
    $(document).ready(function () {
        const searchInput = document.getElementById('paket-search-input');
        const pageSizeSelect = document.getElementById('paket-page-size');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(paketSearchTimeout);
                paketSearchTerm = this.value;
                paketCurrentPage = 1;
                paketSearchTimeout = setTimeout(function () {
                    fetchPaketData(1, paketSearchTerm);
                }, 300);
            });

            searchInput.addEventListener('keypress', function (e) {
                if (e.which === 13) {
                    clearTimeout(paketSearchTimeout);
                    paketSearchTerm = this.value;
                    paketCurrentPage = 1;
                    fetchPaketData(1, paketSearchTerm);
                }
            });
        }

        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', function () {
                paketPerPage = parseInt(this.value, 10) || 10;
                paketCurrentPage = 1;
                fetchPaketData(1, paketSearchTerm);
            });
        }

        fetchPaketData(1, '');
    });
</script>
@endpush
