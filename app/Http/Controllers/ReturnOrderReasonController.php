<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveReasonRequest;
use App\Http\Requests\UpdateReasonRequest;
use App\Models\ReturnOrderReason;
use App\Service\ReturnOrderReasonService;
use Illuminate\Http\Request;

class ReturnOrderReasonController extends Controller
{
    private $returnOrderReasonService;

    public function __construct(ReturnOrderReasonService $returnOrderReasonService)
    {
        $this->middleware('auth');
        $this->returnOrderReasonService = $returnOrderReasonService;
    }

    public function index(Request $request)
    {
        return $this->returnOrderReasonService->index($request);
    }

    public function store(SaveReasonRequest $request){
        return $this->returnOrderReasonService->store($request->validated());
    }

    public function edit(ReturnOrderReason $returnOrderReason){
        return $this->returnOrderReasonService->edit($returnOrderReason);
    }

    public function update(UpdateReasonRequest $request, ReturnOrderReason $returnOrderReason){
        return $this->returnOrderReasonService->update($request->validated(),$returnOrderReason);
    }

    public function destroy(ReturnOrderReason $returnOrderReason){
        return $this->returnOrderReasonService->destroy($returnOrderReason);
    }

    public function updateStatus(Request $request)
    {
        return $this->returnOrderReasonService->updateStatus($request);
    }
}
