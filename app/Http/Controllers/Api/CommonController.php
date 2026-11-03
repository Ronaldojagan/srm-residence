<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\Api\CommonService;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    private $commonService;
    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function termsConditions(){
        return $this->commonService->termsConditions();
    }
    public function returnRefund(){
        return $this->commonService->returnRefund();
    }
    public function contact(){
        return $this->commonService->contact();
    }
    public function refresh(Request $request){
        return $this->commonService->refresh($request);
    }
    public function logout(Request $request){
        return $this->commonService->logout($request);
    }
    public function deleteAccount(Request $request)
    {
        return $this->commonService->deleteAccount($request);
    }
}
