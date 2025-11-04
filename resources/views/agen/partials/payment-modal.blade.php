@foreach($invoices as $invoice)
    @if($invoice->status && $invoice->status->nama_status != 'Sudah Bayar' && $invoice->customer && !$invoice->customer->trashed())
    <div class="modal fade" id="konfirmasiPembayaran{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-header border-bottom">
                    <h5 class="modal-title mb-6" id="modalCenterTitle">
                        <i class="bx bx-wallet me-2 text-success"></i>
                        Konfirmasi Pembayaran
                        <span class="text-dark fw-bold">{{ $invoice->customer->nama_customer }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Form -->
                <form action="{{ route('request-pembayaran-agen', $invoice->id) }}" method="POST" enctype="multipart/form-data" id="paymentForm{{ $invoice->id }}">
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
                                            <strong class="text-dark">{{ $invoice->customer->nama_customer }}</strong><br>
                                            <small class="text-muted">{{ $invoice->customer->alamat }} | {{ $invoice->customer->no_hp }}</small><br>
                                            <small class="text-muted">Paket:
                                                <span class="text-primary fw-bold">{{ $invoice->customer->paket->nama_paket ?? '-' }}</span>
                                            </small><br>
                                            <small class="text-muted">Harga Paket:
                                                <span class="text-primary fw-bold">
                                                    Rp {{ number_format($invoice->customer->paket->harga ?? 0, 0, ',', '.') }}
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
@endforeach