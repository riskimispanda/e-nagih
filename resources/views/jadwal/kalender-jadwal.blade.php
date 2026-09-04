@extends('layouts.contentNavbarLayout')
@section('title', 'Kalender & Jadwal Kegiatan')

@section('page-style')
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false
        },
        theme: {
            extend: {
                colors: {
                    brand: {
                        50: '#eef2ff',
                        100: '#e0e7ff',
                        500: '#6366f1',
                        600: '#4f46e5',
                        700: '#4338ca',
                    }
                }
            }
        }
    };
</script>

<!-- FullCalendar CSS & Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />

<style>
    /* ==========================================================================
       MODERN FULLCALENDAR TAILWIND REDESIGN
       ========================================================================== */
    .fc {
        font-family: inherit;
        --fc-border-color: #f1f5f9;
        --fc-today-bg-color: rgba(99, 102, 241, 0.04);
        --fc-page-bg-color: #ffffff;
        --fc-neutral-bg-color: #f8fafc;
        --fc-list-event-hover-bg-color: #f1f5f9;
    }

    /* Custom Header Toolbar */
    .fc .fc-toolbar.fc-header-toolbar {
        margin-bottom: 1.25rem;
        padding: 0.85rem 1.25rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .fc .fc-toolbar-title {
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        letter-spacing: -0.02em;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Button Styling */
    .fc .fc-button {
        border-radius: 0.75rem !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        padding: 0.45rem 0.85rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        text-transform: capitalize !important;
        border: 1px solid #e2e8f0 !important;
        outline: none !important;
    }

    .fc .fc-button-primary {
        background: #ffffff !important;
        color: #334155 !important;
        border-color: #e2e8f0 !important;
    }

    .fc .fc-button-primary:hover {
        background: #f8fafc !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
    }

    .fc .fc-button-primary:active,
    .fc .fc-button-primary.fc-button-active {
        background: #4f46e5 !important;
        color: #ffffff !important;
        border-color: #4f46e5 !important;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3) !important;
    }

    .fc .fc-button-primary:disabled {
        opacity: 0.5 !important;
        background: #f1f5f9 !important;
        color: #94a3b8 !important;
        border-color: #e2e8f0 !important;
        transform: none !important;
    }

    /* Column Headers */
    .fc .fc-col-header {
        background: #f8fafc;
        border-radius: 0.75rem 0.75rem 0 0;
        overflow: hidden;
    }

    .fc .fc-col-header-cell {
        padding: 0.65rem 0.25rem !important;
        border-color: #e2e8f0 !important;
    }

    .fc .fc-col-header-cell-cushion {
        font-size: 0.725rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        color: #64748b !important;
        text-decoration: none !important;
    }

    /* Day Grid & Cells */
    .fc .fc-scrollgrid {
        border-color: #e2e8f0 !important;
        border-radius: 1rem !important;
        overflow: hidden !important;
    }

    .fc td, .fc th {
        border-color: #f1f5f9 !important;
    }

    .fc .fc-daygrid-day {
        transition: background-color 0.15s ease;
    }

    .fc .fc-daygrid-day:hover {
        background-color: #f8fafc;
        cursor: pointer;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: rgba(99, 102, 241, 0.04) !important;
        position: relative;
    }

    .fc .fc-daygrid-day.fc-day-today::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4f46e5, #6366f1);
        z-index: 1;
    }

    .fc .fc-daygrid-day-top {
        padding: 0.4rem 0.5rem 0.2rem !important;
    }

    .fc .fc-daygrid-day-number {
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        color: #475569 !important;
        text-decoration: none !important;
        width: 1.65rem;
        height: 1.65rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        transition: all 0.15s ease;
    }

    .fc .fc-day-today .fc-daygrid-day-number {
        background: #4f46e5 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.4) !important;
    }

    /* Event Badges */
    .fc .fc-event {
        border-radius: 0.5rem !important;
        border: none !important;
        padding: 2px 6px !important;
        margin-bottom: 2px !important;
        font-size: 0.725rem !important;
        font-weight: 600 !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06) !important;
        cursor: pointer !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    }

    .fc .fc-event:hover {
        transform: translateY(-1px) scale(1.01) !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
        z-index: 10 !important;
    }

    .fc-event-title {
        font-weight: 600 !important;
        letter-spacing: -0.01em;
    }

    /* More link popover */
    .fc .fc-more-link {
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        color: #4f46e5 !important;
        background: #eef2ff !important;
        padding: 1px 6px !important;
        border-radius: 9999px !important;
        text-decoration: none !important;
    }

    .fc .fc-more-link:hover {
        background: #e0e7ff !important;
        color: #3730a3 !important;
    }

    .fc .fc-popover {
        border-radius: 1rem !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
    }

    .fc .fc-popover-header {
        background: #f8fafc !important;
        padding: 0.65rem 0.85rem !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        color: #1e293b !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    /* Modern Tooltip */
    .fc-day-tooltip {
        position: absolute;
        z-index: 2000;
        background: rgba(255, 255, 255, 0.98);
        color: #1e293b;
        border-radius: 1rem;
        font-size: 0.775rem;
        line-height: 1.45;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        pointer-events: none;
        max-width: 320px;
        opacity: 0;
        transform: translateY(6px);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 0.85rem 1rem;
        border: 1px solid #e2e8f0;
        backdrop-filter: blur(8px);
    }

    .fc-day-tooltip.show {
        opacity: 1;
        transform: translateY(0);
    }

    .fc-day-tooltip .title {
        font-weight: 800;
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.825rem;
        color: #0f172a;
        padding-bottom: 0.35rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .fc-day-tooltip ul {
        margin: 0;
        padding-left: 0;
        list-style: none;
    }

    .fc-day-tooltip li {
        margin: 0.35rem 0;
        padding: 0.35rem 0.5rem;
        background: #f8fafc;
        border-radius: 0.5rem;
        border-left: 3px solid #6366f1;
        font-size: 0.75rem;
        color: #334155;
    }

    .fc-day-tooltip .arrow {
        position: absolute;
        width: 10px;
        height: 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        transform: rotate(45deg);
    }

    .fc-day-tooltip.at-right .arrow { left: -6px; top: 16px; border-right: none; border-bottom: none; }
    .fc-day-tooltip.at-left .arrow  { right: -6px; top: 16px; border-left: none; border-top: none; }
    .fc-day-tooltip.at-top .arrow   { left: 20px; bottom: -6px; border-top: none; border-left: none; }
    .fc-day-tooltip.at-bottom .arrow{ left: 20px; top: -6px; border-bottom: none; border-right: none; }

    /* Modal Styling */
    .modern-modal-content {
        border-radius: 1.25rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
        overflow: hidden;
    }
</style>
@endsection

@section('content')
<div class="space-y-5">

    <!-- ======================================================================== -->
    <!-- HERO HEADER & STATS SUMMARY BANNER                                       -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <!-- Left Info -->
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Modul Jadwal & Agenda Tim</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight leading-tight m-0 flex items-center gap-2.5">
                    <i class="bx bx-calendar-event text-indigo-600 text-3xl"></i> Kalender Jadwal
                </h1>
                <p class="text-slate-500 text-xs sm:text-sm max-w-xl mb-0">
                    Kelola meeting, penugasan teknisi, kegiatan operasional, dan pengingat harian secara terpadu.
                </p>
            </div>

            <!-- Right Action -->
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" data-bs-toggle="modal" data-bs-target="#addScheduleModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm hover:shadow transition-all cursor-pointer border-0 outline-none">
                    <i class="bx bx-plus-circle text-base"></i>
                    <span>Tambah Jadwal Baru</span>
                </button>
            </div>
        </div>

        <!-- Category Legends & Live Metrics Bar -->
        <div class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="bg-slate-50 hover:bg-slate-100/80 border border-slate-200/80 rounded-xl p-3 transition-all">
                <div class="text-[11px] font-semibold text-slate-500 flex items-center gap-1.5">
                    <i class="bx bx-calendar text-slate-600"></i> Total Jadwal
                </div>
                <div id="stat-total-events" class="text-lg font-black text-slate-800 mt-0.5">0</div>
            </div>
            <div class="bg-slate-50 hover:bg-slate-100/80 border border-slate-200/80 rounded-xl p-3 transition-all">
                <div class="text-[11px] font-semibold text-slate-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Meeting
                </div>
                <div id="stat-meeting-events" class="text-lg font-black text-indigo-600 mt-0.5">0</div>
            </div>
            <div class="bg-slate-50 hover:bg-slate-100/80 border border-slate-200/80 rounded-xl p-3 transition-all">
                <div class="text-[11px] font-semibold text-slate-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tugas
                </div>
                <div id="stat-task-events" class="text-lg font-black text-emerald-600 mt-0.5">0</div>
            </div>
            <div class="bg-slate-50 hover:bg-slate-100/80 border border-slate-200/80 rounded-xl p-3 transition-all">
                <div class="text-[11px] font-semibold text-slate-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> Acara
                </div>
                <div id="stat-event-events" class="text-lg font-black text-purple-600 mt-0.5">0</div>
            </div>
            <div class="bg-slate-50 hover:bg-slate-100/80 border border-slate-200/80 rounded-xl p-3 transition-all">
                <div class="text-[11px] font-semibold text-slate-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Pengingat
                </div>
                <div id="stat-reminder-events" class="text-lg font-black text-amber-600 mt-0.5">0</div>
            </div>
            <div class="bg-slate-50 hover:bg-slate-100/80 border border-slate-200/80 rounded-xl p-3 transition-all">
                <div class="text-[11px] font-semibold text-slate-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-pink-500"></span> Personal
                </div>
                <div id="stat-personal-events" class="text-lg font-black text-pink-600 mt-0.5">0</div>
            </div>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- MAIN CALENDAR CARD CONTAINER                                             -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5">
        <div id="calendar"></div>
    </div>

</div>

<!-- ======================================================================== -->
<!-- TAILWIND-STYLED MODAL: ADD / EDIT SCHEDULE                               -->
<!-- ======================================================================== -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modern-modal-content bg-white">
            
            <!-- Modal Header -->
            <div class="p-5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shrink-0 shadow-xs">
                        <i class="bx bx-calendar-event"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-800 m-0 leading-tight" id="addScheduleModalLabel">
                            Tambah Jadwal Baru
                        </h5>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Tentukan rincian tanggal, kategori, dan waktu agenda</p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 flex items-center justify-center transition-colors border-0 cursor-pointer" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bx bx-x text-xl"></i>
                </button>
            </div>

            <!-- Modal Body Form -->
            <div class="p-5 sm:p-6 space-y-4">
                <form id="addScheduleForm" autocomplete="off" class="space-y-4">
                    @csrf
                    <input type="hidden" id="scheduleId" name="schedule_id">

                    <!-- Title & Category -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <div class="sm:col-span-7 space-y-1">
                            <label for="scheduleTitle" class="block text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <i class="bx bx-edit text-indigo-500"></i> Judul Jadwal <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none bg-white"
                                   id="scheduleTitle" name="title" placeholder="Contoh: Rapat Evaluasi Jaringan Bulanan" required>
                            <div class="invalid-feedback text-[11px] text-rose-500 mt-1"></div>
                        </div>

                        <div class="sm:col-span-5 space-y-1">
                            <label for="scheduleCategory" class="block text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <i class="bx bx-category text-indigo-500"></i> Kategori <span class="text-rose-500">*</span>
                            </label>
                            <select class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none bg-white"
                                    id="scheduleCategory" name="category" required>
                                <option value="" disabled selected>Pilih Kategori</option>
                                <option value="meeting">🔵 Meeting</option>
                                <option value="task">🟢 Tugas</option>
                                <option value="event">🟣 Acara</option>
                                <option value="reminder">🟡 Pengingat</option>
                                <option value="personal">🔴 Personal</option>
                            </select>
                            <div class="invalid-feedback text-[11px] text-rose-500 mt-1"></div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1">
                        <label for="scheduleDescription" class="block text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                            <i class="bx bx-align-left text-indigo-500"></i> Deskripsi Jadwal (Opsional)
                        </label>
                        <textarea class="w-full rounded-xl border border-slate-200 p-3.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none bg-white"
                                  id="scheduleDescription" name="description" rows="2" placeholder="Tuliskan catatan detail agenda jika ada..."></textarea>
                        <div class="invalid-feedback text-[11px] text-rose-500 mt-1"></div>
                    </div>

                    <!-- Date & Time Type -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 pt-1">
                        <div class="sm:col-span-6 space-y-1">
                            <label for="scheduleDate" class="block text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <i class="bx bx-calendar text-indigo-500"></i> Tanggal Agenda <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none bg-white"
                                   id="scheduleDate" name="date" required>
                            <div class="invalid-feedback text-[11px] text-rose-500 mt-1"></div>
                        </div>

                        <div class="sm:col-span-6 space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5 mb-1.5">
                                <i class="bx bx-time-five text-indigo-500"></i> Pengaturan Waktu
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-medium text-slate-700 transition-all">
                                    <input type="radio" name="time_type" id="specificTime" value="specific" checked class="text-indigo-600 focus:ring-0">
                                    <span class="font-bold">Jam Khusus</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-medium text-slate-700 transition-all">
                                    <input type="radio" name="time_type" id="allDay" value="allday" class="text-indigo-600 focus:ring-0">
                                    <span>Seharian Penuh</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Time Inputs (Start & End) -->
                    <div class="grid grid-cols-2 gap-4 p-3 bg-slate-50/70 border border-slate-200 rounded-xl" id="timeInputs">
                        <div class="space-y-1">
                            <label for="startTime" class="block text-[11px] font-bold text-slate-600">Jam Mulai</label>
                            <input type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-800 bg-white outline-none focus:border-indigo-500"
                                   id="startTime" name="start_time" value="09:00">
                            <div class="invalid-feedback text-[11px] text-rose-500"></div>
                        </div>
                        <div class="space-y-1">
                            <label for="endTime" class="block text-[11px] font-bold text-slate-600">Jam Selesai</label>
                            <input type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-800 bg-white outline-none focus:border-indigo-500"
                                   id="endTime" name="end_time" value="10:00">
                            <div class="invalid-feedback text-[11px] text-rose-500"></div>
                        </div>
                    </div>

                    <!-- Color & Priority -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div class="space-y-1">
                            <label for="scheduleColor" class="block text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <i class="bx bx-palette text-indigo-500"></i> Warna Label
                            </label>
                            <select class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none bg-white font-medium"
                                    id="scheduleColor" name="color">
                                <option value="#6366f1">Indigo (Standard)</option>
                                <option value="#10b981">Emerald Green (Success)</option>
                                <option value="#ef4444">Rose Red (Danger)</option>
                                <option value="#f59e0b">Amber Orange (Warning)</option>
                                <option value="#06b6d4">Cyan (Info)</option>
                                <option value="#8b5cf6">Purple (Creative)</option>
                                <option value="#ec4899">Pink (Personal)</option>
                                <option value="#334155">Slate Dark</option>
                            </select>
                            <div class="invalid-feedback text-[11px] text-rose-500 mt-1"></div>
                        </div>

                        <div class="space-y-1">
                            <label for="schedulePriority" class="block text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <i class="bx bx-signal-5 text-indigo-500"></i> Tingkat Prioritas
                            </label>
                            <select class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none bg-white font-medium"
                                    id="schedulePriority" name="priority">
                                <option value="low">⚪ Prioritas Rendah</option>
                                <option value="medium" selected>🟡 Prioritas Sedang</option>
                                <option value="high">🔴 Prioritas Tinggi (Urgent)</option>
                            </select>
                            <div class="invalid-feedback text-[11px] text-rose-500 mt-1"></div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:px-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3">
                <button type="button" class="px-4 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs transition-all cursor-pointer flex items-center gap-1.5"
                        id="deleteSchedule" style="display: none;">
                    <i class="bx bx-trash text-sm"></i>
                    <span>Hapus Jadwal</span>
                </button>

                <div class="flex items-center gap-2.5 ml-auto">
                    <button type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 font-bold text-xs transition-all cursor-pointer"
                            data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm hover:shadow transition-all cursor-pointer flex items-center gap-1.5 border-0"
                            id="saveSchedule">
                        <span class="spinner-border spinner-border-sm me-1" style="display: none;"></span>
                        <i class="bx bx-check-circle text-sm"></i>
                        <span>Simpan Jadwal</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('page-script')
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
                    title: 'Terjadi Kesalahan',
                    text: message,
                    confirmButtonColor: '#4f46e5',
                    customClass: {
                        popup: 'rounded-2xl'
                    }
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
                    timer: 1600,
                    customClass: {
                        popup: 'rounded-2xl'
                    }
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
        
        function updateStatsCounter(events) {
            if (!Array.isArray(events)) return;
            const now = new Date();
            const todayStart = startOfDay(now);
            const todayEnd = endOfDay(now);
            
            let total = events.length;
            let meeting = 0, task = 0, eventCount = 0, reminder = 0, personal = 0;
            
            events.forEach(ev => {
                const cat = (ev.extendedProps?.category || '').toLowerCase();
                if (cat === 'meeting') meeting++;
                else if (cat === 'task') task++;
                else if (cat === 'event') eventCount++;
                else if (cat === 'reminder') reminder++;
                else if (cat === 'personal') personal++;
            });
            
            const elTotal = document.getElementById('stat-total-events');
            const elMeeting = document.getElementById('stat-meeting-events');
            const elTask = document.getElementById('stat-task-events');
            const elEvent = document.getElementById('stat-event-events');
            const elReminder = document.getElementById('stat-reminder-events');
            const elPersonal = document.getElementById('stat-personal-events');
            
            if (elTotal) elTotal.textContent = total;
            if (elMeeting) elMeeting.textContent = meeting;
            if (elTask) elTask.textContent = task;
            if (elEvent) elEvent.textContent = eventCount;
            if (elReminder) elReminder.textContent = reminder;
            if (elPersonal) elPersonal.textContent = personal;
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
                return `<span class="title">${dateText}</span><em class="text-slate-400 text-[11px]">Tidak ada jadwal</em>`;
            }
            
            const items = events.map(ev => {
                const createdBy = ev.extendedProps?.created_by || 'Staf';
                const cat = ev.extendedProps?.category ? `[${ev.extendedProps.category.toUpperCase()}] ` : '';
                
                if (ev.allDay) {
                    return `<li>
                        <strong class="text-indigo-600">${cat}${escapeHtml(ev.title)}</strong><br>
                        <span class="text-[10px] text-slate-500">Seharian • Dibuat oleh: ${escapeHtml(createdBy)}</span>
                    </li>`;
                }
                
                const s = fmtTime(ev.start);
                const e = ev.end ? fmtTime(ev.end) : '';
                
                return `<li>
                    <strong class="text-indigo-600">${cat}${escapeHtml(ev.title)}</strong><br>
                    <span class="text-[10px] text-slate-500">Pukul ${s}${e ? ' - ' + e : ''} • Oleh: ${escapeHtml(createdBy)}</span>
                </li>`;
            }).join('');
            
            return `<span class="title">${dateText}</span><ul>${items}</ul>`;
        }

        function escapeHtml(s) {
            return (s || '').replace(/[&<>"']/g, c => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));
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
        
        // Custom Buttons with Boxicons
        const customButtons = {
            prevBI: { icon: 'chevron-left', click: function() { calendar.prev(); } },
            nextBI: { icon: 'chevron-right', click: function() { calendar.next(); } },
            todayBI: { text: 'Hari Ini', click: function() { calendar.today(); } }
        };
        
        // Responsive Header Toolbar
        function getHeaderConfig() {
            const w = window.innerWidth;
            if (w < 640) {
                return { left: 'prevBI,nextBI todayBI', center: '', right: 'title' };
            }
            if (w < 840) {
                return { left: 'prevBI,nextBI todayBI', center: 'title', right: 'dayGridMonth,listWeek' };
            }
            return { left: 'prevBI,nextBI todayBI', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' };
        }
        
        // Initialize Calendar
        const calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'standard',
            initialView: 'dayGridMonth',
            locale: 'id',
            firstDay: 1,
            contentHeight: 'auto',
            aspectRatio: 1.6,
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
            
            // Events Loader
            events: function(info, successCallback, failureCallback) {
                apiRequest(`${config.apiBaseUrl}/events?start=${info.startStr}&end=${info.endStr}`)
                .then(data => {
                    successCallback(data);
                    updateStatsCounter(calendar.getEvents());
                })
                .catch(error => {
                    console.error('Backend loading failed:', error);
                    failureCallback(error);
                });
            },
            
            eventsSet: function() {
                updateStatsCounter(calendar.getEvents());
            },
            
            // Date Click
            dateClick: function(info) {
                selectedDate = info.dateStr;
                openAddModal(selectedDate);
            },
            
            // Event Click
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                openEditModal(info.event);
            },
            
            // Tooltip on Hover
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
                    }, 120);
                });
                
                el.addEventListener('mouseleave', () => {
                    clearTimeout(hoverTimeout);
                    hideTooltip();
                });
            }
        });
        
        calendar.render();
        
        // Modal functions
        function openAddModal(date = null) {
            currentSchedule = null;
            currentEventId = null;
            
            document.getElementById('addScheduleModalLabel').textContent = 'Tambah Jadwal Baru';
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
            
            document.getElementById('timeInputs').style.display = 'grid';
            document.getElementById('specificTime').checked = true;
            
            const modal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
            modal.show();
        }
        
        async function openEditModal(event) {
            try {
                let schedule;
                currentEventId = event.id;
                
                const response = await apiRequest(`${config.apiBaseUrl}/${event.id}`);
                schedule = response.data;
                
                currentSchedule = schedule;
                document.getElementById('addScheduleModalLabel').textContent = 'Edit Jadwal Agenda';
                document.getElementById('deleteSchedule').style.display = 'inline-flex';
                
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
                    document.getElementById('timeInputs').style.display = 'grid';
                }
                
                clearFormErrors();
                
                const modal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
                modal.show();
            } catch (error) {
                showError('Gagal memuat data jadwal: ' + error.message);
            }
        }
        
        // Time Type Radio change handler
        const timeTypeRadios = document.querySelectorAll('input[name="time_type"]');
        const timeInputs = document.getElementById('timeInputs');
        
        timeTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'allday') {
                    timeInputs.style.display = 'none';
                } else {
                    timeInputs.style.display = 'grid';
                }
            });
        });
        
        // Save schedule handler
        document.getElementById('saveSchedule').addEventListener('click', async function() {
            const form = document.getElementById('addScheduleForm');
            const saveBtn = this;
            const spinner = saveBtn.querySelector('.spinner-border');
            const icon = saveBtn.querySelector('.bx');
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            saveBtn.disabled = true;
            if (spinner) spinner.style.display = 'inline-block';
            if (icon) icon.style.display = 'none';
            
            try {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                if (data.time_type === 'allday') {
                    data.start_time = null;
                    data.end_time = null;
                }
                
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
                
                showSuccess(response.message || 'Jadwal berhasil disimpan!');
                calendar.refetchEvents();
                
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
                if (spinner) spinner.style.display = 'none';
                if (icon) icon.style.display = 'inline-block';
            }
        });
        
        // Delete schedule handler
        document.getElementById('deleteSchedule').addEventListener('click', async function() {
            const scheduleId = currentSchedule?.id || currentEventId;
            if (!scheduleId) return;
            
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: 'Hapus Jadwal Ini?',
                    text: 'Data agenda yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus Jadwal',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-2xl'
                    }
                });
                
                if (!result.isConfirmed) return;
            } else {
                if (!confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) return;
            }
            
            try {
                await apiRequest(`${config.apiBaseUrl}/${scheduleId}`, {
                    method: 'DELETE'
                });
                
                calendar.refetchEvents();
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addScheduleModal'));
                modal.hide();
                showSuccess('Jadwal berhasil dihapus');
            } catch (error) {
                showError('Gagal menghapus jadwal: ' + error.message);
            }
        });
        
        // Responsive toolbar listener
        window.addEventListener('resize', function() {
            calendar.setOption('headerToolbar', getHeaderConfig());
            hideTooltip();
        });
        window.addEventListener('scroll', hideTooltip, true);
        
        // Modal reset when hidden
        document.getElementById('addScheduleModal').addEventListener('hidden.bs.modal', function() {
            currentSchedule = null;
            currentEventId = null;
            selectedDate = null;
            clearFormErrors();
        });
    });
</script>
@endsection