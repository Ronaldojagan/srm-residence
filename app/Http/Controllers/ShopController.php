<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveShopRequest;
use App\Http\Requests\SaveUserShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Models\BankDetails;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayoutShop;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopChangeRequest;
use App\Models\ShopDeactivationHistory;
use App\Models\ShopOrder;
use App\Service\ShopService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ShopController extends Controller
{
    private $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->middleware('auth');
        $this->shopService = $shopService;
    }
    public function create(Request $request)
    {
        return view('backend.shop.shop');
    }
    public function index(Request $request)
    {
        return $this->shopService->index($request);
    }
    public function store(SaveShopRequest $request)
    {
        return $this->shopService->store($request->validated());
    }
    public function edit(Shop $shop)
    {
        $bank = BankDetails::where('user_id', $shop->user_id)->first();
        return view('backend.shop.shop', [
            'edit' => $shop,
            'bank' => $bank,
        ]);
    }
    public function update(UpdateShopRequest $request, Shop $shop)
    {
        return $this->shopService->update($request->validated(), $shop);
    }
    public function destroy(Shop $shop)
    {
        return $this->shopService->destroy($shop);
    }

  public function view(Shop $shop)
{
    $categories = Category::where('status', true)->get();
    $bank = BankDetails::where('user_id', $shop->user_id)->first();
    $productCount = Product::where('shop_id', $shop->id)->count();

    $productCategoryCounts = Product::where('shop_id', $shop->id)
        ->select('category_id', DB::raw('count(*) as total'))
        ->groupBy('category_id')
        ->get();

    $productSubCategoryCounts = Product::where('shop_id', $shop->id)
        ->select('sub_category_id', DB::raw('count(*) as total'))
        ->groupBy('sub_category_id')
        ->get();

    $totalOrders = ShopOrder::where('shop_id', $shop->id)->count();

    $orderStatusCounts = DB::table('shop_orders as so')
        ->join('orders as o', 'so.order_id', '=', 'o.id')
        ->where('so.shop_id', $shop->id)
        ->select('o.status', DB::raw('count(*) as count'))
        ->groupBy('o.status')
        ->get();

    // Calculate shop earnings from ShopOrder
    $shopEarnings = ShopOrder::where('shop_id', $shop->id)
        ->select(
            DB::raw('SUM(total_amount) as total_revenue'),
            DB::raw('SUM(total_admin_commission) as total_admin_commission'),
            DB::raw('SUM(total_shop_commission) as total_shop_commission')
        )
        ->first();

    $payoutQuery = PayoutShop::where('shop_id', $shop->id);

    $totalPayouts = $payoutQuery->count();
    $paidPayouts = $payoutQuery->clone()->where('status', 'paid')->count();
    $pendingPayouts = $payoutQuery->clone()->whereIn('status', ['generated', 'pending'])->count();
    $failedPayouts = $payoutQuery->clone()->where('status', 'failed')->count();

    $totalAmount = $payoutQuery->clone()->sum('amount');
    $totalCommission = $payoutQuery->clone()->sum('commission');
    $totalPayable = $payoutQuery->clone()->sum('payable_amount');

    $payouts = PayoutShop::with('payout')
        ->where('shop_id', $shop->id)
        ->orderBy('created_at', 'desc')
        ->paginate(15);

    return view('backend.shop.shop_view', compact(
        'shop',
        'categories',
        'bank',
        'productCount',
        'productCategoryCounts',
        'productSubCategoryCounts',
        'totalOrders',
        'orderStatusCounts',
        'shopEarnings',
        'totalPayouts',
        'paidPayouts',
        'pendingPayouts',
        'failedPayouts',
        'totalAmount',
        'totalCommission',
        'totalPayable',
        'payouts'
    ));
}

    public function updateShopStatus(Request $request, Shop $shop)
    {
        try {
            $shop->status = !$shop->status;
            $shop->save();

            return redirect()->back()->with('status', [
                'type' => 'success',
                'message' => 'Shop status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('status', [
                'type' => 'error',
                'message' => 'Failed to update shop status: ' . $e->getMessage()
            ]);
        }
    }

   public function getShopOrders(Request $request, Shop $shop)
{
    // Use ShopOrder instead of Order to get shop-specific orders
    $query = ShopOrder::with(['order.user', 'order.statusHistory'])
        ->where('shop_id', $shop->id);

    // Apply date filter if provided
    if ($request->has('start_date') && $request->has('end_date')) {
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    return DataTables::of($query->latest())
        ->addColumn('action', function ($shopOrder) {
            return '<button class="btn btn-sm btn-outline-secondary view-order-items"
                            data-id="' . Crypt::encrypt($shopOrder->order_id) . '"
                            data-shop-id="' . Crypt::encrypt($shopOrder->shop_id) . '" title="View Items">
                        <i class="mdi mdi-eye-outline"></i>
                    </button>';
        })
        ->addColumn('order_no', function ($shopOrder) {
            return $shopOrder->order->order_no ?? '-';
        })
        ->addColumn('customer_name', function ($shopOrder) {
            return $shopOrder->order->user->name ?? '-';
        })
        ->addColumn('customer_email', function ($shopOrder) {
            return $shopOrder->order->user->email ?? '-';
        })
        ->addColumn('order_status', function ($shopOrder) {
            $status = $shopOrder->order->status ?? 'unknown';
            $color = match (strtolower($status)) {
                'pending' => 'warning',
                'inprogress' => 'info',
                'completed' => 'success',
                'cancelled' => 'danger',
                default => 'secondary',
            };

            return '<span class="badge bg-' . $color . '">' . ucfirst($status) . '</span>';
        })
        ->addColumn('shop_commission', function ($shopOrder) {
            return '₹ ' . number_format($shopOrder->total_shop_commission, 2);
        })
        ->addColumn('admin_commission', function ($shopOrder) {
            return '₹ ' . number_format($shopOrder->total_admin_commission, 2);
        })
        ->editColumn('created_at', function ($shopOrder) {
            return $shopOrder->created_at->format('d M Y');
        })
        ->editColumn('total_amount', function ($shopOrder) {
            return '₹ ' . number_format($shopOrder->total_amount, 2);
        })
        ->rawColumns(['order_status', 'action'])
        ->make(true);
}

public function getShopProducts(Request $request, Shop $shop)
    {
        $query = Product::with(['category', 'subCategory'])
            ->where('shop_id', $shop->id);

        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('approved')) {
            $query->where('approved', $request->approved);
        }

        return DataTables::of($query)
            ->addColumn('product_info', function ($product) {
                $img = $product->image ? url('storage/product/' . $product->image) : url('assets/images/default-product.png');
                return '<div class="d-flex align-items-center">
                        <img src="' . $img . '" class="rounded me-2" width="40" height="40" alt="' . $product->name . '">
                        <div>
                            <h6 class="mb-0">' . $product->name . '</h6>
                            <small class="text-muted">' . ($product->category->name ?? '') . '</small>
                            <small class="text-muted">' . ($product->subCategory->name ?? '') . '</small>
                        </div>
                    </div>';
            })
            ->addColumn('category_name', function ($product) {
                return $product->category->name;
            })
            ->editColumn('amount', function ($product) {
                return '₹ ' . number_format($product->amount, 2);
            })
            ->editColumn('discount_amount', function ($product) {
                return '₹ ' . number_format($product->discount_amount, 2);
            })
            ->addColumn('product_status', function ($row) {
                return match ($row->approved) {
                    1 => '<span class="badge bg-success float-start">Approved</span>',
                    0 => '<span class="badge bg-warning float-start">Pending</span>',
                    -1 => '<span class="badge bg-danger float-start">Rejected</span>',
                    default => '<span class="badge bg-secondary float-start">Unknown</span>',
                };
            })
            ->editColumn('status', function ($product) {
                $class = $product->status ? 'success' : 'danger';
                $label = $product->status ? 'Active' : 'Inactive';
                return '<span class="badge bg-' . $class . '">' . $label . '</span>';
            })
            ->addColumn('action', function ($product) {
                $buttons = '<button class="btn btn-sm btn-outline-secondary view-product-details"
                            data-id="' . Crypt::encrypt($product->id) . '" title="View Details">
                        <i class="mdi mdi-eye-outline"></i>
                    </button>';

                // Add approve/reject buttons if product is pending
                if ($product->approved == 0) {
                    $buttons .= '<button class="btn btn-sm btn-success ms-1 approve-product"
                                data-id="' . Crypt::encrypt($product->id) . '" title="Approve">
                            <i class="mdi mdi-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger ms-1 reject-product"
                                data-id="' . Crypt::encrypt($product->id) . '" title="Reject">
                            <i class="mdi mdi-close"></i>
                        </button>';
                }

                return $buttons;
            })
            ->rawColumns(['product_info', 'status', 'product_status', 'action','discount_amount'])
            ->make(true);
    }

    public function getOrderItems(Request $request, $orderId)
    {
        try {
            $decryptedOrderId = Crypt::decrypt($orderId);
            $shopId = $request->get('shop_id');

            \Log::info('getOrderItems called', [
                'order_id' => $decryptedOrderId,
                'shop_id' => $shopId,
                'encrypted_shop_id' => $request->get('shop_id')
            ]);

            $order = Order::with([
                'user',
                'shippingAddress',
                'precheckout',
                'payment'
            ])->findOrFail($decryptedOrderId);

            $shopOrder = null;
            $shopOrderItems = collect();

            if ($shopId) {
                try {
                    $decryptedShopId = Crypt::decrypt($shopId);
                } catch (\Exception $e) {
                    $decryptedShopId = $shopId;
                }

                \Log::info('Getting shop order for shop_id', [
                    'decrypted_shop_id' => $decryptedShopId,
                    'order_id' => $decryptedOrderId
                ]);

                $shopOrder = ShopOrder::where('order_id', $decryptedOrderId)
                    ->where('shop_id', $decryptedShopId)
                    ->first();

                \Log::info('Shop order found', ['shop_order' => $shopOrder ? $shopOrder->toArray() : null]);

                if ($shopOrder) {
                    $shopOrderItems = OrderItem::where('order_id', $decryptedOrderId)
                        ->where('shop_id', $decryptedShopId)
                        ->with(['product' => function($q) {
                            $q->withTrashed();
                        }, 'shop'])
                        ->get();

                    \Log::info('Shop order items count', ['count' => $shopOrderItems->count()]);

                    $order->setRelation('orderItems', $shopOrderItems);
                }
            } else {
                $allItems = $order->orderItems()
                    ->with(['product' => function($q) {
                        $q->withTrashed();
                    }, 'shop'])
                    ->get();

                $order->setRelation('orderItems', $allItems);
            }

            $data = [
                'order' => $order,
                'shopOrder' => $shopOrder,
                'orderItems' => $shopOrderItems->count() > 0 ? $shopOrderItems : $order->orderItems,
                'currency' => config('settings.currency_symbol', '₹'),
                'date_format' => config('settings.date_format', 'd M Y'),
            ];

            return response()->json([
                'success' => true,
                'html' => view('backend.shop.shop_order_item_modal', $data)->render(),
                'debug' => [
                    'order_id' => $decryptedOrderId,
                    'shop_id' => $shopId,
                    'order_items_count' => $order->orderItems->count(),
                    'shop_order_found' => $shopOrder ? true : false
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getOrderItems: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching order items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProductDetails(Request $request, $productId)
    {
        try {
            $product = Product::with(['category', 'subCategory', 'shop'])
                            ->findOrFail(Crypt::decrypt($productId));

            $data = [
                'product' => $product,
                'currency' => config('settings.currency_symbol', '₹'),
            ];

            return response()->json([
                'success' => true,
                'html' => view('backend.shop.product_details_modal', $data)->render()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProductStatus(Request $request)
    {
        try {
            $productId = Crypt::decrypt($request->product_id);
            $product = Product::findOrFail($productId);

            $product->approved = $request->status;
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating product status: ' . $e->getMessage()
            ], 500);
        }
    }

  public function getShopStats(Shop $shop, Request $request)
{
    try {
        // Default to last 30 days if no dates provided
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Validate dates
        if (!strtotime($startDate) || !strtotime($endDate)) {
            throw new \Exception("Invalid date format");
        }

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Get shop orders in date range - Use ShopOrder instead of Order
        $shopOrders = ShopOrder::where('shop_id', $shop->id)
            ->with('order') // Load the main order if needed
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Calculate totals from ShopOrder
        $totalRevenue = $shopOrders->sum('total_amount');
        $adminCommission = $shopOrders->sum('total_admin_commission');
        $shopCommission = $shopOrders->sum('total_shop_commission');
        $shopRevenue = $totalRevenue - $adminCommission; // Or use $shopCommission directly

        // Prepare chart data - group by day
        $chartData = $shopOrders->groupBy(function ($shopOrder) {
            return $shopOrder->created_at->format('j M');
        })->map(function ($dailyShopOrders) {
            $total = $dailyShopOrders->sum('total_amount');
            $commission = $dailyShopOrders->sum('total_admin_commission');
            $shopCommission = $dailyShopOrders->sum('total_shop_commission');
            return [
                'total' => $total,
                'commission' => $commission,
                'shop_commission' => $shopCommission,
                'shop' => $total - $commission
            ];
        });

        // Get order count from associated orders (distinct orders)
        $orderCount = $shopOrders->groupBy('order_id')->count();

        return response()->json([
            'success' => true,
            'total_revenue' => $totalRevenue,
            'admin_commission' => $adminCommission,
            'shop_commission' => $shopCommission,
            'shop_revenue' => $shopRevenue,
            'summary' => [
                'total_orders' => $orderCount,
                'total_shop_orders' => $shopOrders->count(),
                'average_order_value' => $shopOrders->count() > 0 ? $totalRevenue / $shopOrders->count() : 0,
                'average_shop_earning' => $shopOrders->count() > 0 ? $shopRevenue / $shopOrders->count() : 0
            ],
            'chart_data' => [
                'labels' => $chartData->keys()->toArray(),
                'total_revenue' => $chartData->pluck('total')->toArray(),
                'admin_commission' => $chartData->pluck('commission')->toArray(),
                'shop_commission' => $chartData->pluck('shop_commission')->toArray(),
                'shop_revenue' => $chartData->pluck('shop')->toArray()
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching stats: ' . $e->getMessage()
        ], 500);
    }
}

    public function getPayoutStats(Shop $shop, Request $request)
    {
        try {
            // Default to last 30 days if no dates provided
            $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            // Validate dates
            if (!strtotime($startDate) || !strtotime($endDate)) {
                throw new \Exception("Invalid date format");
            }

            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            // Get payouts in date range
            $payouts = PayoutShop::where('shop_id', $shop->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // Calculate totals
            $totalAmount = $payouts->sum('amount');
            $totalCommission = $payouts->sum('commission');
            $totalPayable = $payouts->sum('payable_amount');

            // Prepare chart data - group by week
            $chartData = $payouts->groupBy(function ($payout) {
                return $payout->created_at->format('W M Y'); // Week number + Month + Year
            })->map(function ($weeklyPayouts) {
                return [
                    'total' => $weeklyPayouts->sum('amount'),
                    'commission' => $weeklyPayouts->sum('commission'),
                    'payable' => $weeklyPayouts->sum('payable_amount')
                ];
            });

            return response()->json([
                'success' => true,
                'total_payouts' => $payouts->count(),
                'paid_payouts' => $payouts->where('status', 'paid')->count(),
                'pending_payouts' => $payouts->whereIn('status', ['generated', 'pending'])->count(),
                'failed_payouts' => $payouts->where('status', 'failed')->count(),
                'total_amount' => $totalAmount,
                'total_commission' => $totalCommission,
                'total_payable' => $totalPayable,
                'chart_data' => [
                    'labels' => $chartData->keys()->toArray(),
                    'total_amount' => $chartData->pluck('total')->toArray(),
                    'commission' => $chartData->pluck('commission')->toArray(),
                    'payable' => $chartData->pluck('payable')->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payout stats: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPayoutDetails(PayoutShop $payout)
    {
        try {
            $payout->load(['payout', 'shop', 'processedBy']);

            return response()->json([
                'success' => true,
                'html' => view('backend.shop.payout_details_modal', [
                    'payout' => $payout,
                    'currency' => '₹'
                ])->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payout details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processPayout(Request $request, PayoutShop $payout)
    {
        try {
            // Validate payout status
            if ($payout->status !== 'pending') {
                throw new \Exception("Only pending payouts can be processed");
            }

            // Process the payout (this would be your actual payout processing logic)
            $payout->update([
                'status' => 'paid',
                'processed_at' => now(),
                'processed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payout processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payout: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deactivate(Request $request, Shop $shop)
    {
        try {
            $request->validate([
                'deactivation_reason' => 'required|string|max:500'
            ]);

            $shop->update([
                'deactivation_status' => true,
            ]);

            // Record in history
            ShopDeactivationHistory::create([
                'shop_id' => $shop->id,
                'action_by' => auth()->id(),
                'reason' => $request->deactivation_reason,
                'action' => 'deactivated'
            ]);

            return redirect()->back()->with('status', [
                'type' => 'success',
                'message' => 'Shop deactivated successfully with reason recorded.'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('status', [
                'type' => 'error',
                'message' => 'Failed to deactivate shop: ' . $e->getMessage()
            ]);
        }
    }

    public function reactivate(Request $request, Shop $shop)
    {
        try {
            $request->validate([
                'reactivation_reason' => 'required|string|max:500'
            ]);

            $shop->update([
                'deactivation_status' => false,
            ]);

            ShopDeactivationHistory::create([
                'shop_id' => $shop->id,
                'action_by' => auth()->id(),
                'reason' => $request->reactivation_reason,
                'action' => 'reactivated'
            ]);

            return redirect()->back()->with('status', [
                'type' => 'success',
                'message' => 'Shop reactivated successfully with reason recorded.'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('status', [
                'type' => 'error',
                'message' => 'Failed to reactivate shop: ' . $e->getMessage()
            ]);
        }
    }

    public function updateShop(Shop $shop)
    {
        return $this->shopService->updateShop($shop);
    }

    public function deactivationHistory(Shop $shop)
    {
        try {
            $history = ShopDeactivationHistory::with('actionBy')
                ->where('shop_id', $shop->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('backend.shop.deactivation_history', compact('shop', 'history'));
        } catch (\Exception $e) {
            return redirect()->back()->with('status', [
                'type' => 'error',
                'message' => 'Failed to load deactivation history: ' . $e->getMessage()
            ]);
        }
    }

     public function updateStatus(Request $request)
    {
        return $this->shopService->updateStatus($request);
    }

    public function createShopForUser(SaveUserShopRequest $request)
    {
        return $this->shopService->createShopForUser($request->validated());
    }

    public function getChangeRequestDetails(ShopChangeRequest $shopChangeRequest)
    {
        return $this->shopService->getChangeRequestDetails($shopChangeRequest);
    }

    public function rejectChangeRequest(Request $request, ShopChangeRequest $shopChangeRequest)
    {
        return $this->shopService->rejectChangeRequest($request, $shopChangeRequest);
    }

    public function approveChangeRequest(Request $request, ShopChangeRequest $shopChangeRequest)
    {
        return $this->shopService->approveChangeRequest($request, $shopChangeRequest);
    }

    public function approveShop(Shop $shop)
    {
        DB::beginTransaction();

        try {

            if ($shop->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop is already approved.'
                ], 400);
            }

            $shop->update([
                'approved' => 1,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'rejection_reason' => null,
                'rejected_at' => null,
                'rejected_by' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shop approved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error approving shop: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve shop.'
            ], 500);
        }
    }

    public function rejectShop(Request $request, Shop $shop)
    {
        DB::beginTransaction();

        try {
            $reason = $request->input('reason');
            if (empty($reason) || strlen(trim($reason)) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a valid rejection reason (minimum 10 characters).'
                ], 422);
            }

            if ($shop->isRejected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop is already rejected.'
                ], 400);
            }

            $shop->update([
                'approved' => -1,
                'rejection_reason' => trim($reason),
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'approved_at' => null,
                'approved_by' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shop rejected successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error rejecting shop: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject shop.'
            ], 500);
        }
    }

    public function unapproveShop(Shop $shop)
    {
        DB::beginTransaction();

        try {
            if (!$shop->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop is not approved.'
                ], 400);
            }

            $shop->update([
                'approved' => 0,
                'approved_at' => null,
                'approved_by' => null,
                'rejection_reason' => null,
                'rejected_at' => null,
                'rejected_by' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shop unapproved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error unapproving shop: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to unapprove shop.'
            ], 500);
        }
    }
}
