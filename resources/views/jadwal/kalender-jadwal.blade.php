@extends('layouts.contentNavbarLayout')
@section('title', 'Schedule')

<!-- FullCalendar CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
<style>
    /* Enhanced Calendar Styling */
    .fc .fc-toolbar.fc-header-toolbar {
        margin-bottom: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .fc .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .fc .fc-button {
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2) !important;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .5rem .75rem;
        font-weight: 600;
        transition: all 0.2s ease;
        border: 2px solid rgba(255,255,255,0.3) !important;
    }
    
    .fc .fc-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.3) !important;
    }
    
    .fc .fc-button:focus {
        box-shadow: 0 0 0 .3rem rgba(255,255,255,.6) !important;
    }
    
    .fc .fc-button-primary {
        background: rgba(255,255,255,0.95) !important;
        color: #495057 !important;
        border: 2px solid rgba(255,255,255,0.8) !important;
    }
    
    .fc .fc-button-primary:hover {
        background: white !important;
        color: #212529 !important;
        border: 2px solid white !important;
        box-shadow: 0 6px 20px rgba(255,255,255,0.4) !important;
    }
    
    .fc .fc-button-primary:active {
        background: #f8f9fa !important;
        color: #212529 !important;
        transform: translateY(0px);
    }
    
    /* Calendar Grid Enhancements */
    .fc .fc-daygrid-day.fc-day-today {
        background: linear-gradient(135deg, rgba(13,110,253,0.1) 0%, rgba(13,110,253,0.05) 100%);
        border: 2px solid rgba(13,110,253,0.3);
    }
    
    .fc .fc-daygrid-day:hover {
        background: rgba(102,126,234,0.05);
        cursor: pointer;
        transition: background 0.2s ease;
    }
    
    .fc .fc-daygrid-day-number {
        font-weight: 600;
        padding: 8px;
    }
    
    .fc .fc-event {
        border-radius: 6px;
        border: none;
        padding: 2px 6px;
        font-size: 0.85rem;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        cursor: pointer;
    }
    
    /* Sneat Modal Styling */
    .modal-content {
        border-radius: 0.5rem;
        border: none;
        box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
    }
    
    .modal-header {
        background: #fff;
        border-bottom: 1px solid #d9dee3;
        border-radius: 0.5rem 0.5rem 0 0;
        padding: 1.5rem 1.5rem 1rem;
    }
    
    .modal-title {
        font-weight: 600;
        font-size: 1.125rem;
        color: #566a7f;
        margin: 0;
    }
    
    .modal-title i {
        color: #696cff;
    }
    
    .btn-close {
        background: transparent;
        border: none;
        font-size: 1rem;
        opacity: 0.5;
        padding: 0.25rem;
    }
    
    .btn-close:hover {
        opacity: 0.75;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        background: #f8f9fa;
        border-top: 1px solid #d9dee3;
        border-radius: 0 0 0.5rem 0.5rem;
        padding: 1rem 1.5rem;
    }
    
    /* Sneat Form Styling */
    .form-label {
        font-weight: 500;
        color: #566a7f;
        margin-bottom: 0.5rem;
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    
    .form-label i {
        color: #a1acb8;
        margin-right: 0.25rem;
    }
    
    .form-control, .form-select {
        border-radius: 0.375rem;
        border: 1px solid #d9dee3;
        padding: 0.4375rem 0.875rem;
        font-size: 0.9375rem;
        line-height: 1.53;
        color: #566a7f;
        background-color: #fff;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
        outline: 0;
    }
    
    .form-control::placeholder {
        color: #a1acb8;
    }
    
    /* Sneat Radio Buttons */
    .form-check {
        margin-bottom: 0.5rem;
    }
    
    .form-check-input {
        width: 1.125rem;
        height: 1.125rem;
        margin-top: 0.125rem;
        border: 1px solid #d9dee3;
        border-radius: 50%;
    }
    
    .form-check-input:checked {
        background-color: #696cff;
        border-color: #696cff;
    }
    
    .form-check-input:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }
    
    .form-check-label {
        color: #566a7f;
        font-size: 0.9375rem;
        margin-left: 0.5rem;
    }
    
    /* Sneat Buttons */
    .btn-primary {
        background-color: #696cff;
        border-color: #696cff;
        color: #fff;
        font-weight: 500;
        border-radius: 0.375rem;
        padding: 0.4375rem 1.25rem;
        font-size: 0.9375rem;
    }
    
    .btn-primary:hover {
        background-color: #5a5fe7;
        border-color: #5a5fe7;
        color: #fff;
    }
    
    .btn-secondary {
        background-color: #8592a3;
        border-color: #8592a3;
        color: #fff;
        font-weight: 500;
        border-radius: 0.375rem;
        padding: 0.4375rem 1.25rem;
        font-size: 0.9375rem;
    }
    
    .btn-secondary:hover {
        background-color: #7a8699;
        border-color: #7a8699;
        color: #fff;
    }
    
    .btn-danger {
        background-color: #ff3e1d;
        border-color: #ff3e1d;
        color: #fff;
        font-weight: 500;
        border-radius: 0.375rem;
        padding: 0.4375rem 1.25rem;
        font-size: 0.9375rem;
    }
    
    .btn-danger:hover {
        background-color: #e6381a;
        border-color: #e6381a;
        color: #fff;
    }
    
    /* Loading spinner */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }
    
    /* Responsive adjustments */
    .fc .fc-toolbar.fc-header-toolbar .fc-toolbar-chunk:nth-child(2) {
        flex: 1 1 auto;
        display: flex;
        justify-content: center;
    }
    
    @media (max-width: 576px) {
        .fc .fc-button {
            padding: .4rem .6rem;
            font-size: .85rem;
        }
        .fc .fc-toolbar-title {
            font-size: 1.1rem;
        }
        .fc .fc-toolbar.fc-header-toolbar .fc-toolbar-chunk {
            flex: 1 1 100%;
        }
        .fc .fc-toolbar.fc-header-toolbar .fc-toolbar-chunk:first-child,
        .fc .fc-toolbar.fc-header-toolbar .fc-toolbar-chunk:last-child {
            display: flex;
            justify-content: space-between;
        }
    }
    
    /* Enhanced tooltip */
    .fc-day-tooltip {
        position: absolute;
        z-index: 2000;
        background: #fff;
        color: #566a7f;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        line-height: 1.4;
        box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
        pointer-events: none;
        max-width: 320px;
        opacity: 0;
        transform: translateY(8px);
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        padding: 1rem;
        border: 1px solid #d9dee3;
    }
    
    .fc-day-tooltip.show {
        opacity: 1;
        transform: translateY(0);
    }
    
    .fc-day-tooltip .title {
        font-weight: 600;
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.9375rem;
        color: #566a7f;
    }
    
    .fc-day-tooltip ul {
        margin: 0;
        padding-left: 1.25rem;
    }
    
    .fc-day-tooltip li {
        margin: 0.25rem 0;
        font-size: 0.8125rem;
        color: #a1acb8;
    }
    
    .fc-day-tooltip .arrow {
        position: absolute;
        width: 10px;
        height: 10px;
        background: #fff;
        border: 1px solid #d9dee3;
        transform: rotate(45deg);
    }
    
    .fc-day-tooltip.at-right .arrow { 
        left: -6px; 
        top: 16px; 
        border-right: none;
        border-bottom: none;
    }
    .fc-day-tooltip.at-left .arrow { 
        right: -6px; 
        top: 16px;
        border-left: none;
        border-top: none;
    }
    .fc-day-tooltip.at-top .arrow { 
        left: 20px; 
        bottom: -6px;
        border-top: none;
        border-left: none;
    }
    .fc-day-tooltip.at-bottom .arrow { 
        left: 20px; 
        top: -6px;
        border-bottom: none;
        border-right: none;
    }
</style>

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">📅 Schedule</h5>
                    <p class="card-subtitle text-muted mb-0">Kelola jadwal dan acara</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                    <i class="bx bx-plus me-1"></i>Tambah Jadwal
                </button>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addScheduleModalLabel">
                    <i class="bx bx-calendar-plus me-2"></i>Tambah Jadwal Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addScheduleForm">
                    @csrf
                    <input type="hidden" id="scheduleId" name="schedule_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="scheduleTitle" class="form-label">
                                <i class="bx bx-text"></i>Judul Jadwal
                            </label>
                            <input type="text" class="form-control" id="scheduleTitle" name="title" placeholder="Masukkan judul jadwal" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="scheduleCategory" class="form-label">
                                <i class="bx bx-category"></i>Kategori
                            </label>
                            <select class="form-select" id="scheduleCategory" name="category" required>
                                <option value="">Pilih kategori</option>
                                <option value="meeting">Meeting</option>
                                <option value="task">Tugas</option>
                                <option value="event">Acara</option>
                                <option value="reminder">Pengingat</option>
                                <option value="personal">Personal</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="scheduleDescription" class="form-label">
                            <i class="bx bx-detail"></i>Deskripsi
                        </label>
                        <textarea class="form-control" id="scheduleDescription" name="description" rows="3" placeholder="Deskripsi jadwal (opsional)"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="scheduleDate" class="form-label">
                                <i class="bx bx-calendar"></i>Tanggal
                            </label>
                            <input type="date" class="form-control" id="scheduleDate" name="date" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bx bx-time"></i>Jenis Waktu
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="time_type" id="allDay" value="allday">
                                <label class="form-check-label" for="allDay">Seharian</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="time_type" id="specificTime" value="specific" checked>
                                <label class="form-check-label" for="specificTime">Waktu Tertentu</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="timeInputs">
                        <div class="col-md-6 mb-3">
                            <label for="startTime" class="form-label">Waktu Mulai</label>
                            <input type="time" class="form-control" id="startTime" name="start_time" value="09:00">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="endTime" class="form-label">Waktu Selesai</label>
                            <input type="time" class="form-control" id="endTime" name="end_time" value="10:00">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="scheduleColor" class="form-label">
                                <i class="bx bx-palette"></i>Warna
                            </label>
                            <select class="form-select" id="scheduleColor" name="color">
                                <option value="#696cff">Primary</option>
                                <option value="#71dd37">Success</option>
                                <option value="#ff3e1d">Danger</option>
                                <option value="#ffab00">Warning</option>
                                <option value="#03c3ec">Info</option>
                                <option value="#8592a3">Secondary</option>
                                <option value="#233446">Dark</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="schedulePriority" class="form-label">
                                <i class="bx bx-error"></i>Prioritas
                            </label>
                            <select class="form-select" id="schedulePriority" name="priority">
                                <option value="low">Rendah</option>
                                <option value="medium" selected>Sedang</option>
                                <option value="high">Tinggi</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer gap-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="deleteSchedule" style="display: none;">
                    <i class="bx bx-trash me-1"></i>Hapus
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="saveSchedule">
                    <span class="spinner-border spinner-border-sm me-1" style="display: none;"></span>
                    <i class="bx bx-check me-1"></i>Simpan Jadwal
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/bootstrap5@6.1.15/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        let selectedDate = null;
        let currentSchedule = null;
        let currentEventId = null;
        
        // Configuration
        const config = {
            apiBaseUrl: '/api/schedule',
            useBackend: true,
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
            document.querySelector('input[name="_token"]')?.value
        };
        
        console.log('Calendar configuration:', config);
        
        // Tooltip element
        const tooltipEl = document.createElement('div');
        tooltipEl.className = 'fc-day-tooltip';
        tooltipEl.innerHTML = '<div class="arrow"></div><div class="content"></div>';
        document.body.appendChild(tooltipEl);
        const tooltipContent = tooltipEl.querySelector('.content');
        
        // Helper functions
        const fmtTime = (d) => d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const startOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0, 0);
        const endOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23, 59, 59, 999);
        
        // API Helper functions
        async function apiRequest(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            try {
                const response = await fetch(url, { ...defaultOptions, ...options });
                
                if (!response.ok) {
                    let errorMessage = 'Terjadi kesalahan pada server';
                    
                    try {
                        const errorData = await response.json();
                        errorMessage = errorData.message || errorMessage;
                        
                        if (response.status === 422 && errorData.errors) {
                            throw { 
                                message: errorMessage, 
                                errors: errorData.errors,
                                status: response.status 
                            };
                        }
                    } catch (parseError) {
                        if (parseError.errors) throw parseError;
                        
                        const errorText = await response.text();
                        errorMessage = errorText || `HTTP ${response.status}: ${response.statusText}`;
                    }
                    
                    throw { message: errorMessage, status: response.status };
                }
                
                return await response.json();
            } catch (error) {
                if (!error.status) {
                    throw { message: 'Tidak dapat terhubung ke server', status: 0 };
                }
                throw error;
            }
        }
        
        function showError(message) {
            console.error('Error:', message);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: message,
                    confirmButtonColor: '#696cff',
                    topLayer: true
                });
            } else {
                alert('Error: ' + message);
            }
        }
        
        function showSuccess(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: message,
                    showConfirmButton: false,
                    timer: 1500,
                    topLayer: true
                });
            } else {
                alert(message);
            }
        }
        
        function clearFormErrors() {
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }
        
        function showFormErrors(errors) {
            clearFormErrors();
            Object.keys(errors).forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                const feedback = input?.parentElement.querySelector('.invalid-feedback');
                
                if (input && feedback) {
                    input.classList.add('is-invalid');
                    feedback.textContent = errors[field][0];
                }
            });
        }
        
        // Enhanced sample data for development
        function getSampleEvents() {
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            
            console.log('Generating sample events for date:', today);
            
            const events = [
                {
                    id: '1',
                    title: 'Meeting Tim Development',
                    start: new Date(today.getFullYear(), today.getMonth(), today.getDate(), 10, 0),
                    end: new Date(today.getFullYear(), today.getMonth(), today.getDate(), 11, 30),
                    color: '#696cff',
                    extendedProps: {
                        category: 'meeting',
                        description: 'Meeting rutin tim development untuk review progress',
                        priority: 'high'
                    }
                },
                {
                    id: '2',
                    title: 'Review Code Frontend',
                    start: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1, 14, 0),
                    end: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1, 16, 0),
                    color: '#ff3e1d',
                    extendedProps: {
                        category: 'task',
                        description: 'Review code untuk fitur baru frontend',
                        priority: 'medium'
                    }
                },
                {
                    id: '3',
                    title: 'Hari Libur Nasional',
                    start: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 2),
                    allDay: true,
                    color: '#71dd37',
                    extendedProps: {
                        category: 'event',
                        description: 'Hari libur nasional',
                        priority: 'low'
                    }
                },
                {
                    id: '4',
                    title: 'Presentasi Proposal',
                    start: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 3, 9, 0),
                    end: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 3, 10, 30),
                    color: '#ffab00',
                    extendedProps: {
                        category: 'meeting',
                        description: 'Presentasi proposal proyek baru',
                        priority: 'high'
                    }
                },
                {
                    id: '5',
                    title: 'Training Laravel',
                    start: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 5, 13, 0),
                    end: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 5, 17, 0),
                    color: '#03c3ec',
                    extendedProps: {
                        category: 'event',
                        description: 'Training Laravel untuk tim development',
                        priority: 'medium'
                    }
                }
            ];
            
            console.log('Sample events generated:', events);
            return events;
        }
        
        function eventsForDate(calendar, date) {
            const sod = startOfDay(date);
            const eod = endOfDay(date);
            return calendar.getEvents().filter(ev => {
                const evStart = ev.start;
                const evEnd = ev.end || ev.start;
                return evStart <= eod && evEnd >= sod;
            }).sort((a, b) => (a.start?.getTime() || 0) - (b.start?.getTime() || 0));
        }
        
        function buildTooltipHtml(date, events) {
            const dateText = date.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                day: '2-digit', 
                month: 'long', 
                year: 'numeric' 
            });
            
            if (!events.length) {
                return `<span class="title">${dateText}</span><em>Tidak ada jadwal</em>`;
            }
            
            const items = events.map(ev => {
            const createdBy = ev.extendedProps?.created_by || 'Tidak diketahui';
            
            if (ev.allDay) {
                return `<li>
                    <strong>${ev.title}</strong> — Seharian<br>
                    <small>Dibuat oleh: ${createdBy}</small>
                </li>`;
            }
            
            const s = fmtTime(ev.start);
            const e = ev.end ? fmtTime(ev.end) : '';
            
            return `<li>
                <strong>${ev.title}</strong> — ${s}${e ? `–${e}` : ''}<br>
                <small>Dibuat oleh: <b>${createdBy}</b></small>
            </li>`;
        }).join('');
            
            return `<span class="title">${dateText}</span><ul>${items}</ul>`;
        }
        
        function positionTooltip(anchorRect) {
            const margin = 15;
            tooltipEl.style.visibility = 'hidden';
            tooltipEl.classList.add('show');
            const tw = tooltipEl.offsetWidth;
            const th = tooltipEl.offsetHeight;
            
            let top = window.scrollY + anchorRect.top + margin;
            let left = window.scrollX + anchorRect.right + margin;
            let placement = 'at-right';
            
            const vw = window.innerWidth;
            const vh = window.innerHeight;
            
            if (left + tw > window.scrollX + vw - 10) {
                left = window.scrollX + anchorRect.left - tw - margin;
                placement = 'at-left';
            }
            
            if (top + th > window.scrollY + vh - 10) {
                const altTop = window.scrollY + anchorRect.top - th - margin;
                if (altTop >= window.scrollY + 10) {
                    top = altTop;
                } else {
                    top = window.scrollY + vh - th - 10;
                }
            }
            if (top < window.scrollY + 10) top = window.scrollY + 10;
            
            tooltipEl.classList.remove('at-right', 'at-left', 'at-top', 'at-bottom');
            tooltipEl.classList.add(placement);
            tooltipEl.style.top = top + 'px';
            tooltipEl.style.left = left + 'px';
            tooltipEl.style.visibility = 'visible';
        }
        
        function showTooltip(html, anchorRect) {
            tooltipContent.innerHTML = html;
            positionTooltip(anchorRect);
            tooltipEl.classList.add('show');
        }
        
        function hideTooltip() {
            tooltipEl.classList.remove('show');
        }
        
        // Custom buttons
        const customButtons = {
            prevBI: { icon: 'chevron-left', click: function() { calendar.prev(); } },
            nextBI: { icon: 'chevron-right', click: function() { calendar.next(); } },
            todayBI: { text: 'Hari ini', click: function() { calendar.today(); } }
        };
        
        // Responsive toolbar
        function getHeaderConfig() {
            const w = window.innerWidth;
            if (w < 576) {
                return { left: 'prevBI,nextBI todayBI', center: 'title', right: 'dayGridMonth,listWeek' };
            }
            if (w < 768) {
                return { left: 'prevBI,nextBI todayBI', center: 'title', right: 'dayGridMonth,listWeek' };
            }
            return { left: 'prevBI,nextBI todayBI', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' };
        }
        
        // Initialize calendar
        const calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'bootstrap5',
            initialView: 'dayGridMonth',
            locale: 'id',
            firstDay: 1,
            contentHeight: 760,
            expandRows: true,
            nowIndicator: true,
            dayMaxEvents: 3,
            moreLinkClick: 'popover',
            
            customButtons: customButtons,
            headerToolbar: getHeaderConfig(),
            
            buttonText: {
                month: 'Bulan',
                week: 'Minggu', 
                day: 'Hari',
                list: 'Agenda'
            },
            titleFormat: { year: 'numeric', month: 'long' },
            navLinks: true,
            selectable: true,
            editable: true,
            eventDisplay: 'block',
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            
            // Enhanced events loading with better logging
            events: function(info, successCallback, failureCallback) {
                console.log('Loading events for range:', info.startStr, 'to', info.endStr);
                console.log('Using backend:', config.useBackend);
                
                if (config.useBackend) {
                    console.log('Attempting to load from backend...');
                    apiRequest(`${config.apiBaseUrl}/events?start=${info.startStr}&end=${info.endStr}`)
                    .then(data => {
                        console.log('Events loaded from backend:', data);
                        successCallback(data);
                    })
                    .catch(error => {
                        console.error('Backend loading failed:', error);
                        console.log('Falling back to sample data');
                        const sampleEvents = getSampleEvents();
                        successCallback(sampleEvents);
                    });
                } else {
                    console.log('Using sample data for development');
                    const sampleEvents = getSampleEvents();
                    successCallback(sampleEvents);
                }
            },
            
            // Enhanced event rendering with logging
            eventDidMount: function(info) {
                console.log('Event mounted:', {
                    id: info.event.id,
                    title: info.event.title,
                    start: info.event.start,
                    end: info.event.end,
                    allDay: info.event.allDay
                });
            },
            
            // Date click handler
            dateClick: function(info) {
                console.log('Date clicked:', info.dateStr);
                selectedDate = info.dateStr;
                openAddModal(selectedDate);
            },
            
            // Event click handler
            eventClick: function(info) {
                console.log('Event clicked:', info.event.title);
                info.jsEvent.preventDefault();
                openEditModal(info.event);
            },
            
            // Day cell hover
            dayCellDidMount: function(arg) {
                const el = arg.el;
                let hoverTimeout;
                
                el.addEventListener('mouseenter', () => {
                    hoverTimeout = setTimeout(() => {
                        const date = arg.date;
                        const evs = eventsForDate(calendar, date);
                        const html = buildTooltipHtml(date, evs);
                        const rect = el.getBoundingClientRect();
                        showTooltip(html, rect);
                    }, 100);
                });
                
                el.addEventListener('mouseleave', () => {
                    clearTimeout(hoverTimeout);
                    hideTooltip();
                });
            }
        });
        
        // Render calendar and log
        calendar.render();
        console.log('Calendar rendered successfully');
        
        // Modal functions
        function openAddModal(date = null) {
            console.log('Opening add modal for date:', date);
            currentSchedule = null;
            currentEventId = null;
            
            document.getElementById('addScheduleModalLabel').innerHTML = '<i class="bx bx-calendar-plus me-2"></i>Tambah Jadwal Baru';
            document.getElementById('deleteSchedule').style.display = 'none';
            
            document.getElementById('addScheduleForm').reset();
            document.getElementById('scheduleId').value = '';
            clearFormErrors();
            
            if (date) {
                document.getElementById('scheduleDate').value = date;
            } else {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('scheduleDate').value = today;
            }
            
            document.getElementById('timeInputs').style.display = 'flex';
            document.getElementById('specificTime').checked = true;
            
            const modal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
            modal.show();
        }
        
        async function openEditModal(event) {
            console.log('Opening edit modal for event:', event.id);
            try {
                let schedule;
                
                currentEventId = event.id;
                
                if (config.useBackend) {
                    const response = await apiRequest(`${config.apiBaseUrl}/${event.id}`);
                    schedule = response.data;
                } else {
                    schedule = {
                        id: event.id,
                        title: event.title,
                        category: event.extendedProps?.category || 'meeting',
                        description: event.extendedProps?.description || '',
                        date: event.start.toISOString().split('T')[0],
                        time_type: event.allDay ? 'allday' : 'specific',
                        start_time: event.allDay ? null : event.start.toTimeString().slice(0, 5),
                        end_time: event.allDay || !event.end ? null : event.end.toTimeString().slice(0, 5),
                        color: event.color || '#696cff',
                        priority: event.extendedProps?.priority || 'medium'
                    };
                }
                
                currentSchedule = schedule;
                document.getElementById('addScheduleModalLabel').innerHTML = '<i class="bx bx-edit me-2"></i>Edit Jadwal';
                document.getElementById('deleteSchedule').style.display = 'inline-block';
                
                document.getElementById('scheduleId').value = schedule.id;
                document.getElementById('scheduleTitle').value = schedule.title;
                document.getElementById('scheduleCategory').value = schedule.category;
                document.getElementById('scheduleDescription').value = schedule.description || '';
                document.getElementById('scheduleDate').value = schedule.date;
                document.getElementById('scheduleColor').value = schedule.color;
                document.getElementById('schedulePriority').value = schedule.priority;
                
                if (schedule.time_type === 'allday') {
                    document.getElementById('allDay').checked = true;
                    document.getElementById('timeInputs').style.display = 'none';
                } else {
                    document.getElementById('specificTime').checked = true;
                    document.getElementById('startTime').value = schedule.start_time || '09:00';
                    document.getElementById('endTime').value = schedule.end_time || '10:00';
                    document.getElementById('timeInputs').style.display = 'flex';
                }
                
                clearFormErrors();
                
                const modal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
                modal.show();
            } catch (error) {
                showError('Gagal memuat data jadwal: ' + error.message);
            }
        }
        
        // Modal form handlers
        const timeTypeRadios = document.querySelectorAll('input[name="time_type"]');
        const timeInputs = document.getElementById('timeInputs');
        
        timeTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'allday') {
                    timeInputs.style.display = 'none';
                } else {
                    timeInputs.style.display = 'flex';
                }
            });
        });
        
        // Enhanced save schedule handler
        document.getElementById('saveSchedule').addEventListener('click', async function() {
            console.log('Save button clicked');
            const form = document.getElementById('addScheduleForm');
            const saveBtn = this;
            const spinner = saveBtn.querySelector('.spinner-border');
            const icon = saveBtn.querySelector('.bx');
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            saveBtn.disabled = true;
            spinner.style.display = 'inline-block';
            icon.style.display = 'none';
            
            try {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                if (data.time_type === 'allday') {
                    data.start_time = null;
                    data.end_time = null;
                }
                
                console.log('Saving schedule data:', data);
                
                if (config.useBackend) {
                    let response;
                    if (currentSchedule && currentSchedule.id) {
                        response = await apiRequest(`${config.apiBaseUrl}/${currentSchedule.id}`, {
                            method: 'PUT',
                            body: JSON.stringify(data)
                        });
                    } else {
                        response = await apiRequest(config.apiBaseUrl, {
                            method: 'POST',
                            body: JSON.stringify(data)
                        });
                    }
                    
                    showSuccess(response.message);
                    console.log('Schedule saved successfully, refreshing calendar');
                    calendar.refetchEvents();
                } else {
                    showSuccess(currentSchedule ? 'Jadwal berhasil diperbarui' : 'Jadwal berhasil ditambahkan');
                    
                    // Remove existing event if editing
                    if (currentSchedule && currentSchedule.id) {
                        const existingEvent = calendar.getEventById(currentSchedule.id);
                        if (existingEvent) {
                            existingEvent.remove();
                            console.log('Removed existing event:', currentSchedule.id);
                        }
                    }
                    
                    // Create new event object
                    const eventData = {
                        id: currentSchedule?.id || Date.now().toString(),
                        title: data.title,
                        color: data.color,
                        extendedProps: {
                            category: data.category,
                            description: data.description,
                            priority: data.priority
                        }
                    };
                    
                    if (data.time_type === 'allday') {
                        eventData.start = data.date;
                        eventData.allDay = true;
                    } else {
                        eventData.start = `${data.date}T${data.start_time}:00`;
                        if (data.end_time) {
                            eventData.end = `${data.date}T${data.end_time}:00`;
                        }
                    }
                    
                    console.log('Adding new event to calendar:', eventData);
                    calendar.addEvent(eventData);
                }
                
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addScheduleModal'));
                modal.hide();
                
            } catch (error) {
                console.error('Save error:', error);
                if (error.errors) {
                    showFormErrors(error.errors);
                } else {
                    showError(error.message);
                }
            } finally {
                saveBtn.disabled = false;
                spinner.style.display = 'none';
                icon.style.display = 'inline-block';
            }
        });
        
        // Enhanced delete schedule handler
        document.getElementById('deleteSchedule').addEventListener('click', async function() {
            const scheduleId = currentSchedule?.id || currentEventId;
            
            if (!scheduleId) {
                showError('Tidak dapat menghapus jadwal: ID tidak ditemukan');
                console.error('Delete error: No schedule ID found', { currentSchedule, currentEventId });
                return;
            }
            
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: 'Hapus Jadwal?',
                    text: 'Jadwal yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ff3e1d',
                    cancelButtonColor: '#8592a3',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    topLayer: true
                });
                
                if (!result.isConfirmed) return;
            } else {
                if (!confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) return;
            }
            
            try {
                console.log('Deleting schedule:', scheduleId);
                
                if (config.useBackend) {
                    await apiRequest(`${config.apiBaseUrl}/${scheduleId}`, {
                        method: 'DELETE'
                    });
                    
                    console.log('Schedule deleted from backend, refreshing calendar');
                    calendar.refetchEvents();
                } else {
                    const existingEvent = calendar.getEventById(scheduleId);
                    if (existingEvent) {
                        existingEvent.remove();
                        console.log('Event removed from calendar:', scheduleId);
                    } else {
                        console.warn('Event not found in calendar:', scheduleId);
                    }
                }
                
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addScheduleModal'));
                modal.hide();
                
                showSuccess('Jadwal berhasil dihapus');
                
            } catch (error) {
                showError('Gagal menghapus jadwal: ' + error.message);
                console.error('Delete error:', error);
            }
        });
        
        // Responsive toolbar handler
        function applyResponsiveToolbar() {
            calendar.setOption('headerToolbar', getHeaderConfig());
        }
        window.addEventListener('resize', applyResponsiveToolbar);
        
        // Hide tooltip handlers
        window.addEventListener('scroll', hideTooltip, true);
        window.addEventListener('resize', hideTooltip);
        
        // Reset modal when closed
        document.getElementById('addScheduleModal').addEventListener('hidden.bs.modal', function() {
            currentSchedule = null;
            currentEventId = null;
            selectedDate = null;
            clearFormErrors();
        });
    });
</script>