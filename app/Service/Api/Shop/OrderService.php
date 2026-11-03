<?php

namespace App\Service\Api\Shop;

use App\Enums\DeliveryStatus;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\UserType;
use App\Models\Application;
use App\Models\Courier;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusHistory;
use App\Models\OrderStatusHistory;
use App\Models\Shipment;
use App\Models\ShopOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderService
{
    public function orderDetails($id)
    {
        $orderId = $id;

        $order = Order::with([
            'user',
            'orderItems.product',
            'orderItems.latestStatus',
            'coupon',
            'payment',
            'shippingAddress',
            'precheckout',
        ])
            ->where('id', $orderId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 0,
                'message' => 'The requested order could not be found. Please verify the order ID and try again.'
            ]);
        }

        $isComplete = $order->status === OrderStatus::COMPLETED;

        $bill = $order->precheckout;

        $billDetails = $bill ? [
            'sub_total'         => $bill->sub_total ?? 0,
            'delivery_charge'   => $bill->delivery_charge ?? 0,
            'save_amount'   => $bill->discount_amount ?? 0,
            'coupon_amount'     => $bill->coupon_amount ?? 0,
            'platform_fee'      => $bill->platform_fee ?? 0,
            'grand_total'       => $bill->grand_total ?? 0,
        ] : [];

        $paymentMethod = optional($order->payment)->payment_method;
        $paymentType = optional($order->payment)->type;

        $shippingAddress = $order->shippingAddress;
        $formattedAddress = null;

        if ($shippingAddress) {
            $formattedAddress = [
                'name' => $shippingAddress->name ?? $order->user->name ?? null,
                'phone' => $shippingAddress->phone ?? null,
                'address_type' => $shippingAddress->address_type ?? null,
                'address' => $shippingAddress->address ?? null,
                'landmark' => $shippingAddress->landmark ?? null,
                'city' => $shippingAddress->city ?? null,
                'state' => $shippingAddress->state ?? null,
                'country' => $shippingAddress->country ?? null,
                'pincode' => $shippingAddress->pincode ?? null,
                'full_address' => $shippingAddress->full_address ?? null,
            ];
        }

        $currentDate = now();

        $feedback = Feedback::where('order_id', $order->id)
                ->get();

        $customer_reviews = $feedback->map(function ($review) {
            return [
                'customer_name' => optional($review->user)->name,
                'rating' => $review->rating,
                'review_text' => $review->review,
                'review_date' => $review->created_at->format('d M Y, h:i A'),
            ];
        })->toArray();

        $orderDetails = [
            'order_id'         => $order->id,
            'order_no'         => $order->order_no,
            'date'             => $order->created_at->format('d-m-Y h:i A'),
            'invoice_no'       => $order->invoice_no ?? null,
            'payment_method'   => $paymentMethod ?? null,
            'payment_type'     => $paymentType ? strtoupper($paymentType) : null,
            'total_amount'     => $order->total_amount,
            'user_address'     => $formattedAddress,
            'status'           => $order->status,
            'user_name'        => optional($order->user)->name,
            'payment_mode'     => $paymentMethod == 'Razorpay' ? 'Online' : 'COD',
            'coupon_code'      => optional($order->coupon)->code ?? null,
            'bill_details'     => $billDetails,
            'invoice_button_show' => $isComplete,
            'invoice_download_url' => url('invoice/' . $order->id),
            'delivery_date'    => $order->delivery_completed_at?->format('d M Y, h:i A') ?? null,
            'customer_review'    => $customer_reviews ?? [],
            'items' => $order->orderItems->map(function ($item) use ($currentDate, $order) {
                $deliveryDate = null;
                $deliveredStatus = $item->statusHistories()
                    ->where('status', OrderItemStatus::DELIVERED->value)
                    ->latest()
                    ->first();

                if ($deliveredStatus) {
                    $deliveryDate = $deliveredStatus->created_at;
                }

                $canReturnOrReplace = false;
                $returnDaysLeft = null;

                $canReturn = false;
                $canReplace = false;
                $returnDaysLeft = 0;
                $replaceDaysLeft = 0;

                $settings = Application::where('group', 'Return Replace Days')->pluck('value', 'name');

                if ($deliveryDate) {
                    $currentDate = now();

                    $returnDeadline = $deliveryDate->copy()->addDays((int) $settings['max_return_days']);
                    $canReturn = $currentDate->lte($returnDeadline);
                    $returnDaysLeft = $canReturn
                        ? max(0, $currentDate->diffInDays($returnDeadline, false))
                        : 0;

                    $replaceDeadline = $deliveryDate->copy()->addDays((int) $settings['max_replace_days']);
                    $canReplace = $currentDate->lte($replaceDeadline);
                    $replaceDaysLeft = $canReplace
                        ? max(0, $currentDate->diffInDays($replaceDeadline, false))
                        : 0;
                }

                $isReturned = $item->status === OrderItemStatus::RETURNED->value;
                $isReturnRequested = $item->status === OrderItemStatus::RETURN_REQUESTED->value;
                $isRefunded = $item->status === OrderItemStatus::REFUNDED->value;

                $feedback = Feedback::where('order_id', $order->id)
                    ->where('product_id', $item->product_id)
                    ->first();

                $verificationStatus = $item->verification_status;

                $showShopVerifyButton = false;
                $shopVerifyReason = null;

                if ($item->status === OrderItemStatus::RETURN_REQUESTED->value) {
                    if ($item->return_verified_by_admin === true && is_null($item->shop_verified)) {
                        $showShopVerifyButton = true;
                        $shopVerifyReason = 'Admin has approved the return. Please verify the returned product.';
                    } elseif ($item->shop_verified === true) {
                        $showShopVerifyButton = false;
                        $shopVerifyReason = 'Already verified by shop on ' . $item->shop_verified_at?->format('d M Y, h:i A');
                    } elseif ($item->shop_verified === false) {
                        $showShopVerifyButton = false;
                        $shopVerifyReason = 'Shop rejected on ' . $item->shop_verified_at?->format('d M Y, h:i A');
                    } elseif ($item->return_verified_by_admin === false || is_null($item->return_verified_by_admin)) {
                        $showShopVerifyButton = false;
                        $shopVerifyReason = 'Waiting for admin approval.';
                    }
                }

                $showReplaceShopVerifyButton = false;
                $replaceShopVerifyReason = null;

                if ($item->status === OrderItemStatus::REPLACE_REQUESTED->value) {
                    if ($item->replace_verified_by_admin === true && is_null($item->replace_approval)) {
                        $showReplaceShopVerifyButton = true;
                        $replaceShopVerifyReason = 'Admin has approved the replacement request. Please verify.';
                    }
                }

                return [
                    'item_id'        => $item->id,
                    'product_id'        => $item->product_id,
                    'product_name'      => $item->product_name,
                    'quantity'          => $item->quantity,
                    'unit'              => $item->quantity_unit,
                    'amount'            => $item->amount,
                    'total_amount'      => $item->total_amount,
                    'image_url'         => $item->image_url,
                    'rating'         => $feedback ? number_format($feedback->rating, 1) : null,
                    'review'         => $feedback ? $feedback->review : null,
                    'status'            => $item->status,
                    'status_description' => $item->status_description,
                    'delivery_date'     => $deliveryDate ? $deliveryDate->format('d M Y, h:i A') : null,
                    'delivery_timestamp' => $deliveryDate ? $deliveryDate->timestamp : null,
                    'returned'          => $isReturned,
                    'refunded'          => $isRefunded,
                    'return_reason'     => $item->return_reason ?? null,
                    'return_requested_at' => $item->return_requested_at?->format('d M Y, h:i A'),
                    'returned_at'       => $item->returned_at?->format('d M Y, h:i A'),
                    'return_image_url'  => $item->return_image_url,

                    'replace_reason'     => $item->replace_reason ?? null,
                    'replace_requested_at' => $item->replace_requested_at?->format('d M Y, h:i A'),
                    'replaced_at'       => $item->replaced_at?->format('d M Y, h:i A'),
                    'replace_image_url' => $item->replace_image_url,

                    'refunded_at'       => $item->refunded_at?->format('d M Y, h:i A'),
                    'refund_amount'     => $item->refund_amount,
                    'formatted_refund_amount' => $item->formatted_refund_amount,

                    'shop_verification' => [
                        'show_verify_button' => $showShopVerifyButton,
                        'verify_reason' => $shopVerifyReason,
                        'show_replace_verify_button' => $showReplaceShopVerifyButton,
                        'replace_verify_reason' => $replaceShopVerifyReason,
                        'verification_status' => $verificationStatus,
                        'admin_verified' => $item->return_verified_by_admin,
                        'admin_verified_at' => $item->admin_verified_at?->format('d M Y, h:i A'),
                        'admin_verification_notes' => $item->admin_verification_notes,
                        'shop_verified' => $item->shop_verified,
                        'shop_verified_at' => $item->shop_verified_at?->format('d M Y, h:i A'),
                        'shop_verification_notes' => $item->shop_verification_notes,
                        'product_condition' => $item->product_condition,
                        'return_approval' => $item->return_approval,
                        'return_approval_at' => $item->return_approval_at?->format('d M Y, h:i A'),
                        'return_approval_notes' => $item->return_approval_notes,

                        'replace_admin_verified' => $item->replace_verified_by_admin,
                        'replace_admin_verified_at' => $item->replace_admin_verified_at?->format('d M Y, h:i A'),
                        'replace_admin_notes' => $item->replace_admin_verification_notes,
                        'replace_approval' => $item->replace_approval,
                        'replace_approval_at' => $item->replace_approval_at?->format('d M Y, h:i A'),
                        'replace_approval_notes' => $item->replace_approval_notes,
                    ],

                    'is_return_request_pending' => $item->isReturnRequestPending(),
                    'is_return_admin_approved' => $item->isReturnAdminApproved(),
                    'is_return_admin_rejected' => $item->isReturnAdminRejected(),
                    'is_waiting_for_shop_verification' => $item->isWaitingForShopVerification(),
                    'is_shop_verified' => $item->isShopVerified(),
                    'is_shop_rejected' => $item->isShopRejected(),
                    'is_return_approved' => $item->isReturnApproved(),
                    'is_return_approval_pending' => $item->isReturnApprovalPending(),
                    'is_approved_for_refund' => $item->isApprovedForRefund(),
                ];
            })->toArray(),
        ];

        return response()->json([
            'status' => 1,
            'data'   => $orderDetails
        ]);
    }

    public function changeListOrderStatus()
    {
        return response()->json([
            'success' => 1,
            'data' => [
                OrderStatus::PENDING,
                OrderStatus::PROCESSING,
                OrderStatus::SHIPPED,
                OrderStatus::DELIVERED,
            ],
        ]);
    }

    public function changeOrderStatus($request, $id)
    {
        $allowedStatuses = [
            OrderStatus::PENDING,
            OrderStatus::PROCESSING,
            OrderStatus::SHIPPED,
            OrderStatus::DELIVERED
        ];

        $validationRules = [
            'status' => 'required|string|in:' . implode(',', $allowedStatuses),
            'notes' => 'nullable|string',
            'order_item_ids' => 'nullable|array',
            'order_item_ids.*' => 'exists:order_items,id,order_id,' . $id,
        ];

        if ($request->status === OrderStatus::SHIPPED) {
            $validationRules['courier_id'] = 'required|exists:couriers,id';
            $validationRules['tracking_number'] = 'required|string|max:100';
            $validationRules['estimated_delivery_at'] = 'nullable|date|after:today';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $order = Order::with(['orderItems', 'shopOrders'])->find($id);

            if (!$order) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Order not found'
                ], 404);
            }

            $currentStatus = $order->status instanceof OrderStatus
                ? $order->status->value
                : $order->status;

            $newStatus = ucfirst($request->status);

            if (!$this->canOrderTransitionTo($currentStatus, $newStatus)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Cannot transition order from ' . $currentStatus . ' to ' . $newStatus
                ], 400);
            }

            $allOrderItems = OrderItem::where('order_id', $id)->get();

            if ($request->has('order_item_ids') && !empty($request->order_item_ids)) {
                $itemsToUpdate = $allOrderItems->whereIn('id', $request->order_item_ids);
                $updateMode = 'specific_items';
            } else {
                $itemsToUpdate = $allOrderItems;
                $updateMode = 'all_items';
            }

            $updatedItems = [];
            switch ($newStatus) {
                case OrderStatus::PROCESSING:
                    $updatedItems = $this->handleOrderProcessing($order, $itemsToUpdate, $request);
                    break;

                case OrderStatus::SHIPPED:
                    $courier = Courier::find($request->courier_id);
                    if (!$courier) {
                        throw new \Exception('Courier not found');
                    }
                    $updatedItems = $this->handleOrderShipped($order, $itemsToUpdate, $request, $courier);
                    break;

                case OrderStatus::DELIVERED:
                    $updatedItems = $this->handleOrderDelivered($order, $itemsToUpdate, $request);
                    break;

                default:
                    $updatedItems = [];
                    break;
            }

            $order->update([
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newStatus,
                'notes' => $request->notes ?? 'Status updated to ' . $newStatus,
                'created_by' => auth()->id(),
                'metadata' => [
                    'total_order_items' => $allOrderItems->count(),
                    'items_updated_count' => count($updatedItems),
                    'update_mode' => $updateMode,
                    'specific_item_ids' => $request->order_item_ids ?? [],
                    'updated_item_ids' => $updatedItems,
                    'shipping_info' => $newStatus === OrderStatus::SHIPPED ? [
                        'courier_id' => $request->courier_id,
                        'courier_name' => $courier->name ?? null,
                        'tracking_number' => $request->tracking_number,
                        'estimated_delivery_at' => $request->estimated_delivery_at,
                    ] : null,
                ]
            ]);

            $this->updateShopOrdersStatus($order, $newStatus, $allOrderItems);

            if ($newStatus === OrderStatus::SHIPPED) {
                $this->updateOrderShipmentStats($order);
            }

            if ($newStatus === OrderStatus::DELIVERED) {
                $this->checkAndUpdateOrderCompletion($order);
            }

            DB::commit();

            // $this->recalculateAndUpdateOrderStatus($order);

            $order->refresh();

            return response()->json([
                'status' => 1,
                'message' => 'Order status updated successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'previous_status' => $currentStatus,
                    'new_status' => $newStatus,
                    'status_description' => OrderStatus::getDescription($newStatus),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Order status change failed: ' . $e->getMessage(), [
                'order_id' => $id,
                'status' => $request->status,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    private function recalculateAndUpdateOrderStatus(Order $order)
    {
        $newOrderStatus = $this->calculateOrderStatusFromItems($order);
        $currentOrderStatus = $order->status instanceof OrderStatus
            ? $order->status->value
            : $order->status;

        if ($currentOrderStatus !== $newOrderStatus) {
            $order->update([
                'status' => $newOrderStatus,
                'status_updated_at' => now(),
                'status_summary' => $this->generateStatusSummary($order)
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newOrderStatus,
                'notes' => 'Auto-calculated based on item status distribution',
                'created_by' => auth()->id(),
                'metadata' => [
                    'trigger' => 'item_status_change',
                    'item_status_counts' => $order->orderItems->groupBy('status')->map->count()->toArray(),
                    'previous_status' => $currentOrderStatus
                ]
            ]);

            \Log::info('Order status auto-updated', [
                'order_id' => $order->id,
                'old_status' => $currentOrderStatus,
                'new_status' => $newOrderStatus
            ]);

            return true;
        }

        return false;
    }

    private function calculateOrderStatusFromItems(Order $order): string
    {
        $items = $order->orderItems;

        if ($items->isEmpty()) {
            return OrderStatus::PENDING;
        }

        $statusCounts = $this->normalizeStatusCounts($items);
        $totalItems = $items->count();

        if ($statusCounts['delivered'] === $totalItems) {
            return OrderStatus::COMPLETED;
        }

        if ($statusCounts['cancelled'] === $totalItems) {
            return OrderStatus::CANCELLED;
        }

        if ($statusCounts['refunded'] === $totalItems) {
            return OrderStatus::REFUNDED;
        }

        if ($statusCounts['cancelled'] > 0 && $statusCounts['cancelled'] < $totalItems) {
            return 'Partially Cancelled';
        }

        if ($statusCounts['returned'] > 0) {
            return 'Partially Returned';
        }

        if ($statusCounts['shipped'] > 0) {
            return OrderStatus::SHIPPED;
        }

        if ($statusCounts['processing'] > 0) {
            return OrderStatus::PROCESSING;
        }

        return OrderStatus::PENDING;
    }

    private function normalizeStatusCounts($items): array
    {
        $counts = [
            'pending' => 0,
            'confirmed' => 0,
            'processing' => 0,
            'shipped' => 0,
            'delivered' => 0,
            'cancelled' => 0,
            'returned' => 0,
            'refunded' => 0,
            'return_requested' => 0,
            'replace_requested' => 0,
        ];

        foreach ($items as $item) {
            $status = strtolower($item->status);

            switch ($status) {
                case 'pending':
                case 'pending':
                    $counts['pending']++;
                    break;
                case 'confirmed':
                case 'confirmed':
                    $counts['confirmed']++;
                    break;
                case 'processing':
                case 'processing':
                    $counts['processing']++;
                    break;
                case 'shipped':
                case 'shipped':
                    $counts['shipped']++;
                    break;
                case 'delivered':
                case 'delivered':
                    $counts['delivered']++;
                    break;
                case 'cancelled':
                case 'cancelled':
                    $counts['cancelled']++;
                    break;
                case 'returned':
                case 'returned':
                    $counts['returned']++;
                    break;
                case 'refunded':
                case 'refunded':
                    $counts['refunded']++;
                    break;
                case 'return_requested':
                    $counts['return_requested']++;
                    break;
                case 'replace_requested':
                    $counts['replace_requested']++;
                    break;
                default:
                    $counts['pending']++;
            }
        }

        return $counts;
    }

    private function generateStatusSummary(Order $order): array
    {
        $items = $order->orderItems;
        $statusCounts = $this->normalizeStatusCounts($items);

        return [
            'total_items' => $items->count(),
            'status_distribution' => $statusCounts,
            'calculated_at' => now()->toISOString(),
            'progress_percentage' => $this->calculateProgressPercentage($statusCounts, $items->count()),
            'is_completed' => $statusCounts['delivered'] === $items->count(),
            'has_issues' => $statusCounts['cancelled'] > 0 || $statusCounts['return_requested'] > 0
        ];
    }

    private function calculateProgressPercentage($statusCounts, $totalItems): int
    {
        if ($totalItems === 0) return 0;

        $progress = 0;
        foreach ($statusCounts as $status => $count) {
            $progress += match($status) {
                'delivered' => $count * 100,
                'shipped' => $count * 75,
                'processing' => $count * 50,
                'confirmed' => $count * 25,
                'pending' => $count * 10,
                default => 0,
            };
        }

        return round($progress / $totalItems);
    }

    private function handleOrderDelivered(Order $order, $itemsToUpdate, $request): array
    {
        $updatedItems = [];

        $order->update([
            'delivery_completed_at' => now(),
        ]);

        foreach ($itemsToUpdate as $item) {
            if ($this->canItemTransitionTo($item->status, OrderItemStatus::DELIVERED->value)) {
                $oldStatus = $item->status;

                $item->update([
                    'status' => OrderItemStatus::DELIVERED->value,
                    'delivered_at' => now(),
                    'updated_at' => now(),
                ]);

                OrderItemStatusHistory::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'status' => OrderItemStatus::DELIVERED->value,
                    'notes' => $request->notes ?? 'Item delivered successfully',
                    'created_by' => auth()->id(),
                    'metadata' => [
                        'previous_status' => $oldStatus,
                        'order_status_change' => OrderStatus::DELIVERED,
                        'delivered_at' => now()->toDateTimeString(),
                    ]
                ]);

                $updatedItems[] = $item->id;
            }
        }

        return $updatedItems;
    }

    private function handleOrderProcessing(Order $order, $itemsToUpdate, $request): array
    {
        $updatedItems = [];

        foreach ($itemsToUpdate as $item) {
            if ($this->canItemTransitionTo($item->status, OrderItemStatus::PROCESSING->value)) {
                $oldStatus = $item->status;

                $item->update([
                    'status' => OrderItemStatus::PROCESSING->value,
                    'updated_at' => now(),
                ]);

                // Create order item status history
                OrderItemStatusHistory::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'status' => OrderItemStatus::PROCESSING->value,
                    'notes' => $request->notes ?? 'Item processing started',
                    'created_by' => auth()->id(),
                    'metadata' => [
                        'previous_status' => $oldStatus,
                        'order_status_change' => OrderStatus::PROCESSING,
                        'changed_at' => now()->toDateTimeString(),
                    ]
                ]);

                $updatedItems[] = $item->id;
            }
        }

        return $updatedItems;
    }

    private function canOrderTransitionTo($currentStatus, $newStatus): bool
    {
        $allowedTransitions = [
            OrderStatus::PENDING => [
                OrderStatus::PROCESSING,
            ],
            OrderStatus::PROCESSING => [
                OrderStatus::SHIPPED,
            ],
            OrderStatus::SHIPPED => [
                OrderStatus::DELIVERED,
            ],
            OrderStatus::DELIVERED => [
                OrderStatus::COMPLETED,
            ],
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    private function updateShopOrdersStatus(Order $order, string $orderStatus, $allOrderItems)
    {
        $shopOrders = ShopOrder::where('order_id', $order->id)->get();

        foreach ($shopOrders as $shopOrder) {
            // Filter items for this specific shop
            $shopOrderItems = $allOrderItems->where('shop_id', $shopOrder->shop_id);

            $totalItems = $shopOrderItems->count();
            $processingItems = $shopOrderItems->where('status', OrderItemStatus::PROCESSING->value)->count();
            $shippedItems = $shopOrderItems->where('status', OrderItemStatus::SHIPPED->value)->count();
            $deliveredItems = $shopOrderItems->where('status', OrderItemStatus::DELIVERED->value)->count();

            $shopOrderStatus = $this->determineShopOrderStatus(
                $totalItems,
                $processingItems,
                $shippedItems,
                $deliveredItems,
                $orderStatus
            );

            $deliveryCompletedAt = null;
            if ($deliveredItems > 0 && $deliveredItems === $totalItems) {
                $latestDelivery = $shopOrderItems
                    ->whereNotNull('delivered_at')
                    ->max('delivered_at');
                $deliveryCompletedAt = $latestDelivery;
            }

            $shopOrder->update([
                'status' => $shopOrderStatus,
                'total_items' => $totalItems,
                'processing_items' => $processingItems,
                'shipped_items' => $shippedItems,
                'delivered_items' => $deliveredItems,
                'delivery_completed_at' => $deliveryCompletedAt,
                'updated_at' => now(),
            ]);
        }
    }

    private function determineShopOrderStatus(
        int $totalItems,
        int $processingItems,
        int $shippedItems,
        int $deliveredItems,
        string $orderStatus
    ): string {
        if ($deliveredItems === $totalItems) {
            return OrderStatus::DELIVERED;
        }

        if ($shippedItems > 0 && $deliveredItems < $totalItems) {
            return OrderStatus::SHIPPED;
        }

        if ($processingItems > 0) {
            return OrderStatus::PROCESSING;
        }

        return $orderStatus;
    }

    private function checkAndUpdateOrderCompletion(Order $order)
    {
        $allOrderItems = OrderItem::where('order_id', $order->id)->get();

        $deliveredItems = $allOrderItems->where('status', OrderItemStatus::DELIVERED->value)->count();
        $totalItems = $allOrderItems->count();

        if ($deliveredItems === $totalItems && $order->status !== OrderStatus::COMPLETED) {
            $order->update([
                'status' => OrderStatus::COMPLETED,
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::COMPLETED,
                'notes' => 'Order automatically marked as completed (all items delivered)',
                'created_by' => auth()->id(),
                'metadata' => [
                    'delivered_items' => $deliveredItems,
                    'total_items' => $totalItems,
                ]
            ]);
        }
    }

    private function handleOrderShipped(Order $order, $itemsToUpdate, $request, Courier $courier): array
    {
        $updatedItems = [];

        foreach ($itemsToUpdate as $item) {
            if ($this->canItemTransitionTo($item->status, OrderItemStatus::SHIPPED->value)) {
                $oldStatus = $item->status;

                $shipment = Shipment::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'courier_id' => $courier->id,
                    'tracking_number' => $request->tracking_number,
                    'status' => DeliveryStatus::PICKED_UP,
                    'shipped_at' => now(),
                    'estimated_delivery_at' => $request->estimated_delivery_at
                        ?: now()->addDays($courier->estimated_delivery_days ?? 5),
                    'notes' => $request->notes,
                    'recipient_name' => optional($order->shippingAddress)->name,
                    'recipient_phone' => optional($order->shippingAddress)->phone,
                    'delivery_address' => optional($order->shippingAddress)->full_address,
                ]);

                $item->update([
                    'status' => OrderItemStatus::SHIPPED->value,
                    'tracking_number' => $request->tracking_number,
                    'courier_name' => $courier->name,
                    'shipment_id' => $shipment->id,
                    'updated_at' => now(),
                ]);

                OrderItemStatusHistory::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'status' => OrderItemStatus::SHIPPED->value,
                    'notes' => $request->notes ?? 'Item shipped via ' . $courier->name . '. Tracking #: ' . $request->tracking_number,
                    'created_by' => auth()->id(),
                    'metadata' => [
                        'previous_status' => $oldStatus,
                        'order_status_change' => OrderStatus::SHIPPED,
                        'courier_id' => $courier->id,
                        'courier_name' => $courier->name,
                        'tracking_number' => $request->tracking_number,
                        'shipment_id' => $shipment->id,
                        'estimated_delivery_at' => $shipment->estimated_delivery_at?->format('Y-m-d H:i:s'),
                        'changed_at' => now()->toDateTimeString(),
                    ]
                ]);

                $updatedItems[] = $item->id;
            }
        }

        if (count($updatedItems) > 0) {
            $this->sendShippingNotification($order, $itemsToUpdate->first(), $shipment);
        }

        return $updatedItems;
    }

    private function sendShippingNotification(Order $order, OrderItem $orderItem, Shipment $shipment)
    {
        try {
            $user = $order->user;

            if (!$user) {
                return;
            }

            $notificationData = [
                'order_id' => $order->id,
                'item_id' => $orderItem->id,
                'tracking_number' => $shipment->tracking_number,
                'courier_name' => $shipment->courier->name ?? 'Courier',
                'estimated_delivery_at' => $shipment->estimated_delivery_at?->format('d M Y'),
                'tracking_url' => $shipment->courier ? $shipment->courier->getTrackingUrl($shipment->tracking_number) : null,
            ];

            Notification::createNotification(
                $order->user_id,
                Notification::TYPE_ORDER_STATUS_UPDATE,
                'Order Shipped',
                "Your order #{$order->order_no} has been shipped via {$shipment->courier->name}. Tracking #: {$shipment->tracking_number}" .
                    ($shipment->estimated_delivery_at
                        ? ". Estimated delivery: " . $shipment->estimated_delivery_at->format('d M Y')
                        : ''),
                $notificationData
            );
        } catch (\Exception $e) {
            \Log::error('Shipping notification failed: ' . $e->getMessage());
        }
    }

    protected function updateOrderShipmentStats($order)
    {
        $totalItems = $order->orderItems()->count();
        $shippedItems = $order->orderItems()
            ->where('status', OrderItemStatus::SHIPPED->value)
            ->orWhere('status', OrderItemStatus::DELIVERED->value)
            ->count();

        $hasShipments = $shippedItems > 0;
        $allItemsShipped = $shippedItems === $totalItems;

        $order->update([
            'has_shipments' => $hasShipments,
            'all_items_shipped' => $allItemsShipped,
            'shipments_count' => Shipment::where('order_id', $order->id)->count(),
            'last_shipment_at' => now(),
        ]);
    }

    private function canItemTransitionTo($currentStatus, $newStatus): bool
    {
        $currentStatusValue = $currentStatus instanceof \BackedEnum ? $currentStatus->value : $currentStatus;

        $allowedTransitions = [
            OrderItemStatus::PENDING->value => [
                OrderItemStatus::PROCESSING->value,
                OrderItemStatus::CANCELLED->value,
            ],
            OrderItemStatus::PROCESSING->value => [
                OrderItemStatus::SHIPPED->value,
                OrderItemStatus::CANCELLED->value,
            ],
            OrderItemStatus::SHIPPED->value => [
                OrderItemStatus::DELIVERED->value,
            ],
            OrderItemStatus::DELIVERED->value => [],
            OrderItemStatus::CANCELLED->value => [],
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatusValue] ?? []);
    }

    public function shopVerifyReturn($request, $orderId, $itemId)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'product_condition' => 'required_if:action,approve|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Find order item with order relationship
            $orderItem = OrderItem::where('id', $itemId)
                ->where('order_id', $orderId)
                ->with('order')
                ->firstOrFail();

            // Verify the order exists
            if (!$orderItem->order) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if user is shop owner
            if (
                auth()->user()->type != UserType::SHOP ||
                auth()->user()->shop->id != $orderItem->shop_id
            ) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Unauthorized to verify this product'
                ], 403);
            }

            // Check if item is in correct status
            if ($orderItem->status->value !== OrderItemStatus::RETURN_REQUESTED->value) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Item is not in return requested status'
                ], 400);
            }

            // Check if admin has approved
            if ($orderItem->return_verified_by_admin !== true) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Return request not approved by admin yet'
                ], 400);
            }

            // Check if already verified by shop
            if (!is_null($orderItem->shop_verified)) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Product already verified by shop'
                ], 400);
            }

            $metadata = [
                'shop_verified' => $request->action == 'approve',
                'product_condition' => $request->product_condition,
                'shop_verification_notes' => $request->notes,
                'shop_verifier_id' => auth()->id(),
                'shop_verified_at' => now()->toDateTimeString(),
            ];

            if ($request->action == 'approve') {
                $orderItem->update([
                    'shop_verified' => true,
                    'product_condition' => $request->product_condition,
                    'shop_verification_notes' => $request->notes,
                    'verified_by_shop' => auth()->user()->shop->id,
                    'shop_verified_at' => now(),
                ]);

                OrderItemStatusHistory::create([
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'status' => OrderItemStatus::SHOP_APPROVED->value,
                    'notes' => 'Shop verified and approved return' . ($request->notes ? ': ' . $request->notes : ''),
                    'metadata' => $metadata,
                    'created_by' => auth()->id()
                ]);

                $message = 'Product verified and return approved successfully';
                $notificationType = 'return_approved';
                $notificationMessage = "Your return request for '{$orderItem->product_name}' has been verified and approved by the shop.";
            } else {
                $orderItem->update([
                    'shop_verified' => false,
                    'shop_verification_notes' => $request->notes,
                    'verified_by_shop' => auth()->id(),
                    'shop_verified_at' => now(),
                ]);

                // Create status history
                OrderItemStatusHistory::create([
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'status' => OrderItemStatus::SHOP_REJECTED->value,
                    'notes' => 'Shop rejected return: ' . ($request->notes ?: 'Product condition not acceptable'),
                    'metadata' => $metadata,
                    'created_by' => auth()->id()
                ]);

                $message = 'Product verification rejected';
                $notificationType = 'return_rejected';
                $notificationMessage = "Your return request for '{$orderItem->product_name}' has been rejected by the shop. Reason: " . ($request->notes ?: 'Product condition not acceptable');
            }

            // Send notification
            $this->sendShopVerificationNotification(
                $orderItem,
                $request->action == 'approve',
                $notificationMessage,
                $notificationType
            );

            DB::commit();

            return response()->json([
                'success' => 1,
                'message' => $message,
                'data' => [
                    'order_id' => $orderId,
                    'item_id' => $orderItem->id,
                    'product_name' => $orderItem->product_name,
                    'shop_verified' => $orderItem->shop_verified,
                    'shop_verified_at' => $orderItem->shop_verified_at?->format('Y-m-d H:i:s'),
                    'product_condition' => $orderItem->product_condition,
                    'status' => $orderItem->status,
                    'status_description' => $orderItem->status_description,
                    'verification_notes' => $orderItem->shop_verification_notes,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => 0,
                'message' => 'Order item not found for this order'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shop verify return error: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'item_id' => $itemId,
                'shop_id' => auth()->user()->shop_id,
            ]);

            return response()->json([
                'success' => 0,
                'message' => 'Failed to process verification: ' . $e->getMessage()
            ], 500);
        }
    }

    public function shopVerifyReplace($request, $orderId, $itemId)
    {

        $request->validate([
            'action' => 'required|in:approve,reject',
            'product_condition' => 'required_if:action,approve|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Find order item with order relationship
            $orderItem = OrderItem::where('id', $itemId)
                ->where('order_id', $orderId)
                ->with('order')
                ->firstOrFail();

            // Verify the order exists
            if (!$orderItem->order) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Order not found'
                ], 404);
            }

            if (
                auth()->user()->type != UserType::SHOP ||
                auth()->user()->shop->id != $orderItem->shop_id
            ) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Unauthorized to verify this product'
                ], 403);
            }

            if ($orderItem->status->value !== OrderItemStatus::REPLACE_REQUESTED->value) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Item is not in replace requested status'
                ], 400);
            }

            if ($orderItem->replace_verified_by_admin !== true) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Replace request not approved by admin yet'
                ], 400);
            }

            if (!is_null($orderItem->shop_verified)) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Product already verified by shop'
                ], 400);
            }

            $metadata = [
                'shop_verified' => $request->action == 'approve',
                'product_condition' => $request->product_condition,
                'shop_verification_notes' => $request->notes,
                'shop_verifier_id' => auth()->id(),
                'shop_verified_at' => now()->toDateTimeString(),
                'new_product_id' => $request->new_product_id,
            ];

            if ($request->action == 'approve') {
                $orderItem->update([
                    'shop_verified' => true,
                    'product_condition' => $request->product_condition,
                    'shop_verification_notes' => $request->notes,
                    'verified_by_shop' => auth()->user()->shop->id,
                    'shop_verified_at' => now(),
                ]);

                OrderItemStatusHistory::create([
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'status' => OrderItemStatus::REPLACE_APPROVED->value,
                    'notes' => 'Shop verified and approved replacement' . ($request->notes ? ': ' . $request->notes : ''),
                    'metadata' => $metadata,
                    'created_by' => auth()->id()
                ]);

                $newProduct = \App\Models\Product::find($request->new_product_id);

                $message = 'Product verified and replacement approved successfully';
                $notificationType = 'replace_approved';
                $notificationMessage = "Your replacement request for '{$orderItem->product_name}' has been verified and approved by the shop.";
            } else {
                $orderItem->update([
                    'shop_verified' => false,
                    'shop_verification_notes' => $request->notes,
                    'verified_by_shop' => auth()->id(),
                    'shop_verified_at' => now(),
                ]);

                OrderItemStatusHistory::create([
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'status' => OrderItemStatus::REPLACE_REQUESTED->value,
                    'notes' => 'Shop rejected replacement: ' . ($request->notes ?: 'Product condition not acceptable'),
                    'metadata' => $metadata,
                    'created_by' => auth()->id()
                ]);

                $message = 'Product verification rejected';
                $notificationType = 'replace_rejected';
                $notificationMessage = "Your replacement request for '{$orderItem->product_name}' has been rejected by the shop. Reason: " . ($request->notes ?: 'Product condition not acceptable');
            }

            $this->sendShopVerificationNotification(
                $orderItem,
                $request->action == 'approve',
                $notificationMessage,
                $notificationType
            );

            DB::commit();

            return response()->json([
                'success' => 1,
                'message' => $message,
                'data' => [
                    'order_id' => $orderId,
                    'item_id' => $orderItem->id,
                    'product_name' => $orderItem->product_name,
                    'shop_verified' => $orderItem->shop_verified,
                    'shop_verified_at' => $orderItem->shop_verified_at?->format('Y-m-d H:i:s'),
                    'product_condition' => $orderItem->product_condition,
                    'new_product_id' => $orderItem->new_product_id,
                    'new_product_name' => isset($newProduct) ? $newProduct->name : null,
                    'status' => $orderItem->status,
                    'status_description' => $orderItem->status_description,
                    'verification_notes' => $orderItem->shop_verification_notes,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => 0,
                'message' => 'Order item not found for this order'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shop verify replace error: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'item_id' => $itemId,
                'shop_id' => auth()->user()->shop_id,
            ]);

            return response()->json([
                'success' => 0,
                'message' => 'Failed to process verification: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sendShopVerificationNotification(
        OrderItem $orderItem,
        bool $isApproved,
        string $message,
        string $notificationType
    ) {
        try {
            $order = $orderItem->order;
            if (!$order) {
                return;
            }

            $notificationData = [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'item_id' => $orderItem->id,
                'product_name' => $orderItem->product_name,
                'shop_name' => auth()->user()->shop->name ?? 'Shop',
                'shop_verified' => $isApproved,
                'shop_verified_at' => now()->format('Y-m-d H:i:s'),
                'verification_type' => strpos($notificationType, 'return') !== false ? 'return' : 'replace',
            ];

            $type = $isApproved
                ? ($notificationType === 'return_approved' ? 'return_shop_approved' : 'replace_shop_approved')
                : ($notificationType === 'return_rejected' ? 'return_shop_rejected' : 'replace_shop_rejected');

            Notification::createNotification(
                $order->user_id,
                $type,
                $isApproved ? 'Shop Verification Approved' : 'Shop Verification Rejected',
                $message,
                $notificationData
            );
        } catch (\Exception $e) {
            Log::error('Shop verification notification failed: ' . $e->getMessage());
        }
    }

    public function getPendingVerifications()
    {
        try {
            $shopId = auth()->user()->shop_id;

            if (!$shopId) {
                return response()->json([
                    'success' => 0,
                    'message' => 'User is not associated with a shop'
                ], 400);
            }

            $pendingReturns = OrderItem::where('shop_id', $shopId)
                ->where('status', OrderItemStatus::RETURN_REQUESTED->value)
                ->where('return_verified_by_admin', true)
                ->whereNull('shop_verified')
                ->with(['order' => function ($query) {
                    $query->select('id', 'order_no', 'user_id');
                }, 'product' => function ($query) {
                    $query->select('id', 'name', 'image');
                }])
                ->get();

            $pendingReplaces = OrderItem::where('shop_id', $shopId)
                ->where('status', OrderItemStatus::REPLACE_REQUESTED->value)
                ->where('replace_verified_by_admin', true)
                ->whereNull('shop_verified')
                ->with(['order' => function ($query) {
                    $query->select('id', 'order_no', 'user_id');
                }, 'product' => function ($query) {
                    $query->select('id', 'name', 'image');
                }])
                ->get();

            return response()->json([
                'success' => 1,
                'data' => [
                    'pending_returns' => $pendingReturns->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'order_id' => $item->order_id,
                            'order_no' => $item->order->order_no ?? 'N/A',
                            'product_name' => $item->product_name,
                            'product_image' => $item->image_url,
                            'return_reason' => $item->return_reason,
                            'return_image' => $item->return_image_url,
                            'return_requested_at' => $item->return_requested_at?->format('Y-m-d H:i:s'),
                            'admin_verification_notes' => $item->admin_verification_notes,
                        ];
                    }),
                    'pending_replaces' => $pendingReplaces->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'order_id' => $item->order_id,
                            'order_no' => $item->order->order_no ?? 'N/A',
                            'product_name' => $item->product_name,
                            'product_image' => $item->image_url,
                            'replace_reason' => $item->replace_reason,
                            'replace_image' => $item->replace_image_url,
                            'replace_requested_at' => $item->replace_requested_at?->format('Y-m-d H:i:s'),
                            'admin_verification_notes' => $item->replace_admin_verification_notes,
                        ];
                    }),
                    'counts' => [
                        'returns' => $pendingReturns->count(),
                        'replaces' => $pendingReplaces->count(),
                        'total' => $pendingReturns->count() + $pendingReplaces->count(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get pending verifications error: ' . $e->getMessage());

            return response()->json([
                'success' => 0,
                'message' => 'Failed to fetch pending verifications'
            ], 500);
        }
    }
}
