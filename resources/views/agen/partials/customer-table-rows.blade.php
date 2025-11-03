@php $rowNumber = ($invoices->currentPage() - 1) * $invoices->perPage() + 1; @endphp
@forelse ($invoices as $invoice)
    @php
        $customer = $invoice->customer;
    @endphp
    <tr class="customer-row text-center {{ $customer && $customer->trashed() ? 'customer-deleted' : '' }}"
        data-id="{{ $customer->id ?? '-' }}"
        data-tagihan="{{ $invoice->status ? ($invoice->status->nama_status == 'Sudah Bayar' ? '0' : ($invoice->tagihan ?? '0')) : '0' }}"
        data-customer-id="{{ $customer->id ?? '-' }}"
        data-invoice-id="{{ $invoice->id }}"
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
            Rp {{ number_format(($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan - ($invoice->saldo ?? 0)), 0, ',', '.') }}
        </td>

        {{-- Status pembayaran --}}
        <td class="text-center">
            @if ($invoice->status)
                <span class="badge
                    bg-{{ $invoice->status->nama_status == 'Sudah Bayar' ? 'success' : 'danger' }}
                    bg-opacity-10
                    text-{{ $invoice->status->nama_status == 'Sudah Bayar' ? 'success' : 'danger' }}">
                    {{ $invoice->status->nama_status }}
                </span>
            @else
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
                <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-info' }} bg-opacity-10 {{ $isOverdue ? 'text-danger' : 'text-info' }}">
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
                    <button class="btn btn-outline-success btn-sm mb-1"
                            data-bs-target="#konfirmasiPembayaran{{ $invoice->id }}"
                            data-bs-toggle="modal"
                            title="Request Pembayaran">
                        <i class="bx bx-money"></i>
                    </button>
                @else
                    <span class="btn btn-outline-secondary btn-sm mb-1 disabled"
                        title="{{ $customer && $customer->trashed() ? 'Pelanggan sudah dihapus' : 'Sudah Dibayar' }}">
                        <i class="bx {{ $customer && $customer->trashed() ? 'bx-block' : 'bx-check' }}"></i>
                    </span>
                @endif

                <a href="/riwayatPembayaran/{{ $invoice->customer_id }}"
                    class="btn btn-outline-info btn-sm mb-1"
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
    @if($invoice->status && $invoice->status->nama_status != 'Sudah Bayar' && $customer && !$customer->trashed())
    <div class="modal fade" id="konfirmasiPembayaran{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-header border-bottom">
                    <h5 class="modal-title mb-6" id="modalCenterTitle">
                        <i class="bx bx-wallet me-2 text-success"></i>
                        Konfirmasi Pembayaran
                        <span class="text-dark fw-bold">{{ $customer->nama_customer }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Form -->
                <form action="/request/pembayaran/agen/{{ $invoice->id }}" method="POST" enctype="multipart/form-data" id="paymentForm{{ $invoice->id }}">
                    @csrf
                    <div class="modal-body">

                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                        <!-- Informasi Pelanggan -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="payment-info-card">
                                    <div class="d-flex justify-start align-items-center">
                                        <i class="bx bx-user-circle me-2 text-primary fs-4"></i>
                                        <div>
                                            <strong class="text-dark">{{ $customer->nama_customer }}</strong><br>
                                            <small class="text-muted">{{ $customer->alamat }} | {{ $customer->no_hp }}</small><br>
                                            <small class="text-muted">Paket:
                                                <span class="text-primary fw-bold">{{ $customer->paket->nama_paket ?? '-' }}</span>
                                            </small><br>
                                            <small class="text-muted">Harga Paket:
                                                <span class="text-primary fw-bold">
                                                    Rp {{ number_format($customer->paket->harga ?? 0, 0, ',', '.') }}
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Jatuh Tempo -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="jatuhTempo" class="form-label">Tanggal Jatuh Tempo</label>
                                <input type="date" class="form-control"
                                    value="{{ $invoice->jatuh_tempo ? \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('Y-m-d') : '' }}"
                                    readonly>
                            </div>
                        </div>

                        <!-- Rincian Tagihan -->
                        <div class="row">
                            <div class="col mb-4 col-lg-4">
                                <label class="form-label mb-2">Tagihan</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input pilihan"
                                        name="bayar[]" value="tagihan"
                                        data-amount="{{ $invoice->tagihan ?? 0 }}"
                                        data-id="{{ $invoice->id }}">
                                    <label class="form-check-label">
                                        Rp {{ number_format($invoice->tagihan ?? 0, 0, ',', '.') }}
                                    </label>
                                </div>
                            </div>

                            <div class="col mb-4 col-lg-4">
                                <label class="form-label mb-2">Biaya Tambahan</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input pilihan"
                                        name="bayar[]" value="tambahan"
                                        data-amount="{{ $invoice->tambahan ?? 0 }}"
                                        data-id="{{ $invoice->id }}">
                                    <label class="form-check-label">
                                        Rp {{ number_format($invoice->tambahan ?? 0, 0, ',', '.') }}
                                    </label>
                                </div>
                            </div>

                            <div class="col mb-4 col-lg-4">
                                <label class="form-label mb-2">Tunggakan</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input pilihan"
                                        name="bayar[]" value="tunggakan"
                                        data-amount="{{ $invoice->tunggakan ?? 0 }}"
                                        data-id="{{ $invoice->id }}">
                                    <label class="form-check-label">
                                        Rp {{ number_format($invoice->tunggakan ?? 0, 0, ',', '.') }}
                                    </label>
                                </div>
                            </div>

                            <div class="col mb-4 col-lg-4">
                                <label class="form-label mb-2">Sisa Saldo</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input pilihan"
                                        name="saldo"
                                        value="{{ $invoice->saldo ?? 0 }}"
                                        data-amount="{{ $invoice->saldo ?? 0 }}"
                                        data-id="{{ $invoice->id }}"
                                        data-type="saldo">
                                    <label class="form-check-label">
                                        Rp {{ number_format($invoice->saldo ?? 0, 0, ',', '.') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="row">
                            <div class="col-12 mb-4 col-lg-12">
                                <label class="form-label">Total</label>
                                <input type="text" id="total{{ $invoice->id }}"
                                    class="form-control" name="total" value="Rp 0" readonly>
                            </div>
                        </div>

                        <!-- Input Jumlah Bayar -->
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Jumlah Bayar <span class="text-danger">*</span></label>
                                <input type="text" class="form-control"
                                    id="revenueAmount{{ $invoice->id }}"
                                    name="revenueAmount"
                                    oninput="formatRupiah(this, {{ $invoice->id }})"
                                    placeholder="Masukkan jumlah bayar" required>
                                <input type="hidden" id="raw{{ $invoice->id }}"
                                    name="jumlah_bayar" value="0">
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select name="metode_id" class="form-select" required>
                                    <option value="" selected disabled>Pilih Metode Pembayaran</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Transfer Bank">Transfer</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bukti Pembayaran -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Bukti Pembayaran</label>
                                <input type="file" class="form-control" name="bukti_pembayaran" accept="image/*">
                                <small class="text-muted">Upload foto bukti pembayaran (opsional)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-success btn-sm" id="submitBtn{{ $invoice->id }}">
                            <i class="bx bx-send me-1"></i>Kirim Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
