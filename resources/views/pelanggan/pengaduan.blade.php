@extends('layouts/contentNavbarLayout')
@section('title', 'Form Pengaduan')

@section('vendor-style')
    <style>
        /* Variabel warna dan style dasar */
        :root {
            --primary-color: #696cff;
            --primary-light: rgba(105, 108, 255, 0.1);
            --secondary-color: #8592a3;
            --success-color: #71dd37;
            --info-color: #03c3ec;
            --warning-color: #ffab00;
            --danger-color: #ff3e1d;
            --dark-color: #233446;
            --light-color: #f9fafb;
            --border-color: #eaeaec;
            --text-color: #566a7f;
            --text-muted: #a1acb8;
            --body-bg: #f5f5f9;
        }

        /* Sticky progress bar styles */
        .sticky-progress {
            position: -webkit-sticky;
            /* For Safari */
            position: sticky !important;
            /* Force sticky positioning */
            top: 10px !important;
            /* Add some space from the top */
            z-index: 1030 !important;
            /* Higher z-index to ensure it's above other elements */
            /* transition: all 0.3s ease; */
            background-color: white;
            width: 100%;
            margin-left: 0 !important;
            margin-right: 0 !important;
            display: block !important;
        }

        .sticky-progress.is-sticky {
            /* box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); */
            /* padding: 10px 0; */
            /* Add some padding */
            border-radius: 0;
            margin-bottom: 0;
            left: 0;
            right: 0;
        }

        .sticky-progress.is-sticky .card {
            margin-bottom: 0 !important;
            border-radius: 0 !important;
            /* background-color: transparent; */
            /* transition: background-color 0.3s ease; */
        }

        /* Add a transition for smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Fix for any parent containers that might interfere with sticky positioning */
        .content-wrapper,
        .container-xxl,
        .container-fluid {
            overflow: visible !important;
        }

        /* Card dan container styles */
        .card {
            border: none;
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            background-color: #fff;
            transition: all 0.2s ease;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-footer {
            background-color: transparent;
            border-top: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }

        /* Form elements */
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .form-control,
        .form-select {
            border-radius: 0.375rem;
            border-color: var(--border-color);
            padding: 0.5rem 0.875rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.15rem var(--primary-light);
        }

        .form-text {
            font-size: 0.8125rem;
            color: var(--text-muted);
        }

        /* Complaint type cards */
        .complaint-type-card {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .complaint-type-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .complaint-type-card.selected {
            border-color: var(--primary-color);
            background-color: var(--primary-light);
        }

        .complaint-type-card .icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: var(--primary-light);
            margin-bottom: 1rem;
        }

        .complaint-type-card h6 {
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
        }

        .complaint-type-card p {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin-bottom: 0;
        }

        /* File upload area */
        .file-upload-wrapper {
            position: relative;
            width: 100%;
            height: 120px;
            border: 2px dashed var(--border-color);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: var(--light-color);
            transition: all 0.2s ease;
        }

        .file-upload-wrapper:hover {
            border-color: var(--primary-color);
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-content {
            text-align: center;
        }

        .file-upload-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .file-upload-text {
            font-size: 0.875rem;
            color: var(--text-color);
        }

        .file-upload-info {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Progress bar */
        .progress {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            background-color: #f0f0f0;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            background-color: var(--primary-color);
            transition: width 0.5s ease;
            background-image: linear-gradient(45deg,
                    rgba(255, 255, 255, 0.15) 25%,
                    transparent 25%,
                    transparent 50%,
                    rgba(255, 255, 255, 0.15) 50%,
                    rgba(255, 255, 255, 0.15) 75%,
                    transparent 75%,
                    transparent);
            background-size: 1rem 1rem;
        }

        .progress-bar-success {
            background-color: var(--success-color) !important;
        }

        /* Progress steps */
        .progress-step {
            transition: all 0.3s ease;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
        }

        .progress-step.text-primary {
            background-color: var(--primary-light);
        }

        /* Badge styles */
        .badge {
            font-weight: 500;
            transition: all 0.3s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {

            .card-header,
            .card-body,
            .card-footer {
                padding: 1.25rem;
            }

            .complaint-type-card {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 mx-auto">
            <!-- Page header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Form Pengaduan</h4>
                    <p class="text-muted mb-0">Sampaikan keluhan Anda dengan mudah melalui form di bawah ini</p>
                </div>
            </div>

            <!-- Progress tracker -->
            <div class="sticky-progress" id="progress-tracker">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-loader-circle text-primary me-2'></i>
                                <span class="fw-semibold">Progres Pengisian Form</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary rounded-pill px-2 py-1 me-2" id="progress-text">0%</span>
                                <i class='bx bx-info-circle text-muted' data-bs-toggle="tooltip"
                                    title="Lengkapi semua field untuk menyelesaikan form"></i>
                            </div>
                        </div>

                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                                id="complaint-progress-bar"></div>
                        </div>

                        <div class="d-flex justify-content-between mt-2">
                            <div class="d-flex align-items-center small text-muted">
                                <div class="progress-step" id="step-1">
                                    <i class='bx bx-radio-circle me-1'></i>
                                    <span>Jenis</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center small text-muted">
                                <div class="progress-step" id="step-2">
                                    <i class='bx bx-radio-circle me-1'></i>
                                    <span>Judul</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center small text-muted">
                                <div class="progress-step" id="step-3">
                                    <i class='bx bx-radio-circle me-1'></i>
                                    <span>Deskripsi</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center small text-muted" id="step-4-container">
                                <div class="progress-step" id="step-4">
                                    <i class='bx bx-radio-circle me-1'></i>
                                    <span>Selesai</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Placeholder for the sticky element to prevent layout shift -->
            <div id="progress-placeholder" style="display: none; margin-bottom: 1.5rem;"></div>

            <!-- Main form card -->
            <div class="card">
                <div class="card-header mb-5">
                    <h5 class="card-title mb-0">Informasi Pengaduan</h5>
                    <p class="card-subtitle text-muted mt-1">Mohon lengkapi semua informasi yang diperlukan</p>
                </div>

                <div class="card-body">
                    <form action="/customer/add/pengaduan" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                        <!-- Complaint type selection -->
                        <div class="mb-4">
                            <label class="form-label d-flex align-items-center">
                                <i class='bx bx-category-alt me-2 text-primary'></i>
                                Jenis Pengaduan
                            </label>
                            <div class="row g-3">
                                @foreach ($jenis as $item)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="complaint-type-card"
                                            onclick="selectComplaintType(this, '{{ $item->jenis_pengaduan }}')">
                                            <div class="icon
                                            text-warning">
                                                <i class='bx bxs-error'></i>
                                            </div>
                                            <h6>{{ $item->jenis_pengaduan }}</h6>
                                            @if ($item->jenis_pengaduan == 'Gangguan Teknis')
                                                <p>Masalah koneksi, kecepatan internet, atau perangkat</p>
                                            @elseif($item->jenis_pengaduan == 'Masalah Tagihan')
                                                <p>Tagihan tidak sesuai, pembayaran, atau invoice</p>
                                            @elseif($item->jenis_pengaduan == 'Lainnya')
                                                <p>Pertanyaan umum atau masalah lainnya</p>
                                            @endif
                                            <input type="radio" value="{{ $item->id }}" class="d-none"
                                                name="id_pengaduan">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Complaint title -->
                        <div class="mb-4">
                            <label for="complaint_title" class="form-label d-flex align-items-center">
                                <i class='bx bx-text me-2 text-primary'></i>
                                Judul Pengaduan
                            </label>
                            <input type="text" class="form-control" id="complaint_title" name="judul"
                                placeholder="Masukkan judul singkat pengaduan Anda">
                        </div>

                        <!-- Complaint description -->
                        <div class="mb-4">
                            <label for="complaint_description" class="form-label d-flex align-items-center">
                                <i class='bx bx-detail me-2 text-primary'></i>
                                Deskripsi Pengaduan
                            </label>
                            <textarea class="form-control" id="complaint_description" name="deskripsi" rows="4"
                                placeholder="Jelaskan secara detail masalah yang Anda alami"></textarea>
                            <div class="form-text mt-1">
                                <i class='bx bx-info-circle me-1'></i>
                                Mohon berikan informasi selengkap mungkin untuk membantu kami menyelesaikan masalah Anda
                                dengan cepat.
                            </div>
                        </div>

                        <!-- File attachment -->
                        <div class="mb-4">
                            <label class="form-label d-flex align-items-center">
                                <i class='bx bx-paperclip me-2 text-primary'></i>
                                Lampiran
                                <span class="text-danger"> (Tidak Wajib)</span>
                            </label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="complaint_attachment" name="lampiran[]" class="form-control"
                                    multiple>
                                <div class="file-upload-content">
                                    <div class="file-upload-icon">
                                        <i class='bx bx-cloud-upload'></i>
                                    </div>
                                    <div class="file-upload-text">
                                        Klik atau seret file ke sini
                                    </div>
                                    <span class="file-upload-info">
                                        Maksimal 2MB (JPG, PNG, PDF)
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- Form footer with buttons -->
                        <div class="card-footer d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm">
                                <i class='bx bx-x me-1'></i>
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class='bx bx-send me-1'></i>
                                Kirim Pengaduan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk memilih jenis pengaduan
        function selectComplaintType(element, type) {
            // Hapus kelas selected dari semua kartu
            document.querySelectorAll('.complaint-type-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Tambahkan kelas selected ke kartu yang diklik
            element.classList.add('selected');

            // Centang radio button
            const radioButton = element.querySelector('input[type="radio"]');
            radioButton.checked = true;

            // Trigger a change event on the radio button to ensure any listeners are notified
            const event = new Event('change', {
                bubbles: true
            });
            radioButton.dispatchEvent(event);

            // Tambahkan efek visual untuk menunjukkan pilihan
            const allCards = document.querySelectorAll('.complaint-type-card');
            allCards.forEach(card => {
                if (card !== element) {
                    card.style.opacity = '0.7';
                } else {
                    card.style.opacity = '1';
                }
            });

            // Log untuk debugging
            console.log('Complaint type selected:', type);
            console.log('Radio button checked:', radioButton.checked);
            console.log('Radio button value:', radioButton.value);

            // Update progress bar
            updateProgressBar();
        }

        // Fungsi untuk preview file upload
        document.getElementById('complaint_attachment').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                const fileUploadText = document.querySelector('.file-upload-text');
                fileUploadText.textContent = fileName;

                // Ubah tampilan area upload
                const uploadWrapper = document.querySelector('.file-upload-wrapper');
                uploadWrapper.style.borderColor = 'var(--primary-color)';
                uploadWrapper.style.backgroundColor = 'var(--primary-light)';

                // Ubah ikon
                const uploadIcon = document.querySelector('.file-upload-icon i');
                uploadIcon.classList.remove('bx-cloud-upload');
                uploadIcon.classList.add('bx-check-circle');
            }

            // Update progress bar
            updateProgressBar();
        });

        // Fungsi untuk mengupdate progress bar
        function updateProgressBar() {
            const progressBar = document.getElementById('complaint-progress-bar');
            const progressText = document.getElementById('progress-text');
            const complaintType = document.querySelector('input[name="id_pengaduan"]:checked');
            const complaintTitle = document.getElementById('complaint_title').value;
            const complaintDescription = document.getElementById('complaint_description').value;
            const priority = document.getElementById('priority')?.value;

            // Referensi ke langkah-langkah progress
            const step1 = document.getElementById('step-1');
            const step2 = document.getElementById('step-2');
            const step3 = document.getElementById('step-3');
            const step4 = document.getElementById('step-4');

            // Reset semua langkah
            [step1, step2, step3, step4].forEach(step => {
                if (step) {
                    step.classList.remove('text-primary', 'fw-bold');
                    const icon = step.querySelector('i');
                    if (icon) {
                        icon.classList.remove('bx-radio-circle-marked', 'bx-check-circle');
                        icon.classList.add('bx-radio-circle');
                    }
                }
            });

            let progress = 0;
            let totalFields = 3; // Default to 3 fields (will be updated based on form)
            let completedSteps = [];

            console.log('Starting progress calculation...');

            // Debug log
            console.log('Updating progress bar:');
            console.log('- Complaint type:', complaintType ? complaintType.value : 'not selected');
            console.log('- Complaint title:', complaintTitle);
            console.log('- Complaint description:', complaintDescription ? 'filled' : 'empty');
            console.log('- Priority:', priority || 'not available');

            // Determine total required fields and adjust calculation
            let totalRequiredFields = 3; // By default: type, title, description
            let hasPriorityField = document.getElementById('priority') !== null;

            if (hasPriorityField) {
                totalRequiredFields = 4; // Add priority if it exists
            } else {
                // If there's no priority field, hide the 4th step indicator or change its label
                const step4Container = document.getElementById('step-4-container');
                if (step4Container) {
                    const step4 = document.getElementById('step-4');
                    if (step4) {
                        const span = step4.querySelector('span');
                        if (span) {
                            span.textContent = 'Kirim'; // Change label to indicate form submission
                        }
                    }
                }
            }

            // Update total fields for percentage calculation
            totalFields = totalRequiredFields;

            // Hitung progress berdasarkan field yang sudah diisi
            if (complaintType) {
                progress += 1;
                completedSteps.push(1);
                console.log('Step 1 completed: Complaint type selected');
            }

            if (complaintTitle && complaintTitle.trim() !== '') {
                progress += 1;
                completedSteps.push(2);
                console.log('Step 2 completed: Title filled');
            }

            if (complaintDescription && complaintDescription.trim() !== '') {
                progress += 1;
                completedSteps.push(3);
                console.log('Step 3 completed: Description filled');
            }

            // Handle step 4 (Priority or Completion)
            let step4Completed = false;

            if (hasPriorityField) {
                // If priority field exists, check if it's filled
                if (priority) {
                    progress += 1;
                    completedSteps.push(4);
                    step4Completed = true;
                    console.log('Step 4 completed: Priority selected');
                }
            } else {
                // If no priority field, step 4 is "Selesai" and is completed when all other steps are completed
                if (complaintType && complaintTitle && complaintTitle.trim() !== '' &&
                    complaintDescription && complaintDescription.trim() !== '') {
                    // Only mark as complete if all previous steps are complete

                    // Check if we already have 3 completed steps (to avoid double-counting)
                    if (progress === 3) {
                        progress += 1;
                        completedSteps.push(4);
                        step4Completed = true;
                        console.log('Step 4 completed: All required fields filled');
                    }
                }
            }

            // Debug log for progress count
            console.log('Total progress count:', progress, 'out of', totalFields);

            // Hitung persentase (ensure it never exceeds 100%)
            let percentage = Math.round((progress / totalFields) * 100);

            // Debug log for percentage calculation
            console.log('Progress calculation:', progress, '/', totalFields, '=', percentage + '%');

            // Ensure percentage never exceeds 100%
            if (percentage > 100) {
                console.log('Percentage exceeded 100%, capping at 100%');
                percentage = 100;
            }

            // Ensure percentage is between 0 and 100
            percentage = Math.max(0, Math.min(100, percentage));

            // Update progress bar
            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage);
            progressText.textContent = percentage + '%';

            console.log('Final progress percentage:', percentage + '%');

            // Ubah warna progress bar dan badge berdasarkan persentase
            if (percentage < 50) {
                progressBar.classList.remove('bg-success', 'bg-warning');
                progressBar.classList.add('bg-primary');
                progressText.classList.remove('bg-success', 'bg-warning');
                progressText.classList.add('bg-primary');
            } else if (percentage < 100) {
                progressBar.classList.remove('bg-primary', 'bg-success');
                progressBar.classList.add('bg-warning');
                progressText.classList.remove('bg-primary', 'bg-success');
                progressText.classList.add('bg-warning');
            } else {
                progressBar.classList.remove('bg-primary', 'bg-warning');
                progressBar.classList.add('bg-success');
                progressText.classList.remove('bg-primary', 'bg-warning');
                progressText.classList.add('bg-success');
            }

            // Update tampilan langkah-langkah
            completedSteps.forEach(stepNum => {
                const step = document.getElementById('step-' + stepNum);
                if (step) {
                    step.classList.add('text-primary', 'fw-bold');
                    const icon = step.querySelector('i');
                    if (icon) {
                        icon.classList.remove('bx-radio-circle');
                        icon.classList.add('bx-check-circle');
                    }
                }
            });

            // Jika semua langkah selesai, tambahkan efek khusus
            if (percentage === 100) {
                progressBar.classList.add('progress-bar-success');
                // Tambahkan animasi konfeti atau efek lain jika diinginkan
            }
        }

        // Tambahkan event listener untuk semua input fields
        document.getElementById('complaint_title').addEventListener('input', updateProgressBar);
        document.getElementById('complaint_description').addEventListener('input', updateProgressBar);

        // Add event listeners to all radio buttons for complaint type
        document.querySelectorAll('input[name="id_pengaduan"]').forEach(radio => {
            radio.addEventListener('change', updateProgressBar);
        });

        // Check if priority element exists before adding event listener
        if (document.getElementById('priority')) {
            document.getElementById('priority').addEventListener('change', updateProgressBar);
        }

        // Inisialisasi progress bar saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Check if any complaint type is already selected (in case of page refresh)
            const selectedComplaintType = document.querySelector('input[name="id_pengaduan"]:checked');
            if (selectedComplaintType) {
                const card = selectedComplaintType.closest('.complaint-type-card');
                if (card) {
                    card.classList.add('selected');
                    card.style.opacity = '1';

                    // Make other cards semi-transparent
                    document.querySelectorAll('.complaint-type-card').forEach(otherCard => {
                        if (otherCard !== card) {
                            otherCard.style.opacity = '0.7';
                        }
                    });
                }
            }

            // Reset any existing progress
            const progressBar = document.getElementById('complaint-progress-bar');
            if (progressBar) {
                progressBar.style.width = '0%';
                progressBar.setAttribute('aria-valuenow', 0);
            }

            const progressText = document.getElementById('progress-text');
            if (progressText) {
                progressText.textContent = '0%';
            }

            // Initialize progress bar
            updateProgressBar();

            // Inisialisasi tooltips
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, {
                        boundary: document.body
                    });
                });
            }

            // Tambahkan efek focus pada input fields
            const formInputs = document.querySelectorAll('.form-control, .form-select');
            formInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.closest('.mb-4').style.borderLeft = '3px solid var(--primary-color)';
                    this.closest('.mb-4').style.paddingLeft = '10px';
                    this.closest('.mb-4').style.transition = 'all 0.2s ease';
                });

                input.addEventListener('blur', function() {
                    this.closest('.mb-4').style.borderLeft = '';
                    this.closest('.mb-4').style.paddingLeft = '';
                });
            });

            // Animasi awal progress bar
            setTimeout(() => {
                const progressBar = document.getElementById('complaint-progress-bar');
                if (progressBar) {
                    progressBar.classList.add('progress-bar-striped', 'progress-bar-animated');
                }
            }, 500);

            // Handle sticky progress bar on scroll
            const progressTracker = document.getElementById('progress-tracker');
            const progressPlaceholder = document.getElementById('progress-placeholder');

            // Get the initial position of the progress tracker
            let progressTrackerOffset = progressTracker.getBoundingClientRect().top + window.pageYOffset;
            const progressTrackerHeight = progressTracker.offsetHeight;

            // Set the placeholder height to match the progress tracker
            progressPlaceholder.style.height = progressTrackerHeight + 'px';

            // Function to handle scroll event
            function handleScroll() {
                if (window.pageYOffset > progressTrackerOffset) {
                    progressTracker.classList.add('is-sticky');
                    progressPlaceholder.style.display = 'block'; // Show placeholder to prevent layout shift

                    // Fallback for browsers with sticky positioning issues
                    if (window.getComputedStyle(progressTracker).position !== 'sticky') {
                        progressTracker.style.position = 'fixed';
                        progressTracker.style.top = '0';
                        progressTracker.style.left = '0';
                        progressTracker.style.right = '0';
                        progressTracker.style.width = '100%';
                    }
                } else {
                    progressTracker.classList.remove('is-sticky');
                    progressPlaceholder.style.display = 'none'; // Hide placeholder when not needed

                    // Reset fallback styles
                    if (progressTracker.style.position === 'fixed') {
                        progressTracker.style.position = '';
                        progressTracker.style.top = '';
                        progressTracker.style.left = '';
                        progressTracker.style.right = '';
                        progressTracker.style.width = '';
                    }
                }
            }

            // Add scroll event listener
            window.addEventListener('scroll', handleScroll);

            // Also handle resize events to recalculate positions
            window.addEventListener('resize', function() {
                // Recalculate the position of the progress tracker
                progressTrackerOffset = progressTracker.getBoundingClientRect().top + window.pageYOffset;
                handleScroll(); // Check if we need to update the sticky state
            });

            // Initial check
            handleScroll();

            // Force a reflow to ensure the sticky positioning is applied correctly
            setTimeout(function() {
                window.dispatchEvent(new Event('scroll'));
            }, 100);
        });
    </script>
@endsection
