@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pelanggan Agen')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Daftar Pelanggan dibawah Agen {{auth()->user()->name}}</h5>
                <small class="card-subtitle">Daftar seluruh pelanggan yang terdaftar dibawah agen {{auth()->user()->name}}</small>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4" id="statistics-cards">
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="content-left">
                                <span class="text-heading">Total Pelanggan</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2" id="total-pelanggan">{{ $statistics['total_pelanggan'] ?? 0 }}</h4>
                                </div>
                                <small class="text-muted">Semua pelanggan</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-user fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="content-left">
                                <span class="text-heading">Pelanggan Aktif</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-success" id="pelanggan-aktif">{{ $statistics['pelanggan_aktif'] ?? 0 }}</h4>
                                </div>
                                <small class="text-muted">Status aktif</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-check-circle fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="content-left">
                                <span class="text-heading">Pelanggan Blokir</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-danger" id="pelanggan-blokir">{{ $statistics['pelanggan_blokir'] ?? 0 }}</h4>
                                </div>
                                <small class="text-muted">Status blokir</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="bx bx-block fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="content-left">
                                <span class="text-heading">Pelanggan Menunggu</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-warning" id="pelanggan-menunggu">{{ $statistics['pelanggan_menunggu'] ?? 0 }}</h4>
                                </div>
                                <small class="text-muted">Status menunggu</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-time fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Card -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pencarian</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="search-input" placeholder="Cari nama, alamat, atau no HP..." value="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter Status</label>
                        <select class="form-select" id="status-filter">
                            <option value="">Semua Status</option>
                            <option value="3">Aktif</option>
                            <option value="9">Blokir</option>
                            <option value="1">Menunggu</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-secondary w-100" id="reset-filters">
                            <i class="bx bx-refresh me-1"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>No. HP</th>
                                <th>Alamat</th>
                                <th>Paket</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-center" id="pelanggan-table-body">
                            @forelse ($pelanggan as $item)
                                <tr>
                                    <td>{{ $loop->iteration + ($pelanggan->currentPage() - 1) * $pelanggan->perPage() }}</td>
                                    <td class="text-start">{{ $item->nama_customer }}</td>
                                    <td>{{ $item->no_hp }}</td>
                                    <td class="text-start">{{ $item->alamat }}</td>
                                    <td>
                                        <span class="badge bg-warning bg-opacity-10 text-warning">
                                            {{ $item->paket->nama_paket ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->status_id == 3)
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="bx bx-check-circle me-1"></i>Aktif
                                            </span>
                                        @elseif($item->status_id == 1)
                                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                                <i class="bx bx-time me-1"></i>Menunggu
                                            </span>
                                        @elseif($item->status_id == 9)
                                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                                <i class="bx bx-block me-1"></i>Blokir
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                <i class="bx bx-question-mark me-1"></i>Unknown
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bx bx-search-alt-2 fs-1 text-muted mb-2"></i>
                                            <span class="text-muted">Tidak ada data pelanggan yang ditemukan</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($pelanggan->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Menampilkan {{ $pelanggan->firstItem() ?? 0 }} sampai {{ $pelanggan->lastItem() ?? 0 }}
                            dari {{ $pelanggan->total() }} data
                        </div>
                        <div id="pagination-container">
                            {{ $pelanggan->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.1); z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
@endsection
@section('page-script')
<script>
$(document).ready(function() {
    let searchTimeout;

    // Search functionality
    $('#search-input').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 500);
    });

    // Status filter functionality
    $('#status-filter').on('change', function() {
        performSearch();
    });

    // Reset filters
    $('#reset-filters').on('click', function() {
        $('#search-input').val('');
        $('#status-filter').val('');
        performSearch();
    });

    // ESC key to reset filters
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#search-input').val('');
            $('#status-filter').val('');
            performSearch();
        }
    });

    function performSearch() {
        const searchTerm = $('#search-input').val();
        const statusFilter = $('#status-filter').val();

        // Show loading
        $('#loading-overlay').removeClass('d-none');

        $.ajax({
            url: '{{ route("pelanggan-agen") }}',
            method: 'GET',
            data: {
                search: searchTerm,
                status: statusFilter
            },
            success: function(response) {
                if (response.success) {
                    // Update table content
                    updateTableContent(response.data);

                    // Update statistics
                    updateStatistics(response.statistics);

                    // Update pagination
                    updatePagination(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Search error:', error);
                showToast('Terjadi kesalahan saat mencari data', 'error');
            },
            complete: function() {
                // Hide loading
                $('#loading-overlay').addClass('d-none');
            }
        });
    }

    function updateTableContent(data) {
        let html = '';

        if (data.data && data.data.length > 0) {
            data.data.forEach(function(item, index) {
                const rowNumber = index + 1 + ((data.current_page - 1) * data.per_page);

                let statusBadge = '';
                if (item.status_id == 3) {
                    statusBadge = '<span class="badge bg-success bg-opacity-10 text-success"><i class="bx bx-check-circle me-1"></i>Aktif</span>';
                } else if (item.status_id == 1) {
                    statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning"><i class="bx bx-time me-1"></i>Menunggu</span>';
                } else if (item.status_id == 9) {
                    statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger"><i class="bx bx-block me-1"></i>Blokir</span>';
                } else {
                    statusBadge = '<span class="badge bg-secondary bg-opacity-10 text-secondary"><i class="bx bx-question-mark me-1"></i>Unknown</span>';
                }

                html += `
                    <tr>
                        <td>${rowNumber}</td>
                        <td class="text-start">${item.nama_customer}</td>
                        <td>${item.no_hp}</td>
                        <td class="text-start">${item.alamat}</td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                ${item.paket ? item.paket.nama_paket : 'N/A'}
                            </span>
                        </td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });
        } else {
            html = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt-2 fs-1 text-muted mb-2"></i>
                            <span class="text-muted">Tidak ada data pelanggan yang ditemukan</span>
                        </div>
                    </td>
                </tr>
            `;
        }

        $('#pelanggan-table-body').html(html);
    }

    function updateStatistics(stats) {
        $('#total-pelanggan').text(stats.total_pelanggan || 0);
        $('#pelanggan-aktif').text(stats.pelanggan_aktif || 0);
        $('#pelanggan-blokir').text(stats.pelanggan_blokir || 0);
        $('#pelanggan-menunggu').text(stats.pelanggan_menunggu || 0);
        $('#percentage-aktif').text('(' + (stats.percentage_aktif || 0) + '%)');
        $('#percentage-blokir').text('(' + (stats.percentage_blokir || 0) + '%)');
    }

    function updatePagination(data) {
        // Simple pagination info update
        if (data.total > 0) {
            const firstItem = ((data.current_page - 1) * data.per_page) + 1;
            const lastItem = Math.min(data.current_page * data.per_page, data.total);
            $('.text-muted').first().text(`Menampilkan ${firstItem} sampai ${lastItem} dari ${data.total} data`);
        } else {
            $('.text-muted').first().text('Menampilkan 0 sampai 0 dari 0 data');
        }
    }

    function showToast(message, type = 'success') {
        // Simple toast notification
        const toastClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const toast = $(`
            <div class="toast align-items-center text-white ${toastClass} border-0 position-fixed"
                 style="top: 20px; right: 20px; z-index: 9999;" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        // Remove toast after it's hidden
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});
</script>
@endsection