<?php

namespace App\Service;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\DeliveryStatus;
use App\Enums\UserType;
use App\Http\Controllers\FilterController;
use App\Models\Courier;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusHistory;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShipmentStatusHistory;
use App\Models\StockHistory;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;

class OrderService
{
    public function index($request)
    {
        if ($request->ajax()) {
            $query = Order::with(['user', 'payment'])
                ->orderBy('id', 'desc');

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            if ($request->has('search') && !empty($request->search['value'])) {
                $searchTerm = $request->search['value'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('order_no', 'like', "%{$searchTerm}%")
                        ->orWhere('total_amount', 'like', "%{$searchTerm}%")
                        ->orWhere('status', 'like', "%{$searchTerm}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                            $userQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('phone', 'like', "%{$searchTerm}%");
                        })
                        ->orWhereHas('payment', function ($paymentQuery) use ($searchTerm) {
                            $paymentQuery->where('payment_method', 'like', "%{$searchTerm}%");
                        });
                });
            }

            if ($request->has('filters')) {
                $filters = is_string($request->filters) ? json_decode($request->filters, true) : $request->filters;
                $logic = $filters[0]['logic'] ?? 'and';

                $needsUserJoin = collect($filters)->contains(fn($f) => in_array($f['column'], ['name', 'phone']));

                if ($needsUserJoin) {
                    $query->leftJoin('users', 'users.id', '=', 'orders.user_id')
                        ->select('orders.*', 'users.name as user_name', 'users.phone as user_phone');
                }

                $query->where(function ($q) use ($filters, $logic) {
                    foreach ($filters as $filter) {
                        FilterController::applyFilter($q, $filter, $logic);
                    }
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="order-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('d-m-Y h:i A');
                })
                ->addColumn('order_no', function ($row) {
                    return $row->order_no ?? '-';
                })
                ->addColumn('total_amount', function ($row) {
                    return $row->total_amount !== null ? '₹' . number_format($row->total_amount, 2) : '-';
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user->name ?? '-';
                })
                ->addColumn('user_phone', function ($row) {
                    return $row->user->phone ?? '-';
                })
                ->addColumn('payment_type', function ($row) {
                    return $row->payment->payment_method ?? '-';
                })
                ->addColumn('order_status', function ($row) {
                    $statusLabels = [
                        OrderStatus::PENDING => 'warning',
                        OrderStatus::PROCESSING => 'primary',
                        OrderStatus::SHIPPED => 'info',
                        OrderStatus::COMPLETED => 'success',
                        OrderStatus::CANCELLED => 'danger',
                        OrderStatus::REFUNDED => 'secondary',
                    ];

                    $status = $row->status;
                    $badgeClass = $statusLabels[$status] ?? 'dark';

                    return '<span class="badge bg-' . $badgeClass . '">' . OrderStatus::getDescription($status) . '</span>';
                })
                ->addColumn('invoice_download', function ($row) {
                    return '<a href="' . url('admin/orders/invoice/' . Crypt::encrypt($row->id)) . '" class="btn btn-sm add-btn text-white" title="Download Invoice">
                        <i class="bi bi-file-earmark-arrow-down"></i>
                    </a>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex gap-2">';

                    $btn .= '<a href="' . url('admin/orders/' . Crypt::encrypt($row->id)) . '" title="View Order">
                            <img src="' . url('img/eye-icon.png') . '" alt="View" width="30" height="30">
                        </a>';

                    // if (OrderStatus::canCancel($row->status)) {
                    //     $btn .= '<a href="#" class="cancel-order-btn"
                    //         data-id="' . $row->id . '"
                    //         data-amount="' . $row->total_amount . '"
                    //         data-payment-type="' . ($row->payment->payment_method ?? 'COD') . '"
                    //         title="Cancel Order">
                    //         <img src="' . url('img/cancel-icon.png') . '" alt="Cancel" width="30" height="30">
                    //     </a>';
                    // }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['checkbox', 'order_no', 'total_amount', 'user_name', 'user_phone', 'order_status', 'invoice_download', 'action'])
                ->make(true);
        }

        return view('backend.order.order_index');
    }

    // Cancel Order
    public function cancel($request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $order = Order::findOrFail($request->order_id);

        if (!OrderStatus::canCancel($order->status)) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled at this stage'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Update order status
            $order->update([
                'status' => OrderStatus::CANCELLED,
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id()
            ]);

            // Restock products
            foreach ($order->orderItems as $orderItem) {
                $product = $orderItem->product;
                if ($product) {
                    $previousStock = $product->product_stock;
                    $newStock = $previousStock + $orderItem->quantity;

                    $product->update(['product_stock' => $newStock]);

                    StockHistory::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'Restock',
                        'quantity' => $orderItem->quantity,
                        'previous_stock' => $previousStock,
                        'new_stock' => $newStock,
                        'total_stock' => $newStock,
                        'reason' => 'Order cancellation - Order #' . $order->id
                    ]);
                }
            }

            // Process refund for online payments
            if ($order->payment && $order->payment->payment_method === 'Razorpay') {
                $this->processRefund($order);
            }

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->withProperties(['reason' => $request->cancellation_reason])
                ->log('Order cancelled');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    // Bulk Cancel
    public function bulkCancel($request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);

        $successCount = 0;
        $failedCount = 0;
        $messages = [];

        foreach ($request->order_ids as $orderId) {
            try {
                $order = Order::findOrFail($orderId);

                if (!OrderStatus::canCancel($order->status)) {
                    $failedCount++;
                    $messages[] = "Order #{$orderId} cannot be cancelled at this stage";
                    continue;
                }

                DB::beginTransaction();

                $order->update([
                    'status' => OrderStatus::CANCELLED,
                    'cancellation_reason' => 'Bulk cancellation by admin',
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id()
                ]);

                // Restock products
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    if ($product) {
                        $previousStock = $product->product_stock;
                        $newStock = $previousStock + $orderItem->quantity;

                        $product->update(['product_stock' => $newStock]);

                        StockHistory::create([
                            'product_id' => $product->id,
                            'user_id' => auth()->id(),
                            'type' => 'Restock',
                            'quantity' => $orderItem->quantity,
                            'previous_stock' => $previousStock,
                            'new_stock' => $newStock,
                            'total_stock' => $newStock,
                            'reason' => 'Order cancellation - Order #' . $order->id
                        ]);
                    }
                }

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($order)
                    ->log('Order cancelled (bulk action)');

                DB::commit();
                $successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failedCount++;
                $messages[] = "Failed to cancel order #{$orderId}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Processed {$successCount} orders successfully, {$failedCount} failed",
            'details' => $messages
        ]);
    }

    // View Order
    public function view(Order $order)
    {
        $activities = Activity::where('subject_type', Order::class)
            ->where('subject_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('backend.order.order_view', compact('order', 'activities'));
    }

    // Invoice
    public function invoice(Order $order)
    {
        try {
            $order = Order::with([
                'orderItems.product.shop',
                'shippingAddress',
                'precheckout',
                'payment',
                'originalOrder',
                'user'
            ])->findOrFail($order->id);

            if ($order->is_replacement_order && !$order->invoice_no) {
                $order->refresh();
            }

            $company = [
                'name' => config('app.name', 'SRM RESIDENCE'),
                'address' => 'Tirunelveli, Tamil Nadu',
                'phone' => '+91 9876543210',
                'email' => 'support@srmresidence.com',
                'gstin' => 'GSTIN1234567890',
                'website' => 'www.srmresidence.com',
            ];

            $seller = null;
            foreach ($order->orderItems as $item) {
                if ($item->product && $item->product->shop) {
                    $seller = $item->product->shop;
                    break;
                }
            }

            if (!$seller) {
                $seller = (object) [
                    'name' => $company['name'],
                    'address' => $company['address'],
                    'phone' => $company['phone'],
                    'email' => $company['email'],
                    'gst_number' => $company['gstin'] ?? null,
                    'pan_number' => null,
                ];
            }

            $totals = [
                'subtotal' => $order->sub_total ?? $order->orderItems->sum('total_amount'),
                'discount' => $order->precheckout->coupon_amount ?? 0,
                'delivery' => $order->precheckout->delivery_charge ?? 0,
                'platform_fee' => $order->precheckout->platform_fee ?? 0,
                'tax' => 0,
                'grand_total' => $order->total_amount,
            ];

            $pdf = Pdf::loadView('backend.invoice', compact(
                'order',
                'company',
                'seller',
                'totals'
            ));

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('margin-top', 10);
            $pdf->setOption('margin-bottom', 10);
            $pdf->setOption('margin-left', 10);
            $pdf->setOption('margin-right', 10);

            return $pdf->download('invoice-' . $order->invoice_no . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Invoice generation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate invoice: ' . $e->getMessage());
        }
    }

    public function updateStatus($request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', OrderStatus::getAll()),
            'notes' => 'nullable|string|max:500'
        ]);

        if ($request->status === OrderStatus::CANCELLED->value && !OrderStatus::canCancel($order->status)) {
            return redirect()->back()->with('status', [
                'type' => 'error',
                'message' => 'Order cannot be cancelled at this stage'
            ]);
        }

        DB::beginTransaction();

        try {
            $oldStatus = $order->status;
            $order->update(['status' => $request->status]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $request->status,
                'notes' => $request->notes ?? 'Status updated'
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'notes' => $request->notes
                ])
                ->log('Order status updated');

            DB::commit();

            return redirect()->back()->with('status', [
                'type' => 'success',
                'message' => 'Order status updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('status', [
                'type' => 'error',
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ]);
        }
    }

    // Update Payment Status
    public function updatePaymentStatus($request, Order $order)
    {
        try {
            $request->validate([
                'status' => 'required|in:Paid,Refund',
                'notes' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            $order->payment()->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->withProperties([
                    'payment_status' => $request->status,
                    'notes' => $request->notes
                ])
                ->log('Payment status updated');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Cancel Product
    public function cancelProduct($request, $order, $orderItem)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
            'refund_amount' => 'required|numeric|min:0|max:' . $orderItem->total_amount
        ]);

        try {
            DB::beginTransaction();

            $orderItem->update([
                'status' => OrderItemStatus::CANCELLED,
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'refund_amount' => $request->refund_amount
            ]);

            OrderItemStatusHistory::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'status' => OrderItemStatus::CANCELLED->value,
                'notes' => $request->cancellation_reason,
                'created_by' => auth()->id()
            ]);

            // Restock product
            $product = $orderItem->product;
            if ($product) {
                $previousStock = $product->product_stock;
                $newStock = $previousStock + $orderItem->quantity;

                StockHistory::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'Restock',
                    'quantity' => $orderItem->quantity,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'total_stock' => $newStock,
                    'reason' => 'Product cancellation - Order #' . $order->id . ', Item #' . $orderItem->id
                ]);
            }

            $this->updateOrderTotals($order);

            if ($order->payment && $request->refund_amount == null) {

                if ($order->payment->payment_method === 'Razorpay') {
                    $this->processPartialRefund($request, $order, $orderItem->total_amount);

                } elseif ($order->payment->payment_method === 'Wallet' || $order->payment->wallet_used) {
                    $this->processWalletRefund($request, $order, $orderItem->total_amount);

                }
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->withProperties([
                    'item_id' => $orderItem->id,
                    'product_name' => $orderItem->product_name,
                    'reason' => $request->cancellation_reason,
                    'refund_amount' => $request->refund_amount
                ])
                ->log('Product cancelled from order');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product cancelled successfully',
                'item_id' => $orderItem->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Product Status History
    public function getProductStatusHistory(OrderItem $orderItem)
    {
        $history = OrderItemStatusHistory::where('order_item_id', $orderItem->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($record) {
                return [
                    'status' => $record->status,
                    'notes' => $record->notes,
                    'created_at' => $record->created_at->format('d M Y h:i A'),
                    'created_by' => $record->creator->name ?? 'System'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    // Partial Refund
    protected function processPartialRefund($request, $order, $refundAmount)
    {
        try {
            DB::beginTransaction();

            $payment = $order->payment;

            if ($refundAmount > $payment->amount) {
                throw new \Exception("Refund amount cannot exceed original payment amount");
            }

            $user = $order->user;
            if (!$user) {
                throw new \Exception("User not found for wallet refund");
            }

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            $previousBalance = $wallet->balance;
            $newBalance = $previousBalance + $refundAmount;

            $wallet->update(['balance' => $newBalance]);

            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $refundAmount,
                'balance_before' => $previousBalance,
                'balance_after' => $newBalance,
                'description' => 'Refund for Order #' . $order->order_no,
                'status' => 'completed',
                'metadata' => [
                    'refund_reason' => $request->refund_reason,
                    'original_payment_id' => $payment->id,
                    'refunded_by' => auth()->id(),
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'payment_method' => $payment->payment_method,
                ],
                'reference_id' => $payment->id,
                'reference_type' => Payment::class,
                'created_at' => now(),
            ]);

            $payment->update([
                'refund_amount' => ($payment->refund_amount ?? 0) + $refundAmount,
                'refunded_at' => now(),
                'refund_response' => [
                    'method' => 'wallet',
                    'wallet_transaction_id' => $walletTransaction->id,
                    'refund_date' => now()->toDateTimeString(),
                    'new_wallet_balance' => $newBalance,
                    'processed_by' => auth()->id(),
                ],
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'wallet_refund',
                'title' => 'Refund Received',
                'message' => '₹' . number_format($refundAmount, 2) . ' has been refunded to your wallet for Order #' . $order->order_no,
                'data' => json_encode([
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'refund_amount' => $refundAmount,
                    'new_wallet_balance' => $newBalance,
                    'transaction_id' => $walletTransaction->transaction_id,
                    'refund_date' => now()->format('d M Y, h:i A'),
                ]),
                'read_at' => null,
                'created_at' => now(),
            ]);

            \Log::info("Wallet refund processed", [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'refund_amount' => $refundAmount,
                'wallet_transaction_id' => $walletTransaction->id,
                'processed_by' => auth()->id(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Refund processed successfully to wallet',
                'data' => [
                    'refund_amount' => $refundAmount,
                    'wallet_balance' => $newBalance,
                    'transaction_id' => $walletTransaction->transaction_id,
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Wallet refund error: ' . $e->getMessage());

            throw new \Exception('Failed to process wallet refund: ' . $e->getMessage());
        }
    }

    protected function processWalletRefund($request, $order, $refundAmount)
    {
        try {
            DB::beginTransaction();

            $payment = $order->payment;

            if ($refundAmount > $payment->amount) {
                throw new \Exception("Refund amount cannot exceed original payment amount");
            }

            $user = $order->user;
            if (!$user) {
                throw new \Exception("User not found for wallet refund");
            }

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            $previousBalance = $wallet->balance;
            $newBalance = $previousBalance + $refundAmount;

            $wallet->update(['balance' => $newBalance]);

            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $refundAmount,
                'balance_before' => $previousBalance,
                'balance_after' => $newBalance,
                'description' => 'Refund for Order #' . $order->order_no,
                'status' => 'completed',
                'metadata' => [
                    'refund_reason' => $request->refund_reason,
                    'original_payment_id' => $payment->id,
                    'refunded_by' => auth()->id(),
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'payment_method' => $payment->payment_method,
                ],
                'reference_id' => $payment->id,
                'reference_type' => Payment::class,
                'created_at' => now(),
            ]);

            $payment->update([
                'refund_amount' => ($payment->refund_amount ?? 0) + $refundAmount,
                'refunded_at' => now(),
                'refund_response' => [
                    'method' => 'wallet',
                    'wallet_transaction_id' => $walletTransaction->id,
                    'refund_date' => now()->toDateTimeString(),
                    'new_wallet_balance' => $newBalance,
                    'processed_by' => auth()->id(),
                ],
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'wallet_refund',
                'title' => 'Refund Received',
                'message' => '₹' . number_format($refundAmount, 2) . ' has been refunded to your wallet for Order #' . $order->order_no,
                'data' => json_encode([
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'refund_amount' => $refundAmount,
                    'new_wallet_balance' => $newBalance,
                    'transaction_id' => $walletTransaction->transaction_id,
                    'refund_date' => now()->format('d M Y, h:i A'),
                ]),
                'read_at' => null,
                'created_at' => now(),
            ]);

            \Log::info("Wallet refund processed", [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'refund_amount' => $refundAmount,
                'wallet_transaction_id' => $walletTransaction->id,
                'processed_by' => auth()->id(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Refund processed successfully to wallet',
                'data' => [
                    'refund_amount' => $refundAmount,
                    'wallet_balance' => $newBalance,
                    'transaction_id' => $walletTransaction->transaction_id,
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Wallet refund error: ' . $e->getMessage());

            throw new \Exception('Failed to process wallet refund: ' . $e->getMessage());
        }
    }

    // Dispatch Items
    public function dispatchOrderItems($request, Order $order)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.courier_id' => 'required|exists:couriers,id',
            'items.*.tracking_number' => 'required|string|max:100',
            'items.*.estimated_days' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'shipping_date' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            $createdShipments = [];
            $shippingDate = $request->shipping_date ? Carbon::parse($request->shipping_date) : now();

            foreach ($request->items as $itemData) {
                $orderItem = OrderItem::find($itemData['order_item_id']);

                // Verify order item belongs to this order
                if ($orderItem->order_id !== $order->id) {
                    throw new \Exception("Order item does not belong to this order");
                }

                // Verify order item hasn't been shipped already
                if ($orderItem->shipment_id || $orderItem->status === OrderItemStatus::SHIPPED->value) {
                    throw new \Exception("Order item already shipped");
                }

                $courier = Courier::find($itemData['courier_id']);

                // Create shipment
                $shipment = $this->createShipmentForItem($order, $orderItem, $courier, $itemData, $shippingDate);

                // Update order item status
                $orderItem->updateStatus(
                    OrderItemStatus::SHIPPED->value,
                    "Shipped via {$courier->name}. Tracking: {$itemData['tracking_number']}",
                    [
                        'courier_name' => $courier->name,
                        'tracking_number' => $itemData['tracking_number'],
                        'estimated_delivery_days' => $itemData['estimated_days']
                    ]
                );

                // Link shipment to order item
                $orderItem->update([
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $itemData['tracking_number'],
                    'courier_name' => $courier->name
                ]);

                $createdShipments[] = $shipment;
            }

            // Update order status if all items are shipped
            $this->updateOrderShippingStatus($order);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->withProperties([
                    'shipments' => count($createdShipments),
                    'couriers' => collect($createdShipments)->pluck('courier.name')->unique()->toArray()
                ])
                ->log('Order items dispatched');

            DB::commit();

            // Send notifications
            $this->sendShipmentNotifications($order, $createdShipments);

            return response()->json([
                'success' => true,
                'message' => 'Order items dispatched successfully',
                'shipments' => $createdShipments
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch order items: ' . $e->getMessage()
            ], 500);
        }
    }

    // Create Shipment for Item
    private function createShipmentForItem(Order $order, OrderItem $orderItem, Courier $courier, array $data, Carbon $shippingDate): Shipment
    {
        $estimatedDeliveryDate = $shippingDate->copy()->addDays($data['estimated_days']);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'courier_id' => $courier->id,
            'tracking_number' => $data['tracking_number'],
            'status' => DeliveryStatus::PICKED_UP->value,
            'shipped_at' => $shippingDate,
            'estimated_delivery_at' => $estimatedDeliveryDate,
            'notes' => $data['notes'] ?? null,
            'recipient_name' => $order->shippingAddress->name ?? $order->user->name,
            'recipient_phone' => $order->shippingAddress->phone ?? $order->user->phone,
            'delivery_address' => $order->shippingAddress->full_address ?? '',
            'last_status_check_at' => now(),
            'metadata' => [
                'estimated_days' => $data['estimated_days'],
                'shipping_date' => $shippingDate->toDateTimeString(),
                'dispatched_by' => auth()->id(),
                'dispatched_at' => now()->toDateTimeString()
            ]
        ]);

        // Create initial status history
        ShipmentStatusHistory::create([
            'shipment_id' => $shipment->id,
            'status' => DeliveryStatus::PICKED_UP->value,
            'notes' => 'Package picked up by courier',
            'metadata' => [
                'courier' => $courier->name,
                'tracking_number' => $data['tracking_number'],
                'estimated_delivery' => $estimatedDeliveryDate->format('d M Y')
            ],
            'created_by' => auth()->id()
        ]);

        return $shipment;
    }

    // Update Order Shipping Status
    private function updateOrderShippingStatus(Order $order): void
    {
        $totalItems = $order->orderItems()->count();
        $shippedItems = $order->orderItems()
            ->whereIn('status', [
                OrderItemStatus::SHIPPED->value,
                OrderItemStatus::DELIVERED->value
            ])
            ->count();

        $allDelivered = $shippedItems === $totalItems;

        $order->update([
            'has_shipments' => $shippedItems > 0,
            'shipments_count' => Shipment::where('order_id', $order->id)->count(),
            'all_items_delivered' => $allDelivered
        ]);

        if ($allDelivered) {
            $order->update(['status' => OrderStatus::COMPLETED]);
        }
    }

    // Get Available Couriers
    public function getAvailableCouriers()
    {
        $couriers = Courier::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'estimated_delivery_days', 'contact_number', 'logo_url']);

        return response()->json([
            'success' => true,
            'couriers' => $couriers
        ]);
    }

    // Get Items for Dispatch
    public function getItemsForDispatch(Order $order)
    {
        $items = $order->orderItems()
            ->where('status', '!=', OrderItemStatus::CANCELLED->value)
            ->whereNull('shipment_id')
            ->with(['product'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'product_image' => $item->product_image,
                    'quantity' => $item->quantity,
                    'quantity_unit' => $item->quantity_unit,
                    'shop_id' => $item->shop_id,
                    'shop_name' => $item->shop->name ?? 'N/A',
                    'can_ship' => $item->status === OrderItemStatus::CONFIRMED->value
                ];
            });

        return response()->json([
            'success' => true,
            'items' => $items,
            'order_id' => $order->id,
            'order_no' => $order->order_no
        ]);
    }

    // Mark Shipment Delivered
    public function markShipmentDelivered($request, Shipment $shipment)
    {
        $request->validate([
            'delivery_proof_url' => 'nullable|url',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            $shipment->update([
                'status' => DeliveryStatus::DELIVERED->value,
                'actual_delivery_at' => now(),
                'delivery_proof_url' => $request->delivery_proof_url
            ]);

            ShipmentStatusHistory::create([
                'shipment_id' => $shipment->id,
                'status' => DeliveryStatus::DELIVERED->value,
                'notes' => $request->notes ?? 'Package delivered',
                'created_by' => auth()->id()
            ]);

            // Update order item status
            if ($shipment->orderItem) {
                $shipment->orderItem->updateStatus(
                    OrderItemStatus::DELIVERED->value,
                    'Item delivered. ' . ($request->notes ?? '')
                );
            }

            // Update order status
            $this->updateOrderDeliveryStatus($shipment->order);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($shipment->order)
                ->withProperties(['shipment_id' => $shipment->id])
                ->log('Shipment marked as delivered');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shipment marked as delivered',
                'shipment' => $shipment->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark shipment as delivered: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get Shipment Tracking
    public function getShipmentTracking(Shipment $shipment)
    {
        try {
            $timeline = $shipment->statusHistory->map(function ($history) {
                return [
                    'status' => $history->status_description,
                    'color' => $history->status_color,
                    'notes' => $history->notes,
                    'date' => $history->created_at->format('d M Y h:i A'),
                    'metadata' => $history->formatted_metadata
                ];
            });

            return response()->json([
                'success' => true,
                'shipment' => [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'status' => $shipment->status_description,
                    'status_color' => $shipment->status_color,
                    'courier' => $shipment->courier->name,
                    'tracking_url' => $shipment->tracking_url,
                    'shipped_at' => $shipment->shipped_at->format('d M Y'),
                    'estimated_delivery_at' => $shipment->estimated_delivery_at?->format('d M Y'),
                    'actual_delivery_at' => $shipment->actual_delivery_at?->format('d M Y h:i A'),
                    'is_delivered' => $shipment->is_delivered,
                    'is_overdue' => $shipment->is_overdue,
                    'days_overdue' => $shipment->days_overdue,
                    'timeline' => $timeline
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tracking information'
            ], 500);
        }
    }

    // Update Item Status
    public function updateItemStatus($request)
    {
        $request->validate([
            'order_id' => 'required',
            'item_id' => 'required|exists:order_items,id',
            'status' => 'required|in:' . implode(',', OrderItemStatus::getAll()),
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $orderId = Crypt::decrypt($request->order_id);
            $order = Order::findOrFail($orderId);
            $orderItem = OrderItem::findOrFail($request->item_id);

            // Verify item belongs to order
            if ($orderItem->order_id !== $order->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order item does not belong to this order'
                ], 400);
            }

            // Check if status transition is valid
            if (!$this->isValidStatusTransition($orderItem->status->value, $request->status)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition'
                ], 400);
            }

            DB::beginTransaction();

            $oldStatus = $orderItem->status->value;
            $orderItem->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

            // Log status history
            OrderItemStatusHistory::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'status' => $request->status,
                'notes' => $request->notes ?? "Status changed from {$oldStatus} to {$request->status}",
                'created_by' => auth()->id()
            ]);

            // Update order overall status if needed
            $this->updateOrderOverallStatus($order);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($orderItem)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'product' => $orderItem->product_name
                ])
                ->log('Product status updated');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully',
                'item' => $orderItem->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update Bulk Item Status
    public function updateBulkItemStatus($request)
    {
        $request->validate([
            'order_id' => 'required',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:order_items,id',
            'status' => 'required|in:' . implode(',', OrderItemStatus::getAll()),
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $orderId = Crypt::decrypt($request->order_id);
            $order = Order::findOrFail($orderId);

            DB::beginTransaction();

            $updatedItems = 0;
            $failedItems = 0;
            $messages = [];

            foreach ($request->item_ids as $itemId) {
                $orderItem = OrderItem::find($itemId);

                if (!$orderItem || $orderItem->order_id !== $order->id) {
                    $failedItems++;
                    $messages[] = "Item #{$itemId} not found or doesn't belong to this order";
                    continue;
                }

                if (!$this->isValidStatusTransition($orderItem->status, $request->status)) {
                    $failedItems++;
                    $messages[] = "Item #{$itemId}: Invalid status transition";
                    continue;
                }

                $oldStatus = $orderItem->status;
                $orderItem->update([
                    'status' => $request->status,
                    'updated_at' => now()
                ]);

                OrderItemStatusHistory::create([
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'status' => $request->status,
                    'notes' => $request->notes ?? "Bulk update: Status changed from {$oldStatus} to {$request->status}",
                    'created_by' => auth()->id()
                ]);

                $updatedItems++;
            }

            // Update order overall status
            $this->updateOrderOverallStatus($order);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->withProperties([
                    'items_updated' => $updatedItems,
                    'status' => $request->status
                ])
                ->log('Bulk product status update');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Updated {$updatedItems} item(s) successfully",
                'failed' => $failedItems,
                'details' => $messages,
                'order' => $order->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bulk items: ' . $e->getMessage()
            ], 500);
        }
    }

    // Submit Return Request
    public function submitReturnRequest(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'return_reason' => 'required|string|max:500',
            'return_reason_details' => 'nullable|string|max:1000',
            'return_image' => 'nullable|image|max:5120' // 5MB
        ]);

        try {
            $item = OrderItem::findOrFail($request->item_id);

            // Check if item can be returned
            if ($item->status->value !== 'delivered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only delivered items can be returned'
                ], 400);
            }

            // Check if return already requested
            if ($item->status->value === 'return_requested') {
                return response()->json([
                    'success' => false,
                    'message' => 'Return already requested for this item'
                ], 400);
            }

            // Handle image upload
            $returnImage = null;
            if ($request->hasFile('return_image')) {
                $image = $request->file('return_image');
                $imageName = 'return_' . time() . '_' . $item->id . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/returns', $imageName);
                $returnImage = $imageName;
            }

            // Update order item
            $item->update([
                'status' => OrderItemStatus::RETURN_REQUESTED->value,
                'return_reason' => $request->return_reason . ($request->return_reason_details ? ': ' . $request->return_reason_details : ''),
                'return_image' => $returnImage,
                'return_requested_at' => now()
            ]);

            // Log status history
            OrderItemStatusHistory::create([
                'order_id' => $item->order_id,
                'order_item_id' => $item->id,
                'status' => OrderItemStatus::RETURN_REQUESTED->value,
                'notes' => $request->return_reason . ($request->return_reason_details ? ': ' . $request->return_reason_details : ''),
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Return request submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit return request: ' . $e->getMessage()
            ], 500);
        }
    }

    // Approve Return
    public function approveReturn(Request $request)
    {
        return $this->processReturnAction($request, 'approve');
    }

    // Reject Return
    public function rejectReturn(Request $request)
    {
        return $this->processReturnAction($request, 'reject');
    }

    // Process Return Action
    private function processReturnAction(Request $request, $action)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:order_items,id',
            'refund_amount' => $action === 'approve' ? 'required|numeric|min:0' : 'nullable',
            'reason' => $action === 'reject' ? 'required|string|max:500' : 'nullable',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $item = OrderItem::findOrFail($request->item_id);

            if ($item->status->value !== 'return_requested') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not in return requested status'
                ], 400);
            }

            if ($action === 'approve') {
                // Process approved return
                $item->update([
                    'status' => OrderItemStatus::RETURNED->value,
                    'returned_at' => now(),
                    'refund_amount' => $request->refund_amount,
                    'refund_status' => 'pending'
                ]);

                // Log status history
                OrderItemStatusHistory::create([
                    'order_id' => $item->order_id,
                    'order_item_id' => $item->id,
                    'status' => OrderItemStatus::RETURNED->value,
                    'notes' => $request->notes ?? 'Return approved. Refund amount: ₹' . $request->refund_amount,
                    'created_by' => auth()->id()
                ]);

                // Process refund if applicable
                if ($request->refund_amount > 0) {
                    $this->processReturnRefund($item, $request->refund_amount);
                }

                // Restock product
                $product = $item->product;
                if ($product) {
                    $previousStock = $product->product_stock;
                    $newStock = $previousStock + $item->quantity;

                    $product->update(['product_stock' => $newStock]);

                    StockHistory::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'Restock',
                        'quantity' => $item->quantity,
                        'previous_stock' => $previousStock,
                        'new_stock' => $newStock,
                        'total_stock' => $newStock,
                        'reason' => 'Return approved - Order #' . $item->order_id
                    ]);
                }
            } else {
                // Process rejected return
                $item->update([
                    'status' => OrderItemStatus::DELIVERED->value
                ]);

                OrderItemStatusHistory::create([
                    'order_id' => $item->order_id,
                    'order_item_id' => $item->id,
                    'status' => OrderItemStatus::DELIVERED->value,
                    'notes' => $request->notes ?? 'Return rejected. Reason: ' . $request->reason,
                    'created_by' => auth()->id()
                ]);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($item->order)
                ->withProperties([
                    'item_id' => $item->id,
                    'action' => $action,
                    'refund_amount' => $request->refund_amount ?? 0
                ])
                ->log('Return request ' . $action . 'ed');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Return request ' . $action . 'ed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process return: ' . $e->getMessage()
            ], 500);
        }
    }

    // Submit Replace Request
    public function submitReplaceRequest(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'replace_reason' => 'required|string|max:500',
            'replace_reason_details' => 'nullable|string|max:1000',
            'replace_image' => 'nullable|image|max:5120'
        ]);

        try {
            $item = OrderItem::findOrFail($request->item_id);

            // Check if item can be replaced
            if ($item->status->value !== 'delivered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only delivered items can be replaced'
                ], 400);
            }

            // Check if replace already requested
            if ($item->status->value === 'replace_requested') {
                return response()->json([
                    'success' => false,
                    'message' => 'Replace already requested for this item'
                ], 400);
            }

            // Handle image upload
            $replaceImage = null;
            if ($request->hasFile('replace_image')) {
                $image = $request->file('replace_image');
                $imageName = 'replace_' . time() . '_' . $item->id . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/replaces', $imageName);
                $replaceImage = $imageName;
            }

            // Update order item
            $item->update([
                'status' => OrderItemStatus::REPLACE_REQUESTED->value,
                'replace_reason' => $request->replace_reason . ($request->replace_reason_details ? ': ' . $request->replace_reason_details : ''),
                'replace_image' => $replaceImage,
                'replace_requested_at' => now()
            ]);

            // Log status history
            OrderItemStatusHistory::create([
                'order_id' => $item->order_id,
                'order_item_id' => $item->id,
                'status' => OrderItemStatus::REPLACE_REQUESTED->value,
                'notes' => $request->replace_reason . ($request->replace_reason_details ? ': ' . $request->replace_reason_details : ''),
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Replace request submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit replace request: ' . $e->getMessage()
            ], 500);
        }
    }

    // Approve Replace
    public function approveReplace(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'new_product_id' => 'nullable|exists:products,id',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            $item = OrderItem::findOrFail($request->item_id);

            if ($item->status->value !== 'replace_requested') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not in replace requested status'
                ], 400);
            }

            // Get replacement product (same or different)
            $newProductId = $request->new_product_id ?: $item->product_id;
            $newProduct = Product::findOrFail($newProductId);

            // Update order item
            $item->update([
                'status' => OrderItemStatus::REPLACE_REQUESTED->value,
                'replace_verified_by_admin' => true,
                'admin_verified_at' => now(),
                'admin_verification_notes' => $request->notes,
                'verified_by' => auth()->id(),
                'new_product_id' => $newProductId
            ]);

            // Log status history
            OrderItemStatusHistory::create([
                'order_id' => $item->order_id,
                'order_item_id' => $item->id,
                'status' => OrderItemStatus::REPLACE_REQUESTED->value,
                'notes' => 'Replace request approved. New product: ' . $newProduct->name . ($request->notes ? ' - ' . $request->notes : ''),
                'created_by' => auth()->id()
            ]);

            // Create replacement order
            $replacementOrder = $this->createReplacementOrder1($item->order, $item, $newProduct);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Replace request approved successfully',
                'replacement_order_id' => $replacementOrder->id,
                'replacement_order_no' => $replacementOrder->order_no
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve replace request: ' . $e->getMessage()
            ], 500);
        }
    }

    // Reject Replace
    public function rejectReplace(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            $item = OrderItem::findOrFail($request->item_id);

            if ($item->status->value !== 'replace_requested') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not in replace requested status'
                ], 400);
            }

            // Update order item
            $item->update([
                'status' => OrderItemStatus::DELIVERED->value,
                'replace_verified_by_admin' => false,
                'admin_verified_at' => now(),
                'admin_verification_notes' => $request->reason
            ]);

            // Log status history
            OrderItemStatusHistory::create([
                'order_id' => $item->order_id,
                'order_item_id' => $item->id,
                'status' => OrderItemStatus::DELIVERED->value,
                'notes' => 'Replace request rejected. Reason: ' . $request->reason . ($request->notes ? ' - ' . $request->notes : ''),
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Replace request rejected successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject replace request: ' . $e->getMessage()
            ], 500);
        }
    }

    // Create Replacement Order
    private function createReplacementOrder1($originalOrder, $orderItem, $newProduct)
    {
        $orderCount = Order::whereDate('created_at', today())->count();
        $orderNo = 'REP' . date('Ymd') . str_pad($orderCount + 1, 6, '0', STR_PAD_LEFT);

        $replacementOrder = Order::create([
            'user_id' => $originalOrder->user_id,
            'order_no' => $orderNo,
            'total_amount' => 0,
            'status' => OrderStatus::PENDING,
            'original_order_id' => $originalOrder->id,
            'original_order_item_id' => $orderItem->id,
            'is_replacement_order' => true,
            'replacement_notes' => 'Free replacement for order #' . $originalOrder->order_no,
            'date' => now(),
        ]);

        OrderItem::create([
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
            'status' => OrderItemStatus::PENDING,
            'is_replacement' => true,
            'original_order_id' => $originalOrder->id,
            'original_order_item_id' => $orderItem->id,
        ]);

        return $replacementOrder;
    }

    public function dispatchItem(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'item_id' => 'required|exists:order_items,id',
            'courier_id' => 'required|exists:couriers,id',
            'tracking_number' => 'required|string|max:100',
            'estimated_days' => 'required|integer|min:1',
            'shipping_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        try {
            $orderId = Crypt::decrypt($request->order_id);
            $order = Order::findOrFail($orderId);
            $orderItem = OrderItem::findOrFail($request->item_id);

            // Verify the item belongs to this order
            if ($orderItem->order_id !== $order->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order item does not belong to this order'
                ], 400);
            }

            // Create shipment
            $shipment = Shipment::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'courier_id' => $request->courier_id,
                'tracking_number' => $request->tracking_number,
                'status' => DeliveryStatus::PICKED_UP->value,
                'shipped_at' => $request->shipping_date,
                'estimated_delivery_at' => Carbon::parse($request->shipping_date)->addDays((int) $request->estimated_days),
                'notes' => $request->notes,
                'recipient_name' => $order->shippingAddress->name ?? $order->user->name,
                'recipient_phone' => $order->shippingAddress->phone ?? $order->user->phone,
                'delivery_address' => $order->shippingAddress->full_address ?? '',
                'last_status_check_at' => now(),
                'metadata' => [
                    'estimated_days' => $request->estimated_days,
                    'dispatched_by' => auth()->id()
                ]
            ]);

            // Create status history
            ShipmentStatusHistory::create([
                'shipment_id' => $shipment->id,
                'status' => DeliveryStatus::PICKED_UP->value,
                'notes' => 'Package picked up by courier',
                'metadata' => [
                    'tracking_number' => $request->tracking_number,
                    'courier_id' => $request->courier_id
                ],
                'created_by' => auth()->id()
            ]);

            // Update order item status
            $orderItem->updateStatus(
                OrderItemStatus::SHIPPED->value,
                "Shipped via courier. Tracking: {$request->tracking_number}",
                [
                    'tracking_number' => $request->tracking_number,
                    'courier_id' => $request->courier_id
                ]
            );

            // Link shipment to order item
            $orderItem->update([
                'shipment_id' => $shipment->id,
                'tracking_number' => $request->tracking_number
            ]);

            // Update order shipping status
            $this->updateOrderShippingStatus($order);

            return response()->json([
                'success' => true,
                'message' => 'Product dispatched successfully',
                'shipment' => $shipment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Bulk Dispatch
    public function dispatchBulk(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'courier_id' => 'required|exists:couriers,id',
            'shipping_date' => 'required|date',
            'items' => 'required|array',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.tracking_number' => 'required|string|max:100',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $orderId = Crypt::decrypt($request->order_id);
            $order = Order::findOrFail($orderId);
            $courier = Courier::findOrFail($request->courier_id);

            $shipments = [];

            foreach ($request->items as $itemData) {
                $orderItem = OrderItem::findOrFail($itemData['order_item_id']);

                // Verify the item belongs to this order
                if ($orderItem->order_id !== $order->id) {
                    throw new \Exception("Order item does not belong to this order");
                }

                // Create shipment
                $shipment = Shipment::create([
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'courier_id' => $courier->id,
                    'tracking_number' => $itemData['tracking_number'],
                    'status' => DeliveryStatus::PICKED_UP->value,
                    'shipped_at' => $request->shipping_date,
                    'estimated_delivery_at' => Carbon::parse($request->shipping_date)->addDays($courier->estimated_delivery_days),
                    'notes' => $request->notes,
                    'recipient_name' => $order->shippingAddress->name ?? $order->user->name,
                    'recipient_phone' => $order->shippingAddress->phone ?? $order->user->phone,
                    'delivery_address' => $order->shippingAddress->full_address ?? '',
                    'last_status_check_at' => now(),
                    'metadata' => [
                        'estimated_days' => $courier->estimated_delivery_days,
                        'dispatched_by' => auth()->id()
                    ]
                ]);

                // Create status history
                ShipmentStatusHistory::create([
                    'shipment_id' => $shipment->id,
                    'status' => DeliveryStatus::PICKED_UP->value,
                    'notes' => 'Package picked up by courier',
                    'metadata' => [
                        'tracking_number' => $itemData['tracking_number'],
                        'courier_id' => $courier->id
                    ],
                    'created_by' => auth()->id()
                ]);

                // Update order item status
                $orderItem->updateStatus(
                    OrderItemStatus::SHIPPED->value,
                    "Shipped via {$courier->name}. Tracking: {$itemData['tracking_number']}",
                    [
                        'tracking_number' => $itemData['tracking_number'],
                        'courier_id' => $courier->id
                    ]
                );

                // Link shipment to order item
                $orderItem->update([
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $itemData['tracking_number'],
                    'courier_name' => $courier->name
                ]);

                $shipments[] = $shipment;
            }

            // Update order shipping status
            $this->updateOrderShippingStatus($order);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($shipments) . ' items dispatched successfully',
                'shipments' => $shipments
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch items: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateOrderTotals($order)
    {
        $activeItems = $order->orderItems()->where('status', '!=', OrderItemStatus::CANCELLED)->get();

        $newTotal = $activeItems->sum('total_amount');
        $newSubtotal = $activeItems->sum(function ($item) {
            return $item->amount * $item->quantity;
        });

        $order->update([
            'total_amount' => $newTotal,
            'sub_total' => $newSubtotal
        ]);
    }

    private function updateOrderDeliveryStatus(Order $order): void
    {
        $totalItems = $order->orderItems()->count();
        $deliveredItems = $order->orderItems()
            ->where('status', OrderItemStatus::DELIVERED->value)
            ->count();

        $allDelivered = $deliveredItems === $totalItems;

        $order->update([
            'all_items_delivered' => $allDelivered,
            'delivery_completed_at' => $allDelivered ? now() : null,
            'delivered_shipments_count' => Shipment::where('order_id', $order->id)
                ->where('status', DeliveryStatus::DELIVERED->value)
                ->count()
        ]);

        if ($allDelivered) {
            $order->update(['status' => OrderStatus::COMPLETED]);
        }
    }

    private function processRefund($order)
    {
        $payment = $order->payment;
        $razorpayApi = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET_KEY'));

        try {
            $refund = $razorpayApi->payment
                ->fetch($payment->transaction_id)
                ->refund([
                    'amount' => $payment->amount * 100,
                    'speed' => 'normal',
                    'receipt' => 'REF_' . $payment->payment_no
                ]);

            $payment->update([
                'refund_id' => $refund->id,
                'refund_amount' => $payment->amount,
                'refunded_at' => now(),
                'refund_response' => json_encode($refund->toArray())
            ]);

            $order->update(['status' => OrderStatus::REFUNDED]);
        } catch (\Exception $e) {
            Log::error("Failed to process refund for order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    private function sendShipmentNotifications($order, $shipments)
    {
        // Notification logic here
    }

    private function isValidStatusTransition($currentStatus, $newStatus)
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

        return in_array($newStatus, $transitions[$currentStatus] ?? []);
    }

    private function updateOrderOverallStatus(Order $order)
    {
        $items = $order->orderItems;

        if ($items->where('status', 'cancelled')->count() == $items->count()) {
            $order->update(['status' => OrderStatus::CANCELLED]);
        } elseif ($items->where('status', 'delivered')->count() == $items->count()) {
            $order->update(['status' => OrderStatus::COMPLETED]);
        } elseif ($items->where('status', 'shipped')->count() > 0) {
            $order->update(['status' => OrderStatus::SHIPPED]);
        } else {
            $order->update(['status' => OrderStatus::PENDING]);
        }
    }

    public function adminVerifyReturn(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
            'refund_amount' => 'required_if:action,approve|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $orderItem = OrderItem::findOrFail($request->item_id);

            if ($request->action == 'approve') {
                $orderItem->update([
                    'return_verified_by_admin' => true,
                    'admin_verification_notes' => $request->notes,
                    'admin_verifier_id' => auth()->id(),
                    'admin_verified_at' => now(),
                    'refund_amount' => $request->refund_amount
                ]);

                $message = 'Return request verified successfully';
            } else {
                $orderItem->update([
                    'return_verified_by_admin' => false,
                    'admin_verification_notes' => $request->notes,
                    'admin_verifier_id' => auth()->id(),
                    'admin_verified_at' => now()
                ]);

                // If admin rejects, update status back to delivered
                $orderItem->update(['status' => OrderItemStatus::DELIVERED]);

                $message = 'Return request rejected successfully';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin verify return error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process verification'
            ], 500);
        }
    }

    /**
     * Shop verify return product
     */
    public function shopVerifyReturn(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'action' => 'required|in:approve,reject',
            'product_condition' => 'required_if:action,approve|string',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $orderItem = OrderItem::findOrFail($request->item_id);

            // Check if user is shop owner
            if (
                auth()->user()->type != UserType::SHOP ||
                auth()->user()->shop_id != $orderItem->shop_id
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to verify this product'
                ], 403);
            }

            if ($request->action == 'approve') {
                $orderItem->update([
                    'shop_verified' => true,
                    'product_condition' => $request->product_condition,
                    'shop_verification_notes' => $request->notes,
                    'shop_verifier_id' => auth()->id(),
                    'shop_verified_at' => now()
                ]);

                $message = 'Product verified successfully';
            } else {
                $orderItem->update([
                    'shop_verified' => false,
                    'shop_verification_notes' => $request->notes,
                    'shop_verifier_id' => auth()->id(),
                    'shop_verified_at' => now()
                ]);

                // If shop rejects, update status back to return_requested
                $orderItem->update(['status' => OrderItemStatus::RETURN_REQUESTED]);

                $message = 'Product verification rejected';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shop verify return error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process verification'
            ], 500);
        }
    }

    /**
     * Admin final approval for return
     */
    public function adminApproveReturn($request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $orderItem = OrderItem::with(['order.user', 'order.payment', 'product'])->findOrFail($request->item_id);

            if ($orderItem->status->value !== OrderItemStatus::RETURN_REQUESTED->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not in return requested status'
                ], 400);
            }

            if ($orderItem->return_verified_by_admin !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin verification not completed'
                ], 400);
            }

            if ($orderItem->shop_verified !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop verification not completed'
                ], 400);
            }

            $refundAmount = $orderItem->refund_amount ?? $orderItem->total_amount;

            if ($refundAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid refund amount'
                ], 400);
            }

            if ($request->action == 'approve') {
                $orderItem->update([
                    'return_approval' => true,
                    'return_approval_notes' => $request->notes,
                    'verified_by' => auth()->id(),
                    'return_approval_at' => now(),
                    'status' => OrderItemStatus::RETURNED->value,
                    'returned_at' => now(),
                    'refund_status' => 'processed',
                    'refunded_at' => now(),
                ]);

                $product = $orderItem->product;

                if ($product) {
                    $previousStock = $product->stock;

                    $newStock = $previousStock + $orderItem->quantity;

                    $product->update([
                        'stock' => $newStock,
                    ]);

                    StockHistory::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'Restock',
                        'quantity' => $orderItem->quantity,
                        'previous_stock' => $previousStock,
                        'new_stock' => $newStock,
                        'total_stock' => $newStock,
                    ]);

                    \Log::info("Stock updated for product #{$product->id}: +{$orderItem->quantity} items. New stock: {$newStock}");
                }

                OrderItemStatusHistory::create([
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'status' => OrderItemStatus::RETURNED->value,
                    'notes' => 'Admin approved return and initiated refund' . ($request->notes ? ': ' . $request->notes : ''),
                    'metadata' => [
                        'return_approval' => true,
                        'refund_amount' => $refundAmount,
                        'approved_by' => auth()->id(),
                        'refunded_at' => now()->toDateTimeString(),
                    ],
                    'created_by' => auth()->id()
                ]);

                $this->processReturnRefund($orderItem->order->user_id, $refundAmount, $orderItem);

                $message = 'Return approved and refund processed successfully';

                $this->sendReturnApprovalNotification($orderItem, $refundAmount, true);

            } else {
                $orderItem->update([
                    'return_approval' => false,
                    'return_approval_notes' => $request->notes,
                    'verified_by' => auth()->id(),
                    'return_approval_at' => now(),
                ]);

                OrderItemStatusHistory::create([
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'status' => OrderItemStatus::RETURN_REQUESTED->value,
                    'notes' => 'Admin rejected return approval: ' . ($request->notes ?: 'Not approved'),
                    'metadata' => [
                        'return_approval' => false,
                        'rejection_reason' => $request->notes,
                        'rejected_by' => auth()->id(),
                    ],
                    'created_by' => auth()->id()
                ]);

                $message = 'Return approval rejected';

                $this->sendReturnApprovalNotification($orderItem, 0, false);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'item_id' => $orderItem->id,
                    'product_name' => $orderItem->product_name,
                    'return_approval' => $request->action == 'approve',
                    'return_approval_at' => $orderItem->return_approval_at?->format('Y-m-d H:i:s'),
                    'refund_amount' => $refundAmount,
                    'status' => $orderItem->status,
                    'status_description' => $orderItem->status_description,
                    'approval_notes' => $orderItem->return_approval_notes,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin approve return error: ' . $e->getMessage(), [
                'item_id' => $request->item_id,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process refund for returned item
     */
    private function processReturnRefund($userId, $amount, OrderItem $orderItem)
    {
        try {
            $wallet = Wallet::getUserWallet($userId);

            $wallet->addFunds(
                amount: $amount,
                type: 'credit',
                description: 'Refund for returned product: ' . $orderItem->product_name,
                metadata: [
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'product_name' => $orderItem->product_name,
                    'return_reason' => $orderItem->return_reason,
                    'refund_type' => 'return',
                ],
                reference: [
                    'id' => $orderItem->id,
                    'type' => 'order_item_return'
                ]
            );

            Log::info('Refund processed to wallet', [
                'user_id' => $userId,
                'amount' => $amount,
                'order_item_id' => $orderItem->id,
                'wallet_balance' => $wallet->balance,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to process return refund: ' . $e->getMessage(), [
                'user_id' => $userId,
                'amount' => $amount,
                'order_item_id' => $orderItem->id,
            ]);
            throw new \Exception('Failed to process refund: ' . $e->getMessage());
        }
    }

    private function sendReturnApprovalNotification(OrderItem $orderItem, $refundAmount, $isApproved)
    {
        try {
            $order = $orderItem->order;
            if (!$order || !$order->user_id) {
                return;
            }

            if ($isApproved) {
                $notificationMessage = "Your return request for '{$orderItem->product_name}' has been approved. " .
                    "Refund of ₹" . number_format($refundAmount, 2) . " has been credited to your wallet.";

                $notificationType = 'return_refund_processed';
                $notificationTitle = 'Return Approved & Refund Processed';
            } else {
                $notificationMessage = "Your return request for '{$orderItem->product_name}' has been rejected. " .
                    "Reason: " . ($orderItem->return_approval_notes ?: 'Not approved');

                $notificationType = 'return_rejected';
                $notificationTitle = 'Return Request Rejected';
            }

            $notificationData = [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'item_id' => $orderItem->id,
                'product_name' => $orderItem->product_name,
                'refund_amount' => $refundAmount,
                'is_approved' => $isApproved,
                'approval_notes' => $orderItem->return_approval_notes,
                'processed_at' => now()->format('Y-m-d H:i:s'),
            ];

            Notification::createNotification(
                $order->user_id,
                $notificationType,
                $notificationTitle,
                $notificationMessage,
                $notificationData
            );

        } catch (\Exception $e) {
            Log::error('Return approval notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Admin verify replace request
     */
    public function adminVerifyReplace(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'action' => 'required|in:approve,reject',
            'new_product_id' => 'nullable|exists:products,id',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $orderItem = OrderItem::findOrFail($request->item_id);

            if ($request->action == 'approve') {
                $orderItem->update([
                    'replace_verified_by_admin' => true,
                    'new_product_id' => $request->new_product_id,
                    'replace_admin_notes' => $request->notes,
                    'replace_admin_verifier_id' => auth()->id(),
                    'replace_admin_verified_at' => now()
                ]);

                $message = 'Replace request verified successfully';
            } else {
                $orderItem->update([
                    'replace_verified_by_admin' => false,
                    'replace_admin_notes' => $request->notes,
                    'replace_admin_verifier_id' => auth()->id(),
                    'replace_admin_verified_at' => now()
                ]);

                // If admin rejects, update status back to delivered
                $orderItem->update(['status' => OrderItemStatus::DELIVERED]);

                $message = 'Replace request rejected successfully';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin verify replace error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process verification'
            ], 500);
        }
    }

    /**
     * Admin final approval for replace
     */
    public function adminApproveReplace(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $orderItem = OrderItem::with(['order.user', 'product', 'newProduct'])->findOrFail($request->item_id);

            if ($orderItem->status->value !== OrderItemStatus::REPLACE_REQUESTED->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not in replace requested status'
                ], 400);
            }

            if ($orderItem->replace_verified_by_admin !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin verification not completed'
                ], 400);
            }

            if ($orderItem->shop_verified !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop verification not completed'
                ], 400);
            }

            if ($request->action == 'approve') {
                $newProduct = $orderItem->newProduct;
                if (!$newProduct) {
                    $newProduct = $orderItem->product;
                }

                $orderItem->update([
                    'replace_approval' => true,
                    'replace_approval_notes' => $request->notes,
                    'verified_by' => auth()->id(),
                    'replace_approval_at' => now(),
                    'status' => OrderItemStatus::REPLACED->value,
                ]);

                OrderItemStatusHistory::create([
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'status' => OrderItemStatus::REPLACED->value,
                    'notes' => 'Admin approved replacement' . ($request->notes ? ': ' . $request->notes : ''),
                    'metadata' => [
                        'replace_approval' => true,
                        'approved_by' => auth()->id(),
                        'new_product_id' => $newProduct->id,
                        'new_product_name' => $newProduct->name,
                    ],
                    'created_by' => auth()->id()
                ]);

                $replacementOrder = Order::createReplacementOrder(
                    $orderItem->order,
                    $orderItem,
                    $newProduct
                );

                if ($replacementOrder) {
                    $orderItem->update(['replacement_order_id' => $replacementOrder->id]);

                    // Send notification to user
                    $this->sendReplaceApprovalNotification($orderItem, $replacementOrder, true);
                }

                $message = 'Replace approved and replacement order created successfully';

            } else {
                // Reject the replacement
                $orderItem->update([
                    'replace_approval' => false,
                    'replace_approval_notes' => $request->notes,
                    'verified_by' => auth()->id(),
                    'replace_approval_at' => now(),
                ]);

                // Create status history
                OrderItemStatusHistory::create([
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'status' => OrderItemStatus::REPLACE_REQUESTED->value,
                    'notes' => 'Admin rejected replacement: ' . ($request->notes ?: 'Not approved'),
                    'metadata' => [
                        'replace_approval' => false,
                        'rejection_reason' => $request->notes,
                        'rejected_by' => auth()->id(),
                    ],
                    'created_by' => auth()->id()
                ]);

                $message = 'Replace approval rejected';

                // Send rejection notification
                $this->sendReplaceApprovalNotification($orderItem, null, false);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'item_id' => $orderItem->id,
                    'product_name' => $orderItem->product_name,
                    'replace_approval' => $request->action == 'approve',
                    'replace_approval_at' => $orderItem->replace_approval_at?->format('Y-m-d H:i:s'),
                    'status' => $orderItem->status,
                    'status_description' => $orderItem->status_description,
                    'approval_notes' => $orderItem->replace_approval_notes,
                    'replacement_order_id' => $orderItem->replacement_order_id,
                    'replacement_order_no' => isset($replacementOrder) ? $replacementOrder->order_no : null,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin approve replace error: ' . $e->getMessage(), [
                'item_id' => $request->item_id,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process approval: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sendReplaceApprovalNotification(OrderItem $orderItem, ?Order $replacementOrder, $isApproved)
    {
        try {
            $order = $orderItem->order;
            if (!$order || !$order->user_id) {
                return;
            }

            if ($isApproved && $replacementOrder) {
                $notificationMessage = "Your replacement request for '{$orderItem->product_name}' has been approved. " .
                    "Replacement order #{$replacementOrder->order_no} has been created.";

                $notificationType = 'replace_approved';
                $notificationTitle = 'Replacement Approved';

                $notificationData = [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'item_id' => $orderItem->id,
                    'product_name' => $orderItem->product_name,
                    'replacement_order_id' => $replacementOrder->id,
                    'replacement_order_no' => $replacementOrder->order_no,
                    'is_approved' => true,
                    'approval_notes' => $orderItem->replace_approval_notes,
                    'processed_at' => now()->format('Y-m-d H:i:s'),
                ];
            } else {
                $notificationMessage = "Your replacement request for '{$orderItem->product_name}' has been rejected. " .
                    "Reason: " . ($orderItem->replace_approval_notes ?: 'Not approved');

                $notificationType = 'replace_rejected';
                $notificationTitle = 'Replacement Request Rejected';

                $notificationData = [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'item_id' => $orderItem->id,
                    'product_name' => $orderItem->product_name,
                    'is_approved' => false,
                    'rejection_reason' => $orderItem->replace_approval_notes,
                    'processed_at' => now()->format('Y-m-d H:i:s'),
                ];
            }

            Notification::createNotification(
                $order->user_id,
                $notificationType,
                $notificationTitle,
                $notificationMessage,
                $notificationData
            );

        } catch (\Exception $e) {
            Log::error('Replace approval notification failed: ' . $e->getMessage());
        }
    }

    public function verificationDashboard()
    {
        $pendingReturns = OrderItem::where('status', OrderItemStatus::RETURN_REQUESTED)
            ->whereNull('return_verified_by_admin')
            ->count();

        $pendingReplaces = OrderItem::where('status', OrderItemStatus::REPLACE_REQUESTED)
            ->whereNull('replace_verified_by_admin')
            ->count();

        $shopVerifications = OrderItem::where('return_verified_by_admin', true)
            ->whereNull('shop_verified')
            ->whereHas('shop', function ($q) {
                $q->where('id', auth()->user()->shop_id);
            })
            ->count();

        return view('admin.verification.dashboard', compact(
            'pendingReturns',
            'pendingReplaces',
            'shopVerifications'
        ));
    }

    public function getReplaceDetails(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id'
        ]);

        $orderItem = OrderItem::with(['newProduct', 'product'])->find($request->item_id);

        return response()->json([
            'success' => true,
            'new_product' => $orderItem->newProduct ? [
                'id' => $orderItem->newProduct->id,
                'name' => $orderItem->newProduct->name,
                'sku' => $orderItem->newProduct->sku,
                'image' => $orderItem->newProduct->image_url
            ] : null,
            'original_product' => [
                'name' => $orderItem->product_name,
                'sku' => $orderItem->product->sku ?? 'N/A'
            ]
        ]);
    }

    public function getAvailableForReplacement(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id'
        ]);

        $orderItem = OrderItem::find($request->item_id);

        $products = Product::approvedWithShop()->where('shop_id', $orderItem->shop_id)
            ->where('id', '==', $orderItem->product_id)
            ->select('id', 'name', 'image')
            ->get()
            ->map(function ($product) {
                $product->image_url = $product->image ? url('storage/product/' . $product->image) : url('img/no-image.png');
                return $product;
            });

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    public function getVerificationDetails($request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id'
        ]);

        $orderItem = OrderItem::with([
            'adminVerifier:id,name',
            'shopVerifier:id,name',
            'returnApprover:id,name'
        ])->find($request->item_id);

        return response()->json([
            'success' => true,
            'details' => [
                'admin_verified' => $orderItem->return_verified_by_admin,
                'admin_verifier' => $orderItem->adminVerifier->name ?? null,
                'admin_verified_at' => $orderItem->admin_verified_at ? $orderItem->admin_verified_at->format('d M Y h:i A') : null,
                'admin_notes' => $orderItem->admin_verification_notes,

                'shop_verified' => $orderItem->shop_verified,
                'shop_verifier' => $orderItem->shopVerifier->name ?? null,
                'shop_verified_at' => $orderItem->shop_verified_at ? $orderItem->shop_verified_at->format('d M Y h:i A') : null,
                'product_condition' => $orderItem->product_condition,

                'return_approval' => $orderItem->return_approval,
                'approver' => $orderItem->returnApprover->name ?? null,
                'return_approval_at' => $orderItem->return_approval_at ? $orderItem->return_approval_at->format('d M Y h:i A') : null,

                'replace_verified' => $orderItem->replace_verified_by_admin,
                'replace_approval' => $orderItem->replace_approval,
            ]
        ]);
    }

    public function getVerificationStatus($request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id'
        ]);

        $orderItem = OrderItem::find($request->item_id);

        return response()->json([
            'success' => true,
            'status' => [
                'admin_verified' => $orderItem->return_verified_by_admin,
                'shop_verified' => $orderItem->shop_verified,
                'return_approved' => $orderItem->return_approval,
                'replace_verified' => $orderItem->replace_verified_by_admin,
                'replace_approved' => $orderItem->replace_approval,
                'current_status' => $orderItem->status->value
            ]
        ]);
    }
}
