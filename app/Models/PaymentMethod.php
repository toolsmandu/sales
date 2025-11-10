<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'slug',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * @return HasMany<PaymentTransaction>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * @return HasMany<Sale>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentMethod $method): void {
            $method->slug = $method->slug ?: self::generateSlug($method->label);
        });

        static::updating(function (PaymentMethod $method): void {
            if ($method->isDirty('label')) {
                $method->slug = self::generateSlug($method->label);
            }
        });
    }

    public function recalculateBalance(): void
    {
        $total = $this->transactions()
            ->selectRaw('COALESCE(SUM(CASE WHEN type = "income" THEN amount ELSE -amount END), 0) as total')
            ->value('total');

        $this->forceFill([
            'balance' => $total ?? 0,
        ])->save();
    }

    public static function generateSlug(string $label): string
    {
        $slug = Str::slug($label);

        if ($slug === '') {
            $slug = Str::random(8);
        }

        return $slug;
    }
}
