@extends('layouts.contentNavbarLayout')

@section('title', 'Manajemen User')

@php
  // ===== Ikon & warna badge per role (sinkron dengan tabel roles) =====
  $roleIcons = [
    1 => 'bx-crown',
    2 => 'bx-wallet',
    3 => 'bx-package',
    4 => 'bx-server',
    5 => 'bx-wrench',
    6 => 'bx-store',
    7 => 'bx-headset',
  ];
  $roleBadgeStyles = [
    1 => 'bg-violet-50 text-violet-700 border-violet-200',
    2 => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    3 => 'bg-amber-50 text-amber-700 border-amber-200',
    4 => 'bg-sky-50 text-sky-700 border-sky-200',
    5 => 'bg-cyan-50 text-cyan-700 border-cyan-200',
    6 => 'bg-teal-50 text-teal-700 border-teal-200',
    7 => 'bg-rose-50 text-rose-700 border-rose-200',
  ];
  $defaultBadgeStyle = 'bg-slate-100 text-slate-700 border-slate-200';
  $hasFilter = $search !== '' || ($roleFilter !== null && $roleFilter !== '');
@endphp

@section('page-style')
  {{-- Tailwind CDN: mengikuti pola halaman modern lain (preflight dimatikan agar tidak menimpa Bootstrap/Sneat) --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      corePlugins: {
        preflight: false
      }
    };
  </script>
  <style>
    /* Preflight Tailwind dimatikan — pastikan utility border tetap terlihat */
    [class~="border"],
    [class*="border-"] {
      border-style: solid;
    }

    /* ====== Kartu ringkasan role (hover lift ala dashboard) ====== */
    .kpi-card {
      transition: transform .2s cubic-bezier(.4, 0, .2, 1), box-shadow .2s cubic-bezier(.4, 0, .2, 1),
        border-color .15s ease, background-color .15s ease;
    }

    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .05), 0 4px 6px -4px rgba(0, 0, 0, .025);
    }

    /* ====== Select filter dengan panah kustom ====== */
    .filter-select {
      -webkit-appearance: none;
      appearance: none;
      padding-right: 2rem !important;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 0.7rem center;
      background-size: 0.85rem;
    }

    /* ====== Pagination ====== */
    .user-pagination .pagination {
      margin-bottom: 0;
      gap: 4px;
      flex-wrap: wrap;
    }

    .user-pagination .page-item .page-link {
      border-radius: 10px !important;
      border: 1px solid #e2e8f0;
      color: #475569;
      font-size: 12px;
      font-weight: 600;
      padding: 6px 11px;
      transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .user-pagination .page-item .page-link:hover {
      background: #eef2ff;
      border-color: #c7d2fe;
      color: #4338ca;
    }

    .user-pagination .page-item.active .page-link {
      background: #4f46e5;
      border-color: #4f46e5;
      color: #fff;
    }

    .user-pagination .page-item.disabled .page-link {
      color: #cbd5e1;
      background: #f8fafc;
      border-color: #f1f5f9;
    }

    /* ====== Modal & offcanvas ====== */
    .user-modal .modal-content,
    .user-offcanvas.offcanvas {
      border: 0;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 18px 45px -12px rgba(15, 23, 42, .25);
    }

    .user-offcanvas.offcanvas {
      border-radius: 0 16px 16px 0;
    }

    .user-modal .modal-body::-webkit-scrollbar,
    .user-offcanvas .offcanvas-body::-webkit-scrollbar {
      width: 6px;
    }

    .user-modal .modal-body::-webkit-scrollbar-thumb,
    .user-offcanvas .offcanvas-body::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 10px;
    }

    /* ====== Animasi baris tabel ====== */
    @keyframes fadeRowIn {
      from {
        opacity: 0;
        transform: translateY(4px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .user-row {
      animation: fadeRowIn .25s ease both;
    }

    @media (prefers-reduced-motion: reduce) {
      .user-row {
        animation: none !important;
      }
    }
  </style>
@endsection

@section('content')
  <div class="space-y-5">

    {{-- ==================== HEADER HALAMAN ==================== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="min-w-0">
          <div
            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-[11px] font-bold uppercase tracking-wider">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
            {{ $roles?->name ?? 'Pengguna' }}
          </div>
          <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight mt-2 mb-0">Manajemen User</h1>
          <p class="text-slate-500 text-xs sm:text-sm mt-1 mb-0">
            Kelola akun pengguna, role, dan hak akses sistem.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <div
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 text-xs font-semibold">
            <i class="bx bx-group text-base text-indigo-500"></i>
            Total <span class="font-extrabold text-slate-800">{{ number_format($user->total(), 0, ',', '.') }}</span> user
          </div>
          <button type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition-colors cursor-pointer border-0">
            <i class="bx bx-user-plus text-base"></i> Tambah User
          </button>
        </div>
      </div>
    </div>

    {{-- ==================== RINGKASAN ROLE ==================== --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-3">
      @php
        $allActive = $roleFilter === null || $roleFilter === '';
        $allQuery = array_filter(['q' => $search !== '' ? $search : null]);
      @endphp

      <a href="{{ route('user', $allQuery) }}"
        class="group kpi-card bg-white rounded-2xl border p-3.5 flex flex-col justify-between gap-2 no-underline {{ $allActive ? 'border-indigo-300 bg-indigo-50/60' : 'border-slate-200 hover:border-slate-300' }}">
        <span class="flex items-center justify-between gap-2">
          <span
            class="w-8 h-8 rounded-xl flex items-center justify-center text-lg shrink-0 {{ $allActive ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200' }}">
            <i class="bx bx-group"></i>
          </span>
          <i class="bx bx-right-arrow-alt text-base text-slate-300 group-hover:text-slate-400"></i>
        </span>
        <span>
          <span class="block text-lg font-extrabold text-slate-800 tabular-nums leading-none">{{ number_format($user->total(), 0, ',', '.') }}</span>
          <span class="block text-[11px] font-bold text-slate-500 mt-1">Semua Role</span>
        </span>
      </a>

      @foreach ($role as $r)
        @php $isActive = (string) $roleFilter === (string) $r->id; @endphp
        <a href="{{ route('user', array_filter(['role' => $r->id, 'q' => $search !== '' ? $search : null])) }}"
          class="group kpi-card bg-white rounded-2xl border p-3.5 flex flex-col justify-between gap-2 no-underline {{ $isActive ? 'border-indigo-300 bg-indigo-50/60' : 'border-slate-200 hover:border-slate-300' }}">
          <span class="flex items-center justify-between gap-2">
            <span
              class="w-8 h-8 rounded-xl flex items-center justify-center text-lg shrink-0 {{ $isActive ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200' }}">
              <i class="bx {{ $roleIcons[$r->id] ?? 'bx-shield' }}"></i>
            </span>
            <i class="bx bx-right-arrow-alt text-base text-slate-300 group-hover:text-slate-400"></i>
          </span>
          <span>
            <span
              class="block text-lg font-extrabold text-slate-800 tabular-nums leading-none">{{ number_format($roleSummary[$r->id] ?? 0, 0, ',', '.') }}</span>
            <span class="block text-[11px] font-bold text-slate-500 mt-1 truncate">{{ $r->name }}</span>
          </span>
        </a>
      @endforeach
    </div>

    {{-- ==================== DAFTAR USER ==================== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div
        class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div>
          <h2 class="text-base font-extrabold text-slate-800 tracking-tight mb-0">Daftar Pengguna</h2>
          <p class="text-slate-500 text-xs mt-1 mb-0">
            @if ($user->total() > 0)
              Menampilkan {{ $user->firstItem() }}–{{ $user->lastItem() }} dari {{ $user->total() }} user
              @if ($hasFilter)
                <span class="text-indigo-600 font-semibold">(terfilter)</span>
              @endif
            @else
              Tidak ada user yang cocok dengan filter
            @endif
          </p>
        </div>

        <form method="GET" action="{{ route('user') }}" id="userFilterForm" class="flex flex-wrap items-center gap-2">
          <div class="relative">
            <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none"
              style="transform: translateY(-50%);"></i>
            <input type="search" name="q" id="userSearch" value="{{ $search }}" autocomplete="off"
              placeholder="Cari nama, email, no HP…"
              class="w-full sm:w-64 py-2 pr-3 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:outline-none focus:border-indigo-400"
              style="padding-left: 2.25rem; box-shadow: none;">
          </div>

          <select name="role" id="userRoleFilter"
            class="filter-select py-2 px-3 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:outline-none focus:border-indigo-400 cursor-pointer">
            <option value="">Semua Role</option>
            @foreach ($role as $r)
              <option value="{{ $r->id }}" @selected((string) $roleFilter === (string) $r->id)>{{ $r->name }}</option>
            @endforeach
          </select>

          @if ($hasFilter)
            <a href="{{ route('user') }}"
              class="inline-flex items-center gap-1 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-semibold no-underline transition-colors">
              <i class="bx bx-x text-base"></i> Reset
            </a>
          @endif
        </form>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm mb-0" style="min-width: 760px;">
          <thead>
            <tr class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
              <th class="px-4 py-3 text-left font-bold border-b border-slate-200" style="width: 60px;">No</th>
              <th class="px-4 py-3 text-left font-bold border-b border-slate-200">Pengguna</th>
              <th class="px-4 py-3 text-left font-bold border-b border-slate-200">Kontak</th>
              <th class="px-4 py-3 text-left font-bold border-b border-slate-200" style="width: 200px;">Role</th>
              <th class="px-4 py-3 text-center font-bold border-b border-slate-200" style="width: 130px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($user as $u)
              <tr class="user-row border-b border-slate-100 hover:bg-slate-50/70 transition-colors"
                style="animation-delay: {{ $loop->index * 30 }}ms;">
                <td class="px-4 py-3 text-xs font-bold text-slate-400 tabular-nums">
                  {{ ($user->currentPage() - 1) * $user->perPage() + $loop->iteration }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div
                      class="relative w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 text-white text-sm font-bold flex items-center justify-center shrink-0 overflow-hidden shadow-sm">
                      <span>{{ strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                      @if (!empty($u->profile))
                        <img src="{{ asset($u->profile) }}" alt="{{ $u->name }}" loading="lazy"
                          class="absolute inset-0 w-full h-full object-cover" onerror="this.style.display='none'">
                      @endif
                    </div>
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-slate-800 truncate mb-0">{{ $u->name }}</p>
                      <p class="text-[11px] text-slate-400 truncate mb-0">ID: {{ $u->id }} • {{ $u->email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <p class="text-xs font-semibold text-slate-700 mb-0 truncate">
                    <i class="bx bx-phone text-slate-400 me-1"></i>{{ $u->no_hp ?: '—' }}
                  </p>
                  <p class="text-[11px] text-slate-400 mb-0 truncate" style="max-width: 220px;">
                    <i class="bx bx-map text-slate-400 me-1"></i>{{ $u->alamat ?: '—' }}
                  </p>
                </td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[11px] font-bold {{ $roleBadgeStyles[$u->roles_id] ?? $defaultBadgeStyle }}">
                    <i class="bx {{ $roleIcons[$u->roles_id] ?? 'bx-shield' }} text-sm"></i>
                    {{ $u->roles?->name ?? 'Tanpa Role' }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-1.5">
                    <a href="{{ url('/profile-user/' . $u->id) }}" data-bs-toggle="tooltip" title="Lihat profil"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-colors no-underline">
                      <i class="bx bx-user-circle text-base"></i>
                    </a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalEditRole{{ $u->id }}"
                      title="Edit username, role &amp; password"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-amber-600 hover:border-amber-200 hover:bg-amber-50 transition-colors no-underline">
                      <i class="bx bx-edit-alt text-base"></i>
                    </a>
                    <a href="{{ url('/hapus/user/' . $u->id) }}" data-user="{{ $u->name }}" data-bs-toggle="tooltip"
                      title="Hapus user"
                      class="js-delete-user inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-colors no-underline">
                      <i class="bx bx-trash text-base"></i>
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-4 py-14">
                  <div class="flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mb-3">
                      <i class="bx bx-user-x"></i>
                    </div>
                    @if ($hasFilter)
                      <p class="text-sm font-semibold text-slate-600 mb-1">Tidak ada user yang cocok</p>
                      <p class="text-xs text-slate-400 mb-3">Coba kata kunci lain atau hapus filter yang aktif.</p>
                      <a href="{{ route('user') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-indigo-200 bg-indigo-50/70 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs transition-colors no-underline">
                        <i class="bx bx-reset text-base"></i> Reset Filter
                      </a>
                    @else
                      <p class="text-sm font-semibold text-slate-600 mb-1">Belum ada user</p>
                      <p class="text-xs text-slate-400 m-0">Tambahkan user pertama lewat tombol “Tambah User”.</p>
                    @endif
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($user->hasPages())
        <div class="user-pagination px-4 sm:px-5 py-4 border-t border-slate-100">
          {{ $user->links('pagination.user') }}
        </div>
      @endif
    </div>
  </div>

  {{-- ==================== OFF CANVAS: TAMBAH USER ==================== --}}
  <div class="offcanvas offcanvas-start user-offcanvas" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
    <div class="offcanvas-header" style="background: linear-gradient(135deg, #4f46e5 0%, #2563eb 60%, #0ea5e9 100%);">
      <div class="d-flex align-items-center">
        <div class="me-3 d-flex align-items-center justify-content-center"
          style="width: 42px; height: 42px; background-color: rgba(255, 255, 255, .18); border-radius: 12px;">
          <i class='bx bx-user-plus text-white fs-5'></i>
        </div>
        <div>
          <h5 id="offcanvasAddUserLabel" class="offcanvas-title text-white mb-0 fw-semibold">Tambah User Baru</h5>
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
            <i class='bx bx-user me-1 text-muted'></i>Username (Nama Login)
          </label>
          <input type="text" class="form-control" id="user_name" name="name" placeholder="Contoh: johndoe" minlength="3"
            maxlength="50" required>
          <div class="form-text">Username dipakai untuk login ke sistem — minimal 3 karakter.</div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium" for="user_email">
            <i class='bx bx-envelope me-1 text-muted'></i>Email
          </label>
          <input type="email" class="form-control" id="user_email" name="email" placeholder="contoh@email.com" required>
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium" for="user_nohp">
            <i class='bx bx-phone me-1 text-muted'></i>No HP
          </label>
          <input type="text" class="form-control" id="user_nohp" name="no_hp" placeholder="081234567890" required>
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium" for="user_alamat">
            <i class='bx bx-map me-1 text-muted'></i>Alamat
          </label>
          <input type="text" class="form-control" id="user_alamat" name="alamat" placeholder="Temanggung" required>
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium" for="user_role">
            <i class='bx bx-shield me-1 text-muted'></i>Role Pengguna
          </label>
          <select class="form-select" id="user_role" name="roles_id" required>
            <option selected disabled value="">Pilih Role</option>
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
            <input type="password" class="form-control" id="user_password" name="password" minlength="8"
              placeholder="Minimal 8 karakter" required>
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
              <i class='bx bx-show'></i>
            </button>
          </div>
          <div class="form-text">Password minimal 8 karakter dengan kombinasi huruf dan angka</div>
        </div>

        <div class="d-flex gap-2 mt-4">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="offcanvas">
            <i class='bx bx-x me-1'></i>Batal
          </button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bx bx-plus me-1"></i>Tambah User
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ==================== MODAL: EDIT ROLE & PASSWORD ==================== --}}
  @foreach ($user as $u)
    <div class="modal fade user-modal" id="modalEditRole{{ $u->id }}" tabindex="-1" aria-hidden="true"
      aria-labelledby="modalEditRoleLabel{{ $u->id }}">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0" style="background: linear-gradient(135deg, #4f46e5 0%, #2563eb 100%);">
            <div class="d-flex align-items-center gap-3">
              <div class="d-flex align-items-center justify-content-center"
                style="width: 42px; height: 42px; background-color: rgba(255,255,255,.18); border-radius: 12px;">
                <i class="bx bx-edit text-white fs-4"></i>
              </div>
              <div>
                <h5 class="modal-title text-white mb-0 fw-semibold" id="modalEditRoleLabel{{ $u->id }}">Edit User</h5>
                <small class="text-white-50">{{ $u->name }} • {{ $u->email }}</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <form action="/edit/role/{{ $u->id }}" method="POST">
            @csrf

            <div class="modal-body border-bottom" style="max-height: 60vh; overflow-y: auto;">
              <div class="row g-3 mb-3">
                <div class="col-sm-6">
                  <div class="rounded-3 border border-slate-200 bg-slate-50 p-3 h-100">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama</div>
                    <div class="text-sm font-semibold text-slate-800">{{ $u->name }}</div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="rounded-3 border border-slate-200 bg-slate-50 p-3 h-100">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">No HP</div>
                    <div class="text-sm font-semibold text-slate-800">{{ $u->no_hp ?: '—' }}</div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="rounded-3 border border-slate-200 bg-slate-50 p-3">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alamat</div>
                    <div class="text-sm font-semibold text-slate-800">{{ $u->alamat ?: '—' }}</div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-12 mb-4">
                  <label class="form-label" for="editUsername{{ $u->id }}">Username (Nama Login)</label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text text-primary"><i class='bx bx-user'></i></span>
                    <input type="text" class="form-control" id="editUsername{{ $u->id }}" name="name"
                      value="{{ $u->name }}" minlength="3" maxlength="50" required>
                  </div>
                  <div class="form-text">Username dipakai untuk login ke sistem — minimal 3 karakter dan harus unik.</div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-12 mb-4">
                  <label class="form-label">Role User</label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text text-primary"><i class='bx bx-shield-quarter'></i></span>
                    <select name="roles_id" class="form-select">
                      @foreach ($role as $r)
                        <option value="{{ $r->id }}" @selected($u->roles_id == $r->id)>{{ $r->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-12 mb-0">
                  <label class="form-label">Password Baru</label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-key text-primary"></i></span>
                    <input type="password" class="form-control" name="password" minlength="8"
                      placeholder="Kosongkan jika tidak diubah">
                  </div>
                  <div class="form-text">Isi hanya jika ingin mengganti password user ini.</div>
                </div>
              </div>
            </div>

            <div class="modal-footer border-0 gap-2">
              <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="bx bx-save me-1"></i>Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endforeach
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // ===== Pencarian & filter role (auto-submit dengan debounce) =====
      var filterForm = document.getElementById('userFilterForm');
      var searchInput = document.getElementById('userSearch');
      var roleSelect = document.getElementById('userRoleFilter');
      var debounceTimer = null;

      if (filterForm && searchInput) {
        searchInput.addEventListener('input', function () {
          clearTimeout(debounceTimer);
          debounceTimer = setTimeout(function () { filterForm.submit(); }, 450);
        });
      }

      if (filterForm && roleSelect) {
        roleSelect.addEventListener('change', function () { filterForm.submit(); });
      }

      // ===== Toggle password di offcanvas =====
      var togglePassword = document.getElementById('togglePassword');
      var passwordInput = document.getElementById('user_password');

      if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
          var isPassword = passwordInput.getAttribute('type') === 'password';
          passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
          var icon = this.querySelector('i');
          if (icon) icon.className = isPassword ? 'bx bx-hide' : 'bx bx-show';
        });
      }

      // ===== Validasi form tambah user =====
      var addUserForm = document.getElementById('addUserForm');
      if (addUserForm) {
        addUserForm.addEventListener('submit', function (e) {
          var name = document.getElementById('user_name').value.trim();
          var email = document.getElementById('user_email').value.trim();
          var role = document.getElementById('user_role').value;
          var password = document.getElementById('user_password').value;
          var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

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

          if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Format email tidak valid');
            return false;
          }
        });
      }

      // ===== Konfirmasi hapus user =====
      document.querySelectorAll('.js-delete-user').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
          var nama = btn.getAttribute('data-user') || 'user ini';

          if (typeof Swal === 'undefined') {
            if (!confirm('Hapus user ' + nama + '?')) e.preventDefault();
            return;
          }

          e.preventDefault();
          Swal.fire({
            icon: 'warning',
            title: 'Hapus user?',
            html: 'User <b>' + nama + '</b> akan dihapus.',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            reverseButtons: true
          }).then(function (result) {
            if (result.isConfirmed) window.location.href = btn.getAttribute('href');
          });
        });
      });

      // ===== Tooltip =====
      if (window.bootstrap && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
          new bootstrap.Tooltip(el);
        });
      }
    });
  </script>
@endsection
