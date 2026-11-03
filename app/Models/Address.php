<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable = [
        'user_id',
        'flat_no',
        'area_name',
        'street',
        'city',
        'state',
        'country',
        'district',
        'pincode',
        'address_type',
        'status',
        'is_default',
        'lat',
        'long',
        'name',
        'phone',
    ];
}
