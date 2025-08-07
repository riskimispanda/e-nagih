@extends('layouts.contentNavbarLayout')

@section('title', 'Profile User')

@section('page-style')
    <style>
        :root {
            --card-border-radius: 20px;
            --primary-color-light: rgba(var(--bs-primary-rgb), 0.08);
            --box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            --box-shadow-hover: 0 10px 35px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .profile-card {
            border: none;
            border-radius: var(--card-border-radius) !important;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .profile-card:hover {
            box-shadow: var(--box-shadow-hover);
        }

        .profile-header {
            background: linear-gradient(to right, rgba(var(--bs-primary-rgb), 0.05), rgba(var(--bs-primary-rgb), 0.01));
            border-bottom: 1px solid rgba(var(--bs-primary-rgb), 0.08);
        }

        .profile-avatar {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .info-box {
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 12px;
            padding: 1.25rem;
            transition: var(--transition);
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .info-box:hover {
            background-color: var(--primary-color-light);
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
            border-color: rgba(var(--bs-primary-rgb), 0.1);
        }

        .info-box .icon {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            display: inline-block;
            color: var(--bs-primary);
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            padding: 10px;
            border-radius: 12px;
            align-items: center;
            justify-content: center;
        }

        .custom-btn {
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .custom-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(var(--bs-primary-rgb), 0.2);
        }

        .custom-input {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .custom-input:focus {
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.1);
            border-color: var(--bs-primary);
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            background-color: rgba(var(--bs-primary-rgb), 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-right: none;
        }

        .form-control {
            border-radius: 0 10px 10px 0;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1rem;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .badge-role {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
        }

        .modal-content {
            border-radius: var(--card-border-radius);
            border: none;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card profile-card mb-4">
                <div class="card-body p-0">
                    <div class="profile-header p-4 p-md-5">
                        <div class="d-flex flex-column flex-md-row align-items-center">
                            <div class="text-center mb-4 mb-md-0">
                                <div class="position-relative d-inline-block">
                                    <img src="{{ asset(auth()->user()->profile) ?? asset('assets/img/avatars/default.png') }}"
                                        alt="user image" class="rounded-circle profile-avatar user-profile-img">
                                    <div class="position-absolute bottom-0 end-0">
                                        <span class="badge rounded-pill bg-success p-2 border border-light border-2"
                                            style="width: 16px; height: 16px;"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-grow-1 ms-md-4 text-center text-md-start">
                                <div
                                    class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-start mb-2">
                                    <div>
                                        <h2 class="fw-bold mb-1">{{ auth()->user()->name ?? 'User Name' }}</h2>
                                        <span
                                            class="badge bg-label-primary badge-role mb-3">{{ auth()->user()->roles()->first()->name ?? 'User Role' }}</span>
                                    </div>
                                    <div class="mt-3 mt-md-0">
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editProfileModal">
                                            <i class="bx bx-edit-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-4 col-sm-6">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex justify-content-center align-items-center me-3 rounded-circle bg-label-primary"
                                                style="width: 38px; height: 38px;">
                                                <i class="bx bx-envelope"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Email</div>
                                                <div class="fw-medium">{{ auth()->user()->email ?? 'user@example.com' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex justify-content-center align-items-center me-3 rounded-circle bg-label-primary"
                                                style="width: 38px; height: 38px;">
                                                <i class="bx bx-phone-call"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">No Telepon</div>
                                                <div class="fw-medium">{{ auth()->user()->no_hp ?? '+1 (123) 456-7890' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex justify-content-center align-items-center me-3 rounded-circle bg-label-primary"
                                                style="width: 38px; height: 38px;">
                                                <i class="bx bx-calendar"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Bergabung</div>
                                                <div class="fw-medium">
                                                    {{ auth()->user()->created_at ? auth()->user()->created_at->format('d M Y') : '01 Jan 2023' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-sm-12">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex justify-content-center align-items-center me-3 rounded-circle bg-label-primary" style="width: 38px; height: 38px;">
                                                <i class="bx bx-message"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Bio</div>
                                                <div class="fw-medium">{{ auth()->user()->bio ?? 'Tidak ada bio' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Profile Photo Upload -->
        <div class="col-md-6 col-12">
            <div class="card profile-card h-100">
                <div class="card-header mb-5 d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 fw-bold">
                        <i class="bx bx-camera text-primary me-2"></i>Profile Photo
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/update-photo/{{ auth()->user()->id }}" method="POST" enctype="multipart/form-data"
                        id="profilePhotoForm">
                        @csrf

                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img src="{{ asset(auth()->user()->profile) ?? asset('assets/img/avatars/default.png') }}"
                                    alt="user-avatar" class="rounded-circle profile-avatar" id="uploadedAvatar">
                                <label for="upload"
                                    class="position-absolute bottom-0 end-0 bg-primary rounded-circle p-2 cursor-pointer shadow-sm"
                                    style="transform: translate(25%, 25%);">
                                    <i class="bx bx-upload text-white" style="font-size: 1rem;"></i>
                                    <input type="file" id="upload" name="profile_photo" class="account-file-input"
                                        hidden accept="image/png, image/jpeg">
                                </label>
                            </div>

                            <div class="mt-3">
                                <h6 class="fw-semibold">{{ auth()->user()->name ?? 'User Name' }}</h6>
                                <p class="text-muted mb-0">{{ auth()->user()->email ?? 'user@example.com' }}</p>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center">
                                <div class="d-flex justify-content-center align-items-center me-3 rounded-circle bg-label-warning"
                                    style="width: 44px; height: 38px; min-width: 38px;">
                                    <i class="bx bx-info-circle"></i>
                                </div>
                                <div class="text-muted small">
                                    Unggah gambar berkualitas tinggi untuk membuat profil Anda menonjol.
                                    Gambar persegi bekerja paling baik.
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="d-flex justify-content-center align-items-center me-3 rounded-circle bg-label-warning"
                                    style="width: 38px; height: 38px; min-width: 38px;">
                                    <i class="bx bx-file"></i>
                                </div>
                                <div class="text-muted small">
                                    file types: JPG, PNG. Maximum size: 2MB.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-sm account-image-reset">
                                <i class="bx bx-refresh me-1"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-save me-1"></i>Save Photo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-md-6 col-12">
            <div class="card profile-card h-100">
                <div class="card-header mb-5 d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 fw-bold">
                        <i class="bx bx-lock text-primary me-2"></i>Ganti Password
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formChangePassword" action="/update/password/{{ auth()->user()->id }}" method="POST">
                        @csrf
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex justify-content-center align-items-center me-3 rounded-circle bg-label-warning"
                                    style="width: 38px; height: 38px; min-width: 38px;">
                                    <i class="bx bx-shield"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold">Ganti Password</h6>
                                    <p class="text-muted mb-0 small">Pastikan akun Anda tetap aman dengan kata sandi yang kuat</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 form-password-toggle mb-5">
                            <label class="form-label fw-medium" for="newPassword">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input class="form-control custom-input" type="password" id="newPassword"
                                    name="password" placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary border border-start-0" type="button"
                                    id="toggleNewPassword">
                                    <i class="bx bx-hide"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-lock me-2"></i>Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Details -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card profile-card">
                <div class="card-header mb-4 d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 fw-bold">
                        <i class="bx bx-user text-primary me-2"></i>Profile Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4 col-sm-6">
                            <div class="info-box">
                                <div class="icon">
                                    <i class="bx bx-user"></i>
                                </div>
                                <h6 class="text-muted small mb-1">Full Name</h6>
                                <h5 class="fw-semibold mb-0">{{ auth()->user()->name ?? 'User Name' }}</h5>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="info-box">
                                <div class="icon">
                                    <i class="bx bx-envelope"></i>
                                </div>
                                <h6 class="text-muted small mb-1">Email Address</h6>
                                <h5 class="fw-semibold mb-0">{{ auth()->user()->email ?? 'user@example.com' }}</h5>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="info-box">
                                <div class="icon">
                                    <i class="bx bx-phone"></i>
                                </div>
                                <h6 class="text-muted small mb-1">No Telepon</h6>
                                <h5 class="fw-semibold mb-0">{{ auth()->user()->no_hp ?? '+1 (123) 456-7890' }}</h5>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="info-box">
                                <div class="icon">
                                    <i class="bx bx-shield"></i>
                                </div>
                                <h6 class="text-muted small mb-1">User Role</h6>
                                <h5 class="fw-semibold mb-0">{{ auth()->user()->roles()->first()->name ?? 'User Role' }}
                                </h5>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="info-box">
                                <div class="icon">
                                    <i class="bx bx-check"></i>
                                </div>
                                <h6 class="text-muted small mb-1">Account Status</h6>
                                <div class="d-flex align-items-center mt-2">
                                    <span class="bg-success rounded-circle me-2"
                                        style="width: 10px; height: 10px;"></span>
                                    <h5 class="fw-semibold mb-0">Active</h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="info-box">
                                <div class="icon">
                                    <i class="bx bx-calendar"></i>
                                </div>
                                <h6 class="text-muted small mb-1">Bergabung</h6>
                                <h5 class="fw-semibold mb-0">
                                    {{ auth()->user()->created_at ? auth()->user()->created_at->format('d M Y') : '01 Jan 2023' }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-5 px-sm-5 pt-0">
                    <div class="text-center mb-4">
                        <div class="avatar avatar-xl mb-3 bg-label-primary rounded-circle mx-auto">
                            <img src="{{ asset(auth()->user()->profile) ?? asset('assets/img/avatars/default.png') }}"
                                alt="Not Found" class="w-100 h-100 rounded-circle"" />
                        </div>
                        <h3 class="mb-1 fw-bold">Edit Profile Information</h3>
                        <p class="text-muted">Update Informasi Profil Kamu</p>
                    </div>

                    <form id="editUserForm" class="row g-3" action="/update/user/{{ auth()->user()->id }}" method="POST">
                        @csrf

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium" for="modalEditUserName">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" id="modalEditUserName" name="name" class="form-control custom-input" value="{{ auth()->user()->name ?? '' }}" placeholder="John Doe" />
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium" for="modalEditUserEmail">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-mail-send"></i></span>
                                <input type="email" id="modalEditUserEmail" name="email" class="form-control custom-input" value="{{ auth()->user()->email ?? '' }}" placeholder="example@domain.com" />
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium" for="modalEditUserPhone">No Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input type="text" id="modalEditUserPhone" name="phone" class="form-control custom-input" value="{{ auth()->user()->no_hp ?? '' }}" placeholder="+1 (123) 456-7890" />
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium" for="modalEditUserAddress">Alamat</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
                                <input type="text" id="modalEditUserAddress" name="address" class="form-control custom-input" value="{{ auth()->user()->alamat ?? '' }}" placeholder="1234 Main St" />
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium" for="modalEditUserBio">Bio</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-edit-alt"></i></span>
                                <textarea id="modalEditUserBio" name="bio" class="form-control custom-input" placeholder="Tell us about yourself" rows="3">{{ auth()->user()->bio ?? '' }}</textarea>
                            </div>
                            <small class="text-muted">Beri Bio Untuk Informasi Profil Kamu.</small>
                        </div>

                        <div class="col-12 d-flex justify-content-center gap-2 mt-4">
                            <button type="reset" class="btn btn-outline-secondary custom-btn" data-bs-dismiss="modal">
                                <i class="ti ti-x me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary custom-btn">
                                <i class="ti ti-device-floppy me-1"></i>Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Profile Photo Upload Preview
            const uploadInput = document.querySelector('.account-file-input');
            const accountUserImage = document.querySelector('.user-profile-img');
            const uploadedAvatar = document.getElementById('uploadedAvatar');
            const resetButton = document.querySelector('.account-image-reset');

            if (uploadInput) {
                uploadInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (accountUserImage) accountUserImage.src = e.target.result;
                            if (uploadedAvatar) uploadedAvatar.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            if (resetButton) {
                resetButton.addEventListener('click', function() {
                    const defaultImageUrl = '{{ asset('assets/img/avatars/default.png') }}';
                    if (accountUserImage) accountUserImage.src = defaultImageUrl;
                    if (uploadedAvatar) uploadedAvatar.src = defaultImageUrl;
                    if (uploadInput) uploadInput.value = '';
                });
            }

            // Password visibility toggle for new password inputs
            const togglePasswordButtons = document.querySelectorAll(
                '#toggleCurrentPassword, #toggleNewPassword, #toggleConfirmPassword');
            if (togglePasswordButtons.length) {
                togglePasswordButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const input = this.closest('.input-group').querySelector('input');
                        const icon = this.querySelector('i');

                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.classList.remove('ti-eye-off');
                            icon.classList.add('ti-eye');
                        } else {
                            input.type = 'password';
                            icon.classList.remove('ti-eye');
                            icon.classList.add('ti-eye-off');
                        }
                    });
                });
            }

            // Legacy password toggle support
            const togglePasswordIcons = document.querySelectorAll('.form-password-toggle i');
            if (togglePasswordIcons.length) {
                togglePasswordIcons.forEach(icon => {
                    icon.addEventListener('click', e => {
                        const input = e.target.closest('.input-group').querySelector('input');
                        if (input.type === 'password') {
                            input.type = 'text';
                            e.target.classList.remove('ti-eye-off');
                            e.target.classList.add('ti-eye');
                        } else {
                            input.type = 'password';
                            e.target.classList.remove('ti-eye');
                            e.target.classList.add('ti-eye-off');
                        }
                    });
                });
            }

            // Add animations to info boxes
            const infoBoxes = document.querySelectorAll('.info-box');
            if (infoBoxes.length) {
                // Add staggered animation on page load
                infoBoxes.forEach((box, index) => {
                    setTimeout(() => {
                        box.style.opacity = '1';
                        box.style.transform = 'translateY(0)';
                    }, 100 * index);
                });
            }

            // Form validation
            const forms = document.querySelectorAll('form');
            if (forms.length) {
                forms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        // You can add custom validation here if needed
                        // This is just a placeholder for future validation logic
                        console.log('Form submitted:', this.id);
                    });
                });
            }

            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('.btn');
            if (buttons.length) {
                buttons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        const rect = button.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const y = e.clientY - rect.top;

                        const ripple = document.createElement('span');
                        ripple.classList.add('ripple');
                        ripple.style.left = `${x}px`;
                        ripple.style.top = `${y}px`;

                        this.appendChild(ripple);

                        setTimeout(() => {
                            ripple.remove();
                        }, 600);
                    });
                });
            }

            // Initialize tooltips if Bootstrap's tooltip is available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltips.forEach(tooltip => {
                    new bootstrap.Tooltip(tooltip);
                });
            }
        });
    </script>

    <style>
        /* Add ripple effect styles */
        .btn {
            position: relative;
            overflow: hidden;
        }

        .ripple {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
            width: 100px;
            height: 100px;
            transform: translate(-50%, -50%);
        }

        @keyframes ripple {
            to {
                transform: translate(-50%, -50%) scale(3);
                opacity: 0;
            }
        }

        /* Add staggered animation for info boxes */
        .info-box {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
    </style>
@endsection
