<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\SaveAddressRequest;
use App\Http\Requests\Api\User\UpdateAddressRequest;
use Illuminate\Http\Request;
use App\Service\Api\User\CommonService;
use App\Http\Requests\Api\User\UpdateProfileRequest;
use App\Http\Requests\Api\User\UpdateWishlistRequest;

class CommonController extends Controller
{
    private $commonService;
    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function profile()
    {
        return $this->commonService->profile();
    }

    public function profileUpdate(UpdateProfileRequest $request)
    {
        return $this->commonService->profileUpdate($request->validated());
    }

    public function home()
    {
        return $this->commonService->home();
    }

    public function productsByOfferPercentage($id, $percentage)
    {
        return $this->commonService->productsByOfferPercentage($id, $percentage);
    }

    public function productsByLowestPriceSlab($id, $amount)
    {
        return $this->commonService->productsByLowestPriceSlab($id, $amount);
    }

    public function bestSelling(Request $request)
    {
        return $this->commonService->bestSelling($request);
    }

    public function topDeals(Request $request)
    {
        return $this->commonService->topDeals($request);
    }

    public function latestProducts()
    {
        return $this->commonService->latestProducts();
    }

    public function wishlist(UpdateWishlistRequest $request)
    {
        return $this->commonService->wishlist($request->validated());
    }

    public function wishlistShow(Request $request)
    {
        return $this->commonService->wishlistShow($request);
    }

    public function coupon(Request $request)
    {
        return $this->commonService->coupon($request);
    }

    public function address()
    {
        return $this->commonService->address();
    }

    public function addressPost(SaveAddressRequest $request)
    {
        return $this->commonService->addressPost($request->validated());
    }

    public function addressEdit($id)
    {
        return $this->commonService->addressEdit($id);
    }

    public function addressUpdate(UpdateAddressRequest $request, $id)
    {
        return $this->commonService->addressUpdate($request->validated(), $id);
    }

    public function addressDelete($id)
    {
        return $this->commonService->addressDelete($id);
    }

    public function shopChangeGet()
    {
        return $this->commonService->shopChangeGet();
    }
    public function shopChangePost(Request $request)
    {
        return $this->commonService->shopChangePost($request);
    }

    public function defaultAddress(Request $request, $id)
    {
        return $this->commonService->defaultAddress($request, $id);
    }

    public function search(Request $request)
    {
        return $this->commonService->search($request);
    }

    public function orderStatus(Request $request)
    {
        return $this->commonService->orderStatus($request);
    }

    public function brandList()
    {
        return $this->commonService->brandList();
    }

    public function brandDetails($id)
    {
        return $this->commonService->brandDetails($id);
    }

    public function wallet(Request $request)
    {
        return $this->commonService->wallet($request);
    }

    public function notification(Request $request)
    {
        return $this->commonService->notification($request);
    }

    public function cancellationReasons(Request $request)
    {
        return $this->commonService->cancellationReasons($request);
    }

    public function userAccountReasons(Request $request)
    {
        return $this->commonService->userAccountReasons($request);
    }

    public function returnOrderReasons(Request $request)
    {
        return $this->commonService->returnOrderReasons($request);
    }

    public function replaceOrderReasons(Request $request)
    {
        return $this->commonService->replaceOrderReasons($request);
    }

    public function categorySelectBoxList()
    {
        return $this->commonService->categorySelectBoxList();
    }

    public function brandSelectBoxList()
    {
        return $this->commonService->brandSelectBoxList();
    }

}
