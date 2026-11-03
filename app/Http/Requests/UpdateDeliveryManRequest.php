<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryManRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            // 'phone' => [
            //     'sometimes',
            //     'digits_between:10,15'
            // ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'full_address' => ['sometimes', 'string', 'max:500'],
            'delivery_point_id' => [
                'sometimes',
                'exists:delivery_points,id'
            ],
            'status' => ['sometimes', 'boolean'],
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
            'name.max' => 'Name cannot exceed 255 characters.',

            'phone.digits_between' => 'Phone number must be between 10 and 15 digits.',

            'image.image' => 'Uploaded file must be an image.',
            'image.mimes' => 'Image must be jpg, jpeg, png or webp.',
            'image.max' => 'Image size must not exceed 2MB.',


            'delivery_point_id.exists' => 'Selected delivery location is invalid.',

            'status.boolean' => 'Invalid status selected.',
        ];
    }
}
