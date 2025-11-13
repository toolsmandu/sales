<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'monthly_hours_quota',
        'hourly_rate',
        'daily_hours_quota',
        'holiday_weekdays',
    ];

    protected $casts = [
        'monthly_hours_quota' => 'integer',
        'hourly_rate' => 'decimal:2',
        'daily_hours_quota' => 'integer',
        'holiday_weekdays' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
