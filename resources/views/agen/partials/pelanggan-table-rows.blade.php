@forelse ($pelanggan as $item)
    <tr>
        <td>{{ $loop->iteration + ($pelanggan->currentPage() - 1) * $pelanggan->perPage() }}</td>
        <td class="text-start">{{ $item->nama_customer }}</td>
        <td>{{ $item->no_hp }}</td>
        <td class="text-start">{{ $item->alamat }}</td>
        <td>
            <span class="badge bg-warning bg-opacity-10 text-warning">
                {{ $item->paket->nama_paket ?? 'N/A' }}
            </span>
        </td>
        <td>
            @if($item->status_id == 3)
                <span class="badge bg-success bg-opacity-10 text-success">
                    <i class="bx bx-check-circle me-1"></i>Aktif
                </span>
            @elseif($item->status_id == 1)
                <span class="badge bg-warning bg-opacity-10 text-warning">
                    <i class="bx bx-time me-1"></i>Menunggu
                </span>
            @elseif($item->status_id == 9)
                <span class="badge bg-danger bg-opacity-10 text-danger">
                    <i class="bx bx-block me-1"></i>Blokir
                </span>
            @else
                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                    <i class="bx bx-question-mark me-1"></i>Unknown
                </span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-4">
            <div class="d-flex flex-column align-items-center">
                <i class="bx bx-search-alt-2 fs-1 text-muted mb-2"></i>
                <span class="text-muted">Tidak ada data pelanggan yang ditemukan</span>
            </div>
        </td>
    </tr>
@endforelse
