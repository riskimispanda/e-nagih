@extends('layouts.blankLayout')

@section('title', 'Login - NBilling')

@section('page-style')
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                },
                colors: {
                    brand: {
                        50: '#eff6ff',
                        100: '#dbeafe',
                        500: '#3b82f6',
                        600: '#2563eb',
                        700: '#1d4ed8',
                        800: '#1e40af',
                        900: '#1e3a8a',
                    }
                }
            }
        }
    }
</script>
<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: #f8fafc !important;
        margin: 0 !important;
        padding: 0 !important;
        min-height: 100vh;
    }
</style>
@endsection

@section('content')
<div class="min-h-screen w-full flex flex-col lg:flex-row bg-slate-50">
    <!-- Sisi Kiri: 50% Layar (Ilustrasi, Center Vertikal & Horizontal) -->
    <div class="hidden lg:flex lg:w-1/2 min-h-screen flex-col items-center justify-center p-8 lg:p-14 bg-slate-100 border-r border-slate-200">
        <div class="w-full max-w-lg text-center flex flex-col items-center justify-center">
            <img
                src="{{ asset('assets/img/illustrations/boy-with-rocket-light.png') }}"
                alt="Ilustrasi Login"
                class="w-full max-h-[420px] xl:max-h-[480px] object-contain mx-auto drop-shadow-sm select-none"
            />
            <h2 class="mt-8 text-2xl font-bold text-slate-800 tracking-tight">
                Sistem Manajemen Billing & Jaringan
            </h2>
            <p class="mt-2.5 text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                Kelola pelanggan, tagihan, dan pemantauan infrastruktur secara terpusat.
            </p>
        </div>
    </div>

    <!-- Sisi Kanan: 50% Layar (Card Login, Center Vertikal & Horizontal) -->
    <div class="w-full lg:w-1/2 min-h-screen flex flex-col items-center justify-center p-6 sm:p-10 lg:p-14 bg-slate-50">
        <div class="w-full max-w-[430px]">
            <!-- Card Container -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
                <!-- Brand / Logo & Title -->
                <div class="text-center mb-6">
                    <a href="{{ url('/') }}" class="inline-block transition-transform hover:opacity-95">
                        <img src="{{ asset('assets/logo_new.png') }}" alt="NBilling Logo" class="h-32 sm:h-36 w-auto mx-auto object-contain" />
                    </a>
                    <h1 class="-mt-2 text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang</h1>
                    <p class="mt-1 text-sm text-slate-500">Silakan masuk untuk mengakses sistem <b>NBilling</b></p>
                </div>

                <!-- Alert Session / Error -->
                @if (session('toast_error') || session('error'))
                    <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-3.5 text-sm text-red-700" role="alert">
                        <svg class="h-5 w-5 shrink-0 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="flex-1 font-medium">
                            {{ session('toast_error') ?? session('error') }}
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-3.5 text-sm text-red-700" role="alert">
                        <svg class="h-5 w-5 shrink-0 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="flex-1">
                            @foreach ($errors->all() as $err)
                                <p class="font-medium">{{ $err }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Form -->
                <form id="formAuthentication" action="/login" method="POST" class="space-y-5">
                    @csrf

                    <!-- Username Field -->
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Username / Email
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input
                                type="text"
                                class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3.5 text-sm text-slate-900 placeholder:text-slate-400 transition-colors focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600"
                                id="email"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Masukkan username Anda"
                                autofocus
                                required
                            />
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Password
                            </label>
                        </div>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-11 text-sm text-slate-900 placeholder:text-slate-400 transition-colors focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600"
                                placeholder="••••••••••••"
                                required
                            />
                            <button
                                type="button"
                                id="passwordToggle"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                                aria-label="Tampilkan / Sembunyikan Password"
                            >
                                <!-- Eye icon (shown when hidden) -->
                                <svg id="eyeIcon" class="h-4 w-4 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <!-- Eye slash icon (hidden by default) -->
                                <svg id="eyeSlashIcon" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            id="submitBtn"
                            class="w-full flex items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 active:bg-blue-800 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <!-- Loading spinner (hidden) -->
                            <svg id="btnSpinner" class="hidden h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="btnText">Login</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer / Copyright -->
            <div class="mt-6 text-center">
                <p class="text-xs text-slate-400">
                    &copy; {{ date('Y') }} NBilling. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';

                if (eyeIcon && eyeSlashIcon) {
                    if (isPassword) {
                        eyeIcon.classList.add('hidden');
                        eyeSlashIcon.classList.remove('hidden');
                    } else {
                        eyeIcon.classList.remove('hidden');
                        eyeSlashIcon.classList.add('hidden');
                    }
                }
            });
        }

        // Form submission loading state
        const form = document.getElementById('formAuthentication');
        const submitBtn = document.getElementById('submitBtn');
        const btnSpinner = document.getElementById('btnSpinner');
        const btnText = document.getElementById('btnText');

        if (form && submitBtn) {
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                if (btnSpinner) btnSpinner.classList.remove('hidden');
                if (btnText) btnText.textContent = 'Memproses...';
            });
        }
    });
</script>
@endsection
