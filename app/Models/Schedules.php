<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Schedules extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'description',
        'date',
        'time_type',
        'start_time',
        'end_time',
        'color',
        'priority',
        'user_id',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'is_active' => 'boolean'
    ];

    /**
     * Relationship dengan User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk jadwal aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk jadwal berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope untuk jadwal dalam rentang tanggal
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope untuk jadwal berdasarkan kategori
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope untuk jadwal berdasarkan prioritas
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Accessor untuk mendapatkan datetime start lengkap
     */
    public function getStartDatetimeAttribute()
    {
        if ($this->time_type === 'allday') {
            return $this->date->startOfDay();
        }
        
        return Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->start_time);
    }

    /**
     * Accessor untuk mendapatkan datetime end lengkap
     */
    public function getEndDatetimeAttribute()
    {
        if ($this->time_type === 'allday') {
            return $this->date->endOfDay();
        }
        
        if ($this->end_time) {
            return Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->end_time);
        }
        
        return $this->start_datetime;
    }

    /**
     * Accessor untuk format FullCalendar
     */
    public function getFullCalendarFormatAttribute()
    {
        $event = [
            'id' => $this->id,
            'title' => $this->title,
            'color' => $this->color,
            'extendedProps' => [
                'category' => $this->category,
                'description' => $this->description,
                'priority' => $this->priority,
                'time_type' => $this->time_type,
                'created_by' => $this->user?->name ?? 'Unknown'
            ]
        ];

        if ($this->time_type === 'allday') {
            $event['start'] = $this->date->format('Y-m-d');
            $event['allDay'] = true;
        } else {
            $event['start'] = $this->start_datetime->format('Y-m-d\TH:i:s');
            if ($this->end_time) {
                $event['end'] = $this->end_datetime->format('Y-m-d\TH:i:s');
            }
        }

        return $event;
    }

    /**
     * Method untuk mendapatkan label kategori
     */
    public function getCategoryLabelAttribute()
    {
        $labels = [
            'meeting' => 'Meeting',
            'task' => 'Tugas',
            'event' => 'Acara',
            'reminder' => 'Pengingat',
            'personal' => 'Personal'
        ];

        return $labels[$this->category] ?? $this->category;
    }

    /**
     * Method untuk mendapatkan label prioritas
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi'
        ];

        return $labels[$this->priority] ?? $this->priority;
    }

    /**
     * Method untuk mendapatkan badge class berdasarkan prioritas
     */
    public function getPriorityBadgeClassAttribute()
    {
        $classes = [
            'low' => 'badge-light-success',
            'medium' => 'badge-light-warning',
            'high' => 'badge-light-danger'
        ];

        return $classes[$this->priority] ?? 'badge-light-secondary';
    }
}