@extends('layouts.contentNavbarLayout')

@section('title', 'Kinerja Teknisi')


@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-5">
            <div class="card-header">
                <h4 class="card-title fw-bold">Data Kinerja Teknisi</h4>
                <small class="card-subtitle">Data Performa kinerja setiap masing-masing teknisi</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr class="text-center fw-bold">
                                <th rowspan="2" class="align-middle">No</th>
                                <th rowspan="2" class="align-middle">Nama Teknisi</th>
                                <th colspan="2" data-bs-toggle="tooltip" data-bs-placement="top" title="Total pekerjaan yang diselesaikan bulan ini">Kinerja Bulan Ini</th>
                                <th colspan="2" data-bs-toggle="tooltip" data-bs-placement="top" title="Akumulasi total pekerjaan dari awal tahun hingga sekarang">Akumulasi Tahun Ini</th>
                            </tr>
                            <tr class="text-center fw-bold">
                                <th data-bs-toggle="tooltip" data-bs-placement="top" title="Jumlah instalasi pelanggan baru"><i class='bx bx-user-plus bx-xs me-1'></i>Instalasi</th>
                                <th data-bs-toggle="tooltip" data-bs-placement="top" title="Jumlah tiket gangguan yang ditutup"><i class='bx bxs-check-square bx-xs me-1'></i>Closing Tiket</th>
                                <th data-bs-toggle="tooltip" data-bs-placement="top" title="Total instalasi tahun ini"><i class='bx bx-user-plus bx-xs me-1'></i>Instalasi</th>
                                <th data-bs-toggle="tooltip" data-bs-placement="top" title="Total closing tiket tahun ini"><i class='bx bxs-check-square bx-xs me-1'></i>Closing Tiket</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @php
                                $no = 1;
                            @endphp
                            @forelse ($teknisi as $item)
                                <tr class="text-center">
                                    <td>{{$no++}}</td>
                                    <td class="text-start">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="avatar-wrapper">
                                                <div class="avatar avatar-sm me-3">
                                                    @if($item->photo)
                                                        <img src="{{ asset('storage/' . $item->photo) }}" alt="Avatar" class="rounded-circle">
                                                    @else
                                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($item->name, 0, 2) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <a href="#" class="text-body text-truncate">
                                                    <span class="fw-bold">{{$item->name}}</span>
                                                </a>
                                                <small class="text-muted">{{$item->email}}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-success fs-6">
                                            {{$item->customer_count ?? 0}}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info fs-6">
                                            {{$item->tiket_count ?? 0}}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-success fs-6">
                                            {{$item->customer_count_year ?? 0}}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info fs-6">
                                            {{$item->tiket_count_year ?? 0}}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <h5 class="text-muted">Data Kinerja Teknisi Tidak Ditemukan.</h5>
                                    </td>
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
    // Inisialisasi Tooltip
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection