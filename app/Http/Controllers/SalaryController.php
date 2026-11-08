<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class SalaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display Salary Dashboard & DataTables
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Salary::query();

            if ($request->filled('salary_month')) {
                $query->where('salary_month', $request->salary_month);
            }
            if ($request->filled('salary_year')) {
                $query->where('salary_month', 'like', $request->salary_year . '-%');
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }
            if ($request->filled('recipient_name')) {
                $query->where('recipient_name', 'like', '%' . $request->recipient_name . '%');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('month_badge', function ($row) {
                    $monthText = $row->formatted_salary_month;
                    return '<span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1 font-12 fw-semibold">
                                <i class="fas fa-calendar-alt me-1"></i>' . e($monthText) . '
                            </span>';
                })
                ->addColumn('recipient_and_title', function ($row) {
                    return '<div>
                                <span class="fw-bold text-dark font-14">' . e($row->recipient_name) . '</span>
                                <small class="text-muted d-block font-11"><i class="fas fa-tag me-1 text-secondary"></i>' . e($row->salary_title) . '</small>
                            </div>';
                })
                ->addColumn('breakdown', function ($row) {
                    $bonus = (float) $row->bonus_incentive > 0 ? '<small class="text-success font-11 d-block">+ Bonus/Inc: ₹' . number_format((float) $row->bonus_incentive, 2) . '</small>' : '';
                    $ded = (float) $row->deductions > 0 ? '<small class="text-danger font-11 d-block">- Deductions: ₹' . number_format((float) $row->deductions, 2) . '</small>' : '';
                    return '<div>
                                <div><span class="text-muted font-11">Base:</span> <strong class="text-dark font-12">₹' . number_format((float) $row->base_amount, 2) . '</strong></div>
                                ' . $bonus . $ded . '
                            </div>';
                })
                ->addColumn('net_salary_display', function ($row) {
                    return '<strong class="text-success fs-6">₹' . number_format((float) $row->net_salary, 2) . '</strong>';
                })
                ->addColumn('credit_details', function ($row) {
                    $dateStr = $row->credit_date ? $row->credit_date->format('d M, Y') : '-';
                    $methodBadges = [
                        'Bank Transfer' => 'bg-info-subtle text-info border border-info',
                        'UPI' => 'bg-success-subtle text-success border border-success',
                        'Cash' => 'bg-secondary-subtle text-secondary border border-secondary',
                        'Cheque' => 'bg-warning-subtle text-warning border border-warning',
                        'Direct Deposit' => 'bg-primary-subtle text-primary border border-primary',
                    ];
                    $badgeCls = $methodBadges[$row->payment_method] ?? 'bg-light text-dark border';
                    $bank = $row->credited_to_bank ? '<small class="text-muted d-block font-11"><i class="fas fa-university me-1"></i>' . e($row->credited_to_bank) . '</small>' : '';
                    $ref = $row->transaction_reference ? '<small class="text-muted d-block font-11">Ref: ' . e($row->transaction_reference) . '</small>' : '';

                    return '<div>
                                <span class="fw-semibold text-dark font-12 d-block">' . $dateStr . '</span>
                                <span class="badge rounded-pill ' . $badgeCls . ' font-10 px-2 py-0 mt-1">' . e($row->payment_method) . '</span>
                                ' . $bank . $ref . '
                            </div>';
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'credited') {
                        return '<span class="badge bg-success font-11 px-2 py-1"><i class="fas fa-check-circle me-1"></i>Credited</span>';
                    } elseif ($row->status === 'pending') {
                        return '<span class="badge bg-warning text-dark font-11 px-2 py-1"><i class="fas fa-clock me-1"></i>Pending</span>';
                    }
                    return '<span class="badge bg-secondary font-11 px-2 py-1">' . ucfirst(e($row->status)) . '</span>';
                })
                ->addColumn('payslip_link', function ($row) {
                    if ($row->payslip_document) {
                        return '<a href="' . asset('storage/' . $row->payslip_document) . '" target="_blank" class="btn btn-xs btn-outline-info font-11" title="View Document">
                                    <i class="fas fa-file-invoice me-1"></i> Slip
                                </a>';
                    }
                    return '<span class="text-muted font-11">-</span>';
                })
                ->addColumn('action', function ($row) {
                    $voucherBtn = '<button type="button" class="btn btn-sm btn-outline-info btn-view-salary-voucher" 
                                    data-id="' . $row->id . '" 
                                    title="View Salary Voucher">
                                    <i class="fas fa-receipt"></i>
                                </button>';

                    $editBtn = '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit-salary" 
                                    data-id="' . $row->id . '" 
                                    title="Edit Salary Details">
                                    <i class="fas fa-edit"></i>
                                </button>';

                    $delBtn = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-salary" 
                                data-id="' . $row->id . '" 
                                data-month="' . e($row->formatted_salary_month) . '" 
                                data-name="' . e($row->recipient_name) . '"
                                title="Delete Salary Record">
                                <i class="fas fa-trash-alt"></i>
                            </button>';

                    return '<div class="btn-group btn-group-sm" role="group">' . $voucherBtn . $editBtn . $delBtn . '</div>';
                })
                ->rawColumns(['month_badge', 'recipient_and_title', 'breakdown', 'net_salary_display', 'credit_details', 'status_badge', 'payslip_link', 'action'])
                ->make(true);
        }

        $stats = $this->calculateSalaryStats();
        $currentMonth = date('Y-m');
        $currentYear = date('Y');

        return view('backend.salary.index', compact('stats', 'currentMonth', 'currentYear'));
    }

    /**
     * Live stats summary AJAX
     */
    public function getStatsAjax(Request $request)
    {
        $year = $request->get('salary_year', date('Y'));
        return response()->json([
            'success' => true,
            'stats' => $this->calculateSalaryStats($year)
        ]);
    }

    /**
     * Calculate Summary Statistics
     */
    private function calculateSalaryStats(?string $year = null): array
    {
        $year = $year ?: date('Y');
        $currentMonth = date('Y-m');

        $yearQuery = Salary::where('salary_month', 'like', $year . '-%')->where('status', 'credited');
        $totalPaidThisYear = (float) $yearQuery->sum('net_salary');

        $thisMonthQuery = Salary::where('salary_month', $currentMonth)->where('status', 'credited');
        $thisMonthSalary = (float) $thisMonthQuery->sum('net_salary');

        // Total distinct months recorded this year for average calculation
        $monthsCount = Salary::where('salary_month', 'like', $year . '-%')->distinct('salary_month')->count('salary_month');
        $averageMonthlySalary = $monthsCount > 0 ? ($totalPaidThisYear / $monthsCount) : 0;

        $totalEntries = Salary::count();

        return [
            'year' => $year,
            'totalPaidThisYear' => $totalPaidThisYear,
            'totalPaidThisYearFormatted' => '₹' . number_format($totalPaidThisYear, 2),
            'thisMonthSalary' => $thisMonthSalary,
            'thisMonthSalaryFormatted' => '₹' . number_format($thisMonthSalary, 2),
            'averageMonthlySalary' => $averageMonthlySalary,
            'averageMonthlySalaryFormatted' => '₹' . number_format($averageMonthlySalary, 2),
            'totalEntries' => $totalEntries,
        ];
    }

    /**
     * Store new Salary record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'salary_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'salary_title' => 'required|string|max:150',
            'recipient_name' => 'required|string|max:150',
            'user_id' => 'nullable|integer',
            'base_amount' => 'required|numeric|min:1',
            'bonus_incentive' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'credit_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'credited_to_bank' => 'nullable|string|max:150',
            'transaction_reference' => 'nullable|string|max:100',
            'status' => 'required|string|in:credited,pending,partial',
            'payslip_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $docPath = null;
        if ($request->hasFile('payslip_document')) {
            $docPath = $request->file('payslip_document')->store('salary_documents', 'public');
        }

        $base = (float) $validated['base_amount'];
        $bonus = (float) ($validated['bonus_incentive'] ?? 0);
        $ded = (float) ($validated['deductions'] ?? 0);
        $net = max(0, $base + $bonus - $ded);

        $salary = Salary::create([
            'salary_month' => $validated['salary_month'],
            'salary_title' => $validated['salary_title'],
            'recipient_name' => $validated['recipient_name'],
            'user_id' => $validated['user_id'] ?? Auth::id(),
            'base_amount' => $base,
            'bonus_incentive' => $bonus,
            'deductions' => $ded,
            'net_salary' => $net,
            'credit_date' => $validated['credit_date'],
            'payment_method' => $validated['payment_method'],
            'credited_to_bank' => $validated['credited_to_bank'] ?? null,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'status' => $validated['status'],
            'payslip_document' => $docPath,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Salary record for ' . $salary->formatted_salary_month . ' added successfully!',
            'salary' => $salary
        ]);
    }

    /**
     * Get Salary data for edit modal
     */
    public function getData($salary)
    {
        if (!$salary instanceof Salary) {
            $salary = Salary::findOrFail($salary);
        }

        $data = $salary->toArray();
        $data['credit_date_formatted'] = $salary->credit_date ? $salary->credit_date->format('Y-m-d') : '';

        return response()->json([
            'success' => true,
            'salary' => $data
        ]);
    }

    /**
     * Update Salary record
     */
    public function update(Request $request, $salary)
    {
        if (!$salary instanceof Salary) {
            $salary = Salary::findOrFail($salary);
        }

        $validated = $request->validate([
            'salary_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'salary_title' => 'required|string|max:150',
            'recipient_name' => 'required|string|max:150',
            'user_id' => 'nullable|integer',
            'base_amount' => 'required|numeric|min:1',
            'bonus_incentive' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'credit_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'credited_to_bank' => 'nullable|string|max:150',
            'transaction_reference' => 'nullable|string|max:100',
            'status' => 'required|string|in:credited,pending,partial',
            'payslip_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('payslip_document')) {
            if ($salary->payslip_document && Storage::disk('public')->exists($salary->payslip_document)) {
                Storage::disk('public')->delete($salary->payslip_document);
            }
            $salary->payslip_document = $request->file('payslip_document')->store('salary_documents', 'public');
        }

        $base = (float) $validated['base_amount'];
        $bonus = (float) ($validated['bonus_incentive'] ?? 0);
        $ded = (float) ($validated['deductions'] ?? 0);
        $net = max(0, $base + $bonus - $ded);

        $salary->update([
            'salary_month' => $validated['salary_month'],
            'salary_title' => $validated['salary_title'],
            'recipient_name' => $validated['recipient_name'],
            'user_id' => $validated['user_id'] ?? $salary->user_id,
            'base_amount' => $base,
            'bonus_incentive' => $bonus,
            'deductions' => $ded,
            'net_salary' => $net,
            'credit_date' => $validated['credit_date'],
            'payment_method' => $validated['payment_method'],
            'credited_to_bank' => $validated['credited_to_bank'] ?? null,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Salary record updated successfully!',
            'salary' => $salary
        ]);
    }

    /**
     * Delete Salary record
     */
    public function destroy($salary)
    {
        if (!$salary instanceof Salary) {
            $salary = Salary::findOrFail($salary);
        }

        $monthStr = $salary->formatted_salary_month;
        if ($salary->payslip_document && Storage::disk('public')->exists($salary->payslip_document)) {
            Storage::disk('public')->delete($salary->payslip_document);
        }

        $salary->delete();

        return response()->json([
            'success' => true,
            'message' => 'Salary record for ' . $monthStr . ' deleted successfully!'
        ]);
    }

    /**
     * Get Voucher Details for viewing / printing
     */
    public function getVoucher($salary)
    {
        if (!$salary instanceof Salary) {
            $salary = Salary::findOrFail($salary);
        }

        return response()->json([
            'success' => true,
            'salary' => [
                'id' => $salary->id,
                'salary_month' => $salary->salary_month,
                'formatted_month' => $salary->formatted_salary_month,
                'salary_title' => $salary->salary_title,
                'recipient_name' => $salary->recipient_name,
                'base_amount' => '₹' . number_format((float) $salary->base_amount, 2),
                'bonus_incentive' => '₹' . number_format((float) $salary->bonus_incentive, 2),
                'deductions' => '₹' . number_format((float) $salary->deductions, 2),
                'net_salary' => '₹' . number_format((float) $salary->net_salary, 2),
                'credit_date' => $salary->credit_date ? $salary->credit_date->format('d M, Y') : '-',
                'payment_method' => $salary->payment_method,
                'credited_to_bank' => $salary->credited_to_bank ?: '-',
                'transaction_reference' => $salary->transaction_reference ?: '-',
                'status' => strtoupper($salary->status),
                'notes' => $salary->notes ?: '-',
                'created_at' => $salary->created_at ? $salary->created_at->format('d M, Y h:i A') : '-',
                'slip_url' => $salary->payslip_document ? asset('storage/' . $salary->payslip_document) : null,
            ]
        ]);
    }
}
