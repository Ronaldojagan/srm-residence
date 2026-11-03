<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Precheckout extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable = [
        'user_id',
        'shipping_address_id',
        'coupon_id',
        'original_sub_total',
        'sub_total',
        'delivery_charge',
        'original_delivery_amount',
        'coupon_amount',
        'discount_amount',
        'grand_total',
        'is_order_placed',
        'added_carts',
        'preckout_type',
        'platform_fee'
    ];

    protected $casts = [
        'original_sub_total' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'coupon_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'is_order_placed' => 'boolean',
        'added_carts' => 'array',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
