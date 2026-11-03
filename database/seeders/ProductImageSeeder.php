<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();

        foreach ($products as $product) {
            $imageCount = rand(2, 4);

            for ($i = 1; $i <= $imageCount; $i++) {
                ProductImage::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'image_path' => "product_image_{$i}.jpg",
                    ],
                    [
                        'created_by' => $product->created_by,
                    ]
                );
            }
        }
    }
}
