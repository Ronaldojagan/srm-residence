<?php

namespace App\Http\Requests\Api\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phone' => ['required', 'digits:10'],
            'otp'   => ['sometimes', 'digits:4'],

            'name'      => ['sometimes', 'string'],
            'email'     => ['nullable', 'email'],
            'device_id' => ['sometimes', 'string'],
            'fcm_token' => ['sometimes', 'string'],

            'tax_type' => ['nullable', Rule::in(['gst', 'pan'])],

            'gst_no' => [
                'exclude_unless:tax_type,gst',
                'required',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
            ],

            'pan_no' => [
                'exclude_unless:tax_type,pan',
                'required',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
            ],

            'shop_name'   => ['required_with:name', 'string'],
            'shop_image' => [
                    'required_with:name',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048'
                ],
            'owner_name'  => ['required_with:name', 'string'],
            'owner_phone' => ['required_with:name', 'digits:10'],
            'address'     => ['required_with:name', 'string'],
            'commission_percentage' => ['required_with:name', 'numeric'],

            'latitude'  => ['required_with:name', 'numeric', 'between:-90,90'],
            'longitude' => ['required_with:name', 'numeric', 'between:-180,180'],

            'bank_name'      => ['required_with:name', 'string'],
            'account_number' => ['required_with:name', 'digits_between:9,18'],
            'ifsc_code'      => ['required_with:name', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'],
            'holder_name'    => ['required_with:name', 'string'],
            'branch'         => ['required_with:name', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'tax_type.in' => 'Tax type must be GST or PAN',
            'gst_no.required' => 'GST number is required when tax type is GST',
            'gst_no.regex' => 'Invalid GST number format',
            'pan_no.required' => 'PAN number is required when tax type is PAN',
            'pan_no.regex' => 'Invalid PAN number format',

            'shop_name.required_with' => 'Shop name is required',
            'owner_name.required_with' => 'Owner name is required',
            'owner_phone.required_with' => 'Owner phone number is required',
            'owner_phone.digits' => 'Owner phone must be 10 digits',
            'address.required_with' => 'Shop address is required',

            'latitude.required_with' => 'Latitude is required',
            'latitude.numeric' => 'Latitude must be a valid number',
            'latitude.between' => 'Latitude must be between -90 and 90',

            'commission_percentage.required_with' => 'Commission Percentage is required',
            'commission_percentage.numeric' => 'Commission Percentage must be a valid number',

            'longitude.required_with' => 'Longitude is required',
            'longitude.numeric' => 'Longitude must be a valid number',
            'longitude.between' => 'Longitude must be between -180 and 180',

            'bank_name.required_with' => 'Bank name is required',
            'account_number.required_with' => 'Account number is required',
            'account_number.digits_between' => 'Account number must be between 9 and 18 digits',
            'ifsc_code.required_with' => 'IFSC code is required',
            'ifsc_code.regex' => 'Invalid IFSC code format',
            'holder_name.required_with' => 'Account holder name is required',
            'branch.required_with' => 'Branch name is required',

            'shop_image.required_with' => 'Shop image is required',
            'shop_image.image' => 'Shop image must be a valid image file',
            'shop_image.mimes' => 'Shop image must be JPG, PNG, or WEBP',
            'shop_image.max' => 'Shop image must not exceed 2MB',
        ];
    }
}
