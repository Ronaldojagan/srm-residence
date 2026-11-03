<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCancellationRequestRequest;
use App\Http\Requests\UpdateCancellationRequestRequest;
use App\Models\CancellationReason;
use App\Service\CancellationReasonService;
use Illuminate\Http\Request;

class CancellationReasonController extends Controller
{
    private $cancellationReasonService;

    public function __construct(CancellationReasonService $cancellationReasonService)
    {
        $this->middleware('auth');
        $this->cancellationReasonService = $cancellationReasonService;
    }

    public function index(Request $request)
    {
        return $this->cancellationReasonService->index($request);
    }
    public function store(SaveCancellationRequestRequest $request){
        return $this->cancellationReasonService->store($request->validated());
    }
    public function edit(CancellationReason $cancellationReason){
        return $this->cancellationReasonService->edit($cancellationReason);
    }
    public function update(UpdateCancellationRequestRequest $request, CancellationReason $cancellationReason){
        return $this->cancellationReasonService->update($request->validated(),$cancellationReason);
    }
    public function destroy(CancellationReason $cancellationReason){
        return $this->cancellationReasonService->destroy($cancellationReason);
    }
    public function updateStatus(Request $request)
    {
        return $this->cancellationReasonService->updateStatus($request);
    }

}
