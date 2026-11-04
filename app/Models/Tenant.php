<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'room_number',
        'room_type',
        'monthly_rent',
        'security_deposit',
        'check_in_date',
        'emergency_contact',
        'address',
        'status',
    ];

    protected $casts = [
        'monthly_rent' => 'float',
        'security_deposit' => 'float',
        'check_in_date' => 'date',
        'status' => 'boolean',
    ];
}
