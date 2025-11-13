<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'purchase_date',
        'product_name',
        'product_expiry_days',
        'remarks',
        'phone',
        'email',
        'sales_amount',
        'payment_method_id',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'sales_amount' => 'decimal:2',
        'product_expiry_days' => 'integer',
    ];

    /**
     * @return BelongsTo<PaymentMethod, Sale>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * @return HasOne<PaymentTransaction>
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(PaymentTransaction::class);
    }

    /**
     * @return BelongsTo<User, Sale>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
