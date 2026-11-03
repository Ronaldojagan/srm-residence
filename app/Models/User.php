<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, ResolveRouteBinding, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'image',
        'password',
        'type',
        'status',
        'device_id',
        'fcm_token',
        'delivery_point_id',
        'full_address'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function bankDetails()
    {
        return $this->hasOne(BankDetails::class, 'user_id');
    }

    public function deliveryPoint()
    {
        return $this->belongsTo(DeliveryPoint::class, 'delivery_point_id');
    }

    public function deliveredOrders()
    {
        return $this->hasMany(Order::class, 'delivery_man_id')->where('status', "Delivered");
    }
}
