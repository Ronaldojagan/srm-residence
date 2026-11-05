<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Room;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        $activeTenants = Tenant::with(['room', 'bills'])->where('status', 1)->orderBy('name')->get();
        $rooms = Room::orderBy('room_number')->get();

        return view('backend.billing.index', compact('stats', 'activeTenants', 'rooms', 'currentMonth', 'currentMonthValue'));
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

                $viewBtn = '<button type="button" class="btn btn-sm btn-outline-primary btn-view-invoice" data-id="' . $row->id . '" title="View & Print Invoice">
                                <i class="fas fa-print"></i>
                            </button>';
                $editBtn = '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit-bill" data-id="' . $row->id . '" title="Edit Bill">
                                <i class="fas fa-edit"></i>
                            </button>';
                $delBtn = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-bill" data-id="' . $row->id . '" data-bill="' . e($row->bill_number) . '" title="Delete Bill">
                                <i class="fas fa-trash-alt"></i>
                            </button>';

                return '<div class="btn-group btn-group-sm" role="group">' . $payBtn . $viewBtn . $editBtn . $delBtn . '</div>';
            })
            ->rawColumns(['bill_number_badge', 'tenant_room', 'bill_type_badge', 'amount_display', 'payment_progress', 'due_date_formatted', 'status_badge', 'action'])
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
                $printBtn = '<button type="button" class="btn btn-sm btn-outline-primary btn-view-receipt" data-id="' . $row->id . '" title="View & Print Receipt">
                                <i class="fas fa-print me-1"></i> Receipt
                            </button>';
                $delBtn = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-payment" data-id="' . $row->id . '" data-receipt="' . e($row->payment_number) . '" title="Delete Payment">
                                <i class="fas fa-trash-alt"></i>
                            </button>';

                return '<div class="btn-group btn-group-sm" role="group">' . $printBtn . $delBtn . '</div>';
            })
            ->rawColumns(['payment_number_badge', 'bill_reference', 'tenant_info', 'amount_display', 'payment_date_formatted', 'payment_method_badge', 'action'])
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
            'eb_unit_rate' => 'nullable|numeric|min:0',
            'eb_amount' => 'nullable|numeric|min:0',
            'items' => 'nullable',
            'notes' => 'nullable|string|max:1000',
            'immediate_payment' => 'nullable|boolean',
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        $roomId = $tenant->room_id;

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

        $tenants = Tenant::with('room')->where('status', 1)->get();
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
            'amount' => 'required|numeric|min:0.5',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $bill = Bill::findOrFail($validated['bill_id']);

        if ($bill->status === 'paid' && $bill->due_amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This invoice is already fully paid.',
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
     * Update an existing Bill
     */
    public function updateBill(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);

        $validated = $request->validate([
            'bill_type' => 'required|string|max:100',
            'billing_month' => 'required|string|max:50',
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:' . $bill->paid_amount,
            'notes' => 'nullable|string|max:1000',
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
}

