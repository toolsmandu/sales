<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'notes',
        'expiry_days',
        'is_in_stock',
        'is_dynamic',
    ];

    protected $casts = [
        'expiry_days' => 'integer',
        'is_in_stock' => 'boolean',
        'is_dynamic' => 'boolean',
    ];

    /**
     * @return BelongsTo<Product, ProductVariation>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
