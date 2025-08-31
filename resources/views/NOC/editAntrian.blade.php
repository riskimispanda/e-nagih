@extends('layouts.contentNavbarLayout')
@section('title','Edit Detail Antrian')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Edit Detail Antrian {{$antrian->nama_customer}}</h5>
                <small class="card-subtitle text-muted">Halaman Edit Detail Antrian</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="/simpan/noc/{{ $antrian->id }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Nama Customer</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" class="form-control" value="{{ $antrian->nama_customer }}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">No HP</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input type="text" class="form-control" value="{{ $antrian->no_hp }}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Usersecret</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-lock"></i></span>
                                <input type="text" name="usersecret" class="form-control" value="{{ $antrian->usersecret }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Password Secret</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input type="text" name="pass" class="form-control" value="{{ $antrian->pass_secret }}">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Remote IP Management</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input type="text" name="remote" class="form-control" value="{{ $antrian->remote ?? ''}}">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Remote Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input type="text" class="form-control" value="{{ $antrian->remote_address ?? ''}}">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Local Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input type="text" name="local_address" class="form-control" value="{{ $antrian->local_address ?? ''}}">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
                            <i class="bx bx-reply me-1"></i>
                            Kembali
                        </a>
                        <button class="btn btn-warning btn-sm" type="submit">
                            <i class="bx bx-save me-1"></i>
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection