<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'table_name',
        'linked_product_id',
        'linked_variation_ids',
    ];
}
