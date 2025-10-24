@forelse ($pengeluarans as $key => $pengeluaran)
<tr class="text-center">
    <td>{{ $pengeluarans->firstItem() + $key }}</td>
    <td>
        <span class="badge bg-label-info">
            {{ \Carbon\Carbon::parse($pengeluaran->tanggal_pengeluaran)->format('d-M-Y') }}
        </span>
    </td>
    <td>{{ $pengeluaran->jenis_pengeluaran }}</td>
    <td>{{ $pengeluaran->keterangan }}</td>
    <td data-amount="{{ $pengeluaran->jumlah_pengeluaran }}">
        Rp {{ number_format($pengeluaran->jumlah_pengeluaran, 0, ',', '.') }}
    </td>
    <td>
        <span class="badge bg-label-primary">
            {{ $pengeluaran->kas->jenis_kas ?? '-'}}
        </span>
    </td>
    <td>
        @if ($pengeluaran->status_id == 1)
            <span class="badge bg-warning bg-opacity-10 text-warning">Menunggu Konfirmasi Penghapusan</span>
        @elseif ($pengeluaran->status_id == 2)
            <span class="badge bg-success bg-opacity-10 text-success">Approved</span>
        @elseif ($pengeluaran->status_id == 3)
            <span class="badge bg-success bg-opacity-10 text-success">Berhasil</span>
        @endif
    </td>
    <td>
        <span class="badge bg-danger bg-opacity-10 text-danger">
            {{ $pengeluaran->user->name }}
        </span>
    </td>
    <td>
        <div class="d-flex gap-2">
            <a href="/edit-pengeluaran/{{ $pengeluaran->id }}">
                <button class="btn btn-outline-warning btn-sm mb-1" title="Edit" data-bs-toggle="tooltip" data-bs-placement="bottom">
                    <i class="bx bx-edit"></i>
                </button>
            </a>
            <button class="btn btn-outline-danger btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#deletePengeluaran{{ $pengeluaran->id }}" title="Hapus" data-bs-placement="bottom">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <div class="d-flex flex-column align-items-center">
            <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
            <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
            <p class="text-muted mb-0">Belum ada Transaksi</p>
        </div>
    </td>
</tr>
@endforelse