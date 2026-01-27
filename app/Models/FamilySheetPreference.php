<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilySheetPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'context',
        'family_product_id',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];
}
