<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, ResolveRouteBinding;

    protected $fillable = [
        'name',
        'image',
        'amount',
        'days',
        'status',
    ];

    // protected $appends = ['image_url', 'is_liked', 'full_images', 'available_stock'];

    // protected $casts = [
    //     'additional_info' => 'array',
    //     'amount' => 'integer',
    //     'discount_amount' => 'integer',
    //     'discount_percentage' => 'integer',
    //     'product_stock' => 'integer',
    // ];
}
