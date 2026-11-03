<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class SaveProductRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'product_no'              => ['nullable', 'string', 'max:255'],
            'name'                    => ['required', 'string', 'max:255'],
            'category_id'             => ['required', 'exists:categories,id'],
            'sub_category_id'         => ['required', 'exists:sub_categories,id'],
            'brand_id'                => ['required', 'exists:brands,id'],
            'image'                   => ['required', 'image', 'max:2048'],
            'quantity'                => ['required'],
            'product_stock'           => ['required', 'numeric', 'min:0'],
            'amount'                  => ['required', 'numeric', 'min:0'],
            'discount_amount'         => ['required', 'numeric', 'min:0'],
            'discount_percentage'     => ['required', 'numeric', 'min:1', 'max:99'],
            'max_product_stock_alert' => ['required', 'numeric', 'min:0'],
            'description'             => ['required', 'string'],
            'status'                  => ['required', 'in:1,0'],
            'additional_images'       => ['sometimes', 'array', 'max:3'],
            'additional_images.*'     => ['image', 'max:2048'],
            'keywords'                => ['nullable', 'string'],
            'additional_info'         => ['nullable', 'array'],
            'additional_info.*.heading' => ['nullable', 'string', 'max:255'],
            'additional_info.*.items'   => ['nullable', 'array'],
            'additional_info.*.items.*.title' => ['nullable', 'string', 'max:255'],
            'additional_info.*.items.*.value' => ['nullable', 'string', 'max:1000'],
        ];
    }

}
