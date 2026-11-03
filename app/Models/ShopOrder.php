<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shop_order_no',
        'order_id',
        'shop_id',
        'user_id',
        'total_amount',
        'total_shop_commission',
        'total_admin_commission',
        'commission_percentage',
        'status',
        'shipped_items',
        'total_items',
        'delivered_items',
        'delivery_completed_at'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id')->where('shop_id', $this->shop_id);
    }
}
