@extends('layouts.contentNavbarLayout')

@section('title', 'Tutup Tiket Open')

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
            400: '#8c8ff5',
            500: '#696cff',
            600: '#5a5de6',
          },
          ink: {
            50: '#f8fafc',
            100: '#f1f5f9',
            200: '#e2e8f0',
            400: '#94a3b8',
            500: '#64748b',
            600: '#475569',
            700: '#334155',
            800: '#1e293b',
          },
          mist: {
            50: '#f6f7fc',
            100: '#eef0f9',
          },
        },
        fontFamily: {
          sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        },
        boxShadow: {
          soft: '0 1px 2px rgba(16,24,40,0.03), 0 4px 16px rgba(16,24,40,0.05)',
        },
      },
    },
  }
</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>

<style>
  body { font-family: 'Inter', sans-serif; }
  .tw-collapse { display: none; }
  .tw-collapse.tw-open { display: block; }
  .tw-header { cursor: pointer; transition: background-color .3s ease, color .3s ease; }
  .tw-header .collapse-icon { transition: transform .3s ease; }
  .tw-header.tw-open .collapse-icon { transform: rotate(180deg); }
  .tw-header.tw-open { background: #f6f7fc; }
  .tw-header.tw-open .card-title,
  .tw-header.tw-open .header-badge,
  .tw-header.tw-open small,
  .tw-header.tw-open i { color: #334155 !important; }
  .info-card { transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease; }
  .info-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,24,40,0.06); background-color: #fff; }
  .tech-info { transition: background-color .2s ease; }
  .tech-info:hover { background-color: #f8f9ff; }
  .connection-info { transition: background-color .2s ease, border-color .2s ease; }
  .connection-info:hover { border-color: #cbd5e1; background-color: #f8fafc; }
  .form-control, .form-select { border-radius: 10px; transition: border-color .2s, box-shadow .2s; }
  .form-control:focus, .form-select:focus { border-color: #94a3b8; box-shadow: 0 0 0 3px rgba(148,163,184,0.18); }
  .input-group { border-radius: 10px; }
  .input-group-text { background-color: #f8fafc; border: 1px solid #e2e8f0; }
  .btn { border-radius: 10px; }
  .tw-divider { background: linear-gradient(90deg, transparent, #e2e8f0, transparent); }
</style>

@section('content')
<div class="container-2xl flex-grow-1 container-p-y">

  <!-- Hero Header -->
  <div class="relative mb-5 overflow-hidden rounded-3xl border border-slate-100 bg-white p-6 shadow-soft sm:p-7">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-mist-50 text-2xl text-ink-500">
          <i class='bx bx-clipboard'></i>
        </div>
        <div>
          <h4 class="m-0 text-xl font-bold text-ink-700 sm:text-2xl">Konfirmasi Tiket Open</h4>
          <p class="m-0 mt-0.5 text-sm text-ink-400">Tinjau detail & teknis, lalu tutup tiket pelanggan</p>
        </div>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <span class="header-badge inline-flex w-fit items-center gap-1.5 rounded-full bg-mist-100 px-4 py-2 text-sm font-medium text-ink-600">
          <i class="bx bx-category"></i>
          {{ $kategori->kategori->nama_kategori }}
        </span>
      </div>
    </div>

    <!-- Ticket flow stepper -->
    <div class="relative mt-6 flex items-center justify-between gap-2 text-xs text-ink-400">
      <div class="flex flex-1 flex-col items-center gap-1.5">
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-mist-100 text-ink-500"><i class='bx bx-receipt'></i></div>
        <span class="font-medium">Tiket Open</span>
      </div>
      <div class="h-px flex-1 bg-slate-100"></div>
      <div class="flex flex-1 flex-col items-center gap-1.5">
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-mist-100 text-ink-500"><i class='bx bx-cog'></i></div>
        <span class="font-medium">Proses Teknis</span>
      </div>
      <div class="h-px flex-1 bg-slate-100"></div>
      <div class="flex flex-1 flex-col items-center gap-1.5">
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-ink-100 text-ink-600"><i class='bx bx-check-double'></i></div>
        <span class="font-semibold text-ink-600">Konfirmasi</span>
      </div>
    </div>
  </div>

  <!-- Detail Tiket - Collapsible Card -->
  <div class="mb-4 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
    <div class="tw-header tw-open flex items-center justify-between border-b border-slate-100 px-5 py-4" data-tw-collapse="detailTiketCollapse" aria-expanded="true">
      <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-mist-100 text-lg text-ink-500"><i class="bx bx-info-circle"></i></span>
        <h5 class="card-title m-0 text-base font-semibold text-ink-700">Detail Tiket {{ $kategori->kategori->nama_kategori }}</h5>
      </div>
      <div class="flex items-center gap-3">
        <span class="header-badge hidden items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-600 sm:inline-flex">
          <span class="mr-1 text-[11px] text-ink-400">Dibuat:</span>{{ $kategori->user->name ?? '-'}}
        </span>
        <i class="bx bx-chevron-down collapse-icon text-xl text-ink-400"></i>
      </div>
    </div>
    <div class="tw-collapse tw-open" id="detailTiketCollapse">
      <div class="p-5 sm:p-6">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div class="info-card flex items-center rounded-2xl border border-slate-100 bg-mist-50 p-4">
            <div class="mr-3 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-500 text-white"><i class="bx bx-user"></i></div>
            <div class="min-w-0">
              <label class="mb-0.5 block text-xs font-medium text-ink-400">Nama Pelanggan</label>
              <p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->nama_customer }}</p>
            </div>
          </div>
          <div class="info-card flex items-center rounded-2xl border border-slate-100 bg-mist-50 p-4">
            <div class="mr-3 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-500 text-white"><i class="bx bx-map"></i></div>
            <div class="min-w-0">
              <label class="mb-0.5 block text-xs font-medium text-ink-400">Alamat</label>
              <p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->alamat }}</p>
            </div>
          </div>
          <div class="info-card flex items-center rounded-2xl border border-slate-100 bg-mist-50 p-4">
            <div class="mr-3 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-500 text-white"><i class="bx bx-phone"></i></div>
            <div class="min-w-0">
              <label class="mb-0.5 block text-xs font-medium text-ink-400">No Telepon</label>
              <p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->no_hp }}</p>
            </div>
          </div>
          <div class="info-card flex items-center rounded-2xl border border-slate-100 bg-mist-50 p-4">
            <div class="mr-3 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-500 text-white"><i class="bx bx-map-pin"></i></div>
            <div class="min-w-0">
              <label class="mb-0.5 block text-xs font-medium text-ink-400">Lokasi</label>
               <a href="{{ $tiket->customer->gps }}" target="_blank" class="btn btn-sm d-inline-flex align-center gap-1" style="border:1px solid #cbd5e1;color:#64748b;" data-bs-toggle="tooltip" title="Lihat di Google Maps" data-bs-placement="bottom">
                <i class="bx bx-navigation"></i>Lihat Maps
              </a>
            </div>
          </div>
        </div>

        <div class="mt-5">
          <label class="mb-1.5 block text-sm font-semibold text-ink-700">Keterangan Tiket</label>
          <div class="rounded-2xl border border-slate-100 bg-mist-50 p-4">
            <p class="mb-0 text-sm text-ink-600">{{ $tiket->keterangan }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Informasi Teknis - Collapsible Card -->
  <div class="mb-4 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
    <div class="tw-header flex items-center justify-between border-b border-slate-100 px-5 py-4" data-tw-collapse="infoTeknisCollapse" aria-expanded="true">
      <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-mist-100 text-lg text-ink-500"><i class="bx bx-cog"></i></span>
        <h5 class="card-title m-0 text-base font-semibold text-ink-700">Informasi Teknis</h5>
      </div>
      <i class="bx bx-chevron-down collapse-icon text-xl text-ink-400"></i>
    </div>
    <div class="tw-collapse" id="infoTeknisCollapse">
      <div class="p-5 sm:p-6">
        <div class="grid grid-cols-1 gap-x-6 md:grid-cols-2">
          <!-- Kolom 1 -->
          <div class="space-y-3">
            @if($tiket->customer->media->nama_media == 'OLT')
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bxs-devices text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Media Koneksi</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->media->nama_media ?? '-'}}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-terminal text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">BTS Server</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->odp->odc->olt->server->lokasi_server ?? '-'}}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-sitemap text-emerald-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">OLT</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->odp->odc->olt->nama_lokasi ?? '-'}}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-network-chart text-sky-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">ODC</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->odp->odc->nama_odc ?? '-'}}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-network-chart text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">ODP</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->odp->nama_odp ?? '-'}}</p></div>
            </div>
            @elseif($tiket->customer->media->nama_media == 'HTB')
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bxs-devices text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Media Koneksi</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->media->nama_media ?? '-'}}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-terminal text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Access Point</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->transiver ?? '-'}}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-terminal text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Station</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->receiver ?? '-'}}</p></div>
            </div>
            @elseif($tiket->customer->media->nama_media == 'Wireless')
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bxs-devices text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Media Koneksi</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->media->nama_media ?? '-'}}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-terminal text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Access Point</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->access_point ?? '-'}}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-terminal text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Station</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->station ?? '-'}}</p></div>
            </div>
            @endif
          </div>

          <!-- Kolom 2 -->
          <div class="mt-3 space-y-3 md:mt-0">
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-server text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Router</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->router->nama_router }}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-chip text-sky-500 mr-3 text-xl"></i>
              <div>
                <small class="text-muted d-block text-xs text-ink-400">Modem Pelanggan</small>
                <p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->perangkat->nama_perangkat ?? 'Belum Ditentukan' }}</p>
                @if($tiket->customer->seri_perangkat || $tiket->customer->mac_address)
                <small class="text-muted d-block" style="font-size:.8rem;">SN: {{ $tiket->customer->seri_perangkat ?? '-' }} | MAC: {{ $tiket->customer->mac_address ?? '-' }}</small>
                @endif
              </div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-package text-slate-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Paket</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->paket->nama_paket }}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-hard-hat text-emerald-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Teknisi</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->customer->teknisi->name ?? '-' }}</p></div>
            </div>
            <div class="tech-info flex items-center rounded-2xl border border-slate-100 p-3.5">
              <i class="bx bx-calendar text-ink-500 mr-3 text-xl"></i>
              <div><small class="text-muted d-block text-xs text-ink-400">Tanggal Open</small><p class="mb-0 fw-semibold text-sm font-semibold text-ink-700">{{ $tiket->created_at->format('d F Y, H:i') }}</p></div>
            </div>
          </div>
        </div>

        <!-- Informasi Koneksi -->
        <div class="mt-5 border-t tw-divider pt-5">
          <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-ink-400">Informasi Koneksi</p>
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div class="connection-info flex items-center rounded-2xl border border-slate-100 bg-white p-3.5">
              <i class="bx bx-lock-alt text-ink-500 mr-3 text-lg"></i>
              <div class="min-w-0"><small class="text-muted d-block text-xs text-ink-400">Usersecret</small><p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->usersecret }}</p></div>
            </div>
            <div class="connection-info flex items-center rounded-2xl border border-slate-100 bg-white p-3.5">
              <i class="bx bx-key text-ink-500 mr-3 text-lg"></i>
              <div class="min-w-0"><small class="text-muted d-block text-xs text-ink-400">Password Secret</small><p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->pass_secret }}</p></div>
            </div>
            <div class="connection-info flex items-center rounded-2xl border border-slate-100 bg-white p-3.5">
              <i class="bx bx-link-alt text-ink-500 mr-3 text-lg"></i>
              <div class="min-w-0"><small class="text-muted d-block text-xs text-ink-400">Jenis Koneksi</small><p class="mb-0 text-sm font-semibold text-ink-700">{{ $tiket->customer->koneksi->nama_koneksi }}</p></div>
            </div>
            <div class="connection-info flex items-center rounded-2xl border border-slate-100 bg-white p-3.5">
              <i class="bx bx-math text-ink-500 mr-3 text-lg"></i>
              <div class="min-w-0"><small class="text-muted d-block text-xs text-ink-400">Local Address</small><p class="mb-0 text-sm font-semibold text-ink-700">{{ $tiket->customer->local_address ?? 'Tidak Tersedia' }}</p></div>
            </div>
            <div class="connection-info flex items-center rounded-2xl border border-slate-100 bg-white p-3.5">
              <i class="bx bx-network-chart text-ink-500 mr-3 text-lg"></i>
              <div class="min-w-0"><small class="text-muted d-block text-xs text-ink-400">Remote Address</small><p class="mb-0 text-sm font-semibold text-ink-700">{{ $tiket->customer->remote_address ?? 'Tidak Tersedia' }}</p></div>
            </div>
            <div class="connection-info flex items-center rounded-2xl border border-slate-100 bg-white p-3.5">
              <i class="bx bx-plug text-ink-500 mr-3 text-lg"></i>
              <div class="min-w-0"><small class="text-muted d-block text-xs text-ink-400">Remote IP Management</small><p class="mb-0 text-sm font-semibold text-ink-700">{{ $tiket->customer->remote }}</p></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Form Upgrade/Downgrade -->
  @if($kategori->kategori->nama_kategori == 'Upgrade' || $kategori->kategori->nama_kategori == 'Downgrade')
  <div class="mb-4 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
    <div class="tw-header flex items-center justify-between border-b border-slate-100 px-5 py-4" data-tw-collapse="formTutupCollapse" aria-expanded="true">
      <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-mist-100 text-lg text-ink-500"><i class="bx bx-edit"></i></span>
        <h5 class="card-title m-0 text-base font-semibold text-ink-700">Form Tutup Tiket {{ $kategori->kategori->nama_kategori }}</h5>
      </div>
      <i class="bx bx-chevron-down collapse-icon text-xl text-ink-400"></i>
    </div>
    <div class="tw-collapse" id="formTutupCollapse">
      <div class="p-5 sm:p-6">
        <div class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-100 bg-mist-50 p-4 sm:grid-cols-2">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-500 text-white"><i class="bx bx-barcode"></i></div>
            <div class="min-w-0">
              <label class="mb-0.5 block text-xs font-medium text-ink-400">SN Modem</label>
              <p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->seri_perangkat ?? '-' }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-500 text-white"><i class="bx bx-wifi"></i></div>
            <div class="min-w-0">
              <label class="mb-0.5 block text-xs font-medium text-ink-400">MAC Address Modem</label>
              <p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->mac_address ?? '-' }}</p>
            </div>
          </div>
        </div>
        <form action="/tutup-tiket/{{ $tiket->id }}" method="POST">
          @csrf
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Router</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bx-server"></i></span>
                <select class="form-select w-full rounded-r-xl border border-l-0" name="router" id="router" required>
                  <option value="" selected disabled>Pilih Router</option>
                  @forelse ($router as $r)
                  <option value="{{ $r->id }}">{{ $r->nama_router }}</option>
                  @empty
                  <option value="" selected disabled>Tidak ada data router</option>
                  @endforelse
                </select>
              </div>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Paket Baru</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bx-package"></i></span>
                <select name="paket" id="paket" required class="form-select w-full rounded-r-xl border border-l-0">
                  <option value="" selected disabled>Pilih Paket</option>
                </select>
              </div>
            </div>
          </div>

          <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Usersecret</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bx-lock"></i></span>
                <input type="text" class="form-control w-full rounded-r-xl border border-l-0" name="usersecret" placeholder="coba@niscala.net.id" id="usersecret">
              </div>
              <div class="form-text mt-1 text-xs text-ink-400"><i class="bx bx-info-circle me-1"></i>Kosongkan jika tidak ada perubahan</div>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Password Secret</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bx-key"></i></span>
                <input type="text" class="form-control w-full rounded-r-xl border border-l-0" name="pass_secret" placeholder="coba123" id="pass_secret">
              </div>
              <div class="form-text mt-1 text-xs text-ink-400"><i class="bx bx-info-circle me-1"></i>Kosongkan jika tidak ada perubahan</div>
            </div>
          </div>

          <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Local Address</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bx-math"></i></span>
                <input type="text" class="form-control w-full rounded-r-xl border border-l-0" name="local_address" placeholder="192.168.1.1" required>
              </div>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Remote Address</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bx-network-chart"></i></span>
                <input type="text" class="form-control w-full rounded-r-xl border border-l-0" name="remote_address" id="remote_address" placeholder="192.168.1.1" required>
              </div>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Remote IP Management</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bx-plug"></i></span>
                <input type="text" class="form-control w-full rounded-r-xl border border-l-0" name="remote" id="remote" placeholder="192.168.1.1">
              </div>
              <div class="form-text mt-1 text-xs text-ink-400"><i class="bx bx-info-circle me-1"></i>Akan terisi otomatis dari Remote Address</div>
            </div>
          </div>

          <div class="mt-6 flex flex-col gap-2 border-t tw-divider pt-5 sm:flex-row sm:justify-end">
            <a href="javascript:history.back()" class="btn btn-secondary btn-sm inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-white transition hover:bg-ink-50">
              Kembali
            </a>
            <button type="submit" class="btn btn-warning btn-sm inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600">
              Konfirmasi Tutup Tiket
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif

  <!-- Form Deaktivasi -->
  @if($kategori->kategori->nama_kategori == 'Deaktivasi')
  <div class="mb-4 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
    <div class="tw-header flex items-center justify-between border-b border-slate-100 px-5 py-4" data-tw-collapse="formDeaktivasiCollapse" aria-expanded="true">
      <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-mist-100 text-lg text-ink-500"><i class="bx bx-power-off"></i></span>
        <h5 class="card-title m-0 text-base font-semibold text-ink-700">Form Konfirmasi Deaktivasi</h5>
      </div>
      <i class="bx bx-chevron-down collapse-icon text-xl text-ink-400"></i>
    </div>
    <div class="tw-collapse" id="formDeaktivasiCollapse">
      <div class="p-5 sm:p-6">
        <form action="/konfirmasi-tiket/{{ $tiket->id }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-100 bg-mist-50 p-4 sm:grid-cols-2">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-500 text-white"><i class="bx bx-barcode"></i></div>
              <div class="min-w-0">
                <label class="mb-0.5 block text-xs font-medium text-ink-400">SN Modem</label>
                <p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->seri_perangkat ?? '-' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-500 text-white"><i class="bx bx-wifi"></i></div>
              <div class="min-w-0">
                <label class="mb-0.5 block text-xs font-medium text-ink-400">MAC Address Modem</label>
                <p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->mac_address ?? '-' }}</p>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Modem</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-devices"></i></span>
                <select name="modem_id" id="" class="form-select w-full rounded-r-xl border border-l-0" readonly disabled>
                  <option value="{{ $tiket->customer->perangkat_id ?? '-'}}">{{$tiket->customer->perangkat->nama_perangkat ?? '-'}}</option>
                </select>
              </div>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Kondisi Modem yang Dikembalikan</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bx-wrench"></i></span>
                <select name="status_modem" class="form-select w-full rounded-r-xl border border-l-0" required>
                  <option value="14">Masih Bagus (Tersedia)</option>
                  <option value="4" selected>Perlu Perbaikan (Maintenance)</option>
                  <option value="15">Rusak Total (Afkir)</option>
                </select>
              </div>
              <small class="mt-1 block text-xs text-ink-400">Menentukan ke status mana modem pelanggan ini dikembalikan.</small>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Keterangan Kondisi Modem</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-message"></i></span>
                <textarea name="keterangan" class="form-control w-full rounded-r-xl border border-l-0" id="" cols="30" rows="5" required></textarea>
              </div>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Foto Modem</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-image"></i></span>
                <input type="file" name="foto" class="form-control w-full rounded-r-xl border border-l-0" required>
              </div>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Tanggal Deaktivasi</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-calendar"></i></span>
                <input type="date" name="tanggal" class="form-control w-full rounded-r-xl border border-l-0" value="{{ now()->format('Y-m-d') }}" readonly>
              </div>
            </div>
          </div>
          <div class="mt-6 flex flex-col gap-2 border-t tw-divider pt-5 sm:flex-row sm:justify-end">
            <a href="javascript:window.history.back()" class="btn btn-secondary btn-sm inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-white transition hover:bg-ink-50">Kembali</a>
            <button class="btn btn-warning btn-sm inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600" type="submit">Konfirmasi</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif

  {{-- Form Gangguan --}}
  @if($kategori->kategori->nama_kategori == 'Gangguan' || $kategori->kategori->nama_kategori == 'Maintenance' || $kategori->kategori->nama_kategori == 'Ganti Alat')
  <div class="mb-4 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
    <div class="tw-header flex items-center justify-between border-b border-slate-100 px-5 py-4" data-tw-collapse="formDeaktivasiCollapse" aria-expanded="true">
      <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-mist-100 text-lg text-ink-500"><i class="bx bx-power-off"></i></span>
        <h5 class="card-title m-0 text-base font-semibold text-ink-700">Form Konfirmasi Gangguan</h5>
      </div>
      <i class="bx bx-chevron-down collapse-icon text-xl text-ink-400"></i>
    </div>
    <div class="tw-collapse" id="formDeaktivasiCollapse">
      <div class="p-5 sm:p-6">
        <form action="/konfirmasi-tiket-gangguan/{{ $tiket->id }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-100 bg-mist-50 p-4 sm:grid-cols-2">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-500 text-white"><i class="bx bx-barcode"></i></div>
              <div class="min-w-0">
                <label class="mb-0.5 block text-xs font-medium text-ink-400">SN Modem</label>
                <p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->seri_perangkat ?? '-' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-500 text-white"><i class="bx bx-wifi"></i></div>
              <div class="min-w-0">
                <label class="mb-0.5 block text-xs font-medium text-ink-400">MAC Address Modem</label>
                <p class="mb-0 truncate text-sm font-semibold text-ink-700">{{ $tiket->customer->mac_address ?? '-' }}</p>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Modem Lama</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-devices"></i></span>
                <select name="modem_lama_id" class="form-select w-full rounded-r-xl border border-l-0" readonly disabled>
                  <option value="{{ $modemLama->perangkat->id ?? '-'}}">{{$modemLama->perangkat->nama_perangkat ?? '-'}}</option>
                </select>
              </div>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Modem Baru</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-devices"></i></span>
                <select name="modem_baru_id" id="modem-baru" class="form-select w-full rounded-r-xl border border-l-0" placeholder="Pilih Modem Baru">
                  <option value="" selected>Pilih Modem Baru</option>
                  @foreach ($perangkat as $item)
                    <option value="{{ $item->id }}" data-stok="{{ $item->jumlah_stok ?? 0 }}">
                      {{ $item->nama_perangkat }}
                    </option>
                  @endforeach
                </select>
              </div>
              <small class="mt-1 block text-xs text-ink-400" id="info-stok">Pilih modem baru jika ganti modem. (Optional)</small>
            </div>

            {{-- Form tambahan yang muncul ketika modem dipilih --}}
            <div id="form-tambahan" class="sm:col-span-2" style="display: none;">
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                  <label class="mb-1.5 block text-sm font-semibold text-ink-700">Serial Number</label>
                  <div class="input-group flex">
                    <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-barcode"></i></span>
                    <select name="modem_detail_id" id="serial_number" class="form-select w-full rounded-r-xl border border-l-0" placeholder="Pilih SN">
                      <option value="">Pilih Serial Number</option>
                    </select>
                  </div>
                  <small id="serial_stock_alert" class="mt-1 block text-xs text-red-500" style="display:none;"><i class="bx bx-error-circle"></i> Tidak ada stok SN tersedia untuk modem ini.</small>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-semibold text-ink-700">MAC Address</label>
                  <div class="input-group flex">
                    <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-network-chart"></i></span>
                    <input type="text" name="mac_address" id="mac_address" class="form-control w-full rounded-r-xl border border-l-0 bg-gray-50" placeholder="Otomatis terisi" readonly>
                  </div>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-semibold text-ink-700">Kondisi Modem Lama yang Ditarik</label>
                  <div class="input-group flex">
                    <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bx-wrench"></i></span>
                    <select name="status_modem_lama" class="form-select w-full rounded-r-xl border border-l-0">
                      <option value="14">Masih Bagus (Tersedia)</option>
                      <option value="4" selected>Perlu Perbaikan (Maintenance)</option>
                      <option value="15">Rusak Total (Afkir)</option>
                    </select>
                  </div>
                  <small class="mt-1 block text-xs text-ink-400">Menentukan apakah perangkat lama masuk ke tab Tersedia, Maintenance, atau Rusak.</small>
                </div>
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Foto</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-image"></i></span>
                <input type="file" name="foto" class="form-control w-full rounded-r-xl border border-l-0">
              </div>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Tanggal Konfirmasi Gangguan</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-calendar"></i></span>
                <input type="date" name="tanggal" class="form-control w-full rounded-r-xl border border-l-0" value="{{ now()->format('Y-m-d') }}" readonly>
              </div>
            </div>
            <div class="sm:col-span-2">
              <label class="mb-1.5 block text-sm font-semibold text-ink-700">Keterangan</label>
              <div class="input-group flex">
                <span class="input-group-text flex items-center rounded-l-xl border-r-0"><i class="bx bxs-message"></i></span>
                <textarea name="keterangan" class="form-control w-full rounded-r-xl border border-l-0" cols="30" rows="5" required></textarea>
              </div>
            </div>
          </div>
          <div class="mt-6 flex flex-col gap-2 border-t tw-divider pt-5 sm:flex-row sm:justify-end">
            <a href="javascript:window.history.back()" class="btn btn-secondary btn-sm inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-white transition hover:bg-ink-50">Kembali</a>
            <button class="btn btn-warning btn-sm inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600" type="submit">Konfirmasi</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif

</div>

<script>
    $(document).ready(function() {
        if (!$('#paket').length) return;

        var paketSelect = new TomSelect('#paket', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: 'Pilih Paket',
            allowEmptyOption: false
        });

        $('#router').change(function() {
            var routerId = $(this).val();
            if (!routerId) return;

            paketSelect.disable();
            paketSelect.clearOptions();
            paketSelect.addOption({ value: '', text: 'Memuat paket...', disabled: true });
            paketSelect.refreshOptions(false);

            $.ajax({
                url: '/api/paket/by-router/' + routerId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    paketSelect.clearOptions();

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function(item) {
                            paketSelect.addOption({ value: item.id, text: item.nama_paket });
                        });
                        paketSelect.enable();
                    } else {
                        paketSelect.addOption({ value: '', text: 'Tidak ada paket tersedia', disabled: true });
                    }
                    paketSelect.refreshOptions(false);
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    paketSelect.clearOptions();
                    paketSelect.addOption({ value: '', text: 'Error memuat data', disabled: true });
                    paketSelect.refreshOptions(false);
                }
            });
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

    // Custom collapse toggle
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-tw-collapse]').forEach(function(header) {
            header.addEventListener('click', function() {
                var target = document.getElementById(header.getAttribute('data-tw-collapse'));
                if (!target) return;
                var isOpen = target.classList.toggle('tw-open');
                header.classList.toggle('tw-open', isOpen);
                header.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });
    });
</script>
{{-- Auto Fill --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const remoteAddressInput = document.getElementById('remote_address');
        const remoteIpInput = document.getElementById('remote');
        if (!remoteAddressInput || !remoteIpInput) return;
        let manualEdit = false;
        remoteAddressInput.addEventListener('input', function() {
            if (!manualEdit) {
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => { remoteIpInput.value = this.value; }, 300);
            }
        });
        remoteIpInput.addEventListener('input', function() { manualEdit = true; });
        remoteAddressInput.addEventListener('focus', function() { manualEdit = false; });
    });
</script>
{{-- JavaScript: TomSelect Modem + Cascading SN + Auto MAC --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modemBaruSelect = document.getElementById('modem-baru');
        var formTambahan = document.getElementById('form-tambahan');
        var macAddressInput = document.getElementById('mac_address');
        var infoStok = document.getElementById('info-stok');
        var serialStockAlert = document.getElementById('serial_stock_alert');
        if (!modemBaruSelect) return;

        // Data serial number dari ModemDetail tersedia
        var serialData = {!! json_encode(
            $modemDetails->groupBy('logistik_id')->map(function ($items) {
                return $items->map(function ($item) {
                    return ['id' => $item->id, 'sn' => $item->serial_number, 'mac' => $item->mac_address];
                })->values();
            })
        ) !!};

        function getModemStok(id) {
            var opt = document.querySelector('#modem-baru option[value="' + id + '"]');
            return opt ? parseInt(opt.getAttribute('data-stok')) || 0 : 0;
        }

        var tomModem = new TomSelect('#modem-baru', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: 'Pilih Modem Baru',
            allowEmptyOption: false,
            onChange: function(value) {
                if (value) {
                    formTambahan.style.display = 'block';
                    var stok = getModemStok(value);
                    infoStok.innerHTML = 'Pilih modem baru jika ganti modem.';
                    infoStok.className = 'mt-1 block text-xs ' + (stok > 0 ? 'text-emerald-600' : 'text-red-500');
                    if (stok == 0) {
                        infoStok.innerHTML += ' <i class="bx bx-error-circle"></i> Stok habis!';
                    }

                    var serials = serialData[value] || [];
                    tomSerial.clear(true);
                    tomSerial.clearOptions();
                    tomSerial.addOption({ value: '', text: 'Pilih Serial Number', disabled: true });
                    tomSerial.addOption(serials.map(function(s) {
                        return { value: s.id, text: s.sn, mac: s.mac };
                    }));
                    tomSerial.setValue('');
                    tomSerial.refreshOptions(false);

                    macAddressInput.value = '';
                    serialStockAlert.style.display = serials.length === 0 ? 'block' : 'none';
                } else {
                    formTambahan.style.display = 'none';
                    infoStok.innerHTML = 'Pilih modem baru jika ganti modem. (Optional)';
                    infoStok.className = 'mt-1 block text-xs text-ink-400';
                }
            }
        });

        var tomSerial = new TomSelect('#serial_number', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: 'Pilih Serial Number',
            allowEmptyOption: false,
            onChange: function(value) {
                if (value) {
                    var opt = tomSerial.options[value];
                    macAddressInput.value = opt && opt.mac ? opt.mac : '';
                } else {
                    macAddressInput.value = '';
                }
            }
        });

        // Trigger initial state
        if (modemBaruSelect.value) {
            tomModem.setValue(modemBaruSelect.value);
        }
    });
</script>
@endsection
