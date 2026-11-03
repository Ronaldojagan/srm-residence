<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUserShopRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'user_id' => 'required|string',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:1000',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'owner_name' => 'required|string|max:255',
            'owner_phone' => 'required|digits:10',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:0,1',
            'account_number' => 'required|regex:/^\d{9,18}$/|max:20',
            'ifsc_code' => 'required|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/|size:11',
            'holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User ID is required.',
            'name.required' => 'Shop Name is required.',
            'phone.required' => 'Shop Phone is required.',
            'phone.digits' => 'Shop Phone must be exactly 10 digits.',
            'address.required' => 'Shop Address is required.',
            'latitude.required' => 'Latitude is required.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'longitude.required' => 'Longitude is required.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'owner_name.required' => 'Owner Name is required.',
            'owner_phone.required' => 'Owner Phone is required.',
            'owner_phone.digits' => 'Owner Phone must be exactly 10 digits.',
            'status.required' => 'Status is required.',
            'account_number.required' => 'The account number is required.',
            'account_number.regex' => 'The account number must be 9 to 18 digits.',
            'ifsc_code.required' => 'The IFSC code is required.',
            'ifsc_code.regex' => 'The IFSC code must be in valid format (e.g., HDFC0001234).',
            'ifsc_code.size' => 'The IFSC code must be exactly 11 characters.',
            'holder_name.required' => 'Account Holder Name is required.',
            'bank_name.required' => 'Bank Name is required.',
            'branch.required' => 'Branch is required.',
            'commission_percentage.required' => 'Commission Percentage is required.',
            'commission_percentage.numeric' => 'Commission Percentage must be a valid number.',
            'commission_percentage.min' => 'Commission Percentage cannot be less than 0.',
            'commission_percentage.max' => 'Commission Percentage cannot be more than 100.',
        ];
    }
}
