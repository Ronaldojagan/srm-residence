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
                    return $row->name ?? '-';
                })
                ->addColumn('image', function ($row) {
                    $imagePath = $row->image == 'avatar.jpg' ? url('img/avatar.jpg') : url('storage/user/'. $row->image);
                    $userDetailLink = '<a href="' . url('/admin/users/' . Crypt::encrypt($row->id) . '/view') . '">' . '</a>';
                    $btn = '<img src="' . $imagePath . '" data-url="' . $imagePath . '" data-toggle="modal" data-target="#image-view" alt="user-img" width="35px" height="35px" class="rounded-circle data-table-image edit open-image-model">' . $userDetailLink;
                    return $btn;
                })
                ->addColumn('phone', function ($row) {
                    if($row->phone){
                        $btn = '<a href="tel:' .$row->phone . '">' . $row->phone . '</a>' ?? '-';
                    }else{
                        $btn = $row->phone ?? '-';
                    }
                    return $btn;
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" '.$checked.' data-id="'.Crypt::encrypt($row->id).'">
                    </div>';
                })
               ->addColumn('action', function ($row) {
                    $viewUrl = url('/admin/users/' . Crypt::encrypt($row['id']) . '/view');
                    $editUrl = url('/admin/users/' . Crypt::encrypt($row['id']) . '/edit');

                    $btn = '<a href="' . $viewUrl . '" class="me-2" title="View">
                                <img src="' . url('img/eye-icon.png') . '" alt="View" width="30">
                            </a>';
                    $btn .= '<a href="' . $editUrl . '" class="me-2" title="Edit">
                                <img src="' . url('img/edit-icon.png') . '" alt="Edit" width="30">
                            </a>';

                    return $btn;
                })
                ->rawColumns(['name','image','phone', 'status', 'action',])
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
