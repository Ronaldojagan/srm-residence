<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
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
        return [
            'name' => ['required'],
            'code' => [
                    'required',
                    Rule::unique('coupons')->ignore($this->coupon)
                ],
            'amount' => ['required', 'numeric'],
            'start_date' => ['required', 'date'],
            'expire_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:0,1'],
            'min_purchase_amount' => ['required', 'numeric'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
            'description' => ['required'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'name.required' => 'The coupon name is required.',
            'code.required' => 'The coupon code is required.',
            'amount.required' => 'Please enter the discount amount.',
            'amount.numeric' => 'The discount amount must be a valid number.',
            'start_date.required' => 'Please select a start date.',
            'start_date.date' => 'Start date must be a valid date.',
            'expire_date.required' => 'Please select an expiry date.',
            'expire_date.date' => 'Expiry date must be a valid date.',
            'expire_date.after_or_equal' => 'Expiry date must be after or equal to the start date.',
            'status.required' => 'Please select the status of the coupon.',
            'status.in' => 'Invalid status value provided.',
            'min_purchase_amount.required' => 'Please enter the minimum purchase amount.',
            'min_purchase_amount.numeric' => 'Minimum purchase amount must be a valid number.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'Image must be a file of type: jpg, jpeg, png, webp.',
            'description.required' => 'Please provide a description for the coupon.',
        ];
    }
}
