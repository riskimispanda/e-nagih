@extends('layouts.blankLayout')

@section('title', 'Halaman Login - NBilling')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<style>
    :root {
        --primary: #696cff;
        --primary-dark: #5f61e6;
        --text: #566a7f;
        --text-light: #6c757d;
        --border: #d9dee3;
        --white: #ffffff;
        --light-bg: #f5f5f9;
        --shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
        --transition: all 0.3s ease;
    }
    
    body {
        background-color: var(--light-bg);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
    }
    
    .authentication-wrapper {
        width: 100%;
    }
    
    .authentication-inner {
        max-width: 400px;
        margin: 0 auto;
    }
    
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
    }
    
    .card:hover {
        box-shadow: 0 0.5rem 1.5rem rgba(161, 172, 184, 0.5);
    }
    
    .card-body {
        padding: 2.5rem;
    }
    
    .app-brand {
        margin-bottom: 1.5rem;
    }
    
    .app-brand img {
        max-height: 125px;
        object-fit: contain;
    }
    
    h4 {
        color: var(--text);
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        font-weight: 500;
        color: var(--text);
        margin-bottom: 0.5rem;
    }
    
    .form-control {
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        transition: var(--transition);
    }
    
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.2);
    }
    
    .input-group-merge .form-control {
        border-right: 0;
    }
    
    .input-group-text {
        background-color: var(--white);
        border: 1px solid var(--border);
        border-left: 0;
        transition: var(--transition);
        cursor: pointer;
    }
    
    .input-group-merge:focus-within .input-group-text {
        border-color: var(--primary);
    }
    
    .input-group-text i {
        color: var(--text-light);
    }
    
    .btn-primary {
        background-color: var(--primary);
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem;
        font-weight: 500;
        transition: var(--transition);
    }
    
    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }
    
    a {
        color: var(--primary);
        text-decoration: none;
        transition: var(--transition);
    }
    
    a:hover {
        color: var(--primary-dark);
    }
    
    /* Animasi untuk elemen form */
    .form-group {
        position: relative;
        margin-bottom: 1.5rem;
    }
    
    /* Responsif untuk mobile */
    @media (max-width: 576px) {
        .card-body {
            padding: 2rem 1.5rem;
        }
    }
    
    /* Efek loading pada tombol */
    .btn-loading {
        position: relative;
        color: transparent;
    }
    
    .btn-loading::after {
        content: "";
        position: absolute;
        width: 20px;
        height: 20px;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        border: 2px solid transparent;
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: button-loading-spinner 1s ease infinite;
    }
    
    @keyframes button-loading-spinner {
        from {
            transform: rotate(0turn);
        }
        to {
            transform: rotate(1turn);
        }
    }
</style>
@section('content')
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <!-- Login Card -->
            <div class="card px-sm-6 px-0">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center">
                        <a href="{{ url('/') }}" class="app-brand-link">
                            <img src="{{ asset('assets/logo_new.png') }}" alt="Logo" class="mx-auto d-block">
                        </a>
                    </div>
                    <!-- /Logo -->
                    
                    <h4 class="text-center">Welcome to NBilling 👋</h4>
                    
                    <form id="formAuthentication" class="mb-6" action="/login" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="email" class="form-label">Username</label>
                            <input type="text" class="form-control" id="email" name="name" placeholder="Masukan Email Anda" autofocus required />
                        </div>
                        <div class="mb-4 form-password-toggle">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="password" required />
                                <span class="input-group-text cursor-pointer" id="passwordToggle"><i class="bx bx-hide"></i></span>
                            </div>
                        </div>
                        <div class="mb-3 mt-5">
                            <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Debug: Cek apakah script berjalan
    console.log('Script loaded');
    
    // Tunggu sampai DOM sepenuhnya dimuat
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded');
        
        // Toggle visibility password - PENDEKATAN LEBIH SIMPLE
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        
        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', function() {
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.innerHTML = '<i class="bx bx-show"></i>';
                } else {
                    passwordInput.type = 'password';
                    this.innerHTML = '<i class="bx bx-hide"></i>';
                }
            });
        }
        
        // Menambahkan efek loading pada tombol login
        const form = document.getElementById('formAuthentication');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.classList.remove('btn-loading');
                    submitBtn.disabled = false;
                }, 2000);
            });
        }
    });

    // Alternatif: Event delegation untuk memastikan event terpasang
    document.addEventListener('click', function(e) {
        if (e.target.closest('#passwordToggle') || e.target.closest('.input-group-text')) {
            const passwordInput = document.getElementById('password');
            const toggleElement = e.target.closest('#passwordToggle') || e.target.closest('.input-group-text');
            
            if (passwordInput && toggleElement) {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleElement.innerHTML = '<i class="bx bx-show"></i>';
                } else {
                    passwordInput.type = 'password';
                    toggleElement.innerHTML = '<i class="bx bx-hide"></i>';
                }
            }
        }
    });
</script>
@endsection