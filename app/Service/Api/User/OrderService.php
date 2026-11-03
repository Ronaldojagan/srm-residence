<?php

namespace App\Service\Api\User;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\Status;
use App\Models\Address;
use App\Models\Application;
use App\Models\CancellationReason;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\OrderItem;
use App\Models\OrderItemStatusHistory;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Precheckout;
use App\Models\Product;
use App\Models\ProductAlert;
use App\Models\ShippingAddress;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopRating;
use App\Models\Slot;
use App\Models\StockHistory;
use App\Models\TempEntry;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Razorpay\Api\Api;

class OrderService
{

    public function generateOrderNumber()
    {
        $order_no = 600000;
        if (Order::get()->count() > 0) {
            $latestOrder = Order::withTrashed()->latest()->first();
            $order_no = intval(str_replace(['RA-', 'RAERR-'], '', $latestOrder->order_no)) + 1;
        }
        $generatedBid =  "RA-" . $order_no;
        $isExist = Order::where('order_no', $generatedBid)->first();
        if ($isExist) {
            $generatedBid = "RAERR-" . $order_no;
        }
        return $generatedBid;
    }

    public function getDefaultAddress($user_id)
    {
        return Address::where('user_id', $user_id)->orderByDesc('is_default')->first();
    }

    public function addToCart($request)
    {
        $user = auth()->user();

        try {
            $address = Address::where('user_id', $user->id)
                ->orderByDesc('is_default')
                ->first();

            if (!$address) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Please add a shipping address before adding items to cart.',
                ]);
            }

            $product = Product::approvedWithShop()
                ->with('shop')
                ->find($request['product_id']);

            if (!$product) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Product not found or unavailable.',
                ]);
            }

            if ($product->product_stock <= 0) {
                return response()->json([
                    'status' => 0,
                    'message' => 'This product is currently out of stock.',
                ]);
            }

            $requestedQuantity = (int) $request['quantity'];

            $cart = Cart::firstOrNew([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);

            if ($requestedQuantity === 0) {
                if ($cart->exists) {
                    $cart->delete();
                    return response()->json([
                        'status' => 1,
                        'message' => 'Item removed from cart successfully.',
                    ]);
                }
                return response()->json([
                    'status' => 0,
                    'message' => 'Item not found in your cart.',
                ]);
            }

            $totalQuantity = $cart->exists ? $cart->quantity + $requestedQuantity : $requestedQuantity;

            if ($totalQuantity > $product->product_stock) {
                $message = $cart->exists
                    ? 'Cannot add more items. You already have ' . $cart->quantity .
                    ' in cart, and only ' . $product->product_stock . ' items available.'
                    : 'Insufficient stock. Only ' . $product->product_stock . ' items available.';

                return response()->json([
                    'status' => 0,
                    'message' => $message,
                ]);
            }

            $cart->quantity = $totalQuantity;
            $cart->save();

            return response()->json([
                'status' => 1,
                'message' => $cart->wasRecentlyCreated ? 'Product added to cart successfully.' : 'Cart updated successfully.',
            ]);

        } catch (\Exception $e) {
            \Log::error('Add to cart error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'status' => 0,
                'message' => 'Failed to add item to cart. Please try again.',
            ]);
        }
    }

    public function cartShow()
    {
        $user = auth()->user();
        $finalData = null;
        $status = "";
        $msg = "";
        $freeDeliveryAmount = (int)Application::where('name', 'free_delivery_amount_above')->value('value') ?? 0;
        try {
            $cart = Cart::where('user_id', $user->id)->get();
            if ($cart->isNotEmpty()) {
                $cartDeletedMsg = null;
                $productIds = $cart->pluck('product_id');
                $products = Product::approvedWithShop()->whereIn('id', $productIds)->get()->keyBy('id');
                foreach ($cart as $item) {
                    $product = $products->get($item->product_id);
                    if (!$product || $product->product_stock == 0) {
                        $cartDeletedMsg = "An item in your cart was invalid and has been removed!";
                        $item->delete();
                    }
                }
                if ($cartDeletedMsg) {
                    $cart = Cart::where('user_id', $user->id)->get();
                }
                if ($cart->isNotEmpty()) {
                    $finalData = self::getCartData($cart);
                    $address = $this->getDefaultAddress($user->id);
                    $finalData['address'] = $address;
                    $finalData['free_delivery_amount'] = $freeDeliveryAmount;
                    $status = 1;
                    $msg = "Cart Details";
                } else {
                    return [
                        'status' => 0,
                        'msg' => 'Cart is Empty' . ($cartDeletedMsg ? ", " . $cartDeletedMsg : ""),
                        'data' => null
                    ];
                }
            } else {
                return ['status' => 0, 'msg' => 'Cart is Empty', 'data' => null];
            }
        } catch (\Exception $e) {
            $status = 0;
            $msg = $e->getMessage();
            $finalData = null;
        }
        return ['status' => $status, 'msg' => $msg, 'data' => $finalData];
    }

    public function getCartData($cart)
    {
        $total = 0;
        $overAllTotal = 0;
        $finalData = ['cart_item' => []];

        foreach ($cart as $key => $item) {
            $product = Product::approvedWithShop()->find($item->product_id);

            $invalid = false;

            if (!$product
                || !$product->category
                || $product->category->status != Status::ACTIVE
                || !$product->shop
                || $product->shop->status != Status::ACTIVE
                || $item->quantity > $product->product_stock
            ) {
                $invalid = true;
            }

            $unitPrice = $product && $product->discount_amount ? $product->discount_amount : ($product->amount ?? 0);
            $itemTotal = $item->quantity * $unitPrice;
            $itemOverAllTotal = $product ? $item->quantity * $product->amount : 0;

            $finalData['cart_item'][$key] = [
                'cart_id' => $item->id,
                'product_id' => $item->product_id,
                'shop_id' => $item->product->shop_id,
                'quantity' => $item->quantity,
                'total_stock' => (string) ($product->product_stock ?? 0),
                'available_stock' => $product->available_stock,
                'price' => $product->amount ?? "0",
                'discount_amount' => $product->discount_amount ?? "0",
                'discount_percentage' => $product->discount_percentage ?? "0",
                'name' => $product->name ?? "Product not available",
                'image' => $product->image_url ?? null,
                'description' => $product->description ?? null,
                'item_total_amount' => number_format($itemTotal, 2),
                'invalid' => $invalid,
            ];

            if (!$invalid) {
                $total += $itemTotal;
                $overAllTotal += $itemOverAllTotal;
            }
        }

        $finalData['total'] = str_replace(",", "", number_format($total, 2));
        $finalData['overAllTotal'] = str_replace(",", "", number_format($overAllTotal, 2));

        $isFirstOrder = !Order::where('user_id', auth()->id())->exists();
        $settings = Application::whereIn('name', [
                'free_delivery_amount_above',
                'delivery_charge',
                'platform_fee'
            ])->pluck('value', 'name');

            $freeDeliveryLimit = (int)($settings['free_delivery_amount_above'] ?? 0);
            $finalData['deliveryCharge'] = (int)($settings['delivery_charge'] ?? 0);
            $finalData['deliveryChargeShow'] = (int)($settings['delivery_charge'] ?? 0);
            $finalData['platformFee'] = (int)($settings['platform_fee'] ?? 0);

            $finalData['isFreeDelivery'] = false;

            if ($isFirstOrder || $finalData['total'] >= $freeDeliveryLimit) {
                $finalData['isFreeDelivery'] = true;
            }

        return $finalData;
    }

    public function preCheckout($request)
    {
        $user = auth()->user();

        try {
            $cart = Cart::where('user_id', $user->id)->get();

            if ($cart->isEmpty()) {
                return ['status' => 0, 'msg' => 'Your cart is empty', 'data' => null];
            }

            $isFirstOrder = !Order::where('user_id', $user->id)->exists();
            $invalidProductExists = false;

            $products = Product::approvedWithShop()
                ->with(['shop', 'category'])
                ->whereIn('id', $cart->pluck('product_id'))
                ->where('status', Status::ACTIVE)
                ->get()
                ->keyBy('id');

            foreach ($cart as $item) {
                $product = $products[$item->product_id] ?? null;

                if (
                    !$product ||
                    !$product->shop || $product->shop->status != Status::ACTIVE ||
                    !$product->category || $product->category->status != Status::ACTIVE
                ) {
                    $item->delete();
                    $invalidProductExists = true;
                    continue;
                }

                if ($item->quantity > $product->product_stock) {
                    $item->update(['quantity' => $product->product_stock]);
                }
            }

            $cart = Cart::where('user_id', $user->id)->get();
            if ($cart->isEmpty()) {
                return ['status' => 0, 'msg' => 'No valid products left in cart', 'data' => null];
            }

            $shippingAddress = Address::find($request['address_id'] ?? 0);
            $addressFill = false;
            $newAddress = null;

            if ($shippingAddress) {
                $required = ['flat_no', 'city', 'district', 'pincode', 'name', 'phone'];
                $addressFill = collect($required)->every(fn($f) => !empty($shippingAddress->$f))
                    && preg_match('/^\d{6}$/', $shippingAddress->pincode)
                    && preg_match('/^\d{10}$/', $shippingAddress->phone);

                if ($addressFill) {
                    $newAddress = ShippingAddress::updateOrCreate(
                        $shippingAddress->only([
                            'flat_no','area_name','street','city','district',
                            'pincode','address_type','is_default','lat','long','name','phone'
                        ]),
                        []
                    );
                }
            }

            $cartData = $this->getCartData($cart);
            $subTotal = $cartData['total'];
            $overAllTotal = $cartData['overAllTotal'];
            $productDiscount = $overAllTotal - $subTotal;

            $settings = Application::whereIn('name', [
                'free_delivery_amount_above',
                'delivery_charge',
                'platform_fee'
            ])->pluck('value', 'name');

            $freeDeliveryLimit = (int)($settings['free_delivery_amount_above'] ?? 0);
            $deliveryCharge = (int)($settings['delivery_charge'] ?? 0);
            $deliveryChargeShow = (int)($settings['delivery_charge'] ?? 0);
            $platformFee = (int)($settings['platform_fee'] ?? 0);

            $isFreeDelivery = false;

            if ($isFirstOrder || $subTotal >= $freeDeliveryLimit) {
                $deliveryCharge = 0;
                $isFreeDelivery = true;
            }

            $couponAmount = 0;
            $couponId = null;
            $couponCode = null;
            $couponError = null;

            $myPreCheckout = Precheckout::where([
                'user_id' => $user->id,
                'is_order_placed' => 0
            ])->first();

            if (isset($request['coupon_remove']) && $request['coupon_remove'] == true) {
                Precheckout::where([
                    'user_id' => $user->id,
                    'is_order_placed' => 0,
                ])->update([
                    'coupon_amount' => 0,
                    'coupon_id' => null,
                ]);

                if ($myPreCheckout) {
                    $myPreCheckout->coupon_amount = 0;
                    $myPreCheckout->coupon_id = null;
                    $myPreCheckout->save();
                }
            }
            else if (!empty($request->coupon_code) || ($myPreCheckout && $myPreCheckout->coupon_id)) {
                if (!empty($request->coupon_code)) {
                    $couponCode = trim($request->coupon_code);
                } else if ($myPreCheckout && $myPreCheckout->coupon_id) {
                    $existingCoupon = Coupon::find($myPreCheckout->coupon_id);
                    $couponCode = $existingCoupon ? $existingCoupon->code : null;
                }

                if ($couponCode) {
                    $coupon = Coupon::where('code', $couponCode)
                        ->where('status', 1)
                        ->first();

                    if ($coupon) {
                        $validation = $this->validateCoupon($coupon, $user, $subTotal);

                        if ($validation['valid']) {
                            $used = Order::where('user_id', $user->id)
                                ->where('coupon_id', $coupon->id)
                                ->exists();

                            if (!$used) {
                                $couponAmount = $coupon->amount;
                                $couponId = $coupon->id;

                                if ($myPreCheckout && $myPreCheckout->coupon_id != $couponId) {
                                    Precheckout::where([
                                        'user_id' => $user->id,
                                        'is_order_placed' => 0,
                                    ])->update([
                                        'coupon_amount' => 0,
                                        'coupon_id' => null,
                                    ]);
                                }
                            } else {
                                $couponError = 'This coupon has already been used by you.';

                                if ($myPreCheckout && $myPreCheckout->coupon_id == $coupon->id) {
                                    Precheckout::where([
                                        'user_id' => $user->id,
                                        'is_order_placed' => 0,
                                    ])->update([
                                        'coupon_amount' => 0,
                                        'coupon_id' => null,
                                    ]);
                                }
                            }
                        } else {
                            $couponError = $validation['message'];

                            if ($myPreCheckout && $myPreCheckout->coupon_id == $coupon->id) {
                                Precheckout::where([
                                    'user_id' => $user->id,
                                    'is_order_placed' => 0,
                                ])->update([
                                    'coupon_amount' => 0,
                                    'coupon_id' => null,
                                ]);
                            }
                        }
                    } else {
                        $couponError = 'Invalid coupon code.';

                        if ($myPreCheckout && $myPreCheckout->coupon_id) {
                            Precheckout::where([
                                'user_id' => $user->id,
                                'is_order_placed' => 0,
                            ])->update([
                                'coupon_amount' => 0,
                                'coupon_id' => null,
                            ]);
                        }
                    }
                }
            }

            $grandTotal = ($subTotal + $deliveryCharge + $platformFee) - $couponAmount;

            DB::beginTransaction();

            $precheckout = Precheckout::updateOrCreate(
                ['user_id' => $user->id, 'is_order_placed' => 0],
                [
                    'shipping_address_id' => $newAddress->id ?? null,
                    'original_sub_total' => $overAllTotal,
                    'sub_total' => $subTotal,
                    'delivery_charge' => $deliveryCharge,
                    'original_delivery_amount' => $deliveryChargeShow,
                    'discount_amount' => $productDiscount,
                    'platform_fee' => $platformFee,
                    'coupon_amount' => $couponAmount,
                    'coupon_id' => $couponId,
                    'platform_fee' => $platformFee,
                    'grand_total' => $grandTotal,
                    'added_carts' => $cartData['cart_item'],
                ]
            );

            DB::commit();
            $freeDeliveryAmount = (int)Application::where('name', 'free_delivery_amount_above')->value('value') ?? 0;
            return [
                'status' => 1,
                'msg' => 'Pre-checkout ready',
                'checkout_enabled' => !$invalidProductExists,
                'data' => [
                    'free_delivery_amount' => $freeDeliveryAmount,
                    'checkout_id' => $precheckout->id,
                    'carts' => $precheckout->added_carts,
                    'address_fill' => $addressFill,
                    'total_items' => $cart->count(),
                    'user_id' => $user->id,
                    'is_order_placed' => 0,
                    'shipping_address_id' => $newAddress->id ?? null,
                    'original_sub_total' => (string)$overAllTotal,
                    'sub_total' => (string)$subTotal,
                    'delivery_charge' => (string)$deliveryChargeShow,
                    'save_amount' => (string)$productDiscount,
                    'coupon_amount' => (string)$couponAmount,
                    'platform_fee' => (string)$platformFee,
                    'grand_total' => (string)$grandTotal,
                    'is_free_delivery' => $isFreeDelivery,
                    'first_order' => $isFirstOrder
                ],
                'coupon_error' => $couponError
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Precheckout error: '.$e->getMessage());
            return ['status' => 0, 'msg' => 'Pre-checkout failed', 'data' => null];
        }
    }
    private function validateCoupon($coupon, $user, $subTotal)
    {
        if (!$coupon->status) {
            return ['valid' => false, 'message' => 'This coupon is no longer active.'];
        }

        if ($coupon->isExpired()) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
        }

        if ($coupon->min_purchase_amount && $subTotal < $coupon->min_purchase_amount) {
            return ['valid' => false, 'message' => 'Minimum purchase amount of ' . $coupon->min_purchase_amount . ' required for this coupon.'];
        }

        if (!$coupon->canBeUsed()) {
            return ['valid' => false, 'message' => 'This coupon cannot be used at the moment.'];
        }

        return ['valid' => true, 'message' => ''];
    }

    public function addressDetail($address_id)
    {
        $address = Address::where('id', $address_id)->first();
        return $address;
    }

    public function setDefaultAddress($user_id, $address_id)
    {
        DB::beginTransaction();
        Address::where('user_id', $user_id)->update(['is_default' => 0]);
        Address::where('id', $address_id)->where('user_id', $user_id)->update(['is_default' => 1]);
        DB::commit();
        return $this->addressDetail($address_id);
    }

    public function orderCheckout($request)
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();

            $validated = $request->validate([
                'checkout_id' => 'required|exists:precheckouts,id',
                'payment_type' => 'required|in:Razorpay,Cash On Delivery,Wallet'
            ]);

            $preCheckout = Precheckout::where([
                'id' => $request->checkout_id,
                'user_id' => $user->id,
                'is_order_placed' => 0
            ])->first();

            if (!$preCheckout) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid checkout ID'
                ], 404);
            }

            if ($request->payment_type === 'Wallet') {
                $wallet = Wallet::getUserWallet($user->id);

                if (!$wallet->canAfford($preCheckout->grand_total)) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Insufficient wallet balance',
                        'data' => [
                            'wallet_balance' => (float) $wallet->balance,
                            'required_amount' => (float) $preCheckout->grand_total,
                            'short_by' => (float) ($preCheckout->grand_total - $wallet->balance)
                        ]
                    ], 400);
                }

                $wallet->deductFunds(
                    $preCheckout->grand_total,
                    'Payment for order',
                    [
                        'checkout_id' => $preCheckout->id,
                        'order_amount' => $preCheckout->grand_total
                    ],
                    [
                        'id' => $preCheckout->id,
                        'type' => 'Precheckout'
                    ]
                );

                $order = $this->createOrder($preCheckout, $user->id, [
                    'type' => 'Wallet',
                    'method' => 'Wallet',
                    'status' => 'Paid',
                    'amount' => $preCheckout->grand_total,
                ]);

                DB::commit();

                $this->sendOrderConfirmationNotifications($order, $user);

                return response()->json([
                    'status' => 1,
                    'message' => 'Order Placed Successfully using Wallet',
                    'data' => [
                        'order_id' => $order->id,
                        'order_no' => $order->order_no,
                        'amount' => (float) $order->total_amount,
                        'wallet_balance' => (float) $wallet->balance,
                        'payment_method' => 'Wallet',
                        'date'            => now()->format('d F Y \a\t h:i A'),
                        'invoice_no' => $order->invoice_no
                    ]
                ]);
            }

            if ($request->payment_type === 'Cash On Delivery') {
                $order = $this->createOrder($preCheckout, $user->id, [
                    'type' => 'Cash On Delivery',
                    'method' => 'Cash On Delivery',
                    'status' => 'Pending'
                ]);

                DB::commit();

                $this->sendOrderConfirmationNotifications($order, $user);

                return response()->json([
                    'status' => 1,
                    'message' => 'Order Placed Successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'order_no' => $order->order_no,
                        'amount' => (float) $order->total_amount,
                        'payment_method' => 'Cash On Delivery',
                        'date'            => now()->format('d F Y \a\t h:i A'),
                        'invoice_no' => $order->invoice_no
                    ]
                ]);
            }

            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET_KEY'));

            $rzOrder = $api->order->create([
                'amount' => $preCheckout->grand_total * 100,
                'currency' => 'INR',
                'payment_capture' => 1,
                'notes' => [
                    'checkout_id' => $preCheckout->id,
                    'user_id' => $user->id,
                    'type' => 'order_payment'
                ]
            ]);

            TempEntry::create([
                'order_id' => $rzOrder->id,
                'notes' => json_encode([
                    'checkout_id' => $preCheckout->id,
                    'user_id' => $user->id,
                    'amount' => $preCheckout->grand_total,
                    'payment_type' => 'Razorpay'
                ]),
                'type' => 'order_payment'
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Razorpay order created',
                'data' => [
                    'order_id' => $rzOrder->id,
                    'amount' => (float) $preCheckout->grand_total,
                    'key' => env('RAZORPAY_KEY'),
                    'currency' => 'INR',
                    'name' => env('APP_NAME'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order checkout error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            return response()->json([
                'status' => 0,
                'message' => 'Failed to process checkout. Please try again.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    protected function createOrder($preCheckout, $userId, $paymentData = [])
    {
        $order = $this->buildOrder($preCheckout, $userId);

        $this->createOrderItems($order, $preCheckout, $userId);

        $this->createShopOrders($order);

        if ($paymentData['type'] === 'Wallet') {
            $this->createWalletPayment($order, $userId, $paymentData, $preCheckout);
        } else {
            $this->createPaymentRecord($order, $userId, $paymentData);
        }

        $preCheckout->update(['is_order_placed' => 1]);

        $this->createOrderStatusHistory($order, $userId, $paymentData);

        $orderStatus = OrderStatus::PENDING;
        $itemStatus = OrderItemStatus::PENDING;

        $order->update(['status' => $orderStatus]);
        $this->updateAllOrderItemsStatus($order->id, $itemStatus->value,
            $paymentData['type'] === 'Cash On Delivery' ? 'Order placed (Cash On Delivery)' : 'Order confirmed and paid',
            $userId
        );

        return $order->fresh();
    }

    public function createRazorpayOrder($preCheckout, $userId, $paymentData = [])
    {
        $existingPayment = Payment::where('transaction_id', $paymentData['transaction_id'])->first();

        if ($existingPayment) {
            $existingOrder = Order::find($existingPayment->order_id);

            DB::commit();
            return $existingOrder;
        }
        $order = $this->buildOrder($preCheckout, $userId);

        $this->createOrderItems($order, $preCheckout, $userId);

        $this->createShopOrders($order);

        if ($paymentData['type'] === 'Wallet') {
            $this->createWalletPayment($order, $userId, $paymentData, $preCheckout);
        } else {
            $this->createPaymentRecord($order, $userId, $paymentData);
        }

        $preCheckout->update(['is_order_placed' => 1]);

        $this->createOrderStatusHistory($order, $userId, $paymentData);

        $orderStatus = OrderStatus::PENDING;
        $itemStatus = OrderItemStatus::PENDING;

        $order->update(['status' => $orderStatus]);
        $this->updateAllOrderItemsStatus($order->id, $itemStatus->value,
            $paymentData['type'] === 'Cash On Delivery' ? 'Order placed (Cash On Delivery)' : 'Order confirmed and paid',
            $userId
        );

        return $order->fresh();
    }

    protected function createPaymentRecord($order, $userId, $paymentData)
    {
        $paymentCount = Payment::count();
        $nextNumber = $paymentCount + 1;
        $paymentNo = 'PAY' . strtoupper(Str::random(10));

        return Payment::create([
            'payment_no' => $paymentNo,
            'transaction_id' => $paymentData['transaction_id'] ?? null,
            'amount' => $paymentData['amount'] ?? $order->total_amount,
            'type' => $paymentData['type'] ?? 'COD',
            'payment_method' => $paymentData['method'] ?? 'Cash On Delivery',
            'date' => now(),
            'user_id' => $userId,
            'order_id' => $order->id,
            'status' => $paymentData['status'] ?? ($paymentData['type'] === 'Cash On Delivery' ? 'Pending' : 'Paid'),
            'merchant_transaction_id' => $paymentData['merchant_txn'] ?? null,
            'response' => $paymentData['response'] ?? null,
            'wallet_used' => false,
            'wallet_related' => false
        ]);
    }

    protected function createWalletPayment($order, $userId, $paymentData, $preCheckout)
    {
        $wallet = Wallet::getUserWallet($userId);
        $paymentCount = Payment::count();
        $nextNumber = $paymentCount + 1;
        $paymentNo = 'PAY' . strtoupper(Str::random(10));

        return Payment::create([
            'payment_no' => $paymentNo,
            'transaction_id' => 'WALLET-' . time() . '-' . $userId,
            'amount' => $paymentData['amount'] ?? $order->total_amount,
            'type' => 'Wallet',
            'payment_method' => 'Wallet',
            'date' => now(),
            'user_id' => $userId,
            'order_id' => $order->id,
            'status' => 'Paid',
            'merchant_transaction_id' => null,
            'response' => json_encode([
                'wallet_id' => $wallet->id,
                'wallet_balance_before' => (float) ($wallet->balance + $order->total_amount),
                'wallet_balance_after' => (float) $wallet->balance,
                'order_amount' => (float) $order->total_amount,
                'checkout_id' => $preCheckout->id
            ]),
            'wallet_used' => true,
            'wallet_id' => $wallet->id,
            'wallet_related' => true
        ]);
    }

    protected function buildOrder($preCheckout, $userId)
    {
        $latestOrder = Order::latest('id')->value('id');
        $nextOrderId = ($latestOrder ?? 0) + 1;

        $amount = floatval(str_replace(',', '', $preCheckout->grand_total));

        return Order::create([
            'user_id' => $userId,
            'coupon_id' => $preCheckout->coupon_id,
            'precheckout_id' => $preCheckout->id,
            'shipping_address_id' => $preCheckout->shipping_address_id,
            'order_type' => $preCheckout->type ?? 'Normal',
            'delivery_slot' => $preCheckout->delivery_range,
            'order_no' => 'ORD' . str_pad($nextOrderId, 6, '0', STR_PAD_LEFT),
            'total_amount' => $amount,
            'status' => OrderStatus::PENDING,
            'date' => now(),
            'invoice_no' => 'INV' . strtoupper(Str::random(5)) . time(),
            'has_shipments' => false,
            'all_items_delivered' => false,
        ]);
    }

    protected function createOrderItems($order, $preCheckout, $userId)
    {
        foreach ($preCheckout->added_carts as $cart) {
            $product = Product::with('shop')->approvedWithShop()->find($cart['product_id']);

            if (!$product) {
                throw new \Exception("Product not found in cart: " . $cart['product_id']);
            }

            $shop = $product->shop;

            $itemAmount = $product->discount_amount > 0 ? $product->discount_amount : $product->amount;
            $itemTotal = $itemAmount * $cart['quantity'];

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'shop_id' => $shop->id,
                'product_name' => $product->name,
                'product_image' => $product->image,
                'description' => $product->description,
                'amount' => $itemAmount,
                'quantity' => $cart['quantity'],
                'quantity_unit' => $cart['quantity_unit'] ?? 'pcs',
                'total_amount' => $itemTotal,
                'status' => OrderItemStatus::PENDING->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->handleStockUpdate(
                $product,
                $cart['quantity'],
                $userId,
                $shop->user_id
            );

            Cart::where('id', $cart['cart_id'])->delete();
        }
    }

    protected function createShopOrders($order)
    {
        $itemsByShop = OrderItem::where('order_id', $order->id)
            ->with('shop')
            ->get()
            ->groupBy('shop_id');

        foreach ($itemsByShop as $shopId => $items) {
            $shop = Shop::find($shopId);
            if (!$shop) {
                continue;
            }

            $totalAmount = 0;
            $totalAdminCommission = 0;
            $totalShopCommission = 0;

            $commissionPerc = $shop->commission_percentage ?? 0;

            foreach ($items as $item) {
                $totalAmount += $item->total_amount;

                $adminComm = ($item->total_amount * $commissionPerc) / 100;
                $shopComm = $item->total_amount - $adminComm;

                $totalAdminCommission += $adminComm;
                $totalShopCommission += $shopComm;
            }

            ShopOrder::create([
                'shop_order_no' => 'SORD' . time() . rand(1000, 9999),
                'order_id' => $order->id,
                'shop_id' => $shop->id,
                'user_id' => $shop->user_id,
                'total_amount' => $totalAmount,
                'total_admin_commission' => $totalAdminCommission,
                'total_shop_commission' => $totalShopCommission,
                'commission_percentage' => $commissionPerc,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function createOrderStatusHistory($order, $userId, $paymentData = [])
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => OrderStatus::PENDING,
            'notes' => 'Order placed by user',
            'created_by' => $userId,
            'created_at' => now(),
        ]);
    }

    protected function handleStockUpdate($product, $qty, $userId, $shopOwnerId)
    {
        $remainingStock = $product->product_stock;

        $newStock = $remainingStock - $qty;

        if ($remainingStock <= $product->max_product_stock_alert) {
            $alertType = ($remainingStock <= ($product->max_product_stock_alert * 0.3))
                ? 'critical_stock'
                : 'low_stock';

            $alertMessage = $alertType === 'critical_stock'
                ? "CRITICAL: {$product->name} very low ({$remainingStock} remaining)"
                : "Low stock: {$product->name} ({$remainingStock} remaining)";

            ProductAlert::create([
                'product_id' => $product->id,
                'type' => $alertType,
                'message' => $alertMessage,
                'triggered_by' => $userId,
                'user_id' => $shopOwnerId,
                'created_at' => now(),
            ]);

            Notification::create([
                'user_id' => $shopOwnerId,
                'type' => 'low_stock_alert',
                'title' => $alertType === 'critical_stock'
                    ? 'Critical Stock Alert!'
                    : 'Low Stock Alert',
                'message' => $alertMessage,
                'data' => json_encode([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'current_stock' => $newStock,
                    'alert_type' => $alertType,
                    'threshold' => $product->max_product_stock_alert,
                    'shop_id' => $product->shop_id ?? null,
                    'triggered_by_user' => $userId
                ]),
                'read_at' => null,
                'created_at' => now(),
            ]);
        }

        $product->product_stock -= $qty;
        $product->save();

        $this->logStockHistory($product, $qty, $userId, 'Sale');
    }

    protected function logStockHistory($product, $qty, $userId, $type = 'Sale')
    {
        StockHistory::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $qty,
            'previous_stock' => $product->getOriginal('product_stock'),
            'new_stock' => $product->product_stock,
            'total_stock' => $product->product_stock,
            'created_at' => now(),
        ]);
    }

    protected function updateAllOrderItemsStatus($orderId, $status, $notes = null, $userId = null)
    {
        $orderItems = OrderItem::where('order_id', $orderId)->get();
        $userId = $userId ?? auth()->id();

        foreach ($orderItems as $item) {
            OrderItemStatusHistory::updateStatus(
                $item,
                $status,
                $notes,
                [
                    'order_id' => $orderId,
                    'status_change_type' => 'bulk_update',
                    'updated_by' => $userId,
                ],
                $userId
            );
        }
    }

    protected function sendOrderConfirmationNotifications($order, $user)
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'order_confirmation',
                'title' => 'Order Confirmed',
                'message' => "Your order #{$order->order_no} has been confirmed successfully.",
                'data' => json_encode([
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'amount' => $order->total_amount
                ]),
                'read_at' => null,
                'created_at' => now(),
            ]);

            $shops = $order->items()->with('shop.user')->get()->pluck('shop')->unique();

            foreach ($shops as $shop) {
                if ($shop->user) {
                    Notification::create([
                        'user_id' => $shop->user->id,
                        'type' => 'new_order',
                        'title' => 'New Order Received',
                        'message' => "New order #{$order->order_no} received for your shop.",
                        'data' => json_encode([
                            'order_id' => $order->id,
                            'order_no' => $order->order_no,
                            'shop_id' => $shop->id
                        ]),
                        'read_at' => null,
                        'created_at' => now(),
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
        }
    }

    public function allOrder($request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Authenticated user not found. Please log in and try again.',
            ], 401);
        }

        $query = Order::where('user_id', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");

                try {
                    $date = Carbon::createFromFormat('d-m-Y', $search)->format('Y-m-d');
                    $q->orWhereDate('created_at', $date);
                } catch (\Exception $e1) {
                    try {
                        $date = Carbon::createFromFormat('d M Y', $search)->format('Y-m-d');
                        $q->orWhereDate('created_at', $date);
                    } catch (\Exception $e2) {
                    }
                }
            });
        }

        if ($request->filled('filter.order_no')) {
            $query->where('order_no', 'like', '%' . $request->filter['order_no'] . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'date' => $order->created_at->format('d M Y h:i A'),
                'total_items' => $order->orderItems()->count(),
                'amount' => $order->total_amount,
                'status' => $order->status,
                'item_images' => $order->orderItems->map(function ($item) {
                    return $item->product->image_url;
                })->filter()->take(1)->values(),
            ];
        });

        return response()->json([
            'status' => 1,
            'message' => $orders->isEmpty()
                ? 'No orders found for the given search or filter.'
                : 'Orders retrieved successfully.',
            'data' => $orders,
        ], 200);
    }

    public function orderDetails($id)
    {
        $orderId = $id;

        $order = Order::with([
                'user',
                'orderItems.product',
                'orderItems.latestStatus',
                'coupon',
                'payment',
                'shippingAddress',
                'precheckout',
            ])
            ->where('id', $orderId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 0,
                'message' => 'The requested order could not be found. Please verify the order ID and try again.'
            ]);
        }

        $isComplete = $order->status === OrderStatus::COMPLETED;

        $bill = $order->precheckout;

        $billDetails = $bill ? [
            'sub_total'         => $bill->sub_total ?? 0,
            'delivery_charge'   => $bill->delivery_charge ?? 0,
            'save_amount'   => $bill->discount_amount ?? 0,
            'coupon_amount'     => $bill->coupon_amount ?? 0,
            'platform_fee'      => $bill->platform_fee ?? 0,
            'grand_total'       => $bill->grand_total ?? 0,
        ] : [];

        $paymentMethod = optional($order->payment)->payment_method;
        $paymentType = optional($order->payment)->type;

        $shippingAddress = $order->shippingAddress;
        $formattedAddress = null;

        if ($shippingAddress) {
            $formattedAddress = [
                'name' => $shippingAddress->name ?? $order->user->name ?? null,
                'phone' => $shippingAddress->phone ?? null,
                'address_type' => $shippingAddress->address_type ?? null,
                'address' => $shippingAddress->address ?? null,
                'landmark' => $shippingAddress->landmark ?? null,
                'city' => $shippingAddress->city ?? null,
                'state' => $shippingAddress->state ?? null,
                'country' => $shippingAddress->country ?? null,
                'pincode' => $shippingAddress->pincode ?? null,
                'full_address' => $shippingAddress->full_address ?? null,
            ];
        }

        $currentDate = now();

        $orderDetails = [
            'order_id'         => $order->id,
            'order_no'         => $order->order_no,
            'date'             => $order->created_at->format('d-m-Y h:i A'),
            'invoice_no'       => $order->invoice_no ?? null,
            'payment_method'   => $paymentMethod ?? null,
            'payment_type'     => $paymentType ? strtoupper($paymentType) : null,
            'total_amount'     => $order->total_amount,
            'user_address'     => $formattedAddress,
            'status'           => $order->status,
            'user_name'        => optional($order->user)->name,
            'payment_mode'     => $paymentMethod == 'Razorpay' ? 'Online' : 'COD',
            'coupon_code'      => optional($order->coupon)->code ?? null,
            'bill_details'     => $billDetails,
            'invoice_button_show' => $isComplete,
            'invoice_download_url' => url('invoice/' . $order->id),
            'delivery_date'    => $order->delivery_completed_at?->format('d M Y, h:i A') ?? null,
            'items' => $order->orderItems->map(function ($item) use ($currentDate, $order) {
                $deliveryDate = null;
                $deliveredStatus = $item->statusHistories()
                    ->where('status', OrderItemStatus::DELIVERED->value)
                    ->latest()
                    ->first();

                if ($deliveredStatus) {
                    $deliveryDate = $deliveredStatus->created_at;
                }

                $canReturnOrReplace = false;
                $returnDaysLeft = null;

                $canReturn = false;
                $canReplace = false;
                $returnDaysLeft = 0;
                $replaceDaysLeft = 0;

                $settings = Application::where('group', 'Return Replace Days')->pluck('value', 'name');

                if ($deliveryDate) {
                    $currentDate = now();

                    $returnDeadline = $deliveryDate->copy()->addDays((int) $settings['max_return_days']);
                    $canReturn = $currentDate->lte($returnDeadline);
                    $returnDaysLeft = $canReturn
                        ? max(0, $currentDate->diffInDays($returnDeadline, false))
                        : 0;

                    $replaceDeadline = $deliveryDate->copy()->addDays((int) $settings['max_replace_days']);
                    $canReplace = $currentDate->lte($replaceDeadline);
                    $replaceDaysLeft = $canReplace
                        ? max(0, $currentDate->diffInDays($replaceDeadline, false))
                        : 0;
                }

                $isReturned = $item->status === OrderItemStatus::RETURNED->value;
                $isReturnRequested = $item->status === OrderItemStatus::RETURN_REQUESTED->value;
                $isRefunded = $item->status === OrderItemStatus::REFUNDED->value;

                $feedback = Feedback::where('order_id', $order->id)
                    ->where('product_id', $item->product_id)
                    ->first();

                return [
                    'item_id'        => $item->id,
                    'product_id'        => $item->product_id,
                    'product_name'      => $item->product_name,
                    'quantity'          => $item->quantity,
                    'unit'              => $item->quantity_unit,
                    'amount'            => $item->amount,
                    'total_amount'      => $item->total_amount,
                    'image_url'         => $item->image_url,
                    'rating'         => $feedback ? number_format($feedback->rating, 1) : null,
                    'review'         => $feedback ? $feedback->review : null,
                    'status'            => $item->status,
                    'status_description'=> $item->status_description,
                    'can_rate'       => !$feedback,
                    'can_cancel'        => $item->can_cancel,
                    'can_return'        => $item->can_return,
                    'can_replace'        => $item->can_replace,
                    'delivery_date'     => $deliveryDate ? $deliveryDate->format('d M Y, h:i A') : null,
                    'delivery_timestamp'=> $deliveryDate ? $deliveryDate->timestamp : null,
                    'can_return_replace' => $canReturnOrReplace &&
                                        $item->status === OrderItemStatus::DELIVERED->value &&
                                        !$isReturned && !$isReturnRequested && !$isRefunded,
                    'can_return' => $canReturn,
                    'return_days_left' => (int)$returnDaysLeft,
                    'can_replace' => $canReplace,
                    'replace_days_left' => (int)$replaceDaysLeft,
                    'return_requested'  => $isReturnRequested,
                    'returned'          => $isReturned,
                    'refunded'          => $isRefunded,
                    'return_reason'     => $item->return_reason ?? null,
                    'return_requested_at' => $item->return_requested_at?->format('d M Y, h:i A'),
                    'returned_at'       => $item->returned_at?->format('d M Y, h:i A'),
                    'refunded_at'       => $item->refunded_at?->format('d M Y, h:i A'),
                    'refund_amount'     => $item->refund_amount,
                    'formatted_refund_amount' => $item->formatted_refund_amount,
                ];
            })->toArray(),
        ];

        return response()->json([
            'status' => 1,
            'data'   => $orderDetails
        ]);
    }

    public function rating(array $data, $id)
    {
        $user = auth()->user();

        $product = Product::withTrashed()->find($data['product_id']);
        if (!$product) {
            return response()->json([
                'status' => 0,
                'message' => 'The specified product could not be found.',
            ], 404);
        }

        $feedback = new Feedback();
        $data['order_id'] = $id;
        $data['user_id'] = $user->id;
        $data['status'] = 'Active';
        $feedback->fill($data);
        $feedback->save();

        return response()->json([
            'status' => 1,
            'message' => "Your rating for the product '{$product->name}' has been submitted successfully.",
        ]);
    }

    public function shopRating(array $data, $id)
    {
        $user = auth()->user();

        $shop = Shop::withTrashed()->find($data['shop_id']);
        if (!$shop) {
            return response()->json([
                'status' => 0,
                'message' => 'Shop missing',
            ], 404);
        }

        $feedback = ShopRating::where('order_id', $id)
            ->where('user_id', $user->id)
            ->where('shop_id', $data['shop_id'])
            ->first();

        if ($feedback) {
            $feedback->update($data);
            $message = 'Rating updated';
        } else {
            $data['order_id'] = $id;
            $data['user_id'] = $user->id;
            $data['status'] = 'Active';
            ShopRating::create($data);
            $message = 'Rating added';
        }

        return response()->json([
            'status' => 1,
            'message' => $message,
        ]);
    }

    public function cancelProduct($request, Order $order, OrderItem $orderItem)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $orderItem = OrderItem::where('id', $orderItem->id)
                ->lockForUpdate()
                ->first();

            if (!$orderItem->can_cancel) {
                return response()->json([
                    'status' => 0,
                    'message' => 'This product cannot be cancelled at this stage'
                ]);
            }

            $paymentMethod = optional($order->payment)->payment_method;
            $refundAmount  = 0;
            $walletUpdated = false;

            $isWalletRefund = in_array($paymentMethod, ['Wallet', 'Razorpay']);

            /** ---------------- Wallet Refund ---------------- */
            if ($isWalletRefund) {
                $refundAmount = $orderItem->total_amount;

                $wallet = Wallet::getUserWallet($order->user_id);
                $wallet->addFunds(
                    $refundAmount,
                    'credit',
                    'Refund for cancelled product - Order #' . $order->order_no,
                    [
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'product_name' => $orderItem->product_name,
                        'cancellation_reason' => $request->cancellation_reason,
                    ],
                    [
                        'id' => $order->id,
                        'type' => 'Order'
                    ]
                );

                // Update payment refund history
                if ($order->payment) {
                    $refunds = json_decode($order->payment->partial_refunds ?? '[]', true);

                    $refunds[] = [
                        'item_id'        => $orderItem->id,
                        'refund_id'      => 'WALLET_REFUND_' . uniqid(),
                        'amount'         => $refundAmount,
                        'reason'         => $request->cancellation_reason,
                        'refunded_at'    => now(),
                        'status'         => 'Completed',
                        'refund_method'  => 'Wallet Credit',
                    ];

                    $order->payment->update([
                        'partial_refunds' => json_encode($refunds),
                        'total_refunded'  => ($order->payment->total_refunded ?? 0) + $refundAmount
                    ]);
                }

                $walletUpdated = true;
            }

            /** ---------------- Update Order Item ---------------- */
            $orderItem->update([
                'status'              => OrderItemStatus::CANCELLED,
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at'        => now(),
                'cancelled_by'        => auth()->id(),
                'refund_amount'       => $refundAmount,
                'refunded_at'         => $walletUpdated ? now() : null,
            ]);

            /** ---------------- Status History ---------------- */
            OrderItemStatusHistory::create([
                'order_id'      => $order->id,
                'order_item_id' => $orderItem->id,
                'status'        => OrderItemStatus::CANCELLED,
                'notes'         => $request->cancellation_reason,
                'metadata'      => [
                    'refund_amount' => $refundAmount,
                    'refund_method' => $walletUpdated ? 'Wallet Credit' : 'None',
                    'payment_method'=> $paymentMethod
                ],
                'created_by' => auth()->id()
            ]);

            /** ---------------- Restock Product ---------------- */
            if ($product = $orderItem->product) {
                $previousStock = $product->product_stock;

                $product->increment('product_stock', $orderItem->quantity);

                StockHistory::create([
                    'product_id'     => $product->id,
                    'user_id'        => auth()->id(),
                    'type'           => 'Restock',
                    'quantity'       => $orderItem->quantity,
                    'previous_stock' => $previousStock,
                    'new_stock'      => $previousStock + $orderItem->quantity,
                    'total_stock'    => $previousStock + $orderItem->quantity,
                    'reason'         => 'Product cancellation - Order #' . $order->order_no
                ]);
            }

            /** ---------------- Update Order Status ---------------- */
            $activeItemsCount = $order->orderItems()
                ->whereNotIn('status', [
                    OrderItemStatus::CANCELLED->value,
                    OrderItemStatus::REFUNDED->value
                ])->count();

            if ($activeItemsCount === 0) {
                $order->update([
                    'status' => OrderStatus::CANCELLED,
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'All items cancelled',
                ]);
            }

            DB::commit();

            /** ---------------- Notification ---------------- */
            Notification::createNotification(
                $order->user_id,
                Notification::TYPE_ORDER_STATUS_UPDATE,
                'Product Cancelled',
                "Your product '{$orderItem->product_name}' has been cancelled." .
                ($walletUpdated
                    ? " Refund of ₹" . number_format($refundAmount, 2) . " credited to wallet."
                    : " No refund applicable."),
                [
                    'order_id' => $order->id,
                    'item_id'  => $orderItem->id,
                    'refund_amount' => $refundAmount,
                    'payment_method' => $paymentMethod,
                ]
            );

            return response()->json([
                'status' => 1,
                'message' => 'Product cancelled successfully',
                'data' => [
                    'item_id' => $orderItem->id,
                    'refund_amount' => $refundAmount,
                    'payment_method' => $paymentMethod,
                    'wallet_refunded' => $walletUpdated,
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => 'Failed to cancel product',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function requestReturn($request, Order $order, OrderItem $orderItem)
    {
        $request->validate([
            'return_reason' => 'required|string|max:500',
            'return_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            DB::beginTransaction();

            if (!$orderItem->can_return) {
                return response()->json([
                    'status' => 0,
                    'message' => 'This product cannot be returned'
                ]);
            }

            if ($orderItem->status->value !== OrderItemStatus::DELIVERED->value) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Only delivered products can be returned'
                ]);
            }

            $deliveredAt = $orderItem->statusHistories()
                ->where('status', OrderItemStatus::DELIVERED->value)
                ->latest()
                ->first();

            if (!$deliveredAt) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Delivery information not found'
                ]);
            }

            $returnDeadline = $deliveredAt->created_at->addDays(7);
            if (now()->gt($returnDeadline)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Return period (7 days) has expired'
                ]);
            }

            $imagePath = null;
            if ($request->hasFile('return_image')) {
                $imagePath = $request->file('return_image')->store('returns', 'public');
            }

            $orderItem->update([
                'status' => OrderItemStatus::RETURN_REQUESTED,
                'return_reason' => $request->return_reason,
                'return_image' => $imagePath,
                'return_requested_at' => now(),
            ]);

            OrderItemStatusHistory::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'status' => OrderItemStatus::RETURN_REQUESTED,
                'notes' => $request->return_reason,
                'metadata' => [
                    'return_image' => $imagePath,
                    'return_deadline' => $returnDeadline->format('Y-m-d H:i:s'),
                ],
                'created_by' => auth()->id()
            ]);

            $this->updateOrderForReturn($order);

            DB::commit();

            $this->sendReturnRequestNotification($order, $orderItem);

            return response()->json([
                'status' => 1,
                'message' => 'Return request submitted successfully',
                'data' => [
                    'item_id' => $orderItem->id,
                    'return_requested_at' => $orderItem->return_requested_at->format('Y-m-d H:i:s'),
                    'return_deadline' => $returnDeadline->format('Y-m-d H:i:s'),
                    'return_image_url' => $orderItem->return_image_url,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => 'Failed to submit return request. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    protected function sendReturnRequestNotification($order, $orderItem)
    {
        Notification::createNotification(
            auth()->id(),
            Notification::TYPE_ORDER_STATUS_UPDATE,
            'New Return Request',
            "Product: {$orderItem->product_name} - Order #{$order->order_no}",
            [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'item_id' => $orderItem->id,
                'return_reason' => $orderItem->return_reason,
            ]
        );
    }

    protected function updateOrderForReturn($order)
    {
        $returnItemsCount = $order->orderItems()
            ->where('status', OrderItemStatus::RETURN_REQUESTED->value)
            ->count();

        $totalItemsCount = $order->orderItems()->count();

        if ($returnItemsCount === $totalItemsCount) {
            $order->update([
                'status' => 'return_requested',
            ]);
        }
    }

    public function requestReplace($request, Order $order, OrderItem $orderItem)
    {
        $request->validate([
            'replace_reason' => 'required|string|max:500',
            'replace_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            if (!$orderItem->can_return) {
                return response()->json([
                    'status' => 0,
                    'message' => 'This product cannot be replaced'
                ]);
            }

            if ($orderItem->status->value !== OrderItemStatus::DELIVERED->value) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Only delivered products can be replaced'
                ]);
            }

            $deliveredAt = $orderItem->statusHistories()
                ->where('status', OrderItemStatus::DELIVERED->value)
                ->latest()
                ->first();

            if (!$deliveredAt) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Delivery information not found'
                ]);
            }

            $replaceDeadline = $deliveredAt->created_at->addDays(7);
            if (now()->gt($replaceDeadline)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Replace period (7 days) has expired'
                ]);
            }

            $imagePath = null;
            if ($request->hasFile('replace_image')) {
                $imagePath = $request->file('replace_image')->store('replaces', 'public');
            }

            $orderItem->update([
                'status' => OrderItemStatus::REPLACE_REQUESTED,
                'replace_reason' => $request->replace_reason,
                'replace_image' => $imagePath,
                'replace_requested_at' => now(),
            ]);

            OrderItemStatusHistory::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'status' => OrderItemStatus::REPLACE_REQUESTED,
                'notes' => $request->replace_reason,
                'metadata' => [
                    'replace_image' => $imagePath,
                    'description' => $request->description,
                    'replace_deadline' => $replaceDeadline->format('Y-m-d H:i:s'),
                ],
                'created_by' => auth()->id()
            ]);

            DB::commit();

            $this->sendReplaceRequestNotification($order, $orderItem);

            return response()->json([
                'status' => 1,
                'message' => 'Replace request submitted successfully',
                'data' => [
                    'item_id' => $orderItem->id,
                    'replace_requested_at' => $orderItem->replace_requested_at->format('Y-m-d H:i:s'),
                    'replace_deadline' => $replaceDeadline->format('Y-m-d H:i:s'),
                    'replace_image_url' => $this->getReplaceImageUrl($orderItem),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => 'Failed to submit replace request. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    protected function getReplaceImageUrl($orderItem)
    {
        return $orderItem->replace_image ? url('storage/replaces/' . $orderItem->replace_image) : null;
    }

    protected function sendReplaceRequestNotification($order, $orderItem)
    {
        Notification::createNotification(
            auth()->id(),
            Notification::TYPE_ORDER_STATUS_UPDATE,
            'New Replace Request',
            "Product: {$orderItem->product_name} - Order #{$order->order_no}",
            [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'item_id' => $orderItem->id,
                'replace_reason' => $orderItem->replace_reason,
            ]
        );
    }

}
