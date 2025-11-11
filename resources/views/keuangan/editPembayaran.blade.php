@extends('layouts.contentNavbarLayout')

@section('title', 'Request Edit Pembayaran')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Daftar Request Edit Pembayaran</h5>
                <small class="card-subtitle text-muted">Halaman Daftar Request Edit Pembayaran Dari Admin Keuangan</small>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Customer</th>
                                <th>Total Tagihan</th>
                                <th>Jumlah Bayar Lama</th>
                                <th>Jumlah Bayar Baru</th>
                                <th>Metode Bayar Lama</th>
                                <th>Metode Bayar Baru</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                                <th>Admin</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @php
                                $no = 1;
                            @endphp
                            @forelse ($pembayaran as $item)
                                <tr>
                                    <td>{{$no++}}</td>
                                    <td>{{$item->invoice->customer->nama_customer ?? '-'}}</td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            Rp {{ number_format($item->invoice->tagihan + $item->invoice->tunggakan + $item->invoice->tambahan - $item->invoice->saldo ?? 0, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning bg-opacity-10 text-warning">
                                            Rp {{ number_format($item->jumlah_bayar_baru ?? 0, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $item->metode_bayar }}
                                    </td>
                                    <td>
                                        {{ $item->metode_bayar_new ?? '-' }}
                                    </td>
                                    <td>
                                        {{ $item->ket_edit }}
                                    </td>
                                    <td>
                                        @if(auth()->user()->roles_id == 1)
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="/konfirmasiEditPembayaran/{{ $item->id }}" 
                                                class="btn btn-outline-success btn-sm btn-confirm-request" 
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" 
                                                title="Konfirmasi Request">
                                                 <i class="bx bx-check"></i>
                                             </a>
                                             <a href="/rejectEditPembayaran/{{ $item->id }}" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Reject Request">
                                                <i class="bx bx-x"></i>
                                             </a>
                                        </div>
                                        @else
                                        <div class="d-flex justify-content-center gap-2">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                Menunggu
                                            </span>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info fw-bold" style="text-transform: uppercase">
                                            {{ $item->admin->name ?? '-'}}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="fw-bold">Tidak Ada Request</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tangkap semua tombol konfirmasi
        document.querySelectorAll('.btn-confirm-request').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // cegah default link
                let href = this.getAttribute('href'); // ambil link action
    
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Request pembayaran akan dikonfirmasi!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, konfirmasi sekarang!',
                    cancelButtonText: 'Batal',
                    topLayer: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // jika dikonfirmasi, arahkan ke link
                        window.location.href = href;
                    }
                });
            });
        });
    });
</script>
@endsection