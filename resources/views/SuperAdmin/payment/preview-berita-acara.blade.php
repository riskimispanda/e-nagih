@extends('layouts.contentNavbarLayout')
@section('title', 'Halaman Preview Berita Acara')
<style>
    #print-area .card {
        box-shadow: none !important;
    }
    @media print {
        .card {
            box-shadow: none !important;
            border: 1px solid #000 !important; /* optional kalau mau ada garis */
        }
        .no-print {
            display: none !important; /* tombol tidak ikut tercetak */
        }
    }
</style>
@section('content')
<div id="print-area"> {{-- Hanya bagian ini yang akan diprint --}}
    <div class="row">
        <div class="col-md-12 mx-auto">
            <div class="card border-0 p-4">
                <div class="text-center mt-8">
                    <h4 class="fw-bold text-uppercase">Berita Acara Aktivasi Pelanggan Belum Bayar</h4>
                    <p class="mb-0 fw-bold" style="font-style: italic">No : BAAPBB / 0001 / 1108 / 2025</p>
                </div>
                <hr>
                <p class="mt-3">
                    Pada hari <strong>{{ \Carbon\Carbon::parse($data->tanggal_ba)->locale('id')->isoFormat('dddd') }}</strong> 
                    tanggal <strong>{{ \Carbon\Carbon::parse($data->tanggal_ba)->format('d') }}</strong> 
                    bulan <strong>{{ \Carbon\Carbon::parse($data->tanggal_ba)->locale('id')->isoFormat('MMMM') }}</strong> 
                    tahun <strong>{{ \Carbon\Carbon::parse($data->tanggal_ba)->format('Y') }}</strong> 
                    kami mengajukan permohonan aktivasi belum bayar pelanggan dengan data sebagai berikut:
                </p>
                <hr>
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td width="30%"><strong>Nama</strong></td>
                            <td>: {{$data->customer->nama_customer}}</td>
                        </tr>
                        <tr>
                            <td><strong>Alamat</strong></td>
                            <td>: {{$data->customer->alamat}}</td>
                        </tr>
                        <tr>
                            <td><strong>Layanan</strong></td>
                            <td>: {{$data->customer->paket->nama_paket}}</td>
                        </tr>
                        <tr>
                            <td><strong>Nominal Tagihan</strong></td>
                            <td>: Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>User Dial</strong></td>
                            <td>: {{ $data->customer->usersecret }}</td>
                        </tr>
                        <tr>
                            <td><strong>Alasan Aktivasi</strong></td>
                            <td>: {{$data->keterangan}}</td>
                        </tr>
                        <tr>
                            <td><strong>Lama Aktivasi</strong></td>
                            <td>: Hingga Tanggal <strong>{{ \Carbon\Carbon::parse($data->tanggal_selesai_ba)->translatedFormat('d F Y') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
                <hr>
                <p class="mt-3">
                    Selanjutnya mohon tim terkait dapat melakukan proses aktivasi belum bayar pelanggan sesuai data di atas 
                    dan sesuai prosedur yang sudah ditetapkan sesuai lama waktu aktivasi atau hingga pembayaran dilakukan.
                </p>
                <br><br>
                <div class="row text-center mt-4">
                    <div class="col">
                        <p><strong>Pemohon ({{ $data->admin->roles->name }})</strong></p><br><br>
                        <p class="fw-bold text-uppercase">{{$data->admin->name}}</p>
                    </div>
                    <div class="col">
                        <p><strong>Pelaksana (NOC)</strong></p><br><br>
                        <p class="fw-bold">{{$data->noc->name ?? 'Menunggu'}}</p>
                    </div>
                </div><br>
                <div class="d-flex justify-content-center">
                    <div class="align-items-center">
                        <p><strong>Mengetahui (Super Admin)</strong></p><br><br>
                        <p class="fw-bold text-uppercase text-center">Eko Rahmadi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> {{-- end #print-area --}}

{{-- Tombol tidak ikut tercetak --}}
<div class="d-flex justify-content-end gap-3 mt-4 no-print">
    <a href="javascript:window.history.back()" class="btn btn-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
    <button type="button" onclick="printDiv('print-area')" class="btn btn-info btn-sm">
        <i class="bx bx-printer me-1"></i> Print PDF
    </button>
    <a href="/approve-berita-acara/{{ $data->customer_id }}" class="btn btn-success btn-sm">
        <i class="bx bx-check-circle me-1"></i> ACC
    </a>
</div>
<script>
    function printDiv(divId) {
        let content = document.getElementById(divId).innerHTML;
        let original = document.body.innerHTML;
    
        document.body.innerHTML = content;
        window.print();
        document.body.innerHTML = original;
        location.reload(); // reload biar tombol balik lagi
    }
</script>
@endsection