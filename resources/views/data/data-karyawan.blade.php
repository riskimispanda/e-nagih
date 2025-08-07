@extends('layouts.contentNavbarLayout')
@section('title', 'Data Karyawan')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title fw-bold">Data Karyawan</h4>
                <small class="card-subtitle text-muted">Daftar Karyawan</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No.</th>
                                <th>Nama Karyawan</th>
                                <th>Nomor HP</th>
                                <th>Alamat</th>
                                <th>Bio</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($karyawan as $k)
                                <tr>
                                    <td class="fw-semibold">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-user text-primary me-2 fw-bold"></i>
                                            <div>
                                                <span class="fw-medium">{{ $k->name }}</span>
                                                <div class="text-muted small">Email: {{ $k->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-semibold">{{ $k->no_hp ?? '-' }}</td>
                                    <td class="fw-semibold">{{ ucwords($k->alamat ?? '-') }}</td>
                                    <td>
                                        <span class="badge bg-warning bg-opacity-10 text-warning fw-bold">
                                            {{ strtolower($k->bio ?? '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger fw-bold">
                                            {{ strtoupper($k->roles->name) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                        <span class="text-muted">Tidak ada data karyawan</span>
                                    </div>
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
@endsection