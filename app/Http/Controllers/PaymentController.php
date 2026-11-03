<?php

namespace App\Http\Controllers;

use App\Service\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware('auth');
        $this->paymentService = $paymentService;
    }
    public function index(Request $request)
    {
        return $this->paymentService->index($request);
    }

    public function shopPaymentIndex(Request $request)
    {
        return $this->paymentService->shopPaymentIndex($request);
    }

}
