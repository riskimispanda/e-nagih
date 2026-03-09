@extends('layouts.contentNavbarLayout')

@section('title', 'Dashboard')

@section('vendor-style')
  @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
  @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
  @vite('resources/assets/js/dashboards-analytics.js')
@endsection
<!-- Tambahkan Boxicons dan Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

<style>
  .toggle-icon {
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 18px;
  }

  .toggle-icon:hover {
    transform: scale(1.2);
  }

  .dots-placeholder {
    font - size: 24px;
    letter-spacing: 3px;
    color: #6c757d;
  }

  /* Custom scrollbar untuk transaksi */
  .transaction-list::-webkit-scrollbar {
    width: 4px;
  }

  .transaction-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .transaction-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
  }

  .transaction-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }

  /* ===== JADWAL ALERT STYLES ===== */
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

    0 %,
    100 % {
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
    background: #d4ff00ff;
    border-radius: 50%;
    animation: pulse-dot 1.5s ease-in-out infinite;
    flex-shrink: 0;
  }

  .jadwal-title-text {
    font - size: 0.95rem;
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
    border: 1px solid #ffffffff;
    border-top: none;
    border-radius: 0 0 14px 14px;
    padding: 14px 16px 16px;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(.22, 1, .36, 1), padding 0.35s ease, opacity 0.3s ease;
    max-height: 2000px;
    opacity: 1;
  }

  .jadwal-body.collapsed {
    max - height: 0;
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
    font - size: 0.88rem;
    font-weight: 700;
    color: #1e3a5f;
    line-height: 1.3;
    flex: 1;
  }

  .jadwal-priority-badge {
    font - size: 0.65rem;
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
    font - size: 0.85rem;
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
    font - size: 0.75rem;
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
</style>
@section('content')
  <div class="space-y-6">
    <!-- Alert Jadwal Hari Ini -->
    @if($todaySchedules->count())
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
        <!-- Header -->
        <div class="jadwal-header">
          <div class="jadwal-header-left">
            <div class="jadwal-pulse-dot"></div>
            <span class="jadwal-title-text"><i class="bx bx-calendar-check fs-4"></i> Jadwal Hari Ini</span>
            <span class="jadwal-count-badge">{{ $todaySchedules->count() }} Jadwal</span>
          </div>
          <span class="jadwal-date-text d-none d-md-block">{{ $todayFormatted }}</span>
          <div class="jadwal-header-actions">
            <button class="jadwal-btn-icon" id="jadwal-toggle-btn" title="Sembunyikan/Tampilkan">
              <i class="bx bx-chevron-up" id="jadwal-chevron-icon"></i>
            </button>
            <button class="jadwal-btn-icon" id="jadwal-close-btn" title="Tutup">
              <i class="bx bx-x"></i>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="jadwal-body" id="jadwal-body">
          <div class="jadwal-grid">
            @foreach($todaySchedules as $i => $schedule)
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
                <!-- Title + Priority -->
                <div class="jadwal-card-top">
                  <div class="jadwal-card-title">{{ $schedule->title }}</div>
                  @if($schedule->priority)
                    <span class="jadwal-priority-badge {{ $pClass }}">{{ $pLabel }}</span>
                  @endif
                </div>

                <!-- Time -->
                <div class="jadwal-meta-row">
                  @if($schedule->time_type === 'specific')
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

                  @if($schedule->category)
                    <span class="jadwal-category-chip">
                      <i class="bx {{ $catIcon }}"></i>{{ $catLabel }}
                    </span>
                  @endif
                </div>

                <!-- Description -->
                @if($schedule->description)
                  <div class="jadwal-desc">{{ $schedule->description }}</div>
                @endif

                <!-- User -->
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

    <!-- Welcome Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="flex flex-col md:flex-row items-center">
        <div class="md:w-7/12 p-6 md:p-8">
          <h1 class="text-2xl md:text-3xl fw-bold text-gray-900 mb-4">
            Selamat Datang {{ $users->name }} 🎉
          </h1>
          <p class="text-gray-600 text-lg leading-relaxed">
            <span class="fw-bold text-blue-600">NBilling</span> adalah aplikasi berbasis web yang dirancang untuk
            mempermudah proses pencatatan, pengelolaan, dan penagihan pembayaran pelanggan.
          </p>
        </div>
        <div class="md:w-5/12 flex justify-center md:justify-end p-4">
          <img src="{{ asset('assets/img/illustrations/man-with-laptop.png') }}" alt="Welcome Illustration"
            class="w-64 md:w-72 scaleX-n1-rtl">
        </div>
      </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Total Pelanggan -->
      <div
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-yellow-50 rounded-xl">
            <i class="bx bx-user text-yellow-600 text-xl"></i>
          </div>
        </div>
        <p class="text-gray-600 text-sm font-medium mb-1">Total Pelanggan</p>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{$paket}}</h3>
        <div class="flex items-center text-sm text-green-600 font-medium">
          <i class='bx bx-up-arrow-alt mr-1'></i>
          <span>{{$newCustomer->count()}} baru</span>
        </div>
      </div>

      <!-- Total Pendapatan -->
      <div
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-blue-50 rounded-xl">
            <i class="bx bx-wallet text-blue-600 text-xl"></i>
          </div>
        </div>
        <p class="text-gray-600 text-sm font-medium mb-1">Total Pendapatan</p>
        <h3 class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
      </div>

      <!-- Total Pengeluaran -->
      <div
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-red-50 rounded-xl">
            <i class="bx bx-cart text-red-600 text-xl"></i>
          </div>
        </div>
        <p class="text-gray-600 text-sm font-medium mb-1">Total Pengeluaran</p>
        <h3 class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
      </div>

      <!-- Laba/Rugi -->
      <div
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-green-50 rounded-xl">
            <i class="bx bxs-wallet text-green-600 text-xl"></i>
          </div>
        </div>
        <p class="text-gray-600 text-sm font-medium mb-1">Laba/Rugi</p>
        <h3 class="text-2xl font-bold text-gray-900">Rp {{ number_format($laba, 0, ',', '.') }}</h3>
      </div>
    </div>

    <!-- Status Pelanggan & Tiket -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Pelanggan Lunas -->
      <div
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-green-50 rounded-xl">
            <i class="bx bxs-wallet text-green-600 text-xl"></i>
          </div>
        </div>
        <p class="text-gray-600 text-sm font-medium mb-1">Pelanggan Lunas</p>
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-2xl font-bold text-gray-900 amount-text" id="lunas-amount">
            <span class="nominal-text">Rp {{ number_format($pelangganLunas, 0, ',', '.') }}</span>
            <span class="dots-placeholder hidden text-gray-400 text-xl">•••••••</span>
          </h3>
          <button class="text-gray-400 hover:text-green-600 transition-colors duration-200 toggle-icon"
            data-target="lunas-card">
            <i class="bx bx-show text-xl"></i>
          </button>
        </div>
        <span
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-2">
          <i class="bx bxs-user mr-1"></i>{{ $countLunas }} Pelanggan Lunas (MONTHLY)
        </span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
          <i class="bx bxs-user mr-1"></i>{{ $countInvoiceAllPaid }} Pelanggan Lunas (YEARLY)
        </span>
      </div>

      <!-- Pelanggan Belum Lunas -->
      <div
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-red-50 rounded-xl">
            <i class="bx bxs-cart text-red-600 text-xl"></i>
          </div>
        </div>
        <p class="text-gray-600 text-sm font-medium mb-1">Pelanggan Belum Lunas</p>
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-2xl font-bold text-gray-900 amount-text" id="belum-lunas-amount">
            <span class="nominal-text">Rp {{ number_format($pelangganBelumLunas, 0, ',', '.') }}</span>
            <span class="dots-placeholder hidden text-gray-400 text-xl">•••••••</span>
          </h3>
          <button class="text-gray-400 hover:text-green-600 transition-colors duration-200 toggle-icon"
            data-target="belum-lunas-card">
            <i class="bx bx-show text-xl"></i>
          </button>
        </div>
        <span
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-2">
          <i class="bx bxs-user mr-1"></i>{{ $countInvoiceAllUnPaid }} Pelanggan Belum Lunas (MONTHLY)
        </span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
          <i class="bx bxs-user mr-1"></i>{{ $countBelumLunas }} Pelanggan Belum Lunas (YEARLY)
        </span>
      </div>

      <!-- Tiket Open -->
      <a href="/tiket-open" class="block transform hover:scale-105 transition-transform duration-200"
        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Halaman Tiket Open">
        <div
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 h-full">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-yellow-50 rounded-xl">
              <i class="bx bx-card text-yellow-600 text-xl"></i>
            </div>
          </div>
          <p class="text-gray-600 text-sm font-medium mb-1">Tiket Open</p>
          <h3 class="text-2xl font-bold text-gray-900 mb-2">{{$open}}</h3>
          <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
            <i class="bx bx-card mr-1"></i>{{ $open }} Tiket
          </span>
        </div>
      </a>

      <!-- Tiket Closed -->
      <a href="/tiket-closed" class="block transform hover:scale-105 transition-transform duration-200"
        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Halaman Tiket Closed">
        <div
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 h-full">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-green-50 rounded-xl">
              <i class="bx bxs-card text-green-600 text-xl"></i>
            </div>
          </div>
          <p class="text-gray-600 text-sm font-medium mb-1">Tiket Closed</p>
          <h3 class="text-2xl font-bold text-gray-900 mb-2">{{$closed}}</h3>
          <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <i class="bx bxs-card mr-1"></i>{{ $closed }} Tiket
          </span>
        </div>
      </a>
    </div>

    <!-- Log Sistem -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <div>
          <h3 class="text-lg fw-bold text-gray-900">Log Sistem </h3>
          <p class="text-gray-600 text-sm">Aktivitas terbaru
            <strong>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}</strong>
          </p>
        </div>
        <div>
          <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <i class="bx bx-radio-circle-marked mr-1"></i> Live
          </span>
        </div>
      </div>
      <div class="p-6 mb-2 flex-grow overflow-auto">
        <div class="table-responsive">
          <table id="activityLogTable" class="table table-hover w-full whitespace-nowrap pt-3">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
              <tr>
                <th class="px-3 py-2 text-left font-medium">Waktu</th>
                <th class="px-3 py-2 text-left font-medium">User</th>
                <th class="px-3 py-2 text-left font-medium">Roles</th>
                <th class="px-3 py-2 text-left font-medium">Aktivitas</th>
              </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
              <!-- Polled data will go here -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Mapping Section -->
    <div class="space-y-4">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-xl fw-bold text-gray-900 mb-2">Mapping Customer</h3>
        <p class="text-gray-600">Peta mapping untuk melihat lokasi server, olt, odc, odp, dan customer</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div id="map" class="h-96 lg:h-[520px] w-full rounded-lg"></div>
      </div>
    </div>

    <!-- Transactions & Bottom Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Transactions -->
      <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <h5 class="text-lg fw-semibold text-gray-900">Transaksi Baru</h5>
          <p class="text-grey-600">Transaksi baru-baru ini</p>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            @foreach ($pembayaran->take(5) as $transaksi)
              <div class="flex items-center space-x-4 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                <div class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                  <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="Wallet" class="w-6 h-6">
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">
                    {{$transaksi->invoice->customer->nama_customer}}
                  </p>
                  <div class="flex items-center space-x-2 mt-1">
                    <span
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                      {{$transaksi->metode_bayar}}
                    </span>
                  </div>
                </div>
                <div class="flex-shrink-0">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Rp {{ number_format($transaksi->jumlah_bayar, 0, ',', '.') }}
                  </span>
                </div>
              </div>
            @endforeach
          </div>

          {{-- Tombol View All --}}
          <div class="text-center mt-6 pt-4 border-t border-gray-200">
            <a href="/data/pembayaran"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200"
              data-bs-toggle="tooltip" title="Lihat Transaksi" data-bs-placement="top">
              View All
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", async () => {
    const map = L.map('map').setView([-9.5, 110.5], 10);
    const bounds = L.latLngBounds();

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 20,
      attribution: '© Panda'
    }).addTo(map);


    const colors = {
      server: 'red',
      olt: 'orange',
      odc: 'green',
      odp: 'blue',
      customer: 'purple'
    };

    const icons = {
      server: 'bx-server fw-bold',
      olt: 'bx-terminal fw-bold',
      odc: 'bx-terminal fw-bold',
      odp: 'bx-terminal fw-bold',
      customer: 'bx-user fw-bold'
    };

    const data = await fetch("{{ route('peta.data') }}").then(res => res.json());

    const nodes = {
      server: {},
      olt: {},
      odc: {},
      odp: {},
      customer: {}
    };

    const linesDrawn = [];

    data.forEach(item => {
      const latlng = [item.lat, item.lng];
      bounds.extend(latlng);
      nodes[item.jenis][item.id] = item;

      const customIcon = L.divIcon({
        className: '',
        html: `<i class='bx ${icons[item.jenis]}' style="font-size: 24px; color: ${colors[item.jenis]};"></i>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12]
      });

      const marker = L.marker(latlng, { icon: customIcon }).addTo(map);

      const detailPopup = `
  <div class="p-2">
    <b class="text-sm">${item.jenis.toUpperCase()}</b><br>
      <span class="text-xs">Nama: ${item.nama}</span><br>
        <span class="text-xs">Koordinat: ${item.lat}, ${item.lng}</span>
      </div>
      `;

      marker.on('mouseover', function () {
        marker.bindPopup(detailPopup).openPopup();
      });

      marker.on('mouseout', function () {
        map.closePopup();
      });

      marker.on('click', function () {
        drawConnections(item);
      });
    });

    function drawConnections(item) {
      linesDrawn.forEach(line => map.removeLayer(line));
      linesDrawn.length = 0;

      const connection = [];

      if (item.jenis === 'customer') {
        const odp = nodes.odp[item.odp_id];
        const odc = odp ? nodes.odc[odp.odc_id] : null;
        const olt = odc ? nodes.olt[odc.olt_id] : null;
        const server = olt ? nodes.server[olt.server_id] : null;

        if (odp) connection.push([item, odp]);
        if (odc) connection.push([odp, odc]);
        if (olt) connection.push([odc, olt]);
        if (server) connection.push([olt, server]);

      } else if (item.jenis === 'odp') {
        const odc = nodes.odc[item.odc_id];
        const olt = odc ? nodes.olt[odc.olt_id] : null;
        const server = olt ? nodes.server[olt.server_id] : null;

        if (odc) connection.push([item, odc]);
        if (olt) connection.push([odc, olt]);
        if (server) connection.push([olt, server]);

      } else if (item.jenis === 'odc') {
        const olt = nodes.olt[item.olt_id];
        const server = olt ? nodes.server[olt.server_id] : null;

        if (olt) connection.push([item, olt]);
        if (server) connection.push([olt, server]);

      } else if (item.jenis === 'olt') {
        const server = nodes.server[item.server_id];
        if (server) connection.push([item, server]);
      }

      connection.forEach(([child, parent]) => {
        if (child && parent) {
          const line = L.polyline([
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

    map.fitBounds(bounds);

    // Legend
    const legend = L.control({ position: "bottomright" });
    legend.onAdd = function () {
      const div = L.DomUtil.create("div", "bg-white p-4 rounded-lg shadow-lg border border-gray-200");
      const types = Object.keys(colors);
      div.innerHTML = `<h4 class="font-semibold text-gray-900 mb-2">Legenda</h4>`;
      types.forEach(key => {
        div.innerHTML += `
                    <div class="flex items-center space-x-2 mb-1">
                        <i class='bx ${icons[key]}' style="color:${colors[key]};font-size:16px;"></i>
                        <span class="text-sm text-gray-700">${key.charAt(0).toUpperCase() + key.slice(1)}</span>
                    </div>`;
      });
      return div;
    };
    legend.addTo(map);
  });

  // Toggle visibility untuk card amount + Jadwal Alert
  document.addEventListener('DOMContentLoaded', function () {

    // ===== Jadwal Alert Toggle & Close =====
    const jadwalWrapper = document.getElementById('jadwal-alert-wrapper');
    const jadwalBody = document.getElementById('jadwal-body');
    const jadwalToggleBtn = document.getElementById('jadwal-toggle-btn');
    const jadwalCloseBtn = document.getElementById('jadwal-close-btn');
    const jadwalChevron = document.getElementById('jadwal-chevron-icon');

    if (jadwalWrapper) {
      let isCollapsed = false;

      if (jadwalToggleBtn) {
        jadwalToggleBtn.addEventListener('click', function () {
          isCollapsed = !isCollapsed;
          jadwalBody.classList.toggle('collapsed', isCollapsed);
          jadwalChevron.style.transition = 'transform 0.50s ease';
          jadwalChevron.style.transform = isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
        });
      }

      if (jadwalCloseBtn) {
        jadwalCloseBtn.addEventListener('click', function () {
          jadwalWrapper.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
          jadwalWrapper.style.opacity = '0';
          jadwalWrapper.style.transform = 'translateY(-12px)';
          setTimeout(function () { jadwalWrapper.style.display = 'none'; }, 320);
        });
      }
    }


    initializeCardStates();

    document.querySelectorAll('.toggle-icon').forEach(icon => {
      icon.addEventListener('click', function () {
        const target = this.getAttribute('data-target');
        toggleCardVisibility(target, this);
      });
    });

    function toggleCardVisibility(target, icon) {
      const cardElement = document.querySelector(`[data-target="${target}"]`).closest('.bg-white');
      const nominalText = cardElement.querySelector('.nominal-text');
      const dotsPlaceholder = cardElement.querySelector('.dots-placeholder');
      const iconElement = icon.querySelector('i');

      if (nominalText.classList.contains('hidden')) {
        // Show - tampilkan teks asli
        nominalText.classList.remove('hidden');
        dotsPlaceholder.classList.add('hidden');
        iconElement.classList.remove('bx-show');
        iconElement.classList.add('bx-hide');
        icon.classList.remove('text-gray-400');
        icon.classList.add('text-green-600');

        localStorage.setItem(`card-${target}-hidden`, 'false');
      } else {
        // Hide - tampilkan dots
        nominalText.classList.add('hidden');
        dotsPlaceholder.classList.remove('hidden');
        iconElement.classList.remove('bx-hide');
        iconElement.classList.add('bx-show');
        icon.classList.remove('text-green-600');
        icon.classList.add('text-gray-400');

        localStorage.setItem(`card-${target}-hidden`, 'true');
      }
    }

    function initializeCardStates() {
      const cards = [
        { target: 'lunas-card' },
        { target: 'belum-lunas-card' }
      ];

      cards.forEach(card => {
        const isHidden = localStorage.getItem(`card-${card.target}-hidden`) === 'true';
        const icon = document.querySelector(`[data-target="${card.target}"]`);

        if (icon) {
          const cardElement = icon.closest('.bg-white');
          const nominalText = cardElement.querySelector('.nominal-text');
          const dotsPlaceholder = cardElement.querySelector('.dots-placeholder');
          const iconElement = icon.querySelector('i');

          if (isHidden) {
            nominalText.classList.add('hidden');
            dotsPlaceholder.classList.remove('hidden');
            iconElement.classList.remove('bx-hide');
            iconElement.classList.add('bx-show');
            icon.classList.remove('text-green-600');
            icon.classList.add('text-gray-400');
          } else {
            nominalText.classList.remove('hidden');
            dotsPlaceholder.classList.add('hidden');
            iconElement.classList.remove('bx-show');
            iconElement.classList.add('bx-hide');
            icon.classList.remove('text-gray-400');
            icon.classList.add('text-green-600');
          }
        }
      });
    }

    // INIT DATATABLES WITH POLLING
    function initLogSistemDataTable() {
      if (typeof $.fn.DataTable === 'undefined') {
        $.getScript('https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', function () {
          setupDataTable();
        });
      } else {
        setupDataTable();
      }

      function setupDataTable() {
        const logTable = $('#activityLogTable').DataTable({
          ajax: {
            url: "{{ route('api.dashboard.activity-logs') }}",
            type: "GET",
            dataSrc: "data"
          },
          columns: [
            {
              data: "created_at",
              render: function (data, type, row) {
                if (type === 'sort' || type === 'type') {
                  return row.created_at_raw;
                }
                return `<span class="text-gray-600 text-xs" title="${row.created_at_raw}"><i class='bx bx-time-five mr-1'></i> ${data}</span>`;
              }
            },
            {
              data: "causer_name",
              render: function (data) {
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class='bx bx-user mr-1'></i> ${data}</span>`;
              }
            },
            {
              data: "role_name",
              render: function (data) {
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class='bx bx-shield-quarter mr-1'></i> ${data}</span>`;
              }
            },
            {
              data: "description",
              render: function (data) {
                return `<span class="text-gray-700 font-medium text-xs break-words">${data}</span>`;
              }
            }
          ],
          order: [[0, 'desc']],
          pageLength: 5,
          lengthMenu: [5, 10, 25, 50],
          dom: '<"flex justify-between items-center mb-2"lf>rt<"flex justify-between items-center mt-3 pt-2 text-sm text-gray-600"ip>',
          language: {
            emptyTable: "Tidak ada log aktivitas",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ log",
            infoEmpty: "Menampilkan 0 log",
            infoFiltered: "(difilter dari _MAX_ total log)",
            search: "Cari:",
            lengthMenu: "_MENU_",
            zeroRecords: "Log aktivitas tidak ditemukan",
            paginate: {
              first: "Awal",
              last: "Akhir",
              next: "Lanjut",
              previous: "Kembali"
            }
          }
        });

        // Set DataTables polling every 5 seconds
        setInterval(function () {
          if ($('#activityLogTable').is(':visible')) {
            logTable.ajax.reload(null, false); // Reload without resetting paging
          }
        }, 5000);
      }
    }

    // Call datatables init
    initLogSistemDataTable();
  });
</script>