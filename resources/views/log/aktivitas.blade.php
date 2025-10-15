@extends('layouts.contentNavbarLayout')

@section('title', 'Log Aktivitas')

<!-- Tambahkan CDN -->
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

    /* Mobile responsive improvements */
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
</style>

@section('content')
<div class="row">
        <div class="col-12">
            <!-- Stats and Info Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="bg-white rounded-lg border border-gray-200 p-4 hover-lift">
                        <div class="flex items-center space-x-3">
                            <div class="bg-yellow-100 p-3 rounded-lg">
                                <i class='bx bx-clipboard text-yellow-400 text-xl'></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Total Aktivitas</p>
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
                            <div class="bg-green-100 p-3 rounded-lg">
                                <i class='bx bx-check text-emerald-600 text-xl'></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Status Sistem</p>
                                <h3 class="text-lg font-bold text-emerald-600">Aktif</h3>
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
                            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-1">Daftar Aktivitas</h2>
                            <p class="text-gray-500 text-sm">Riwayat aktivitas pengguna yang tercatat sistem</p>
                        </div>
                        <div class="flex items-center space-x-2 text-sm">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-gray-600 badge bg-label-info">Sistem aktif</span>
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
                                   placeholder="Cari aktivitas..."
                                   value="{{ request('search') }}">
                        </div>

                        <!-- Role Filter -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class='bx bx-user-circle text-gray-400'></i>
                            </div>
                            <select class="w-full pl-10 pr-8 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white text-sm appearance-none"
                                    id="filterRole" 
                                    name="roles"
                                    onchange="document.getElementById('filterForm').submit()">
                                <option value="">Semua Role</option>
                                @foreach($role as $roles)
                                <option value="{{ $roles->id }}" {{ (string)request('roles') === (string)$roles->id ? 'selected' : '' }}>
                                    {{ $roles->name }}
                                </option>
                                @endforeach
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

                        <!-- Reset Filter -->
                        <button type="button" 
                                onclick="resetFilters()"
                                class="w-full md:w-auto px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-white hover:border-gray-400 transition-all duration-200 font-medium text-sm flex items-center justify-center space-x-2 bg-white">
                            <i class='bx bx-reset'></i>
                            <span>Reset Filter</span>
                        </button>

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
                                            <span class="text-xs sm:text-sm">User</span>
                                        </div>
                                    </th>
                                    <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">
                                        <div class="flex items-center space-x-1 sm:space-x-2">
                                            <i class='bx bx-shield text-sm'></i>
                                            <span class="text-xs sm:text-sm">Role</span>
                                        </div>
                                    </th>
                                    <th class="px-3 sm:px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        <div class="flex items-center space-x-1 sm:space-x-2">
                                            <i class='bx bx-check text-sm'></i>
                                            <span class="text-xs sm:text-sm">Aktivitas</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($logs as $log)
                                <tr class="hover:bg-blue-50 transition-colors duration-150 group">
                                    <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-1.5 h-1.5 bg-blue-500 rounded-full flex-shrink-0"></div>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class='bx bx-calendar mr-1 text-xs'></i>
                                                <span class="text-xs">{{ $log->updated_at->format('d M Y, H:i') }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-600 rounded-full flex items-center justify-center text-white text-xs sm:text-sm font-semibold flex-shrink-0">
                                                {{ substr($log->causer->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <span class="font-medium text-gray-900 text-sm block truncate">{{ $log->causer->name ?? '-' }}</span>
                                                <span class="text-gray-500 text-xs truncate hidden sm:block">{{ $log->causer->email ?? '' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class='bx bx-user-circle mr-1 text-xs'></i>
                                            <span class="text-xs">{{ $log->causer->roles->name ?? '-' }}</span>
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-4 py-3">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full flex-shrink-0"></div>
                                            <span class="text-gray-700 font-normal text-sm break-words">{{ $log->description }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 sm:px-6 py-8 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                                <i class='bx bx-receipt text-gray-400 text-xl sm:text-2xl'></i>
                                            </div>
                                            <div class="text-center">
                                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-1">Tidak ada data</h3>
                                                <p class="text-gray-500 text-sm max-w-sm">Belum ada aktivitas yang tercatat dalam sistem.</p>
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
                                <input type="hidden" name="roles" value="{{ request('roles') }}">
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
    // Submit search saat tekan Enter
    document.getElementById('searchInput').addEventListener('keydown', function(e){
        if(e.key === 'Enter'){
            document.getElementById('filterForm').submit();
        }
    });

    // Auto-submit 500ms setelah user berhenti mengetik
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(){
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => document.getElementById('filterForm').submit(), 500);
    });

    // Reset filters
    function resetFilters() {
        const url = new URL(window.location.href);
        url.search = '';
        window.location.href = url.toString();
    }

    // Add loading state to form submissions
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

    // Add fade-in animation to table rows
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.05}s`;
            row.classList.add('fade-in');
        });
    });

    // Mobile menu toggle for filters
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