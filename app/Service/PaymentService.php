<?php

namespace App\Service;

use App\Http\Controllers\FilterController;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class PaymentService
{

    public function index($request)
    {
        if ($request->ajax()) {
            $query = Payment::orderBy('id', 'desc');

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            if ($request->has('search') && !empty($request->search['value'])) {
                $searchTerm = $request->search['value'];

                $query->where(function ($q) use ($searchTerm) {
                    $q->where('payment_no', 'like', "%{$searchTerm}%")
                        ->orWhere('transaction_id', 'like', "%{$searchTerm}%")
                        ->orWhere('merchant_transaction_id', 'like', "%{$searchTerm}%")
                        ->orWhere('type', 'like', "%{$searchTerm}%")
                        ->orWhere('payment_method', 'like', "%{$searchTerm}%")
                        ->orWhere('amount', 'like', "%{$searchTerm}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                            $userQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('phone', 'like', "%{$searchTerm}%");
                        })
                        ->orWhereHas('order', function ($orderQuery) use ($searchTerm) {
                            $orderQuery->where('order_no', 'like', "%{$searchTerm}%");
                                // ->orWhereHas('shop', function ($shopQuery) use ($searchTerm) {
                                //     $shopQuery->where('name', 'like', "%{$searchTerm}%");
                                // });
                        });
                });
            }

            if ($request->has('filters')) {
                $filters = is_string($request->filters) ? json_decode($request->filters, true) : $request->filters;
                $logic = $filters[0]['logic'] ?? 'and';

                $needsUserJoin = collect($filters)->contains(fn($f) => in_array($f['column'], ['name', 'phone']));

                if ($needsUserJoin) {
                    $query->leftJoin('users', 'users.id', '=', 'payment_requests.user_id')
                        ->select('payment_requests.*', 'users.name as user_name', 'users.phone as user_phone');
                }

                $query->where(function ($q) use ($filters, $logic) {
                    foreach ($filters as $filter) {
                        FilterController::applyFilter($q, $filter, $logic);
                    }
                });
            }
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('d-m-Y h:i A');
                })
                ->addColumn('name', function ($row) {
                    return $row->user ? '<a href="' . url('admin/users/' . Crypt::encrypt($row->user->id)) . '/view">' . $row->user->name . '</a>' : '-';
                })
                ->addColumn('amount', function ($row) {
                    return '₹' . number_format($row->amount, 2) ?? '-';
                })
                // ->addColumn('payment_no', function ($row) {
                //     return $row->payment_no ?? '-';
                // })
                ->addColumn('transaction_id', function ($row) {
                    return $row->transaction_id ?? '-';
                })
                // ->addColumn('merchant_transaction_id', function ($row) {
                //     return $row->merchant_transaction_id ?? '-';
                // })
                ->addColumn('type', function ($row) {
                    return $row->type ?? '-';
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user ? '<a href="' . url('admin/users/' . Crypt::encrypt($row->user->id)) . '/view">' . $row->user->name . '</a>' : '-';
                })
               ->addColumn('order_id', function ($row) {
                    if ($row->order && $row->order->order_no) {
                        return '<a href="' . url('/admin/orders/' . Crypt::encrypt($row->order->id)) . '">' . $row->order->order_no . '</a>';
                    }
                    return '-';
                })
                // ->addColumn('response', function ($row) {
                //     return $row->response ?? '-';
                // })
                ->addColumn('payment_method', function ($row) {
                    return $row->payment_method ?? '-';
                })
                ->addColumn('payment_table_status', function ($row) {
                    switch ($row->status) {
                        case 'Paid':
                            return '<span class="badge bg-success float-start">Paid</span>';
                        case 'Pending':
                            return '<span class="badge bg-warning text-dark float-start">Pending</span>';
                        case 'Refund':
                            return '<span class="badge bg-danger float-start">Refund</span>';
                        default:
                            return '<span class="badge bg-secondary float-start">Unknown</span>';
                    }
                })
                ->rawColumns(['name', 'payment_table_status', 'user_name', 'order_id'])
                ->make(true);
        }

        return view('backend.payment.payment_index');
    }
}
