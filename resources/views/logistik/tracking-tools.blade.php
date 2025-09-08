@extends('layouts.contentNavbarLayout')

@section('title', 'Tracking Tools')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Tracking Tools</h5>
                <small class="card-subtitle text-muted">Halaman Untuk Tracking Modem Atau Tenda yang sudah Terpakai</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark text-center">
                            <tr class="fw-bold">
                                <th>No</th>
                                <th>Nama Alat</th>
                                <th>Nama Pelanggan</th>
                                <th>Mac Address</th>
                                <th>Seri Perangkat</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $no = 1;
                            @endphp
                            @forelse ($data as $item)
                                <tr class="text-center">
                                    <td>{{$no++}}</td>
                                    <td>
                                        <span class="badge bg-label-warning">
                                            {{$item->perangkat->nama_perangkat ?? '-'}}
                                        </span>
                                    </td>
                                    <td>{{$item->customer->nama_customer ?? '-'}}</td>
                                    <td>
                                        <span class="badge bg-label-danger">
                                            {{ $item->mac_address ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-danger">
                                            {{ $item->serial_number ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->status_id == 13)
                                            <span class="badge bg-label-success">
                                                {{$item->status->nama_status ?? '-'}}
                                            </span>
                                        @elseif($item->status_id == 14)
                                            <span class="badge bg-label-warning">
                                                {{$item->status->nama_status ?? '-'}}
                                            </span>
                                        @else
                                            <span class="badge bg-label-danger">
                                                {{$item->status->nama_status ?? '-'}}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Data Tidak Ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection