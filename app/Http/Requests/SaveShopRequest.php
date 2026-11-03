<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $shopId = $this->route('shop') ? decrypt($this->route('shop')) : null;

        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:1000',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'image' => ($this->isMethod('post') ? 'required|' : 'nullable|') . 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'owner_name' => 'required|string|max:255',
            'owner_phone' => [
                'required',
                'digits:10',
                Rule::unique('users', 'phone')
                    ->where('type', 2)
                    ->ignore($this->user_id ?? null),
            ],

            'email' => [
                'nullable',
                'string',
                'max:255',
                'email',
                Rule::unique('users', 'email')
                    ->where('type', 2)
                    ->ignore($this->user_id ?? null),
            ],
            'status' => 'required',
            'account_number' => ($this->isMethod('post') ? 'required|' : 'nullable|') . 'regex:/^\d{9,18}$/|max:20',
            'ifsc_code' => ($this->isMethod('post') ? 'required|' : 'nullable|') . 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/|size:11',
            'holder_name' => ($this->isMethod('post') ? 'required|' : 'nullable|') . 'string|max:255',
            'bank_name' => ($this->isMethod('post') ? 'required|' : 'nullable|') . 'string|max:255',
            'branch' => ($this->isMethod('post') ? 'required|' : 'nullable|') . 'string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Shop Name is required.',
            'shop_phone.required' => 'Shop Phone is required.',
            'address.required' => 'Shop Address is required.',
            'image.required' => 'Shop Image is required.',
            'image.image' => 'The uploaded file must be an image.',
            'latitude.required' => 'Latitude is required.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'longitude.required' => 'Longitude is required.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'owner_name.required' => 'Owner Name is required.',
            'email.required' => 'Email is required.',
            'password.required' => 'Password is required.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either Active or Inactive.',
            'account_number.required' => 'The account number is required.',
            'account_number.regex'    => 'The account number must be 9 to 18 digits.',
            'account_number.max'      => 'The account number may not be greater than 20 characters.',

            'ifsc_code.required' => 'The IFSC code is required.',
            'ifsc_code.regex'    => 'The IFSC code must be in valid format (e.g., HDFC0001234).',
            'ifsc_code.size'     => 'The IFSC code must be exactly 11 characters.',
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
