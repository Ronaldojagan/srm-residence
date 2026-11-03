<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Models\Coupon;
use App\Service\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    private $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->middleware('auth');
        $this->couponService = $couponService;
    }
    public function create(Request $request)
    {
        return view('backend.coupon.coupon');
    }
    public function index(Request $request)
    {
        return $this->couponService->index($request);
    }
    public function store(SaveCouponRequest $request){
        return $this->couponService->store($request->validated());
    }
    public function edit(Coupon $coupon) {
        return view('backend.coupon.coupon', [
            'edit'=>$coupon,
        ]);
    }
    public function update(UpdateCouponRequest $request, Coupon $coupon){
        return $this->couponService->update($request->validated(), $coupon);
    }
    public function destroy(Coupon $coupon){
        return $this->couponService->destroy($coupon);
    }
    public function view(Coupon $coupon){
        return $this->couponService->view($coupon);
    }
    public function updateStatus(Request $request)
    {
        return $this->couponService->updateStatus($request);
    }
}
