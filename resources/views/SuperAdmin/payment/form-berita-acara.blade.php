@extends('layouts.contentNavbarLayout')

@section('title', 'Form Berita Acara')
<style>
    label{
        font-weight: bold;
    }
</style>
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title fw-bold">Form Pembuatan Berita Acara</h4>
                <small class="card-subtitle">Halaman untuk membuat berita acara</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold">Detail Form Pembuatan Berita Acara</h6>
                <div class="d-flex justify-content-start">
                    <a href="javascript:window.history.back()" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <hr>
                <form action="/berita-acara-store/{{ $data->id }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Nama Pelanggan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" name="nama" class="form-control" value="{{ $data->nama_customer }}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Alamat Pelanggan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-map"></i></span>
                                <input type="text" name="alamat" class="form-control" value="{{ $data->alamat }}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Paket Langganan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-package"></i></span>
                                <input type="text" name="paket" class="form-control" value="{{ $data->paket->nama_paket }}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Status Langganan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-package"></i></span>
                                <input type="text" name="status" class="form-control" value="{{ $data->status->nama_status }}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-12 mb-3">
                            <label class="form-label">*Keterangan Berita Acara</label>
                            <div class="input-group">
                                <textarea name="keterangan" class="form-control" cols="30" rows="10" required></textarea>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label class="form-label">*Tanggal Terbit Berita Acara</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label class="form-label">*Tanggal Selesai Berita Acara</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <input type="date" name="tanggal_selesai" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label class="form-label">*Kategori Berita Acara</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-package"></i></span>
                                <select name="kategori" id="kate" class="form-select">
                                    <option value="" selected disabled>Pilih Kategori</option>
                                    @foreach ($kategori as $item)
                                        <option value="{{ $item->id }}">{{$item->nama_kategori}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-5">
                            <button class="btn btn-outline-warning btn-sm">
                                <i class="bx bx-save me-1"></i>Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- JavaScript --}}
<script>
    new TomSelect('#kate',{
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
</script>
@endsection