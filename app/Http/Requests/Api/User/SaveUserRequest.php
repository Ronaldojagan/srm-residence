<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUserRequest extends FormRequest
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
            'name'=>['sometimes'],
            'otp'=>['sometimes', 'numeric', 'digits:4'],
            'email'=>['sometimes','email'],
            'phone'=>['required','digits:10'],
            'lat'=>['sometimes','string'],
            'long'=>['sometimes','string'],
            'street'=>['sometimes','string'],
            'city'=>['sometimes','string'],
            'state'=>['sometimes','string'],
            'country'=>['sometimes','string'],
            'district'=>['sometimes','string'],
            'pincode'=>['sometimes','string'],
            'flat_no'=>['sometimes','string'],
            'area_name'=>['sometimes','string'],
            'device_id'=>['sometimes','string'],
            'fcm_token'=>['sometimes','string'],
            'address_type' => ['sometimes', Rule::in(['home', 'office', 'other'])],
        ];
    }
}
