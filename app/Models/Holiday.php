<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Ambil data hari libur aktif pada tanggal tertentu (default hari ini).
     */
    public static function getHolidayForDate(?Carbon $date = null): ?self
    {
        $targetDate = ($date ?? Carbon::today('Asia/Jakarta'))->toDateString();

        return static::where('is_active', true)
            ->whereDate('date', $targetDate)
            ->first();
    }

    /**
     * Ambil daftar string tanggal (Y-m-d) hari libur aktif dalam rentang tanggal.
     */
    public static function getActiveDatesBetween(Carbon $start, Carbon $end): array
    {
        return static::where('is_active', true)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->toArray();
    }
}
