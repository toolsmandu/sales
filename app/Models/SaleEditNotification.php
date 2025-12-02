<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleEditNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'actor_id',
        'message',
    ];

    /**
     * @return BelongsTo<Sale, SaleEditNotification>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * @return BelongsTo<User, SaleEditNotification>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
