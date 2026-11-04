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
        $rooms = Room::orderBy('room_number')->get();

        return view('backend.expenses.room_expenses', compact('stats', 'rooms'));
    }

    public function personalExpenses(Request $request)
    {
        if ($request->ajax()) {
            $query = Expense::where('expense_type', 'personal');

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
                ->addColumn('title_desc', function ($row) {
                    $notes = $row->notes ? '<small class="text-muted d-block text-wrap" style="max-width:280px;">' . e(Str::limit($row->notes, 50)) . '</small>' : '';
                    return '<div>
                        <span class="fw-bold text-dark d-block">' . e($row->title) . '</span>
                        ' . $notes . '
                    </div>';
                })
                ->addColumn('category_badge', function ($row) {
                    return '<span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1">' . e($row->category) . '</span>';
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
                ->rawColumns(['title_desc', 'category_badge', 'amount_display', 'paid_to_display', 'action'])
                ->make(true);
        }

        $stats = $this->getPersonalExpenseStats();

        return view('backend.expenses.personal_expenses', compact('stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_type' => 'required|in:room,personal',
            'room_id' => 'nullable|exists:rooms,id',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'paid_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

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
        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'paid_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

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

    private function getPersonalExpenseStats()
    {
        $now = Carbon::now();
        $total = Expense::where('expense_type', 'personal')->sum('amount');
        $thisMonth = Expense::where('expense_type', 'personal')
            ->whereMonth('expense_date', $now->month)
            ->whereYear('expense_date', $now->year)
            ->sum('amount');
        $topCategory = Expense::where('expense_type', 'personal')
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();
        $totalRecords = Expense::where('expense_type', 'personal')->count();

        return [
            'totalExpenses' => $total,
            'thisMonthExpenses' => $thisMonth,
            'topCategory' => $topCategory ? $topCategory->category . ' (₹' . number_format($topCategory->total) . ')' : 'None',
            'totalRecords' => $totalRecords,
        ];
    }
}
