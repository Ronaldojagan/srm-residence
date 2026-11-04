<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Service\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->middleware('auth');
        $this->userService = $userService;
    }
    public function index(Request $request)
    {
        return $this->userService->index($request);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'type' => 'required|string',
            'status' => 'nullable'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'type' => $validated['type'],
            'status' => $request->has('status') ? 1 : 1,
            'image' => 'avatar.jpg',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully!',
                'user' => $user
            ]);
        }

        return redirect('/admin/users')->with('success', 'User created successfully!');
    }

    public function getUserData(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => $user,
            'encrypted_id' => Crypt::encrypt($user->id)
        ]);
    }

    public function edit(User $user)
    {
        return view('backend.user.user', [
            'edit' => $user,
        ]);
    }
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'type' => 'required|string',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? $user->phone;
        $user->type = $validated['type'];

        if (!empty($validated['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!'
            ]);
        }

        session()->flash("success", "User has been successfully updated!");
        return redirect('/admin/users');
    }
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'status' => '1',
            'success' => true,
            'message' => 'User deleted successfully!'
        ]);
    }
    public function view(User $user)
    {
        // Get basic stats
        $stats = [
            'total_orders' => $user->orders()->count(),
            'total_payments' => $user->orders()->sum('total_amount'),
            'total_addresses' => $user->addresses()->count(),
        ];

        // Get addresses
        $addresses = $user->addresses()->latest()->get();

        // Prepare chart data
        $orderChartData = $this->getOrderChartData($user);
        $statusChartData = $this->getStatusChartData($user);

        return view('backend.user.user_view', compact(
            'user',
            'stats',
            'addresses',
            'orderChartData',
            'statusChartData'
        ));
    }

    public function getUserStats($userId, Request $request)
    {
        $user = User::findOrFail(Crypt::decrypt($userId));

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Calculate stats with date range
        $query = $user->orders();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $stats = [
            'total_orders' => $query->count(),
            'total_payments' => $query->sum('total_amount'),
            'total_addresses' => $user->addresses()->count(),
            'order_chart' => $this->getOrderChartData($user, $startDate, $endDate),
            'status_chart' => $this->getStatusChartData($user, $startDate, $endDate)
        ];

        return response()->json($stats);
    }

    public function getUserOrders(Request $request, $userId)
    {
        $user = User::findOrFail(Crypt::decrypt($userId));

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $orders = $user->orders()->withCount('orderItems')->latest();

        if ($startDate && $endDate) {
            $orders->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->has('search') && !empty($request->search['value'])) {
            $searchTerm = $request->search['value'];

            $orders->where(function ($q) use ($searchTerm) {
                $q->where('order_no', 'like', "%{$searchTerm}%")
                ->orWhere('total_amount', 'like', "%{$searchTerm}%")
                ->orWhere('status', 'like', "%{$searchTerm}%");
                if (\Carbon\Carbon::hasFormat($searchTerm, 'd-m-Y')) {
                    $q->orWhereDate('created_at', \Carbon\Carbon::createFromFormat('d-m-Y', $searchTerm)->format('Y-m-d'));
                }
            });
        }

        return DataTables::of($orders)
            ->addColumn('created_at', fn($order) => $order->created_at->format('d-m-Y'))
            ->addColumn('items_count', fn($order) => $order->orderItems->count())
            ->addColumn('order_no', fn($order) => $order->order_no)
            ->addColumn('total_amount', fn($order) => '₹' . number_format($order->total_amount, 2))
            ->addColumn('status', function ($order) {
                $statusLabels = [
                        OrderStatus::PENDING => 'warning',
                        OrderStatus::PROCESSING => 'primary',
                        OrderStatus::SHIPPED => 'info',
                        OrderStatus::COMPLETED => 'success',
                        OrderStatus::CANCELLED => 'danger',
                        OrderStatus::REFUNDED => 'secondary',
                    ];

                    $status = $order->status;
                    $badgeClass = $statusLabels[$status] ?? 'dark';

                    return '<span class="badge bg-' . $badgeClass . '">' . OrderStatus::getDescription($status) . '</span>';
            })
            ->addColumn('actions', function ($order) {
                $encryptedId = Crypt::encrypt($order->id);
                return '<button class="btn btn-sm add-btn text-white view-order-items" data-id="' . $encryptedId . '">
                        <i class="mdi mdi-eye-outline"></i> View
                    </button>';
            })
            ->rawColumns(['status', 'actions', 'created_at', 'total_amount', 'order_no'])
            ->make(true);
    }
    private function getOrderChartData(User $user, $startDate = null, $endDate = null)
    {
        $query = $user->orders()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByRaw('DATE(created_at)');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        $data = $query->get();

        // ✅ Normalize the collection to [date => count]
        $countsByDate = $data->mapWithKeys(function ($item) {
            return [Carbon::parse($item->date)->format('Y-m-d') => $item->count];
        });

        // Fill in all dates
        $chartData = [];
        $currentDate = $startDate ? Carbon::parse($startDate) : now()->subDays(29);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $chartData[$dateString] = $countsByDate[$dateString] ?? 0;
            $currentDate->addDay();
        }

        return [
            'labels' => array_keys($chartData),
            'data' => array_values($chartData),
        ];
    }


    private function getStatusChartData(User $user, $startDate = null, $endDate = null)
    {
        $query = $user->orders()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $data = $query->get();

        if ($data->isEmpty()) {
            return ['labels' => [], 'data' => []];
        }

        return [
            'labels' => $data->pluck('status')->toArray(),
            'data' => $data->pluck('count')->toArray()
        ];
    }

    public function updateStatus(Request $request)
    {
        return $this->userService->updateStatus($request);
    }

    public function getUserDetails($id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
            $user = User::findOrFail($decryptedId);

            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load user details'
            ]);
        }
    }
}
