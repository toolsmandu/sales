<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
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

    public function setActivationKeyAttribute($value): void
    {
        $this->attributes['activation_key'] = $value !== null ? Crypt::encryptString($value) : null;
    }

    public function getActivationKeyAttribute($value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            Log::warning('Unable to decrypt activation key', [
                'stock_key_id' => $this->getKey(),
                'error' => $e->getMessage(),
            ]);

            return $value;
        }
    }
}
