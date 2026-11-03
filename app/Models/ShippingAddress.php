<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingAddress extends Model
{

    use HasFactory, SoftDeletes, ResolveRouteBinding;
    protected $fillable = [
        'flat_no',
        'area_name',
        'street',
        'city',
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

    protected $casts = [
        'is_default' => 'bool'
    ];

    public function getFullAddressAttribute()
    {
        return collect([
            $this->flat_no,
            $this->street,
            $this->area_name,
            $this->city,
            $this->district,
            $this->pincode,
        ])->filter()->implode(', ');
    }

}
