<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusHistory;
use App\Models\Shipment;
use App\Models\Shop;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Service\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->middleware('auth');
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        return $this->orderService->index($request);
    }

    public function bulkCancel(Request $request)
    {
        return $this->orderService->bulkCancel($request);
    }

    public function cancel(Request $request)
    {
        return $this->orderService->cancel($request);
    }

    public function view(Order $order)
    {
        return $this->orderService->view($order);
    }

    public function invoice(Order $order)
    {
        return $this->orderService->invoice($order);
    }

    public function updateStatus(Request $request, Order $order)
    {
        return $this->orderService->updateStatus($request, $order);
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        return $this->orderService->updatePaymentStatus($request, $order);
    }

    public function cancelProduct(Request $request, Order $order, OrderItem $orderItem)
    {
        return $this->orderService->cancelProduct($request, $order, $orderItem);
    }

    public function getProductStatusHistory(OrderItem $orderItem)
    {
        return $this->orderService->getProductStatusHistory($orderItem);
    }

    public function processPartialRefund(Request $request, Order $order)
    {
        return $this->orderService->processPartialRefund($request, $order);
    }

    public function getAvailableCouriers()
    {
        return $this->orderService->getAvailableCouriers();
    }

    public function getItemsForDispatch(Order $order)
    {
        return $this->orderService->getItemsForDispatch($order);
    }

    public function dispatchOrderItems(Request $request, Order $order)
    {
        return $this->orderService->dispatchOrderItems($request, $order);
    }

    public function showDispatchForm(Order $order)
    {
        return view('backend.order.dispatch_items', compact('order'));
    }

    public function markShipmentDelivered(Request $request, Shipment $shipment)
    {
        return $this->orderService->markShipmentDelivered($request, $shipment);
    }

    public function getShipmentTracking(Shipment $shipment)
    {
        return $this->orderService->getShipmentTracking($shipment);
    }

    public function updateItemStatus(Request $request)
    {
        return $this->orderService->updateItemStatus($request);
    }

    public function updateBulkItemStatus(Request $request)
    {
        return $this->orderService->updateBulkItemStatus($request);
    }

    public function markAllDelivered(Request $request)
    {
        $request->validate([
            'order_id' => 'required'
        ]);

        try {
            $orderId = Crypt::decrypt($request->order_id);
            $order = Order::findOrFail($orderId);

            DB::beginTransaction();

            $updatedItems = 0;
            foreach ($order->orderItems as $item) {
                if (!in_array($item->status->value, ['delivered', 'cancelled', 'returned', 'refunded'])) {
                    $item->update(['status' => OrderItemStatus::DELIVERED, 'delivered_at' => now()]);

                    OrderItemStatusHistory::create([
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                        'status' => OrderItemStatus::DELIVERED->value,
                        'notes' => 'Marked as delivered (bulk action)',
                        'created_by' => auth()->id()
                    ]);

                    $updatedItems++;
                }
            }

            $order->update(['status' => OrderStatus::COMPLETED]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->withProperties(['items_updated' => $updatedItems])
                ->log('Marked all products as delivered');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Marked {$updatedItems} products as delivered",
                'order' => $order->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark items as delivered: ' . $e->getMessage()
            ], 500);
        }
    }

    public function submitReturnRequest(Request $request)
    {
        return $this->orderService->submitReturnRequest($request);
    }

    public function approveReturn(Request $request)
    {
        return $this->orderService->approveReturn($request);
    }

    public function rejectReturn(Request $request)
    {
        return $this->orderService->rejectReturn($request);
    }

    public function submitReplaceRequest(Request $request)
    {
        return $this->orderService->submitReplaceRequest($request);
    }

    public function approveReplace(Request $request)
    {
        return $this->orderService->approveReplace($request);
    }

    public function rejectReplace(Request $request)
    {
        return $this->orderService->rejectReplace($request);
    }

    public function getModelName(Request $request)
    {
        $model = $request->query('model');
        $id = $request->query('id');

        if (!$model || !$id) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        try {
            $modelClass = match ($model) {
                'shop' => Shop::class,
                default => null,
            };

            if (!$modelClass) {
                return response("ID: $id", 200);
            }

            $record = $modelClass::find($id);

            return $record
                ? response($record->name)
                : response("ID: $id (not found)", 200);
        } catch (\Exception $e) {
            return response("ID: $id", 200);
        }
    }

    public function dispatchItem(Request $request)
    {
        return $this->orderService->dispatchItem($request);
    }

    public function dispatchBulk(Request $request)
    {
        return $this->orderService->dispatchBulk($request);
    }

     public function adminVerifyReturn(Request $request)
    {
        return $this->orderService->adminVerifyReturn($request);
    }

     public function shopVerifyReturn(Request $request)
    {
        return $this->orderService->shopVerifyReturn($request);
    }

     public function adminApproveReturn(Request $request)
    {
        return $this->orderService->adminApproveReturn($request);
    }

    public function adminVerifyReplace(Request $request)
    {
        return $this->orderService->adminVerifyReplace($request);
    }

    public function adminApproveReplace(Request $request)
    {
        return $this->orderService->adminApproveReplace($request);
    }

    public function getReplaceDetails(Request $request)
    {
        return $this->orderService->getReplaceDetails($request);
    }

    public function getAvailableForReplacement(Request $request)
    {
        return $this->orderService->getAvailableForReplacement($request);
    }

    public function getVerificationDetails(Request $request)
    {
        return $this->orderService->getVerificationDetails($request);
    }

    public function getVerificationStatus(Request $request)
    {
        return $this->orderService->getVerificationStatus($request);
    }
}
