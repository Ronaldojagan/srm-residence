<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePackageRequest extends FormRequest
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
            'amount' => ['required','string'],
            'days' => ['required','numeric'],
            'status' => ['required','boolean'],
            // 'image'  => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
            'name.required'   => 'The package name is required.',
            'name.string'     => 'The package name must be a valid string.',
            'name.max'        => 'The package name must not exceed 255 characters.',

            'image.required'  => 'Please upload a package image.',
            'image.image'     => 'The uploaded file must be an image.',
            'image.mimes'     => 'The image must be a file of type: jpg, jpeg, png, webp.',
            'image.max'       => 'The image size must not exceed 2MB.',
        ];
    }
}
