<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the Reports & Analytics view
     */
    public function index(Request $request)
    {
        // Automatically mark past due unpaid bills as overdue
        $this->syncOverdueBills();

        // Natural numeric order for rooms
        $rooms = Room::orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC')
            ->get(['id', 'room_number', 'floor', 'room_type', 'occupancy_status', 'is_self']);

        // Tenants ordered by name
        $tenants = Tenant::orderBy('name', 'asc')
            ->get(['id', 'name', 'phone', 'room_number', 'status']);

        // Distinct floors from rooms
        $floors = Room::select('floor')
            ->whereNotNull('floor')
            ->where('floor', '!=', '')
            ->distinct()
            ->orderBy('floor')
            ->pluck('floor');

        // Expense categories
        $defaultCategories = [
            'Maintenance & Repairs',
            'Electricity Bill',
            'Water & Plumbing',
            'Painting & Civil',
            'Housekeeping & Cleaning',
            'Furniture & Appliance',
            'WiFi & Internet',
            'Other',
        ];
        $existingCategories = Expense::select('category')->distinct()->pluck('category')->toArray();
        $expenseCategories = array_values(array_unique(array_filter(array_merge($defaultCategories, $existingCategories))));

        return view('backend.report.report', compact('rooms', 'tenants', 'floors', 'expenseCategories'));
    }

    /**
     * Mark past due bills as overdue
     */
    private function syncOverdueBills(): void
    {
        Bill::where('status', 'unpaid')
            ->whereDate('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);
    }

    /**
     * Apply date range filters to Eloquent query
     */
    private function applyDateFilter($query, Request $request, string $column = 'created_at')
    {
        $range = $request->get('date_range', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($range === 'custom') {
            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween($column, [$start, $end]);
            } elseif ($startDate) {
                $query->where($column, '>=', Carbon::parse($startDate)->startOfDay());
            } elseif ($endDate) {
                $query->where($column, '<=', Carbon::parse($endDate)->endOfDay());
            }
            return;
        }

        switch ($range) {
            case 'today':
                $query->whereDate($column, Carbon::today());
                break;
            case 'yesterday':
                $query->whereDate($column, Carbon::yesterday());
                break;
            case 'this_week':
                $query->whereBetween($column, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereBetween($column, [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            case 'last_month':
                $query->whereBetween($column, [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                break;
            case 'this_year':
                $query->whereBetween($column, [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]);
                break;
            case 'all':
            default:
                break;
        }
    }

    /**
     * Return live KPI metrics based on active filter criteria
     */
    public function reportSummary(Request $request)
    {
        $this->syncOverdueBills();

        // 1. Bills & Invoices stats
        $billsQuery = Bill::query();
        $this->applyDateFilter($billsQuery, $request, 'bill_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $billsQuery->where('room_id', $request->room_id);
        }
        if ($request->filled('tenant_id') && $request->tenant_id !== 'all') {
            $billsQuery->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $billsQuery->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }

        $totalInvoiced = (float) (clone $billsQuery)->sum('amount');
        $billsCount = (int) (clone $billsQuery)->count();
        $outstandingDues = (float) (clone $billsQuery)->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->sum('due_amount');
        $pendingBillsCount = (int) (clone $billsQuery)->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->count();
        $overdueCount = (int) (clone $billsQuery)->where('status', 'overdue')->count();
        $overdueAmount = (float) (clone $billsQuery)->where('status', 'overdue')->sum('due_amount');

        // 2. Collections & Payments stats
        $paymentsQuery = BillPayment::query();
        $this->applyDateFilter($paymentsQuery, $request, 'payment_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $paymentsQuery->whereHas('bill', fn($q) => $q->where('room_id', $request->room_id));
        }
        if ($request->filled('tenant_id') && $request->tenant_id !== 'all') {
            $paymentsQuery->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $paymentsQuery->whereHas('bill.room', fn($q) => $q->where('floor', $request->floor));
        }
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $paymentsQuery->where('payment_method', $request->payment_method);
        }

        $totalCollected = (float) (clone $paymentsQuery)->sum('amount');
        $paymentsCount = (int) (clone $paymentsQuery)->count();

        // 3. Expenses stats
        $expensesQuery = Expense::query();
        $this->applyDateFilter($expensesQuery, $request, 'expense_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $expensesQuery->where('room_id', $request->room_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $expensesQuery->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }
        if ($request->filled('category') && $request->category !== 'all') {
            $expensesQuery->where('category', $request->category);
        }
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $expensesQuery->where('payment_method', $request->payment_method);
        }

        $totalExpenses = (float) (clone $expensesQuery)->sum('amount');
        $expensesCount = (int) (clone $expensesQuery)->count();

        // 4. Net & Rates
        $netCashflow = $totalCollected - $totalExpenses;
        $collectionRate = $totalInvoiced > 0 ? round(($totalCollected / $totalInvoiced) * 100, 1) : 0;

        // 5. Rooms & Occupancy stats
        $roomsQuery = Room::query();
        if ($request->filled('floor') && $request->floor !== 'all') {
            $roomsQuery->where('floor', $request->floor);
        }
        $totalRooms = (int) (clone $roomsQuery)->count();
        $occupiedRooms = (int) (clone $roomsQuery)->where('occupancy_status', 'occupied')->count();
        $availableRooms = (int) (clone $roomsQuery)->where('occupancy_status', 'available')->where('is_self', 0)->count();
        $selfRooms = (int) (clone $roomsQuery)->where('is_self', 1)->count();
        $maintenanceRooms = (int) (clone $roomsQuery)->where('occupancy_status', 'maintenance')->count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // 6. Active Residents
        $tenantsQuery = Tenant::query();
        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $tenantsQuery->where('room_id', $request->room_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $tenantsQuery->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }
        $totalTenants = (int) (clone $tenantsQuery)->count();
        $activeTenants = (int) (clone $tenantsQuery)->where('status', 1)->count();
        $totalSecurityDeposits = (float) (clone $tenantsQuery)->where('status', 1)->sum('security_deposit');
        $totalMonthlyRent = (float) (clone $tenantsQuery)->where('status', 1)->sum('monthly_rent');
        $totalScheduledDues = (float) ((clone $tenantsQuery)->where('status', 1)->selectRaw('SUM(monthly_rent + maintenance_charges + other_charges) as total')->value('total') ?? 0);
        $totalRoomRentCapacity = (float) (clone $roomsQuery)->where('is_self', 0)->sum('monthly_rent');
        $totalRoomDepositCapacity = (float) (clone $roomsQuery)->where('is_self', 0)->sum('security_deposit');

        return response()->json([
            'success' => true,
            'cards' => [
                'total_invoiced' => $totalInvoiced,
                'total_invoiced_formatted' => '₹' . number_format($totalInvoiced, 2),
                'bills_count' => $billsCount,
                'total_collected' => $totalCollected,
                'total_collected_formatted' => '₹' . number_format($totalCollected, 2),
                'payments_count' => $paymentsCount,
                'outstanding_dues' => $outstandingDues,
                'outstanding_dues_formatted' => '₹' . number_format($outstandingDues, 2),
                'pending_bills_count' => $pendingBillsCount,
                'overdue_count' => $overdueCount,
                'overdue_amount' => $overdueAmount,
                'overdue_amount_formatted' => '₹' . number_format($overdueAmount, 2),
                'total_expenses' => $totalExpenses,
                'total_expenses_formatted' => '₹' . number_format($totalExpenses, 2),
                'expenses_count' => $expensesCount,
                'net_cashflow' => $netCashflow,
                'net_cashflow_formatted' => ($netCashflow >= 0 ? '₹' : '-₹') . number_format(abs($netCashflow), 2),
                'collection_rate' => $collectionRate . '%',
                'total_rooms' => $totalRooms,
                'occupied_rooms' => $occupiedRooms,
                'available_rooms' => $availableRooms,
                'self_rooms' => $selfRooms,
                'maintenance_rooms' => $maintenanceRooms,
                'occupancy_rate' => $occupancyRate . '%',
                'total_tenants' => $totalTenants,
                'active_tenants' => $activeTenants,
                'total_security_deposits' => $totalSecurityDeposits,
                'total_security_deposits_formatted' => '₹' . number_format($totalSecurityDeposits, 2),
                'total_monthly_rent' => $totalMonthlyRent,
                'total_monthly_rent_formatted' => '₹' . number_format($totalMonthlyRent, 2),
                'total_scheduled_dues' => $totalScheduledDues,
                'total_scheduled_dues_formatted' => '₹' . number_format($totalScheduledDues, 2),
                'total_room_rent_capacity' => $totalRoomRentCapacity,
                'total_room_rent_capacity_formatted' => '₹' . number_format($totalRoomRentCapacity, 2),
                'total_room_deposit_capacity' => $totalRoomDepositCapacity,
                'total_room_deposit_capacity_formatted' => '₹' . number_format($totalRoomDepositCapacity, 2),
            ]
        ]);
    }

    /**
     * Backward-compatible alias for getReportData
     */
    public function getReportData(Request $request)
    {
        return $this->reportSummary($request);
    }

    /**
     * Return visual charts data for reports dashboard
     */
    public function reportCharts(Request $request)
    {
        // 1. Monthly Trends (Last 6 Months)
        $months = [];
        $invoicedData = [];
        $collectedData = [];
        $expensesData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthLabel = $monthDate->format('M Y');
            $monthName = $monthDate->format('F Y');
            $year = $monthDate->year;
            $monthNum = $monthDate->month;

            $months[] = $monthLabel;

            $inv = (float) Bill::where('billing_month', $monthName)
                ->orWhere(function ($q) use ($year, $monthNum) {
                    $q->whereYear('bill_date', $year)->whereMonth('bill_date', $monthNum);
                })->sum('amount');

            $col = (float) BillPayment::whereYear('payment_date', $year)
                ->whereMonth('payment_date', $monthNum)
                ->sum('amount');

            $exp = (float) Expense::whereYear('expense_date', $year)
                ->whereMonth('expense_date', $monthNum)
                ->sum('amount');

            $invoicedData[] = round($inv, 2);
            $collectedData[] = round($col, 2);
            $expensesData[] = round($exp, 2);
        }

        // 2. Bill Status Breakdown
        $statusCounts = Bill::select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusData = [
            'paid' => $statusCounts['paid'] ?? 0,
            'partially_paid' => $statusCounts['partially_paid'] ?? 0,
            'unpaid' => $statusCounts['unpaid'] ?? 0,
            'overdue' => $statusCounts['overdue'] ?? 0,
        ];

        // 3. Expense Categories Breakdown
        $expensesByCategory = Expense::select('category', DB::raw('sum(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $categoryLabels = [];
        $categoryValues = [];
        foreach ($expensesByCategory as $item) {
            $categoryLabels[] = $item->category;
            $categoryValues[] = round((float) $item->total, 2);
        }

        return response()->json([
            'months' => $months,
            'invoiced' => $invoicedData,
            'collected' => $collectedData,
            'expenses' => $expensesData,
            'status' => $statusData,
            'expense_categories' => [
                'labels' => $categoryLabels,
                'values' => $categoryValues,
            ]
        ]);
    }

    /**
     * Dynamic DataTables records provider for the active report module
     */
    public function getReportTableData(Request $request)
    {
        $this->syncOverdueBills();
        $type = $request->get('report_type', 'bills');

        switch ($type) {
            case 'payments':
                return $this->getPaymentsDataTable($request);
            case 'rooms':
                return $this->getRoomsDataTable($request);
            case 'tenants':
                return $this->getTenantsDataTable($request);
            case 'expenses':
                return $this->getExpensesDataTable($request);
            case 'eb':
                return $this->getEbDataTable($request);
            case 'bills':
            default:
                return $this->getBillsDataTable($request);
        }
    }

    /**
     * DataTable for Bills & Invoices
     */
    private function getBillsDataTable(Request $request)
    {
        $query = Bill::with(['tenant', 'room', 'creator'])->latest('bill_date')->latest('id');

        $this->applyDateFilter($query, $request, 'bill_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('tenant_id') && $request->tenant_id !== 'all') {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('bill_number_badge', function ($row) {
                return '<div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark border px-2 py-1 fw-bold font-12">
                        <i class="fas fa-file-invoice text-primary me-1"></i> ' . e($row->bill_number) . '
                    </span>
                </div>';
            })
            ->addColumn('billing_month_display', function ($row) {
                $date = $row->bill_date ? $row->bill_date->format('d M Y') : '-';
                return '<div>
                    <span class="fw-bold text-dark d-block">' . e($row->billing_month) . '</span>
                    <small class="text-muted"><i class="far fa-calendar-alt me-1 font-10"></i>' . $date . '</small>
                </div>';
            })
            ->addColumn('room_badge', function ($row) {
                if ($row->room) {
                    return '<span class="badge bg-primary text-white fw-bold px-2 py-1">
                                <i class="fas fa-door-closed me-1"></i> Room ' . e($row->room->room_number) . '
                            </span>
                            <div class="small text-muted mt-1">' . e($row->room->floor) . '</div>';
                }
                return '<span class="badge bg-secondary text-white px-2 py-1">Room ' . e($row->tenant->room_number ?? 'N/A') . '</span>';
            })
            ->addColumn('tenant_info', function ($row) {
                if ($row->tenant) {
                    $phone = $row->tenant->phone ? '<small class="text-muted d-block"><i class="fas fa-phone-alt me-1 font-10"></i>' . e($row->tenant->phone) . '</small>' : '';
                    return '<div>
                        <a href="' . url('/admin/tenants/' . $row->tenant->id . '/view') . '" class="fw-bold text-dark text-decoration-none hover-primary">' . e($row->tenant->name) . '</a>
                        ' . $phone . '
                    </div>';
                }
                return '<span class="text-muted">Former Resident</span>';
            })
            ->addColumn('bill_type_display', function ($row) {
                return '<span class="badge bg-light text-secondary border px-2 py-1 fw-semibold font-11">
                    ' . e($row->bill_type) . '
                </span>';
            })
            ->addColumn('amount_formatted', function ($row) {
                return '<span class="fw-bold text-dark font-13">₹' . number_format($row->amount, 2) . '</span>';
            })
            ->addColumn('paid_formatted', function ($row) {
                return '<span class="fw-bold text-success font-13">₹' . number_format($row->paid_amount, 2) . '</span>';
            })
            ->addColumn('due_formatted', function ($row) {
                $color = $row->due_amount > 0 ? 'text-danger' : 'text-muted';
                return '<span class="fw-bold ' . $color . ' font-13">₹' . number_format($row->due_amount, 2) . '</span>';
            })
            ->addColumn('due_date_formatted', function ($row) {
                if (!$row->due_date) return '-';
                $due = Carbon::parse($row->due_date);
                $isPast = $due->isPast() && $row->due_amount > 0;
                $text = $due->format('d M, Y');
                if ($isPast) {
                    $days = abs(Carbon::today()->diffInDays($due, false));
                    return '<span class="text-danger fw-bold">' . $text . '</span><br><small class="badge bg-danger-subtle text-danger font-10">Overdue ' . $days . 'd</small>';
                }
                return '<span class="text-muted">' . $text . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                switch ($row->status) {
                    case 'paid':
                        return '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i> Paid</span>';
                    case 'partially_paid':
                        return '<span class="badge bg-info text-white px-2 py-1"><i class="fas fa-adjust me-1"></i> Partial</span>';
                    case 'overdue':
                        return '<span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i> Overdue</span>';
                    default:
                        return '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-clock me-1"></i> Unpaid</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $btns = '<div class="btn-group btn-group-sm" role="group">';
                $btns .= '<a href="' . url('/admin/billing') . '" class="btn btn-outline-primary" title="Go to Billing / Print Invoice"><i class="fas fa-external-link-alt"></i></a>';
                $btns .= '</div>';
                return $btns;
            })
            ->rawColumns(['bill_number_badge', 'billing_month_display', 'room_badge', 'tenant_info', 'bill_type_display', 'amount_formatted', 'paid_formatted', 'due_formatted', 'due_date_formatted', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * DataTable for Payments & Receipts
     */
    private function getPaymentsDataTable(Request $request)
    {
        $query = BillPayment::with(['bill.room', 'tenant', 'receiver'])->latest('payment_date')->latest('id');

        $this->applyDateFilter($query, $request, 'payment_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->whereHas('bill', fn($q) => $q->where('room_id', $request->room_id));
        }
        if ($request->filled('tenant_id') && $request->tenant_id !== 'all') {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->whereHas('bill.room', fn($q) => $q->where('floor', $request->floor));
        }
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('payment_number_badge', function ($row) {
                return '<span class="badge bg-success-subtle text-success border border-success px-2 py-1 fw-bold font-12">
                    <i class="fas fa-receipt me-1"></i> ' . e($row->payment_number) . '
                </span>';
            })
            ->addColumn('bill_number_display', function ($row) {
                if ($row->bill) {
                    return '<span class="fw-semibold text-dark"><i class="fas fa-file-invoice text-muted me-1 font-11"></i>' . e($row->bill->bill_number) . '</span><br><small class="text-muted">' . e($row->bill->billing_month) . '</small>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('payment_date_formatted', function ($row) {
                return $row->payment_date ? $row->payment_date->format('d M Y') : '-';
            })
            ->addColumn('tenant_info', function ($row) {
                if ($row->tenant) {
                    $phone = $row->tenant->phone ? '<small class="text-muted d-block"><i class="fas fa-phone-alt me-1 font-10"></i>' . e($row->tenant->phone) . '</small>' : '';
                    return '<div>
                        <a href="' . url('/admin/tenants/' . $row->tenant->id . '/view') . '" class="fw-bold text-dark text-decoration-none hover-primary">' . e($row->tenant->name) . '</a>
                        ' . $phone . '
                    </div>';
                }
                return '<span class="text-muted">Resident</span>';
            })
            ->addColumn('room_badge', function ($row) {
                $room = $row->bill->room ?? ($row->tenant->room ?? null);
                if ($room) {
                    return '<span class="badge bg-primary text-white fw-bold px-2 py-1">
                                <i class="fas fa-door-closed me-1"></i> Room ' . e($room->room_number) . '
                            </span>
                            <div class="small text-muted mt-1">' . e($room->floor) . '</div>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('payment_method_badge', function ($row) {
                $icon = match ($row->payment_method) {
                    'Cash' => 'fas fa-money-bill-wave text-success',
                    'UPI' => 'fas fa-mobile-alt text-primary',
                    'Bank Transfer' => 'fas fa-university text-info',
                    'Cheque' => 'fas fa-money-check text-warning',
                    'Card' => 'fas fa-credit-card text-purple',
                    default => 'fas fa-wallet text-secondary'
                };
                return '<span class="badge bg-light text-dark border px-2 py-1 fw-semibold font-11">
                    <i class="' . $icon . ' me-1 font-11"></i> ' . e($row->payment_method) . '
                </span>';
            })
            ->addColumn('transaction_id_display', function ($row) {
                return '<span class="text-muted font-11">' . e($row->transaction_id ?: 'N/A') . '</span>';
            })
            ->addColumn('amount_formatted', function ($row) {
                return '<span class="fw-bold text-success fs-6">₹' . number_format($row->amount, 2) . '</span>';
            })
            ->addColumn('received_by_name', function ($row) {
                return '<span class="text-muted font-11">' . e($row->receiver->name ?? 'Admin') . '</span>';
            })
            ->rawColumns(['payment_number_badge', 'bill_number_display', 'tenant_info', 'room_badge', 'payment_method_badge', 'transaction_id_display', 'amount_formatted', 'received_by_name'])
            ->make(true);
    }

    /**
     * DataTable for Rooms & Occupancy
     */
    private function getRoomsDataTable(Request $request)
    {
        $query = Room::with(['currentTenant']);

        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->where('floor', $request->floor);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'self') {
                $query->where('is_self', 1);
            } else {
                $query->where('occupancy_status', $request->status)->where('is_self', 0);
            }
        }
        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->where('id', $request->room_id);
        }

        // Natural numeric ordering
        $query->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('room_badge', function ($row) {
                return '<span class="badge bg-primary text-white fw-bold px-2 py-1 fs-6">
                    <i class="fas fa-door-closed me-1"></i> Room ' . e($row->room_number) . '
                </span>';
            })
            ->addColumn('floor_display', function ($row) {
                return '<span class="fw-semibold text-dark">' . e($row->floor) . '</span>';
            })
            ->addColumn('room_type_display', function ($row) {
                return '<span class="badge bg-light text-secondary border px-2 py-1">' . e($row->room_type) . '</span>';
            })
            ->addColumn('capacity_display', function ($row) {
                return $row->capacity . ' Persons';
            })
            ->addColumn('monthly_rent_formatted', function ($row) {
                return '<span class="fw-bold text-dark">₹' . number_format($row->monthly_rent, 2) . '</span>';
            })
            ->addColumn('eb_rate_formatted', function ($row) {
                return '₹' . number_format($row->eb_unit_rate, 2) . ' / unit';
            })
            ->addColumn('maintenance_formatted', function ($row) {
                return '₹' . number_format($row->maintenance_charges, 2);
            })
            ->addColumn('other_formatted', function ($row) {
                return '₹' . number_format($row->other_charges, 2);
            })
            ->addColumn('total_monthly_formatted', function ($row) {
                return '<span class="fw-bold text-primary fs-6">₹' . number_format($row->total_monthly, 2) . '</span>';
            })
            ->addColumn('deposit_formatted', function ($row) {
                return '₹' . number_format($row->security_deposit, 2);
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->is_self) {
                    return '<span class="badge bg-secondary text-white px-2 py-1"><i class="fas fa-user-shield me-1"></i> Self-Occupied</span>';
                }
                switch ($row->occupancy_status) {
                    case 'occupied':
                        return '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-user-check me-1"></i> Occupied</span>';
                    case 'available':
                        return '<span class="badge bg-info text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i> Available</span>';
                    case 'maintenance':
                        return '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-tools me-1"></i> Maintenance</span>';
                    default:
                        return '<span class="badge bg-light text-dark border px-2 py-1">' . e(ucfirst($row->occupancy_status)) . '</span>';
                }
            })
            ->addColumn('current_tenant_display', function ($row) {
                if ($row->is_self) {
                    return '<span class="badge bg-light text-muted border font-11">Owner / Management</span>';
                }
                if ($row->currentTenant) {
                    return '<div>
                        <a href="' . url('/admin/tenants/' . $row->currentTenant->id . '/view') . '" class="fw-bold text-dark text-decoration-none hover-primary">' . e($row->currentTenant->name) . '</a>
                        <small class="text-muted d-block"><i class="fas fa-phone-alt font-10 me-1"></i>' . e($row->currentTenant->phone ?? '-') . '</small>
                    </div>';
                }
                return '<span class="text-muted">Vacant</span>';
            })
            ->rawColumns(['room_badge', 'floor_display', 'room_type_display', 'monthly_rent_formatted', 'total_monthly_formatted', 'status_badge', 'current_tenant_display'])
            ->make(true);
    }

    /**
     * DataTable for Tenants & Leases
     */
    private function getTenantsDataTable(Request $request)
    {
        $query = Tenant::with(['room', 'bills', 'payments']);

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status === 'active' ? 1 : 0);
        }

        $query->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('resident_info', function ($row) {
                return '<div>
                    <a href="' . url('/admin/tenants/' . $row->id . '/view') . '" class="fw-bold text-dark text-decoration-none hover-primary fs-6">' . e($row->name) . '</a>
                    <div class="small text-muted mt-1"><i class="fas fa-phone-alt font-10 me-1"></i>' . e($row->phone ?? 'No Phone') . '</div>
                </div>';
            })
            ->addColumn('room_badge', function ($row) {
                $roomNo = $row->room_number ?? ($row->room ? $row->room->room_number : '-');
                $floor = $row->room ? $row->room->floor : '';
                return '<span class="badge bg-primary text-white fw-bold px-2 py-1">
                            <i class="fas fa-door-closed me-1"></i> Room ' . e($roomNo) . '
                        </span>
                        <div class="small text-muted mt-1">' . e($floor) . '</div>';
            })
            ->addColumn('commence_date_formatted', function ($row) {
                return $row->commence_date ? $row->commence_date->format('d M Y') : '-';
            })
            ->addColumn('monthly_rent_formatted', function ($row) {
                return '<span class="fw-bold text-dark">₹' . number_format($row->monthly_rent, 2) . '</span>';
            })
            ->addColumn('deposit_formatted', function ($row) {
                return '₹' . number_format($row->security_deposit, 2);
            })
            ->addColumn('total_invoiced_formatted', function ($row) {
                $inv = (float) $row->bills->sum('amount');
                return '<span class="fw-bold text-dark">₹' . number_format($inv, 2) . '</span>';
            })
            ->addColumn('total_paid_formatted', function ($row) {
                $paid = (float) $row->bills->sum('paid_amount');
                return '<span class="fw-bold text-success">₹' . number_format($paid, 2) . '</span>';
            })
            ->addColumn('outstanding_due_formatted', function ($row) {
                $due = (float) $row->bills->sum('due_amount');
                $color = $due > 0 ? 'text-danger' : 'text-muted';
                return '<span class="fw-bold ' . $color . '">₹' . number_format($due, 2) . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status) {
                    return '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-user-check me-1"></i> Active</span>';
                }
                return '<span class="badge bg-secondary text-white px-2 py-1"><i class="fas fa-user-times me-1"></i> Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . url('/admin/tenants/' . $row->id . '/view') . '" class="btn btn-sm btn-outline-info" title="View Resident Details">
                    <i class="fas fa-eye"></i> View
                </a>';
            })
            ->rawColumns(['resident_info', 'room_badge', 'monthly_rent_formatted', 'total_invoiced_formatted', 'total_paid_formatted', 'outstanding_due_formatted', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * DataTable for Expenses
     */
    private function getExpensesDataTable(Request $request)
    {
        $query = Expense::with(['room', 'creator'])->latest('expense_date')->latest('id');

        $this->applyDateFilter($query, $request, 'expense_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('expense_type', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('expense_date_formatted', function ($row) {
                return $row->expense_date ? $row->expense_date->format('d M Y') : '-';
            })
            ->addColumn('type_badge', function ($row) {
                if ($row->expense_type === 'room') {
                    return '<span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">Room</span>';
                }
                return '<span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1">General / Personal</span>';
            })
            ->addColumn('room_badge', function ($row) {
                if ($row->room) {
                    return '<span class="badge bg-primary text-white fw-bold px-2 py-1">
                                <i class="fas fa-door-closed me-1"></i> Room ' . e($row->room->room_number) . '
                            </span>
                            <div class="small text-muted mt-1">' . e($row->room->floor) . '</div>';
                }
                return '<span class="badge bg-light text-muted border px-2 py-1">Common Residence</span>';
            })
            ->addColumn('category_badge', function ($row) {
                return '<span class="badge bg-info-subtle text-info border border-info px-2 py-1 fw-semibold font-11">' . e($row->category) . '</span>';
            })
            ->addColumn('title_desc', function ($row) {
                $notes = $row->notes ? '<small class="text-muted d-block text-wrap" style="max-width:260px;">' . e($row->notes) . '</small>' : '';
                return '<div>
                    <span class="fw-bold text-dark d-block">' . e($row->title) . '</span>
                    ' . $notes . '
                </div>';
            })
            ->addColumn('amount_formatted', function ($row) {
                return '<span class="fw-bold text-danger fs-6">₹' . number_format($row->amount, 2) . '</span>';
            })
            ->addColumn('payment_method_display', function ($row) {
                return '<span class="badge bg-light text-dark border px-2 py-1 font-11">' . e($row->payment_method) . '</span>';
            })
            ->addColumn('paid_to_display', function ($row) {
                return '<span class="fw-semibold text-secondary font-12">' . e($row->paid_to ?? '-') . '</span>';
            })
            ->addColumn('recorded_by_name', function ($row) {
                return '<span class="text-muted font-11">' . e($row->creator->name ?? 'Admin') . '</span>';
            })
            ->rawColumns(['type_badge', 'room_badge', 'category_badge', 'title_desc', 'amount_formatted', 'payment_method_display', 'paid_to_display', 'recorded_by_name'])
            ->make(true);
    }

    /**
     * DataTable for Electricity (EB) Readings
     */
    private function getEbDataTable(Request $request)
    {
        $query = Bill::with(['tenant', 'room'])
            ->where(function ($q) {
                $q->whereNotNull('eb_current_reading')
                    ->orWhere('eb_consumed_units', '>', 0)
                    ->orWhere('eb_amount', '>', 0)
                    ->orWhere('bill_type', 'like', '%Electricity%');
            })
            ->latest('bill_date')->latest('id');

        $this->applyDateFilter($query, $request, 'bill_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('tenant_id') && $request->tenant_id !== 'all') {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('billing_month_display', function ($row) {
                $date = $row->bill_date ? $row->bill_date->format('d M Y') : '-';
                return '<div>
                    <span class="fw-bold text-dark d-block">' . e($row->billing_month) . '</span>
                    <small class="text-muted"><i class="far fa-calendar-alt me-1 font-10"></i>' . $date . '</small>
                </div>';
            })
            ->addColumn('room_badge', function ($row) {
                if ($row->room) {
                    return '<span class="badge bg-primary text-white fw-bold px-2 py-1">
                                <i class="fas fa-door-closed me-1"></i> Room ' . e($row->room->room_number) . '
                            </span>
                            <div class="small text-muted mt-1">' . e($row->room->floor) . '</div>';
                }
                return '<span class="badge bg-secondary text-white px-2 py-1">Room ' . e($row->tenant->room_number ?? 'N/A') . '</span>';
            })
            ->addColumn('tenant_name_display', function ($row) {
                if ($row->tenant) {
                    return '<a href="' . url('/admin/tenants/' . $row->tenant->id . '/view') . '" class="fw-bold text-dark text-decoration-none hover-primary">' . e($row->tenant->name) . '</a>';
                }
                return '<span class="text-muted">Former Resident</span>';
            })
            ->addColumn('previous_reading_display', function ($row) {
                return number_format((float) $row->eb_previous_reading, 2);
            })
            ->addColumn('current_reading_display', function ($row) {
                return $row->eb_current_reading !== null ? number_format((float) $row->eb_current_reading, 2) : '-';
            })
            ->addColumn('consumed_units_display', function ($row) {
                return '<span class="fw-bold text-primary fs-6">' . number_format((float) $row->eb_consumed_units, 2) . ' kWh</span>';
            })
            ->addColumn('unit_rate_display', function ($row) {
                return '₹' . number_format((float) $row->eb_unit_rate, 2);
            })
            ->addColumn('eb_amount_formatted', function ($row) {
                return '<span class="fw-bold text-dark fs-6">₹' . number_format((float) $row->eb_amount, 2) . '</span>';
            })
            ->addColumn('bill_status_badge', function ($row) {
                switch ($row->status) {
                    case 'paid':
                        return '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i> Paid</span>';
                    case 'partially_paid':
                        return '<span class="badge bg-info text-white px-2 py-1"><i class="fas fa-adjust me-1"></i> Partial</span>';
                    case 'overdue':
                        return '<span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i> Overdue</span>';
                    default:
                        return '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-clock me-1"></i> Unpaid</span>';
                }
            })
            ->rawColumns(['billing_month_display', 'room_badge', 'tenant_name_display', 'consumed_units_display', 'eb_amount_formatted', 'bill_status_badge'])
            ->make(true);
    }

    /**
     * CSV Streaming Export with Full Records & Active Filters
     */
    public function exportReport(Request $request): StreamedResponse
    {
        $this->syncOverdueBills();
        $type = $request->get('report_type', 'bills');
        $fileName = 'srm_residence_' . $type . '_report_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($type, $request) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens Indian Rupees and characters accurately
            fputs($handle, "\xEF\xBB\xBF");

            switch ($type) {
                case 'payments':
                    $this->exportPaymentsCsv($handle, $request);
                    break;
                case 'rooms':
                    $this->exportRoomsCsv($handle, $request);
                    break;
                case 'tenants':
                    $this->exportTenantsCsv($handle, $request);
                    break;
                case 'expenses':
                    $this->exportExpensesCsv($handle, $request);
                    break;
                case 'eb':
                    $this->exportEbCsv($handle, $request);
                    break;
                case 'bills':
                default:
                    $this->exportBillsCsv($handle, $request);
                    break;
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportBillsCsv($handle, Request $request)
    {
        fputcsv($handle, [
            'Bill Number',
            'Billing Month',
            'Bill Date',
            'Due Date',
            'Room Number',
            'Floor',
            'Resident Name',
            'Resident Phone',
            'Bill Type',
            'Total Amount (INR)',
            'Paid Amount (INR)',
            'Due Amount (INR)',
            'Status',
            'EB Units',
            'EB Amount (INR)',
            'Generated By',
        ]);

        $query = Bill::with(['tenant', 'room', 'creator'])->latest('bill_date')->latest('id');
        $this->applyDateFilter($query, $request, 'bill_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('tenant_id') && $request->tenant_id !== 'all') {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $query->chunk(100, function ($bills) use ($handle) {
            foreach ($bills as $bill) {
                fputcsv($handle, [
                    $bill->bill_number,
                    $bill->billing_month,
                    $bill->bill_date ? $bill->bill_date->format('Y-m-d') : '',
                    $bill->due_date ? $bill->due_date->format('Y-m-d') : '',
                    $bill->room ? $bill->room->room_number : ($bill->tenant->room_number ?? 'N/A'),
                    $bill->room ? $bill->room->floor : '',
                    $bill->tenant ? $bill->tenant->name : 'Former Resident',
                    $bill->tenant ? $bill->tenant->phone : '',
                    $bill->bill_type,
                    number_format($bill->amount, 2, '.', ''),
                    number_format($bill->paid_amount, 2, '.', ''),
                    number_format($bill->due_amount, 2, '.', ''),
                    ucfirst(str_replace('_', ' ', $bill->status)),
                    $bill->eb_consumed_units,
                    number_format($bill->eb_amount, 2, '.', ''),
                    $bill->creator ? $bill->creator->name : 'System',
                ]);
            }
        });
    }

    private function exportPaymentsCsv($handle, Request $request)
    {
        fputcsv($handle, [
            'Receipt Number',
            'Invoice Number',
            'Payment Date',
            'Resident Name',
            'Resident Phone',
            'Room Number',
            'Floor',
            'Payment Method',
            'Transaction ID',
            'Amount Paid (INR)',
            'Received By',
            'Notes',
        ]);

        $query = BillPayment::with(['bill.room', 'tenant', 'receiver'])->latest('payment_date')->latest('id');
        $this->applyDateFilter($query, $request, 'payment_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->whereHas('bill', fn($q) => $q->where('room_id', $request->room_id));
        }
        if ($request->filled('tenant_id') && $request->tenant_id !== 'all') {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->whereHas('bill.room', fn($q) => $q->where('floor', $request->floor));
        }
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        $query->chunk(100, function ($payments) use ($handle) {
            foreach ($payments as $p) {
                $room = $p->bill->room ?? ($p->tenant->room ?? null);
                fputcsv($handle, [
                    $p->payment_number,
                    $p->bill ? $p->bill->bill_number : '',
                    $p->payment_date ? $p->payment_date->format('Y-m-d') : '',
                    $p->tenant ? $p->tenant->name : 'Resident',
                    $p->tenant ? $p->tenant->phone : '',
                    $room ? $room->room_number : '',
                    $room ? $room->floor : '',
                    $p->payment_method,
                    $p->transaction_id ?: '',
                    number_format($p->amount, 2, '.', ''),
                    $p->receiver ? $p->receiver->name : 'Admin',
                    $p->notes ?: '',
                ]);
            }
        });
    }

    private function exportRoomsCsv($handle, Request $request)
    {
        fputcsv($handle, [
            'Room Number',
            'Floor',
            'Room Type',
            'Capacity',
            'Monthly Rent (INR)',
            'EB Rate (INR/unit)',
            'Maintenance Charges (INR)',
            'Other Charges (INR)',
            'Total Monthly Due (INR)',
            'Security Deposit (INR)',
            'Occupancy Status',
            'Self Occupied',
            'Current Resident',
            'Resident Phone',
        ]);

        $query = Room::with(['currentTenant']);
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->where('floor', $request->floor);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'self') {
                $query->where('is_self', 1);
            } else {
                $query->where('occupancy_status', $request->status)->where('is_self', 0);
            }
        }
        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->where('id', $request->room_id);
        }
        $query->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC');

        $query->chunk(100, function ($rooms) use ($handle) {
            foreach ($rooms as $r) {
                fputcsv($handle, [
                    $r->room_number,
                    $r->floor,
                    $r->room_type,
                    $r->capacity,
                    number_format($r->monthly_rent, 2, '.', ''),
                    number_format($r->eb_unit_rate, 2, '.', ''),
                    number_format($r->maintenance_charges, 2, '.', ''),
                    number_format($r->other_charges, 2, '.', ''),
                    number_format($r->total_monthly, 2, '.', ''),
                    number_format($r->security_deposit, 2, '.', ''),
                    $r->is_self ? 'Self' : ucfirst($r->occupancy_status),
                    $r->is_self ? 'Yes' : 'No',
                    $r->currentTenant ? $r->currentTenant->name : 'None',
                    $r->currentTenant ? $r->currentTenant->phone : '',
                ]);
            }
        });
    }

    private function exportTenantsCsv($handle, Request $request)
    {
        fputcsv($handle, [
            'Resident Name',
            'Phone',
            'Email',
            'Room Number',
            'Floor',
            'Move-in Date',
            'Monthly Rent (INR)',
            'Security Deposit (INR)',
            'Initial EB Reading',
            'Total Invoiced (INR)',
            'Total Paid (INR)',
            'Current Dues (INR)',
            'Status',
        ]);

        $query = Tenant::with(['room', 'bills']);
        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status === 'active' ? 1 : 0);
        }
        $query->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC');

        $query->chunk(100, function ($tenants) use ($handle) {
            foreach ($tenants as $t) {
                $roomNo = $t->room_number ?? ($t->room ? $t->room->room_number : '');
                $floor = $t->room ? $t->room->floor : '';
                $inv = (float) $t->bills->sum('amount');
                $paid = (float) $t->bills->sum('paid_amount');
                $due = (float) $t->bills->sum('due_amount');

                fputcsv($handle, [
                    $t->name,
                    $t->phone ?: '',
                    $t->email ?: '',
                    $roomNo,
                    $floor,
                    $t->commence_date ? $t->commence_date->format('Y-m-d') : '',
                    number_format($t->monthly_rent, 2, '.', ''),
                    number_format($t->security_deposit, 2, '.', ''),
                    $t->eb_start_reading,
                    number_format($inv, 2, '.', ''),
                    number_format($paid, 2, '.', ''),
                    number_format($due, 2, '.', ''),
                    $t->status ? 'Active' : 'Inactive',
                ]);
            }
        });
    }

    private function exportExpensesCsv($handle, Request $request)
    {
        fputcsv($handle, [
            'Expense Date',
            'Expense Type',
            'Room Number',
            'Floor',
            'Category',
            'Title / Item',
            'Amount (INR)',
            'Payment Method',
            'Paid To',
            'Recorded By',
            'Notes',
        ]);

        $query = Expense::with(['room', 'creator'])->latest('expense_date')->latest('id');
        $this->applyDateFilter($query, $request, 'expense_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('expense_type', $request->status);
        }

        $query->chunk(100, function ($expenses) use ($handle) {
            foreach ($expenses as $e) {
                fputcsv($handle, [
                    $e->expense_date ? $e->expense_date->format('Y-m-d') : '',
                    $e->expense_type === 'room' ? 'Room' : 'General',
                    $e->room ? $e->room->room_number : 'Common',
                    $e->room ? $e->room->floor : '',
                    $e->category,
                    $e->title,
                    number_format($e->amount, 2, '.', ''),
                    $e->payment_method,
                    $e->paid_to ?: '',
                    $e->creator ? $e->creator->name : 'Admin',
                    $e->notes ?: '',
                ]);
            }
        });
    }

    private function exportEbCsv($handle, Request $request)
    {
        fputcsv($handle, [
            'Billing Month',
            'Bill Date',
            'Room Number',
            'Floor',
            'Resident Name',
            'Previous Reading',
            'Current Reading',
            'Consumed Units (kWh)',
            'EB Rate (INR/unit)',
            'EB Total Amount (INR)',
            'Bill Status',
        ]);

        $query = Bill::with(['tenant', 'room'])
            ->where(function ($q) {
                $q->whereNotNull('eb_current_reading')
                    ->orWhere('eb_consumed_units', '>', 0)
                    ->orWhere('eb_amount', '>', 0)
                    ->orWhere('bill_type', 'like', '%Electricity%');
            })
            ->latest('bill_date')->latest('id');

        $this->applyDateFilter($query, $request, 'bill_date');

        if ($request->filled('room_id') && $request->room_id !== 'all') {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('tenant_id') && $request->tenant_id !== 'all') {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('floor') && $request->floor !== 'all') {
            $query->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }

        $query->chunk(100, function ($bills) use ($handle) {
            foreach ($bills as $b) {
                fputcsv($handle, [
                    $b->billing_month,
                    $b->bill_date ? $b->bill_date->format('Y-m-d') : '',
                    $b->room ? $b->room->room_number : ($b->tenant->room_number ?? 'N/A'),
                    $b->room ? $b->room->floor : '',
                    $b->tenant ? $b->tenant->name : 'Former Resident',
                    $b->eb_previous_reading,
                    $b->eb_current_reading,
                    $b->eb_consumed_units,
                    $b->eb_unit_rate,
                    number_format($b->eb_amount, 2, '.', ''),
                    ucfirst(str_replace('_', ' ', $b->status)),
                ]);
            }
        });
    }
}
