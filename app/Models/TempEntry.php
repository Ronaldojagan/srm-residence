<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TempEntry extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable=[
        'order_id',
        'type',
        'notes',
        'status',
    ];
}
