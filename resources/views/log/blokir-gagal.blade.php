@extends('layouts.contentNavbarLayout')

@section('title', 'Log Blokir Gagal')

<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<style>
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 6px;
        flex-wrap: wrap;
    }

    .pagination li {
        margin: 0;
    }

    .pagination .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        color: #6b7280;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .pagination .page-link:hover {
        background-color: #f8fafc;
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .pagination .active .page-link {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    .pagination .disabled .page-link {
        background-color: #f9fafb;
        color: #d1d5db;
        cursor: not-allowed;
    }

    .hover-lift:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .mobile-stack {
            flex-direction: column;
            align-items: stretch;
        }

        .mobile-text-center {
            text-align: center;
        }

        .mobile-padding {
            padding: 1rem 0.75rem;
        }
    }

    .error-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .error-connection_failed { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .error-secret_empty { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .error-user_not_found { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .error-multiple_users { background: #fce7f3; color: #db2777; border: 1px solid #fbcfe8; }
    .error-name_mismatch { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .error-api_exception { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .error-unknown_error { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
</style>

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Stats and Info Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="bg-white rounded-lg border border-gray-200 p-4 hover-lift">
                    <div class="flex items-center space-x-3">
                        <div class="bg-red-100 p-3 rounded-lg">
                            <i class='bx bx-x-circle text-red-500 text-xl'></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Total Gagal Blokir</p>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $logs->total() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="bg-white rounded-lg border border-gray-200 p-4 hover-lift">
                    <div class="flex items-center space-x-3">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class='bx bx-time text-blue-600 text-xl'></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Update Terakhir</p>
                            <h3 class="text-lg font-bold text-gray-900">{{ now()->format('d M Y, H:i') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="bg-white rounded-lg border border-gray-200 p-4 hover-lift">
                    <div class="flex items-center space-x-3">
                        <div class="bg-yellow-100 p-3 rounded-lg">
                            <i class='bx bx-server text-yellow-600 text-xl'></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Sumber Error</p>
                            <h3 class="text-lg font-bold text-gray-900">
                                @php
                                    $auto = $logs->where('source', 'auto')->count();
                                    $manual = $logs->where('source', 'manual')->count();
                                @endphp
                                {{ $auto }} Auto / {{ $manual }} Manual
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden fade-in">
            <!-- Card Header -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-white">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-1">Daftar Gagal Blokir</h2>
                        <p class="text-gray-500 text-sm">Riwayat kegagalan blokir pelanggan dari sistem dan manual</p>
                    </div>
                    <div class="flex items-center space-x-2 text-sm">
                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                        <span class="text-gray-600 badge bg-label-danger">Perlu perhatian</span>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
                <form method="GET" id="filterForm" class="space-y-3 md:space-y-0 md:grid md:grid-cols-2 lg:grid-cols-4 md:gap-3">
                    <!-- Search Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-search text-gray-400'></i>
                        </div>
                        <input type="text"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white text-sm"
                               id="searchInput"
                               name="search"
                               placeholder="Cari pelanggan, usersecret, pesan..."
                               value="{{ request('search') }}">
                    </div>

                    <!-- Error Type Filter -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-error text-gray-400'></i>
                        </div>
                        <select class="w-full pl-10 pr-8 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white text-sm appearance-none"
                               id="filterErrorType"
                               name="error_type"
                               onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Tipe Error</option>
                            <option value="connection_failed" {{ request('error_type') === 'connection_failed' ? 'selected' : '' }}>Koneksi Gagal</option>
                            <option value="secret_empty" {{ request('error_type') === 'secret_empty' ? 'selected' : '' }}>Usersecret Kosong</option>
                            <option value="user_not_found" {{ request('error_type') === 'user_not_found' ? 'selected' : '' }}>User Tidak Ditemukan</option>
                            <option value="multiple_users" {{ request('error_type') === 'multiple_users' ? 'selected' : '' }}>Multiple Users</option>
                            <option value="name_mismatch" {{ request('error_type') === 'name_mismatch' ? 'selected' : '' }}>Nama Tidak Cocok</option>
                            <option value="api_exception" {{ request('error_type') === 'api_exception' ? 'selected' : '' }}>API Exception</option>
                            <option value="unknown_error" {{ request('error_type') === 'unknown_error' ? 'selected' : '' }}>Error Tidak Diketahui</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                            <i class='bx bx-chevron-down text-gray-400'></i>
                        </div>
                    </div>

                    <!-- Source Filter -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-source text-gray-400'></i>
                        </div>
                        <select class="w-full pl-10 pr-8 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white text-sm appearance-none"
                               id="filterSource"
                               name="source"
                               onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Sumber</option>
                            <option value="auto" {{ request('source') === 'auto' ? 'selected' : '' }}>Sistem (Otomatis)</option>
                            <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>Manual (Admin)</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                            <i class='bx bx-chevron-down text-gray-400'></i>
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class='bx bx-calendar text-gray-400'></i>
                        </div>
                        <input type="date"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white text-sm"
                               id="filterDate"
                               name="date"
                               value="{{ request('date') }}"
                               onchange="document.getElementById('filterForm').submit()">
                    </div>

                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                </form>
            </div>

            <!-- Table Section -->
            <div class="px-0 sm:px-2 py-2">
                <div class="table-responsive">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-800">
                            <tr>
                                <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class='bx bx-time text-sm'></i>
                                        <span class="text-xs sm:text-sm">Waktu</span>
                                    </div>
                                </th>
                                <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class='bx bx-user text-sm'></i>
                                        <span class="text-xs sm:text-sm">Pelanggan</span>
                                    </div>
                                </th>
                                <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class='bx bx-wifi text-sm'></i>
                                        <span class="text-xs sm:text-sm">Usersecret</span>
                                    </div>
                                </th>
                                <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class='bx bx-error-circle text-sm'></i>
                                        <span class="text-xs sm:text-sm">Tipe Error</span>
                                    </div>
                                </th>
                                <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class='bx bx-detail text-sm'></i>
                                        <span class="text-xs sm:text-sm">Pesan Error</span>
                                    </div>
                                </th>
                                <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <i class='bx bx-source text-sm'></i>
                                        <span class="text-xs sm:text-sm">Sumber</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logs as $log)
                            <tr class="hover:bg-red-50 transition-colors duration-150 group">
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-1.5 h-1.5 bg-red-500 rounded-full flex-shrink-0"></div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                            <i class='bx bx-calendar mr-1 text-xs'></i>
                                            <span class="text-xs">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-600 rounded-full flex items-center justify-center text-white text-xs sm:text-sm font-semibold flex-shrink-0">
                                            {{ substr($log->customer->nama_customer ?? '?', 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-medium text-gray-900 text-sm block truncate max-w-[120px] sm:max-w-[180px]">{{ $log->customer->nama_customer ?? '-' }}</span>
                                            <span class="text-gray-500 text-xs truncate hidden sm:block">ID: {{ $log->customer_id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-mono bg-gray-100 text-gray-700 border border-gray-200">
                                        {{ $log->usersecret ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                    <span class="error-badge error-{{ $log->error_type }}">
                                        @switch($log->error_type)
                                            @case('connection_failed') Koneksi Gagal @break
                                            @case('secret_empty') Usersecret Kosong @break
                                            @case('user_not_found') User Tidak Ditemukan @break
                                            @case('multiple_users') Multiple Users @break
                                            @case('name_mismatch') Nama Tidak Cocok @break
                                            @case('api_exception') API Exception @break
                                            @default Error Tidak Diketahui
                                        @endswitch
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-1.5 h-1.5 bg-red-500 rounded-full flex-shrink-0"></div>
                                        <span class="text-gray-700 font-normal text-sm break-words">{{ $log->error_message }}</span>
                                    </div>
                                    @if($log->error_detail)
                                    <details class="mt-1">
                                        <summary class="text-xs text-gray-500 cursor-pointer hover:text-blue-600">
                                            <i class='bx bx-info-circle'></i> Detail error
                                        </summary>
                                        <pre class="mt-1 p-2 bg-gray-100 rounded text-xs text-gray-600 overflow-x-auto max-w-[300px] sm:max-w-[500px] whitespace-pre-wrap">{{ $log->error_detail }}</pre>
                                    </details>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $log->source === 'auto' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                        <i class='bx {{ $log->source === 'auto' ? 'bx-bot' : 'bx-user' }} mr-1'></i>
                                        {{ $log->source === 'auto' ? 'Sistem' : 'Manual' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 sm:px-6 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class='bx bx-check-circle text-gray-400 text-xl sm:text-2xl'></i>
                                        </div>
                                        <div class="text-center">
                                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-1">Tidak ada data</h3>
                                            <p class="text-gray-500 text-sm max-w-sm">Belum ada kegagalan blokir yang tercatat.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination and Info Section -->
                <div class="mt-4 px-3 sm:px-4">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mobile-stack">
                        <!-- Data Count Info -->
                        <div class="flex items-center text-sm text-gray-600">
                            <div class="flex items-center space-x-2 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
                                <i class='bx bx-data text-blue-500'></i>
                                <span class="text-xs sm:text-sm">
                                    Menampilkan <span class="font-semibold text-gray-900">{{ $logs->firstItem() ?? 0 }}</span> -
                                    <span class="font-semibold text-gray-900">{{ $logs->lastItem() ?? 0 }}</span> dari
                                    <span class="font-semibold text-gray-900">{{ $logs->total() }}</span> data
                                </span>
                            </div>
                        </div>

                        <!-- Per Page Selector -->
                        <form method="GET" id="perPageForm" class="flex items-center space-x-2">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="error_type" value="{{ request('error_type') }}">
                            <input type="hidden" name="source" value="{{ request('source') }}">
                            <input type="hidden" name="date" value="{{ request('date') }}">

                            <label for="pageSize" class="text-sm font-medium text-gray-700 whitespace-nowrap text-xs sm:text-sm">
                                <i class='bx bx-list-ul mr-1'></i>Data per halaman:
                            </label>
                            <div class="relative">
                                <select id="pageSize"
                                        name="per_page"
                                        class="appearance-none pl-2 pr-6 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white text-xs sm:text-sm"
                                        onchange="document.getElementById('perPageForm').submit()">
                                    @foreach([10,25,50,100,250,500] as $size)
                                    <option value="{{ $size }}" {{ (int)request('per_page',10)===$size ? 'selected' : '' }}>
                                        {{ $size }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-1 flex items-center pointer-events-none">
                                    <i class='bx bx-chevron-down text-gray-400 text-xs'></i>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Pagination Links -->
                    <div class="mt-3 flex justify-center">
                        <div class="bg-white px-3 py-2 rounded-lg border border-gray-200">
                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('keydown', function(e){
        if(e.key === 'Enter'){
            document.getElementById('filterForm').submit();
        }
    });

    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(){
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => document.getElementById('filterForm').submit(), 500);
    });

    function resetFilters() {
        const url = new URL(window.location.href);
        url.search = '';
        window.location.href = url.toString();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="bx bx-loader-alt animate-spin mr-2"></i>Memproses...';
                    submitBtn.disabled = true;
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.05}s`;
            row.classList.add('fade-in');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const filterToggle = document.createElement('button');
        filterToggle.className = 'lg:hidden bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium flex items-center space-x-2 mb-3';
        filterToggle.innerHTML = '<i class="bx bx-filter-alt"></i><span>Filter</span>';

        const filterSection = document.querySelector('.bg-gray-50');
        if (filterSection && window.innerWidth < 1024) {
            filterSection.classList.add('hidden');
            filterToggle.addEventListener('click', function() {
                filterSection.classList.toggle('hidden');
            });
            filterSection.parentNode.insertBefore(filterToggle, filterSection);
        }
    });
</script>
@endsection
