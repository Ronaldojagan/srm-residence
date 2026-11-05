<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_id',
        'name',
        'email',
        'phone',
        'room_number',
        'room_type',
        'monthly_rent',
        'eb_charges',
        'maintenance_charges',
        'other_charges',
        'security_deposit',
        'commence_date',
        'emergency_contact',
        'address',
        'status',
    ];

    protected $casts = [
        'room_id' => 'integer',
        'monthly_rent' => 'float',
        'eb_charges' => 'float',
        'maintenance_charges' => 'float',
        'other_charges' => 'float',
        'security_deposit' => 'float',
        'commence_date' => 'date',
        'status' => 'boolean',
    ];

    public function getTotalMonthlyDueAttribute(): float
    {
        return (float) $this->monthly_rent + (float) $this->eb_charges + (float) $this->maintenance_charges + (float) $this->other_charges;
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'tenant_id');
    }

    public function payments()
    {
        return $this->hasMany(BillPayment::class, 'tenant_id');
    }
}

