<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Electronics',
                'sort_order' => 1,
                'status' => true,
                'image' => 'avatar.jpg'
            ],
            [
                'name' => 'Fashion',
                'sort_order' => 2,
                'status' => true,
                'image' => 'avatar.jpg'
            ],
            [
                'name' => 'Groceries',
                'sort_order' => 3,
                'status' => true,
                'image' => 'avatar.jpg'
            ],
            [
                'name' => 'Home & Kitchen',
                'sort_order' => 4,
                'status' => true,
                'image' => 'avatar.jpg'
            ],
            [
                'name' => 'Beauty & Health',
                'sort_order' => 5,
                'status' => true,
                'image' => 'avatar.jpg'
            ],
            [
                'name' => 'Sports & Fitness',
                'sort_order' => 6,
                'status' => true,
                'image' => 'avatar.jpg'
            ],
            [
                'name' => 'Books & Stationery',
                'sort_order' => 7,
                'status' => true,
                'image' => 'avatar.jpg'
            ],
            [
                'name' => 'Toys & Games',
                'sort_order' => 8,
                'status' => true,
                'image' => 'avatar.jpg'
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
