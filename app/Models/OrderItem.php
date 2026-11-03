<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable = [
        'order_id',
        'user_id',
        'shop_id',
        'product_id',
        'product_name',
        'product_image',
        'amount',
        'quantity_unit',
        'quantity',
        'description',
        'total_amount',

        'status',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'refund_status',
        'refund_amount',
        'refund_id',
        'delivered_at',
        'refunded_at',
        'return_reason',
        'return_image',
        'return_requested_at',
        'returned_at',

        'return_verified_by_admin',
        'admin_verified_at',
        'admin_verification_notes',
        'shop_verified',
        'shop_verified_at',
        'shop_verification_notes',
        'product_condition',
        'verified_by',
        'verified_by_shop',

        'return_approval',
        'return_approval_at',
        'return_approval_notes',

        'replace_reason',
        'replace_image',
        'replace_requested_at',

        'replace_verified_by_admin',
        'replace_admin_verified_at',
        'replace_admin_verification_notes',
        'replace_approval',
        'replace_approval_at',
        'replace_approval_notes',
        'new_product_id',
        'replacement_order_id',
        'original_order_item_id',

        'tracking_number',
        'courier_name',
        'shipment_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'return_requested_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
        'admin_verified_at' => 'datetime',
        'shop_verified_at' => 'datetime',
        'return_approval_at' => 'datetime',
        'replace_requested_at' => 'datetime',
        'replace_admin_verified_at' => 'datetime',
        'replace_approval_at' => 'datetime',
        'return_verified_by_admin' => 'boolean',
        'shop_verified' => 'boolean',
        'return_approval' => 'boolean',
        'replace_verified_by_admin' => 'boolean',
        'replace_approval' => 'boolean',
        'status' => OrderItemStatus::class
    ];

    protected $appends = [
        'image_url',
        'return_image_url',
        'status_description',
        'status_color',
        'can_cancel',
        'can_return',
        'can_replace',
        'is_active',
        'verification_status',
        'replace_image_url'
    ];

    public function getReturnImageUrlAttribute()
    {
        return $this->return_image ? url('storage/' . $this->return_image) : null;
    }

    public function getImageUrlAttribute()
    {
        if ($this->product_image == 'avatar.jpg') {
            return url('img/avatar.jpg');
        }
        return url('/storage/product/' . $this->product_image);
    }

    public function getStatusDescriptionAttribute(): string
    {
        $statusValue = $this->status instanceof OrderItemStatus
            ? $this->status->value
            : $this->status;

        return OrderItemStatus::getDescription($statusValue);
    }

    public function canTransitionTo($newStatus): bool
    {
        $transitions = [
            'pending' => ['confirmed', 'processing', 'cancelled'],
            'confirmed' => ['processing', 'pending', 'cancelled'],
            'processing' => ['shipped', 'confirmed', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => ['return_requested', 'replace_requested'],
            'return_requested' => ['returned', 'delivered'],
            'replace_requested' => ['pending', 'cancelled'],
            'returned' => [],
            'cancelled' => [],
        ];

        $currentStatus = $this->status instanceof \App\Enums\OrderItemStatus
            ? $this->status->value
            : $this->status;

        return in_array($newStatus, $transitions[$currentStatus] ?? []);
    }

    public function getStatusColorAttribute(): string
    {
        return OrderItemStatus::getColor($this->status->value);
    }

    public function getCanCancelAttribute(): bool
    {
        return OrderItemStatus::canCancel($this->status->value);
    }

    public function getCanReturnAttribute(): bool
    {
        return OrderItemStatus::canReturn($this->status->value);
    }

    public function getCanReplaceAttribute(): bool
    {
        return OrderItemStatus::canReplace($this->status->value);
    }

    public function getIsActiveAttribute(): bool
    {
        return OrderItemStatus::isActive($this->status->value);
    }

    public function getCanShipAttribute(): bool
    {
        return OrderItemStatus::canShip($this->status->value);
    }

    public function getVerificationStatusAttribute(): array
    {
        return [
            'admin_verified' => $this->return_verified_by_admin,
            'admin_verified_at' => $this->admin_verified_at?->format('Y-m-d H:i:s'),
            'admin_notes' => $this->admin_verification_notes,
            'shop_verified' => $this->shop_verified,
            'shop_verified_at' => $this->shop_verified_at?->format('Y-m-d H:i:s'),
            'shop_notes' => $this->shop_verification_notes,
            'product_condition' => $this->product_condition,
            'return_approval' => $this->return_approval,
            'return_approval_at' => $this->return_approval_at?->format('Y-m-d H:i:s'),
            'return_approval_notes' => $this->return_approval_notes,
            'is_approved_for_refund' => $this->isApprovedForRefund(),

            'replace_admin_verified' => $this->replace_verified_by_admin,
            'replace_admin_verified_at' => $this->replace_admin_verified_at?->format('Y-m-d H:i:s'),
            'replace_admin_notes' => $this->replace_admin_verification_notes,
            'replace_approval' => $this->replace_approval,
            'replace_approval_at' => $this->replace_approval_at?->format('Y-m-d H:i:s'),
            'replace_approval_notes' => $this->replace_approval_notes,
        ];
    }

    public function replacementOrder()
    {
        return $this->hasOne(Order::class, 'original_order_item_id')
            ->where('is_replacement_order', true);
    }
    public function getReplaceImageUrlAttribute()
    {
        return $this->replace_image ? url('storage/' . $this->replace_image) : null;
    }

    public function getNextStatusesAttribute(): array
    {
        return OrderItemStatus::getNextStatus($this->status->value);
    }

    public function getIsRefundableAttribute(): bool
    {
        return in_array($this->status->value, OrderItemStatus::getRefundableStatuses());
    }

    public function getFormattedRefundAmountAttribute(): ?string
    {
        return $this->refund_amount ? '₹' . number_format($this->refund_amount, 2) : null;
    }

    public function isReturnRequestPending(): bool
    {
        return $this->status === OrderItemStatus::RETURN_REQUESTED->value
            && is_null($this->return_verified_by_admin);
    }

    public function isReturnAdminApproved(): bool
    {
        return $this->status === OrderItemStatus::RETURN_REQUESTED->value
            && $this->return_verified_by_admin === true;
    }

    public function isReturnAdminRejected(): bool
    {
        return $this->status === OrderItemStatus::RETURN_REQUESTED->value
            && $this->return_verified_by_admin === false;
    }

    public function isWaitingForShopVerification(): bool
    {
        return $this->status === OrderItemStatus::RETURN_REQUESTED->value
            && $this->return_verified_by_admin === true
            && is_null($this->shop_verified);
    }

    public function isShopVerified(): bool
    {
        return $this->shop_verified === true;
    }

    public function isShopRejected(): bool
    {
        return $this->shop_verified === false;
    }

    public function isReturnApproved(): bool
    {
        return $this->return_approval === true;
    }

    public function isReturnApprovalPending(): bool
    {
        return $this->return_verified_by_admin === true
            && $this->shop_verified === true
            && is_null($this->return_approval);
    }

    public function isApprovedForRefund(): bool
    {
        return $this->return_verified_by_admin === true
            && $this->shop_verified === true
            && $this->return_approval === true
            && $this->status === OrderItemStatus::RETURNED->value;
    }

    public function isReplaceAdminApproved(): bool
    {
        return $this->status === OrderItemStatus::REPLACE_REQUESTED->value
            && $this->replace_verified_by_admin === true;
    }

    public function isReplaceApprovalPending(): bool
    {
        return $this->replace_verified_by_admin === true
            && is_null($this->replace_approval);
    }

    public function isReplaceApproved(): bool
    {
        return $this->replace_approval === true;
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function newProduct()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function payoutShops()
    {
        return $this->hasMany(PayoutShop::class, 'shop_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderItemStatusHistory::class);
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function adminVerifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function shopVerifier()
    {
        return $this->belongsTo(User::class, 'verified_by_shop');
    }

    public function returnApprover()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function replaceApprover()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderItemStatusHistory::class);
    }

    public function latestStatus()
    {
        return $this->hasOne(OrderItemStatusHistory::class)->latestOfMany();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            OrderItemStatus::PENDING->value,
            OrderItemStatus::CONFIRMED->value,
            OrderItemStatus::PROCESSING->value,
            OrderItemStatus::SHIPPED->value
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', OrderItemStatus::DELIVERED->value);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', OrderItemStatus::CANCELLED->value);
    }

    public function scopePendingRefund($query)
    {
        return $query->where('status', OrderItemStatus::CANCELLED->value)
                     ->where('refund_status', 'pending');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', OrderItemStatus::REFUNDED->value)
                     ->orWhere('refund_status', 'processed');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', OrderItemStatus::RETURNED->value);
    }

    public function scopeReturnRequested($query)
    {
        return $query->where('status', OrderItemStatus::RETURN_REQUESTED->value);
    }

    public function scopePendingAdminVerification($query)
    {
        return $query->where('status', OrderItemStatus::RETURN_REQUESTED->value)
                     ->whereNull('return_verified_by_admin');
    }

    public function scopeAdminApprovedReturns($query)
    {
        return $query->where('status', OrderItemStatus::RETURN_REQUESTED->value)
                     ->where('return_verified_by_admin', true);
    }

    public function scopePendingShopVerification($query)
    {
        return $query->where('status', OrderItemStatus::RETURN_REQUESTED->value)
                     ->where('return_verified_by_admin', true)
                     ->whereNull('shop_verified');
    }

    public function scopePendingReturnApproval($query)
    {
        return $query->where('status', OrderItemStatus::RETURN_REQUESTED->value)
                     ->where('return_verified_by_admin', true)
                     ->where('shop_verified', true)
                     ->whereNull('return_approval');
    }

    public function scopeReturnApproved($query)
    {
        return $query->where('status', OrderItemStatus::RETURNED->value)
                     ->where('return_approval', true);
    }

    public function scopeShopApprovedReturns($query)
    {
        return $query->where('status', OrderItemStatus::RETURNED->value)
                     ->where('shop_verified', true);
    }

    public function scopeShopRejectedReturns($query)
    {
        return $query->where('status', OrderItemStatus::RETURN_REQUESTED->value)
                     ->where('return_verified_by_admin', true)
                     ->where('shop_verified', false);
    }

    public function updateStatus(
        string $status,
        ?string $notes = null,
        ?array $metadata = null,
        ?int $createdBy = null
    ): self {
        return OrderItemStatusHistory::updateStatus($this, $status, $notes, $metadata, $createdBy);
    }

    public function cancel(
        string $reason,
        ?float $refundAmount = null,
        ?int $cancelledBy = null
    ): self {
        $metadata = [
            'cancellation_reason' => $reason,
            'refund_amount' => $refundAmount,
            'cancelled_by' => $cancelledBy ?? auth()->id()
        ];

        return $this->updateStatus(
            OrderItemStatus::CANCELLED->value,
            $reason,
            $metadata,
            $cancelledBy
        );
    }

    public function processRefund(
        float $amount,
        string $refundId,
        ?string $notes = null,
        ?int $processedBy = null
    ): self {
        $metadata = [
            'refund_amount' => $amount,
            'refund_id' => $refundId,
            'refund_processed_by' => $processedBy ?? auth()->id(),
            'refund_processed_at' => now()->toDateTimeString()
        ];

        return $this->updateStatus(
            OrderItemStatus::REFUNDED->value,
            $notes ?? "Refund processed for ₹{$amount}",
            $metadata,
            $processedBy
        );
    }

    public function requestReturn(string $reason, ?string $image = null, ?int $requestedBy = null): self
    {
        $this->update([
            'return_reason' => $reason,
            'return_image' => $image,
            'return_requested_at' => now(),
        ]);

        $metadata = [
            'return_reason' => $reason,
            'return_image' => $image,
            'return_requested_by' => $requestedBy ?? auth()->id()
        ];

        return $this->updateStatus(
            OrderItemStatus::RETURN_REQUESTED->value,
            $reason,
            $metadata,
            $requestedBy
        );
    }

    public function adminApproveReturn(?string $notes = null, ?int $approvedBy = null): self
    {
        $this->update([
            'return_verified_by_admin' => true,
            'admin_verified_at' => now(),
            'admin_verification_notes' => $notes,
            'verified_by' => $approvedBy ?? auth()->id(),
        ]);

        $metadata = [
            'admin_verified' => true,
            'admin_verification_notes' => $notes,
            'verified_by' => $approvedBy ?? auth()->id(),
        ];

        return $this->updateStatus(
            OrderItemStatus::RETURN_REQUESTED->value,
            'Admin approved return' . ($notes ? ': ' . $notes : ''),
            $metadata,
            $approvedBy
        );
    }

    public function adminRejectReturn(string $reason, ?int $rejectedBy = null): self
    {
        $this->update([
            'return_verified_by_admin' => false,
            'admin_verified_at' => now(),
            'admin_verification_notes' => $reason,
            'verified_by' => $rejectedBy ?? auth()->id(),
        ]);

        $metadata = [
            'admin_verified' => false,
            'rejection_reason' => $reason,
            'verified_by' => $rejectedBy ?? auth()->id(),
        ];

        return $this->updateStatus(
            OrderItemStatus::RETURN_REQUESTED->value,
            'Admin rejected return: ' . $reason,
            $metadata,
            $rejectedBy
        );
    }

    public function shopVerifyReturn(
        bool $isApproved,
        ?string $notes = null,
        ?string $productCondition = null,
        ?int $verifiedBy = null
    ): self {
        $this->update([
            'shop_verified' => $isApproved,
            'shop_verified_at' => now(),
            'shop_verification_notes' => $notes,
            'product_condition' => $productCondition,
            'verified_by_shop' => $verifiedBy ?? auth()->id(),
        ]);

        $metadata = [
            'shop_verified' => $isApproved,
            'shop_verification_notes' => $notes,
            'product_condition' => $productCondition,
            'verified_by_shop' => $verifiedBy ?? auth()->id(),
        ];

        if ($isApproved) {
            return $this->updateStatus(
                OrderItemStatus::RETURN_REQUESTED->value,
                'Shop verified return' . ($notes ? ': ' . $notes : ''),
                $metadata,
                $verifiedBy
            );
        } else {
            return $this->updateStatus(
                OrderItemStatus::RETURN_REQUESTED->value,
                'Shop rejected return: ' . ($notes ?: 'Product condition not acceptable'),
                $metadata,
                $verifiedBy
            );
        }
    }

    public function approveReturn(?string $notes = null, ?int $approvedBy = null): self
    {
        $this->update([
            'return_approval' => true,
            'return_approval_at' => now(),
            'return_approval_notes' => $notes,
            'verified_by' => $approvedBy ?? auth()->id(),
            'status' => OrderItemStatus::RETURNED->value,
            'returned_at' => now(),
        ]);

        $metadata = [
            'return_approval' => true,
            'return_approval_notes' => $notes,
            'verified_by' => $approvedBy ?? auth()->id(),
        ];

        return $this->updateStatus(
            OrderItemStatus::RETURNED->value,
            'Return approved for refund' . ($notes ? ': ' . $notes : ''),
            $metadata,
            $approvedBy
        );
    }

    public function rejectReturn(string $reason, ?int $rejectedBy = null): self
    {
        $this->update([
            'return_approval' => false,
            'return_approval_at' => now(),
            'return_approval_notes' => $reason,
            'verified_by' => $rejectedBy ?? auth()->id(),
        ]);

        $metadata = [
            'return_approval' => false,
            'rejection_reason' => $reason,
            'verified_by' => $rejectedBy ?? auth()->id(),
        ];

        return $this->updateStatus(
            OrderItemStatus::RETURN_REQUESTED->value,
            'Return rejected: ' . $reason,
            $metadata,
            $rejectedBy
        );
    }

    public function getTimeline(): array
    {
        return OrderItemStatusHistory::getTimeline($this);
    }

    public function getCurrentStatusHistory(): ?OrderItemStatusHistory
    {
        return OrderItemStatusHistory::getCurrentStatus($this);
    }
}
