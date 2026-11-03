<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ProductAlert::where('user_id', auth()->id())->with('product')->latest();

            switch ($request->filter) {
                case 'unread':
                    $query->unread();
                    break;
                case 'critical':
                    $query->critical();
                    break;
                case 'low':
                    $query->low();
                    break;
                default:
                    break;
            }

            return DataTables::of($query)
                ->addColumn('icon', function($alert) {
                    return $alert->type === 'critical_stock'
                        ? '<i class="mdi mdi-alert-circle text-danger"></i>'
                        : '<i class="mdi mdi-alert text-warning"></i>';
                })
                ->addColumn('product_name', function($alert) {
                    return '<a href="'.url('shop-admin/products/'.$alert->product_id.'/edit').'">'.$alert->product->name.'</a>';
                })
                ->addColumn('type_badge', function($alert) {
                    $class = $alert->type === 'critical_stock' ? 'danger' : 'warning';
                    $type = Str::title(str_replace('_', ' ', $alert->type));
                    return '<span class="badge bg-'.$class.'">'.$type.'</span>';
                })
                ->addColumn('status_badge', function($alert) {
                    return $alert->is_read
                        ? '<span class="badge bg-success">Read</span>'
                        : '<span class="badge bg-secondary">Unread</span>';
                })
                ->addColumn('actions', function($alert) {
                    $buttons = '';

                    if (!$alert->is_read) {
                        $buttons .= '<form method="POST" action="'.url('shop-admin/alerts/'.Crypt::encrypt($alert->id).'/read').'" class="d-inline">
                            '.csrf_field().'
                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Mark as Read">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>';
                    }

                    $buttons .= '<a href="'.url('shop-admin/products/'.$alert->product_id.'/edit').'" class="btn btn-sm btn-outline-info ms-1" title="View Product">
                        <i class="fas fa-eye"></i>
                    </a>';

                    return $buttons;
                })

                ->editColumn('created_at', function($alert) {
                    return $alert->created_at->format('M j, Y H:i');
                })
                ->editColumn('triggered_by', function($alert) {
                    return Str::title(str_replace('_', ' ', $alert->triggered_by));
                })
                ->rawColumns(['icon', 'product_name', 'type_badge', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('backend.alerts.index');
    }

    public function markAsRead(ProductAlert $alert)
    {
        $alert->markAsRead();

        return response()->json(['success' => 'Alert marked as read']);
    }

    public function markAllAsRead()
    {
        ProductAlert::where('user_id', auth()->id())->unread()->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['success' => 'All alerts marked as read']);
    }
}
