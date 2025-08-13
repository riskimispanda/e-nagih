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
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>Paket</th>
                                <th>Tanggal Bayar</th>
                                <th>Periode</th>
                                <th>Jumlah Bayar</th>
                                <th>Metode Bayar</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @php
                                $no = 1;
                            @endphp
                            @forelse ($invoice as $item)
                            <tr>
                                <td>{{$no++}}</td>
                                <td>
                                    {{ $item->customer->nama_customer }}
                                </td>
                                <td>
                                    <span class="badge bg-info">{{$item->customer->paket->nama_paket}}</span>
                                </td>
                                <td>
                                    @if($item->pembayaran->isNotEmpty())
                                        @foreach ($item->pembayaran as $cek)
                                            <span>{{ \Carbon\Carbon::parse($cek->tanggal_bayar)->locale('id')->translatedFormat('D F Y h:m:s') }}</span>
                                        @endforeach
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->jatuh_tempo)->locale('id')->translatedFormat('F') }}
                                </td>
                                <td>
                                    @if($item->pembayaran->isNotEmpty())
                                        @foreach ($item->pembayaran as $cek)
                                        <span class="badge bg-success">Rp. {{ number_format($cek->jumlah_bayar, 0, ',', '.') }}</span>
                                        @endforeach
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    @if($item->pembayaran->isNotEmpty())
                                        @foreach ($item->pembayaran as $cek)
                                        <span class="badge bg-warning">{{$cek->metode_bayar}}</span>
                                        @endforeach
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    @if($item->pembayaran->isNotEmpty())
                                        @foreach ($item->pembayaran as $cek)
                                        <span>{{$cek->keterangan}}</span>
                                        @endforeach
                                    @else
                                    -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">Tidak Ada Data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
