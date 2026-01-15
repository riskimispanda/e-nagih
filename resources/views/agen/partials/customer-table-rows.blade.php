@php
    $rowNumber = ($invoices->currentPage() - 1) * $invoices->perPage() + 1;
    $is_ajax = $is_ajax ?? false;
@endphp
@forelse ($invoices as $invoice)
    @php
        $customer = $invoice->customer;
    @endphp
    <tr class="customer-row text-center {{ $customer && $customer->trashed() ? 'customer-deleted' : '' }}"
        data-id="{{ $customer->id ?? '-' }}"
        data-tagihan="{{ $invoice->status ? ($invoice->status->nama_status == 'Sudah Bayar' ? '0' : ($invoice->tagihan ?? '0')) : '0' }}"
        data-customer-id="{{ $customer->id ?? '-' }}" data-invoice-id="{{ $invoice->id }}"
        data-tagihan-tambahan="{{ $invoice->tambahan ?? '0' }}"
        data-status-tagihan="{{ $invoice->status->nama_status ?? 'N/A' }}"
        data-jatuh-tempo="{{ $invoice->jatuh_tempo ?? '' }}"
        data-bulan-tempo="{{ $invoice->jatuh_tempo ? \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('F') : '' }}"
        data-customer-deleted="{{ $customer && $customer->trashed() ? 'true' : 'false' }}">

        <td class="fw-bold text-center">{{ $rowNumber++ }}</td>

        {{-- Nama --}}
        <td class="text-start customer-name">
            {{ $customer->nama_customer ?? 'Tidak Diketahui' }}
        </td>

        {{-- Alamat --}}
        <td class="text-start customer-address">
            {{ $customer->alamat ?? '-' }}
        </td>

        {{-- Nomor HP --}}
        <td class="nomor-hp">{{ $customer->no_hp ?? '-' }}</td>

        {{-- Paket --}}
        <td class="text-center">
            @if($customer && $customer->paket)
                <span class="badge bg-warning bg-opacity-10 text-warning">
                    {{ $customer->paket->nama_paket }}
                </span>
            @endif
            @if($customer && $customer->status_id == 3)
                <small class="badge bg-success bg-opacity-10 text-success mt-1">Aktif</small>
            @elseif($customer && $customer->status_id == 9)
                <small class="badge bg-danger bg-opacity-10 text-danger mt-1">Non Aktif</small>
            @else
                <small class="badge bg-secondary bg-opacity-10 text-secondary mt-1">-</small>
            @endif
        </td>

        {{-- Total tagihan --}}
        <td class="text-end">
            Rp
            {{ number_format(($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan - ($invoice->saldo ?? 0)), 0, ',', '.') }}
        </td>

        {{-- Status pembayaran --}}
        <td class="text-center">
            @if ($invoice->status)
                <span class="d-none">{{ $invoice->status->nama_status }}</span>
                <span class="badge
                            bg-{{ $invoice->status->nama_status == 'Sudah Bayar' ? 'success' : 'danger' }}
                            bg-opacity-10
                            text-{{ $invoice->status->nama_status == 'Sudah Bayar' ? 'success' : 'danger' }}">
                    {{ $invoice->status->nama_status }}
                </span>
            @else
                <span class="d-none">N/A</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary">N/A</span>
            @endif
        </td>

        {{-- Jatuh tempo --}}
        <td class="text-center">
            @php
                try {
                    $jatuhTempo = $invoice->jatuh_tempo ? \Carbon\Carbon::parse($invoice->jatuh_tempo) : null;
                    $isOverdue = $jatuhTempo && $jatuhTempo->isPast() && optional($invoice->status)->nama_status != 'Sudah Bayar';
                } catch (\Exception $e) {
                    $jatuhTempo = null;
                    $isOverdue = false;
                }
            @endphp
            @if($jatuhTempo)
                <span
                    class="badge {{ $isOverdue ? 'bg-danger' : 'bg-info' }} bg-opacity-10 {{ $isOverdue ? 'text-danger' : 'text-info' }}">
                    {{ $jatuhTempo->format('d M Y') }}
                    @if($isOverdue)
                        <br><small>Terlambat</small>
                    @endif
                </span>
            @else
                <span class="badge bg-secondary bg-opacity-10 text-secondary">N/A</span>
            @endif
        </td>

        {{-- Tanggal pembayaran terakhir --}}
        <td class="text-center">
            @if($invoice->pembayaran()->exists())
                <span class="badge bg-success">
                    {{ $invoice->pembayaran()->latest()->first()->created_at->format('d M Y H:i:s') }}
                </span>
            @else
                <span class="badge bg-secondary">-</span>
            @endif
        </td>

        {{-- Tombol aksi --}}
        <td class="text-center">
            <div class="d-flex justify-content-center gap-2">
                @if($invoice->status && $invoice->status->nama_status != 'Sudah Bayar' && (!$customer || !$customer->trashed()))
                    <button class="btn btn-outline-success btn-sm mb-1" data-bs-target="#konfirmasiPembayaran{{ $invoice->id }}"
                        data-bs-toggle="modal" title="Request Pembayaran">
                        <i class="bx bx-money"></i>
                    </button>
                @else
                    <span class="btn btn-outline-secondary btn-sm mb-1 disabled"
                        title="{{ $customer && $customer->trashed() ? 'Pelanggan sudah dihapus' : 'Sudah Dibayar' }}">
                        <i class="bx {{ $customer && $customer->trashed() ? 'bx-block' : 'bx-check' }}"></i>
                    </span>
                @endif

                <a href="/riwayatPembayaran/{{ $invoice->customer_id }}" class="btn btn-outline-info btn-sm mb-1"
                    title="Histori pembayaran {{ $customer->nama_customer ?? '' }}">
                    <i class="bx bx-history"></i>
                </a>
            </div>
        </td>
        <td>
            <span class="badge bg-label-{{ $invoice->customer->trashed() ? 'danger' : 'success' }}">
                {{ $invoice->customer->trashed() ? 'Deaktivasi' : 'Aktif' }}
            </span>
        </td>
        {{-- Keterangan --}}
        <td class="text-start">
            {{ $invoice->pembayaran->first()->keterangan ?? '-' }}
        </td>
    </tr>

    {{-- Modal Konfirmasi Pembayaran --}}
    @if(!$is_ajax)
        @include('agen.partials.payment-modal', ['invoice' => $invoice])
    @endif
@empty
    <tr>
        <td colspan="12" class="text-center py-5">
            <div class="d-flex flex-column align-items-center">
                <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                <p class="text-muted mb-0">Tidak ditemukan data yang sesuai dengan filter.</p>
            </div>
        </td>
    </tr>
@endforelse