@extends('layouts.contentNavbarLayout')
@section('title', 'Edit Logistik')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Edit Logistik</h5>
                <small class="card-subtitle">Kelola Detail Logistik</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="/update-logistik/{{ $log->id }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Nama Perangkat</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-chip"></i></span>
                                <input type="text" class="form-control" name="nama_perangkat" value="{{ $log->nama_perangkat }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Jumlah Stok</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-math"></i></span>
                                <input type="number" class="form-control" name="stok" value="{{ $log->jumlah_stok }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Kategori</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-package"></i></span>
                                <select class="form-select" name="kategori">
                                    <option value="">-</option>
                                    @foreach ($data as $k)
                                    <option value="{{ $k->id }}" {{ $log->kategori_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_logistik }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Harga</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-money"></i></span>
                                <input type="text" id="harga" class="form-control" value="{{ number_format($log->harga, 0, ',', '.') }}">
                            </div>
                            <input type="text" name="harga" id="rawHarga" hidden value="{{ $log->harga }}">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="/data/logistik">
                                <button type="button" class="btn btn-secondary btn-sm"><i class="bx bx-chevrons-left"></i>Kembali</button>
                            </a>
                            <button type="button" id="btnUpdate" class="btn btn-warning btn-sm"><i class="bx bx-file"></i>Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JS --}}
<script>
    document.getElementById('harga').addEventListener('keyup', function(e) {
        let value = this.value.replace(/\D/g, ''); // hapus semua non-angka
        value = new Intl.NumberFormat('id-ID').format(value); // format ke rupiah
        this.value = value;
    });
</script>
<script>
    document.getElementById('harga').addEventListener('keyup', function () {
        // Ambil angka murni tanpa titik atau koma
        let raw = this.value.replace(/\D/g, '');
        
        // Update hidden input dengan angka murni
        document.getElementById('rawHarga').value = raw;
        
        // Format kembali tampilan input jumlah_anggaran
        if (raw) {
            this.value = new Intl.NumberFormat('id-ID').format(raw);
        } else {
            this.value = '';
        }
    });
</script>
<script>
    document.getElementById('btnUpdate').addEventListener('click', function () {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data Perangkat akan diperbarui!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal',
            topLayer: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector('form').submit();
            }
        });
    });
</script>

@endsection