<?php

namespace App\Http\Controllers\Api\Shop;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\ShopOrder;
use App\Service\Api\Shop\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function orderDetails($id)
    {
        return $this->orderService->orderDetails($id);
    }

    public function changeListOrderStatus()
    {
        return $this->orderService->changeListOrderStatus();
    }

    public function changeOrderStatus(Request $request, $id)
    {
        return $this->orderService->changeOrderStatus($request, $id);
    }

    public function shopVerifyReturn(Request $request, $orderId, $itemId)
    {
        return $this->orderService->shopVerifyReturn($request, $orderId, $itemId);
    }

    public function shopVerifyReplace(Request $request, $orderId, $itemId)
    {
        return $this->orderService->shopVerifyReplace($request, $orderId, $itemId);
    }


    public function getOrderList(Request $request)
    {
        $user = auth()->user();

        if (!$user->shop) {
            return response()->json([
                'status' => 0,
                'message' => 'Shop not found'
            ], 404);
        }

        $limit    = $request->input('limit', 10);
        $search   = $request->input('search');
        $orderBy  = $request->input('orderBy', 'id');
        $orderDir = $request->input('orderDir', 'desc');
        $status   = $request->input('status');

        $query = ShopOrder::with(['order:id,order_no', 'orderItems:id,order_id,product_image'])
            ->where('shop_id', $user->shop->id);

        if ($request->filled(['from_date', 'to_date'])) {
            $query->whereBetween('created_at', [
                $request->from_date,
                $request->to_date
            ]);
        }
        // else {
        //     $query->whereBetween('created_at', [
        //         now()->subWeek()->startOfWeek(),
        //         now()->subWeek()->endOfWeek()
        //     ]);
        // }

        $filterData = $this->getOrderCounts(clone $query, $status);

        if ($search) {
            $query->search($search);
        }

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        $orders = $query->orderBy($orderBy, $orderDir)->paginate($limit);

        $orders->getCollection()->transform(function ($shopOrder) {
            $item = $shopOrder->order->orderItems->first();

            return [
                'order_id'    => optional($shopOrder->order)->id,
                'order_no'    => optional($shopOrder->order)->order_no,
                'date'        => $shopOrder->created_at->format('d-m-Y h:i A'),
                'total_items' => $shopOrder->orderItems->count(),
                'status'      => ucfirst($shopOrder->status),
                'item_image'  => $item?->image_url,
            ];
        });

        return response()->json([
            'status' => 1,
            'message' => 'Order List',
            'filter_data' => $filterData,
            'data' => $orders->items(),
            'pagination' => [
                'current_page'      => $orders->currentPage(),
                'last_page'         => $orders->lastPage(),
                'total'             => $orders->total(),
                'per_page'          => $orders->perPage(),
                'current_page_url'  => $orders->url($orders->currentPage()),
                'next_page_url'     => $orders->nextPageUrl(),
                'prev_page_url'     => $orders->previousPageUrl(),
            ]
        ], 200);
    }

    public function getOrderCounts($query, $status = null)
    {
        return [
            'all_status_count' => (clone $query)->count(),
            'pending_status_count' => (clone $query)->where('status', OrderStatus::PENDING)->count(),
            'in_progress_status_count' => (clone $query)->where('status', OrderStatus::PROCESSING)->count(),
            'ready_to_deliver_status_count' => (clone $query)->where('status', OrderStatus::SHIPPED)->count(),
            'active_filter' => $status ?? 'All',
        ];
    }
}
