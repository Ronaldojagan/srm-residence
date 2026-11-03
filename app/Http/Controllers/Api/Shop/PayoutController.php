<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Service\Api\Shop\PayoutService;

class PayoutController extends Controller
{
    private $payoutService;
    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    public function payout(Request $request)
    {
        return $this->payoutService->payout($request);
    }

    public function payoutDetails($id)
    {
        return $this->payoutService->payoutDetails($id);
    }

}
