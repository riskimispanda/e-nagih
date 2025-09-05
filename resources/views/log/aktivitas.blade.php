@extends('layouts.contentNavbarLayout')

@section('title', 'Log Aktivitas')

@section('content')

<nav class="breadcrumb-nav">
    <ul class="breadcrumb breadcrumb-transparent breadcrumb-style2 mb-3">
        <li class="breadcrumb-item"><a href="/">Home</a></li>
        <li class="breadcrumb-item"><a href="/user/management">User Management</a></li>
        <li class="breadcrumb-item active text-primary" aria-current="page">Log Users</li>
    </ul>
</nav>

<div class="row">
    <div class="col-12">
        <div class="row mb-4">
            <div class="col-12">
                <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h4 class="fw-bold text-dark mb-2">Log Aktivitas</h4>
                    <p class="text-muted mb-0">Kelola dan pantau aktivitas pengguna</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title">Daftar Aktivitas</h5>
            <p class="card-text">Berikut adalah daftar aktivitas users yang tercatat dalam sistem.</p>
        </div>
        {{-- Filter bar --}}
        <div class="card-header border-bottom">
            <form method="GET" id="filterForm" class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchInput" name="search"
                    placeholder="Search..."
                    value="{{ request('search') }}">
                </div>
                
                <div class="col-md-4">
                    <select class="form-select" id="filterRole" name="roles" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Role</option>
                        @foreach($role as $roles)
                        <option value="{{ $roles->id }}" {{ (string)request('roles') === (string)$roles->id ? 'selected' : '' }}>
                            {{ $roles->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-4">
                    <input type="date" class="form-control" id="filterDate" name="date"
                    value="{{ request('date') }}"
                    onchange="document.getElementById('filterForm').submit()">
                </div>
                
                {{-- Simpan per_page saat submit filter --}}
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>
        
        
        <div class="card-body">
            <div class="table-responsive mt-5">
                <table class="table table-hover" id="activityTable">
                    <thead class="table-dark text-center">
                        <tr>
                            <th class="text-white">Waktu</th>
                            <th class="text-white">User</th>
                            <th class="text-white">Role</th>
                            <th class="text-white">Aktivitas</th>
                            <th class="text-white">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="text-center">
                            <td>
                                <span class="badge bg-warning bg-opacity-10 text-dark">
                                    {{ $log->updated_at->format('d-M-Y H:i') }}
                                </span>
                            </td>
                            <td>{{ $log->causer->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    {{ $log->causer->roles->name ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $log->description }}</td>
                            <td>
                                <a href="/logs-detail/{{ $log->id }}" class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Detail Aktivitas">
                                    <i class="bx bx-info-circle"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-dark mt-3 mb-2">Tidak ada data</h5>
                                    <p class="text-muted mb-0">Belum ada Aktivitas</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    
                </table>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="data-count-info">
                        Menampilkan <span class="count-highlight">{{ $logs->firstItem() ?? 0 }}</span> -
                        <span class="count-highlight">{{ $logs->lastItem() ?? 0 }}</span> dari
                        <span class="count-highlight">{{ $logs->total() }}</span> data
                    </div>
                
                    <form method="GET" id="perPageForm" class="d-flex align-items-center gap-2">
                        {{-- Bawa semua filter saat ganti per_page --}}
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="roles"  value="{{ request('roles') }}">
                        <input type="hidden" name="date"   value="{{ request('date') }}">
                
                        <label for="pageSize" class="mb-0 mt-3">Data per halaman:</label>
                        <select id="pageSize" name="per_page" class="form-select form-select-sm" onchange="document.getElementById('perPageForm').submit()">
                            @foreach([10,25,50,100,250,500] as $size)
                                <option value="{{ $size }}" {{ (int)request('per_page',10)===$size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="justify-content-end mt-3">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    function changePageSize(select) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', select.value);
        
        // Biar page balik ke 1 saat ganti jumlah data
        url.searchParams.delete('page');
        
        // Pastikan filter yang sudah dipilih tetap ada
        const search = document.getElementById('searchInput').value;
        const role   = document.getElementById('filterRole').value;
        const date   = document.getElementById('filterDate').value;
        
        if (search) url.searchParams.set('search', search);
        if (role)   url.searchParams.set('roles', role);
        if (date)   url.searchParams.set('date', date);
        
        window.location.href = url.toString();
    }
</script>
<script>
    // Submit search saat tekan Enter
    document.getElementById('searchInput').addEventListener('keydown', function(e){
        if(e.key === 'Enter'){
            document.getElementById('filterForm').submit();
        }
    });

    // Optional: auto-submit 500ms setelah user berhenti mengetik
    let t;
    document.getElementById('searchInput').addEventListener('input', function(){
        clearTimeout(t);
        t = setTimeout(()=> document.getElementById('filterForm').submit(), 500);
    });
</script>

@endsection