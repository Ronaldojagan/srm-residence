<?php

namespace App\Http\Requests\Api\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBankRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name' => 'required|required|string|max:255',
            'account_number' => 'required|required|string|max:30',
            'ifsc_code' => [
                'required',
                'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'
            ],
            'branch' => 'required|string|max:255',
            'holder_name' => 'required|required|string|max:255',
        ];
    }
}
