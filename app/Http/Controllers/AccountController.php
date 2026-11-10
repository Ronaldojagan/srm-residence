<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\LoanEmi;
use App\Models\Room;
use App\Models\Salary;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Default index: Redirect to Journal
     */
    public function index()
    {
        return redirect()->route('accounts.journal');
    }

    /* =========================================================================
     * 1. JOURNAL & CASH BOOK
     * ========================================================================= */

    /**
     * Display Journal & Cash Book View
     */
    public function journal(Request $request)
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

        $stats = $this->calculateJournalStats($startOfMonth, $endOfMonth);

        $defaultCategories = [
            'Direct Hostel Income',
            'Capital Introduced',
            'Owner Drawings',
            'Bank Interest',
            'Bank Charges',
            'Scrap / Waste Sale',
            'Laundry / Vending',
            'Cash Deposit / Contra',
            'Cash Withdrawal / Contra',
            'Asset Purchase',
            'Repair & Maintenance',
            'Miscellaneous Expense',
            'General',
        ];

        return view('backend.accounts.journal', compact('stats', 'startOfMonth', 'endOfMonth', 'defaultCategories'));
    }

    /**
     * Server-side DataTables AJAX for Unified Journal
     */
    public function journalData(Request $request)
    {
        $sql = $this->getUnifiedJournalSql();
        $query = DB::table(DB::raw("({$sql}) as ledger"));

        // Date filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('trans_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('trans_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('trans_date', '<=', $request->end_date);
        }

        // Flow type (income / expense)
        if ($request->filled('flow_type') && in_array($request->flow_type, ['income', 'expense'])) {
            $query->where('flow_type', $request->flow_type);
        }

        // Module / Source type filter
        if ($request->filled('source_type') && $request->source_type !== 'all') {
            $query->where('source_type', $request->source_type);
        }

        // Payment method filter
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        return DataTables::of($query)
            ->editColumn('trans_date', function ($row) {
                $dt = Carbon::parse($row->trans_date);
                return '<div class="fw-semibold text-dark font-13">' . $dt->format('d M, Y') . '</div>
                        <small class="text-muted font-11"><i class="far fa-clock me-1"></i>' . $dt->diffForHumans() . '</small>';
            })
            ->addColumn('voucher_display', function ($row) {
                $icon = match ($row->source_type) {
                    'bill_payment' => 'fas fa-receipt text-success',
                    'expense' => 'fas fa-wallet text-danger',
                    'salary' => 'fas fa-hand-holding-dollar text-success',
                    'loan_emi' => 'fas fa-hand-holding-usd text-warning',
                    'journal' => 'fas fa-file-invoice text-info',
                    default => 'fas fa-file-alt text-secondary',
                };
                return '<div class="d-flex align-items-center gap-2">
                            <i class="' . $icon . ' font-14"></i>
                            <span class="font-monospace fw-bold text-dark font-12">' . e($row->voucher_no) . '</span>
                        </div>';
            })
            ->addColumn('flow_badge', function ($row) {
                if ($row->flow_type === 'income') {
                    return '<span class="badge bg-success-subtle text-success border border-success px-2 py-1 font-12 fw-semibold">
                                <i class="fas fa-arrow-down me-1"></i> INFLOW (Cr)
                            </span>';
                } else {
                    return '<span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1 font-12 fw-semibold">
                                <i class="fas fa-arrow-up me-1"></i> OUTFLOW (Dr)
                            </span>';
                }
            })
            ->addColumn('category_display', function ($row) {
                $primaryBadge = match ($row->source_type) {
                    'bill_payment' => 'badge bg-success-subtle text-success',
                    'expense' => 'badge bg-danger-subtle text-danger',
                    'salary' => 'badge bg-success-subtle text-success',
                    'loan_emi' => 'badge bg-warning-subtle text-warning',
                    'journal' => 'badge bg-info-subtle text-info',
                    default => 'badge bg-secondary-subtle text-secondary',
                };
                return '<div>
                            <span class="' . $primaryBadge . ' px-2 py-0 font-11 fw-semibold mb-1 d-inline-block">' . e($row->primary_category) . '</span>
                            <div class="fw-semibold text-dark font-12">' . e($row->sub_category) . '</div>
                        </div>';
            })
            ->addColumn('party_display', function ($row) {
                $notes = $row->notes ? '<small class="text-muted d-block font-11 text-truncate" style="max-width: 200px;" title="' . e($row->notes) . '"><i class="far fa-sticky-note me-1"></i>' . e($row->notes) . '</small>' : '';
                return '<div>
                            <span class="fw-bold text-dark font-13">' . e($row->party_name) . '</span>
                            ' . $notes . '
                        </div>';
            })
            ->addColumn('payment_method_badge', function ($row) {
                $method = $row->payment_method ?: 'Cash';
                $badgeClass = match (strtolower($method)) {
                    'cash' => 'bg-secondary-subtle text-secondary border border-secondary',
                    'upi', 'gpay', 'phonepe', 'paytm' => 'bg-success-subtle text-success border border-success',
                    'bank transfer', 'neft', 'rtgs', 'imps' => 'bg-info-subtle text-info border border-info',
                    'cheque' => 'bg-warning-subtle text-warning border border-warning',
                    default => 'bg-light text-dark border',
                };
                return '<span class="badge ' . $badgeClass . ' px-2 py-1 font-11">
                            <i class="fas fa-credit-card me-1 font-10"></i>' . e($method) . '
                        </span>';
            })
            ->addColumn('inflow_amount', function ($row) {
                if ($row->flow_type === 'income') {
                    return '<span class="fw-bold text-success font-13">+ ₹' . number_format((float) $row->amount, 2) . '</span>';
                }
                return '<span class="text-muted font-12">-</span>';
            })
            ->addColumn('outflow_amount', function ($row) {
                if ($row->flow_type === 'expense') {
                    return '<span class="fw-bold text-danger font-13">- ₹' . number_format((float) $row->amount, 2) . '</span>';
                }
                return '<span class="text-muted font-12">-</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row->source_type === 'journal') {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-journal" data-id="' . $row->id . '" title="Edit Voucher">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-journal" data-id="' . $row->id . '" data-voucher="' . e($row->voucher_no) . '" title="Delete Voucher">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>';
                } elseif ($row->source_type === 'bill_payment') {
                    return '<a href="' . url('/admin/billing/payments/' . $row->id . '/print') . '" target="_blank" class="btn btn-sm btn-outline-primary" title="Print Receipt">
                                <i class="fas fa-print"></i>
                            </a>';
                } elseif ($row->source_type === 'salary') {
                    return '<a href="' . url('/admin/salary/' . $row->id . '/voucher') . '" target="_blank" class="btn btn-sm btn-outline-info" title="View Payslip Voucher">
                                <i class="fas fa-file-invoice"></i>
                            </a>';
                } elseif ($row->source_type === 'loan_emi') {
                    return '<a href="' . url('/admin/loans') . '" class="btn btn-sm btn-outline-warning" title="View Loan Details">
                                <i class="fas fa-external-link-alt"></i>
                            </a>';
                } else {
                    return '<button type="button" class="btn btn-sm btn-outline-secondary disabled" title="System Logged">
                                <i class="fas fa-check"></i>
                            </button>';
                }
            })
            ->rawColumns(['trans_date', 'voucher_display', 'flow_badge', 'category_display', 'party_display', 'payment_method_badge', 'inflow_amount', 'outflow_amount', 'action'])
            ->make(true);
    }

    /**
     * AJAX Stats for Journal metric cards based on selected filter
     */
    public function journalStats(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $flowType = $request->get('flow_type');
        $sourceType = $request->get('source_type');
        $paymentMethod = $request->get('payment_method');

        $stats = $this->calculateJournalStats($startDate, $endDate, $flowType, $sourceType, $paymentMethod);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Calculate Summary Statistics for Journal & All-Time Reserves
     */
    private function calculateJournalStats(?string $startDate = null, ?string $endDate = null, ?string $flowType = null, ?string $sourceType = null, ?string $paymentMethod = null): array
    {
        $sql = $this->getUnifiedJournalSql();

        // 1. All-time global totals (for "Balance Amount We Have")
        $allTimeData = DB::select("
            SELECT 
                COALESCE(SUM(CASE WHEN flow_type = 'income' THEN amount ELSE 0 END), 0) AS total_inflow,
                COALESCE(SUM(CASE WHEN flow_type = 'expense' THEN amount ELSE 0 END), 0) AS total_outflow,
                COALESCE(SUM(CASE WHEN flow_type = 'income' AND LOWER(payment_method) = 'cash' THEN amount 
                                  WHEN flow_type = 'expense' AND LOWER(payment_method) = 'cash' THEN -amount 
                                  ELSE 0 END), 0) AS cash_in_hand,
                COALESCE(SUM(CASE WHEN flow_type = 'income' AND LOWER(payment_method) != 'cash' THEN amount 
                                  WHEN flow_type = 'expense' AND LOWER(payment_method) != 'cash' THEN -amount 
                                  ELSE 0 END), 0) AS bank_balance
            FROM ({$sql}) AS ledger
        ")[0];

        $allTimeInflow = (float) $allTimeData->total_inflow;
        $allTimeOutflow = (float) $allTimeData->total_outflow;
        $currentBalance = $allTimeInflow - $allTimeOutflow;
        $cashInHand = (float) $allTimeData->cash_in_hand;
        $bankBalance = (float) $allTimeData->bank_balance;

        // 2. Filtered totals for the selected period
        $filteredQuery = DB::table(DB::raw("({$sql}) as ledger"));

        if ($startDate && $endDate) {
            $filteredQuery->whereBetween('trans_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $filteredQuery->where('trans_date', '>=', $startDate);
        } elseif ($endDate) {
            $filteredQuery->where('trans_date', '<=', $endDate);
        }

        if ($flowType && in_array($flowType, ['income', 'expense'])) {
            $filteredQuery->where('flow_type', $flowType);
        }
        if ($sourceType && $sourceType !== 'all') {
            $filteredQuery->where('source_type', $sourceType);
        }
        if ($paymentMethod && $paymentMethod !== 'all') {
            $filteredQuery->where('payment_method', $paymentMethod);
        }

        $filteredInflow = (float) (clone $filteredQuery)->where('flow_type', 'income')->sum('amount');
        $filteredOutflow = (float) (clone $filteredQuery)->where('flow_type', 'expense')->sum('amount');
        $filteredNet = $filteredInflow - $filteredOutflow;
        $transactionCount = (int) $filteredQuery->count();

        return [
            // All-Time Liquidity (Real-Time Balance We Have)
            'currentBalance' => $currentBalance,
            'currentBalanceFormatted' => '₹' . number_format($currentBalance, 2),
            'cashInHand' => $cashInHand,
            'cashInHandFormatted' => '₹' . number_format($cashInHand, 2),
            'bankBalance' => $bankBalance,
            'bankBalanceFormatted' => '₹' . number_format($bankBalance, 2),
            'allTimeInflow' => $allTimeInflow,
            'allTimeInflowFormatted' => '₹' . number_format($allTimeInflow, 2),
            'allTimeOutflow' => $allTimeOutflow,
            'allTimeOutflowFormatted' => '₹' . number_format($allTimeOutflow, 2),

            // Filtered Period Stats
            'filteredInflow' => $filteredInflow,
            'filteredInflowFormatted' => '₹' . number_format($filteredInflow, 2),
            'filteredOutflow' => $filteredOutflow,
            'filteredOutflowFormatted' => '₹' . number_format($filteredOutflow, 2),
            'filteredNet' => $filteredNet,
            'filteredNetFormatted' => ($filteredNet >= 0 ? '+ ₹' : '- ₹') . number_format(abs($filteredNet), 2),
            'filteredNetIsPositive' => $filteredNet >= 0,
            'transactionCount' => $transactionCount,
        ];
    }

    /**
     * Store Manual Journal Voucher Entry
     */
    public function storeJournalEntry(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'entry_type' => 'required|string|in:income,expense,capital,drawing,transfer',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:200',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'account_name' => 'nullable|string|max:100',
            'reference_no' => 'nullable|string|max:100',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('journal_attachments', 'public');
        }

        $entryNumber = JournalEntry::generateEntryNumber();

        $entry = JournalEntry::create([
            'entry_number' => $entryNumber,
            'entry_date' => $validated['entry_date'],
            'entry_type' => $validated['entry_type'],
            'category' => $validated['category'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'account_name' => $validated['account_name'] ?? null,
            'reference_no' => $validated['reference_no'] ?? null,
            'attachment' => $filePath,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Journal voucher ' . $entry->entry_number . ' recorded successfully!',
            'entry' => $entry
        ]);
    }

    /**
     * Get Journal Entry data for editing
     */
    public function getJournalEntryData(JournalEntry $entry)
    {
        return response()->json([
            'success' => true,
            'entry' => [
                'id' => $entry->id,
                'entry_number' => $entry->entry_number,
                'entry_date' => $entry->entry_date ? $entry->entry_date->format('Y-m-d') : '',
                'entry_type' => $entry->entry_type,
                'category' => $entry->category,
                'title' => $entry->title,
                'amount' => (float) $entry->amount,
                'payment_method' => $entry->payment_method,
                'account_name' => $entry->account_name,
                'reference_no' => $entry->reference_no,
                'notes' => $entry->notes,
                'attachment_url' => $entry->attachment ? asset('storage/' . $entry->attachment) : null,
            ]
        ]);
    }

    /**
     * Update Journal Entry
     */
    public function updateJournalEntry(Request $request, JournalEntry $entry)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'entry_type' => 'required|string|in:income,expense,capital,drawing,transfer',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:200',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'account_name' => 'nullable|string|max:100',
            'reference_no' => 'nullable|string|max:100',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('attachment')) {
            if ($entry->attachment) {
                Storage::disk('public')->delete($entry->attachment);
            }
            $entry->attachment = $request->file('attachment')->store('journal_attachments', 'public');
        }

        $entry->update([
            'entry_date' => $validated['entry_date'],
            'entry_type' => $validated['entry_type'],
            'category' => $validated['category'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'account_name' => $validated['account_name'] ?? null,
            'reference_no' => $validated['reference_no'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Journal voucher ' . $entry->entry_number . ' updated successfully!',
            'entry' => $entry
        ]);
    }

    /**
     * Delete Journal Entry
     */
    public function destroyJournalEntry(JournalEntry $entry)
    {
        $num = $entry->entry_number;
        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Journal voucher ' . $num . ' deleted successfully!'
        ]);
    }

    /**
     * Export Journal to CSV
     */
    public function exportJournal(Request $request): StreamedResponse
    {
        $sql = $this->getUnifiedJournalSql();
        $query = DB::table(DB::raw("({$sql}) as ledger"));

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('trans_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('flow_type') && in_array($request->flow_type, ['income', 'expense'])) {
            $query->where('flow_type', $request->flow_type);
        }
        if ($request->filled('source_type') && $request->source_type !== 'all') {
            $query->where('source_type', $request->source_type);
        }
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        $records = $query->orderBy('trans_date', 'desc')->get();

        $filename = 'srm_journal_' . date('Y_m_d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['Date', 'Voucher Number', 'Flow Type', 'Module', 'Category', 'Party / Description', 'Payment Mode', 'Credit (Inflow ₹)', 'Debit (Outflow ₹)', 'Reference Code', 'Notes']);

            foreach ($records as $r) {
                $credit = $r->flow_type === 'income' ? $r->amount : '0.00';
                $debit = $r->flow_type === 'expense' ? $r->amount : '0.00';
                fputcsv($handle, [
                    $r->trans_date,
                    $r->voucher_no,
                    strtoupper($r->flow_type),
                    $r->primary_category,
                    $r->sub_category,
                    $r->party_name,
                    $r->payment_method ?: 'Cash',
                    $credit,
                    $debit,
                    $r->ref_code,
                    $r->notes,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /* =========================================================================
     * 2. PROFIT & LOSS (P&L) STATEMENT
     * ========================================================================= */

    /**
     * Display Profit & Loss View
     */
    public function pnl(Request $request)
    {
        $now = Carbon::now();
        $startDate = $request->get('start_date', $now->copy()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', $now->copy()->endOfMonth()->format('Y-m-d'));
        $preset = $request->get('preset', 'this_month');

        $pnl = $this->calculatePnlData($startDate, $endDate);

        return view('backend.accounts.pnl', compact('pnl', 'startDate', 'endDate', 'preset'));
    }

    /**
     * AJAX endpoint for dynamic P&L updates
     */
    public function pnlData(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!$startDate || !$endDate) {
            $now = Carbon::now();
            $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        }

        $pnl = $this->calculatePnlData($startDate, $endDate);

        return response()->json([
            'success' => true,
            'pnl' => $pnl
        ]);
    }

    /**
     * Calculate Detailed Profit & Loss Data
     */
    private function calculatePnlData(string $startDate, string $endDate): array
    {
        // ----------------------------------------------------
        // A. REVENUES / INCOME
        // ----------------------------------------------------
        // 1. Bill Payments categorized by bill_type
        $billIncomeQuery = DB::table('bill_payments')
            ->leftJoin('bills', 'bill_payments.bill_id', '=', 'bills.id')
            ->whereNull('bill_payments.deleted_at')
            ->whereBetween('bill_payments.payment_date', [$startDate, $endDate]);

        $rentIncome = (float) (clone $billIncomeQuery)->where('bills.bill_type', 'Monthly Rent')->sum('bill_payments.amount');
        $ebIncome = (float) (clone $billIncomeQuery)->where('bills.bill_type', 'Electricity')->sum('bill_payments.amount');
        $maintenanceIncome = (float) (clone $billIncomeQuery)->where('bills.bill_type', 'Maintenance')->sum('bill_payments.amount');
        $otherBillIncome = (float) (clone $billIncomeQuery)->whereNotIn('bills.bill_type', ['Monthly Rent', 'Electricity', 'Maintenance', 'Security Deposit'])->sum('bill_payments.amount');

        // If any generic bill payment without specific bill type
        $nullTypeIncome = (float) (clone $billIncomeQuery)->whereNull('bills.bill_type')->sum('bill_payments.amount');
        $otherBillIncome += $nullTypeIncome;

        // 2. Direct Operating Income from Journal
        $journalIncomeQuery = DB::table('journal_entries')
            ->whereNull('deleted_at')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->whereIn('entry_type', ['income', 'capital']);

        $journalIncomeTotal = (float) (clone $journalIncomeQuery)->sum('amount');
        $journalIncomeItems = (clone $journalIncomeQuery)
            ->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->get();

        // 3. Family Salary Income (Salary received by family members brought into residence & personal funds)
        $familySalaryIncome = (float) DB::table('salaries')
            ->whereNull('deleted_at')
            ->where('status', 'credited')
            ->whereBetween(DB::raw('COALESCE(credit_date, DATE(created_at))'), [$startDate, $endDate])
            ->sum('net_salary');

        $totalRevenue = $rentIncome + $ebIncome + $maintenanceIncome + $otherBillIncome + $journalIncomeTotal + $familySalaryIncome;

        $incomeBreakdown = [
            [
                'category' => 'Room Rent Collections',
                'description' => 'Monthly room rental charges received from tenants',
                'amount' => $rentIncome,
                'share_pct' => $totalRevenue > 0 ? round(($rentIncome / $totalRevenue) * 100, 1) : 0,
                'icon' => 'fas fa-home text-primary',
            ],
            [
                'category' => 'Electricity (EB) Collections',
                'description' => 'Sub-meter unit consumption collections from rooms',
                'amount' => $ebIncome,
                'share_pct' => $totalRevenue > 0 ? round(($ebIncome / $totalRevenue) * 100, 1) : 0,
                'icon' => 'fas fa-bolt text-warning',
            ],
            [
                'category' => 'Maintenance & Amenities',
                'description' => 'Cleaning, water & hostel facility fees collected',
                'amount' => $maintenanceIncome,
                'share_pct' => $totalRevenue > 0 ? round(($maintenanceIncome / $totalRevenue) * 100, 1) : 0,
                'icon' => 'fas fa-tools text-info',
            ],
        ];

        if ($familySalaryIncome > 0) {
            $incomeBreakdown[] = [
                'category' => 'Family Salary Income',
                'description' => 'Monthly salary earnings received by family members brought into residence funds',
                'amount' => $familySalaryIncome,
                'share_pct' => $totalRevenue > 0 ? round(($familySalaryIncome / $totalRevenue) * 100, 1) : 0,
                'icon' => 'fas fa-hand-holding-dollar text-success',
            ];
        }

        if ($otherBillIncome > 0) {
            $incomeBreakdown[] = [
                'category' => 'Other Tenant Bill Collections',
                'description' => 'Mess, fines & miscellaneous bill collections',
                'amount' => $otherBillIncome,
                'share_pct' => $totalRevenue > 0 ? round(($otherBillIncome / $totalRevenue) * 100, 1) : 0,
                'icon' => 'fas fa-layer-group text-secondary',
            ];
        }

        foreach ($journalIncomeItems as $jItem) {
            $incomeBreakdown[] = [
                'category' => e($jItem->category) . ' (Direct Income)',
                'description' => 'Direct cash/bank income from general ledger',
                'amount' => (float) $jItem->total_amount,
                'share_pct' => $totalRevenue > 0 ? round(((float) $jItem->total_amount / $totalRevenue) * 100, 1) : 0,
                'icon' => 'fas fa-receipt text-success',
            ];
        }

        // ----------------------------------------------------
        // B. DIRECT OPERATING EXPENSES (Hostel Operations)
        // ----------------------------------------------------
        $roomExpenses = DB::table('expenses')
            ->whereNull('deleted_at')
            ->where('expense_type', 'room')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->get();

        $totalOperatingExpenses = (float) $roomExpenses->sum('total_amount');

        $operatingExpenseBreakdown = [];
        foreach ($roomExpenses as $re) {
            $operatingExpenseBreakdown[] = [
                'category' => $re->category ?: 'General Maintenance',
                'amount' => (float) $re->total_amount,
                'share_pct' => $totalOperatingExpenses > 0 ? round(((float) $re->total_amount / $totalOperatingExpenses) * 100, 1) : 0,
                'icon' => 'fas fa-wrench text-warning',
            ];
        }

        // Operating Profit / EBITDA
        $operatingProfit = $totalRevenue - $totalOperatingExpenses;
        $operatingMarginPct = $totalRevenue > 0 ? round(($operatingProfit / $totalRevenue) * 100, 1) : 0;

        // ----------------------------------------------------
        // C. INDIRECT & OVERHEAD EXPENSES
        // Note: Salaries are Family Income (Inflow), not an expense.
        // ----------------------------------------------------
        // 1. Personal Expenses
        $personalExpensesTotal = (float) DB::table('expenses')
            ->whereNull('deleted_at')
            ->where('expense_type', 'personal')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // 2. Financing Costs / Loan Interest & EMIs
        $loanEmisTotal = (float) DB::table('loan_emis')
            ->whereNull('deleted_at')
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        // 3. Other Indirect Journal Expenses
        $journalExpensesTotal = (float) DB::table('journal_entries')
            ->whereNull('deleted_at')
            ->whereIn('entry_type', ['expense', 'drawing'])
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum('amount');

        $totalIndirectExpenses = $personalExpensesTotal + $loanEmisTotal + $journalExpensesTotal;

        $indirectExpenseBreakdown = [
            [
                'category' => 'Personal & Family Expenses',
                'description' => 'Personal drawings and non-room expense logs',
                'amount' => $personalExpensesTotal,
                'icon' => 'fas fa-user-tag text-info',
            ],
            [
                'category' => 'Loan Repayments / Financing',
                'description' => 'Total loan EMI installments paid',
                'amount' => $loanEmisTotal,
                'icon' => 'fas fa-hand-holding-usd text-warning',
            ],
        ];

        if ($journalExpensesTotal > 0) {
            $indirectExpenseBreakdown[] = [
                'category' => 'Other Journal Overheads',
                'description' => 'Direct ledger expense vouchers and drawings',
                'amount' => $journalExpensesTotal,
                'icon' => 'fas fa-file-invoice text-danger',
            ];
        }

        // ----------------------------------------------------
        // D. TOTAL EXPENSES & NET PROFIT
        // ----------------------------------------------------
        $totalExpenses = $totalOperatingExpenses + $totalIndirectExpenses;
        $netProfit = $totalRevenue - $totalExpenses;
        $netMarginPct = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        // ----------------------------------------------------
        // E. 6-MONTH COMPARATIVE TREND
        // ----------------------------------------------------
        $monthlyTrend = [];
        $curr = Carbon::parse($endDate)->startOfMonth();
        for ($i = 5; $i >= 0; $i--) {
            $mStart = $curr->copy()->subMonths($i)->startOfMonth()->format('Y-m-d');
            $mEnd = $curr->copy()->subMonths($i)->endOfMonth()->format('Y-m-d');
            $mLabel = $curr->copy()->subMonths($i)->format('M Y');

            // Inflow: Bill collections + Family Salary income + Journal income/capital
            $mIn = (float) DB::table('bill_payments')->whereNull('deleted_at')->whereBetween('payment_date', [$mStart, $mEnd])->sum('amount')
                 + (float) DB::table('salaries')->whereNull('deleted_at')->where('status', 'credited')->whereBetween(DB::raw('COALESCE(credit_date, DATE(created_at))'), [$mStart, $mEnd])->sum('net_salary')
                 + (float) DB::table('journal_entries')->whereNull('deleted_at')->whereIn('entry_type', ['income', 'capital'])->whereBetween('entry_date', [$mStart, $mEnd])->sum('amount');

            // Outflow: Expenses + Loan EMIs + Journal expense/drawings
            $mExp = (float) DB::table('expenses')->whereNull('deleted_at')->whereBetween('expense_date', [$mStart, $mEnd])->sum('amount')
                  + (float) DB::table('loan_emis')->whereNull('deleted_at')->where('status', 'paid')->whereBetween('payment_date', [$mStart, $mEnd])->sum('amount')
                  + (float) DB::table('journal_entries')->whereNull('deleted_at')->whereIn('entry_type', ['expense', 'drawing'])->whereBetween('entry_date', [$mStart, $mEnd])->sum('amount');

            $mNet = $mIn - $mExp;

            $monthlyTrend[] = [
                'month' => $mLabel,
                'income' => $mIn,
                'expense' => $mExp,
                'net' => $mNet,
                'is_profit' => $mNet >= 0
            ];
        }

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_formatted' => Carbon::parse($startDate)->format('d M, Y'),
                'end_formatted' => Carbon::parse($endDate)->format('d M, Y'),
            ],
            'totalRevenue' => $totalRevenue,
            'totalRevenueFormatted' => '₹' . number_format($totalRevenue, 2),
            'incomeBreakdown' => $incomeBreakdown,

            'totalOperatingExpenses' => $totalOperatingExpenses,
            'totalOperatingExpensesFormatted' => '₹' . number_format($totalOperatingExpenses, 2),
            'operatingExpenseBreakdown' => $operatingExpenseBreakdown,

            'operatingProfit' => $operatingProfit,
            'operatingProfitFormatted' => ($operatingProfit >= 0 ? '₹' : '- ₹') . number_format(abs($operatingProfit), 2),
            'operatingMarginPct' => $operatingMarginPct,

            'totalIndirectExpenses' => $totalIndirectExpenses,
            'totalIndirectExpensesFormatted' => '₹' . number_format($totalIndirectExpenses, 2),
            'indirectExpenseBreakdown' => $indirectExpenseBreakdown,

            'totalExpenses' => $totalExpenses,
            'totalExpensesFormatted' => '₹' . number_format($totalExpenses, 2),

            'netProfit' => $netProfit,
            'netProfitFormatted' => ($netProfit >= 0 ? '₹' : '- ₹') . number_format(abs($netProfit), 2),
            'netMarginPct' => $netMarginPct,
            'isProfitable' => $netProfit >= 0,

            'monthlyTrend' => $monthlyTrend,
        ];
    }

    /**
     * Export P&L to CSV
     */
    public function exportPnl(Request $request): StreamedResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $pnl = $this->calculatePnlData($startDate, $endDate);

        $filename = 'srm_pnl_' . $startDate . '_to_' . $endDate . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($pnl) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, ['SRM RESIDENCE - PROFIT & LOSS STATEMENT']);
            fputcsv($handle, ['Period', $pnl['period']['start_formatted'] . ' to ' . $pnl['period']['end_formatted']]);
            fputcsv($handle, []);

            fputcsv($handle, ['REVENUE / INCOME', 'AMOUNT (₹)', 'SHARE (%)']);
            foreach ($pnl['incomeBreakdown'] as $item) {
                fputcsv($handle, [$item['category'], number_format($item['amount'], 2), $item['share_pct'] . '%']);
            }
            fputcsv($handle, ['TOTAL REVENUE (A)', number_format($pnl['totalRevenue'], 2), '100%']);
            fputcsv($handle, []);

            fputcsv($handle, ['OPERATING EXPENSES (DIRECT)', 'AMOUNT (₹)', 'SHARE (%)']);
            foreach ($pnl['operatingExpenseBreakdown'] as $item) {
                fputcsv($handle, [$item['category'], number_format($item['amount'], 2), $item['share_pct'] . '%']);
            }
            fputcsv($handle, ['TOTAL OPERATING EXPENSES (B)', number_format($pnl['totalOperatingExpenses'], 2), '100%']);
            fputcsv($handle, ['OPERATING PROFIT / EBITDA (A - B)', number_format($pnl['operatingProfit'], 2), $pnl['operatingMarginPct'] . '%']);
            fputcsv($handle, []);

            fputcsv($handle, ['INDIRECT EXPENSES & OVERHEADS', 'AMOUNT (₹)']);
            foreach ($pnl['indirectExpenseBreakdown'] as $item) {
                fputcsv($handle, [$item['category'], number_format($item['amount'], 2)]);
            }
            fputcsv($handle, ['TOTAL INDIRECT EXPENSES (C)', number_format($pnl['totalIndirectExpenses'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['SUMMARY FINANCIALS', 'AMOUNT (₹)']);
            fputcsv($handle, ['TOTAL EXPENSES (B + C)', number_format($pnl['totalExpenses'], 2)]);
            fputcsv($handle, ['NET PROFIT / (NET LOSS)', number_format($pnl['netProfit'], 2)]);
            fputcsv($handle, ['NET PROFIT MARGIN', $pnl['netMarginPct'] . '%']);

            fclose($handle);
        }, 200, $headers);
    }

    /* =========================================================================
     * 3. BALANCE SHEET
     * ========================================================================= */

    /**
     * Display Balance Sheet View
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->get('as_of_date', Carbon::now()->format('Y-m-d'));
        $data = $this->calculateBalanceSheetData($asOfDate);

        return view('backend.accounts.balance_sheet', compact('data', 'asOfDate'));
    }

    /**
     * AJAX endpoint for dynamic Balance Sheet updates
     */
    public function balanceSheetData(Request $request)
    {
        $asOfDate = $request->get('as_of_date', Carbon::now()->format('Y-m-d'));
        $data = $this->calculateBalanceSheetData($asOfDate);

        return response()->json([
            'success' => true,
            'balance_sheet' => $data
        ]);
    }

    /**
     * Calculate Detailed Balance Sheet Financial Position
     */
    private function calculateBalanceSheetData(string $asOfDate): array
    {
        $sql = $this->getUnifiedJournalSql();

        // 1. LIQUID ASSETS (Exact Cash & Bank balances up to asOfDate)
        $liquidityData = DB::select("
            SELECT 
                COALESCE(SUM(CASE WHEN LOWER(payment_method) = 'cash' THEN (CASE WHEN flow_type = 'income' THEN amount ELSE -amount END) ELSE 0 END), 0) AS cash_in_hand,
                COALESCE(SUM(CASE WHEN LOWER(payment_method) != 'cash' THEN (CASE WHEN flow_type = 'income' THEN amount ELSE -amount END) ELSE 0 END), 0) AS bank_balance,
                COALESCE(SUM(CASE WHEN flow_type = 'income' THEN amount ELSE -amount END), 0) AS total_liquid
            FROM ({$sql}) AS ledger
            WHERE trans_date <= ?
        ", [$asOfDate])[0];

        $cashInHand = (float) $liquidityData->cash_in_hand;
        $bankBalance = (float) $liquidityData->bank_balance;
        $totalLiquidCash = (float) $liquidityData->total_liquid;

        // 2. ACCOUNTS RECEIVABLE (Unpaid Tenant Bills up to asOfDate)
        $accountsReceivable = (float) DB::table('bills')
            ->whereNull('deleted_at')
            ->where('bill_date', '<=', $asOfDate)
            ->where('status', '!=', 'Paid')
            ->sum('due_amount');

        // Detailed receivables breakdown by tenant
        $receivableTenants = DB::table('bills')
            ->leftJoin('tenants', 'bills.tenant_id', '=', 'tenants.id')
            ->whereNull('bills.deleted_at')
            ->where('bills.bill_date', '<=', $asOfDate)
            ->where('bills.status', '!=', 'Paid')
            ->select('tenants.name', 'tenants.room_number', DB::raw('SUM(bills.due_amount) as total_due'))
            ->groupBy('tenants.id', 'tenants.name', 'tenants.room_number')
            ->having('total_due', '>', 0)
            ->orderByDesc('total_due')
            ->limit(10)
            ->get();

        // 3. FIXED & INFRASTRUCTURE ASSETS (Room Amenities & Capital Assets)
        $roomCount = (int) Room::count();
        // Estimated capital infrastructure value per room (beds, AC, electrical, building fittings approx ₹35,000 / room)
        $estimatedRoomAssets = $roomCount * 35000;

        // Capital Asset Journal Entries
        $capitalJournalAssets = (float) DB::table('journal_entries')
            ->whereNull('deleted_at')
            ->where('entry_date', '<=', $asOfDate)
            ->where('category', 'Asset Purchase')
            ->sum('amount');

        $totalFixedAssets = $estimatedRoomAssets + $capitalJournalAssets;

        // TOTAL ASSETS
        $totalCurrentAssets = $totalLiquidCash + $accountsReceivable;
        $totalAssets = $totalCurrentAssets + $totalFixedAssets;

        // ----------------------------------------------------
        // LIABILITIES (What SRM Residence owes)
        // ----------------------------------------------------
        // 1. Current Liabilities: Tenant Caution / Security Deposits Held
        // These are refundable when tenants vacate
        $securityDepositsHeld = (float) Tenant::where('status', 1)->sum('security_deposit');

        // 2. Long-Term Debt: Outstanding Loans Balance
        $activeLoansQuery = DB::table('loans')
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->where('start_date', '<=', $asOfDate);

        $loanDebtOutstanding = (float) (clone $activeLoansQuery)->sum('outstanding_balance');
        $activeLoansList = (clone $activeLoansQuery)
            ->select('loan_title', 'lender_name', 'principal_amount', 'outstanding_balance', 'repayment_frequency', 'emi_amount')
            ->get();

        // TOTAL LIABILITIES
        $totalLiabilities = $securityDepositsHeld + $loanDebtOutstanding;

        // ----------------------------------------------------
        // EQUITY & NET WORTH
        // ----------------------------------------------------
        // Accounting Equation: Equity = Assets - Liabilities
        $netWorth = $totalAssets - $totalLiabilities;
        $totalLiabilitiesAndEquity = $totalLiabilities + $netWorth;

        $isEquilibrium = abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01;

        return [
            'asOfDate' => $asOfDate,
            'asOfFormatted' => Carbon::parse($asOfDate)->format('d M, Y'),

            // Liquid Reserves
            'cashInHand' => $cashInHand,
            'cashInHandFormatted' => '₹' . number_format($cashInHand, 2),
            'bankBalance' => $bankBalance,
            'bankBalanceFormatted' => '₹' . number_format($bankBalance, 2),
            'totalLiquidCash' => $totalLiquidCash,
            'totalLiquidCashFormatted' => '₹' . number_format($totalLiquidCash, 2),

            // Receivables
            'accountsReceivable' => $accountsReceivable,
            'accountsReceivableFormatted' => '₹' . number_format($accountsReceivable, 2),
            'receivableTenants' => $receivableTenants,

            // Fixed Assets
            'roomCount' => $roomCount,
            'estimatedRoomAssets' => $estimatedRoomAssets,
            'capitalJournalAssets' => $capitalJournalAssets,
            'totalFixedAssets' => $totalFixedAssets,
            'totalFixedAssetsFormatted' => '₹' . number_format($totalFixedAssets, 2),

            // Assets Total
            'totalCurrentAssets' => $totalCurrentAssets,
            'totalCurrentAssetsFormatted' => '₹' . number_format($totalCurrentAssets, 2),
            'totalAssets' => $totalAssets,
            'totalAssetsFormatted' => '₹' . number_format($totalAssets, 2),

            // Liabilities
            'securityDepositsHeld' => $securityDepositsHeld,
            'securityDepositsHeldFormatted' => '₹' . number_format($securityDepositsHeld, 2),
            'loanDebtOutstanding' => $loanDebtOutstanding,
            'loanDebtOutstandingFormatted' => '₹' . number_format($loanDebtOutstanding, 2),
            'activeLoansList' => $activeLoansList,
            'totalLiabilities' => $totalLiabilities,
            'totalLiabilitiesFormatted' => '₹' . number_format($totalLiabilities, 2),

            // Net Worth & Equity
            'netWorth' => $netWorth,
            'netWorthFormatted' => '₹' . number_format($netWorth, 2),
            'totalLiabilitiesAndEquity' => $totalLiabilitiesAndEquity,
            'totalLiabilitiesAndEquityFormatted' => '₹' . number_format($totalLiabilitiesAndEquity, 2),
            'isEquilibrium' => $isEquilibrium,
        ];
    }

    /**
     * Export Balance Sheet to CSV
     */
    public function exportBalanceSheet(Request $request): StreamedResponse
    {
        $asOfDate = $request->get('as_of_date', Carbon::now()->format('Y-m-d'));
        $bs = $this->calculateBalanceSheetData($asOfDate);

        $filename = 'srm_balance_sheet_' . $asOfDate . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($bs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, ['SRM RESIDENCE - BALANCE SHEET']);
            fputcsv($handle, ['Statement Date', $bs['asOfFormatted']]);
            fputcsv($handle, []);

            fputcsv($handle, ['ASSETS', 'AMOUNT (₹)']);
            fputcsv($handle, ['1. Current Assets:']);
            fputcsv($handle, ['   Cash in Hand', number_format($bs['cashInHand'], 2)]);
            fputcsv($handle, ['   Bank & Digital Balance', number_format($bs['bankBalance'], 2)]);
            fputcsv($handle, ['   Total Liquid Reserves', number_format($bs['totalLiquidCash'], 2)]);
            fputcsv($handle, ['   Accounts Receivable (Tenant Dues)', number_format($bs['accountsReceivable'], 2)]);
            fputcsv($handle, ['   Total Current Assets', number_format($bs['totalCurrentAssets'], 2)]);
            fputcsv($handle, ['2. Non-Current / Fixed Assets:']);
            fputcsv($handle, ['   Room Furniture & Infrastructure', number_format($bs['estimatedRoomAssets'], 2)]);
            fputcsv($handle, ['   Capital Equipment & Purchases', number_format($bs['capitalJournalAssets'], 2)]);
            fputcsv($handle, ['   Total Fixed Assets', number_format($bs['totalFixedAssets'], 2)]);
            fputcsv($handle, ['TOTAL ASSETS (A)', number_format($bs['totalAssets'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['LIABILITIES', 'AMOUNT (₹)']);
            fputcsv($handle, ['1. Current Liabilities:']);
            fputcsv($handle, ['   Tenant Security Deposits Held (Refundable)', number_format($bs['securityDepositsHeld'], 2)]);
            fputcsv($handle, ['2. Long-Term Debt:']);
            fputcsv($handle, ['   Active Loans Outstanding Balance', number_format($bs['loanDebtOutstanding'], 2)]);
            fputcsv($handle, ['TOTAL LIABILITIES (B)', number_format($bs['totalLiabilities'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['EQUITY & NET WORTH (A - B)', 'AMOUNT (₹)']);
            fputcsv($handle, ['Accumulated Reserves & Owner Capital', number_format($bs['netWorth'], 2)]);
            fputcsv($handle, ['TOTAL LIABILITIES & EQUITY', number_format($bs['totalLiabilitiesAndEquity'], 2)]);

            fclose($handle);
        }, 200, $headers);
    }

    /* =========================================================================
     * HELPER: UNIFIED JOURNAL SQL
     * ========================================================================= */

    /**
     * Builds the unified raw SQL query across all transaction tables
     */
    private function getUnifiedJournalSql(): string
    {
        return "
            SELECT 
                p.id,
                'bill_payment' AS source_type,
                p.payment_number AS voucher_no,
                p.payment_date AS trans_date,
                'income' AS flow_type,
                'Rent & Collections' AS primary_category,
                COALESCE(b.bill_type, 'Bill Payment') AS sub_category,
                CONCAT(COALESCE(t.name, 'Tenant'), ' (Room ', COALESCE(t.room_number, 'N/A'), ')') AS party_name,
                p.payment_method,
                p.amount,
                p.transaction_id AS ref_code,
                p.notes,
                p.created_at
            FROM bill_payments p
            LEFT JOIN bills b ON p.bill_id = b.id
            LEFT JOIN tenants t ON p.tenant_id = t.id
            WHERE p.deleted_at IS NULL

            UNION ALL

            SELECT 
                e.id,
                'expense' AS source_type,
                CONCAT('EXP-', e.id) AS voucher_no,
                e.expense_date AS trans_date,
                'expense' AS flow_type,
                CASE WHEN e.expense_type = 'room' THEN 'Room Expense' ELSE 'Personal Expense' END AS primary_category,
                e.category AS sub_category,
                COALESCE(e.paid_to, e.title) AS party_name,
                e.payment_method,
                e.amount,
                e.title AS ref_code,
                e.notes,
                e.created_at
            FROM expenses e
            WHERE e.deleted_at IS NULL

            UNION ALL

            SELECT 
                s.id,
                'salary' AS source_type,
                CONCAT('SAL-', LPAD(s.id, 4, '0')) AS voucher_no,
                COALESCE(s.credit_date, DATE(s.created_at)) AS trans_date,
                'income' AS flow_type,
                'Family Salary Income' AS primary_category,
                s.salary_title AS sub_category,
                CASE WHEN s.recipient_name LIKE '%Family%' THEN s.recipient_name ELSE CONCAT(s.recipient_name, ' (Family Earner)') END AS party_name,
                s.payment_method,
                s.net_salary AS amount,
                s.transaction_reference AS ref_code,
                s.notes,
                s.created_at
            FROM salaries s
            WHERE s.deleted_at IS NULL AND s.status = 'credited'

            UNION ALL

            SELECT 
                le.id,
                'loan_emi' AS source_type,
                CONCAT('EMI-', le.loan_id, '-', le.installment_number) AS voucher_no,
                le.payment_date AS trans_date,
                'expense' AS flow_type,
                'Loan EMI' AS primary_category,
                COALESCE(l.loan_title, 'Loan Repayment') AS sub_category,
                COALESCE(l.lender_name, 'Financier') AS party_name,
                le.payment_method,
                le.amount,
                le.transaction_id AS ref_code,
                le.notes,
                le.created_at
            FROM loan_emis le
            LEFT JOIN loans l ON le.loan_id = l.id
            WHERE le.deleted_at IS NULL AND le.status = 'paid'

            UNION ALL

            SELECT 
                j.id,
                'journal' AS source_type,
                j.entry_number AS voucher_no,
                j.entry_date AS trans_date,
                CASE WHEN j.entry_type IN ('income', 'capital') THEN 'income' ELSE 'expense' END AS flow_type,
                'Journal Voucher' AS primary_category,
                j.category AS sub_category,
                j.title AS party_name,
                j.payment_method,
                j.amount,
                j.reference_no AS ref_code,
                j.notes,
                j.created_at
            FROM journal_entries j
            WHERE j.deleted_at IS NULL
        ";
    }
}
