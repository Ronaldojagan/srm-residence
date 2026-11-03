<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MissingBottle extends Model
{
    use HasFactory, ResolveRouteBinding;

    protected $table = 'missing_bottles';

    protected $fillable = [
        'user_id',
        'order_id',
        'delivery_man_id',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Customer
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Delivery Man
    public function deliveryMan()
    {
        return $this->belongsTo(User::class, 'delivery_man_id');
    }
}
