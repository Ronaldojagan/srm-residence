<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Service\Api\User\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SaveOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\Api\User\SaveRatingRequest;
use App\Http\Requests\Api\User\SaveCartRequest;
use App\Http\Requests\Api\User\SaveShopRatingRequest;
use App\Http\Requests\OrderCheckoutRequest;
use App\Models\Cart;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit');
        $search = $request->input('search');
        $orderBy = $request->input('orderBy');
        $orderDir = $request->input('orderDir');
        $status = $request->input('status', "Active");
        $user_id = $request->input('user_id', null);
        $order = Order::search($search)
            ->when(!empty($user_id), function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            })
            ->where('status', $status)
            ->orderBy($orderBy ? $orderBy : 'id', $orderDir ? $orderDir : 'desc')
            ->paginate(($limit ? $limit : 10));
            $order->getCollection()->transform(function ($item) {
                if ($item->order_status === OrderStatus::ORDERED) {
                    $item->order_status = OrderStatus::PENDING;
                }
                return $item;
            });

        return response()->json(['status' => 1, 'data' => $order, 'message' => 'Order List'], 200);
    }

    public function view(Order $order)
    {
        $data = $this->orderService->orderDetail($order->id);
        return response()->json(['status' => 1, 'data' => $data, 'message' => 'Order Details'], 200);
    }

    public function store(SaveOrderRequest $request)
    {
        $data = $this->orderService->store($request->validated());
        return response()->json(['status' => 1, 'data' => $data, 'message' => 'Order Created'], 200);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $data = $this->orderService->update($request->validated(), $order->id);
        return response()->json(['status' => 1, 'data' => $data, 'message' => 'Order Updated'], 200);
    }

    public function destroy(Order $order)
    {
        if ($order->delete()) {
            return response()->json(['status' => 1, 'message' => 'Record Deleted'], 200);
        }
    }

    public function addToCart(SaveCartRequest $request)
    {
        return $this->orderService->addToCart($request->validated());
    }

    public function preCheckout(Request $request)
    {
        return $this->orderService->preCheckout($request);
    }

    public function orderCheckout(Request $request)
    {
        return $this->orderService->orderCheckout($request);
    }

    public function cartShow()
    {
        return $this->orderService->cartShow();
    }

    public function cartDelete($id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(['status' => 0, 'message' => 'Cart item not found'], 200);
        }

        if ($cart->delete()) {
            return response()->json(['status' => 1, 'message' => 'Record deleted successfully'], 200);
        }

        return response()->json(['status' => 0, 'message' => 'Failed to delete cart item'], 200);
    }

    public function allOrder(Request $request)
    {
        return $this->orderService->allOrder($request);
    }

    public function orderDetails($id)
    {
        return $this->orderService->orderDetails($id);
    }

    public function rating(SaveRatingRequest $request, $id)
    {
        return $this->orderService->rating($request->validated(), $id);
    }

    public function shopRating(SaveShopRatingRequest $request, $id)
    {
        return $this->orderService->shopRating($request->validated(), $id);
    }

    public function invoice($id)
    {
        try {
            $order = Order::with([
                'orderItems.product.shop',
                'shippingAddress',
                'precheckout',
                'payment',
                'originalOrder',
                'user'
            ])->findOrFail($id);

            if ($order->is_replacement_order && !$order->invoice_no) {
                $order->refresh();
            }

            $company = [
                'name' => config('app.name', 'SRM RESIDENCE'),
                'address' => 'Tirunelveli, Tamil Nadu',
                'phone' => '+91 9876543210',
                'email' => 'support@srmresidence.com',
                'gstin' => 'GSTIN1234567890',
                'website' => 'www.srmresidence.com',
            ];

            $seller = null;
            foreach ($order->orderItems as $item) {
                if ($item->product && $item->product->shop) {
                    $seller = $item->product->shop;
                    break;
                }
            }

            if (!$seller) {
                $seller = (object) [
                    'name' => $company['name'],
                    'address' => $company['address'],
                    'phone' => $company['phone'],
                    'email' => $company['email'],
                    'gst_number' => $company['gstin'] ?? null,
                    'pan_number' => null,
                ];
            }

            $totals = [
                'subtotal' => $order->sub_total ?? $order->orderItems->sum('total_amount'),
                'discount' => $order->precheckout->coupon_amount ?? 0,
                'delivery' => $order->precheckout->delivery_charge ?? 0,
                'platform_fee' => $order->precheckout->platform_fee ?? 0,
                'tax' => 0,
                'grand_total' => $order->total_amount,
            ];

            $pdf = Pdf::loadView('backend.invoice', compact(
                'order',
                'company',
                'seller',
                'totals'
            ));

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('margin-top', 10);
            $pdf->setOption('margin-bottom', 10);
            $pdf->setOption('margin-left', 10);
            $pdf->setOption('margin-right', 10);

            return $pdf->download('invoice-' . $order->invoice_no . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Invoice generation error: ' . $e->getMessage());

            return response()->json([
                'status' => 0,
                'message' => 'Failed to generate invoice',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function shopInvoice($id)
    {
        try {
            $authShop = auth()->user()->shop;

            if (!$authShop) {
                return response()->json([
                    'status' => 0,
                    'message' => 'You are not associated with any shop'
                ], 403);
            }

            $order = Order::with([
                'orderItems.product' => function($query) use ($authShop) {
                    $query->where('shop_id', $authShop->id);
                },
                'orderItems.product.category',
                'orderItems.product.shop',
                'shippingAddress',
                'payment',
                'user'
            ])
            ->whereHas('orderItems.product', function($query) use ($authShop) {
                $query->where('shop_id', $authShop->id);
            })
            ->findOrFail($id);

            $shopOrderItems = $order->orderItems->filter(function ($item) use ($authShop) {
                return $item->product && $item->product->shop_id == $authShop->id;
            });

            if ($shopOrderItems->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No items in this order belong to your shop'
                ], 403);
            }

            $shop = $authShop;

            $shopDetails = [
                'id' => $shop->id,
                'name' => $shop->name ?? config('app.name', 'SRM RESIDENCE'),
                'business_name' => $shop->business_name ?? $shop->name,
                'address' => $shop->address ?? 'Tirunelveli, Tamil Nadu',
                'city' => $shop->city ?? 'Tirunelveli',
                'state' => $shop->state ?? 'Tamil Nadu',
                'pincode' => $shop->pincode ?? '627001',
                'phone' => $shop->owner_phone ?? null,
                'email' => $shop->email ?? 'N/A',
                'gst_number' => $shop->user->gst_no ?? null,
                'pan_number' => $shop->user->pan_no ?? 'ABCDE1234F',
                'logo' => $shop->logo ?? null,
                'website' => $shop->website ?? 'www.srmresidence.com',
            ];

            $company = [
                'name' => config('app.name', 'SRM RESIDENCE'),
                'address' => 'Tirunelveli, Tamil Nadu',
                'phone' => '+91 9876543210',
                'email' => 'support@srmresidence.com',
                'gstin' => 'GSTIN1234567890',
                'website' => 'www.srmresidence.com',
            ];

            $shopSubtotal = $shopOrderItems->sum('total_amount');

            $totalOrderSubtotal = $order->sub_total ?? $order->orderItems->sum('total_amount');
            $shopDiscount = $totalOrderSubtotal > 0 ?
                ($shopSubtotal / $totalOrderSubtotal) * ($order->precheckout->coupon_amount ?? 0) : 0;

            $shopDelivery = $totalOrderSubtotal > 0 ?
                ($shopSubtotal / $totalOrderSubtotal) * ($order->precheckout->delivery_charge ?? 0) : 0;

            $shopPlatformFee = $totalOrderSubtotal > 0 ?
                ($shopSubtotal / $totalOrderSubtotal) * ($order->precheckout->platform_fee ?? 0) : 0;

            $shopTax = 0;

            $shopTotals = [
                'subtotal' => $shopSubtotal,
                'discount' => round($shopDiscount, 2),
                'delivery' => round($shopDelivery, 2),
                'platform_fee' => round($shopPlatformFee, 2),
                'tax' => round($shopTax, 2),
                'grand_total' => round($shopSubtotal - $shopDiscount + $shopDelivery + $shopPlatformFee + $shopTax, 2),
            ];

            $commissionPercentage = 10;
            $commissionAmount = round(($shopTotals['grand_total'] * $commissionPercentage) / 100, 2);
            $netPayableToShop = $shopTotals['grand_total'] - $commissionAmount;

            $data = [
                'order' => $order,
                'shop' => (object) $shopDetails,
                'company' => $company,
                'shopOrderItems' => $shopOrderItems,
                'shopTotals' => $shopTotals,
                'commission' => [
                    'percentage' => $commissionPercentage,
                    'amount' => $commissionAmount,
                    'net_payable' => $netPayableToShop,
                ],
                'isMultiShopOrder' => $order->orderItems->count() > $shopOrderItems->count(),
                'totalOrderItems' => $order->orderItems->count(),
                'shopItemsCount' => $shopOrderItems->count(),
            ];

            $pdf = Pdf::loadView('backend.shop_invoice', $data);

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('margin-top', 10);
            $pdf->setOption('margin-bottom', 10);
            $pdf->setOption('margin-left', 10);
            $pdf->setOption('margin-right', 10);

            return $pdf->download('invoice-' . $order->invoice_no . '.pdf');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Order not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to generate invoice',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        return $this->orderService->cancel($request, $id);
    }

    public function cancelProduct(Request $request, Order $order, OrderItem $orderItem)
    {
        return $this->orderService->cancelProduct($request, $order, $orderItem);
    }

    public function requestReturn(Request $request, Order $order, OrderItem $orderItem)
    {
        return $this->orderService->requestReturn($request, $order, $orderItem);
    }

    public function requestReplace(Request $request, Order $order, OrderItem $orderItem)
    {
        return $this->orderService->requestReplace($request, $order, $orderItem);
    }


}
