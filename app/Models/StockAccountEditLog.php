<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAccountEditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id',
        'context',
        'message',
    ];

    /**
     * @return BelongsTo<User, StockAccountEditLog>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
