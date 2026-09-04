@extends('layouts.contentNavbarLayout')

@section('title', 'Persetujuan Permintaan Pengadaan Barang')

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
<div class="space-y-5">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-1">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 font-bold text-base">
                    <i class="bx bx-check-shield"></i>
                </span>
                <h4 class="text-xl font-bold text-slate-800 m-0 tracking-tight">Persetujuan Pengadaan Barang</h4>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 m-0">
                Pimpinan / Keuangan memeriksa dan menyetujui pengajuan pembelian perangkat atau kebutuhan logistik.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="/pengadaan" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all shadow-xs no-underline">
                <i class="bx bx-arrow-back text-base text-slate-500"></i> Riwayat Pengadaan
            </a>
        </div>
    </div>

    <!-- Approval List Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <h6 class="text-sm font-bold text-slate-700 m-0">Antrean Menunggu Persetujuan</h6>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                {{ count($pendingPengadaan) }} Permintaan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse m-0">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Barang & Kategori</th>
                        <th class="py-3 px-4">Kuantitas</th>
                        <th class="py-3 px-4">Harga Satuan</th>
                        <th class="py-3 px-4">Total Biaya</th>
                        <th class="py-3 px-4">Pemohon</th>
                        <th class="py-3 px-4">Alokasi RAB</th>
                        <th class="py-3 px-4 text-center">Keputusan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse ($pendingPengadaan as $index => $p)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-semibold text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 text-sm mb-0.5">
                                    {{ $p->nama_barang }}
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                    <span class="inline-flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded text-slate-600 font-medium">
                                        <i class="bx bx-purchase-tag-alt text-xs"></i>
                                        {{ $p->jenis_pengadaan ?? 'Umum' }}
                                    </span>
                                    <span>Diajukan: {{ $p->tanggal_permintaan ? $p->tanggal_permintaan->format('d M Y, H:i') : '-' }}</span>
                                </div>
                                @if ($p->keterangan)
                                    <p class="text-[11px] text-slate-500 italic mt-1 mb-0 max-w-xs" title="{{ $p->keterangan }}">
                                        "{{ $p->keterangan }}"
                                    </p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ number_format($p->jumlah, 0, ',', '.') }} unit
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-600">
                                Rp {{ number_format($p->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-blue-700 text-sm">
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
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        Anggaran: Rp {{ number_format($p->rab->jumlah_anggaran, 0, ',', '.') }}
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs">Tanpa Beban RAB</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Form Setujui --}}
                                    <form action="/pengadaan/acc/{{ $p->id }}" method="POST" class="m-0" onsubmit="return confirm('Setujui pengadaan ini? Sistem akan mencatat pengeluaran kas otomatis.')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition border-0 cursor-pointer">
                                            <i class="bx bx-check text-base"></i> Setujui
                                        </button>
                                    </form>

                                    {{-- Tombol Buka Modal Tolak --}}
                                    <button type="button" 
                                        onclick="openModalTolak({{ $p->id }}, '{{ addslashes($p->nama_barang) }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs border border-rose-200 transition cursor-pointer">
                                        <i class="bx bx-x text-base"></i> Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="bx bx-check-circle text-5xl text-emerald-400 mb-2 block"></i>
                                <div class="font-bold text-slate-600 text-sm">Tidak Ada Antrean Persetujuan</div>
                                <div class="text-xs text-slate-400 mt-1">Semua permintaan pengadaan telah diproses.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Modal Tolak Permintaan Pengadaan --}}
<div class="modal fade" id="tolakPengadaanModal" tabindex="-1" aria-labelledby="tolakPengadaanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden bg-white">
            <div class="px-6 py-4 bg-rose-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="bx bx-x-circle text-2xl"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-white m-0" id="tolakPengadaanModalLabel">Tolak Pengadaan Barang</h5>
                        <p class="text-xs text-rose-100 m-0">Berikan alasan mengapa pengajuan ini tidak disetujui</p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-lg bg-white/10 text-white flex items-center justify-center border-0 cursor-pointer" data-bs-dismiss="modal">
                    <i class="bx bx-x text-xl"></i>
                </button>
            </div>

            <form id="formTolakPengadaan" method="POST" action="">
                @csrf
                <div class="p-6 space-y-3">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                        <span class="text-slate-500 block">Nama Barang:</span>
                        <strong class="text-slate-800 text-sm" id="modalTolakNamaBarang">-</strong>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alasan_tolak" rows="3" class="form-control text-xs sm:text-sm" placeholder="Contoh: Stok di gudang masih mencukupi, anggaran dialihkan ke pos lain, atau spesifikasi tidak sesuai..." required></textarea>
                    </div>
                </div>

                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200/80 flex items-center justify-end gap-2">
                    <button type="button" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-600 bg-white hover:bg-slate-100 transition cursor-pointer" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-xl bg-rose-600 hover:bg-rose-700 text-white transition flex items-center gap-1 cursor-pointer border-0">
                        <i class="bx bx-x"></i> Tolak Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openModalTolak(id, namaBarang) {
    const form = document.getElementById('formTolakPengadaan');
    form.action = '/pengadaan/tolak/' + id;
    document.getElementById('modalTolakNamaBarang').textContent = namaBarang;

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('tolakPengadaanModal'));
    modal.show();
}
</script>
@endpush
