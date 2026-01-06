<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockKeyView extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_key_id',
        'viewed_by_user_id',
        'remarks',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function stockKey(): BelongsTo
    {
        return $this->belongsTo(StockKey::class);
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_by_user_id');
    }
}
