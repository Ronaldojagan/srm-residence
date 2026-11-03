<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Address;
use App\Models\DeliveryPoint;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PayoutShop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function index()
    {
        return view('backend.report.report');
    }

    public function getReportData(Request $request)
    {
        $type = $request->type;
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;

        switch ($type) {
            case 'user':
                return $this->getUserReportData($startDate, $endDate);
            case 'order':
                return $this->getOrderReportData($startDate, $endDate);
            case 'product':
                return $this->getProductReportData($startDate, $endDate);
            case 'shop':
                return $this->getShopReportData($startDate, $endDate);
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }

    private function getUserReportData($startDate, $endDate)
    {
        $query = User::where('type', UserType::USER);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalUsers = $query->count();
        $activeUsers = $query->clone()->where('status', 1)->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        // Order statistics for users
        $orderQuery = Order::with('user');
        if ($startDate && $endDate) {
            $orderQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalOrders = $orderQuery->count();
        $totalRevenue = $orderQuery->sum('total_amount');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Address statistics
        $addressQuery = Address::query();
        if ($startDate && $endDate) {
            $addressQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $totalAddresses = $addressQuery->count();

        // Order status distribution
        $orderStatuses = Order::selectRaw('status, count(*) as count')
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('status')
            ->pluck('count', 'status');

        // Monthly order chart data
        $monthlyOrders = Order::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'cards' => [
                [
                    'title' => 'Total Users',
                    'value' => $totalUsers,
                    'icon' => 'users',
                    'image' => url('img/report-card1.png'),
                ],
                [
                    'title' => 'Active Users',
                    'value' => $activeUsers,
                    'icon' => 'user-check',
                    'image' => url('img/report-card2.png'),
                ],
                [
                    'title' => 'Inactive Users',
                    'value' => $inactiveUsers,
                    'icon' => 'user-x',
                    'image' => url('img/report-card3.png'),
                ],
                [
                    'title' => 'Total Orders',
                    'value' => $totalOrders,
                    'icon' => 'shopping-cart',
                    'image' => url('img/report-card4.png'),
                ],
                [
                    'title' => 'Total Revenue',
                    'value' => number_format($totalRevenue, 2),
                    'icon' => 'dollar-sign',
                    'image' => url('img/report-card4.png'),
                ],
                [
                    'title' => 'Avg Order Value',
                    'value' => number_format($avgOrderValue, 2),
                    'icon' => 'credit-card',
                    'image' => url('img/report-card3.png'),
                ],
                [
                    'title' => 'Saved Addresses',
                    'value' => $totalAddresses,
                    'icon' => 'map-pin',
                    'image' => url('img/report-card2.png'),
                ]
            ],
            'charts' => [
                'order_status' => [
                    'labels' => $orderStatuses->keys()->map(function ($status) {
                        return ucfirst(str_replace('_', ' ', $status));
                    })->toArray(),
                    'data' => $orderStatuses->values()->toArray(),
                    'colors' => $orderStatuses->keys()->map(function ($status) {
                        return $this->getStatusColor($status);
                    })->toArray()
                ],
                'monthly_orders' => [
                    'labels' => $monthlyOrders->pluck('month')->toArray(),
                    'data' => $monthlyOrders->pluck('count')->toArray(),
                    'colors' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
                ]
            ]
        ]);
    }

    private function getOrderReportData($startDate, $endDate)
    {
        $query = Order::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalOrders = $query->count();
        $totalRevenue = $query->sum('total_amount');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Payment statistics
        $paymentQuery = Payment::query();
        if ($startDate && $endDate) {
            $paymentQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $totalPayments = $paymentQuery->count();
        $totalPaymentAmount = $paymentQuery->sum('amount');

        // Order status distribution
        $orderStatuses = Order::selectRaw('status, count(*) as count')
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('status')
            ->pluck('count', 'status');

        // Payment method distribution
        $paymentMethods = Payment::selectRaw('payment_method, count(*) as count')
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('payment_method')
            ->pluck('count', 'payment_method');

        return response()->json([
            'cards' => [
                [
                    'title' => 'Total Orders',
                    'value' => $totalOrders,
                    'icon' => 'shopping-cart',
                    'image' => url('img/report-card1.png'),
                ],
                [
                    'title' => 'Total Revenue',
                    'value' => number_format($totalRevenue, 2),
                    'icon' => 'dollar-sign',
                    'image' => url('img/report-card2.png'),
                ],
                [
                    'title' => 'Avg Order Value',
                    'value' => number_format($avgOrderValue, 2),
                    'icon' => 'credit-card',
                    'image' => url('img/report-card3.png'),
                ],
                [
                    'title' => 'Total Payments',
                    'value' => $totalPayments,
                    'icon' => 'credit-card',
                    'image' => url('img/report-card4.png'),
                ],
                [
                    'title' => 'Payment Amount',
                    'value' => number_format($totalPaymentAmount, 2),
                    'icon' => 'dollar-sign',
                    'image' => url('img/report-card4.png'),
                ]
            ],
            'charts' => [
                'order_status' => [
                    'labels' => $orderStatuses->keys()->map(function ($status) {
                        return ucfirst(str_replace('_', ' ', $status));
                    })->toArray(),
                    'data' => $orderStatuses->values()->toArray(),
                    'colors' => $orderStatuses->keys()->map(function ($status) {
                        return $this->getStatusColor($status);
                    })->toArray()
                ],
                'payment_methods' => [
                    'labels' => $paymentMethods->keys()->toArray(),
                    'data' => $paymentMethods->values()->toArray(),
                    'colors' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e']
                ]
            ]
        ]);
    }

    private function getProductReportData($startDate, $endDate)
    {
        $query = Product::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalProducts = $query->count();
        $activeProducts = $query->clone()->where('status', 1)->count();
        $inactiveProducts = $totalProducts - $activeProducts;
        $outOfStock = $query->clone()->where('product_stock', '<=', 0)->count();

        // Top selling products
        $topProducts = Product::withCount(['orderItems as total_sold' => function ($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->whereHas('order', function ($q2) use ($startDate, $endDate) {
                    $q2->whereBetween('created_at', [$startDate, $endDate]);
                });
            }
        }])
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Stock history
       $stockHistory = Product::selectRaw('
            SUM(CASE WHEN product_stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(CASE WHEN product_stock > 0 AND product_stock <= max_product_stock_alert THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN product_stock > max_product_stock_alert THEN 1 ELSE 0 END) as in_stock
        ')
        ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->first();

        return response()->json([
            'cards' => [
                [
                    'title' => 'Total Products',
                    'value' => $totalProducts,
                    'icon' => 'box',
                    'image' => url('img/report-card1.png'),
                ],
                [
                    'title' => 'Active Products',
                    'value' => $activeProducts,
                    'icon' => 'check-circle',
                    'image' => url('img/report-card2.png'),
                ],
                [
                    'title' => 'Inactive Products',
                    'value' => $inactiveProducts,
                    'icon' => 'x-circle',
                    'image' => url('img/report-card3.png'),
                ],
                [
                    'title' => 'Out of Stock',
                    'value' => $outOfStock,
                    'icon' => 'alert-triangle',
                    'image' => url('img/report-card4.png'),
                ],
                [
                    'title' => 'Low Stock',
                    'value' => $stockHistory->low_stock,
                    'icon' => 'battery',
                    'image' => url('img/report-card4.png'),
                ],
                [
                    'title' => 'In Stock',
                    'value' => $stockHistory->in_stock,
                    'icon' => 'battery-charging',
                    'image' => url('img/report-card3.png'),
                ]
            ],
            'charts' => [
                'top_products' => [
                    'labels' => $topProducts->pluck('name')->toArray(),
                    'data' => $topProducts->pluck('total_sold')->toArray(),
                    'colors' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
                ],
                'stock_status' => [
                    'labels' => ['Out of Stock', 'Low Stock', 'In Stock'],
                    'data' => [
                        $stockHistory->out_of_stock,
                        $stockHistory->low_stock,
                        $stockHistory->in_stock
                    ],
                    'colors' => ['#e74a3b', '#f6c23e', '#1cc88a']
                ]
            ]
        ]);
    }

   private function getShopReportData($startDate, $endDate)
{
    // Shop statistics
    $shopQuery = Shop::query();

    if ($startDate && $endDate) {
        $shopQuery->whereBetween('created_at', [$startDate, $endDate]);
    }

    $totalShops = $shopQuery->count();
    $activeShops = $shopQuery->clone()->where('status', 1)->count();
    $inactiveShops = $totalShops - $activeShops;

    // Shop order statistics based on OrderItem
    $orderItemQuery = OrderItem::query()->with(['order', 'shop']);

    if ($startDate && $endDate) {
        $orderItemQuery->whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        });
    }

    // Calculate shop-specific metrics from OrderItem
    $totalShopOrderItems = $orderItemQuery->count();
    $totalShopSales = $orderItemQuery->sum('total_amount');

    // Calculate commissions based on shop's commission percentage
    $shopSalesData = $orderItemQuery->get()
        ->groupBy('shop_id')
        ->map(function ($items, $shopId) {
            $shop = Shop::find($shopId);
            $totalSales = $items->sum('total_amount');
            $commissionRate = $shop ? $shop->commission_percentage : 0;
            $commission = ($totalSales * $commissionRate) / 100;

            return [
                'shop_id' => $shopId,
                'total_sales' => $totalSales,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commission,
                'shop_earnings' => $totalSales - $commission
            ];
        });

    $totalCommission = $shopSalesData->sum('commission_amount');
    $totalShopEarnings = $shopSalesData->sum('shop_earnings');

    // Get total platform orders
    $orderQuery = Order::query();
    if ($startDate && $endDate) {
        $orderQuery->whereBetween('created_at', [$startDate, $endDate]);
    }

    $totalPlatformOrders = $orderQuery->count();
    $totalPlatformRevenue = $orderQuery->sum('total_amount');

    // Top performing shops with detailed calculations
    $topShops = Shop::withCount(['orderItems as total_orders' => function ($q) use ($startDate, $endDate) {
        if ($startDate && $endDate) {
            $q->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        }
    }])
    ->withSum(['orderItems as total_sales' => function ($q) use ($startDate, $endDate) {
        if ($startDate && $endDate) {
            $q->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        }
    }], 'total_amount')
    ->with(['orderItems' => function ($q) use ($startDate, $endDate) {
        if ($startDate && $endDate) {
            $q->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        }
    }])
    ->orderByDesc('total_sales')
    ->limit(5)
    ->get()
    ->map(function ($shop) {
        $totalSales = $shop->total_sales ?? 0;
        $commissionRate = $shop->commission_percentage;
        $commission = ($totalSales * $commissionRate) / 100;

        return [
            'id' => $shop->id,
            'name' => $shop->name,
            'total_orders' => $shop->total_orders ?? 0,
            'total_sales' => $totalSales,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commission,
            'shop_earnings' => $totalSales - $commission,
            'status' => $shop->status,
            'image_url' => $shop->image_url
        ];
    });

    return response()->json([
        'cards' => [
            [
                'title' => 'Total Shops',
                'value' => $totalShops,
                'icon' => 'store',
                'image' => url('img/report-card1.png'),
            ],
            [
                'title' => 'Active Shops',
                'value' => $activeShops,
                'icon' => 'check-circle',
                'image' => url('img/report-card2.png'),
            ],
            [
                'title' => 'Inactive Shops',
                'value' => $inactiveShops,
                'icon' => 'x-circle',
                'image' => url('img/report-card3.png'),
            ],
            [
                'title' => 'Shop Order Items',
                'value' => $totalShopOrderItems,
                'icon' => 'shopping-cart',
                'image' => url('img/report-card4.png'),
            ],
            [
                'title' => 'Total Shop Sales',
                'value' => number_format($totalShopSales, 2),
                'icon' => 'dollar-sign',
                'image' => url('img/report-card4.png'),
            ],
            [
                'title' => 'Total Commission',
                'value' => number_format($totalCommission, 2),
                'icon' => 'percent',
                'image' => url('img/report-card3.png'),
            ],
            [
                'title' => 'Total Shop Earnings',
                'value' => number_format($totalShopEarnings, 2),
                'icon' => 'credit-card',
                'image' => url('img/report-card2.png'),
            ],
            [
                'title' => 'Total Platform Orders',
                'value' => $totalPlatformOrders,
                'icon' => 'package',
                'image' => url('img/report-card1.png'),
            ],
            [
                'title' => 'Total Platform Revenue',
                'value' => number_format($totalPlatformRevenue, 2),
                'icon' => 'trending-up',
                'image' => url('img/report-card1.png'),
            ]
        ],
        'charts' => [
            'top_shops' => [
                'labels' => $topShops->pluck('name')->toArray(),
                'data' => $topShops->pluck('total_sales')->toArray(),
                'colors' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
            ]
        ],
        'top_shops_details' => $topShops
    ]);
}

    private function getStatusColor($status)
    {
        $colors = [
            'pending' => '#f6c23e',
            'inprogress' => '#36b9cc',
            'completed' => '#1cc88a',
            'cancelled' => '#e74a3b'
        ];

        return $colors[strtolower($status)] ?? '#858796';
    }

    public function getReportTableData(Request $request)
    {
        $type = $request->type;
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;
        $filters = $request->filters ?? [];
        $filterLogic = $request->filter_logic ?? 'and';

        switch ($type) {
            case 'user':
                return $this->getUserTableData($startDate, $endDate, $filters, $filterLogic);
            case 'order':
                return $this->getOrderTableData($startDate, $endDate, $filters, $filterLogic);
            case 'deliverymans':
                return $this->getDeliveryMenTableData($startDate, $endDate, $filters, $filterLogic);
            case 'deliverypoints':
                return $this->getDeliveryPointsTableData($startDate, $endDate, $filters, $filterLogic);
            case 'product':
                return $this->getProductTableData($startDate, $endDate, $filters, $filterLogic);
            case 'shop':
                return $this->getShopTableData($startDate, $endDate, $filters, $filterLogic);
            case 'shoppayout':
                return $this->getShopPayoutTableData($startDate, $endDate, $filters, $filterLogic);
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }

    private function getUserTableData($startDate, $endDate, $filters, $filterLogic)
    {
        $query = User::where('type', UserType::USER)
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'status',
                'created_at'
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($filters) {
            $filters = is_string($filters) ? json_decode($filters, true) : $filters;
            $logic = $filterLogic;

            $query->where(function ($q) use ($filters, $logic) {
                foreach ($filters as $filter) {
                    FilterController::applyOrderFilter($q, $filter, $logic);
                }
            });
        }

        return DataTables::of($query)
            ->addColumn('created_at', function ($user) {
                return $user->created_at->format('d-m-Y h:i A');
            })
            ->addColumn('status', function ($user) {
                return $user->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    private function getDeliveryMenTableData($startDate, $endDate, $filters, $filterLogic)
    {
        $query = User::with('deliveryPoint')
            ->where('type', UserType::DELIVERY)
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'status',
                'created_at',
                'delivery_point_id'
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($filters) {
            $filters = is_string($filters) ? json_decode($filters, true) : $filters;
            $logic = $filterLogic;

            $query->where(function ($q) use ($filters, $logic) {
                foreach ($filters as $filter) {
                    FilterController::applyOrderFilter($q, $filter, $logic);
                }
            });
        }

        return DataTables::of($query)
            ->addColumn('created_at', function ($deliveryMan) {
                return $deliveryMan->created_at->format('d-m-Y h:i A');
            })
            ->addColumn('status', function ($deliveryMan) {
                return $deliveryMan->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('delivery_location', function ($deliveryMan) {
                $point = $deliveryMan->deliveryPoint;

                return $point
                    ? $point->location . ' - ' . $point->sub_location
                    : '-';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    private function getDeliveryPointsTableData($startDate, $endDate, $filters, $filterLogic)
    {
        $query = DeliveryPoint::where('status', true)
            ->select([
                'id',
                'location',
                'sub_location',
                'status',
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($filters) {
            $filters = is_string($filters) ? json_decode($filters, true) : $filters;
            $logic = $filterLogic;

            $query->where(function ($q) use ($filters, $logic) {
                foreach ($filters as $filter) {
                    FilterController::applyOrderFilter($q, $filter, $logic);
                }
            });
        }

        return DataTables::of($query)
            ->addColumn('created_at', function ($deliveryMan) {
                return $deliveryMan->created_at->format('d-m-Y h:i A');
            })
            ->addColumn('status', function ($deliveryMan) {
                return $deliveryMan->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    private function getOrderTableData($startDate, $endDate, $filters, $filterLogic)
    {
        $query = Order::with(['user'])
            ->select([
                'id',
                'order_no as order_id',
                'user_id',
                'total_amount',
                'status as order_status',
                'created_at'
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        if ($filters) {
            $filters = is_string($filters) ? json_decode($filters, true) : $filters;
            $logic = $filterLogic;

            $query->where(function ($q) use ($filters, $logic) {
                foreach ($filters as $filter) {
                    FilterController::applyOrderFilter($q, $filter, $logic);
                }
            });
        }

        return DataTables::of($query)
            ->addColumn('created_at', function ($order) {
                return $order->created_at->format('d-m-Y h:i A');
            })
            ->addColumn('customer', function ($order) {
                return $order->user->name ?? 'N/A';
            })
            ->rawColumns(['order_status'])
            ->make(true);
    }


    private function getProductTableData($startDate, $endDate, $filters, $filterLogic)
    {
        $query = Product::with(['category'])
            ->select([
                'id',
                'name as product_name',
                'category_id',
                'amount as price',
                'product_stock',
                'status',
                'created_at'
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($filters) {
            $filters = is_string($filters) ? json_decode($filters, true) : $filters;
            $logic = $filterLogic;

            $query->where(function ($q) use ($filters, $logic) {
                foreach ($filters as $filter) {
                    FilterController::applyOrderFilter($q, $filter, $logic);
                }
            });
        }

        return DataTables::of($query)
            ->addColumn('created_at', function ($product) {
                return $product->created_at->format('d-m-Y h:i A');
            })
            ->addColumn('category', function ($product) {
                return $product->category->name ?? 'N/A';
            })
            ->addColumn('status', function ($product) {
                return $product->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    private function getShopTableData($startDate, $endDate, $filters, $filterLogic)
    {
        $query = Shop::select([
            'id',
            'name as shop_name',
            'owner_name as owner',
            'address as location',
            'status',
            'created_at'
        ]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($filters) {
            $filters = is_string($filters) ? json_decode($filters, true) : $filters;
            $logic = $filterLogic;

            $query->where(function ($q) use ($filters, $logic) {
                foreach ($filters as $filter) {
                    FilterController::applyOrderFilter($q, $filter, $logic);
                }
            });
        }

        return DataTables::of($query)
            ->addColumn('created_at', function ($shop) {
                return $shop->created_at->format('d-m-Y h:i A');
            })
            ->addColumn('status', function ($shop) {
                return $shop->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    private function getShopPayoutTableData($startDate, $endDate, $filters, $filterLogic)
    {
        $query = PayoutShop::with(['shop', 'payout'])
            ->select([
                'shop_id',
                DB::raw('DATE(created_at) as payout_date'),
                DB::raw('COUNT(*) as total_payouts'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(commission) as commission_amount'),
                DB::raw('SUM(payable_amount) as payable_amount'),
                DB::raw('ROUND(AVG((commission/NULLIF(amount,0))*100), 2) as commission_rate'),
                DB::raw('GROUP_CONCAT(DISTINCT status) as payout_status'),
                DB::raw('MAX(id) as latest_id')
            ])
            ->groupBy('shop_id', DB::raw('DATE(created_at)'));

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($filters) {
            $filters = is_string($filters) ? json_decode($filters, true) : $filters;
            $logic = $filterLogic;
            $query->where(function ($q) use ($filters, $logic) {
                foreach ($filters as $filter) {
                    FilterController::applyFilter($q, $filter, $logic);
                }
            });
        }

        return DataTables::of($query)
            ->addColumn('shop_name', function ($row) {
                $shop = Shop::find($row->shop_id);
                return $shop->name ?? 'N/A';
            })
            ->addColumn('owner_name', function ($row) {
                $shop = Shop::find($row->shop_id);
                return $shop->owner_name ?? 'N/A';
            })
            ->editColumn('total_amount', function ($row) {
                return number_format($row->total_amount, 2);
            })
            ->editColumn('commission_amount', function ($row) {
                return number_format($row->commission_amount, 2);
            })
            ->editColumn('payable_amount', function ($row) {
                return number_format($row->payable_amount, 2);
            })
            ->editColumn('commission_rate', function ($row) {
                return number_format($row->commission_rate ?? 0, 2);
            })
            ->editColumn('payout_date', function ($row) {
                return Carbon::parse($row->payout_date)->format('d-m-Y');
            })
            ->editColumn('payout_status', function ($row) {
                $statuses = explode(',', $row->payout_status);
                if (in_array('paid', $statuses)) {
                    return 'paid';
                } elseif (in_array('processing', $statuses)) {
                    return 'processing';
                } elseif (in_array('failed', $statuses)) {
                    return 'failed';
                } else {
                    return 'generated';
                }
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

}
