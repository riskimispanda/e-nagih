@extends('layouts.contentNavbarLayout')

@section('title', 'Histori Pelanggan')

@section('page-style')
<!-- Tailwind CSS CDN with Preflight Disabled -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false
        }
    };
</script>
<style>
    .kpi-card {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.025);
    }
    .table-hover-row:hover td {
        background-color: #f8fafc;
    }
</style>
@endsection

@section('content')
@php
    /**
     * Helper tampilan (hanya untuk render, tidak mengubah data):
     * format rupiah dan pilih ikon metode pembayaran.
     */
    $rupiah = function ($value): string {
        return $value ? 'Rp ' . number_format((float) $value, 0, ',', '.') : '-';
    };

    $metodeIcon = function (?string $metode): string {
        $m = strtolower($metode ?? '');

        if ($m === '') {
            return 'bx-credit-card';
        }
        if (str_contains($m, 'cash') || str_contains($m, 'tunai')) {
            return 'bx-money';
        }
        if (str_contains($m, 'tripay') || str_contains($m, 'qris') || str_contains($m, 'dana') || str_contains($m, 'gopay') || str_contains($m, 'ovo') || str_contains($m, 'shopee') || str_contains($m, 'wallet')) {
            return 'bx-qr-scan';
        }

        return 'bx-credit-card';
    };

    // Total untuk baris tfoot (hanya invoice yang tampil di halaman ini).
    $pageInvoices = $invoice->getCollection();
    $pageTagihan = $pageInvoices->sum(fn ($i) => (float) ($i->tagihan ?? 0));
    $pageTambahan = $pageInvoices->sum(fn ($i) => (float) ($i->tambahan ?? 0));
    $pageTunggakan = $pageInvoices->sum(fn ($i) => (float) ($i->tunggakan ?? 0));
    $pageSaldo = $pageInvoices->sum(fn ($i) => (float) ($i->saldo ?? 0));
    $pageDibayar = $pageInvoices->sum(fn ($i) => (float) $i->pembayaran->sum('jumlah_bayar'));

    // Nomor halaman untuk pagination (maks. 5 tombol + elipsis).
    $currentPage = $invoice->currentPage();
    $lastPage = $invoice->lastPage();
    if ($lastPage <= 7) {
        $paginationPages = range(1, $lastPage);
    } else {
        $paginationPages = [1];
        if ($currentPage > 4) {
            $paginationPages[] = '...';
        }
        foreach (range(max(2, $currentPage - 1), min($lastPage - 1, $currentPage + 1)) as $p) {
            $paginationPages[] = $p;
        }
        if ($currentPage < $lastPage - 3) {
            $paginationPages[] = '...';
        }
        $paginationPages[] = $lastPage;
    }
@endphp

<div class="space-y-6">

    <!-- ======================================================================== -->
    <!-- TOP HEADER / ACTION BAR                                                  -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Left Info -->
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Modul Keuangan &middot; Riwayat Pembayaran</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight leading-tight m-0 flex items-center gap-2.5">
                    <i class="bx bx-history text-indigo-600 text-2xl"></i> Histori Pelanggan
                </h1>
                <p class="text-slate-500 text-xs sm:text-sm m-0">
                    Riwayat tagihan dan pembayaran
                    <span class="font-semibold text-slate-700">{{ $deaktivasi->nama_customer ?? '-' }}</span>,
                    termasuk rincian tiap transaksi.
                </p>
            </div>

            <!-- Right Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="javascript:history.back()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all shadow-sm no-underline">
                    <i class="bx bx-chevron-left text-base text-slate-500"></i> Kembali
                </a>
                <button type="button" onclick="location.reload()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-all cursor-pointer border-0">
                    <i class="bx bx-refresh text-base"></i> Segarkan
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- KPI SUMMARY CARDS                                                        -->
    <!-- ======================================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Total Invoice -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Total Invoice</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bx bx-receipt"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $summary['total_invoice'] }}</div>
                <div class="text-[11px] font-medium text-slate-500 mt-0.5">Seluruh periode</div>
            </div>
        </div>

        <!-- Total Dibayar -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Total Dibayar</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bx bx-wallet"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $rupiah($summary['total_dibayar']) }}</div>
                <div class="text-[11px] font-medium text-emerald-600 mt-0.5 truncate">Akumulasi semua pembayaran</div>
            </div>
        </div>

        <!-- Belum Bayar -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Belum Bayar</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bx bx-time-five"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $summary['belum_bayar'] }}</div>
                <div class="text-[11px] font-medium text-amber-600 mt-0.5">
                    {{ $summary['belum_bayar'] > 0 ? 'Invoice menunggu pembayaran' : 'Tidak ada tagihan tertunda' }}
                </div>
            </div>
        </div>

        <!-- Tunggakan -->
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Tunggakan Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                    <i class="bx bx-error-circle"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $rupiah($summary['tunggakan']) }}</div>
                <div class="text-[11px] font-medium text-rose-600 mt-0.5">
                    {{ $summary['tunggakan'] > 0 ? 'Dari invoice belum bayar' : 'Tidak ada tunggakan' }}
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================================== -->
    <!-- STATUS DEAKTIVASI PELANGGAN                                              -->
    <!-- ======================================================================== -->
    @if($deaktivasi && $deaktivasi->trashed())
    <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200 flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg shrink-0">
            <i class="bx bx-error-circle"></i>
        </div>
        <div class="text-xs sm:text-sm text-amber-800">
            <div class="font-bold">Pelanggan telah dinonaktifkan</div>
            <div class="mt-0.5">
                Deaktivasi pada
                <span class="font-semibold">{{ \Carbon\Carbon::parse($deaktivasi->deleted_at)->locale('id')->isoFormat('D MMMM Y, H:mm:ss') }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- ======================================================================== -->
    <!-- RIWAYAT TABLE                                                            -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        <!-- Table Header -->
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
                    <i class="bx bx-receipt"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 m-0 leading-tight">Riwayat Invoice</h3>
                    <p class="text-xs text-slate-500 m-0 mt-0.5">Klik tombol di kolom aksi untuk melihat rincian pembayaran tiap invoice</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100 self-start md:self-auto">
                <i class="bx bx-receipt"></i> {{ $invoice->total() }} Invoice
            </span>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-3 text-center">Paket</th>
                        <th class="py-3 px-3 text-right">Tagihan</th>
                        <th class="py-3 px-3 text-center">Tambahan</th>
                        <th class="py-3 px-3 text-center">Tunggakan</th>
                        <th class="py-3 px-3 text-center">Sisa Saldo</th>
                        <th class="py-3 px-4 text-center">Bayar Terakhir</th>
                        <th class="py-3 px-3 text-center">Periode</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Total Dibayar</th>
                        <th class="py-3 px-3">Metode</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4 text-center w-16">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @php $no = ($invoice->currentPage()-1)*10 + 1; @endphp
                    @forelse ($invoice as $item)
                        @php
                            // Relasi pembayaran sudah diurutkan dari controller (tanggal_bayar, id).
                            $payments = $item->pembayaran;
                            $lastPayment = $payments->last();
                            $totalDibayar = $payments->sum('jumlah_bayar');
                            $metodeUnik = $payments->pluck('metode_bayar')->filter()->unique()->values();
                        @endphp
                        <tr class="table-hover-row transition-colors">
                            <td class="py-3 px-4 text-center font-medium text-slate-400">{{ $no++ }}</td>

                            <!-- Pelanggan -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm shrink-0">
                                        <i class="bx bx-user"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $item->customer->nama_customer ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-400">ID Pelanggan: {{ $item->customer_id }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Paket -->
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/80">
                                    {{ $item->customer->paket->nama_paket ?? '-' }}
                                </span>
                            </td>

                            <!-- Tagihan -->
                            <td class="py-3 px-3 text-right font-bold text-slate-800 whitespace-nowrap">{{ $rupiah($item->tagihan) }}</td>

                            <!-- Tambahan -->
                            <td class="py-3 px-3 text-center">
                                @if($item->tambahan)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60 whitespace-nowrap">
                                        {{ $rupiah($item->tambahan) }}
                                    </span>
                                @else
                                    <span class="text-slate-300 font-medium">-</span>
                                @endif
                            </td>

                            <!-- Tunggakan -->
                            <td class="py-3 px-3 text-center">
                                @if($item->tunggakan)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/60 whitespace-nowrap">
                                        {{ $rupiah($item->tunggakan) }}
                                    </span>
                                @else
                                    <span class="text-slate-300 font-medium">-</span>
                                @endif
                            </td>

                            <!-- Sisa Saldo -->
                            <td class="py-3 px-3 text-center">
                                @if($item->saldo)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 whitespace-nowrap">
                                        {{ $rupiah($item->saldo) }}
                                    </span>
                                @else
                                    <span class="text-slate-300 font-medium">-</span>
                                @endif
                            </td>

                            <!-- Bayar Terakhir -->
                            <td class="py-3 px-4 text-center">
                                @if($lastPayment)
                                    <div class="font-semibold text-slate-700 whitespace-nowrap"
                                        title="{{ \Carbon\Carbon::parse($lastPayment->tanggal_bayar)->locale('id')->isoFormat('dddd, D MMMM Y') }}">
                                        {{ \Carbon\Carbon::parse($lastPayment->tanggal_bayar)->locale('id')->isoFormat('D MMM Y') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ \Carbon\Carbon::parse($lastPayment->tanggal_bayar)->locale('id')->isoFormat('dddd') }}
                                    </div>
                                @else
                                    <span class="text-slate-300 font-medium">-</span>
                                @endif
                            </td>

                            <!-- Periode -->
                            <td class="py-3 px-3 text-center font-medium text-slate-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->jatuh_tempo)->locale('id')->translatedFormat('F Y') }}
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-3 text-center">
                                @if($item->status_id == 7)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60 whitespace-nowrap">
                                        <i class="bx bx-x-circle text-sm"></i> Belum Bayar
                                    </span>
                                @elseif($item->status_id == 8)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 whitespace-nowrap">
                                        <i class="bx bx-check-circle text-sm"></i> Sudah Bayar
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 whitespace-nowrap">
                                        <i class="bx bx-help-circle text-sm"></i> Unknown
                                    </span>
                                @endif
                            </td>

                            <!-- Total Dibayar -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if($totalDibayar)
                                    <div class="font-extrabold text-slate-800">{{ $rupiah($totalDibayar) }}</div>
                                    @if($payments->count() > 1)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 mt-0.5">
                                            {{ $payments->count() }}x bayar
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-300 font-medium">-</span>
                                @endif
                            </td>

                            <!-- Metode -->
                            <td class="py-3 px-3">
                                @forelse($metodeUnik as $metode)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/80 mb-0.5">
                                        <i class="bx {{ $metodeIcon($metode) }} text-sm text-slate-500"></i>{{ $metode }}
                                    </span>
                                @empty
                                    <span class="text-slate-300 font-medium">-</span>
                                @endforelse
                            </td>

                            <!-- Keterangan -->
                            <td class="py-3 px-4">
                                @if($lastPayment && $lastPayment->keterangan)
                                    <div class="text-[11px] text-slate-500 max-w-[220px] truncate" title="{{ $lastPayment->keterangan }}">
                                        {{ $lastPayment->keterangan }}
                                    </div>
                                @else
                                    <span class="text-slate-300 font-medium">-</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3 px-4 text-center">
                                @if($payments->isNotEmpty())
                                    <button type="button"
                                        class="detail-toggle w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-indigo-600 flex items-center justify-center text-sm transition-colors cursor-pointer mx-auto"
                                        data-target="#detail-{{ $item->id }}" aria-expanded="false" title="Lihat rincian pembayaran">
                                        <i class="bx bx-plus-circle"></i>
                                    </button>
                                @else
                                    <span class="text-slate-300 font-medium">-</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Detail Rincian Pembayaran -->
                        @if($payments->isNotEmpty())
                            <tr class="detail-row hidden" id="detail-{{ $item->id }}">
                                <td colspan="14" class="p-0">
                                    <div class="bg-slate-50/80 p-5">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                                                    <i class="bx bx-receipt"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-slate-800">Rincian Pembayaran</div>
                                                    <div class="text-[11px] text-slate-500">
                                                        Periode {{ \Carbon\Carbon::parse($item->jatuh_tempo)->locale('id')->translatedFormat('F Y') }}
                                                        &middot; {{ $payments->count() }} transaksi
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200/60 self-start sm:self-auto">
                                                <i class="bx bx-wallet"></i> Total: {{ $rupiah($totalDibayar) }}
                                            </span>
                                        </div>

                                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                                            <table class="w-full text-left border-collapse">
                                                <thead>
                                                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                                        <th class="py-2.5 px-4 w-12 text-center">#</th>
                                                        <th class="py-2.5 px-4">Tanggal Bayar</th>
                                                        <th class="py-2.5 px-4 text-right">Jumlah Bayar</th>
                                                        <th class="py-2.5 px-4 text-center">Metode</th>
                                                        <th class="py-2.5 px-4">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                                                    @foreach($payments as $index => $payment)
                                                        @php
                                                            $tanggalBayar = $payment->tanggal_bayar
                                                                ? \Carbon\Carbon::parse($payment->tanggal_bayar)
                                                                : null;
                                                        @endphp
                                                        <tr class="table-hover-row transition-colors">
                                                            <td class="py-2.5 px-4 text-center font-medium text-slate-400">{{ $index + 1 }}</td>
                                                            <td class="py-2.5 px-4">
                                                                @if($tanggalBayar)
                                                                    <div class="font-semibold text-slate-700 whitespace-nowrap">
                                                                        <i class="bx bx-calendar-event text-indigo-500 mr-1"></i>{{ $tanggalBayar->locale('id')->isoFormat('D MMMM Y') }}
                                                                    </div>
                                                                    @if($tanggalBayar->format('H:i') !== '00:00')
                                                                        <div class="text-[10px] text-slate-400">Pukul {{ $tanggalBayar->format('H.i') }} WIB</div>
                                                                    @endif
                                                                @else
                                                                    <span class="text-slate-300 font-medium">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="py-2.5 px-4 text-right font-bold text-slate-800 whitespace-nowrap">{{ $rupiah($payment->jumlah_bayar) }}</td>
                                                            <td class="py-2.5 px-4 text-center">
                                                                @if($payment->metode_bayar)
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/80">
                                                                        <i class="bx {{ $metodeIcon($payment->metode_bayar) }} text-sm text-slate-500"></i>{{ $payment->metode_bayar }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-slate-300 font-medium">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="py-2.5 px-4 text-slate-500">{{ $payment->keterangan ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="14" class="py-12 text-center text-slate-400">
                                <i class="bx bx-receipt text-3xl text-slate-300 block mb-2"></i>
                                <div class="font-medium text-slate-600">Belum ada riwayat invoice</div>
                                <div class="text-xs text-slate-400 mt-0.5">Invoice pelanggan ini masih kosong</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200 text-xs font-bold text-slate-700">
                        <td class="py-3.5 px-4" colspan="3">
                            <span class="text-slate-500 uppercase tracking-wider text-[11px]">Total Halaman Ini ({{ $invoice->count() }} Invoice)</span>
                        </td>
                        <td class="py-3.5 px-3 text-right text-slate-900 font-extrabold whitespace-nowrap">{{ $rupiah($pageTagihan) }}</td>
                        <td class="py-3.5 px-3 text-center text-amber-700 font-extrabold whitespace-nowrap">{{ $pageTambahan ? $rupiah($pageTambahan) : '-' }}</td>
                        <td class="py-3.5 px-3 text-center text-rose-700 font-extrabold whitespace-nowrap">{{ $pageTunggakan ? $rupiah($pageTunggakan) : '-' }}</td>
                        <td class="py-3.5 px-3 text-center text-emerald-700 font-extrabold whitespace-nowrap">{{ $pageSaldo ? $rupiah($pageSaldo) : '-' }}</td>
                        <td class="py-3.5 px-4" colspan="3"></td>
                        <td class="py-3.5 px-4 text-right text-slate-900 font-black whitespace-nowrap">{{ $rupiah($pageDibayar) }}</td>
                        <td class="py-3.5 px-4" colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Menampilkan
                <span class="font-bold text-slate-700">{{ $invoice->firstItem() ?? 0 }}</span> -
                <span class="font-bold text-slate-700">{{ $invoice->lastItem() ?? 0 }}</span>
                dari <span class="font-bold text-slate-700">{{ number_format($invoice->total(), 0, ',', '.') }}</span> invoice
            </div>

            @if($invoice->hasPages())
                <div class="flex items-center gap-1">
                    @if($invoice->onFirstPage())
                        <span class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-300 flex items-center justify-center opacity-50">
                            <i class="bx bx-chevron-left text-sm"></i>
                        </span>
                    @else
                        <a href="{{ $invoice->url($invoice->currentPage() - 1) }}" title="Halaman Sebelumnya"
                            class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 font-semibold text-xs transition flex items-center justify-center no-underline">
                            <i class="bx bx-chevron-left text-sm"></i>
                        </a>
                    @endif

                    @foreach($paginationPages as $p)
                        @if($p === '...')
                            <span class="w-6 text-center text-slate-400 text-xs font-mono select-none">...</span>
                        @elseif($p == $invoice->currentPage())
                            <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-sm flex items-center justify-center">{{ $p }}</span>
                        @else
                            <a href="{{ $invoice->url($p) }}"
                                class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 font-semibold text-xs transition flex items-center justify-center no-underline">{{ $p }}</a>
                        @endif
                    @endforeach

                    @if($invoice->hasMorePages())
                        <a href="{{ $invoice->url($invoice->currentPage() + 1) }}" title="Halaman Selanjutnya"
                            class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 font-semibold text-xs transition flex items-center justify-center no-underline">
                            <i class="bx bx-chevron-right text-sm"></i>
                        </a>
                    @else
                        <span class="w-8 h-8 rounded-xl border border-slate-200 bg-white text-slate-300 flex items-center justify-center opacity-50">
                            <i class="bx bx-chevron-right text-sm"></i>
                        </span>
                    @endif
                </div>
            @endif
        </div>

    </div>

</div>

<script>
    // Buka/tutup rincian pembayaran per invoice
    $(document).on('click', '.detail-toggle', function () {
        const $btn = $(this);
        const $row = $($btn.data('target'));
        const willShow = $row.hasClass('hidden');

        $row.toggleClass('hidden', !willShow);
        $btn.attr('aria-expanded', willShow ? 'true' : 'false');
        $btn.find('i')
            .toggleClass('bx-plus-circle', !willShow)
            .toggleClass('bx-minus-circle', willShow);
        $btn.closest('tr').toggleClass('bg-slate-50', willShow);
    });
</script>
@endsection
