@extends('layouts.contentNavbarLayout')

@section('title', 'Daftar Permintaan Pengadaan Barang')

@section('page-style')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false
        }
    };
</script>
<style>
    .kpi-stat-card {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .kpi-stat-card:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<div class="space-y-5">

    <!-- Header Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-1">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 font-bold text-base">
                    <i class="bx bx-cart-alt"></i>
                </span>
                <h4 class="text-xl font-bold text-slate-800 m-0 tracking-tight">Daftar Pengadaan Barang</h4>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 m-0">
                Pantau proses pengajuan, persetujuan pimpinan, hingga konfirmasi penerimaan stok ke gudang logistik.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="openModalPengadaan()" data-bs-toggle="modal" data-bs-target="#pengadaanModal" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition-all shadow-md shadow-blue-500/20 cursor-pointer border-0">
                <i class="bx bx-plus-circle text-base"></i> Ajukan Pengadaan
            </button>
            <a href="/data/logistik" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all shadow-xs no-underline">
                <i class="bx bx-box text-base text-slate-500"></i> Gudang Logistik
            </a>
        </div>
    </div>

    <!-- Status KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <a href="/pengadaan" class="kpi-stat-card p-4 rounded-xl border border-slate-200 bg-white shadow-xs no-underline block {{ !$currentStatus ? 'ring-2 ring-blue-500/30 border-blue-400' : '' }}">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Semua Pengajuan</span>
            <span class="text-xl sm:text-2xl font-black text-slate-800">{{ $counts['all'] ?? 0 }}</span>
        </a>

        <a href="/pengadaan?status=1" class="kpi-stat-card p-4 rounded-xl border border-amber-200/80 bg-amber-50/50 shadow-xs no-underline block {{ $currentStatus == 1 ? 'ring-2 ring-amber-500/40 border-amber-400' : '' }}">
            <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider block mb-1">Menunggu Approval</span>
            <span class="text-xl sm:text-2xl font-black text-amber-700">{{ $counts['menunggu'] ?? 0 }}</span>
        </a>

        <a href="/pengadaan?status=2" class="kpi-stat-card p-4 rounded-xl border border-blue-200/80 bg-blue-50/50 shadow-xs no-underline block {{ $currentStatus == 2 ? 'ring-2 ring-blue-500/40 border-blue-400' : '' }}">
            <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider block mb-1">Disetujui (Proses)</span>
            <span class="text-xl sm:text-2xl font-black text-blue-700">{{ $counts['disetujui'] ?? 0 }}</span>
        </a>

        <a href="/pengadaan?status=3" class="kpi-stat-card p-4 rounded-xl border border-emerald-200/80 bg-emerald-50/50 shadow-xs no-underline block {{ $currentStatus == 3 ? 'ring-2 ring-emerald-500/40 border-emerald-400' : '' }}">
            <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider block mb-1">Selesai (Diterima)</span>
            <span class="text-xl sm:text-2xl font-black text-emerald-700">{{ $counts['selesai'] ?? 0 }}</span>
        </a>

        <a href="/pengadaan?status=18" class="kpi-stat-card p-4 rounded-xl border border-rose-200/80 bg-rose-50/50 shadow-xs no-underline block {{ $currentStatus == 18 ? 'ring-2 ring-rose-500/40 border-rose-400' : '' }}">
            <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider block mb-1">Ditolak</span>
            <span class="text-xl sm:text-2xl font-black text-rose-700">{{ $counts['ditolak'] ?? 0 }}</span>
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Filter Toolbar -->
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form action="/pengadaan" method="GET" class="flex items-center gap-2 m-0 max-w-md w-full">
                @if ($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="bx bx-search text-base"></i>
                    </span>
                    <input type="text" name="q" value="{{ $search }}"
                        class="form-control ps-9 py-1.5 rounded-xl text-xs sm:text-sm border-slate-200" 
                        placeholder="Cari nama barang, jenis, atau catatan...">
                </div>
                <button type="submit" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl border-0 transition cursor-pointer">
                    Cari
                </button>
                @if ($search || $currentStatus)
                    <a href="/pengadaan" class="px-3 py-2 text-slate-400 hover:text-slate-600 text-xs no-underline font-semibold">
                        Reset
                    </a>
                @endif
            </form>

            <div class="text-xs text-slate-500">
                Menampilkan <strong>{{ $pengadaans->count() }}</strong> dari total <strong>{{ $pengadaans->total() }}</strong> pengajuan
            </div>
        </div>

        <!-- Table Responsive -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse m-0">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Barang & Kategori</th>
                        <th class="py-3 px-4">Kuantitas</th>
                        <th class="py-3 px-4">Harga Satuan</th>
                        <th class="py-3 px-4">Total Biaya</th>
                        <th class="py-3 px-4">Pemohon</th>
                        <th class="py-3 px-4">Alokasi RAB</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi / Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse ($pengadaans as $p)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 text-sm mb-0.5">
                                    {{ $p->nama_barang }}
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                    <span class="inline-flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded text-slate-600 font-medium">
                                        <i class="bx bx-purchase-tag-alt text-xs"></i>
                                        {{ $p->jenis_pengadaan ?? 'Umum' }}
                                    </span>
                                    <span>Tgl: {{ $p->tanggal_permintaan ? $p->tanggal_permintaan->format('d M Y') : '-' }}</span>
                                </div>
                                @if ($p->keterangan)
                                    <p class="text-[11px] text-slate-500 italic mt-1 mb-0 max-w-xs truncate" title="{{ $p->keterangan }}">
                                        "{{ $p->keterangan }}"
                                    </p>
                                @endif
                                @if ($p->alasan_tolak)
                                    <div class="mt-1 p-1.5 rounded bg-rose-50 border border-rose-200 text-rose-700 text-[11px]">
                                        <strong>Alasan Tolak:</strong> {{ $p->alasan_tolak }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ number_format($p->jumlah, 0, ',', '.') }} unit
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-600">
                                Rp {{ number_format($p->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-blue-700">
                                Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">{{ $p->user->name ?? '-' }}</div>
                                <span class="text-[10px] text-slate-400">ID #{{ $p->user_id }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($p->rab)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="bx bx-book-bookmark text-xs"></i> {{ $p->rab->kegiatan }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($p->status_id == 1)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu Persetujuan
                                    </span>
                                @elseif ($p->status_id == 2)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="bx bx-check text-xs"></i> Disetujui (Dana Siap)
                                    </span>
                                    <div class="text-[10px] text-slate-500 mt-0.5">Menunggu Barang Tiba</div>
                                @elseif ($p->status_id == 3)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="bx bx-check-double text-xs"></i> Selesai (Diterima)
                                    </span>
                                    @if ($p->perangkat)
                                        <div class="text-[10px] text-emerald-600 font-medium mt-0.5">
                                            Masuk ke: {{ $p->perangkat->nama_perangkat }}
                                        </div>
                                    @endif
                                @elseif ($p->status_id == 18)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="bx bx-x text-xs"></i> Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                        {{ $p->status->nama_status ?? 'Status #' . $p->status_id }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($p->status_id == 2)
                                    {{-- Tombol Konfirmasi Barang Diterima & Masuk Stok Manual --}}
                                    <button type="button" 
                                        onclick="openModalTerimaBarang({{ json_encode($p) }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition border-0 cursor-pointer">
                                        <i class="bx bx-package text-base"></i> Terima & Masukkan Stok
                                    </button>
                                @elseif ($p->status_id == 1)
                                    <span class="text-xs text-amber-600 font-medium">
                                        <i class="bx bx-time text-xs"></i> Menunggu Persetujuan
                                    </span>
                                @elseif ($p->status_id == 3)
                                    <span class="text-xs text-slate-400">
                                        <i class="bx bx-check-circle text-emerald-500"></i> Stok Tersinkron
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                <i class="bx bx-folder-open text-4xl mb-2 block"></i>
                                Tidak ada data permintaan pengadaan barang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($pengadaans->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $pengadaans->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Modal Konfirmasi Penerimaan Barang & Input Stok Manual (Diadopsi dari /data/logistik) --}}
<div class="modal fade" id="terimaBarangModal" tabindex="-1" aria-labelledby="terimaBarangModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden bg-white">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white text-xl shadow-inner">
                        <i class="bx bx-package"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-white m-0 tracking-tight" id="terimaBarangModalLabel">
                            Terima & Masukkan Stok Logistik
                        </h5>
                        <p class="text-xs text-emerald-100/90 m-0 mt-0.5">
                            Konfirmasi kedatangan fisik barang pengadaan & integrasikan unit ke inventaris gudang
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition border-0 cursor-pointer" data-bs-dismiss="modal">
                    <i class="bx bx-x text-xl leading-none"></i>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form id="formTerimaBarang" method="POST" action="">
                @csrf
                <input type="hidden" name="jumlah_rusak" id="terima_jumlah_rusak_input" value="0">

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Ringkasan Pengadaan Card -->
                    <div class="p-4 rounded-xl bg-gradient-to-br from-slate-50 to-emerald-50/40 border border-slate-200/90 text-xs">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2 pb-2 border-b border-slate-200">
                            <span class="inline-flex items-center gap-1.5 font-bold text-slate-800 text-sm">
                                <i class="bx bx-box text-emerald-600 text-base"></i>
                                <span id="modalBarangNama">-</span>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200" id="modalBarangKategori">
                                -
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-slate-600">
                            <div>Jumlah Pengajuan: <strong class="text-slate-800 font-bold" id="modalBarangQty">0</strong> unit</div>
                            <div>Harga Satuan: <strong class="text-slate-800 font-bold" id="modalBarangHarga">Rp 0</strong></div>
                            <div>Total Pengajuan: <strong class="text-emerald-700 font-bold" id="modalBarangTotal">Rp 0</strong></div>
                        </div>
                    </div>

                    <!-- Row 1: Tindakan Penambahan Stok & Kuantitas Diterima -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
                        <div class="md:col-span-7 space-y-1">
                            <label class="block text-xs font-bold text-slate-700">
                                <i class="bx bx-transfer-alt text-emerald-600 mr-1"></i>Tindakan Penambahan Stok <span class="text-rose-500">*</span>
                            </label>
                            <select name="tipe_penerimaan" id="selectTipePenerimaan" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-xs cursor-pointer" onchange="handleTipePenerimaanChange(this.value)">
                                <option value="update_existing">Tambahkan ke Perangkat yang Sudah Ada di Gudang</option>
                                <option value="new_item">Daftarkan Sebagai Perangkat Master Baru di Gudang</option>
                                <option value="non_stok">Non-Stok (Konsumsi / Habis Pakai Tanpa Catat Aset)</option>
                            </select>
                        </div>

                        <!-- Jumlah Fisik Diterima -->
                        <div class="md:col-span-5 space-y-1">
                            <label class="block text-xs font-bold text-slate-700">
                                <i class="bx bx-check-shield text-emerald-600 mr-1"></i>Jumlah Diterima Fisik <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="jumlah_diterima" id="inputJumlahDiterima" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-xs" min="1" required>
                            <span class="block text-[10.5px] text-slate-400" id="hintJumlahDiterima">Jumlah unit yang tiba dari supplier</span>
                        </div>
                    </div>

                    <!-- Opsi 1: Existing Perangkat Dropdown -->
                    <div id="sectionExistingPerangkat" class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700">
                            <i class="bx bx-chip text-emerald-600 mr-1"></i>Pilih Perangkat Master di Gudang <span class="text-rose-500">*</span>
                        </label>
                        <select name="perangkat_id" id="selectPerangkatExisting" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-xs cursor-pointer" onchange="onExistingPerangkatChange(this)">
                            <option value="" disabled selected>-- Pilih Perangkat yang Sesuai --</option>
                            @foreach ($perangkatList as $pItem)
                                <option value="{{ $pItem->id }}" 
                                    data-kategori-id="{{ $pItem->kategori_id }}"
                                    data-kategori-nama="{{ strtolower($pItem->kategori->nama_logistik ?? '') }}"
                                    data-nama="{{ $pItem->nama_perangkat }}">
                                    {{ $pItem->nama_perangkat }} [{{ $pItem->kategori->nama_logistik ?? 'Umum' }}] — Stok Gudang: {{ $pItem->jumlah_stok }}
                                </option>
                            @endforeach
                        </select>
                        <span class="block text-[10.5px] text-slate-400">Stok barang akan diakumulasikan ke item master perangkat ini</span>
                    </div>

                    <!-- Opsi 2: New Perangkat Fields -->
                    <div id="sectionNewPerangkat" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 hidden">
                        <div class="md:col-span-7 space-y-1">
                            <label class="block text-xs font-bold text-slate-700">
                                <i class="bx bx-chip text-emerald-600 mr-1"></i>Nama Perangkat Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama_perangkat" id="inputNamaPerangkatBaru" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-xs" placeholder="Contoh: Router TP-Link Archer C6">
                        </div>
                        <div class="md:col-span-5 space-y-1">
                            <label class="block text-xs font-bold text-slate-700">
                                <i class="bx bx-category text-emerald-600 mr-1"></i>Kategori Logistik <span class="text-rose-500">*</span>
                            </label>
                            <select name="kategori_id" id="selectKategoriBaru" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-xs cursor-pointer" onchange="onNewKategoriChange(this)">
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                @foreach ($kategoriList as $kItem)
                                    <option value="{{ $kItem->id }}" data-nama="{{ strtolower($kItem->nama_logistik) }}">{{ $kItem->nama_logistik }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Splitter Ratio Section -->
                    <div id="terimaRatioField" style="display:none;" class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 space-y-1.5">
                        <label class="block text-xs font-bold text-amber-900 flex items-center gap-1.5" for="terima_rasio">
                            <i class='bx bx-git-repo-forked text-amber-600'></i> Rasio Output Splitter <span class="text-rose-500">*</span>
                        </label>
                        <select name="rasio" id="terima_rasio" class="w-full rounded-xl border border-amber-300 bg-white px-3 py-2 text-xs font-bold text-amber-950 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition cursor-pointer">
                            <option value="" selected disabled>Pilih Rasio Splitter</option>
                            <option value="1:2">1 : 2 (2 Output Port)</option>
                            <option value="1:4">1 : 4 (4 Output Port)</option>
                            <option value="1:8">1 : 8 (8 Output Port)</option>
                            <option value="1:16">1 : 16 (16 Output Port)</option>
                            <option value="1:32">1 : 32 (32 Output Port)</option>
                        </select>
                        <span class="block text-[10.5px] text-amber-800">Rasio pembagian optik untuk perhitungan topologi port.</span>
                    </div>

                    <!-- Dynamic Serial / MAC Address Section (Identik dengan form Tambah Stok /data/logistik) -->
                    <div id="terimaSerialFields" style="display:none;" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-800 mb-0 flex items-center gap-1.5" id="terimaSerialFieldLabel">
                                <i class='bx bx-barcode text-emerald-600 text-sm'></i> Detail Unit (Serial & MAC)
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="autoGenerateRowsByQty()" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-[11px] font-semibold transition flex items-center gap-1 cursor-pointer" title="Sesuaikan jumlah baris dengan kuantitas yang diterima">
                                    <i class="bx bx-sync"></i> Sesuaikan Baris
                                </button>
                                <span class="text-[10.5px] text-slate-500 font-medium hidden sm:inline" id="terimaSerialFieldHint">Input identitas per unit</span>
                            </div>
                        </div>

                        <div id="terimaDeviceUnits" class="space-y-2 max-h-60 overflow-y-auto custom-scroll pr-1">
                            <!-- Template Unit Row (Dibuat dan disinkronkan secara dinamis) -->
                            <div class="terima-device-unit flex items-center gap-2">
                                <input type="hidden" name="is_rusak[]" value="0">
                                <div class="w-6 text-center text-slate-400 font-mono text-xs font-bold unit-idx">1</div>
                                <div class="flex-1 terima-sn-col">
                                    <input type="text" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-100 transition" name="serial_number[]" placeholder="Serial Number (SN) *">
                                </div>
                                <div class="flex-1 terima-mac-col">
                                    <input type="text" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-100 transition" name="mac_address[]" placeholder="MAC Address">
                                </div>
                                <div class="w-16 flex-shrink-0 flex items-center justify-center">
                                    <button type="button" class="terima-unit-rstatus w-full h-8 rounded-lg border text-[10px] font-bold transition-all cursor-pointer flex items-center justify-center gap-1 border-slate-200 bg-slate-50 text-slate-600 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-700" title="Klik untuk menandai unit rusak" data-status="ok">
                                        <i class="bx bx-check-circle text-sm"></i> OK
                                    </button>
                                </div>
                                <div class="w-8 flex-shrink-0 flex items-center justify-center">
                                    <button type="button" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center transition-all terima-add-unit cursor-pointer" title="Tambah baris unit">
                                        <i class="bx bx-plus text-base"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                            <span class="text-[10.5px] text-slate-500 font-medium">Unit berstatus <strong class="text-rose-600">RUSAK</strong> langsung dicatat di gudang afkir.</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-rose-50 text-rose-700 text-[11px] font-bold border border-rose-200" id="terimaRusakSummary">
                                Rusak: <span id="terimaRusakCount">0</span> unit
                            </span>
                        </div>
                    </div>

                    <!-- Jumlah Rusak (untuk kategori non-serial: kabel dropcore, dsb) -->
                    <div id="terimaJumlahRusakField" style="display:none;" class="p-3.5 rounded-xl bg-rose-50/70 border border-rose-200 space-y-1.5">
                        <label class="block text-xs font-bold text-rose-800 flex items-center gap-1.5" for="terima_jumlah_rusak_manual">
                            <i class='bx bx-x-circle text-rose-600'></i> Jumlah Unit / Meter Rusak / Cacat
                        </label>
                        <input type="number" id="terima_jumlah_rusak_manual" class="w-full rounded-xl border border-rose-300 bg-white px-3 py-2 text-xs font-bold text-rose-900 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition" placeholder="Contoh: 0" min="0" value="0">
                        <span class="block text-[10.5px] text-rose-700">Barang yang cacat/rusak saat pengiriman. Stok tersedia akan dikurangi otomatis.</span>
                    </div>

                    <!-- Catatan Penerimaan -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700">
                            <i class="bx bx-note text-emerald-600 mr-1"></i>Catatan Penerimaan / Lokasi Rak / No. Surat Jalan
                        </label>
                        <textarea name="catatan_penerimaan" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-xs" placeholder="Contoh: Barang diterima dalam kondisi baik oleh staf Budi, disimpan di Rak B-02. No. Surat Jalan: SJ-99182"></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200/80 flex items-center justify-end gap-2.5">
                    <button type="button" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-600 bg-white hover:bg-slate-100 transition cursor-pointer" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitTerimaBarang" class="px-5 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition flex items-center gap-1.5 cursor-pointer border-0 shadow-sm hover:shadow">
                        <i class="bx bx-check-double text-base"></i> Konfirmasi & Masukkan Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Form Pengadaan Barang --}}
<div class="modal fade" id="pengadaanModal" tabindex="-1" aria-labelledby="pengadaanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden bg-white">
      
      <!-- Modal Header -->
      <div class="px-6 py-4 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-400 shadow-inner">
            <i class="bx bx-cart-add text-xl"></i>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h5 class="text-base font-bold text-white m-0 tracking-tight" id="pengadaanModalLabel">
                Permintaan Pengadaan Barang
              </h5>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/30 text-blue-200 border border-blue-400/30">
                Logistik
              </span>
            </div>
            <p class="text-xs text-slate-300 m-0 mt-0.5">
              Ajukan permintaan pembelian barang atau perangkat logistik ke manajemen
            </p>
          </div>
        </div>
        <button type="button" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition border-0 cursor-pointer" data-bs-dismiss="modal" aria-label="Close">
          <i class="bx bx-x text-xl leading-none"></i>
        </button>
      </div>

      <!-- Modal Body Form -->
      <form action="/pengadaan/store" method="POST" id="pengadaanForm">
        @csrf
        
        <!-- Hidden Inputs for actual numeric values -->
        <input type="hidden" name="harga_satuan" id="hiddenHargaSatuan" value="{{ old('harga_satuan', 0) }}">
        <input type="hidden" name="total_harga" id="hiddenTotalHarga" value="{{ old('total_harga', 0) }}">

        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
          @if (isset($errors) && $errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
              <div class="font-bold flex items-center gap-1.5 mb-1 text-rose-900">
                <i class="bx bx-error-circle text-base"></i> Terdapat kesalahan validasi:
              </div>
              <ul class="list-disc list-inside space-y-0.5 m-0 pl-1">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <!-- Card 1: Informasi Barang -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 space-y-3">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 uppercase tracking-wider">
              <i class="bx bx-package text-blue-600 text-sm"></i> Detail Barang
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                  Nama Barang <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="bx bx-box text-base"></i>
                  </span>
                  <input type="text" name="nama_barang" value="{{ old('nama_barang') }}"
                    class="form-control ps-9 py-2 rounded-lg text-xs sm:text-sm border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" 
                    placeholder="Contoh: Modem FiberHome AN5506-04-FG" required>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                  Jenis / Kategori Pengadaan
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="bx bx-purchase-tag-alt text-base"></i>
                  </span>
                  <input type="text" name="jenis_pengadaan" value="{{ old('jenis_pengadaan') }}"
                    class="form-control ps-9 py-2 rounded-lg text-xs sm:text-sm border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" 
                    placeholder="Pilih atau ketik kategori..." list="listJenisPengadaanModal">
                  <datalist id="listJenisPengadaanModal">
                    <option value="Perangkat Jaringan">
                    <option value="Kabel & Fiber Optik">
                    <option value="Aksesoris & Konektor">
                    <option value="Peralatan Kerja / Tools">
                    <option value="Operasional Kantor">
                    <option value="Suku Cadang / Sparepart">
                  </datalist>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 2: Kuantitas & Kalkulasi Biaya -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 space-y-3">
            <div class="flex items-center justify-between">
              <span class="flex items-center gap-2 text-xs font-bold text-slate-600 uppercase tracking-wider">
                <i class="bx bx-calculator text-emerald-600 text-sm"></i> Kuantitas & Harga
              </span>
              <span class="text-[11px] font-medium text-slate-500">Kalkulasi Otomatis</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                  Jumlah / Kuantitas <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="bx bx-hash text-base"></i>
                  </span>
                  <input type="number" name="jumlah" id="pengadaanJumlah" value="{{ old('jumlah', 1) }}" min="1" step="1"
                    class="form-control ps-9 py-2 rounded-lg text-xs sm:text-sm border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-semibold" 
                    placeholder="1" required>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                  Harga Satuan <span class="text-rose-500">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-slate-100 text-slate-600 text-xs font-semibold border-slate-300">Rp</span>
                  <input type="text" id="pengadaanHargaSatuan"
                    class="form-control py-2 text-xs sm:text-sm border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-semibold" 
                    placeholder="0" required autocomplete="off">
                </div>
              </div>
            </div>

            <!-- Calculation Preview Card -->
            <div class="bg-gradient-to-r from-blue-50/80 via-indigo-50/40 to-slate-50 border border-blue-200/80 rounded-xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-blue-500/20">
                  <i class="bx bx-wallet text-xl"></i>
                </div>
                <div>
                  <span class="text-[11px] font-semibold text-slate-500 block">Estimasi Total Biaya</span>
                  <span class="text-xs text-slate-700 font-medium" id="previewKalkulasi">
                    1 unit × Rp 0
                  </span>
                </div>
              </div>
              <div class="text-left sm:text-right sm:border-l sm:border-blue-200/80 sm:pl-4">
                <span class="text-[10px] uppercase font-bold tracking-wider text-blue-600 block">Total Pengadaan</span>
                <span class="text-base sm:text-lg font-extrabold text-blue-700" id="previewTotalFormatted">
                  Rp 0
                </span>
              </div>
            </div>
          </div>

          <!-- Card 3: RAB & Keterangan Tambahan -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">
                Alokasi Pos RAB <span class="text-slate-400 font-normal">(Opsional)</span>
              </label>
              <div class="relative">
                <select name="rab_id" id="pengadaanRabId" class="form-select text-xs sm:text-sm py-2 rounded-lg border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                  <option value="">-- Tanpa Beban RAB (Umum) --</option>
                  @foreach ($rabList ?? $rab ?? [] as $item)
                    <option value="{{ $item->id }}" {{ old('rab_id') == $item->id ? 'selected' : '' }}>
                      {{ $item->kegiatan }} (Anggaran: Rp {{ number_format($item->jumlah_anggaran, 0, ',', '.') }})
                    </option>
                  @endforeach
                </select>
              </div>
              <p class="text-[11px] text-slate-500 mt-1 mb-0">Pilih jika pengadaan ini diambil dari anggaran proyek tertentu.</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">
                Keterangan / Spesifikasi
              </label>
              <textarea name="keterangan" rows="2"
                class="form-control text-xs sm:text-sm rounded-lg border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" 
                placeholder="Spesifikasi barang, urgensi pembelian, atau catatan penempatan...">{{ old('keterangan') }}</textarea>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-between px-6 py-3.5 bg-slate-50 border-t border-slate-200/80">
          <div class="flex items-center gap-1.5 text-xs text-slate-500">
            <i class="bx bx-shield-quarter text-base text-blue-500"></i>
            <span>Status awal: <strong class="text-slate-700 font-medium">Menunggu Persetujuan</strong></span>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl border border-slate-300 text-slate-600 bg-white hover:bg-slate-100 transition cursor-pointer" data-bs-dismiss="modal">
              Batal
            </button>
            <button type="submit" id="btnSubmitPengadaan" class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md shadow-blue-500/25 transition flex items-center gap-1.5 cursor-pointer border-0">
              <i class="bx bx-paper-plane text-base"></i>
              <span>Kirim Permintaan</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openModalPengadaan() {
    const modalEl = document.getElementById('pengadaanModal');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (window.$) {
            $('#pengadaanModal').modal('show');
        }
    }
}

// ==========================================================================
// Form Modal Terima & Masukkan Stok (Logistik Stock Receipt Logic)
// ==========================================================================
const TERIMA_SERIALIZED_CATEGORIES = ['modem', 'tenda', 'sfp', 'olt', 'odp', 'odc', 'htb', 'splitter'];
const TERIMA_NO_MAC_CATEGORIES = ['olt', 'odp', 'odc', 'splitter'];

function getTerimaActiveCategory() {
    const tipe = document.getElementById('selectTipePenerimaan')?.value;
    if (tipe === 'update_existing') {
        const sel = document.getElementById('selectPerangkatExisting');
        if (sel && sel.selectedIndex >= 0) {
            const opt = sel.options[sel.selectedIndex];
            return (opt.dataset.kategoriNama || '').toLowerCase().trim();
        }
    } else if (tipe === 'new_item') {
        const sel = document.getElementById('selectKategoriBaru');
        if (sel && sel.selectedIndex >= 0) {
            const opt = sel.options[sel.selectedIndex];
            return (opt.dataset.nama || opt.textContent || '').toLowerCase().trim();
        }
    }
    return '';
}

function handleTipePenerimaanChange(val) {
    const existingSec = document.getElementById('sectionExistingPerangkat');
    const newSec = document.getElementById('sectionNewPerangkat');

    if (val === 'update_existing') {
        existingSec?.classList.remove('hidden');
        newSec?.classList.add('hidden');
    } else if (val === 'new_item') {
        existingSec?.classList.add('hidden');
        newSec?.classList.remove('hidden');
    } else {
        existingSec?.classList.add('hidden');
        newSec?.classList.add('hidden');
    }
    updateTerimaFieldsByCategory();
}

function onExistingPerangkatChange(sel) {
    updateTerimaFieldsByCategory();
}

function onNewKategoriChange(sel) {
    updateTerimaFieldsByCategory();
}

function updateTerimaFieldsByCategory() {
    const tipe = document.getElementById('selectTipePenerimaan')?.value;
    const cat = getTerimaActiveCategory();
    const ratioField = document.getElementById('terimaRatioField');
    const serialFields = document.getElementById('terimaSerialFields');
    const jumlahRusakField = document.getElementById('terimaJumlahRusakField');
    const jmlInput = document.getElementById('inputJumlahDiterima');
    const label = document.getElementById('terimaSerialFieldLabel');
    const hint = document.getElementById('terimaSerialFieldHint');

    if (tipe === 'non_stok') {
        if (ratioField) ratioField.style.display = 'none';
        if (serialFields) serialFields.style.display = 'none';
        if (jumlahRusakField) jumlahRusakField.style.display = 'none';
        if (jmlInput) jmlInput.readOnly = false;
        return;
    }

    const isSplitter = cat === 'splitter';
    const isSerialized = isSplitter || TERIMA_SERIALIZED_CATEGORIES.includes(cat);
    const hideMac = TERIMA_NO_MAC_CATEGORIES.includes(cat);

    if (ratioField) {
        ratioField.style.display = isSplitter ? 'block' : 'none';
    }

    if (isSerialized) {
        if (serialFields) serialFields.style.display = 'block';
        if (jumlahRusakField) jumlahRusakField.style.display = 'none';
        if (jmlInput) jmlInput.readOnly = true;

        // Tampilkan/sembunyikan kolom MAC sesuai kategori
        document.querySelectorAll('.terima-device-unit').forEach(unit => {
            const macCol = unit.querySelector('.terima-mac-col');
            if (macCol) {
                macCol.style.display = hideMac ? 'none' : '';
            }
        });

        if (label) {
            label.innerHTML = `<i class='bx bx-barcode text-emerald-600 text-sm'></i> Detail Unit ${hideMac ? '(Serial Number)' : '(Serial & MAC)'}`;
        }
        if (hint) {
            hint.textContent = hideMac ? 'Input Serial Number untuk tiap unit' : 'Input Serial Number & MAC Address untuk tiap unit';
        }

        syncTerimaJumlahStok();
        syncTerimaRusakUI();
    } else {
        if (serialFields) serialFields.style.display = 'none';
        if (jumlahRusakField) jumlahRusakField.style.display = 'block';
        if (jmlInput) jmlInput.readOnly = false;
        syncTerimaRusakUI();
    }
}

function openModalTerimaBarang(pengadaan) {
    const form = document.getElementById('formTerimaBarang');
    form.action = '/pengadaan/terima/' + pengadaan.id;

    // Set ringkasan barang
    document.getElementById('modalBarangNama').textContent = pengadaan.nama_barang;
    const catName = pengadaan.kategori?.nama_logistik || pengadaan.perangkat?.kategori?.nama_logistik || 'Umum';
    document.getElementById('modalBarangKategori').textContent = catName;
    document.getElementById('modalBarangQty').textContent = pengadaan.jumlah;
    document.getElementById('modalBarangHarga').textContent = 'Rp ' + Number(pengadaan.harga_satuan).toLocaleString('id-ID');
    document.getElementById('modalBarangTotal').textContent = 'Rp ' + Number(pengadaan.total_harga).toLocaleString('id-ID');

    document.getElementById('inputJumlahDiterima').value = pengadaan.jumlah;
    document.getElementById('inputNamaPerangkatBaru').value = pengadaan.nama_barang;

    const selectTipe = document.getElementById('selectTipePenerimaan');
    const selectPerangkat = document.getElementById('selectPerangkatExisting');
    const selectKategori = document.getElementById('selectKategoriBaru');

    if (pengadaan.jenis_pengadaan === 'perangkat_lama' && pengadaan.perangkat_id) {
        selectTipe.value = 'update_existing';
        selectPerangkat.value = pengadaan.perangkat_id;
    } else {
        selectTipe.value = 'new_item';
        if (pengadaan.kategori_id) {
            selectKategori.value = pengadaan.kategori_id;
        }
    }

    handleTipePenerimaanChange(selectTipe.value);

    // Auto generate baris unit sesuai jumlah pengajuan
    const qty = parseInt(pengadaan.jumlah, 10) || 1;
    setTerimaUnitRows(qty);

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('terimaBarangModal'));
    modal.show();
}

function autoGenerateRowsByQty() {
    const qty = parseInt(document.getElementById('inputJumlahDiterima')?.value, 10) || 1;
    setTerimaUnitRows(qty);
}

function setTerimaUnitRows(targetCount) {
    const container = document.getElementById('terimaDeviceUnits');
    if (!container) return;

    targetCount = Math.max(1, targetCount);
    let rows = container.querySelectorAll('.terima-device-unit');

    if (rows.length === 0) return;

    const firstRow = rows[0];

    // Jika baris kurang, clone baris pertama
    while (rows.length < targetCount) {
        const clone = firstRow.cloneNode(true);
        clone.querySelectorAll('input[type="text"]').forEach(inp => inp.value = '');
        const hiddenRusak = clone.querySelector('input[name="is_rusak[]"]');
        if (hiddenRusak) hiddenRusak.value = '0';
        const rBtn = clone.querySelector('.terima-unit-rstatus');
        if (rBtn) setTerimaUnitStatus(rBtn, 'ok');

        const btnCol = clone.querySelector('.w-8.flex-shrink-0');
        if (btnCol) {
            btnCol.innerHTML = `
                <button type="button" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center justify-center transition-all terima-remove-unit cursor-pointer" title="Hapus unit">
                    <i class="bx bx-x text-base"></i>
                </button>
            `;
        }
        container.appendChild(clone);
        rows = container.querySelectorAll('.terima-device-unit');
    }

    // Jika baris berlebih, hapus dari bawah
    while (rows.length > targetCount) {
        rows[rows.length - 1].remove();
        rows = container.querySelectorAll('.terima-device-unit');
    }

    reindexTerimaRows();
    updateTerimaFieldsByCategory();
}

function reindexTerimaRows() {
    const rows = document.querySelectorAll('#terimaDeviceUnits .terima-device-unit');
    rows.forEach((row, idx) => {
        const idxEl = row.querySelector('.unit-idx');
        if (idxEl) idxEl.textContent = idx + 1;
    });
}

function setTerimaUnitStatus(btn, status) {
    btn.dataset.status = status;
    const rusak = status === 'rusak';
    const unit = btn.closest('.terima-device-unit');
    const hiddenRusak = unit ? unit.querySelector('input[name="is_rusak[]"]') : null;
    if (hiddenRusak) hiddenRusak.value = rusak ? '1' : '0';

    btn.className = rusak
        ? 'terima-unit-rstatus w-full h-8 rounded-lg border text-[10px] font-bold transition-all cursor-pointer flex items-center justify-center gap-1 border-rose-300 bg-rose-100 text-rose-700 shadow-xs'
        : 'terima-unit-rstatus w-full h-8 rounded-lg border text-[10px] font-bold transition-all cursor-pointer flex items-center justify-center gap-1 border-slate-200 bg-slate-50 text-slate-600 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-700 shadow-xs';
    btn.innerHTML = rusak
        ? '<i class="bx bx-x-circle text-sm"></i> RUSAK'
        : '<i class="bx bx-check-circle text-sm"></i> OK';
    btn.title = rusak ? 'Unit cacat/rusak. Klik untuk ubah ke OK' : 'Klik untuk menandai unit rusak';
}

function syncTerimaRusakUI() {
    const hiddenInput = document.getElementById('terima_jumlah_rusak_input');
    const countEl = document.getElementById('terimaRusakCount');
    const plainField = document.getElementById('terima_jumlah_rusak_manual');
    const cat = getTerimaActiveCategory();
    const isSerialized = cat === 'splitter' || TERIMA_SERIALIZED_CATEGORIES.includes(cat);

    if (isSerialized) {
        let count = 0;
        document.querySelectorAll('.terima-unit-rstatus').forEach(btn => {
            if (btn.dataset.status === 'rusak') count++;
        });
        if (hiddenInput) hiddenInput.value = count;
        if (countEl) countEl.textContent = count;
    } else {
        const plainVal = parseInt(plainField?.value, 10) || 0;
        if (hiddenInput) hiddenInput.value = plainVal;
        if (countEl) countEl.textContent = plainVal;
    }
}

function syncTerimaJumlahStok() {
    const cat = getTerimaActiveCategory();
    const isSerialized = cat === 'splitter' || TERIMA_SERIALIZED_CATEGORIES.includes(cat);
    const jmlInput = document.getElementById('inputJumlahDiterima');
    if (isSerialized && jmlInput) {
        const unitCount = document.querySelectorAll('#terimaDeviceUnits .terima-device-unit').length;
        jmlInput.value = unitCount;
    }
}

// --------------------------------------------------------------------------
// Form Modal Pengadaan Barang Logic
// --------------------------------------------------------------------------
function getNumericValue(val) {
    if (typeof val === 'number') return val;
    const clean = String(val || '').replace(/\D/g, '');
    return clean ? parseInt(clean, 10) : 0;
}

function formatCurrencyIdr(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount || 0);
}

function updatePengadaanCalculations() {
    const jmlInput = document.getElementById('pengadaanJumlah');
    const hargaDisplay = document.getElementById('pengadaanHargaSatuan');
    const hiddenHarga = document.getElementById('hiddenHargaSatuan');
    const hiddenTotal = document.getElementById('hiddenTotalHarga');
    const previewKalkulasi = document.getElementById('previewKalkulasi');
    const previewTotal = document.getElementById('previewTotalFormatted');

    if (!jmlInput || !hargaDisplay || !hiddenHarga || !hiddenTotal) return;

    const qty = Math.max(1, parseInt(jmlInput.value, 10) || 1);
    const unitPrice = getNumericValue(hargaDisplay.value);

    hiddenHarga.value = unitPrice;
    const total = qty * unitPrice;
    hiddenTotal.value = total;

    if (previewKalkulasi) {
        previewKalkulasi.textContent = `${qty} unit × ${formatCurrencyIdr(unitPrice)}`;
    }
    if (previewTotal) {
        previewTotal.textContent = formatCurrencyIdr(total);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const hargaDisplay = document.getElementById('pengadaanHargaSatuan');
    const jmlInput = document.getElementById('pengadaanJumlah');
    const form = document.getElementById('pengadaanForm');
    const btnSubmit = document.getElementById('btnSubmitPengadaan');

    if (hargaDisplay) {
        const initialVal = parseInt(document.getElementById('hiddenHargaSatuan')?.value, 10) || 0;
        if (initialVal > 0) {
            hargaDisplay.value = formatCurrencyIdr(initialVal);
        }

        hargaDisplay.addEventListener('input', function() {
            const num = getNumericValue(this.value);
            this.value = num > 0 ? formatCurrencyIdr(num) : '';
            updatePengadaanCalculations();
        });

        hargaDisplay.addEventListener('focus', function() {
            const num = getNumericValue(this.value);
            if (num > 0) {
                this.value = num;
            }
        });

        hargaDisplay.addEventListener('blur', function() {
            const num = getNumericValue(this.value);
            this.value = num > 0 ? formatCurrencyIdr(num) : '';
            updatePengadaanCalculations();
        });
    }

    if (jmlInput) {
        jmlInput.addEventListener('input', updatePengadaanCalculations);
        jmlInput.addEventListener('change', updatePengadaanCalculations);
    }

    updatePengadaanCalculations();

    if (form) {
        form.addEventListener('submit', function(e) {
            updatePengadaanCalculations();
            const harga = parseInt(document.getElementById('hiddenHargaSatuan')?.value, 10) || 0;
            const total = parseInt(document.getElementById('hiddenTotalHarga')?.value, 10) || 0;

            if (harga <= 0 || total <= 0) {
                e.preventDefault();
                alert('Silakan masukkan harga satuan yang valid (minimal Rp 1).');
                hargaDisplay?.focus();
                return false;
            }

            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="bx bx-loader-alt bx-spin text-base"></i> Mengirim...';
            }
        });
    }

    // ======================================================================
    // Event Handlers Modal Terima & Masukkan Stok
    // ======================================================================
    const terimaContainer = document.getElementById('terimaDeviceUnits');
    if (terimaContainer) {
        terimaContainer.addEventListener('click', function(e) {
            // Click Tambah Baris (+)
            if (e.target.closest('.terima-add-unit')) {
                const firstUnit = terimaContainer.querySelector('.terima-device-unit');
                if (!firstUnit) return;
                const newUnit = firstUnit.cloneNode(true);
                newUnit.querySelectorAll('input[type="text"]').forEach(inp => inp.value = '');
                const hiddenRusak = newUnit.querySelector('input[name="is_rusak[]"]');
                if (hiddenRusak) hiddenRusak.value = '0';
                const rBtn = newUnit.querySelector('.terima-unit-rstatus');
                if (rBtn) setTerimaUnitStatus(rBtn, 'ok');

                const btnCol = newUnit.querySelector('.w-8.flex-shrink-0');
                if (btnCol) {
                    btnCol.innerHTML = `
                        <button type="button" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center justify-center transition-all terima-remove-unit cursor-pointer" title="Hapus unit">
                            <i class="bx bx-x text-base"></i>
                        </button>
                    `;
                }
                terimaContainer.appendChild(newUnit);
                reindexTerimaRows();
                updateTerimaFieldsByCategory();
                syncTerimaJumlahStok();
                syncTerimaRusakUI();
            }

            // Click Hapus Baris (x)
            if (e.target.closest('.terima-remove-unit')) {
                const units = terimaContainer.querySelectorAll('.terima-device-unit');
                if (units.length > 1) {
                    e.target.closest('.terima-device-unit').remove();
                    reindexTerimaRows();
                    syncTerimaJumlahStok();
                    syncTerimaRusakUI();
                }
            }

            // Click Toggle OK / RUSAK status
            const statusBtn = e.target.closest('.terima-unit-rstatus');
            if (statusBtn) {
                setTerimaUnitStatus(statusBtn, statusBtn.dataset.status === 'ok' ? 'rusak' : 'ok');
                syncTerimaRusakUI();
            }
        });
    }

    const inputJmlDiterima = document.getElementById('inputJumlahDiterima');
    if (inputJmlDiterima) {
        inputJmlDiterima.addEventListener('input', function() {
            const cat = getTerimaActiveCategory();
            const isSerialized = cat === 'splitter' || TERIMA_SERIALIZED_CATEGORIES.includes(cat);
            if (!isSerialized) {
                const plainRusak = document.getElementById('terima_jumlah_rusak_manual');
                const total = parseInt(this.value, 10) || 0;
                if (plainRusak && parseInt(plainRusak.value, 10) > total) {
                    plainRusak.value = total;
                }
                syncTerimaRusakUI();
            }
        });
    }

    const plainRusakField = document.getElementById('terima_jumlah_rusak_manual');
    if (plainRusakField) {
        plainRusakField.addEventListener('input', function() {
            let val = parseInt(this.value, 10) || 0;
            if (val < 0) val = 0;
            const total = parseInt(document.getElementById('inputJumlahDiterima')?.value, 10) || 0;
            if (val > total) {
                val = total;
                this.value = total;
            }
            syncTerimaRusakUI();
        });
    }

    const formTerima = document.getElementById('formTerimaBarang');
    if (formTerima) {
        formTerima.addEventListener('submit', function(e) {
            const tipe = document.getElementById('selectTipePenerimaan')?.value;
            const cat = getTerimaActiveCategory();
            const jml = parseInt(document.getElementById('inputJumlahDiterima')?.value, 10) || 0;

            if (jml <= 0) {
                e.preventDefault();
                alert('Jumlah barang yang diterima minimal 1.');
                return false;
            }

            if (tipe === 'update_existing') {
                const pId = document.getElementById('selectPerangkatExisting')?.value;
                if (!pId) {
                    e.preventDefault();
                    alert('Pilih perangkat master yang ada di gudang terlebih dahulu.');
                    return false;
                }
            } else if (tipe === 'new_item') {
                const namaP = document.getElementById('inputNamaPerangkatBaru')?.value.trim();
                const katId = document.getElementById('selectKategoriBaru')?.value;
                if (!namaP || !katId) {
                    e.preventDefault();
                    alert('Mohon isi nama perangkat baru dan pilih kategori logistik.');
                    return false;
                }
            }

            if (tipe !== 'non_stok') {
                const isSplitter = cat === 'splitter';
                const isSerialized = isSplitter || TERIMA_SERIALIZED_CATEGORIES.includes(cat);
                const hideMac = TERIMA_NO_MAC_CATEGORIES.includes(cat);

                if (isSplitter) {
                    const rasio = document.getElementById('terima_rasio')?.value;
                    if (!rasio) {
                        e.preventDefault();
                        alert('Silakan pilih rasio output splitter (misal 1:2, 1:4, 1:8, 1:16, 1:32).');
                        return false;
                    }
                }

                if (isSerialized) {
                    let missingSN = false;
                    let missingMAC = false;
                    const rows = document.querySelectorAll('#terimaDeviceUnits .terima-device-unit');
                    rows.forEach(r => {
                        const sn = r.querySelector('input[name="serial_number[]"]')?.value.trim();
                        const mac = r.querySelector('input[name="mac_address[]"]')?.value.trim();
                        if (!sn) missingSN = true;
                        if (!hideMac && !mac) missingMAC = true;
                    });

                    if (missingSN) {
                        e.preventDefault();
                        alert('Semua unit harus memiliki Serial Number (SN).');
                        return false;
                    }
                    if (missingMAC) {
                        e.preventDefault();
                        alert('Semua unit harus memiliki MAC Address.');
                        return false;
                    }
                } else {
                    const rusakCount = parseInt(document.getElementById('terima_jumlah_rusak_manual')?.value, 10) || 0;
                    if (rusakCount > jml) {
                        e.preventDefault();
                        alert(`Jumlah unit rusak (${rusakCount}) tidak boleh melebihi jumlah diterima (${jml}).`);
                        return false;
                    }
                }
            }

            const btnSubmit = document.getElementById('btnSubmitTerimaBarang');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="bx bx-loader-alt bx-spin text-base"></i> Memproses & Menyimpan...';
            }
        });
    }

    // Reopen modal automatically if validation errors returned by Laravel
    @if (isset($errors) && $errors->any())
        const modalEl = document.getElementById('pengadaanModal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    @endif
});
</script>
@endpush
