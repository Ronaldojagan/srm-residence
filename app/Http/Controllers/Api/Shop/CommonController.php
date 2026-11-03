<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Shop\SaveBankRequest;
use Illuminate\Http\Request;
use App\Service\Api\Shop\CommonService;
use App\Http\Requests\Api\User\UpdateProfileRequest;

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

    public function home(Request $request)
    {
        return $this->commonService->home($request);
    }

    public function notification(Request $request)
    {
        return $this->commonService->notification($request);
    }

    public function bankDetails(Request $request)
    {
        return $this->commonService->bankDetails($request);
    }

    public function bankDetailsUpdate(SaveBankRequest $request)
    {
        return $this->commonService->bankDetailsUpdate($request->validated());
    }

    public function userAccountReasons(Request $request)
    {
        return $this->commonService->userAccountReasons($request);
    }

    public function courier(Request $request)
    {
        return $this->commonService->courier($request);
    }

    public function categorySelectBoxList()
    {
        return $this->commonService->categorySelectBoxList();
    }

    public function subCategorySelectBoxList($id)
    {
        return $this->commonService->subCategorySelectBoxList($id);
    }

    public function brandSelectBoxList()
    {
        return $this->commonService->brandSelectBoxList();
    }

    public function stockHistory(Request $request)
    {
        return $this->commonService->stockHistory($request);
    }


}
