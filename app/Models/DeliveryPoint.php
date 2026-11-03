<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Model;

class DeliveryPoint extends Model
{
    use ResolveRouteBinding;
    protected $fillable = [
        'location',
        'sub_location',
        'status',
    ];

    public function deliveryMen()
    {
        return $this->hasMany(User::class, 'delivery_point_id');
    }
}
