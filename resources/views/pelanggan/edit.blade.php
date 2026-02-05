@extends('layouts.contentNavbarLayout')
@section('title', 'Edit Pelanggan - ' . $pelanggan->nama_customer)

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-edit-alt me-2"></i>Edit Pelanggan</h5>
            <small class="text-muted">Perbarui data pelanggan layanan internet.</small>
          </div>
          <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Kembali
          </a>
        </div>
      </div>

      <form id="formUpdatePelanggan" action="{{ route('pelanggan.update', $pelanggan->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
          <!-- Section 1: Informasi Pribadi -->
          <div class="col-md-12 col-lg-6">
            <div class="card h-100 shadow-sm">
              <div class="card-header bg-transparent border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-user me-2"></i>Informasi Pribadi</h6>
              </div>
              <div class="card-body pt-3">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label" for="nama_customer">Nama Lengkap</label>
                    <div class="input-group input-group-merge">
                      <span class="input-group-text"><i class="bx bx-user"></i></span>
                      <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama_customer"
                        name="nama" value="{{ old('nama', $pelanggan->nama_customer) }}"
                        placeholder="Nama Lengkap Pelanggan" required>
                      @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="no_hp">Nomor HP/WA</label>
                    <div class="input-group input-group-merge">
                      <span class="input-group-text"><i class="bx bx-phone"></i></span>
                      <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" name="no_hp"
                        value="{{ old('no_hp', $pelanggan->no_hp) }}" placeholder="08..." required>
                      @error('no_hp')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="no_identitas">Nomor Identitas (KTP/SIM)</label>
                    <div class="input-group input-group-merge">
                      <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                      <input type="text" class="form-control @error('no_identitas') is-invalid @enderror"
                        id="no_identitas" name="no_identitas" value="{{ old('no_identitas', $pelanggan->no_identitas) }}"
                        placeholder="16 digit NIK">
                      @error('no_identitas')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  {{-- Foto Identitas --}}
                  <div class="col-12">
                    <label class="form-label" for="foto_identitas">Upload Foto Identitas (Opsional)</label>
                    <div class="input-group">
                       <input type="file" class="form-control @error('identitas_file') is-invalid @enderror" id="foto_identitas" name="identitas_file" accept="image/*">
                       @error('identitas_file')
                         <div class="invalid-feedback">{{ $message }}</div>
                       @enderror
                    </div>
                    @if($pelanggan->identitas)
                      <div class="mt-2">
                        <small class="text-muted d-block mb-1">Foto saat ini:</small>
                        <a href="{{ asset($pelanggan->identitas) }}" target="_blank">
                          <img src="{{ asset($pelanggan->identitas) }}" alt="Identitas Pelanggan" class="img-thumbnail" style="max-height: 100px;">
                        </a>
                      </div>
                    @endif
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="alamat">Alamat Lengkap</label>
                    <div class="input-group input-group-merge">
                      <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
                      <input type="text" class="form-control @error('alamat') is-invalid @enderror" id="alamat"
                        name="alamat" value="{{ old('alamat', $pelanggan->alamat) }}" placeholder="Alamat pemasangan">
                      @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="gps">Koordinat GPS</label>
                    <div class="input-group input-group-merge">
                      <span class="input-group-text"><i class="bx bx-current-location"></i></span>
                      <input type="text" class="form-control @error('gps') is-invalid @enderror" id="gps" name="gps"
                        value="{{ old('gps', $pelanggan->gps) }}" placeholder="-6.xxx, 106.xxx">
                      {{-- Button to open maps in new tab could be added here --}}
                    </div>
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="pic">Sales / Agen / PIC</label>
                    <select name="agen_id" class="form-select" id="pic">
                      <option value="" disabled {{ old('agen_id', $pelanggan->agen_id) ? '' : 'selected' }}>Pilih Agen
                      </option>
                      @foreach ($agen as $item)
                        <option value="{{ $item->id }}" {{ old('agen_id', $pelanggan->agen_id) == $item->id ? 'selected' : '' }}>
                          {{ $item->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2: Layanan & Jaringan -->
          <div class="col-md-12 col-lg-6">
            <div class="card h-100 shadow-sm">
              <div class="card-header bg-transparent border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-network-chart me-2"></i>Layanan & Jaringan</h6>
              </div>
              <div class="card-body pt-3">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Paket Langganan</label>
                    <select name="paket" id="paket" class="form-select" required>
                      @foreach ($paket as $item)
                        <option value="{{ $item->id }}" {{ old('paket', $pelanggan->paket_id) == $item->id ? 'selected' : '' }}>
                          {{ $item->nama_paket }} ({{ number_format($item->harga, 0, ',', '.') }})
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Router / NAS</label>
                    <select name="router" id="router" class="form-select" required>
                      @foreach ($router as $item)
                        <option value="{{ $item->id }}" {{ old('router', $pelanggan->router_id) == $item->id ? 'selected' : '' }}>
                          {{ $item->nama_router }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">BTS Server</label>
                    <select name="bts" id="bts" class="form-select">
                      @foreach ($bts as $item)
                        <option value="{{ $item->id }}" {{ old('bts', $pelanggan->getServer->id ?? '') == $item->id ? 'selected' : '' }}>{{ $item->lokasi_server }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Jenis Koneksi</label>
                    <select name="koneksi" id="koneksi" class="form-select">
                      @foreach ($koneksi as $item)
                        <option value="{{ $item->id }}" {{ old('koneksi', $pelanggan->koneksi_id) == $item->id ? 'selected' : '' }}>{{$item->nama_koneksi}}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Media Koneksi</label>
                    <select name="media" id="media" class="form-select">
                      @foreach ($media as $item)
                        <option value="{{ $item->id }}" {{ old('media', $pelanggan->media_id) == $item->id ? 'selected' : '' }}>{{ $item->nama_media }}</option>
                      @endforeach
                    </select>
                  </div>

                  {{-- Conditional Fields based on Media --}}
                  {{-- FO --}}
                  @if($pelanggan->media_id == 3)
                    <div class="col-md-4">
                      <label class="form-label">OLT</label>
                      <select name="olt" id="olt" class="form-select">
                        <option value="" selected>-</option>
                        @foreach ($olt as $item)
                          <option value="{{ $item->id }}" {{ ($pelanggan->odp->odc->olt->id ?? null) == $item->id ? 'selected' : '' }}>{{$item->nama_lokasi}}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">ODC</label>
                      <select name="odc" id="odc" class="form-select">
                        <option value="" selected>-</option>
                        @foreach ($odc as $item)
                          <option value="{{ $item->id }}" {{ ($pelanggan->odp->odc->id ?? null) == $item->id ? 'selected' : '' }}>{{$item->nama_odc}}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">ODP</label>
                      <select name="odp" id="odp" class="form-select">
                        <option value="{{ $pelanggan->lokasi_id }}" selected>{{$pelanggan->odp->nama_odp ?? '-'}}</option>
                        @foreach ($odp as $item)
                          <option value="{{ $item->id }}" {{ old('odp', $pelanggan->lokasi_id) == $item->id ? 'selected' : '' }}>{{$item->nama_odp}}</option>
                        @endforeach
                      </select>
                    </div>
                  @endif

                  {{-- Wireless --}}
                  @if($pelanggan->media_id == 2)
                    <div class="col-md-6">
                      <label class="form-label">Access Point</label>
                      <input type="text" class="form-control" name="access_point"
                        value="{{ old('access_point', $pelanggan->access_point) }}">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Station</label>
                      <input type="text" class="form-control" name="station"
                        value="{{ old('station', $pelanggan->station) }}">
                    </div>
                  @endif

                  {{-- Fiber Converter/HTB --}}
                  @if($pelanggan->media_id == 1)
                    <div class="col-md-6">
                      <label class="form-label">Transiver</label>
                      <input type="text" class="form-control" name="transiver"
                        value="{{ old('transiver', $pelanggan->transiver) }}">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Receiver</label>
                      <input type="text" class="form-control" name="receiver"
                        value="{{ old('receiver', $pelanggan->receiver) }}">
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <!-- Section 3: Konfigurasi Teknis -->
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-transparent border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-server me-2"></i>Konfigurasi Teknis & Perangkat</h6>
              </div>
              <div class="card-body pt-3">
                <div class="row g-3">
                  <div class="col-md-6 col-lg-3">
                    <label class="form-label">PPPoE / Secret User</label>
                    <div class="input-group input-group-merge">
                      <span class="input-group-text"><i class="bx bx-user-circle"></i></span>
                      <input type="text" class="form-control font-monospace" name="usersecret"
                        value="{{ old('usersecret', $pelanggan->usersecret) }}">
                    </div>
                  </div>
                  <div class="col-md-6 col-lg-3">
                    <label class="form-label">Password Secret</label>
                    <div class="input-group input-group-merge">
                      <span class="input-group-text"><i class="bx bx-key"></i></span>
                      <input type="text" class="form-control font-monospace" name="pass_secret"
                        value="{{ old('pass_secret', $pelanggan->pass_secret) }}">
                    </div>
                  </div>
                  <div class="col-md-6 col-lg-3">
                    <label class="form-label">Local Address</label>
                    <input type="text" class="form-control font-monospace" name="local_address"
                      value="{{ old('local_address', $pelanggan->local_address) }}" placeholder="0.0.0.0">
                  </div>
                  <div class="col-md-6 col-lg-3">
                    <label class="form-label">Remote Address</label>
                    <input type="text" class="form-control font-monospace" name="remote_address"
                      value="{{ old('remote_address', $pelanggan->remote_address) }}" placeholder="0.0.0.0">
                  </div>
                  <div class="col-md-6 col-lg-3">
                    <label class="form-label">Remote Management IP</label>
                    <input type="text" class="form-control font-monospace" name="remote"
                      value="{{ old('remote', $pelanggan->remote) }}">
                  </div>

                  <div class="col-md-6 col-lg-3">
                    <label class="form-label">Perangkat CPE</label>
                    <select name="perangkat" id="perangkat" class="form-select">
                      @foreach ($perangkat as $item)
                        <option value="{{ $item->id }}" {{ old('perangkat', $pelanggan->perangkat_id) == $item->id ? 'selected' : '' }}>{{$item->nama_perangkat}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6 col-lg-3">
                    <label class="form-label">Seri/SN Perangkat</label>
                    <input type="text" class="form-control" name="seri"
                      value="{{ old('seri', $pelanggan->seri_perangkat) }}">
                  </div>
                  <div class="col-md-6 col-lg-3">
                    <label class="form-label">MAC Address</label>
                    <input type="text" class="form-control font-monospace" name="mac"
                      value="{{ old('mac', $pelanggan->mac_address) }}" placeholder="AA:BB:CC:DD:EE:FF">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 mt-4 text-end">
            <button type="submit" class="btn btn-primary px-4"><i class="bx bx-save me-1"></i> Simpan Perubahan</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Initialize TomSelect for all selects that should be searchable
      const selectConfigs = [
        '#paket', '#router', '#olt', '#odc', '#odp', '#bts', '#perangkat', '#pic', '#media', '#koneksi'
      ];

      selectConfigs.forEach(selector => {
        const el = document.querySelector(selector);
        if (el) {
          new TomSelect(el, { create: false, sortField: { field: "text", direction: "asc" } });
        }
      });

      // Confirmation before submit
      document.getElementById('formUpdatePelanggan').addEventListener('submit', function (e) {
        e.preventDefault();
        Swal.fire({
          title: 'Konfirmasi Data',
          text: "Apakah data yang dimasukkan sudah benar? Perubahan akan disimpan ke sistem dan Router Mikrotik.",
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Ya, Simpan!',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            this.submit();
          }
        });
      });
    });
  </script>
@endsection
