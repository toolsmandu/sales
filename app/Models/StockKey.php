<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class StockKey extends Model
{
    protected $fillable = [
        'product_id',
        'activation_key',
        'viewed_at',
        'viewed_by_user_id',
        'viewed_by_pin_name',
        'viewed_remarks',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeFresh(Builder $query): Builder
    {
        return $query->whereNull('viewed_at');
    }

    public function scopeViewed(Builder $query): Builder
    {
        return $query->whereNotNull('viewed_at');
    }

    public function viewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_by_user_id');
    }

    public function markAsViewed(?User $viewer = null, ?string $remarks = null): void
    {
        $this->forceFill([
            'viewed_at' => now(),
            'viewed_by_user_id' => $viewer?->getKey(),
            'viewed_by_pin_name' => null,
            'viewed_remarks' => $remarks !== null ? trim($remarks) : null,
        ])->save();
    }
}
