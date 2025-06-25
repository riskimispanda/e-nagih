@extends('layouts.contentNavbarLayout')

@section('title', 'Log Aktivitas')

@section('content')
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
            <div class="card-header border-bottom">
                <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
                    <div class="col-md-4 user_role">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                    </div>
                    <div class="col-md-4 user_plan">
                        <select class="form-select" id="filterRole">
                            <option value="">Filter Role</option>
                            <option value="Admin">Admin</option>
                            <option value="User">User</option>
                        </select>
                    </div>
                    <div class="col-md-4 user_status">
                        <input type="date" class="form-control" id="filterDate">
                    </div>
                </div>
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
                                        {{$log->updated_at}}
                                    </span>
                                </td>
                                <td>{{$log->causer->name}}</td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        {{$log->causer->roles->name}}
                                    </span>
                                </td>
                                <td>{{$log->description}}</td>
                                <td>
                                    <a href="/logs-detail/{{ $log->id }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Detail Aktivitas">
                                        <i class="bx bx-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
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
                    <div class="justify-content-end mt-5 me-2">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection