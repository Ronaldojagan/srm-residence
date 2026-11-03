<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
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
            'name'   => ['required', 'string', 'max:255'],
            'image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'   => 'The brand name is required.',
            'name.string'     => 'The brand name must be a valid string.',
            'name.max'        => 'The brand name must not exceed 255 characters.',

            'image.image'     => 'The uploaded file must be an image.',
            'image.mimes'     => 'Allowed image types are: jpg, jpeg, png, webp.',
            'image.max'       => 'The image must not be larger than 2MB.',
        ];
    }
}
