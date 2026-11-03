<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDeliveryManRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'digits_between:10,15'
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'full_address' => ['required', 'string', 'max:500'],
            'delivery_point_id' => [
                'required',
                'exists:delivery_points,id'
            ],
            'status' => ['required', 'boolean'],
        ];
    }

     /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Delivery man name is required.',
            'name.max' => 'Name cannot exceed 255 characters.',

            'phone.required' => 'Phone number is required.',
            'phone.digits_between' => 'Phone number must be between 10 and 15 digits.',

            'image.image' => 'Uploaded file must be an image.',
            'image.mimes' => 'Image must be jpg, jpeg, png or webp.',
            'image.max' => 'Image size must not exceed 2MB.',

            'full_address.required' => 'Address is required.',

            'delivery_point_id.required' => 'Please select a delivery location.',
            'delivery_point_id.exists' => 'Selected delivery location is invalid.',

            'status.required' => 'Please select status.',
            'status.boolean' => 'Invalid status selected.',
        ];
    }
}
