<?php

namespace App\Service;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\FilterController;
use App\Models\Courier;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusHistory;
use App\Models\OrderStatusHistory;
use App\Models\Shipment;
use App\Models\Shop;
use App\Models\StockHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DataTables;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Spatie\Activitylog\Models\Activity;

class DummyOrderService
{
public function approveReturn(Request $request, Order $order, OrderItem $orderItem)
{
    $request->validate([
        'notes' => 'nullable|string|max:500',
    ]);

    try {
        DB::beginTransaction();

        // Check if item has return request
        if ($orderItem->status !== OrderItemStatus::RETURN_REQUESTED->value) {
            return response()->json([
                'status' => 0,
                'message' => 'No return request found for this item'
            ]);
        }

        // Update order item with admin approval details (status remains RETURN_REQUESTED)
        $orderItem->update([
            'return_verified_by_admin' => true,
            'admin_verified_at' => now(),
            'admin_verification_notes' => $request->notes,
            'verified_by' => auth()->id(), // Admin who verified
        ]);

        // Create status history for admin approval
        OrderItemStatusHistory::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'status' => OrderItemStatus::RETURN_REQUESTED->value,
            'notes' => 'Admin approved return request' . ($request->notes ? ': ' . $request->notes : ''),
            'metadata' => [
                'admin_verified' => true,
                'verified_by' => auth()->id(),
                'verification_notes' => $request->notes,
            ],
            'created_by' => auth()->id()
        ]);

        DB::commit();

        // Send notification to shop for verification
        $this->sendShopVerificationNotification($order, $orderItem);

        return response()->json([
            'status' => 1,
            'message' => 'Return request approved. Shop will verify the product.',
            'data' => [
                'item_id' => $orderItem->id,
                'admin_verified' => true,
                'admin_verified_at' => $orderItem->admin_verified_at?->format('Y-m-d H:i:s'),
                'shop_verified' => null, // Not yet verified by shop
                'return_requested_at' => $orderItem->return_requested_at?->format('Y-m-d H:i:s'),
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 0,
            'message' => 'Failed to approve return'
        ], 500);
    }
}

public function shopVerifyProduct(Request $request, Order $order, OrderItem $orderItem)
{
    $request->validate([
        'is_approved_for_refund' => 'required|boolean',
        'verification_notes' => 'nullable|string|max:500',
        'product_condition' => ['nullable', Rule::in(['good', 'damaged', 'used', 'sealed'])],
    ]);

    try {
        DB::beginTransaction();

        // Check if admin has approved
        if ($orderItem->return_verified_by_admin !== true) {
            return response()->json([
                'status' => 0,
                'message' => 'Return request not approved by admin yet'
            ]);
        }

        // Check if already verified by shop
        if (!is_null($orderItem->shop_verified)) {
            return response()->json([
                'status' => 0,
                'message' => 'Product already verified by shop'
            ]);
        }

        // Update shop verification
        $orderItem->update([
            'shop_verified' => $request->is_approved_for_refund,
            'shop_verified_at' => now(),
            'shop_verification_notes' => $request->verification_notes,
            'product_condition' => $request->product_condition,
            'verified_by_shop' => auth()->id(),
        ]);

        // If shop approves refund, change status to RETURNED and process refund
        if ($request->is_approved_for_refund === true) {
            // Change status to RETURNED
            $orderItem->update([
                'status' => OrderItemStatus::RETURNED,
                'returned_at' => now(),
            ]);

            // Create status history
            OrderItemStatusHistory::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'status' => OrderItemStatus::RETURNED,
                'notes' => 'Shop verified and approved return' . ($request->verification_notes ? ': ' . $request->verification_notes : ''),
                'metadata' => [
                    'shop_verified' => true,
                    'product_condition' => $request->product_condition,
                    'verified_by_shop' => auth()->id(),
                ],
                'created_by' => auth()->id()
            ]);

            $message = 'Product verified and return approved. ' . ($refundResult['message'] ?? '');

        } else {
            // Shop rejected the return
            OrderItemStatusHistory::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'status' => OrderItemStatus::RETURN_REQUESTED->value,
                'notes' => 'Shop rejected return: ' . ($request->verification_notes ?: 'Product condition not acceptable'),
                'metadata' => [
                    'shop_verified' => false,
                    'product_condition' => $request->product_condition,
                    'verified_by_shop' => auth()->id(),
                ],
                'created_by' => auth()->id()
            ]);

            $message = 'Product verified and return rejected.';
        }

        DB::commit();

        // Send notification to user
        $this->sendShopVerificationResultNotification($order, $orderItem, $request->is_approved_for_refund);

        return response()->json([
            'status' => 1,
            'message' => $message,
            'data' => [
                'item_id' => $orderItem->id,
                'shop_verified' => (bool) $request->is_approved_for_refund,
                'shop_verified_at' => $orderItem->shop_verified_at?->format('Y-m-d H:i:s'),
                'status' => $orderItem->status,
                'status_description' => $orderItem->status_description,
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 0,
            'message' => 'Failed to verify product'
        ], 500);
    }
}

public function rejectReturn(Request $request, Order $order, OrderItem $orderItem)
{
    $request->validate([
        'rejection_reason' => 'required|string|max:500',
    ]);

    try {
        DB::beginTransaction();

        if ($orderItem->status !== OrderItemStatus::RETURN_REQUESTED->value) {
            return response()->json([
                'status' => 0,
                'message' => 'No return request found'
            ]);
        }

        // Update order item with admin rejection (status remains RETURN_REQUESTED)
        $orderItem->update([
            'return_verified_by_admin' => false,
            'admin_verified_at' => now(),
            'admin_verification_notes' => $request->rejection_reason,
            'verified_by' => auth()->id(),
        ]);

        // Create status history
        OrderItemStatusHistory::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'status' => OrderItemStatus::RETURN_REQUESTED->value,
            'notes' => 'Admin rejected return request: ' . $request->rejection_reason,
            'metadata' => [
                'admin_verified' => false,
                'verified_by' => auth()->id(),
                'rejection_reason' => $request->rejection_reason,
            ],
            'created_by' => auth()->id()
        ]);

        DB::commit();

        // Notify user
        $this->sendReturnRejectionNotification($order, $orderItem, $request->rejection_reason);

        return response()->json([
            'status' => 1,
            'message' => 'Return request rejected',
            'data' => [
                'item_id' => $orderItem->id,
                'admin_verified' => false,
                'admin_verified_at' => $orderItem->admin_verified_at?->format('Y-m-d H:i:s'),
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 0,
            'message' => 'Failed to reject return'
        ], 500);
    }
}

public function getReturnDetails(Order $order, OrderItem $orderItem)
{
    if ($orderItem->status !== OrderItemStatus::RETURN_REQUESTED->value) {
        return response()->json([
            'status' => 0,
            'message' => 'No return request found'
        ]);
    }

    $data = [
        'item_id' => $orderItem->id,
        'product_name' => $orderItem->product_name,
        'return_reason' => $orderItem->return_reason,
        'return_image_url' => $orderItem->return_image_url,
        'return_requested_at' => $orderItem->return_requested_at?->format('Y-m-d H:i:s'),
        'status' => $orderItem->status,
        'status_description' => $orderItem->status_description,

        // Verification status
        'admin_verification' => [
            'verified' => $orderItem->return_verified_by_admin,
            'verified_at' => $orderItem->admin_verified_at?->format('Y-m-d H:i:s'),
            'notes' => $orderItem->admin_verification_notes,
        ],
        'shop_verification' => [
            'verified' => $orderItem->shop_verified,
            'verified_at' => $orderItem->shop_verified_at?->format('Y-m-d H:i:s'),
            'notes' => $orderItem->shop_verification_notes,
            'product_condition' => $orderItem->product_condition,
        ],
    ];

    return response()->json([
        'status' => 1,
        'data' => $data
    ]);
}
protected function sendShopVerificationNotification($order, $orderItem)
{
    // Get shop owner
    $shop = $orderItem->shop;
    if ($shop && $shop->owner) {
        Notification::createNotification(
            $shop->owner->id,
            Notification::TYPE_ORDER_STATUS_UPDATE,
            'Product Return Verification Required',
            "Please verify product: {$orderItem->product_name} - Order #{$order->order_no}",
            [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'item_id' => $orderItem->id,
                'product_name' => $orderItem->product_name,
                'return_reason' => $orderItem->return_reason,
            ]
        );
    }
}

protected function sendShopVerificationResultNotification($order, $orderItem, $isApproved)
{
    $message = $isApproved
        ? "Your product '{$orderItem->product_name}' has been verified and return approved. Refund will be processed."
        : "Your return request for '{$orderItem->product_name}' was rejected by shop verification.";

    Notification::createNotification(
        $order->user_id,
        Notification::TYPE_ORDER_STATUS_UPDATE,
        $isApproved ? 'Return Approved' : 'Return Rejected',
        $message,
        [
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'item_id' => $orderItem->id,
            'shop_approved' => $isApproved,
        ]
    );
}

public function adminVerifyReplace(Request $request, Order $order, OrderItem $orderItem)
{
    $request->validate([
        'notes' => 'nullable|string|max:500',
        'new_product_id' => 'required|exists:products,id', // Optional: if replacing with different product
    ]);

    try {
        DB::beginTransaction();

        // Check if item has replace request
        if ($orderItem->status !== OrderItemStatus::REPLACE_REQUESTED->value) {
            return response()->json([
                'status' => 0,
                'message' => 'No replace request found for this item'
            ]);
        }

        // Get replacement product
        $newProduct = Product::findOrFail($request->new_product_id);

        // Check product availability
        if (!$newProduct->is_available) {
            return response()->json([
                'status' => 0,
                'message' => 'Replacement product is not available'
            ]);
        }

        // Update order item with admin approval
        $orderItem->update([
            'replace_verified_by_admin' => true,
            'admin_verified_at' => now(),
            'admin_verification_notes' => $request->notes,
            'verified_by' => auth()->id(),
            'new_product_id' => $request->new_product_id,
        ]);

        // Create status history
        OrderItemStatusHistory::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'status' => OrderItemStatus::REPLACE_REQUESTED->value,
            'notes' => 'Admin approved replace request' . ($request->notes ? ': ' . $request->notes : ''),
            'metadata' => [
                'admin_verified' => true,
                'verified_by' => auth()->id(),
                'new_product_id' => $request->new_product_id,
                'new_product_name' => $newProduct->name,
            ],
            'created_by' => auth()->id()
        ]);

        // Generate new replacement order with amount 0
        $replacementOrder = $this->createReplacementOrder($order, $orderItem, $newProduct);

        DB::commit();

        // Send notification to user
        $this->sendReplaceApprovalNotification($order, $orderItem, $replacementOrder);

        return response()->json([
            'status' => 1,
            'message' => 'Replace request approved. New replacement order created.',
            'data' => [
                'item_id' => $orderItem->id,
                'admin_verified' => true,
                'admin_verified_at' => $orderItem->admin_verified_at?->format('Y-m-d H:i:s'),
                'replacement_order' => [
                    'id' => $replacementOrder->id,
                    'order_no' => $replacementOrder->order_no,
                    'amount' => 0,
                    'created_at' => $replacementOrder->created_at->format('Y-m-d H:i:s'),
                ],
                'original_order' => [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                ]
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 0,
            'message' => 'Failed to approve replace request: ' . $e->getMessage()
        ], 500);
    }
}

protected function createReplacementOrder($originalOrder, $orderItem, $newProduct)
{
    // Generate new order number
    $orderNo = 'REP' . date('Ymd') . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT);

    // Create replacement order with amount 0
    $replacementOrder = Order::create([
        'user_id' => $originalOrder->user_id,
        'shop_id' => $originalOrder->shop_id,
        'order_no' => $orderNo,
        'total_amount' => 0,
        'discount_amount' => 0,
        'final_amount' => 0,
        'status' => OrderStatus::PENDING,
        'payment_status' => 'paid', // Free replacement
        'original_order_id' => $originalOrder->id, // Link to original order
        'original_order_item_id' => $orderItem->id,
        'order_type' => 'replacement',
        'replacement_notes' => 'Free replacement for order #' . $originalOrder->order_no,
    ]);

    // Create order item with amount 0
    $replacementOrderItem = OrderItem::create([
        'order_id' => $replacementOrder->id,
        'user_id' => $originalOrder->user_id,
        'shop_id' => $originalOrder->shop_id,
        'product_id' => $newProduct->id,
        'product_name' => $newProduct->name,
        'product_image' => $newProduct->image,
        'amount' => 0,
        'quantity' => $orderItem->quantity,
        'quantity_unit' => $orderItem->quantity_unit,
        'total_amount' => 0,
        'status' => OrderItemStatus::PENDING,
        'is_replacement' => true,
        'original_order_id' => $originalOrder->id,
        'original_order_item_id' => $orderItem->id,
    ]);

    // Copy shipping address from original order
    if ($originalOrder->shippingAddress) {
        $originalOrder->shippingAddress->replicate()->fill([
            'order_id' => $replacementOrder->id,
        ])->save();
    }

    return $replacementOrder;
}

public function rejectReplace(Request $request, Order $order, OrderItem $orderItem)
{
    $request->validate([
        'rejection_reason' => 'required|string|max:500',
    ]);

    try {
        DB::beginTransaction();

        if ($orderItem->status !== OrderItemStatus::REPLACE_REQUESTED->value) {
            return response()->json([
                'status' => 0,
                'message' => 'No replace request found'
            ]);
        }

        // Update order item with admin rejection
        $orderItem->update([
            'replace_verified_by_admin' => false,
            'admin_verified_at' => now(),
            'admin_verification_notes' => $request->rejection_reason,
            'verified_by' => auth()->id(),
        ]);

        // Create status history
        OrderItemStatusHistory::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'status' => OrderItemStatus::REPLACE_REQUESTED->value,
            'notes' => 'Admin rejected replace request: ' . $request->rejection_reason,
            'metadata' => [
                'admin_verified' => false,
                'verified_by' => auth()->id(),
                'rejection_reason' => $request->rejection_reason,
            ],
            'created_by' => auth()->id()
        ]);

        DB::commit();

        // Notify user
        $this->sendReplaceRejectionNotification($order, $orderItem, $request->rejection_reason);

        return response()->json([
            'status' => 1,
            'message' => 'Replace request rejected',
            'data' => [
                'item_id' => $orderItem->id,
                'admin_verified' => false,
                'admin_verified_at' => $orderItem->admin_verified_at?->format('Y-m-d H:i:s'),
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 0,
            'message' => 'Failed to reject replace'
        ], 500);
    }
}
public function getReplaceReasons()
{
    $reasons = ReplaceOrderReason::where('is_show', true)->get(['id', 'reason']);

    return response()->json([
        'status' => 1,
        'data' => $reasons
    ]);
}
public function getReplaceDetails(Order $order, OrderItem $orderItem)
{
    if ($orderItem->status !== OrderItemStatus::REPLACE_REQUESTED->value) {
        return response()->json([
            'status' => 0,
            'message' => 'No replace request found'
        ]);
    }

    // Get replacement order if exists
    $replacementOrder = Order::where('original_order_id', $order->id)
        ->where('original_order_item_id', $orderItem->id)
        ->first();

    $data = [
        'item_id' => $orderItem->id,
        'product_name' => $orderItem->product_name,
        'replace_reason' => $orderItem->replace_reason,
        'replace_image_url' => $this->getReplaceImageUrl($orderItem),
        'replace_requested_at' => $orderItem->replace_requested_at?->format('Y-m-d H:i:s'),
        'status' => $orderItem->status,
        'status_description' => $orderItem->status_description,

        // Verification status
        'admin_verification' => [
            'verified' => $orderItem->replace_verified_by_admin,
            'verified_at' => $orderItem->admin_verified_at?->format('Y-m-d H:i:s'),
            'notes' => $orderItem->admin_verification_notes,
        ],

        // Replacement order info
        'replacement_order' => $replacementOrder ? [
            'id' => $replacementOrder->id,
            'order_no' => $replacementOrder->order_no,
            'amount' => (float) $replacementOrder->total_amount,
            'status' => $replacementOrder->status,
            'created_at' => $replacementOrder->created_at->format('Y-m-d H:i:s'),
            'is_free' => $replacementOrder->total_amount == 0,
        ] : null,
    ];

    return response()->json([
        'status' => 1,
        'data' => $data
    ]);
}

 protected function sendReplaceApprovalNotification($order, $orderItem, $replacementOrder)
    {
        Notification::createNotification(
            $order->user_id,
            Notification::TYPE_ORDER_STATUS_UPDATE,
            'Replace Request Approved',
            "Your replace request for '{$orderItem->product_name}' has been approved. New replacement order #{$replacementOrder->order_no} created.",
            [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'replacement_order_id' => $replacementOrder->id,
                'replacement_order_no' => $replacementOrder->order_no,
            ]
        );
    }
}
