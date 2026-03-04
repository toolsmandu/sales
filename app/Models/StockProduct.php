<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockProduct extends Model
{
    use HasFactory;

    protected $table = 'stock_products';

    protected $fillable = [
        'name',
        'slug',
        'table_name',
        'expiry_days',
        'linked_product_id',
        'linked_variation_ids',
        'linked_products',
        'stock_account_note',
    ];

    protected $casts = [
        'linked_variation_ids' => 'array',
        'linked_products' => 'array',
        'expiry_days' => 'integer',
    ];
}
