@extends('layouts.contentNavbarLayout')
@section('title', 'Edit Pelanggan')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Edit Pelanggan {{$pelanggan->nama_customer}}</h5>
                <small class="card-subtitle">Halaman edit pelanggan</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form id="formUpdatePelanggan" action="/update-pelanggan/{{ $pelanggan->id }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="d-flex border-bottom mb-3">
                            <h6 class="fw-semibold">Informasi Pelanggan</h6>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Nama Pelanggan</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-user"></i>
                                </span>
                                <input type="text" class="form-control" id="nama_customer" name="nama" value="{{ $pelanggan->nama_customer }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">No Pelanggan</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-phone"></i>
                                </span>
                                <input type="text" class="form-control" id="nama_customer" name="no_hp" value="{{ $pelanggan->no_hp }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Alamat</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-pin"></i>
                                </span>
                                <input type="text" class="form-control" id="nama_customer" name="alamat" value="{{ $pelanggan->alamat }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">GPS</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-map"></i>
                                </span>
                                <input type="text" class="form-control" id="nama_customer" name="gps" value="{{ $pelanggan->gps }}">
                            </div>
                        </div>
                        <div class="col-sm-12 mb-2">
                            <label class="form-label">No Identitas</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-math"></i>
                                </span>
                                <input type="text" class="form-control" id="nama_customer" name="no_identitas" value="{{ $pelanggan->no_identitas }}">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="d-flex border-bottom mb-3">
                            <h6 class="fw-semibold">Informasi Teknis</h6>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Router</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-server"></i>
                                </span>
                                <select name="router" id="router" class="form-select">
                                    <option value="{{ $pelanggan->router_id }}" selected>{{$pelanggan->router->nama_router}}</option>
                                    @foreach ($router as $item)
                                    <option value="{{ $item->id }}">{{$item->nama_router}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Paket Langganan</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-package"></i>
                                </span>
                                <select name="paket" id="paket" class="form-select">
                                    <option value="{{ $pelanggan->paket_id }}" selected>{{$pelanggan->paket->nama_paket}}</option>
                                    @foreach ($paket as $item)
                                    <option value="{{ $item->id }}">{{$item->nama_paket}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">BTS Server</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-terminal"></i>
                                </span>
                                <select name="bts" id="bts" class="form-select">
                                    <option value="" selected >{{$pelanggan->odp->odc->olt->lokasi_server}}</option>
                                    @foreach ($bts as $item)
                                    <option value="{{ $item->id }}">{{$item->lokasi_server}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        {{-- OLT --}}
                        @if($pelanggan->media_id == 3)
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">OLT</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-plug"></i>
                                </span>
                                <select name="olt" id="olt" class="form-select">
                                    <option value="" selected >{{$pelanggan->odp->odc->olt->nama_lokasi}}</option>
                                    @foreach ($olt as $item)
                                    <option value="{{ $item->id }}">{{$item->nama_lokasi}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">ODC</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-plug"></i>
                                </span>
                                <select name="odc" id="odc" class="form-select">
                                    <option value="" selected >{{$pelanggan->odp->odc->nama_odc}}</option>
                                    @foreach ($odc as $item)
                                    <option value="{{ $item->id }}">{{$item->nama_odc}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">ODP</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-plug"></i>
                                </span>
                                <select name="odp" id="odp" class="form-select">
                                    <option value="{{ $pelanggan->lokasi_id }}" selected >{{$pelanggan->odp->nama_odp}}</option>
                                    @foreach ($odp as $item)
                                    <option value="{{ $item->id }}">{{$item->nama_odp}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        
                        {{-- HTB --}}
                        @if($pelanggan->media_id == 1)
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Transiver</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-link"></i>
                                </span>
                                <input type="text" class="form-control" name="transiver" value="{{ $pelanggan->transiver }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Receiver</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-link"></i>
                                </span>
                                <input type="text" class="form-control" name="receiver" value="{{ $pelanggan->receiver }}">
                            </div>
                        </div>
                        @endif
                        
                        {{-- Wireless --}}
                        @if($pelanggan->media_id == 2)
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Access Point</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-link"></i>
                                </span>
                                <input type="text" class="form-control" name="access_point" value="{{ $pelanggan->access_point }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Station</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-link"></i>
                                </span>
                                <input type="text" class="form-control" name="station" value="{{ $pelanggan->station }}">
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Media Koneksi</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-link"></i>
                                </span>
                                <select name="media" id="media" class="form-select">
                                    <option value="{{ $pelanggan->media_id }}" selected >{{$pelanggan->media->nama_media}}</option>
                                    @foreach ($media as $item)
                                    @if($pelanggan->media->nama_media != $item->nama_media)
                                    <option value="{{ $item->id }}">{{$item->nama_media}}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Jenis Koneksi</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-link"></i>
                                </span>
                                <select name="koneksi" id="koneksi" class="form-select">
                                    <option value="{{ $pelanggan->koneksi_id }}" selected >{{$pelanggan->koneksi->nama_koneksi}}</option>
                                    @foreach ($koneksi as $item)
                                    @if($pelanggan->koneksi->nama_koneksi != $item->nama_koneksi)
                                    <option value="{{ $item->id }}">{{$item->nama_koneksi}}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Local Address</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-link"></i>
                                </span>
                                <input type="text" class="form-control" name="local_address" value="{{ $pelanggan->local_address }}">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Remote Address</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-link"></i>
                                </span>
                                <input type="text" class="form-control" name="remote_address" value="{{ $pelanggan->remote_address }}">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Remote IP Management</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-link"></i>
                                </span>
                                <input type="text" class="form-control" name="remote" value="{{ $pelanggan->remote }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Usersecret</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-envelope"></i>
                                </span>
                                <input type="text" class="form-control" name="usersecret" value="{{ $pelanggan->usersecret }}">
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Password Secret</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-key"></i>
                                </span>
                                <input type="text" class="form-control" name="pass_secret" value="{{ $pelanggan->pass_secret }}">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Perangkat</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-devices"></i>
                                </span>
                                <select name="perangkat" id="perangkat" class="form-select">
                                    <option value="{{ $pelanggan->perangkat_id }}" selected >{{$pelanggan->perangkat->nama_perangkat}}</option>
                                    @foreach ($perangkat as $item)
                                    @if($pelanggan->perangkat->nama_perangkat != $item->nama_perangkat)
                                    <option value="{{ $item->id }}">{{$item->nama_perangkat}}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Seri Perangkat</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-window"></i>
                                </span>
                                <input type="text" class="form-control" name="seri" value="{{ $pelanggan->seri_perangkat }}">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Mac Address</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">
                                    <i class="bx bx-window"></i>
                                </span>
                                <input type="text" class="form-control" name="mac" value="{{ $pelanggan->mac_address }}">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-outline-warning btn-sm">
                                <i class="bx bx-save me-1"></i>Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Tom Select --}}
<script>
    // Paket
    new TomSelect('#paket',{
        create: false,
    });
    
    // Router
    new TomSelect('#router',{
        create: false,
    });
</script>

<script>
    document.getElementById('formUpdatePelanggan').addEventListener('submit', function(e) {
        e.preventDefault(); // hentikan submit dulu
        
        Swal.fire({
            title: 'Yakin Update Data?',
            text: "Perubahan akan tersimpan di database!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            topLayer: true
        }).then((result) => {
            if (result.isConfirmed) {
                e.target.submit(); // submit form kalau user klik "Ya"
            }
        });
    });
</script>
@endsection