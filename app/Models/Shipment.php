<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'courier_id',
        'tracking_number',
        'status',
        'shipped_at',
        'estimated_delivery_at',
        'actual_delivery_at',
        'delivery_attempts',
        'notes',
        'delivery_proof_url',
        'recipient_name',
        'recipient_phone',
        'delivery_address',
        'last_status_check_at',
        'auto_update_attempts'
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'estimated_delivery_at' => 'datetime',
        'actual_delivery_at' => 'datetime',
        'last_status_check_at' => 'datetime',
        'delivery_attempts' => 'integer',
        'auto_update_attempts' => 'integer',
        'status' => DeliveryStatus::class
    ];

    protected $appends = [
        'status_description',
        'status_color',
        'tracking_url',
        'is_delivered',
        'is_overdue'
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shipment) {
            $shipment->status = $shipment->status ?? DeliveryStatus::PICKED_UP->value;
            $shipment->shipped_at = $shipment->shipped_at ?? now();
            $shipment->delivery_attempts = $shipment->delivery_attempts ?? 0;
            $shipment->auto_update_attempts = $shipment->auto_update_attempts ?? 0;
        });

        static::created(function ($shipment) {
            if ($shipment->courier->shouldAutoUpdateDelivery()) {
                $shipment->scheduleAutoUpdate();
            }
        });
    }

    /**
     * Get status description
     */
    public function getStatusDescriptionAttribute(): string
    {
        return DeliveryStatus::getDescription($this->status->value);
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return DeliveryStatus::getColor($this->status->value);
    }

    /**
     * Get tracking URL
     */
    public function getTrackingUrlAttribute(): ?string
    {
        if (!$this->tracking_number || !$this->courier) {
            return null;
        }

        return $this->courier->getTrackingUrl($this->tracking_number);
    }

    /**
     * Check if delivered
     */
    public function getIsDeliveredAttribute(): bool
    {
        return $this->status === DeliveryStatus::DELIVERED;
    }

    /**
     * Check if overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->estimated_delivery_at || $this->is_delivered) {
            return false;
        }

        return now()->greaterThan($this->estimated_delivery_at);
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->is_overdue) {
            return null;
        }

        return now()->diffInDays($this->estimated_delivery_at);
    }

    /**
     * Relationships
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function deliveryProof()
    {
        return $this->morphOne(Media::class, 'model')->where('collection_name', 'delivery_proof');
    }

    public function statusHistory()
    {
        return $this->hasMany(ShipmentStatusHistory::class);
    }

    /**
     * Scopes
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', DeliveryStatus::DELIVERED->value);
    }

    public function scopePending($query)
    {
        return $query->whereNotIn('status', [
            DeliveryStatus::DELIVERED->value,
            DeliveryStatus::CANCELLED->value,
            DeliveryStatus::RETURNED->value
        ]);
    }

    public function scopeOverdue($query)
    {
        return $query->pending()
            ->whereDate('estimated_delivery_at', '<', now())
            ->whereNotNull('estimated_delivery_at');
    }

    public function scopeNeedsAutoUpdate($query)
    {
        return $query->whereHas('courier', function ($q) {
            $q->where('auto_update_delivery', true)
              ->where('is_active', true);
        })
        ->whereNotIn('status', [
            DeliveryStatus::DELIVERED->value,
            DeliveryStatus::CANCELLED->value,
            DeliveryStatus::RETURNED->value
        ])
        ->where(function ($q) {
            $q->whereNull('last_status_check_at')
              ->orWhereRaw('last_status_check_at <= DATE_SUB(NOW(), INTERVAL couriers.auto_update_delay_hours HOUR)');
        });
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(?string $proofUrl = null, ?string $notes = null): self
    {
        $this->update([
            'status' => DeliveryStatus::DELIVERED->value,
            'actual_delivery_at' => now(),
            'delivery_proof_url' => $proofUrl,
            'notes' => $notes,
            'last_status_check_at' => now()
        ]);

        // Also update the order item status
        if ($this->orderItem) {
            $this->orderItem->updateStatus(
                \App\Enums\OrderItemStatus::DELIVERED->value,
                'Automatically marked as delivered via shipment tracking'
            );
        }

        // Create status history
        ShipmentStatusHistory::create([
            'shipment_id' => $this->id,
            'status' => DeliveryStatus::DELIVERED->value,
            'notes' => $notes ?? 'Automatically marked as delivered',
            'metadata' => ['proof_url' => $proofUrl]
        ]);

        return $this->fresh();
    }

    /**
     * Update status based on elapsed time (simulated tracking)
     */
    public function simulateStatusUpdate(): bool
    {
        $shippedDaysAgo = $this->shipped_at->diffInDays(now());
        $estimatedDays = $this->courier->estimated_delivery_days;

        $newStatus = match (true) {
            // If delivered within estimated time or 1 day after
            $shippedDaysAgo >= $estimatedDays && $shippedDaysAgo <= ($estimatedDays + 1) => DeliveryStatus::DELIVERED,

            // If overdue but not too long
            $shippedDaysAgo > ($estimatedDays + 1) && $shippedDaysAgo <= ($estimatedDays + 3) => DeliveryStatus::OUT_FOR_DELIVERY,

            // If significantly overdue
            $shippedDaysAgo > ($estimatedDays + 3) && $shippedDaysAgo <= ($estimatedDays + 7) => DeliveryStatus::DELAYED,

            // If very overdue
            $shippedDaysAgo > ($estimatedDays + 7) && $shippedDaysAgo <= ($estimatedDays + 14) => DeliveryStatus::FAILED_ATTEMPT,

            // If extremely overdue
            $shippedDaysAgo > ($estimatedDays + 14) => DeliveryStatus::RETURNED,

            // Normal progression
            default => $this->getNextSimulatedStatus($shippedDaysAgo, $estimatedDays)
        };

        if ($newStatus && $newStatus->value !== $this->status->value) {
            $this->updateStatus($newStatus->value, 'Auto-updated based on shipment timeline');
            return true;
        }

        $this->update(['last_status_check_at' => now()]);
        return false;
    }

    /**
     * Get next simulated status based on timeline
     */
    private function getNextSimulatedStatus(int $daysSinceShipping, int $estimatedDays): ?DeliveryStatus
    {
        if ($daysSinceShipping <= 0) {
            return DeliveryStatus::PICKED_UP;
        } elseif ($daysSinceShipping <= 1) {
            return DeliveryStatus::IN_TRANSIT;
        } elseif ($daysSinceShipping <= ceil($estimatedDays * 0.7)) {
            return DeliveryStatus::IN_TRANSIT;
        } elseif ($daysSinceShipping <= ceil($estimatedDays * 0.9)) {
            return DeliveryStatus::OUT_FOR_DELIVERY;
        }

        return null;
    }

    /**
     * Update shipment status
     */
    public function updateStatus(string $status, ?string $notes = null, ?array $metadata = null): self
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => $status,
            'notes' => $notes,
            'last_status_check_at' => now(),
            'auto_update_attempts' => $this->auto_update_attempts + 1
        ]);

        // Update timestamps based on status
        if ($status === DeliveryStatus::DELIVERED->value && !$this->actual_delivery_at) {
            $this->update(['actual_delivery_at' => now()]);
        }

        // Create status history
        ShipmentStatusHistory::create([
            'shipment_id' => $this->id,
            'status' => $status,
            'notes' => $notes,
            'metadata' => $metadata,
            'previous_status' => $oldStatus->value
        ]);

        // Update order item status if delivered
        if ($status === DeliveryStatus::DELIVERED->value && $this->orderItem) {
            $this->orderItem->updateStatus(
                \App\Enums\OrderItemStatus::DELIVERED->value,
                $notes ?? 'Marked as delivered via shipment'
            );
        }

        return $this->fresh();
    }

    /**
     * Schedule automatic status update
     */
    public function scheduleAutoUpdate(): void
    {
        if (!$this->courier->shouldAutoUpdateDelivery()) {
            return;
        }

        $delayHours = $this->courier->auto_update_delay_hours ?? 24;

        // Schedule job for automatic update
        \App\Jobs\AutoUpdateShipmentStatus::dispatch($this->id)
            ->delay(now()->addHours($delayHours));
    }

    /**
     * Check if needs manual follow-up
     */
    public function needsManualFollowUp(): bool
    {
        if ($this->is_delivered || $this->status === DeliveryStatus::CANCELLED) {
            return false;
        }

        $overdueDays = $this->days_overdue;

        return $overdueDays !== null && $overdueDays >= 3;
    }
}
