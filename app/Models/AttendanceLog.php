<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'type',
        'date',
        'time',
        'latitude',
        'longitude',
        'distance_meters',
        'location_name',
        'photo_path',
        'status',
        'duration',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
        'distance_meters' => 'integer',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedTimeAttribute(): string
    {
        return date('h:i A', strtotime($this->time));
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'check_in' => 'Presensi Masuk',
            'break_start' => 'Mulai Istirahat',
            'break_end' => 'Selesai Istirahat',
            'check_out' => 'Presensi Pulang',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
