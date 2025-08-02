@extends('layouts.contentNavbarLayout')

@section('title', 'Tiket Closed')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title fw-bold">Tiket Closed</h4>
                <small class="card-subtitle">Daftar tiket yang belum ditutup</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th scope="col">No</th>
                                <th>Nama Pelanggan</th>
                                <th>Alamat</th>
                                <th>No HP</th>
                                <th>Lokasi</th>
                                <th>Keterangan</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($customer as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->customer->nama_customer }}</td>
                                <td>{{ $item->customer->alamat }}</td>
                                <td>{{ $item->customer->no_hp }}</td>
                                <td>
                                    @php
                                    $gps = $item->customer->gps;
                                    $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                    $url = $isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps);
                                    @endphp
                                    <div class="row">
                                        <a href="{{ $url }}" target="_blank" class="btn btn-action btn-maps" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat di Google Maps">
                                            <i class="bx bx-map"></i>
                                        </a></a>
                                    </div>
                                </td>
                                <td>{{ $item->keterangan }}</td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        {{ $item->kategori->nama_kategori }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <a href="/tiket-open/{{ $item->id }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Tutup Tiket">
                                            <i class="bx bx-user-check text-warning"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center fw-bold">Tidak ada data</td>
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