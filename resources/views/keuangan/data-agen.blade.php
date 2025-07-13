@extends('layouts.contentNavbarLayout')

@section('title', 'Data Agen')

@section('page-style')
<style>
.search-container {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
}

.modern-table {
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.modern-table thead th {
    background: #343a40;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.modern-table tbody tr {
    transition: all 0.3s ease;
}

.modern-table tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.input-group .btn {
    border-color: #ced4da;
}

.input-group .input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
    color: #6c757d;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.filter-active {
    border-color: #667eea !important;
    background-color: #f8f9ff !important;
}

#filterIndicator {
    animation: pulse 2s infinite;
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    white-space: nowrap;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* Responsive improvements */
@media (max-width: 576px) {
    .search-container {
        padding: 1rem;
    }

    .search-container h6 {
        font-size: 1rem;
    }

    .search-container h6 small {
        display: block;
        margin-top: 0.25rem;
    }

    #filterIndicator {
        font-size: 0.75rem;
        padding: 0.375rem 0.5rem;
    }

    .btn-sm {
        padding: 0.375rem 0.5rem;
        font-size: 0.75rem;
    }

    .search-info {
        font-size: 0.875rem;
    }
}

@media (max-width: 768px) {
    .modern-table {
        font-size: 0.875rem;
    }

    .modern-table th,
    .modern-table td {
        padding: 0.5rem 0.25rem;
    }
}

/* Button improvements */
#resetFilters {
    transition: all 0.3s ease;
    border-radius: 0.375rem;
}

#resetFilters:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#clearSearch {
    border-left: none;
}

#clearSearch:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

/* Search container improvements */
.search-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    position: relative;
    overflow: hidden;
}

.search-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

/* Flex improvements for better browser support */
.gap-2 {
    gap: 0.5rem;
}

/* Fallback for older browsers */
@supports not (gap: 0.5rem) {
    .gap-2 > * + * {
        margin-left: 0.5rem;
    }

    .flex-column.gap-2 > * + * {
        margin-left: 0;
        margin-top: 0.5rem;
    }
}

/* Additional responsive utilities */
.flex-shrink-0 {
    flex-shrink: 0;
}

/* Smooth transitions for all interactive elements */
.btn, .badge, .form-control, .input-group-text {
    transition: all 0.3s ease;
}

/* Focus improvements */
.form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    border-color: #667eea;
}

/* Keyboard key styling */
kbd {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    color: #495057;
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.125rem 0.375rem;
    text-transform: uppercase;
}

/* Empty state improvements */
.search-empty-state td {
    background-color: #f8f9fa;
    border: 2px dashed #dee2e6;
}

.search-empty-state .text-primary {
    font-weight: 600;
}

/* ESC hint improvements */
#escHint {
    transition: all 0.3s ease;
}

#escHint:hover {
    color: #667eea !important;
}

/* Better spacing for mobile */
@media (max-width: 575.98px) {
    .gap-2 {
        gap: 0.375rem;
    }

    @supports not (gap: 0.375rem) {
        .gap-2 > * + * {
            margin-left: 0.375rem;
        }

        .flex-column.gap-2 > * + * {
            margin-left: 0;
            margin-top: 0.375rem;
        }
    }
}
</style>
@endsection

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard" class="text-decoration-none">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="/corp/pendapatan" class="text-decoration-none">Langganan</a>
        </li>
        <li class="breadcrumb-item active">Data Agen</li>
    </ol>
</nav>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Data Agen atau Sales</h5>
                <small class="card-subtitle text-muted">Daftar Agen atau Sales</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="search-container">
                    <h6 class="mb-3 fw-bold text-dark">
                        <i class="bx bx-search me-2"></i>Filter & Pencarian Data
                        <small class="text-muted fw-normal">
                            ({{ $agen->total() }} total agen)
                        </small>
                    </h6>
                    <div class="row mb-3">
                        <div class="col-lg-8 col-md-7 mb-3 mb-md-0">
                            <label class="form-label">Nama Agen</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bx bx-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Cari nama, email, alamat, atau nomor HP..."
                                       id="searchAgen" title="Ketik untuk mencari berdasarkan nama, email, alamat, atau nomor HP">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-5">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <div class="d-flex justify-content-start justify-content-md-end align-items-center">
                                <span class="badge bg-info bg-opacity-10 text-info border border-info flex-shrink-0" id="filterIndicator" style="display: none;">
                                    <i class="bx bx-filter-alt me-1"></i>Filter Aktif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
                    <div class="search-info">
                        <span class="text-muted" id="searchResults">
                            <i class="bx bx-info-circle me-1"></i>
                            Menampilkan <span class="fw-bold text-primary" id="visibleCount">{{ $agen->count() }}</span>
                            dari <span class="fw-bold" id="totalCount">{{ $agen->count() }}</span> agen
                        </span>
                    </div>
                    <div class="search-actions d-flex align-items-center gap-2">
                        <small class="text-muted d-none d-sm-inline" id="escHint">
                            <i class="bx bx-keyboard me-1"></i>Tekan <kbd class="small">ESC</kbd> untuk reset
                        </small>
                    </div>
                </div>
                <hr class="my-2 mb-4">
                <div class="table-responsive">
                    <table class="table modern-table" id="agenTable">
                        <thead class="table-dark text-center fw-bold">
                            <tr>
                                <th>No</th>
                                <th>Nama Agen</th>
                                <th>Email Agen</th>
                                <th>Alamat Agen</th>
                                <th>No. HP Agen</th>
                                <th>Jumlah Pelanggan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($agen as $a)
                                <tr class="agen-row" data-id="{{ $a->id }}"
                                    data-nama="{{ strtolower($a->name) }}"
                                    data-email="{{ strtolower($a->email) }}"
                                    data-alamat="{{ strtolower($a->alamat ?? '') }}"
                                    data-hp="{{ $a->no_hp ?? '' }}">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="agen-name">{{ $a->name }}</td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger agen-email">
                                            {{ $a->email }}
                                        </span>
                                    </td>
                                    <td class="agen-alamat">{{ $a->alamat ?? '-' }}</td>
                                    <td class="agen-hp">{{ $a->no_hp ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ $a->customer_count }} Pelanggan
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/agen/pelanggan/{{ $a->id }}"
                                           class="btn btn-sm btn-outline-danger"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="bottom"
                                           title="Lihat Daftar Pelanggan">
                                            <i class="bx bx-list-ul"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-state-row">
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bx bx-user-x text-muted" style="font-size: 3rem;"></i>
                                            <h5 class="text-dark mt-3 mb-2">Tidak ada data agen</h5>
                                            <p class="text-muted mb-0">Belum ada agen yang terdaftar dalam sistem</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $agen->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchAgen');
    const clearButton = document.getElementById('clearSearch');
    const agenTable = document.getElementById('agenTable');
    const agenRows = agenTable.querySelectorAll('.agen-row');
    const filterIndicator = document.getElementById('filterIndicator');
    const escHint = document.getElementById('escHint');

    // Function to filter table rows
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();

        agenRows.forEach(function(row, index) {
            const nama = row.getAttribute('data-nama') || '';
            const email = row.getAttribute('data-email') || '';
            const alamat = row.getAttribute('data-alamat') || '';
            const hp = row.getAttribute('data-hp') || '';

            // Check search term match (nama, email, alamat, or hp)
            const matchesSearch = searchTerm === '' ||
                nama.includes(searchTerm) ||
                email.includes(searchTerm) ||
                alamat.includes(searchTerm) ||
                hp.includes(searchTerm);

            // Show/hide row based on search
            if (matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Update row numbers for visible rows
        updateRowNumbers();

        // Update search results counter
        updateSearchCounter();

        // Show/hide empty state
        toggleEmptyState();
    }

    // Function to update row numbers for visible rows
    function updateRowNumbers() {
        const visibleRows = Array.from(agenRows).filter(row => row.style.display !== 'none');
        visibleRows.forEach(function(row, index) {
            const numberCell = row.querySelector('td:first-child');
            if (numberCell) {
                numberCell.textContent = index + 1;
            }
        });
    }

    // Function to update search results counter
    function updateSearchCounter() {
        const visibleRows = Array.from(agenRows).filter(row => row.style.display !== 'none');
        const totalRows = agenRows.length;

        document.getElementById('visibleCount').textContent = visibleRows.length;
        document.getElementById('totalCount').textContent = totalRows;

        // Show/hide filter indicator with smooth animation
        const hasActiveFilters = searchInput.value.trim() !== '';

        if (hasActiveFilters) {
            filterIndicator.style.display = 'inline-block';
            searchInput.classList.add('filter-active');

            // Show ESC hint when filter is active
            if (escHint) {
                escHint.style.opacity = '1';
                escHint.style.visibility = 'visible';
            }

            // Add smooth fade in
            setTimeout(() => {
                filterIndicator.style.opacity = '1';
            }, 10);
        } else {
            // Add smooth fade out
            filterIndicator.style.opacity = '0';
            setTimeout(() => {
                filterIndicator.style.display = 'none';
            }, 300);
            searchInput.classList.remove('filter-active');

            // Hide ESC hint when no filter
            if (escHint) {
                escHint.style.opacity = '0.7';
                escHint.style.visibility = 'visible';
            }
        }

        // Update search results text based on screen size
        updateSearchResultsText(visibleRows.length, totalRows);
    }

    // Function to update search results text responsively
    function updateSearchResultsText(visible, total) {
        const searchResults = document.getElementById('searchResults');
        const isMobile = window.innerWidth < 576;

        if (isMobile) {
            searchResults.innerHTML = `
                <i class="bx bx-info-circle me-1"></i>
                <span class="fw-bold text-primary">${visible}</span>/<span class="fw-bold">${total}</span> agen
            `;
        } else {
            searchResults.innerHTML = `
                <i class="bx bx-info-circle me-1"></i>
                Menampilkan <span class="fw-bold text-primary">${visible}</span>
                dari <span class="fw-bold">${total}</span> agen
            `;
        }
    }

    // Function to show/hide empty state
    function toggleEmptyState() {
        const visibleRows = Array.from(agenRows).filter(row => row.style.display !== 'none');
        const tbody = agenTable.querySelector('tbody');
        let emptyRow = tbody.querySelector('.search-empty-state');
        const searchTerm = searchInput.value.trim();

        if (visibleRows.length === 0 && searchTerm !== '') {
            // Hide all agen rows
            agenRows.forEach(row => row.style.display = 'none');

            // Show search empty state if not exists
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.className = 'search-empty-state';
                emptyRow.innerHTML = `
                    <td colspan="7" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-dark mt-3 mb-2">Tidak ada hasil pencarian</h5>
                            <p class="text-muted mb-2">Tidak ditemukan agen yang sesuai dengan kata kunci "<strong class="text-primary">${searchTerm}</strong>"</p>
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                Coba gunakan kata kunci lain atau tekan <kbd class="small">ESC</kbd> untuk reset pencarian
                            </small>
                        </div>
                    </td>
                `;
                tbody.appendChild(emptyRow);
            } else {
                // Update existing empty state with new search term
                const strongElement = emptyRow.querySelector('strong');
                if (strongElement) {
                    strongElement.textContent = searchTerm;
                }
                emptyRow.style.display = '';
            }
        } else {
            // Hide search empty state
            if (emptyRow) {
                emptyRow.style.display = 'none';
            }
        }
    }

    // Function to reset all filters
    function resetAllFilters() {
        searchInput.value = '';
        filterTable();
        searchInput.focus();

        // Add visual feedback for reset action
        if (escHint) {
            escHint.style.color = '#28a745';
            escHint.innerHTML = '<i class="bx bx-check me-1"></i>Pencarian direset!';
            setTimeout(() => {
                escHint.style.color = '';
                escHint.innerHTML = '<i class="bx bx-keyboard me-1"></i>Tekan <kbd class="small">ESC</kbd> untuk reset';
            }, 2000);
        }
    }

    // Add event listeners
    searchInput.addEventListener('input', function() {
        filterTable();
    });

    clearButton.addEventListener('click', function() {
        searchInput.value = '';
        filterTable();
        searchInput.focus();
    });

    // Enhanced keyboard shortcuts
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Escape') {
            resetAllFilters();
        }
    });

    // Global ESC listener for better UX
    document.addEventListener('keyup', function(e) {
        if (e.key === 'Escape' && searchInput.value.trim() !== '') {
            resetAllFilters();
        }
    });

    // Initialize counter on page load
    updateSearchCounter();

    // Handle window resize for responsive text
    window.addEventListener('resize', function() {
        const visibleRows = Array.from(agenRows).filter(row => row.style.display !== 'none');
        updateSearchResultsText(visibleRows.length, agenRows.length);
    });

    // Add smooth transitions to filter indicator
    filterIndicator.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

    // Improve button interactions
    clearButton.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.05)';
    });

    clearButton.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });

    // Add transition to clear button
    clearButton.style.transition = 'all 0.3s ease';

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add loading state for better UX
    searchInput.addEventListener('focus', function() {
        this.parentElement.style.boxShadow = '0 0 0 0.2rem rgba(102, 126, 234, 0.25)';
    });

    searchInput.addEventListener('blur', function() {
        this.parentElement.style.boxShadow = 'none';
    });
});
</script>
@endsection