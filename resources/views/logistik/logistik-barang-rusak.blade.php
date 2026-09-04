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
            <form method="GET" class="flex items-center gap-2">
                <select name="bulan" onchange="this.form.submit()" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
                    <option value="">Semua Bulan</option>
                    @foreach ($bulanOptions as $opt)
                        <option value="{{ $opt['value'] }}" {{ $selectedBulan == $opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
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
                    <th class="px-3 py-2">No.</th>
                    <th class="px-3 py-2">Nama Customer</th>
                    <th class="px-3 py-2">Nama Teknisi</th>
                    <th class="px-3 py-2">Tanggal Closing</th>
                    <th class="px-3 py-2">Keterangan Closing</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($perangkat as $index => $t)
                @php
                    $customer = $t->customer;
                    $teknisi = $t->teknisi ?? ($customer->teknisi ?? null);
                @endphp
                <tr class="hover:bg-gray-50 text-sm">
                    <td class="px-3 py-2">{{ ($perangkat->firstItem() ?? 1) + $index - 1 }}</td>
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-1">
                            <i class='bx bx-user text-gray-400'></i>
                            <span class="font-medium">{{ $customer->nama_customer ?? '-' }}</span>
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $teknisi->name ?? '-' }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-gray-600">
                        {{ $t->tanggal_selesai ? \Carbon\Carbon::parse($t->tanggal_selesai)->format('d F Y') : '-' }}
                    </td>
                    <td class="px-3 py-2 text-gray-600">
                        {{ $t->keterangan ?? '-' }}
                    </td>
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

        @if ($perangkat->hasPages())
            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm text-gray-600">
                <div>
                    Menampilkan {{ $perangkat->firstItem() }}–{{ $perangkat->lastItem() }}
                    dari {{ $perangkat->total() }} data
                </div>
                <div class="flex items-center gap-1">
                    {{-- Previous --}}
                    @if ($perangkat->onFirstPage())
                        <span class="px-3 py-1.5 rounded border border-gray-200 text-gray-300 cursor-not-allowed">Sebelumnya</span>
                    @else
                        <a href="{{ $perangkat->previousPageUrl() }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Sebelumnya</a>
                    @endif

                    {{-- Page numbers --}}
                    @foreach ($perangkat->getUrlRange(max(1, $perangkat->currentPage() - 2), min($perangkat->lastPage(), $perangkat->currentPage() + 2)) as $page => $url)
                        @if ($page == $perangkat->currentPage())
                            <span class="px-3 py-1.5 rounded border border-gray-400 bg-gray-100 font-medium text-gray-700">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($perangkat->hasMorePages())
                        <a href="{{ $perangkat->nextPageUrl() }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Berikutnya</a>
                    @else
                        <span class="px-3 py-1.5 rounded border border-gray-200 text-gray-300 cursor-not-allowed">Berikutnya</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
