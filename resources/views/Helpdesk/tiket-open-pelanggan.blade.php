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
                                <th>Status</th>
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
                                    @if ($item->status->nama_status == 'Maintenance')
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        Maintenance
                                    </span>
                                    @else
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        Aktif
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="d-flex justify-content-center gap-2">
                                            @if($item->status->nama_status != 'Maintenance')
                                            <a href="/open-tiket/{{ $item->id }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Open Tiket">
                                                <i class="bx bx-lock-open-alt text-danger"></i>
                                            </a>
                                            @else
                                            <a disabled data-bs-toggle="tooltip" data-bs-placement="bottom" title="Proses">
                                                <i class="bx bx-lock text-success"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center fw-bold">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex mt-3">
                    <div class="d-flex justify-content-center gap-2">
                        {{ $customer->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection