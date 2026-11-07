<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
                    return '<div>
                        <span class="fw-bold text-danger fs-6">₹' . number_format($row->amount, 2) . '</span>
                        <small class="text-muted d-block"><i class="far fa-credit-card me-1"></i>' . e($row->payment_method) . '</small>
                    </div>';
                })
                ->addColumn('expense_date_formatted', function ($row) {
                    return $row->expense_date ? $row->expense_date->format('d M Y') : '-';
                })
                ->addColumn('paid_to_display', function ($row) {
                    return '<span class="fw-semibold text-secondary">' . e($row->paid_to ?? '-') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group btn-group-sm" role="group">';
                    $btn .= '<button type="button" class="btn btn-outline-primary btn-edit-expense" data-id="' . $row->id . '" title="Edit Expense"><i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete-expense" data-id="' . $row->id . '" data-title="' . e($row->title) . '" title="Delete Expense"><i class="fas fa-trash-alt"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['room_badge', 'title_desc', 'category_badge', 'amount_display', 'paid_to_display', 'action'])
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
                        $previewItems = [];
                        foreach (array_slice($row->items, 0, 2) as $it) {
                            $qtyLabel = !empty($it['quantity']) && (float)$it['quantity'] != 1 ? ' (' . $it['quantity'] . 'x)' : '';
                            $previewItems[] = e($it['title']) . $qtyLabel;
                        }
                        $previewText = implode(', ', $previewItems);
                        if ($count > 2) {
                            $previewText .= ' +' . ($count - 2) . ' more';
                        }

                        return '<div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-11 cursor-pointer btn-view-items" data-id="' . $row->id . '">
                                <i class="fas fa-list-ul me-1"></i> ' . $count . ' Line Items
                            </span>
                            <small class="text-muted d-block font-11 mt-1 text-truncate" style="max-width: 200px;" title="' . e($previewText) . '">' . $previewText . '</small>
                        </div>';
                    }
                    return '<span class="text-muted font-11">Single Entry</span>';
                })
                ->addColumn('amount_display', function ($row) {
                    return '<span class="fw-bold text-danger fs-6">₹' . number_format($row->amount, 2) . '</span>';
                })
                ->addColumn('expense_date_formatted', function ($row) {
                    return $row->expense_date ? $row->expense_date->format('d M Y') : '-';
                })
                ->addColumn('paid_to_display', function ($row) {
                    return '<span class="fw-semibold text-secondary">' . e($row->paid_to ?? '-') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $hasItems = !empty($row->items) && is_array($row->items) && count($row->items) > 0;
                    $itemsBtn = $hasItems ? '<button type="button" class="btn btn-outline-info btn-view-items" data-id="' . $row->id . '" title="View Line Items Breakdown"><i class="fas fa-list-ul"></i></button>' : '';

                    $btn = '<div class="btn-group btn-group-sm" role="group">';
                    $btn .= $itemsBtn;
                    $btn .= '<button type="button" class="btn btn-outline-primary btn-edit-expense" data-id="' . $row->id . '" title="Edit Expense"><i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete-expense" data-id="' . $row->id . '" data-title="' . e($row->title) . '" title="Delete Expense"><i class="fas fa-trash-alt"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['title_desc', 'items_summary', 'amount_display', 'paid_to_display', 'action'])
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

                    if ($amt > 0 || $rate > 0) {
                        $cleanItems[] = [
                            'title' => trim($item['title']),
                            'quantity' => $qty,
                            'per_rate' => $rate !== null ? (float)$rate : round($amt / ($qty ?: 1), 2),
                            'amount' => (float)$amt,
                            'notes' => trim($item['notes'] ?? ''),
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

        $stats = $expense->expense_type === 'room' ? $this->getRoomExpenseStats() : $this->getPersonalExpenseStats();

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

                    if ($amt > 0 || $rate > 0) {
                        $cleanItems[] = [
                            'title' => trim($item['title']),
                            'quantity' => $qty,
                            'per_rate' => $rate !== null ? (float)$rate : round($amt / ($qty ?: 1), 2),
                            'amount' => (float)$amt,
                            'notes' => trim($item['notes'] ?? ''),
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

        $stats = $expense->expense_type === 'room' ? $this->getRoomExpenseStats() : $this->getPersonalExpenseStats();

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

    public function destroy(Expense $expense)
    {
        $type = $expense->expense_type;
        $expense->delete();

        $stats = $type === 'room' ? $this->getRoomExpenseStats() : $this->getPersonalExpenseStats();

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

    private function getRoomExpenseStats()
    {
        $now = Carbon::now();
        $total = Expense::where('expense_type', 'room')->sum('amount');
        $thisMonth = Expense::where('expense_type', 'room')
            ->whereMonth('expense_date', $now->month)
            ->whereYear('expense_date', $now->year)
            ->sum('amount');
        $roomCount = Room::count();
        $avgPerRoom = $roomCount > 0 ? round($total / $roomCount) : 0;
        $totalRecords = Expense::where('expense_type', 'room')->count();

        return [
            'totalExpenses' => $total,
            'thisMonthExpenses' => $thisMonth,
            'avgPerRoom' => $avgPerRoom,
            'totalRecords' => $totalRecords,
        ];
    }

    private function getPersonalExpenseStats($month = null)
    {
        $allTotal = Expense::where('expense_type', 'personal')->sum('amount');
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

        $monthExpenses = (clone $monthQuery)->sum('amount');
        $monthRecords = (clone $monthQuery)->count();
        $highestExpense = (clone $monthQuery)->max('amount') ?? 0;

        $topCategory = (clone $monthQuery)
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        // Count line items in this month
        $monthWithItems = (clone $monthQuery)->whereNotNull('items')->get();
        $lineItemsCount = 0;
        foreach ($monthWithItems as $mExp) {
            if (is_array($mExp->items)) {
                $lineItemsCount += count($mExp->items);
            }
        }

        return [
            'totalExpenses' => $allTotal,
            'thisMonthExpenses' => $monthExpenses,
            'monthLabel' => $monthLabel,
            'monthRecords' => $monthRecords,
            'highestExpense' => $highestExpense,
            'topCategory' => $topCategory ? $topCategory->category . ' (₹' . number_format($topCategory->total) . ')' : 'None',
            'lineItemsCount' => $lineItemsCount,
            'totalRecords' => $allRecords,
        ];
    }
}
