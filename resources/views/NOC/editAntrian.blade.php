@extends('layouts.contentNavbarLayout')
@section('title','Edit Detail Antrian')
<style>
    #paket-select:disabled {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
    
    #router-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
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
                            <label class="form-label">Router</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bxs-package"></i></span>
                                <select name="router_id" class="form-select" id="router-select">
                                    <option value="">Pilih Router</option>
                                    @foreach ($router as $item)
                                        <option value="{{ $item->id }}" {{ $antrian->router_id == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_router }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label">Paket</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bxs-package"></i></span>
                                <select name="paket_id" class="form-select" id="paket-select" {{ !$antrian->router_id ? 'disabled' : '' }}>
                                    @if($antrian->router_id && $antrian->paket_id)
                                        @foreach ($paket as $item)
                                            @if($item->router_id == $antrian->router_id)
                                                <option value="{{ $item->id }}" {{ $antrian->paket_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->nama_paket }}
                                                </option>
                                            @endif
                                        @endforeach
                                    @else
                                        <option value="">Pilih Router Terlebih Dahulu</option>
                                    @endif
                                </select>
                            </div>
                            @if(!$antrian->router_id)
                            <small class="text-warning mt-1">
                                <i class="bx bx-info-circle"></i> Pilih router terlebih dahulu untuk memilih paket
                            </small>
                            @endif
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
                                <input type="text" name="remote" class="form-control" value="{{ $antrian->remote ?? '' }}">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Remote Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input type="text" name="remote_address" class="form-control" value="{{ $antrian->remote_address ?? '' }}">
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="form-label">Local Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input type="text" name="local_address" class="form-control" value="{{ $antrian->local_address ?? '' }}">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const routerSelect = document.getElementById('router-select');
    const paketSelect = document.getElementById('paket-select');
    const paketContainer = paketSelect.closest('.col-sm-6');

    // Fungsi untuk menampilkan loading dengan animasi
    function showLoading() {
        paketSelect.innerHTML = `
            <option value="">
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </option>
        `;
    }

    // Fungsi untuk update paket dengan delay
    function updatePaketOptions(routerId) {
        if (routerId) {
            paketSelect.disabled = false;
            showLoading();
            
            // Simulasi loading 3 detik sebelum fetch data
            let loadingTime = 3000;
            let startTime = Date.now();
            
            const loadingInterval = setInterval(() => {
                let elapsed = Date.now() - startTime;
                let remaining = Math.ceil((loadingTime - elapsed) / 1000);
                
                if (remaining > 0) {
                    paketSelect.innerHTML = `<option value="">Loading... (${remaining}s)</option>`;
                }
            }, 500);
            
            // Tambahkan delay 3 detik sebelum fetch data
            setTimeout(() => {
                clearInterval(loadingInterval);
                
                fetch(`/get-paket-by-router/${routerId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            let options = '<option value="">Pilih Paket</option>';
                            data.forEach(paket => {
                                options += `<option value="${paket.id}">${paket.nama_paket}</option>`;
                            });
                            paketSelect.innerHTML = options;
                            
                            // Hapus pesan warning jika ada
                            const existingWarning = paketContainer.querySelector('.text-warning');
                            if (existingWarning) {
                                existingWarning.remove();
                            }
                        } else {
                            paketSelect.innerHTML = '<option value="">Tidak ada paket untuk router ini</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        paketSelect.innerHTML = '<option value="">Error loading paket</option>';
                    });
            }, loadingTime);
            
        } else {
            paketSelect.disabled = true;
            paketSelect.innerHTML = '<option value="">Pilih Router Terlebih Dahulu</option>';
            
            // Tambahkan pesan warning jika belum ada
            if (!paketContainer.querySelector('.text-warning')) {
                const warningMsg = document.createElement('small');
                warningMsg.className = 'text-warning mt-1';
                warningMsg.innerHTML = '<i class="bx bx-info-circle"></i> Pilih router terlebih dahulu untuk memilih paket';
                paketContainer.appendChild(warningMsg);
            }
        }
    }

    // Event listener untuk perubahan router
    routerSelect.addEventListener('change', function() {
        updatePaketOptions(this.value);
    });

    // Event listener untuk klik dropdown paket
    paketSelect.addEventListener('click', function() {
        if (!routerSelect.value) {
            // Tampilkan alert atau toast
            alert('Silakan pilih router terlebih dahulu sebelum memilih paket');
            
            // Focus ke dropdown router
            routerSelect.focus();
            
            // Tambahkan style border merah ke router select
            routerSelect.style.borderColor = '#ff0000';
            setTimeout(() => {
                routerSelect.style.borderColor = '';
            }, 2000);
        }
    });

    // Initialize state berdasarkan nilai awal
    if (!routerSelect.value) {
        paketSelect.disabled = true;
    }
});
</script>
@endsection