@extends('layouts.contentNavbarLayout')

@section('title', 'Tiket Open')

@section('page-style')
<script src="https://cdn.tailwindcss.com"></script>
@endsection

@section('content')
<!-- Header -->
<div class="mb-4">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h4 class="text-lg font-bold text-gray-800">Tiket Open</h4>
            <small class="text-sm text-gray-500">Daftar tiket yang sedang terbuka</small>
        </div>
        <a href="/tiket-barang" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition">
            <i class="bx bx-package"></i> Tiket Barang Keluar
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:-translate-y-1 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div>
                <h6 class="text-sm font-medium text-gray-500 mb-2">Total Data Pelanggan</h6>
                <h3 class="text-2xl font-bold text-blue-600 mb-0">{{ $customer->total() ?? $customer->count() }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 text-xl">
                <i class="bx bxs-user"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:-translate-y-1 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div>
                <h6 class="text-sm font-medium text-gray-500 mb-2">Tiket Aktif</h6>
                <h3 class="text-2xl font-bold text-red-500 mb-0">{{ $tiketOpenAktif }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-500 text-xl">
                <i class="bx bxs-wrench"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:-translate-y-1 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div>
                <h6 class="text-sm font-medium text-gray-500 mb-2">Tiket Closed</h6>
                <h3 class="text-2xl font-bold text-green-500 mb-0">{{ $tiketClosed }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-green-500 text-xl">
                <i class="bx bxs-check-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Modern Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
            <h5 class="font-semibold text-gray-800">Daftar Pelanggan</h5>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-medium">{{ $customer->total() ?? $customer->count() }} Data</span>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <form id="searchForm" method="GET" action="{{ url()->current() }}" class="flex-1">
                <div class="flex items-stretch">
                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-400"><i class="bx bx-search"></i></span>
                    <input type="text" class="flex-1 border border-gray-300 rounded-r-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200" name="search" id="searchInput"
                           value="{{ $search }}" placeholder="Cari nama, alamat, no HP...">
                    @if($search)
                    <a href="{{ url()->current() }}" class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 text-gray-500 hover:bg-gray-50" type="button">
                        <i class="bx bx-x"></i>
                    </a>
                    @endif
                </div>
            </form>
            <form id="perPageForm" method="GET" action="{{ url()->current() }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <div class="flex items-stretch">
                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-400"><i class="bx bx-list-ul"></i></span>
                    <select name="per_page" id="perPageSelect" class="border border-gray-300 rounded-r-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200" onchange="this.form.submit()">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto modern-table-container">
        <table class="w-full text-sm">
            <thead class="bg-slate-50/80 text-slate-500 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3.5 text-center font-medium uppercase text-[11px] tracking-wider w-16">No</th>
                    <th class="px-4 py-3.5 text-left font-medium uppercase text-[11px] tracking-wider">Pelanggan</th>
                    <th class="px-4 py-3.5 text-left font-medium uppercase text-[11px] tracking-wider">Paket</th>
                    <th class="px-4 py-3.5 text-left font-medium uppercase text-[11px] tracking-wider">No HP</th>
                    <th class="px-4 py-3.5 text-center font-medium uppercase text-[11px] tracking-wider w-20">Lokasi</th>
                    <th class="px-4 py-3.5 text-center font-medium uppercase text-[11px] tracking-wider w-28">Status</th>
                    <th class="px-4 py-3.5 text-center font-medium uppercase text-[11px] tracking-wider w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $startNumber = ($customer->currentPage() - 1) * $customer->perPage() + 1;
                @endphp
                @forelse ($customer as $item)
                <tr class="modern-table-row hover:bg-slate-50/70 transition-colors">
                    <td class="px-4 py-3.5 text-center">
                        <div class="w-8 h-8 mx-auto rounded-lg bg-slate-100 flex items-center justify-center font-semibold text-slate-500">{{ $startNumber++ }}</div>
                    </td>

                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500"><i class="bx bx-user"></i></div>
                            <div>
                                <h6 class="font-semibold text-gray-800 m-0">{{ $item->nama_customer }}</h6>
                                <p class="text-xs text-gray-500 m-0">{{ Str::limit($item->alamat, 35) }}</p>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-amber-50 text-amber-600 text-xs font-medium"><i class="bx bx-package"></i>{{ $item->paket->nama_paket ?? '-' }}</span>
                    </td>

                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-2"><i class="bx bx-phone text-gray-400"></i><span class="text-gray-700">{{ $item->no_hp }}</span></div>
                    </td>

                    <td class="px-4 py-3.5 text-center">
                        @php
                        $gps = $item->gps;
                        $isLink = Str::startsWith($gps, ['http://', 'https://']);
                        $url = $isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps);
                        @endphp
                        <a href="{{ $url }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 text-indigo-500 hover:bg-indigo-500 hover:text-white transition" data-bs-toggle="tooltip" title="Buka di Google Maps" data-bs-placement="bottom">
                            <i class="bx bx-map"></i>
                        </a>
                    </td>

                    <td class="px-4 py-3.5 text-center">
                        @if ($item->status->nama_status == 'Maintenance')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-50 text-red-500 text-xs font-medium"><i class="bx bx-wrench"></i> Maintenance</span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-50 text-green-500 text-xs font-medium"><i class="bx bx-check"></i> Aktif</span>
                        @endif
                    </td>

                    <td class="px-4 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if($item->status->nama_status != 'Maintenance')
                            <a href="/open-tiket/{{ $item->id }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-500 text-white hover:bg-red-600 hover:scale-105 transition" data-bs-toggle="tooltip" title="Buka Tiket" data-bs-placement="bottom">
                                <i class="bx bx-lock-open-alt"></i>
                            </a>
                            @else
                            <button class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 text-gray-400" disabled data-bs-toggle="tooltip" title="Sedang Diproses" data-bs-placement="bottom">
                                <i class="bx bx-lock"></i>
                            </button>
                            @endif
                            <a href="/history-tiket/{{ $item->id }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-cyan-200 text-cyan-500 hover:bg-cyan-500 hover:text-white transition" data-bs-placement="bottom" data-bs-toggle="tooltip" title="Lihat History Tiket">
                                <i class="bx bx-show"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12">
                        <i class="bx bx-inbox text-5xl text-gray-300 mb-3"></i>
                        <h5 class="text-gray-400 font-semibold">Tidak ada data</h5>
                        <p class="text-gray-400 text-sm mb-0">Tidak ada data yang sama dengan search</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($customer instanceof \Illuminate\Pagination\LengthAwarePaginator && $customer->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-sm text-gray-500">
                @if($perPage == 'all')
                    Menampilkan <strong>{{ $customer->count() }}</strong> data
                @else
                    Menampilkan <strong>{{ $customer->firstItem() ?? 0 }} - {{ $customer->lastItem() ?? 0 }}</strong>
                    dari <strong>{{ $customer->total() }}</strong> data
                @endif
            </div>
            <div class="modern-pagination">
                {{ $customer->appends(['search' => $search, 'per_page' => $perPage])->onEachSide(1)->links('pagination::simple-bootstrap-5') }}
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Add scroll indicator for mobile
        const tableContainer = document.querySelector('.modern-table-container');

        function checkScroll() {
            if (tableContainer) {
                const hasHorizontalScroll = tableContainer.scrollWidth > tableContainer.clientWidth;
                if (hasHorizontalScroll) {
                    tableContainer.style.paddingBottom = '10px';
                } else {
                    tableContainer.style.paddingBottom = '0';
                }
            }
        }

        checkScroll();
        window.addEventListener('resize', checkScroll);

        // Auto submit search form dengan debounce
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        const debouncedSearch = debounce(function() {
            searchForm.submit();
        }, 500);

        if (searchInput) {
            searchInput.addEventListener('input', debouncedSearch);
        }
    });
</script>
@endsection
