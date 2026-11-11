<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'office_id',
        'date',
        'check_in',
        'check_out',
        'break_start',
        'break_end',
        'check_in_lat',
        'check_in_lng',
        'check_in_distance',
        'check_in_photo',
        'check_in_status',
        'check_out_lat',
        'check_out_lng',
        'check_out_distance',
        'check_out_photo',
        'check_out_status',
        'working_minutes',
        'break_minutes',
        'status',
        'notes',
        'face_verified',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_lat' => 'float',
        'check_in_lng' => 'float',
        'check_in_distance' => 'integer',
        'check_out_lat' => 'float',
        'check_out_lng' => 'float',
        'check_out_distance' => 'integer',
        'working_minutes' => 'integer',
        'break_minutes' => 'integer',
        'face_verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class)->orderBy('time', 'asc');
    }

    /**
     * Format working minutes to "HH:MM"
     */
    public function getFormattedWorkingHoursAttribute(): string
    {
        $minutes = $this->working_minutes ?: 0;
        $hours = floor($minutes / 60);
        $remMin = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $remMin);
    }

    /**
     * Calculate and update working and break minutes
     */
    public function recalculateDurations(): void
    {
        if ($this->break_start && $this->break_end) {
            $start = Carbon::parse($this->break_start);
            $end = Carbon::parse($this->break_end);
            $this->break_minutes = max(0, $start->diffInMinutes($end));
        }

        if ($this->check_in) {
            $start = Carbon::parse($this->check_in);
            $end = $this->check_out ? Carbon::parse($this->check_out) : Carbon::now('Asia/Jakarta');
            $totalMins = max(0, $start->diffInMinutes($end));
            $this->working_minutes = max(0, $totalMins - $this->break_minutes);
        }
        $this->save();
    }
}
