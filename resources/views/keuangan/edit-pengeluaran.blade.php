@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Pengeluaran')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Edit Pengeluaran {{$pengeluaran->jenis_pengeluaran}}</h5>
                <small class="card-subtitle">Kelola Detail Pengeluaran {{$pengeluaran->jenis_pengeluaran}}</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="/update-pengeluaran/{{ $pengeluaran->id }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Jenis Pengeluaran</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-chevrons-left"></i></span>
                                <input type="text" class="form-control" name="jenis_pengeluaran" value="{{ $pengeluaran->jenis_pengeluaran }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Jumlah Pengeluaran</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-money"></i></span>
                                <input type="text" class="form-control" id="jumlah_pengeluaran" value="{{ number_format($pengeluaran->jumlah_pengeluaran, 0, ',', '.') }}">
                            </div>
                            <input type="text" id="rawPengeluaran" name="jumlah_pengeluaran" hidden value="{{ $pengeluaran->jumlah_pengeluaran }}">
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Tanggal Pengeluaran</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <input type="date" class="form-control" name="tanggal" value="{{ $pengeluaran->tanggal_pengeluaran }}">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Jenis Kas</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-wallet"></i></span>
                                <select class="form-select" name="jenis_kas">
                                    @foreach ($kas as $k)
                                        <option value="{{ $k->id }}" 
                                            {{ $pengeluaran->kas?->jenis_kas == $k->id ? 'selected' : '' }}>
                                            {{ $k->jenis_kas }}
                                        </option>
                                    @endforeach
                                </select>                                
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">RAB</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                <select class="form-select" name="rab_id" id="rab_id">
                                    <option value="">-</option>
                                    @foreach ($data as $k) {{-- Pastikan $data berisi RAB yang sesuai dengan $pengeluaran->rab_id --}}
                                        @php
                                            $bulan = $k->bulan ? \Carbon\Carbon::create()->month((int) $k->bulan)->locale('id')->translatedFormat('F') : '';
                                        @endphp
                                        <option value="{{ $k->id }}" {{ $pengeluaran->rab_id == $k->id ? 'selected' : '' }}>
                                            {{ $k->kegiatan }} 
                                            {{ $k->tahun_anggaran ? "| Tahun: {$k->tahun_anggaran}" : '' }}
                                            {{ $bulan ? '| Bulan: ' . $bulan : '' }}
                                        </option>
                                    @endforeach
                                </select>                                                                                       
                            </div>
                        </div>
                        <div class="col-sm-12 mb-2">
                            <label class="form-label">Keterangan</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-message"></i></span>
                                <textarea name="keterangan" class="form-control" cols="30" rows="5">{{$pengeluaran->keterangan}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/pengeluaran/global" class="btn btn-secondary btn-sm"><i class="bx bx-chevrons-left"></i>Kembali</a>
                            <button type="button" class="btn btn-warning btn-sm" id="btnUpdate"><i class="bx bx-file"></i>Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Js --}}
<script>
    document.getElementById('jumlah_pengeluaran').addEventListener('keyup', function(e) {
        let value = this.value.replace(/\D/g, ''); // hapus semua non-angka
        value = new Intl.NumberFormat('id-ID').format(value); // format ke rupiah
        this.value = value;
    });
</script>
<script>
    document.getElementById('jumlah_pengeluaran').addEventListener('keyup', function () {
        // Ambil angka murni tanpa titik atau koma
        let raw = this.value.replace(/\D/g, '');
        
        // Update hidden input dengan angka murni
        document.getElementById('rawPengeluaran').value = raw;
        
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
            text: "Data Pengeluaran akan diperbarui!",
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
    new TomSelect('#rab_id',{
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
</script>
@endsection