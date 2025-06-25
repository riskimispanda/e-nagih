@extends('layouts.contentNavbarLayout')

@section('title', 'Transaksi Kas Kecil')

<style>
  .revenue-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      border: 1px solid #e9ecef;
      transition: all 0.3s ease;
  }

  .revenue-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  }

  .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
  }

  .search-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      border: 1px solid #e9ecef;
  }

  .table-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      border: 1px solid #e9ecef;
      overflow: hidden;
  }

  .search-input {
      border-radius: 8px;
      border: 1px solid #d0d7de;
      padding: 0.75rem 1rem 0.75rem 2.5rem;
      font-size: 0.875rem;
      transition: all 0.2s ease;
  }

  .search-input:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
      outline: none;
  }

  .btn-modern {
      border-radius: 8px;
      font-weight: 500;
      padding: 0.75rem 1.5rem;
      font-size: 0.875rem;
      transition: all 0.2s ease;
      border: none;
  }

  .btn-modern:hover {
      transform: translateY(-1px);
  }

  .status-badge {
      padding: 0.375rem 0.75rem;
      border-radius: 8px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
  }

  .action-btn {
      padding: 0.375rem 0.75rem;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 500;
      border: none;
      transition: all 0.2s ease;
  }

  .action-btn:hover {
      transform: translateY(-1px);
  }

  .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
  }

  .loading-content {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      display: flex;
      align-items: center;
      gap: 1rem;
  }

  .spinner {
      width: 24px;
      height: 24px;
      border: 2px solid #f3f3f3;
      border-top: 2px solid #0d6efd;
      border-radius: 50%;
      animation: spin 1s linear infinite;
  }

  @keyframes spin {
      0% {
          transform: rotate(0deg);
      }

      100% {
          transform: rotate(360deg);
      }
  }

  .table-responsive {
      border-radius: 0;
  }

  .table th {
      background-color: #f8f9fa;
      border-bottom: 2px solid #dee2e6;
      font-weight: 600;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #6c757d;
      padding: 1rem 1.5rem;
  }

  .table td {
      padding: 1rem 1.5rem;
      vertical-align: middle;
      border-bottom: 1px solid #f1f3f4;
  }

  .table tbody tr:hover {
      background-color: #f8f9fa;
  }

  .customer-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
  }
</style>

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
      <a href="/kas" class="text-decoration-none">Data Kas</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Transaksi Kas Kecil</li>
  </ol>
</nav>

<div class="row">
  <div class="col-12">
    <div class="card-header mb-4">
      <h4 class="card-title fw-bold">Transaksi Kas Kecil</h4>
      <small class="text-muted">Record Transaksi Kas Kecil</small>
    </div>

    <div class="row g-4 mb-4">
      <!-- Total Revenue -->
      <div class="col-12 col-sm-6 col-lg-4">
          <div class="revenue-card p-4">
              <div class="d-flex justify-content-between align-items-center">
                  <div>
                      <p class="text-muted small mb-1 fw-medium">Total Kas Kecil</p>
                      <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($jumlah, 0, ',', '.') }}</h5>
                  </div>
                  <div class="stat-icon bg-success bg-opacity-10 text-success">
                      <i class="bx bx-trending-up"></i>
                  </div>
              </div>
          </div>
      </div>

      <!-- Monthly Revenue -->
      <div class="col-12 col-sm-6 col-lg-4">
          <a data-bs-toggle="tooltip" title="Sisa Kas Kecil Bulan Lalu" data-bs-placement="bottom">
              <div class="revenue-card p-4">
                  <div class="d-flex justify-content-between align-items-center">
                      <div>
                          <p class="text-muted small mb-1 fw-medium">Sisa Kas Kecil</p>
                          <h5 class="fw-bold text-dark mb-0">{{ $sisa ?? 'Rp 0' }}</h5>
                      </div>
                      <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                          <i class="bx bx-calendar"></i>
                      </div>
                  </div>
              </div>
          </a>
      </div>

      <!-- Total Invoices -->
      <div class="col-12 col-sm-6 col-lg-4">
          <div class="revenue-card p-4">
              <div class="d-flex justify-content-between align-items-center">
                  <div>
                      <p class="text-muted small mb-1 fw-medium">Total Transaksi</p>
                      <h5 class="fw-bold text-dark mb-0">{{$transaksi}}</h5>
                  </div>
                  <div class="stat-icon bg-info bg-opacity-10 text-info">
                      <i class="bx bx-receipt"></i>
                  </div>
              </div>
          </div>
      </div>
  </div>
    
    <div class="card">
      <div class="card-body">
        <div class="row mb-5">
          <div class="col-sm-4">
            <div class="form-group">
              <label class="form-label mb-2">Tanggal</label>
              <input type="date" class="form-control">
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label class="form-label mb-2">Search</label>
              <input type="text" class="form-control" placeholder="Cari Transaksi">
            </div>
          </div>
        </div>
        
        <hr>
        
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-dark text-center">
              <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Admin</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody class="text-center">
              @forelse ($kas as $item)
              <tr>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_kas)->translatedFormat('d F Y') }}</td>
                <td>
                  <span class="badge bg-warning bg-opacity-10 text-warning">
                    {{ $item->keterangan }}
                  </span>
                </td>
                <td>Rp. {{ number_format($item->debit, 0, ',', '.') }}</td>
                <td>Rp. {{ number_format($item->kredit, 0, ',', '.') }}</td>
                <td>
                  <div class="d-flex flex-column align-items-center">
                    <span class="badge bg-danger bg-opacity-10 text-danger mb-1">
                      <strong>{{ strtoupper(optional($item->user)->name ?? '-') }}</strong>
                    </span>
                    <small class="text-muted">{{ $item->user->roles->name ?? '-' }}</small>
                  </div>
                </td>
                <td>
                  <a href="#" class="btn btn-info btn-sm mb-2" data-bs-toggle="tooltip" title="Detail" data-bs-placement="bottom">
                    <i class="bx bx-info-circle"></i>
                  </a>
                  <a href="#" class="btn btn-warning btn-sm mb-2" data-bs-toggle="tooltip" title="Edit" data-bs-placement="bottom">
                    <i class="bx bx-edit"></i>
                  </a>
                  <a href="#" class="btn btn-danger btn-sm mb-2" data-bs-toggle="tooltip" title="Hapus" data-bs-placement="bottom">
                    <i class="bx bx-trash"></i>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center py-5">
                  <div class="d-flex flex-column align-items-center">
                    <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                    <p class="text-muted mb-0">Belum ada Transaksi</p>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="mt-5">
          {{ $kas->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection