@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pelanggan Agen')

@section('page-style')
<style>
    .customer-deleted {
        background-color: #f8d7da !important;
    }
    .customer-deleted td {
        text-decoration: line-through;
        opacity: 0.7;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Daftar Pelanggan Anda</h5>
                <small class="card-subtitle">Daftar seluruh pelanggan (aktif & nonaktif) yang terdaftar di bawah Anda.</small>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label for="searchInput" class="form-label">Pencarian</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Cari nama, alamat, atau no. hp..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="button" id="resetBtn">
                                <i class="bx bx-reset"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted" id="data-info">
                        Menampilkan <span class="fw-bold text-primary" id="visibleCount">{{ $pelanggan->count() }}</span> dari <span class="fw-bold" id="totalCount">{{ $pelanggan->total() }}</span> data
                    </span>
                </div>
                <div class="table-responsive position-relative z-index-1">
                    <table class="table table-hover">
                        <thead class="text-center table-dark table-hover">
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th>Nama Pelanggan</th>
                                <th>No. HP</th>
                                <th>Alamat</th>
                                <th>Paket</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center" id="table-body">
                            @include('agen.partials.pelanggan-agen-rows', ['pelanggan' => $pelanggan])
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-4" id="pagination-container">
                    {{ $pelanggan->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('table-body');
    const paginationContainer = document.getElementById('pagination-container');
    const visibleCountEl = document.getElementById('visibleCount');
    const totalCountEl = document.getElementById('totalCount');
    const resetBtn = document.getElementById('resetBtn');

    function fetchData(page = 1, search = '') {
        const url = new URL("{{ route('pelanggan-agen') }}");
        url.searchParams.append('page', page);
        if (search) url.searchParams.append('search', search);

        // Show loading indicator
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>`;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update table content
            tableBody.innerHTML = data.table_html;

            // Update pagination
            paginationContainer.innerHTML = data.pagination_html;

            // Update counts
            visibleCountEl.textContent = data.visible_count;
            totalCountEl.textContent = data.total_count;

            // Re-initialize tooltips for new content
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-danger">Gagal memuat data. Silakan coba lagi.</td></tr>`;
        });
    }

    // Search input handler with debounce
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchData(1, searchInput.value);
        }, 500); // 500ms delay
    });

    // Pagination handler
    document.addEventListener('click', function (e) {
        if (e.target.matches('.pagination a')) {
            e.preventDefault();
            const page = new URL(e.target.href).searchParams.get('page');
            fetchData(page, searchInput.value);
        }
    });

    // Reset button handler
    resetBtn.addEventListener('click', function() {
        searchInput.value = '';
        fetchData(1, '');
    });
});
</script>
@endsection
