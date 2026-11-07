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
        'eb_unit_rate',
        'maintenance_charges',
        'other_charges',
        'security_deposit',
        'occupancy_status',
        'is_self',
        'amenities',
        'status',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'eb_unit_rate' => 'decimal:2',
        'maintenance_charges' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'capacity' => 'integer',
        'is_self' => 'boolean',
        'status' => 'boolean',
    ];

    public function getTotalMonthlyAttribute(): float
    {
        return (float) $this->monthly_rent + (float) $this->maintenance_charges + (float) $this->other_charges;
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'room_id');
    }

    public function assignedTenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_rooms', 'room_id', 'tenant_id')->withTimestamps();
    }

    public function activeTenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_rooms', 'room_id', 'tenant_id')->where('tenants.status', 1);
    }

    public function getActiveResidentsAttribute()
    {
        if ($this->relationLoaded('activeTenants') && $this->activeTenants->isNotEmpty()) {
            return $this->activeTenants;
        }
        $t = $this->activeTenants()->get();
        if ($t->isNotEmpty()) {
            return $t;
        }
        if ($this->currentTenant) {
            return collect([$this->currentTenant]);
        }
        return collect();
    }

    public function currentTenant()
    {
        return $this->hasOne(Tenant::class, 'room_id')->where('status', 1)->latestOfMany();
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'room_id');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'room_id');
    }
}

