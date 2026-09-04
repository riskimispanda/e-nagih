@extends('layouts.contentNavbarLayout')
@section('title', 'Riwayat Tiket - ' . $customer->nama_customer)

@section('page-style')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                colors: {
                    brand: {
                        50: '#eff6ff',
                        100: '#dbeafe',
                        200: '#bfdbfe',
                        300: '#93c5fd',
                        400: '#60a5fa',
                        500: '#3b82f6',
                        600: '#2563eb',
                        700: '#1d4ed8',
                        800: '#1e40af',
                        900: '#1e3a8a'
                    }
                }
            }
        }
    };
</script>
<style>
    .status-btn {
        outline: none !important;
        box-shadow: none;
    }
    .status-btn.active-tab {
        background-color: #ffffff !important;
        color: #0f172a !important;
        font-weight: 600 !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
    .table-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
    .table-scroll::-webkit-scrollbar-track { background: #f8fafc; }
    .table-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    .copy-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        transition: all 0.3s ease;
    }
</style>
@endsection

@section('content')
@php
    $totalCount = $tickets->total();
    $selesaiCount = \App\Models\TiketOpen::where('customer_id', $customer->id)->where('status_id', 3)->count();
    $batalCount = \App\Models\TiketOpen::where('customer_id', $customer->id)->where('status_id', 19)->count();
    $pendingCount = \App\Models\TiketOpen::where('customer_id', $customer->id)->whereNotIn('status_id', [3, 19])->count();

    // Inisial avatar
    $names = explode(' ', trim($customer->nama_customer));
    $initials = strtoupper(substr($names[0] ?? 'C', 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));

    // GPS / Map URL
    $gps = $customer->gps;
    $isLink = Str::startsWith($gps, ['http://', 'https://']);
    $mapUrl = $isLink ? $gps : ($gps ? 'https://www.google.com/maps?q=' . urlencode($gps) : 'https://www.google.com/maps/search/' . urlencode($customer->alamat));

    // Phone cleanup for WhatsApp
    $rawPhone = preg_replace('/[^0-9]/', '', $customer->no_hp);
    if (Str::startsWith($rawPhone, '0')) {
        $waPhone = '62' . substr($rawPhone, 1);
    } else {
        $waPhone = $rawPhone;
    }
@endphp

<div class="space-y-4 pb-8">

    {{-- Breadcrumbs Navigation --}}
    <nav aria-label="Breadcrumb" class="flex items-center flex-wrap gap-1.5 text-xs text-slate-500">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-slate-500 hover:text-brand-600 transition no-underline">
            <i class="bx bx-home-alt text-sm text-slate-400"></i>
            <span>Dashboard</span>
        </a>
        <i class="bx bx-chevron-right text-[11px] text-slate-400"></i>
        <a href="/tiket-open" class="text-slate-500 hover:text-brand-600 transition no-underline">
            Tiket Open
        </a>
        <i class="bx bx-chevron-right text-[11px] text-slate-400"></i>
        <span class="text-brand-600 font-semibold flex items-center gap-1">
            <i class="bx bx-history text-xs"></i>
            <span>Riwayat Tiket</span>
        </span>
    </nav>

    <!-- Header Riwayat Pelanggan (Executive & Terpadu) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 bg-gradient-to-r from-slate-50/80 via-white to-brand-50/30">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                
                <!-- Sisi Kiri: Avatar & Identitas Pelanggan -->
                <div class="flex items-start sm:items-center gap-4">
                    <div class="relative shrink-0">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 via-indigo-600 to-indigo-700 text-white font-bold text-lg shadow-md shadow-brand-500/20">
                            {{ $initials }}
                        </div>
                        <span class="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-white text-[9px] text-white" title="Status Pelanggan">
                            <i class="bx bx-check"></i>
                        </span>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 tracking-tight m-0">
                                {{ $customer->nama_customer }}
                            </h1>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-medium bg-slate-100 text-slate-600 border border-slate-200/80">
                                #CUST-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}
                            </span>

                            <!-- Media Badge -->
                            @if($customer->media_id == 3)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                    <i class="bx bx-git-commit text-xs"></i> Fiber Optic
                                </span>
                            @elseif($customer->media_id == 2)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200/70">
                                    <i class="bx bx-broadcast text-xs"></i> Wireless
                                </span>
                            @elseif($customer->media_id == 1)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/70">
                                    <i class="bx bx-wifi text-xs"></i> Hotspot
                                </span>
                            @endif

                            <!-- Package Badge -->
                            @if($customer->paket?->nama_paket)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200/70">
                                <i class="bx bx-package text-xs"></i> {{ $customer->paket->nama_paket }}
                            </span>
                            @endif
                        </div>

                        <!-- Quick Contact Bar -->
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                            <span class="flex items-center gap-1 text-slate-700 font-medium">
                                <i class="bx bx-phone text-slate-400"></i>
                                <span>{{ $customer->no_hp }}</span>
                                @if($waPhone)
                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="inline-flex items-center gap-0.5 text-emerald-600 hover:text-emerald-700 font-semibold ml-0.5" title="Chat WhatsApp">
                                    <i class="bx bxl-whatsapp text-xs"></i> WA
                                </a>
                                @endif
                            </span>

                            <span class="text-slate-300">•</span>

                            <span class="flex items-center gap-1 text-slate-700 truncate max-w-[240px] sm:max-w-md">
                                <i class="bx bx-map text-slate-400"></i>
                                <span title="{{ $customer->alamat }}">{{ $customer->alamat ?: '-' }}</span>
                                @if($mapUrl)
                                <a href="{{ $mapUrl }}" target="_blank" class="text-brand-600 hover:text-brand-700 ml-0.5 inline-flex items-center" title="Buka di Maps">
                                    <i class="bx bx-link-external text-[11px]"></i>
                                </a>
                                @endif
                            </span>

                            @if($customer->email)
                            <span class="text-slate-300">•</span>
                            <span class="flex items-center gap-1 text-slate-700">
                                <i class="bx bx-envelope text-slate-400"></i>
                                <span>{{ $customer->email }}</span>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sisi Kanan: Action Buttons & Quick Summary -->
                <div class="flex flex-wrap items-center gap-2.5 lg:self-center">
                    <div class="hidden sm:flex flex-col items-end pr-3 border-r border-slate-200/80">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Riwayat</span>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                            <span>{{ $totalCount }} Tiket</span>
                            <span class="text-slate-300 font-normal">•</span>
                            <span class="text-emerald-600 font-semibold text-[11px]">{{ $selesaiCount }} Selesai</span>
                        </div>
                    </div>

                    <a href="/tiket-open" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900">
                        <i class="bx bx-arrow-back text-base"></i>
                        <span>Kembali</span>
                    </a>

                    @if(Route::has('open-tiket'))
                    <a href="{{ route('open-tiket', $customer->id) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-xs shadow-brand-500/20 transition hover:bg-brand-700">
                        <i class="bx bx-plus-circle text-base"></i>
                        <span>Buka Tiket Baru</span>
                    </a>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <!-- 3 Cards Grid: Kontak & Lokasi, Perangkat & Koneksi, Jalur Topologi Jaringan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Card 1: Kontak & Lokasi -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs flex flex-col justify-between space-y-3">
            <div class="flex items-center gap-2 text-slate-800 font-bold text-xs pb-2.5 border-b border-slate-100">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                    <i class="bx bx-user-pin text-base"></i>
                </div>
                <span>Kontak & Lokasi</span>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">No. Telepon</span>
                    <span class="font-semibold text-slate-800">{{ $customer->no_hp ?: '-' }}</span>
                </div>
                <div class="flex items-start justify-between gap-2">
                    <span class="text-slate-400 shrink-0">Alamat</span>
                    <div class="text-right font-semibold text-slate-800">
                        <span class="truncate max-w-[170px] inline-block" title="{{ $customer->alamat }}">{{ $customer->alamat ?: '-' }}</span>
                        @if($mapUrl)
                        <a href="{{ $mapUrl }}" target="_blank" class="text-brand-600 hover:text-brand-700 ml-1 inline-block align-middle" title="Buka di Google Maps">
                            <i class="bx bx-link-external text-xs"></i>
                        </a>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Email</span>
                    <span class="font-semibold text-slate-800 truncate max-w-[170px]" title="{{ $customer->email }}">{{ $customer->email ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Perangkat & Koneksi -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs flex flex-col justify-between space-y-3">
            <div class="flex items-center gap-2 text-slate-800 font-bold text-xs pb-2.5 border-b border-slate-100">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bx bx-devices text-base"></i>
                </div>
                <span>Perangkat & Koneksi</span>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Router Gateway</span>
                    <span class="font-semibold text-slate-800 truncate max-w-[170px]" title="{{ $customer->router?->nama_router ?? '-' }}">
                        {{ $customer->router?->nama_router ?? '-' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Koneksi / ONT</span>
                    <span class="font-semibold text-slate-800">
                        {{ $customer->koneksi?->nama_koneksi ?? '-' }} &bull; {{ $customer->perangkat?->nama_perangkat ?? '-' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">MAC / Serial</span>
                    <div class="flex items-center gap-1 font-mono font-semibold text-slate-800 text-[11px]">
                        <span>{{ $customer->mac_address ?: '-' }}</span>
                        @if($customer->mac_address)
                        <button type="button" onclick="copyText('{{ $customer->mac_address }}', 'MAC Address')" class="border-0 bg-transparent text-slate-400 hover:text-brand-600 p-0 cursor-pointer inline-flex items-center" title="Salin MAC">
                            <i class="bx bx-copy text-xs"></i>
                        </button>
                        @endif
                        <span class="text-slate-300 font-sans">/</span>
                        <span>{{ $customer->seri_perangkat ?: '-' }}</span>
                        @if($customer->seri_perangkat)
                        <button type="button" onclick="copyText('{{ $customer->seri_perangkat }}', 'Serial Number')" class="border-0 bg-transparent text-slate-400 hover:text-brand-600 p-0 cursor-pointer inline-flex items-center" title="Salin SN">
                            <i class="bx bx-copy text-xs"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Jalur Topologi Jaringan -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs flex flex-col justify-between space-y-3">
            <div class="flex items-center gap-2 text-slate-800 font-bold text-xs pb-2.5 border-b border-slate-100">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i class="bx bx-git-branch text-base"></i>
                </div>
                <span>Jalur Topologi Jaringan</span>
            </div>

            @if($customer->media_id == 3)
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">1. Server</span>
                    <span class="font-semibold text-slate-800 truncate max-w-[170px]" title="{{ $customer->odp?->odc?->olt?->server?->lokasi_server ?? '-' }}">
                        {{ $customer->odp?->odc?->olt?->server?->lokasi_server ?? '-' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">2. OLT</span>
                    <span class="font-semibold text-slate-800 truncate max-w-[170px]" title="{{ $customer->odp?->odc?->olt?->nama_lokasi ?? '-' }}">
                        {{ $customer->odp?->odc?->olt?->nama_lokasi ?? '-' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">3. ODC / ODP</span>
                    <span class="font-semibold text-slate-800 truncate max-w-[170px]">
                        {{ $customer->odp?->odc?->nama_odc ?? '-' }} / {{ $customer->odp?->nama_odp ?? '-' }}
                    </span>
                </div>
            </div>
            @elseif($customer->media_id == 2)
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Transceiver</span>
                    <span class="font-semibold text-slate-800">{{ $customer->transiver ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Receiver</span>
                    <span class="font-semibold text-slate-800">{{ $customer->receiver ?? '-' }}</span>
                </div>
            </div>
            @elseif($customer->media_id == 1)
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Access Point</span>
                    <span class="font-semibold text-slate-800">{{ $customer->access_point ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Station</span>
                    <span class="font-semibold text-slate-800">{{ $customer->station ?? '-' }}</span>
                </div>
            </div>
            @else
            <div class="text-slate-400 text-xs py-1">Jalur distribusi belum ditentukan</div>
            @endif
        </div>

    </div>

    <!-- Ticket History Table Section (Direct & Clean) -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">

        <!-- Table Control & Filter Header -->
        <div class="p-4 sm:px-5 sm:py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 m-0 flex items-center gap-2">
                    Riwayat Tiket
                    <span class="text-xs font-normal text-slate-400">({{ $totalCount }} catatan)</span>
                </h3>
            </div>

            <!-- Segmented Status Tabs & Search -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Segmented Filter Control (No ugly border, pure macOS/iOS style) -->
                <div class="inline-flex rounded-lg bg-slate-100/90 p-0.5 text-xs">
                    <button type="button" onclick="filterStatus('all')" class="status-btn active-tab border-0 bg-white text-slate-900 font-semibold px-2.5 py-1 rounded-md transition cursor-pointer" id="btn-all">
                        Semua ({{ $totalCount }})
                    </button>
                    <button type="button" onclick="filterStatus('selesai')" class="status-btn border-0 bg-transparent text-slate-500 font-medium px-2.5 py-1 rounded-md transition cursor-pointer hover:text-slate-900" id="btn-selesai">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span>Selesai ({{ $selesaiCount }})
                    </button>
                    <button type="button" onclick="filterStatus('menunggu')" class="status-btn border-0 bg-transparent text-slate-500 font-medium px-2.5 py-1 rounded-md transition cursor-pointer hover:text-slate-900" id="btn-menunggu">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 mr-1"></span>Menunggu ({{ $pendingCount }})
                    </button>
                    <button type="button" onclick="filterStatus('batal')" class="status-btn border-0 bg-transparent text-slate-500 font-medium px-2.5 py-1 rounded-md transition cursor-pointer hover:text-slate-900" id="btn-batal">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500 mr-1"></span>Batal ({{ $batalCount }})
                    </button>
                </div>

                <!-- Live Search Input -->
                <div class="relative min-w-[180px] sm:min-w-[200px]">
                    <i class="bx bx-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="ticketSearch" onkeyup="searchTickets()" placeholder="Cari tiket..." class="w-full pl-7 pr-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition">
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto table-scroll">
            <table class="w-full text-left border-collapse" id="ticketTable">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-2.5 px-4 w-12 text-center">#</th>
                        <th class="py-2.5 px-4">Kategori</th>
                        <th class="py-2.5 px-4 min-w-[240px]">Keterangan / Keluhan</th>
                        <th class="py-2.5 px-4 min-w-[160px]">Dibuat</th>
                        <th class="py-2.5 px-4 text-center">Status</th>
                        <th class="py-2.5 px-4 min-w-[220px]">Penyelesaian / Penutupan</th>
                        <th class="py-2.5 px-4 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                    @forelse($tickets as $ticket)
                    @php
                        // Status mapping
                        if ($ticket->status_id == 3) {
                            $statusType = 'selesai';
                        } elseif ($ticket->status_id == 19) {
                            $statusType = 'batal';
                        } else {
                            $statusType = 'menunggu';
                        }

                        // Category styling
                        $kat = strtolower($ticket->kategori->nama_kategori ?? '');
                        if (Str::contains($kat, 'gangguan')) {
                            $catBadge = 'bg-rose-50 text-rose-700 border-rose-200/70';
                        } elseif (Str::contains($kat, 'maintenance')) {
                            $catBadge = 'bg-blue-50 text-blue-700 border-blue-200/70';
                        } elseif (Str::contains($kat, ['pasang', 'instal'])) {
                            $catBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200/70';
                        } else {
                            $catBadge = 'bg-purple-50 text-purple-700 border-purple-200/70';
                        }
                    @endphp
                    <tr class="ticket-row hover:bg-slate-50/70 transition-colors" data-status="{{ $statusType }}">

                        <!-- Col 1: Number & ID -->
                        <td class="py-3 px-4 text-center font-semibold text-slate-400">
                            <span class="text-slate-500">{{ $tickets->firstItem() + $loop->index }}</span>
                            <span class="block text-[10px] text-slate-400 font-mono">#{{ $ticket->id }}</span>
                        </td>

                        <!-- Col 2: Kategori -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full font-semibold border {{ $catBadge }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $ticket->kategori->nama_kategori ?? 'Umum' }}
                            </span>
                        </td>

                        <!-- Col 3: Keterangan -->
                        <td class="py-3 px-4">
                            <div class="max-w-md">
                                <p class="text-slate-800 font-medium line-clamp-2 m-0 leading-relaxed" title="{{ $ticket->keterangan }}">
                                    {{ $ticket->keterangan }}
                                </p>
                                @if($ticket->foto)
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 text-[11px] text-brand-600 font-medium">
                                        <i class="bx bx-image-alt"></i> Lampiran Foto
                                    </span>
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Col 4: Waktu Dibuat -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1 text-slate-700 font-medium">
                                    <i class="bx bx-calendar text-slate-400 text-xs"></i>
                                    <span>{{ $ticket->created_at->translatedFormat('d M Y, H:i') }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-[11px] text-slate-500">
                                    <i class="bx bx-user text-slate-400 text-xs"></i>
                                    <span>Oleh: <strong class="text-slate-600 font-semibold">{{ $ticket->user->name ?? '-' }}</strong></span>
                                </div>
                            </div>
                        </td>

                        <!-- Col 5: Status -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($ticket->status_id == 3)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="bx bx-check text-sm"></i> Selesai
                                </span>
                            @elseif($ticket->status_id == 19)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class="bx bx-x text-sm"></i> Dibatalkan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="bx bx-time-five text-sm"></i> Menunggu
                                </span>
                            @endif
                        </td>

                        <!-- Col 6: Resolusi / Log Penutupan -->
                        <td class="py-3 px-4">
                            @if($ticket->status_id == 3)
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1 font-semibold text-slate-700">
                                        <i class="bx bx-wrench text-emerald-500 text-xs"></i>
                                        <span>Teknisi: {{ $ticket->teknisi->name ?? '-' }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 flex items-center gap-1">
                                        <i class="bx bx-check-double text-slate-400 text-xs"></i>
                                        <span>
                                            {{ $ticket->tanggal_selesai ? \Carbon\Carbon::parse($ticket->tanggal_selesai)->translatedFormat('d M Y, H:i') : \Carbon\Carbon::parse($ticket->updated_at)->translatedFormat('d M Y, H:i') }}
                                        </span>
                                    </div>
                                </div>
                            @elseif($ticket->status_id == 19)
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1 font-medium text-slate-700">
                                        <i class="bx bx-user-x text-rose-500 text-xs"></i>
                                        <span>Batal oleh: <strong class="text-slate-800">{{ $ticket->cancelledBy->name ?? '-' }}</strong></span>
                                    </div>
                                    @if($ticket->alasan_batal)
                                    <p class="text-[11px] text-rose-600 font-medium m-0 truncate max-w-[200px]" title="{{ $ticket->alasan_batal }}">
                                        Alasan: {{ $ticket->alasan_batal }}
                                    </p>
                                    @endif
                                    @if($ticket->cancelled_at)
                                    <span class="text-[10px] text-slate-400 block">
                                        {{ \Carbon\Carbon::parse($ticket->cancelled_at)->translatedFormat('d M Y, H:i') }}
                                    </span>
                                    @endif
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] text-amber-700 font-medium bg-amber-50/80 px-2 py-0.5 rounded">
                                    <i class="bx bx-loader-alt bx-spin text-xs"></i>
                                    Dalam antrean teknisi
                                </span>
                            @endif
                        </td>

                        <!-- Col 7: Aksi -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $ticket->id }}" class="border-0 inline-flex items-center gap-1 px-2.5 py-1 rounded border border-slate-200 bg-white text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-brand-600 transition cursor-pointer">
                                <i class="bx bx-show text-sm"></i>
                                <span>Detail</span>
                            </button>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 px-4 text-center">
                            <div class="flex flex-col items-center justify-center gap-2 text-slate-400">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 text-xl">
                                    <i class="bx bx-folder-open"></i>
                                </div>
                                <h6 class="text-sm font-bold text-slate-600 m-0 mt-1">Tidak Ada Riwayat Tiket</h6>
                                <p class="text-xs text-slate-400 m-0 max-w-sm">
                                    Belum ada catatan riwayat gangguan atau tiket yang pernah dibuat untuk pelanggan ini.
                                </p>
                                @if(Route::has('open-tiket'))
                                <a href="{{ route('open-tiket', $customer->id) }}" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 transition">
                                    <i class="bx bx-plus"></i> Buat Tiket Sekarang
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modals Container (Rendered outside table for valid DOM structure) -->
        @foreach($tickets as $ticket)
        @php
            $kat = strtolower($ticket->kategori->nama_kategori ?? '');
            if (Str::contains($kat, 'gangguan')) {
                $catBadge = 'bg-rose-50 text-rose-700 border-rose-200/70';
            } elseif (Str::contains($kat, 'maintenance')) {
                $catBadge = 'bg-blue-50 text-blue-700 border-blue-200/70';
            } elseif (Str::contains($kat, ['pasang', 'instal'])) {
                $catBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200/70';
            } else {
                $catBadge = 'bg-purple-50 text-purple-700 border-purple-200/70';
            }
        @endphp
        <div class="modal fade" id="modalDetail{{ $ticket->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 font-bold">
                                <i class="bx bx-receipt text-lg"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-slate-900 m-0 flex items-center gap-2">
                                    Detail Tiket #{{ $ticket->id }}
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $catBadge }}">
                                        {{ $ticket->kategori->nama_kategori ?? 'Umum' }}
                                    </span>
                                </h5>
                                <span class="text-[11px] text-slate-400">
                                    Pelanggan: <strong class="text-slate-600">{{ $customer->nama_customer }}</strong>
                                </span>
                            </div>
                        </div>

                        <button type="button" class="btn-close text-xs" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-5 text-left text-xs">

                        <!-- Status Banner -->
                        <div class="p-3.5 rounded-xl border flex items-center justify-between
                            @if($ticket->status_id == 3) bg-emerald-50/60 border-emerald-200 text-emerald-900
                            @elseif($ticket->status_id == 19) bg-rose-50/60 border-rose-200 text-rose-900
                            @else bg-amber-50/60 border-amber-200 text-amber-900 @endif">
                            <div class="flex items-center gap-2">
                                @if($ticket->status_id == 3)
                                    <i class="bx bx-check-circle text-lg text-emerald-600"></i>
                                    <span class="font-bold">Status Tiket: Selesai Ditangani</span>
                                @elseif($ticket->status_id == 19)
                                    <i class="bx bx-x-circle text-lg text-rose-600"></i>
                                    <span class="font-bold">Status Tiket: Dibatalkan</span>
                                @else
                                    <i class="bx bx-time-five text-lg text-amber-600"></i>
                                    <span class="font-bold">Status Tiket: Menunggu Teknisi</span>
                                @endif
                            </div>
                            <span class="text-[11px] opacity-80">
                                {{ $ticket->created_at->translatedFormat('d F Y, H:i') }}
                            </span>
                        </div>

                        <!-- Deskripsi Masalah / Keterangan -->
                        <div>
                            <label class="font-semibold text-slate-500 uppercase tracking-wider text-[11px] block mb-1.5">
                                Keterangan / Keluhan Gangguan
                            </label>
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 text-slate-800 text-xs leading-relaxed whitespace-pre-line font-normal">
                                {{ $ticket->keterangan ?: 'Tidak ada keterangan tambahan.' }}
                            </div>
                        </div>

                        <!-- Foto Bukti (jika ada) -->
                        @if($ticket->foto)
                        <div>
                            <label class="font-semibold text-slate-500 uppercase tracking-wider text-[11px] block mb-1.5">
                                Foto / Lampiran Bukti
                            </label>
                            <div class="rounded-xl border border-slate-200/80 overflow-hidden bg-slate-50 p-2 max-w-sm">
                                <a href="{{ asset('storage/' . $ticket->foto) }}" target="_blank" class="block group relative">
                                    <img src="{{ asset('storage/' . $ticket->foto) }}" alt="Foto Tiket" class="rounded-lg max-h-48 w-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white font-medium transition rounded-lg">
                                        <i class="bx bx-zoom-in text-xl mr-1"></i> Buka Foto Asli
                                    </div>
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- Audit Timeline -->
                        <div>
                            <label class="font-semibold text-slate-500 uppercase tracking-wider text-[11px] block mb-2">
                                Jejak Riwayat Penanganan
                            </label>

                            <div class="border border-slate-200/80 rounded-xl p-4 bg-slate-50/50 space-y-4">

                                <!-- Step 1: Dibuat -->
                                <div class="flex items-start gap-3">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700 text-sm">
                                        <i class="bx bx-plus"></i>
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-slate-800 m-0">Tiket Dibuat / Dibuka</p>
                                        <p class="text-slate-500 m-0">
                                            Oleh <strong class="text-slate-700">{{ $ticket->user->name ?? '-' }}</strong> pada {{ $ticket->created_at->translatedFormat('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>

                                @if($ticket->status_id == 3)
                                <!-- Step 2: Selesai -->
                                <div class="flex items-start gap-3">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm">
                                        <i class="bx bx-check"></i>
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-emerald-700 m-0">Tiket Selesai & Ditutup</p>
                                        <p class="text-slate-600 m-0">
                                            Diselesaikan oleh Teknisi: <strong class="text-slate-800">{{ $ticket->teknisi->name ?? '-' }}</strong>
                                        </p>
                                        <p class="text-[11px] text-slate-400 m-0">
                                            Waktu selesai: {{ $ticket->tanggal_selesai ? \Carbon\Carbon::parse($ticket->tanggal_selesai)->translatedFormat('d M Y H:i') : \Carbon\Carbon::parse($ticket->updated_at)->translatedFormat('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                @elseif($ticket->status_id == 19)
                                <!-- Step 2: Dibatalkan -->
                                <div class="flex items-start gap-3">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700 text-sm">
                                        <i class="bx bx-x"></i>
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-rose-700 m-0">Tiket Dibatalkan</p>
                                        <p class="text-slate-600 m-0">
                                            Dibatalkan oleh: <strong class="text-slate-800">{{ $ticket->cancelledBy->name ?? '-' }}</strong>
                                        </p>
                                        @if($ticket->alasan_batal)
                                        <p class="text-rose-600 font-medium m-0">
                                            Alasan: {{ $ticket->alasan_batal }}
                                        </p>
                                        @endif
                                        @if($ticket->cancelled_at)
                                        <p class="text-[11px] text-slate-400 m-0">
                                            Waktu: {{ \Carbon\Carbon::parse($ticket->cancelled_at)->translatedFormat('d M Y H:i') }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                                @else
                                <!-- Step 2: Menunggu -->
                                <div class="flex items-start gap-3">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-sm">
                                        <i class="bx bx-time"></i>
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-amber-700 m-0">Menunggu Penugasan Teknisi</p>
                                        <p class="text-slate-500 m-0">
                                            Tiket sedang dalam antrean tim Helpdesk & Teknisi lapangan.
                                        </p>
                                    </div>
                                </div>
                                @endif

                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-3 bg-slate-50 border-t border-slate-200/80 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">ID Tiket: #{{ $ticket->id }}</span>
                        <button type="button" class="border-0 px-3.5 py-1.5 rounded-lg bg-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-300 transition cursor-pointer" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
        @endforeach

        <!-- Table Footer / Pagination -->
        <div class="p-3.5 sm:px-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/40">
            <div class="text-xs text-slate-500">
                Menampilkan <span class="font-semibold text-slate-700">{{ $tickets->firstItem() ?? 0 }}</span> - <span class="font-semibold text-slate-700">{{ $tickets->lastItem() ?? 0 }}</span> dari <span class="font-semibold text-slate-700">{{ $tickets->total() }}</span> riwayat tiket
            </div>
            @if($tickets->hasPages())
            <div class="pagination-custom">
                {!! $tickets->links('pagination::bootstrap-5') !!}
            </div>
            @endif
        </div>

    </div>

</div>

<!-- Floating Copy Toast Notification -->
<div id="copyToast" class="copy-toast hidden opacity-0 bg-slate-900 text-white px-3.5 py-2 rounded-lg shadow-lg text-xs font-medium flex items-center gap-2">
    <i class="bx bx-check-circle text-emerald-400 text-sm"></i>
    <span id="copyToastMsg">Teks disalin ke clipboard!</span>
</div>

@endsection

@section('page-script')
<script>
    // Copy to Clipboard with toast feedback
    function copyText(text, label) {
        if (!text || text === '-' || text === 'Tidak ada') {
            showToast('Tidak ada ' + label + ' untuk disalin');
            return;
        }
        navigator.clipboard.writeText(text).then(function() {
            showToast(label + ' disalin: ' + text);
        }).catch(function() {
            var dummy = document.createElement("input");
            document.body.appendChild(dummy);
            dummy.setAttribute("value", text);
            dummy.select();
            document.execCommand("copy");
            document.body.removeChild(dummy);
            showToast(label + ' disalin: ' + text);
        });
    }

    function showToast(msg) {
        var toast = document.getElementById('copyToast');
        var toastMsg = document.getElementById('copyToastMsg');
        toastMsg.textContent = msg;
        toast.classList.remove('hidden');
        setTimeout(function() {
            toast.classList.remove('opacity-0');
        }, 10);
        setTimeout(function() {
            toast.classList.add('opacity-0');
            setTimeout(function() {
                toast.classList.add('hidden');
            }, 300);
        }, 2000);
    }

    // Client-side instant filter by status
    let currentFilter = 'all';
    function filterStatus(status) {
        currentFilter = status;

        // Update active tab styles
        document.querySelectorAll('.status-btn').forEach(function(btn) {
            btn.classList.remove('active-tab');
            btn.classList.add('text-slate-500', 'font-medium');
        });

        var activeBtn = document.getElementById('btn-' + status);
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-500', 'font-medium');
            activeBtn.classList.add('active-tab');
        }

        applyTableFilters();
    }

    // Client-side live search
    function searchTickets() {
        applyTableFilters();
    }

    function applyTableFilters() {
        var query = document.getElementById('ticketSearch').value.toLowerCase().trim();
        var rows = document.querySelectorAll('.ticket-row');

        rows.forEach(function(row) {
            var rowStatus = row.getAttribute('data-status');
            var rowText = row.innerText.toLowerCase();

            var matchesStatus = (currentFilter === 'all' || rowStatus === currentFilter);
            var matchesQuery = (query === '' || rowText.indexOf(query) > -1);

            if (matchesStatus && matchesQuery) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Init active button state & tooltips
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    });
</script>
@endsection
