<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding, LogsActivity;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'shipping_address_id',
        'delivery_man_id',
        'precheckout_id',
        'order_no',
        'total_amount',
        'status',
        'date',
        'invoice_no',
        'order_type',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'completed_at',
        'completion_code',

        'has_shipments',
        'all_items_delivered',
        'delivery_completed_at',
        'shipments_count',
        'delivered_shipments_count',

        'original_order_id',
        'original_order_item_id',
        'replacement_notes',
        'is_replacement_order',
    ];

    protected $casts = [
        'date' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'delivery_completed_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'is_replacement_order' => 'boolean',
    ];

    protected $appends = [
        'is_replacement',
        'original_order_info',
    ];

    protected static $logAttributes = ['status'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'order';

    public function getIsReplacementAttribute()
    {
        return $this->is_replacement_order || $this->original_order_id !== null;
    }

    public function getOriginalOrderInfoAttribute()
    {
        if (!$this->original_order_id) {
            return null;
        }

        return [
            'id' => $this->original_order_id,
            'order_no' => optional($this->originalOrder)->order_no,
        ];
    }

    public function scopeSearch($query, $search)
    {
        if (isset($search)) {
            $query->orWhere('order_no', 'LIKE', '%' . $search . '%')
                ->orWhere('status', 'LIKE', '%' . $search . '%')
                ->orWhere('total_amount', 'LIKE', '%' . $search . '%')
                ->orWhere('invoice_no', 'LIKE', '%' . $search . '%');
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryMan()
    {
        return $this->belongsTo(User::class, 'delivery_man_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public function precheckout()
    {
        return $this->belongsTo(Precheckout::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function missingBottles()
    {
        return $this->hasMany(MissingBottle::class, 'order_id', 'id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function shopOrders()
    {
        return $this->hasMany(ShopOrder::class);
    }

    public function replacementFor()
    {
        return $this->belongsTo(OrderItem::class, 'original_order_item_id');
    }

    public function originalOrder()
    {
        return $this->belongsTo(Order::class, 'original_order_id');
    }

    public function replacementOrders()
    {
        return $this->hasMany(Order::class, 'original_order_id')
            ->where('is_replacement_order', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', OrderStatus::COMPLETED);
    }

    public function scopeReplacementOrders($query)
    {
        return $query->where('is_replacement_order', true);
    }

    public function scopeNonReplacement($query)
    {
        return $query->where('is_replacement_order', false)
            ->whereNull('original_order_id');
    }

    public function scopeWithOriginalOrder($query, $orderId)
    {
        return $query->where('original_order_id', $orderId);
    }

    public function markAsCompleted()
    {
        $this->update(['status' => OrderStatus::COMPLETED]);
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Order has been {$eventName}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_amount', 'is_replacement_order'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Order was {$eventName}")
            ->useLogName('order');
    }

    public function generateCompletionCode()
    {
        return Str::random(10);
    }

    public function isValidCompletionCode($code)
    {
        return $this->completion_code == $code;
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function createReplacementOrder($originalOrder, $orderItem, $newProduct)
    {
        try {
            $latest = Order::where('is_replacement_order')->latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $orderNo = 'ALKARO' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

            $orderData = [
                'user_id' => $originalOrder->user_id,
                'order_no' => $orderNo,
                'total_amount' => 0,
                'invoice_no' => 'INV' . strtoupper(Str::random(5)) . time(),
                'status' => OrderStatus::PENDING,
                'original_order_id' => $originalOrder->id,
                'original_order_item_id' => $orderItem->id,
                'is_replacement_order' => true,
                'replacement_notes' => 'Free replacement for order #' . $originalOrder->order_no,
                'date' => now(),
            ];

            if ($originalOrder->shipping_address_id) {
                $orderData['shipping_address_id'] = $originalOrder->shipping_address_id;
            }

            $replacementOrder = Order::create($orderData);

            OrderStatusHistory::create([
                'order_id' => $replacementOrder->id,
                'status' => OrderStatus::PENDING,
                'notes' => 'Replacement order created for order #' . $originalOrder->order_no,
                'created_by' => auth()->id() ?? 1,
                'metadata' => [
                    'original_order_id' => $originalOrder->id,
                    'original_order_no' => $originalOrder->order_no,
                    'original_item_id' => $orderItem->id,
                    'replacement_type' => 'replace',
                    'created_via' => 'system',
                ]
            ]);

            $orderItemData = [
                'order_id' => $replacementOrder->id,
                'user_id' => $originalOrder->user_id,
                'shop_id' => $orderItem->shop_id,
                'product_id' => $newProduct->id,
                'product_name' => $newProduct->name,
                'product_image' => $newProduct->image,
                'amount' => 0,
                'quantity' => $orderItem->quantity,
                'quantity_unit' => $orderItem->quantity_unit,
                'total_amount' => 0,
                'status' => OrderItemStatus::PENDING->value,
                'original_order_item_id' => $orderItem->id,
                'replacement_order_id' => $orderItem->order_id,
                'new_product_id' => $orderItem->product_id,
            ];

            $orderItemData['new_product_id'] = $newProduct->id;

            if ($orderItem->description) {
                $orderItemData['description'] = $orderItem->description;
            }

            $replacementOrderItem = OrderItem::create($orderItemData);

            OrderItemStatusHistory::create([
                'order_id' => $replacementOrder->id,
                'order_item_id' => $replacementOrderItem->id,
                'status' => OrderItemStatus::PENDING->value,
                'notes' => 'Replacement item created for original product: ' . $orderItem->product_name,
                'created_by' => auth()->id() ?? 1,
                'metadata' => [
                    'original_order_id' => $originalOrder->id,
                    'original_item_id' => $orderItem->id,
                    'original_product_id' => $orderItem->product_id,
                    'original_product_name' => $orderItem->product_name,
                    'new_product_id' => $newProduct->id,
                    'new_product_name' => $newProduct->name,
                    'replacement_reason' => $orderItem->replace_reason,
                    'created_via' => 'system',
                ]
            ]);

            if ($originalOrder->shippingAddress) {
            $originalShipping = $originalOrder->shippingAddress;

            $shippingAddressData = [
                'name' => $originalShipping->name,
                'phone' => $originalShipping->phone,
                'flat_no' => $originalShipping->flat_no ?? null,
                'area_name' => $originalShipping->area_name ?? null,
                'street' => $originalShipping->street ?? null,
                'city' => $originalShipping->city ?? null,
                'district' => $originalShipping->district ?? null,
                'pincode' => $originalShipping->pincode ?? null,
                'address_type' => $originalShipping->address_type ?? 'home',
                'is_default' => false,
                'lat' => $originalShipping->lat ?? null,
                'long' => $originalShipping->long ?? null,
            ];

            $shippingAddressData['user_id'] = $originalOrder->user_id;
            $shippingAddressData['order_id'] = $replacementOrder->id;

            $shippingAddress = ShippingAddress::create($shippingAddressData);

            $replacementOrder->update(['shipping_address_id' => $shippingAddress->id]);
        }

            return $replacementOrder;

        } catch (\Exception $e) {
            \Log::error('Create replacement order error: ' . $e->getMessage(), [
                'original_order_id' => $originalOrder->id ?? null,
                'order_item_id' => $orderItem->id ?? null,
                'new_product_id' => $newProduct->id ?? null,
            ]);
            throw $e;
        }
    }

    public function getReplacementDetails()
    {
        if (!$this->is_replacement_order) {
            return null;
        }

        return [
            'original_order' => $this->originalOrder ? [
                'id' => $this->originalOrder->id,
                'order_no' => $this->originalOrder->order_no,
                'date' => $this->originalOrder->date?->format('Y-m-d H:i:s'),
            ] : null,
            'replaced_item' => $this->replacementFor ? [
                'id' => $this->replacementFor->id,
                'product_name' => $this->replacementFor->product_name,
                'reason' => $this->replacementFor->replace_reason,
            ] : null,
            'replacement_notes' => $this->replacement_notes,
        ];
    }
}
