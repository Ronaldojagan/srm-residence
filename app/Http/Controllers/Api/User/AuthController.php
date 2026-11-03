<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\SaveUserRequest;
use Illuminate\Http\Request;
use App\Service\Api\User\AuthService;

class AuthController extends Controller
{
    private $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function signIn(SaveUserRequest $request)
    {
        return $this->authService->signIn($request->validated());
    }
    public function razorpay(Request $request)
    {
        return view('backend.razorpay', [
            'data' => [
                'amount' => $request->amount * 100,
                'order_id' => $request->order_id,
            ]
        ]);
    }
    public function payment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $razorpayPaymentId = $request->razorpay_payment_id;
        $razorpayOrderId = $request->razorpay_order_id;
        $razorpaySignature = $request->razorpay_signature;

        $generatedSignature = hash_hmac(
            'sha256',
            $razorpayOrderId . "|" . $razorpayPaymentId,
            env('RAZORPAY_SECRET_KEY')
        );

        if ($generatedSignature === $razorpaySignature) {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified successfully.',
                'payment_id' => $razorpayPaymentId,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed.',
            ], 422);
        }
    }
}
