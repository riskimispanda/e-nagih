@extends('layouts.contentNavbarLayout')

@section('title', 'Dashboard')

@php
  // ===== Formatter rupiah (ringkas untuk card, lengkap untuk tooltip) =====
  $formatRupiah = function ($value) {
    $value = (float) $value;
    $abs = abs($value);
    $sign = $value < 0 ? '-' : '';

    if ($abs >= 1000000000000) {
      return $sign . 'Rp ' . number_format($abs / 1000000000000, 1, ',', '.') . ' T';
    }
    if ($abs >= 1000000000) {
      return $sign . 'Rp ' . number_format($abs / 1000000000, 1, ',', '.') . ' M';
    }
    if ($abs >= 1000000) {
      return $sign . 'Rp ' . number_format($abs / 1000000, 1, ',', '.') . ' Jt';
    }
    if ($abs >= 1000) {
      return $sign . 'Rp ' . number_format($abs / 1000, 0, ',', '.') . ' Rb';
    }

    return $sign . 'Rp ' . number_format($abs, 0, ',', '.');
  };
  $formatRupiahFull = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

  // ===== Layer peta (sinkron dengan warna di script) =====
  $mapLayers = [
    'server' => ['label' => 'Server', 'color' => '#ef4444'],
    'olt' => ['label' => 'OLT', 'color' => '#f97316'],
    'odc' => ['label' => 'ODC', 'color' => '#22c55e'],
    'odp' => ['label' => 'ODP', 'color' => '#3b82f6'],
    'customer' => ['label' => 'Customer', 'color' => '#a855f7'],
  ];

  $labaPositif = $laba >= 0;

  $chartData = [
    'labels' => $chartMonthLabels,
    'pendapatan' => $chartPendapatan,
    'pengeluaran' => $chartPengeluaran,
    'pelangganBaru' => $chartPelangganBaru,
    'tagihan' => [
      'lunas' => (int) $countLunas,
      'belumLunas' => (int) $countInvoiceAllUnPaid,
    ],
  ];
@endphp

@section('vendor-style')
  @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
@endsection

@section('page-style')
  {{-- Tailwind CDN: mengikuti pola dashboard logistik (preflight dimatikan agar tidak menimpa Bootstrap/Sneat) --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      corePlugins: {
        preflight: false
      }
    };
  </script>
  <script>
    // Terapkan mode privasi sebelum render agar nominal tidak berkedip tampil
    try {
      if (localStorage.getItem('dashboard-privacy-mode') === '1') {
        document.documentElement.classList.add('privacy-mode');
      }
    } catch (e) {}
  </script>
  <style>
    /* ====== Kartu KPI ====== */
    .kpi-card {
      transition: transform .2s cubic-bezier(.4, 0, .2, 1), box-shadow .2s cubic-bezier(.4, 0, .2, 1);
    }

    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(15, 23, 42, .07), 0 4px 6px -4px rgba(15, 23, 42, .05);
    }

    /* ====== Mode privasi nominal ====== */
    .private-dots {
      display: none;
    }

    html.privacy-mode .private-amount {
      display: none !important;
    }

    html.privacy-mode .private-dots {
      display: inline-flex !important;
    }

    html.privacy-mode .chart-private {
      filter: blur(8px);
      pointer-events: none;
      user-select: none;
    }

    /* ====== Alert jadwal hari ini ====== */
    @keyframes slideInDown {
      from {
        opacity: 0;
        transform: translateY(-16px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes pulse-dot {

      0%,
      100% {
        transform: scale(1);
        opacity: 1;
      }

      50% {
        transform: scale(1.4);
        opacity: .7;
      }
    }

    #jadwal-alert-wrapper {
      animation: slideInDown 0.45s cubic-bezier(.22, 1, .36, 1) both;
    }

    .jadwal-header {
      background: linear-gradient(135deg, #1e40af 0%, #3b82f6 60%, #06b6d4 100%);
      border-radius: 14px 14px 0 0;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .jadwal-header-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .jadwal-pulse-dot {
      width: 10px;
      height: 10px;
      background: #d4ff00;
      border-radius: 50%;
      animation: pulse-dot 1.5s ease-in-out infinite;
      flex-shrink: 0;
    }

    .jadwal-title-text {
      font-size: 0.95rem;
      font-weight: 700;
      color: #fff;
      letter-spacing: .3px;
    }

    .jadwal-count-badge {
      background: rgba(255, 255, 255, 0.25);
      color: #fff;
      font-size: 0.72rem;
      font-weight: 700;
      padding: 2px 9px;
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.35);
      white-space: nowrap;
    }

    .jadwal-date-text {
      color: rgba(255, 255, 255, 0.82);
      font-size: 0.78rem;
      margin-left: auto;
      white-space: nowrap;
    }

    .jadwal-header-actions {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-shrink: 0;
    }

    .jadwal-btn-icon {
      background: rgba(255, 255, 255, 0.18);
      border: none;
      color: #fff;
      border-radius: 8px;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background .2s, transform .2s;
    }

    .jadwal-btn-icon:hover {
      background: rgba(255, 255, 255, 0.35);
      transform: scale(1.1);
    }

    .jadwal-body {
      background: #f0f7ff;
      border: 1px solid #fff;
      border-top: none;
      border-radius: 0 0 14px 14px;
      padding: 14px 16px 16px;
      overflow: hidden;
      transition: max-height 0.4s cubic-bezier(.22, 1, .36, 1), padding 0.35s ease, opacity 0.3s ease;
      max-height: 2000px;
      opacity: 1;
    }

    .jadwal-body.collapsed {
      max-height: 0;
      padding-top: 0;
      padding-bottom: 0;
      opacity: 0;
      pointer-events: none;
    }

    .jadwal-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 12px;
    }

    .jadwal-card {
      background: #fff;
      border-radius: 11px;
      border-left: 4px solid #3b82f6;
      box-shadow: 0 1px 6px rgba(59, 130, 246, 0.08);
      padding: 12px 14px;
      transition: transform .2s, box-shadow .2s;
      animation: fadeInUp 0.4s ease both;
      display: flex;
      flex-direction: column;
      gap: 7px;
      position: relative;
      overflow: hidden;
    }

    .jadwal-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(59, 130, 246, 0.15);
    }

    .jadwal-card::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 60px;
      height: 60px;
      background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 70%);
      pointer-events: none;
    }

    .jadwal-card-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 8px;
    }

    .jadwal-card-title {
      font-size: 0.88rem;
      font-weight: 700;
      color: #1e3a5f;
      line-height: 1.3;
      flex: 1;
    }

    .jadwal-priority-badge {
      font-size: 0.65rem;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 20px;
      white-space: nowrap;
      flex-shrink: 0;
      text-transform: uppercase;
      letter-spacing: .4px;
    }

    .priority-high {
      background: #fee2e2;
      color: #dc2626;
    }

    .priority-medium {
      background: #fef3c7;
      color: #d97706;
    }

    .priority-low {
      background: #dcfce7;
      color: #16a34a;
    }

    .jadwal-meta-row {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.75rem;
      color: #4b6a8a;
    }

    .jadwal-meta-row i {
      font-size: 0.85rem;
    }

    .jadwal-time-chip {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: #dbeafe;
      color: #1d4ed8;
      font-size: 0.73rem;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 20px;
    }

    .jadwal-category-chip {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: #f0f9ff;
      color: #0369a1;
      font-size: 0.72rem;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 20px;
      border: 1px solid #bae6fd;
    }

    .jadwal-desc {
      font-size: 0.75rem;
      color: #64748b;
      line-height: 1.45;
      border-top: 1px solid #e0efff;
      padding-top: 6px;
      margin-top: 2px;
      word-break: break-word;
    }

    .jadwal-user-row {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 0.72rem;
      color: #64748b;
      border-top: 1px dashed #dbeafe;
      padding-top: 6px;
      margin-top: 2px;
    }

    .jadwal-user-avatar {
      width: 20px;
      height: 20px;
      background: linear-gradient(135deg, #3b82f6, #06b6d4);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.6rem;
      font-weight: 700;
      color: #fff;
      flex-shrink: 0;
    }

    /* ====== Peta ====== */
    #map {
      z-index: 1;
    }

    #map .leaflet-control-attribution {
      font-size: 10px;
      background: rgba(255, 255, 255, .8);
    }

    #map .leaflet-popup-content {
      margin: 10px 12px;
    }

    @media (prefers-reduced-motion: reduce) {

      .kpi-card,
      .jadwal-card {
        transition: none !important;
      }

      .kpi-card:hover,
      .jadwal-card:hover {
        transform: none !important;
      }

      #jadwal-alert-wrapper,
      .jadwal-card,
      .jadwal-pulse-dot {
        animation: none !important;
      }
    }
  </style>
@endsection

@section('vendor-script')
  @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')
  <div class="space-y-6">

    {{-- ==================== HEADER HALAMAN ==================== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="min-w-0">
          <div
            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-[11px] font-bold uppercase tracking-wider">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            {{ $roles?->name ?? 'Pengguna' }}
          </div>
          <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight mt-2 mb-0">
            Selamat datang, {{ $users->name }} 👋
          </h1>
          <p class="text-slate-500 text-xs sm:text-sm mt-1 mb-0">
            Ringkasan operasional NBilling —
            <span class="font-semibold text-slate-600">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</span>
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button type="button" id="privacy-toggle" aria-pressed="false"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
            <i id="privacy-toggle-icon" class="bx bx-lock-open-alt text-base text-slate-500"></i>
            <span id="privacy-toggle-label">Sembunyikan Nominal</span>
          </button>
          <a href="/data/pembayaran"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-indigo-200 bg-indigo-50/70 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs transition-colors no-underline">
            <i class="bx bx-receipt text-base text-indigo-600"></i> Lihat Transaksi
          </a>
          <button type="button" onclick="window.location.reload()"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer border-0">
            <i class="bx bx-refresh text-base"></i> Segarkan
          </button>
        </div>
      </div>
    </div>

    {{-- ==================== ALERT JADWAL HARI INI ==================== --}}
    @if ($todaySchedules->count())
      @php
        $categoryIcons = [
          'meeting' => 'bx-group',
          'task' => 'bx-task',
          'event' => 'bx-calendar-event',
          'reminder' => 'bx-bell',
          'personal' => 'bx-user',
        ];
        $categoryLabels = [
          'meeting' => 'Meeting',
          'task' => 'Tugas',
          'event' => 'Acara',
          'reminder' => 'Pengingat',
          'personal' => 'Personal',
        ];
        $priorityLabels = [
          'high' => 'Tinggi',
          'medium' => 'Sedang',
          'low' => 'Rendah',
        ];
        $todayFormatted = \Carbon\Carbon::today()->locale('id')->isoFormat('dddd, D MMMM YYYY');
      @endphp
      <div id="jadwal-alert-wrapper">
        <div class="jadwal-header">
          <div class="jadwal-header-left">
            <div class="jadwal-pulse-dot"></div>
            <span class="jadwal-title-text"><i class="bx bx-calendar-check fs-4"></i> Jadwal Hari Ini</span>
            <span class="jadwal-count-badge">{{ $todaySchedules->count() }} Jadwal</span>
          </div>
          <span class="jadwal-date-text hidden md:inline">{{ $todayFormatted }}</span>
          <div class="jadwal-header-actions">
            <button class="jadwal-btn-icon" id="jadwal-toggle-btn" title="Sembunyikan/Tampilkan" aria-label="Sembunyikan atau tampilkan jadwal">
              <i class="bx bx-chevron-up" id="jadwal-chevron-icon"></i>
            </button>
            <button class="jadwal-btn-icon" id="jadwal-close-btn" title="Tutup" aria-label="Tutup panel jadwal">
              <i class="bx bx-x"></i>
            </button>
          </div>
        </div>

        <div class="jadwal-body" id="jadwal-body">
          <div class="jadwal-grid">
            @foreach ($todaySchedules as $i => $schedule)
              @php
                $borderColor = $schedule->color ?? '#3b82f6';
                $catIcon = $categoryIcons[$schedule->category] ?? 'bx-calendar';
                $catLabel = $categoryLabels[$schedule->category] ?? ucfirst($schedule->category ?? '');
                $pLabel = $priorityLabels[$schedule->priority] ?? ucfirst($schedule->priority ?? '');
                $pClass = match ($schedule->priority) {
                  'high' => 'priority-high',
                  'medium' => 'priority-medium',
                  default => 'priority-low',
                };
                $userName = $schedule->user?->name ?? 'Unknown';
                $userInitial = strtoupper(substr($userName, 0, 1));
                $animDelay = $i * 60;
              @endphp
              <div class="jadwal-card" style="border-left-color: {{ $borderColor }}; animation-delay: {{ $animDelay }}ms;">
                <div class="jadwal-card-top">
                  <div class="jadwal-card-title">{{ $schedule->title }}</div>
                  @if ($schedule->priority)
                    <span class="jadwal-priority-badge {{ $pClass }}">{{ $pLabel }}</span>
                  @endif
                </div>

                <div class="jadwal-meta-row">
                  @if ($schedule->time_type === 'specific')
                    <span class="jadwal-time-chip">
                      <i class="bx bx-time-five"></i>
                      {{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '--:--' }}
                      –
                      {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '--:--' }}
                    </span>
                  @else
                    <span class="jadwal-time-chip">
                      <i class="bx bx-sun"></i> Seharian
                    </span>
                  @endif

                  @if ($schedule->category)
                    <span class="jadwal-category-chip">
                      <i class="bx {{ $catIcon }}"></i>{{ $catLabel }}
                    </span>
                  @endif
                </div>

                @if ($schedule->description)
                  <div class="jadwal-desc">{{ $schedule->description }}</div>
                @endif

                <div class="jadwal-user-row">
                  <div class="jadwal-user-avatar">{{ $userInitial }}</div>
                  <span>{{ $userName }}</span>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    @endif

    {{-- ==================== RINGKASAN KEUANGAN ==================== --}}
    <div class="space-y-4">
      <div class="px-1">
        <h2 class="text-xs font-extrabold uppercase tracking-[.14em] text-slate-400 mb-0">Ringkasan Keuangan</h2>
        <p class="text-sm font-semibold text-slate-600 mt-1 mb-0">Akumulasi pendapatan, pengeluaran, dan tren 6 bulan terakhir</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
        {{-- Total Pendapatan --}}
        <div class="kpi-card bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm flex flex-col justify-between h-full">
          <div class="flex items-start justify-between gap-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pendapatan</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
              <i class="bx bx-trending-up"></i>
            </div>
          </div>
          <div class="mt-3">
            <div class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight tabular-nums leading-tight">
              <span class="private-amount" title="{{ $formatRupiahFull($totalPendapatan) }}">{{ $formatRupiah($totalPendapatan) }}</span>
              <span class="private-dots text-slate-400">••••••</span>
            </div>
            <div class="text-[11px] font-medium text-emerald-600 mt-1.5 flex items-center gap-1">
              <i class="bx bx-check-circle"></i> Seluruh pemasukan tercatat
            </div>
          </div>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="kpi-card bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm flex flex-col justify-between h-full">
          <div class="flex items-start justify-between gap-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pengeluaran</span>
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
              <i class="bx bx-trending-down"></i>
            </div>
          </div>
          <div class="mt-3">
            <div class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight tabular-nums leading-tight">
              <span class="private-amount" title="{{ $formatRupiahFull($totalPengeluaran) }}">{{ $formatRupiah($totalPengeluaran) }}</span>
              <span class="private-dots text-slate-400">••••••</span>
            </div>
            <div class="text-[11px] font-medium text-rose-600 mt-1.5 flex items-center gap-1">
              <i class="bx bx-check-circle"></i> Seluruh pengeluaran tercatat
            </div>
          </div>
        </div>

        {{-- Laba / Rugi --}}
        <div class="kpi-card bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-sm flex flex-col justify-between h-full">
          <div class="flex items-start justify-between gap-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Laba / Rugi</span>
            <div
              class="w-8 h-8 rounded-xl flex items-center justify-center text-lg shrink-0 {{ $labaPositif ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-50 text-rose-600' }}">
              <i class="bx bx-wallet"></i>
            </div>
          </div>
          <div class="mt-3">
            <div class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight tabular-nums leading-tight">
              <span class="private-amount" title="{{ $formatRupiahFull($laba) }}">{{ $formatRupiah($laba) }}</span>
              <span class="private-dots text-slate-400">••••••</span>
            </div>
            <div class="text-[11px] font-medium mt-1.5 flex items-center gap-1 {{ $labaPositif ? 'text-indigo-600' : 'text-rose-600' }}">
              <i class="bx {{ $labaPositif ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
              {{ $labaPositif ? 'Surplus' : 'Defisit' }} — pendapatan dikurangi pengeluaran
            </div>
          </div>
        </div>
      </div>

      {{-- Chart tren + status tagihan --}}
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-5 sm:px-6 pt-5 flex items-start justify-between gap-4">
            <div>
              <h3 class="text-base font-extrabold text-slate-800 tracking-tight mb-0">Tren Keuangan 6 Bulan</h3>
              <p class="text-slate-500 text-xs mt-1 mb-0">Pendapatan dibandingkan pengeluaran per bulan</p>
            </div>
            <div class="hidden sm:flex items-center gap-3 text-[11px] font-bold text-slate-500 shrink-0">
              <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Pendapatan</span>
              <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>Pengeluaran</span>
            </div>
          </div>
          <div id="chart-tren" class="chart-private px-2 pb-3" style="min-height: 330px"></div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
          <div class="px-5 pt-5">
            <h3 class="text-base font-extrabold text-slate-800 tracking-tight mb-0">Status Tagihan</h3>
            <p class="text-slate-500 text-xs mt-1 mb-0">Pelanggan lunas vs belum lunas</p>
          </div>
          <div id="chart-status-tagihan" class="chart-private" style="min-height: 200px"></div>
          <div class="px-5 pb-3 flex flex-wrap items-center justify-center gap-x-5 gap-y-1.5 text-[11px] font-bold text-slate-600">
            <span class="inline-flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Lunas bulan ini: {{ $countLunas }}
            </span>
            <span class="inline-flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>Belum lunas: {{ $countInvoiceAllUnPaid }}
            </span>
          </div>
          <p class="px-5 pb-3 text-center text-[11px] font-medium text-slate-400 m-0">
            Tahun ini: {{ $countInvoiceAllPaid }} lunas • {{ $countBelumLunas }} belum lunas
          </p>

          <div class="border-t border-slate-100 px-5 pt-3 pb-2 flex-grow">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pelanggan Baru</span>
              <span class="text-[11px] font-medium text-slate-400">6 bulan</span>
            </div>
            <div id="chart-pelanggan-baru" class="chart-private" style="min-height: 130px"></div>
          </div>
        </div>
      </div>
    </div>

    {{-- ==================== PELANGGAN & OPERASIONAL ==================== --}}
    <div class="space-y-4">
      <div class="px-1">
        <h2 class="text-xs font-extrabold uppercase tracking-[.14em] text-slate-400 mb-0">Pelanggan &amp; Operasional</h2>
        <p class="text-sm font-semibold text-slate-600 mt-1 mb-0">Status pelanggan dan tiket bulan berjalan</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Total Pelanggan --}}
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between h-full">
          <div class="flex items-start justify-between gap-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pelanggan</span>
            <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shrink-0">
              <i class="bx bx-group"></i>
            </div>
          </div>
          <div class="mt-3">
            <div class="text-xl font-extrabold text-slate-800 tracking-tight tabular-nums leading-tight">{{ $paket }}</div>
            <div class="text-[11px] font-medium mt-1.5 flex items-center gap-1 {{ $newCustomer->count() > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
              <i class="bx bx-up-arrow-alt"></i> {{ $newCustomer->count() }} baru bulan ini
            </div>
          </div>
        </div>

        {{-- Pelanggan Lunas --}}
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between h-full">
          <div class="flex items-start justify-between gap-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pelanggan Lunas</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
              <i class="bx bx-user-check"></i>
            </div>
          </div>
          <div class="mt-3">
            <div class="text-xl font-extrabold text-slate-800 tracking-tight tabular-nums leading-tight">
              <span class="private-amount" title="{{ $formatRupiahFull($pelangganLunas) }}">{{ $formatRupiah($pelangganLunas) }}</span>
              <span class="private-dots text-slate-400">••••••</span>
            </div>
            <div class="flex flex-wrap items-center gap-1.5 mt-2">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                <i class="bx bx-calendar text-[11px]"></i>{{ $countLunas }} bulan ini
              </span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                {{ $countInvoiceAllPaid }} tahun ini
              </span>
            </div>
          </div>
        </div>

        {{-- Pelanggan Belum Lunas --}}
        <div class="kpi-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between h-full">
          <div class="flex items-start justify-between gap-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Belum Lunas</span>
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
              <i class="bx bx-user-x"></i>
            </div>
          </div>
          <div class="mt-3">
            <div class="text-xl font-extrabold text-slate-800 tracking-tight tabular-nums leading-tight">
              <span class="private-amount" title="{{ $formatRupiahFull($pelangganBelumLunas) }}">{{ $formatRupiah($pelangganBelumLunas) }}</span>
              <span class="private-dots text-slate-400">••••••</span>
            </div>
            <div class="flex flex-wrap items-center gap-1.5 mt-2">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                <i class="bx bx-calendar text-[11px]"></i>{{ $countInvoiceAllUnPaid }} bulan ini
              </span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                {{ $countBelumLunas }} tahun ini
              </span>
            </div>
          </div>
        </div>

        {{-- Tiket Open --}}
        <a href="/tiket-open" class="kpi-card group bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between h-full no-underline hover:border-amber-300">
          <div class="flex items-start justify-between gap-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Tiket Open</span>
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-amber-100 transition-colors">
              <i class="bx bx-receipt"></i>
            </div>
          </div>
          <div class="mt-3">
            <div class="text-xl font-extrabold text-slate-800 tracking-tight tabular-nums leading-tight">{{ $open }}</div>
            <div class="text-[11px] font-semibold text-amber-600 mt-1.5 flex items-center gap-1">
              <i class="bx bx-right-arrow-alt text-sm"></i> Lihat tiket open
            </div>
          </div>
        </a>

        {{-- Tiket Closed --}}
        <a href="/tiket-closed" class="kpi-card group bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col justify-between h-full no-underline hover:border-sky-300">
          <div class="flex items-start justify-between gap-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600">Tiket Closed</span>
            <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-sky-100 transition-colors">
              <i class="bx bx-check-shield"></i>
            </div>
          </div>
          <div class="mt-3">
            <div class="text-xl font-extrabold text-slate-800 tracking-tight tabular-nums leading-tight">{{ $closed }}</div>
            <div class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
              <i class="bx bx-right-arrow-alt text-sm"></i> Lihat tiket closed
            </div>
          </div>
        </a>
      </div>
    </div>

    {{-- ==================== TRANSAKSI BARU ==================== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div
        class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="text-base font-extrabold text-slate-800 tracking-tight mb-0">Transaksi Baru</h3>
          <p class="text-slate-500 text-xs mt-1 mb-0">Pembayaran terbaru bulan ini</p>
        </div>
        <a href="/data/pembayaran"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-indigo-200 bg-indigo-50/70 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs transition-colors no-underline shrink-0 self-start sm:self-auto">
          <i class="bx bx-receipt text-base text-indigo-600"></i> Lihat Semua Transaksi
        </a>
      </div>

      <div class="p-3 sm:p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2">
          @forelse ($pembayaran->take(9) as $transaksi)
            @php
              $metode = strtolower($transaksi->metode_bayar ?? '');
              $metodeClass = match (true) {
                  str_contains($metode, 'cash') || str_contains($metode, 'tunai') => 'bg-amber-100 text-amber-800',
                  str_contains($metode, 'transfer') => 'bg-sky-100 text-sky-800',
                  str_contains($metode, 'qris') || str_contains($metode, 'qr') => 'bg-indigo-100 text-indigo-800',
                  default => 'bg-slate-100 text-slate-700',
              };
              $namaCustomer = $transaksi->invoice?->customer?->nama_customer ?? 'Pelanggan';
              $inisial = strtoupper(mb_substr($namaCustomer, 0, 1));
            @endphp
            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:border-slate-200 hover:bg-slate-50 transition-colors">
              <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white text-xs font-bold flex items-center justify-center shrink-0">
                {{ $inisial }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate mb-0">{{ $namaCustomer }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold {{ $metodeClass }}">
                    {{ $transaksi->metode_bayar ?: 'Lainnya' }}
                  </span>
                  <span class="text-[11px] text-slate-400">{{ $transaksi->created_at?->locale('id')->diffForHumans() }}</span>
                </div>
              </div>
              <div class="text-right shrink-0">
                <span class="text-sm font-bold text-emerald-600 tabular-nums private-amount">{{ $formatRupiahFull($transaksi->jumlah_bayar) }}</span>
                <span class="text-xs font-bold text-slate-400 tabular-nums private-dots">••••••</span>
              </div>
            </div>
          @empty
            <div class="col-span-full flex flex-col items-center justify-center py-10 text-center">
              <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-3">
                <i class="bx bx-inbox"></i>
              </div>
              <p class="text-sm font-semibold text-slate-600 mb-1">Belum ada transaksi bulan ini</p>
              <p class="text-xs text-slate-400 m-0">Transaksi pembayaran akan tampil di sini.</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- ==================== PETA MAPPING ==================== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div>
          <h3 class="text-base font-extrabold text-slate-800 tracking-tight mb-0">Mapping Customer</h3>
          <p class="text-slate-500 text-xs mt-1 mb-0">Lokasi server, OLT, ODC, ODP, dan customer</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          @foreach ($mapLayers as $key => $layer)
            <label
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full border border-slate-200 bg-white text-[11px] font-semibold text-slate-600 cursor-pointer hover:border-slate-300 select-none transition-colors">
              <input type="checkbox" class="map-layer-toggle accent-slate-600" data-layer="{{ $key }}" checked>
              <span class="w-2 h-2 rounded-full" style="background: {{ $layer['color'] }}"></span>
              {{ $layer['label'] }}
              <span id="legend-count-{{ $key }}" class="text-slate-400 font-bold">0</span>
            </label>
          @endforeach

          <button type="button" id="map-reset-view"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold border-0 cursor-pointer transition-colors">
            <i class="bx bx-reset text-sm"></i> Reset
          </button>
        </div>
      </div>

      <div class="relative">
        <div id="map" class="h-96 lg:h-[480px] w-full"></div>
        <div id="map-skeleton" class="absolute inset-0 bg-slate-100 animate-pulse flex items-center justify-center">
          <span class="text-slate-400 text-xs font-bold inline-flex items-center gap-2">
            <i class="bx bx-loader-alt animate-spin text-lg"></i> Memuat peta…
          </span>
        </div>
        <div id="map-error" class="hidden absolute inset-0 bg-white/90 flex items-center justify-center p-4">
          <span class="text-rose-500 text-xs font-bold inline-flex items-center gap-2 text-center">
            <i class="bx bx-error-circle text-lg"></i> Gagal memuat data peta. Muat ulang halaman untuk mencoba lagi.
          </span>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

  <script>
    window.dashboardChartData = @json($chartData);
  </script>

  <script>
    (function () {
      'use strict';

      function ready(fn) {
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', fn);
        } else {
          fn();
        }
      }

      var rupiah = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

      function compactRupiah(value) {
        var v = Number(value) || 0;
        var abs = Math.abs(v);
        if (abs >= 1e12) return (v / 1e12).toFixed(1).replace('.', ',') + ' T';
        if (abs >= 1e9) return (v / 1e9).toFixed(1).replace('.', ',') + ' M';
        if (abs >= 1e6) return (v / 1e6).toFixed(1).replace('.', ',') + ' Jt';
        if (abs >= 1e3) return (v / 1e3).toFixed(0) + ' Rb';
        return String(v);
      }

      function fullRupiah(value) {
        return 'Rp ' + rupiah.format(Number(value) || 0);
      }

      ready(function () {
        initPrivacyToggle();
        initJadwalAlert();
        initCharts();
        initMap();
      });

      /* ==================== Mode privasi nominal ==================== */
      function initPrivacyToggle() {
        var KEY = 'dashboard-privacy-mode';
        var btn = document.getElementById('privacy-toggle');
        var icon = document.getElementById('privacy-toggle-icon');
        var label = document.getElementById('privacy-toggle-label');

        function apply(state) {
          document.documentElement.classList.toggle('privacy-mode', state);
          try { localStorage.setItem(KEY, state ? '1' : '0'); } catch (e) {}
          if (icon) icon.className = 'bx ' + (state ? 'bx-lock-alt' : 'bx-lock-open-alt') + ' text-base text-slate-500';
          if (label) label.textContent = state ? 'Tampilkan Nominal' : 'Sembunyikan Nominal';
          if (btn) btn.setAttribute('aria-pressed', state ? 'true' : 'false');
        }

        var stored = false;
        try { stored = localStorage.getItem(KEY) === '1'; } catch (e) {}
        apply(stored);

        if (btn) {
          btn.addEventListener('click', function () {
            apply(!document.documentElement.classList.contains('privacy-mode'));
          });
        }
      }

      /* ==================== Alert jadwal ==================== */
      function initJadwalAlert() {
        var wrapper = document.getElementById('jadwal-alert-wrapper');
        if (!wrapper) return;

        var body = document.getElementById('jadwal-body');
        var toggleBtn = document.getElementById('jadwal-toggle-btn');
        var closeBtn = document.getElementById('jadwal-close-btn');
        var chevron = document.getElementById('jadwal-chevron-icon');
        var isCollapsed = false;

        if (toggleBtn && body) {
          toggleBtn.addEventListener('click', function () {
            isCollapsed = !isCollapsed;
            body.classList.toggle('collapsed', isCollapsed);
            if (chevron) {
              chevron.style.transition = 'transform 0.5s ease';
              chevron.style.transform = isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
            }
          });
        }

        if (closeBtn) {
          closeBtn.addEventListener('click', function () {
            wrapper.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            wrapper.style.opacity = '0';
            wrapper.style.transform = 'translateY(-12px)';
            setTimeout(function () { wrapper.style.display = 'none'; }, 320);
          });
        }
      }

      /* ==================== ApexCharts ==================== */
      function initCharts() {
        var data = window.dashboardChartData || {};
        if (typeof ApexCharts === 'undefined') return;

        var muted = '#94a3b8';
        var fontFamily = 'Public Sans, Inter, sans-serif';

        var trendEl = document.getElementById('chart-tren');
        if (trendEl) {
          new ApexCharts(trendEl, {
            chart: {
              type: 'area',
              height: 330,
              toolbar: { show: false },
              zoom: { enabled: false },
              fontFamily: fontFamily,
              parentHeightOffset: 0
            },
            series: [
              { name: 'Pendapatan', data: data.pendapatan || [] },
              { name: 'Pengeluaran', data: data.pengeluaran || [] }
            ],
            colors: ['#10b981', '#f43f5e'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5, lineCap: 'round' },
            fill: {
              type: 'gradient',
              gradient: { shadeIntensity: 1, opacityFrom: 0.32, opacityTo: 0.04, stops: [0, 90] }
            },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4, padding: { left: 6, right: 10 } },
            xaxis: {
              categories: data.labels || [],
              axisBorder: { show: false },
              axisTicks: { show: false },
              labels: { style: { colors: muted, fontSize: '11px', fontWeight: 600 } }
            },
            yaxis: {
              labels: { formatter: compactRupiah, style: { colors: muted, fontSize: '11px', fontWeight: 600 } }
            },
            legend: { show: false },
            markers: { size: 0, strokeWidth: 0, hover: { size: 5 } },
            tooltip: { y: { formatter: fullRupiah } },
            noData: { text: 'Belum ada data', style: { color: muted, fontSize: '12px' } }
          }).render();
        }

        var donutEl = document.getElementById('chart-status-tagihan');
        if (donutEl) {
          var tagihan = data.tagihan || { lunas: 0, belumLunas: 0 };
          new ApexCharts(donutEl, {
            chart: { type: 'donut', height: 200, fontFamily: fontFamily },
            series: [Number(tagihan.lunas) || 0, Number(tagihan.belumLunas) || 0],
            labels: ['Lunas', 'Belum Lunas'],
            colors: ['#10b981', '#f43f5e'],
            stroke: { width: 0 },
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: {
              pie: {
                expandOnClick: false,
                donut: {
                  size: '74%',
                  labels: {
                    show: true,
                    name: { show: true, fontSize: '11px', color: muted, fontWeight: 600 },
                    value: {
                      show: true,
                      fontSize: '22px',
                      fontWeight: 800,
                      color: '#1e293b',
                      formatter: function (val) { return val + ' plg'; }
                    },
                    total: {
                      show: true,
                      showAlways: true,
                      label: 'Total',
                      fontSize: '11px',
                      color: muted,
                      formatter: function (w) {
                        return w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0) + ' plg';
                      }
                    }
                  }
                }
              }
            },
            tooltip: {
              y: { formatter: function (val) { return val + ' pelanggan'; } }
            },
            noData: { text: 'Belum ada data', style: { color: muted, fontSize: '12px' } }
          }).render();
        }

        var growthEl = document.getElementById('chart-pelanggan-baru');
        if (growthEl) {
          new ApexCharts(growthEl, {
            chart: { type: 'bar', height: 130, sparkline: { enabled: true }, fontFamily: fontFamily },
            series: [{ name: 'Pelanggan Baru', data: data.pelangganBaru || [] }],
            colors: ['#6366f1'],
            plotOptions: { bar: { columnWidth: '45%', borderRadius: 5 } },
            xaxis: { categories: data.labels || [] },
            tooltip: {
              y: { formatter: function (val) { return val + ' pelanggan baru'; } }
            }
          }).render();
        }
      }

      /* ==================== Peta Leaflet ==================== */
      function initMap() {
        var mapEl = document.getElementById('map');
        if (!mapEl || typeof L === 'undefined') return;

        var skeleton = document.getElementById('map-skeleton');
        var errorBox = document.getElementById('map-error');
        var map = L.map(mapEl).setView([-9.5, 110.5], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 20,
          attribution: '© OpenStreetMap'
        }).addTo(map);

        var colors = {
          server: '#ef4444',
          olt: '#f97316',
          odc: '#22c55e',
          odp: '#3b82f6',
          customer: '#a855f7'
        };
        var icons = {
          server: 'bx-server',
          olt: 'bx-terminal',
          odc: 'bx-git-branch',
          odp: 'bx-hdd',
          customer: 'bx-user'
        };

        var layers = {};
        var counts = {};
        var nodes = { server: {}, olt: {}, odc: {}, odp: {}, customer: {} };
        var linesDrawn = [];
        var bounds = L.latLngBounds();

        Object.keys(colors).forEach(function (key) {
          layers[key] = L.layerGroup().addTo(map);
          counts[key] = 0;
        });

        function hideSkeleton() {
          if (skeleton) skeleton.classList.add('hidden');
        }

        function showError() {
          hideSkeleton();
          if (errorBox) errorBox.classList.remove('hidden');
        }

        fetch("{{ route('peta.data') }}")
          .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
          })
          .then(function (data) {
            (data || []).forEach(function (item) {
              if (!item || !item.jenis || !layers[item.jenis]) return;

              var latlng = [item.lat, item.lng];
              bounds.extend(latlng);
              nodes[item.jenis][item.id] = item;
              counts[item.jenis]++;

              var marker = L.marker(latlng, {
                icon: L.divIcon({
                  className: '',
                  html: "<i class='bx " + (icons[item.jenis] || 'bx-map-pin') +
                    "' style=\"font-size: 20px; color: " + colors[item.jenis] +
                    "; text-shadow: 0 1px 3px rgba(255,255,255,.9);\"></i>",
                  iconSize: [20, 20],
                  iconAnchor: [10, 10]
                })
              });

              marker.bindPopup(
                '<div class="text-xs"><b>' + String(item.jenis).toUpperCase() + '</b><br>' +
                'Nama: ' + (item.nama || '-') + '<br>' +
                'Koordinat: ' + item.lat + ', ' + item.lng + '</div>'
              );
              marker.on('mouseover', function () { marker.openPopup(); });
              marker.on('mouseout', function () { map.closePopup(); });
              marker.on('click', function () { drawConnections(item); });
              marker.addTo(layers[item.jenis]);
            });

            Object.keys(counts).forEach(function (key) {
              var el = document.getElementById('legend-count-' + key);
              if (el) el.textContent = counts[key];
            });

            if (bounds.isValid()) {
              map.fitBounds(bounds, { padding: [30, 30] });
            }
            hideSkeleton();
            setTimeout(function () { map.invalidateSize(); }, 120);
          })
          .catch(showError);

        document.querySelectorAll('.map-layer-toggle').forEach(function (toggle) {
          toggle.addEventListener('change', function () {
            var key = this.getAttribute('data-layer');
            if (!layers[key]) return;
            if (this.checked) {
              map.addLayer(layers[key]);
            } else {
              map.removeLayer(layers[key]);
            }
          });
        });

        var resetBtn = document.getElementById('map-reset-view');
        if (resetBtn) {
          resetBtn.addEventListener('click', function () {
            if (bounds.isValid()) map.fitBounds(bounds, { padding: [30, 30] });
          });
        }

        function drawConnections(item) {
          linesDrawn.forEach(function (line) { map.removeLayer(line); });
          linesDrawn.length = 0;

          var connection = [];

          if (item.jenis === 'customer') {
            var odp = nodes.odp[item.odp_id];
            var odc = odp ? nodes.odc[odp.odc_id] : null;
            var olt = odc ? nodes.olt[odc.olt_id] : null;
            var server = olt ? nodes.server[olt.server_id] : null;

            if (odp) connection.push([item, odp]);
            if (odc) connection.push([odp, odc]);
            if (olt) connection.push([odc, olt]);
            if (server) connection.push([olt, server]);
          } else if (item.jenis === 'odp') {
            var odc2 = nodes.odc[item.odc_id];
            var olt2 = odc2 ? nodes.olt[odc2.olt_id] : null;
            var server2 = olt2 ? nodes.server[olt2.server_id] : null;

            if (odc2) connection.push([item, odc2]);
            if (olt2) connection.push([odc2, olt2]);
            if (server2) connection.push([olt2, server2]);
          } else if (item.jenis === 'odc') {
            var olt3 = nodes.olt[item.olt_id];
            var server3 = olt3 ? nodes.server[olt3.server_id] : null;

            if (olt3) connection.push([item, olt3]);
            if (server3) connection.push([olt3, server3]);
          } else if (item.jenis === 'olt') {
            var server4 = nodes.server[item.server_id];
            if (server4) connection.push([item, server4]);
          }

          connection.forEach(function (pair) {
            var child = pair[0];
            var parent = pair[1];
            if (child && parent) {
              var line = L.polyline([
                [child.lat, child.lng],
                [parent.lat, parent.lng]
              ], {
                color: colors[child.jenis] || 'gray',
                weight: 3,
                opacity: 0.8
              }).addTo(map);
              linesDrawn.push(line);
            }
          });
        }
      }
    })();
  </script>
@endpush
