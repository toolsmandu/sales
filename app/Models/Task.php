<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    public const RECURRENCE_ONCE = 'once';
    public const RECURRENCE_DAILY = 'daily';
    public const RECURRENCE_WEEKLY = 'weekly';
    public const RECURRENCE_MONTHLY = 'monthly';
    public const RECURRENCE_CUSTOM = 'custom';

    protected $fillable = [
        'title',
        'description',
        'assigned_user_id',
        'created_by',
        'recurrence_type',
        'custom_weekdays',
        'custom_interval_days',
        'start_date',
        'end_date',
        'due_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'custom_weekdays' => 'array',
        'custom_interval_days' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'due_date' => 'date',
    ];

    public static function recurrenceOptions(): array
    {
        return [
            self::RECURRENCE_ONCE => 'One time',
            self::RECURRENCE_DAILY => 'Daily',
            self::RECURRENCE_WEEKLY => 'Weekly',
            self::RECURRENCE_MONTHLY => 'Monthly',
            self::RECURRENCE_CUSTOM => 'Custom range',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    public function scopeForMonthYear($query, int $month, int $year)
    {
        return $query->where(function ($inner) use ($month, $year) {
            $inner->whereMonth('start_date', $month)->whereYear('start_date', $year)
                ->orWhere(function ($dateQuery) use ($month, $year) {
                    $dateQuery->whereMonth('due_date', $month)->whereYear('due_date', $year);
                });
        });
    }

    public function wasCompletedOn(Carbon $date): bool
    {
        if ($this->relationLoaded('completions')) {
            return $this->completions->contains(
                fn (TaskCompletion $completion) => $completion->completed_on->isSameDay($date)
            );
        }

        return $this->completions()->whereDate('completed_on', $date->toDateString())->exists();
    }

    public function isDueOn(Carbon $date): bool
    {
        return match ($this->recurrence_type) {
            self::RECURRENCE_ONCE => $this->due_date?->isSameDay($date) ?? false,
            self::RECURRENCE_DAILY => $this->isWithinRange($date),
            self::RECURRENCE_WEEKLY => $this->isWithinRange($date) && $this->start_date?->dayOfWeek === $date->dayOfWeek,
            self::RECURRENCE_MONTHLY => $this->isWithinRange($date) && $this->start_date?->day === $date->day,
            self::RECURRENCE_CUSTOM => $this->isWithinRange($date) && $this->matchesCustomPattern($date),
            default => false,
        };
    }

    protected function isWithinRange(Carbon $date): bool
    {
        $start = $this->start_date ?? $this->due_date;
        $end = $this->end_date;

        if (!$start) {
            return false;
        }

        if ($date->lt($start)) {
            return false;
        }

        if ($end && $date->gt($end)) {
            return false;
        }

        return true;
    }

    protected function matchesCustomPattern(Carbon $date): bool
    {
        $interval = (int) ($this->custom_interval_days ?? 0);
        $start = $this->start_date ?? $this->due_date;

        if ($interval > 0 && $start) {
            $diff = $start->diffInDays($date);
            return $diff % $interval === 0;
        }

        return $this->matchesCustomWeekday($date);
    }

    protected function matchesCustomWeekday(Carbon $date): bool
    {
        $days = $this->custom_weekdays ?? [];
        if (empty($days)) {
            return true;
        }

        $weekday = strtolower($date->format('l'));

        return in_array($weekday, $days, true);
    }
}
