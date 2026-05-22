@extends('layouts/contentNavbarLayout')
@section('title', 'Data Corporate')

@section('vendor-style')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endsection

<style>
  /* Modern Card Styling */
  /* Modern Card Styling */
  .stats-card {
    border: 1px solid rgba(0, 0, 0, 0.05);
    /* Very subtle border */
    border-radius: 1rem;
    /* Softer rounded corners */
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
    /* Very soft shadow */
    transition: all 0.2s ease-in-out;
    background-color: #fff;
  }

  .stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    border-color: transparent;
  }

  .stats-icon-wrapper {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
  }

  /* Table Styling */
  table.dataTable thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    padding: 1rem 0.75rem;
  }

  table.dataTable tbody td {
    padding: 0.875rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
  }

  table.dataTable tbody tr:hover {
    background-color: #f8f9fa;
  }

  /* Avatar Styling */
  .avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
  }

  /* DataTables Controls Alignment */
  #tableFilter,
  #invoiceTableFilter {
    text-align: right;
  }

  #tableFilter .dataTables_filter,
  #invoiceTableFilter .dataTables_filter {
    text-align: right;
  }

  #tableInfo,
  #invoiceTableInfo {
    display: flex;
    align-items: center;
  }

  #tableInfo .dataTables_info,
  #invoiceTableInfo .dataTables_info {
    margin: 0;
    padding: 0.375rem 0;
  }

  #tablePagination,
  #invoiceTablePagination {
    display: flex;
    justify-content: flex-end;
    align-items: center;
  }

  #tablePagination .dataTables_paginate,
  #invoiceTablePagination .dataTables_paginate {
    margin: 0;
  }

  /* Responsive adjustments */
  @media (max-width: 767px) {

    #tableFilter,
    #tablePagination,
    #invoiceTableFilter,
    #invoiceTablePagination {
      text-align: left;
      justify-content: flex-start;
      margin-top: 1rem;
    }

    /* Mobile table improvements */
    .table-responsive {
      font-size: 0.875rem;
    }

    table.dataTable {
      min-width: 600px;
    }

    table.dataTable thead th,
    table.dataTable tbody td {
      padding: 0.75rem 0.5rem;
      white-space: nowrap;
    }

    /* Make avatar smaller on mobile */
    .avatar-sm {
      width: 2rem;
      height: 2rem;
    }

    .avatar-sm .avatar-initial {
      font-size: 0.65rem;
    }
  }

  /* Custom Nav Tabs Styling */
  .nav-tabs-custom {
    border-bottom: 2px solid #e9ecef;
    gap: 0.5rem;
  }

  .nav-tabs-custom .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
    background: transparent;
    position: relative;
  }

  .nav-tabs-custom .nav-link:hover {
    color: #696cff;
    border-bottom-color: rgba(105, 108, 255, 0.3);
    background-color: rgba(105, 108, 255, 0.05);
  }

  .nav-tabs-custom .nav-link.active {
    color: #696cff;
    border-bottom-color: #696cff;
    background-color: rgba(105, 108, 255, 0.08);
  }

  .nav-tabs-custom .nav-link i {
    font-size: 1.1rem;
    vertical-align: middle;
  }

  /* Mobile Pills Styling */
  .nav-pills .nav-link {
    border-radius: 0.5rem;
    padding: 1rem 0.5rem;
    transition: all 0.3s ease;
    text-align: center;
    color: #6c757d;
    background-color: #f8f9fa;
    border: 2px solid transparent;
  }

  .nav-pills .nav-link:hover {
    background-color: rgba(105, 108, 255, 0.1);
    border-color: rgba(105, 108, 255, 0.3);
    color: #696cff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .nav-pills .nav-link.active {
    background-color: #696cff;
    color: white;
    border-color: #696cff;
    box-shadow: 0 4px 12px rgba(105, 108, 255, 0.4);
  }

  .nav-pills .nav-link i {
    transition: transform 0.3s ease;
  }

  .nav-pills .nav-link:hover i,
  .nav-pills .nav-link.active i {
    transform: scale(1.1);
  }

  /* Tab Content Animation */
  .tab-content>.tab-pane {
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
  }

  .tab-content>.active {
    display: block;
    opacity: 1;
  }

  /* Responsive Nav Adjustments */
  @media (max-width: 576px) {
    .nav-pills .nav-link {
      padding: 0.875rem 0.25rem;
      font-size: 0.8rem;
    }

    .nav-pills .nav-link i {
      font-size: 1.25rem !important;
    }

    .nav-pills .nav-link small {
      font-size: 0.7rem;
    }
  }
</style>

@section('content')
  <div class="row">
    <div class="col-12">
      <!-- Header Card -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h4 class="card-title fw-bold mb-1">
              <i class="bx bx-buildings me-2"></i>Data Corporate
            </h4>
            <small class="text-muted">Kelola data perusahaan dengan mudah dan efisien</small>
          </div>
          <button onclick="openModal()" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>Tambah Corporate
          </button>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="row g-4 mb-4">
        <div class="col-lg-3 col-sm-6">
          <div class="card stats-card h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                  <div class="stats-icon-wrapper bg-label-primary text-primary">
                    <i class="bx bx-buildings bx-sm"></i>
                  </div>
                </div>
                <div>
                  <small class="text-muted d-block mb-1">Total Corporate</small>
                  <h4 class="mb-0 fw-bold">125</h4>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-sm-6">
          <div class="card stats-card h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                  <div class="stats-icon-wrapper bg-label-success text-success">
                    <i class="bx bx-check-circle bx-sm"></i>
                  </div>
                </div>
                <div>
                  <small class="text-muted d-block mb-1">Active</small>
                  <h4 class="mb-0 fw-bold text-success">98</h4>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-sm-6">
          <div class="card stats-card h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                  <div class="stats-icon-wrapper bg-label-warning text-warning">
                    <i class="bx bx-pause-circle bx-sm"></i>
                  </div>
                </div>
                <div>
                  <small class="text-muted d-block mb-1">Inactive</small>
                  <h4 class="mb-0 fw-bold text-warning">27</h4>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-sm-6">
          <div class="card stats-card h-100 cursor-pointer" style="cursor: pointer;"
            onclick="window.location.href='{{ route('corporate.payment.history') }}'">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                  <div class="stats-icon-wrapper bg-label-info text-info">
                    <i class="bx bx-wallet bx-sm"></i>
                  </div>
                </div>
                <div>
                  <small class="text-muted d-block mb-1">Pembayaran Bulan Ini</small>
                  <h5 class="mb-0 fw-bold text-dark">Rp 0</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="card">
        <div class="card-body">
          <!-- Desktop Tabs (hidden on mobile) -->
          <div class="d-none d-md-flex justify-content-center mb-3">
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                  data-bs-target="#dataCorporate" aria-controls="dataCorporate" aria-selected="true">
                  <i class="bx bx-buildings me-2"></i>
                  <span class="fw-bold">Data Corporate</span>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#invoiceCorporate"
                  aria-controls="invoiceCorporate" aria-selected="false">
                  <i class="bx bx-receipt me-2"></i>
                  <span class="fw-bold">Invoice Corporate</span>
                </button>
              </li>
            </ul>
          </div>

          <!-- Mobile Pills (visible only on mobile) -->
          <div class="d-md-none mb-3">
            <ul class="nav nav-pills nav-fill" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                  data-bs-target="#dataCorporate" aria-controls="dataCorporate" aria-selected="true">
                  <i class="bx bx-buildings d-block mb-1 me-2" style="font-size: 1.5rem;"></i>
                  <small class="d-block fw-bold">Data Corporate</small>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#invoiceCorporate"
                  aria-controls="invoiceCorporate" aria-selected="false">
                  <i class="bx bx-receipt d-block mb-1 me-2" style="font-size: 1.5rem;"></i>
                  <small class="d-block fw-bold">Invoice Corporate</small>
                </button>
              </li>
            </ul>
          </div>
          <div class="separator">
            <hr>
          </div>
          <div class="tab-content">
            <!-- Tab 1: Data Corporate -->
            <div class="tab-pane fade show active" id="dataCorporate" role="tabpanel">
              <!-- Table Controls (Length & Search) -->
              <div class="row mb-3">
                <div class="col-sm-12 col-md-6">
                  <div id="tableLength"></div>
                </div>
                <div class="col-sm-12 col-md-6">
                  <div id="tableFilter"></div>
                </div>
              </div>

              <!-- Table Responsive -->
              <div class="table-responsive">
                <table id="corporateTable" class="table table-hover" style="width:100%">
                  <thead class="table-light">
                    <tr>
                      <th class="text-center">No</th>
                      <th>Nama Corporate</th>
                      <th>Email</th>
                      <th>Telepon</th>
                      <th>Alamat</th>
                      <th class="text-center">Status</th>
                      <th class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Data akan dimuat secara dinamis dari server -->
                  </tbody>
                </table>
              </div>

              <!-- Pagination (Outside table-responsive) -->
              <div class="row mt-3">
                <div class="col-sm-12 col-md-6">
                  <div id="tableInfo" class="dataTables_info"></div>
                </div>
                <div class="col-sm-12 col-md-6">
                  <div id="tablePagination" class="dataTables_paginate"></div>
                </div>
              </div>
            </div>
            <!-- End Tab 1: Data Corporate -->

            <!-- Tab 2: Invoice Corporate -->
            <div class="tab-pane fade" id="invoiceCorporate" role="tabpanel">
              <!-- Table Controls (Length & Search) -->
              <div class="row mb-3">
                <div class="col-12 mb-4">
                  <div class="row g-3">
                    <div class="col-md-3">
                      <label class="form-label text-muted small text-uppercase fw-bold">Bulan</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text bg-lighter border-0 ps-3"><i
                            class="bx bx-calendar text-muted"></i></span>
                        <select id="filterMonth" class="form-select bg-lighter border-0 ps-2">
                          <option value="">Semua Bulan</option>
                          <option value="1">Januari</option>
                          <option value="2">Februari</option>
                          <option value="3">Maret</option>
                          <option value="4">April</option>
                          <option value="5">Mei</option>
                          <option value="6">Juni</option>
                          <option value="7">Juli</option>
                          <option value="8">Agustus</option>
                          <option value="9">September</option>
                          <option value="10">Oktober</option>
                          <option value="11">November</option>
                          <option value="12">Desember</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label text-muted small text-uppercase fw-bold">Tahun</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text bg-lighter border-0 ps-3"><i
                            class="bx bx-calendar-event text-muted"></i></span>
                        <select id="filterYear" class="form-select bg-lighter border-0 ps-2">
                          <option value="">Semua Tahun</option>
                          <!-- Options will be populated by JS -->
                        </select>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label text-muted small text-uppercase fw-bold">Status</label>
                      <div class="input-group input-group-merge">
                        <span class="input-group-text bg-lighter border-0 ps-3"><i
                            class="bx bx-info-circle text-muted"></i></span>
                        <select id="filterStatus" class="form-select bg-lighter border-0 ps-2">
                          <option value="">Semua Status</option>
                          <option value="7">Belum Bayar</option>
                          <option value="8">Sudah Bayar</option>
                        </select>
                      </div>
                    </div>

                  </div>
                </div>
                <div class="col-sm-12 col-md-6">
                  <div id="invoiceTableLength"></div>
                </div>
                <div class="col-sm-12 col-md-6">
                  <div id="invoiceTableFilter"></div>
                </div>
              </div>

              <!-- Table Responsive -->
              <div class="table-responsive">
                <table id="invoiceTable" class="table table-hover" style="width:100%">
                  <thead class="table-light">
                    <tr>
                      <th class="text-center">No</th>
                      <th>Periode Tagihan</th>
                      <th>Nama Corporate</th>
                      <th>Jatuh Tempo</th>
                      <th class="text-end">Tagihan</th>
                      <th class="text-center">Status</th>
                      <th class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Data akan dimuat secara dinamis dari server -->
                  </tbody>
                </table>
              </div>

              <!-- Pagination (Outside table-responsive) -->
              <div class="row mt-3">
                <div class="col-sm-12 col-md-6">
                  <div id="invoiceTableInfo" class="dataTables_info"></div>
                </div>
                <div class="col-sm-12 col-md-6">
                  <div id="invoiceTablePagination" class="dataTables_paginate"></div>
                </div>
              </div>
            </div>
            <!-- End Tab 2: Invoice Corporate -->
          </div>
        </div>
      </div>

      <!-- Modal Tambah/Edit Corporate -->
      <div class="modal fade" id="corporateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bx bx-buildings me-2"></i>Tambah Data Corporate
              </h5>
              <button type="button" class="btn-close" onclick="closeModal()"></button>
            </div>
            <form action="/helpdesk/store" method="POST" enctype="multipart/form-data" id="formAntrianCorp">
              @csrf
              <input type="hidden" name="jenis_pelanggan" value="Perusahaan">
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-sm-6 form-group">
                    <label class="form-label">Nama PIC<strong class="text-danger"> *</strong></label>
                    <input type="text" class="form-control" name="nama_pic" placeholder="Adit" required>
                  </div>
                  <div class="col-sm-6 form-group">
                    <label class="form-label">Nama Perusahaan<strong class="text-danger"> *</strong></label>
                    <input type="text" class="form-control" name="nama_perusahaan" placeholder="Niscala" required>
                  </div>
                  <div class="col-sm-6 form-group">
                    <label class="form-label">Nomor Hp<strong class="text-danger"> *</strong></label>
                    <input type="text" class="form-control" name="no_hp" placeholder="08123456789" required>
                  </div>
                  <div class="col-sm-6 form-group">
                    <label class="form-label">Titik Lokasi / Google Maps<strong class="text-danger"> *</strong></label>
                    <input type="text" class="form-control" name="gps" placeholder="https://goolge.com" required>
                  </div>
                  <div class="col-sm-12 form-group">
                    <label class="form-label">Alamat<strong class="text-danger"> *</strong></label>
                    <textarea name="alamat" cols="20" rows="3" class="form-control" required></textarea>
                  </div>
                  <div class="col-sm-6 form-group">
                    <label class="form-label">Foto KTP</label>
                    <input type="file" class="form-control" name="foto">
                    <small class="text-muted">Ukuran File 2MB (JPG, JPEG, PNG)</small>
                  </div>
                  <div class="col-sm-6 form-group">
                    <label class="form-label">Harga Custom<strong class="text-danger"> *</strong></label>
                    <input type="text" class="form-control" id="harga" oninput="formatRupiah(this)" placeholder="Rp. 0"
                      required>
                    <input type="text" class="form-control" name="harga" id="harga_real" hidden>
                  </div>
                  <div class="col-sm-6 form-group">
                    <label class="form-label">Teknisi<strong class="text-danger"> *</strong></label>
                    <select class="form-control form-select" id="teknisi" name="teknisi" required>
                      <option value="">--Pilih Teknisi--</option>
                      @foreach($teknisi as $item)
                        <option value="{{$item->id}}">{{$item->name}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-sm-6 form-group">
                    <label class="form-label">Paket Langganan<strong class="text-danger"> *</strong></label>
                    <input type="text" class="form-control" name="paket" required>
                  </div>
                  <div class="col-sm-6 form-group">
                    <label class="form-label">Speed Internet<strong class="text-danger"> *</strong></label>
                    <input type="text" name="speed" class="form-control" placeholder="100Mbps">
                  </div>
                  <div class="col-sm-6 form-group">
                    <label class="form-label">Tanggal Registrasi<strong class="text-danger"> *</strong></label>
                    <input type="date" class="form-control" name="tanggal" required>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" onclick="closeModal()">
                  <i class="bx bx-x me-1"></i>Batal
                </button>
                <button type="submit" class="btn btn-primary">
                  <i class="bx bx-save me-1"></i>Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal Konfirmasi Pembayaran -->
      <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bx bx-money me-2"></i>Konfirmasi Pembayaran
              </h5>
              <button type="button" class="btn-close" onclick="closePaymentModal()"></button>
            </div>
            <form id="formPayment" onsubmit="submitPayment(event)">
              <div class="modal-body">
                <input type="hidden" id="payment_invoice_id" name="invoice_id">

                <!-- Info Tagihan -->
                <div class="alert alert-primary mb-3">
                  <div class="d-flex justify-content-between mb-1">
                    <span class="fw-bold">No. Invoice:</span>
                    <span id="payment_invoice_number">INV-XXX</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total Tagihan:</span>
                    <span id="payment_tagihan">Rp 0</span>
                  </div>
                </div>

                <div class="row g-3">
                  <!-- Checkbox Tagihan (Sesuai Request User) -->
                  <div class="col-12">
                    <div class="form-check custom-option custom-option-basic">
                      <label class="form-check-label custom-option-content" for="checkTagihan">
                        <input class="form-check-input" type="checkbox" id="checkTagihan" required>
                        <span class="custom-option-header">
                          <span class="h6 mb-0">Tagihan Valid</span>
                          <small class="text-muted">Centang untuk konfirmasi tagihan ini</small>
                        </span>
                      </label>
                    </div>
                  </div>

                  <div class="col-12 form-group">
                    <label class="form-label">Tanggal Bayar<strong class="text-danger"> *</strong></label>
                    <input type="date" class="form-control" name="tanggal_bayar" id="payment_date" required>
                  </div>

                  <div class="col-12 form-group">
                    <label class="form-label">Jumlah Bayar<strong class="text-danger"> *</strong></label>
                    <input type="text" class="form-control" id="payment_amount_display"
                      oninput="formatRupiahPayment(this)" required>
                    <input type="hidden" name="jumlah_bayar" id="payment_amount">
                  </div>

                  <div class="col-12 form-group">
                    <label class="form-label">Metode Bayar<strong class="text-danger"> *</strong></label>
                    <select class="form-select" name="metode_bayar" required>
                      <option value="">-- Pilih Metode --</option>
                      <option value="Transfer">Transfer Bank</option>
                      <option value="Cash">Cash / Tunai</option>
                    </select>
                  </div>

                  <div class="col-12 form-group">
                    <label class="form-label">Bukti Bayar</label>
                    <input type="file" class="form-control" name="bukti_bayar" accept="image/*">
                    <small class="text-muted">Format: JPG, JPEG, PNG (Max 2MB)</small>
                  </div>

                  <div class="col-12 form-group">
                    <label class="form-label">Keterangan</label>
                    <textarea class="form-control" name="keterangan" rows="2"
                      placeholder="Catatan tambahan..."></textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" onclick="closePaymentModal()">
                  <i class="bx bx-x me-1"></i>Batal
                </button>
                <button type="submit" class="btn btn-primary">
                  <i class="bx bx-save me-1"></i>Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
@endsection

    @section('page-script')
      <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

      <script>
        let dataTable;
        let invoiceTable;

        jQuery(document).ready(function ($) {
          // Load statistics
          loadStatistics();

          // Initialize DataTable dengan server-side processing
          dataTable = $('#corporateTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
              "url": "/api/corporate/data",
              "type": "GET",
              "error": function (xhr, error, code) {
                console.error('Error loading data:', error);
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'Gagal memuat data corporate'
                });
              }
            },
            "columns": [
              {
                "data": "DT_RowIndex",
                "name": "DT_RowIndex",
                "orderable": false,
                "searchable": false,
                "className": "text-center"
              },
              {
                "data": "nama_perusahaan",
                "name": "nama_perusahaan",
                "render": function (data, type, row) {
                  let initial = data.substring(0, 2).toUpperCase();
                  let colors = ['primary', 'info', 'warning', 'success', 'danger'];
                  let color = colors[Math.floor(Math.random() * colors.length)];

                  return `
                                  <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                      <span class="avatar-initial rounded-circle bg-label-${color}">${initial}</span>
                                    </div>
                                    <span class="fw-medium">${data}</span>
                                  </div>
                                `;
                }
              },
              {
                "data": "no_hp",
                "name": "no_hp"
              },
              {
                "data": "no_hp",
                "name": "no_hp"
              },
              {
                "data": "alamat",
                "name": "alamat"
              },
              {
                "data": "status",
                "name": "status",
                "className": "text-center",
                "render": function (data, type, row) {
                  let badgeClass = 'bg-label-secondary';
                  if (row.status_id == 3) {
                    badgeClass = 'bg-label-success';
                    data = "Aktif";
                  } else if (row.status_id == 9) {
                    badgeClass = 'bg-label-warning';
                    data = "Non Aktif";
                  }
                  return `<span class="badge ${badgeClass}">${data}</span>`;
                }
              },
              {
                "data": "id",
                "orderable": false,
                "searchable": false,
                "className": "text-center",
                "render": function (data, type, row) {
                  return `
                                <div class="btn-group" role="group">
                                  <button type="button" class="btn btn-sm btn-icon btn-outline-primary btn-edit" data-id="${data}">
                                    <i class="bx bx-edit"></i>
                                  </button>
                                  <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-delete" data-id="${data}">
                                    <i class="bx bx-trash"></i>
                                  </button>
                                </div>
                              `;
                }
              }
            ],
            "language": {
              "lengthMenu": "Tampilkan _MENU_ data per halaman",
              "zeroRecords": "Data tidak ditemukan",
              "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
              "infoEmpty": "Tidak ada data tersedia",
              "infoFiltered": "(difilter dari _MAX_ total data)",
              "search": "Cari:",
              "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
              },
              "processing": "Memuat data..."
            },
            "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "ordering": true,
            "searching": true,
            "responsive": true,
            "initComplete": function (settings, json) {
              // Move the length and filter controls to proper divs
              $('#tableLength').append($('#corporateTable_wrapper .dataTables_length'));
              $('#tableFilter').append($('#corporateTable_wrapper .dataTables_filter'));
              $('#tableInfo').append($('#corporateTable_wrapper .dataTables_info'));
              $('#tablePagination').append($('#corporateTable_wrapper .dataTables_paginate'));
            }
          });

          console.log('DataTable initialized successfully!');

          // Populate Years dan Set Default Filters SEBELUM Initialize DataTable
          let currentYear = new Date().getFullYear();
          let currentMonth = new Date().getMonth() + 1; // Current month (1-12)
          let startYear = 2023; // Assuming start from 2023

          for (let y = currentYear; y >= startYear; y--) {
            $('#filterYear').append(new Option(y, y));
          }

          // Set default filters SEBELUM DataTable init
          $('#filterMonth').val(currentMonth);
          $('#filterYear').val(currentYear);

          // Initialize Invoice DataTable dengan server-side processing
          invoiceTable = $('#invoiceTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
              "url": "/api/corporate/invoice",
              "type": "GET",
              "data": function (d) {
                d.month = $('#filterMonth').val();
                d.year = $('#filterYear').val();
                d.status_id = $('#filterStatus').val();
              },
              "error": function (xhr, error, code) {
                console.error('Error loading invoice data:', error);
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'Gagal memuat data invoice'
                });
              }
            },
            "columns": [
              {
                "data": "DT_RowIndex",
                "name": "DT_RowIndex",
                "orderable": false,
                "searchable": false,
                "className": "text-center"
              },
              {
                "data": "periode",
                "name": "tanggal_invoice",
                "render": function (data, type, row) {
                  return `<span class="fw-medium">${data}</span>`;
                }
              },
              {
                "data": "perusahaan",
                "name": "perusahaan",
                "render": function (data, type, row) {
                  let initial = data.substring(0, 2).toUpperCase();
                  let colors = ['primary', 'info', 'warning', 'success', 'danger'];
                  let color = colors[Math.floor(Math.random() * colors.length)];

                  return `
                                                                                                                                                                                              <div class="d-flex align-items-center">
                                                                                                                                                                                                <div class="avatar avatar-sm me-2">
                                                                                                                                                                                                  <span class="avatar-initial rounded-circle bg-label-${color}">${initial}</span>
                                                                                                                                                                                                </div>
                                                                                                                                                                                                <span>${data}</span>
                                                                                                                                                                                              </div>
                                                                                                                                                                                            `;
                }
              },
              {
                "data": "jatuh_tempo",
                "name": "jatuh_tempo"
              },
              {
                "data": "tagihan_formatted",
                "name": "tagihan",
                "className": "text-end fw-semibold"
              },
              {
                "data": "status",
                "name": "status",
                "className": "text-center",
                "render": function (data, type, row) {
                  let badgeClass = 'bg-label-secondary';

                  // Status paid
                  if (row.status_id == 8) {
                    badgeClass = 'bg-label-success';
                    data = 'Sudah Bayar';
                  }
                  // Status unpaid
                  else if (row.status_id == 7) {
                    if (row.is_overdue) {
                      badgeClass = 'bg-label-danger';
                      data = 'Belum Bayar';
                    } else {
                      badgeClass = 'bg-label-danger';
                      data = 'Belum Bayar';
                    }
                  }

                  return `<span class="badge ${badgeClass}">${data}</span>`;
                }
              },
              {
                "data": "id",
                "orderable": false,
                "searchable": false,
                "className": "text-center",
                "render": function (data, type, row) {
                  let buttons = `
                                                                                            <div class="btn-group" role="group">
                                                                                              <button type="button" class="btn btn-sm btn-icon btn-outline-info btn-view-invoice" data-id="${data}" title="Lihat">
                                                                                                <i class="bx bx-show"></i>
                                                                                              </button>
                                                                                              <button type="button" class="btn btn-sm btn-icon btn-outline-primary btn-download-invoice" data-id="${data}" title="Download">
                                                                                                <i class="bx bx-download"></i>
                                                                                              </button>`;

                  // Tambahkan button konfirmasi pembayaran jika status belum bayar (status_id = 7)
                  if (row.status_id == 7) {
                    buttons += `
                                                                                            <button type="button" class="btn btn-sm btn-icon btn-outline-success btn-confirm-payment"
                                                                                              data-id="${data}"
                                                                                              data-invoice="${row.invoice_number}"
                                                                                              data-perusahaan="${row.perusahaan}"
                                                                                              data-tagihan="${row.tagihan_formatted}"
                                                                                              data-tagihan-raw="${row.tagihan}"
                                                                                              title="Konfirmasi Pembayaran">
                                                                                              <i class="bx bx-check-circle"></i>
                                                                                            </button>`;
                  }

                  buttons += `</div>`;
                  return buttons;
                }
              }
            ],
            "language": {
              "lengthMenu": "Tampilkan _MENU_ data per halaman",
              "zeroRecords": "Data tidak ditemukan",
              "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
              "infoEmpty": "Tidak ada data tersedia",
              "infoFiltered": "(difilter dari _MAX_ total data)",
              "search": "Cari:",
              "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
              },
              "processing": "Memuat data..."
            },
            "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "ordering": true,
            "searching": true,
            "responsive": true,
            "initComplete": function (settings, json) {
              // Move the length and filter controls to proper divs
              $('#invoiceTableLength').append($('#invoiceTable_wrapper .dataTables_length'));
              $('#invoiceTableFilter').append($('#invoiceTable_wrapper .dataTables_filter'));
              $('#invoiceTableInfo').append($('#invoiceTable_wrapper .dataTables_info'));
              $('#invoiceTablePagination').append($('#invoiceTable_wrapper .dataTables_paginate'));
            }
          });

          console.log('Invoice DataTable initialized successfully!');

          // Handle Filter Changes
          $('#filterMonth, #filterYear, #filterStatus').on('change', function () {
            invoiceTable.ajax.reload();
          });
        });

        // Load Statistics
        function loadStatistics() {
          $.ajax({
            url: '/api/corporate/statistics',
            type: 'GET',
            success: function (response) {
              // Update statistics cards
              $('.stats-card').eq(0).find('h4').text(response.total);
              $('.stats-card').eq(1).find('h4').text(response.active);
              $('.stats-card').eq(2).find('h4').text(response.inactive);
              $('.stats-card').eq(3).find('h5').text(response.revenue);
            },
            error: function (xhr, error, code) {
              console.error('Error loading statistics:', error);
            }
          });
        }

        // Modal Functions
        function openModal() {
          const modal = new bootstrap.Modal(document.getElementById('corporateModal'));
          modal.show();
        }

        function closeModal() {
          const modal = bootstrap.Modal.getInstance(document.getElementById('corporateModal'));
          if (modal) {
            modal.hide();
          }
          const form = document.getElementById('formAntrianCorp');
          if (form) form.reset();
        }

        // Edit Button Handler
        jQuery(document).on('click', '.btn-edit', function () {
          const id = $(this).data('id');
          console.log('Edit corporate ID:', id);

          // TODO: Implement edit functionality
          Swal.fire({
            icon: 'info',
            title: 'Edit Corporate',
            text: 'Fitur edit akan segera tersedia'
          });
        });

        // Delete Button Handler
        jQuery(document).on('click', '.btn-delete', function () {
          const id = $(this).data('id');

          Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
          }).then((result) => {
            if (result.isConfirmed) {
              // TODO: Implement delete functionality
              console.log('Delete corporate ID:', id);

              Swal.fire({
                icon: 'info',
                title: 'Hapus Corporate',
                text: 'Fitur hapus akan segera tersedia'
              });
            }
          });
        });

        // Invoice View Button Handler
        jQuery(document).on('click', '.btn-view-invoice', function () {
          const id = $(this).data('id');
          console.log('View invoice ID:', id);

          // TODO: Implement view invoice functionality
          Swal.fire({
            icon: 'info',
            title: 'Lihat Invoice',
            text: 'Fitur lihat invoice akan segera tersedia'
          });
        });

        // Invoice Download Button Handler
        jQuery(document).on('click', '.btn-download-invoice', function () {
          const id = $(this).data('id');
          console.log('Download invoice ID:', id);

          // TODO: Implement download invoice functionality
          Swal.fire({
            icon: 'info',
            title: 'Download Invoice',
            text: 'Fitur download invoice akan segera tersedia'
          });
        });

        // Payment Modal Functions
        function openPaymentModal(id, invoiceNumber, tagihan, tagihanRaw) {
          document.getElementById('payment_invoice_id').value = id;
          document.getElementById('payment_invoice_number').innerText = invoiceNumber;
          document.getElementById('payment_tagihan').innerText = tagihan;

          // Set inputs
          document.getElementById('payment_amount_display').value = tagihan; // Auto fill display
          document.getElementById('payment_amount').value = tagihanRaw; // Auto fill hidden value
          document.getElementById('payment_date').valueAsDate = new Date(); // Default today

          const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
          modal.show();
        }

        function closePaymentModal() {
          const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
          if (modal) modal.hide();
          document.getElementById('formPayment').reset();
        }

        function formatRupiahPayment(el) {
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

          // Simpan nilai angka murni ke input hidden
          let angka_murni = el.value.replace(/[^0-9]/g, "");
          document.getElementById("payment_amount").value = angka_murni;
        }

        // Handle Payment Button Click
        jQuery(document).on('click', '.btn-confirm-payment', function () {
          const id = $(this).data('id');
          const invoiceNumber = $(this).data('invoice');

          let tagihanRaw = $(this).data('tagihan-raw');
          let tagihanFormatted = $(this).data('tagihan');

          // Fallback if data attributes are missing (e.g. for existing rendered rows?)
          if (tagihanRaw === undefined) {
            const rowData = invoiceTable.row($(this).parents('tr')).data();
            tagihanRaw = rowData ? rowData.tagihan : 0;
            tagihanFormatted = rowData ? rowData.tagihan_formatted : 'Rp 0';
          }

          openPaymentModal(id, invoiceNumber, tagihanFormatted, tagihanRaw);
        });

        // Handle Payment Submission
        function submitPayment(e) {
          e.preventDefault();

          const form = document.getElementById('formPayment');
          const formData = new FormData(form);
          const id = document.getElementById('payment_invoice_id').value;

          // Show loading
          Swal.fire({
            title: 'Memproses...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
          });

          $.ajax({
            url: `/api/corporate/invoice/${id}/confirm-payment`,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
              closePaymentModal();
              Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: response.message,
                timer: 2000,
                showConfirmButton: false
              });
              invoiceTable.ajax.reload(null, false);
              loadStatistics();
            },
            error: function (xhr) {
              let msg = 'Terjadi kesalahan saat menyimpan pembayaran';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
              }
              Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: msg
              });
            }
          });
        }



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

          // Simpan nilai angka murni ke input hidden
          let angka_murni = el.value.replace(/[^0-9]/g, "");
          document.getElementById("harga_real").value = angka_murni;
        }
      </script>
    @endsection