<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryMan extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'img',
        'password',
        'location',

        'status',
        'device_id',
        'fcm_token',
    ];
}
