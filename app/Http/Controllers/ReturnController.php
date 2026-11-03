<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Enums\OrderItemStatus;
use App\Enums\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        // Get statistics for dashboard
        $stats = [
            'total_pending' => OrderItem::where('status', OrderItemStatus::RETURN_REQUESTED->value)
                ->count(),

            'pending_admin' => OrderItem::where('status', OrderItemStatus::RETURN_REQUESTED->value)
                ->whereNull('return_verified_by_admin')
                ->count(),

            'pending_shop' => OrderItem::where('status', OrderItemStatus::RETURN_REQUESTED->value)
                ->where('return_verified_by_admin', true)
                ->whereNull('shop_verified')
                ->count(),

            'pending_final' => OrderItem::where('status', OrderItemStatus::RETURN_REQUESTED->value)
                ->where('return_verified_by_admin', true)
                ->where('shop_verified', true)
                ->whereNull('return_approval')
                ->count(),

            'completed' => OrderItem::where('status', OrderItemStatus::RETURNED->value)
                ->where('return_approval', true)
                ->count(),
        ];

        return view('backend.returns.index', compact('stats'));
    }

    public function datatable(Request $request)
    {
        $query = OrderItem::with(['order', 'user', 'product', 'shop'])
            ->where('status', OrderItemStatus::RETURN_REQUESTED->value)
            ->orWhere('status', OrderItemStatus::RETURNED->value)
            ->select('order_items.*');

        // Apply filters
        if ($request->has('status_filter') && $request->status_filter != 'all') {
            switch ($request->status_filter) {
                case 'pending_admin':
                    $query->whereNull('return_verified_by_admin');
                    break;
                case 'pending_shop':
                    $query->where('return_verified_by_admin', true)
                          ->whereNull('shop_verified');
                    break;
                case 'pending_final':
                    $query->where('return_verified_by_admin', true)
                          ->where('shop_verified', true)
                          ->whereNull('return_approval');
                    break;
                case 'approved':
                    $query->where('return_approval', true);
                    break;
                case 'rejected':
                    $query->where(function($q) {
                        $q->where('return_verified_by_admin', false)
                          ->orWhere('shop_verified', false)
                          ->orWhere('return_approval', false);
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
                        'view_url' => url('orders.view', ['order' => Crypt::encrypt($row->order_id)])
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
            ->addColumn('return_reason', function ($row) {
                return $row->return_reason;
            })
            ->addColumn('verification_status', function ($row) {
                if ($row->status->value == OrderItemStatus::RETURNED->value && $row->return_approval) {
                    return $row->refund_status == 'processed' ? 'approved' : 'processing_refund';
                }

                if ($row->return_verified_by_admin === false) {
                    return 'rejected';
                }

                if (is_null($row->return_verified_by_admin)) {
                    return 'pending_admin';
                }

                if ($row->return_verified_by_admin === true && is_null($row->shop_verified)) {
                    return 'pending_shop';
                }

                if ($row->shop_verified === false) {
                    return 'rejected';
                }

                if ($row->shop_verified === true && is_null($row->return_approval)) {
                    return 'pending_final';
                }

                if ($row->return_approval === false) {
                    return 'rejected';
                }

                return 'pending_admin';
            })
            ->addColumn('refund_amount', function ($row) {
                return $row->refund_amount;
            })
            ->addColumn('actions', function ($row) {
                $user = auth()->user();
                $canApprove = false;
                $canReject = false;

                // Check permissions based on user type and verification status
                if ($user->type == UserType::SUPERADMIN) {
                    if (is_null($row->return_verified_by_admin)) {
                        $canApprove = $canReject = true;
                    } elseif ($row->return_verified_by_admin === true &&
                             $row->shop_verified === true &&
                             is_null($row->return_approval)) {
                        $canApprove = $canReject = true;
                    }
                } elseif ($user->type == UserType::SHOP && $user->shop_id == $row->shop_id) {
                    if ($row->return_verified_by_admin === true && is_null($row->shop_verified)) {
                        $canApprove = $canReject = true;
                    }
                }

                return [
                    'id' => Crypt::encrypt($row->id),
                    'can_approve' => $canApprove,
                    'can_reject' => $canReject
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
                'adminVerifier',
                'shopVerifier',
                'returnApprover'
            ])->findOrFail($itemId);

            $user = auth()->user();
            $canAdminVerify = $user->type == UserType::SUPERADMIN && is_null($orderItem->return_verified_by_admin);
            $canShopVerify = $user->type == UserType::SHOP &&
                           $user->shop_id == $orderItem->shop_id &&
                           $orderItem->return_verified_by_admin === true &&
                           is_null($orderItem->shop_verified);
            $canFinalApprove = $user->type == UserType::SUPERADMIN &&
                             $orderItem->return_verified_by_admin === true &&
                             $orderItem->shop_verified === true &&
                             is_null($orderItem->return_approval);
            $canProcessRefund = $user->type == UserType::SUPERADMIN &&
                               $orderItem->return_approval === true &&
                               $orderItem->status->value == OrderItemStatus::RETURNED->value &&
                               $orderItem->refund_status != 'processed';

            $data = [
                'id' => $orderItem->id,
                'order' => [
                    'id' => $orderItem->order_id,
                    'order_no' => $orderItem->order->order_no ?? 'N/A',
                    'view_url' => url('orders.view', ['order' => Crypt::encrypt($orderItem->order_id)]),
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
                'return_reason' => $orderItem->return_reason,
                'return_reason_details' => $orderItem->return_reason_details,
                'return_image_url' => $orderItem->return_image_url,
                'requested_at' => $orderItem->return_requested_at ? $orderItem->return_requested_at->format('d M Y h:i A') : 'N/A',

                // Verification status
                'admin_verification' => [
                    'status' => is_null($orderItem->return_verified_by_admin) ? 'pending' :
                               ($orderItem->return_verified_by_admin ? 'completed' : 'rejected'),
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
                    'status' => is_null($orderItem->return_approval) ? 'pending' :
                               ($orderItem->return_approval ? 'completed' : 'rejected'),
                    'approver' => $orderItem->returnApprover->name ?? null,
                    'date' => $orderItem->return_approval_at ? $orderItem->return_approval_at->format('d M Y h:i A') : null,
                    'notes' => $orderItem->return_approval_notes
                ],

                'refund_status' => $orderItem->refund_status ?? 'pending',
                'refund_amount' => $orderItem->refund_amount ? number_format($orderItem->refund_amount, 2) : null,
                'refund_id' => $orderItem->refund_id,
                'refund_date' => $orderItem->refunded_at ? $orderItem->refunded_at->format('d M Y h:i A') : null,

                // Permissions
                'can_admin_verify' => $canAdminVerify,
                'can_shop_verify' => $canShopVerify,
                'can_final_approve' => $canFinalApprove,
                'can_process_refund' => $canProcessRefund
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading return details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load return details'
            ], 500);
        }
    }

    public function adminVerify(Request $request)
    {
        try {
            $itemId = Crypt::decrypt($request->item_id);
            $orderItem = OrderItem::findOrFail($itemId);

            if ($orderItem->status->value != OrderItemStatus::RETURN_REQUESTED->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid return request status'
                ], 400);
            }

            DB::beginTransaction();

            if ($request->action == 'approve') {
                $orderItem->adminApproveReturn(
                    $request->notes,
                    auth()->id(),
                    $request->refund_amount
                );

                $message = 'Return request approved successfully';
            } else {
                $orderItem->adminRejectReturn(
                    $request->notes,
                    auth()->id()
                );

                $message = 'Return request rejected successfully';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error admin verifying return: ' . $e->getMessage());
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

            if ($orderItem->status->value != OrderItemStatus::RETURN_REQUESTED->value ||
                $orderItem->return_verified_by_admin !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification state'
                ], 400);
            }

            DB::beginTransaction();

            if ($request->action == 'approve') {
                $orderItem->shopVerifyReturn(
                    true,
                    $request->notes,
                    $request->product_condition,
                    auth()->id()
                );

                $message = 'Product verified successfully';
            } else {
                $orderItem->shopVerifyReturn(
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
            Log::error('Error shop verifying return: ' . $e->getMessage());
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

            if ($orderItem->status->value != OrderItemStatus::RETURN_REQUESTED->value ||
                $orderItem->return_verified_by_admin !== true ||
                $orderItem->shop_verified !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid approval state'
                ], 400);
            }

            DB::beginTransaction();

            if ($request->action == 'approve') {
                $orderItem->approveReturn(
                    $request->notes,
                    auth()->id()
                );

                $message = 'Return approved successfully';
            } else {
                $orderItem->rejectReturn(
                    $request->notes,
                    auth()->id()
                );

                $message = 'Return rejected successfully';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error final approving return: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process final approval'
            ], 500);
        }
    }

    public function processRefund(Request $request)
    {
        try {
            $itemId = Crypt::decrypt($request->item_id);
            $orderItem = OrderItem::with('order.payment')->findOrFail($itemId);

            if ($orderItem->status->value != OrderItemStatus::RETURNED->value ||
                $orderItem->return_approval !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return not approved for refund'
                ], 400);
            }

            DB::beginTransaction();

            // Process refund through payment gateway
            $refundId = 'REF_' . time() . '_' . $orderItem->id;
            $refundAmount = $orderItem->refund_amount ?? $orderItem->total_amount;

            $orderItem->processRefund(
                $refundAmount,
                $refundId,
                'Refund processed for returned product',
                auth()->id()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing refund: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pending()
    {
        // Get only pending returns for admin verification
        $pendingReturns = OrderItem::with(['order', 'user', 'product'])
            ->where('status', OrderItemStatus::RETURN_REQUESTED->value)
            ->whereNull('return_verified_by_admin')
            ->orderBy('return_requested_at', 'asc')
            ->get();

        return view('backend.returns.pending', compact('pendingReturns'));
    }
}
