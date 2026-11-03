<?php

namespace App\Http\Requests\Api\Shop;

use Illuminate\Foundation\Http\FormRequest;

class SaveProductDetailsUpdateRequest extends FormRequest
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
        return [
            'name'                    => ['required', 'string', 'max:255'],
            'category_id'             => ['required', 'exists:categories,id'],
            'sub_category_id'         => ['required', 'exists:sub_categories,id'],
            'brand_id'                => ['required', 'exists:brands,id'],
            'shop_id'                 => ['sometimes', 'exists:shops,id'],
            'image'                   => ['sometimes', 'image', 'max:2048'],
            'quantity'                => ['sometimes'],
            'max_product_stock_alert' => ['sometimes', 'numeric', 'min:0'],
            'description'             => ['sometimes', 'string'],
            'keywords'                => ['nullable', 'string'],
            'additional_images'       => ['sometimes', 'array', 'max:3'],
            'additional_images.*'     => ['image', 'max:2048'],
            'additional_info'                     => ['nullable'],
            'additional_info.*.heading'           => ['nullable', 'string', 'max:255'],
            'additional_info.*.items'             => ['nullable'],
            'additional_info.*.items.*.title'     => ['nullable', 'string', 'max:255'],
            'additional_info.*.items.*.value'     => ['nullable', 'string', 'max:1000'],
            'youtube_videos'           => ['nullable'],
            'youtube_videos.*.url'     => ['nullable', 'url', 'max:500'],
        ];
    }
}
