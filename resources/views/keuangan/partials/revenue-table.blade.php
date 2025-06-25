{{-- <table class="table table-modern" id="dataTable">
    <thead>
        <tr>
            <th class="sortable" data-sort="index">
                <i class="bx bx-hash me-1"></i>No
                <i class="bx bx-chevron-up sort-icon"></i>
            </th>
            <th class="sortable" data-sort="date">
                <i class="bx bx-calendar me-1"></i>Tanggal
                <i class="bx bx-chevron-up sort-icon"></i>
            </th>
            <th class="sortable" data-sort="customer">
                <i class="bx bx-user me-1"></i>Pelanggan
                <i class="bx bx-chevron-up sort-icon"></i>
            </th>
            <th class="sortable" data-sort="package">
                <i class="bx bx-package me-1"></i>Paket
                <i class="bx bx-chevron-up sort-icon"></i>
            </th>
            <th class="sortable" data-sort="amount">
                <i class="bx bx-money me-1"></i>Jumlah
                <i class="bx bx-chevron-up sort-icon"></i>
            </th>
            <th class="sortable" data-sort="due_date">
                <i class="bx bx-time me-1"></i>Jatuh Tempo
                <i class="bx bx-chevron-up sort-icon"></i>
            </th>
            <th class="sortable" data-sort="status">
                <i class="bx bx-check-circle me-1"></i>Status
                <i class="bx bx-chevron-up sort-icon"></i>
            </th>
            <th>
                <i class="bx bx-cog me-1"></i>Aksi
            </th>
        </tr>
    </thead>
    <tbody id="tableBody">
        @forelse($invoices ?? [] as $index => $invoice)
            <tr>
                <td class="fw-medium">{{ $invoices->firstItem() + $index }}</td>
                <td>{{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }}</td>
                <td>
                    <div class="fw-medium text-dark">{{ $invoice->customer->nama_customer ?? 'N/A' }}</div>
                    <small class="text-muted">{{ Str::limit($invoice->customer->alamat ?? '', 30) }}</small>
                </td>
                <td>
                    <span class="badge bg-light text-dark border">{{ $invoice->paket->nama_paket ?? 'N/A' }}</span>
                </td>
                <td class="fw-bold text-dark">Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}</td>
                <td>{{ \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d/m/Y') }}</td>
                <td>
                    @php
                        $statusClass = 'status-pending';
                        if ($invoice->status->nama_status == 'Sudah Bayar') {
                            $statusClass = 'status-paid';
                        } elseif (\Carbon\Carbon::parse($invoice->jatuh_tempo)->isPast()) {
                            $statusClass = 'status-overdue';
                        }
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ $invoice->status->nama_status ?? 'N/A' }}
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-primary action-btn"
                            onclick="viewInvoice({{ $invoice->id }})" title="Lihat Detail">
                            <i class="bx bx-show"></i>
                        </button>
                        @if ($invoice->status->nama_status != 'Sudah Bayar')
                            <a href="{{ route('payment.show', $invoice->id) }}"
                                class="btn btn-sm btn-outline-success action-btn" title="Bayar">
                                <i class="bx bx-credit-card"></i>
                            </a>
                        @endif
                        <button class="btn btn-sm btn-outline-info action-btn"
                            onclick="printInvoice({{ $invoice->id }})" title="Cetak">
                            <i class="bx bx-printer"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="no-data">
                    <i class="bx bx-inbox"></i>
                    <h6 class="mt-2 mb-1">Tidak ada data</h6>
                    <p class="mb-0">Belum ada data pendapatan yang tersedia</p>
                </td>
            </tr>
        @endforelse
    </tbody>
</table> --}}
