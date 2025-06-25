@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pelanggan')

@section('page-style')
<style>
    /* Header Styles */
    .modern-card-header {
        padding: 1.25rem 1.25rem 0.75rem;
        background-color: #ffffff;
        border-bottom: none;
        position: relative;
    }
    
    .card {
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        transition: all 0.2s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    
    .card-header {
        background-color: #ffffff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1rem;
    }
    
    .header-title {
        font-size: 1.2rem;
        color: #2c3e50;
        margin-bottom: 1rem;
        position: relative;
        padding-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .header-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        height: 2px;
        width: 40px;
        background-color: #5e72e4;
        border-radius: 2px;
    }
    
    /* Data Card Styles */
    .data-card {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
        padding: 0.85rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        border-left: 3px solid transparent;
    }
    
    .data-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    
    .data-card.primary-card {
        border-left-color: #5e72e4;
    }
    
    .data-card.success-card {
        border-left-color: #2dce89;
    }
    
    .data-card.danger-card {
        border-left-color: #f5365c;
    }
    
    .data-card.secondary-card {
        border-left-color: #8898aa;
    }
    
    .data-card-icon {
        font-size: 1.25rem;
        margin-right: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        position: relative;
        transition: all 0.2s ease;
    }
    
    .data-card:hover .data-card-icon {
        transform: scale(1.05);
    }
    
    .data-card-icon.primary {
        background-color: rgba(94, 114, 228, 0.1);
        color: #5e72e4;
    }
    
    .data-card-icon.success {
        background-color: rgba(45, 206, 137, 0.1);
        color: #2dce89;
    }
    
    .data-card-icon.danger {
        background-color: rgba(245, 54, 92, 0.1);
        color: #f5365c;
    }
    
    .data-card-icon.secondary {
        background-color: rgba(136, 152, 170, 0.1);
        color: #8898aa;
    }
    
    .data-card-content {
        flex: 1;
    }
    
    .data-label {
        font-size: 0.7rem;
        color: #8898aa;
        margin-bottom: 0.2rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .data-value {
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.2;
    }
    
    .data-value .badge {
        font-size: 0.75rem;
        padding: 0.3rem 0.6rem;
        border-radius: 4px;
    }
    
    /* Search & Sort Container Styles */
    .search-container {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    
    .search-container:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    
    .search-sort-wrapper {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .search-group {
        flex: 1;
        min-width: 250px;
    }
    
    .sort-group {
        flex: 0 0 auto;
        min-width: 180px;
    }
    
    .search-input {
        border-radius: 4px 0 0 4px;
        border: 1px solid #e9ecef;
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    
    .search-input:focus {
        box-shadow: 0 0 0 0.15rem rgba(94, 114, 228, 0.1);
        border-color: #5e72e4;
        outline: none;
    }
    
    .search-button {
        border-radius: 0 4px 4px 0;
        background-color: #5e72e4;
        border: none;
        color: white;
        padding: 0.5rem 0.75rem;
        transition: all 0.2s ease;
    }
    
    .search-button:hover {
        background-color: #4a5cd0;
    }
    
    .sort-label {
        font-size: 0.75rem;
        color: #8898aa;
        font-weight: 500;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .sort-select {
        border-radius: 4px;
        border: 1px solid #e9ecef;
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        background-color: #ffffff;
        color: #495057;
        width: 100%;
    }
    
    .sort-select:focus {
        box-shadow: 0 0 0 0.15rem rgba(94, 114, 228, 0.1);
        border-color: #5e72e4;
        outline: none;
    }
    
    .sort-select option {
        padding: 0.5rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .search-sort-wrapper {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .search-group,
        .sort-group {
            width: 100%;
            min-width: unset;
        }
    }
    
    @media (max-width: 576px) {
        .search-container {
            padding: 0.75rem;
        }
        
        .search-input,
        .sort-select {
            font-size: 0.8rem;
            padding: 0.45rem 0.65rem;
        }
    }
    
    .divider {
        height: 1px;
        background-color: rgba(0, 0, 0, 0.05);
        margin: 1rem 0;
        border: none;
    }
    
    /* Modern Table Styles */
    .modern-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
    
    .modern-table thead {
        background-color: #f8f9fa;
    }
    
    .modern-table th {
        padding: 1rem;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .modern-table td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f1f1;
        color: #495057;
        font-size: 0.9rem;
    }
    
    .modern-table tbody tr {
        transition: background-color 0.2s;
    }
    
    .modern-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .modern-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    /* Action Buttons */
    .action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        margin: 0 3px;
        transition: all 0.2s;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
    }
    
    .action-btn i {
        font-size: 1rem;
    }
    
    /* Status Badges */
    .status-badge {
        padding: 0.4rem 0.8rem;
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    
    /* No Results Message */
    .no-results {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
        font-style: italic;
        display: none;
    }
    
    /* Pagination Styles */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        padding: 1rem;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
    }
    
    .pagination-info {
        font-size: 0.875rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .pagination-btn {
        padding: 0.5rem 0.75rem;
        border: 1px solid #e9ecef;
        background-color: #ffffff;
        color: #495057;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        min-width: 36px;
        text-align: center;
    }
    
    .pagination-btn:hover:not(:disabled) {
        background-color: #5e72e4;
        color: white;
        border-color: #5e72e4;
    }
    
    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .pagination-btn.active {
        background-color: #5e72e4;
        color: white;
        border-color: #5e72e4;
    }
    
    .page-size-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .page-size-select {
        padding: 0.375rem 0.5rem;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        font-size: 0.875rem;
        background-color: #ffffff;
        color: #495057;
    }
    
    .page-size-select:focus {
        outline: none;
        border-color: #5e72e4;
        box-shadow: 0 0 0 0.15rem rgba(94, 114, 228, 0.1);
    }
    
    .data-count-info {
        background-color: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        color: #495057;
        border-left: 3px solid #5e72e4;
    }
    
    .count-highlight {
        font-weight: 600;
        color: #5e72e4;
    }
    
    /* Responsive pagination */
    @media (max-width: 768px) {
        .pagination-container {
            flex-direction: column;
            gap: 1rem;
        }
        
        .pagination-info {
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
        }
        
        .pagination-controls {
            flex-wrap: wrap;
            justify-content: center;
        }
    }
    
    /* Payment Confirmation Modal Styles - Minimalist Version */
    .payment-modal .modal-dialog {
        max-width: 480px;
        margin: 1.75rem auto;
    }
    
    @media (max-width: 576px) {
        .payment-modal .modal-dialog {
            max-width: 95%;
            margin: 0.5rem auto;
        }
    }
    
    .payment-modal .modal-content {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        background-color: #ffffff;
    }
    
    .payment-modal .modal-header {
        background-color: #5e72e4;
        border-bottom: none;
        padding: 1rem 1.25rem;
        position: relative;
    }
    
    .payment-modal .modal-title {
        font-size: 1rem;
        font-weight: 600;
        color: white;
        display: flex;
        align-items: center;
    }
    
    .payment-modal .modal-title i {
        margin-right: 0.5rem;
        font-size: 1.1rem;
    }
    
    .payment-modal .modal-body {
        padding: 1.25rem;
    }
    
    .payment-modal .modal-footer {
        border-top: 1px solid #f1f1f1;
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    
    @media (max-width: 576px) {
        .payment-modal .modal-footer {
            justify-content: space-between;
        }
        
        .payment-modal .btn-cancel,
        .payment-modal .btn-confirm {
            flex: 1;
        }
    }
    
    .payment-modal .close-btn {
        background: none;
        border: none;
        color: white;
        font-size: 1.1rem;
        line-height: 1;
        padding: 4px;
        margin: 0;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    
    .payment-modal .close-btn:hover {
        opacity: 1;
    }
    
    .payment-modal .customer-info {
        border-left: 2px solid #5e72e4;
        padding: 0.5rem 0 0.5rem 0.75rem;
        margin-bottom: 1.25rem;
    }
    
    .payment-modal .info-item {
        margin-bottom: 0.5rem;
        display: flex;
        flex-wrap: wrap;
    }
    
    .payment-modal .info-item:last-child {
        margin-bottom: 0;
    }
    
    .payment-modal .info-label {
        font-size: 0.75rem;
        color: #8898aa;
        font-weight: 500;
        width: 120px;
        flex-shrink: 0;
    }
    
    @media (max-width: 576px) {
        .payment-modal .info-label {
            width: 100%;
            margin-bottom: 0.125rem;
        }
        
        .payment-modal .info-value {
            width: 100%;
            padding-left: 0.25rem;
        }
    }
    
    .payment-modal .info-value {
        font-size: 0.875rem;
        color: #2c3e50;
        font-weight: 600;
        flex-grow: 1;
    }
    
    .payment-modal .form-label {
        font-size: 0.8rem;
        color: #495057;
        font-weight: 500;
        margin-bottom: 0.375rem;
    }
    
    .payment-modal .form-control {
        border-radius: 4px;
        border: 1px solid #e9ecef;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: border-color 0.2s ease;
    }
    
    .payment-modal .form-control:focus {
        border-color: #5e72e4;
        box-shadow: none;
    }
    
    .payment-modal .form-select {
        border-radius: 4px;
        border: 1px solid #e9ecef;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: border-color 0.2s ease;
    }
    
    .payment-modal .form-select:focus {
        border-color: #5e72e4;
        box-shadow: none;
    }
    
    .payment-modal .btn-confirm {
        background-color: #5e72e4;
        border: none;
        border-radius: 4px;
        padding: 0.5rem 1rem;
        font-weight: 500;
        font-size: 0.875rem;
        transition: background-color 0.2s;
        color: white;
    }
    
    .payment-modal .btn-confirm:hover {
        background-color: #4a5cd0;
    }
    
    .payment-modal .btn-cancel {
        background-color: transparent;
        border: 1px solid #e9ecef;
        color: #495057;
        border-radius: 4px;
        padding: 0.5rem 1rem;
        font-weight: 500;
        font-size: 0.875rem;
        transition: background-color 0.2s;
    }
    
    .payment-modal .btn-cancel:hover {
        background-color: #f8f9fa;
    }
    
    .payment-modal .payment-amount {
        font-size: 1.5rem;
        font-weight: 600;
        color: #5e72e4;
        text-align: center;
        margin: 1rem 0;
    }
    
    .payment-modal .payment-amount .currency {
        font-size: 1rem;
        margin-right: 0.25rem;
        font-weight: 500;
    }
    
    .payment-modal .payment-amount.no-invoice {
        color: #f5365c !important;
        font-size: 1.2rem;
    }
    
    .payment-modal .payment-amount.no-invoice::after {
        content: " (Tidak ada tagihan)";
        font-size: 0.8rem;
        color: #8898aa;
        font-weight: 400;
    }
    
    .payment-modal .payment-details {
        margin-top: 1.25rem;
    }
    
    .payment-modal .payment-method-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 0.375rem;
    }
    
    .payment-modal .payment-method-option {
        display: flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        cursor: pointer;
        transition: border-color 0.2s;
        flex: 1;
        min-width: 120px;
    }
    
    .payment-modal .payment-method-option:hover {
        border-color: #5e72e4;
    }
    
    .payment-modal .payment-method-option.selected {
        border-color: #5e72e4;
        background-color: rgba(94, 114, 228, 0.05);
    }
    
    .payment-modal .payment-method-icon {
        color: #5e72e4;
        margin-right: 0.5rem;
        font-size: 1rem;
    }
    
    .payment-modal .payment-method-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #495057;
    }
</style>
@endsection

@section('content')
<div class="row">
    <!-- Card with data summary -->
    <div class="col-sm-12 mb-4">
        <div class="card">
            <div class="card-header modern-card-header">
                <h4 class="header-title">Data Global</h4>
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="data-card bg-white primary-card">
                            <div class="data-card-icon primary">
                                <i class="bx bx-user"></i>
                            </div>
                            <div class="data-card-content">
                                <div class="data-label">Jumlah Pelanggan</div>
                                <div class="data-value">
                                    <span class="badge bg-primary rounded-pill">
                                        {{ count($data) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="data-card bg-white success-card">
                            <div class="data-card-icon success">
                                <i class="bx bx-check-circle"></i>
                            </div>
                            <div class="data-card-content">
                                <div class="data-label">Pelanggan Aktif</div>
                                <div class="data-value">
                                    <span class="badge bg-success rounded-pill">
                                        @php
                                        $aktif = 0;
                                        foreach ($data as $item) {
                                            if ($item->status_id == 3) {
                                                $aktif++;
                                            }
                                        }
                                        echo $aktif;
                                        @endphp
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="data-card bg-white danger-card">
                            <div class="data-card-icon danger">
                                <i class="bx bx-x-circle"></i>
                            </div>
                            <div class="data-card-content">
                                <div class="data-label">Pelanggan Non-Aktif</div>
                                <div class="data-value">
                                    <span class="badge bg-danger rounded-pill">
                                        @php
                                        $nonaktif = 0;
                                        foreach ($data as $item) {
                                            if ($item->status_id == 9) {
                                                $nonaktif++;
                                            }
                                        }
                                        echo $nonaktif;
                                        @endphp
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (auth()->user()->roles_id == 1)
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="data-card bg-white primary-card">
                            <div class="data-card-icon primary">
                                <i class="bx bx-money"></i>
                            </div>
                            <a href="/payment/approve">
                                <div class="data-card-content">
                                    <div class="data-label">Konfirmasi Pembayaran</div>
                                    <div class="data-value">
                                        <span class="badge bg-primary rounded-pill">
                                            {{ count($pembayaran) }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card with search and table -->
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header modern-card-header mb-5">
                <h4 class="header-title">Daftar Pelanggan</h4>
                
                <!-- Success/Error Messages -->
                @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                
                @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                
                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="search-container">
                            <div class="search-sort-wrapper">
                                <div class="search-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control search-input"
                                        placeholder="Cari pelanggan..." id="searchCustomer">
                                        <button class="btn search-button" type="button" id="searchButton">
                                            <i class="bx bx-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="sort-group col-sm-12">
                            <div class="sort-label">Urutkan Berdasarkan</div>
                            <select class="sort-select" id="sortSelect">
                                <option value="default">Default</option>
                                <option value="name-asc">Nama A-Z</option>
                                <option value="name-desc">Nama Z-A</option>
                                <option value="status-active">Status Aktif</option>
                                <option value="status-inactive">Status Non-Aktif</option>
                                <option value="package">Paket</option>
                                <option value="payment-paid">Sudah Bayar</option>
                                <option value="payment-unpaid">Belum Bayar</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- WebSocket Connection Status -->
                <div class="row mb-3" style="display: none">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Status Koneksi WebSocket</h5>
                                <div id="status" class="alert alert-info">Menghubungkan ke server WebSocket...</div>
                                <div id="messages" class="small text-muted"
                                style="max-height: 150px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive mb-2">
                    <table class="table modern-table" id="customerTable">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>Telp.</th>
                                <th hidden>BTS Server</th>
                                <th>Paket</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                            <tr class="customer-row text-center" data-id="{{ $item->id }}"
                                data-tagihan="{{ $item->invoice->isNotEmpty() && $item->invoice->first()->status ? ($item->invoice->first()->status->nama_status == 'Sudah Bayar' ? '0' : $item->invoice->first()->tagihan ?? '0') : '0' }}"
                                data-customer-id="{{ $item->id }}"
                                data-invoice-id="{{ $item->invoice->isNotEmpty() ? $item->invoice->first()->id : '' }}"
                                data-tagihan-tambahan="{{ $item->invoice->isNotEmpty() ? $item->invoice->first()->tambahan : '' }}">
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="customer-name">{{ $item->nama_customer }}</td>
                                <td class="customer-address">{{ $item->alamat }}</td>
                                <td class="nomor-hp">{{ $item->no_hp }}</td>
                                <td hidden>{{ $item->getServer->lokasi_server ?? 'Menunggu' }}</td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 status-badge text-warning">
                                        {{ $item->paket->nama_paket }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($item->status_id == 3)
                                    <span class="badge bg-success bg-opacity-10 text-success status-badge">
                                        Aktif
                                    </span>
                                    @elseif($item->status_id == 9)
                                    <span class="badge bg-danger status-badge bg-opacity-10 text-danger"
                                    style="text-transform: uppercase">
                                    Isolire Billing
                                </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if (auth()->user()->roles_id == 1 || auth()->user()->roles_id == 2)
                                <a href="#" class="btn btn-success action-btn add-customer-btn mb-2"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Konfirmasi Pembayaran">
                                <i class="bx bx-money"></i>
                            </a>
                            <a href="/blokir/{{ $item->id }}"
                                class="btn btn-danger action-btn blokir-customer-btn mb-2"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Blokir Pelanggan">
                                <i class="bx bx-block"></i>
                            </a>
                            <a href="/unblokir/{{ $item->id }}"
                                class="btn btn-warning action-btn unblokir-customer-btn mb-2"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Aktifkan Pelanggan">
                                <i class="bx bx-check-circle"></i>
                            </a>
                            @endif
                            <a href="/detail-pelanggan/{{ $item->id }}"
                                class="btn btn-info action-btn detail-customer-btn mb-2"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Detail Pelanggan">
                                <i class="bx bx-show"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if(empty($item))
            <div class="d-flex flex-column align-items-center justify-content-center py-5" id="noDataMessage">
                <div class="mb-3">
                    <i class="bx bx-user-x" style="font-size: 4rem; color: #8898aa;"></i>
                </div>
                <h5 class="text-muted mb-2">Belum Ada Pelanggan</h5>
                <p class="text-muted">Saat ini belum ada data pelanggan yang terdaftar.</p>
            </div>
            @endif
            <div class="no-results" id="noResults">
                <p>Tidak ada data pelanggan yang sesuai dengan pencarian.</p>
            </div>
        </div>
        
        <!-- Pagination Container -->
        <div class="pagination-container" id="paginationContainer">
            <div class="pagination-info">
                <div class="data-count-info">
                    Menampilkan <span class="count-highlight" id="showingStart">1</span> -
                    <span class="count-highlight" id="showingEnd">10</span> dari
                    <span class="count-highlight" id="totalRecords">{{ count($data) }}</span> data
                </div>
                <div class="page-size-selector">
                    <label for="pageSize">Data per halaman:</label>
                    <select id="pageSize" class="page-size-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            <div class="pagination-controls" id="paginationControls">
                <!-- Pagination buttons will be generated by JavaScript -->
            </div>
        </div>
    </div>
</div>
</div>

</div>

<!-- Payment Confirmation Modal - Minimalist Version -->
<div class="modal fade payment-modal" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel"
aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="paymentModalLabel">
                <i class="bx bx-money"></i>Pembayaran
            </h5>
            <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close"
            style="margin-left: auto;">
            <i class="bx bx-x"></i>
        </button>
    </div>
    <div class="modal-body">
        <form id="paymentForm" enctype="multipart/form-data" method="post"
        action="/konfirmasi/pembayaran/{{ $item->id ?? '' }}">
        @csrf
        @if($item->id ?? '')
        <input type="hidden" name="invoice_id" value="{{ $item->invoice->isNotEmpty() ? $item->invoice->first()->id : '' }}">
        @endif
        <!-- Customer Information -->
        <div class="customer-info">
            <div class="info-item">
                <div class="info-label">Nama</div>
                <div class="info-value" id="customerName">-</div>
            </div>
            <div class="info-item">
                <div class="info-label">Paket</div>
                <div class="info-value" id="customerPackage">-</div>
            </div>
            <div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value" id="billStatus">-</div>
            </div>
        </div>
        <hr>
        <!-- Payment Amount -->
        <div class="payment-amount">
            <span class="currency">Rp</span><span id="paymentAmount"></span>
        </div>
        <hr>
        <!-- Payment Details -->
        <div class="payment-details">
            <div class="mb-3">
                <label for="paymentDate" class="form-label">Tanggal Pembayaran</label>
                <input type="date" class="form-control" id="paymentDate" name="paymentDate" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Metode Pembayaran</label>
                <select class="form-select" id="paymentMethodSelect" name="paymentMethodSelect">
                    <option selected disabled>Pilih Metode Pembayaran</option>
                    @foreach ($metode as $item)
                    <option value="{{ $item->id }}">{{ $item->nama_metode }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-3" id="transferDetails" style="display: none;">
                <label for="transferProof" class="form-label">Bukti Pembayaran</label>
                <input type="file" class="form-control" id="transferProof" name="transferProof"
                accept="image/*,.pdf">
                <small class="form-text text-muted">Format: JPG, PNG, PDF (Maks. 5MB)</small>
            </div>
            
            <div class="mb-3">
                <label for="paymentNotes" class="form-label">Catatan</label>
                <textarea class="form-control" id="paymentNotes" name="paymentNotes" rows="2"
                placeholder="Tambahkan catatan jika diperlukan"></textarea>
            </div>
        </div>
        <div class="modal-footer mt-5 gap-3">
            <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-warning btn-sm">Konfirmasi</button>
        </div>
    </form>
</div>
</div>
</div>
</div>
@endsection


@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM Elements
        const searchInput = document.getElementById('searchCustomer');
        const searchButton = document.getElementById('searchButton');
        const sortSelect = document.getElementById('sortSelect');
        const customerTable = document.getElementById('customerTable');
        const tableBody = customerTable.querySelector('tbody');
        const noResultsMessage = document.getElementById('noResults');
        const paginationContainer = document.getElementById('paginationContainer');
        const paginationControls = document.getElementById('paginationControls');
        const pageSizeSelect = document.getElementById('pageSize');
        const showingStart = document.getElementById('showingStart');
        const showingEnd = document.getElementById('showingEnd');
        const totalRecords = document.getElementById('totalRecords');
        const noDataMessage = document.getElementById('noDataMessage');
        
        // Payment Modal Elements
        const paymentButtons = document.querySelectorAll('.add-customer-btn');
        const paymentModalEl = document.getElementById('paymentModal');
        const paymentModal = new bootstrap.Modal(paymentModalEl);
        const paymentMethodSelect = document.getElementById('paymentMethodSelect');
        const transferDetails = document.getElementById('transferDetails');
        const paymentDateInput = document.getElementById('paymentDate');
        
        // Set default payment date to today
        const today = new Date();
        const formattedDate = today.toISOString().substr(0, 10);
        paymentDateInput.value = formattedDate;
        
        // Pagination and Data Management
        let allCustomerData = [];
        let filteredData = [];
        let currentPage = 1;
        let pageSize = 10;
        
        // Initialize customer data from DOM
        function initializeCustomerData() {
            const rows = document.querySelectorAll('.customer-row');
            allCustomerData = Array.from(rows).map((row, index) => {
                return {
                    element: row.cloneNode(true),
                    originalIndex: index + 1,
                    id: row.getAttribute('data-id'),
                    name: row.querySelector('.customer-name').textContent.toLowerCase(),
                    address: row.querySelector('.customer-address').textContent.toLowerCase(),
                    phone: row.querySelector('td:nth-child(4)').textContent,
                    package: row.querySelector('td:nth-child(6)').textContent.trim(),
                    status: row.querySelector('td:nth-child(7)').textContent.trim(),
                    payment: row.querySelector('td:nth-child(8)').textContent.trim(),
                    tagihan: row.getAttribute('data-tagihan'),
                    customerId: row.getAttribute('data-customer-id'),
                    invoiceId: row.getAttribute('data-invoice-id'),
                    tagihanTambahan: row.getAttribute('data-tagihan-tambahan')
                };
            });
            filteredData = [...allCustomerData];
            updateDisplay();
        }
        
        // Search functionality
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                filteredData = [...allCustomerData];
            } else {
                filteredData = allCustomerData.filter(customer => {
                    return customer.name.includes(searchTerm) ||
                    customer.address.includes(searchTerm) ||
                    customer.phone.toLowerCase().includes(searchTerm);
                });
            }
            
            currentPage = 1; // Reset to first page after search
            updateDisplay();
        }
        
        // Sort functionality
        function performSort() {
            const sortValue = sortSelect.value;
            
            if (sortValue === 'default') {
                filteredData.sort((a, b) => a.originalIndex - b.originalIndex);
            } else if (sortValue === 'name-asc') {
                filteredData.sort((a, b) => a.name.localeCompare(b.name));
            } else if (sortValue === 'name-desc') {
                filteredData.sort((a, b) => b.name.localeCompare(a.name));
            } else if (sortValue === 'status-active') {
                filteredData.sort((a, b) => {
                    const isActiveA = a.status.includes('Aktif') ? 1 : 0;
                    const isActiveB = b.status.includes('Aktif') ? 1 : 0;
                    return isActiveB - isActiveA;
                });
            } else if (sortValue === 'status-inactive') {
                filteredData.sort((a, b) => {
                    const isActiveA = a.status.includes('Aktif') ? 1 : 0;
                    const isActiveB = b.status.includes('Aktif') ? 1 : 0;
                    return isActiveA - isActiveB;
                });
            } else if (sortValue === 'package') {
                filteredData.sort((a, b) => a.package.localeCompare(b.package));
            } else if (sortValue === 'payment-paid') {
                filteredData.sort((a, b) => {
                    const isPaidA = a.payment.includes('Sudah Bayar') ? 1 : 0;
                    const isPaidB = b.payment.includes('Sudah Bayar') ? 1 : 0;
                    return isPaidB - isPaidA;
                });
            } else if (sortValue === 'payment-unpaid') {
                filteredData.sort((a, b) => {
                    const isPaidA = a.payment.includes('Sudah Bayar') ? 1 : 0;
                    const isPaidB = b.payment.includes('Sudah Bayar') ? 1 : 0;
                    return isPaidA - isPaidB;
                });
            }
            
            updateDisplay();
        }
        
        // Pagination functions
        function updateDisplay() {
            const totalItems = filteredData.length;
            const totalPages = Math.ceil(totalItems / pageSize);
            const startIndex = (currentPage - 1) * pageSize;
            const endIndex = Math.min(startIndex + pageSize, totalItems);
            
            // Clear table body
            tableBody.innerHTML = '';
            
            if (totalItems === 0 && searchInput.value.trim() !== '') {
                // Show no results message only when searching
                noResultsMessage.style.display = 'block';
                customerTable.style.display = 'none';
                paginationContainer.style.display = 'none';
                return;
            } else {
                noResultsMessage.style.display = 'none';
                customerTable.style.display = '';
                paginationContainer.style.display = 'flex';
            }
            
            // Display current page data
            for (let i = startIndex; i < endIndex; i++) {
                const customer = filteredData[i];
                const row = customer.element.cloneNode(true);
                
                // Update row number
                const numberCell = row.querySelector('td:first-child');
                if (numberCell) {
                    numberCell.textContent = i + 1;
                }
                
                tableBody.appendChild(row);
            }
            
            // Update pagination info
            showingStart.textContent = totalItems > 0 ? startIndex + 1 : 0;
            showingEnd.textContent = endIndex;
            totalRecords.textContent = totalItems;
            
            // Update pagination controls
            updatePaginationControls(totalPages);
            
            // Re-attach event listeners for payment buttons
            attachPaymentButtonListeners();
        }
        
        // Update pagination controls
        function updatePaginationControls(totalPages) {
            paginationControls.innerHTML = '';
            
            if (totalPages <= 1) return;
            
            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = 'pagination-btn';
            prevBtn.innerHTML = '<i class="bx bx-chevron-left"></i>';
            prevBtn.disabled = currentPage === 1;
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updateDisplay();
                }
            });
            paginationControls.appendChild(prevBtn);
            
            // Page numbers
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            // First page
            if (startPage > 1) {
                const firstBtn = document.createElement('button');
                firstBtn.className = 'pagination-btn';
                firstBtn.textContent = '1';
                firstBtn.addEventListener('click', () => {
                    currentPage = 1;
                    updateDisplay();
                });
                paginationControls.appendChild(firstBtn);
                
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-btn';
                    ellipsis.textContent = '...';
                    ellipsis.style.cursor = 'default';
                    paginationControls.appendChild(ellipsis);
                }
            }
            
            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = 'pagination-btn';
                if (i === currentPage) {
                    pageBtn.classList.add('active');
                }
                pageBtn.textContent = i;
                pageBtn.addEventListener('click', () => {
                    currentPage = i;
                    updateDisplay();
                });
                paginationControls.appendChild(pageBtn);
            }
            
            // Last page
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-btn';
                    ellipsis.textContent = '...';
                    ellipsis.style.cursor = 'default';
                    paginationControls.appendChild(ellipsis);
                }
                
                const lastBtn = document.createElement('button');
                lastBtn.className = 'pagination-btn';
                lastBtn.textContent = totalPages;
                lastBtn.addEventListener('click', () => {
                    currentPage = totalPages;
                    updateDisplay();
                });
                paginationControls.appendChild(lastBtn);
            }
            
            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.className = 'pagination-btn';
            nextBtn.innerHTML = '<i class="bx bx-chevron-right"></i>';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    updateDisplay();
                }
            });
            paginationControls.appendChild(nextBtn);
        }
        
        // Event Listeners
        searchButton.addEventListener('click', performSearch);
        searchInput.addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                performSearch();
            }
            if (this.value === '') {
                performSearch();
            }
        });
        
        // Real-time search with debounce
        searchInput.addEventListener('input', function() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(performSearch, 300);
        });
        
        // Sort dropdown change
        sortSelect.addEventListener('change', performSort);
        
        // Page size change
        pageSizeSelect.addEventListener('change', function() {
            pageSize = parseInt(this.value);
            currentPage = 1;
            updateDisplay();
        });
        
        // Payment Modal Functionality
        function attachPaymentButtonListeners() {
            const currentPaymentButtons = document.querySelectorAll('.add-customer-btn');
            currentPaymentButtons.forEach(button => {
                // Remove existing listeners to prevent duplicates
                button.replaceWith(button.cloneNode(true));
            });
            
            // Re-select buttons after cloning
            const newPaymentButtons = document.querySelectorAll('.add-customer-btn');
            newPaymentButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get customer data from the row
                    const row = this.closest('tr');
                    const customerName = row.querySelector('.customer-name').textContent;
                    const customerPackage = row.querySelector('td:nth-child(6)').textContent;
                    const billStatus = row.querySelector('td:nth-child(8) .badge').textContent.trim();
                    
                    // Set customer data in the modal
                    document.getElementById('customerName').textContent = customerName;
                    document.getElementById('customerPackage').textContent = customerPackage;
                    document.getElementById('billStatus').textContent = billStatus;
                    
                    // Reset form state
                    document.getElementById('paymentForm').reset();
                    paymentDateInput.value = formattedDate;
                    paymentMethodSelect.value = 'Pilih Metode Pembayaran';
                    transferDetails.style.display = 'none';
                    
                    // Set payment amount from invoice data
                    const lain = row.getAttribute('data-tagihan-tambahan') || '0';
                    const a = row.getAttribute('data-tagihan') || '0';
                    const amount = parseInt(a) + parseInt(lain);
                    const customerId = row.getAttribute('data-customer-id');
                    const invoiceId = row.getAttribute('data-invoice-id');
                    
                    // Validate and format the amount
                    let formattedAmount = '0';
                    if (amount && amount !== '0' && amount !== '') {
                        const numericAmount = parseInt(amount);
                        if (!isNaN(numericAmount)) {
                            formattedAmount = numericAmount.toLocaleString('id-ID');
                        }
                    }
                    
                    document.getElementById('paymentAmount').textContent = formattedAmount;
                    
                    // Update form action with correct customer ID
                    const paymentForm = document.getElementById('paymentForm');
                    if (customerId) {
                        paymentForm.action = `/konfirmasi/pembayaran/${customerId}`;
                    }
                    
                    // Add hidden input for invoice ID if available
                    let invoiceInput = paymentForm.querySelector('input[name="invoice_id"]');
                    if (!invoiceInput) {
                        invoiceInput = document.createElement('input');
                        invoiceInput.type = 'hidden';
                        invoiceInput.name = 'invoice_id';
                        paymentForm.appendChild(invoiceInput);
                    }
                    invoiceInput.value = invoiceId || '';
                    
                    // Show warning if no invoice amount
                    const paymentAmountElement = document.getElementById('paymentAmount');
                    const paymentAmountContainer = paymentAmountElement.parentElement;
                    
                    if (amount === '0' || amount === '' || !amount) {
                        paymentAmountContainer.classList.add('no-invoice');
                        paymentAmountContainer.title = 'Tidak ada tagihan yang belum dibayar';
                        
                        // Disable submit button if no amount
                        const submitButton = paymentForm.querySelector('button[type="submit"]');
                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.textContent = 'Tidak Ada Tagihan';
                        }
                    } else {
                        paymentAmountContainer.classList.remove('no-invoice');
                        paymentAmountContainer.title = '';
                        
                        // Enable submit button
                        const submitButton = paymentForm.querySelector('button[type="submit"]');
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = 'Konfirmasi';
                        }
                    }
                    
                    // Show the modal
                    paymentModal.show();
                });
            });
        }
        
        // Handle payment method selection with select dropdown
        paymentMethodSelect.addEventListener('change', function() {
            const method = this.value;
            
            // Show/hide transfer details based on selected method
            if (method === '2' || method === '3') {
                transferDetails.style.display = 'block';
            } else {
                transferDetails.style.display = 'none';
            }
        });
        
        // Initialize the system
        initializeCustomerData();
    });
</script>

<script>
    // Function to update customer counters (total, active, non-active)
    function updateCustomerCounters() {
        const tableBody = document.querySelector('#customerTable tbody');
        if (!tableBody) return;
        
        const allRows = tableBody.querySelectorAll('tr.customer-row');
        const counts = {
            total: allRows.length,
            active: 0,
            nonActive: 0,
            pembayaran: 0
        };
        
        allRows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(7)');
            if (statusCell && statusCell.textContent.trim().includes('Aktif')) {
                counts.active++;
            } else {
                counts.nonActive++;
            }
            
            // Increment pembayaran count based on some condition
            const paymentCell = row.querySelector('td:nth-child(8)'); // Assuming payment status is in the 8th column
            if (paymentCell && paymentCell.textContent.trim() === 'Belum Dibayar') {
                counts.pembayaran++;
            }
        });
        
        // Update badges using a more robust method
        const updateBadge = (selector, value) => {
            const badge = document.querySelector(selector);
            if (badge) badge.textContent = value;
        };
        
        updateBadge('.data-card.primary-card .badge.bg-primary', counts.total);
        updateBadge('.data-card.success-card .badge.bg-success', counts.active);
        updateBadge('.data-card.danger-card .badge.bg-danger', counts.nonActive);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize state management
        const state = {
            lastUpdate: null,
            updateQueue: new Set(),
            isUpdating: false
        };
        
        // Initialize customer counters
        
        // Improved real-time updates handling
        window.Echo.channel('updates-data')
        .listen('.data.updated', handleRealTimeUpdate);
        
        // Call updateCustomerCounters after handling real-time updates
        handleRealTimeUpdate(e); // Call the function to update counters after handling real-time updates
        
        function handleRealTimeUpdate(e) {
            console.log('Received real-time update:', e);
            
            showNotification(e.notification);
            
            if (e.data) {
                const dataArray = Array.isArray(e.data) ? e.data : [e.data];
                queueDataUpdate(dataArray);
            }
        }
        
        function showNotification(notification) {
            if (!notification) return;
            
            Swal.fire({
                text: notification.message,
                icon: notification.type,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                topLayer: true,
                animation: true,
                customClass: {
                    popup: 'animated fadeInDown'
                }
            });
        }
    });
</script>

@endsection
