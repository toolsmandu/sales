<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotEntry extends Model
{
    protected $fillable = [
        'product_id',
        'question',
        'answer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';

        return $query->where(function (Builder $subQuery) use ($like): void {
            $subQuery
                ->where('question', 'like', $like)
                ->orWhere('answer', 'like', $like);
        });
    }
}
