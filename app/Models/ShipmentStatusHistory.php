<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'status',
        'notes',
        'metadata',
        'previous_status',
        'created_by'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'status_description',
        'status_color'
    ];

    /**
     * Get status description
     */
    public function getStatusDescriptionAttribute(): string
    {
        return DeliveryStatus::getDescription($this->status);
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return DeliveryStatus::getColor($this->status);
    }

    /**
     * Relationships
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get formatted metadata
     */
    public function getFormattedMetadataAttribute(): array
    {
        if (!$this->metadata) {
            return [];
        }

        $formatted = [];
        foreach ($this->metadata as $key => $value) {
            $formatted[ucwords(str_replace('_', ' ', $key))] = $value;
        }

        return $formatted;
    }
}
