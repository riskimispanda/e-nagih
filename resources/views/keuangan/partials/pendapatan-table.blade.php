@php
$no = 1;
@endphp
@forelse($pendapatan ?? [] as $index => $revenue)
<tr>
    <td>{{ $no++ }}</td>
    <td>
        <div class="d-flex align-items-center">
            <i class="bx bx-money text-dark me-2"></i>
            <span class="fw-semibold text-dark">
                Rp {{ number_format($revenue->jumlah_pendapatan, 0, ',', '.') }}
            </span>
        </div>
    </td>
    <td>
        <span class="badge bg-info bg-opacity-10 text-info">
            {{ $revenue->jenis_pendapatan }}
        </span>
    </td>
    <td>
        <div class="text-muted">
            {{ Str::limit($revenue->deskripsi, 50) }}
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <i class="bx bx-calendar text-primary me-2"></i>
            {{ \Carbon\Carbon::parse($revenue->tanggal)->format('d/m/Y') }}
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <i class="bx bx-credit-card text-secondary me-2"></i>
            {{ $revenue->metode_bayar }}
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <i class="bx bx-user text-primary me-2"></i>
            {{ $revenue->user->name ?? 'N/A' }}
        </div>
    </td>
    <td>
        <div class="d-flex gap-2">
            <button onclick="viewPendapatan({{ $revenue->id }})"
                class="action-btn bg-info bg-opacity-10 text-primary btn-sm">
                <i class="bx bx-show"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-5">
        <div class="d-flex flex-column align-items-center">
            <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
            <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
            <p class="text-muted mb-0">Belum ada data pendapatan lain-lain yang tersedia</p>
        </div>
    </td>
</tr>
@endforelse
