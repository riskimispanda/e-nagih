@extends('layouts.contentNavbarLayout')
@section('title','Halaman Berita Acara')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title fw-bold mb-1">Data Berita Acara</h4>
                <small class="card-subtitle text-muted">Kelola data pelanggan</small>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <div class="input-group" style="width: 300px;">
                        <span class="input-group-text"><i class="bx bx-search-alt"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari pelanggan...">
                    </div>
                    <button class="btn btn-outline-secondary" type="button" id="refreshBtn">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <a href="javascript:window.history.back()" data-bs-toggle="tooltip" data-bs-placement="top" title="Kembali">
                            <button class="btn btn-secondary btn-sm"><i class="bx bx-arrow-back fs-5 me-2"></i> Kembali</button>
                        </a>
                    </div>
                    
                    <!-- Entries per page dropdown -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0">Tampilkan:</label>
                        <select class="form-select form-select-sm" id="entriesPerPage" style="width: auto;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">Semua</option>
                        </select>
                        <span class="text-muted">entri</span>
                    </div>
                </div>

                <!-- Table Info -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted" id="tableInfo">
                        Menampilkan 0 sampai 0 dari 0 entri
                    </div>
                    <div class="text-muted" id="filteredInfo" style="display: none;">
                        (difilter dari <span id="totalEntries">0</span> total entri)
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="beritaAcaraTable">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Pelanggan</th>
                                <th>Paket</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @forelse ($data as $item)
                            <tr class="text-center" data-search="{{ strtolower($item->nama_customer . ' ' . $item->alamat . ' ' . $item->paket->nama_paket) }}">
                                <td class="row-number">{{ $no++ }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->nama_customer }}</div>
                                    <small class="text-muted">
                                        <i class="bx bx-map-pin me-1"></i>
                                        {{ Str::limit($item->alamat, 30) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ $item->paket->nama_paket }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->status_id == 3)
                                    <span class="badge bg-label-success fw-bold">AKTIF</span>
                                    @elseif($item->status_id == 9)
                                    <span class="badge bg-label-danger fw-bold">BLOKIR</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($item->status_id == 16 || $item->status_id == 17)
                                        <button class="btn btn-outline-danger btn-sm" disabled>
                                            <i class="bx bx-clipboard"></i>
                                        </button>
                                        @else
                                        <a href="/buat-berita-acara/{{ $item->id }}">
                                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Buat BA {{ $item->nama_customer }}">
                                                <i class="bx bx-clipboard"></i>
                                            </button>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr id="noDataRow">
                                <td colspan="5" class="text-center text-muted">Tidak ada data pelanggan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted" id="paginationInfo">
                        Halaman 1 dari 1
                    </div>
                    <nav aria-label="Table pagination">
                        <ul class="pagination pagination-sm mb-0" id="pagination">
                            <!-- Pagination will be generated by JavaScript -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    let currentPage = 1;
    let entriesPerPage = 10;
    let allRows = [];
    let filteredRows = [];
    let isFiltered = false;

    // Initialize
    function init() {
        // Get all table rows (excluding header and no-data row)
        allRows = $('#beritaAcaraTable tbody tr:not(#noDataRow)').toArray();
        filteredRows = [...allRows];
        
        // Update total entries info
        $('#totalEntries').text(allRows.length);
        
        // Initial display
        displayPage();
        updatePagination();
        updateTableInfo();
    }

    // Display current page
    function displayPage() {
        const startIndex = (currentPage - 1) * entriesPerPage;
        const endIndex = entriesPerPage === 'all' ? filteredRows.length : startIndex + entriesPerPage;
        
        // Hide all rows first
        $('#beritaAcaraTable tbody tr').hide();
        
        // Show rows for current page
        if (filteredRows.length === 0) {
            // Show no data message
            if ($('#noDataRow').length === 0) {
                $('#beritaAcaraTable tbody').append('<tr id="noDataRow"><td colspan="5" class="text-center text-muted">Tidak ada data yang ditemukan</td></tr>');
            }
            $('#noDataRow').show();
        } else {
            // Hide no data row
            $('#noDataRow').hide();
            
            // Show filtered rows for current page
            for (let i = startIndex; i < endIndex && i < filteredRows.length; i++) {
                $(filteredRows[i]).show();
                // Update row number
                $(filteredRows[i]).find('.row-number').text(i + 1);
            }
        }
    }

    // Update pagination controls
    function updatePagination() {
        const totalPages = entriesPerPage === 'all' ? 1 : Math.ceil(filteredRows.length / entriesPerPage);
        const pagination = $('#pagination');
        pagination.empty();

        if (totalPages <= 1) {
            return;
        }

        // Previous button
        const prevDisabled = currentPage === 1 ? 'disabled' : '';
        pagination.append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                    <i class="bx bx-chevron-left"></i>
                </a>
            </li>
        `);

        // Page numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        // Adjust range if we're near the beginning or end
        if (endPage - startPage < 4) {
            if (startPage === 1) {
                endPage = Math.min(totalPages, startPage + 4);
            } else {
                startPage = Math.max(1, endPage - 4);
            }
        }

        // First page and ellipsis
        if (startPage > 1) {
            pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
            if (startPage > 2) {
                pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            }
        }

        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            const active = i === currentPage ? 'active' : '';
            pagination.append(`
                <li class="page-item ${active}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Last page and ellipsis
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            }
            pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
        }

        // Next button
        const nextDisabled = currentPage === totalPages ? 'disabled' : '';
        pagination.append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                    <i class="bx bx-chevron-right"></i>
                </a>
            </li>
        `);
    }

    // Update table info
    function updateTableInfo() {
        const totalFiltered = filteredRows.length;
        const totalAll = allRows.length;
        
        if (entriesPerPage === 'all') {
            $('#tableInfo').text(`Menampilkan 1 sampai ${totalFiltered} dari ${totalFiltered} entri`);
            $('#paginationInfo').text(`Halaman 1 dari 1`);
        } else {
            const startIndex = totalFiltered === 0 ? 0 : (currentPage - 1) * entriesPerPage + 1;
            const endIndex = Math.min(currentPage * entriesPerPage, totalFiltered);
            const totalPages = Math.ceil(totalFiltered / entriesPerPage);
            
            $('#tableInfo').text(`Menampilkan ${startIndex} sampai ${endIndex} dari ${totalFiltered} entri`);
            $('#paginationInfo').text(`Halaman ${currentPage} dari ${totalPages}`);
        }

        // Show/hide filtered info
        if (isFiltered && totalFiltered !== totalAll) {
            $('#filteredInfo').show();
        } else {
            $('#filteredInfo').hide();
        }
    }

    // Search functionality
    $('#searchInput').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        
        if (searchTerm === '') {
            // No search term, show all rows
            filteredRows = [...allRows];
            isFiltered = false;
        } else {
            // Filter rows based on search term
            filteredRows = allRows.filter(function(row) {
                const searchData = $(row).data('search');
                return searchData.includes(searchTerm);
            });
            isFiltered = true;
        }
        
        // Reset to first page
        currentPage = 1;
        
        // Update display
        displayPage();
        updatePagination();
        updateTableInfo();
    });

    // Entries per page change
    $('#entriesPerPage').on('change', function() {
        entriesPerPage = $(this).val() === 'all' ? 'all' : parseInt($(this).val());
        currentPage = 1; // Reset to first page
        
        displayPage();
        updatePagination();
        updateTableInfo();
    });

    // Pagination click handler
    $(document).on('click', '#pagination .page-link', function(e) {
        e.preventDefault();
        
        const page = parseInt($(this).data('page'));
        if (page && page !== currentPage) {
            currentPage = page;
            displayPage();
            updatePagination();
            updateTableInfo();
            
            // Scroll to top of table
            $('html, body').animate({
                scrollTop: $('#beritaAcaraTable').offset().top - 100
            }, 300);
        }
    });

    // Refresh button
    $('#refreshBtn').on('click', function() {
        $('#searchInput').val('');
        $('#entriesPerPage').val('25');
        entriesPerPage = 25;
        currentPage = 1;
        filteredRows = [...allRows];
        isFiltered = false;
        
        displayPage();
        updatePagination();
        updateTableInfo();
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Initialize the pagination system
    init();
});
</script>
@endsection