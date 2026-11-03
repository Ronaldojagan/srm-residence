<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveReasonRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user();
    }

    public function rules()
    {
        return [
            'reason' => ['required', 'string', 'min:3', 'max:255'],
            'is_show' => ['required', 'boolean']
        ];
    }
}
