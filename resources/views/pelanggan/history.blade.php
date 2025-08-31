@extends('layouts.contentNavbarLayout')
@section('title', 'Histori Pelanggan')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Histori Pelanggan</h5>
                <small class="card-subtitle">Riwayat Pembayaran Pelanggan</small>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-start mb-5">
                    <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
                        <i class="bx bx-chevron-left"></i> Kembali
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>Paket</th>
                                <th>Tanggal Bayar</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th>Jumlah Bayar</th>
                                <th>Metode Bayar</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = ($invoice->currentPage()-1)*10 + 1; @endphp
                            @forelse ($invoice as $item)
                                @php $lastPayment = $item->pembayaran->last(); @endphp
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $item->customer->nama_customer }}</td>
                                    <td><span class="badge bg-info bg-opacity-10 text-info">{{ $item->customer->paket->nama_paket }}</span></td>
                                    <td>
                                        <span class="badge bg-warning bg-opacity-10 text-warning">
                                            {{ $lastPayment ? \Carbon\Carbon::parse($lastPayment->created_at)->translatedFormat('d-M-Y H:i:s') : '-' }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($item->jatuh_tempo)->locale('id')->translatedFormat('F Y') }}</td>
                                    <td>
                                        @if($item->status_id == 7)
                                            <span class="badge bg-danger">Belum Bayar</span>
                                        @elseif($item->status_id == 8)
                                            <span class="badge bg-success">Sudah Bayar</span>
                                        @else
                                            <span class="badge bg-secondary">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $lastPayment ? 'Rp. '.number_format($lastPayment->jumlah_bayar,0,',','.') : '-' }}
                                    </td>
                                    <td>
                                        {{ $lastPayment ? $lastPayment->metode_bayar : '-' }}
                                    </td>
                                    <td>
                                        {{ $lastPayment->keterangan ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">Tidak Ada Data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-start mt-3 align-items-center gap-2">
                    <span>Halaman:</span>
                    <select id="paginationDropdown" class="form-select form-select-sm" style="width: auto;">
                        @for ($i = 1; $i <= $invoice->lastPage(); $i++)
                            <option value="{{ $i }}" {{ $i == $invoice->currentPage() ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                    <span>dari {{ $invoice->lastPage() }} halaman</span>
                </div>

                {{-- Pagination Links --}}
                <div class="mt-2">
                    {{ $invoice->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('paginationDropdown').addEventListener('change', function () {
        let page = this.value;
        let url = new URL(window.location.href);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
    });
</script>
@endsection
