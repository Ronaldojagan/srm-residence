<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanEmi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class LoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Loans & EMI Management Dashboard
     */
    public function index(Request $request)
    {
        // 1. AJAX DataTables for Loans
        if ($request->ajax() && $request->get('table') === 'loans') {
            $query = Loan::query()->with('emis');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('loan_type')) {
                $query->where('loan_type', $request->loan_type);
            }
            if ($request->filled('repayment_frequency')) {
                $query->where('repayment_frequency', $request->repayment_frequency);
            }
            if ($request->filled('lender_name')) {
                $query->where('lender_name', 'like', '%' . $request->lender_name . '%');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check-input bulk-select-row" value="' . $row->id . '">';
                })
                ->addColumn('loan_title_badge', function ($row) {
                    $acc = $row->account_number ? '<small class="text-muted d-block font-11"><i class="fas fa-hashtag me-1"></i>A/C: ' . e($row->account_number) . '</small>' : '';
                    $bankDebit = $row->bank_account_debited ? '<small class="text-secondary d-block font-11"><i class="fas fa-university me-1"></i>Debited: ' . e($row->bank_account_debited) . '</small>' : '';
                    return '<div>
                                <span class="fw-bold text-dark font-14">' . e($row->loan_title) . '</span>
                                ' . $acc . $bankDebit . '
                            </div>';
                })
                ->addColumn('lender_and_type', function ($row) {
                    $typeBadges = [
                        'business' => 'bg-primary-subtle text-primary border border-primary',
                        'personal' => 'bg-info-subtle text-info border border-info',
                        'property' => 'bg-success-subtle text-success border border-success',
                        'appliance' => 'bg-warning-subtle text-warning border border-warning',
                        'vehicle' => 'bg-purple-subtle text-purple border border-purple',
                        'hand_loan' => 'bg-secondary-subtle text-secondary border border-secondary',
                    ];
                    $badgeCls = $typeBadges[$row->loan_type] ?? 'bg-light text-dark border';
                    $typeLabel = ucwords(str_replace('_', ' ', $row->loan_type));
                    $freqBadge = $row->frequency_badge;

                    return '<div>
                                <span class="fw-semibold text-dark d-block font-13"><i class="fas fa-landmark text-primary me-1"></i>' . e($row->lender_name) . '</span>
                                <div class="mt-1 d-flex align-items-center gap-1 flex-wrap">
                                    <span class="badge ' . $badgeCls . ' font-11">' . e($typeLabel) . '</span>
                                    ' . $freqBadge . '
                                </div>
                            </div>';
                })
                ->addColumn('principal_and_emi', function ($row) {
                    $rate = (float) $row->interest_rate > 0 ? '<small class="text-muted font-11"> @ ' . $row->interest_rate . '% p.a.</small>' : '';
                    $isWeekly = $row->is_weekly;
                    $emiLabel = $isWeekly ? 'Weekly Payment:' : 'Monthly EMI:';
                    $suffix = $isWeekly ? ' /wk' : ' /mo';
                    $tenureUnit = $isWeekly ? 'Weeks' : 'Months';

                    return '<div>
                                <div><span class="text-muted font-11">Principal:</span> <strong class="text-dark">₹' . number_format((float) $row->principal_amount, 2) . '</strong>' . $rate . '</div>
                                <div class="mt-1"><span class="text-muted font-11">' . $emiLabel . '</span> <strong class="text-danger font-13">₹' . number_format((float) $row->emi_amount, 2) . '</strong><small class="text-muted font-11">' . $suffix . '</small></div>
                                <small class="text-muted font-11"><i class="fas fa-clock me-1"></i>Tenure: ' . $row->tenure_months . ' ' . $tenureUnit . '</small>
                            </div>';
                })
                ->addColumn('repayment_progress', function ($row) {
                    $repaid = (float) $row->total_repaid;
                    $pct = $row->progress_percentage;
                    $totalPaidCount = (int) $row->total_paid_units;
                    $unit = $row->is_weekly ? 'wks' : 'mos';
                    $priorCleared = (int) $row->already_paid_units > 0 
                        ? '<small class="text-secondary d-block font-10">(' . $row->already_paid_units . ' ' . $unit . ' prior cleared)</small>' 
                        : '';

                    return '<div style="min-width: 140px;">
                                <div class="d-flex justify-content-between font-11 mb-1">
                                    <span class="text-success fw-bold">₹' . number_format($repaid, 0) . '</span>
                                    <span class="text-muted">' . $pct . '% (' . $totalPaidCount . '/' . $row->tenure_months . ')</span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 4px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: ' . $pct . '%;" aria-valuenow="' . $pct . '" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="font-11 text-muted mt-1 text-end">
                                    Bal: <strong class="text-danger">₹' . number_format((float) $row->outstanding_balance, 0) . '</strong>
                                </div>
                                ' . $priorCleared . '
                            </div>';
                })
                ->addColumn('next_due_date', function ($row) {
                    if ($row->status === 'closed' || (float)$row->outstanding_balance <= 0) {
                        return '<span class="badge bg-secondary font-11"><i class="fas fa-check-circle me-1"></i>Loan Fully Paid</span>';
                    }
                    $nextDue = $row->next_due_date;
                    $isWeekly = $row->is_weekly;
                    $dateStr = $nextDue ? $nextDue->format('d M, Y') : ($isWeekly ? 'Next Week' : ($row->emi_day ?: 5) . 'th of month');
                    $isSoon = $nextDue && $nextDue->diffInDays(now(), false) >= -3 && $nextDue->diffInDays(now(), false) <= 0;
                    $badgeCls = $isSoon ? 'bg-danger text-white' : 'bg-warning-subtle text-dark border border-warning';
                    $cycleText = $isWeekly ? 'Every 7 days (Weekly)' : ('Day ' . ($row->emi_day ?: 5) . ' of month');

                    return '<div>
                                <span class="badge ' . $badgeCls . ' font-11 px-2 py-1">
                                    <i class="fas fa-clock me-1"></i>Due: ' . $dateStr . '
                                </span>
                                <small class="text-muted d-block font-11 mt-1">' . $cycleText . '</small>
                            </div>';
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'closed' || (float)$row->outstanding_balance <= 0) {
                        return '<span class="badge bg-success text-white px-2 py-1 font-12"><i class="fas fa-check-double me-1"></i>Closed</span>';
                    } elseif ($row->status === 'overdue') {
                        return '<span class="badge bg-danger text-white px-2 py-1 font-12"><i class="fas fa-exclamation-triangle me-1"></i>Overdue</span>';
                    }
                    return '<span class="badge bg-primary text-white px-2 py-1 font-12"><i class="fas fa-sync-alt me-1 font-11"></i>Active</span>';
                })
                ->addColumn('action', function ($row) {
                    $payBtn = '';
                    if ($row->status !== 'closed' && (float)$row->outstanding_balance > 0) {
                        $payBtn = '<button type="button" class="btn btn-sm btn-outline-success btn-record-loan-emi" 
                                    data-id="' . $row->id . '" 
                                    data-title="' . e($row->loan_title) . '" 
                                    data-lender="' . e($row->lender_name) . '"
                                    data-amount="' . $row->emi_amount . '"
                                    data-frequency="' . $row->repayment_frequency . '"
                                    title="Record Payment">
                                    <i class="fas fa-credit-card me-1"></i> Pay
                                </button>';
                    }

                    $scheduleBtn = '<button type="button" class="btn btn-sm btn-outline-info btn-view-loan-schedule" 
                                        data-id="' . $row->id . '" 
                                        data-title="' . e($row->loan_title) . '" 
                                        title="View Repayment Schedule & Ledger">
                                        <i class="fas fa-list-ol"></i>
                                    </button>';

                    $editBtn = '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit-loan" 
                                    data-id="' . $row->id . '" 
                                    title="Edit Loan Details">
                                    <i class="fas fa-edit"></i>
                                </button>';

                    $delBtn = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-loan" 
                                data-id="' . $row->id . '" 
                                data-title="' . e($row->loan_title) . '" 
                                title="Delete Loan Account">
                                <i class="fas fa-trash-alt"></i>
                            </button>';

                    return '<div class="btn-group btn-group-sm" role="group">' . $payBtn . $scheduleBtn . $editBtn . $delBtn . '</div>';
                })
                ->rawColumns(['checkbox', 'loan_title_badge', 'lender_and_type', 'principal_and_emi', 'repayment_progress', 'next_due_date', 'status_badge', 'action'])
                ->make(true);
        }

        // 2. AJAX DataTables for EMI Payment History
        if ($request->ajax() && $request->get('table') === 'emis') {
            $query = LoanEmi::query()->with('loan');

            if ($request->filled('loan_id')) {
                $query->where('loan_id', $request->loan_id);
            }
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }
            if ($request->filled('start_date')) {
                $query->whereDate('payment_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('payment_date', '<=', $request->end_date);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('loan_info', function ($row) {
                    if (!$row->loan) return '<span class="text-muted">Deleted Loan</span>';
                    $freq = $row->loan->frequency_badge;
                    return '<div>
                                <strong class="text-dark font-13">' . e($row->loan->loan_title) . '</strong>
                                <div class="mt-1 d-flex align-items-center gap-1">
                                    <small class="text-muted font-11"><i class="fas fa-landmark me-1"></i>' . e($row->loan->lender_name) . '</small>
                                    ' . $freq . '
                                </div>
                            </div>';
                })
                ->addColumn('installment_badge', function ($row) {
                    $total = $row->loan ? $row->loan->tenure_months : '-';
                    $unit = ($row->loan && $row->loan->is_weekly) ? 'Week' : 'Month';
                    return '<span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1 font-12">
                                Installment #' . $row->installment_number . ' of ' . $total . ' (' . $unit . ')
                            </span>';
                })
                ->addColumn('payment_date_formatted', function ($row) {
                    return '<div>
                                <span class="fw-semibold text-dark font-12">' . ($row->payment_date ? $row->payment_date->format('d M, Y') : '-') . '</span>
                                <small class="text-muted d-block font-11">' . ($row->payment_date ? $row->payment_date->diffForHumans() : '') . '</small>
                            </div>';
                })
                ->addColumn('amount_display', function ($row) {
                    return '<strong class="text-success fs-6">₹' . number_format((float) $row->amount, 2) . '</strong>';
                })
                ->addColumn('payment_method_badge', function ($row) {
                    $badges = [
                        'Auto-Debit / NACH' => 'bg-info-subtle text-info border border-info',
                        'Bank Transfer' => 'bg-primary-subtle text-primary border border-primary',
                        'UPI' => 'bg-success-subtle text-success border border-success',
                        'Cash' => 'bg-secondary-subtle text-secondary border border-secondary',
                        'Cheque' => 'bg-warning-subtle text-warning border border-warning',
                    ];
                    $cls = $badges[$row->payment_method] ?? 'bg-light text-dark border';
                    $tx = $row->transaction_id ? '<small class="text-muted d-block font-11">Ref: ' . e($row->transaction_id) . '</small>' : '';
                    return '<div>
                                <span class="badge rounded-pill ' . $cls . ' px-2 py-1 font-11">' . e($row->payment_method) . '</span>
                                ' . $tx . '
                            </div>';
                })
                ->addColumn('receipt_link', function ($row) {
                    if ($row->receipt) {
                        return '<a href="' . asset('storage/' . $row->receipt) . '" target="_blank" class="btn btn-xs btn-outline-info font-11">
                                    <i class="fas fa-paperclip me-1"></i> Receipt
                                </a>';
                    }
                    return '<span class="text-muted font-11">-</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-loan-emi" 
                                data-id="' . $row->id . '" 
                                data-inst="' . $row->installment_number . '" 
                                title="Delete Payment Record">
                                <i class="fas fa-trash-alt"></i>
                            </button>';
                })
                ->rawColumns(['loan_info', 'installment_badge', 'payment_date_formatted', 'amount_display', 'payment_method_badge', 'receipt_link', 'action'])
                ->make(true);
        }

        // 3. Regular Page View
        $stats = $this->calculateLoanStats();
        $loans = Loan::orderBy('loan_title', 'asc')->get();

        return view('backend.loans.index', compact('stats', 'loans'));
    }

    /**
     * Live statistics summary AJAX
     */
    public function getStatsAjax()
    {
        return response()->json([
            'success' => true,
            'stats' => $this->calculateLoanStats()
        ]);
    }

    /**
     * Helper to compute loans statistics
     */
    private function calculateLoanStats(): array
    {
        $activeLoans = Loan::where('status', 'active')->where('outstanding_balance', '>', 0)->get();
        $allLoans = Loan::all();

        $totalActive = $activeLoans->count();
        $totalBorrowed = (float) $allLoans->sum('principal_amount');
        
        // Total repaid = sum of already_paid_amount on all loans + sum of all paid EMIs in loan_emis table
        $totalRepaid = (float) $allLoans->sum('total_repaid');
        $totalOutstanding = (float) $activeLoans->sum('outstanding_balance');

        // Monthly EMI outflow: For monthly loans, emi_amount. For weekly loans, emi_amount * 4.33
        $monthlyEmiOutflow = 0;
        foreach ($activeLoans as $al) {
            if ($al->repayment_frequency === 'weekly') {
                $monthlyEmiOutflow += ((float) $al->emi_amount * 4.33);
            } else {
                $monthlyEmiOutflow += (float) $al->emi_amount;
            }
        }

        return [
            'totalActive' => $totalActive,
            'totalBorrowed' => $totalBorrowed,
            'totalBorrowedFormatted' => '₹' . number_format($totalBorrowed, 2),
            'totalRepaid' => $totalRepaid,
            'totalRepaidFormatted' => '₹' . number_format($totalRepaid, 2),
            'totalOutstanding' => $totalOutstanding,
            'totalOutstandingFormatted' => '₹' . number_format($totalOutstanding, 2),
            'monthlyEmiOutflow' => $monthlyEmiOutflow,
            'monthlyEmiOutflowFormatted' => '₹' . number_format($monthlyEmiOutflow, 2),
        ];
    }

    /**
     * Store new Loan / EMI account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_title' => 'required|string|max:150',
            'loan_type' => 'required|string|in:business,personal,property,appliance,vehicle,hand_loan,other',
            'repayment_frequency' => 'required|string|in:monthly,weekly',
            'lender_name' => 'required|string|max:150',
            'account_number' => 'nullable|string|max:100',
            'principal_amount' => 'required|numeric|min:1',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'tenure_months' => 'required|integer|min:1|max:520', // Total weeks or months
            'already_paid_units' => 'nullable|integer|min:0',
            'already_paid_amount' => 'nullable|numeric|min:0',
            'emi_amount' => 'required|numeric|min:1',
            'emi_day' => 'nullable|integer|min:1|max:31',
            'start_date' => 'required|date',
            'bank_account_debited' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $frequency = $validated['repayment_frequency'] ?? 'monthly';
        $tenure = (int) $validated['tenure_months'];

        if ($frequency === 'weekly') {
            $endDate = $startDate->copy()->addWeeks($tenure);
        } else {
            $endDate = $startDate->copy()->addMonths($tenure);
        }

        $emiAmount = (float) $validated['emi_amount'];
        $alreadyUnits = (int) ($validated['already_paid_units'] ?? 0);
        $alreadyAmount = (float) ($validated['already_paid_amount'] ?? 0);

        if ($alreadyAmount == 0 && $alreadyUnits > 0) {
            $alreadyAmount = (float) ($alreadyUnits * $emiAmount);
        }

        $totalPayable = (float) ($emiAmount * $tenure);
        if ($totalPayable < (float) $validated['principal_amount']) {
            $totalPayable = (float) $validated['principal_amount'];
        }

        $outstanding = max(0, $totalPayable - $alreadyAmount);
        $status = $outstanding <= 0 ? 'closed' : 'active';

        $loan = Loan::create([
            'loan_title' => $validated['loan_title'],
            'loan_type' => $validated['loan_type'],
            'repayment_frequency' => $frequency,
            'lender_name' => $validated['lender_name'],
            'account_number' => $validated['account_number'] ?? null,
            'principal_amount' => $validated['principal_amount'],
            'interest_rate' => $validated['interest_rate'] ?? 0,
            'tenure_months' => $tenure,
            'already_paid_units' => $alreadyUnits,
            'already_paid_amount' => $alreadyAmount,
            'emi_amount' => $emiAmount,
            'emi_day' => $validated['emi_day'] ?? 5,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_repaid' => $alreadyAmount,
            'outstanding_balance' => $outstanding,
            'status' => $status,
            'bank_account_debited' => $validated['bank_account_debited'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loan account "' . $loan->loan_title . '" created successfully!',
            'loan' => $loan
        ]);
    }

    /**
     * Get Loan data for editing
     */
    public function getData($loan)
    {
        if (!$loan instanceof Loan) {
            $loan = Loan::findOrFail($loan);
        }

        $data = $loan->toArray();
        $data['start_date_formatted'] = $loan->start_date ? $loan->start_date->format('Y-m-d') : '';
        $data['is_weekly'] = $loan->is_weekly;
        $data['frequency_label'] = $loan->frequency_label;

        return response()->json([
            'success' => true,
            'loan' => $data
        ]);
    }

    /**
     * Update Loan details
     */
    public function update(Request $request, $loan)
    {
        if (!$loan instanceof Loan) {
            $loan = Loan::findOrFail($loan);
        }

        $validated = $request->validate([
            'loan_title' => 'required|string|max:150',
            'loan_type' => 'required|string|in:business,personal,property,appliance,vehicle,hand_loan,other',
            'repayment_frequency' => 'required|string|in:monthly,weekly',
            'lender_name' => 'required|string|max:150',
            'account_number' => 'nullable|string|max:100',
            'principal_amount' => 'required|numeric|min:1',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'tenure_months' => 'required|integer|min:1|max:520',
            'already_paid_units' => 'nullable|integer|min:0',
            'already_paid_amount' => 'nullable|numeric|min:0',
            'emi_amount' => 'required|numeric|min:1',
            'emi_day' => 'nullable|integer|min:1|max:31',
            'start_date' => 'required|date',
            'status' => 'required|string|in:active,closed,overdue',
            'bank_account_debited' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $frequency = $validated['repayment_frequency'] ?? 'monthly';
        $tenure = (int) $validated['tenure_months'];

        if ($frequency === 'weekly') {
            $endDate = $startDate->copy()->addWeeks($tenure);
        } else {
            $endDate = $startDate->copy()->addMonths($tenure);
        }

        $alreadyUnits = (int) ($validated['already_paid_units'] ?? 0);
        $alreadyAmount = (float) ($validated['already_paid_amount'] ?? 0);
        if ($alreadyAmount == 0 && $alreadyUnits > 0) {
            $alreadyAmount = (float) ($alreadyUnits * (float)$validated['emi_amount']);
        }

        $loan->update([
            'loan_title' => $validated['loan_title'],
            'loan_type' => $validated['loan_type'],
            'repayment_frequency' => $frequency,
            'lender_name' => $validated['lender_name'],
            'account_number' => $validated['account_number'] ?? null,
            'principal_amount' => $validated['principal_amount'],
            'interest_rate' => $validated['interest_rate'] ?? 0,
            'tenure_months' => $tenure,
            'already_paid_units' => $alreadyUnits,
            'already_paid_amount' => $alreadyAmount,
            'emi_amount' => $validated['emi_amount'],
            'emi_day' => $validated['emi_day'] ?? 5,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $validated['status'],
            'bank_account_debited' => $validated['bank_account_debited'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $loan->recalculateBalance();

        return response()->json([
            'success' => true,
            'message' => 'Loan account updated successfully!',
            'loan' => $loan
        ]);
    }

    /**
     * Delete Loan
     */
    public function destroy($loan)
    {
        if (!$loan instanceof Loan) {
            $loan = Loan::findOrFail($loan);
        }

        $title = $loan->loan_title;
        $loan->emis()->delete();
        $loan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Loan "' . $title . '" and all associated EMI records deleted successfully!'
        ]);
    }

    /**
     * Get Complete EMI Schedule & History for a Loan
     */
    public function getSchedule($loan)
    {
        if (!$loan instanceof Loan) {
            $loan = Loan::findOrFail($loan);
        }

        $loan->load('emis');
        $paidEmis = $loan->emis->keyBy('installment_number');

        $schedule = [];
        $startDate = Carbon::parse($loan->start_date);
        $dueDay = (int) ($loan->emi_day ?: 5);
        $isWeekly = $loan->is_weekly;
        $alreadyUnits = (int) $loan->already_paid_units;

        for ($i = 1; $i <= $loan->tenure_months; $i++) {
            if ($isWeekly) {
                $dueDate = $startDate->copy()->addWeeks($i - 1);
            } else {
                $dueDate = $startDate->copy()->addMonths($i - 1)->day(min($dueDay, 28));
            }

            // Check if covered by prior payments
            if ($i <= $alreadyUnits) {
                $schedule[] = [
                    'installment_number' => $i,
                    'due_date' => $dueDate->format('d M, Y'),
                    'is_paid' => true,
                    'amount' => (float) $loan->emi_amount,
                    'payment_date' => $dueDate->format('d M, Y'),
                    'payment_method' => 'Prior Cleared',
                    'transaction_id' => 'Initial Balance',
                    'notes' => 'Installment cleared prior to system logging',
                    'status' => 'Paid (Prior)'
                ];
            } else {
                $isPaid = isset($paidEmis[$i]);
                $emiRecord = $isPaid ? $paidEmis[$i] : null;

                $schedule[] = [
                    'installment_number' => $i,
                    'due_date' => $dueDate->format('d M, Y'),
                    'is_paid' => $isPaid,
                    'amount' => $isPaid ? (float) $emiRecord->amount : (float) $loan->emi_amount,
                    'payment_date' => $isPaid ? $emiRecord->payment_date->format('d M, Y') : null,
                    'payment_method' => $isPaid ? $emiRecord->payment_method : null,
                    'transaction_id' => $isPaid ? $emiRecord->transaction_id : null,
                    'notes' => $isPaid ? $emiRecord->notes : null,
                    'status' => $isPaid ? 'Paid' : ($dueDate->isPast() ? 'Overdue' : 'Upcoming')
                ];
            }
        }

        // Next installment recommendation
        $nextInstallment = min($loan->tenure_months, (int) $loan->total_paid_units + 1);

        return response()->json([
            'success' => true,
            'loan' => [
                'id' => $loan->id,
                'loan_title' => $loan->loan_title,
                'lender_name' => $loan->lender_name,
                'repayment_frequency' => $loan->repayment_frequency,
                'frequency_label' => $loan->frequency_label,
                'is_weekly' => $loan->is_weekly,
                'principal_amount' => '₹' . number_format((float) $loan->principal_amount, 2),
                'emi_amount' => '₹' . number_format((float) $loan->emi_amount, 2),
                'total_repaid' => '₹' . number_format((float) $loan->total_repaid, 2),
                'already_paid_units' => $loan->already_paid_units,
                'already_paid_amount' => '₹' . number_format((float) $loan->already_paid_amount, 2),
                'outstanding_balance' => '₹' . number_format((float) $loan->outstanding_balance, 2),
                'progress_percentage' => $loan->progress_percentage,
                'tenure_months' => $loan->tenure_months,
                'tenure_unit' => $loan->tenure_unit,
                'next_installment' => $nextInstallment,
                'status' => strtoupper($loan->status)
            ],
            'schedule' => $schedule
        ]);
    }

    /**
     * Record EMI installment payment
     */
    public function recordEmi(Request $request, $loan)
    {
        if (!$loan instanceof Loan) {
            $loan = Loan::findOrFail($loan);
        }

        $validated = $request->validate([
            'installment_number' => 'required|integer|min:1',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('loan_receipts', 'public');
        }

        $emi = LoanEmi::create([
            'loan_id' => $loan->id,
            'installment_number' => $validated['installment_number'],
            'payment_date' => $validated['payment_date'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'status' => 'paid',
            'receipt' => $receiptPath,
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => Auth::id(),
        ]);

        $loan->recalculateBalance();

        return response()->json([
            'success' => true,
            'message' => 'Installment #' . $emi->installment_number . ' for "' . $loan->loan_title . '" recorded successfully!',
            'emi' => $emi
        ]);
    }

    /**
     * Delete an EMI installment record
     */
    public function destroyEmi($emi)
    {
        if (!$emi instanceof LoanEmi) {
            $emi = LoanEmi::findOrFail($emi);
        }

        $loan = $emi->loan;
        $inst = $emi->installment_number;
        $emi->delete();

        if ($loan) {
            $loan->recalculateBalance();
        }

        return response()->json([
            'success' => true,
            'message' => 'EMI installment #' . $inst . ' deleted successfully!'
        ]);
    }

    /**
     * Bulk Delete Loans
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:loans,id',
        ]);

        $count = count($validated['ids']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            LoanEmi::whereIn('loan_id', $validated['ids'])->delete();
            Loan::whereIn('id', $validated['ids'])->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} loan account(s) and associated EMIs!"
        ]);
    }

    /**
     * Bulk Update Loans
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:loans,id',
            'status' => 'nullable|string|in:active,closed,overdue',
            'lender_name' => 'nullable|string|max:150',
            'loan_type' => 'nullable|string|in:business,personal,property,appliance,vehicle,hand_loan,other',
            'repayment_frequency' => 'nullable|string|in:monthly,weekly',
            'bank_account_debited' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:500',
        ]);

        $updateData = [];
        if (!empty($validated['status'])) $updateData['status'] = $validated['status'];
        if (!empty($validated['lender_name'])) $updateData['lender_name'] = $validated['lender_name'];
        if (!empty($validated['loan_type'])) $updateData['loan_type'] = $validated['loan_type'];
        if (!empty($validated['repayment_frequency'])) $updateData['repayment_frequency'] = $validated['repayment_frequency'];
        if (!empty($validated['bank_account_debited'])) $updateData['bank_account_debited'] = $validated['bank_account_debited'];
        if (isset($validated['notes']) && $validated['notes'] !== '') $updateData['notes'] = $validated['notes'];

        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No fields provided to update.'
            ], 422);
        }

        $count = count($validated['ids']);
        Loan::whereIn('id', $validated['ids'])->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$count} loan account(s)!"
        ]);
    }
}
