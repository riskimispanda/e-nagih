@extends('layouts.contentNavbarLayout')

@section('title', 'Tiket Closed')

{{-- Tailwind CSS CDN --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            50: '#eef0ff',
            100: '#e0e3ff',
            500: '#696cff',
            600: '#5a5de6',
          },
        },
        fontFamily: {
          sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        },
        boxShadow: {
          soft: '0 1px 3px rgba(16,24,40,0.04), 0 4px 16px rgba(16,24,40,0.05)',
          'soft-lg': '0 2px 8px rgba(16,24,40,0.06), 0 12px 32px rgba(16,24,40,0.08)',
        },
      },
    },
  }
</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>

{{-- Driver.js for Tutorial Popup --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
<style>
  body { font-family: 'Inter', sans-serif; }
  /* Custom scrollbar for table */
  .table-scroll::-webkit-scrollbar { height: 8px; }
  .table-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
  .table-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
  /* Soft row hover handled by tailwind classes; keep transitions smooth */
  .row-hover { transition: background-color .2s ease, transform .2s ease; }
  .row-hover:hover { transform: translateY(-1px); }
</style>

@section('content')
  <div class="max-w-[1400px] mx-auto px-4 py-6 sm:px-6 lg:px-8 space-y-6">

    {{-- Tiket Open --}}
    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
      <div class="px-5 py-5 sm:px-7 sm:py-6 border-b border-slate-100">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h4 class="text-lg font-semibold text-slate-800 m-0">Tiket Dalam Proses</h4>
            <p class="text-sm text-slate-400 mt-1 mb-0">Daftar tiket yang sedang menunggu penanganan.</p>
          </div>
          <button type="button" class="btn-guide inline-flex items-center gap-2 self-start lg:self-auto rounded-lg border border-brand-500 text-brand-600 px-3.5 py-2 text-sm font-medium hover:bg-brand-50 transition" id="btnGuideExport">
            <i class="bx bx-help-circle"></i> Panduan Export
          </button>
        </div>
      </div>

      <div class="px-5 py-5 sm:px-7">
        <div class="flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end">
          <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-xs font-medium text-slate-500">Bulan</label>
            <select name="month_proses" id="monthFilterProses" class="filter-proses w-full sm:w-44 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-100 outline-none transition">
              <option value="all" {{ !$selectedMonthProses ? 'selected' : '' }}>Semua</option>
              @foreach($months as $num => $name)
                <option value="{{ $num }}" {{ $selectedMonthProses == $num ? 'selected' : '' }}>
                  {{ $name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-xs font-medium text-slate-500">Kategori</label>
            <select name="kategori_proses" id="kategoriFilterProses" class="filter-proses w-full sm:w-48 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-100 outline-none transition">
              <option value="all" {{ !$selectedKategoriProses ? 'selected' : '' }}>Semua</option>
              @foreach($kategoriTiket as $kategori)
                <option value="{{ $kategori->id }}" {{ $selectedKategoriProses == $kategori->id ? 'selected' : '' }}>
                  {{ $kategori->nama_kategori }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="flex flex-col gap-1 flex-1 min-w-[200px]">
            <label class="text-xs font-medium text-slate-500">Search</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <i class="bx bx-search"></i>
              </span>
              <input type="text" class="filter-proses w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-sm text-slate-700 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-100 outline-none transition" name="search_proses" id="searchInputProses" value="{{ $searchProses ?? '' }}" placeholder="Cari nama atau alamat...">
            </div>
          </div>

          <button type="button" id="btnExportProses" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-600 transition">
            <i class="bx bx-spreadsheet"></i> Export Excel
          </button>
        </div>
      </div>

      <div class="px-2 pb-2">
        <div class="table-scroll overflow-x-auto">
          <table class="w-full text-left text-sm border-separate border-spacing-y-1">
            <thead class="bg-slate-50">
              <tr class="text-[11px] uppercase tracking-wider text-slate-400">
                <th class="font-semibold px-4 py-3">No</th>
                <th class="font-semibold px-4 py-3">Pelanggan</th>
                <th class="font-semibold px-4 py-3">No HP</th>
                <th class="font-semibold px-4 py-3 text-center">Lokasi</th>
                <th class="font-semibold px-4 py-3">Keterangan</th>
                <th class="font-semibold px-4 py-3 text-center">Status</th>
                <th class="font-semibold px-4 py-3">Kategori</th>
                <th class="font-semibold px-4 py-3">Tanggal Di Buat</th>
                <th class="font-semibold px-4 py-3">Di Buat Oleh</th>
                <th class="font-semibold px-4 py-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="prosesTableBody" class="text-slate-600">
              @forelse ($customer as $item)
                <tr class="row-hover bg-white ring-1 ring-slate-100 rounded-xl">
                  <td class="px-4 py-3 rounded-l-xl font-medium text-slate-500">{{ $customer->firstItem() + $loop->index }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-500 text-lg">
                        <i class="bx bx-user"></i>
                      </div>
                      <div class="min-w-0">
                        <h6 class="font-semibold text-slate-700 text-sm truncate mb-0.5">{{ $item->customer->nama_customer ?? '-' }}</h6>
                        <p class="text-xs text-slate-400 truncate mb-0">{{ Str::limit($item->customer->alamat ?? '-', 30) }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $item->customer->no_hp ?? '-' }}</td>
                  <td class="px-4 py-3 text-center">
                    @php
                      $gps = $item->customer->gps ?? null;
                      $url = $gps ? (Str::startsWith($gps, ['http://', 'https://']) ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                    @endphp
                    <a href="{{ $url }}" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-brand-500 transition hover:bg-brand-500 hover:text-white hover:border-brand-500 {{ !$gps ? 'pointer-events-none opacity-40' : '' }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $gps ? 'Lihat di Google Maps' : 'Lokasi tidak tersedia' }}">
                      <i class="bx bx-map"></i>
                    </a>
                  </td>
                  <td class="px-4 py-3 max-w-[200px]">
                    <span class="block truncate" data-bs-toggle="tooltip" title="{{ $item->keterangan }}">
                      {{ $item->keterangan }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    @if ($item->status_id == 6)
                      <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-600">Menunggu</span>
                    @elseif($item->status_id == 3)
                      <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-600">Selesai</span>
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-600">
                      {{ $item->kategori->nama_kategori }}
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-md bg-sky-50 px-2 py-1 text-xs font-medium text-sky-600">
                      {{ $item->created_at }}
                    </span>
                  </td>
                  <td class="px-4 py-3 font-semibold text-slate-700">{{ $item->user->name }}</td>
                  <td class="px-4 py-3 rounded-r-xl text-center">
                    <div class="flex justify-center gap-2">
                      @if ($item->status_id == 3)
                        <button class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="Tiket sudah selesai">
                          <i class="bx bx-check-double"></i>
                        </button>
                      @else
                        <a href="/tiket-open/{{ $item->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-amber-500 transition hover:bg-amber-500 hover:text-white hover:border-amber-500" data-bs-toggle="tooltip" data-bs-placement="top" title="Proses & Tutup Tiket">
                          <i class="bx bx-wrench"></i>
                        </a>
                        <button type="button" class="btn-cancel-tiket inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-500 transition hover:bg-rose-500 hover:text-white hover:border-rose-500" data-tiket-id="{{ $item->id }}" data-nama="{{ $item->customer->nama_customer ?? '-' }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Cancel">
                          <i class="bx bx-x"></i>
                        </button>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center gap-2 text-slate-400">
                      <i class="bx bx-inbox text-5xl"></i>
                      <h5 class="text-base font-semibold text-slate-500 m-0">Tidak ada data tiket</h5>
                      <p class="text-sm mb-0">Tidak ada tiket yang cocok dengan pencarian Anda.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      @if ($customer->hasPages())
        <div class="card-footer mt-3 px-5 py-4 border-t border-slate-100" id="prosesPagination">
          <div class="flex justify-between items-center">
            <div class="footer">
              {!! $customer->links('pagination::bootstrap-5') !!}
            </div>
          </div>
        </div>
      @endif
    </div>

    {{-- Tiket Closed Selesai --}}
    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
      <div class="px-5 py-5 sm:px-7 sm:py-6 border-b border-slate-100">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h4 class="text-lg font-semibold text-slate-800 m-0">Tiket Selesai</h4>
            <p class="text-sm text-slate-400 mt-1 mb-0">Daftar tiket yang telah selesai ditangani.</p>
          </div>
        </div>
      </div>

      <div class="px-5 py-5 sm:px-7">
        <div class="flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end">
          <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-xs font-medium text-slate-500">Bulan</label>
            <select name="month_selesai" id="monthFilterSelesai" class="filter-selesai w-full sm:w-44 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-100 outline-none transition">
              <option value="all" {{ !$selectedMonthSelesai ? 'selected' : '' }}>Semua</option>
              @foreach($months as $num => $name)
                <option value="{{ $num }}" {{ $selectedMonthSelesai == $num ? 'selected' : '' }}>
                  {{ $name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-xs font-medium text-slate-500">Kategori</label>
            <select name="kategori_selesai" id="kategoriFilterSelesai" class="filter-selesai w-full sm:w-48 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-100 outline-none transition">
              <option value="all" {{ !$selectedKategoriSelesai ? 'selected' : '' }}>Semua</option>
              @foreach($kategoriTiket as $kategori)
                <option value="{{ $kategori->id }}" {{ $selectedKategoriSelesai == $kategori->id ? 'selected' : '' }}>
                  {{ $kategori->nama_kategori }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="flex flex-col gap-1 flex-1 min-w-[200px]">
            <label class="text-xs font-medium text-slate-500">Search</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <i class="bx bx-search"></i>
              </span>
              <input type="text" class="filter-selesai w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-sm text-slate-700 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-100 outline-none transition" name="search_selesai" id="searchInputSelesai" value="{{ $searchSelesai ?? '' }}" placeholder="Cari nama atau alamat...">
            </div>
          </div>

          <button type="button" id="btnExportSelesai" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-600 transition">
            <i class="bx bx-spreadsheet"></i> Export Excel
          </button>
        </div>
      </div>

      <div class="px-2 pb-2">
        <div class="table-scroll overflow-x-auto">
          <table class="w-full text-left text-sm border-separate border-spacing-y-1">
            <thead class="bg-slate-50">
              <tr class="text-[11px] uppercase tracking-wider text-slate-400">
                <th class="font-semibold px-4 py-3">No</th>
                <th class="font-semibold px-4 py-3">Pelanggan</th>
                <th class="font-semibold px-4 py-3">No HP</th>
                <th class="font-semibold px-4 py-3 text-center">Lokasi</th>
                <th class="font-semibold px-4 py-3">Keterangan</th>
                <th class="font-semibold px-4 py-3 text-center">Status</th>
                <th class="font-semibold px-4 py-3">Kategori</th>
                <th class="font-semibold px-4 py-3">Tanggal Selesai</th>
                <th class="font-semibold px-4 py-3">Teknisi</th>
                <th class="font-semibold px-4 py-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="selesaiTableBody" class="text-slate-600">
              @forelse ($completedTickets as $item)
                <tr class="row-hover bg-white ring-1 ring-slate-100 rounded-xl">
                  <td class="px-4 py-3 rounded-l-xl font-medium text-slate-500">{{ $completedTickets->firstItem() + $loop->index }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-500 text-lg">
                        <i class="bx bx-user"></i>
                      </div>
                      <div class="min-w-0">
                        <h6 class="font-semibold text-slate-700 text-sm truncate mb-0.5">{{ $item->customer->nama_customer ?? '-' }}</h6>
                        <p class="text-xs text-slate-400 truncate mb-0">{{ Str::limit($item->customer->alamat ?? '-', 30) }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $item->customer->no_hp ?? '-' }}</td>
                  <td class="px-4 py-3 text-center">
                    @php
                      $gps = $item->customer->gps ?? null;
                      $url = $gps ? (Str::startsWith($gps, ['http://', 'https://']) ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                    @endphp
                    <a href="{{ $url }}" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-brand-500 transition hover:bg-brand-500 hover:text-white hover:border-brand-500 {{ !$gps ? 'pointer-events-none opacity-40' : '' }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $gps ? 'Lihat di Google Maps' : 'Lokasi tidak tersedia' }}">
                      <i class="bx bx-map"></i>
                    </a>
                  </td>
                  <td class="px-4 py-3 max-w-[200px]">
                    <span class="block truncate" data-bs-toggle="tooltip" title="{{ $item->keterangan }}">
                      {{ $item->keterangan }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    @if ($item->status_id == 6)
                      <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-600">Menunggu</span>
                    @elseif($item->status_id == 3)
                      <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-600">Selesai</span>
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-600">
                      {{ $item->kategori->nama_kategori }}
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-600">
                      {{ $item->updated_at }}
                    </span>
                  </td>
                  <td class="px-4 py-3 font-semibold text-slate-700">{{ $item->teknisi->name ?? '-' }}</td>
                  <td class="px-4 py-3 rounded-r-xl text-center">
                    <div class="flex justify-center gap-2">
                      @if ($item->status_id == 3)
                        <button class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="Tiket sudah selesai">
                          <i class="bx bx-check-double"></i>
                        </button>
                      @else
                        <a href="/tiket-open/{{ $item->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-amber-500 transition hover:bg-amber-500 hover:text-white hover:border-amber-500" data-bs-toggle="tooltip" data-bs-placement="top" title="Proses & Tutup Tiket">
                          <i class="bx bx-wrench"></i>
                        </a>
                        <button type="button" class="btn-cancel-tiket inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-500 transition hover:bg-rose-500 hover:text-white hover:border-rose-500" data-tiket-id="{{ $item->id }}" data-nama="{{ $item->customer->nama_customer ?? '-' }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Cancel">
                          <i class="bx bx-x"></i>
                        </button>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center gap-2 text-slate-400">
                      <i class="bx bx-inbox text-5xl"></i>
                      <h5 class="text-base font-semibold text-slate-500 m-0">Tidak ada data tiket</h5>
                      <p class="text-sm mb-0">Tidak ada tiket yang cocok dengan pencarian Anda.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      @if ($completedTickets->hasPages())
        <div class="card-footer mt-3 px-5 py-4 border-t border-slate-100" id="selesaiPagination">
          <div class="flex justify-between items-center">
            <div class="footer">
              {!! $completedTickets->links('pagination::bootstrap-5') !!}
            </div>
          </div>
        </div>
      @endif
    </div>

    {{-- Tiket Dibatalkan --}}
    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
      <div class="px-5 py-5 sm:px-7 sm:py-6 border-b border-slate-100">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h4 class="text-lg font-semibold text-slate-800 m-0">Tiket Dibatalkan</h4>
            <p class="text-sm text-slate-400 mt-1 mb-0">Daftar tiket yang dibatalkan lengkap dengan alasan pembatalan.</p>
          </div>
        </div>
      </div>

      <div class="px-5 py-5 sm:px-7">
        <div class="flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end">
          <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-xs font-medium text-slate-500">Bulan</label>
            <select name="month_batal" id="monthFilterBatal" class="filter-batal w-full sm:w-44 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-100 outline-none transition">
              <option value="all" {{ !$selectedMonthBatal ? 'selected' : '' }}>Semua</option>
              @foreach($months as $num => $name)
                <option value="{{ $num }}" {{ $selectedMonthBatal == $num ? 'selected' : '' }}>
                  {{ $name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-xs font-medium text-slate-500">Kategori</label>
            <select name="kategori_batal" id="kategoriFilterBatal" class="filter-batal w-full sm:w-48 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-100 outline-none transition">
              <option value="all" {{ !$selectedKategoriBatal ? 'selected' : '' }}>Semua</option>
              @foreach($kategoriTiket as $kategori)
                <option value="{{ $kategori->id }}" {{ $selectedKategoriBatal == $kategori->id ? 'selected' : '' }}>
                  {{ $kategori->nama_kategori }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="flex flex-col gap-1 flex-1 min-w-[200px]">
            <label class="text-xs font-medium text-slate-500">Search</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <i class="bx bx-search"></i>
              </span>
              <input type="text" class="filter-batal w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-sm text-slate-700 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-100 outline-none transition" name="search_batal" id="searchInputBatal" value="{{ $searchBatal ?? '' }}" placeholder="Cari nama atau alamat...">
            </div>
          </div>

          <button type="button" id="btnExportBatal" class="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-600 transition">
            <i class="bx bx-spreadsheet"></i> Export Excel
          </button>
        </div>
      </div>

      <div class="px-2 pb-2">
        <div class="table-scroll overflow-x-auto">
          <table class="w-full text-left text-sm border-separate border-spacing-y-1">
            <thead class="bg-slate-50">
              <tr class="text-[11px] uppercase tracking-wider text-slate-400">
                <th class="font-semibold px-4 py-3">No</th>
                <th class="font-semibold px-4 py-3">Pelanggan</th>
                <th class="font-semibold px-4 py-3">No HP</th>
                <th class="font-semibold px-4 py-3">Keterangan</th>
                <th class="font-semibold px-4 py-3 text-center">Status</th>
                <th class="font-semibold px-4 py-3">Kategori</th>
                <th class="font-semibold px-4 py-3 min-w-[160px]">Alasan Pembatalan</th>
                <th class="font-semibold px-4 py-3">Dibatalkan Oleh</th>
                <th class="font-semibold px-4 py-3">Waktu Dibatalkan</th>
              </tr>
            </thead>
            <tbody id="batalTableBody" class="text-slate-600">
              @forelse ($cancelledTickets as $item)
                <tr class="row-hover bg-white ring-1 ring-slate-100 rounded-xl">
                  <td class="px-4 py-3 rounded-l-xl font-medium text-slate-500">{{ $cancelledTickets->firstItem() + $loop->index }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-500 text-lg">
                        <i class="bx bx-x"></i>
                      </div>
                      <div class="min-w-0">
                        <h6 class="font-semibold text-slate-700 text-sm truncate mb-0.5">{{ $item->customer->nama_customer ?? '-' }}</h6>
                        <p class="text-xs text-slate-400 truncate mb-0">{{ Str::limit($item->customer->alamat ?? '-', 30) }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $item->customer->no_hp ?? '-' }}</td>
                  <td class="px-4 py-3 max-w-[200px]">
                    <span class="block truncate" data-bs-toggle="tooltip" title="{{ $item->keterangan }}">
                      {{ $item->keterangan }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-600">Dibatalkan</span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                      {{ $item->kategori->nama_kategori }}
                    </span>
                  </td>
                  <td class="px-4 py-3 max-w-[220px]">
                    <span class="block text-xs text-rose-600" data-bs-toggle="tooltip" title="{{ $item->alasan_batal }}">
                      {{ $item->alasan_batal ?? '-' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 font-semibold text-slate-700">{{ $item->cancelledBy->name ?? '-' }}</td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-500">
                      {{ $item->cancelled_at ? \Carbon\Carbon::parse($item->cancelled_at)->translatedFormat('d M Y H:i') : '-' }}
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center gap-2 text-slate-400">
                      <i class="bx bx-inbox text-5xl"></i>
                      <h5 class="text-base font-semibold text-slate-500 m-0">Tidak ada tiket dibatalkan</h5>
                      <p class="text-sm mb-0">Tidak ada tiket batal yang cocok dengan pencarian Anda.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      @if ($cancelledTickets->hasPages())
        <div class="card-footer mt-3 px-5 py-4 border-t border-slate-100" id="batalPagination">
          <div class="flex justify-between items-center">
            <div class="footer">
              {!! $cancelledTickets->links('pagination::bootstrap-5') !!}
            </div>
          </div>
        </div>
      @endif
    </div>

  </div>
@endsection

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // --- UTILITY FUNCTIONS ---
    function initializeTooltips() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        var existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
        if (existingTooltip) {
          existingTooltip.dispose();
        }
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }

    // --- MAIN LOGIC ---
    function setupTableFilters(prefix) {
      const searchInput = document.getElementById(`searchInput${prefix}`);
      const monthFilter = document.getElementById(`monthFilter${prefix}`);
      const kategoriFilter = document.getElementById(`kategoriFilter${prefix}`);
      const tableBody = document.getElementById(`${prefix.toLowerCase()}TableBody`);
      const paginationContainer = document.getElementById(`${prefix.toLowerCase()}Pagination`);

      let searchTimeout;

      function fetchData(page = 1, pushState = true) {
        const search = searchInput.value;
        const month = monthFilter.value;
        const kategori = kategoriFilter.value;

        const url = new URL(window.location.href);

        url.searchParams.set(`search_${prefix.toLowerCase()}`, search);
        url.searchParams.set(`month_${prefix.toLowerCase()}`, month);
        url.searchParams.set(`kategori_${prefix.toLowerCase()}`, kategori);
        url.searchParams.set(`${prefix.toLowerCase()}_page`, page);

        url.searchParams.set('ajax', 1);

        if (pushState) {
          window.history.pushState({ path: url.href }, '', url.href);
        }

        fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(response => response.ok ? response.text() : Promise.reject('Network response was not ok.'))
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTableBody = doc.getElementById(`${prefix.toLowerCase()}TableBody`);
            const newPagination = doc.getElementById(`${prefix.toLowerCase()}Pagination`);

            if (newTableBody) tableBody.innerHTML = newTableBody.innerHTML;
            if (paginationContainer) paginationContainer.innerHTML = newPagination ? newPagination.innerHTML : '';

            initializeTooltips();
          })
          .catch(error => console.error('Error fetching data:', error));
      }

      searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchData(1), 500);
      });

      monthFilter.addEventListener('change', () => fetchData(1));
      kategoriFilter.addEventListener('change', () => fetchData(1));

      paginationContainer?.addEventListener('click', function (e) {
        const pageLink = e.target.closest('.pagination a');
        if (pageLink) {
          e.preventDefault();
          const url = new URL(pageLink.href);
          const page = url.searchParams.get(`${prefix.toLowerCase()}_page`);
          fetchData(page);
        }
      });

      // Export functionality
      document.getElementById(`btnExport${prefix}`).addEventListener('click', function () {
        const search = searchInput.value;
        const month = monthFilter.value;
        const kategori = kategoriFilter.value;

        const exportUrl = new URL(`/export-tiket-${prefix.toLowerCase()}`, window.location.origin);
        exportUrl.searchParams.set(`search_${prefix.toLowerCase()}`, search);
        exportUrl.searchParams.set(`month_${prefix.toLowerCase()}`, month);
        exportUrl.searchParams.set(`kategori_${prefix.toLowerCase()}`, kategori);

        window.location.href = exportUrl.href;
      });
    }

    // --- INITIALIZATION ---
    setupTableFilters('Proses');
    setupTableFilters('Selesai');
    setupTableFilters('Batal');
    initializeTooltips();

    // --- CANCEL TIKET MODAL (SweetAlert dengan textarea alasan) ---
    document.querySelectorAll('.btn-cancel-tiket').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var tiketId = btn.getAttribute('data-tiket-id');
        var nama = btn.getAttribute('data-nama') || 'pelanggan ini';

        Swal.fire({
          title: 'Batalkan Tiket?',
          html:
            'Tiket untuk <strong>' + nama + '</strong> akan dibatalkan.<br>' +
            'Harap isi alasan pembatalan di bawah ini.',
          icon: 'warning',
          input: 'textarea',
          inputLabel: 'Alasan Pembatalan',
          inputPlaceholder: 'Contoh: pelanggan tidak merespon, salah input, dll...',
          inputAttributes: { required: true },
          inputValidator: function (value) {
            if (!value || !value.trim()) {
              return 'Alasan pembatalan wajib diisi!';
            }
          },
          showCancelButton: true,
          confirmButtonColor: '#e11d48',
          cancelButtonColor: '#64748b',
          confirmButtonText: 'Ya, Batalkan',
          cancelButtonText: 'Batal',
          preConfirm: function (alasan) {
            var formData = new FormData();
            formData.append('alasan_batal', alasan);
            formData.append('_token', '{{ csrf_token() }}');
            return fetch('/cancel-tiket/' + tiketId, {
              method: 'POST',
              body: formData,
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (response) {
              if (!response.ok) {
                throw new Error('Gagal membatalkan tiket');
              }
              return response.json();
            });
          }
        }).then(function (result) {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Berhasil',
              text: 'Tiket berhasil dibatalkan.',
              icon: 'success',
              confirmButtonColor: '#696cff'
            }).then(function () {
              window.location.reload();
            });
          }
        });
      });
    });

    window.onpopstate = function (event) {
      if (event.state) {
        window.location.reload();
      }
    };
  });
</script>
{{-- Driver.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const driver = window.driver.js.driver;

    const driverObj = driver({
      showProgress: true,
      animate: true,
      doneBtnText: 'Selesai',
      nextBtnText: 'Lanjut',
      prevBtnText: 'Kembali',
      steps: [
        {
          element: '#prosesTableBody',
          popover: {
            title: 'Layanan Filter & Export',
            description: 'Selamat datang! Di sini Anda dapat memfilter data sebelum mengunduhnya ke Excel. Mari kita lihat caranya.',
            position: 'bottom',
            align: 'start'
          }
        },
        {
          element: '#monthFilterProses',
          popover: {
            title: 'Filter Berdasarkan Bulan',
            description: 'Pilih bulan tertentu untuk menampilkan tiket yang dibuat pada periode tersebut.',
            position: 'bottom',
            align: 'start'
          }
        },
        {
          element: '#kategoriFilterProses',
          popover: {
            title: 'Filter Berdasarkan Kategori',
            description: 'Gunakan ini untuk memisahkan tiket berdasarkan jenisnya, seperti Gangguan, Deaktivasi, atau Relokasi.',
            position: 'bottom',
            align: 'start'
          }
        },
        {
          element: '#searchInputProses',
          popover: {
            title: 'Pencarian Instan',
            description: 'Ketik nama pelanggan, alamat, atau nomor HP di sini. Tabel akan otomatis memproses pencarian Anda.',
            position: 'bottom',
            align: 'start'
          }
        },
        {
          element: '#btnExportProses',
          popover: {
            title: 'Unduh Laporan Excel',
            description: 'Setelah memfilter data yang diinginkan, klik tombol ini untuk mengunduh laporan dalam format Excel (.xlsx) secara instan.',
            position: 'bottom',
            align: 'end'
          }
        },
        {
          popover: {
            title: 'Siap Digunakan!',
            description: 'Fitur yang sama juga tersedia pada tabel "Tiket Selesai" di bawah. Selamat bekerja!',
          }
        }
      ]
    });

    const btnGuideExport = document.getElementById('btnGuideExport');
    if (btnGuideExport) {
      btnGuideExport.addEventListener('click', function(e) {
        e.preventDefault();
        driverObj.drive();
      });
    }
  });
</script>
