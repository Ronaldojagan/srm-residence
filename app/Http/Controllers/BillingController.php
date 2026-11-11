<?php

namespace App\Http\Controllers;

use App\Exports\BillsExport;
use App\Exports\PaymentsExport;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Room;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class BillingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Compute current overall & monthly billing statistics
     */
    public function getStatsData()
    {
        // Update any overdue bills automatically
        Bill::where('status', 'unpaid')
            ->whereDate('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        $currentMonth = Carbon::now()->format('F Y');

        // Overall stats
        $totalBilled = (float) Bill::sum('amount');
        $totalCollected = (float) BillPayment::sum('amount');
        $totalDue = (float) Bill::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->sum('due_amount');
        $overdueCount = Bill::where('status', 'overdue')->count();

        // Current Month stats
        $thisMonthBilled = (float) Bill::where('billing_month', $currentMonth)->sum('amount');
        $thisMonthCollected = (float) BillPayment::whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');

        $collectionRate = $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0;

        // Active Leases & Deposits
        $scheduledRent = (float) (Tenant::where('status', 1)->selectRaw('SUM(monthly_rent + maintenance_charges + other_charges) as total')->value('total') ?? 0);
        $baseRent = (float) (Tenant::where('status', 1)->sum('monthly_rent') ?? 0);
        $totalSecurityDeposits = (float) (Tenant::where('status', 1)->sum('security_deposit') ?? 0);

        return [
            'totalBilled' => $totalBilled,
            'totalBilledFormatted' => '₹' . number_format($totalBilled),
            'totalCollected' => $totalCollected,
            'totalCollectedFormatted' => '₹' . number_format($totalCollected),
            'totalDue' => $totalDue,
            'totalDueFormatted' => '₹' . number_format($totalDue),
            'overdueCount' => $overdueCount,
            'thisMonthBilled' => $thisMonthBilled,
            'thisMonthBilledFormatted' => '₹' . number_format($thisMonthBilled),
            'thisMonthCollected' => $thisMonthCollected,
            'thisMonthCollectedFormatted' => '₹' . number_format($thisMonthCollected),
            'collectionRate' => $collectionRate,
            'scheduledRent' => $scheduledRent,
            'scheduledRentFormatted' => '₹' . number_format($scheduledRent),
            'baseRent' => $baseRent,
            'baseRentFormatted' => '₹' . number_format($baseRent),
            'totalSecurityDeposits' => $totalSecurityDeposits,
            'totalSecurityDepositsFormatted' => '₹' . number_format($totalSecurityDeposits),
        ];
    }

    /**
     * AJAX endpoint to return live statistics
     */
    public function getStats()
    {
        return response()->json([
            'success' => true,
            'stats' => $this->getStatsData(),
        ]);
    }

    /**
     * Display Bills & Payments Dashboard
     */
    public function index(Request $request)
    {
        $currentMonth = Carbon::now()->format('F Y');
        $currentMonthValue = Carbon::now()->format('Y-m');

        $stats = $this->getStatsData();

        $activeTenants = Tenant::with(['room', 'rooms', 'bills'])
            ->where('status', 1)
            ->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC')
            ->get();
        $rooms = Room::orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC')->get();
        $settings = app(\App\Service\SettingsService::class)->settingsIndex();

        return view('backend.billing.index', compact('stats', 'activeTenants', 'rooms', 'currentMonth', 'currentMonthValue', 'settings'));
    }

    /**
     * AJAX DataTables for Bills
     */
    public function billsData(Request $request)
    {
        $query = Bill::with(['tenant', 'room'])->latest();

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('bill_type')) {
            $query->where('bill_type', $request->bill_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('billing_month')) {
            $query->where('billing_month', $request->billing_month);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="form-check-input bulk-select-row" value="' . $row->id . '">';
            })
            ->addColumn('bill_number_badge', function ($row) {
                return '<div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark border px-2 py-1 fw-bold font-12">
                        <i class="fas fa-file-invoice text-primary me-1"></i> ' . e($row->bill_number) . '
                    </span>
                </div>';
            })
            ->addColumn('tenant_room', function ($row) {
                $tenantName = $row->tenant ? e($row->tenant->name) : 'Former Tenant';
                $tenantPhone = $row->tenant && $row->tenant->phone ? e($row->tenant->phone) : '-';
                $roomText = $row->room ? 'Room ' . e($row->room->room_number) . ' (' . e($row->room->floor) . ')' : 'Room ' . e($row->tenant->room_number ?? 'N/A');

                return '<div>
                    <span class="fw-bold text-dark d-block">' . $tenantName . '</span>
                    <small class="text-muted d-block"><i class="fas fa-phone-alt me-1 font-10"></i>' . $tenantPhone . '</small>
                    <span class="badge bg-info-subtle text-info border border-info px-1 py-0 mt-1 font-11">
                        <i class="fas fa-door-closed me-1"></i> ' . $roomText . '
                    </span>
                </div>';
            })
            ->addColumn('bill_type_badge', function ($row) {
                $icons = [
                    'Monthly Rent' => 'fas fa-home text-primary',
                    'Electricity' => 'fas fa-bolt text-warning',
                    'Maintenance' => 'fas fa-tools text-info',
                    'Security Deposit' => 'fas fa-shield-alt text-success',
                    'Mess / Food' => 'fas fa-utensils text-danger',
                    'Water' => 'fas fa-tint text-info',
                    'Penalty / Fine' => 'fas fa-exclamation-circle text-danger',
                ];
                $iconClass = $icons[$row->bill_type] ?? 'fas fa-tag text-secondary';

                return '<div class="fw-semibold text-dark">
                    <i class="' . $iconClass . ' me-1 font-12"></i> ' . e($row->bill_type) . '
                    <small class="text-muted d-block font-11">' . e($row->billing_month) . '</small>
                </div>';
            })
            ->addColumn('amount_display', function ($row) {
                return '<span class="fw-bold text-dark font-13">₹' . number_format($row->amount, 2) . '</span>';
            })
            ->addColumn('payment_progress', function ($row) {
                $percent = $row->amount > 0 ? min(100, round(($row->paid_amount / $row->amount) * 100)) : 0;
                $barColor = $percent >= 100 ? 'bg-success' : ($percent > 0 ? 'bg-info' : 'bg-secondary');

                return '<div style="min-width: 130px;">
                    <div class="d-flex justify-content-between font-11 fw-semibold mb-1">
                        <span class="text-success">₹' . number_format($row->paid_amount) . '</span>
                        <span class="text-danger">Due: ₹' . number_format($row->due_amount) . '</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar ' . $barColor . '" role="progressbar" style="width: ' . $percent . '%;" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>';
            })
            ->addColumn('due_date_formatted', function ($row) {
                $due = Carbon::parse($row->due_date);
                $isPast = $due->isPast() && $row->due_amount > 0;
                $days = abs(Carbon::today()->diffInDays($due, false));

                $text = $due->format('d M, Y');
                if ($isPast) {
                    return '<span class="text-danger fw-bold">' . $text . '</span><br><small class="badge bg-danger-subtle text-danger font-10">Overdue ' . $days . 'd</small>';
                }
                return '<span class="text-muted">' . $text . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                switch ($row->status) {
                    case 'paid':
                        return '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1 font-11"></i> Paid</span>';
                    case 'partially_paid':
                        return '<span class="badge bg-info text-white px-2 py-1"><i class="fas fa-adjust me-1 font-11"></i> Partial</span>';
                    case 'overdue':
                        return '<span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-exclamation-triangle me-1 font-11"></i> Overdue</span>';
                    default:
                        return '<span class="badge bg-warning text-white px-2 py-1"><i class="fas fa-clock me-1 font-11"></i> Unpaid</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $payBtn = '';
                if ($row->status !== 'paid') {
                    $payBtn = '<button type="button" class="btn btn-sm btn-outline-success btn-record-payment" 
                                data-id="' . $row->id . '" 
                                data-bill="' . e($row->bill_number) . '"
                                data-tenant="' . e($row->tenant->name ?? 'Tenant') . '" 
                                data-due="' . $row->due_amount . '" 
                                title="Record Payment">
                                <i class="fas fa-credit-card me-1"></i> Pay
                            </button>';
                }

                $viewBtn = '<button type="button" class="btn btn-sm btn-outline-primary btn-view-invoice" data-id="' . $row->id . '" title="View Invoice Preview">
                                <i class="fas fa-eye"></i>
                            </button>';

                $printUrl = url('/admin/billing/bills/' . $row->id . '/print');
                $tenantName = e($row->tenant->name ?? 'Tenant');
                $tenantPhone = e($row->tenant->phone ?? '');
                $tenantEmail = e($row->tenant->email ?? '');
                $roomNo = e($row->room->room_number ?? ($row->tenant->room_number ?? '-'));
                $billNo = e($row->bill_number);
                $month = e($row->billing_month);
                $type = e($row->bill_type);
                $amt = number_format((float)$row->amount, 2);
                $due = number_format((float)$row->amount - (float)$row->paid_amount, 2);
                $dueDate = $row->due_date ? $row->due_date->format('d M, Y') : '-';

                $shareBtn = '<button type="button" class="btn btn-sm btn-outline-info btn-open-share-hub" 
                                data-id="' . $row->id . '"
                                data-bill="' . $billNo . '"
                                data-tenant="' . $tenantName . '"
                                data-phone="' . $tenantPhone . '"
                                data-email="' . $tenantEmail . '"
                                data-room="' . $roomNo . '"
                                data-month="' . $month . '"
                                data-type="' . $type . '"
                                data-amount="' . $amt . '"
                                data-due="' . $due . '"
                                data-duedate="' . $dueDate . '"
                                data-url="' . $printUrl . '"
                                title="Share Invoice (WhatsApp, Apps, Print, Email)">
                                <i class="fas fa-share-alt"></i>
                            </button>';

                $editBtn = '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit-bill" data-id="' . $row->id . '" title="Edit Invoice">
                                <i class="fas fa-edit"></i>
                            </button>';
                $delBtn = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-bill" data-id="' . $row->id . '" data-bill="' . $billNo . '" title="Delete Invoice">
                                <i class="fas fa-trash-alt"></i>
                            </button>';

                return '<div class="btn-group btn-group-sm" role="group">' . $payBtn . $viewBtn . $shareBtn . $editBtn . $delBtn . '</div>';
            })
            ->rawColumns(['checkbox', 'bill_number_badge', 'tenant_room', 'bill_type_badge', 'amount_display', 'payment_progress', 'due_date_formatted', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * AJAX DataTables for Payments
     */
    public function paymentsData(Request $request)
    {
        $query = BillPayment::with(['bill.room', 'tenant', 'receiver'])->latest();

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="form-check-input bulk-select-row" value="' . $row->id . '">';
            })
            ->addColumn('payment_number_badge', function ($row) {
                return '<span class="badge bg-light text-dark border px-2 py-1 fw-bold font-12">
                    <i class="fas fa-receipt text-success me-1"></i> ' . e($row->payment_number) . '
                </span>';
            })
            ->addColumn('bill_reference', function ($row) {
                if ($row->bill) {
                    return '<div>
                        <span class="fw-semibold text-primary">' . e($row->bill->bill_number) . '</span>
                        <small class="text-muted d-block">' . e($row->bill->bill_type) . ' (' . e($row->bill->billing_month) . ')</small>
                    </div>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('tenant_info', function ($row) {
                $name = $row->tenant ? e($row->tenant->name) : 'Tenant';
                $room = $row->bill && $row->bill->room ? 'Room ' . e($row->bill->room->room_number) : '';
                return '<div>
                    <span class="fw-bold text-dark d-block">' . $name . '</span>
                    <small class="text-muted">' . $room . '</small>
                </div>';
            })
            ->addColumn('amount_display', function ($row) {
                return '<span class="fw-bold text-success font-13">+ ₹' . number_format($row->amount, 2) . '</span>';
            })
            ->addColumn('payment_date_formatted', function ($row) {
                return '<span class="text-dark">' . Carbon::parse($row->payment_date)->format('d M, Y') . '</span>';
            })
            ->addColumn('payment_method_badge', function ($row) {
                $badges = [
                    'Cash' => 'bg-secondary text-white',
                    'UPI / GPay' => 'bg-info text-white',
                    'Bank Transfer' => 'bg-primary text-white',
                    'Credit / Debit Card' => 'bg-purple text-white',
                    'Cheque' => 'bg-warning text-dark',
                ];
                $cls = $badges[$row->payment_method] ?? 'bg-secondary text-white';
                $ref = $row->transaction_id ? '<small class="text-muted d-block font-11">Ref: ' . e($row->transaction_id) . '</small>' : '';

                return '<div>
                    <span class="badge rounded-pill ' . $cls . ' px-2 py-1 font-11">' . e($row->payment_method) . '</span>
                    ' . $ref . '
                </div>';
            })
            ->addColumn('action', function ($row) {
                $shareBtn = '<button type="button" class="btn btn-sm btn-outline-success btn-table-share-receipt"
                                data-id="' . $row->id . '"
                                data-receipt="' . e($row->payment_number) . '"
                                data-tenant="' . e($row->tenant->name ?? 'Resident') . '"
                                data-phone="' . e($row->tenant->phone ?? '') . '"
                                data-room="' . e($row->bill->room->room_number ?? ($row->tenant->room_number ?? '-')) . '"
                                data-bill="' . e($row->bill->bill_number ?? '-') . '"
                                data-amount="' . number_format((float)$row->amount, 2) . '"
                                data-method="' . e($row->payment_method) . '"
                                data-tx="' . e($row->transaction_id ?? '-') . '"
                                data-date="' . ($row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format('d M, Y') : '-') . '"
                                data-url="' . url('/admin/billing/payments/' . $row->id . '/print') . '"
                                title="Share Receipt via WhatsApp or Apps">
                                <i class="fab fa-whatsapp me-1"></i> Share
                            </button>';
                $printBtn = '<button type="button" class="btn btn-sm btn-outline-primary btn-view-receipt" data-id="' . $row->id . '" title="View & Print Receipt">
                                <i class="fas fa-print me-1"></i> Receipt
                            </button>';
                $delBtn = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-payment" data-id="' . $row->id . '" data-receipt="' . e($row->payment_number) . '" title="Delete Payment">
                                <i class="fas fa-trash-alt"></i>
                            </button>';

                return '<div class="btn-group btn-group-sm" role="group">' . $shareBtn . $printBtn . $delBtn . '</div>';
            })
            ->rawColumns(['checkbox', 'payment_number_badge', 'bill_reference', 'tenant_info', 'amount_display', 'payment_date_formatted', 'payment_method_badge', 'action'])
            ->make(true);
    }

    /**
     * Store new Bill
     */
    public function storeBill(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'bill_type' => 'required|string|max:100',
            'billing_month' => 'required|string|max:50',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'amount' => 'required|numeric|min:1',
            'eb_previous_reading' => 'nullable|numeric|min:0',
            'eb_current_reading' => 'nullable|numeric|min:0',
            'eb_consumed_units' => 'nullable|numeric|min:0',
            'eb_unit_rate' => 'nullable|numeric|min:0|max:100',
            'eb_amount' => 'nullable|numeric|min:0',
            'items' => 'nullable',
            'notes' => 'nullable|string|max:1000',
            'immediate_payment' => 'nullable|boolean',
            'payment_amount' => 'nullable|numeric|min:1',
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
        ], [
            'tenant_id.required' => 'Please select a resident.',
            'tenant_id.exists' => 'Selected resident does not exist.',
            'bill_type.required' => 'Invoice type is required.',
            'billing_month.required' => 'Billing month is required.',
            'bill_date.required' => 'Bill date is required.',
            'due_date.required' => 'Due date is required.',
            'due_date.after_or_equal' => 'Due date must be on or after the bill issue date.',
            'amount.required' => 'Invoice total amount is required.',
            'amount.min' => 'Invoice total amount must be at least ₹1.00.',
            'eb_previous_reading.min' => 'Previous EB reading cannot be negative.',
            'eb_current_reading.min' => 'Current EB reading cannot be negative.',
            'eb_unit_rate.min' => 'EB unit rate cannot be negative.',
            'payment_amount.min' => 'Payment amount must be at least ₹1.00.',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        $roomId = $tenant->room_id;

        // Check EB current vs previous reading
        if ($request->filled('eb_current_reading') && $request->filled('eb_previous_reading')) {
            $prev = (float) $request->input('eb_previous_reading');
            $curr = (float) $request->input('eb_current_reading');
            if ($curr < $prev) {
                return response()->json([
                    'success' => false,
                    'message' => "Current EB meter reading ({$curr}) cannot be less than previous reading ({$prev}).",
                    'errors' => ['eb_current_reading' => ["Current meter reading ({$curr}) cannot be less than previous reading ({$prev})."]]
                ], 422);
            }
        }

        // Check duplicate invoice for the same resident, bill type, and month
        $existingInvoice = Bill::where('tenant_id', $tenant->id)
            ->where('bill_type', $validated['bill_type'])
            ->where('billing_month', $validated['billing_month'])
            ->first();

        if ($existingInvoice) {
            return response()->json([
                'success' => false,
                'message' => "An invoice for '{$validated['bill_type']}' for '{$validated['billing_month']}' already exists for {$tenant->name} ({$existingInvoice->bill_number}).",
                'errors' => ['billing_month' => ["Invoice already exists for {$tenant->name} ({$existingInvoice->bill_number})."]]
            ], 422);
        }

        // Immediate payment validation
        if (!empty($validated['immediate_payment'])) {
            $paidAmt = (float) $request->input('payment_amount', $validated['amount']);
            if ($paidAmt <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Immediate payment amount must be at least ₹1.00.',
                    'errors' => ['payment_amount' => ['Payment amount must be at least ₹1.00.']]
                ], 422);
            }
            if ($paidAmt > (float) $validated['amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Immediate payment amount cannot exceed the total invoice amount of ₹' . number_format($validated['amount'], 2) . '.',
                    'errors' => ['payment_amount' => ['Payment amount cannot exceed the total invoice amount.']]
                ], 422);
            }
        }

        $items = $request->input('items');
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        $cleanItems = [];
        if (is_array($items)) {
            foreach ($items as $item) {
                if (!empty($item['title'])) {
                    $cleanItems[] = [
                        'title' => trim($item['title']),
                        'type' => $item['type'] ?? 'General',
                        'amount' => isset($item['amount']) ? (float) $item['amount'] : 0.00,
                        'details' => $item['details'] ?? null,
                    ];
                }
            }
        }

        $bill = Bill::create([
            'bill_number' => Bill::generateBillNumber(),
            'tenant_id' => $tenant->id,
            'room_id' => $roomId,
            'bill_type' => $validated['bill_type'],
            'billing_month' => $validated['billing_month'],
            'bill_date' => $validated['bill_date'],
            'due_date' => $validated['due_date'],
            'amount' => $validated['amount'],
            'eb_previous_reading' => $request->filled('eb_previous_reading') ? (float) $request->input('eb_previous_reading') : null,
            'eb_current_reading' => $request->filled('eb_current_reading') ? (float) $request->input('eb_current_reading') : null,
            'eb_consumed_units' => $request->filled('eb_consumed_units') ? (float) $request->input('eb_consumed_units') : null,
            'eb_unit_rate' => $request->filled('eb_unit_rate') ? (float) $request->input('eb_unit_rate') : null,
            'eb_amount' => $request->filled('eb_amount') ? (float) $request->input('eb_amount') : null,
            'items' => !empty($cleanItems) ? $cleanItems : null,
            'paid_amount' => 0.00,
            'due_amount' => $validated['amount'],
            'status' => Carbon::parse($validated['due_date'])->isPast() ? 'overdue' : 'unpaid',
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // If user recorded immediate full or partial payment
        if (!empty($validated['immediate_payment'])) {
            $paidAmt = $request->input('payment_amount', $validated['amount']);
            if ($paidAmt > 0) {
                BillPayment::create([
                    'payment_number' => BillPayment::generatePaymentNumber(),
                    'bill_id' => $bill->id,
                    'tenant_id' => $tenant->id,
                    'amount' => min($paidAmt, $validated['amount']),
                    'payment_date' => $validated['bill_date'],
                    'payment_method' => $validated['payment_method'] ?? 'Cash',
                    'transaction_id' => $validated['transaction_id'] ?? null,
                    'notes' => 'Initial payment upon invoice creation.',
                    'received_by' => Auth::id(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice ' . $bill->bill_number . ' generated successfully!',
            'bill' => $bill->fresh(['tenant', 'room']),
            'stats' => $this->getStatsData(),
        ]);
    }

    /**
     * Batch generate monthly rent bills for all active tenants
     */
    public function quickGenerateMonthlyRent(Request $request)
    {
        $validated = $request->validate([
            'billing_month' => 'required|string|max:50',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
        ]);

        $tenants = Tenant::with('room')
            ->where('status', 1)
            ->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC')
            ->get();
        if ($tenants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active tenants found to generate bills for.',
            ], 422);
        }

        $created = 0;
        $skipped = 0;

        foreach ($tenants as $t) {
            // Check if monthly rent bill already exists for this tenant & month
            $exists = Bill::where('tenant_id', $t->id)
                ->where('bill_type', 'Monthly Rent')
                ->where('billing_month', $validated['billing_month'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $rent = (float) $t->monthly_rent + (float) $t->maintenance_charges + (float) $t->other_charges;
            if ($rent <= 0 && $t->room) {
                $rent = (float) $t->room->total_monthly;
            }
            if ($rent <= 0) {
                $rent = 5000.00; // Default fallback
            }

            $items = [
                ['title' => 'Room Monthly Rent', 'type' => 'Rent', 'amount' => (float) $t->monthly_rent],
            ];
            if ((float) $t->maintenance_charges > 0) {
                $items[] = ['title' => 'Maintenance Charges', 'type' => 'Maintenance', 'amount' => (float) $t->maintenance_charges];
            }
            if ((float) $t->other_charges > 0) {
                $items[] = ['title' => 'Other Charges', 'type' => 'Other', 'amount' => (float) $t->other_charges];
            }

            Bill::create([
                'bill_number' => Bill::generateBillNumber(),
                'tenant_id' => $t->id,
                'room_id' => $t->room_id,
                'bill_type' => 'Monthly Rent',
                'billing_month' => $validated['billing_month'],
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'],
                'amount' => $rent,
                'items' => $items,
                'paid_amount' => 0.00,
                'due_amount' => $rent,
                'status' => Carbon::parse($validated['due_date'])->isPast() ? 'overdue' : 'unpaid',
                'notes' => 'Automated monthly rent invoice for ' . $validated['billing_month'],
                'created_by' => Auth::id(),
            ]);

            $created++;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully generated {$created} rent invoice(s). " . ($skipped > 0 ? "({$skipped} tenant(s) already had invoices for this month)." : ""),
            'created_count' => $created,
            'skipped_count' => $skipped,
            'stats' => $this->getStatsData(),
        ]);
    }

    /**
     * Record a Payment against an Invoice
     */
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'bill_id.required' => 'Invoice reference is required.',
            'bill_id.exists' => 'Selected invoice does not exist.',
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be at least ₹1.00.',
            'payment_date.required' => 'Payment date is required.',
            'payment_method.required' => 'Please select a payment method.',
            'receipt.mimes' => 'Receipt must be a JPG, PNG, or PDF file.',
            'receipt.max' => 'Receipt file size may not exceed 5MB.',
        ]);

        $bill = Bill::findOrFail($validated['bill_id']);

        if ($bill->status === 'paid' && $bill->due_amount <= 0.001) {
            return response()->json([
                'success' => false,
                'message' => 'This invoice is already fully paid.',
                'errors' => ['amount' => ['This invoice is already fully paid.']]
            ], 422);
        }

        if ((float) $validated['amount'] > ((float) $bill->due_amount + 0.01)) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount (₹' . number_format($validated['amount'], 2) . ') cannot exceed the pending due amount of ₹' . number_format($bill->due_amount, 2) . '.',
                'errors' => ['amount' => ['Payment amount cannot exceed the pending due amount of ₹' . number_format($bill->due_amount, 2) . '.']]
            ], 422);
        }

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $fileName = 'receipt_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $receiptPath = $file->storeAs('receipts', $fileName, 'public');
        }

        $payment = BillPayment::create([
            'payment_number' => BillPayment::generatePaymentNumber(),
            'bill_id' => $bill->id,
            'tenant_id' => $bill->tenant_id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'receipt' => $receiptPath,
            'notes' => $validated['notes'] ?? null,
            'received_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment of ₹' . number_format($payment->amount, 2) . ' recorded successfully!',
            'payment' => $payment->fresh(['bill', 'tenant']),
            'bill' => $bill->fresh(),
            'stats' => $this->getStatsData(),
        ]);
    }

    /**
     * Get Bill Details for Invoice modal
     */
    public function getBillDetails($id)
    {
        $bill = Bill::with(['tenant.room', 'room', 'payments.receiver'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'bill' => $bill,
            'formatted' => [
                'bill_date' => Carbon::parse($bill->bill_date)->format('d M, Y'),
                'due_date' => Carbon::parse($bill->due_date)->format('d M, Y'),
                'amount' => number_format($bill->amount, 2),
                'paid_amount' => number_format($bill->paid_amount, 2),
                'due_amount' => number_format($bill->due_amount, 2),
                'eb_previous_reading' => $bill->eb_previous_reading !== null ? number_format($bill->eb_previous_reading, 2) : null,
                'eb_current_reading' => $bill->eb_current_reading !== null ? number_format($bill->eb_current_reading, 2) : null,
                'eb_consumed_units' => $bill->eb_consumed_units !== null ? number_format($bill->eb_consumed_units, 2) : null,
                'eb_unit_rate' => $bill->eb_unit_rate !== null ? number_format($bill->eb_unit_rate, 2) : null,
                'eb_amount' => $bill->eb_amount !== null ? number_format($bill->eb_amount, 2) : null,
            ]
        ]);
    }

    /**
     * Get Tenant Billing Profile & Latest Reading via AJAX
     */
    public function getTenantBillingInfo($tenantId)
    {
        $tenant = Tenant::with('room')->findOrFail($tenantId);

        return response()->json([
            'success' => true,
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'phone' => $tenant->phone,
                'room_id' => $tenant->room_id,
                'room_number' => $tenant->room ? $tenant->room->room_number : $tenant->room_number,
                'room_type' => $tenant->room_type,
                'monthly_rent' => (float) $tenant->monthly_rent,
                'maintenance_charges' => (float) $tenant->maintenance_charges,
                'other_charges' => (float) $tenant->other_charges,
                'total_monthly_due' => (float) $tenant->total_monthly_due,
                'eb_unit_rate' => (float) ($tenant->eb_unit_rate ?? 10.00),
                'eb_start_reading' => (float) ($tenant->eb_start_reading ?? 0.00),
                'eb_previous_reading' => (float) $tenant->last_eb_reading,
            ]
        ]);
    }

    /**
     * Get Payment Details for Receipt modal
     */
    public function getPaymentDetails($id)
    {
        $payment = BillPayment::with(['bill.room', 'tenant', 'receiver'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'payment' => $payment,
            'formatted' => [
                'payment_date' => Carbon::parse($payment->payment_date)->format('d M, Y'),
                'amount' => number_format($payment->amount, 2),
            ]
        ]);
    }

    /**
     * Dedicated printable invoice view
     */
    public function printInvoice($id)
    {
        $bill = Bill::with(['tenant.room', 'tenant.rooms', 'room', 'payments.receiver'])->findOrFail($id);
        $settingsService = app(\App\Service\SettingsService::class);
        $settings = $settingsService->settingsIndex();

        return view('backend.billing.invoice_print', compact('bill', 'settings'));
    }

    /**
     * Dedicated printable payment receipt view
     */
    public function printReceipt($id)
    {
        $payment = BillPayment::with(['bill.room', 'tenant.room', 'receiver'])->findOrFail($id);
        $settingsService = app(\App\Service\SettingsService::class);
        $settings = $settingsService->settingsIndex();

        return view('backend.billing.receipt_print', compact('payment', 'settings'));
    }

    /**
     * Update an existing Bill
     */
    public function updateBill(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);
        $minAmount = max(1, (float) $bill->paid_amount);

        $validated = $request->validate([
            'bill_type' => 'required|string|max:100',
            'billing_month' => 'required|string|max:50',
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:' . $minAmount,
            'notes' => 'nullable|string|max:1000',
        ], [
            'bill_type.required' => 'Invoice type is required.',
            'billing_month.required' => 'Billing month is required.',
            'due_date.required' => 'Due date is required.',
            'amount.required' => 'Invoice amount is required.',
            'amount.min' => 'Invoice amount cannot be less than the already collected amount of ₹' . number_format($minAmount, 2) . '.',
        ]);

        $bill->update([
            'bill_type' => $validated['bill_type'],
            'billing_month' => $validated['billing_month'],
            'due_date' => $validated['due_date'],
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $bill->recalculatePaymentStatus();

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully!',
            'bill' => $bill->fresh(),
            'stats' => $this->getStatsData(),
        ]);
    }

    /**
     * Delete a Bill
     */
    public function destroyBill($id)
    {
        $bill = Bill::findOrFail($id);
        $billNum = $bill->bill_number;
        
        // Soft delete associated payments first
        $bill->payments()->delete();
        $bill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice ' . $billNum . ' and related payments deleted successfully.',
            'stats' => $this->getStatsData(),
        ]);
    }

    /**
     * Delete a Payment
     */
    public function destroyPayment($id)
    {
        $payment = BillPayment::findOrFail($id);
        $payNum = $payment->payment_number;
        $bill = $payment->bill;

        $payment->delete();

        if ($bill) {
            $bill->recalculatePaymentStatus();
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment receipt ' . $payNum . ' deleted. Invoice balance updated.',
            'stats' => $this->getStatsData(),
        ]);
    }

    /**
     * Send Invoice via Email to Resident
     */
    public function sendInvoiceEmail(Request $request, $id)
    {
        try {
            $bill = Bill::with(['tenant.room', 'tenant.rooms', 'room', 'payments'])->findOrFail($id);

            $email = trim($request->input('email', ''));
            if (empty($email)) {
                $email = $bill->tenant?->email;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a valid recipient email address for this tenant.',
                ], 422);
            }

            $settings = app(\App\Service\SettingsService::class)->settingsIndex();
            \App\Service\SettingsService::configureMailer();

            Mail::to($email)->send(new \App\Mail\InvoiceMail($bill, $settings));

            return response()->json([
                'success' => true,
                'message' => "Invoice #{$bill->bill_number} sent successfully to {$email}!",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export Invoices & Bills to Excel (.xlsx) with wide auto-sized columns and total amounts in the last row
     */
    public function exportBills(Request $request)
    {
        $fileName = 'srm_residence_invoices_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new BillsExport($request), $fileName);
    }

    /**
     * Export Payment Receipts to Excel (.xlsx) with wide auto-sized columns and total amounts in the last row
     */
    public function exportPayments(Request $request)
    {
        $fileName = 'srm_residence_payments_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new PaymentsExport($request), $fileName);
    }

    /**
     * Bulk Delete Bills
     */
    public function bulkDeleteBills(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:bills,id',
        ]);

        $count = count($validated['ids']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            BillPayment::whereIn('bill_id', $validated['ids'])->delete();
            Bill::whereIn('id', $validated['ids'])->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} invoice(s) and related payments!",
            'stats' => $this->getStatsData(),
        ]);
    }

    /**
     * Bulk Update Bills
     */
    public function bulkUpdateBills(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:bills,id',
            'bill_type' => 'nullable|string|max:100',
            'billing_month' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $updateData = [];
        if (!empty($validated['bill_type'])) $updateData['bill_type'] = $validated['bill_type'];
        if (!empty($validated['billing_month'])) $updateData['billing_month'] = $validated['billing_month'];
        if (!empty($validated['due_date'])) $updateData['due_date'] = $validated['due_date'];
        if (isset($validated['notes']) && $validated['notes'] !== '') $updateData['notes'] = $validated['notes'];

        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No fields provided to update.'
            ], 422);
        }

        $count = count($validated['ids']);
        Bill::whereIn('id', $validated['ids'])->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$count} invoice(s)!",
            'stats' => $this->getStatsData(),
        ]);
    }

    /**
     * Bulk Delete Payment Receipts
     */
    public function bulkDeletePayments(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:bill_payments,id',
        ]);

        $count = count($validated['ids']);
        $payments = BillPayment::whereIn('id', $validated['ids'])->get();
        $billIds = $payments->pluck('bill_id')->unique()->filter();

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $billIds) {
            BillPayment::whereIn('id', $validated['ids'])->delete();
            foreach ($billIds as $bId) {
                $b = Bill::find($bId);
                if ($b) $b->recalculatePaymentStatus();
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} payment receipt(s) and refreshed invoice balances!",
            'stats' => $this->getStatsData(),
        ]);
    }
}

