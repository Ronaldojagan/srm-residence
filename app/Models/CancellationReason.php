<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationReason extends Model
{
    use HasFactory, ResolveRouteBinding;

    protected $fillable = [
        'reason',
        'is_show',
    ];

    protected $casts = [
        'is_show' => 'boolean',
    ];
}
