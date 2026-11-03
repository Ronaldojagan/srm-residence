<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveCouponRequest extends FormRequest
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
            'name'               => ['required', 'string', 'max:255'],
            'code'               => ['required', 'string', 'max:50', 'unique:coupons,code,' . $this->id],
            'amount'             => ['required', 'numeric', 'min:0'],
            'start_date'         => ['required'],
            'expire_date'        => ['required', 'after_or_equal:start_date'],
            'status'             => ['required', 'in:1,0'],
            'min_purchase_amount'=> ['required', 'numeric', 'min:0'],
            'usage_limit'        => ['nullable', 'integer', 'min:1'],
            'image'              => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'description'        => ['required', 'string'],
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'name.required'                => 'Coupon name is required.',
            'code.required'                => 'Coupon code is required.',
            'code.unique'                  => 'This coupon code already exists.',
            'amount.required'              => 'Please enter a discount amount.',
            'amount.numeric'               => 'The discount amount must be a number.',
            'start_date.required'          => 'Please specify the start date.',
            'expire_date.required'         => 'Please specify the expiration date.',
            'expire_date.after_or_equal'   => 'The expiration date must be after or equal to the start date.',
            'status.required'              => 'Coupon status is required.',
            'status.in'                    => 'Status must be either active or inactive.',
            'min_purchase_amount.required' => 'Minimum purchase amount is required.',
            'min_purchase_amount.numeric'  => 'Minimum purchase must be a valid number.',
            'usage_limit.integer'          => 'Usage limit must be a whole number.',
            'usage_limit.min'              => 'Usage limit must be at least 1.',
            'image.required'               => 'Please upload a coupon image.',
            'image.image'                  => 'The uploaded file must be an image.',
            'image.mimes'                  => 'Only jpeg, png, jpg, and gif formats are allowed.',
            'image.max'                    => 'Image size should not exceed 2MB.',
            'description.required'         => 'Please provide a description for the coupon.',
        ];
    }
}
