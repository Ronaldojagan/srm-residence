<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Carbon\Carbon;
use App\Models\TempEntry;
use App\Models\Precheckout;
use App\Models\Payment;
use App\Models\User;
use App\Models\Notification;
use App\Models\Wallet;
use App\Models\Order;
use App\Service\Api\User\OrderService;

class RazorpayWebhookController extends Controller
{
    protected $api;
    protected $logChannel;

    public function __construct()
    {
        $this->api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET_KEY'));
        $this->logChannel = 'razorpay_payment';
    }

    public function handleWebhook(Request $request)
    {
        Log::channel($this->logChannel)->info('Razorpay Webhook Received:', [
            'headers' => $request->headers->all(),
            'payload' => $request->all()
        ]);

        $webhookBody = $request->all();
        $event = $webhookBody['event'] ?? null;

        Log::channel($this->logChannel)->info('Processing Razorpay Webhook Event: ' . $event);

        switch ($event) {
            case 'order.paid':
                return $this->handleOrderPaid($webhookBody);
            // case 'payment.captured':
            //     return $this->handlePaymentCaptured($webhookBody);
            // case 'payment.failed':
            //     return $this->handlePaymentFailed($webhookBody);
            default:
                Log::channel($this->logChannel)->warning('Unhandled webhook event: ' . $event);
                return response()->json(['status' => 'unhandled_event'], 200);
        }
    }

    private function handleOrderPaid($webhookBody)
    {
        DB::beginTransaction();

        try {
            // Extract payment and order from the webhook payload
            $payload = $webhookBody['payload']['payment']['entity'] ?? null;
            $orderId = $webhookBody['payload']['order']['entity']['id'] ?? null;
            $notes = $webhookBody['payload']['order']['entity']['notes'] ?? [];

            Log::channel($this->logChannel)->info('Processing order.paid webhook', [
                'order_id' => $orderId,
                'payment_id' => $payload['id'] ?? null,
                'notes' => $notes
            ]);

            if (!$payload || !$orderId) {
                Log::channel($this->logChannel)->error('Invalid order.paid payload', $webhookBody);
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            // Find temp entry using order_id
            $tempEntry = TempEntry::where('order_id', $orderId)->first();

            if (!$tempEntry) {
                Log::channel($this->logChannel)->error('Temp entry not found for order: ' . $orderId);
                return response()->json(['error' => 'Order not found'], 404);
            }

            if ($tempEntry->status == 1) {
                Log::channel($this->logChannel)->info('Payment already processed for order: ' . $orderId);
                return response()->json(['status' => 'already_processed'], 200);
            }

            $type = $notes['type'] ?? null;

            switch ($type) {
                case 'order_payment':
                    $this->processOrderPayment($tempEntry, $payload, $notes);
                    break;

                case 'wallet_topup':
                    $this->processWalletTopup($tempEntry, $payload, $notes);
                    break;

                default:
                    Log::channel($this->logChannel)->error('Unknown payment type in order.paid: ' . $type);
                    return response()->json(['error' => 'Unknown payment type'], 400);
            }

            DB::commit();
            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel($this->logChannel)->error('Error processing order.paid webhook: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'payload' => $webhookBody
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function handlePaymentCaptured($webhookBody)
    {
        DB::beginTransaction();

        try {
            $payload = $webhookBody['payload']['payment']['entity'] ?? null;
            $orderId = $payload['order_id'] ?? null;
            $notes = $payload['notes'] ?? [];

            Log::channel($this->logChannel)->info('Processing payment.captured webhook', [
                'order_id' => $orderId,
                'payment_id' => $payload['id'] ?? null,
                'notes' => $notes
            ]);

            if (!$payload || !$orderId) {
                Log::channel($this->logChannel)->error('Invalid payment.captured payload', $webhookBody);
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $tempEntry = TempEntry::where('order_id', $orderId)->first();

            if (!$tempEntry) {
                Log::channel($this->logChannel)->error('Temp entry not found for order: ' . $orderId);
                return response()->json(['error' => 'Order not found'], 404);
            }

            if ($tempEntry->status == 1) {
                Log::channel($this->logChannel)->info('Payment already processed for order: ' . $orderId);
                return response()->json(['status' => 'already_processed'], 200);
            }

            $type = $notes['type'] ?? null;

            switch ($type) {
                case 'order_payment':
                    $this->processOrderPayment($tempEntry, $payload, $notes);
                    break;

                case 'wallet_topup':
                    $this->processWalletTopup($tempEntry, $payload, $notes);
                    break;

                default:
                    Log::channel($this->logChannel)->error('Unknown payment type in payment.captured: ' . $type);
                    return response()->json(['error' => 'Unknown payment type'], 400);
            }

            DB::commit();
            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel($this->logChannel)->error('Error processing payment.captured webhook: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'payload' => $webhookBody
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function handlePaymentFailed($webhookBody)
    {
        try {
            $payload = $webhookBody['payload']['payment']['entity'] ?? null;
            $orderId = $payload['order_id'] ?? null;
            $notes = $payload['notes'] ?? [];

            Log::channel($this->logChannel)->warning('Payment failed webhook received', [
                'order_id' => $orderId,
                'payment_id' => $payload['id'] ?? null,
                'error' => $payload['error_description'] ?? 'Unknown error',
                'notes' => $notes
            ]);

            if ($orderId) {
                $tempEntry = TempEntry::where('order_id', $orderId)->first();
                if ($tempEntry) {
                    $tempEntry->update([
                        'payment_status' => 'Failed',
                        'status' => 2,
                        'processed_at' => Carbon::now(),
                        'error_response' => json_encode($payload)
                    ]);

                    // Send notification if user_id is in notes
                    if (isset($notes['user_id'])) {
                        Notification::create([
                            'user_id' => $notes['user_id'],
                            'type' => 'payment_failed',
                            'title' => 'Payment Failed',
                            'message' => 'Your payment has failed. Please try again.',
                            'data' => json_encode([
                                'order_id' => $orderId,
                                'error' => $payload['error_description'] ?? 'Payment failed'
                            ]),
                            'read_at' => null,
                        ]);
                    }
                }
            }

            return response()->json(['status' => 'failure_handled'], 200);

        } catch (\Exception $e) {
            Log::channel($this->logChannel)->error('Error processing payment.failed webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function processOrderPayment($tempEntry, $payment, $notes)
    {
        $checkoutId = $notes['checkout_id'] ?? null;
        $userId = $notes['user_id'] ?? null;

        Log::channel($this->logChannel)->info('Processing order payment', [
            'checkout_id' => $checkoutId,
            'user_id' => $userId,
            'payment_id' => $payment['id'] ?? null
        ]);

        if (!$checkoutId || !$userId) {
            throw new \Exception('Missing checkout_id or user_id in notes');
        }

        $preCheckout = Precheckout::where([
            'id' => $checkoutId,
            'user_id' => $userId,
            'is_order_placed' => 0
        ])->first();

        if (!$preCheckout) {
            throw new \Exception('Invalid checkout session');
        }

        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $orderService = new OrderService();

        $order = $orderService->createRazorpayOrder($preCheckout, $userId, [
            'type' => 'Razorpay',
            'method' => $payment['method'] ?? 'Razorpay',
            'status' => 'Paid',
            'transaction_id' => $payment['id'] ?? null,
            'amount' => $preCheckout->grand_total,
            'merchant_txn' => $tempEntry->order_id,
            'response' => json_encode($payment),
        ]);

        if (!$order) {
            throw new \Exception('Failed to create order');
        }

        $tempEntry->update([
            'payment_status' => 'Paid',
            'status' => 1,
            'processed_at' => Carbon::now(),
        ]);

        Notification::create([
            'user_id' => $userId,
            'type' => 'order_confirmation',
            'title' => 'Order Confirmed',
            'message' => "Your order #{$order->order_no} has been confirmed successfully.",
            'data' => json_encode([
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'total_amount' => $order->total_amount
            ]),
            'read_at' => null,
        ]);

        Log::channel($this->logChannel)->info('Order created via webhook', [
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'payment_id' => $payment['id'] ?? null,
            'user_id' => $userId,
            'amount' => $order->total_amount,
            'timestamp' => Carbon::now()->toISOString()
        ]);
    }

    private function processWalletTopup($tempEntry, $payment, $notes)
    {
        $userId = $notes['user_id'] ?? null;
        $amount = $notes['amount'] ?? 0;

        Log::channel($this->logChannel)->info('Processing wallet topup', [
            'user_id' => $userId,
            'amount' => $amount,
            'payment_id' => $payment['id'] ?? null
        ]);

        if (!$userId) {
            throw new \Exception('Missing user_id in notes');
        }

        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $wallet = Wallet::getUserWallet($userId);

        if (Payment::where('transaction_id', $payment['id'])->exists()) {
            Log::channel($this->logChannel)->info('Payment already processed: ' . $payment['id']);
            return;
        }

        $paymentNo = 'WALLET-' . now()->format('YmdHis') . Str::random(4);

        $paymentRecord = Payment::create([
            'payment_no' => $paymentNo,
            'transaction_id' => $payment['id'] ?? null,
            'amount' => $amount,
            'type' => 'Wallet Topup',
            'payment_method' => $payment['method'] ?? 'Razorpay',
            'date' => now(),
            'user_id' => $userId,
            'status' => 'Paid',
            'merchant_transaction_id' => $tempEntry->order_id,
            'response' => json_encode($payment),
            'wallet_related' => true,
            'wallet_id' => $wallet->id,
            'wallet_used' => false
        ]);

        $wallet->addFunds(
            $amount,
            'credit',
            'Wallet topup via Razorpay (Webhook)',
            [
                'razorpay_payment_id' => $payment['id'] ?? null,
                'razorpay_order_id' => $tempEntry->order_id,
                'method' => $payment['method'] ?? 'unknown',
                'payment_id' => $paymentRecord->id
            ],
            [
                'id' => $payment['id'] ?? null,
                'type' => 'Payment'
            ]
        );

        $tempEntry->update([
            'payment_status' => 'Paid',
            'status' => 1,
            'processed_at' => now(),
        ]);

        Notification::create([
            'user_id' => $userId,
            'type' => 'wallet_topup',
            'title' => 'Wallet Recharged',
            'message' => "Your wallet has been recharged with ₹" . number_format($amount, 2),
            'data' => json_encode([
                'amount' => $amount,
                'new_balance' => $wallet->balance,
                'transaction_id' => $payment['id'] ?? null,
                'payment_no' => $paymentNo
            ]),
            'read_at' => null,
        ]);

        Log::channel($this->logChannel)->info('Wallet topup processed via webhook', [
            'user_id' => $userId,
            'amount' => $amount,
            'payment_id' => $payment['id'] ?? null,
            'new_balance' => $wallet->balance,
            'payment_no' => $paymentNo,
            'timestamp' => now()->toISOString()
        ]);
    }
}
