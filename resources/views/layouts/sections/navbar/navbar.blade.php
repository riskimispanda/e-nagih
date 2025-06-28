@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = $navbarDetached ?? '';
@endphp

<style>
    #layout-navbar {
        position: sticky;
        top: 0;
        z-index: 1050;
        background-color: #fff;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    
</style>

<!-- Navbar -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
id="layout-navbar">
@endif
@if (isset($navbarDetached) && $navbarDetached == '')
<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{ $containerNav }}">
        @endif
        
        <!--  Brand demo (display only for navbar-full and hide on below xl) -->
        @if (isset($navbarFull))
        <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                <span class="app-brand-logo demo">
                    <img src="{{ asset('assets/e-nagih.png') }}" alt="Logo" height="100%" width="100%">
                </span>
            </a>
        </div>
        @endif
        
        <!-- ! Not required for layout-without-menu -->
        @if (!isset($navbarHideToggle))
        <div
        class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="bx bx-menu bx-md"></i>
        </a>
    </div>
    @endif
    
    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search bx-md"></i>
                <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2" placeholder="Search..."
                aria-label="Search...">
            </div>
        </div>
        <!-- /Search -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            
            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset(auth()->user()->profile) }}" alt class="w-px-40 h-px-40 rounded-circle">
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset(auth()->user()->profile) }}" alt
                                        class="w-px-40 h-px-40 rounded-circle">
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $users->name }}</h6>
                                    <small class="text-muted">{{ $roles->name }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="/profile-user/{{ Auth::user()->id }}">
                            <i class="bx bx-user bx-md me-3"></i><span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="/logout">
                            <i class="bx bx-power-off bx-md me-3"></i><span>Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
    
    @if (!isset($navbarDetached))
</div>
@endif
</nav>
<!-- / Navbar -->
