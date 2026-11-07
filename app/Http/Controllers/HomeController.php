<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Expense;
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

        $recentUsers = User::latest()->take(5)->get();
        $residenceData = $this->getResidenceData();

        return view('backend.index', compact(
            'startDate',
            'endDate',
            'recentUsers',
            'residenceData'
        ));
    }

    private function getResidenceData()
    {
        // Update any overdue bills automatically
        \App\Models\Bill::where('status', 'unpaid')
            ->whereDate('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        $totalUsers = User::count();
        $tenantCount = Tenant::where('status', 1)->count();
        $staffCount = User::where('type', '!=', UserType::USER)->count();

        $totalRooms = Room::count();
        $occupiedRooms = Room::where('occupancy_status', 'occupied')->count();
        $availableRooms = Room::where('occupancy_status', 'available')->count();
        $selfRooms = Room::where(function($q) {
            $q->where('occupancy_status', 'self')->orWhere('is_self', 1);
        })->count();
        $maintenanceRooms = Room::where('occupancy_status', 'maintenance')->count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Monthly Turnover (Collections this month)
        $thisMonthTurnover = (float) \App\Models\BillPayment::whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->sum('amount');
        $totalTurnoverAllTime = (float) \App\Models\BillPayment::sum('amount');
        $monthlyTurnover = $thisMonthTurnover > 0 ? $thisMonthTurnover : $totalTurnoverAllTime;

        // Last month turnover for growth comparison
        $lastMonth = Carbon::now()->subMonth();
        $lastMonthTurnover = (float) \App\Models\BillPayment::whereMonth('payment_date', $lastMonth->month)
            ->whereYear('payment_date', $lastMonth->year)
            ->sum('amount');

        if ($lastMonthTurnover > 0) {
            $diff = (($thisMonthTurnover - $lastMonthTurnover) / $lastMonthTurnover) * 100;
            $monthlyTurnoverGrowth = ($diff >= 0 ? '+' : '') . round($diff, 1) . '%';
        } elseif ($thisMonthTurnover > 0) {
            $monthlyTurnoverGrowth = '+100%';
        } else {
            $monthlyTurnoverGrowth = '0%';
        }

        // Monthly Expenses
        $thisMonthExpenses = (float) Expense::whereMonth('expense_date', $currentMonth)
            ->whereYear('expense_date', $currentYear)
            ->sum('amount');
        $totalExpensesAllTime = (float) Expense::sum('amount');
        $monthlyExpenses = $thisMonthExpenses > 0 ? $thisMonthExpenses : $totalExpensesAllTime;

        $lastMonthExpenses = (float) Expense::whereMonth('expense_date', $lastMonth->month)
            ->whereYear('expense_date', $lastMonth->year)
            ->sum('amount');

        if ($lastMonthExpenses > 0) {
            $diffExp = (($thisMonthExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100;
            $monthlyExpenseChange = ($diffExp >= 0 ? '+' : '') . round($diffExp, 1) . '%';
        } elseif ($thisMonthExpenses > 0) {
            $monthlyExpenseChange = '+100%';
        } else {
            $monthlyExpenseChange = '0%';
        }

        // Net Cashflow & Profit Margin
        $netProfit = $monthlyTurnover - $monthlyExpenses;
        $profitMargin = $monthlyTurnover > 0 ? round(($netProfit / $monthlyTurnover) * 100, 1) . '%' : '0%';

        $pendingDues = (float) \App\Models\Bill::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->sum('due_amount');
        $overdueTenantsCount = \App\Models\Bill::where('status', 'overdue')->count();

        // Rent Collection Pipeline Breakdown
        $collectedOnTime = (float) \App\Models\BillPayment::sum('amount');
        $paidTenants = \App\Models\Bill::where('status', 'paid')->distinct('tenant_id')->count('tenant_id');

        $dueAmount = (float) \App\Models\Bill::whereIn('status', ['unpaid', 'partially_paid'])->sum('due_amount');
        $dueTenants = \App\Models\Bill::whereIn('status', ['unpaid', 'partially_paid'])->distinct('tenant_id')->count('tenant_id');

        $overdueAmount = (float) \App\Models\Bill::where('status', 'overdue')->sum('due_amount');
        $overdueTenants = \App\Models\Bill::where('status', 'overdue')->distinct('tenant_id')->count('tenant_id');

        $totalPipeline = $collectedOnTime + $dueAmount + $overdueAmount;
        $collectedPct = $totalPipeline > 0 ? round(($collectedOnTime / $totalPipeline) * 100) : 0;
        $duePct = $totalPipeline > 0 ? round(($dueAmount / $totalPipeline) * 100) : 0;
        $overduePct = $totalPipeline > 0 ? round(($overdueAmount / $totalPipeline) * 100) : 0;

        $rentPipeline = [
            [
                'label' => 'Collected On-Time',
                'amount' => $collectedOnTime,
                'count' => $paidTenants,
                'pct' => $collectedPct,
                'color' => 'success',
                'icon' => 'check-circle'
            ],
            [
                'label' => 'Grace Period / Due',
                'amount' => $dueAmount,
                'count' => $dueTenants,
                'pct' => $duePct,
                'color' => 'warning',
                'icon' => 'hourglass-half'
            ],
            [
                'label' => 'Overdue (> Due Date)',
                'amount' => $overdueAmount,
                'count' => $overdueTenants,
                'pct' => $overduePct,
                'color' => 'danger',
                'icon' => 'exclamation-circle'
            ],
        ];

        // Past 6 Months Financial Trends
        $trendMonths = [];
        $trendTurnover = [];
        $trendExpenses = [];
        $trendProfit = [];

        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $trendMonths[] = $dt->format('M');
            $m = $dt->month;
            $y = $dt->year;

            $t = (float) \App\Models\BillPayment::whereMonth('payment_date', $m)->whereYear('payment_date', $y)->sum('amount');
            $e = (float) Expense::whereMonth('expense_date', $m)->whereYear('expense_date', $y)->sum('amount');
            $p = $t - $e;

            $trendTurnover[] = (int) $t;
            $trendExpenses[] = (int) $e;
            $trendProfit[] = (int) $p;
        }

        // Expense Category Breakdown
        $expGrouped = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $expenseLabels = [];
        $expenseAmounts = [];
        if ($expGrouped->count() > 0) {
            foreach ($expGrouped as $eg) {
                $expenseLabels[] = $eg->category;
                $expenseAmounts[] = (int) $eg->total;
            }
        } else {
            $expenseLabels = ['Maintenance & Repairs', 'Electricity & Water', 'WiFi & Amenities', 'Staff & Operational'];
            $expenseAmounts = [0, 0, 0, 0];
        }

        $topExpenses = [];
        $totalAllExpenses = array_sum($expenseAmounts);
        if ($totalAllExpenses > 0 && $expGrouped->count() > 0) {
            foreach ($expGrouped->take(2) as $eg) {
                $pct = round(($eg->total / $totalAllExpenses) * 100, 1);
                $topExpenses[] = [
                    'category' => $eg->category,
                    'amount' => (float) $eg->total,
                    'pct' => $pct . '%',
                ];
            }
        }

        // Recent Tenants Check-ins with real status ordered by room number
        $tenantsList = Tenant::with(['room', 'bills'])
            ->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC')
            ->take(5)
            ->get();
        $recentTenants = [];
        foreach ($tenantsList as $t) {
            $hasOverdue = $t->bills ? $t->bills->where('status', 'overdue')->count() > 0 : false;
            $hasDue = $t->bills ? $t->bills->whereIn('status', ['unpaid', 'partially_paid'])->count() > 0 : false;
            $rentStatus = $hasOverdue ? 'Overdue' : ($hasDue ? 'Due' : 'Paid');

            $recentTenants[] = [
                'id' => $t->id,
                'name' => $t->name,
                'email' => $t->email ?? 'Not provided',
                'phone' => $t->phone ?? '-',
                'emergency_contact' => $t->emergency_contact ?? 'Not provided',
                'address' => $t->address ?? 'Not provided',
                'room' => $t->room ? 'Room ' . $t->room->room_number : ($t->room_number ? 'Room ' . $t->room_number : 'Unassigned'),
                'room_number' => $t->room ? $t->room->room_number : ($t->room_number ?? '-'),
                'floor' => $t->room ? $t->room->floor : '-',
                'type' => $t->room ? $t->room->room_type : ($t->room_type ?? 'Standard'),
                'rent' => (float) ($t->monthly_rent > 0 ? $t->monthly_rent : ($t->room ? $t->room->total_monthly : 5000)),
                'base_rent' => (float) $t->monthly_rent,
                'maintenance' => (float) $t->maintenance_charges,
                'other_charges' => (float) $t->other_charges,
                'eb_rate' => (float) ($t->eb_unit_rate ?? 10.00),
                'eb_start_reading' => (float) ($t->eb_start_reading ?? 0.00),
                'deposit' => (float) ($t->security_deposit ?? 0.00),
                'status' => $rentStatus,
                'date' => $t->commence_date ? Carbon::parse($t->commence_date)->format('d M Y') : $t->created_at->format('d M Y'),
            ];
        }

        // Rooms under maintenance
        $maintRooms = Room::where('occupancy_status', 'maintenance')->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC')->get();
        $maintenanceRequests = [];
        if ($maintRooms->count() > 0) {
            foreach ($maintRooms as $mr) {
                $maintenanceRequests[] = [
                    'room' => 'Room ' . $mr->room_number . ' (' . $mr->floor . ')',
                    'category' => 'Room Maintenance',
                    'issue' => 'Room undergoing servicing / deep cleaning',
                    'priority' => 'Medium',
                    'status' => 'In Progress',
                    'time' => $mr->updated_at ? $mr->updated_at->diffForHumans() : 'Active',
                ];
            }
        }

        return [
            'totalUsers' => $totalUsers,
            'tenantCount' => $tenantCount,
            'staffCount' => $staffCount,
            'totalRooms' => $totalRooms,
            'occupiedRooms' => $occupiedRooms,
            'availableRooms' => $availableRooms,
            'selfRooms' => $selfRooms,
            'maintenanceRooms' => $maintenanceRooms,
            'occupancyRate' => $occupancyRate,
            'monthlyTurnover' => $monthlyTurnover,
            'monthlyTurnoverGrowth' => $monthlyTurnoverGrowth,
            'monthlyExpenses' => $monthlyExpenses,
            'monthlyExpenseChange' => $monthlyExpenseChange,
            'netProfit' => $netProfit,
            'profitMargin' => $profitMargin,
            'pendingDues' => $pendingDues,
            'overdueTenants' => $overdueTenantsCount,
            'rentPipeline' => $rentPipeline,
            'financialTrends' => [
                'months' => $trendMonths,
                'turnover' => $trendTurnover,
                'expenses' => $trendExpenses,
                'profit' => $trendProfit,
            ],
            'expenseCategories' => [
                'labels' => $expenseLabels,
                'amounts' => $expenseAmounts,
                'top' => $topExpenses,
            ],
            'recentTenants' => $recentTenants,
            'maintenanceRequests' => $maintenanceRequests,
        ];
    }

    public function getAnalyticsData(Request $request)
    {
        $residenceData = $this->getResidenceData();
        return response()->json([
            'status' => 'success',
            'data' => $residenceData
        ]);
    }

    public function getTodayActivityData(Request $request)
    {
        $today = Carbon::today();
        $recentUsers = User::whereDate('created_at', $today)->latest()->limit(5)->get();
        $recentTenants = Tenant::whereDate('created_at', $today)->latest()->limit(5)->get();

        $activities = collect();

        foreach ($recentTenants as $tenant) {
            $activities->push([
                'type' => 'tenant',
                'title' => 'New Tenant Registered',
                'description' => $tenant->name . ' (' . ($tenant->phone ?? '-') . ')',
                'time' => $tenant->created_at->diffForHumans(),
                'icon' => 'user-check',
                'color' => 'primary'
            ]);
        }

        foreach ($recentUsers as $user) {
            $activities->push([
                'type' => 'user',
                'title' => 'New User Registered',
                'description' => $user->name . ' (' . ($user->phone ?? '-') . ')',
                'time' => $user->created_at->diffForHumans(),
                'icon' => 'user-plus',
                'color' => 'success'
            ]);
        }

        return response()->json([
            'data' => $activities,
            'newTenants' => $recentTenants->count(),
            'newUsers' => $recentUsers->count()
        ]);
    }

    public function getUser(Request $request)
    {
        if ($request->ajax()) {
            $query = User::query();

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
