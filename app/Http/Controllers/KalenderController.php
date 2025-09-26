<?php

namespace App\Http\Controllers;

use App\Models\Schedules;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class KalenderController extends Controller
{
    public function jadwal(): View
    {
        return view('jadwal.kalender-jadwal', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
        ]);
    }

    public function getEvents(Request $request): JsonResponse
    {
        $start = $request->get('start');
        $end   = $request->get('end');

        $schedules = Schedules::active()
            ->where('user_id', auth()->id())
            ->betweenDates($start, $end)
            ->get();

        $events = $schedules->map->full_calendar_format;

        return response()->json($events);
    }

    /**
     * Store a new schedule
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|in:meeting,task,event,reminder,personal',
            'description' => 'nullable|string',
            'date'        => 'required|date',
            'time_type'   => 'required|in:allday,specific',
            'start_time'  => 'nullable|required_if:time_type,specific|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after:start_time',
            'color'       => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'priority'    => 'required|in:low,medium,high'
        ]);

        if ($validated['time_type'] === 'allday') {
            $validated['start_time'] = null;
            $validated['end_time']   = null;
        }

        $validated['user_id'] = auth()->id();

        $schedule = Schedules::create($validated);

        // Catat Log Aktivitas
        activity('Schedule')
            ->causedBy(auth()->user())
            ->log(auth()->user()->name . ' Menambah Schedule ' . $schedule->title . ' pada ' . Carbon::parse($schedule->created_at)->locale('id')->isoFormat('D MMMM Y'));

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil ditambahkan',
            'data'    => $schedule->full_calendar_format
        ]);
    }

    /**
     * Update an existing schedule
     */
    public function update(Request $request, Schedules $schedule): JsonResponse
    {
        if (!$this->authorizeSchedule($schedule)) {
            return $this->unauthorizedResponse();
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|in:meeting,task,event,reminder,personal',
            'description' => 'nullable|string',
            'date'        => 'required|date',
            'time_type'   => 'required|in:allday,specific',
            'start_time'  => 'nullable|required_if:time_type,specific|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after:start_time',
            'color'       => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'priority'    => 'required|in:low,medium,high'
        ]);

        if ($validated['time_type'] === 'allday') {
            $validated['start_time'] = null;
            $validated['end_time']   = null;
        }

        $schedule->update($validated);
        activity('Schedule')
            ->causedBy(auth()->user())
            ->log(auth()->user()->name . ' Memperbaharui Schedule ' . $schedule->title . ' pada ' . Carbon::parse($schedule->created_at)->locale('id')->isoFormat('D MMMM Y'));
        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diperbarui',
            'data'    => $schedule->fresh()->full_calendar_format
        ]);
    }

    /**
     * Delete a schedule
     */
    public function destroy(Schedules $schedule): JsonResponse
    {
        if (!$this->authorizeSchedule($schedule)) {
            return $this->unauthorizedResponse();
        }

        $schedule->delete();
        activity('Schedule')
            ->causedBy(auth()->user())
            ->log(auth()->user()->name . ' Menghapus Schedule ' . $schedule->title . ' pada ' . Carbon::parse($schedule->created_at)->locale('id')->isoFormat('D MMMM Y'));
        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus'
        ]);
    }

    /**
     * Update schedule date/time (for drag & drop)
     */
    public function updateDateTime(Request $request, Schedules $schedule): JsonResponse
    {
        if (!$this->authorizeSchedule($schedule)) {
            return $this->unauthorizedResponse();
        }

        $validated = $request->validate([
            'date'       => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i|after:start_time'
        ]);

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dipindahkan',
            'data'    => $schedule->fresh()->full_calendar_format
        ]);
    }

    /**
     * Get schedule details
     */
    public function show(Schedules $schedule): JsonResponse
    {
        if (!$this->authorizeSchedule($schedule)) {
            return $this->unauthorizedResponse();
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $schedule->id,
                'title'          => $schedule->title,
                'category'       => $schedule->category,
                'category_label' => $schedule->category_label,
                'description'    => $schedule->description,
                'date'           => $schedule->date->format('Y-m-d'),
                'time_type'      => $schedule->time_type,
                'start_time'     => $schedule->start_time,
                'end_time'       => $schedule->end_time,
                'color'          => $schedule->color,
                'priority'       => $schedule->priority,
                'priority_label' => $schedule->priority_label,
                'created_at'     => $schedule->created_at->format('d/m/Y H:i')
            ]
        ]);
    }

    /**
     * Helper: authorize schedule ownership
     */
    private function authorizeSchedule(Schedules $schedule): bool
    {
        return $schedule->user_id === auth()->id();
    }

    /**
     * Helper: unauthorized response
     */
    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
}