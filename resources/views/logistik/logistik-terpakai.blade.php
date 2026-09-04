@extends('layouts.contentNavbarLayout')

@section('title', $title)

@section('page-style')
<script src="https://cdn.tailwindcss.com"></script>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
        <h6 class="font-bold {{ $accentText }}"><i class='bx {{ $icon }} me-2'></i>{{ $title }}</h6>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="/logistik/terpakai" class="flex items-center gap-2">
                <select name="bulan" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
                    <option value="">Semua Bulan</option>
                    @foreach ($bulanOptions as $opt)
                    <option value="{{ $opt['value'] }}" {{ $selectedBulan == $opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 text-sm border border-blue-500 text-blue-600 rounded hover:bg-blue-50">
                    <i class='bx bx-filter mr-1'></i> Filter
                </button>
            </form>
            <a href="/dashboard-logistik" class="px-3 py-1.5 text-sm border border-gray-400 text-gray-600 rounded hover:bg-gray-50">
                <i class='bx bx-arrow-back mr-1'></i> Kembali
            </a>
        </div>
    </div>
    <div class="p-4 overflow-x-auto">
        <table class="w-full" id="statusTable">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-3 py-2.5 text-center w-12 whitespace-nowrap">No.</th>
                    <th class="px-3 py-2.5 whitespace-nowrap">Nama Perangkat</th>
                    <th class="px-3 py-2.5 whitespace-nowrap">Kategori</th>
                    <th class="px-3 py-2.5 whitespace-nowrap">Nama Customer</th>
                    <th class="px-3 py-2.5 whitespace-nowrap">Nama Teknisi</th>
                    <th class="px-3 py-2.5 whitespace-nowrap">Tanggal Terpasang</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($perangkat as $index => $d)
                @php
                $kategori = $d->perangkat->kategori->nama_logistik ?? 'N/A';
                $teknisi = $d->customer->teknisi->name ?? '-';
                $customerName = $d->customer->nama_customer ?? '-';
                $bulanIndo = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                $isPengganti = is_null($d->tanggal_terpakai);
                $tanggal = $d->tanggal_terpakai
                    ? $d->tanggal_terpakai->format('d') . ', ' . $bulanIndo[(int)$d->tanggal_terpakai->format('n') - 1] . ' ' . $d->tanggal_terpakai->format('Y')
                    : null;
                $tiketClose = $d->customer ? $d->customer->tiket()->where('status_id', 3)->latest()->first() : null;
                $tanggalGanti = $tiketClose ? $tiketClose->updated_at->format('d') . ', ' . $bulanIndo[(int)$tiketClose->updated_at->format('n') - 1] . ' ' . $tiketClose->updated_at->format('Y') : '-';
                @endphp
                <tr class="hover:bg-gray-50 text-sm align-middle">
                    <td class="px-3 py-2.5 text-center whitespace-nowrap">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">{{ $index + 1 }}</span>
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <i class='bx bx-chip text-gray-400'></i>
                            <span class="font-medium text-gray-800">{{ $d->perangkat->nama_perangkat ?? '-' }}</span>
                            @if($isPengganti)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                <i class='bx bx-transfer-alt'></i>
                            </span>
                            @endif
                        </div>
                        @if($d->serial_number)
                        <div class="text-xs text-gray-400 mt-0.5">SN: {{ $d->serial_number }}</div>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ $kategori }}</span>
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-gray-700">{{ $customerName }}</td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-gray-700">{{ $teknisi }}</td>
                    <td class="px-3 py-2.5 whitespace-nowrap">
                        @if($isPengganti)
                        <div class="text-xs text-gray-400">{{ $tanggalGanti }}</div></div>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-medium">{{ $tanggal }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500">
                        <i class='bx {{ $icon }} text-2xl text-gray-300 block mb-1'></i>
                        Belum ada data {{ strtolower($label) }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
