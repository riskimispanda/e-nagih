@extends('layouts/contentNavbarLayout')
@section('title', 'History Pembayaran Corporate')
@section('vendor-style')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <style>
    /* Soft UI Styling */
    .card-soft {
      border: 1px solid rgba(0, 0, 0, 0.05);
      border-radius: 1rem;
      box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.03);
      background-color: #fff;
      transition: all 0.3s ease;
    }

    .card-soft:hover {
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
    }

    .form-select-soft,
    .form-control-soft {
      border: 1px solid #f0f2f4;
      background-color: #f8f9fa;
      border-radius: 0.5rem;
      padding: 0.6rem 1rem;
    }

    .form-select-soft:focus,
    .form-control-soft:focus {
      border-color: #696cff;
      box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.1);
      background-color: #fff;
    }

    .table-soft thead th {
      background-color: #fafbfc;
      border-bottom: 2px solid #f0f2f4;
      color: #566a7f;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.5px;
    }

    .table-soft tbody td {
      vertical-align: middle;
      border-bottom: 1px solid #f4f6f8;
      color: #697a8d;
      padding: 1rem 0.75rem;
    }

    .badge-soft-success {
      background-color: rgba(113, 221, 55, 0.1);
      color: #71dd37;
      border: 1px solid rgba(113, 221, 55, 0.2);
    }
  </style>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card card-soft">
        <div
          class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom-0 pb-0 pt-4 px-4">
          <h4 class="fw-bold mb-0 text-primary">
            <i class="bx bx-history me-2"></i>History Pembayaran
          </h4>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="/data/corporate">Data Corporate</a></li>
              <li class="breadcrumb-item active">History Pembayaran</li>
            </ol>
          </nav>
        </div>

        <div class="card-body p-4">
          <!-- Filters -->
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <label class="form-label text-muted small fw-bold text-uppercase">Bulan</label>
              <select id="filterMonth" class="form-select form-select-soft">
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
            <div class="col-md-3">
              <label class="form-label text-muted small fw-bold text-uppercase">Tahun</label>
              <select id="filterYear" class="form-select form-select-soft">
                <option value="">Semua Tahun</option>
              </select>
            </div>
            <div class="col-md-6 d-flex align-items-end justify-content-end">
              <!-- Spacer or additional actions -->
            </div>
          </div>

          <!-- Table -->
          <div class="card-datatable text-nowrap">
            <table id="paymentTable" class="table table-soft table-hover w-100">
              <thead>
                <tr>
                  <th class="text-center" width="5%">No</th>
                  <th>Periode Tagihan</th>
                  <th>Nama Perusahaan</th>
                  <th>Tanggal Bayar</th>
                  <th class="text-end">Jumlah Bayar</th>
                  <th class="text-center">Metode</th>
                  <th>Keterangan</th>
                  <th class="text-center">Bukti</th>
                </tr>
              </thead>
              <tbody>
                <!-- Data from Server -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <!-- Responsive Extension -->
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
  <script>
    jQuery(document).ready(function ($) {
      // Populate Years
      let currentYear = new Date().getFullYear();
      for (let y = currentYear; y >= 2023; y--) {
        $('#filterYear').append(new Option(y, y));
      }

      // Set Default Filters
      $('#filterMonth').val(new Date().getMonth() + 1);
      $('#filterYear').val(currentYear);

      // Init DataTable
      let table = $('#paymentTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: {
          details: {
            type: 'column',
            renderer: function (api, rowIdx, columns) {
              var data = $.map(columns, function (col, i) {
                return col.hidden ?
                  '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                  '<td class="fw-bold py-2" width="30%">' + col.title + '</td> ' +
                  '<td class="py-2">' + col.data + '</td>' +
                  '</tr>' :
                  '';
              }).join('');

              return data ?
                $('<div class="p-3 bg-light rounded"><table class="table table-sm table-borderless mb-0"/></div>').find('table').append(data).end() :
                false;
            }
          }
        },
        ajax: {
          url: "{{ route('api.corporate.payment-history') }}",
          data: function (d) {
            d.month = $('#filterMonth').val();
            d.year = $('#filterYear').val();
          }
        },
        columns: [
          { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'control text-center', responsivePriority: 1 },
          { data: 'invoice_number', name: 'invoice_number', responsivePriority: 3 },
          { data: 'nama_perusahaan', name: 'id', responsivePriority: 1 },
          { data: 'tanggal_bayar', name: 'tanggal_bayar', responsivePriority: 2 },
          { data: 'jumlah_bayar_formatted', name: 'jumlah_bayar', className: 'fw-bold text-dark', responsivePriority: 1 },
          { data: 'metode_bayar', name: 'metode_bayar', responsivePriority: 4 },
          {
            data: 'keterangan', name: 'keterangan', responsivePriority: 5, render: function (data) {
              return data ? `<span class="text-muted fst-italic small">${data}</span>` : '-';
            }
          },
          {
            data: 'bukti_bayar', name: 'bukti_bayar', orderable: false, searchable: false, className: 'text-center', responsivePriority: 2,
            render: function (data) {
              if (data) {
                return `<a href="/${data}" target="_blank" class="btn btn-sm btn-icon btn-label-secondary rounded-pill"><i class="bx bx-image-alt"></i></a>`;
              }
              return '-';
            }
          }
        ],
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        dom: '<"row mx-2"<"col-md-6"l><"col-md-6"f>>t<"row mx-2"<"col-md-6"i><"col-md-6"p>>',
        lengthMenu: [10, 25, 50, 100],
        order: [[3, 'desc']]
      });

      // Filter Trigger
      $('#filterMonth, #filterYear').change(function () {
        table.ajax.reload();
      });
    });
  </script>
@endsection