@extends('layouts.contentNavbarLayout')

@section('title', 'Dashboard Log Aktivitas')

<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
    * { box-sizing: border-box; }

    .panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        transition: box-shadow 0.2s ease, transform 0.15s ease;
    }
    .panel.hoverable:hover {
        box-shadow: 0 6px 24px rgba(0,0,0,0.06);
        transform: translateY(-2px);
    }

    .panel-header {
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid #f1f3f5;
    }
    .panel-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }
    .panel-sub {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 2px;
    }

    .filter-input {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 0 12px 0 40px;
        font-size: 0.8125rem;
        color: #374151;
        background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
        width: 100%;
    }
    .filter-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
    }
    .filter-input::placeholder { color: #9ca3af; }

    .filter-select {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 0 34px 0 40px;
        font-size: 0.8125rem;
        color: #374151;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%236b7280'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right 12px center;
        appearance: none;
        -webkit-appearance: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        width: 100%;
        cursor: pointer;
    }
    .filter-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
    }

    .field-icon {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1.05rem;
        pointer-events: none;
    }

    .btn-filter {
        height: 42px;
        padding: 0 18px;
        border-radius: 10px;
        font-size: 0.8125rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .btn-primary { background: #3b82f6; color: #fff; border-color: #3b82f6; box-shadow: 0 2px 8px rgba(59,130,246,0.25); }
    .btn-primary:hover { background: #2563eb; border-color: #2563eb; }
    .btn-ghost { background: #fff; color: #6b7280; border-color: #d1d5db; }
    .btn-ghost:hover { background: #f9fafb; color: #374151; }

    .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    .log-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
    .log-table thead th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    .log-table tbody tr { border-bottom: 1px solid #f1f3f5; transition: background 0.15s; }
    .log-table tbody tr:hover { background: #f7fbff; }
    .log-table tbody td { padding: 12px 16px; color: #374151; vertical-align: middle; }
    .log-table tbody tr:last-child { border-bottom: none; }

    .cell-time {
        font-variant-numeric: tabular-nums;
        line-height: 1.45;
    }
    .cell-time .t-date { color: #374151; font-weight: 500; }
    .cell-time .t-time { color: #9ca3af; font-size: 0.6875rem; }

    .cell-user { display: flex; align-items: center; gap: 10px; }
    .cell-user .u-name { font-weight: 500; color: #111827; white-space: nowrap; }
    .cell-user .u-email { color: #9ca3af; font-size: 0.6875rem; white-space: nowrap; }

    .cell-desc { display: flex; align-items: flex-start; gap: 8px; line-height: 1.5; }
    .cell-desc .d-icon {
        flex-shrink: 0; width: 20px; height: 20px; margin-top: 1px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8125rem; border-radius: 6px;
    }

    .avatar {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 600; font-size: 0.75rem; color: #fff; flex-shrink: 0;
    }

    .role-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 9999px;
        font-size: 0.6875rem; font-weight: 600; white-space: nowrap; line-height: 1.5;
        border: 1px solid; vertical-align: middle;
    }
    .role-badge.superadmin { background: #eef2ff; color: #4f46e5; border-color: #c7d2fe; }
    .role-badge.admin       { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .role-badge.noc         { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .role-badge.teknisi     { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .role-badge.sales       { background: #fdf2f8; color: #be185d; border-color: #fbcfe8; }
    .role-badge.default     { background: #f3f4f6; color: #374151; border-color: #e5e7eb; }

    .chip {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 9999px;
        font-size: 0.6875rem; font-weight: 500; white-space: nowrap; line-height: 1.5;
        background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb;
    }

    .pagination { display: flex; list-style: none; padding: 0; margin: 0; gap: 4px; flex-wrap: wrap; align-items: center; }
    .pagination li a, .pagination li span {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 34px; height: 34px; padding: 0 10px;
        border: 1px solid #e5e7eb; border-radius: 8px;
        color: #6b7280; text-decoration: none; font-weight: 500; font-size: 0.8125rem; background: #fff;
        transition: all 0.15s;
    }
    .pagination li a:hover { background: #f0f7ff; border-color: #3b82f6; color: #3b82f6; }
    .pagination li.active span { background: #3b82f6; border-color: #3b82f6; color: #fff; }
    .pagination li.disabled span { background: #f9fafb; color: #d1d5db; cursor: not-allowed; border-color: #f3f4f6; }

    .fade-in { animation: fadeInUp 0.4s ease both; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* Fallback: jika animasi tidak berjalan, pastikan teks tetap terlihat */
    .fade-in, .fade-in * { opacity: 1 !important; }
    @media (prefers-reduced-motion: no-preference) {
        .fade-in, .fade-in * { opacity: 1; }
    }

    .stat-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 20px; display: flex; align-items: center; gap: 15px; transition: all 0.2s; position: relative; overflow: hidden; }
    .stat-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.06); transform: translateY(-2px); }
    .stat-card::after { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
    .stat-card.blue::after { background: #3b82f6; }
    .stat-card.amber::after { background: #f59e0b; }
    .stat-card.emerald::after { background: #10b981; }
    .stat-card.violet::after { background: #8b5cf6; }
    .stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0; }

    .trend-up { color: #10b981; background: #d1fae5; }
    .chart-bar { display: flex; align-items: flex-end; gap: 8px; height: 150px; padding-top: 8px; }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; height: 100%; justify-content: flex-end; }
    .bar-fill { width: 100%; max-width: 40px; border-radius: 6px 6px 2px 2px; position: relative; transition: height 0.6s ease; background: #3b82f6; min-height: 4px; }
    .bar-fill:hover { filter: brightness(0.95); }
    .bar-label { font-size: 0.625rem; color: #9ca3af; font-weight: 500; }
    .bar-value { font-size: 0.6875rem; color: #111827; font-weight: 600; }

    .user-rank { display: flex; align-items: center; gap: 10px; padding: 8px 0; }
    .user-rank + .user-rank { border-top: 1px dashed #f1f3f5; }
    .rank-no { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; flex-shrink: 0; }
    .rank-badge { margin-left: auto; font-size: 0.6875rem; font-weight: 600; color: #3b82f6; background: #eff6ff; padding: 2px 10px; border-radius: 9999px; white-space: nowrap; }

    .empty-state { padding: 48px 16px; text-align: center; }

    @media (max-width: 640px) {
        .log-table thead { display: none; }
        .log-table tbody tr { display: block; padding: 14px 16px; margin-bottom: 6px; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; }
        .log-table tbody td { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 7px 0; border: none; font-size: 0.8125rem; }
        .log-table tbody td::before { content: attr(data-label); font-weight: 600; color: #6b7280; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.03em; flex-shrink: 0; }
        .cell-time { text-align: right; }
        .stat-card { padding: 16px; }
    }
</style>

@section('content')
<div class="space-y-6 fade-in">

    <!-- Page Header -->
    <div class="panel px-5 py-4 fade-in" style="animation-delay:0s; display:flex; flex-direction:column; gap:16px; background:#fff;">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:#eff6ff; color:#1d4ed8; box-shadow:0 2px 8px rgba(59,130,246,0.15);">
                        <i class="bx bx-pulse" style="font-size:1.4rem;"></i>
                    </div>
                    <div class="flex flex-col justify-center leading-none">
                        <h1 class="text-xl font-bold text-gray-900" style="line-height:1.15;">Log Aktivitas</h1>
                        <p class="text-sm text-gray-500 mt-1" style="line-height:1.2;">Dashboard pemantauan aktivitas pengguna</p>
                    </div>
                </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2 text-xs bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 w-fit"
                     style="box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="font-semibold text-gray-800">Sistem Aktif</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-500">{{ now()->format('d M Y') }}</span>
                    <span class="text-gray-400">{{ now()->format('H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Accent strip bawah -->
        <div class="h-1 rounded-full w-full" style="background:#3b82f6;"></div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="stat-card blue fade-in" style="animation-delay:.02s">
            <div class="stat-icon bg-amber-50 text-amber-500"><i class="bx bx-clipboard"></i></div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 font-medium">Total Aktivitas</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($logs->total()) }}</p>
                    <span class="trend-up text-xs font-semibold px-1.5 py-0.5 rounded-full flex items-center gap-0.5"><i class="bx bx-trending-up"></i>Total</span>
                </div>
            </div>
        </div>
        <div class="stat-card amber fade-in" style="animation-delay:.06s">
            <div class="stat-icon bg-blue-50 text-blue-500"><i class="bx bx-calendar-check"></i></div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 font-medium">Hari Ini</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalToday) }}</p>
                    <span class="text-xs text-gray-400 font-medium">aktivitas</span>
                </div>
            </div>
        </div>
        <div class="stat-card emerald fade-in" style="animation-delay:.1s">
            <div class="stat-icon bg-emerald-50 text-emerald-500"><i class="bx bx-user-check"></i></div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 font-medium">Pengguna Aktif</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-gray-900">{{ $topUsers->count() }}</p>
                    <span class="text-xs text-gray-400 font-medium">user (7 hari)</span>
                </div>
            </div>
        </div>
        <div class="stat-card violet fade-in" style="animation-delay:.14s">
            <div class="stat-icon bg-violet-50 text-violet-500"><i class="bx bx-time-five"></i></div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 font-medium">Terakhir Update</p>
                <p class="text-sm font-bold text-gray-900 leading-snug">
                    {{ $recentUpdate ? $recentUpdate->created_at->diffForHumans() : '-' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Activity Chart -->
        <div class="panel lg:col-span-2 fade-in" style="animation-delay:.18s">
            <div class="panel-header">
                <div>
                    <div class="panel-title flex items-center gap-2">
                        <i class="bx bx-bar-chart-alt-2 text-blue-500 text-lg"></i>
                        Tren Aktivitas 7 Hari
                    </div>
                    <p class="panel-sub">Jumlah aktivitas per hari</p>
                </div>
                <span class="chip" style="background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;">7 hari terakhir</span>
            </div>
            <div class="p-5">
                <div class="chart-bar">
                    @foreach($chartData as $i => $val)
                    @php
                        $h = $val > 0 ? max(8, round($val / $activityMax * 100)) : 4;
                        $gray = $val <= 0;
                    @endphp
                    <div class="bar-col">
                        <span class="bar-value" style="{{ $gray ? 'color:#9ca3af' : '' }}">{{ $val }}</span>
                        <div class="bar-fill" style="height: {{ $h }}%; {{ $gray ? 'background:#d1d5db' : '' }}"
                             title="{{ $chartLabels[$i] ?? '' }}: {{ $val }} aktivitas"></div>
                        <span class="bar-label">{{ $chartLabels[$i] ?? '' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Users -->
        <div class="panel fade-in" style="animation-delay:.22s">
            <div class="panel-header">
                <div>
                    <div class="panel-title flex items-center gap-2">
                        <i class="bx bx-trophy text-amber-500 text-lg"></i>
                        Pengguna Paling Aktif
                    </div>
                    <p class="panel-sub">Top 5 aktivitas dalam 7 hari</p>
                </div>
            </div>
            <div class="p-5">
                @forelse($topUsers->take(5) as $name => $count)
                @php
                    $initial = strtoupper(substr($name, 0, 1) ?: '?');
                    $colors = ['#6366f1','#3b82f6','#10b981','#f59e0b','#ef4444'];
                @endphp
                <div class="user-rank">
                    <div class="avatar" style="background: {{ $colors[$loop->index % 5] }}">{{ $initial }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-sm text-gray-900 truncate">{{ $name }}</p>
                        <p class="text-xs text-gray-400">aktivitas 7 hari</p>
                    </div>
                    <span class="rank-badge">{{ number_format($count) }} <i class="bx bx-line-chart ml-0.5"></i></span>
                </div>
                @empty
                <div class="text-center text-gray-400 text-sm py-6">Belum ada data</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Table Panel -->
    <div class="panel fade-in" style="animation-delay:.26s">
        <!-- Header -->
        <div class="panel-header">
            <div>
                <div class="panel-title flex items-center gap-2">
                    <i class="bx bx-list-ul text-gray-500 text-lg"></i>
                    Detail Log Aktivitas
                </div>
                <p class="panel-sub">Riwayat aktivitas pengguna yang tercatat sistem</p>
            </div>

            <!-- Role breakdown quick chips -->
            @if($perRole->isNotEmpty())
            <div class="hidden lg:flex items-center gap-2 flex-wrap">
                @foreach($perRole->take(4) as $name => $count)
                <span class="chip">
                    {{ $name }}
                    <strong>{{ number_format($count) }}</strong>
                </span>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Filters -->
        <div class="px-5 py-4 bg-gray-50 border-b border-gray-100">
            <form method="GET" id="filterForm" class="flex flex-col lg:flex-row lg:items-center gap-3">
                <div class="relative flex-1 min-w-0">
                    <i class="bx bx-search field-icon"></i>
                    <input type="text" id="searchInput" name="search" class="filter-input"
                           placeholder="Cari nama user atau aktivitas..." value="{{ request('search') }}">
                </div>

                <div class="relative w-full sm:w-48 flex-shrink-0">
                    <i class="bx bx-shield-quarter field-icon"></i>
                    <select id="filterRole" name="roles" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Role</option>
                        @foreach($role as $roles)
                        <option value="{{ $roles->id }}" {{ (string)request('roles') === (string)$roles->id ? 'selected' : '' }}>
                            {{ $roles->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="relative w-full sm:w-44 flex-shrink-0">
                    <i class="bx bx-calendar field-icon"></i>
                    <input type="date" id="filterDate" name="date" class="filter-input"
                           value="{{ request('date') }}" onchange="this.form.submit()">
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <button type="submit" class="btn-filter btn-primary hidden md:inline-flex">
                        <i class="bx bx-search"></i> Cari
                    </button>
                    <button type="button" onclick="resetFilters()" class="btn-filter btn-ghost">
                        <i class="bx bx-reset"></i> Reset
                    </button>
                </div>

                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>

        <!-- Toolbar: info + per page -->
        <div class="px-5 py-2.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 bg-white">
            <p class="text-xs text-gray-500">
                Menampilkan data log aktivitas
            </p>
            <form method="GET" id="perPageForm" class="flex items-center gap-2">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="roles" value="{{ request('roles') }}">
                <input type="hidden" name="date" value="{{ request('date') }}">
                <label class="text-xs text-gray-500 font-medium whitespace-nowrap">Tampilkan</label>
                <select name="per_page" class="filter-select" style="width:74px; padding-left:12px; padding-right:30px; height:34px;" onchange="this.form.submit()">
                    @foreach([10,25,50,100,250,500] as $size)
                    <option value="{{ $size }}" {{ (int)request('per_page',10)===$size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <label class="text-xs text-gray-500 font-medium whitespace-nowrap">baris</label>
            </form>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="log-table">
                <thead>
                    <tr>
                        <th style="width:180px">Waktu</th>
                        <th style="width:220px">Pengguna</th>
                        <th style="width:140px">Role</th>
                        <th>Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="fade-in" style="animation-delay: {{ $loop->index * 0.02 }}s">
                        <td data-label="Waktu">
                            <div class="cell-time">
                                <div class="t-date">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="t-time">{{ $log->created_at->format('H:i') }} WIB</div>
                            </div>
                        </td>
                        <td data-label="Pengguna">
                            <div class="cell-user">
                                <div class="avatar" style="background:#6b7280">{{ strtoupper(substr($log->causer->name ?? 'U', 0, 1)) }}</div>
                                <div class="min-w-0">
                                    <p class="u-name truncate">{{ $log->causer->name ?? '-' }}</p>
                                    <p class="u-email truncate">{{ $log->causer->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td data-label="Role">
                            @php
                                $roleName = $log->causer->roles->name ?? '-';
                                $roleClassMap = [
                                    'Super Admin' => 'superadmin',
                                    'Admin'       => 'admin',
                                    'NOC'         => 'noc',
                                    'Teknisi'     => 'teknisi',
                                    'Sales'       => 'sales',
                                ];
                                $roleClass = $roleClassMap[$roleName] ?? 'default';
                            @endphp
                            <span class="role-badge {{ $roleClass }}">{{ $roleName }}</span>
                        </td>
                        <td data-label="Aktivitas">
                            @php
                                $desc = $log->description ?? '';
                                $isLogin = str_contains($desc, 'login') || str_contains($desc, 'masuk');
                                $iconCls = $isLogin ? 'bx-log-in-circle' : 'bx-check-circle';
                                $iconColor = $isLogin ? 'color:#047857;background:#ecfdf5' : 'color:#1d4ed8;background:#eff6ff';
                            @endphp
                            <div class="cell-desc">
                                <i class="bx d-icon {{ $iconCls }}" style="{{ $iconColor }}"></i>
                                <span class="text-gray-700">{{ $desc }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="bx bx-receipt text-gray-400 text-2xl"></i>
                                </div>
                                <p class="font-medium text-gray-900 text-sm">Tidak ada data ditemukan</p>
                                <p class="text-gray-400 text-xs mt-1">Coba ubah filter atau kata kunci pencarian</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <i class="bx bx-data text-blue-400"></i>
                    <span>Menampilkan <strong class="text-gray-700">{{ $logs->firstItem() }}</strong>–<strong class="text-gray-700">{{ $logs->lastItem() }}</strong> dari <strong class="text-gray-700">{{ number_format($logs->total()) }}</strong> data</span>
                </div>
            </div>

            <div class="mt-3 flex justify-center">{{ $logs->links() }}</div>
        </div>
        @endif
    </div>
</div>

<script>
(function(){
    var searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keydown', function(e){
        if(e.key === 'Enter'){ e.preventDefault(); document.getElementById('filterForm').submit(); }
    });
    var timer;
    searchInput.addEventListener('input', function(){
        clearTimeout(timer);
        var val = this.value.trim();
        timer = setTimeout(function(){ document.getElementById('filterForm').submit(); }, 500);
    });
    window.resetFilters = function(){ window.location.href = window.location.pathname; };
    document.querySelectorAll('#filterForm, #perPageForm').forEach(function(f){
        f.addEventListener('submit', function(){
            var btn = this.querySelector('button[type="submit"]');
            if(btn){ btn.innerHTML = '<i class="bx bx-loader-alt animate-spin"></i> Memproses'; btn.disabled = true; }
        });
    });
})();
</script>
@endsection
