@extends('layouts.contentNavbarLayout')

@section('title','Tiket Barang Keluar')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title fw-bold">Tiket Barang Keluar</h4>
                <small class="card-subtitle">Halaman Tiket Barang Keluar</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="#" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Nama Barang</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-package"></i></span>
                                <input type="text" class="form-control" placeholder="SFP 10G AB-CD">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Kategori Barang</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-cart"></i></span>
                                <select name="" class="form-select" id="aset">
                                    <option value="" selected disabled>Pilih Kategori</option>
                                    @foreach ($kategori as $item)
                                        <option value="{{ $item->id }}">{{$item->nama_logistik}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Perangkat</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-devices"></i></span>
                                <select name="" class="form-select" id="perangkat">
                                    <option value="" selected disabled>Pilih Perangkat</option>
                                    @foreach ($perangkat as $item)
                                        <option value="{{ $item->id }}">{{$item->nama_perangkat}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Lokasi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-map"></i></span>
                                <input type="text" class="form-control" placeholder="-827291793, 98716723">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-warning btn-sm"><i class="bx bx-save me-2"></i>Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript Code --}}
<script>
    new TomSelect("#aset", {
        create: false, // tidak bisa menambah opsi baru
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
    new TomSelect("#perangkat", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
</script>
@endsection