@extends('layouts.contentNavbarLayout')

@section('title', 'User Management')

@section('styles')
<style>
    /* Enhanced Modern Design Styles */
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --card-shadow: 0 4px 25px rgba(0, 0, 0, 0.06);
        --hover-shadow: 0 8px 35px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
        --border-radius: 1rem;
    }
    
    /* Card Enhancements */
    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        overflow: hidden;
        background: #ffffff;
    }
    
    .card:hover {
        box-shadow: var(--hover-shadow);
        transform: translateY(-2px);
    }
    
    /* Enhanced Header Design */
    .header-with-pattern {
        background: var(--primary-gradient);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        padding: 2.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .header-with-pattern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        opacity: 0.1;
        animation: patternMove 20s linear infinite;
    }
    
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    @keyframes patternMove {
        0% { transform: translate(0, 0); }
        100% { transform: translate(60px, 60px); }
    }
    
    /* Enhanced Header Content */
    .header-content {
        position: relative;
        z-index: 2;
    }
    
    .header-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .header-content:hover .header-icon {
        transform: scale(1.05) rotate(-5deg);
        background: rgba(255, 255, 255, 0.25);
    }
    
    /* Enhanced Stats Display */
    .stats-summary {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 1rem 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .summary-item {
        padding: 0.5rem 1rem;
        transition: var(--transition);
        border-right: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .summary-item:last-child {
        border-right: none;
    }
    
    .summary-item:hover {
        transform: translateY(-2px);
    }
    
    /* Enhanced Search Bar */
    .search-container {
        position: relative;
        z-index: 2;
    }
    
    .search-container .input-group {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .search-container .input-group:focus-within {
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        transform: translateY(-1px);
    }
    
    .search-container .form-control {
        border: none;
        padding: 0.75rem 1rem;
        background: transparent;
    }
    
    /* Enhanced Table Design */
    .table {
        margin: 0;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .table thead th {
        background: #f8fafc;
        padding: 1rem;
        font-weight: 600;
        color: #475569;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        transition: var(--transition);
    }
    
    .table tbody tr:hover td {
        background-color: #f8fafc;
    }
    
    /* Enhanced User Avatar */
    .avatar-initial {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--primary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
        text-transform: uppercase;
        transition: var(--transition);
    }
    
    tr:hover .avatar-initial {
        transform: scale(1.05) rotate(-3deg);
    }
    
    /* Enhanced Role Badges */
    .role-badge {
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        transition: var(--transition);
    }
    
    .role-badge.role-admin {
        background-color: #fee2e2;
        color: #ef4444;
    }
    
    .role-badge.role-teknisi {
        background-color: #dcfce7;
        color: #10b981;
    }
    
    .role-badge.role-logistik {
        background-color: #fef3c7;
        color: #f59e0b;
    }
    
    .role-badge.role-default {
        background-color: #e0e7ff;
        color: #6366f1;
    }
    
    /* Enhanced Action Buttons */
    .action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        border-radius: 8px;
        transition: var(--transition);
        margin: 0 0.2rem;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
    }
    
    .action-btn i {
        font-size: 1rem;
    }
    
    /* Enhanced Modal/Offcanvas */
    .offcanvas {
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        border: none;
        box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
    }
    
    .offcanvas-header {
        background: var(--primary-gradient);
        padding: 2rem;
        border-bottom: none;
    }
    
    .offcanvas-body {
        padding: 2rem;
    }
    
    /* Form Controls */
    .form-control, .form-select {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        border-color: #e2e8f0;
        transition: var(--transition);
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    
    .form-text {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .header-with-pattern {
            padding: 1.5rem;
        }
        
        .stats-summary {
            display: none;
        }
        
        .search-container {
            width: 100%;
            margin-top: 1rem;
        }
        
        .table thead {
            display: none;
        }
        
        .table, .table tbody, .table tr, .table td {
            display: block;
            width: 100%;
        }
        
        .table tr {
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
        }
        
        .table td::before {
            content: attr(data-label);
            position: absolute;
            left: 1rem;
            width: 45%;
            text-align: left;
            font-weight: 600;
        }
    }
</style>
@endsection

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
                                    <span class="text-muted small d-block">Total User</span>
                                    <span class="fw-semibold">{{ $user->where('roles_id', '!=', 8)->count() }}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="text-muted small d-block">Admin</span>
                                    <span class="fw-semibold text-danger">
                                        {{ $user->where('roles_id', 1)->count() }}
                                    </span>
                                </div>
                                <div class="summary-item">
                                    <span class="text-muted small d-block">Teknisi</span>
                                    <span class="fw-semibold text-success">
                                        {{ $user->where('roles_id', 2)->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="search-container">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class='bx bx-search text-muted'></i>
                                </span>
                                <input type="text" id="search" class="form-control border-start-0 ps-0"
                                placeholder="Cari user..." aria-label="Search" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-end align-items-center mb-4 gap-4">
                    <div class="row">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBoth">
                            <i class="bx bx-user me-1"></i> Tambah User
                        </button>
                    </div>
                    <div class="row">
                        <a href="/log/aktivitas" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lihat Log Users">
                            <button type="button" class="btn btn-outline-danger btn-sm">
                                <i class='bx bx-clipboard me-1'></i> Log Users
                            </button>
                        </a>
                    </div>
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
                                    <span class="role-badge @if($u->roles->name == 'Admin') role-admin @elseif($u->roles->name == 'Teknisi') role-teknisi @elseif($u->roles->name == 'Logistik') role-logistik @else role-default @endif">
                                    {{ $u->roles->name }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm">
                                    <i class="bx bx-trash"></i>
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
