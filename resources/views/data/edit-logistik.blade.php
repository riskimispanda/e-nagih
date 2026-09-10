@extends('layouts.contentNavbarLayout')
@section('title', 'Edit Logistik')

@section('page-style')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false }
    };
</script>
<style>
    .custom-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 9999px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endsection

@section('content')
@php
$kategoriLabel = $log->kategori->nama_logistik ?? '';
$isModem = str_contains(strtolower($kategoriLabel), 'modem');
$isSplitter = strtolower($kategoriLabel) === 'splitter';
$isSerial = in_array(strtolower($kategoriLabel), ['modem','tenda','sfp','olt','odp','odc','htb','splitter']);
$isNoMac = in_array(strtolower($kategoriLabel), ['olt','odp','odc','splitter']);
$totalUnit = $modemDetails->count();
$currentRasio = $isSplitter ? ($modemDetails->first()->rasio ?? '') : '';
@endphp

<div class="space-y-5 max-w-[1500px] mx-auto pb-8">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb">
        <ol class="flex items-center gap-1.5 text-xs text-slate-500 mb-0 p-0 list-none">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 font-medium transition-colors no-underline">
                    <i class="bx bx-home-alt text-sm"></i><span>Dashboard</span>
                </a>
            </li>
            <li class="text-slate-300">/</li>
            <li><a href="/data/logistik" class="text-slate-500 hover:text-blue-600 font-medium transition-colors no-underline">Logistik</a></li>
            <li class="text-slate-300">/</li>
            <li class="text-blue-600 font-semibold">Edit Logistik</li>
        </ol>
    </nav>

    {{-- Header Card --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-800 p-5 sm:p-6 text-white shadow-lg shadow-blue-900/20">
        <div class="absolute -right-10 -top-10 h-56 w-56 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-10 h-56 w-56 rounded-full bg-indigo-400/20 blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3.5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/15 backdrop-blur-md border border-white/25 text-white text-2xl">
                    <i class="bx bx-edit"></i>
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-white tracking-tight leading-tight mb-0.5">Edit Logistik</h1>
                    <p class="text-xs text-blue-100/90 mb-0">Perbarui detail perangkat inventaris gudang</p>
                </div>
            </div>
            <a href="/data/logistik" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-semibold backdrop-blur-md border border-white/30 transition-all cursor-pointer no-underline self-start sm:self-auto">
                <i class="bx bx-chevrons-left text-base"></i><span>Kembali</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="/update-logistik/{{ $log->id }}" method="POST" id="editDeviceForm">
            @csrf

            <div class="p-5 sm:p-6 space-y-6">

                {{-- Informasi Perangkat --}}
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 text-base">
                            <i class="bx bx-chip"></i>
                        </div>
                        <h2 class="text-sm font-bold text-slate-800 mb-0">Informasi Perangkat</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700" for="nama_perangkat">
                                <i class="bx bx-chip text-blue-600 mr-1"></i>Nama Perangkat
                            </label>
                            <input type="text" class="form-input-custom" name="nama_perangkat" id="nama_perangkat" value="{{ $log->nama_perangkat }}" placeholder="Nama perangkat">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700" for="edit_kategori">
                                <i class="bx bx-category text-blue-600 mr-1"></i>Kategori
                            </label>
                            <select class="form-input-custom bg-white" name="kategori" id="edit_kategori">
                                <option value="">-</option>
                                @foreach ($data as $k)
                                <option value="{{ $k->id }}" data-nama="{{ $k->nama_logistik }}" {{ $log->kategori_id == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_logistik }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700" for="edit_jumlah_stok">
                                <i class="bx bx-package text-blue-600 mr-1"></i>Jumlah Stok
                            </label>
                            <input type="number" class="form-input-custom" name="stok" id="edit_jumlah_stok" value="{{ $log->jumlah_stok }}" min="1" placeholder="Jumlah unit / meter">
                        </div>
                        <div class="space-y-1" id="editJumlahRusakWrap" style="{{ $isSerial ? 'display:none;' : '' }}">
                            <label class="block text-xs font-semibold text-rose-700" for="edit_jumlah_rusak">
                                <i class="bx bx-x-circle text-rose-500 mr-1"></i>Jumlah Unit Rusak
                            </label>
                            <input type="number" class="form-input-custom" name="jumlah_rusak" id="edit_jumlah_rusak" value="{{ $log->jumlah_rusak ?? 0 }}" min="0" placeholder="Jumlah unit / meter">
                            <span class="block text-[10.5px] text-slate-400">Stok tersedia dihitung otomatis (= stok total - rusak - terpakai).</span>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700" for="harga">
                                <i class="bx bx-money text-blue-600 mr-1"></i>Harga Satuan (Rp)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                                <input type="text" id="harga" class="form-input-custom !pl-9" value="{{ number_format($log->harga, 0, ',', '.') }}">
                            </div>
                            <input type="hidden" name="harga" id="rawHarga" value="{{ $log->harga }}">
                        </div>
                    </div>
                </div>

                {{-- Rasio Splitter --}}
                <div id="editRatioField" class="p-4 sm:p-5 rounded-xl bg-amber-50/70 border border-amber-200 space-y-3" style="{{ $isSplitter ? '' : 'display:none;' }}">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600 text-base">
                            <i class="bx bx-git-repo-forked"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-amber-950 mb-0">Rasio Splitter</h3>
                            <p class="text-[11px] text-amber-800 mb-0">Berlaku untuk semua unit splitter ini</p>
                        </div>
                    </div>
                    <select name="rasio" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium max-w-xs">
                        <option value="" {{ $currentRasio ? '' : 'selected' }} disabled>Pilih Rasio</option>
                        <option value="1:2" {{ $currentRasio == '1:2' ? 'selected' : '' }}>1 : 2 (2 Output)</option>
                        <option value="1:4" {{ $currentRasio == '1:4' ? 'selected' : '' }}>1 : 4 (4 Output)</option>
                        <option value="1:8" {{ $currentRasio == '1:8' ? 'selected' : '' }}>1 : 8 (8 Output)</option>
                        <option value="1:16" {{ $currentRasio == '1:16' ? 'selected' : '' }}>1 : 16 (16 Output)</option>
                    </select>
                </div>

                {{-- SN / MAC Fields --}}
                <div id="editSerialFields" class="space-y-3" style="{{ $isSerial ? '' : 'display:none;' }}">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 text-base">
                                <i class="bx bx-barcode"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 mb-0" id="editSerialFieldLabel">
                                    Detail Unit Perangkat ({{ $isNoMac ? 'Serial Number' : 'Serial & MAC' }})
                                </h3>
                                <p class="text-[11px] text-slate-400 mb-0">Ditampilkan saat closing instalasi</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            <i class="bx bx-hash"></i><span id="editUnitCount">{{ $totalUnit }}</span> unit
                        </span>
                    </div>

                    <div id="editUnitsHeader" class="hidden grid grid-cols-12 gap-3" style="{{ $isSerial ? '' : 'display:grid;' }}">
                        <div class="col-span-5 sn-col"><span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Serial Number</span></div>
                        <div class="col-span-5 mac-col" style="{{ $isNoMac ? 'display:none;' : '' }}"><span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">MAC Address</span></div>
                        <div class="col-span-2"><span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tindakan</span></div>
                    </div>

                    <div id="editDeviceUnits" class="space-y-2.5">
                        @forelse ($modemDetails as $md)
                        @php
                        $mdStatus = (int) $md->status_id;
                        $mdRusak = ($mdStatus === \App\Helpers\LogistikStatus::RUSAK);
                        $mdTerpakai = ($mdStatus === \App\Helpers\LogistikStatus::TERPAKAI);
                        @endphp
                        <div class="device-unit grid grid-cols-12 gap-3 items-center bg-slate-50/60 border border-slate-200 rounded-xl p-2.5 {{ $mdTerpakai ? 'is-terpakai border-blue-300 bg-blue-50/60' : '' }}">
                            <div class="col-span-5 sn-col">
                                <input type="text" class="form-input-custom" name="edit_serial_number[]" value="{{ $md->serial_number }}" placeholder="Serial Number" {{ $mdTerpakai ? 'readonly' : '' }}>
                            </div>
                            <div class="col-span-5 mac-col" style="{{ $isNoMac ? 'display:none;' : '' }}">
                                <input type="text" class="form-input-custom" name="edit_mac_address[]" value="{{ $md->mac_address }}" placeholder="MAC Address" {{ $mdTerpakai ? 'readonly' : '' }}>
                            </div>
                            <div class="col-span-2 flex items-center justify-end gap-1">
                                @if ($mdTerpakai)
                                <input type="hidden" name="edit_is_rusak[]" value="0">
                                <span class="unit-terpakai-badge inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200" title="Unit sedang Terpakai oleh pelanggan">
                                    <i class="bx bx-wifi text-sm"></i>Terpakai
                                </span>
                                @else
                                <input type="hidden" name="edit_is_rusak[]" value="{{ $mdRusak ? '1' : '0' }}">
                                <button type="button" class="unit-rstatus w-9 h-9 rounded-lg border flex items-center justify-center transition-all cursor-pointer {{ $mdRusak ? 'bg-rose-100 border-rose-300 text-rose-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' }}" data-status="{{ $mdRusak ? 'rusak' : 'ok' }}" title="{{ $mdRusak ? 'Unit rusak (afkir). Klik untuk kembalikan ke OK.' : 'Unit normal. Klik untuk menandai rusak.' }}">
                                    <i class="bx {{ $mdRusak ? 'bx-x-circle' : 'bx-check-circle' }} text-base"></i>
                                </button>
                                @endif
                                <button type="button" class="w-9 h-9 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center transition-all add-unit cursor-pointer" title="Tambah unit"><i class="bx bx-plus text-lg"></i></button>
                            </div>
                            <input type="hidden" name="edit_detail_id[]" value="{{ $md->id }}">

                            @if ($isModem && $mdTerpakai)
                            @php
                            $cust = $md->customer ?? ($md->serial_number ? \App\Models\Customer::where('seri_perangkat', $md->serial_number)->first() : null);
                            @endphp
                            <div class="customer-info-box col-span-12 mt-1 pt-2 border-t border-blue-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center flex-wrap gap-2 text-slate-700">
                                    @if ($cust)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-100/90 text-blue-800 border border-blue-200">
                                        <i class="bx bx-user text-xs"></i>
                                        {{ $cust->nama_customer }}
                                        <span class="text-blue-600 font-normal">#{{ $cust->id }}</span>
                                    </span>
                                    @if ($cust->no_hp)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $cust->no_hp) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-mono text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 no-underline transition-colors" title="Hubungi WhatsApp">
                                        <i class="bx bxl-whatsapp text-sm text-emerald-600"></i>{{ $cust->no_hp }}
                                    </a>
                                    @endif
                                    @if ($md->tanggal_terpakai)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-500">
                                        <i class="bx bx-calendar text-slate-400"></i> Terpasang: <strong class="text-slate-600">{{ \Carbon\Carbon::parse($md->tanggal_terpakai)->format('d M Y') }}</strong>
                                    </span>
                                    @endif
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="bx bx-info-circle text-xs"></i> Status unit terpakai (Belum tertaut ke ID pelanggan)
                                    </span>
                                    @endif
                                </div>
                                @if ($cust)
                                <div class="flex items-center gap-1.5 self-end sm:self-auto shrink-0">
                                    <a href="{{ route('detail-pelanggan', $cust->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white text-blue-700 hover:bg-blue-50 border border-blue-300 shadow-2xs no-underline transition-all">
                                        <i class="bx bx-show text-xs"></i> Detail Pelanggan
                                    </a>
                                    <a href="{{ url('/edit-pelanggan/' . $cust->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white text-slate-600 hover:bg-slate-100 border border-slate-300 shadow-2xs no-underline transition-all">
                                        <i class="bx bx-edit text-xs"></i> Edit
                                    </a>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="device-unit grid grid-cols-12 gap-3 items-center bg-slate-50/60 border border-slate-200 rounded-xl p-2.5">
                            <div class="col-span-5 sn-col">
                                <input type="text" class="form-input-custom" name="edit_serial_number[]" placeholder="Serial Number">
                            </div>
                            <div class="col-span-5 mac-col" style="{{ $isNoMac ? 'display:none;' : '' }}">
                                <input type="text" class="form-input-custom" name="edit_mac_address[]" placeholder="MAC Address">
                            </div>
                            <div class="col-span-2 flex items-center justify-end gap-1">
                                <input type="hidden" name="edit_is_rusak[]" value="0">
                                <button type="button" class="unit-rstatus w-9 h-9 rounded-lg border bg-emerald-50 border-emerald-200 text-emerald-700 flex items-center justify-center transition-all cursor-pointer" data-status="ok" title="Unit normal. Klik untuk menandai rusak.">
                                    <i class="bx bx-check-circle text-base"></i>
                                </button>
                                <button type="button" class="w-9 h-9 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center transition-all add-unit cursor-pointer" title="Tambah unit"><i class="bx bx-plus text-lg"></i></button>
                            </div>
                            <input type="hidden" name="edit_detail_id[]" value="">
                        </div>
                        @endforelse
                    </div>
                    <p class="text-[11px] text-slate-400 mb-0">Serial Number {{ $isNoMac ? '' : 'dan MAC Address' }} per unit perangkat</p>
                </div>

                {{-- Aksi --}}
                <div class="flex items-center justify-end gap-2.5 pt-5 border-t border-slate-100">
                    <a href="/data/logistik" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all cursor-pointer no-underline">
                        Batal
                    </a>
                    <button type="button" id="btnUpdate" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-sm hover:shadow cursor-pointer border-0 outline-none">
                        <i class="bx bx-save"></i><span>Update</span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<style>
    .form-input-custom {
        width: 100%;
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        color: #1e293b;
        outline: none;
        transition: all 0.15s ease;
    }
    .form-input-custom:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .form-input-custom::placeholder { color: #94a3b8; }
</style>
@endsection

@push('scripts')
<script src="/assets/js/logistik.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var SERIALIZED = ['modem','tenda','sfp','olt','odp','odc','htb','splitter'];
    var NO_MAC = ['olt','odp','odc','splitter'];
    var kategoriSelect = document.getElementById('edit_kategori');
    var serialFields = document.getElementById('editSerialFields');
    var jumlahStok = document.getElementById('edit_jumlah_stok');

    function currentCategoryName() {
        var selected = kategoriSelect.options[kategoriSelect.selectedIndex];
        return (selected && selected.dataset.nama || '').toLowerCase();
    }

    function syncEditUnitCount() {
        var badge = document.getElementById('editUnitCount');
        if (badge) {
            badge.textContent = document.querySelectorAll('#editDeviceUnits .device-unit').length;
        }
    }

    function applyEditMacVisibility() {
        var cat = currentCategoryName();
        var hideMac = NO_MAC.includes(cat);
        var label = document.getElementById('editSerialFieldLabel');
        var header = document.getElementById('editUnitsHeader');
        var headerMacCol = header ? header.querySelector('.mac-col') : null;
        var headerSnCol = header ? header.querySelector('.sn-col') : null;
        var deviceUnits = document.getElementById('editDeviceUnits');
        if (deviceUnits) deviceUnits.style.display = '';
        document.querySelectorAll('#editDeviceUnits .device-unit').forEach(function (unit) {
            var macCol = unit.querySelector('.mac-col');
            var snCol = unit.querySelector('.sn-col');
            if (hideMac) {
                if (macCol) macCol.style.display = 'none';
                if (snCol) {
                    snCol.classList.remove('col-span-5');
                    snCol.classList.add('col-span-10');
                }
                if (headerMacCol) headerMacCol.style.display = 'none';
                if (headerSnCol) {
                    headerSnCol.classList.remove('col-span-5');
                    headerSnCol.classList.add('col-span-10');
                }
            } else {
                if (macCol) macCol.style.display = '';
                if (snCol) {
                    snCol.classList.remove('col-span-10');
                    snCol.classList.add('col-span-5');
                }
                if (headerMacCol) headerMacCol.style.display = '';
                if (headerSnCol) {
                    headerSnCol.classList.remove('col-span-10');
                    headerSnCol.classList.add('col-span-5');
                }
            }
        });
        if (label) {
            var labelText = hideMac ? 'Detail Unit Perangkat (Serial Number)' : 'Detail Unit Perangkat (Serial & MAC)';
            label.innerHTML = labelText;
        }
        syncEditUnitCount();
    }

    if (kategoriSelect) {
        function toggleFields() {
            var nama = currentCategoryName();
            var isSerial = SERIALIZED.includes(nama);
            var isSplitter = (nama === 'splitter');
            var ratioField = document.getElementById('editRatioField');
            if (ratioField) ratioField.style.display = isSplitter ? '' : 'none';
            var header = document.getElementById('editUnitsHeader');
            var jumlahRusakWrap = document.getElementById('editJumlahRusakWrap');
            if (isSplitter) {
                serialFields.style.display = '';
                if (jumlahStok) {
                    jumlahStok.value = document.querySelectorAll('#editDeviceUnits .device-unit').length || 1;
                    jumlahStok.readOnly = true;
                }
                if (jumlahRusakWrap) jumlahRusakWrap.style.display = 'none';
                applyEditMacVisibility();
                if (header) header.style.display = '';
                return;
            }
            if (isSerial) {
                serialFields.style.display = '';
                if (jumlahStok) {
                    jumlahStok.value = document.querySelectorAll('#editDeviceUnits .device-unit').length || 1;
                    jumlahStok.readOnly = true;
                }
                if (jumlahRusakWrap) jumlahRusakWrap.style.display = 'none';
                applyEditMacVisibility();
            } else {
                serialFields.style.display = 'none';
                if (jumlahStok) jumlahStok.readOnly = false;
                if (jumlahRusakWrap) jumlahRusakWrap.style.display = '';
            }
            if (header) header.style.display = isSerial ? '' : 'none';
        }
        kategoriSelect.addEventListener('change', toggleFields);
    }

    applyEditMacVisibility();

    document.getElementById('editDeviceUnits').addEventListener('click', function (e) {
        if (e.target.closest('.add-unit')) {
            // Gunakan unit normal (Tersedia/OK) sebagai template; jika tak ada, pakai unit pertama
            var template = document.querySelector('#editDeviceUnits .device-unit:not(.is-terpakai)');
            var first = template || document.querySelector('#editDeviceUnits .device-unit');
            var clone = first.cloneNode(true);
            clone.querySelectorAll('input').forEach(function (inp) { inp.value = ''; });
            clone.querySelectorAll('input').forEach(function (inp) { inp.removeAttribute('readonly'); });
            clone.classList.remove('is-terpakai', 'border-blue-300', 'bg-blue-50/60');
            var statusSpan = clone.querySelector('.unit-terpakai-badge');
            if (statusSpan) statusSpan.remove();
            var custInfo = clone.querySelector('.customer-info-box');
            if (custInfo) custInfo.remove();
            var btn = clone.querySelector('.add-unit');
            btn.className = 'w-9 h-9 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center justify-center transition-all remove-unit cursor-pointer';
            btn.innerHTML = '<i class="bx bx-x text-lg"></i>';
            btn.title = 'Hapus unit';
            var rBtn = clone.querySelector('.unit-rstatus');
            if (rBtn) setEditUnitStatus(rBtn, 'ok');
            var rh = clone.querySelector('input[name="edit_is_rusak[]"]');
            if (rh) rh.value = '0';
            first.parentNode.appendChild(clone);
            applyEditMacVisibility();
            syncEditStok();
        }
        if (e.target.closest('.remove-unit')) {
            var units = document.querySelectorAll('#editDeviceUnits .device-unit');
            if (units.length > 1) {
                e.target.closest('.device-unit').remove();
                syncEditStok();
            }
        }
        var statusBtn = e.target.closest('.unit-rstatus');
        if (statusBtn) {
            setEditUnitStatus(statusBtn, statusBtn.dataset.status === 'ok' ? 'rusak' : 'ok');
        }
    });

    function setEditUnitStatus(btn, status) {
        btn.dataset.status = status;
        var rusak = status === 'rusak';
        var unit = btn.closest('.device-unit');
        var rh = unit ? unit.querySelector('input[name="edit_is_rusak[]"]') : null;
        if (rh) rh.value = rusak ? '1' : '0';
        btn.className = rusak
            ? 'unit-rstatus w-9 h-9 rounded-lg border bg-rose-100 border-rose-300 text-rose-700 flex items-center justify-center transition-all cursor-pointer'
            : 'unit-rstatus w-9 h-9 rounded-lg border bg-emerald-50 border-emerald-200 text-emerald-700 flex items-center justify-center transition-all cursor-pointer';
        btn.innerHTML = '<i class="bx ' + (rusak ? 'bx-x-circle' : 'bx-check-circle') + ' text-base"></i>';
        btn.title = rusak ? 'Unit rusak (afkir). Klik untuk kembalikan ke OK.' : 'Unit normal. Klik untuk menandai rusak.';
    }

    function syncEditStok() {
        var selected = kategoriSelect.options[kategoriSelect.selectedIndex];
        var nama = (selected && selected.dataset.nama || '').toLowerCase();
        if (SERIALIZED.includes(nama) && jumlahStok) {
            jumlahStok.value = document.querySelectorAll('#editDeviceUnits .device-unit').length;
        }
    }

    document.getElementById('btnUpdate').addEventListener('click', function () {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data Perangkat akan diperbarui!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal',
            topLayer: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector('form').submit();
            }
        });
    });
});

document.getElementById('harga').addEventListener('keyup', function () {
    var raw = this.value.replace(/\D/g, '');
    document.getElementById('rawHarga').value = raw;
    if (raw) {
        this.value = new Intl.NumberFormat('id-ID').format(raw);
    } else {
        this.value = '';
    }
});
</script>
@endpush
