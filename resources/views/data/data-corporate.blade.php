@extends('layouts.contentNavbarLayout')

@section('title', 'Data Corporate')
<style>
  .dataTables_wrapper {
    font-family: inherit;
  }

  .dataTables_length select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    outline: none;
  }

  .dataTables_filter input {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    outline: none;
  }

  .dataTables_paginate .paginate_button {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    margin: 0 0.125rem;
    background: white;
    color: #374151;
    cursor: pointer;
  }

  .dataTables_paginate .paginate_button.current {
    background: #111827;
    color: white;
    border-color: #111827;
  }

  .dataTables_paginate .paginate_button:hover {
    background: #f9fafb;
    border-color: #d1d5db;
  }

  .dataTables_paginate .paginate_button.current:hover {
    background: #111827;
  }

  .dataTables_info {
    font-size: 0.875rem;
    color: #6b7280;
  }

  .dataTables_length,
  .dataTables_filter {
    margin-bottom: 1rem;
  }

  .dataTables_paginate {
    margin-top: 1rem;
  }

  table.dataTable {
    border-collapse: collapse;
    width: 100% !important;
    min-width: 800px;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .dataTables_wrapper {
      padding: 0.5rem;
    }

    .dataTables_length select {
      font-size: 0.75rem;
      padding: 0.375rem 0.5rem;
    }

    .dataTables_filter input {
      font-size: 0.75rem;
      padding: 0.375rem 0.5rem;
      width: 150px;
    }

    .dataTables_paginate .paginate_button {
      padding: 0.375rem 0.5rem;
      font-size: 0.75rem;
      margin: 0 0.0625rem;
    }

    .dataTables_info {
      font-size: 0.75rem;
    }
  }

  @media (max-width: 640px) {

    /* Mobile-specific table adjustments */
    .overflow-x-auto {
      -webkit-overflow-scrolling: touch;
      scrollbar-width: thin;
    }

    .overflow-x-auto::-webkit-scrollbar {
      height: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 2px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 2px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }

    /* Adjust page padding for mobile */
    .p-6 {
      padding: 1rem;
    }

    /* Adjust header on mobile */
    .bg-gradient-to-r {
      border-radius: 0.75rem;
    }
  }

  table.dataTable thead th {
    background-color: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    color: #4b5563;
    white-space: nowrap;
  }

  table.dataTable tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    white-space: nowrap;
  }

  @media (max-width: 768px) {
    table.dataTable thead th {
      padding: 0.5rem 0.75rem;
      font-size: 0.625rem;
    }

    table.dataTable tbody td {
      padding: 0.5rem 0.75rem;
      font-size: 0.875rem;
    }
  }

  table.dataTable tbody tr:hover {
    background-color: #f9fafb;
  }

  table.dataTable.no-footer {
    border-bottom: none;
  }

  /* Tab Styles */
  .tab-button {
    position: relative;
    color: #6b7280;
    background-color: transparent;
    border-bottom-color: transparent;
  }

  .tab-button.active {
    background-color: #eff6ff;
    border-bottom-color: #2563eb;
    color: #2563eb;
    font-weight: 600;
  }

  .tab-button:hover {
    background-color: #f9fafb;
  }

  .tab-button.active:hover {
    background-color: #dbeafe;
  }

  .tab-button svg {
    transition: all 0.2s ease;
  }

  .tab-button.active svg {
    stroke: #2563eb;
  }

  .tab-content.hidden {
    display: none;
  }

  /* Custom scrollbar for modal */
  #corporateModal::-webkit-scrollbar {
    width: 8px;
  }

  #corporateModal::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 4px;
  }

  #corporateModal::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
  }

  #corporateModal::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
  }

  /* Modal body scrollbar */
  #modalContent .overflow-y-auto::-webkit-scrollbar {
    width: 8px;
  }

  #modalContent .overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
  }

  #modalContent .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
  }

  #modalContent .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }

  /* Ensure scrollbar is visible on all browsers */
  #modalContent .overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
  }

  /* Close button responsive styling */
  button[onclick="closeModal()"] {
    min-width: 44px;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Modal animation */
  #modalContent {
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
  }

  /* Ensure modal stays within viewport on small screens */
  @media (max-height: 600px) {
    #modalContent {
      max-height: 95vh !important;
    }

    #modalContent .overflow-y-auto {
      max-height: calc(95vh - 200px) !important;
    }
  }

  /* Improve form field focus states */
  #corporateForm input:focus,
  #corporateForm select:focus,
  #corporateForm textarea:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  /* Mobile-friendly file input */
  @media (max-width: 640px) {
    #corporateForm input[type="file"] {
      font-size: 12px;
    }
  }

  /* Mobile responsive improvements */
  @media (max-width: 640px) {

    /* On mobile, modal takes full width with small margin */
    #modalContent {
      max-width: 100vw !important;
      margin: 0.5rem !important;
      max-height: calc(100vh - 1rem);
    }

    /* Adjust modal body for mobile */
    #modalContent .overflow-y-auto {
      padding-bottom: 1rem;
    }

    /* Buttons on mobile - stacked */
    #corporateForm button[type="submit"],
    #corporateForm button[onclick="closeModal()"] {
      width: 100%;
    }
  }

  /* Tablet adjustments */
  @media (min-width: 641px) and (max-width: 1024px) {
    #modalContent {
      max-width: 95% !important;
    }
  }

  /* Form grid responsive adjustments */
  @media (max-width: 640px) {
    #corporateForm .grid-cols-3 {
      grid-template-columns: 1fr;
    }
  }

  @media (min-width: 641px) and (max-width: 1024px) {
    #corporateForm .grid-cols-3 {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  /* Table responsive improvements */
  @media (max-width: 768px) {
    table.dataTable thead th {
      padding: 0.5rem 0.75rem;
      font-size: 0.625rem;
    }

    table.dataTable tbody td {
      padding: 0.5rem 0.75rem;
      font-size: 0.75rem;
    }

    .table-modern .badge {
      font-size: 0.625rem;
      padding: 0.25rem 0.5rem;
    }
  }

  @media (max-width: 640px) {

    /* Hide non-essential columns on mobile */
    table.dataTable th:nth-child(4),
    table.dataTable td:nth-child(4) {
      display: none;
    }

    /* Adjust table container padding */
    .bg-white.rounded-xl.shadow-sm {
      padding: 0.5rem;
    }

    /* Make action buttons touch-friendly */
    table.dataTable tbody td button {
      padding: 0.375rem 0.5rem;
    }

    /* Ensure modal close button is touch-friendly */
    button[onclick="closeModal()"] {
      min-width: 44px;
      min-height: 44px;
    }
  }
</style>

@section('vendor-style')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
@endsection

@section('page-script')
  <script src="https://cdn.tailwindcss.com"></script>
@endsection

@section('content')

  <div class="p-2 rounded-lg bg-white">
    <!-- Header -->
    <div
      class="mb-6 sm:mb-8 bg-gradient-to-r from-blue-600 to-gray-900 rounded-xl p-4 sm:p-6 lg:p-8 text-white relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-32 h-32 bg-white rounded-full translate-x-1/2 translate-y-1/2"></div>
        <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-white rounded-full"></div>
      </div>

      <!-- Header Content -->
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-3 sm:mb-4">
          <div class="bg-white/20 backdrop-blur-sm p-2 sm:p-3 rounded-lg">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
              </path>
            </svg>
          </div>
          <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">Data Corporate</h1>
        </div>
        <p class="text-white/80 mb-4 sm:mb-6 text-sm sm:text-base">Kelola informasi perusahaan</p>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 md:gap-4">
          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 sm:p-4 border border-white/20">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-white/70 text-[10px] sm:text-xs uppercase tracking-wide mb-0.5 sm:mb-1">Total</p>
                <p class="text-xl sm:text-2xl font-bold text-white">{{ $stats['total'] ?? 0 }}</p>
              </div>
              <div class="bg-white/20 p-1.5 sm:p-2 rounded-lg">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                  </path>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 sm:p-4 border border-white/20">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-white/70 text-[10px] sm:text-xs uppercase tracking-wide mb-0.5 sm:mb-1">Aktif</p>
                <p class="text-xl sm:text-2xl font-bold text-white">{{ $stats['aktif'] ?? 0 }}</p>
              </div>
              <div class="bg-green-400/20 p-1.5 sm:p-2 rounded-lg">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 sm:p-4 border border-white/20">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-white/70 text-[10px] sm:text-xs uppercase tracking-wide mb-0.5 sm:mb-1">Pending</p>
                <p class="text-xl sm:text-2xl font-bold text-white">{{ $stats['pending'] ?? 0 }}</p>
              </div>
              <div class="bg-yellow-400/20 p-1.5 sm:p-2 rounded-lg">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 sm:p-4 border border-white/20">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-white/70 text-[10px] sm:text-xs uppercase tracking-wide mb-0.5 sm:mb-1">Non-Aktif</p>
                <p class="text-xl sm:text-2xl font-bold text-white">{{ $stats['nonAktif'] ?? 0 }}</p>
              </div>
              <div class="bg-red-400/20 p-1.5 sm:p-2 rounded-lg">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>



    <!-- Controls -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 sm:p-6 mb-4 sm:mb-6 border border-gray-200">
      <div class="flex flex-col sm:flex-row justify-end items-stretch sm:items-center gap-3">
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
          <button
            class="flex-1 sm:flex-none px-3 sm:px-4 py-2 sm:py-2.5 bg-gray-100 text-gray-700 rounded-lg text-xs sm:text-sm font-medium hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
            <i class="bx bx-import text-sm sm:text-base"></i>
            <span class="hidden sm:inline">Import</span>
            <span class="sm:hidden">Imp</span>
          </button>
          <button
            class="flex-1 sm:flex-none px-3 sm:px-4 py-2 sm:py-2.5 bg-gray-100 text-gray-700 rounded-lg text-xs sm:text-sm font-medium hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
            <i class="bx bx-export text-sm sm:text-base"></i>
            <span class="hidden sm:inline">Export</span>
            <span class="sm:hidden">Exp</span>
          </button>
          <button onclick="openModal()"
            class="flex-1 sm:flex-none px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-gray-900 to-gray-700 text-white rounded-lg text-xs sm:text-sm font-medium hover:from-gray-800 hover:to-gray-600 transition-all flex items-center justify-center gap-2 shadow-md">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span class="hidden sm:inline">Tambah Corporate</span>
            <span class="sm:hidden">Tambah</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-4 overflow-hidden">
      <div class="flex border-b border-gray-200">
        <button onclick="switchTab('data')" id="tabData"
          class="tab-button flex-1 px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium transition-all duration-200 border-b-2 border-transparent hover:bg-gray-50 focus:outline-none text-gray-700 flex items-center justify-center gap-1.5 sm:gap-2">
          <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
            </path>
          </svg>
          <span class="hidden sm:inline">Data Corporate</span>
          <span class="sm:hidden">Data</span>
        </button>
        <button onclick="switchTab('invoice')" id="tabInvoice"
          class="tab-button flex-1 px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium transition-all duration-200 border-b-2 border-transparent hover:bg-gray-50 focus:outline-none text-gray-700 flex items-center justify-center gap-1.5 sm:gap-2">
          <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
          </svg>
          <span class="hidden sm:inline">Invoice Corporate</span>
          <span class="sm:hidden">Invoice</span>
        </button>
      </div>
    </div>

    <!-- Tab Content: Data Corporate -->
    <div id="contentData" class="tab-content">
      <!-- DataTable -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden p-3">
        <div class="overflow-x-auto">
          <table id="corporateTable" class="w-full min-w-[800px]">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
              <tr>
                <th
                  class="px-4 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  No</th>
                <th
                  class="px-4 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Nama Perusahaan</th>
                <th
                  class="px-4 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Kontak</th>
                <th
                  class="px-4 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Paket</th>
                <th
                  class="px-4 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Status</th>
                <th
                  class="px-4 sm:px-6 py-3 sm:py-4 text-left text-[10px] sm:text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Tanggal</th>
                <th
                  class="px-4 sm:px-6 py-3 sm:py-4 text-right text-[10px] sm:text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tab Content: Invoice Corporate -->
    <div id="contentInvoice" class="tab-content hidden">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden p-3">
        <div class="overflow-x-auto">
          <table id="invoiceTable" class="w-full min-w-[800px]">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  <div class="flex items-center gap-2">
                    No. Invoice
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12">
                      </path>
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  <div class="flex items-center gap-2">
                    Perusahaan
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12">
                      </path>
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  <div class="flex items-center gap-2">
                    Tagihan
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12">
                      </path>
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  <div class="flex items-center gap-2">
                    Status
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12">
                      </path>
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  <div class="flex items-center gap-2">
                    Jatuh Tempo
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12">
                      </path>
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr class="text-left">
                <td class="px-4 py-3 text-sm font-mono">INV-CORP-001</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center text-xs font-medium">PT</div>
                    <div>
                      <div class="text-sm font-medium">PT. Media Digital</div>
                      <div class="text-xs text-gray-500">Digital Marketing</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm font-semibold text-gray-900">Rp 2.500.000</td>
                <td class="px-4 py-3 font-bold">
                  <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full">Belum Dibayar</span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">2024-02-01</td>
                <td class="px-4 py-3 text-center">
                  <button class="text-gray-600 hover:text-gray-900 mr-3 text-sm"><i
                      class="bx bx-eye text-info"></i></button>
                  <button class="text-gray-600 hover:text-gray-900 text-sm"><i
                      class="bx bx-download text-primary"></i></button>
                </td>
              </tr>
              <tr class="text-left">
                <td class="px-4 py-3 text-sm font-mono">INV-CORP-002</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center text-xs font-medium">CV</div>
                    <div>
                      <div class="text-sm font-medium">CV. Maju Jaya</div>
                      <div class="text-xs text-gray-500">Konstruksi</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm font-semibold text-gray-900">Rp 1.800.000</td>
                <td class="px-4 py-3 font-bold">
                  <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Sudah Dibayar</span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">2024-02-01</td>
                <td class="px-4 py-3 text-center">
                  <button class="text-gray-600 hover:text-gray-900 mr-3 text-sm"><i
                      class="bx bx-eye text-info"></i></button>
                  <button class="text-gray-600 hover:text-gray-900 text-sm"><i
                      class="bx bx-download text-primary"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Corporate -->
    <div id="corporateModal" class="fixed inset-0 hidden backdrop-blur-sm overflow-y-auto h-full w-full"
      style="background-color: rgba(0, 0, 0, 0.5); z-index: 9999; position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
      <div class="relative min-h-screen flex items-center justify-center p-2 sm:p-4 py-4 sm:py-8">
        <div id="modalContent"
          class="relative w-full max-w-2xl lg:max-w-4xl mx-auto bg-white rounded-xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 ease-out flex flex-col max-h-[90vh]"
          style="z-index: 10000; position: relative;">
          <!-- Header Modal -->
          <div class="flex justify-between items-center p-4 sm:p-6 border-b border-gray-200">
            <div class="flex-1">
              <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Tambah Corporate</h3>
              <p class="text-xs sm:text-sm text-gray-500 mt-1">Isi data perusahaan dengan lengkap</p>
            </div>
            <button onclick="closeModal()"
              class="text-gray-400 hover:text-gray-600 transform transition-transform hover:scale-110 p-2 hover:bg-gray-100 rounded-lg">
              <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Form Body -->
          <div class="p-4 sm:p-6 overflow-y-auto flex-1">
            <form id="corporateForm" class="space-y-3 sm:space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <!-- Nama PIC -->
                <div>
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                    Nama PIC <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="nama_pic" required
                    class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm"
                    placeholder="Adit">
                </div>

                <!-- Nama Perusahaan -->
                <div>
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                    Nama Perusahaan <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="nama_perusahaan" required
                    class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm"
                    placeholder="Niscala">
                </div>

                <!-- Nomor HP -->
                <div>
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                    Nomor HP <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="no_hp" required
                    class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm"
                    placeholder="08123456789">
                </div>

                <!-- Titik Lokasi / Google Maps -->
                <div>
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                    Titik Lokasi / Google Maps <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="gps" required
                    class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm"
                    placeholder="https://google.com">
                </div>
              </div>

              <!-- Alamat -->
              <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                  Alamat <span class="text-red-500">*</span>
                </label>
                <textarea name="alamat" rows="3 sm:rows-4" required
                  class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm resize-none"
                  placeholder="Masukkan alamat lengkap"></textarea>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <!-- Foto KTP -->
                <div>
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                    Foto KTP
                  </label>
                  <input type="file" name="foto" accept=".jpg,.jpeg,.png,.pdf"
                    class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                  <p class="text-[10px] sm:text-xs text-gray-500 mt-1">Max 2MB (JPG, PNG, PDF)</p>
                </div>

                <!-- Harga Custom -->
                <div>
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                    Harga Custom <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="harga" id="harga" required oninput="formatRupiah(this)"
                    class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm"
                    placeholder="Rp. 0">
                  <input type="hidden" name="harga_real" id="harga_real">
                </div>

                <!-- Teknisi -->
                <div>
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                    Teknisi <span class="text-red-500">*</span>
                  </label>
                  <select name="teknisi" required
                    class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                    <option value="">-- Pilih Teknisi --</option>
                    @foreach($teknisi as $item)
                      <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <!-- Paket Langganan -->
                <div>
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                    Paket Langganan <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="paket" required
                    class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                </div>

                <!-- Speed Internet -->
                <div>
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                    Speed Internet <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="speed" required
                    class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm"
                    placeholder="100Mbps">
                </div>

                <!-- Tanggal Registrasi -->
                <div>
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                    Tanggal Registrasi <span class="text-red-500">*</span>
                  </label>
                  <input type="date" name="tanggal" required
                    class="w-full px-2.5 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm">
                </div>
              </div>

              <!-- Buttons -->
              <div
                class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 sm:pt-6 border-t border-gray-200 mt-4 shrink-0">
                <button type="button" onclick="closeModal()"
                  class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-xs sm:text-sm font-medium transition-colors">
                  Batal
                </button>
                <button type="submit"
                  class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 bg-gradient-to-r from-gray-900 to-gray-700 text-white rounded-lg hover:from-gray-800 hover:to-gray-600 text-xs sm:text-sm font-medium transition-all shadow-md">
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      // Modal functions
      function openModal() {
        const modal = document.getElementById('corporateModal');
        const modalContent = document.getElementById('modalContent');

        // Show modal with initial state
        modal.classList.remove('hidden');

        // Force initial styles - start fully transparent
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0)';
        modal.style.transition = 'background-color 0.3s ease-in-out';

        // Trigger animation after a brief delay
        setTimeout(() => {
          modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
          modalContent.classList.remove('scale-95', 'opacity-0');
          modalContent.classList.add('scale-100', 'opacity-100');
        }, 50);

        document.body.style.overflow = 'hidden';

        // Reset form when opening
        document.getElementById('corporateForm').reset();
      }

      function closeModal() {
        const modal = document.getElementById('corporateModal');
        const modalContent = document.getElementById('modalContent');

        // Trigger close animation
        modal.style.transition = 'background-color 0.3s ease-in-out';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0)';
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');

        // Hide modal after animation completes
        setTimeout(() => {
          modal.classList.add('hidden');
          modal.style.transition = '';
          modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
          document.body.style.overflow = 'auto';
          document.getElementById('corporateForm').reset();
        }, 300);
      }

      // Tab switching function
      function switchTab(tab) {
        const tabData = document.getElementById('tabData');
        const tabInvoice = document.getElementById('tabInvoice');
        const contentData = document.getElementById('contentData');
        const contentInvoice = document.getElementById('contentInvoice');

        tabData.classList.remove('active', 'bg-blue-50', 'border-blue-600', 'text-blue-600', 'font-semibold');
        tabInvoice.classList.remove('active', 'bg-blue-50', 'border-blue-600', 'text-blue-600', 'font-semibold');

        if (tab === 'data') {
          tabData.classList.add('active', 'bg-blue-50', 'border-blue-600', 'text-blue-600', 'font-semibold');
          contentData.classList.remove('hidden');
          contentInvoice.classList.add('hidden');
        } else {
          tabInvoice.classList.add('active', 'bg-blue-50', 'border-blue-600', 'text-blue-600', 'font-semibold');
          contentData.classList.add('hidden');
          contentInvoice.classList.remove('hidden');
        }
      }

      // Close modal when clicking outside
      document.getElementById('corporateModal').addEventListener('click', function (e) {
        if (e.target === this) {
          closeModal();
        }
      });

      // Add smooth hover effects to form inputs
      document.querySelectorAll('input, select, textarea').forEach(element => {
        element.addEventListener('focus', function () {
          this.classList.add('ring-2', 'ring-blue-500', 'border-blue-500');
          this.style.transition = 'all 0.2s ease-in-out';
        });

        element.addEventListener('blur', function () {
          this.classList.remove('ring-2', 'ring-blue-500', 'border-blue-500');
        });
      });

      // Add animation to form labels on focus
      document.querySelectorAll('input, select, textarea').forEach(element => {
        const label = element.previousElementSibling;
        if (label && label.tagName === 'LABEL') {
          element.addEventListener('focus', function () {
            label.classList.add('text-gray-900');
            label.classList.add('transform', 'translate-x-1');
            label.style.transition = 'all 0.2s ease-in-out';
          });

          element.addEventListener('blur', function () {
            label.classList.remove('text-gray-900');
            label.classList.remove('transform', 'translate-x-1');
          });
        }
      });

      // Format Rupiah
      function formatRupiah(el) {
        let angka = el.value.replace(/[^,\d]/g, "").toString();
        let split = angka.split(",");
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
          let separator = sisa ? "." : "";
          rupiah += separator + ribuan.join(".");
        }

        rupiah = split[1] !== undefined ? rupiah + "," + split[1] : rupiah;
        el.value = "Rp " + rupiah;

        let angka_murni = el.value.replace(/[^0-9]/g, "");
        document.getElementById("harga_real").value = angka_murni;
      }

      // Form submission
      document.getElementById('corporateForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('/api/corporate/store', {
          method: 'POST',
          body: formData,
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              closeModal();
              alert(data.message);

              // Reload table data
              const table = $('#corporateTable').DataTable();
              table.ajax.reload();
            } else {
              alert('Gagal: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan data');
          });
      });

      function updateStats() {
        const table = $('#corporateTable').DataTable();
        const totalRows = table.data().length;

        // Update total count (this is just an example, in real app you'd update server-side)
        console.log('Total corporate:', totalRows);
      }

      // Wait for DataTables library to be loaded
      function initializeDataTables() {
        jQuery(document).ready(function ($) {
          try {
            console.log('Initializing DataTable...');
            console.log('jQuery version:', $.fn.jquery);
            console.log('DataTable available:', typeof $.fn.DataTable);

            var table = $('#corporateTable').DataTable({
              ajax: {
                url: '/api/corporate',
                dataSrc: 'data',
                error: function (xhr, error, code) {
                  console.error('AJAX Error:', error, code);
                  console.error('Response:', xhr.responseText);
                }
              },
              columns: [
                { data: 'id_formatted' },
                {
                  data: null,
                  render: function (data, type, row) {
                    return `
                                              <div class="flex items-center gap-3">
                                                  <div class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center text-xs font-medium">PT</div>
                                                  <div>
                                                      <div class="text-sm font-medium">${data.nama_perusahaan}</div>
                                                      <div class="text-xs text-gray-500">${data.bidang_usaha}</div>
                                                  </div>
                                              </div>
                                          `;
                  }
                },
                { data: 'kontak' },
                { data: 'email' },
                {
                  data: 'status',
                  render: function (data, type, row) {
                    const statusClass = row.status_class || 'gray';
                    return `<span class="px-2 py-1 bg-${statusClass}-100 text-${statusClass}-700 text-xs rounded-full">${data}</span>`;
                  }
                },
                { data: 'tanggal' },
                {
                  data: null,
                  render: function (data, type, row) {
                    return `
                                              <button class="text-gray-600 hover:text-gray-900 mr-3 text-sm"><i class="bx bx-pencil text-warning"></i></button>
                                              <button class="text-gray-600 hover:text-red-600 text-sm"><i class="bx bx-trash text-danger"></i></button>
                                          `;
                  }
                }
              ],
              language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                paginate: {
                  first: "Pertama",
                  last: "Terakhir",
                  next: "Selanjutnya",
                  previous: "Sebelumnya"
                },
                zeroRecords: "Tidak ada data yang ditemukan",
                emptyTable: "Tidak ada data tersedia"
              },
              pageLength: 10,
              ordering: true,
              order: [[0, 'desc']],
              searching: true,
              info: true,
              paging: true,
              autoWidth: false
            });

            console.log('DataTable initialized successfully');

            var invoiceTable = $('#invoiceTable').DataTable({
              language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                paginate: {
                  first: "Pertama",
                  last: "Terakhir",
                  next: "Selanjutnya",
                  previous: "Sebelumnya"
                },
                zeroRecords: "Tidak ada data yang ditemukan",
                emptyTable: "Tidak ada data tersedia"
              },
              pageLength: 10,
              ordering: true,
              order: [[0, 'desc']],
              searching: true,
              info: true,
              paging: true,
              autoWidth: false
            });

            // Custom filter untuk status
            $('#statusFilter').on('change', function () {
              var status = $(this).val();
              if (status) {
                table.column(4).search(status).draw();
              } else {
                table.column(4).search('').draw();
              }
            });
          } catch (error) {
            console.error('Error initializing DataTable:', error);
            alert('Error loading data table. Please refresh the page.');
          }
        });
      }

      // Listen for datatables-ready event
      window.addEventListener('datatables-ready', function () {
        console.log('DataTables ready event received, initializing tables...');
        initializeDataTables();
      });
    </script>

    <!-- Load DataTables after theme jQuery -->
    <script>
      // Dynamically load DataTables library after page is ready
      (function () {
        // Wait for theme's jQuery to be available
        var checkJQuery = setInterval(function () {
          if (typeof jQuery !== 'undefined') {
            clearInterval(checkJQuery);
            console.log('Theme jQuery detected, loading DataTables...');

            // Load DataTables library
            var script = document.createElement('script');
            script.src = 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js';
            script.onload = function () {
              console.log('DataTables library loaded successfully');
              // Trigger a custom event when DataTables is ready
              window.dispatchEvent(new Event('datatables-ready'));
            };
            script.onerror = function () {
              console.error('Failed to load DataTables library');
            };
            document.head.appendChild(script);
          }
        }, 100);
      })();
    </script>

@endsection