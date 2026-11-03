<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, ResolveRouteBinding;

     protected $fillable = [
        'user_id',
        'order_id',
        'transaction_id',
        'merchant_transaction_id',
        'payment_no',
        'amount',
        'type',
        'date',
        'response',
        'payment_method',
        'refund_id',
        'refund_amount',
        'refunded_at',
        'refund_response',
        'status',
        'collected_at',
        'payment_collected',
        'wallet_related',
        'wallet_id',
        'wallet_used',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'date' => 'date',
        'refunded_at' => 'datetime',
        'response' => 'array',
        'refund_response' => 'array',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
