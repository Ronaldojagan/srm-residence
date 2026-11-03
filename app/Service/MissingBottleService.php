<?php

namespace App\Service;

use App\Enums\UserType;
use App\Models\MissingBottle;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class MissingBottleService
{

    public function index($request)
    {
        if ($request->ajax()) {
            $data = Order::with('missingBottles', 'user')->whereHas('missingBottles')->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    $btn = $row['created_at'];
                    return $btn;
                })
                ->addColumn('name', function ($row) {
                    return $row->user ? '<a href="' . url('admin/users/' . Crypt::encrypt($row->user->id)) . '/view">' . $row->user->name . '</a>' : '-';
                })
                ->addColumn('order_id', function ($row) {
                    return '<a href="' . url('/admin/orders/' . Crypt::encrypt($row->id)) . '">' . $row->order_no . '</a>';
                })
                ->addColumn('phone', function ($row) {
                    if ($row->user->phone) {
                        $btn = '<a href="tel:' . $row->user->phone . '">' . $row->user->phone . '</a>' ?? '-';
                    } else {
                        $btn = '-';
                    }
                    return $btn;
                })
                ->addColumn('location', function ($row) {
                    $shippingAddress = $row->shippingAddress;
                    return $shippingAddress
                        ? $shippingAddress->city . ' - ' . $shippingAddress->district
                        : '-';
                })
                ->addColumn('status', function ($row) {
                    switch ($row->status) {
                        case 'Returned':
                            return '<span class="badge bg-success float-start">Returned</span>';
                        case 'Pending':
                            return '<span class="badge bg-warning text-dark float-start">Pending</span>';
                        default:
                            return '<span class="badge bg-secondary float-start">Unknown</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = url('/admin/bottle-missing/' . Crypt::encrypt($row['id']) . '/view');
                    $btn = '<a href="' . $viewUrl . '" class="me-2" title="View">
                                <img src="' . url('img/eye-icon.png') . '" alt="View" width="30">
                            </a>';
                    $btn .= '<a href="javascript:void(0)" data-id="' . Crypt::encrypt($row->id) . '" class="p-1 delete" title="Delete">
                                <img src="' . url('img/delete-icon.png') . '" alt="Delete" width="30">
                            </a>';

                    return $btn;
                })
                ->rawColumns(['name', 'phone', 'status', 'action',])
                ->make(true);
        }
        return view('backend.bottle_missing.index');
    }

    public function destroy(int $orderId)
    {
        MissingBottle::where('order_id', $orderId)->delete();
        return ['status' => 1];
    }

    public function view(Order $order)
    {
        $shippingAddress = $order->shippingAddress;
        return view('backend.bottle_missing.view', compact('order', 'shippingAddress'));
    }

    public function orderMissingBottleList($order)
    {
        $data = MissingBottle::with(['order.user'])
            ->where('order_id', $order->id)
            ->latest()
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('missed_date', function ($row) {
                return $row->created_at?->format('d-m-Y') ?? '-';
            })
            ->addColumn('returned_date', function ($row) {
                return $row->date
                    ? \Carbon\Carbon::parse($row->date)->format('d-m-Y')
                    : '-';
            })
            ->addColumn('status', function ($row) {
                switch ($row->status) {
                    case 'Returned':
                        return '<span class="badge bg-success">Returned</span>';

                    case 'Pending':
                        return '<span class="badge bg-warning text-dark">Pending</span>';

                    default:
                        return '<span class="badge bg-secondary">Unknown</span>';
                }
            })
            ->rawColumns(['status'])
            ->make(true);
    }
}
