@extends('layouts.contentNavbarLayout')
@section('title', 'Edit Pelanggan - ' . $pelanggan->nama_customer)

@php
    $userRole = strtolower(optional(auth()->user()->roles)->name ?? '');
    $userRoleId = (int)(auth()->user()->roles_id ?? 0);
    $canEditTechnical = auth()->check() && (
        in_array($userRoleId, [1, 4]) ||
        in_array($userRole, ['noc', 'super admin', 'superadmin'])
    );
    $canEditProrata = auth()->check() && (
        in_array($userRoleId, [1, 2, 4]) ||
        in_array($userRole, ['noc', 'super admin', 'superadmin', 'admin keuangan'])
    );
@endphp

@section('page-style')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                colors: {
                    brand: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' }
                }
            }
        }
    };
</script>
<style>
    .form-control-custom {
        width: 100%; height: 42px; border-radius: 0.75rem;
        border: 1.5px solid #e2e8f0; background: #fff;
        padding: 0.55rem 0.85rem; font-size: 0.875rem; color: #0f172a; line-height: 1.25rem;
        transition: all .15s ease; box-sizing: border-box;
    }
    .form-control-custom:hover { border-color: #94a3b8; }
    .form-control-custom:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .form-control-custom.is-invalid { border-color: #e11d48; background: #fff1f2; }
    textarea.form-control-custom { height: auto; min-height: 80px; }

    .ts-wrapper { width: 100% !important; }
    .ts-wrapper .ts-control {
        min-height: 42px !important; height: 42px !important; border-radius: .75rem !important;
        border: 1.5px solid #e2e8f0 !important; padding: .5rem .85rem !important; font-size: .875rem !important;
        background: #fff !important; box-shadow: none !important; display: flex !important;
        align-items: center !important; color: #0f172a !important; transition: all .15s ease !important;
    }
    .ts-wrapper:hover .ts-control { border-color: #94a3b8 !important; }
    .ts-wrapper.focus .ts-control { border-color: #2563eb !important; box-shadow: 0 0 0 3px rgba(37,99,235,.1) !important; }
    .ts-wrapper .ts-control input { font-size: .875rem !important; color: #0f172a !important; }
    .ts-dropdown {
        border-radius: .75rem !important; border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,.08) !important; padding: .35rem !important;
        z-index: 1050 !important; background: #fff !important;
    }
    .ts-dropdown .option { border-radius: .5rem !important; padding: .5rem .75rem !important; font-size: .875rem !important; color: #1e293b !important; }
    .ts-dropdown .option.active { background: #f1f5f9 !important; color: #0f172a !important; }
    .ts-dropdown .option.selected { background: #eff6ff !important; color: #1d4ed8 !important; font-weight: 600 !important; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .animate-section { animation: fadeIn .4s ease both; }
    .section-card { backdrop-filter: blur(8px); }
    .input-icon-group { position: relative; }
    .input-icon-group .icon-left {
        position: absolute; top: 50%; left: 12px; transform: translateY(-50%);
        color: #94a3b8; pointer-events: none; z-index: 1; transition: color .15s ease;
    }
    .input-icon-group:focus-within .icon-left { color: #2563eb; }
</style>
@endsection

@section('content')
<div class="max-w-12xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-slate-500">
        <a href="{{ route('pelanggan') }}" class="hover:text-blue-600 transition no-underline text-slate-500">Pelanggan</a>
        <i class="bx bx-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-slate-800 font-semibold truncate max-w-[200px]">{{ $pelanggan->nama_customer }}</span>
        <i class="bx bx-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-blue-600 font-semibold">Edit</span>
    </nav>

    <form id="formUpdatePelanggan" action="{{ route('pelanggan.update', $pelanggan->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Header Card --}}
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-2xl shadow-lg shadow-slate-900/10 overflow-hidden mb-6">
            <div class="relative px-5 sm:px-8 py-6 sm:py-7">
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-sm text-white flex items-center justify-center font-bold text-lg shrink-0 border border-white/10 shadow-lg shadow-black/20">
                            {{ strtoupper(substr($pelanggan->nama_customer ?? 'PL', 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2.5 flex-wrap mb-1.5">
                                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight m-0">{{ $pelanggan->nama_customer }}</h1>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-white/10 text-white/70 font-mono tracking-wide">
                                    #{{ $pelanggan->id }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                @if(optional($pelanggan->status)->nama_status)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        {{ $pelanggan->status->nama_status }}
                                    </span>
                                @endif
                                @if($canEditTechnical)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-blue-500/15 text-blue-400 border border-blue-500/20">
                                        <i class="bx bx-shield-quarter text-xs"></i> Admin & NOC
                                    </span>
                                @elseif($canEditProrata)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/20">
                                        <i class="bx bx-dollar text-xs"></i> Admin Keuangan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-white/10 text-white/60 border border-white/10">
                                        <i class="bx bx-user text-xs"></i> Data Kontak
                                    </span>
                                @endif
                                <span class="text-white/30 hidden sm:inline">|</span>
                                <p class="text-[11px] text-white/40 m-0 flex items-center gap-2 flex-wrap">
                                    <span>Paket: <strong class="text-white/60">{{ $pelanggan->paket->nama_paket ?? '-' }}</strong></span>
                                    <span class="text-white/20">/</span>
                                    <span>Router: <strong class="text-white/60">{{ $pelanggan->router->nama_router ?? '-' }}</strong></span>
                                    <span class="text-white/20">/</span>
                                    <span>Media: <strong class="text-white/60">{{ $pelanggan->media->nama_media ?? '-' }}</strong></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white/80 hover:text-white font-semibold text-xs transition-all duration-200 no-underline shrink-0 border border-white/10 hover:border-white/20 backdrop-blur-sm">
                        <i class="bx bx-arrow-back text-base"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        {{-- Form Body --}}
        <div class="space-y-5">

            {{-- SECTION 1: Informasi Pribadi --}}
                <div id="sectionPribadi" class="animate-section relative bg-white rounded-2xl border border-slate-200/80 shadow-sm shadow-slate-200/50">
                <div class="px-5 sm:px-7 py-5 ">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base shrink-0">
                                <i class="bx bx-user"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 m-0">Informasi Pribadi & Kontak</h3>
                                <p class="text-[11px] text-slate-400 m-0 mt-0.5">Biodata pelanggan, kontak aktif, dan lokasi pemasangan</p>
                            </div>
                        </div>
                        <span class="hidden sm:inline-flex text-[10px] font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100 tracking-wide uppercase">
                            {{ $canEditTechnical ? '1 / 3' : 'Data' }}
                        </span>
                    </div>
                </div>
                <div class="h-px bg-gradient-to-r from-transparent via-slate-300 to-transparent mx-5 sm:mx-7"></div>

                <div class="p-5 sm:p-7 space-y-5">

                    {{-- Prorata --}}
                    @if($canEditProrata)
                        <div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 mb-2.5">
                                <i class="bx bx-dollar text-blue-500 text-sm"></i>
                                Status Prorata Tagihan
                                <span class="text-[10px] font-medium text-blue-500 bg-blue-50 px-2 py-0.5 rounded-md">Admin & Keuangan</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="relative flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 bg-white hover:border-blue-300 hover:bg-blue-50/30 cursor-pointer transition-all duration-200 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:shadow-sm has-[:checked]:shadow-blue-500/10">
                                    <input type="radio" name="jenis_pelanggan" value="lama" {{ old('jenis_pelanggan', 'lama') == 'lama' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-xs">Pelanggan Lama</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">Tagihan bulanan normal tanpa prorata</div>
                                    </div>
                                </label>
                                <label class="relative flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 bg-white hover:border-blue-300 hover:bg-blue-50/30 cursor-pointer transition-all duration-200 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:shadow-sm has-[:checked]:shadow-blue-500/10">
                                    <input type="radio" name="jenis_pelanggan" value="baru" {{ old('jenis_pelanggan', 'lama') == 'baru' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-xs">Pelanggan Baru</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">Prorata proporsional pada tagihan awal</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="jenis_pelanggan" value="{{ old('jenis_pelanggan', 'lama') }}">
                    @endif

                    {{-- Form Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">

                        {{-- Nama --}}
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="nama_customer">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <div class="input-icon-group">
                                <i class="icon-left bx bx-user text-[15px]"></i>
                                <input type="text" class="form-control-custom pl-10 @error('nama') is-invalid @enderror" id="nama_customer" name="nama" value="{{ old('nama', $pelanggan->nama_customer) }}" placeholder="Nama lengkap pelanggan" required>
                            </div>
                            @error('nama') <p class="mt-1.5 text-[11px] text-rose-500 flex items-center gap-1 m-0"><i class="bx bx-error-circle text-sm"></i> {{ $message }}</p> @enderror
                        </div>

                        {{-- No HP --}}
                        <div>
                            <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="no_hp">
                                Nomor HP / WhatsApp <span class="text-rose-500">*</span>
                            </label>
                            <div class="input-icon-group">
                                <i class="icon-left bx bx-phone text-[15px]"></i>
                                <input type="text" class="form-control-custom pl-10 font-mono tracking-wide @error('no_hp') is-invalid @enderror" id="no_hp" name="no_hp" value="{{ old('no_hp', $pelanggan->no_hp) }}" placeholder="628123456789" required>
                            </div>
                            @error('no_hp') <p class="mt-1.5 text-[11px] text-rose-500 flex items-center gap-1 m-0"><i class="bx bx-error-circle text-sm"></i> {{ $message }}</p> @enderror
                        </div>

                        {{-- NIK --}}
                        <div>
                            <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="no_identitas">
                                Nomor Identitas (NIK/KTP)
                            </label>
                            <div class="input-icon-group">
                                <i class="icon-left bx bx-id-card text-[15px]"></i>
                                <input type="text" class="form-control-custom pl-10 font-mono tracking-wide @error('no_identitas') is-invalid @enderror" id="no_identitas" name="no_identitas" value="{{ old('no_identitas', $pelanggan->no_identitas) }}" placeholder="16 digit NIK">
                            </div>
                            @error('no_identitas') <p class="mt-1.5 text-[11px] text-rose-500 flex items-center gap-1 m-0"><i class="bx bx-error-circle text-sm"></i> {{ $message }}</p> @enderror
                        </div>

                        {{-- Alamat --}}
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="alamat">
                                Alamat Lengkap Pemasangan
                            </label>
                            <div class="input-icon-group">
                                <i class="icon-left bx bx-map-pin text-[15px]"></i>
                                <input type="text" class="form-control-custom pl-10 @error('alamat') is-invalid @enderror" id="alamat" name="alamat" value="{{ old('alamat', $pelanggan->alamat) }}" placeholder="Jalan, RT/RW, Dusun, Kelurahan, Kecamatan">
                            </div>
                            @error('alamat') <p class="mt-1.5 text-[11px] text-rose-500 flex items-center gap-1 m-0"><i class="bx bx-error-circle text-sm"></i> {{ $message }}</p> @enderror
                        </div>

                        {{-- GPS --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-semibold text-slate-700 m-0" for="gps">Koordinat GPS</label>
                                @if($pelanggan->gps)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($pelanggan->gps) }}" target="_blank" class="text-[11px] text-blue-600 hover:text-blue-700 inline-flex items-center gap-1 font-semibold no-underline transition">
                                        <i class="bx bx-map-pin"></i> Maps
                                    </a>
                                @endif
                            </div>
                            <div class="input-icon-group">
                                <i class="icon-left bx bx-current-location text-[15px]"></i>
                                <input type="text" class="form-control-custom pl-10 font-mono text-xs tracking-wide @error('gps') is-invalid @enderror" id="gps" name="gps" value="{{ old('gps', $pelanggan->gps) }}" placeholder="-6.xxx, 106.xxx">
                            </div>
                            @error('gps') <p class="mt-1.5 text-[11px] text-rose-500 flex items-center gap-1 m-0"><i class="bx bx-error-circle text-sm"></i> {{ $message }}</p> @enderror
                        </div>

                        {{-- PIC --}}
                        <div>
                            <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="pic">Sales / Agen / PIC</label>
                            <select name="agen_id" class="form-control-custom" id="pic">
                                <option value="" disabled {{ old('agen_id', $pelanggan->agen_id) ? '' : 'selected' }}>Pilih Sales / Agen</option>
                                @foreach ($agen as $item)
                                    <option value="{{ $item->id }}" {{ old('agen_id', $pelanggan->agen_id) == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="hidden lg:block"></div>

                        {{-- Upload Foto Identitas --}}
                        <div class="sm:col-span-1">
                            <div class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/50 hover:border-blue-300 hover:bg-blue-50/20 transition-colors duration-200">
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 mb-2" for="foto_identitas">
                                    <i class="bx bx-camera text-slate-400 text-sm"></i> Foto Identitas
                                </label>
                                @if($pelanggan->identitas)
                                    <div class="mb-3 flex items-center gap-3 p-2.5 bg-white rounded-lg border border-slate-200 shadow-xs">
                                        <a href="{{ asset($pelanggan->identitas) }}" target="_blank" class="block shrink-0 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 hover:opacity-80 transition">
                                            <img src="{{ asset($pelanggan->identitas) }}" alt="Identitas" class="w-11 h-11 object-cover">
                                        </a>
                                        <div class="min-w-0 flex-1">
                                            <span class="text-[10px] text-slate-400 block font-medium">Tersimpan</span>
                                            <a href="{{ asset($pelanggan->identitas) }}" target="_blank" class="text-[11px] text-blue-600 font-semibold hover:text-blue-700 inline-flex items-center gap-1 no-underline transition">
                                                <i class="bx bx-show text-xs"></i> Lihat Foto
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" class="block w-full text-[11px] text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer border border-slate-200 rounded-lg p-1 bg-white transition" id="foto_identitas" name="identitas_file" accept="image/*">
                                @error('identitas_file') <p class="mt-1.5 text-[11px] text-rose-500 flex items-center gap-1 m-0"><i class="bx bx-error-circle text-sm"></i> {{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Upload Foto Rumah --}}
                        <div class="sm:col-span-1">
                            <div class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/50 hover:border-blue-300 hover:bg-blue-50/20 transition-colors duration-200">
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 mb-2" for="foto_rumah">
                                    <i class="bx bx-home text-slate-400 text-sm"></i> Foto Lokasi
                                </label>
                                @if($pelanggan->foto_rumah)
                                    <div class="mb-3 flex items-center gap-3 p-2.5 bg-white rounded-lg border border-slate-200 shadow-xs">
                                        <a href="{{ asset($pelanggan->foto_rumah) }}" target="_blank" class="block shrink-0 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 hover:opacity-80 transition">
                                            <img src="{{ asset($pelanggan->foto_rumah) }}" alt="Foto Rumah" class="w-11 h-11 object-cover">
                                        </a>
                                        <div class="min-w-0 flex-1">
                                            <span class="text-[10px] text-slate-400 block font-medium">Tersimpan</span>
                                            <a href="{{ asset($pelanggan->foto_rumah) }}" target="_blank" class="text-[11px] text-blue-600 font-semibold hover:text-blue-700 inline-flex items-center gap-1 no-underline transition">
                                                <i class="bx bx-show text-xs"></i> Lihat Foto
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" class="block w-full text-[11px] text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer border border-slate-200 rounded-lg p-1 bg-white transition" id="foto_rumah" name="foto_rumah" accept="image/*">
                                @error('foto_rumah') <p class="mt-1.5 text-[11px] text-rose-500 flex items-center gap-1 m-0"><i class="bx bx-error-circle text-sm"></i> {{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: Layanan & Jaringan --}}
            @if($canEditTechnical)
                <div id="sectionLayanan" class="animate-section relative bg-white rounded-2xl border border-slate-200/80 shadow-sm shadow-slate-200/50" style="animation-delay: .1s">
                    <div class="px-5 sm:px-7 py-5 ">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-base shrink-0">
                                    <i class="bx bx-wifi"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 m-0">Layanan & Distribusi Jaringan</h3>
                                    <p class="text-[11px] text-slate-400 m-0 mt-0.5">Paket internet, router Mikrotik, dan jalur distribusi transmisi</p>
                                </div>
                            </div>
                            <span class="hidden sm:inline-flex text-[10px] font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100 tracking-wide uppercase">2 / 3</span>
                        </div>
                    </div>
                    <div class="h-px bg-gradient-to-r from-transparent via-slate-300 to-transparent mx-5 sm:mx-7"></div>

                    <div class="p-5 sm:p-7">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                            {{-- Paket --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="paket">Paket Langganan <span class="text-rose-500">*</span></label>
                                <select name="paket" id="paket" class="form-control-custom" required>
                                    @foreach ($paket as $item)
                                        <option value="{{ $item->id }}" {{ old('paket', $pelanggan->paket_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_paket }} (Rp {{ number_format($item->harga, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Router --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="router">Router / NAS <span class="text-rose-500">*</span></label>
                                <select name="router" id="router" class="form-control-custom" required>
                                    @foreach ($router as $item)
                                        <option value="{{ $item->id }}" {{ old('router', $pelanggan->router_id) == $item->id ? 'selected' : '' }}>{{ $item->nama_router }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- BTS --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="bts">BTS Server</label>
                                <select name="bts" id="bts" class="form-control-custom">
                                    @foreach ($bts as $item)
                                        <option value="{{ $item->id }}" {{ old('bts', $pelanggan->getServer->id ?? '') == $item->id ? 'selected' : '' }}>{{ $item->lokasi_server }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Koneksi --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="koneksi">Jenis Koneksi</label>
                                <select name="koneksi" id="koneksi" class="form-control-custom">
                                    @foreach ($koneksi as $item)
                                        <option value="{{ $item->id }}" {{ old('koneksi', $pelanggan->koneksi_id) == $item->id ? 'selected' : '' }}>{{ $item->nama_koneksi }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Media --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="media">Media Koneksi</label>
                                <select name="media" id="media" class="form-control-custom">
                                    @foreach ($media as $item)
                                        <option value="{{ $item->id }}" {{ old('media', $pelanggan->media_id) == $item->id ? 'selected' : '' }}>{{ $item->nama_media }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Conditional Media Fields --}}
                            @if($pelanggan->media_id == 3)
                                <div>
                                    <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="olt">OLT</label>
                                    <select name="olt" id="olt" class="form-control-custom">
                                        <option value="" selected>-</option>
                                        @foreach ($olt as $item)
                                            <option value="{{ $item->id }}" {{ ($pelanggan->odp->odc->olt->id ?? null) == $item->id ? 'selected' : '' }}>{{ $item->nama_lokasi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="odc">ODC</label>
                                    <select name="odc" id="odc" class="form-control-custom">
                                        <option value="" selected>-</option>
                                        @foreach ($odc as $item)
                                            <option value="{{ $item->id }}" {{ ($pelanggan->odp->odc->id ?? null) == $item->id ? 'selected' : '' }}>{{ $item->nama_odc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="odp">ODP</label>
                                    <select name="odp" id="odp" class="form-control-custom">
                                        <option value="{{ $pelanggan->lokasi_id }}" selected>{{ $pelanggan->odp->nama_odp ?? '-' }}</option>
                                        @foreach ($odp as $item)
                                            <option value="{{ $item->id }}" {{ old('odp', $pelanggan->lokasi_id) == $item->id ? 'selected' : '' }}>{{ $item->nama_odp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif($pelanggan->media_id == 2)
                                <div>
                                    <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5">Access Point</label>
                                    <div class="input-icon-group">
                                        <i class="icon-left bx bx-broadcast text-[15px]"></i>
                                        <input type="text" class="form-control-custom pl-10" name="access_point" value="{{ old('access_point', $pelanggan->access_point) }}" placeholder="Nama / SSID Access Point">
                                    </div>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5">Station</label>
                                    <div class="input-icon-group">
                                        <i class="icon-left bx bx-radio text-[15px]"></i>
                                        <input type="text" class="form-control-custom pl-10" name="station" value="{{ old('station', $pelanggan->station) }}" placeholder="Identitas Station">
                                    </div>
                                </div>
                            @elseif($pelanggan->media_id == 1)
                                <div>
                                    <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5">Transceiver</label>
                                    <div class="input-icon-group">
                                        <i class="icon-left bx bx-transfer text-[15px]"></i>
                                        <input type="text" class="form-control-custom pl-10" name="transiver" value="{{ old('transiver', $pelanggan->transiver) }}" placeholder="Port / Detail Transceiver">
                                    </div>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5">Receiver</label>
                                    <div class="input-icon-group">
                                        <i class="icon-left bx bx-transfer-alt text-[15px]"></i>
                                        <input type="text" class="form-control-custom pl-10" name="receiver" value="{{ old('receiver', $pelanggan->receiver) }}" placeholder="Port / Detail Receiver">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <input type="hidden" name="paket" value="{{ $pelanggan->paket_id }}">
                <input type="hidden" name="router" value="{{ $pelanggan->router_id }}">
                <input type="hidden" name="bts" value="{{ $pelanggan->getServer->id ?? '' }}">
                <input type="hidden" name="koneksi" value="{{ $pelanggan->koneksi_id }}">
                <input type="hidden" name="media" value="{{ $pelanggan->media_id }}">
                <input type="hidden" name="odp" value="{{ $pelanggan->lokasi_id }}">
                <input type="hidden" name="access_point" value="{{ $pelanggan->access_point }}">
                <input type="hidden" name="station" value="{{ $pelanggan->station }}">
                <input type="hidden" name="transiver" value="{{ $pelanggan->transiver }}">
                <input type="hidden" name="receiver" value="{{ $pelanggan->receiver }}">
            @endif

            {{-- SECTION 3: Konfigurasi Teknis --}}
            @if($canEditTechnical)
                <div id="sectionTeknis" class="animate-section relative bg-white rounded-2xl border border-slate-200/80 shadow-sm shadow-slate-200/50" style="animation-delay: .2s">
                    <div class="px-5 sm:px-7 py-5 ">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base shrink-0">
                                    <i class="bx bx-server"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 m-0">Konfigurasi Teknis & Perangkat CPE</h3>
                                    <p class="text-[11px] text-slate-400 m-0 mt-0.5">Autentikasi PPPoE Mikrotik dan spesifikasi perangkat modem</p>
                                </div>
                            </div>
                            <span class="hidden sm:inline-flex text-[10px] font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100 tracking-wide uppercase">3 / 3</span>
                        </div>
                    </div>
                    <div class="h-px bg-gradient-to-r from-transparent via-slate-300 to-transparent mx-5 sm:mx-7"></div>

                    <div class="p-5 sm:p-7">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                            {{-- PPPoE User --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5">PPPoE Secret User</label>
                                <div class="input-icon-group">
                                    <i class="icon-left bx bx-user-circle text-[15px]"></i>
                                    <input type="text" class="form-control-custom pl-10 font-mono text-xs tracking-wide" name="usersecret" value="{{ old('usersecret', $pelanggan->usersecret) }}" placeholder="Username PPPoE">
                                </div>
                            </div>

                            {{-- Password --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5">Password Secret</label>
                                <div class="input-icon-group">
                                    <i class="icon-left bx bx-key text-[15px]"></i>
                                    <input type="text" class="form-control-custom pl-10 font-mono text-xs tracking-wide" name="pass_secret" value="{{ old('pass_secret', $pelanggan->pass_secret) }}" placeholder="Password PPPoE">
                                </div>
                            </div>

                            {{-- Local Address --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5">Local Address</label>
                                <div class="input-icon-group">
                                    <i class="icon-left bx bx-network-chart text-[15px]"></i>
                                    <input type="text" class="form-control-custom pl-10 font-mono text-xs tracking-wide" name="local_address" value="{{ old('local_address', $pelanggan->local_address) }}" placeholder="10.10.10.1">
                                </div>
                            </div>

                            {{-- Remote Address --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5">Remote Address</label>
                                <div class="input-icon-group">
                                    <i class="icon-left bx bx-globe text-[15px]"></i>
                                    <input type="text" class="form-control-custom pl-10 font-mono text-xs tracking-wide" name="remote_address" value="{{ old('remote_address', $pelanggan->remote_address) }}" placeholder="10.10.10.2">
                                </div>
                            </div>

                            {{-- Remote Mgmt --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5">Remote Management IP</label>
                                <div class="input-icon-group">
                                    <i class="icon-left bx bx-devices text-[15px]"></i>
                                    <input type="text" class="form-control-custom pl-10 font-mono text-xs tracking-wide" name="remote" value="{{ old('remote', $pelanggan->remote) }}" placeholder="IP Remote Modem">
                                </div>
                            </div>

                            {{-- Modem --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="perangkat">Modem</label>
                                <select name="perangkat" id="perangkat" class="form-control-custom">
                                    @foreach ($perangkat as $item)
                                        <option value="{{ $item->id }}" {{ old('perangkat', $pelanggan->perangkat_id) == $item->id ? 'selected' : '' }}>{{ $item->nama_perangkat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Serial Number (dari logistik) --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="modem_detail_id">Serial Number</label>
                                @php
                                    $selectedSeriId = null;
                                    $selectedSeriMac = $pelanggan->mac_address;
                                    $selectedSeriSn = $pelanggan->seri_perangkat;
                                    foreach ($logistikSerial as $ls) {
                                        if ($ls['logistik_id'] == $pelanggan->perangkat_id && $ls['serial_number'] == $pelanggan->seri_perangkat) {
                                            $selectedSeriId = $ls['id'];
                                            $selectedSeriMac = $ls['mac_address'] ?? $pelanggan->mac_address;
                                            $selectedSeriSn = $ls['serial_number'];
                                            break;
                                        }
                                    }
                                @endphp
                                <input type="hidden" name="seri" id="seri_input" value="{{ old('seri', $selectedSeriSn) }}">
                                <select name="modem_detail_id" id="modem_detail_id" class="form-control-custom">
                                    <option value="" data-sn="{{ $selectedSeriSn }}" data-mac="{{ $selectedSeriMac }}" data-logistik="{{ $pelanggan->perangkat_id }}">
                                        {{ $selectedSeriSn ? $selectedSeriSn . ' (SN saat ini)' : 'Pilih Serial Number' }}
                                    </option>
                                    @foreach ($logistikSerial as $ls)
                                        <option value="{{ $ls['id'] }}" data-sn="{{ $ls['serial_number'] }}" data-mac="{{ $ls['mac_address'] }}" data-logistik="{{ $ls['logistik_id'] }}" {{ $selectedSeriId == $ls['id'] ? 'selected' : '' }}>
                                            {{ $ls['serial_number'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- MAC Address (terisi otomatis) --}}
                            <div>
                                <label class="flex items-center gap-1 text-xs font-semibold text-slate-700 mb-1.5" for="mac">MAC Address</label>
                                <div class="input-icon-group">
                                    <i class="icon-left bx bx-chip text-[15px]"></i>
                                    <input type="text" class="form-control-custom pl-10 font-mono text-xs tracking-wide uppercase" name="mac" id="mac" value="{{ old('mac', $selectedSeriMac) }}" placeholder="AA:BB:CC:DD:EE:FF" readonly>
                                </div>
                                <p class="mt-1 text-[10px] text-slate-400 m-0 flex items-center gap-1">
                                    <i class="bx bx-info-circle text-xs"></i> Terisi otomatis dari data logistik berdasarkan SN terpilih.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <input type="hidden" name="usersecret" value="{{ $pelanggan->usersecret }}">
                <input type="hidden" name="pass_secret" value="{{ $pelanggan->pass_secret }}">
                <input type="hidden" name="local_address" value="{{ $pelanggan->local_address }}">
                <input type="hidden" name="remote_address" value="{{ $pelanggan->remote_address }}">
                <input type="hidden" name="remote" value="{{ $pelanggan->remote }}">
                <input type="hidden" name="perangkat" value="{{ $pelanggan->perangkat_id }}">
                <input type="hidden" name="modem_detail_id" value="{{ $selectedSeriId ?? '' }}">
                <input type="hidden" name="seri" value="{{ $pelanggan->seri_perangkat }}">
                <input type="hidden" name="mac" value="{{ $pelanggan->mac_address }}">

                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="bx bx-lock-alt text-base"></i>
                    </div>
                    <div class="text-xs text-amber-800 leading-relaxed">
                        <span class="font-bold">Catatan Hak Akses:</span>
                        Bagian <strong>Layanan & Jaringan</strong> serta <strong>Konfigurasi Teknis CPE</strong> dikunci. Hanya <strong>Super Admin</strong> atau <strong>NOC</strong> yang dapat memperbarui data ini.
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer Action Bar --}}
        <div class="mt-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm shadow-slate-200/50">
            <div class="px-5 sm:px-7 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2.5 text-[11px] text-slate-400 text-center sm:text-left">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                        <i class="bx bx-sync text-sm text-slate-500"></i>
                    </div>
                    <span>Perubahan akan langsung tersinkronisasi ke database & router Mikrotik.</span>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ url()->previous() }}" class="flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all duration-200 no-underline text-center">
                        Batal
                    </a>
                    <button type="submit" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-7 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold text-xs transition-all duration-200 cursor-pointer border-0 shadow-md shadow-blue-600/20 hover:shadow-lg hover:shadow-blue-600/30">
                        <i class="bx bx-check-circle text-base"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const noHpInput = document.getElementById('no_hp');
    if (noHpInput) {
        const formatNoHp = function () {
            let val = noHpInput.value.replace(/\D/g, '');
            if (val.startsWith('0')) val = '62' + val.substring(1);
            noHpInput.value = val;
        };
        noHpInput.addEventListener('input', formatNoHp);
        noHpInput.addEventListener('blur', formatNoHp);
    }

    const selectConfigs = ['#paket','#router','#olt','#odc','#odp','#bts','#perangkat','#pic','#media','#koneksi'];
    const tsInstances = {};
    selectConfigs.forEach(selector => {
        const el = document.querySelector(selector);
        if (el && !el.tomselect) {
            const ts = new TomSelect(el, { create: false, sortField: { field: "text", direction: "asc" } });
            tsInstances[selector] = ts;
            ts.on('dropdown_open', function() {
                const section = el.closest('.animate-section');
                if (section) section.style.zIndex = '50';
            });
            ts.on('dropdown_close', function() {
                const section = el.closest('.animate-section');
                if (section) section.style.zIndex = '';
            });
        }
    });

    // Otomatis isi MAC Address dan Serial Number berdasarkan Modem terpilih dari logistik
    const macInput = document.getElementById('mac');
    const seriInput = document.getElementById('seri_input');
    const perangkatSelect = document.getElementById('perangkat');
    const modemDetailSelect = document.getElementById('modem_detail_id');

    const logistikSerialData = @json($logistikSerial);
    const initialPerangkatId = "{{ $pelanggan->perangkat_id }}";
    const initialModemDetailId = "{{ $selectedSeriId ?? '' }}";
    const initialSeriSn = "{{ $selectedSeriSn ?? '' }}";
    const initialMac = "{{ $selectedSeriMac ?? '' }}";

    let tsModem = null;
    if (modemDetailSelect) {
        tsModem = new TomSelect(modemDetailSelect, {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
        tsInstances['#modem_detail_id'] = tsModem;

        tsModem.on('dropdown_open', function() {
            const section = modemDetailSelect.closest('.animate-section');
            if (section) section.style.zIndex = '50';
        });
        tsModem.on('dropdown_close', function() {
            const section = modemDetailSelect.closest('.animate-section');
            if (section) section.style.zIndex = '';
        });

        tsModem.on('change', function(val) {
            if (!val) {
                if (seriInput) seriInput.value = initialSeriSn || '';
                if (macInput) macInput.value = initialMac || '';
                return;
            }
            const found = logistikSerialData.find(item => String(item.id) === String(val));
            if (found) {
                if (macInput) macInput.value = found.mac_address || '';
                if (seriInput) seriInput.value = found.serial_number || '';
            }
        });

        function updateModemSerialOptions(selectedPerangkatId, preserveVal = null) {
            if (!tsModem) return;
            const currentSelected = preserveVal !== null ? preserveVal : tsModem.getValue();
            tsModem.clear();
            tsModem.clearOptions();

            // Tambahkan opsi fallback SN saat ini jika tipe perangkat cocok
            if (initialSeriSn && (String(initialPerangkatId) === String(selectedPerangkatId) || !selectedPerangkatId)) {
                tsModem.addOption({
                    value: initialModemDetailId ? String(initialModemDetailId) : '',
                    text: initialSeriSn + ' (SN saat ini)'
                });
            }

            // Tambahkan serial number yang sesuai perangkat dari logistik
            logistikSerialData.forEach(item => {
                if (String(item.logistik_id) === String(selectedPerangkatId)) {
                    tsModem.addOption({
                        value: String(item.id),
                        text: item.serial_number
                    });
                }
            });

            tsModem.refreshOptions(false);

            // Tentukan pilihan aktif
            if (currentSelected && tsModem.options[String(currentSelected)]) {
                tsModem.setValue(String(currentSelected));
            } else if (initialModemDetailId && tsModem.options[String(initialModemDetailId)]) {
                tsModem.setValue(String(initialModemDetailId));
            } else if (initialSeriSn && tsModem.options['']) {
                tsModem.setValue('');
            } else {
                const keys = Object.keys(tsModem.options);
                if (keys.length > 0) {
                    tsModem.setValue(keys[0]);
                }
            }
        }

        // Hubungkan perubahan pilihan perangkat ke daftar serial number
        if (tsInstances['#perangkat']) {
            tsInstances['#perangkat'].on('change', function(val) {
                updateModemSerialOptions(val);
            });
        } else if (perangkatSelect) {
            perangkatSelect.addEventListener('change', function() {
                updateModemSerialOptions(this.value);
            });
        }

        // Inisialisasi awal saat form dibuka
        const curPerangkat = tsInstances['#perangkat'] ? tsInstances['#perangkat'].getValue() : (perangkatSelect ? perangkatSelect.value : initialPerangkatId);
        updateModemSerialOptions(curPerangkat, initialModemDetailId);
    }

    const form = document.getElementById('formUpdatePelanggan');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Data',
                text: "Apakah data pelanggan sudah benar? Perubahan akan disimpan dan disinkronkan.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) this.submit();
            });
        });
    }
});
</script>
@endpush
