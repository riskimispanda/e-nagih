@extends('layouts.contentNavbarLayout')

@section('title', 'Tutup Tiket Open')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-2">
            <div class="card-header">
                <h4 class="card-title fw-bold" style="text-transform: uppercase">Konfirmasi Tiket Open</h4>
                <small class="card-subtitle">Form Untuk Konfirmasi Tiket Open</small>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-10">
                    <h5 class="mb-0 fw-semibold">Detail Tiket {{$kategori->kategori->nama_kategori}}</h5>
                    <small class="float-end badge bg-warning bg-opacity-10 text-warning fw-bold">Kategori Tiket: {{$kategori->kategori->nama_kategori}} by {{$kategori->user->name}}</small>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-sm-6 mb-6">
                            <label class="form-label" for="basic-icon-default-fullname">Nama Pelanggan</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" class="form-control" value="{{$tiket->customer->nama_customer}}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-6">
                            <label class="form-label" for="basic-icon-default-company">Alamat</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-map"></i></span>
                                <input type="text" class="form-control" value="{{$tiket->customer->alamat}}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-6">
                            <label class="form-label" for="basic-icon-default-fullname">No Telepon</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input type="text" class="form-control" value="{{$tiket->customer->no_hp}}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-6">
                            <label class="form-label" for="basic-icon-default-company">Google Maps</label>
                            <div class="input-group input-group-merge">
                                <a href="{{$tiket->customer->gps}}" class="btn btn-secondary btn-sm" target="_blank" class="form-control" readonly data-bs-toggle="tooltip" title="Lihat di Google Maps" data-bs-placement="bottom">
                                    <i class="bx bx-pin me-2"></i>Lihat di Google Maps 
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="form-label" for="basic-icon-default-message">Keterangan</label>
                        <div class="input-group input-group-merge">
                            <textarea id="basic-icon-default-message" class="form-control" placeholder="Hi, Do you have a moment to talk Joe?" aria-label="Hi, Do you have a moment to talk Joe?" aria-describedby="basic-icon-default-message2" readonly>{{$tiket->keterangan}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row mb-3">
                        <div class="col-sm-4 mb-2">
                            <label class="form-label" for="basic-icon-default-fullname">BTS Server</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="bx bx-terminal"></i></span>
                                <input type="text" class="form-control" value="{{$tiket->customer->odp->odc->olt->server->lokasi_server}}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label" for="basic-icon-default-fullname">Router</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="bx bx-server"></i></span>
                                <input type="text" class="form-control" value="{{$tiket->customer->router->nama_router}}" readonly id="routerOld">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Paket Langganan</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-package"></i></span>
                                <input type="text" class="form-control" value="{{$tiket->customer->paket->nama_paket}}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">OLT</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-sitemap"></i></span>
                                <input type="text" class="form-control" value="{{$tiket->customer->odp->odc->olt->nama_lokasi}}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">ODC</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-network-chart"></i></span>
                                <input type="text" class="form-control" value="{{$tiket->customer->odp->odc->nama_odc}}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">ODP</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-network-chart"></i></span>
                                <input type="text" class="form-control" value="{{$tiket->customer->odp->nama_odp}}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Tanggal Open Tiket</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>    
                                <input type="text" class="form-control" value="{{$tiket->created_at->format('d F Y, H:i')}}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Teknisi Sebelumnya</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-hard-hat"></i></span>    
                                <input type="text" class="form-control" value="{{$tiket->customer->teknisi->name}}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Usersecret</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-lock"></i></span>    
                                <input type="text" name="oldUsersecret" class="form-control" value="{{$tiket->customer->usersecret}}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Password Secret</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>    
                                <input type="text" name="pass_secretOld" class="form-control" value="{{$tiket->customer->pass_secret}}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Jenis Koneksi</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-link-alt"></i></span>    
                                <input type="text" class="form-control" value="{{$tiket->customer->koneksi->nama_koneksi}}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label class="form-label">Local Address</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-math"></i></span>    
                                <input type="text" class="form-control" value="{{$tiket->customer->local_address ?? 'Tidak Tersedia'}}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Remote Address</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-network-chart"></i></span>    
                                <input type="text" class="form-control" value="{{$tiket->customer->remote_address ?? 'Tidak Tersedia'}}" readonly>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Remote IP Management</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-plug"></i></span>    
                                <input type="text" class="form-control" value="{{$tiket->customer->remote}}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if($kategori->kategori->nama_kategori == 'Upgrade' || $kategori->kategori->nama_kategori == 'Downgrade')
        <div class="card">
            <div class="card-body">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-10">
                    <h5 class="mb-0 fw-semibold">Form Tutup Tiket {{$kategori->kategori->nama_kategori}}</h5>
                </div>
                <div class="card-body">
                    <form action="/tutup-tiket/{{ $tiket->id }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-sm-6 mb-2">
                                <label class="form-label">Router</label>
                                <div class="input-group input-group-merge">
                                    <select class="form-select" name="router" id="router" required>
                                        <option value="" selected disabled>Pilih Router</option>
                                        @forelse ($router as $r)
                                        <option value="{{ $r->id }}">{{ $r->nama_router }}</option>
                                        @empty
                                        <option value="" selected disabled>Tidak ada data</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Paket Tersedia</label>
                                <div class="input-group input-group-merge">
                                    <select name="paket" id="paket" required class="form-select">
                                        <option value="" selected disabled>Pilih Paket</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6 mb-2">
                                <label class="form-label">Usersecret</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-lock"></i></span>
                                    <input type="text" class="form-control" name="usersecret" placeholder="coba@niscala.net.id" id="usersecret">
                                </div>
                                <small class="text-muted">*Jika tidak ada perubahan, kosongkan saja</small>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Password Secret</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-key"></i></span>
                                    <input type="text" class="form-control" name="pass_secret" placeholder="coba123" id="pass_secret">
                                </div>
                                <small class="text-muted">*Jika tidak ada perubahan, kosongkan saja</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 mb-2">
                                <label for="local_address" class="form-label">Local Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-math"></i></span>
                                    <input type="text" class="form-control" name="local_address" placeholder="192.168.1.1">
                                </div>
                            </div>
                            <div class="col-sm-4 mb-2">
                                <label for="remote_address" class="form-label">Remote Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-network-chart"></i></span>
                                    <input type="text" class="form-control" name="remote_address" placeholder="192.168.1.1">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="remote" class="form-label">Remote IP Management</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-plug"></i></span>
                                    <input type="text" class="form-control" name="remote" placeholder="192.168.1.1">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bx bx-file me-1"></i>Kirim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inisialisasi Tom Select di #paket
        var paketSelect = new TomSelect('#paket', {
            create: false, // kalau true, user bisa tambah manual
            sortField: { field: 'text', direction: 'asc' }
        });

        $('#router').change(function() {
            var routerId = $(this).val();
            $.ajax({
                url: '/api/paket/by-router/' + routerId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Data API:', data);

                    // Hapus semua option di Tom Select
                    paketSelect.clearOptions();
                    // paketSelect.addOption({ value: '', text: 'Pilih Paket', disabled: true });

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function(item) {
                            paketSelect.addOption({ value: item.id, text: item.nama_paket });
                        });
                    } else {
                        paketSelect.addOption({ value: '', text: 'Tidak ada paket', disabled: true });
                    }

                    // Refresh Tom Select
                    paketSelect.refreshOptions(false);
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });
    });
</script>

@endsection