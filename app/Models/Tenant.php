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
        'eb_unit_rate',
        'eb_start_reading',
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
        'eb_unit_rate' => 'float',
        'eb_start_reading' => 'float',
        'maintenance_charges' => 'float',
        'other_charges' => 'float',
        'security_deposit' => 'float',
        'commence_date' => 'date',
        'status' => 'boolean',
    ];

    public function getMonthlyRentAttribute($value)
    {
        $rooms = $this->assigned_rooms;
        if ($rooms->isNotEmpty()) {
            return (float) $rooms->sum('monthly_rent');
        }
        return (float) $value;
    }

    public function getSecurityDepositAttribute($value)
    {
        $rooms = $this->assigned_rooms;
        if ($rooms->isNotEmpty()) {
            return (float) $rooms->sum('security_deposit');
        }
        return (float) $value;
    }

    public function getMaintenanceChargesAttribute($value)
    {
        $rooms = $this->assigned_rooms;
        if ($rooms->isNotEmpty()) {
            return (float) $rooms->sum('maintenance_charges');
        }
        return (float) $value;
    }

    public function getOtherChargesAttribute($value)
    {
        $rooms = $this->assigned_rooms;
        if ($rooms->isNotEmpty()) {
            return (float) $rooms->sum('other_charges');
        }
        return (float) $value;
    }

    public function getEbUnitRateAttribute($value)
    {
        $rooms = $this->assigned_rooms;
        if ($rooms->isNotEmpty() && (float) $rooms->first()->eb_unit_rate > 0) {
            return (float) $rooms->first()->eb_unit_rate;
        }
        return (float) ($value ?? 10.00);
    }

    public function getTotalMonthlyDueAttribute(): float
    {
        return (float) $this->monthly_rent + (float) $this->maintenance_charges + (float) $this->other_charges;
    }

    public function getLastEbReadingAttribute(): float
    {
        if ($this->relationLoaded('bills')) {
            $lastBill = $this->bills->whereNotNull('eb_current_reading')->sortByDesc('bill_date')->sortByDesc('id')->first();
        } else {
            $lastBill = $this->bills()
                ->whereNotNull('eb_current_reading')
                ->orderBy('bill_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        }

        if ($lastBill && $lastBill->eb_current_reading !== null) {
            return (float) $lastBill->eb_current_reading;
        }

        return (float) ($this->eb_start_reading ?? 0.00);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'tenant_rooms', 'tenant_id', 'room_id')->withTimestamps();
    }

    public function getAssignedRoomsAttribute()
    {
        if ($this->relationLoaded('rooms') && $this->rooms->isNotEmpty()) {
            return $this->rooms;
        }
        $rooms = $this->rooms()->get();
        if ($rooms->isNotEmpty()) {
            return $rooms;
        }
        if ($this->room) {
            return collect([$this->room]);
        }
        return collect();
    }

    public function getRoomNumbersDisplayAttribute(): string
    {
        $rooms = $this->assigned_rooms;
        if ($rooms->isNotEmpty()) {
            return $rooms->sortBy(fn($r) => (int) $r->room_number)->pluck('room_number')->implode(', ');
        }
        return $this->room_number ?? 'Unassigned';
    }

    public function getRoomTypesDisplayAttribute(): string
    {
        $rooms = $this->assigned_rooms;
        if ($rooms->isNotEmpty()) {
            return $rooms->pluck('room_type')->unique()->filter()->implode(', ');
        }
        return $this->room_type ?? 'Standard';
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

