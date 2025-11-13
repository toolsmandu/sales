<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_in_stock'];

    protected $casts = [
        'is_in_stock' => 'boolean',
    ];

    /**
     * @return HasMany<ProductVariation>
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }
}
