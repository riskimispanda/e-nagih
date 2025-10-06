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
    
    .data-card.warning-card {
        border-left-color: #f7b924;
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
    
    .data-card-icon.warning {
        background-color: rgba(247, 185, 36, 0.1);
        color: #f7b924;
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
    
    /* Export Dropdown Styles */
    .export-dropdown {
        position: relative;
        display: inline-block;
    }
    
    /* .export-dropdown .dropdown-toggle {
        background-color: #2dce89;
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border-radius: 4px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    } */
    
    .export-dropdown .dropdown-toggle:hover {
        background-color: #24a46d;
    }
    
    .export-dropdown .dropdown-toggle:focus {
        box-shadow: 0 0 0 0.15rem rgba(45, 206, 137, 0.25);
        outline: none;
    }
    
    .export-dropdown .dropdown-menu {
        min-width: 200px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 0.5rem 0;
        margin-top: 0.25rem;
    }
    
    .export-dropdown .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        color: #495057;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }
    
    .export-dropdown .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #2dce89;
    }
    
    .export-dropdown .dropdown-item i {
        font-size: 1rem;
        width: 16px;
        text-align: center;
    }
    
    .export-dropdown .dropdown-divider {
        height: 1px;
        background-color: #e9ecef;
        margin: 0.5rem 0;
        border: none;
    }
    
    .export-dropdown .dropdown-header {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        color: #8898aa;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #f8f9fa;
        margin-bottom: 0.25rem;
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
        
        .export-dropdown .dropdown-toggle {
            width: 100%;
            justify-content: center;
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
                                        {{ $data->total() }}
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
                                        {{ \App\Models\Customer::where('status_id', 3)->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="data-card bg-white danger-card" data-bs-toggle="modal" data-bs-target="#blokir">
                            <div class="data-card-icon danger">
                                <i class="bx bx-x-circle"></i>
                            </div>
                            <div class="data-card-content">
                                <div class="data-label">Pelanggan Non-Aktif</div>
                                <div class="data-value">
                                    <span class="badge bg-danger rounded-pill">
                                        {{ $nonAktif->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3" data-bs-toggle="modal" data-bs-target="#maintenance">
                        <div class="data-card bg-white danger-card">
                            <div class="data-card-icon danger">
                                <i class="bx bx-loader"></i>
                            </div>
                            <div class="data-card-content">
                                <div class="data-label">Maintenance Hari Ini</div>
                                <div class="data-value">
                                    <span class="badge bg-danger rounded-pill">
                                        {{ $maintenance->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3" data-bs-toggle="modal" data-bs-target="#selesai">
                        <div class="data-card bg-white success-card">
                            <div class="data-card-icon success">
                                <i class="bx bx-check-circle"></i>
                            </div>
                            <div class="data-card-content">
                                <div class="data-label">Installasi Hari Ini</div>
                                <div class="data-value">
                                    <span class="badge bg-success rounded-pill">
                                        {{ $hariIni }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3" data-bs-toggle="modal" data-bs-target="#antrian">
                        <div class="data-card bg-white warning-card">
                            <div class="data-card-icon warning">
                                <i class="bx bx-hourglass"></i>
                            </div>
                            <div class="data-card-content">
                                <div class="data-label">Antrian Hari Ini</div>
                                <div class="data-value">
                                    <span class="badge bg-warning rounded-pill">
                                        {{ $menunggu }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3" data-bs-toggle="modal" data-bs-target="#bulanan">
                        <div class="data-card bg-white success-card">
                            <div class="data-card-icon success">
                                <i class="bx bx-calendar"></i>
                            </div>
                            <div class="data-card-content">
                                <div class="data-label">Instalasi {{now()->translatedFormat('F Y')}}</div>
                                <div class="data-value">
                                    <span class="badge bg-success rounded-pill">
                                        {{ $bulananInstallasi }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3 mb-3">
                        <a href="/data-agen" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat Data Agen">
                            <div class="data-card bg-white secondary-card" data-bs-toggle="modal" data-bs-target="#gagal">
                                <div class="data-card-icon secondary">
                                    <i class="bx bx-user"></i>
                                </div>
                                <div class="data-card-content">
                                    <div class="data-label">Data Agen</div>
                                    <div class="data-value">
                                        <span class="badge bg-secondary rounded-pill">
                                            {{ $countAgen }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3 mb-3">
                        <div class="data-card bg-white secondary-card" data-bs-toggle="modal" data-bs-target="#gagal">
                            <div class="data-card-icon secondary">
                                <i class="bx bx-error-circle"></i>
                            </div>
                            <div class="data-card-content">
                                <div class="data-label">Data Import</div>
                                <div class="data-value">
                                    <span class="badge bg-secondary rounded-pill">
                                        {{ $importData }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <div class="col-12 col-md-3 mb-4">
                        <div class="sort-group col-sm-12">
                            <div class="sort-label">Pencarian</div>
                            <div class="input-group">
                                <input type="text" class="form-control search-input" placeholder="Cari pelanggan..." id="searchCustomer">
                                <button class="btn search-button" type="button" id="searchButton">
                                    <i class="bx bx-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-4">
                        <div class="sort-group col-sm-12">
                            <div class="sort-label">Urutkan Berdasarkan</div>
                            <select class="sort-select" id="sortSelect">
                                <option value="default">Default</option>
                                <option value="name-asc">Nama A-Z</option>
                                <option value="name-desc">Nama Z-A</option>
                                <option value="status-active">Status Aktif</option>
                                <option value="status-inactive">Status Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 mb-4">
                        <div class="sort-group col-sm-12">
                            <div class="sort-label">Halaman</div>
                            <div class="pagination-info">
                                <div class="page-size-selector">
                                    <label for="pageSize">Data per halaman:</label>
                                    <select id="pageSize" class="page-size-select">
                                        @foreach([10, 25, 50, 100, 250] as $size)
                                            <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                                                {{ $size }}
                                            </option>
                                        @endforeach
                                        <option value="{{ $data->total() }}" {{ request('per_page') == $data->total() ? 'selected' : '' }}>Semua</option>
                                    </select>
                                </div>
                                <div class="data-count-info">
                                    Menampilkan <span class="count-highlight">{{ $data->firstItem() ?? 0 }}</span> -
                                    <span class="count-highlight">{{ $data->lastItem() ?? 0 }}</span> dari
                                    <span class="count-highlight">{{ $data->total() }}</span> data
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="d-flex justify-content-start">
                            <!-- Export Dropdown -->
                            <div class="export-dropdown">
                                <button class="btn btn-secondary btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-file me-1"></i>
                                    Export Excel
                                </button>
                                <ul class="dropdown-menu dropdown-menu-scrollable" style="max-height: 300px; overflow-y: auto;">
                                    <li><h6 class="dropdown-header">Export Semua Data</h6></li>
                                    <li>
                                        <a class="dropdown-item" href="/semua">
                                            <i class="bx bx-download"></i>
                                            Semua Pelanggan
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="/aktif">
                                            <i class="bx bx-check-circle"></i>
                                            Pelanggan Aktif
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="/nonaktif">
                                            <i class="bx bx-x-circle"></i>
                                            Pelanggan Non-Aktif
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Export Berdasarkan Paket</h6></li>
                                    @php
                                        $pakets = \App\Models\Paket::orderBy('nama_paket')->get();
                                    @endphp
                                    @forelse($pakets as $paket)
                                    <li>
                                        <a class="dropdown-item" href="/paket/{{ $paket->id }}">
                                            <i class="bx bx-package"></i>
                                            {{ $paket->nama_paket }}
                                            <small class="text-muted">({{ $paket->customer->count() }} pelanggan)</small>
                                        </a>
                                    </li>
                                    @empty
                                    <li>
                                        <span class="dropdown-item text-muted">
                                            <i class="bx bx-info-circle"></i>
                                            Tidak ada paket tersedia
                                        </span>
                                    </li>
                                    @endforelse
                                    @if($pakets->count() > 0)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="/ringkasan-per-paket">
                                            <i class="bx bx-chart"></i>
                                            Ringkasan Per Paket
                                        </a>
                                    </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Export Berdasarkan Bulan</h6></li>
                                    @php
                                        $months = [
                                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                        ];
                                        $currentYear = date('Y');
                                    @endphp

                                    @foreach($months as $num => $name)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('export.bulan', ['month' => $num, 'year' => $currentYear]) }}">
                                            <i class="bx bx-calendar"></i>
                                            {{ $name }} {{ $currentYear }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
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
                                <th>Telp.</th>
                                <th>PIC</th>
                                <th>ODP</th>
                                <th>Paket</th>
                                <th>Status</th>
                                <th>Tanggal Installasi</th>
                                <th>History Pembayaran</th>
                                <th>Pembayaran Terakhir</th>
                                <th>Remote IP</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="customerTableBody">
                            @foreach ($data as $item)
                            <tr class="customer-row text-center" data-id="{{ $item->id }}"
                                data-tagihan="{{ $item->invoice->isNotEmpty() && $item->invoice->first()->status ? ($item->invoice->first()->status->nama_status == 'Sudah Bayar' ? '0' : $item->invoice->first()->tagihan ?? '0') : '0' }}"
                                data-customer-id="{{ $item->id }}"
                                data-invoice-id="{{ $item->invoice->isNotEmpty() ? $item->invoice->first()->id : '' }}"
                                data-tagihan-tambahan="{{ $item->invoice->isNotEmpty() ? $item->invoice->first()->tambahan : '' }}">
                                <td class="text-center">{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                                <td class="customer-name">
                                    <div class="fw-bold">
                                    {{ $item->nama_customer }}
                                    </div>
                                    <span class="d-block text-muted">
                                        <small>{{$item->alamat}}</small>
                                    </span>
                                </td>
                                <td class="nomor-hp">{{ $item->no_hp }}</td>
                                <td class="fw-bold">{{$item->agen->name ?? '-'}}</td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 status-badge text-info">
                                        {{ $item->media_id == 3 ? ($item->odp->nama_odp ?? '-') : '-' }}
                                    </span>
                                </td>
                                <td class="nama-paket">
                                    <span class="badge bg-warning bg-opacity-10 status-badge text-warning">
                                        {{ $item->paket->nama_paket ?? '-'}}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($item->status_id == 3)
                                    <span class="badge bg-success bg-opacity-10 text-success status-badge">
                                        Aktif
                                    </span>
                                    @elseif($item->status_id == 9)
                                    <span class="badge bg-danger status-badge bg-opacity-10 text-danger" style="text-transform: uppercase">
                                        Isolire Billing
                                    </span>
                                    @elseif($item->status_id == 4)
                                    <span class="badge bg-info status-badge bg-opacity-10 text-info" style="text-transform: uppercase">
                                        Maintenance
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ is_numeric($item->tanggal_selesai) ? '-' : (\Carbon\Carbon::parse($item->tanggal_selesai)->format('d-F-Y H:i:s')) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="/riwayatPembayaran/{{ $item->id }}" class="btn btn-outline-info action-btn" data-bs-toggle="tooltip" title="History Pembayaran {{ $item->nama_customer }}" data-bs-placement="top">
                                        <i class="bx bx-history"></i>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-label-danger">{{$item->last_pembayaran ?? '-'}}</span>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="d-flex justify-content-center">
                                            <a href="http://{{ $item->remote ?? '#' }}" target="_blank" class="btn btn-warning action-btn" data-bs-toggle="tooltip" title="Akses Remote" data-bs-placement="bottom">
                                                <i class="bx bx-cloud"></i>
                                            </a>
                                            <a href="/traffic-pelanggan/{{ $item->id }}" class="btn btn-info action-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Traffic {{ $item->nama_customer }}">
                                                <i class="bx bx-chart"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="row">
                                        <div class="d-flex justify-content-center">
                                            @if (auth()->user()->roles_id == 1 || auth()->user()->roles_id == 2)
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
                                            <a href="/edit-pelanggan/{{ $item->id }}"
                                                class="btn btn-secondary action-btn detail-customer-btn mb-2"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Pelanggan"
                                                title="Edit Pelanggan">
                                                <i class="bx bx-pencil text-white"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="12">
                                    <span class="badge bg-label-danger fw-bold" style="text-transform: uppercase">
                                        Total Pendapatan bulan {{ date('M') }}: Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
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
                    <div class="pagination-controls">
                        {{ $data->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

{{-- Modal Antrian --}}
<div class="modal fade" id="antrian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel4">Antrian Pelanggan hari ini</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>Status</th>
                                <th>Teknisi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($antrian as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$item->nama_customer}}</td>
                                    <td>
                                        @if ($item->status_id == 1)
                                            <span class="badge bg-warning bg-opacity-10 text-warning">Menunggu</span>
                                        @elseif($item->status_id == 2)
                                            <span class="badge bg-info bg-opacity-10 text-info">On Progress</span>
                                        @elseif($item->status_id == 5)
                                            <span class="badge bg-info bg-opacity-10 text-info">Assigment</span>
                                        @endif
                                    </td>
                                    <td>{{$item->teknisi->name ?? '-'}}</td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="4">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Selesai --}}
<div class="modal fade" id="selesai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel4">Installasi hari ini</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>Status</th>
                                <th>Teknisi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($selesai as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$item->nama_customer}}</td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success">Selesai</span>
                                    </td>
                                    <td>{{$item->teknisi->name ?? '-'}}</td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="4">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal Bulanan --}}
<div class="modal fade" id="bulanan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel4">Instalasi Bulan ini</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>Status</th>
                                <th>Teknisi</th>
                                <th>Tanggal Registrasi</th>
                                <th>Tanggal Selesai</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($installasiBulanan as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$item->nama_customer}}</td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success">Selesai</span>
                                    </td>
                                    <td>{{$item->teknisi->name ?? '-'}}</td>
                                    <td>
                                        <span class="badge bg-label-info">{{ \Carbon\Carbon::parse($item->created_at)->locale('id')->translatedFormat('d F Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ \Carbon\Carbon::parse($item->tanggal_selesai)->locale('id')->translatedFormat('d F Y') }}</span>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="4">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pelanggan Blokir --}}
<div class="modal fade" id="blokir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel4">Pelanggan Isolire Billing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>No HP</th>
                                <th>Paket</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($nonAktif as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$item->nama_customer}}</td>
                                    <td>{{$item->no_hp}}</td>
                                    <td>
                                        <span class="badge bg-info">{{$item->paket->nama_paket}}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger">ISOLIRE BILLING</span>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="5">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pelanggan Maintenance --}}
<div class="modal fade" id="maintenance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel4">Pelanggan Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>No HP</th>
                                <th>Paket</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($maintenance as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$item->nama_customer}}</td>
                                    <td>{{$item->no_hp}}</td>
                                    <td>
                                        <span class="badge bg-info">{{$item->paket->nama_paket}}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger">ISOLIRE BILLING</span>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="5">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
        const pageSizeSelect = document.getElementById('pageSize');
        
        // Set current values from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        searchInput.value = urlParams.get('search') || '';
        sortSelect.value = urlParams.get('sort') || 'default';
        pageSizeSelect.value = urlParams.get('per_page') || '10';
        
        // Function to update URL and reload page
        function updatePage() {
            const params = new URLSearchParams();
            
            if (searchInput.value.trim()) {
                params.set('search', searchInput.value.trim());
            }
            
            if (sortSelect.value !== 'default') {
                params.set('sort', sortSelect.value);
            }
            
            if (pageSizeSelect.value !== '10') {
                params.set('per_page', pageSizeSelect.value);
            }
            
            const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.location.href = newUrl;
        }
        
        // Search functionality
        function performSearch() {
            updatePage();
        }
        
        // Event Listeners
        searchButton.addEventListener('click', performSearch);
        
        searchInput.addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                performSearch();
            }
        });
        
        // Real-time search with debounce
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.trim() === '' && urlParams.get('search')) {
                    updatePage();
                }
            }, 500);
        });
        
        // Sort dropdown change
        sortSelect.addEventListener('change', updatePage);
        
        // Page size change
        pageSizeSelect.addEventListener('change', updatePage);
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
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