@extends('layouts.contentNavbarLayout')

@section('title', 'Profile Paket')

<style>
    .form-label{
        font-weight: 600;
    }
</style>

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title fw-bold">Manajemen Router & Profile Paket</h4>
                <small class="card-subtitle">Daftar Router dan Profile Paket Langganan Niscala</small>
            </div>
        </div>
        
        {{-- Table Router --}}
        <div class="card mb-5">
            <div class="card-header border-bottom mb-5">
                <h5 class="card-title fw-bold">Data Router</h5>
                <small class="card-subtitle">Daftar seluruh router yang terdaftar</small>
            </div>
            {{-- Table Profile Paket --}}
            <div class="card-body">
                <div class="d-flex justify-content-start mb-5">
                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahRouter">
                        <i class="bx bx-plus me-1"></i>Tambah Router
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Nama Router</th>
                                <th>IP Address</th>
                                <th>Port</th>
                                <th>Status</th>
                                <th>CPU Load</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center" >
                            @forelse ($router as $index => $r)
                            <tr class="text-center">
                                <td>{{ $router->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        {{ $r->nama_router }}
                                    </span>
                                </td>
                                <td><span class="badge bg-warning bg-opacity-10 text-dark">{{ $r->ip_address }}</span></td>
                                <td><span class="badge bg-info bg-opacity-10 text-info">{{ $r->port }}</span></td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success">{{$r->status_koneksi}}</span>
                                </td>
                                <td>
                                    @if ($r->cpu_load !== null)
                                    <div class="progress" style="height: 16px;">
                                        <div class="progress-bar
                                                @if($r->cpu_load >= 80) bg-danger
                                                @elseif($r->cpu_load >= 60) bg-warning
                                                @elseif($r->cpu_load >= 40) bg-info
                                                @else bg-success
                                                @endif"
                                        role="progressbar"
                                        style="width: {{ $r->cpu_load }}%;"
                                        aria-valuenow="{{ $r->cpu_load }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                        {{ $r->cpu_load }}%
                                    </div>
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="row">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="/interface/{{ $r->id }}" data-bs-toggle="tooltip" title="Server" data-bs-placement="bottom">
                                            <i class="bx bx-server text-primary"></i>
                                        </a>|
                                        <a href="#" onclick="editRouter({{ $r->id }})" data-bs-toggle="tooltip" title="Edit" data-bs-placement="bottom">
                                            <i class="bx bx-edit text-warning"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bx bx-search-alt-2 fs-1 text-muted mb-2"></i>
                                    <span class="text-muted">Tidak ada data paket yang ditemukan</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Result Count -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted">Total: {{ $router->total() }}</span>
                {{ $router->appends(request()->except('router_page'))->links() }}
                <div class="text-muted">
                    Menampilkan {{ $router->count() }} dari {{ $router->total() }} data
                </div>
            </div>
        </div>
    </div>
    
    <!-- Data Table Card Profile Paket-->
    <div class="card">
        <div class="card-header border-bottom mb-4">
            <h5 class="card-title fw-bold">Profile Paket</h5>
            <small class="card-subtitle">Daftar Profile Paket Langganan</small>
        </div>
        {{-- Filter --}}
        <div class="card-body border-bottom">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Pencarian Paket</label>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="d-flex gap-2">
                                <input type="text" class="form-control" id="paket-search-input" placeholder="Cari nama paket...">
                                <button type="button" class="btn btn-secondary btn-sm" id="paket-search-btn">Cari</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Table Profile Paket --}}
        <div class="card-body">
            <div class="d-flex justify-content-start mb-5">
                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPaket">
                    <i class="bx bx-plus me-1"></i>Tambah Paket
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nama Paket</th>
                            <th>Nama Profile</th>
                            <th>Nama Router</th>
                            <th>Harga</th>
                            <th>Jumlah Pelanggan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>                     
                    <tbody id="paket-table-body">
                        <tr><td colspan="7" class="text-center">Memuat data...</td></tr>
                    </tbody>
                </table>
                <ul class="pagination justify-content-center mt-4" id="paket-pagination"></ul>
            </div>
            
            <!-- Result Count -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted" id="paket-total-data">Total Data: 0</span>
                <div class="text-muted" id="paket-showing-data">Menampilkan 0 dari 0 data</div>
            </div>
        </div>
        
    </div>
</div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.1); z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

{{-- Modal Tambah Paket --}}
<div class="modal fade" id="modalTambahPaket" tabindex="-1" aria-labelledby="modalTambahPaketLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahPaketLabel"><i class="bx bx-plus me-1"></i>Tambah Paket Langganan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <hr class="mb-0">
            <form action="/tambah/paket" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label mb-2">*Router</label>
                        <select name="router_id" class="form-select">
                            <option value="" selected disabled>Pilih Router</option>
                            @foreach ($router as $r)
                            <option value="{{ $r->id }}">{{ $r->nama_router }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Nama Paket</label>
                        <input type="text" class="form-control" id="nama_paket" name="nama_paket" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Nama Profile</label>
                        <input type="text" class="form-control" id="paket_nama" name="profile_name" required>
                        <span>
                            <small class="text-danger fw-bold">*Harus sesuai dengan nama profile paket di Mikrotik</small>
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Harga Paket Langganan</label>
                        <input type="text" class="form-control" id="harga" name="harga" required>
                        <input hidden type="text" class="form-control" id="hargaRaw" name="hargaRaw">
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-outline-warning btn-sm">
                        <i class="bx bx-file me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Tambah Router --}}
<div class="modal fade" id="modalTambahRouter" tabindex="-1" aria-labelledby="modalTambahPaketLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahPaketLabel"><i class="bx bx-plus me-1"></i>Tambah Router</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <hr class="mb-0">
            <form action="/tambah/router" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label mb-2">*Nama Router</label>
                        <input type="text" class="form-control" id="nama_router" name="nama_router" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*IP Address Router</label>
                        <input type="text" class="form-control" id="ip_address" name="ip_address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Port</label>
                        <input type="text" class="form-control" id="port" name="port" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-outline-warning btn-sm">
                        <i class="bx bx-file me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Router --}}
<div class="modal fade" id="modalEditRouter" tabindex="-1" aria-labelledby="modalEditRouterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditRouterLabel"><i class="bx bx-edit me-1"></i>Edit Router</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <hr class="mb-0">
            <form id="editRouterForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label mb-2">*Nama Router</label>
                        <input type="text" class="form-control" id="edit_nama_router" name="nama_router" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*IP Address Router</label>
                        <input type="text" class="form-control" id="edit_ip_address" name="ip_address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Port</label>
                        <input type="text" class="form-control" id="edit_port" name="port" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Password</label>
                        <input type="password" class="form-control" id="edit_password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-outline-warning btn-sm">
                        <i class="bx bx-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Paket --}}
<div class="modal fade" id="modalEditPaket" tabindex="-1" aria-labelledby="modalEditPaketLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditPaketLabel"><i class="bx bx-edit me-1"></i>Edit Paket Langganan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <hr class="mb-0">
            <form id="editPaketForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label mb-2">*Router</label>
                        <select name="router_id" id="edit_router_id" class="form-select" required>
                            <option value="" selected disabled>Pilih Router</option>
                            @foreach ($router as $r)
                            <option value="{{ $r->id }}">{{ $r->nama_router }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Nama Paket</label>
                        <input type="text" class="form-control" id="edit_nama_paket" name="nama_paket" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Nama Profile</label>
                        <input type="text" class="form-control" id="edit_profile_name" name="profile_name" required>
                        <span>
                            <small class="text-danger fw-bold">*Harus sesuai dengan nama profile paket di Mikrotik</small>
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-2">*Harga Paket Langganan</label>
                        <input type="text" class="form-control" id="edit_harga" name="harga" required>
                        <input hidden type="text" class="form-control" id="edit_hargaRaw" name="hargaRaw">
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-outline-warning btn-sm">
                        <i class="bx bx-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    const harga = document.getElementById('harga');
    const hargaRaw = document.getElementById('hargaRaw');
    const editHarga = document.getElementById('edit_harga');
    const editHargaRaw = document.getElementById('edit_hargaRaw');
    
    // Price formatting for add modal
    harga.addEventListener('input', function(e) {
        let value = this.value.replace(/[^,\d]/g, '').toString();
        let cleanValue = value.replace(/[^0-9]/g, '');
        
        // Simpan angka mentah ke input hidden
        hargaRaw.value = cleanValue;
        
        // Format ke Rupiah
        let formatted = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(cleanValue);
        
        this.value = formatted;
    });
    
    // Price formatting for edit modal
    editHarga.addEventListener('input', function(e) {
        let value = this.value.replace(/[^,\d]/g, '').toString();
        let cleanValue = value.replace(/[^0-9]/g, '');
        
        // Simpan angka mentah ke input hidden
        editHargaRaw.value = cleanValue;
        
        // Format ke Rupiah
        let formatted = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(cleanValue);
        
        this.value = formatted;
    });
    
    // Function to handle paket edit - define it globally first
    function editPaket(id) {
        console.log('editPaket called with id:', id);
        
        try {
            // Show loading
            if ($('#loading-overlay').length > 0) {
                $('#loading-overlay').removeClass('d-none');
            }
            
            // Fetch paket data
            $.ajax({
                url: `/edit/paket/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Received data:', data);
                    
                    try {
                        // Populate modal fields
                        $('#edit_nama_paket').val(data.nama_paket || '');
                        $('#edit_profile_name').val(data.paket_name || '');
                        $('#edit_router_id').val(data.router_id || '');
                        
                        // Format and set price
                        const harga = data.harga || 0;
                        const formattedPrice = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        }).format(harga);
                        $('#edit_harga').val(formattedPrice);
                        $('#edit_hargaRaw').val(harga);
                        
                        // Set form action
                        $('#editPaketForm').attr('action', `/update/paket/${id}`);
                        
                        // Hide loading
                        if ($('#loading-overlay').length > 0) {
                            $('#loading-overlay').addClass('d-none');
                        }
                        
                        // Show modal using jQuery
                        $('#modalEditPaket').modal('show');
                        console.log('Modal should be shown');
                    } catch (e) {
                        console.error('Error populating modal:', e);
                        if ($('#loading-overlay').length > 0) {
                            $('#loading-overlay').addClass('d-none');
                        }
                        alert('Terjadi kesalahan saat mengisi form: ' + e.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr, status, error);
                    if ($('#loading-overlay').length > 0) {
                        $('#loading-overlay').addClass('d-none');
                    }
                    alert('Terjadi kesalahan saat mengambil data paket: ' + error);
                }
            });
        } catch (e) {
            console.error('Function error:', e);
            alert('Terjadi kesalahan: ' + e.message);
        }
    }
    
    // Make editPaket function globally available
    window.editPaket = editPaket;
    
    // Client-side pagination & search for Profile Paket
    let paketCurrentPage = 1;
    let paketSearchTerm = '';
    const paketPerPage = 10;
    
    function fetchPaketData(page = 1, search = '') {
        $('#loading-overlay').removeClass('d-none');
        fetch(`/paket/data?page=${page}&per_page=${paketPerPage}&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(data => {
            renderPaketTable(data);
            renderPaketPagination(data);
            $('#paket-total-data').text(`Total Data: ${data.total}`);
            $('#paket-showing-data').text(`Menampilkan ${data.from || 0} - ${data.to || 0} dari ${data.total} data`);
            $('#loading-overlay').addClass('d-none');
        })
        .catch(() => {
            $('#paket-table-body').html('<tr><td colspan="7" class="text-center">Gagal memuat data</td></tr>');
            $('#paket-pagination').html('');
            $('#paket-total-data').text('Total Data: 0');
            $('#paket-showing-data').text('Menampilkan 0 dari 0 data');
            $('#loading-overlay').addClass('d-none');
        });
    }
    
    function renderPaketTable(data) {
        let html = '';
        if (data.data && data.data.length > 0) {
            data.data.forEach((item, idx) => {
                const badgeClass = item.nama_paket === 'ISOLIREBILLING' ? 'bg-danger text-danger' : 'bg-info text-primary';
                const formattedPrice = new Intl.NumberFormat('id-ID').format(item.harga || 0);
                const customerCount = item.customer ? item.customer.length : 0;
                html += `
    <tr class="text-center">
    <td>${data.from + idx}</td>
    <td><span class="badge ${badgeClass} bg-opacity-10">${item.nama_paket || ''}</span></td>
    <td>${item.paket_name || '-'}</td>
    <td>${item.router ? (item.router.nama_router || '-') : '-'}</td>
    <td>Rp ${formattedPrice}</td>
    <td><span class="fw-bold badge bg-warning bg-opacity-10 text-warning">${customerCount}</span></td>
    <td>
    <div class="row">
    <div class="d-flex justify-content-center gap-2">
    <a href="#" onclick="editPaket(${item.id})" data-bs-toggle="tooltip" title="Edit Profile" data-bs-placement="bottom">
    <i class="bx bx-edit text-warning"></i>
    </a>|
    <a href="/hapus/paket/${item.id}" data-bs-toggle="tooltip" title="Hapus Profile" data-bs-placement="bottom" onclick="return confirm('Apakah Anda yakin ingin menghapus paket ini?')">
    <i class="bx bx-trash text-danger"></i>
    </a>
    </div>
    </div>
    </td>
    </tr>
    `;
            });
        } else {
            html = `<tr><td colspan="7" class="text-center py-4"><div class="d-flex flex-column align-items-center"><i class="bx bx-search-alt-2 fs-1 text-muted mb-2"></i><span class="text-muted">Tidak ada data paket yang ditemukan</span></div></td></tr>`;
        }
        $('#paket-table-body').html(html);
        $('[data-bs-toggle="tooltip"]').tooltip();
    }
    
    function renderPaketPagination(data) {
        let html = '';
        if (data.last_page > 1) {
            for (let i = 1; i <= data.last_page; i++) {
                html += `<li class="page-item${i === data.current_page ? ' active' : ''}"><a class="page-link" href="#" onclick="goToPaketPage(${i});return false;">${i}</a></li>`;
            }
        }
        $('#paket-pagination').html(html);
    }
    
    function goToPaketPage(page) {
        paketCurrentPage = page;
        fetchPaketData(page, paketSearchTerm);
    }
    
    $('#paket-search-btn').on('click', function() {
        paketSearchTerm = $('#paket-search-input').val();
        paketCurrentPage = 1;
        fetchPaketData(1, paketSearchTerm);
    });
    $('#paket-search-input').on('keypress', function(e) {
        if (e.which === 13) {
            paketSearchTerm = $('#paket-search-input').val();
            paketCurrentPage = 1;
            fetchPaketData(1, paketSearchTerm);
        }
    });
    
    // Debounce for input event
    let paketSearchTimeout;
    $('#paket-search-input').on('input', function() {
        clearTimeout(paketSearchTimeout);
        paketSearchTerm = $(this).val();
        paketCurrentPage = 1;
        paketSearchTimeout = setTimeout(function() {
            fetchPaketData(1, paketSearchTerm);
        }, 300);
    });
    
    // Initial load
    $(document).ready(function() {
        fetchPaketData(1, '');
    });
    
    // Function to handle router edit
    function editRouter(id) {
        // Fetch router data
        fetch(`/edit/router/${id}`)
        .then(response => response.json())
        .then(data => {
            // Populate modal fields
            document.getElementById('edit_nama_router').value = data.nama_router;
            document.getElementById('edit_ip_address').value = data.ip_address;
            document.getElementById('edit_port').value = data.port;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_password').value = data.password;
            
            // Set form action
            document.getElementById('editRouterForm').action = `/update/router/${id}`;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('modalEditRouter'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil data router');
        });
    }
    
    
</script>

@endsection