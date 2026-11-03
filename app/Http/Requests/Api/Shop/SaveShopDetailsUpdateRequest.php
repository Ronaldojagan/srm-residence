<?php

namespace App\Http\Requests\Api\Shop;

use Illuminate\Foundation\Http\FormRequest;

class SaveShopDetailsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return auth()->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shopId = $this->route('shop')?->id;

        return [
            'name' => 'sometimes|string|max:255|unique:shops,name,' . $shopId,
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'sometimes|string|max:500',
            'remarks' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Shop name already exists',
            'image.image' => 'The file must be an image',
            'image.max' => 'The image size must not exceed 2MB',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => $this->name === '' ? null : $this->name,
            'address' => $this->address === '' ? null : $this->address,
        ]);
    }

}


