@extends('layouts.contentNavbarLayout')

@section('title', $title)

@section('page-style')
<script src="https://cdn.tailwindcss.com"></script>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
        <h6 class="font-bold {{ $accentText }}"><i class='bx {{ $icon }} me-2'></i>{{ $title }}</h6>
        <a href="/dashboard-logistik" class="px-3 py-1.5 text-sm border border-gray-400 text-gray-600 rounded hover:bg-gray-50">
            <i class='bx bx-arrow-back mr-1'></i> Kembali
        </a>
    </div>
    <div class="p-4 overflow-x-auto">
        <table class="w-full" id="statusTable">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-3 py-2">No.</th>
                    <th class="px-3 py-2">Nama Perangkat</th>
                    <th class="px-3 py-2">Kategori</th>
                    <th class="px-3 py-2">{{ $label }}</th>
                    <th class="px-3 py-2">Total Harga</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($perangkat as $index => $p)
                @php
                $kategori = $p->kategori->nama_logistik ?? 'N/A';
                $jumlah = $p->{$field} ?? 0;
                $unit = ($field == 'stok_tersedia' && $kategori == 'Kabel') ? 'Meter' : 'Unit';
                @endphp
                <tr class="hover:bg-gray-50 text-sm">
                    <td class="px-3 py-2">{{ $index + 1 }}</td>
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-1">
                            <i class='bx bx-chip text-gray-400'></i>
                            <span class="font-medium">{{ $p->nama_perangkat }}</span>
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $kategori }}</span>
                    </td>
                    <td class="px-3 py-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $accentBadge }}">{{ $jumlah }} {{ $unit }}</span>
                    </td>
                    <td class="px-3 py-2 font-semibold">Rp {{ number_format($p->harga * $p->jumlah_stok, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500">
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
