<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Carbon\Carbon;
use App\Models\TempEntry;
use App\Models\Precheckout;
use App\Models\Payment;
use App\Models\User;
use App\Models\Notification;
use App\Models\Wallet;
use App\Service\Api\User\OrderService;

class RazorpayController extends Controller
{
    public function paymentSuccess(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tempEntry = TempEntry::where('order_id', $request->razorpay_order_id)->first();

            if (!$tempEntry) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Order not found'
                ], 404);
            }

            $notes = json_decode($tempEntry->notes, true);
            Log::info('Order Payment Success Data:', array_merge($request->all(), $notes));

            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET_KEY'));

            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
            ];

            $payment = $api->payment->fetch($request->razorpay_payment_id);

            if ($payment->status !== 'captured') {
                return response()->json([
                    'status' => 0,
                    'message' => 'Payment not captured'
                ], 400);
            }

            $existingPayment = Payment::where('transaction_id', $payment->id)->first();
            if ($existingPayment) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Payment already processed'
                ], 409);
            }

            $preCheckout = Precheckout::where([
                'id' => $notes['checkout_id'],
                'user_id' => $notes['user_id'],
                'is_order_placed' => 0
            ])->first();

            if (!$preCheckout) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid checkout session'
                ], 404);
            }

            $user = User::find($notes['user_id']);
            if (!$user) {
                return response()->json([
                    'status' => 0,
                    'message' => 'User not found'
                ], 404);
            }

            $commonController = new OrderService();

            $order = $commonController->createRazorpayOrder($preCheckout, $user->id, [
                'type' => 'Razorpay',
                'method' => 'Razorpay',
                'status' => 'Paid',
                'transaction_id' => $request->razorpay_payment_id,
                'amount' => $preCheckout->grand_total,
                'merchant_txn' => $request->razorpay_order_id,
                'response' => json_encode(array_merge($attributes, $payment->toArray())),
            ]);

            $tempEntry->update([
                'payment_status' => 'Paid',
                'status' => 1,
                'processed_at' => Carbon::now(),
            ]);

            DB::commit();

            $this->sendOrderConfirmationNotifications($order, $user);

            $paymentRecord = Payment::where('order_id', $order->id)->first();

            return response()->json([
                'status' => 1,
                'message' => 'Payment successful and order placed',
                'data' => [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'invoice_no' => $order->invoice_no,
                    'total_amount' => (float) $order->total_amount,
                    'transaction_id' => $payment->id,
                    'payment_no' => $paymentRecord->payment_no ?? '',
                    'date' => now()->format('d F Y \a\t h:i A'),
                    'payment_method' => 'Razorpay',
                    'payment_status' => 'Paid',
                    'items_count' => $order->items()->count(),
                ]
            ]);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            DB::rollBack();
            Log::error('Payment Signature Error: ' . $e->getMessage());

            return response()->json([
                'status' => 0,
                'message' => 'Payment signature verification failed'
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order Payment Success Error: ' . $e->getMessage());

            return response()->json([
                'status' => 0,
                'message' => 'Failed to process payment',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    private function sendOrderConfirmationNotifications($order, $user)
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'order_confirmation',
                'title' => 'Order Confirmed',
                'message' => "Your order #{$order->order_no} has been confirmed successfully.",
                'data' => json_encode(['order_id' => $order->id]),
                'read_at' => null,
                'created_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
        }
    }

    public function addMoney(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:10|max:50000'
        ]);

        $user = auth()->user();
        $amount = (float) $data['amount'];
        try {

            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET_KEY'));

            $rzOrder = $api->order->create([
                'amount' => (int) ($amount * 100),
                'currency' => 'INR',
                'payment_capture' => 1,
                'notes' => [
                    'user_id' => $user->id,
                    'type' => 'wallet_topup'
                ]
            ]);

            TempEntry::create([
                'order_id' => $rzOrder->id,
                'type' => 'wallet_topup',
                'notes' => json_encode([ 'user_id' => $user->id, 'amount' => $amount, 'type' => 'wallet_topup', 'wallet_topup' => true ]),
            ]);

            return response()->json([
                'status' => 1,
                'message' => 'Order created',
                'data' => [
                    'order_id' => $rzOrder->id,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'key' => env('RAZORPAY_KEY'),
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Wallet Topup Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Unable to create payment order'
            ], 500);
        }
    }

    public function walletPaymentSuccess(Request $request)
    {
        $data = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
        ]);

        $tempEntry = TempEntry::where('order_id', $data['razorpay_order_id'])
            ->where('type', 'wallet_topup')
            ->first();

        if (!$tempEntry) {
            return response()->json([
                'status' => 0,
                'message' => 'Order not found'
            ], 404);
        }

        if ($tempEntry->status == 1) {
            return response()->json([
                'status' => 1,
                'message' => 'Wallet already credited'
            ], 200);
        }

        if (Payment::where('transaction_id', $data['razorpay_payment_id'])
            ->where('wallet_related', true)
            ->exists()) {
            return response()->json([
                'status' => 1,
                'message' => 'Payment already processed'
            ], 409);
        }

            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET_KEY'));

            $payment = $api->payment->fetch($data['razorpay_payment_id']);

            if ($payment->status !== 'captured') {
                return response()->json([
                    'status' => 0,
                    'message' => 'Payment not captured'
                ], 400);
            }

            DB::beginTransaction();

            $notes = json_decode($tempEntry->notes, true);
            $user   = User::findOrFail($notes['user_id']);
            $amount = (float) $notes['amount'];

            $wallet = Wallet::getUserWallet($user->id);

            $wallet->addFunds(
                $amount,
                'credit',
                'Wallet topup via Razorpay',
                [
                    'razorpay_payment_id' => $payment->id,
                    'razorpay_order_id' => $data['razorpay_order_id'],
                    'method' => $payment->method
                ],
                [
                    'id' => $payment->id,
                    'type' => 'Payment'
                ]
            );

            $wallet->increment('balance', $amount);

            $paymentNo = 'WALLET-' . now()->timestamp . Str::random(4);

            Payment::create([
                'payment_no' => $paymentNo,
                'transaction_id' => $payment->id,
                'amount' => $amount,
                'type' => 'Wallet Topup',
                'payment_method' => 'Razorpay',
                'date' => now(),
                'user_id' => $user->id,
                'status' => 'Paid',
                'merchant_transaction_id' => $data['razorpay_order_id'],
                'response' => $payment->toArray(),
                'wallet_related' => true,
                'wallet_id' => $wallet->id,
                'wallet_used' => false
            ]);

            $tempEntry->update([
                'payment_status' => 'Paid',
                'status' => 1,
                'processed_at' => now(),
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'wallet_topup',
                'title' => 'Wallet Recharged',
                'message' => "Your wallet has been recharged with ₹" . number_format($amount, 2),
                'data' => [
                    'amount' => $amount,
                    'new_balance' => $wallet->balance,
                    'transaction_id' => $payment->id
                ],
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Wallet topup successful',
                'data' => [
                    'amount_added' => $amount,
                    'new_balance' => (float) $wallet->balance,
                    'transaction_id' => $payment->id,
                    'payment_no' => $paymentNo,
                    'date' => now()->format('d F Y \a\t h:i A'),
                    'formatted_balance' => '₹' . number_format($wallet->balance, 2)
                ]
            ]);
        try {

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Wallet Topup Error', [
                'order_id' => $data['razorpay_order_id'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Failed to process wallet topup'
            ], 500);
        }
    }

}
