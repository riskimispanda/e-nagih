@extends('layouts.contentNavbarLayout')

@section('title', 'Data Invoice')

@section('styles')
    <style>
        /* Modern, clean design styles */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem 1.5rem 1rem;
        }

        .header-with-pattern {
            background-color: #ffffff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%233b82f6' fill-opacity='0.03'%3E%3Cpath opacity='.5' d='M96 95h4v1h-4v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9zm-1 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9z'/%3E%3Cpath d='M6 5V0H5v5H0v1h5v94h1V6h94V5H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            padding: 1.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h4 {
            font-weight: 600;
            color: #333;
            margin-bottom: 0;
            font-size: 1.25rem;
        }

        /* Header icon styling */
        .header-icon {
            width: 52px;
            height: 52px;
            background-color: rgba(59, 130, 246, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.1);
        }

        .header-content {
            transition: all 0.3s ease;
        }

        .header-content:hover .header-icon {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        /* Invoice summary styling */
        .invoice-summary {
            padding-right: 1.5rem;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }

        .summary-item {
            padding: 0.25rem 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Search input styling */
        .search-container {
            min-width: 280px;
        }

        .search-container .input-group {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }

        .search-container .input-group:focus-within {
            box-shadow: 0 3px 12px rgba(59, 130, 246, 0.12);
        }

        .search-container .input-group-text {
            border-color: #e2e8f0;
            color: #94a3b8;
        }

        .form-control {
            border: 1px solid #e2e8f0;
            box-shadow: none;
            transition: all 0.2s ease;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: none;
        }

        .btn-secondary {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .btn-secondary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .btn-outline-secondary {
            border-color: #e2e8f0;
            color: #64748b;
        }

        .btn-outline-secondary:hover {
            background-color: #f8fafc;
            color: #334155;
            border-color: #cbd5e1;
        }

        /* Table styling */
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.02);
            margin-bottom: 0;
        }

        .table thead {
            background-color: #f8fafc;
        }

        .table th {
            color: #475569;
            font-weight: 600;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 0.95rem;
        }

        .table-hover tbody tr {
            transition: all 0.15s ease;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Status badges */
        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.025em;
        }

        .badge.bg-label-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .badge.bg-label-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Action buttons */
        .btn-outline-primary {
            border-color: #3b82f6;
            color: #3b82f6;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background-color: #3b82f6;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(59, 130, 246, 0.2);
        }

        /* No results message */
        #noResults td,
        #serverNoResults td {
            padding: 2rem;
            color: #64748b;
            font-style: italic;
        }

        /* Avatar styling */
        .avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background-color: #f1f5f9;
            overflow: hidden;
        }

        .avatar-initial {
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Breadcrumb styling */
        .breadcrumb {
            padding: 0.5rem 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .breadcrumb-item a:hover {
            color: #3b82f6;
        }

        .breadcrumb-item.active {
            color: #3b82f6;
            font-weight: 500;
        }
    </style>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">

            <nav class="breadcrumb-nav">
                <ul class="breadcrumb breadcrumb-transparent breadcrumb-style2 mb-3">
                    <li class="breadcrumb-item"><a href="/customer">Home</a></li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">Invoice</li>
                </ul>
            </nav>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header header-with-pattern">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 py-2">
                        <div class="header-content">
                            <div class="d-flex align-items-center">
                                <div class="header-icon me-3 d-flex align-items-center justify-content-center">
                                    <i class='bx bx-receipt fs-3 text-primary'></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-semibold">Data Invoice Tagihan</h4>
                                    <p class="text-muted mb-0 small">Daftar tagihan bulanan pelanggan</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="invoice-summary me-4 d-none d-lg-block">
                                <div class="d-flex gap-4">
                                    <div class="summary-item">
                                        <span class="text-muted small d-block">Total Invoice</span>
                                        <span class="fw-semibold">{{ count($invoice) }}</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="text-muted small d-block">Belum Bayar</span>
                                        <span class="fw-semibold text-danger">
                                            {{ $invoice->where('status.nama_status', 'Belum Bayar')->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="search-container">
                                <form id="searchForm" method="GET" action="{{ url()->current() }}" class="d-flex">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class='bx bx-search text-muted'></i>
                                        </span>
                                        <input type="text" name="search" id="search"
                                            class="form-control border-start-0 ps-0" placeholder="Cari invoice..."
                                            aria-label="Search" value="{{ request()->get('search') }}" />
                                        @if (request()->has('search') && request()->get('search') != '')
                                            <a href="{{ url()->current() }}"
                                                class="btn btn-outline-secondary d-flex align-items-center">
                                                <i class='bx bx-x'></i>
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="5%">No.</th>
                                        <th width="25%">Nama Pelanggan</th>
                                        <th width="20%">Jatuh Tempo</th>
                                        <th width="20%">Tagihan</th>
                                        <th width="20%">Tagihan Tambahan</th>
                                        <th width="15%">Status</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($invoice) > 0)
                                        @foreach ($invoice as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ $item->customer->nama_customer }}
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class='bx bx-calendar text-muted me-2'></i>
                                                        {{ date('d-m-Y', strtotime($item->jatuh_tempo)) }}
                                                    </div>
                                                    <small
                                                        class="text-muted">{{ date('H:i', strtotime($item->jatuh_tempo)) }}
                                                        WIB</small>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">Rp {{ number_format($item->tagihan, 0) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">Rp {{ number_format($item->tambahan, 0) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-label-{{ $item->status->nama_status == 'Belum Bayar' ? 'danger' : 'success' }}">
                                                        {{ $item->status->nama_status }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="/payment/invoice/{{ $item->id }}"
                                                        class="btn btn-outline-primary btn-sm">
                                                        <i class="bx bx-credit-card me-1"></i> Bayar
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr id="serverNoResults">
                                            <td colspan="6" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class='bx bx-receipt text-muted mb-2' style="font-size: 2rem;"></i>
                                                    <div>Tidak ada data invoice ditemukan</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Client-side search functionality
            const searchInput = document.getElementById('search');
            const searchForm = document.getElementById('searchForm');
            const dataTable = document.getElementById('dataTable');
            const tableRows = dataTable.querySelectorAll('tbody tr');

            // Function to perform client-side search
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let resultsFound = false;

                // Check for server-side no results message
                const serverNoResults = document.getElementById('serverNoResults');
                if (serverNoResults) {
                    // If we're doing client-side search, hide the server-side message
                    serverNoResults.style.display = 'none';
                }

                // If search is empty, show all rows
                if (searchTerm === '') {
                    tableRows.forEach(row => {
                        if (row.id !== 'serverNoResults' && row.id !== 'noResults') {
                            row.style.display = '';
                        }
                    });

                    // Remove client-side no results message if it exists
                    const noResultsRow = document.getElementById('noResults');
                    if (noResultsRow) {
                        noResultsRow.remove();
                    }

                    return;
                }

                // Filter rows based on search term
                tableRows.forEach(row => {
                    // Skip the no results rows
                    if (row.id === 'serverNoResults' || row.id === 'noResults') {
                        return;
                    }

                    const cells = row.querySelectorAll('td');
                    if (cells.length >= 5) {
                        // Get all text content from the row for searching
                        const rowText = row.textContent.toLowerCase();

                        // Check if row contains the search term
                        if (rowText.includes(searchTerm)) {
                            row.style.display = '';
                            resultsFound = true;
                            // const highlightMatch = (element) => {
                            //     const text = element.textContent;
                            //     if (text.toLowerCase().includes(searchTerm)) {
                            //         const regex = new RegExp(`(${searchTerm})`, 'gi');
                            //         element.innerHTML = text.replace(regex, '<mark>$1</mark>');
                            //     }
                            // };
                            // row.querySelectorAll('div, span, small').forEach(highlightMatch);
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });

                // Add no results message if needed
                if (!resultsFound) {
                    if (!document.getElementById('noResults')) {
                        const tbody = dataTable.querySelector('tbody');
                        const noResultsRow = document.createElement('tr');
                        noResultsRow.id = 'noResults';
                        noResultsRow.innerHTML = `
                            <td colspan="6" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-search text-muted mb-2' style="font-size: 2rem;"></i>
                                    <div>Tidak ada invoice yang cocok dengan pencarian</div>
                                    <small class="text-muted mt-1">Coba dengan kata kunci lain</small>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(noResultsRow);
                    }
                } else {
                    const noResultsRow = document.getElementById('noResults');
                    if (noResultsRow) {
                        noResultsRow.remove();
                    }
                }
            }

            // Event listeners
            searchInput.addEventListener('keyup', function(e) {
                performSearch();

                // If Enter key is pressed, prevent form submission
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });

            // Handle form submission for server-side search
            searchForm.addEventListener('submit', function(e) {
                const searchTerm = searchInput.value.trim();

                // For empty searches, prevent server request and just show all rows
                if (searchTerm === '') {
                    e.preventDefault();
                    tableRows.forEach(row => {
                        row.style.display = '';
                    });

                    // Remove any no-results message
                    const noResultsRow = document.getElementById('noResults');
                    if (noResultsRow) {
                        noResultsRow.remove();
                    }
                }

                // For quick searches (less than 3 characters), use client-side filtering
                if (searchTerm.length > 0 && searchTerm.length < 3) {
                    e.preventDefault();
                    performSearch();
                }
            });
        });
    </script>
@endsection
