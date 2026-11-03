<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\UserType;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\ShopOrder;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $orderCount = Order::count();
        $packageCount = Package::count();
        $userCount = User::where('type', UserType::USER)->count();
        $shopCount = User::where('type', UserType::DELIVERY)->count();

        $orderQuery = Order::whereBetween('created_at', [$startDate, $endDate]);
        $totalRevenue = $orderQuery->sum('total_amount');
        $totalOrdersInRange = $orderQuery->count();

        $order = Order::latest()->take(10)->get();
        $user = User::where('type', UserType::USER)->latest()->take(10)->get();

        $analyticsData = $this->getAnalyticsDatas($startDate, $endDate);

        $recentUsers = User::where('type', 'customer')->latest()->take(5)->get();
        return view('backend.index', compact(
            'order',
            'user',
            'orderCount',
            'packageCount',
            'userCount',
            'shopCount',
            // 'commission',
            'totalRevenue',
            'totalOrdersInRange',
            'analyticsData',
            'startDate',
            'endDate',
            'recentUsers'
        ));
    }

    private function getAnalyticsDatas($startDate = null, $endDate = null)
    {
        // Order Status
        $orderStatusQuery = Order::query();
        if ($startDate && $endDate) {
            $orderStatusQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orderStatusData = $orderStatusQuery
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst(str_replace('_', ' ', $item->status)),
                    'count' => $item->count
                ];
            });

        $dailyOrdersQuery = Order::query();
        if ($startDate && $endDate) {
            $dailyOrdersQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $dailyOrders = $dailyOrdersQuery
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M d'),
                    'count' => $item->count
                ];
            });

        $topProductsQuery = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id');

        if ($startDate && $endDate) {
            $topProductsQuery->whereBetween('orders.created_at', [$startDate, $endDate]);
        }
        $monthlyRevenueQuery = Order::query();
        $monthlyCommissionQuery = ShopOrder::query();

        if ($startDate && $endDate) {
            $monthlyRevenueQuery->whereBetween('created_at', [$startDate, $endDate]);
            $monthlyCommissionQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $monthlyRevenue = $monthlyRevenueQuery
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', $item->month)->format('M Y'),
                    'revenue' => $item->revenue
                ];
            });

        // Get monthly commission from ShopOrder
        //     $monthlyCommission = $monthlyCommissionQuery
        //         ->select(
        //             DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
        //             DB::raw('SUM(total_admin_commission) as commission')
        //         )
        //         ->groupBy('month')
        //         ->orderBy('month')
        //         ->get()
        //         ->map(function ($item) {
        //             return [
        //                 'month' => Carbon::createFromFormat('Y-m', $item->month)->format('M Y'),
        //                 'commission' => $item->commission
        //             ];
        //         });

        //     $mergedMonthlyData = [];
        // foreach ($monthlyRevenue as $revenueItem) {
        //     $commissionItem = $monthlyCommission->firstWhere('month', $revenueItem['month']);
        //     $mergedMonthlyData[] = [
        //         'month' => $revenueItem['month'],
        //         'revenue' => $revenueItem['revenue'],
        //         'commission' => $commissionItem['commission'] ?? 0
        //     ];
        // }

        $todayActivity = $this->getTodayActivity();

        return [
            'orderStatus' => $orderStatusData,
            'dailyOrders' => $dailyOrders,
            // 'topProducts' => $topProducts,
            // 'monthlyRevenue' => $mergedMonthlyData,
            'todayActivity' => $todayActivity
        ];
    }


    private function getTodayActivity()
    {
        $today = Carbon::today();
        $recentOrders = Order::whereDate('created_at', $today)
            ->with(['user'])
            ->latest()
            ->limit(5)
            ->get();

        $recentUsers = User::whereDate('created_at', $today)
            ->where('type', UserType::USER)
            ->latest()
            ->limit(5)
            ->get();

        // Format recent activities
        $recentActivities = collect();

        foreach ($recentOrders as $order) {
            $recentActivities->push([
                'type' => 'order',
                'title' => 'New Order #' . $order->order_no,
                'description' => 'Order placed by ' . ($order->user->name ?? 'Customer'),
                'time' => $order->created_at->diffForHumans(),
                'icon' => 'shopping-cart',
                'color' => 'primary'
            ]);
        }

        foreach ($recentUsers as $user) {
            $recentActivities->push([
                'type' => 'user',
                'title' => 'New User Registered',
                'description' => $user->name . ' (' . $user->phone . ')',
                'time' => $user->created_at->diffForHumans(),
                'icon' => 'user-plus',
                'color' => 'success'
            ]);
        }

        // Sort by time desc
        $recentActivities = $recentActivities->sortByDesc(function ($item) {
            return $item['time'];
        })->values();

        return [
            'newOrders' => Order::whereDate('created_at', $today)->count(),
            'newUsers' => User::whereDate('created_at', $today)->count(),
            'completedOrders' => Order::whereDate('created_at', $today)
                ->where('status', OrderStatus::COMPLETED)->count(),
            'totalRevenue' => Order::whereDate('created_at', $today)->sum('total_amount'),
            'recentActivities' => $recentActivities
        ];
    }

    public function getAnalyticsData(Request $request)
    {
        // If both dates are provided and not empty, use them; otherwise, leave null
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;

        // Pass to analytics function
        $data = $this->getAnalyticsDatas($startDate, $endDate);

        // Build query conditionally
        $orderQuery = Order::query();
        $shopOrderQuery = ShopOrder::query();
        $productQuery = Product::query();
        $userQuery = User::query();

        if ($startDate && $endDate) {
            $orderQuery->whereBetween('created_at', [$startDate, $endDate]);
            $shopOrderQuery->whereBetween('created_at', [$startDate, $endDate]);
            $productQuery->whereBetween('created_at', [$startDate, $endDate]);
            $userQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Summary counts
        $productCount = $productQuery->count();
        $userCount = (clone $userQuery)->where('type', UserType::USER)->count();
        $shopCount = (clone $userQuery)->where('type', UserType::SHOP)->count();

        $data['summary'] = [
            'totalOrders' => $orderQuery->count(),
            'totalProducts' => $productCount,
            'totalUsers' => $userCount,
            'totalShops' => $shopCount,
            'totalRevenue' => $orderQuery->sum('total_amount'),
            'commission' => $shopOrderQuery->sum('total_admin_commission'),
            'averageOrderValue' => $orderQuery->avg('total_amount') ?: 0
        ];

        return response()->json($data);
    }


    public function getTodayActivityData(Request $request)
    {
        $today = Carbon::today();
        $recentOrders = Order::whereDate('created_at', $today)
            ->with(['user'])
            ->latest()
            ->limit(5)
            ->get();

        $recentUsers = User::whereDate('created_at', $today)
            ->where('type', UserType::USER)
            ->latest()
            ->limit(5)
            ->get();

        $activities = collect();

        foreach ($recentOrders as $order) {
            $activities->push([
                'type' => 'order',
                'title' => 'New Order #' . $order->order_no,
                'description' => 'Order placed by ' . ($order->user->name ?? 'Customer'),
                'time' => $order->created_at->diffForHumans(),
                'icon' => 'shopping-cart',
                'color' => 'primary'
            ]);
        }

        foreach ($recentUsers as $user) {
            $activities->push([
                'type' => 'user',
                'title' => 'New User Registered',
                'description' => $user->name . ' (' . $user->phone . ')',
                'time' => $user->created_at->diffForHumans(),
                'icon' => 'user-plus',
                'color' => 'success'
            ]);
        }

        // Sort by time desc
        $activities = $activities->sortByDesc(function ($item) {
            return $item['time'];
        })->values();

        return response()->json([
            'data' => $activities,
            'newOrders' => Order::whereDate('created_at', $today)->count(),
            'newUsers' => User::whereDate('created_at', $today)->count()
        ]);
    }

    public function getOrders(Request $request)
    {
        if ($request->ajax()) {
            $query = Order::with(['user', 'package'])
                ->orderBy('id', 'desc');
            if ($request->has('start_date') && $request->start_date) {
                $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
            }
            $data = $query->orderBy('id', 'desc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('order_id', function ($row) {
                    return $row->order_no ?? '-';
                })
                ->addColumn('user_name', function ($row) {
                    return $row->shop->name ?? '-';
                })
                ->addColumn('package', function ($row) {
                    return $row->user->name ?? '-';
                })
                ->addColumn('amount', function ($row) {
                    return $row->user->phone ?? '-';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . url('admin/orders/' . $row->id) . '" class="btn btn-sm btn-primary">
                        <i class="fa fa-eye"></i> View
                    </a>';
                })
                ->rawColumns(['order_id', 'user_name', 'package', 'amount', 'action'])
                ->make(true);
        }

        return view('backend.index');
    }

    public function getUser(Request $request)
    {
        if ($request->ajax()) {
            $query = User::where('type', UserType::USER);

            if ($request->has('start_date') && $request->start_date) {
                $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
            }

            if ($request->has('end_date') && $request->end_date) {
                $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
            }

            $data = $query->orderBy('id', 'desc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('d-m-Y');
                })
                ->addColumn('name', function ($row) {
                    return $row->name ?? '-';
                })
                ->addColumn('phone', function ($row) {
                    return $row->phone ?? '-';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == true) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->rawColumns(['name', 'phone', 'status', 'date'])
                ->make(true);
        }

        return view('backend.index');
    }
}
