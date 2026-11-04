<?php

namespace App\Service;

use App\Enums\UserType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UserService
{

    public function index($request)
    {
        if ($request->ajax()) {
            $data = User::where('type', UserType::USER)->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    $btn = $row['created_at'];
                    return $btn;
                })
                ->addColumn('name', function ($row) {
                    return '<div class="d-flex align-items-center">
                        <div>
                            <span class="fw-bold text-dark d-block">' . e($row->name) . '</span>
                            <small class="text-muted">' . e($row->email) . '</small>
                        </div>
                    </div>';
                })
                ->addColumn('image', function ($row) {
                    $imagePath = $row->image == 'avatar.jpg' ? url('img/avatar.jpg') : url('storage/user/'. $row->image);
                    return '<img src="' . $imagePath . '" alt="user" width="38px" height="38px" class="rounded-circle shadow-sm">';
                })
                ->addColumn('phone', function ($row) {
                    return $row->phone ? '<a href="tel:' . $row->phone . '" class="text-primary fw-semibold">' . $row->phone . '</a>' : '<span class="text-muted">-</span>';
                })
                ->addColumn('type', function ($row) {
                    $badge = $row->type == 'Admin' ? 'bg-purple text-white' : ($row->type == 'delivery' ? 'bg-info text-white' : 'bg-primary text-white');
                    $label = $row->type == 'customer' ? 'Tenant / User' : ucfirst($row->type);
                    return '<span class="badge rounded-pill ' . $badge . ' px-2 py-1">' . $label . '</span>';
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch mb-0">
                        <input class="form-check-input status-toggle" type="checkbox" '.$checked.' data-id="'.Crypt::encrypt($row->id).'" style="cursor:pointer; width: 36px; height: 18px;">
                    </div>';
                })
               ->addColumn('action', function ($row) {
                    $encId = Crypt::encrypt($row['id']);
                    $viewUrl = url('/admin/users/' . $encId . '/view');

                    $btn = '<div class="btn-group btn-group-sm" role="group">';
                    $btn .= '<a href="' . $viewUrl . '" class="btn btn-outline-info" title="View Profile"><i class="fas fa-eye"></i></a>';
                    $btn .= '<button type="button" class="btn btn-outline-primary btn-edit-user" data-id="' . $encId . '" title="Edit User"><i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete-user" data-id="' . $encId . '" data-name="' . e($row->name) . '" title="Delete User"><i class="fas fa-trash-alt"></i></button>';
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['name','image','phone','type', 'status', 'action'])
                ->make(true);
        }
        return view('backend.user.user_index');
    }

    public function update(array $data, User $user)
    {
        DB::beginTransaction();
        $user->fill($data);
        $user->save();
        DB::commit();
        session()->flash("success", "User has been successfully updated!");
        return redirect('/admin/users');
    }

    public function destroy($user)
    {
        if ($user->count() != 0) {
            $user->delete();
            return ['status' => '1'];
        } else {
            return ['status' => '0'];
        }
    }

    public function view(User $user)
    {
        return view('backend.user.user_view', compact('user'));
    }

    public function getUserOrders($request, $user)
    {
        $orders = Order::where('user_id', $user->id)->latest();

        return DataTables::of($orders)
        ->addColumn('created_at', fn($order) => $order->created_at->format('d-m-Y'))
        ->addColumn('items_count', fn($order) => $order->orderItems->count())
        ->addColumn('total_amount', fn($order) => '₹' . number_format($order->total_amount, 2))
        ->addColumn('status', function ($order) {
            $class = $order->status == 'Completed' ? 'success' : 'warning';
            return '<span class="badge bg-' . $class . '">' . ucfirst($order->status) . '</span>';
        })
        ->addColumn('actions', function ($order) {
            $encryptedId = Crypt::encrypt($order->id);
            return '<button class="btn btn-sm add-btn text-white view-order-items" data-id="' . $encryptedId . '">
                        <i class="mdi mdi-eye-outline"></i> View
                    </button>';
        })
        ->rawColumns(['status', 'actions', 'created_at'])
        ->make(true);
    }

    public function updateStatus($request)
    {

        try {
            $id = Crypt::decrypt($request->id);
            $user = User::findOrFail($id);

            $user->status = $request->status;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: '.$e->getMessage()
            ], 500);
        }
    }
}
