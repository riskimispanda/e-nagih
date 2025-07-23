@extends('layouts.contentNavbarLayout')

@section('title', 'Tiket Open')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title fw-bold">Tiket Open</h4>
                <small class="card-subtitle">Daftar tiket yang sedang terbuka</small>
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
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($customer as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_customer }}</td>
                                <td>{{ $item->alamat }}</td>
                                <td>{{ $item->no_hp }}</td>
                                <td>
                                    @php
                                        $gps = $item->gps;
                                        $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                        $url = $isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps);
                                    @endphp
                                    <div class="row">
                                        <a href="{{ $url }}" target="_blank" class="btn btn-action btn-maps" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat di Google Maps">
                                            <i class="bx bx-map"></i>
                                        </a></a>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="/open-tiket/{{ $item->id }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Open Tiket">
                                                <i class="bx bx-lock-open-alt text-danger"></i>
                                            </a>
                                        </div>
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