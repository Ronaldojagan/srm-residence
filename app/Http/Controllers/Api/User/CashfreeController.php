<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\TempEntry;
use App\Models\Precheckout;
use App\Models\Payment;
use App\Models\User;
use App\Models\Notification;
use App\Models\Wallet;
use App\Service\Api\User\OrderService;

class CashfreeController extends Controller
{
    protected $appId;
    protected $secretKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->appId = env('CASHFREE_APP_ID');
        $this->secretKey = env('CASHFREE_SECRET_KEY');
        $env = env('CASHFREE_ENV', 'sandbox');

        $this->baseUrl = $env === 'production'
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
    }

    // Method to create wallet topup order
    public function createWalletTopup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10|max:50000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $amount = (float) $request->amount;

        try {
            // Generate unique order ID
            $orderId = 'WALLET_' . now()->timestamp . '_' . Str::random(8);

            // Create temp entry
            $tempEntry = TempEntry::create([
                'order_id' => $orderId,
                'type' => 'wallet_topup',
                'notes' => json_encode([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'type' => 'wallet_topup',
                    'gateway' => 'cashfree'
                ]),
                'status' => 0,
                'payment_status' => 'Pending'
            ]);

            // Initiate Cashfree payment
            $result = $this->initiateCashfreePayment($orderId, $amount, $user, 'Wallet Topup', 'wallet_topup');

            if (!$result['success']) {
                return response()->json([
                    'status' => 0,
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Payment order created',
                'data' => [
                    'order_id' => $orderId,
                    'payment_link' => $result['payment_link'],
                    'amount' => $amount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Cashfree Wallet Topup Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Unable to create payment order'
            ], 500);
        }
    }

    // Method to create order for product purchase
    public function createOrderPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'checkout_id' => 'required|exists:precheckouts,id',
            'amount' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $amount = (float) $request->amount;
        $checkoutId = $request->checkout_id;

        try {
            // Generate unique order ID
            $orderId = 'ORDER_' . now()->timestamp . '_' . Str::random(8);

            // Create temp entry
            $tempEntry = TempEntry::create([
                'order_id' => $orderId,
                'type' => 'order_payment',
                'notes' => json_encode([
                    'user_id' => $user->id,
                    'checkout_id' => $checkoutId,
                    'amount' => $amount,
                    'type' => 'order_payment',
                    'gateway' => 'cashfree'
                ]),
                'status' => 0,
                'payment_status' => 'Pending'
            ]);

            // Initiate Cashfree payment
            $result = $this->initiateCashfreePayment($orderId, $amount, $user, 'Order Payment', 'order_payment');

            if (!$result['success']) {
                return response()->json([
                    'status' => 0,
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $orderId,
                    'payment_link' => $result['payment_link'],
                    'amount' => $amount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Cashfree Order Payment Error', [
                'user_id' => $user->id,
                'checkout_id' => $checkoutId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Unable to create payment order'
            ], 500);
        }
    }

    // Common method to initiate Cashfree payment
    private function initiateCashfreePayment($orderId, $amount, $user, $orderNote, $paymentType)
    {
        try {
            $callbackUrl = url('/api/V1/payment/callback/cashfree');
            $notifyUrl = url('/api/payment/webhook/cashfree');

            $payload = [
                "order_id" => $orderId,
                "order_amount" => $amount,
                "order_currency" => "INR",
                "order_note" => $orderNote,
                "customer_details" => [
                    "customer_id" => "cust_" . $user->id,
                    "customer_name" => $user->name ?? 'Customer',
                    "customer_email" => $user->email ?? 'customer@example.com',
                    "customer_phone" => $user->phone ?? '9999999999'
                ],
                "order_meta" => [
                    "return_url" => $callbackUrl . '?order_id=' . $orderId . '&type=' . $paymentType,
                    "notify_url" => $notifyUrl
                ]
            ];

            $headers = [
                "Content-Type: application/json",
                "x-api-version: 2022-01-01",
                "x-client-id: " . $this->appId,
                "x-client-secret: " . $this->secretKey
            ];

            $url = $this->baseUrl . '/orders';

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                Log::error('CURL Error: ' . $error);
                return ['success' => 0, 'message' => 'Network error occurred'];
            }

            if ($httpCode === 200) {
                $responseData = json_decode($response, true);
                if (isset($responseData['payment_link'])) {
                    Log::info('Cashfree Payment Link Created', [
                        'order_id' => $orderId,
                        'payment_link' => $responseData['payment_link']
                    ]);

                    return [
                        'success' => 1,
                        'payment_link' => $responseData['payment_link']
                    ];
                }
            }

            Log::error('Cashfree API Error', [
                'http_code' => $httpCode,
                'response' => $response,
                'order_id' => $orderId,
                'amount' => $amount
            ]);

            return ['success' => 0, 'message' => 'Cashfree payment initiation failed'];

        } catch (\Exception $e) {
            Log::error('Cashfree initiation error: ' . $e->getMessage());
            return ['success' => 0, 'message' => 'Cashfree payment initiation failed'];
        }
    }

    public function paymentCallback(Request $request)
    {
        $orderId = $request->input('order_id');
        $type = $request->input('type', 'order_payment');

        Log::info('Cashfree Callback Received', [
            'order_id' => $orderId,
            'type' => $type,
            'all_params' => $request->all()
        ]);

        $redirectUrl = url('/payment/callback') . '?' . http_build_query([
            'order_id' => $orderId,
            'type'     => $type,
            'gateway'  => 'cashfree',
            'timestamp'=> now()->timestamp,
        ]);

        return redirect($redirectUrl);
    }

    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string',
            'type' => 'required|in:wallet_topup,order_payment'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $orderId = $request->order_id;
        $type = $request->type;

        try {
            // Verify with Cashfree
            $paymentDetails = $this->verifyCashfreePayment($orderId);

            if (!$paymentDetails || $paymentDetails['status'] !== 'paid') {
                return response()->json([
                    'status' => 0,
                    'message' => 'Payment verification failed or payment not completed'
                ], 400);
            }

            // Process payment based on type
            switch ($type) {
                case 'wallet_topup':
                    $result = $this->processWalletTopup($orderId, $paymentDetails);
                    break;

                case 'order_payment':
                    $result = $this->processOrderPayment($orderId, $paymentDetails);
                    break;

                default:
                    return response()->json([
                        'status' => 0,
                        'message' => 'Invalid payment type'
                    ], 400);
            }

            if ($result['status'] === 1) {
                return response()->json([
                    'status' => 1,
                    'message' => $result['message'],
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Verify payment error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Payment verification failed'
            ], 500);
        }
    }

    private function processWalletTopup($orderId, $paymentDetails)
    {
        DB::beginTransaction();

        try {
            $tempEntry = TempEntry::where('order_id', $orderId)
                ->where('type', 'wallet_topup')
                ->first();

            if (!$tempEntry) {
                return ['status' => 0, 'message' => 'Order not found'];
            }

            if ($tempEntry->status == 1) {
                $payment = Payment::where('merchant_transaction_id', $orderId)->first();
                return [
                    'status' => 1,
                    'message' => 'Payment already processed',
                    'data' => [
                        'payment_no' => $payment->payment_no ?? '',
                        'transaction_id' => $payment->transaction_id ?? '',
                        'amount' => $payment->amount ?? 0
                    ]
                ];
            }

            $notes = json_decode($tempEntry->notes, true);
            $userId = $notes['user_id'] ?? null;
            $amount = $notes['amount'] ?? $paymentDetails['amount'];

            if (!$userId) {
                return ['status' => 0, 'message' => 'User not found in order'];
            }

            $user = User::find($userId);
            if (!$user) {
                return ['status' => 0, 'message' => 'User not found'];
            }

            // Check for duplicate payment
            if (Payment::where('transaction_id', $paymentDetails['payment_id'])->exists()) {
                return ['status' => 0, 'message' => 'Duplicate payment detected'];
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
                'merchant_transaction_id' => $orderId,
                'response' => json_encode($paymentDetails['raw_response']),
                'wallet_related' => true,
                'wallet_id' => $wallet->id,
                'wallet_used' => false
            ]);

            // Add funds to wallet
            $wallet->addFunds(
                $amount,
                'credit',
                'Wallet topup via Cashfree',
                [
                    'cashfree_payment_id' => $paymentDetails['payment_id'],
                    'cashfree_order_id' => $orderId,
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

            DB::commit();

            return [
                'status' => 1,
                'message' => 'Wallet topup successful',
                'data' => [
                    'payment_no' => $paymentNo,
                    'transaction_id' => $paymentDetails['payment_id'],
                    'amount_added' => $amount,
                    'new_balance' => (float) $wallet->balance,
                    'date' => now()->format('d F Y \a\t h:i A')
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Process Wallet Topup Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return ['status' => 0, 'message' => 'Failed to process wallet topup'];
        }
    }

    private function processOrderPayment($orderId, $paymentDetails)
    {
        DB::beginTransaction();

        try {
            $tempEntry = TempEntry::where('order_id', $orderId)
                ->where('type', 'order_payment')
                ->first();

            if (!$tempEntry) {
                return ['status' => 0, 'message' => 'Order not found'];
            }

            if ($tempEntry->status == 1) {
                $payment = Payment::where('merchant_transaction_id', $orderId)->first();
                return [
                    'status' => 1,
                    'message' => 'Payment already processed',
                    'data' => [
                        'order_id' => $payment->order_id ?? '',
                        'transaction_id' => $payment->transaction_id ?? ''
                    ]
                ];
            }

            $notes = json_decode($tempEntry->notes, true);
            $userId = $notes['user_id'] ?? null;
            $checkoutId = $notes['checkout_id'] ?? null;
            $amount = $notes['amount'] ?? $paymentDetails['amount'];

            if (!$userId || !$checkoutId) {
                return ['status' => 0, 'message' => 'Invalid order data'];
            }

            $user = User::find($userId);
            if (!$user) {
                return ['status' => 0, 'message' => 'User not found'];
            }

            // Check for duplicate payment
            if (Payment::where('transaction_id', $paymentDetails['payment_id'])->exists()) {
                return ['status' => 0, 'message' => 'Duplicate payment detected'];
            }

            $preCheckout = Precheckout::where([
                'id' => $checkoutId,
                'user_id' => $userId,
                'is_order_placed' => 0
            ])->first();

            if (!$preCheckout) {
                return ['status' => 0, 'message' => 'Invalid checkout session'];
            }

            $orderService = new OrderService();

            $order = $orderService->createCashfreeOrder($preCheckout, $userId, [
                'type' => 'Cashfree',
                'method' => 'Cashfree',
                'status' => 'Paid',
                'transaction_id' => $paymentDetails['payment_id'],
                'amount' => $preCheckout->grand_total,
                'merchant_txn' => $orderId,
                'response' => json_encode($paymentDetails['raw_response']),
            ]);

            if (!$order) {
                throw new \Exception('Failed to create order');
            }

            // Update temp entry
            $tempEntry->update([
                'payment_status' => 'Paid',
                'status' => 1,
                'processed_at' => now(),
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

            DB::commit();

            return [
                'status' => 1,
                'message' => 'Order payment successful',
                'data' => [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'invoice_no' => $order->invoice_no ?? '',
                    'total_amount' => (float) $order->total_amount,
                    'transaction_id' => $paymentDetails['payment_id'],
                    'date' => now()->format('d F Y \a\t h:i A'),
                    'payment_method' => 'Cashfree',
                    'payment_status' => 'Paid'
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Process Order Payment Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return ['status' => 0, 'message' => 'Failed to process order payment'];
        }
    }

    // Verify payment with Cashfree API
    private function verifyCashfreePayment($orderId)
    {
        try {
            $headers = [
                "Content-Type: application/json",
                "x-api-version: 2022-09-01",
                "x-client-id: " . $this->appId,
                "x-client-secret: " . $this->secretKey
            ];

            $url = $this->baseUrl . '/orders/' . $orderId;

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

            Log::error('Cashfree Verification Failed', [
                'order_id' => $orderId,
                'http_code' => $httpCode,
                'response' => $response
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Cashfree verification error: ' . $e->getMessage());
            return null;
        }
    }
}
