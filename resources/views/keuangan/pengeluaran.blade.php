@extends('layouts.contentNavbarLayout')

@section('title', 'Pengeluaran Global')
<style>
  /* Modal Responsive Styles */
  .modal-content {
    border: none;
    box-shadow: 0 0.25rem 1.5rem rgba(0, 0, 0, 0.15);
    border-radius: 0.75rem;
    overflow: hidden;
    animation: modalFadeIn 0.3s ease;
  }

  @keyframes modalFadeIn {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .modal-dialog {
    margin: 1rem auto;
    transition: all 0.3s ease;
  }

  .modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1.25rem 1.5rem;
  }

  .modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
  }

  .modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1rem 1.5rem;
  }

  /* Mobile Responsive */
  @media (max-width: 576px) {
    .modal-dialog {
      margin: 0.5rem;
      max-width: calc(100% - 1rem);
    }

    .modal-content {
      border-radius: 0.5rem;
    }

    .modal-header,
    .modal-footer {
      padding: 1rem;
    }

    .modal-body {
      padding: 1rem;
      max-height: calc(100vh - 150px);
    }

    .modal-title {
      font-size: 1.1rem;
    }

    .btn {
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
    }
  }

  /* Form Enhancements */
  .form-label {
    color: #566a7f;
    font-weight: 500;
    margin-bottom: 0.5rem;
  }

  .form-control,
  .form-select {
    border: 1px solid #d9dee3;
    border-radius: 0.375rem;
    padding: 0.625rem 0.875rem;
    font-size: 0.9375rem;
    transition: all 0.2s ease;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: #696cff;
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
  }

  .input-group-text {
    background-color: #f5f5f9;
    border: 1px solid #d9dee3;
    color: #566a7f;
    font-weight: 500;
  }

  .avatar {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Button Styles */
  .btn-primary {
    background-color: #696cff;
    border-color: #696cff;
    box-shadow: 0 0.125rem 0.25rem rgba(105, 108, 255, 0.4);
  }

  .btn-primary:hover {
    background-color: #5f61e6;
    border-color: #5f61e6;
    transform: translateY(-1px);
    box-shadow: 0 0.25rem 0.5rem rgba(105, 108, 255, 0.4);
  }

  .btn-outline-secondary {
    color: #8592a3;
    border-color: #8592a3;
  }

  .btn-outline-secondary:hover {
    background-color: #8592a3;
    border-color: #8592a3;
    color: #fff;
  }

  /* Scrollbar Styling */
  .modal-body::-webkit-scrollbar {
    width: 6px;
  }

  .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }

  .modal-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
  }

  .modal-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }
</style>
@section('content')
  <!-- Tailwind CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
      <h4 class="text-lg font-bold text-gray-800 m-0">Data Pengeluaran</h4>
      <p class="text-sm text-gray-500 m-0 mt-1">Kelola dan pantau data pengeluaran perusahaan</p>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">

          <!-- Filter -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1.5">Filter Bulan</label>
              @php
                $selectedMonth = request('month', date('n'));
              @endphp
              <select name="month" id="monthFilter" class="form-select w-full">
                <option value="all" {{ $selectedMonth == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                <option value="1" {{ $selectedMonth == '1' ? 'selected' : '' }}>Januari</option>
                <option value="2" {{ $selectedMonth == '2' ? 'selected' : '' }}>Februari</option>
                <option value="3" {{ $selectedMonth == '3' ? 'selected' : '' }}>Maret</option>
                <option value="4" {{ $selectedMonth == '4' ? 'selected' : '' }}>April</option>
                <option value="5" {{ $selectedMonth == '5' ? 'selected' : '' }}>Mei</option>
                <option value="6" {{ $selectedMonth == '6' ? 'selected' : '' }}>Juni</option>
                <option value="7" {{ $selectedMonth == '7' ? 'selected' : '' }}>Juli</option>
                <option value="8" {{ $selectedMonth == '8' ? 'selected' : '' }}>Agustus</option>
                <option value="9" {{ $selectedMonth == '9' ? 'selected' : '' }}>September</option>
                <option value="10" {{ $selectedMonth == '10' ? 'selected' : '' }}>Oktober</option>
                <option value="11" {{ $selectedMonth == '11' ? 'selected' : '' }}>November</option>
                <option value="12" {{ $selectedMonth == '12' ? 'selected' : '' }}>Desember</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1.5">Filter Tahun</label>
              <select name="year" id="yearFilter" class="form-select w-full">
                @php
                  $currentYear = date('Y');
                  $selectedYear = request('year', $currentYear);
                @endphp
                @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                  <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
              </select>
            </div>
            <div>
              <label for="kategoriFilter" class="block text-sm font-medium text-gray-600 mb-1.5">Kategori</label>
              <select class="form-select w-full" id="kategoriFilter">
                <option value="" selected>Semua Kategori</option>
                @foreach ($kategoriPengeluaran as $kategori)
                  <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>
                    {{ ucfirst($kategori) }}
                  </option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="searchInput" class="block text-sm font-medium text-gray-600 mb-1.5">Search</label>
              <input type="text" class="form-control w-full" id="searchInput" placeholder="Cari...">
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
              <a href="#" id="exportFilterBtn" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium rounded-lg bg-red-500 hover:bg-red-600 text-white transition">
                <i class="bx bx-export"></i> Export Sesuai Filter
              </a>
            </div>
          </div>
        </div>

          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
              <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 text-blue-600 text-2xl shrink-0">
                <i class="bx bx-money"></i>
              </div>
              <div class="min-w-0">
                <p class="text-sm text-gray-500 m-0">Total Saldo</p>
                <h3 id="totalSaldo" class="text-xl font-bold text-gray-900 m-0 truncate">Rp {{ number_format($total, 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-400 m-0 mt-0.5">Total saldo bersih (Pemasukan - Pengeluaran)</p>
              </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
              <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 text-2xl shrink-0">
                <i class="bx bx-calendar"></i>
              </div>
              <div class="min-w-0">
                <p id="saldoBulanIniLabel" class="text-sm text-gray-500 m-0">{{ $saldoLabel }}</p>
                <h3 id="saldoBulanIni" class="text-xl font-bold text-gray-900 m-0 truncate">Rp {{ number_format($saldoBulanIni, 0, ',', '.') }}</h3>
                <p id="saldoBulanIniSub" class="text-xs text-gray-400 m-0 mt-0.5">{{ $saldoSub }}</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
              <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 text-2xl shrink-0">
                <i class="bx bx-wallet"></i>
              </div>
              <div class="min-w-0">
                <p class="text-sm text-gray-500 m-0">Total Pengeluaran</p>
                <h3 id="totalPengeluaranAll" class="text-xl font-bold text-gray-900 m-0 truncate">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-400 m-0 mt-0.5">Seluruh pengeluaran terkonfirmasi</p>
              </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
              <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-green-50 text-green-600 text-2xl shrink-0">
                <i class="bx bx-calendar"></i>
              </div>
              <div class="min-w-0">
                <p class="text-sm text-gray-500 m-0">Hari Ini</p>
                <h3 id="pengeluaranHariIni" class="text-xl font-bold text-gray-900 m-0 truncate">Rp {{ number_format($dailyPengeluaran, 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-400 m-0 mt-0.5">Pengeluaran hari ini</p>
              </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
              <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-50 text-amber-600 text-2xl shrink-0">
                <i class="bx bx-line-chart"></i>
              </div>
              <div class="min-w-0">
                <p id="pengeluaranBulanIniLabel" class="text-sm text-gray-500 m-0">{{ $pengeluaranLabel }}</p>
                <h3 id="pengeluaranBulanIni" class="text-xl font-bold text-gray-900 m-0 truncate">Rp {{ number_format($monthlyPengeluaran, 0, ',', '.') }}</h3>
                <p id="pengeluaranBulanIniSub" class="text-xs text-gray-400 m-0 mt-0.5">{{ $pengeluaranSub }}</p>
              </div>
            </div>
            <a href="/request/hapus/pengeluaran" data-bs-toggle="tooltip" title="Request Konfirmasi" data-bs-placement="bottom"
              class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:border-red-200 transition">
              <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-red-50 text-red-600 text-2xl shrink-0">
                <i class="bx bx-line-chart"></i>
              </div>
              <div class="min-w-0">
                <p class="text-sm text-gray-500 m-0">Request Konfirmasi</p>
                <h3 id="totalRequest" class="text-xl font-bold text-gray-900 m-0 truncate">{{ $totalRequest }}</h3>
                <p class="text-xs text-gray-400 m-0 mt-0.5">Total Request Hapus Pengeluaran</p>
              </div>
            </a>
          </div>

          <!-- Table Card -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-100">
              <button type="button" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition" data-bs-toggle="modal" data-bs-target="#modalScrollable">
                <i class="bx bx-plus"></i> Tambah
              </button>
              <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500 m-0">Tampilkan:</label>
                <select id="entriesPerPage" class="form-select form-select-sm w-auto">
                  <option value="10" selected>10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
                <span class="text-sm text-gray-500">Entri</span>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm" id="pengeluaranTable">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                  <tr>
                    <th class="px-4 py-3 text-center font-semibold">No</th>
                    <th class="px-4 py-3 text-center font-semibold">Tanggal</th>
                    <th class="px-4 py-3 text-center font-semibold">Jenis Pengeluaran</th>
                    <th class="px-4 py-3 text-center font-semibold">Keterangan</th>
                    <th class="px-4 py-3 text-center font-semibold">Jumlah</th>
                    <th class="px-4 py-3 text-center font-semibold">Jenis Kas</th>
                    <th class="px-4 py-3 text-center font-semibold">Status</th>
                    <th class="px-4 py-3 text-center font-semibold">Admin</th>
                    <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  @forelse ($pengeluarans as $key => $pengeluaran)
                    <tr class="hover:bg-gray-50">
                      <td class="px-4 py-3 text-center text-gray-600">{{ $key + 1 }}</td>
                      <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium">
                          {{ \Carbon\Carbon::parse($pengeluaran->tanggal_pengeluaran)->format('d-M-Y') }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-center text-gray-700">{{ $pengeluaran->jenis_pengeluaran }}</td>
                      <td class="px-4 py-3 text-center text-gray-700">{{ $pengeluaran->keterangan }}</td>
                      <td class="px-4 py-3 text-center" data-amount="{{ $pengeluaran->jumlah_pengeluaran }}">
                        <span class="inline-block px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-medium">
                          Rp {{ number_format($pengeluaran->jumlah_pengeluaran, 0, ',', '.') }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-medium">
                          {{ $pengeluaran->kas->jenis_kas ?? '-'}}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-center">
                        @if ($pengeluaran->status_id == 1)
                          <span class="inline-block px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">Menunggu Konfirmasi Penghapusan</span>
                        @elseif ($pengeluaran->status_id == 2)
                          <span class="inline-block px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium">Approved</span>
                        @elseif ($pengeluaran->status_id == 3)
                          <span class="inline-block px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium">Berhasil</span>
                        @endif
                      </td>
                      <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-xs font-medium">
                          {{ $pengeluaran->user->name }}
                        </span>
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                          <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sky-600 hover:bg-sky-50 transition" data-bs-toggle="modal"
                            data-bs-target="#detailPengeluaranModal" title="Detail" data-bs-placement="bottom"
                            data-tanggal="{{ \Carbon\Carbon::parse($pengeluaran->tanggal_pengeluaran)->format('d-M-Y') }}"
                            data-jenis="{{ $pengeluaran->jenis_pengeluaran }}"
                            data-keterangan="{{ $pengeluaran->keterangan }}"
                            data-jumlah="Rp {{ number_format($pengeluaran->jumlah_pengeluaran, 0, ',', '.') }}"
                            data-kas="{{ $pengeluaran->kas->jenis_kas ?? '-' }}"
                            data-metode="{{ $pengeluaran->metode_bayar }}"
                            data-status="{{ $pengeluaran->status_id == 1 ? 'Menunggu Konfirmasi Penghapusan' : ($pengeluaran->status_id == 2 ? 'Approved' : 'Berhasil') }}"
                            data-admin="{{ $pengeluaran->user->name }}"
                            data-rab="{{ $pengeluaran->rab->kegiatan ?? '-' }}"
                            data-bukti="{{ $pengeluaran->bukti_pengeluaran ? asset('uploads/'.basename($pengeluaran->bukti_pengeluaran)) : '' }}"
                            data-bukti-ext="{{ $pengeluaran->bukti_pengeluaran ? strtolower(pathinfo($pengeluaran->bukti_pengeluaran, PATHINFO_EXTENSION)) : '' }}">
                            <i class="bx bx-show"></i>
                          </button>
                          <a href="/edit-pengeluaran/{{ $pengeluaran->id }}">
                            <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 hover:bg-amber-50 transition" title="Edit" data-bs-toggle="tooltip" data-bs-placement="bottom">
                              <i class="bx bx-edit"></i>
                            </button>
                          </a>
                          <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 transition" data-bs-toggle="modal"
                            data-bs-target="#deletePengeluaranModal" data-id="{{ $pengeluaran->id }}" title="Hapus"
                            data-bs-placement="bottom">
                            <i class="bx bx-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="9" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center">
                          <i class="bx bx-receipt text-gray-300" style="font-size: 3rem;"></i>
                          <h5 class="text-gray-700 mt-3 mb-2 m-0">Tidak ada data</h5>
                          <p class="text-gray-400 m-0">Belum ada Transaksi</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-t border-gray-100">
              <div id="customPaginationInfo" class="text-sm text-gray-500">
                Menampilkan {{ $pengeluarans->count() }} dari {{ $pengeluarans->total() }} records
              </div>
              <div class="pagination-container">
                {{ $pengeluarans->links('pagination::bootstrap-5') }}
              </div>
            </div>
          </div>
  </div>

  {{-- Modal Add Pengeluaran --}}
  <div class="modal fade" id="modalScrollable" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content border-0 bg-white shadow-2xl rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h5 class="text-lg font-semibold text-gray-800" id="modalScrollableTitle">Tambah Pengeluaran</h5>
          <button type="button" class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition" data-bs-dismiss="modal" aria-label="Close">
            <i class="bx bx-x text-2xl leading-none"></i>
          </button>
        </div>
        <form action="/pengeluaran/tambah" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1.5">Rencana Anggaran Biaya</label>
                <select name="rab_id" id="select-rab" class="form-select">
                  <option value="">Pilih RAB</option>
                  @foreach ($rab as $item)
                    @php
                      $bulan = \Carbon\Carbon::create()->month((int) $item->bulan)->locale('id')->translatedFormat('F');
                    @endphp
                    <option value="{{ $item->id }}" data-anggaran="{{ $item->jumlah_anggaran }}">
                      {{ $item->kegiatan }}
                      {{ $item->item ? "({$item->item} item" : '' }}
                      {{ $item->item && $item->keterangan ? ' | ' : '' }}
                      {{ $item->keterangan ? "Ket: {$item->keterangan}" : '' }}
                      {{ $item->item ? ')' : '' }}
                      {{ $item->tahun_anggaran ? " | Tahun: {$item->tahun_anggaran}" : '' }}
                      {{ $item->bulan ? ' | Bulan: ' . $bulan : '' }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div>
                <label for="kasSelect" class="block text-sm font-medium text-gray-600 mb-1.5">Jenis Kas</label>
                <select name="kas_id" id="kasSelect" class="form-select">
                  <option value="" selected disabled>Pilih Jenis Kas</option>
                  @foreach ($kas as $item)
                    <option value="{{ $item->id }}">{{$item->jenis_kas}}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="mb-4" id="jumlah-item-group" style="display: none;">
                <label class="block text-sm font-medium text-gray-600 mb-1.5">
                  <i class="bx bx-cart me-1"></i>Jumlah Item
                </label>
                <input type="number" name="item" class="form-control" placeholder="100">
              </div>
              <div class="mb-4" id="anggaran-info" style="display: none;">
                <label class="block text-sm font-medium text-gray-600 mb-1.5">
                  <i class="bx bx-money me-1"></i>Anggaran RAB
                </label>
                <input type="text" id="anggaran-amount" class="form-control" value="Rp 0" readonly>
              </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="tanggalPengeluaran" class="block text-sm font-medium text-gray-600 mb-1.5">Tanggal</label>
                <input type="date" class="form-control" id="tanggalPengeluaran" required name="tanggalPengeluaran">
              </div>
              <div>
                <label for="jenisPengeluaran" class="block text-sm font-medium text-gray-600 mb-1.5">Jenis Pengeluaran</label>
                <input name="jenisPengeluaran" type="text" class="form-control" id="jenisPengeluaran"
                  placeholder="Contoh: Operasional, Gaji, Lainnya" required>
              </div>
            </div>
            <div>
              <label for="keterangan" class="block text-sm font-medium text-gray-600 mb-1.5">Keterangan</label>
              <textarea name="keterangan" class="form-control" id="keterangan" rows="3"
                placeholder="Masukkan keterangan pengeluaran..." required></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="jumlahPengeluaran" class="block text-sm font-medium text-gray-600 mb-1.5">Jumlah</label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="text" class="form-control" required placeholder="Masukkan jumlah pengeluaran"
                    id="jumlahPengeluaran" oninput="formatRupiah(this)">
                </div>
                <input name="jumlahPengeluaran" type="text" class="form-control mt-1" id="jumlahPengeluaranNumeric"
                  readonly placeholder="0" hidden>
              </div>
              <div>
                <label for="metodePengeluaran" class="block text-sm font-medium text-gray-600 mb-1.5">Metode Pengeluaran</label>
                <select class="form-select" id="metodePengeluaran" required name="metodePengeluaran">
                  <option selected disabled>Pilih Metode</option>
                  @foreach ($metodes as $metode)
                    <option value="{{ $metode->nama_metode }}">{{ $metode->nama_metode }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div>
              <label for="buktiPengeluaran" class="block text-sm font-medium text-gray-600 mb-1.5">Bukti Pengeluaran</label>
              <input name="buktiPengeluaran" type="file" class="form-control" id="buktiPengeluaran"
                accept=".jpg,.jpeg,.png,.pdf">
              <div class="form-text text-muted">
                <i class="bx bx-info-circle me-1"></i>
                  Format file: JPG, PNG, PDF. Maksimal ukuran 2MB.
              </div>
            </div>
          </div>
          <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100">
            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
<script>
  function formatRupiah(input) {
    let value = input.value.replace(/\D/g, '');
    const numericInput = document.getElementById('jumlahPengeluaranNumeric');
    if (numericInput) {
      numericInput.value = value;
    }
    if (value !== '') {
      value = parseInt(value);
      input.value = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(value);
    } else {
      if (numericInput) {
        numericInput.value = '';
      }
    }
  }
</script>

<script>
  // Kompres gambar bukti di sisi klien sebelum diupload agar selalu di bawah batas upload server
  (function () {
    var input = document.getElementById('buktiPengeluaran');
    if (!input) return;

    input.addEventListener('change', function (e) {
      var file = e.target.files && e.target.files[0];
      if (!file) return;

      var type = file.type;
      // Hanya kompres gambar (bukan PDF); abaikan format yang tidak bisa dibaca canvas
      if (!type.startsWith('image/') || type === 'image/heic' || type === 'image/heif') return;

      var reader = new FileReader();
      reader.onload = function (ev) {
        var img = new Image();
        img.onload = function () {
          var maxDim = 1280;
          var width = img.width;
          var height = img.height;
          if (width > height && width > maxDim) {
            height = Math.round(height * maxDim / width);
            width = maxDim;
          } else if (height > maxDim) {
            width = Math.round(width * maxDim / height);
            height = maxDim;
          }

          var canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;
          canvas.getContext('2d').drawImage(img, 0, 0, width, height);

          canvas.toBlob(function (blob) {
            if (!blob) return;
            var baseName = (file.name || 'bukti').replace(/\.[^.]+$/, '');
            var newFile = new File([blob], baseName + '.jpg', { type: 'image/jpeg' });
            var dt = new DataTransfer();
            dt.items.add(newFile);
            e.target.files = dt.files;
          }, 'image/jpeg', 0.7);
        };
        img.src = ev.target.result;
      };
      reader.readAsDataURL(file);
    });
  })();
</script>

{{-- Modal Request Hapus Pengeluaran (Single Generic Modal) --}}
<div class="modal fade" id="deletePengeluaranModal" tabindex="-1" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-white">
        <h5 class="modal-title" id="modalScrollableTitle"><i class="bx bx-trash me-1 text-danger"></i>Hapus
          Pengeluaran
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="deletePengeluaranForm" action="" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body border-bottom border-top mt-2 mb-2">
          <div class="row mb-3">
            <div class="col-sm-12">
              <label class="form-label fw-medium">Alasan<span class="text-danger">*</span></label>
              <textarea name="alasan" class="form-control" id="alasan" rows="3"
                placeholder="Masukkan alasan ingin menghapus pengeluaran..." required></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer gap-2 mt-6">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger btn-sm">Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal Detail Pengeluaran --}}
<div class="modal fade" id="detailPengeluaranModal" tabindex="-1" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 bg-white shadow-2xl rounded-2xl overflow-hidden">
      <div class="flex items-center px-6 py-4 border-b border-gray-100">
        <h5 class="text-lg font-semibold text-gray-800 flex items-center gap-2 m-0">
          <i class="bx bx-receipt text-info text-xl"></i> Detail Pengeluaran
        </h5>
      </div>
      <div class="px-6 py-5">
        {{-- Summary --}}
        <div class="flex flex-wrap items-center justify-between gap-3 bg-gray-50 rounded-xl px-5 py-4 mb-5">
          <div>
            <p class="text-sm text-gray-500 m-0">Jumlah Pengeluaran</p>
            <h4 id="detailJumlah" class="text-2xl font-bold text-gray-900 m-0">-</h4>
          </div>
          <span id="detailStatus" class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">-</span>
        </div>

        {{-- Detail list --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-400 mb-1">Tanggal</p>
            <p id="detailTanggal" class="font-semibold text-gray-800 m-0">-</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-400 mb-1">Jenis Pengeluaran</p>
            <p id="detailJenis" class="font-semibold text-gray-800 m-0">-</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-400 mb-1">Jenis Kas</p>
            <p id="detailKas" class="font-semibold text-gray-800 m-0">-</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-400 mb-1">Metode Bayar</p>
            <p id="detailMetode" class="font-semibold text-gray-800 m-0">-</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-400 mb-1">Admin</p>
            <p id="detailAdmin" class="font-semibold text-gray-800 m-0">-</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-400 mb-1">RAB</p>
            <p id="detailRab" class="font-semibold text-gray-800 m-0">-</p>
          </div>
          <div class="col-span-1 md:col-span-2">
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-400 mb-1">Keterangan</p>
            <p id="detailKeterangan" class="font-semibold text-gray-800 m-0 whitespace-pre-wrap">-</p>
          </div>
        </div>

        <hr class="my-5 border-gray-100">

        {{-- Bukti --}}
        <p class="text-xs uppercase tracking-wide font-semibold text-gray-400 mb-2">Bukti Pengeluaran</p>
        <div id="detailBukti" class="border border-gray-200 rounded-xl p-4 bg-gray-50 text-center"></div>
      </div>
      <div class="flex justify-end px-6 py-4 border-t border-gray-100">
        <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Event delegation for delete buttons
    // Since buttons might be loaded via AJAX, we bind the click event to a parent or use the modal show event
    var deleteModal = document.getElementById('deletePengeluaranModal');
    if (deleteModal) {
      deleteModal.addEventListener('show.bs.modal', function (event) {
        // Button that triggered the modal
        var button = event.relatedTarget;
        // Extract info from data-* attributes
        var id = button.getAttribute('data-id');
        // Update the modal's content.
        var form = deleteModal.querySelector('#deletePengeluaranForm');
        form.action = '/pengeluaran/hapus/' + id;
      });
    }

    // Populate detail modal from data-* attributes of the clicked button
    var detailModal = document.getElementById('detailPengeluaranModal');
    if (detailModal) {
      detailModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!button) return;

        document.getElementById('detailTanggal').textContent = button.getAttribute('data-tanggal') || '-';
        document.getElementById('detailJenis').textContent = button.getAttribute('data-jenis') || '-';
        document.getElementById('detailJumlah').textContent = button.getAttribute('data-jumlah') || '-';
        document.getElementById('detailKas').textContent = button.getAttribute('data-kas') || '-';
        document.getElementById('detailMetode').textContent = button.getAttribute('data-metode') || '-';
        document.getElementById('detailAdmin').textContent = button.getAttribute('data-admin') || '-';
        document.getElementById('detailRab').textContent = button.getAttribute('data-rab') || '-';
        document.getElementById('detailKeterangan').textContent = button.getAttribute('data-keterangan') || '-';

        // Status badge with Tailwind color
        var statusEl = document.getElementById('detailStatus');
        var statusText = button.getAttribute('data-status') || '-';
        statusEl.textContent = statusText;
        var statusClass = 'bg-gray-100 text-gray-600';
        if (statusText.indexOf('Berhasil') !== -1 || statusText === 'Approved') {
          statusClass = 'bg-green-100 text-green-700';
        } else if (statusText.indexOf('Menunggu') !== -1) {
          statusClass = 'bg-yellow-100 text-yellow-700';
        }
        statusEl.className = 'px-3 py-1 rounded-full text-sm font-medium ' + statusClass;

        var buktiUrl = button.getAttribute('data-bukti');
        var buktiExt = (button.getAttribute('data-bukti-ext') || '').toLowerCase();
        var buktiContainer = document.getElementById('detailBukti');

        if (buktiUrl) {
          if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(buktiExt)) {
            buktiContainer.innerHTML =
              '<a href="' + buktiUrl + '" target="_blank">' +
              '<img src="' + buktiUrl + '" class="mx-auto rounded-xl shadow-sm" style="max-height:300px;" alt="Bukti Pengeluaran">' +
              '</a>' +
              '<div class="mt-3">' +
              '<a href="' + buktiUrl + '" target="_blank" download class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition">' +
              '<i class="bx bx-download"></i> Unduh Gambar</a></div>';
          } else if (buktiExt === 'pdf') {
            buktiContainer.innerHTML =
              '<a href="' + buktiUrl + '" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition">' +
              '<i class="bx bx-file"></i> Lihat / Unduh PDF</a>';
          } else {
            buktiContainer.innerHTML =
              '<a href="' + buktiUrl + '" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition">' +
              '<i class="bx bx-download"></i> Unduh File</a>';
          }
        } else {
          buktiContainer.innerHTML = '<span class="text-gray-400">Tidak ada bukti</span>';
        }
      });
    }
  });
</script>

@section('page-script')
  <script>
    $(document).ready(function () {
      // Initialize TomSelect (dijaga agar error di sini tidak menggagalkan handler di bawahnya)
      try {
        if (typeof TomSelect !== 'undefined') {
          new TomSelect('#select-rab', {
            create: false,
            sortField: {
              field: "text",
              direction: "asc"
            }
          });
        }
      } catch (e) {
        console.warn('TomSelect init dilewati:', e);
      }

      let searchTimeout;

      // Fungsi untuk memuat data pengeluaran
      function loadPengeluaran(page = 1) {
        $.ajax({
          url: '{{ route("pengeluaran.ajax-filter") }}',
          type: 'GET',
          data: {
            month: $('#monthFilter').val(),
            year: $('#yearFilter').val(),
            kategori: $('#kategoriFilter').val(),
            search: $('#searchInput').val(),
            per_page: $('#entriesPerPage').val(),
            page: page
          },
          success: function (response) {
            // Update tabel dan pagination
            $('#pengeluaranTable tbody').html(response.table);
            $('.pagination-container').html(response.pagination);
            $('#customPaginationInfo').text(`Menampilkan ${response.count} dari ${response.total} records`);

            // Update cards
            if (response.totalPengeluaran) $('#totalPengeluaranAll').text('Rp ' + response.totalPengeluaran);
            if (response.dailyPengeluaran) $('#pengeluaranHariIni').text('Rp ' + response.dailyPengeluaran);
            if (response.monthlyPengeluaran) $('#pengeluaranBulanIni').text('Rp ' + response.monthlyPengeluaran);
            if (response.totalSaldo) $('#totalSaldo').text('Rp ' + response.totalSaldo);
            if (response.saldoBulanIni) $('#saldoBulanIni').text('Rp ' + response.saldoBulanIni);

            // Update labels and subtitles dynamically
            if (response.saldoLabel) $('#saldoBulanIniLabel').text(response.saldoLabel);
            if (response.saldoSub) $('#saldoBulanIniSub').text(response.saldoSub);
            if (response.pengeluaranLabel) $('#pengeluaranBulanIniLabel').text(response.pengeluaranLabel);
            if (response.pengeluaranSub) $('#pengeluaranBulanIniSub').text(response.pengeluaranSub);
          },
          error: function (xhr) {
            console.error('Error:', xhr);
          }
        });
      }

      // Event listener untuk semua filter
      $('#monthFilter, #yearFilter, #kategoriFilter').on('change', function () {
        loadPengeluaran(1);
      });

      $('#entriesPerPage').on('change', function () {
        loadPengeluaran(1);
      });

      $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
          loadPengeluaran(1);
        }, 500); // Debounce to avoid too many requests
      });

      // Event listener untuk pagination
      $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        var href = $(this).attr('href') || '';
        var match = href.match(/[?&]page=(\d+)/);
        var page = match ? match[1] : 1;
        loadPengeluaran(page);
      });

      // Export button functionality
      $('#exportFilterBtn').on('click', function (e) {
        e.preventDefault();
        const month = $('#monthFilter').val();
        const year = $('#yearFilter').val();

        // Build export URL based on filters
        let exportUrl = '{{ route("pengeluaran.export.month", ["month" => ":month", "year" => ":year"]) }}';
        exportUrl = exportUrl.replace(':month', month).replace(':year', year);

        // Redirect to export URL
        window.location.href = exportUrl;
      });
    });
  </script>
@endsection