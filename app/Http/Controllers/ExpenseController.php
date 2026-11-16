<?php

namespace App\Http\Controllers;

use App\Exports\PersonalExpensesExport;
use App\Exports\RoomExpensesExport;
use App\Models\Expense;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function roomExpenses(Request $request)
    {
        if ($request->ajax()) {
            $query = Expense::where('expense_type', 'room')->with('room');

            if ($request->filled('room_id')) {
                $query->where('room_id', $request->room_id);
            }
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }
            if ($request->filled('start_date')) {
                $query->whereDate('expense_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('expense_date', '<=', $request->end_date);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check-input bulk-select-row" value="' . $row->id . '">';
                })
                ->addColumn('room_badge', function ($row) {
                    if ($row->room) {
                        return '<span class="badge bg-primary text-white fw-bold px-2 py-1">
                                    <i class="mdi mdi-door-closed me-1"></i> Room ' . e($row->room->room_number) . '
                                </span>
                                <div class="small text-muted mt-1">' . e($row->room->floor) . '</div>';
                    }
                    return '<span class="badge bg-secondary text-white px-2 py-1">Common Area</span>';
                })
                ->addColumn('title_desc', function ($row) {
                    $notes = $row->notes ? '<small class="text-muted d-block text-wrap" style="max-width:260px;">' . e(Str::limit($row->notes, 50)) . '</small>' : '';
                    return '<div>
                        <span class="fw-bold text-dark d-block">' . e($row->title) . '</span>
                        ' . $notes . '
                    </div>';
                })
                ->addColumn('category_badge', function ($row) {
                    return '<span class="badge bg-info-subtle text-info border border-info px-2 py-1">' . e($row->category) . '</span>';
                })
                ->addColumn('amount_display', function ($row) {
                    $total = (float)$row->amount;
                    $paid = (float)$row->paid_amount;
                    $due = max(0, round($total - $paid, 2));
                    $percent = $total > 0 ? min(100, round(($paid / $total) * 100)) : 0;
                    $barColor = $percent >= 100 ? 'bg-success' : ($percent > 0 ? 'bg-warning' : 'bg-secondary');

                    $statusBadge = '';
                    if ($row->payment_status === 'paid' || ($total > 0 && $due <= 0)) {
                        $statusBadge = '<span class="badge bg-success text-white font-10 px-2 py-0"><i class="fas fa-check-circle me-1"></i>Paid</span>';
                    } elseif ($paid > 0) {
                        $statusBadge = '<span class="badge bg-warning text-dark font-10 px-2 py-0"><i class="fas fa-adjust me-1"></i>Partial</span>';
                    } else {
                        $statusBadge = '<span class="badge bg-danger text-white font-10 px-2 py-0"><i class="fas fa-clock me-1"></i>Pending</span>';
                    }

                    return '<div style="min-width: 140px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-danger font-13">₹' . number_format($total, 2) . '</span>
                            ' . $statusBadge . '
                        </div>
                        <div class="d-flex justify-content-between font-11 fw-semibold mb-1">
                            <span class="text-success">Paid: ₹' . number_format($paid, 2) . '</span>
                            <span class="text-danger">Due: ₹' . number_format($due, 2) . '</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar ' . $barColor . '" role="progressbar" style="width: ' . $percent . '%;" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>';
                })
                ->addColumn('expense_date_formatted', function ($row) {
                    return $row->expense_date ? $row->expense_date->format('d M Y') : '-';
                })
                ->addColumn('paid_to_display', function ($row) {
                    return '<span class="fw-semibold text-secondary">' . e($row->paid_to ?? '-') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $payBtn = '';
                    if ($row->payment_status !== 'paid' && $row->due_amount > 0) {
                        $payBtn = '<button type="button" class="btn btn-outline-success btn-mark-expense-paid" data-id="' . $row->id . '" data-title="' . e($row->title) . '" title="Mark as Paid"><i class="fas fa-check-circle me-1"></i> Pay</button>';
                    } else {
                        $payBtn = '<button type="button" class="btn btn-outline-secondary btn-mark-expense-unpaid" data-id="' . $row->id . '" data-title="' . e($row->title) . '" title="Mark as Unpaid"><i class="fas fa-undo"></i></button>';
                    }

                    $btn = '<div class="btn-group btn-group-sm" role="group">';
                    $btn .= $payBtn;
                    $btn .= '<button type="button" class="btn btn-outline-primary btn-edit-expense" data-id="' . $row->id . '" title="Edit Expense"><i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete-expense" data-id="' . $row->id . '" data-title="' . e($row->title) . '" title="Delete Expense"><i class="fas fa-trash-alt"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['checkbox', 'room_badge', 'title_desc', 'category_badge', 'amount_display', 'paid_to_display', 'action'])
                ->make(true);
        }

        $stats = $this->getRoomExpenseStats();
        $rooms = Room::orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC')->get();

        return view('backend.expenses.room_expenses', compact('stats', 'rooms'));
    }

    public function personalExpenses(Request $request)
    {
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));

        if ($request->ajax()) {
            $query = Expense::where('expense_type', 'personal')->latest('expense_date');

            if ($request->filled('month')) {
                $monthVal = $request->month;
                if ($monthVal !== 'all') {
                    if (preg_match('/^\d{4}-\d{2}$/', $monthVal)) {
                        $carbonMonth = Carbon::createFromFormat('Y-m', $monthVal);
                        $query->where(function ($q) use ($carbonMonth, $monthVal) {
                            $q->where('expense_month', $carbonMonth->format('F Y'))
                              ->orWhere('expense_month', $monthVal)
                              ->orWhere(function ($sub) use ($carbonMonth) {
                                  $sub->whereYear('expense_date', $carbonMonth->year)
                                      ->whereMonth('expense_date', $carbonMonth->month);
                              });
                        });
                    } else {
                        $query->where('expense_month', $monthVal);
                    }
                }
            }

            if ($request->filled('paid_to')) {
                $query->where('paid_to', 'like', '%' . $request->paid_to . '%');
            }
            if ($request->filled('start_date')) {
                $query->whereDate('expense_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('expense_date', '<=', $request->end_date);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check-input bulk-select-row" value="' . $row->id . '">';
                })
                ->addColumn('title_desc', function ($row) {
                    $notes = $row->notes ? '<small class="text-muted d-block text-wrap font-11 mt-1" style="max-width:320px;">' . e(Str::limit($row->notes, 65)) . '</small>' : '';
                    $monthBadge = $row->expense_month ? '<span class="badge bg-light text-muted border font-10 me-1"><i class="far fa-calendar-alt me-1"></i>' . e($row->expense_month) . '</span>' : '';

                    return '<div>
                        <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                            ' . $monthBadge . '
                            <span class="fw-bold text-dark font-13">' . e($row->title) . '</span>
                        </div>
                        ' . $notes . '
                    </div>';
                })
                ->addColumn('items_summary', function ($row) {
                    if (!empty($row->items) && is_array($row->items) && count($row->items) > 0) {
                        $count = count($row->items);
                        $paidCount = 0;
                        foreach ($row->items as $it) {
                            if (!empty($it['is_paid'])) {
                                $paidCount++;
                            }
                        }
                        $pendingCount = $count - $paidCount;

                        $previewItems = [];
                        foreach (array_slice($row->items, 0, 2) as $it) {
                            $qtyLabel = !empty($it['quantity']) && (float)$it['quantity'] != 1 ? ' (' . $it['quantity'] . 'x)' : '';
                            $itemPaidIcon = !empty($it['is_paid']) ? ' <i class="fas fa-check text-success font-10"></i>' : '';
                            $previewItems[] = e($it['title']) . $qtyLabel . $itemPaidIcon;
                        }
                        $previewText = implode(', ', $previewItems);
                        if ($count > 2) {
                            $previewText .= ' +' . ($count - 2) . ' more';
                        }

                        $statusBadge = '';
                        if ($pendingCount === 0) {
                            $statusBadge = '<span class="badge bg-success-subtle text-success border border-success font-10 px-1 py-0"><i class="fas fa-check-circle me-1"></i>All Paid</span>';
                        } elseif ($paidCount > 0) {
                            $statusBadge = '<span class="badge bg-warning-subtle text-dark border border-warning font-10 px-1 py-0">' . $paidCount . '/' . $count . ' Paid</span>';
                        } else {
                            $statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger font-10 px-1 py-0">' . $count . ' Pending</span>';
                        }

                        return '<div>
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-11 cursor-pointer btn-view-items" data-id="' . $row->id . '">
                                    <i class="fas fa-list-ul me-1"></i> ' . $count . ' Items
                                </span>
                                ' . $statusBadge . '
                            </div>
                            <small class="text-muted d-block font-11 text-truncate" style="max-width: 220px;" title="' . strip_tags($previewText) . '">' . $previewText . '</small>
                        </div>';
                    }
                    return '<span class="text-muted font-11">Single Entry</span>';
                })
                ->addColumn('amount_display', function ($row) {
                    $total = (float)$row->amount;
                    $paid = (float)$row->paid_amount;
                    $due = max(0, round($total - $paid, 2));
                    $percent = $total > 0 ? min(100, round(($paid / $total) * 100)) : 0;
                    $barColor = $percent >= 100 ? 'bg-success' : ($percent > 0 ? 'bg-warning' : 'bg-secondary');

                    $statusBadge = '';
                    if ($row->payment_status === 'paid' || ($total > 0 && $due <= 0)) {
                        $statusBadge = '<span class="badge bg-success text-white font-10 px-2 py-0"><i class="fas fa-check-circle me-1"></i>Paid</span>';
                    } elseif ($paid > 0) {
                        $statusBadge = '<span class="badge bg-warning text-dark font-10 px-2 py-0"><i class="fas fa-adjust me-1"></i>Partial</span>';
                    } else {
                        $statusBadge = '<span class="badge bg-danger text-white font-10 px-2 py-0"><i class="fas fa-clock me-1"></i>Pending</span>';
                    }

                    return '<div style="min-width: 140px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-danger font-13">₹' . number_format($total, 2) . '</span>
                            ' . $statusBadge . '
                        </div>
                        <div class="d-flex justify-content-between font-11 fw-semibold mb-1">
                            <span class="text-success">Paid: ₹' . number_format($paid, 2) . '</span>
                            <span class="text-danger">Due: ₹' . number_format($due, 2) . '</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar ' . $barColor . '" role="progressbar" style="width: ' . $percent . '%;" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>';
                })
                ->addColumn('expense_date_formatted', function ($row) {
                    return $row->expense_date ? $row->expense_date->format('d M Y') : '-';
                })
                ->addColumn('paid_to_display', function ($row) {
                    return '<span class="fw-semibold text-secondary">' . e($row->paid_to ?? '-') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $hasItems = !empty($row->items) && is_array($row->items) && count($row->items) > 0;
                    $itemsBtn = $hasItems ? '<button type="button" class="btn btn-outline-info btn-view-items" data-id="' . $row->id . '" title="View & Pay Line Items Breakdown"><i class="fas fa-list-ul me-1"></i> Items</button>' : '';

                    $payBtn = '';
                    if ($row->payment_status !== 'paid' && $row->due_amount > 0) {
                        $payBtn = '<button type="button" class="btn btn-outline-success btn-mark-expense-paid" data-id="' . $row->id . '" data-title="' . e($row->title) . '" title="Mark All as Paid"><i class="fas fa-check-circle me-1"></i> Pay</button>';
                    } else {
                        $payBtn = '<button type="button" class="btn btn-outline-secondary btn-mark-expense-unpaid" data-id="' . $row->id . '" data-title="' . e($row->title) . '" title="Mark as Unpaid"><i class="fas fa-undo"></i></button>';
                    }

                    $btn = '<div class="btn-group btn-group-sm" role="group">';
                    $btn .= $payBtn;
                    $btn .= $itemsBtn;
                    $btn .= '<button type="button" class="btn btn-outline-primary btn-edit-expense" data-id="' . $row->id . '" title="Edit Expense"><i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete-expense" data-id="' . $row->id . '" data-title="' . e($row->title) . '" title="Delete Expense"><i class="fas fa-trash-alt"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['checkbox', 'title_desc', 'items_summary', 'amount_display', 'paid_to_display', 'action'])
                ->make(true);
        }

        $stats = $this->getPersonalExpenseStats($selectedMonth);

        // Available months for filtering (past 12 months)
        $availableMonths = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $availableMonths[$date->format('Y-m')] = $date->format('F Y');
        }

        return view('backend.expenses.personal_expenses', compact('stats', 'availableMonths', 'selectedMonth'));
    }

    public function store(Request $request)
    {
        $isPersonal = $request->expense_type === 'personal';

        $rules = [
            'expense_type' => 'required|in:room,personal',
            'room_id' => 'nullable|required_if:expense_type,room|exists:rooms,id',
            'category' => $isPersonal ? 'nullable|string|max:100' : 'required|string|max:100',
            'title' => 'required|string|min:2|max:255',
            'amount' => 'nullable|numeric|min:0|max:10000000',
            'expense_date' => 'required|date',
            'expense_month' => 'nullable|string|max:50',
            'payment_method' => $isPersonal ? 'nullable|string|max:50' : 'required|string|max:50',
            'paid_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.title' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.per_rate' => 'nullable|numeric|min:0',
            'items.*.amount' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ];

        $messages = [
            'room_id.required_if' => 'Please select a room for room-specific expenses.',
            'category.required' => 'Please select an expense category.',
            'title.required' => 'Expense title/item description is required.',
            'expense_date.required' => 'Expense date is required.',
            'payment_method.required' => 'Please select a payment method.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($isPersonal && empty($validated['category'])) {
            $validated['category'] = 'Personal';
        }

        // Process line items if provided
        $cleanItems = [];
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                if (!empty($item['title'])) {
                    $qty = isset($item['quantity']) && is_numeric($item['quantity']) && (float)$item['quantity'] > 0
                        ? (float)$item['quantity']
                        : 1;

                    $rate = isset($item['per_rate']) && is_numeric($item['per_rate'])
                        ? (float)$item['per_rate']
                        : null;

                    if ($rate !== null && $rate >= 0) {
                        $amt = isset($item['amount']) && is_numeric($item['amount']) && (float)$item['amount'] > 0
                            ? (float)$item['amount']
                            : round($qty * $rate, 2);
                    } else {
                        $amt = isset($item['amount']) && is_numeric($item['amount']) ? (float)$item['amount'] : 0;
                        $rate = $qty > 0 ? round($amt / $qty, 2) : $amt;
                    }

                    $isPaid = !empty($item['is_paid']) && ($item['is_paid'] == '1' || $item['is_paid'] === true || $item['is_paid'] === 'true');

                    if ($amt > 0 || $rate > 0) {
                        $cleanItems[] = [
                            'title' => trim($item['title']),
                            'quantity' => $qty,
                            'per_rate' => $rate !== null ? (float)$rate : round($amt / ($qty ?: 1), 2),
                            'amount' => (float)$amt,
                            'notes' => trim($item['notes'] ?? ''),
                            'is_paid' => $isPaid,
                            'paid_at' => $isPaid ? ($item['paid_at'] ?? now()->toDateTimeString()) : null,
                        ];
                    }
                }
            }
        }

        if (!empty($cleanItems)) {
            $validated['items'] = $cleanItems;
            $validated['amount'] = array_sum(array_column($cleanItems, 'amount'));
        } else {
            $validated['items'] = null;
            if (empty($validated['amount']) || (float)$validated['amount'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please specify an expense amount or add at least one line item with an amount.',
                ], 422);
            }
        }

        // Auto determine expense_month
        if (empty($validated['expense_month'])) {
            $validated['expense_month'] = Carbon::parse($validated['expense_date'])->format('F Y');
        }

        $validated['created_by'] = auth()->id();

        $expense = Expense::create($validated);
        $expense->syncPaymentStatus(true);

        $stats = $expense->expense_type === 'room' ? $this->getRoomExpenseStats() : $this->getPersonalExpenseStats($expense->expense_month);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => ucfirst($expense->expense_type) . ' expense recorded successfully!',
                'expense' => $expense,
                'stats' => $stats
            ]);
        }

        $redirect = $expense->expense_type === 'room' ? '/admin/expenses/rooms' : '/admin/expenses/personal';
        return redirect($redirect)->with('success', 'Expense recorded successfully!');
    }

    public function getData(Expense $expense)
    {
        return response()->json([
            'success' => true,
            'expense' => $expense->load('room')
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $isPersonal = $expense->expense_type === 'personal' || $request->expense_type === 'personal';

        $rules = [
            'room_id' => 'nullable|exists:rooms,id',
            'category' => $isPersonal ? 'nullable|string|max:100' : 'required|string|max:100',
            'title' => 'required|string|min:2|max:255',
            'amount' => 'nullable|numeric|min:0|max:10000000',
            'expense_date' => 'required|date',
            'expense_month' => 'nullable|string|max:50',
            'payment_method' => $isPersonal ? 'nullable|string|max:50' : 'required|string|max:50',
            'paid_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.title' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.per_rate' => 'nullable|numeric|min:0',
            'items.*.amount' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ];

        $messages = [
            'category.required' => 'Please select an expense category.',
            'title.required' => 'Expense title is required.',
            'expense_date.required' => 'Expense date is required.',
            'payment_method.required' => 'Please select a payment method.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($isPersonal && empty($validated['category'])) {
            $validated['category'] = 'Personal';
        }

        $cleanItems = [];
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                if (!empty($item['title'])) {
                    $qty = isset($item['quantity']) && is_numeric($item['quantity']) && (float)$item['quantity'] > 0
                        ? (float)$item['quantity']
                        : 1;

                    $rate = isset($item['per_rate']) && is_numeric($item['per_rate'])
                        ? (float)$item['per_rate']
                        : null;

                    if ($rate !== null && $rate >= 0) {
                        $amt = isset($item['amount']) && is_numeric($item['amount']) && (float)$item['amount'] > 0
                            ? (float)$item['amount']
                            : round($qty * $rate, 2);
                    } else {
                        $amt = isset($item['amount']) && is_numeric($item['amount']) ? (float)$item['amount'] : 0;
                        $rate = $qty > 0 ? round($amt / $qty, 2) : $amt;
                    }

                    $isPaid = !empty($item['is_paid']) && ($item['is_paid'] == '1' || $item['is_paid'] === true || $item['is_paid'] === 'true');

                    if ($amt > 0 || $rate > 0) {
                        $cleanItems[] = [
                            'title' => trim($item['title']),
                            'quantity' => $qty,
                            'per_rate' => $rate !== null ? (float)$rate : round($amt / ($qty ?: 1), 2),
                            'amount' => (float)$amt,
                            'notes' => trim($item['notes'] ?? ''),
                            'is_paid' => $isPaid,
                            'paid_at' => $isPaid ? ($item['paid_at'] ?? now()->toDateTimeString()) : null,
                        ];
                    }
                }
            }
        }

        if (!empty($cleanItems)) {
            $validated['items'] = $cleanItems;
            $validated['amount'] = array_sum(array_column($cleanItems, 'amount'));
        } else {
            $validated['items'] = null;
            if (empty($validated['amount']) || (float)$validated['amount'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please specify an expense amount or add at least one line item with an amount.',
                ], 422);
            }
        }

        if (empty($validated['expense_month'])) {
            $validated['expense_month'] = Carbon::parse($validated['expense_date'])->format('F Y');
        }

        $expense->update($validated);
        $expense->syncPaymentStatus(true);

        $stats = $expense->expense_type === 'room' ? $this->getRoomExpenseStats() : $this->getPersonalExpenseStats($expense->expense_month);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully!',
                'expense' => $expense,
                'stats' => $stats
            ]);
        }

        $redirect = $expense->expense_type === 'room' ? '/admin/expenses/rooms' : '/admin/expenses/personal';
        return redirect($redirect)->with('success', 'Expense updated successfully!');
    }

    /**
     * Toggle or update paid status for an individual line item or all line items in an expense
     */
    public function toggleItemPaid(Request $request, Expense $expense)
    {
        $index = $request->input('item_index');
        $action = $request->input('action', 'toggle'); // 'toggle', 'all_paid', 'all_unpaid'

        $items = $expense->items;

        if ($action === 'all_paid') {
            if (is_array($items) && count($items) > 0) {
                foreach ($items as &$it) {
                    $it['is_paid'] = true;
                    $it['paid_at'] = now()->toDateTimeString();
                }
                unset($it);
                $expense->items = $items;
            }
            $expense->paid_amount = $expense->amount;
            $expense->payment_status = 'paid';
            $expense->status = true;
            $expense->save();
            $message = "All line items in '{$expense->title}' marked as Paid!";
        } elseif ($action === 'all_unpaid') {
            if (is_array($items) && count($items) > 0) {
                foreach ($items as &$it) {
                    $it['is_paid'] = false;
                    $it['paid_at'] = null;
                }
                unset($it);
                $expense->items = $items;
            }
            $expense->paid_amount = 0.00;
            $expense->payment_status = 'unpaid';
            $expense->status = false;
            $expense->save();
            $message = "All line items in '{$expense->title}' marked as Unpaid / Pending.";
        } elseif ($index !== null && is_array($items) && isset($items[$index])) {
            $currentlyPaid = !empty($items[$index]['is_paid']);
            $newStatus = !$currentlyPaid;
            $items[$index]['is_paid'] = $newStatus;
            $items[$index]['paid_at'] = $newStatus ? now()->toDateTimeString() : null;
            $expense->items = $items;
            $expense->syncPaymentStatus(true);
            $itemName = $items[$index]['title'] ?? 'Line item';
            $itemAmt = number_format((float)($items[$index]['amount'] ?? 0), 2);
            $message = $newStatus ? "'{$itemName}' marked as Paid (₹{$itemAmt})!" : "'{$itemName}' marked as Unpaid / Pending.";
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid line item index or action.'], 422);
        }

        $stats = $expense->expense_type === 'room' ? $this->getRoomExpenseStats() : $this->getPersonalExpenseStats($expense->expense_month);

        return response()->json([
            'success' => true,
            'message' => $message,
            'expense' => $expense->fresh(),
            'stats' => $stats
        ]);
    }

    /**
     * Mark an entire expense as Paid or Unpaid
     */
    public function markExpensePaid(Request $request, Expense $expense)
    {
        $status = $request->input('status', 'paid');
        $isPaid = $status === 'paid';

        $items = $expense->items;
        if (is_array($items) && count($items) > 0) {
            foreach ($items as &$it) {
                $it['is_paid'] = $isPaid;
                $it['paid_at'] = $isPaid ? now()->toDateTimeString() : null;
            }
            unset($it);
            $expense->items = $items;
        }

        $expense->paid_amount = $isPaid ? (float)$expense->amount : 0.00;
        $expense->payment_status = $isPaid ? 'paid' : 'unpaid';
        $expense->status = $isPaid;
        $expense->save();

        $stats = $expense->expense_type === 'room' ? $this->getRoomExpenseStats() : $this->getPersonalExpenseStats($expense->expense_month);

        return response()->json([
            'success' => true,
            'message' => $isPaid ? "Expense '{$expense->title}' marked as fully Paid!" : "Expense '{$expense->title}' marked as Unpaid / Pending.",
            'expense' => $expense->fresh(),
            'stats' => $stats
        ]);
    }

    public function destroy(Expense $expense)
    {
        $type = $expense->expense_type;
        $month = $expense->expense_month;
        $expense->delete();

        $stats = $type === 'room' ? $this->getRoomExpenseStats() : $this->getPersonalExpenseStats($month);

        return response()->json([
            'success' => true,
            'status' => '1',
            'message' => 'Expense removed successfully!',
            'stats' => $stats
        ]);
    }

    public function getPersonalStatsAjax(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $stats = $this->getPersonalExpenseStats($month);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    public function getRoomStatsAjax(Request $request)
    {
        $stats = $this->getRoomExpenseStats();

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    private function getRoomExpenseStats()
    {
        $now = Carbon::now();
        $total = (float) Expense::where('expense_type', 'room')->sum('amount');
        $totalPaid = (float) Expense::where('expense_type', 'room')->sum('paid_amount');
        $totalBalance = max(0, round($total - $totalPaid, 2));

        $thisMonth = (float) Expense::where('expense_type', 'room')
            ->whereMonth('expense_date', $now->month)
            ->whereYear('expense_date', $now->year)
            ->sum('amount');
        $thisMonthPaid = (float) Expense::where('expense_type', 'room')
            ->whereMonth('expense_date', $now->month)
            ->whereYear('expense_date', $now->year)
            ->sum('paid_amount');
        $thisMonthBalance = max(0, round($thisMonth - $thisMonthPaid, 2));

        $roomCount = Room::count();
        $avgPerRoom = $roomCount > 0 ? round($total / $roomCount) : 0;
        $totalRecords = Expense::where('expense_type', 'room')->count();

        return [
            'totalExpenses' => $total,
            'totalPaid' => $totalPaid,
            'totalBalance' => $totalBalance,
            'thisMonthExpenses' => $thisMonth,
            'thisMonthPaid' => $thisMonthPaid,
            'thisMonthBalance' => $thisMonthBalance,
            'avgPerRoom' => $avgPerRoom,
            'totalRecords' => $totalRecords,
        ];
    }

    private function getPersonalExpenseStats($month = null)
    {
        $allTotal = (float) Expense::where('expense_type', 'personal')->sum('amount');
        $allPaid = (float) Expense::where('expense_type', 'personal')->sum('paid_amount');
        $allBalance = max(0, round($allTotal - $allPaid, 2));
        $allRecords = Expense::where('expense_type', 'personal')->count();

        $monthQuery = Expense::where('expense_type', 'personal');
        $monthLabel = 'This Month';

        if (!empty($month) && $month !== 'all') {
            if (preg_match('/^\d{4}-\d{2}$/', $month)) {
                $cMonth = Carbon::createFromFormat('Y-m', $month);
                $monthLabel = $cMonth->format('F Y');
                $monthQuery->where(function ($q) use ($cMonth, $month) {
                    $q->where('expense_month', $cMonth->format('F Y'))
                      ->orWhere('expense_month', $month)
                      ->orWhere(function ($sub) use ($cMonth) {
                          $sub->whereYear('expense_date', $cMonth->year)
                              ->whereMonth('expense_date', $cMonth->month);
                      });
                });
            } else {
                $monthLabel = $month;
                $monthQuery->where('expense_month', $month);
            }
        } else {
            $now = Carbon::now();
            $monthLabel = $now->format('F Y');
            $monthQuery->whereMonth('expense_date', $now->month)
                       ->whereYear('expense_date', $now->year);
        }

        $monthExpenses = (float) (clone $monthQuery)->sum('amount');
        $monthPaid = (float) (clone $monthQuery)->sum('paid_amount');
        $monthBalance = max(0, round($monthExpenses - $monthPaid, 2));
        $monthRecords = (clone $monthQuery)->count();
        $paidRecords = (clone $monthQuery)->where('payment_status', 'paid')->count();
        $pendingRecords = (clone $monthQuery)->where('payment_status', '!=', 'paid')->count();
        $highestExpense = (clone $monthQuery)->max('amount') ?? 0;

        $topCategory = (clone $monthQuery)
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        // Count line items in this month
        $monthWithItems = (clone $monthQuery)->whereNotNull('items')->get();
        $lineItemsCount = 0;
        $paidLineItems = 0;
        $pendingLineItems = 0;
        foreach ($monthWithItems as $mExp) {
            if (is_array($mExp->items)) {
                foreach ($mExp->items as $it) {
                    $lineItemsCount++;
                    if (!empty($it['is_paid'])) {
                        $paidLineItems++;
                    } else {
                        $pendingLineItems++;
                    }
                }
            }
        }

        $effectiveTotal = ($month === 'all') ? $allTotal : $monthExpenses;
        $effectivePaid = ($month === 'all') ? $allPaid : $monthPaid;
        $effectiveBalance = max(0, round($effectiveTotal - $effectivePaid, 2));

        return [
            'totalExpenses' => $effectiveTotal,
            'totalPaid' => $effectivePaid,
            'totalBalance' => $effectiveBalance,
            'allTotal' => $allTotal,
            'allPaid' => $allPaid,
            'allBalance' => $allBalance,
            'monthLabel' => $monthLabel,
            'thisMonthExpenses' => $monthExpenses,
            'thisMonthPaid' => $monthPaid,
            'thisMonthBalance' => $monthBalance,
            'monthRecords' => ($month === 'all') ? $allRecords : $monthRecords,
            'paidRecords' => $paidRecords,
            'pendingRecords' => $pendingRecords,
            'highestExpense' => $highestExpense,
            'topCategory' => $topCategory ? $topCategory->category . ' (₹' . number_format($topCategory->total) . ')' : 'None',
            'lineItemsCount' => $lineItemsCount,
            'paidLineItems' => $paidLineItems,
            'pendingLineItems' => $pendingLineItems,
            'totalRecords' => $allRecords,
        ];
    }

    /**
     * Export Room Expenses to Excel (.xlsx) with wide auto-sized columns and total amounts in the last row
     */
    public function exportRoomExpenses(Request $request)
    {
        $fileName = 'srm_residence_room_expenses_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new RoomExpensesExport($request), $fileName);
    }

    /**
     * Export Personal Expenses with Itemized Line Items to Excel (.xlsx) with wide auto-sized columns and total amounts in the last row
     */
    public function exportPersonalExpenses(Request $request)
    {
        $fileName = 'srm_residence_personal_expenses_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new PersonalExpensesExport($request), $fileName);
    }

    /**
     * Bulk Delete Expenses
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:expenses,id',
        ]);

        $count = count($validated['ids']);
        Expense::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} expense record(s)!"
        ]);
    }

    /**
     * Bulk Update Expenses
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:expenses,id',
            'category' => 'nullable|string|max:100',
            'expense_month' => 'nullable|string|max:50',
            'expense_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'paid_to' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:500',
        ]);

        $updateData = [];
        if (!empty($validated['category'])) $updateData['category'] = $validated['category'];
        if (!empty($validated['expense_month'])) $updateData['expense_month'] = $validated['expense_month'];
        if (!empty($validated['expense_date'])) $updateData['expense_date'] = $validated['expense_date'];
        if (!empty($validated['payment_method'])) $updateData['payment_method'] = $validated['payment_method'];
        if (!empty($validated['paid_to'])) $updateData['paid_to'] = $validated['paid_to'];
        if (isset($validated['notes']) && $validated['notes'] !== '') $updateData['notes'] = $validated['notes'];

        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No fields were provided to update.'
            ], 422);
        }

        $count = count($validated['ids']);
        Expense::whereIn('id', $validated['ids'])->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$count} expense record(s)!"
        ]);
    }
}
