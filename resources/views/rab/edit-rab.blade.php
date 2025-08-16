@extends('layouts.contentNavbarLayout')

@section('title', 'Edit RAB')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Edit RAB {{$rab->kegiatan}}</h5>
                <small class="card-subtitle">Kelola Detail RAB {{$rab->kegiatan}}</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="/update-rab/{{ $rab->id }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Nama Kegiatan</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-tab"></i></span>
                                <input type="text" name="kegiatan" class="form-control" value="{{ $rab->kegiatan }}">
                            </div>
                        </div>
                        <div class="col-sm-3 mb-2">
                            <label class="form-label">Tahun Anggaran</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <select name="tahun_anggaran" class="form-select">
                                    @for ($i = date('Y'); $i <= date('Y') + 5; $i++)
                                    <option value="{{ $i }}" {{ $rab->tahun_anggaran == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                    @endfor
                                </select>                            
                            </div>
                        </div>
                        <div class="col-sm-3 mb-2">
                            <label class="form-label">Bulan Anggaran</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <select class="form-select" name="bulan">
                                    @foreach ([
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                    ] as $key => $value)
                                    <option value="{{ $key }}" {{ $rab->bulan == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                    @endforeach
                                </select>                        
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Jumlah Anggaran</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-money"></i></span>
                                <input type="text" class="form-control" id="jumlah_anggaran" value="{{ number_format($rab->jumlah_anggaran, 0, ',', '.') }}">
                            </div>
                            <input type="text" id="rawJumlah_anggaran" name="jumlah_anggaran" hidden value="{{ $rab->jumlah_anggaran }}">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Jumlah Item</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-package"></i></span>
                                <input type="text" name="item" class="form-control" id="jumlah_anggaran" value="{{ $rab->item }}">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-12">
                            <label class="form-label">Keterangan</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-message"></i></span>
                                <textarea name="keterangan" class="form-control" cols="10" rows="5">{{ $rab->keterangan }}</textarea>
                            </div>
                        </div>                        
                    </div>
                    <div class="row">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/rab" class="btn btn-secondary btn-sm">
                                <i class='bx  bx-chevrons-left'  ></i> 
                                Kembali
                            </a>
                            <button type="button" id="btnUpdate" class="btn btn-warning btn-sm"><i class="bx bx-file me-1"></i>Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Js --}}
<script>
    document.getElementById('jumlah_anggaran').addEventListener('keyup', function(e) {
        let value = this.value.replace(/\D/g, ''); // hapus semua non-angka
        value = new Intl.NumberFormat('id-ID').format(value); // format ke rupiah
        this.value = value;
    });
</script>
<script>
    document.getElementById('jumlah_anggaran').addEventListener('keyup', function () {
        // Ambil angka murni tanpa titik atau koma
        let raw = this.value.replace(/\D/g, '');
        
        // Update hidden input dengan angka murni
        document.getElementById('rawJumlah_anggaran').value = raw;
        
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
            text: "Data RAB akan diperbarui!",
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