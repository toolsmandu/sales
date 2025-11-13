<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'started_at',
        'ended_at',
        'total_minutes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForMonthYear($query, int $month, int $year)
    {
        return $query->whereMonth('work_date', $month)->whereYear('work_date', $year);
    }

    public function getTotalHoursAttribute(): float
    {
        return round($this->total_minutes / 60, 2);
    }

    public static function minutesBetween(?Carbon $start, ?Carbon $end): int
    {
        if (!$start || !$end) {
            return 0;
        }

        return max(0, (int) $start->diffInMinutes($end));
    }
}
