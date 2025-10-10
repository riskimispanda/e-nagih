@extends('layouts.contentNavbarLayout')

@section('title', 'Data Pelanggan Agen')


@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Daftar Pelanggan dibawah Agen {{auth()->user()->name}}</h5>
                <small class="card-subtitle">Daftar seluruh pelanggan yang terdaftar dibawah agen {{auth()->user()->name}}</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-center table-dark table-hover">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>No. HP</th>
                                <th>Alamat</th>
                                <th>Paket</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @php
                                $no = 1;
                            @endphp
                            @forelse ($pelanggan as $item)
                            <tr>
                                <td>{{$no++}}</td>
                                <td>{{$item->nama_customer}}</td>
                                <td>{{$item->no_hp}}</td>
                                <td>{{$item->alamat}}</td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        {{$item->paket->nama_paket}}
                                    </span>
                                </td>
                                <td>
                                    @if($item->status_id == 3)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        Aktif
                                    </span>
                                    @elseif($item->status_id == 1)
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        Menunggu
                                    </span>
                                    @elseif($item->status_id == 9)
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        Blokir
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                            <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                                            <p class="text-muted mb-0">Belum ada Data Pelanggan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-5">
                    {{ $pelanggan->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection