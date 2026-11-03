<?php

namespace App\Service;

use App\Enums\OrderStatus;
use App\Enums\UserType;
use App\Http\Controllers\FilterController;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Crypt;

class ReportService
{
    public function reportFilter($request)
    {
        if ($request->ajax()) {
            $type = $request->type;
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            if (empty($type)) {
                return response()->json(['data' => []]);
            }

            if ($type == 'User') {
                $query = User::where('type', UserType::USER)->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('id', 'desc');

                if (!empty($request->search['value'])) {
                    $search = $request->search['value'];

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%")
                            ->orWhere('status', 'like', "%$search%")
                            ->orWhereRaw("DATE(created_at) LIKE ?", ["%$search%"]);
                    });
                }

                if ($request->has('filters')) {
                    $filters = is_string($request->filters) ? json_decode($request->filters, true) : $request->filters;
                    $logic = $filters[0]['logic'] ?? 'and';

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
                        return $row ? '<a href="' . url('admin/users/' . Crypt::encrypt($row->id)) . '/view">' . $row->name . '</a>' : '-';
                    })
                    ->addColumn('email', function ($row) {
                        return $row->email ? '<a href="mailto:' .$row->email . '">' . $row->email . '</a>' : "-";
                    })
                    ->addColumn('phone', function ($row) {
                        return $row->phone ? '<a href="tel:' .$row->phone . '">' . $row->phone . '</a>' : "-";
                    })
                    ->addColumn('status', function ($row) {
                        return $row->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                    })
                    ->rawColumns(['date', 'name', 'email', 'phone', 'status'])
                    ->make(true);
            } elseif ($type == 'Shops') {
                $query = Shop::with('user')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('id', 'desc');

                if (!empty($request->search['value'])) {
                    $search = $request->search['value'];

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%")
                            ->orWhere('status', 'like', "%$search%")
                            ->orWhere('created_at', 'like', "%$search%")
                            ->orWhereHas('user', function ($sub) use ($search) {
                                $sub->where('name', 'like', "%$search%");
                            });
                    });
                }

                if ($request->has('filters')) {
                    $filters = is_string($request->filters) ? json_decode($request->filters, true) : $request->filters;
                    $logic = $filters[0]['logic'] ?? 'and';

                    $query->where(function ($q) use ($filters, $logic) {
                        foreach ($filters as $filter) {
                            FilterController::applyFilter($q, $filter, $logic);
                        }
                    });
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('date', function ($row) {
                        return $row->created_at->format('d-m-Y');
                    })
                    ->addColumn('shop_name', function ($row) {
                        return $row->name ?? '-';
                    })
                    ->addColumn('owner', function ($row) {
                        return $row->user->name ?? '-';
                    })
                    ->addColumn('location', function ($row) {
                        return $row->address ?? '-';
                    })
                    ->addColumn('status', function ($row) {
                        return $row->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                    })
                    ->rawColumns(['date', 'shop_name', 'owner', 'location', 'status'])
                    ->make(true);
            } elseif ($type == 'Product') {
                $query = Product::with('category')->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('id', 'desc');

                if (!empty($request->search['value'])) {
                    $search = $request->search['value'];

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('status', 'like', "%$search%")
                            ->orWhere('amount', 'like', "%$search%")
                            ->orWhere('product_stock', 'like', "%$search%")
                            ->orWhere('created_at', 'like', "%$search%")
                            ->orWhereHas('category', function ($sub) use ($search) {
                                $sub->where('name', 'like', "%$search%");
                            });
                    });
                }
                if ($request->has('filters')) {
                    $filters = is_string($request->filters) ? json_decode($request->filters, true) : $request->filters;
                    $logic = $filters[0]['logic'] ?? 'and';

                    $query->where(function ($q) use ($filters, $logic) {
                        foreach ($filters as $filter) {
                            FilterController::applyFilter($q, $filter, $logic);
                        }
                    });
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('date', function ($row) {
                        return $row->created_at->format('d-m-Y');
                    })
                    ->addColumn('product_name', function ($row) {
                        return $row->name ?? '-';
                    })
                    ->addColumn('category', function ($row) {
                        return $row->category->name ?? '-';
                    })
                    ->addColumn('price', function ($row) {
                        return '₹' . number_format($row->amount, 2) ?? '-';
                    })
                    ->addColumn('product_stock', function ($row) {
                        return $row->product_stock ?? '0';
                    })
                    ->addColumn('status', function ($row) {
                        return $row->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                    })
                    ->rawColumns(['date', 'product_name', 'category', 'price', 'product_stock', 'status'])
                    ->make(true);
            } elseif ($type == 'Order') {
                $query = Order::with('user')->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('id', 'desc');

                if (!empty($request->search['value'])) {
                    $search = $request->search['value'];

                    $query->where(function ($q) use ($search) {
                        $q->where('order_no', 'like', "%$search%")
                            ->orWhere('status', 'like', "%$search%")
                            ->orWhere('total_amount', 'like', "%$search%")
                            ->orWhere('created_at', 'like', "%$search%")
                            ->orWhereHas('user', function ($sub) use ($search) {
                                $sub->where('name', 'like', "%$search%");
                            });
                    });
                }
                if ($request->has('filters')) {
                    $filters = is_string($request->filters) ? json_decode($request->filters, true) : $request->filters;
                    $logic = $filters[0]['logic'] ?? 'and';

                    $query->where(function ($q) use ($filters, $logic) {
                        foreach ($filters as $filter) {
                            FilterController::applyFilter($q, $filter, $logic);
                        }
                    });
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('date', function ($row) {
                        return $row->created_at->format('d-m-Y');
                    })
                    ->addColumn('order_id', function ($row) {
                        return $row->order_no ?? '-';
                    })
                    ->addColumn('customer', function ($row) {
                        return $row->user->name ?? '-';
                    })
                    ->addColumn('shop_name', function ($row) {
                        return $row->shop->name ?? '-';
                    })
                    ->addColumn('total_amount', function ($row) {
                        return '₹' . number_format($row->total_amount, 2) ?? '-';
                    })
                    ->addColumn('order_status', function ($row) {
                        $statusLabels = [
                            OrderStatus::PENDING => 'warning',
                            OrderStatus::PROCESSING => 'info',
                            OrderStatus::COMPLETED => 'success',
                        ];

                        $status = $row->status;
                        $badgeClass = $statusLabels[$status] ?? 'dark';

                        return '<span class="badge bg-' . $badgeClass . '">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
                    })
                    ->rawColumns(['date', 'order_id', 'customer', 'total_amount', 'order_status', 'shop_name'])
                    ->make(true);
            }
        }

        return view('backend.report.report');
    }
}
