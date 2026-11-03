<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            ['name' => 'Apple', 'sort_order' => 1, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Samsung', 'sort_order' => 2, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Nike', 'sort_order' => 3, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Adidas', 'sort_order' => 4, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Sony', 'sort_order' => 5, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'LG', 'sort_order' => 6, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Levi\'s', 'sort_order' => 7, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Puma', 'sort_order' => 8, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Reebok', 'sort_order' => 9, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Dell', 'sort_order' => 10, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'HP', 'sort_order' => 11, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Lenovo', 'sort_order' => 12, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Asus', 'sort_order' => 13, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Acer', 'sort_order' => 14, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Microsoft', 'sort_order' => 15, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Google', 'sort_order' => 16, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'OnePlus', 'sort_order' => 17, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Xiaomi', 'sort_order' => 18, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Oppo', 'sort_order' => 19, 'status' => true, 'image' => 'avatar.jpg'],
            ['name' => 'Vivo', 'sort_order' => 20, 'status' => true, 'image' => 'avatar.jpg'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['name' => $brand['name']],
                $brand
            );
        }
    }
}
