<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'status',
        'notes',
        'metadata',
        'created_by',
        'effective_from'
    ];

    protected $casts = [
        'metadata' => 'array',
        'effective_from' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getStatusDescriptionAttribute(): string
    {
        return OrderItemStatus::getDescription($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return OrderItemStatus::getColor($this->status);
    }

    public function getFormattedMetadataAttribute(): ?array
    {
        if (!$this->metadata) {
            return null;
        }

        $formatted = [];
        foreach ($this->metadata as $key => $value) {
            $formatted[ucwords(str_replace('_', ' ', $key))] = $value;
        }

        return $formatted;
    }

    public function getIsRefundableAttribute(): bool
    {
        return in_array($this->status, OrderItemStatus::getRefundableStatuses());
    }

    public function getRefundAmountAttribute(): ?float
    {
        return $this->metadata['refund_amount'] ?? null;
    }

    public function getRefundIdAttribute(): ?string
    {
        return $this->metadata['refund_id'] ?? null;
    }

    public function getTrackingNumberAttribute(): ?string
    {
        return $this->metadata['tracking_number'] ?? null;
    }

    public function getCourierNameAttribute(): ?string
    {
        return $this->metadata['courier_name'] ?? null;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForOrderItem($query, $orderItemId)
    {
        return $query->where('order_item_id', $orderItemId);
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', OrderItemStatus::CANCELLED->value);
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', OrderItemStatus::REFUNDED->value);
    }

    public function scopeReturned($query)
    {
        return $query->where('status', OrderItemStatus::RETURNED->value);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderBy('effective_from', 'desc')->orderBy('created_at', 'desc');
    }

    public function scopeChronological($query)
    {
        return $query->orderBy('effective_from', 'asc')->orderBy('created_at', 'asc');
    }

    public static function createStatusHistory(
        OrderItem $orderItem,
        string $status,
        ?string $notes = null,
        ?array $metadata = null,
        ?int $createdBy = null
    ): self {
        return self::create([
            'order_id' => $orderItem->order_id,
            'order_item_id' => $orderItem->id,
            'status' => $status,
            'notes' => $notes,
            'metadata' => $metadata,
            'created_by' => $createdBy ?? auth()->id(),
            'effective_from' => now()
        ]);
    }

    public static function updateStatus(
        OrderItem $orderItem,
        string $status,
        ?string $notes = null,
        ?array $metadata = null,
        ?int $createdBy = null
    ): OrderItem {
        $orderItem->update([
            'status' => $status,
            'updated_at' => now()
        ]);

        if ($status === OrderItemStatus::CANCELLED->value) {
            $orderItem->update([
                'cancelled_at' => now(),
                'cancelled_by' => $createdBy ?? auth()->id()
            ]);
        } elseif ($status === OrderItemStatus::REFUNDED->value) {
            $orderItem->update([
                'refunded_at' => now(),
                'refund_amount' => $metadata['refund_amount'] ?? null,
                'refund_id' => $metadata['refund_id'] ?? null
            ]);
        }

        self::createStatusHistory($orderItem, $status, $notes, $metadata, $createdBy);

        return $orderItem->fresh();
    }

    public static function isValidTransition(string $fromStatus, string $toStatus): bool
    {
        $allowedTransitions = OrderItemStatus::getNextStatus($fromStatus);
        return in_array($toStatus, $allowedTransitions);
    }

    public static function getTimeline(OrderItem $orderItem): array
    {
        return self::where('order_item_id', $orderItem->id)
            ->chronological()
            ->get()
            ->map(function ($history) {
                return [
                    'id' => $history->id,
                    'status' => $history->status,
                    'status_description' => $history->status_description,
                    'status_color' => $history->status_color,
                    'notes' => $history->notes,
                    'metadata' => $history->formatted_metadata,
                    'created_by' => $history->creator->name ?? 'System',
                    'created_at' => $history->created_at->format('d M Y h:i A'),
                    'effective_from' => $history->effective_from->format('d M Y h:i A'),
                    'is_refundable' => $history->is_refundable,
                    'refund_amount' => $history->refund_amount,
                    'refund_id' => $history->refund_id,
                    'tracking_number' => $history->tracking_number,
                    'courier_name' => $history->courier_name,
                ];
            })
            ->toArray();
    }

    public static function getCurrentStatus(OrderItem $orderItem): ?self
    {
        return self::where('order_item_id', $orderItem->id)
            ->recentFirst()
            ->first();
    }

    public static function getStatusChangesBetweenDates(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $status = null
    ) {
        $query = self::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->with(['orderItem', 'creator'])
            ->recentFirst()
            ->get();
    }

    public static function getStatusStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $query = self::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $statistics = $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $allStatuses = OrderItemStatus::getAll();
        $result = [];
        foreach ($allStatuses as $status) {
            $result[$status] = $statistics[$status] ?? 0;
        }

        return $result;
    }
}
