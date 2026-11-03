<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDeliveryPointsRequest extends FormRequest
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
            'location'   => ['required', 'string', 'max:255'],
            'sub_location'   => ['required', 'string', 'max:255'],
            'status' => ['required','boolean'],
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
            'location.required'   => 'The location name is required.',
            'location.string'     => 'The location name must be a valid string.',
            'location.max'        => 'The location name must not exceed 255 characters.',

            'sub_location.required'   => 'The sub location name is required.',
            'sub_location.string'     => 'The sub location name must be a valid string.',
            'sub_location.max'        => 'The sub location name must not exceed 255 characters.',

            'image.required'  => 'Please upload a brand image.',
            'image.image'     => 'The uploaded file must be an image.',
            'image.mimes'     => 'The image must be a file of type: jpg, jpeg, png, webp.',
            'image.max'       => 'The image size must not exceed 2MB.',
        ];
    }
}
