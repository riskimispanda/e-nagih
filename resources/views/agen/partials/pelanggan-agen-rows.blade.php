@php
    $no = $pelanggan->firstItem();
@endphp
@forelse ($pelanggan as $item)
<tr class="{{ $item->trashed() ? 'customer-deleted' : '' }}">
    <td>{{$no++}}</td>
    <td class="text-start fw-bold">{{$item->nama_customer}}</td>
    <td class="text-start">{{$item->no_hp}}</td>
    <td class="text-start">{{$item->alamat}}</td>
    <td>
        @if($item->paket)
        <span class="badge bg-warning bg-opacity-10 text-warning">
            {{$item->paket->nama_paket}}
        </span>
        @else
        <span class="badge bg-secondary bg-opacity-10 text-secondary">-</span>
        @endif
    </td>
    <td>
        @if($item->trashed())
            <span class="badge bg-danger">Non-Aktif</span>
        @elseif($item->status)
            <span class="badge bg-{{ $item->status->id == 3 ? 'success' : ($item->status->id == 9 ? 'danger' : 'warning') }}">
                {{ $item->status->nama_status }}
            </span>
        @else
            <span class="badge bg-secondary">N/A</span>
        @endif
    </td>
    <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y') }}</td>
    <td>
        <div class="d-flex justify-content-center gap-2">
            <a href="/riwayatPembayaran/{{ $item->id }}" class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Riwayat Pembayaran">
                <i class="bx bx-history"></i>
            </a>
            <a href="http://{{ $item->remote }}" target="_blank" class="btn btn-outline-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Remote Router">
                <i class="bx bx-cloud"></i>
            </a>
            <a href="/traffic-pelanggan/{{ $item->id }}" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Traffic Pelanggan">
                <i class="bx bx-chart"></i>
            </a>
        </div>
    </td>
</tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5">
            <div class="d-flex flex-column align-items-center">
                <i class="bx bx-user-x text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-dark mt-3 mb-2">Tidak Ada Data Pelanggan</h5>
                <p class="text-muted mb-0">Tidak ada pelanggan yang cocok dengan kriteria pencarian Anda.</p>
            </div>
        </td>
    </tr>
@endforelse
