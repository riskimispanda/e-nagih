@extends('layouts.contentNavbarLayout')

@section('title', 'Proses Installasi Corp')

@section('vendor-style')
  <style>
    /* Modern Card Styling */
    .card {
      border: none;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
    }

    .card:hover {
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    /* Header Styling */
    .card-header {
      background-color: #f8f9fa;
      border-bottom: 1px solid #e9ecef;
    }

    .card-title {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.25rem;
    }

    .card-subtitle {
      color: #6c757d;
      font-size: 0.875rem;
      margin-bottom: 0;
    }

    /* Form Label Styling */
    .form-label {
      font-weight: 500;
      color: #495057;
      margin-bottom: 0.5rem;
      font-size: 0.875rem;
    }

    /* Input Group Styling */
    .input-group-text {
      background-color: #f8f9fa;
      border-color: #dee2e6;
      color: #6c757d;
    }

    .form-control:read-only {
      background-color: #f8f9fa;
      cursor: not-allowed;
    }

    /* Badge Styling */
    .badge {
      padding: 0.35rem 0.65rem;
      font-weight: 500;
      font-size: 0.75rem;
    }

    /* Button Styling */
    .btn-sm {
      padding: 0.5rem 1rem;
      font-weight: 500;
    }

    /* Breadcrumb Styling */
    .breadcrumb {
      background-color: transparent;
      padding: 0.75rem 0;
      margin-bottom: 1.5rem;
    }

    .breadcrumb-item+.breadcrumb-item::before {
      color: #6c757d;
    }

    /* Section Spacing */
    .row {
      margin-bottom: 1.5rem;
    }

    .row:last-child {
      margin-bottom: 0;
    }

    /* File Input Styling */
    input[type="file"].form-control {
      padding: 0.375rem 0.75rem;
    }

    /* Link Button Styling */
    .btn-secondary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 40px;
      height: 38px;
    }

    /* Card Footer */
    .card-footer {
      background-color: #f8f9fa;
      border-top: 1px solid #e9ecef;
      padding: 1rem 1.5rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .mb-6 {
        margin-bottom: 1rem !important;
      }
    }
  </style>
@endsection

@section('content')

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="/dashboard">Dashboard</a>
      </li>
      <li class="breadcrumb-item">
        <a href="/teknisi/antrian">Antrian</a>
      </li>
      <li class="breadcrumb-item active">Proses</li>
    </ol>
  </nav>

  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex align-items-center">
            <i class="bx bx-buildings me-2 fs-4 text-primary"></i>
            <div>
              <h4 class="card-title mb-0">Proses Installasi Perusahaan</h4>
              <p class="card-subtitle mb-0">Halaman Proses Installasi</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xl">
      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Informasi Perusahaan</h5>
          <small class="badge bg-danger bg-opacity-10 text-danger float-end">Priority</small>
        </div>
        <hr class="my-1">
        <div class="card-body">
          <div class="row">
            <div class="col-sm-6 mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Nama Perusahaan</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="bx bx-buildings"></i></span>
                <input type="text" class="form-control" value="{{ $corp->nama_perusahaan }}" readonly>
              </div>
            </div>
            <div class="col-sm-6 mb-6">
              <label class="form-label" for="basic-icon-default-company">Nama PIC</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-company2" class="input-group-text"><i class="bx bx-user"></i></span>
                <input type="text" class="form-control" value="{{ $corp->nama_pic }}" readonly>
              </div>
            </div>
            <div class="col-sm-6 mb-6">
              <label class="form-label" for="basic-icon-default-email">Nomor Hp</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                <input type="text" class="nomor-hp form-control" value="{{ $corp->no_hp }}" readonly>
              </div>
            </div>
            <div class="col-sm-6 mb-6">
              <label class="form-label">Admin</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-user"></i></span>
                <input type="text" class="form-control" value="{{ $corp->usr->name }}" readonly>
              </div>
            </div>
            <div class="col-sm-12 mb-6">
              <label class="form-label" for="basic-icon-default-message">Alamat</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-message2" class="input-group-text"><i class="bx bx-compass"></i></span>
                <input type="text" class="form-control" value="{{ $corp->alamat }}" readonly>
              </div>
            </div>
            <div class="col-sm-6 mb-6">
              <label class="form-label">Foto Identitas</label>
              <div>
                <a href="{{ asset($corp->foto) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                  <i class="bx bx-image me-1"></i> Lihat Foto
                </a>
              </div>
            </div>
            <div class="col-sm-6 mb-6">
              <label class="form-label">Lokasi / GPS</label>
              <div>
                <a href="{{ $corp->gps }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                  <i class="bx bx-map me-1"></i> Buka Maps
                </a>
              </div>
            </div>
            <div class="col-sm-12 mb-6">
              <label class="form-label">Tanggal Registrasi</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                <input type="date" class="form-control" value="{{ $corp->tanggal }}" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl">
      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Konfigurasi Jaringan</h5>
          <small class="badge bg-danger bg-opacity-10 text-danger float-end">Priority</small>
        </div>
        <hr class="my-1">
        <div class="card-body">
          <div class="row">
            <div class="col-sm-12 mb-6">
              <label class="form-label">Speed Internet</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-tachometer"></i></span>
                <input type="text" class="form-control" value="{{ $corp->speed }}" readonly>
              </div>
            </div>
            <div class="col-sm-12 mb-6">
              <label class="form-label">Paket Langganan</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-package"></i></span>
                <input type="text" class="form-control" value="{{ $corp->paket }}" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xl">
      <div class="card">
        <div class="card-header">
          <small class="badge bg-danger bg-opacity-10 text-danger float-end">Priority</small>
          <h5 class="card-title">Konfirmasi Installasi</h5>
          <p class="card-subtitle">Konfigurasi Jaringan Di Pelanggan</p>
        </div>
        <hr class="my-1">
        <form action="/confirm/corp/{{ $corp->id }}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="card-body">
            <div class="row">
              <div class="col-sm-6 mb-4">
                <label class="form-label">Foto Tempat</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-image"></i></span>
                  <input type="file" class="form-control" name="foto_tempat">
                </div>
              </div>
              <div class="col-sm-6 mb-4">
                <label class="form-label">Foto Perangkat</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-image"></i></span>
                  <input type="file" class="form-control" name="foto_perangkat">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-4 mb-4">
                <label class="form-label">Nama Perangkat</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-devices"></i></span>
                  <input type="text" class="form-control" name="perangkat" placeholder="Router">
                </div>
              </div>
              <div class="col-sm-4 mb-4">
                <label class="form-label">Seri Perangkat</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-devices"></i></span>
                  <input type="text" class="form-control" name="seri" placeholder="1234657bhs">
                </div>
              </div>
              <div class="col-sm-4 mb-4">
                <label class="form-label">Mac Address</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-devices"></i></span>
                  <input type="text" class="form-control" name="mac" placeholder="XX:XX:XX:XX:XX:XX">
                </div>
              </div>
              <div class="col-sm-6 mb-4">
                <label class="form-label">Router</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-cable-car"></i></span>
                  <select name="router" class="form-select" id="router">
                    <option value="">Pilih Router</option>
                    @foreach ($router as $item)
                      <option value="{{ $item->id }}">{{ $item->nama_router }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
          <hr class="my-1">
          <div class="card-footer d-flex gap-2 float-end">
            <button type="button" class="btn btn-secondary btn-sm"
              onclick="window.location.href='/teknisi/antrian'">Kembali</button>
            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Devices
    new TomSelect('#router', {
      create: false,
      sortField: {
        field: "text",
        direction: "asc"
      }
    });
  </script>


@endsection
