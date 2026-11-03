<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'tracking_url_template',
        'estimated_delivery_days',
        'is_active',
        'auto_update_delivery',
        'auto_update_delay_hours',
        'contact_number',
        'website',
        'logo_url'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_update_delivery' => 'boolean',
        'estimated_delivery_days' => 'integer',
        'auto_update_delay_hours' => 'integer'
    ];

    /**
     * Relationships
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function orderItems()
    {
        return $this->hasManyThrough(OrderItem::class, Shipment::class);
    }

    /**
     * Get tracking URL for a tracking number
     */
    public function getTrackingUrl(string $trackingNumber): string
    {
        return str_replace('{tracking_number}', $trackingNumber, $this->tracking_url_template);
    }

    /**
     * Get estimated delivery date based on shipping date
     */
    public function getEstimatedDeliveryDate(\Carbon\Carbon $shippingDate): \Carbon\Carbon
    {
        return $shippingDate->addDays($this->estimated_delivery_days);
    }

    /**
     * Check if automatic delivery updates are enabled
     */
    public function shouldAutoUpdateDelivery(): bool
    {
        return $this->is_active && $this->auto_update_delivery;
    }
}
