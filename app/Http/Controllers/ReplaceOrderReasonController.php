<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveReasonRequest;
use App\Http\Requests\UpdateReasonRequest;
use App\Models\ReplaceOrderReason;
use App\Service\ReplaceOrderReasonService;
use Illuminate\Http\Request;

class ReplaceOrderReasonController extends Controller
{
    private $replaceOrderReasonService;

    public function __construct(ReplaceOrderReasonService $replaceOrderReasonService)
    {
        $this->middleware('auth');
        $this->replaceOrderReasonService = $replaceOrderReasonService;
    }

    public function index(Request $request)
    {
        return $this->replaceOrderReasonService->index($request);
    }

    public function store(SaveReasonRequest $request){
        return $this->replaceOrderReasonService->store($request->validated());
    }

    public function edit(ReplaceOrderReason $replaceOrderReason){
        return $this->replaceOrderReasonService->edit($replaceOrderReason);
    }

    public function update(UpdateReasonRequest $request, ReplaceOrderReason $replaceOrderReason){
        return $this->replaceOrderReasonService->update($request->validated(),$replaceOrderReason);
    }

    public function destroy(ReplaceOrderReason $replaceOrderReason){
        return $this->replaceOrderReasonService->destroy($replaceOrderReason);
    }

    public function updateStatus(Request $request)
    {
        return $this->replaceOrderReasonService->updateStatus($request);
    }
}
