@extends('layouts.contentNavbarLayout')

@section('title', 'Detail Pelanggan')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Detail Pelanggan</h5>
                <small class="card-subtitle text-muted">Informasi lengkap pelanggan</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-dark table-hover">
                            <tr>
                                <th>Nama Pelanggan</th>
                                <th>Alamat</th>
                                <th>No. HP</th>
                                <th>Router</th>
                                <th>Paket</th>
                                <th>Usersecret</th>
                                <th>Password Secret</th>
                                <th>Remote Address</th>
                                <th>Ip Address</th>
                                <th>Local Address</th>
                                <th>Tanggal Registrasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <tr>
                                    <td>{{ $data->nama_customer}}</td>
                                    <td>{{ $data->alamat_customer }}</td>
                                </tr>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection