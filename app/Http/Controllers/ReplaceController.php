<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Order;
use App\Enums\OrderItemStatus;
use App\Enums\UserType;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReplaceController extends Controller
{
    public function index(Request $request)
    {
        // Get statistics for dashboard
        $stats = [
            'total_pending' => OrderItem::where('status', OrderItemStatus::REPLACE_REQUESTED->value)
                ->count(),

            'pending_admin' => OrderItem::where('status', OrderItemStatus::REPLACE_REQUESTED->value)
                ->whereNull('replace_verified_by_admin')
                ->count(),

            'pending_shop' => OrderItem::where('status', OrderItemStatus::REPLACE_REQUESTED->value)
                ->where('replace_verified_by_admin', true)
                ->whereNull('shop_verified')
                ->count(),

            'pending_final' => OrderItem::where('status', OrderItemStatus::REPLACE_REQUESTED->value)
                ->where('replace_verified_by_admin', true)
                ->where('shop_verified', true)
                ->whereNull('replace_approval')
                ->count(),

            'pending_replacement' => OrderItem::where('status', OrderItemStatus::REPLACE_APPROVED->value)
                ->where('replace_approval', true)
                ->whereNull('replacement_order_id')
                ->count(),

            'completed' => OrderItem::where('status', OrderItemStatus::REPLACED->value)
                ->whereNotNull('replacement_order_id')
                ->count(),
        ];

        return view('backend.replace.index', compact('stats'));
    }

    public function datatable(Request $request)
    {
        $query = OrderItem::with(['order', 'user', 'product', 'shop', 'replacementOrder', 'replacementProduct'])
            ->where('status', OrderItemStatus::REPLACE_REQUESTED->value)
            ->orWhere('status', OrderItemStatus::REPLACE_APPROVED->value)
            ->orWhere('status', OrderItemStatus::REPLACED->value)
            ->select('order_items.*');

        // Apply filters
        if ($request->has('status_filter') && $request->status_filter != 'all') {
            switch ($request->status_filter) {
                case 'pending_admin':
                    $query->whereNull('replace_verified_by_admin');
                    break;
                case 'pending_shop':
                    $query->where('replace_verified_by_admin', true)
                          ->whereNull('shop_verified');
                    break;
                case 'pending_final':
                    $query->where('replace_verified_by_admin', true)
                          ->where('shop_verified', true)
                          ->whereNull('replace_approval');
                    break;
                case 'pending_replacement':
                    $query->where('replace_approval', true)
                          ->whereNull('replacement_order_id');
                    break;
                case 'processing':
                    $query->whereNotNull('replacement_order_id')
                          ->where('status', OrderItemStatus::REPLACE_APPROVED->value);
                    break;
                case 'completed':
                    $query->where('status', OrderItemStatus::REPLACED->value);
                    break;
                case 'rejected':
                    $query->where(function($q) {
                        $q->where('replace_verified_by_admin', false)
                          ->orWhere('shop_verified', false)
                          ->orWhere('replace_approval', false);
                    });
                    break;
            }
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                return $row->created_at;
            })
            ->addColumn('order', function ($row) {
                if ($row->order) {
                    return [
                        'id' => $row->order_id,
                        'order_no' => $row->order->order_no,
                        'view_url' => route('orders.view', ['order' => Crypt::encrypt($row->order_id)])
                    ];
                }
                return null;
            })
            ->addColumn('replacement_order', function ($row) {
                if ($row->replacementOrder) {
                    return [
                        'id' => $row->replacement_order_id,
                        'order_no' => $row->replacementOrder->order_no,
                        'view_url' => route('orders.view', ['order' => Crypt::encrypt($row->replacement_order_id)])
                    ];
                }
                return null;
            })
            ->addColumn('product', function ($row) {
                return [
                    'id' => $row->product_id,
                    'name' => $row->product_name,
                    'image_url' => $row->product_image ? url('storage/product/' . $row->product_image) : url('img/no-image.png'),
                    'quantity' => $row->quantity,
                    'amount' => number_format($row->amount, 2)
                ];
            })
            ->addColumn('replacement_product', function ($row) {
                if ($row->replacement_product_id && $row->replacementProduct) {
                    return [
                        'id' => $row->replacement_product_id,
                        'name' => $row->replacementProduct->name,
                        'image_url' => $row->replacementProduct->featured_image ? url('storage/product/' . $row->replacementProduct->featured_image) : url('img/no-image.png'),
                        'price' => number_format($row->replacementProduct->price, 2)
                    ];
                }
                return null;
            })
            ->addColumn('customer', function ($row) {
                if ($row->user) {
                    return [
                        'id' => $row->user_id,
                        'name' => $row->user->name,
                        'email' => $row->user->email,
                        'phone' => $row->user->phone
                    ];
                }
                return null;
            })
            ->addColumn('replace_reason', function ($row) {
                return $row->replace_reason;
            })
            ->addColumn('verification_status', function ($row) {
                if ($row->status->value == OrderItemStatus::REPLACED->value) {
                    return 'completed';
                }

                if ($row->replace_verified_by_admin === false) {
                    return 'rejected';
                }

                if (is_null($row->replace_verified_by_admin)) {
                    return 'pending_admin';
                }

                if ($row->replace_verified_by_admin === true && is_null($row->shop_verified)) {
                    return 'pending_shop';
                }

                if ($row->shop_verified === false) {
                    return 'rejected';
                }

                if ($row->shop_verified === true && is_null($row->replace_approval)) {
                    return 'pending_final';
                }

                if ($row->replace_approval === false) {
                    return 'rejected';
                }

                if ($row->replace_approval === true && is_null($row->replacement_order_id)) {
                    return 'pending_replacement';
                }

                if ($row->replace_approval === true && $row->replacement_order_id) {
                    return 'processing';
                }

                return 'pending_admin';
            })
            ->addColumn('price_difference', function ($row) {
                if ($row->replacement_product_id && $row->replacementProduct) {
                    $originalTotal = $row->amount * $row->quantity;
                    $replacementTotal = $row->replacementProduct->price * $row->replacement_quantity;
                    $difference = $replacementTotal - $originalTotal;

                    return [
                        'amount' => number_format(abs($difference), 2),
                        'type' => $difference >= 0 ? 'additional' : 'refund'
                    ];
                }
                return null;
            })
            ->addColumn('actions', function ($row) {
                $user = auth()->user();
                $canApprove = false;
                $canReject = false;
                $canSelectProduct = false;
                $canCreateOrder = false;

                // Check permissions based on user type and verification status
                if ($user->type == UserType::SUPERADMIN) {
                    if (is_null($row->replace_verified_by_admin)) {
                        $canApprove = $canReject = true;
                        $canSelectProduct = true;
                    } elseif ($row->replace_verified_by_admin === true &&
                             $row->shop_verified === true &&
                             is_null($row->replace_approval)) {
                        $canApprove = $canReject = true;
                    }
                } elseif ($user->type == UserType::SHOP && $user->shop_id == $row->shop_id) {
                    if ($row->replace_verified_by_admin === true && is_null($row->shop_verified)) {
                        $canApprove = $canReject = true;
                    }
                }

                // Check if can create replacement order
                if ($row->replace_approval === true &&
                    is_null($row->replacement_order_id) &&
                    !is_null($row->replacement_product_id) &&
                    $user->type == UserType::SUPERADMIN) {
                    $canCreateOrder = true;
                }

                return [
                    'id' => Crypt::encrypt($row->id),
                    'can_approve' => $canApprove,
                    'can_reject' => $canReject,
                    'can_select_product' => $canSelectProduct,
                    'can_create_order' => $canCreateOrder
                ];
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function details($orderItem)
    {
        try {
            $itemId = Crypt::decrypt($orderItem);
            $orderItem = OrderItem::with([
                'order',
                'user',
                'product',
                'shop',
                'replacementProduct',
                'replacementOrder',
                'adminVerifier',
                'shopVerifier',
                'replaceApprover'
            ])->findOrFail($itemId);

            $user = auth()->user();
            $canAdminVerify = $user->type == UserType::SUPERADMIN && is_null($orderItem->replace_verified_by_admin);
            $canShopVerify = $user->type == UserType::SHOP &&
                           $user->shop_id == $orderItem->shop_id &&
                           $orderItem->replace_verified_by_admin === true &&
                           is_null($orderItem->shop_verified);
            $canFinalApprove = $user->type == UserType::SUPERADMIN &&
                             $orderItem->replace_verified_by_admin === true &&
                             $orderItem->shop_verified === true &&
                             is_null($orderItem->replace_approval);
            $canCreateOrder = $user->type == UserType::SUPERADMIN &&
                             $orderItem->replace_approval === true &&
                             is_null($orderItem->replacement_order_id) &&
                             !is_null($orderItem->replacement_product_id);
            $canSelectProduct = $user->type == UserType::SUPERADMIN &&
                              (is_null($orderItem->replace_verified_by_admin) ||
                               $orderItem->replace_verified_by_admin === true);

            // Calculate price difference
            $priceDifference = null;
            if ($orderItem->replacement_product_id && $orderItem->replacementProduct) {
                $originalTotal = $orderItem->amount * $orderItem->quantity;
                $replacementTotal = $orderItem->replacementProduct->price * $orderItem->replacement_quantity;
                $difference = $replacementTotal - $originalTotal;

                $priceDifference = [
                    'original_total' => number_format($originalTotal, 2),
                    'replacement_total' => number_format($replacementTotal, 2),
                    'difference_amount' => number_format(abs($difference), 2),
                    'difference_type' => $difference >= 0 ? 'additional' : 'refund',
                    'customer_pays' => $difference > 0 ? number_format($difference, 2) : '0.00',
                    'customer_gets_refund' => $difference < 0 ? number_format(abs($difference), 2) : '0.00'
                ];
            }

            $data = [
                'id' => $orderItem->id,
                'order' => [
                    'id' => $orderItem->order_id,
                    'order_no' => $orderItem->order->order_no ?? 'N/A',
                    'view_url' => route('orders.view', ['order' => Crypt::encrypt($orderItem->order_id)]),
                    'date' => $orderItem->order->created_at->format('d M Y h:i A') ?? 'N/A'
                ],
                'customer' => [
                    'id' => $orderItem->user_id,
                    'name' => $orderItem->user->name ?? 'Unknown',
                    'email' => $orderItem->user->email ?? 'N/A',
                    'phone' => $orderItem->user->phone ?? 'N/A'
                ],
                'product' => [
                    'id' => $orderItem->product_id,
                    'name' => $orderItem->product_name,
                    'image_url' => $orderItem->product_image ? url('storage/product/' . $orderItem->product_image) : url('img/no-image.png'),
                    'quantity' => $orderItem->quantity,
                    'amount' => number_format($orderItem->amount, 2)
                ],
                'replacement_product' => $orderItem->replacementProduct ? [
                    'id' => $orderItem->replacement_product_id,
                    'name' => $orderItem->replacementProduct->name,
                    'image_url' => $orderItem->replacementProduct->featured_image ? url('storage/product/' . $orderItem->replacementProduct->featured_image) : url('img/no-image.png'),
                    'price' => number_format($orderItem->replacementProduct->price, 2),
                    'quantity' => $orderItem->replacement_quantity,
                    'stock' => $orderItem->replacementProduct->stock_quantity
                ] : null,
                'replace_reason' => $orderItem->replace_reason,
                'replace_reason_details' => $orderItem->replace_reason_details,
                'replace_image_url' => $orderItem->replace_image_url,
                'requested_at' => $orderItem->replace_requested_at ? $orderItem->replace_requested_at->format('d M Y h:i A') : 'N/A',

                // Price difference information
                'price_difference' => $priceDifference,

                // Verification status
                'admin_verification' => [
                    'status' => is_null($orderItem->replace_verified_by_admin) ? 'pending' :
                               ($orderItem->replace_verified_by_admin ? 'completed' : 'rejected'),
                    'verifier' => $orderItem->adminVerifier->name ?? null,
                    'date' => $orderItem->admin_verified_at ? $orderItem->admin_verified_at->format('d M Y h:i A') : null,
                    'notes' => $orderItem->admin_verification_notes
                ],

                'shop_verification' => [
                    'status' => is_null($orderItem->shop_verified) ? 'pending' :
                               ($orderItem->shop_verified ? 'completed' : 'rejected'),
                    'verifier' => $orderItem->shopVerifier->name ?? null,
                    'date' => $orderItem->shop_verified_at ? $orderItem->shop_verified_at->format('d M Y h:i A') : null,
                    'notes' => $orderItem->shop_verification_notes,
                    'product_condition' => $orderItem->product_condition
                ],

                'final_approval' => [
                    'status' => is_null($orderItem->replace_approval) ? 'pending' :
                               ($orderItem->replace_approval ? 'completed' : 'rejected'),
                    'approver' => $orderItem->replaceApprover->name ?? null,
                    'date' => $orderItem->replace_approval_at ? $orderItem->replace_approval_at->format('d M Y h:i A') : null,
                    'notes' => $orderItem->replace_approval_notes
                ],

                'replacement_order' => $orderItem->replacementOrder ? [
                    'id' => $orderItem->replacement_order_id,
                    'order_no' => $orderItem->replacementOrder->order_no,
                    'status' => $orderItem->replacementOrder->status,
                    'view_url' => route('orders.view', ['order' => Crypt::encrypt($orderItem->replacement_order_id)]),
                    'created_at' => $orderItem->replacementOrder->created_at->format('d M Y h:i A')
                ] : null,

                // Permissions
                'can_admin_verify' => $canAdminVerify,
                'can_shop_verify' => $canShopVerify,
                'can_final_approve' => $canFinalApprove,
                'can_create_order' => $canCreateOrder,
                'can_select_product' => $canSelectProduct
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading replace details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load replace details'
            ], 500);
        }
    }

    public function adminVerify(Request $request)
    {
        try {
            $itemId = Crypt::decrypt($request->item_id);
            $orderItem = OrderItem::findOrFail($itemId);

            if ($orderItem->status->value != OrderItemStatus::REPLACE_REQUESTED->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid replace request status'
                ], 400);
            }

            DB::beginTransaction();

            if ($request->action == 'approve') {
                $orderItem->adminApproveReplace(
                    $request->notes,
                    auth()->id()
                );

                $message = 'Replace request approved successfully';
            } else {
                $orderItem->adminRejectReplace(
                    $request->notes,
                    auth()->id()
                );

                $message = 'Replace request rejected successfully';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error admin verifying replace: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process verification'
            ], 500);
        }
    }

    public function shopVerify(Request $request)
    {
        try {
            $itemId = Crypt::decrypt($request->item_id);
            $orderItem = OrderItem::findOrFail($itemId);

            if ($orderItem->status->value != OrderItemStatus::REPLACE_REQUESTED->value ||
                $orderItem->replace_verified_by_admin !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification state'
                ], 400);
            }

            DB::beginTransaction();

            if ($request->action == 'approve') {
                $orderItem->shopVerifyReplace(
                    true,
                    $request->notes,
                    $request->product_condition,
                    auth()->id()
                );

                $message = 'Product verified successfully';
            } else {
                $orderItem->shopVerifyReplace(
                    false,
                    $request->notes,
                    null,
                    auth()->id()
                );

                $message = 'Product verification rejected';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error shop verifying replace: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process verification'
            ], 500);
        }
    }

    public function finalApprove(Request $request)
    {
        try {
            $itemId = Crypt::decrypt($request->item_id);
            $orderItem = OrderItem::findOrFail($itemId);

            if ($orderItem->status->value != OrderItemStatus::REPLACE_REQUESTED->value ||
                $orderItem->replace_verified_by_admin !== true ||
                $orderItem->shop_verified !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid approval state'
                ], 400);
            }

            DB::beginTransaction();

            if ($request->action == 'approve') {
                $orderItem->approveReplace(
                    $request->notes,
                    auth()->id()
                );

                $message = 'Replace approved successfully';
            } else {
                $orderItem->rejectReplace(
                    $request->notes,
                    auth()->id()
                );

                $message = 'Replace rejected successfully';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error final approving replace: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process final approval'
            ], 500);
        }
    }

    public function searchReplacementProducts(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_id' => 'required',
                'search' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request'
                ], 400);
            }

            $itemId = Crypt::decrypt($request->item_id);
            $orderItem = OrderItem::findOrFail($itemId);

            $query = Product::where('status', 1)
                ->where('shop_id', $orderItem->shop_id)
                ->where('id', '!=', $orderItem->product_id); // Exclude the original product

            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('sku', 'like', '%' . $request->search . '%')
                      ->orWhereHas('category', function($q) use ($request) {
                          $q->where('name', 'like', '%' . $request->search . '%');
                      });
                });
            }

            $products = $query->select('id', 'name', 'sku', 'price', 'stock_quantity', 'featured_image')
                ->orderBy('name')
                ->limit(20)
                ->get()
                ->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'price' => number_format($product->price, 2),
                        'stock' => $product->stock_quantity,
                        'image_url' => $product->featured_image ? url('storage/product/' . $product->featured_image) : url('img/no-image.png')
                    ];
                });

            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching replacement products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search products'
            ], 500);
        }
    }

    public function selectReplacementProduct(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_id' => 'required',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data'
                ], 400);
            }

            $itemId = Crypt::decrypt($request->item_id);
            $orderItem = OrderItem::findOrFail($itemId);
            $product = Product::findOrFail($request->product_id);

            // Check if product belongs to the same shop
            if ($product->shop_id != $orderItem->shop_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected product must be from the same shop'
                ], 400);
            }

            // Check stock availability
            if ($product->stock_quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for selected product'
                ], 400);
            }

            DB::beginTransaction();

            $orderItem->selectReplacementProduct(
                $product->id,
                $request->quantity,
                $request->notes,
                auth()->id()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Replacement product selected successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error selecting replacement product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to select replacement product'
            ], 500);
        }
    }

    public function createReplacementOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_id' => 'required',
                'payment_method' => 'required|string',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data'
                ], 400);
            }

            $itemId = Crypt::decrypt($request->item_id);
            $orderItem = OrderItem::with(['user', 'product', 'replacementProduct', 'order'])->findOrFail($itemId);

            if ($orderItem->status->value != OrderItemStatus::REPLACE_APPROVED->value ||
                $orderItem->replace_approval !== true ||
                is_null($orderItem->replacement_product_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid state for creating replacement order'
                ], 400);
            }

            DB::beginTransaction();

            // Calculate price difference
            $originalTotal = $orderItem->amount * $orderItem->quantity;
            $replacementTotal = $orderItem->replacementProduct->price * $orderItem->replacement_quantity;
            $priceDifference = $replacementTotal - $originalTotal;

            // Create new order for replacement
            $order = new Order();
            $order->order_no = 'REPLACE_' . time() . '_' . $orderItem->order_id;
            $order->user_id = $orderItem->user_id;
            $order->total_amount = $replacementTotal;
            $order->payment_method = $request->payment_method;
            $order->status = $priceDifference > 0 ? OrderStatus::PENDING : OrderStatus::PENDING;
            $order->notes = $request->notes . ' | Replacement for Order #' . $orderItem->order->order_no;
            $order->created_by = auth()->id();
            $order->save();

            // Create order item for replacement product
            $newOrderItem = new OrderItem();
            $newOrderItem->order_id = $order->id;
            $newOrderItem->product_id = $orderItem->replacement_product_id;
            $newOrderItem->shop_id = $orderItem->shop_id;
            $newOrderItem->user_id = $orderItem->user_id;
            $newOrderItem->product_name = $orderItem->replacementProduct->name;
            $newOrderItem->product_image = $orderItem->replacementProduct->featured_image;
            $newOrderItem->quantity = $orderItem->replacement_quantity;
            $newOrderItem->amount = $orderItem->replacementProduct->price;
            $newOrderItem->status = $priceDifference > 0 ? OrderItemStatus::PENDING->value : OrderItemStatus::CONFIRMED->value;
            $newOrderItem->save();

            // Update the original order item with replacement order reference
            $orderItem->replacement_order_id = $order->id;
            $orderItem->status = OrderItemStatus::REPLACED->value;
            $orderItem->save();

            // If price difference is negative (replacement is cheaper), process refund
            if ($priceDifference < 0) {
                $refundAmount = abs($priceDifference);
                $refundId = 'REF_REPLACE_' . time() . '_' . $orderItem->id;

                $orderItem->processRefund(
                    $refundAmount,
                    $refundId,
                    'Refund for price difference in replacement',
                    auth()->id()
                );
            }

            // Update product stock
            $orderItem->replacementProduct->decrement('stock_quantity', $orderItem->replacement_quantity);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Replacement order created successfully',
                'order_id' => Crypt::encrypt($order->id),
                'order_no' => $order->order_no
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating replacement order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create replacement order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pending()
    {
        // Get only pending replacements for admin verification
        $pendingReplacements = OrderItem::with(['order', 'user', 'product'])
            ->where('status', OrderItemStatus::REPLACE_REQUESTED->value)
            ->whereNull('replace_verified_by_admin')
            ->orderBy('replace_requested_at', 'asc')
            ->get();

        return view('backend.replace.pending', compact('pendingReplacements'));
    }
}
