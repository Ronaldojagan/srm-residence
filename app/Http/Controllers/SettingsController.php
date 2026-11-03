<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveContactRequest;
use App\Http\Requests\SaveDeliveryChargeRequest;
use App\Http\Requests\SaveFreeDeliveryAmountAboveRequest;
use App\Http\Requests\SavePlatformFeeRequest;
use App\Http\Requests\SaveTermsRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use App\Service\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->middleware('auth', ['except' => ['requestUserDelete']]);
        $this->settingsService = $settingsService;
    }

    public function profile(Request $request)
    {
        return $this->settingsService->profile($request);
    }
    public function profileEdit(Request $request)
    {
        return $this->settingsService->profileEdit($request);
    }
    public function updateProfile(UpdateProfileRequest $request, User $user)
    {
        return $this->settingsService->updateProfile($request->validated(), $user);
    }
    public function password(Request $request)
    {
        return $this->settingsService->password($request);
    }
    public function updatePassword(UpdatePasswordRequest $request, User $user)
    {
        return $this->settingsService->updatePassword($request->validated(), $user);
    }
    public function settingsIndex()
    {
        $settings = $this->settingsService->settingsIndex();

        return view('backend.settings', [
            'settings' => $settings,
        ]);
    }
    public function contact(SaveContactRequest $request)
    {
        return $this->settingsService->contact($request->validated());
    }
    public function termsConditions(SaveTermsRequest $request)
    {
        return $this->settingsService->termsConditions($request->validated());
    }
    public function refundPolicy(Request $request)
    {
        $data = $request->validate([
            'refund_policy' => 'required|min:50'
        ]);

        return $this->settingsService->refundPolicy($data);
    }
    public function deliveryCharge(SaveDeliveryChargeRequest $request)
    {
        return $this->settingsService->deliveryCharge($request->validated());
    }
    public function freeDeliveryAmountAbove(SaveFreeDeliveryAmountAboveRequest $request)
    {
        return $this->settingsService->freeDeliveryAmountAbove($request->validated());
    }
    public function platformFee(SavePlatformFeeRequest $request)
    {
        return $this->settingsService->platformFee($request->validated());
    }
    public function requestUserDelete(Request $request)
    {
        return $this->settingsService->requestUserDelete($request);
    }
    public function returnReplaceDaysSettings(Request $request)
    {
        $data = $request->validate([
            'max_return_days' => 'required|integer|min:1|max:365',
            'max_replace_days' => 'required|integer|min:1|max:365'
        ]);

        return $this->settingsService->returnReplaceDaysSettings($data);
    }

}
