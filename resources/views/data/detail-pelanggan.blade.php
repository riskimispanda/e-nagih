@extends('layouts.contentNavbarLayout')
@section('title', 'Detail Pelanggan')

@section('page-style')
  <!-- Load Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Konfigurasi Tailwind untuk menghindari konflik dengan Bootstrap
    tailwind.config = {
      important: true, // Prioritaskan class Tailwind
      corePlugins: {
        preflight: false, // Matikan reset CSS agar tidak merusak navbar bawaan
      },
      theme: {
        extend: {
          colors: {
            primary: '#6366f1',
            'primary-dark': '#4f46e5',
            'primary-light': '#e0e7ff',
            success: '#10b981',
            danger: '#ef4444',
            warning: '#f59e0b',
            slate: {
              50: '#f8fafc',
              100: '#f1f5f9',
              200: '#e2e8f0',
              300: '#cbd5e1',
              400: '#94a3b8',
              500: '#64748b',
              600: '#475569',
              700: '#334155',
              800: '#1e293b',
            }
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
          boxShadow: {
            'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
            'glow': '0 0 15px rgba(99, 102, 241, 0.2)',
          }
        }
      }
    }
  </script>

  <style>
    /* Styling tambahan untuk transisi dan efek */
    .fade-in {
      animation: fadeIn 0.5s ease-out forwards;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.5);
    }
  </style>
@endsection

@section('content')
  <div class="fade-in min-h-screen text-slate-700">

    <!-- Breadcrumb using Tailwind -->
    <nav class="flex mb-6 text-sm text-slate-500" aria-label="Breadcrumb">
      <ol class="inline-flex items-center space-x-2">
        <li class="inline-flex items-center">
          <a href="{{ url('/dashboard') }}" class="hover:text-primary transition-colors">
            Dashboard
          </a>
        </li>
        <li class="flex items-center">
          <i class="bx bx-chevron-right text-lg"></i>
          <a href="{{ url('/data/pelanggan') }}" class="ml-2 hover:text-primary transition-colors">
            Data Pelanggan
          </a>
        </li>
        <li class="flex items-center">
          <i class="bx bx-chevron-right text-lg"></i>
          <span class="ml-2 font-medium text-slate-700">Detail Pelanggan</span>
        </li>
      </ol>
    </nav>

    <!-- Header Section -->
    <div
      class="relative w-full bg-gradient-to-r from-indigo-600 to-violet-600 rounded-3xl shadow-glow overflow-hidden mb-8 text-white p-6 sm:p-8">
      <!-- Decorative Background Shapes -->
      <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white opacity-10 blur-2xl"></div>
      <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 rounded-full bg-indigo-400 opacity-20 blur-xl"></div>

      <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6">
        <!-- Avatar -->
        <div class="flex-shrink-0">
          <div
            class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-sm border-4 border-white/30 flex items-center justify-center text-3xl font-bold shadow-lg">
            {{ strtoupper(substr($customer->nama_customer, 0, 2)) }}
          </div>
        </div>

        <!-- Main Info -->
        <div class="flex-grow text-center md:text-left space-y-2">
          <div class="flex flex-col md:flex-row items-center gap-3">
            <h1 class="text-3xl font-bold tracking-tight">{{ $customer->nama_customer }}</h1>
            <span
              class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                          {{ $customer->status_id == 3 ? 'bg-green-500/20 text-green-100 border border-green-500/30' :
    ($customer->status_id == 9 ? 'bg-red-500/20 text-red-100 border border-red-500/30' : 'bg-yellow-500/20 text-yellow-100 border border-yellow-500/30') }}">
              {{ $customer->status_id == 3 ? 'Aktif' : ($customer->status_id == 9 ? 'Nonaktif' : 'Status Lain') }}
            </span>
          </div>

          <div class="flex flex-wrap justify-center md:justify-start gap-4 text-indigo-100 text-sm">
            <div class="flex items-center gap-1">
              <i class="bx bx-hash text-lg"></i>
              <span>ID: {{ $customer->id }}</span>
            </div>
            <div class="flex items-center gap-1">
              <i class="bx bx-user-pin text-lg"></i>
              <span>Teknisi: {{ $customer->teknisi->name ?? 'Belum Ditentukan' }}</span>
            </div>
            <div class="flex items-center gap-1">
              <i class="bx bx-calendar text-lg"></i>
              <span>Bergabung: {{ $customer->created_at ? $customer->created_at->format('d M Y') : '-' }}</span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3 mt-4 md:mt-0">
          @if($customer->gps)
            @php
              // Check if GPS field is a URL or coordinates
              $gpsValue = $customer->gps;
              $gpsUrl = $gpsValue;

              // If it's not a URL (doesn't start with http/https), assume it's coordinates
              if (!preg_match('/^https?:\/\//i', $gpsValue)) {
                // Remove any spaces and check if it's in coordinate format (lat,lng)
                $cleanCoords = trim($gpsValue);

                // Check if it matches coordinate pattern (e.g., "-7.123,110.456" or "-7.123, 110.456")
                if (preg_match('/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', $cleanCoords)) {
                  // Convert to Google Maps URL
                  $gpsUrl = 'https://www.google.com/maps?q=' . str_replace(' ', '', $cleanCoords);
                }
              }
            @endphp
            <a href="{{ $gpsUrl }}" target="_blank"
              class="flex items-center justify-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-xl text-white font-medium transition-all duration-200 border border-white/20 group">
              <i class="bx bx-map text-xl group-hover:scale-110 transition-transform"></i>
              <span>Lokasi</span>
            </a>
          @else
            <button disabled
              class="flex items-center justify-center gap-2 px-4 py-2 bg-white/5 backdrop-blur-md rounded-xl text-white/50 font-medium cursor-not-allowed border border-white/10">
              <i class="bx bx-map text-xl"></i>
              <span>Lokasi Tidak Tersedia</span>
            </button>
          @endif
          <a href="{{ url('/pelanggan/edit/' . $customer->id) }}"
            class="flex items-center justify-center gap-2 px-4 py-2 bg-white text-indigo-600 rounded-xl font-bold shadow-lg hover:shadow-xl hover:bg-indigo-50 transition-all duration-200 transform hover:-translate-y-0.5">
            <i class="bx bx-edit text-xl"></i>
            <span>Edit Data</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      <!-- Left Column (Stats & Profile) -->
      <div class="space-y-6">
        <!-- Mini Stats Cards -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-white p-5 rounded-2xl shadow-soft border border-slate-100 hover:shadow-md transition-shadow">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tagihan</div>
            <div class="text-lg font-bold {{ optional($invoice)->status_id == 7 ? 'text-red-500' : 'text-green-600' }}">
              Rp {{ number_format($customer->paket->harga ?? 0, 0, ',', '.') }}
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl shadow-soft border border-slate-100 hover:shadow-md transition-shadow">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Paket</div>
            <div class="text-lg font-bold text-primary truncate">
              {{ \Illuminate\Support\Str::limit($customer->paket->nama_paket ?? '-', 10) }}
            </div>
          </div>
        </div>

        <!-- Contact & Personal Info -->
        <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-700 flex items-center gap-2">
              <span class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-primary"><i
                  class="bx bx-user-circle text-xl"></i></span>
              Data Personal
            </h3>
          </div>
          <div class="p-6 space-y-5">
            <!-- List Item -->
            <div class="flex justify-between items-center group">
              <div class="flex items-center gap-3 text-slate-500">
                <i class="bx bx-phone text-xl group-hover:text-primary transition-colors"></i>
                <span class="text-sm font-medium">No. Telepon</span>
              </div>
              <span class="text-slate-700 font-semibold">{{ $customer->no_hp ?? '-' }}</span>
            </div>
            <!-- List Item -->
            <div class="flex justify-between items-center group">
              <div class="flex items-center gap-3 text-slate-500">
                <i class="bx bx-envelope text-xl group-hover:text-primary transition-colors"></i>
                <span class="text-sm font-medium">Email</span>
              </div>
              <span class="text-slate-700 font-semibold truncate max-w-[150px]">{{ $customer->email ?? '-' }}</span>
            </div>
            <!-- List Item -->
            <div class="flex justify-between items-center group">
              <div class="flex items-center gap-3 text-slate-500">
                <i class="bx bx-id-card text-xl group-hover:text-primary transition-colors"></i>
                <span class="text-sm font-medium">No. KTP</span>
              </div>
              <span class="text-slate-700 font-semibold">{{ $customer->no_identitas ?? '-' }}</span>
            </div>

            <div class="pt-4 border-t border-slate-50">
              <div class="text-sm text-slate-500 mb-2 font-medium flex items-center gap-2">
                <i class="bx bx-home"></i> Alamat Pemasangan
              </div>
              <p
                class="text-slate-700 leading-relaxed text-sm bg-slate-50 p-3 rounded-lg border border-slate-100 select-all">
                {{ $customer->alamat ?? 'Alamat tidak tersedia' }}
              </p>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
              <a href="{{ asset($customer->identitas) }}" target="_blank"
                class="flex items-center justify-center gap-2 py-2 px-3 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-primary transition-all">
                <i class="bx bx-image"></i> Foto KTP
              </a>
              <a href="{{ asset($customer->foto_rumah) }}" target="_blank"
                class="flex items-center justify-center gap-2 py-2 px-3 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-primary transition-all">
                <i class="bx bx-home"></i> Foto Rumah
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column (Technical & Billing) -->
      <div class="lg:col-span-2 space-y-6">

        <!-- Technical Specs -->
        <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-700 flex items-center gap-2">
              <span class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-primary"><i
                  class="bx bx-server text-xl"></i></span>
              Informasi Teknis
            </h3>
            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wide">
              {{ $customer->media->nama_media ?? 'Unknown Media' }}
            </span>
          </div>

          <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <!-- Network Section -->
            <div class="space-y-4">
              <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Infrastruktur Jaringan</h4>

              <div class="flex justify-between items-center py-2 border-b border-slate-50">
                <span class="text-sm text-slate-500">Server</span>
                <span
                  class="font-semibold text-slate-700">{{ $customer->odp->odc->olt->server->lokasi_server ?? '-' }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-50">
                <span class="text-sm text-slate-500">OLT</span>
                <span class="font-semibold text-slate-700">{{ $customer->odp->odc->olt->nama_lokasi ?? '-' }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-50">
                <span class="text-sm text-slate-500">ODC</span>
                <span class="font-semibold text-slate-700">{{ $customer->odp->odc->nama_odc ?? '-' }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-50">
                <span class="text-sm text-slate-500">ODP</span>
                <span class="font-semibold text-slate-700">{{ $customer->odp->nama_odp ?? '-' }}</span>
              </div>
            </div>

            <!-- Connection Section -->
            <div class="space-y-4">
              <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Detail Koneksi</h4>

              <div class="flex justify-between items-center py-2 border-b border-slate-50">
                <span class="text-sm text-slate-500">IP Local</span>
                <span
                  class="font-mono text-sm bg-slate-100 px-2 py-0.5 rounded text-slate-700 select-all">{{ $customer->local_address ?? '-' }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-50">
                <span class="text-sm text-slate-500">IP Remote</span>
                <span
                  class="font-mono text-sm bg-slate-100 px-2 py-0.5 rounded text-slate-700 select-all">{{ $customer->remote_address ?? '-' }}</span>
              </div>
              <div>
                <span class="text-sm text-slate-500 block mb-1">PPPoE User</span>
                <div class="flex items-center gap-2">
                  <span
                    class="font-mono text-sm font-semibold text-primary bg-indigo-50 px-3 py-1.5 rounded-lg w-full select-all">{{ $customer->usersecret ?? '-' }}</span>
                </div>
              </div>
              <div>
                <span class="text-sm text-slate-500 block mb-1">PPPoE Password</span>
                <div class="flex items-center gap-2">
                  <span
                    class="font-mono text-sm font-semibold text-red-500 bg-red-50 px-3 py-1.5 rounded-lg w-full select-all">{{ $customer->pass_secret ?? '******' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Billing Status -->
        <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-700 flex items-center gap-2">
              <span class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-primary"><i
                  class="bx bx-wallet text-xl"></i></span>
              Informasi Pembayaran
            </h3>
            <a href="/riwayatPembayaran/{{ $customer->id }}" class="text-sm text-primary font-bold hover:underline">
              Lihat Riwayat <i class="bx bx-right-arrow-alt"></i>
            </a>
          </div>

          <div class="p-6">
            <div class="flex flex-col md:flex-row gap-6 items-center">
              <!-- Status Badge Area -->
              <div
                class="w-full md:w-1/3 flex flex-col items-center justify-center p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase mb-3">Status Bulan Ini</p>

                @if (optional($invoice)->status_id == 7)
                  <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-3">
                    <i class='bx bx-time text-3xl text-red-500'></i>
                  </div>
                  <h4 class="text-xl font-bold text-red-600">Belum Lunas</h4>
                  <span class="text-xs text-red-400 mt-1">Harap segera dibayar</span>
                @elseif(optional($invoice)->status_id == 8)
                  <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-3">
                    <i class='bx bx-check text-3xl text-green-600'></i>
                  </div>
                  <h4 class="text-xl font-bold text-green-600">Lunas</h4>
                  <span class="text-xs text-green-400 mt-1">Terima kasih</span>
                @else
                  <div class="w-16 h-16 rounded-full bg-slate-200 flex items-center justify-center mb-3">
                    <i class='bx bx-minus text-3xl text-slate-500'></i>
                  </div>
                  <h4 class="text-xl font-bold text-slate-500">Tidak Ada</h4>
                @endif
              </div>

              <!-- Billing Details -->
              <div class="w-full md:w-2/3 grid grid-cols-1 gap-4">
                <div
                  class="flex justify-between items-center p-3 hover:bg-slate-50 rounded-lg transition-colors border-b border-dashed border-slate-200">
                  <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-50 rounded text-primary"><i class="bx bx-money"></i></div>
                    <span class="text-sm font-medium text-slate-600">Total Tagihan</span>
                  </div>
                  <span class="font-bold text-slate-800">Rp
                    {{ number_format($customer->paket->harga ?? 0, 0, ',', '.') }}</span>
                </div>

                <div
                  class="flex justify-between items-center p-3 hover:bg-slate-50 rounded-lg transition-colors border-b border-dashed border-slate-200">
                  <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-50 rounded text-primary"><i class="bx bx-calendar-event"></i></div>
                    <span class="text-sm font-medium text-slate-600">Jatuh Tempo</span>
                  </div>
                  <span
                    class="font-bold {{ isset($invoice->jatuh_tempo) && \Carbon\Carbon::parse($invoice->jatuh_tempo)->isPast() ? 'text-red-500' : 'text-slate-800' }}">
                    {{ isset($invoice->jatuh_tempo) ? \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d M Y') : '-' }}
                  </span>
                </div>

                <div class="flex justify-between items-center p-3 hover:bg-slate-50 rounded-lg transition-colors">
                  <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-50 rounded text-primary"><i class="bx bx-credit-card-front"></i></div>
                    <span class="text-sm font-medium text-slate-600">Metode</span>
                  </div>
                  <span class="font-bold text-slate-800">{{ $customer->metode_pembayaran ?? 'Manual Transfer' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Notification Toast (Hidden by default) -->
  <div id="update-toast"
    class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300">
    <div class="bg-white border border-slate-200 shadow-xl rounded-2xl p-4 flex items-center gap-4 max-w-sm">
      <div class="bg-green-100 text-green-600 rounded-full p-2">
        <i class="bx bx-check-double text-xl"></i>
      </div>
      <div>
        <h6 class="font-bold text-slate-800">Data Diperbarui</h6>
        <p class="text-xs text-slate-500">Informasi pelanggan berhasil disinkronkan.</p>
      </div>
      <button onclick="hideToast()" class="text-slate-400 hover:text-slate-600 ml-auto">
        <i class="bx bx-x text-xl"></i>
      </button>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const customerId = {{ $customer->id }};
      let lastUpdated = null;

      // Toast Functions
      window.showToast = function () {
        const toast = document.getElementById('update-toast');
        toast.classList.remove('translate-y-20', 'opacity-0');
        setTimeout(hideToast, 5000);
      }

      window.hideToast = function () {
        const toast = document.getElementById('update-toast');
        toast.classList.add('translate-y-20', 'opacity-0');
      }

      function pollForUpdates() {
        fetch(`/api/customers/${customerId}`)
          .then(response => {
            if (response.ok) return response.json();
            throw new Error('Network response was not ok');
          })
          .then(data => {
            if (data && lastUpdated && lastUpdated !== data.last_updated) {
              // In real implementation, update DOM elements here
              showToast();
            }
            if (data) lastUpdated = data.last_updated;
          })
          .catch(console.error)
          .finally(() => {
            setTimeout(pollForUpdates, 60000);
          });
      }

      pollForUpdates();
    });
  </script>
@endsection
