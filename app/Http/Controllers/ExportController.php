<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use App\Exports\SingleOrdersExport;

class ExportController extends Controller
{
    public function exportOrders(Request $request)
    {
        $orderIds = $request->has('order_ids') ? explode(',', $request->order_ids) : null;

        $fileName = $orderIds ? 'selected_orders_' . date('Ymd_His') . '.xlsx' : 'all_orders_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new OrdersExport($orderIds), $fileName);
    }

    public function exportSingle(Order $order)
    {
        $fileName = 'order_' . $order->order_no . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new SingleOrdersExport([$order->id]), $fileName);
    }
}
