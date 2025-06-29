@extends('layouts.contentNavbarLayout')

@section('title', 'User Management')


@section('content')

<div class="row">
    <div class="col-12">
        
        <nav class="breadcrumb-nav">
            <ul class="breadcrumb breadcrumb-transparent breadcrumb-style2 mb-3">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="/user-management">User Management</a></li>
                <li class="breadcrumb-item active text-primary" aria-current="page">Manajemen User</li>
            </ul>
        </nav>
        
        <div class="card">
            <div class="card-header header-with-pattern">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 py-2">
                    <div class="header-content">
                        <div class="d-flex align-items-center">
                            <div class="header-icon me-3 d-flex align-items-center justify-content-center">
                                <i class='bx bx-user-circle fs-3 text-primary'></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-semibold">User Management</h4>
                                <p class="text-muted mb-0 small">Kelola pengguna dan hak akses sistem</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="stats-summary me-4 d-none d-lg-block">
                            <div class="d-flex gap-4">
                                <div class="summary-item">
                                    <span class="text-muted small d-block fw-bold">Total User: {{ $user->where('roles_id', '!=', 8)->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-start align-items-center mb-5 gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBoth">
                        <i class="bx bx-user-plus me-1"></i>Add User
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="dataTable" class="table table-hover" style="width:100%">
                        <thead class="text-center table-dark">
                            <tr>
                                <th>No</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @if (count($user->where('roles_id', '!=', 8)) > 0)
                            @foreach ($user as $u)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initial me-4">
                                            <img src="{{ asset($u->profile) }}" class="rounded-circle" width="40" height="40">
                                        </div>
                                        <div>
                                            <span class="fw-medium">{{ $u->name }}</span>
                                            <div class="text-muted small">ID: {{ $u->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class='bx bx-envelope text-muted me-2'></i>
                                        <span>{{ $u->email }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        {{ $u->roles->name }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalCenter{{ $u->id }}">
                                        <i class="bx bx-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr id="noDataResults">
                                <td colspan="5" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class='bx bx-user-circle text-muted mb-2' style="font-size: 2rem;"></i>
                                        <div>Tidak ada data user ditemukan</div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modern Offcanvas Modal --}}
<div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasBoth" aria-labelledby="offcanvasBothLabel">
    <div class="offcanvas-header" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-bottom: none;">
        <div class="d-flex align-items-center">
            <div class="me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(255, 255, 255, 0.2); border-radius: 8px;">
                <i class='bx bx-user-plus text-white fs-5'></i>
            </div>
            <div>
                <h5 id="offcanvasBothLabel" class="offcanvas-title text-white mb-0 fw-semibold">Tambah User Baru</h5>
                <small class="text-white-50">Buat akun pengguna baru untuk sistem</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" style="padding: 1.5rem;">
        <form action="/user/store" method="POST" id="addUserForm">
            @csrf
            <div class="mb-4">
                <label class="form-label fw-medium" for="user_name">
                    <i class='bx bx-user me-1 text-muted'></i>Nama Lengkap
                </label>
                <input type="text" class="form-control" id="user_name" name="name" placeholder="Contoh: John Doe" required>
                <div class="form-text">Masukkan nama lengkap pengguna</div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-medium" for="user_email">
                    <i class='bx bx-envelope me-1 text-muted'></i>Email
                </label>
                <input type="email" class="form-control" id="user_email" name="email" placeholder="contoh@email.com" required>
                <div class="form-text">Email akan digunakan untuk login ke sistem</div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-medium" for="user_role">
                    <i class='bx bx-shield me-1 text-muted'></i>Role Pengguna
                </label>
                <select class="form-select" id="user_role" name="roles_id" required>
                    <option selected disabled>Pilih Role</option>
                    @foreach ($role as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
                <div class="form-text">Tentukan hak akses pengguna dalam sistem</div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-medium" for="user_password">
                    <i class='bx bx-lock me-1 text-muted'></i>Password
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="user_password" name="password" placeholder="Minimal 8 karakter" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class='bx bx-show'></i>
                    </button>
                </div>
                <div class="form-text">Password minimal 8 karakter dengan kombinasi huruf dan angka</div>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="offcanvas">
                    <i class='bx bx-x me-1'></i>Batal
                </button>
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bx bx-plus me-1"></i>Tambah User
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
@foreach ($user as $u)
<div class="modal fade" id="modalCenter{{ $u->id }}" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom d-flex align-items-center gap-2">
                <div class="d-flex mb-3">
                    <span><i class="bx bx-edit fs-4 me-2 text-primary"></i></span>
                    <h5 class="modal-title mb-0" id="modalCenterTitle">Edit Role</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/edit/role/{{ $u->id }}" method="POST">
                @csrf
                <div class="modal-body border-bottom">
                    <div class="row">
                        <div class="col-sm-12 mb-4">
                            <label class="form-label">Nama User</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text text-primary"><i class="bx bx-user"></i></span>
                                <input type="text" class="form-control" value="{{ $u->name }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 mb-4">
                            <label class="form-label">Email</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text text-primary"><i class="bx bx-envelope"></i></span>
                                <input type="text" class="form-control" value="{{ $u->email }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 mb-4">
                            <label class="form-label">Role User</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text text-primary"><i class='bx  bx-chevrons-up'></i></span>
                                <select name="roles_id" class="form-select">
                                    @foreach ($role as $r)
                                    <option value="{{ $r->id }}" @if($u->roles_id == $r->id) selected @endif>
                                        {{ $r->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer mt-5 gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-outline-danger btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach



@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('search');
        const dataTable = document.getElementById('dataTable');
        const tableRows = dataTable.querySelectorAll('tbody tr.user-row');
        const roleFilter = document.getElementById('roleFilter');
        
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const selectedRole = roleFilter.value.toLowerCase();
            let resultsFound = false;
            
            // If search is empty and no role filter, show all rows
            if (searchTerm === '' && selectedRole === '') {
                tableRows.forEach(row => {
                    row.style.display = '';
                });
                
                // Remove no results message if it exists
                const noResultsRow = document.getElementById('noResults');
                if (noResultsRow) {
                    noResultsRow.remove();
                }
                return;
            }
            
            // Filter rows based on search term and role
            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                const roleText = row.querySelector('.role-badge').textContent.toLowerCase();
                
                const matchesSearch = searchTerm === '' || rowText.includes(searchTerm);
                const matchesRole = selectedRole === '' || roleText.includes(selectedRole);
                
                if (matchesSearch && matchesRole) {
                    row.style.display = '';
                    resultsFound = true;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Add no results message if needed
            if (!resultsFound) {
                if (!document.getElementById('noResults')) {
                    const tbody = dataTable.querySelector('tbody');
                    const noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'noResults';
                    noResultsRow.innerHTML = `
                            <td colspan="5" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-search text-muted mb-2' style="font-size: 2rem;"></i>
                                    <div>Tidak ada user yang cocok dengan pencarian</div>
                                    <small class="text-muted mt-1">Coba dengan kata kunci lain</small>
                                </div>
                            </td>
                        `;
                    tbody.appendChild(noResultsRow);
                }
            } else {
                const noResultsRow = document.getElementById('noResults');
                if (noResultsRow) {
                    noResultsRow.remove();
                }
            }
        }
        
        // Event listeners for search and filter
        searchInput.addEventListener('keyup', function(e) {
            performSearch();
        });
        
        searchInput.addEventListener('input', function() {
            performSearch();
        });
        
        roleFilter.addEventListener('change', function() {
            performSearch();
        });
        
        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('user_password');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.className = 'bx bx-show';
                } else {
                    icon.className = 'bx bx-hide';
                }
            });
        }
        
        // Form validation
        const addUserForm = document.getElementById('addUserForm');
        if (addUserForm) {
            addUserForm.addEventListener('submit', function(e) {
                const name = document.getElementById('user_name').value.trim();
                const email = document.getElementById('user_email').value.trim();
                const role = document.getElementById('user_role').value;
                const password = document.getElementById('user_password').value;
                
                if (!name || !email || !role || !password) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field yang diperlukan');
                    return false;
                }
                
                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password harus minimal 8 karakter');
                    return false;
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Format email tidak valid');
                    return false;
                }
            });
        }
        
        // Add tooltips to action buttons
        const actionButtons = document.querySelectorAll('.action-btn');
        actionButtons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                const title = this.getAttribute('title');
                if (title) {
                    // You can add tooltip library here if needed
                }
            });
        });
    });
</script>
@endsection
