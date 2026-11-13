<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nik',
        'phone',
        'role',
        'department_id',
        'shift_id',
        'working_days',
        'office_id',
        'position',
        'face_reference',
        'face_reference_status',
        'pending_face_reference',
        'face_rejection_reason',
        'allow_face_registration',
        'face_unlock_requested',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'allow_face_registration' => 'boolean',
            'face_unlock_requested' => 'boolean',
            'working_days' => 'array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Super Admin selalu memiliki akses penuh ke panel admin
        if ($this->email === 'admin@hadirku.com' || $this->id === 1) {
            return true;
        }

        return $this->role === 'admin' && (bool) $this->is_active;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0D9488&color=ffffff&bold=true';
    }

    public function getFaceReferenceUrlAttribute(): ?string
    {
        if ($this->face_reference) {
            return asset('storage/' . $this->face_reference);
        }
        return null;
    }

    public function getPendingFaceReferenceUrlAttribute(): ?string
    {
        if ($this->pending_face_reference) {
            return asset('storage/' . $this->pending_face_reference);
        }
        return null;
    }

    public static function dayOptions(): array
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
    }

    public static function dayShortLabels(): array
    {
        return [
            1 => 'Sen',
            2 => 'Sel',
            3 => 'Rab',
            4 => 'Kam',
            5 => 'Jum',
            6 => 'Sab',
            7 => 'Min',
        ];
    }

    /**
     * Cek apakah tanggal tertentu merupakan hari kerja pegawai ini.
     */
    public function isWorkingDay(?\Carbon\Carbon $date = null): bool
    {
        $targetDate = $date ?? \Carbon\Carbon::today('Asia/Jakarta');
        $days = $this->working_days;

        // Fallback default Senin-Jumat jika belum diatur
        if (! is_array($days) || empty($days)) {
            $days = [1, 2, 3, 4, 5];
        }

        $normalized = array_map('intval', $days);

        return in_array($targetDate->dayOfWeekIso, $normalized, true);
    }

    /**
     * Ringkasan jadwal hari kerja pegawai (misal: "Sen, Rab, Jum").
     */
    public function getWorkingDaysSummaryAttribute(): string
    {
        $days = $this->working_days;
        if (! is_array($days) || empty($days)) {
            $days = [1, 2, 3, 4, 5];
        }

        $normalized = array_map('intval', $days);
        sort($normalized);
        $short = self::dayShortLabels();

        $labels = array_values(array_filter(array_map(fn ($d) => $short[$d] ?? null, $normalized)));

        return implode(', ', $labels);
    }
}
