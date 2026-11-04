<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_number',
        'floor',
        'room_type',
        'capacity',
        'monthly_rent',
        'security_deposit',
        'occupancy_status',
        'amenities',
        'status',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'capacity' => 'integer',
        'status' => 'boolean',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'room_number', 'room_number');
    }
}
