<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\TempEntry;
use App\Models\Precheckout;
use App\Models\Payment;
use App\Models\User;
use App\Models\Notification;
use App\Models\Wallet;
use App\Service\Api\User\OrderService;

class CashfreeWebhookController extends Controller
{
    protected $appId;
    protected $secretKey;

    public function __construct()
    {
        $this->appId = env('CASHFREE_APP_ID');
        $this->secretKey = env('CASHFREE_SECRET_KEY');
    }

    public function handleWebhook(Request $request)
    {
        Log::info('Cashfree Webhook Received:', [
            'headers' => $request->headers->all(),
            'payload' => $request->all()
        ]);

        // Verify webhook signature
        $webhookSignature = $request->header('X-Webhook-Signature');
        $payload = $request->getContent();

        if (!$this->verifyWebhookSignature($payload, $webhookSignature)) {
            Log::error('Cashfree Webhook Signature Verification Failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $data = $request->input('data', []);

        Log::info('Processing Cashfree Webhook Event: ' . $event);

        switch ($event) {
            case 'ORDER.PAYMENT.SUCCESS':
                return $this->handlePaymentSuccess($data);

            case 'ORDER.PAYMENT.FAILED':
                return $this->handlePaymentFailed($data);

            case 'ORDER.PAYMENT.PENDING':
                return $this->handlePaymentPending($data);

            case 'ORDER.REFUNDED':
                return $this->handleOrderRefunded($data);

            default:
                Log::info('Unhandled Cashfree webhook event: ' . $event);
                return response()->json(['status' => 'ignored'], 200);
        }
    }

    private function verifyWebhookSignature($payload, $signature)
    {
        try {
            // Calculate expected signature
            $timestamp = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] ?? '';
            $expectedSignature = hash_hmac('sha256', $timestamp . $payload, $this->secretKey);

            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            Log::error('Webhook signature verification error: ' . $e->getMessage());
            return false;
        }
    }

    private function handlePaymentSuccess($data)
    {
        $orderId = $data['order_id'] ?? null;
        $paymentId = $data['payment_id'] ?? null;

        if (!$orderId || !$paymentId) {
            Log::error('Invalid payment success payload', $data);
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        DB::beginTransaction();

        try {
            $tempEntry = TempEntry::where('order_id', $orderId)->first();

            if (!$tempEntry) {
                Log::error('Temp entry not found for order: ' . $orderId);
                return response()->json(['error' => 'Order not found'], 404);
            }

            if ($tempEntry->status == 1) {
                Log::info('Payment already processed for order: ' . $orderId);
                return response()->json(['status' => 'already_processed'], 200);
            }

            $notes = json_decode($tempEntry->notes, true);
            $type = $notes['type'] ?? null;

            // Get payment details from Cashfree
            $paymentDetails = $this->getPaymentDetails($orderId);

            if (!$paymentDetails || $paymentDetails['status'] !== 'paid') {
                throw new \Exception('Payment verification failed');
            }

            switch ($type) {
                case 'wallet_topup':
                    $this->processWalletTopupWebhook($tempEntry, $paymentDetails);
                    break;

                case 'order_payment':
                    $this->processOrderPaymentWebhook($tempEntry, $paymentDetails);
                    break;

                default:
                    Log::error('Unknown payment type: ' . $type);
                    return response()->json(['error' => 'Unknown payment type'], 400);
            }

            DB::commit();
            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment success webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function processWalletTopupWebhook($tempEntry, $paymentDetails)
    {
        $notes = json_decode($tempEntry->notes, true);
        $userId = $notes['user_id'] ?? null;
        $amount = $notes['amount'] ?? $paymentDetails['amount'];

        if (!$userId) {
            throw new \Exception('Missing user_id in notes');
        }

        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }

        // Check if payment already exists
        if (Payment::where('transaction_id', $paymentDetails['payment_id'])->exists()) {
            Log::info('Payment already processed: ' . $paymentDetails['payment_id']);
            return;
        }

        $wallet = Wallet::getUserWallet($userId);

        // Generate payment number
        $paymentNo = 'WALLET-' . now()->format('YmdHis') . Str::random(4);

        // Create payment record
        $paymentRecord = Payment::create([
            'payment_no' => $paymentNo,
            'transaction_id' => $paymentDetails['payment_id'],
            'amount' => $amount,
            'type' => 'Wallet Topup',
            'payment_method' => 'Cashfree',
            'date' => now(),
            'user_id' => $userId,
            'status' => 'Paid',
            'merchant_transaction_id' => $tempEntry->order_id,
            'response' => json_encode($paymentDetails['raw_response']),
            'wallet_related' => true,
            'wallet_id' => $wallet->id,
            'wallet_used' => false
        ]);

        // Add funds to wallet
        $wallet->addFunds(
            $amount,
            'credit',
            'Wallet topup via Cashfree (Webhook)',
            [
                'cashfree_payment_id' => $paymentDetails['payment_id'],
                'cashfree_order_id' => $tempEntry->order_id,
                'method' => $paymentDetails['method'] ?? 'cashfree',
                'payment_id' => $paymentRecord->id
            ],
            [
                'id' => $paymentDetails['payment_id'],
                'type' => 'Payment'
            ]
        );

        // Update temp entry
        $tempEntry->update([
            'payment_status' => 'Paid',
            'status' => 1,
            'processed_at' => now(),
        ]);

        // Create notification
        Notification::create([
            'user_id' => $userId,
            'type' => 'wallet_topup',
            'title' => 'Wallet Recharged',
            'message' => "Your wallet has been recharged with ₹" . number_format($amount, 2),
            'data' => json_encode([
                'amount' => $amount,
                'new_balance' => $wallet->balance,
                'transaction_id' => $paymentDetails['payment_id'],
                'payment_no' => $paymentNo
            ]),
            'read_at' => null,
        ]);

        Log::info('Wallet topup processed via webhook', [
            'user_id' => $userId,
            'amount' => $amount,
            'payment_id' => $paymentDetails['payment_id'],
            'new_balance' => $wallet->balance
        ]);
    }

    private function processOrderPaymentWebhook($tempEntry, $paymentDetails)
    {
        $notes = json_decode($tempEntry->notes, true);
        $userId = $notes['user_id'] ?? null;
        $checkoutId = $notes['checkout_id'] ?? null;

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

        $orderService = new OrderService();

        $order = $orderService->createCashfreeOrder($preCheckout, $userId, [
            'type' => 'Cashfree',
            'method' => 'Cashfree',
            'status' => 'Paid',
            'transaction_id' => $paymentDetails['payment_id'],
            'amount' => $preCheckout->grand_total,
            'merchant_txn' => $tempEntry->order_id,
            'response' => json_encode($paymentDetails['raw_response']),
        ]);

        if (!$order) {
            throw new \Exception('Failed to create order');
        }

        // Update temp entry
        $tempEntry->update([
            'payment_status' => 'Paid',
            'status' => 1,
            'processed_at' => Carbon::now(),
        ]);

        // Send notification
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

        Log::info('Order created via webhook', [
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'payment_id' => $paymentDetails['payment_id']
        ]);
    }

    private function handlePaymentFailed($data)
    {
        $orderId = $data['order_id'] ?? null;
        $errorCode = $data['error_code'] ?? null;
        $errorDescription = $data['error_description'] ?? 'Payment failed';

        if ($orderId) {
            $tempEntry = TempEntry::where('order_id', $orderId)->first();

            if ($tempEntry) {
                $tempEntry->update([
                    'payment_status' => 'Failed',
                    'status' => 2, // Failed status
                    'processed_at' => now(),
                    'error_message' => $errorDescription
                ]);

                $notes = json_decode($tempEntry->notes, true);
                $userId = $notes['user_id'] ?? null;

                if ($userId) {
                    Notification::create([
                        'user_id' => $userId,
                        'type' => 'payment_failed',
                        'title' => 'Payment Failed',
                        'message' => 'Your payment failed. Please try again.',
                        'data' => json_encode([
                            'order_id' => $orderId,
                            'error' => $errorDescription
                        ]),
                        'read_at' => null,
                    ]);
                }
            }
        }

        Log::info('Payment failed processed', [
            'order_id' => $orderId,
            'error_code' => $errorCode,
            'error_description' => $errorDescription
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    private function handlePaymentPending($data)
    {
        $orderId = $data['order_id'] ?? null;

        if ($orderId) {
            $tempEntry = TempEntry::where('order_id', $orderId)->first();

            if ($tempEntry) {
                $tempEntry->update([
                    'payment_status' => 'Pending',
                    'status' => 0,
                ]);

                Log::info('Payment pending status updated', ['order_id' => $orderId]);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }

    private function handleOrderRefunded($data)
    {
        $orderId = $data['order_id'] ?? null;
        $refundId = $data['refund_id'] ?? null;
        $refundAmount = $data['refund_amount'] ?? 0;

        if ($orderId && $refundId) {
            $payment = Payment::where('merchant_transaction_id', $orderId)->first();

            if ($payment) {
                $payment->update([
                    'refund_amount' => $refundAmount / 100,
                    'refund_status' => 'processed',
                    'refund_id' => $refundId,
                    'refund_response' => json_encode($data)
                ]);

                // If it's a wallet payment, deduct from wallet
                if ($payment->wallet_related && $payment->wallet_id) {
                    $wallet = Wallet::find($payment->wallet_id);
                    if ($wallet) {
                        $refundAmountInr = $refundAmount / 100;
                        $wallet->deductFunds(
                            $refundAmountInr,
                            'Wallet refund for failed payment',
                            [
                                'refund_id' => $refundId,
                                'payment_id' => $payment->transaction_id,
                                'reason' => 'Refund processed'
                            ]
                        );

                        Notification::create([
                            'user_id' => $payment->user_id,
                            'type' => 'wallet_refund',
                            'title' => 'Refund Processed',
                            'message' => "₹" . number_format($refundAmountInr, 2) . " has been refunded to your wallet.",
                            'data' => json_encode([
                                'amount' => $refundAmountInr,
                                'new_balance' => $wallet->balance,
                                'refund_id' => $refundId
                            ]),
                            'read_at' => null,
                        ]);
                    }
                }
            }
        }

        Log::info('Refund processed', [
            'order_id' => $orderId,
            'refund_id' => $refundId,
            'refund_amount' => $refundAmount / 100
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    private function getPaymentDetails($orderId)
    {
        try {
            $headers = [
                "Content-Type: application/json",
                "x-api-version: 2022-09-01",
                "x-client-id: " . $this->appId,
                "x-client-secret: " . $this->secretKey
            ];

            $baseUrl = env('CASHFREE_ENV', 'sandbox') === 'production'
                ? 'https://api.cashfree.com/pg'
                : 'https://sandbox.cashfree.com/pg';

            $url = $baseUrl . '/orders/' . $orderId;

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode === 200) {
                $responseData = json_decode($response, true);

                if (isset($responseData['order_status']) && in_array($responseData['order_status'], ['PAID', 'ACTIVE'])) {
                    return [
                        'payment_id' => $responseData['cf_order_id'] ?? $orderId,
                        'order_id' => $orderId,
                        'amount' => $responseData['order_amount'],
                        'status' => strtolower($responseData['order_status']),
                        'method' => $responseData['payment_method'] ?? 'cashfree',
                        'currency' => $responseData['order_currency'] ?? 'INR',
                        'raw_response' => $responseData
                    ];
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Cashfree payment details error: ' . $e->getMessage());
            return null;
        }
    }
}
