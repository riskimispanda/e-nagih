@extends('layouts.contentNavbarLayout')
@section('title', 'Data Pengaduan Customer')

@section('vendor-style')
    <style>
        /* General Styles */
        body {
            scroll-behavior: smooth;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 0;
        }

        /* Card Styles */
        .card {
            box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.12);
            border-radius: 0.75rem;
            border: none;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1.5rem rgba(161, 172, 184, 0.18);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.75rem 1.75rem 1.25rem;
        }

        .card-body {
            padding: 1.5rem 1.75rem;
        }

        /* Search Styles */
        .search-input {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            transition: all 0.25s ease-in-out;
            border: 1px solid #e0e4e8;
            background-color: #f8f9fa;
            font-size: 0.9375rem;
            height: 3rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .search-input:focus {
            border-color: #696cff;
            background-color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
            outline: none;
        }

        .search-input::placeholder {
            color: #a1acb8;
            font-weight: 400;
        }

        .search-wrapper {
            position: relative;
            min-width: 300px;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-wrapper i {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: #697a8d;
            font-size: 1.25rem;
            transition: all 0.2s ease;
        }

        .search-wrapper input:focus+i {
            color: #696cff;
        }

        .search-wrapper input {
            padding-left: 3rem;
        }

        .search-wrapper::after {
            content: '';
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 1.5rem;
            background-color: #e0e4e8;
            display: none;
        }

        .controls-wrapper {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        /* Table Styles */
        .table-container {
            border-radius: 0.5rem;
            overflow: hidden;
            background-color: #fff;
            margin-bottom: 1.5rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #566a7f;
            padding: 1rem 1.25rem;
            background-color: #f5f5f9;
            border-bottom: 1px solid #f0f2f4;
        }

        .table td {
            vertical-align: middle;
            padding: 1.15rem 1.25rem;
            color: #697a8d;
            border-bottom: 1px solid #f0f2f4;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover {
            background-color: #f8f8fc;
        }

        /* Row highlight for real-time updates */
        .bg-light-primary {
            background-color: rgba(105, 108, 255, 0.08) !important;
            transition: background-color 0.5s ease-in-out;
        }

        .table tr.bg-light-primary:hover {
            background-color: rgba(105, 108, 255, 0.12) !important;
        }

        /* Status Badge Styles */
        .status-badge {
            padding: 0.35rem 0.8rem;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .status-badge i {
            margin-right: 0.35rem;
            font-size: 0.875rem;
        }

        .status-badge-pending {
            background-color: rgba(255, 171, 0, 0.12);
            color: #ffab00;
        }

        .status-badge-completed {
            background-color: rgba(40, 199, 111, 0.12);
            color: #28c76f;
        }

        .status-badge-processing {
            background-color: rgba(0, 207, 232, 0.12);
            color: #00cfe8;
        }

        .status-badge-cancelled {
            background-color: rgba(234, 84, 85, 0.12);
            color: #ea5455;
        }

        /* Action Button Styles */
        .action-btn {
            padding: 0.5rem;
            transition: all 0.2s;
            border-radius: 0.375rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.25rem 0.5rem rgba(161, 172, 184, 0.15);
        }

        .action-btn i {
            font-size: 1.15rem;
        }

        /* Pagination Styles */
        .pagination {
            gap: 0.25rem;
        }

        .pagination .page-item .page-link {
            border-radius: 0.375rem;
            min-width: 2.25rem;
            height: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            color: #697a8d;
            border: 1px solid #d9dee3;
        }

        .pagination .page-item.active .page-link {
            background-color: #696cff;
            border-color: #696cff;
            color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(105, 108, 255, 0.4);
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
        }

        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }

        /* Form Styles */
        .form-label {
            font-weight: 500;
            color: #566a7f;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 0.375rem;
            padding: 0.625rem 0.875rem;
            border: 1px solid #d9dee3;
            background-color: #fff;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #696cff;
            box-shadow: 0 0 0.25rem rgba(105, 108, 255, 0.1);
        }

        /* Empty State Styles */
        .empty-state {
            display: none;
            text-align: center;
            padding: 3rem 1.5rem;
        }

        .empty-state-icon {
            font-size: 3rem;
            color: #d9dee3;
            margin-bottom: 1rem;
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 500;
            color: #566a7f;
            margin-bottom: 0.5rem;
        }

        .empty-state-subtitle {
            color: #697a8d;
            margin-bottom: 1.5rem;
        }

        /* Responsive Styles */
        @media (max-width: 767.98px) {
            .card-header {
                padding: 1.5rem 1.25rem 1rem;
            }

            .card-body {
                padding: 1.25rem;
            }

            .table th,
            .table td {
                padding: 1rem;
            }

            .search-wrapper {
                width: 100%;
            }

            .controls-wrapper {
                width: 100%;
                justify-content: space-between;
            }
        }

        /* Sticky Header for Table */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Loading Indicator */
        .loading-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 20;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            width: 2.5rem;
            height: 2.5rem;
            border: 0.25rem solid rgba(105, 108, 255, 0.2);
            border-radius: 50%;
            border-top-color: #696cff;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Tooltip Styles */
        .custom-tooltip {
            position: relative;
        }

        .custom-tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #566a7f;
            color: #fff;
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 100;
            margin-bottom: 0.5rem;
        }

        /* Refresh Indicator Styles - Hidden but kept for reference */
        .refresh-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #28c76f;
            position: relative;
            margin-left: 5px;
        }

        .refresh-indicator::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: #28c76f;
            animation: pulse 2s infinite;
            opacity: 0.8;
        }

        .refresh-indicator.refreshing {
            background-color: #ffab00;
        }

        .refresh-indicator.refreshing::after {
            background-color: #ffab00;
            animation: pulse 1s infinite;
        }

        /* Notification Badge Styles */
        .notification-badge {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-right: 10px;
        }

        .notification-badge i {
            font-size: 1.5rem;
            color: #697a8d;
        }

        .notification-badge .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ea5455;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .notification-badge.has-new i {
            color: #ea5455;
            animation: bell-shake 0.8s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
            transform-origin: top center;
        }

        @keyframes bell-shake {
            0% {
                transform: rotate(0);
            }

            15% {
                transform: rotate(5deg);
            }

            30% {
                transform: rotate(-5deg);
            }

            45% {
                transform: rotate(4deg);
            }

            60% {
                transform: rotate(-4deg);
            }

            75% {
                transform: rotate(2deg);
            }

            85% {
                transform: rotate(-2deg);
            }

            92% {
                transform: rotate(1deg);
            }

            100% {
                transform: rotate(0);
            }
        }

        /* Table update flash animation */
        .table-updated {
            animation: flash-update 1s ease-out;
        }

        @keyframes flash-update {
            0% {
                background-color: rgba(105, 108, 255, 0.08);
            }

            100% {
                background-color: transparent;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.8;
            }

            70% {
                transform: scale(2.5);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 0;
            }
        }

        /* Toast animation styles */
        @keyframes toast-in {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes toast-out {
            0% {
                transform: translateY(0);
                opacity: 1;
            }

            100% {
                transform: translateY(20px);
                opacity: 0;
            }
        }

        .toast-container {
            z-index: 1090;
        }

        .toast {
            margin-bottom: 0.75rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                <h1 class="page-title">Data Pengaduan Customer</h1>
                <div class="d-flex align-items-center mt-3 mt-md-0">
                    <span class="text-muted me-3 d-none d-md-block">Total: <span
                            id="totalRecords">{{ count($pengaduan) }}</span> pengaduan</span>
                    <div class="d-flex align-items-center me-3">
                        <span class="text-muted me-2 small d-none">Terakhir diperbarui: <span id="lastRefreshTime"
                                class="fw-semibold">{{ date('H:i:s') }}</span></span>
                        <div class="refresh-indicator d-none" id="refreshIndicator"></div>
                        <div id="notificationBadge" class="notification-badge">
                            <i class="bx bx-bell"></i>
                            <span class="notification-count">0</span>
                        </div>
                    </div>
                    <button class="btn btn-outline-primary btn-sm d-flex align-items-center" id="refreshBtn">
                        <i class="bx bx-refresh me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="controls-wrapper w-100">
                        <div class="search-wrapper w-100">
                            <i class="bx bx-search"></i>
                            <input type="text" id="searchInput" class="form-control search-input"
                                placeholder="Cari pengaduan berdasarkan nama, jenis, atau status...">
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div id="activeFilters" class="d-flex flex-wrap gap-2">
                            <!-- Active filters will be displayed here -->
                        </div>
                        <button id="clearFilters" class="btn btn-outline-secondary btn-sm d-none">
                            <i class="bx bx-x"></i> Clear
                        </button>
                    </div>
                </div>

                <div class="card-body position-relative">
                    <!-- Loading Overlay -->
                    <div class="loading-overlay" id="loadingOverlay">
                        <div class="spinner"></div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container">
                        <div class="table-responsive mt-5">
                            <table class="table" id="pengaduanTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Customer</th>
                                        <th>Jenis Pengaduan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pengaduan as $item)
                                        <tr>
                                            <td><span class="fw-semibold">#{{ $item->id }}</span></td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $item->customer->nama_customer }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $item->pengaduan->jenis_pengaduan }}</td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span
                                                        class="fw-semibold">{{ $item->created_at->format('d F Y') }}</span>
                                                    <small
                                                        class="text-muted">{{ $item->created_at->isoFormat('dddd, HH:mm') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = '';
                                                    $statusIcon = '';
                                                    switch ($item->status_id) {
                                                        case 1:
                                                            $statusClass = 'status-badge-pending';
                                                            $statusIcon = 'bx-time';
                                                            $statusText = 'Menunggu';
                                                            break;
                                                        case 2:
                                                            $statusClass = 'status-badge-processing';
                                                            $statusIcon = 'bx-loader-circle';
                                                            $statusText = 'Diproses';
                                                            break;
                                                        case 3:
                                                            $statusClass = 'status-badge-completed';
                                                            $statusIcon = 'bx-check-circle';
                                                            $statusText = 'Selesai';
                                                            break;
                                                        case 4:
                                                            $statusClass = 'status-badge-cancelled';
                                                            $statusIcon = 'bx-x-circle';
                                                            $statusText = 'Dibatalkan';
                                                            break;
                                                        default:
                                                            $statusClass = 'status-badge-pending';
                                                            $statusIcon = 'bx-question-mark';
                                                            $statusText = 'Unknown';
                                                    }
                                                @endphp
                                                <span class="status-badge {{ $statusClass }}">
                                                    <i class="bx {{ $statusIcon }}"></i>
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-sm action-btn custom-tooltip"
                                                        data-tooltip="Lihat Detail">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-outline-warning btn-sm action-btn custom-tooltip"
                                                        data-bs-toggle="modal" data-bs-target="#tambah" data-tooltip="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-sm action-btn custom-tooltip"
                                                        data-tooltip="Hapus">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                                <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                                                <p class="text-muted mb-0">Belum ada Transaksi</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <hr class="my-2">
                        <div class="empty-state" id="emptyState">
                            <div class="empty-state-icon">
                                <i class="bx bx-search"></i>
                            </div>
                            <h4 class="empty-state-title">Tidak ada pengaduan ditemukan</h4>
                            <p class="empty-state-subtitle">Coba ubah filter atau kata kunci pencarian Anda</p>
                            <button class="btn btn-primary" id="resetSearchBtn">Reset Pencarian</button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <!-- Pagination akan ditampilkan di sini -->
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                    <i class="bx bx-chevron-left"></i>
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">
                                    <i class="bx bx-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>


        </div>
    </div>

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize variables
            const searchInput = document.getElementById('searchInput');
            const table = document.getElementById('pengaduanTable');
            const emptyState = document.getElementById('emptyState');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const refreshBtn = document.getElementById('refreshBtn');
            const clearFiltersBtn = document.getElementById('clearFilters');
            const resetSearchBtn = document.getElementById('resetSearchBtn');
            const activeFiltersContainer = document.getElementById('activeFilters');
            const totalRecordsElement = document.getElementById('totalRecords');

            let activeFilters = {
                search: ''
            };

            // Search functionality with debounce
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();

                // Update active filters
                activeFilters.search = query;

                // Debounce search to improve performance
                searchTimeout = setTimeout(() => {
                    showLoading();
                    applyAllFilters();
                    hideLoading();
                    updateActiveFiltersDisplay();
                }, 300);
            });

            // We've removed the filter button, so this functionality is no longer needed

            // Clear search functionality
            clearFiltersBtn.addEventListener('click', function() {
                showLoading();

                // Reset search value
                searchInput.value = '';

                // Reset active filters
                activeFilters.search = '';

                // Show all rows
                const rows = table.getElementsByTagName('tr');
                for (let i = 1; i < rows.length; i++) {
                    rows[i].style.display = '';
                }

                // Update UI
                updateActiveFiltersDisplay();
                checkEmptyState();
                updateTotalRecordsCount();

                hideLoading();

                // Hide clear filters button
                clearFiltersBtn.classList.add('d-none');
            });

            // Reset search button in empty state
            resetSearchBtn.addEventListener('click', function() {
                clearFiltersBtn.click();
            });

            // Refresh button functionality
            refreshBtn.addEventListener('click', function() {
                refreshData(true); // true means show loading indicator
            });

            // Function to refresh data
            function refreshData(showLoadingIndicator = false) {
                if (showLoadingIndicator) {
                    showLoading();
                }

                // Fetch real data from the server
                fetchPengaduanData();

                // Set a timeout to complete the refresh process
                setTimeout(() => {
                    if (showLoadingIndicator) {
                        hideLoading();
                        // Show toast notification only when manually refreshed
                        showToast('Data berhasil diperbarui', 'success');

                        // Reset notification counter when manually refreshed
                        resetNotificationCounter();
                    }

                    // Apply any active search filter to the new data
                    if (activeFilters.search) {
                        applyAllFilters();
                    }

                    // Re-attach event listeners to any new buttons
                    attachEventListeners();
                }, 800);
            }

            // Function to reset notification counter
            function resetNotificationCounter() {
                newEntriesCount = 0;
                const notificationBadge = document.getElementById('notificationBadge');
                const notificationCount = notificationBadge.querySelector('.notification-count');
                notificationCount.textContent = '0';
                notificationBadge.classList.remove('has-new');
            }

            // Function to attach event listeners to dynamically added elements
            function attachEventListeners() {
                // Add event listeners to all action buttons
                document.querySelectorAll('.action-btn').forEach(button => {
                    if (!button.hasAttribute('data-listener')) {
                        button.setAttribute('data-listener', 'true');
                        button.addEventListener('click', function(e) {
                            if (this.getAttribute('data-tooltip') === 'Hapus') {
                                // Prevent default action for demo
                                e.preventDefault();
                                e.stopPropagation();

                                // Get the row
                                const row = this.closest('tr');

                                // Add a fade-out effect
                                row.style.transition = 'opacity 0.5s ease';
                                row.style.opacity = '0';

                                // Remove the row after animation
                                setTimeout(() => {
                                    row.remove();
                                    updateTotalRecordsCount();
                                    showToast('Data berhasil dihapus', 'success');
                                }, 500);
                            }
                        });
                    }
                });
            }

            // Function to flash the table to indicate new data
            function flashTableUpdate() {
                const tableContainer = document.querySelector('.table-container');
                if (tableContainer) {
                    tableContainer.classList.add('table-updated');
                    setTimeout(() => {
                        tableContainer.classList.remove('table-updated');
                    }, 1000);
                }
            }

            // Store the last check timestamp
            let lastCheckTimestamp = new Date().toISOString();
            let newEntriesCount = 0;

            // Function to fetch real data from the server
            function fetchPengaduanData() {
                // Fetch data from the server using AJAX with the last check timestamp
                fetch(`/helpdesk/get-pengaduan-data?last_check=${encodeURIComponent(lastCheckTimestamp)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Update the hidden timestamp
                            const refreshIndicator = document.getElementById('lastRefreshTime');
                            if (refreshIndicator) {
                                refreshIndicator.textContent = data.timestamp;
                            }

                            // Check if there are new entries
                            if (data.has_new) {
                                // Show notification
                                showNewEntriesNotification(data.new_entries);
                            }

                            // Update the table with the new data (silently in the background)
                            updateTableWithNewData(data.data);

                            // Update the total count
                            if (totalRecordsElement) {
                                totalRecordsElement.textContent = data.count;
                            }

                            // Update the last check timestamp for the next poll
                            lastCheckTimestamp = data.timestamp;
                        } else {
                            console.error('Error fetching data:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        // Don't show error toast for background polling
                    });
            }

            // Function to show notification for new entries
            function showNewEntriesNotification(newEntries) {
                if (!newEntries || newEntries.length === 0) return;

                // Update the notification badge
                const notificationBadge = document.getElementById('notificationBadge');
                const notificationCount = notificationBadge.querySelector('.notification-count');

                // Increment the count
                newEntriesCount += newEntries.length;
                notificationCount.textContent = newEntriesCount;

                // Show the badge
                notificationBadge.classList.remove('d-none');
                notificationBadge.classList.add('has-new');

                // Play notification sound if supported
                playNotificationSound();

                // Show toast notification for each new entry (max 3 to avoid flooding)
                const entriesToShow = newEntries.slice(0, 3);
                entriesToShow.forEach((entry, index) => {
                    const customerName = entry.customer ? entry.customer.nama_customer : 'Unknown';
                    const complaintType = entry.pengaduan ? entry.pengaduan.jenis_pengaduan : 'Unknown';

                    // Add a small delay between toasts to make them appear sequentially
                    setTimeout(() => {
                        showToast(`Pengaduan baru dari ${customerName}: ${complaintType}`,
                            'primary');
                    }, index * 300);
                });

                // If there are more entries than we're showing, add a summary toast
                if (newEntries.length > 3) {
                    setTimeout(() => {
                        showToast(`${newEntries.length - 3} pengaduan baru lainnya telah diterima`, 'info');
                    }, 900);
                }

                // Reset animation to allow it to trigger again
                setTimeout(() => {
                    notificationBadge.classList.remove('has-new');
                    setTimeout(() => {
                        notificationBadge.classList.add('has-new');
                    }, 50);
                }, 1000);
            }

            // Function to play notification sound
            function playNotificationSound() {
                try {
                    // Create audio element
                    const audio = new Audio('/assets/sounds/notification.mp3');
                    audio.volume = 0.5;
                    audio.play().catch(e => {
                        // Browser may block autoplay, just ignore
                        console.log('Could not play notification sound:', e);
                    });
                } catch (e) {
                    // Browser doesn't support Audio API or other issue
                    console.log('Audio not supported:', e);
                }
            }

            // Function to update the table with new data
            function updateTableWithNewData(pengaduanData) {
                if (!pengaduanData || !pengaduanData.length) {
                    return;
                }

                // Get the table body
                const tbody = table.querySelector('tbody');
                if (!tbody) return;

                // Store current rows for comparison
                const currentRows = Array.from(tbody.querySelectorAll('tr')).map(row => {
                    const idCell = row.querySelector('td:first-child span');
                    return idCell ? idCell.textContent.replace('#', '') : null;
                });

                // Clear any previous highlights
                tbody.querySelectorAll('tr').forEach(row => {
                    row.classList.remove('bg-light-primary');
                });

                // Create a document fragment to hold the new rows
                const fragment = document.createDocumentFragment();

                // Create rows for each pengaduan
                pengaduanData.forEach(item => {
                    // Check if this row already exists
                    const rowExists = currentRows.includes(item.id.toString());

                    // Create or update row
                    let row;
                    if (rowExists) {
                        // Find the existing row - we need to loop through to find the matching ID
                        const rows = tbody.querySelectorAll('tr');
                        for (let i = 0; i < rows.length; i++) {
                            const idSpan = rows[i].querySelector('td:first-child span');
                            if (idSpan && idSpan.textContent === `#${item.id}`) {
                                row = rows[i];
                                break;
                            }
                        }
                    }

                    // If row wasn't found or doesn't exist, create a new one
                    if (!row) {
                        row = document.createElement('tr');
                        row.classList.add('bg-light-primary'); // Highlight new rows
                        row.style.transition = 'background-color 1s ease';
                    }

                    // Determine status class and icon
                    let statusClass = 'status-badge-pending';
                    let statusIcon = 'bx-time';

                    if (item.status) {
                        const statusName = item.status.nama_status.toLowerCase();
                        if (statusName === 'selesai') {
                            statusClass = 'status-badge-completed';
                            statusIcon = 'bx-check-circle';
                        } else if (statusName === 'diproses') {
                            statusClass = 'status-badge-processing';
                            statusIcon = 'bx-loader-circle';
                        } else if (statusName === 'dibatalkan') {
                            statusClass = 'status-badge-cancelled';
                            statusIcon = 'bx-x-circle';
                        }
                    }

                    // Format the date
                    const createdAt = new Date(item.created_at);
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt',
                        'Nov', 'Des'
                    ];
                    const formattedDate =
                        `${createdAt.getDate().toString().padStart(2, '0')} ${months[createdAt.getMonth()]} ${createdAt.getFullYear()}`;
                    const formattedTime =
                        `${createdAt.getHours().toString().padStart(2, '0')}:${createdAt.getMinutes().toString().padStart(2, '0')}`;

                    // Set the row content
                    row.innerHTML = `
                        <td><span class="fw-semibold">#${item.id}</span></td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold">${item.customer ? item.customer.nama_customer : 'Unknown'}</span>
                            </div>
                        </td>
                        <td>${item.pengaduan ? item.pengaduan.jenis_pengaduan : 'Unknown'}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span>${formattedDate}</span>
                                <small class="text-muted">${formattedTime}</small>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge ${statusClass}">
                                <i class="bx ${statusIcon}"></i>
                                ${item.status ? item.status.nama_status : 'Unknown'}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm action-btn custom-tooltip" data-tooltip="Lihat Detail">
                                    <i class="bx bx-show"></i>
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm action-btn custom-tooltip" data-bs-toggle="modal" data-bs-target="#tambah" data-tooltip="Edit">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm action-btn custom-tooltip" data-tooltip="Hapus" data-id="${item.id}">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;

                    if (!rowExists) {
                        fragment.appendChild(row);
                    }
                });

                // If we have new rows, add them to the table
                if (fragment.children.length > 0) {
                    // Clear the table if we're replacing all data
                    if (currentRows.length === 0) {
                        tbody.innerHTML = '';
                    }

                    // Add the new rows
                    tbody.insertBefore(fragment, tbody.firstChild);
                }

                // Check if we need to show empty state
                checkEmptyState();
            }

            // Function to update the last refresh time
            function updateLastRefreshTime() {
                const now = new Date();
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const seconds = now.getSeconds().toString().padStart(2, '0');

                const timeString = `${hours}:${minutes}:${seconds}`;

                const refreshTimeElement = document.getElementById('lastRefreshTime');
                if (refreshTimeElement) {
                    refreshTimeElement.textContent = timeString;
                }
            }

            // Set up polling for real-time updates (every 3 seconds)
            let pollingInterval;
            let countdownInterval;
            let countdownValue = 3;
            const REFRESH_INTERVAL = 3; // seconds

            function updateCountdown() {
                // We're no longer showing the countdown in the UI
                // This function is kept for compatibility but doesn't update the UI
            }

            function startCountdown() {
                // Reset countdown value
                countdownValue = REFRESH_INTERVAL;

                // Clear any existing countdown
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }

                // Update immediately
                updateCountdown();

                // Set up countdown
                countdownInterval = setInterval(() => {
                    countdownValue--;
                    updateCountdown();

                    if (countdownValue < 0) {
                        countdownValue = REFRESH_INTERVAL;
                    }
                }, 1000);
            }

            function stopCountdown() {
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }
                // No longer updating UI elements
            }

            function startPolling() {
                // Clear any existing interval first
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }

                // Start countdown
                startCountdown();

                // Set up new polling interval (3000ms = 3 seconds)
                pollingInterval = setInterval(() => {
                    refreshData(false); // false means don't show loading indicator for automatic refreshes
                    // Reset countdown after refresh
                    countdownValue = REFRESH_INTERVAL;
                }, REFRESH_INTERVAL * 1000);
            }

            function stopPolling() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }

                stopCountdown();
            }

            // Start polling when page loads
            startPolling();

            // Stop polling when page is not visible to save resources
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    stopPolling();
                } else {
                    startPolling();
                }
            });

            // Also refresh immediately when page becomes visible again
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    refreshData(false);
                }
            });

            // Search function
            function applyAllFilters() {
                const rows = table.getElementsByTagName('tr');
                let visibleCount = 0;
                const searchQuery = activeFilters.search.toLowerCase();

                for (let i = 1; i < rows.length; i++) {
                    let showRow = true;
                    const cells = rows[i].getElementsByTagName('td');

                    // Skip if no cells (shouldn't happen in a well-formed table)
                    if (cells.length === 0) continue;

                    // Search filter
                    if (searchQuery) {
                        let foundInSearch = false;

                        for (let j = 0; j < cells.length; j++) {
                            if (cells[j].textContent.toLowerCase().includes(searchQuery)) {
                                foundInSearch = true;
                                break;
                            }
                        }

                        if (!foundInSearch) {
                            showRow = false;
                        }
                    }

                    // Update row visibility
                    rows[i].style.display = showRow ? '' : 'none';

                    // Count visible rows
                    if (showRow) {
                        visibleCount++;
                    }
                }

                // Check if we need to show empty state
                checkEmptyState();

                // Update total records count
                updateTotalRecordsCount(visibleCount);

                return visibleCount;
            }

            // Helper function to parse date from "DD MMM YYYY" format
            function parseDate(dateString) {
                const months = {
                    'Jan': 0,
                    'Feb': 1,
                    'Mar': 2,
                    'Apr': 3,
                    'May': 4,
                    'Mei': 4,
                    'Jun': 5,
                    'Jul': 6,
                    'Aug': 7,
                    'Agu': 7,
                    'Sep': 8,
                    'Oct': 9,
                    'Okt': 9,
                    'Nov': 10,
                    'Dec': 11,
                    'Des': 11
                };

                const parts = dateString.split(' ');
                if (parts.length === 3) {
                    const day = parseInt(parts[0], 10);
                    const month = months[parts[1]];
                    const year = parseInt(parts[2], 10);

                    if (!isNaN(day) && month !== undefined && !isNaN(year)) {
                        return new Date(year, month, day);
                    }
                }

                // Fallback to current date if parsing fails
                return new Date();
            }

            // Update active filters display
            function updateActiveFiltersDisplay() {
                // Clear current filters
                activeFiltersContainer.innerHTML = '';

                // Check if search is active
                if (activeFilters.search) {
                    clearFiltersBtn.classList.remove('d-none');
                    addFilterBadge('Pencarian: ' + activeFilters.search, 'search');
                } else {
                    clearFiltersBtn.classList.add('d-none');
                }
            }

            // Add filter badge
            function addFilterBadge(text, filterType) {
                const badge = document.createElement('div');
                badge.className = 'badge bg-light text-dark d-flex align-items-center p-2';
                badge.innerHTML = `
                    <span>${text}</span>
                    <button class="btn-close btn-close-sm ms-2" data-filter-type="${filterType}"></button>
                `;

                // Add click handler to remove search filter
                badge.querySelector('.btn-close').addEventListener('click', function() {
                    // Clear search
                    activeFilters.search = '';
                    searchInput.value = '';

                    showLoading();
                    applyAllFilters();
                    updateActiveFiltersDisplay();
                    hideLoading();
                });

                activeFiltersContainer.appendChild(badge);
            }

            // Check if we need to show empty state
            function checkEmptyState() {
                const rows = table.getElementsByTagName('tr');
                let allHidden = true;

                for (let i = 1; i < rows.length; i++) {
                    if (rows[i].style.display !== 'none') {
                        allHidden = false;
                        break;
                    }
                }

                if (allHidden && rows.length > 1) {
                    // Show empty state
                    table.closest('.table-responsive').style.display = 'none';
                    emptyState.style.display = 'block';
                } else {
                    // Hide empty state
                    table.closest('.table-responsive').style.display = 'block';
                    emptyState.style.display = 'none';
                }
            }

            // Update total records count
            function updateTotalRecordsCount(visibleCount) {
                if (visibleCount !== undefined) {
                    totalRecordsElement.textContent = visibleCount;
                } else {
                    // Count visible rows
                    const rows = table.getElementsByTagName('tr');
                    let count = 0;

                    for (let i = 1; i < rows.length; i++) {
                        if (rows[i].style.display !== 'none') {
                            count++;
                        }
                    }

                    totalRecordsElement.textContent = count;
                }
            }

            // Show loading overlay
            function showLoading() {
                loadingOverlay.style.display = 'flex';
            }

            // Hide loading overlay
            function hideLoading() {
                loadingOverlay.style.display = 'none';
            }

            // Show toast notification
            function showToast(message, type = 'info') {
                // Create toast container if it doesn't exist
                let toastContainer = document.querySelector('.toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                    document.body.appendChild(toastContainer);
                }

                // Create toast element
                const toastEl = document.createElement('div');
                toastEl.className = `toast align-items-center text-white bg-${type} border-0 shadow-lg`;
                toastEl.setAttribute('role', 'alert');
                toastEl.setAttribute('aria-live', 'assertive');
                toastEl.setAttribute('aria-atomic', 'true');

                // Add icon based on toast type
                let icon = 'bx-info-circle';
                if (type === 'success') icon = 'bx-check-circle';
                if (type === 'danger') icon = 'bx-error-circle';
                if (type === 'warning') icon = 'bx-error';
                if (type === 'primary') icon = 'bx-bell';

                toastEl.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body d-flex align-items-center">
                            <i class="bx ${icon} me-2" style="font-size: 1.25rem;"></i>
                            <span>${message}</span>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                `;

                // Add animation class
                toastEl.style.animation = 'toast-in 0.3s ease-out forwards';

                // Add toast to container
                toastContainer.appendChild(toastEl);

                // Initialize and show toast
                const toast = new bootstrap.Toast(toastEl, {
                    delay: 4000
                });
                toast.show();

                // Remove toast after it's hidden
                toastEl.addEventListener('hidden.bs.toast', function() {
                    toastEl.style.animation = 'toast-out 0.3s ease-in forwards';
                    setTimeout(() => {
                        toastEl.remove();
                    }, 300);
                });
            }

            // We've removed the filter functionality

            // Add click handler for notification badge
            const notificationBadge = document.getElementById('notificationBadge');
            if (notificationBadge) {
                notificationBadge.addEventListener('click', function() {
                    // Get the current count before resetting
                    const currentCount = parseInt(notificationBadge.querySelector('.notification-count')
                        .textContent);

                    // Reset notification counter
                    resetNotificationCounter();

                    // Flash the table to draw attention to the new entries
                    flashTableUpdate();

                    // Show toast with count information
                    if (currentCount > 0) {
                        showToast(`${currentCount} pengaduan baru telah ditandai sebagai dibaca`,
                            'success');
                    } else {
                        showToast('Tidak ada notifikasi baru', 'info');
                    }
                });
            }

            // Initialize the page
            updateTotalRecordsCount();
            checkEmptyState();
            attachEventListeners();
        });
    </script>
@endsection
@endsection
