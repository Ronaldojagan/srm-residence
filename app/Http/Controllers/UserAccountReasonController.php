<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveReasonRequest;
use App\Http\Requests\UpdateReasonRequest;
use App\Models\UserAccountReason;
use App\Service\UserAccountReasonService;
use Illuminate\Http\Request;

class UserAccountReasonController extends Controller
{
    private $userAccountReasonService;

    public function __construct(UserAccountReasonService $userAccountReasonService)
    {
        $this->middleware('auth');
        $this->userAccountReasonService = $userAccountReasonService;
    }

    public function index(Request $request)
    {
        return $this->userAccountReasonService->index($request);
    }

    public function store(SaveReasonRequest $request){
        return $this->userAccountReasonService->store($request->validated());
    }

    public function edit(UserAccountReason $userAccountReason){
        return $this->userAccountReasonService->edit($userAccountReason);
    }

    public function update(UpdateReasonRequest $request, UserAccountReason $userAccountReason){
        return $this->userAccountReasonService->update($request->validated(),$userAccountReason);
    }

    public function destroy(UserAccountReason $userAccountReason){
        return $this->userAccountReasonService->destroy($userAccountReason);
    }

    public function updateStatus(Request $request)
    {
        return $this->userAccountReasonService->updateStatus($request);
    }
}
