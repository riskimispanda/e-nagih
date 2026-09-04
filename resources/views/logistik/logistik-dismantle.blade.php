@extends('layouts.contentNavbarLayout')

@section('title', $title)

@section('page-style')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .checkbox-wiggle:checked {
        animation: wiggle 0.2s ease;
    }
    @keyframes wiggle {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
</style>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
        <h6 class="font-bold {{ $accentText }}"><i class='bx {{ $icon }} me-2'></i>{{ $title }}</h6>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="/logistik/dismantle" class="flex items-center gap-2">
                <select name="bulan" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
                    <option value="">Semua Bulan</option>
                    @foreach ($bulanOptions as $opt)
                    <option value="{{ $opt['value'] }}" {{ $selectedBulan == $opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 text-sm border border-purple-500 text-purple-600 rounded hover:bg-purple-50">
                    <i class='bx bx-filter mr-1'></i> Filter
                </button>
            </form>
            <button id="bulkDeleteBtn" class="px-3 py-1.5 text-sm border border-red-400 text-red-600 rounded hover:bg-red-50 hidden" onclick="confirmBulkDelete()">
                <i class='bx bx-trash mr-1'></i> Hapus Terpilih
            </button>
            <a href="/dashboard-logistik" class="px-3 py-1.5 text-sm border border-gray-400 text-gray-600 rounded hover:bg-gray-50">
                <i class='bx bx-arrow-back mr-1'></i> Kembali
            </a>
        </div>
    </div>
    <div class="p-4 overflow-x-auto">
        <form id="bulkForm" method="POST" action="/logistik/dismantle/bulk-delete">
            @csrf
            <table class="w-full" id="statusTable">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        <th class="px-3 py-2.5 text-center w-10">
                            <input type="checkbox" id="checkAll" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        </th>
                        <th class="px-3 py-2.5 text-center w-12 whitespace-nowrap">No.</th>
                        <th class="px-3 py-2.5 whitespace-nowrap">Nama Barang</th>
                        <th class="px-3 py-2.5 whitespace-nowrap">Status Barang</th>
                        <th class="px-3 py-2.5 whitespace-nowrap">Keterangan Dismantle</th>
                        <th class="px-3 py-2.5 whitespace-nowrap">Nama Pelanggan</th>
                        <th class="px-3 py-2.5 whitespace-nowrap">Nama Teknisi</th>
                        <th class="px-3 py-2.5 whitespace-nowrap">Tanggal Dismantle</th>
                        <th class="px-3 py-2.5 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($dismantles as $index => $d)
                    @php
                    $badge = match($d->status_barang) {
                        14 => 'bg-green-100 text-green-700',
                        4 => 'bg-yellow-100 text-yellow-700',
                        15 => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                    $bulanIndo = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    $tanggal = $d->tanggal_dismantle
                        ? $d->tanggal_dismantle->format('d') . ' ' . $bulanIndo[(int)$d->tanggal_dismantle->format('n') - 1] . ' ' . $d->tanggal_dismantle->format('Y')
                        : '-';
                    @endphp
                    <tr class="hover:bg-gray-50 text-sm align-middle">
                        <td class="px-3 py-2.5 text-center">
                            <input type="checkbox" name="ids[]" value="{{ $d->id }}" class="row-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500 checkbox-wiggle">
                        </td>
                        <td class="px-3 py-2.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">{{ $index + 1 }}</span>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <i class='bx bx-chip text-gray-400'></i>
                                <span class="font-medium text-gray-800">{{ $d->perangkat_nama }}</span>
                            </div>
                            @if($d->serial_number)
                            <div class="text-xs text-gray-400 mt-0.5">SN: {{ $d->serial_number }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $d->statusLabel() }}</span>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap text-gray-700">{{ $d->keterangan_dismantle ?? '-' }}</td>
                        <td class="px-3 py-2.5 whitespace-nowrap text-gray-700">{{ $d->customer_nama }}</td>
                        <td class="px-3 py-2.5 whitespace-nowrap text-gray-700">{{ $d->teknisi->name ?? '-' }}</td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-purple-50 text-purple-600 text-xs font-medium">{{ $tanggal }}</span>
                        </td>
                        <td class="px-3 py-2.5 text-center whitespace-nowrap">
                            <button type="button" onclick="confirmSingleDelete({{ $d->id }})" class="px-2 py-1 text-sm border border-red-400 text-red-600 rounded hover:bg-red-50 transition" title="Hapus">
                                <i class='bx bx-trash'></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-3 py-8 text-center text-sm text-gray-500">
                            <i class='bx {{ $icon }} text-2xl text-gray-300 block mb-1'></i>
                            Belum ada data {{ strtolower($label) }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var checkAll = document.getElementById('checkAll');
    var rowCheckboxes = document.querySelectorAll('.row-checkbox');
    var bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    if (!checkAll || !rowCheckboxes.length) return;

    checkAll.addEventListener('change', function() {
        rowCheckboxes.forEach(function(cb) {
            cb.checked = checkAll.checked;
        });
        toggleBulkBtn();
    });

    rowCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            var allChecked = true;
            rowCheckboxes.forEach(function(c) { if (!c.checked) allChecked = false; });
            checkAll.checked = allChecked;
            toggleBulkBtn();
        });
    });

    function toggleBulkBtn() {
        var anyChecked = false;
        rowCheckboxes.forEach(function(c) { if (c.checked) anyChecked = true; });
        bulkDeleteBtn.classList.toggle('hidden', !anyChecked);
    }
});

function confirmSingleDelete(id) {
    Swal.fire({
        title: 'Hapus Data Dismantle?',
        text: 'Data yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logistik/dismantle/delete/' + id;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmBulkDelete() {
    var checked = document.querySelectorAll('.row-checkbox:checked');
    if (!checked.length) return;

    Swal.fire({
        title: 'Hapus ' + checked.length + ' Data Dismantle?',
        text: 'Semua data terpilih akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('bulkForm').submit();
        }
    });
}
</script>
@endpush
@endsection