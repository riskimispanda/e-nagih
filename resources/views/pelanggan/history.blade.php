@extends('layouts.contentNavbarLayout')
@section('title', 'Histori Pelanggan')

@section('vendor-style')
    <style>
        /* Custom styles for history page */
        .history-card {
            border: none;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 0.125rem 0.5rem rgba(161, 172, 184, 0.2);
        }

        .history-card:hover {
            box-shadow: 0 0.5rem 1.5rem rgba(161, 172, 184, 0.3);
        }

        .history-card .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(67, 89, 113, 0.1);
            padding: 1.5rem;
        }

        .history-card .card-body {
            padding: 0;
        }

        .history-filter {
            padding: 1rem 1.5rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(67, 89, 113, 0.1);
        }

        .timeline-item {
            position: relative;
            padding: 1.5rem 1.5rem 1.5rem 3rem;
            border-left: 2px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .timeline-item:hover {
            background-color: rgba(105, 108, 255, 0.05);
        }

        .timeline-item:last-child {
            border-left-color: transparent;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -0.5625rem;
            top: 1.75rem;
            width: 1.125rem;
            height: 1.125rem;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #696cff;
        }

        .timeline-item.payment::before {
            background-color: #71dd37;
            border-color: #71dd37;
        }

        .timeline-item.status::before {
            background-color: #03c3ec;
            border-color: #03c3ec;
        }

        .timeline-item.complaint::before {
            background-color: #ffab00;
            border-color: #ffab00;
        }

        .timeline-item.installation::before {
            background-color: #696cff;
            border-color: #696cff;
        }

        .timeline-date {
            font-size: 0.8125rem;
            color: #8592a3;
            margin-bottom: 0.5rem;
        }

        .timeline-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #566a7f;
        }

        .timeline-content {
            color: #697a8d;
            margin-bottom: 0.5rem;
        }

        .timeline-footer {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.75rem;
        }

        .timeline-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 0.375rem;
        }

        .timeline-badge i {
            margin-right: 0.25rem;
            font-size: 0.875rem;
        }

        .timeline-badge.payment {
            background-color: rgba(113, 221, 55, 0.16);
            color: #71dd37;
        }

        .timeline-badge.status {
            background-color: rgba(3, 195, 236, 0.16);
            color: #03c3ec;
        }

        .timeline-badge.complaint {
            background-color: rgba(255, 171, 0, 0.16);
            color: #ffab00;
        }

        .timeline-badge.installation {
            background-color: rgba(105, 108, 255, 0.16);
            color: #696cff;
        }

        .empty-history {
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .empty-history i {
            font-size: 3rem;
            color: #d9dee3;
            margin-bottom: 1rem;
        }

        .empty-history h6 {
            color: #566a7f;
            margin-bottom: 0.5rem;
        }

        .empty-history p {
            color: #8592a3;
            max-width: 340px;
            margin: 0 auto;
        }

        .history-tabs .nav-link {
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            color: #566a7f;
            border: none;
            border-bottom: 2px solid transparent;
        }

        .history-tabs .nav-link.active {
            color: #696cff;
            background: transparent;
            border-bottom-color: #696cff;
        }

        .history-tabs .nav-link:hover:not(.active) {
            color: #697a8d;
            border-color: transparent;
        }

        .history-tabs .nav-link i {
            margin-right: 0.5rem;
        }

        .history-tabs .badge {
            margin-left: 0.5rem;
        }

        @media (max-width: 767.98px) {
            .timeline-item {
                padding-left: 2.5rem;
            }

            .history-tabs .nav-link {
                padding: 0.75rem 1rem;
            }

            .history-tabs .nav-link span {
                display: none;
            }

            .history-tabs .nav-link i {
                margin-right: 0;
                font-size: 1.25rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card history-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Histori Aktivitas</h5>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-export me-1"></i> Ekspor
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bx bx-file me-2"></i>PDF</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bx bx-spreadsheet me-2"></i>Excel</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bx bx-printer me-2"></i>Cetak</a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filter and Tabs -->
                    <div class="history-filter">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <ul class="nav nav-tabs history-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all"
                                            role="tab" aria-controls="all" aria-selected="true">
                                            <i class="bx bx-list-ul"></i>
                                            <span>Semua</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="payment-tab" data-bs-toggle="tab" href="#payment"
                                            role="tab" aria-controls="payment" aria-selected="false">
                                            <i class="bx bx-credit-card"></i>
                                            <span>Pembayaran</span>
                                            <span class="badge bg-label-primary rounded-pill">3</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="status-tab" data-bs-toggle="tab" href="#status"
                                            role="tab" aria-controls="status" aria-selected="false">
                                            <i class="bx bx-info-circle"></i>
                                            <span>Status</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="complaint-tab" data-bs-toggle="tab" href="#complaint"
                                            role="tab" aria-controls="complaint" aria-selected="false">
                                            <i class="bx bx-message-square-dots"></i>
                                            <span>Pengaduan</span>
                                        </a>
                                    </li>
                                </ul>
                                <hr class="my-2">
                            </div>
                            <div class="col-md-6 me-2 mb-2 mt-4">
                                <div class="d-flex gap-2 justify-content-md-end">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                        <input type="date" id="date-filter" class="form-control"
                                            placeholder="Filter tanggal">
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                        <input type="text" id="search-filter" class="form-control" placeholder="Cari...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Content -->
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                            <div class="timeline">
                                <!-- Empty state for no search results -->
                                <div class="empty-history d-none" id="no-results-message">
                                    <i class="bx bx-search-alt"></i>
                                    <h6>Tidak Ada Hasil</h6>
                                    <p>Tidak ada hasil yang cocok dengan filter yang Anda terapkan. Coba ubah kriteria
                                        pencarian atau hapus filter.</p>
                                    <button type="button" class="btn btn-primary mt-3 btn-sm" id="clear-filters-btn">
                                        <i class="bx bx-reset me-1"></i> Hapus Filter
                                    </button>
                                </div>

                                <!-- Complaint History Item -->
                                @foreach ($pengaduan as $item)
                                    <div class="timeline-item complaint history-entry"
                                        data-date="{{ $item->created_at->format('Y-m-d') }}"
                                        data-content="{{ strtolower($item->jenis_pengaduan . ' ' . $item->deskripsi . ' ' . $item->pengaduan->jenis_pengaduan . ' ' . $item->status->nama_status) }}">
                                        <div class="timeline-date">
                                            <i class="bx bx-calendar-event me-1"></i>
                                            {{ $item->created_at->format('d M Y, H:i') }}
                                        </div>
                                        <h6 class="timeline-title">{{ $item->jenis_pengaduan }}</h6>
                                        <div class="timeline-content">
                                            {{ $item->deskripsi }}
                                        </div>
                                        @if ($item->pengaduan->jenis_pengaduan == 'Gangguan Teknis')
                                            <div class="timeline-footer">
                                                <span class="timeline-badge complaint">
                                                    <i class="bx bx-wifi"></i>
                                                    {{ $item->pengaduan->jenis_pengaduan }}
                                                </span>
                                                @if ($item->status_id == 1)
                                                    <span class="timeline-badge bg-warning bg-opacity-10 complaint">
                                                        <i class="bx bx-rotate-left"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @elseif($item->status_id == 2)
                                                    <span class="timeline-badge bg-info bg-opacity-10 complaint text-info">
                                                        <i class="bx bx-rotate-left"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @elseif($item->status_id == 3)
                                                    <span
                                                        class="timeline-badge bg-success bg-opacity-10 complaint text-success">
                                                        <i class="bx bx-check-circle"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif($item->pengaduan->jenis_pengaduan == 'Masalah Tagihan')
                                            <div class="timeline-footer">
                                                <span class="timeline-badge complaint">
                                                    <i class="bx bx-receipt"></i>
                                                    {{ $item->pengaduan->jenis_pengaduan }}
                                                </span>
                                                @if ($item->status_id == 1)
                                                    <span class="timeline-badge bg-warning bg-opacity-10 complaint">
                                                        <i class="bx bx-rotate-left"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @elseif($item->status_id == 2)
                                                    <span class="timeline-badge bg-info bg-opacity-10 complaint text-info">
                                                        <i class="bx bx-rotate-left"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @elseif($item->status_id == 3)
                                                    <span
                                                        class="timeline-badge bg-success bg-opacity-10 complaint text-success">
                                                        <i class="bx bx-check-circle"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif($item->pengaduan->jenis_pengaduan == 'Lainnya')
                                            <div class="timeline-footer">
                                                <span class="timeline-badge complaint">
                                                    <i class="bx bx-help-circle"></i>
                                                    {{ $item->pengaduan->jenis_pengaduan }}
                                                </span>
                                                @if ($item->status_id == 1)
                                                    <span class="timeline-badge bg-warning bg-opacity-10 complaint">
                                                        <i class="bx bx-rotate-left"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @elseif($item->status_id == 2)
                                                    <span class="timeline-badge bg-info bg-opacity-10 complaint text-info">
                                                        <i class="bx bx-rotate-left"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @elseif($item->status_id == 3)
                                                    <span
                                                        class="timeline-badge bg-success bg-opacity-10 complaint text-success">
                                                        <i class="bx bx-check-circle"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                <div class="empty-history d-none">
                                    <i class="bx bx-message-square-x"></i>
                                    <h6>Tidak Ada Pengaduan</h6>
                                    <p>Anda belum pernah mengajukan pengaduan. Jika Anda mengalami masalah, silakan ajukan
                                        pengaduan melalui menu Pengaduan.</p>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                            <div class="timeline">
                                <!-- Payment History Items -->
                                <div class="timeline-item payment history-entry" data-date="2023-05-15"
                                    data-content="pembayaran tagihan berhasil premium 20 mbps mei 2023 lunas">
                                    <div class="timeline-date">
                                        <i class="bx bx-calendar-event me-1"></i> 15 Mei 2023, 14:30
                                    </div>
                                    <h6 class="timeline-title">Pembayaran Tagihan Berhasil</h6>
                                    <div class="timeline-content">
                                        Pembayaran tagihan internet paket "Premium 20 Mbps" untuk periode Mei 2023 telah
                                        berhasil.
                                    </div>
                                    <div class="timeline-footer">
                                        <span class="timeline-badge payment">
                                            <i class="bx bx-check-circle"></i> Lunas
                                        </span>
                                        <span class="text-muted">No. Invoice: INV/2023/05/001</span>
                                    </div>
                                </div>

                                <div class="timeline-item payment history-entry" data-date="2023-04-30"
                                    data-content="pembayaran biaya instalasi awal lunas">
                                    <div class="timeline-date">
                                        <i class="bx bx-calendar-event me-1"></i> 30 April 2023, 13:20
                                    </div>
                                    <h6 class="timeline-title">Pembayaran Biaya Instalasi</h6>
                                    <div class="timeline-content">
                                        Pembayaran biaya instalasi awal telah berhasil dilakukan.
                                    </div>
                                    <div class="timeline-footer">
                                        <span class="timeline-badge payment">
                                            <i class="bx bx-check-circle"></i> Lunas
                                        </span>
                                        <span class="text-muted">No. Invoice: INV/2023/04/099</span>
                                    </div>
                                </div>

                                <div class="timeline-item payment history-entry" data-date="2023-04-15"
                                    data-content="pembayaran deposit pendaftaran layanan internet lunas">
                                    <div class="timeline-date">
                                        <i class="bx bx-calendar-event me-1"></i> 15 April 2023, 11:45
                                    </div>
                                    <h6 class="timeline-title">Pembayaran Deposit</h6>
                                    <div class="timeline-content">
                                        Pembayaran deposit untuk pendaftaran layanan internet telah berhasil.
                                    </div>
                                    <div class="timeline-footer">
                                        <span class="timeline-badge payment">
                                            <i class="bx bx-check-circle"></i> Lunas
                                        </span>
                                        <span class="text-muted">No. Invoice: INV/2023/04/050</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="status" role="tabpanel" aria-labelledby="status-tab">
                            <div class="timeline">
                                <!-- Status Update History Items -->
                                @foreach ($pengaduan as $update)
                                    @if ($update->status_id == 3)
                                        <div class="timeline-item status history-entry"
                                            data-date="{{ $update->created_at->format('Y-m-d') }}"
                                            data-content="{{ strtolower($update->pengaduan->jenis_pengaduan . ' ' . $update->status->nama_status . ' perubahan status') }}">
                                            <div class="timeline-date">
                                                <i class="bx bx-calendar-event me-1"></i>
                                                {{ $update->created_at->format('d M Y, H:i') }}
                                            </div>
                                            <h6 class="timeline-title">{{ $update->pengaduan->jenis_pengaduan }}</h6>
                                            <div class="timeline-content">
                                                {{ $update->status->nama_status }}.
                                            </div>
                                            <div class="timeline-footer">
                                                <span class="timeline-badge status">
                                                    <i class="bx bx-info-circle"></i> Perubahan Status
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                <div class="empty-history d-none">
                                    <i class="bx bx-message-square-x"></i>
                                    <h6>Belum Ada Perubahan Status</h6>
                                    <p>Anda belum memiliki riwayat perubahan status pengaduan.</p>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="complaint" role="tabpanel" aria-labelledby="complaint-tab">
                            <div class="timeline">
                                <!-- Complaint History Items -->
                                @foreach ($pengaduan as $item)
                                    @if ($item->status_id == 1 || $item->status_id == 2)
                                        <div class="timeline-item complaint history-entry"
                                            data-date="{{ $item->created_at->format('Y-m-d') }}"
                                            data-content="{{ strtolower($item->jenis_pengaduan . ' ' . $item->deskripsi . ' ' . $item->pengaduan->jenis_pengaduan . ' ' . $item->status->nama_status) }}">
                                            <div class="timeline-date">
                                                <i class="bx bx-calendar-event me-1"></i>
                                                {{ $item->created_at->format('d M Y, H:i') }}
                                            </div>
                                            <h6 class="timeline-title">{{ $item->jenis_pengaduan }}</h6>
                                            <div class="timeline-content">
                                                {{ $item->deskripsi }}
                                            </div>
                                            <div class="timeline-footer">
                                                <span class="timeline-badge complaint">
                                                    <i class="bx bx-message-square-dots"></i>
                                                    {{ $item->pengaduan->jenis_pengaduan }}
                                                </span>
                                                @if ($item->status_id == 1)
                                                    <span class="timeline-badge bg-warning bg-opacity-10 complaint">
                                                        <i class="bx bx-rotate-left"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @elseif($item->status_id == 2)
                                                    <span class="timeline-badge bg-info bg-opacity-10 complaint text-info">
                                                        <i class="bx bx-rotate-left"></i>
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                <!-- Empty State if no complaints -->
                                <div class="empty-history d-none">
                                    <i class="bx bx-message-square-x"></i>
                                    <h6>Tidak Ada Pengaduan</h6>
                                    <p>Anda belum pernah mengajukan pengaduan. Jika Anda mengalami masalah, silakan ajukan
                                        pengaduan melalui menu Pengaduan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-center py-3 mb-4">
                    <button type="button" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-refresh me-1"></i> Muat Lebih Banyak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips if Bootstrap 5 is used
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Get references to the filter inputs
            const searchInput = document.getElementById('search-filter');
            const dateInput = document.getElementById('date-filter');

            // Function to filter history entries
            function filterHistoryEntries() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const dateFilter = dateInput.value; // Format: YYYY-MM-DD

                // Get all history entries
                const historyEntries = document.querySelectorAll('.history-entry');

                // Track if we have any visible entries
                let hasVisibleEntries = false;

                // Get the active tab
                const activeTab = document.querySelector('.tab-pane.active');

                // Loop through each entry and check if it matches the filters
                historyEntries.forEach(entry => {
                    const entryContent = entry.getAttribute('data-content');
                    const entryDate = entry.getAttribute('data-date');

                    // Check if the entry matches both filters
                    const matchesSearch = !searchTerm || entryContent.includes(searchTerm);
                    const matchesDate = !dateFilter || entryDate === dateFilter;

                    // Show or hide the entry based on the filters
                    if (matchesSearch && matchesDate) {
                        entry.style.display = '';
                        if (entry.closest('.tab-pane.active')) {
                            hasVisibleEntries = true;
                        }
                    } else {
                        entry.style.display = 'none';
                    }
                });

                // Show or hide the "No Results" message
                const noResultsMessage = document.getElementById('no-results-message');
                if (noResultsMessage) {
                    // Only show the no results message if we have active filters
                    if ((searchTerm || dateFilter) && !hasVisibleEntries && activeTab) {
                        noResultsMessage.classList.remove('d-none');
                    } else {
                        noResultsMessage.classList.add('d-none');
                    }
                }

                // Show or hide empty state messages in each tab
                const emptyStates = document.querySelectorAll('.empty-history:not(#no-results-message)');
                emptyStates.forEach(emptyState => {
                    const parentTab = emptyState.closest('.tab-pane');
                    if (parentTab && parentTab.classList.contains('active')) {
                        // Only check if there are no filters applied
                        if (!searchTerm && !dateFilter) {
                            const entriesInTab = parentTab.querySelectorAll('.history-entry');
                            if (entriesInTab.length === 0) {
                                emptyState.classList.remove('d-none');
                            } else {
                                emptyState.classList.add('d-none');
                            }
                        } else {
                            emptyState.classList.add('d-none');
                        }
                    }
                });

                // Update the "Load More" button visibility
                const loadMoreBtn = document.querySelector('.card-footer .btn');
                if (loadMoreBtn) {
                    loadMoreBtn.style.display = hasVisibleEntries ? '' : 'none';
                }
            }

            // Add event listeners to the filter inputs
            searchInput.addEventListener('input', filterHistoryEntries);
            dateInput.addEventListener('change', filterHistoryEntries);

            // Add event listeners to tab buttons to reset filters when changing tabs
            const tabButtons = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function() {
                    // Reset filters when changing tabs
                    searchInput.value = '';
                    dateInput.value = '';
                    filterHistoryEntries();
                });
            });

            // Add clear button functionality
            const clearFilters = () => {
                searchInput.value = '';
                dateInput.value = '';
                filterHistoryEntries();
            };

            // Add a clear button next to the search input
            const searchContainer = searchInput.closest('.input-group');
            if (searchContainer) {
                const clearButton = document.createElement('button');
                clearButton.className = 'btn btn-outline-secondary btn-sm';
                clearButton.innerHTML = '<i class="bx bx-x"></i>';
                clearButton.setAttribute('type', 'button');
                clearButton.setAttribute('title', 'Clear filters');
                clearButton.addEventListener('click', clearFilters);

                searchContainer.appendChild(clearButton);
            }

            // Add event listener to the clear filters button in the no results message
            const clearFiltersBtn = document.getElementById('clear-filters-btn');
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', clearFilters);
            }
        });
    </script>
@endsection
