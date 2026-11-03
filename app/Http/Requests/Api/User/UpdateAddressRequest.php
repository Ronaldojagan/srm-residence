<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|digits:10',
            'lat' => 'required',
            'long' => 'required',
            'flat_no' => 'required|string|max:255',
            'area_name' => 'sometimes|string|max:255',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'address_type' => ['required', Rule::in(['home', 'office', 'other'])],
            'status' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'city.required' => 'City is required.',
            'pincode.required' => 'Pincode is required.',
            'address_type.in' => 'Address type must be home, work, or other.',
        ];
    }

}
