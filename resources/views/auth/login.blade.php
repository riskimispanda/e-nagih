@extends('layouts/blankLayout')

@section('title', 'Login Basic - Pages')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register -->
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="{{ url('/') }}" class="app-brand-link">
                                <img src="{{ asset('assets/nagih.svg') }}" alt="Logo" width="100%" height="205"
                                    class="mx-auto d-block">
                                {{-- <span class="app-brand-logo demo">
                                    <img src="{{ asset('assets/nagih.svg') }}" alt="Logo" height="50">
                                {{-- <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span> --}}
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="text-center mb-5">Welcome to E-Nagih 👋</h4>
                        <p></p>

                        <form id="formAuthentication" class="mb-6" action="/login" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label for="email" class="form-label">Username</label>
                                <input type="text" class="form-control" id="email" name="name" placeholder="Masukan Email Anda" autofocus required />
                            </div>
                            <div class="mb-6 form-password-toggle">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password" required />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-8">
                                <div class="d-flex justify-content-between mt-8">
                                    <a href="{{ url('auth/forgot-password-basic') }}">
                                        <span>Lupa Sandi?</span>
                                    </a>
                                </div>
                            </div>
                            <div class="mb-6">
                                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>
@endsection
