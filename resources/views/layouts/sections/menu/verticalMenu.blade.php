@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$menuUser = Auth::user();
$menuUserName = optional($menuUser)->name ?? 'User';
$menuUserRole = optional(optional($menuUser)->roles)->name ?? 'User';
$menuUserProfile = optional($menuUser)->profile;
$menuUserId = optional($menuUser)->id;
$menuUserInitial = strtoupper(mb_substr(trim($menuUserName), 0, 1)) ?: 'U';

$currentPath = trim(request()->path(), '/');
$currentRouteName = Route::currentRouteName();

$normalizeMenuPath = function ($url) {
    if (!is_string($url) || $url === '' || $url === '#' || str_starts_with($url, 'javascript:')) {
        return null;
    }

    $path = parse_url($url, PHP_URL_PATH);

    return is_string($path) ? trim($path, '/') : null;
};

$isMenuUrlActive = function ($url) use ($currentPath, $normalizeMenuPath) {
    $path = $normalizeMenuPath($url);

    if ($path === null) {
        return false;
    }

    if ($path === '') {
        return $currentPath === '';
    }

    return $currentPath === $path || str_starts_with($currentPath, $path . '/');
};

$isMenuDirectActive = function ($item) use ($currentPath, $currentRouteName, $isMenuUrlActive) {
    if (isset($item->url) && $isMenuUrlActive($item->url)) {
        return true;
    }

    return isset($item->slug)
        && is_string($item->slug)
        && $item->slug !== '-'
        && $item->slug === $currentRouteName;
};

$isMenuChildActive = function ($item) use ($isMenuDirectActive) {
    if (!isset($item->submenu)) {
        return false;
    }

    foreach ($item->submenu as $child) {
        if ($isMenuDirectActive($child)) {
            return true;
        }
    }

    return false;
};
@endphp

<style>
    /* ============================================================
       Vertical menu — clean neutral (scoped to #layout-menu)
       ============================================================ */
    #layout-menu {
        --vm-bg: #ffffff;
        --vm-surface: #f8fafc;
        --vm-border: #e2e8f0;
        --vm-border-soft: #f1f5f9;
        --vm-text: #475569;
        --vm-text-strong: #1e293b;
        --vm-muted: #94a3b8;
        --vm-accent: #4f46e5;
        --vm-accent-strong: #4338ca;
        --vm-accent-soft: #eef2ff;
        --vm-accent-border: #e0e7ff;
        --vm-radius: 12px;
        --vm-brand-height: 91px;

        display: flex;
        flex-direction: column;
        height: 100dvh;
        overflow: hidden;
        background: var(--vm-bg);
        border-right: 1px solid var(--vm-border);
        box-shadow: none;
    }

    /* Brand ------------------------------------------------------ */
    #layout-menu .app-brand {
        flex: 0 0 auto;
        min-height: 0;
        padding: 14px 14px 12px;
        background: var(--vm-bg);
        border-bottom: 1px solid var(--vm-border);
    }

    #layout-menu .app-brand-link {
        width: 100%;
        justify-content: center;
    }

    #layout-menu .app-brand-logo {
        width: 100%;
        height: auto;
        background: transparent;
        border-radius: 10px;
        overflow: hidden;
    }

    #layout-menu .app-brand-logo img {
        display: block;
        width: 100%;
        height: 64px;
        object-fit: cover;
        border-radius: 10px;
    }

    #layout-menu .app-brand .layout-menu-toggle {
        background: var(--vm-bg);
        border: 1px solid var(--vm-border);
        box-shadow: 0 6px 16px -8px rgba(15, 23, 42, 0.35);
    }

    #layout-menu .app-brand .layout-menu-toggle i {
        color: var(--vm-accent);
    }

    /* Scroll area ------------------------------------------------ */
    #layout-menu .menu-inner {
        position: relative;
        flex: 1 1 auto;
        min-height: 0;
        height: auto;
        max-height: none;
        padding: 10px 8px 14px;
        overflow: hidden;
        background: var(--vm-bg);
    }

    /* Section headers ------------------------------------------- */
    #layout-menu .menu-header {
        display: flex;
        align-items: center;
        gap: 8px;
        width: auto;
        margin: 16px 10px 6px;
        padding: 0;
        position: relative;
    }

    #layout-menu .menu-header::after {
        content: '';
        flex: 1 1 auto;
        height: 1px;
        background: var(--vm-border);
    }

    #layout-menu .menu-header::before {
        display: none;
    }

    #layout-menu .menu-header .menu-header-text {
        flex: 0 0 auto;
        color: var(--vm-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        line-height: 1.4;
        white-space: nowrap;
    }

    /* Main menu items ------------------------------------------- */
    #layout-menu .menu-inner > .menu-item {
        width: 100%;
        margin: 2px 0;
    }

    #layout-menu .menu-inner > .menu-item > .menu-link {
        width: 100%;
        min-height: 42px;
        margin: 0;
        padding: 6px 10px;
        border: 1px solid transparent;
        border-radius: var(--vm-radius);
        color: var(--vm-text);
        background: transparent;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.25;
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    #layout-menu .menu-inner > .menu-item > .menu-link > .menu-title {
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1.35;
    }

    #layout-menu .menu-inner > .menu-item > .menu-link.menu-toggle {
        padding-right: 34px;
    }

    #layout-menu .menu-inner > .menu-item > .menu-link:hover {
        background: var(--vm-surface);
        border-color: var(--vm-border-soft);
        color: var(--vm-text-strong);
    }

    /* Icon tile ------------------------------------------------- */
    #layout-menu .menu-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        margin-right: 10px;
        margin-left: 0;
        border: 1px solid var(--vm-border);
        border-radius: 10px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 17px;
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    #layout-menu .menu-inner > .menu-item > .menu-link:hover .menu-icon {
        background: var(--vm-accent-soft);
        border-color: var(--vm-accent-border);
        color: var(--vm-accent);
    }

    /* Active / parent with active child ------------------------- */
    #layout-menu .menu-inner > .menu-item.active > .menu-link {
        background: var(--vm-accent-soft) !important;
        border-color: var(--vm-accent-border);
        color: var(--vm-accent-strong);
        box-shadow: 0 1px 2px rgba(79, 70, 229, 0.06);
    }

    #layout-menu .menu-inner > .menu-item.active > .menu-link .menu-icon {
        background: var(--vm-accent);
        border-color: var(--vm-accent);
        color: #ffffff;
    }

    #layout-menu .menu-inner > .menu-item.active:before {
        display: none;
    }

    #layout-menu .menu-inner > .menu-item.open > .menu-link {
        background: var(--vm-surface);
        border-color: var(--vm-border-soft);
        color: var(--vm-text-strong);
    }

    #layout-menu .menu-inner > .menu-item.has-active-child > .menu-link {
        background: var(--vm-surface);
        border-color: var(--vm-border-soft);
        color: var(--vm-text-strong);
    }

    #layout-menu .menu-inner > .menu-item.has-active-child > .menu-link .menu-icon {
        background: var(--vm-accent-soft);
        border-color: var(--vm-accent-border);
        color: var(--vm-accent);
    }

    /* Chevron --------------------------------------------------- */
    #layout-menu .menu-toggle::after {
        right: 12px;
        color: var(--vm-muted);
        font-size: 16px;
        opacity: 0.9;
    }

    #layout-menu .menu-item.open > .menu-link.menu-toggle::after {
        color: var(--vm-accent);
    }

    /* Submenu --------------------------------------------------- */
    #layout-menu .menu-sub {
        position: relative;
        margin: 2px 0 8px;
        padding: 2px 0;
    }

    #layout-menu .menu-sub::before {
        content: '';
        position: absolute;
        top: 8px;
        bottom: 8px;
        left: 25px;
        width: 1px;
        background: var(--vm-border);
    }

    #layout-menu .menu-sub > .menu-item {
        width: 100%;
        margin: 1px 0;
    }

    #layout-menu .menu-sub .menu-link {
        min-height: 30px;
        margin: 0;
        padding: 5px 10px 5px 50px;
        border: 1px solid transparent;
        border-radius: 9px;
        color: #64748b;
        background: transparent;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.35;
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    #layout-menu .menu-sub .menu-link:hover {
        background: var(--vm-surface);
        border-color: var(--vm-border-soft);
        color: var(--vm-text-strong);
    }

    #layout-menu .menu-sub .menu-icon {
        display: none;
    }

    #layout-menu .menu-sub > .menu-item > .menu-link::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 22px;
        width: 6px;
        height: 6px;
        border: 0 !important;
        border-radius: 50%;
        background-color: #cbd5e1 !important;
        transform: translateY(-50%);
    }

    #layout-menu .menu-sub > .menu-item.active > .menu-link {
        background: var(--vm-accent-soft) !important;
        border-color: var(--vm-accent-border);
        color: var(--vm-accent-strong);
        font-weight: 600;
    }

    #layout-menu .menu-sub > .menu-item.active > .menu-link::before {
        left: 21px;
        width: 8px;
        height: 8px;
        background-color: var(--vm-accent) !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }

    /* Badge ----------------------------------------------------- */
    #layout-menu .menu-link .badge {
        margin-left: auto;
        padding: 0.3rem 0.5rem;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
        letter-spacing: 0.02em;
    }

    /* Scroll shadow -------------------------------------------- */
    #layout-menu .menu-inner-shadow {
        top: var(--vm-brand-height);
        height: 16px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(255, 255, 255, 0));
    }

    /* User card ------------------------------------------------- */
    #layout-menu .menu-user-card {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 8px 10px 12px;
        padding: 10px;
        border: 1px solid var(--vm-border);
        border-radius: 14px;
        background: var(--vm-surface);
    }

    #layout-menu .menu-user-avatar {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: var(--vm-accent-soft);
        color: var(--vm-accent-strong);
        font-size: 12px;
        font-weight: 800;
        overflow: visible;
    }

    #layout-menu .menu-user-avatar img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border-radius: 10px;
        object-fit: cover;
    }

    #layout-menu .menu-user-status {
        position: absolute;
        right: -2px;
        bottom: -2px;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #10b981;
        border: 2px solid var(--vm-surface);
    }

    #layout-menu .menu-user-meta {
        display: flex;
        flex: 1 1 auto;
        min-width: 0;
        flex-direction: column;
        gap: 2px;
        line-height: 1.2;
    }

    #layout-menu .menu-user-name {
        overflow: hidden;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #layout-menu .menu-user-role {
        overflow: hidden;
        color: var(--vm-muted);
        font-size: 10px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #layout-menu .menu-user-action {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        color: var(--vm-muted);
        text-decoration: none;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    #layout-menu .menu-user-action:hover {
        background: var(--vm-accent-soft);
        color: var(--vm-accent);
    }

    /* Focus state ----------------------------------------------- */
    #layout-menu .menu-link:focus-visible {
        outline: 2px solid var(--vm-accent);
        outline-offset: 2px;
    }

    /* Collapsed sidebar ----------------------------------------- */
    html.layout-menu-collapsed:not(.layout-menu-hover) #layout-menu .menu-inner > .menu-item > .menu-link {
        justify-content: center;
        padding: 6px;
    }

    html.layout-menu-collapsed:not(.layout-menu-hover) #layout-menu .menu-inner > .menu-item > .menu-link.menu-toggle {
        padding-right: 6px;
    }

    html.layout-menu-collapsed:not(.layout-menu-hover) #layout-menu .menu-inner > .menu-item > .menu-link .menu-icon {
        margin: 0;
    }

    html.layout-menu-collapsed:not(.layout-menu-hover) #layout-menu .menu-inner > .menu-item > .menu-link.menu-toggle::after {
        display: none;
    }

    html.layout-menu-collapsed:not(.layout-menu-hover) #layout-menu .menu-inner > .menu-header {
        justify-content: center;
        margin: 12px 10px 6px;
        padding: 0;
        width: auto;
        text-indent: 0;
    }

    html.layout-menu-collapsed:not(.layout-menu-hover) #layout-menu .menu-inner > .menu-header .menu-header-text,
    html.layout-menu-collapsed:not(.layout-menu-hover) #layout-menu .menu-inner > .menu-header::after {
        display: none;
    }

    html.layout-menu-collapsed:not(.layout-menu-hover) #layout-menu .menu-user-card {
        justify-content: center;
        padding: 8px;
    }

    html.layout-menu-collapsed:not(.layout-menu-hover) #layout-menu .menu-user-meta,
    html.layout-menu-collapsed:not(.layout-menu-hover) #layout-menu .menu-user-action {
        display: none;
    }

    @media (max-height: 520px) {
        #layout-menu .menu-user-card {
            display: none;
        }
    }
</style>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <!-- ! Hide app brand if navbar-full -->
    <div class="app-brand demo justify-content-center">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/logo_new.png') }}" alt="Logo" height="64">
            </span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)
        @if (isset($menu->roles) && in_array($menuUserRole, $menu->roles, true))
        {{-- menu headers --}}
        @if (isset($menu->menuHeader))
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
        </li>
        @else
        @php
        $hasSubmenu = isset($menu->submenu) && count((array) $menu->submenu) > 0;
        $directActive = $isMenuDirectActive($menu);
        $childActive = $isMenuChildActive($menu);
        $itemIsOpen = $hasSubmenu && ($directActive || $childActive);
        $itemIsActive = $directActive && !$hasSubmenu;
        $hasActiveChild = $hasSubmenu && ($directActive || $childActive);
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{ $itemIsActive ? 'active' : '' }} {{ $itemIsOpen ? 'open' : '' }} {{ $hasActiveChild ? 'has-active-child' : '' }}">
            <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                class="{{ $hasSubmenu ? 'menu-link menu-toggle' : 'menu-link' }}"
                @if ($itemIsActive) aria-current="page" @endif
                @if (isset($menu->target) && !empty($menu->target)) target="{{ $menu->target }}" rel="noopener" @endif>
                @isset($menu->icon)
                <i class="{{ $menu->icon }}" aria-hidden="true"></i>
                @endisset
                <div class="menu-title">{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                @isset($menu->badge)
                <div class="badge rounded-pill bg-{{ $menu->badge[0] }} text-uppercase ms-auto">
                    {{ $menu->badge[1] }}</div>
                @endisset
            </a>

            {{-- submenu --}}
            @if ($hasSubmenu)
            <ul class="menu-sub">
                @foreach ($menu->submenu as $submenu)
                @if (isset($submenu->roles) && in_array($menuUserRole, $submenu->roles, true))
                @php
                $submenuIsActive = $isMenuDirectActive($submenu);
                @endphp
                <li class="menu-item {{ $submenuIsActive ? 'active' : '' }}">
                    <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}"
                        class="menu-link"
                        @if ($submenuIsActive) aria-current="page" @endif>
                        @isset($submenu->icon)
                        <i class="{{ $submenu->icon }}" aria-hidden="true"></i>
                        @endisset
                        <div class="menu-title">{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
                    </a>
                </li>
                @endif
                @endforeach
            </ul>
            @endif
        </li>
        @endif
        @endif
        @endforeach
    </ul>

    <div class="menu-user-card">
        <div class="menu-user-avatar" aria-hidden="true">
            <span>{{ $menuUserInitial }}</span>
            @if ($menuUserProfile)
            <img src="{{ asset($menuUserProfile) }}" alt="" onerror="this.remove()">
            @endif
            <span class="menu-user-status"></span>
        </div>
        <div class="menu-user-meta">
            <span class="menu-user-name" title="{{ $menuUserName }}">{{ $menuUserName }}</span>
            <span class="menu-user-role" title="{{ $menuUserRole }}">{{ $menuUserRole }}</span>
        </div>
        <a href="{{ $menuUserId ? url('/profile-user/' . $menuUserId) : 'javascript:void(0);' }}"
            class="menu-user-action" title="Buka profil" aria-label="Buka profil">
            <i class="bx bx-user"></i>
        </a>
    </div>

</aside>
