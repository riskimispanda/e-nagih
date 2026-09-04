@extends('layouts.contentNavbarLayout')

@section('title', 'Buat Permintaan Pengadaan Baru')

@section('page-style')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false
        }
    };
</script>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    <!-- Header Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/pengadaan" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center no-underline transition">
                <i class="bx bx-arrow-back text-xl"></i>
            </a>
            <div>
                <h4 class="text-xl font-bold text-slate-800 m-0 tracking-tight">Formulir Pengadaan Barang</h4>
                <p class="text-xs sm:text-sm text-slate-500 m-0">Ajukan pembelian perangkat atau perlengkapan logistik</p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <form action="/pengadaan/store" method="POST" id="formCreatePengadaan">
            @csrf
            
            <input type="hidden" name="harga_satuan" id="hiddenCreateHargaSatuan" value="{{ old('harga_satuan', 0) }}">
            <input type="hidden" name="total_harga" id="hiddenCreateTotalHarga" value="{{ old('total_harga', 0) }}">

            <div class="p-6 space-y-5">
                @if (isset($errors) && $errors->any())
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                        <div class="font-bold mb-1">Terdapat kesalahan validasi:</div>
                        <ul class="list-disc list-inside space-y-0.5 m-0 pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Section 1: Informasi Barang -->
                <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 space-y-3">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-600 uppercase tracking-wider">
                        <i class="bx bx-package text-blue-600 text-sm"></i> Detail Barang
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Nama Barang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama_barang" value="{{ old('nama_barang') }}"
                                class="form-control text-xs sm:text-sm rounded-lg border-slate-300" 
                                placeholder="Contoh: Modem FiberHome / SFP 10G" required>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Jenis / Kategori Pengadaan
                            </label>
                            <input type="text" name="jenis_pengadaan" value="{{ old('jenis_pengadaan') }}"
                                class="form-control text-xs sm:text-sm rounded-lg border-slate-300" 
                                placeholder="Pilih atau ketik kategori..." list="listJenis">
                            <datalist id="listJenis">
                                <option value="Perangkat Jaringan">
                                <option value="Kabel & Fiber Optik">
                                <option value="Aksesoris & Konektor">
                                <option value="Peralatan Kerja / Tools">
                                <option value="Operasional Kantor">
                            </datalist>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Kuantitas & Harga -->
                <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-xs font-bold text-slate-600 uppercase tracking-wider">
                            <i class="bx bx-calculator text-emerald-600 text-sm"></i> Kuantitas & Estimasi Harga
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Jumlah / Kuantitas <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="jumlah" id="createJumlah" value="{{ old('jumlah', 1) }}" min="1"
                                class="form-control text-xs sm:text-sm rounded-lg border-slate-300 font-semibold" required>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Harga Satuan (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-slate-100 text-slate-600 text-xs font-semibold">Rp</span>
                                <input type="text" id="createHargaSatuan"
                                    class="form-control text-xs sm:text-sm rounded-lg border-slate-300 font-semibold" 
                                    placeholder="0" required autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <!-- Live Calculation Card -->
                    <div class="bg-gradient-to-r from-blue-50/80 via-indigo-50/40 to-slate-50 border border-blue-200/80 rounded-xl p-3.5 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center">
                                <i class="bx bx-wallet text-lg"></i>
                            </div>
                            <div>
                                <span class="text-[11px] font-semibold text-slate-500 block">Rincian Estimasi Biaya</span>
                                <span class="text-xs text-slate-700 font-medium" id="createPreviewKalkulasi">1 unit × Rp 0</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-blue-600 block">Total Pengadaan</span>
                            <span class="text-lg font-extrabold text-blue-700" id="createPreviewTotal">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Pos Anggaran & Catatan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Alokasi Pos RAB <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <select name="rab_id" class="form-select text-xs sm:text-sm rounded-lg border-slate-300">
                            <option value="">-- Tanpa Beban RAB (Umum) --</option>
                            @foreach ($rab as $r)
                                <option value="{{ $r->id }}" {{ old('rab_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->kegiatan }} (Rp {{ number_format($r->jumlah_anggaran, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Keperluan</label>
                        <textarea name="keterangan" rows="2" class="form-control text-xs sm:text-sm rounded-lg border-slate-300" placeholder="Alasan urgensi atau spesifikasi...">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200/80 flex items-center justify-end gap-2">
                <a href="/pengadaan" class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl border border-slate-300 text-slate-600 bg-white hover:bg-slate-100 transition no-underline">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md transition border-0 cursor-pointer">
                    <i class="bx bx-paper-plane me-1"></i> Ajukan Pengadaan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const jmlInput = document.getElementById('createJumlah');
    const hargaDisplay = document.getElementById('createHargaSatuan');
    const hiddenHarga = document.getElementById('hiddenCreateHargaSatuan');
    const hiddenTotal = document.getElementById('hiddenCreateTotalHarga');
    const previewKalkulasi = document.getElementById('createPreviewKalkulasi');
    const previewTotal = document.getElementById('createPreviewTotal');

    function fmt(num) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);
    }

    function calc() {
        const qty = Math.max(1, parseInt(jmlInput.value, 10) || 1);
        const raw = String(hargaDisplay.value || '').replace(/\D/g, '');
        const price = raw ? parseInt(raw, 10) : 0;

        hiddenHarga.value = price;
        const total = qty * price;
        hiddenTotal.value = total;

        previewKalkulasi.textContent = `${qty} unit × ${fmt(price)}`;
        previewTotal.textContent = fmt(total);
    }

    hargaDisplay.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        const num = raw ? parseInt(raw, 10) : 0;
        this.value = num > 0 ? fmt(num) : '';
        calc();
    });

    hargaDisplay.addEventListener('focus', function() {
        const raw = this.value.replace(/\D/g, '');
        if (raw) this.value = parseInt(raw, 10);
    });

    hargaDisplay.addEventListener('blur', function() {
        const raw = this.value.replace(/\D/g, '');
        const num = raw ? parseInt(raw, 10) : 0;
        this.value = num > 0 ? fmt(num) : '';
        calc();
    });

    jmlInput.addEventListener('input', calc);
});
</script>
@endpush
