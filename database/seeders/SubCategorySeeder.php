<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    public function run()
    {
        $electronics = Category::where('name', 'Electronics')->first();
        $fashion = Category::where('name', 'Fashion')->first();
        $groceries = Category::where('name', 'Groceries')->first();
        $homeKitchen = Category::where('name', 'Home & Kitchen')->first();
        $beauty = Category::where('name', 'Beauty & Health')->first();
        $sports = Category::where('name', 'Sports & Fitness')->first();

        $subCategories = [
            ['category_id' => $electronics->id, 'name' => 'Smartphones', 'sort_order' => 1, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $electronics->id, 'name' => 'Laptops', 'sort_order' => 2, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $electronics->id, 'name' => 'Headphones', 'sort_order' => 3, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $electronics->id, 'name' => 'Televisions', 'sort_order' => 4, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $electronics->id, 'name' => 'Cameras', 'sort_order' => 5, 'status' => true, 'image' => 'avatar.jpg'],

            ['category_id' => $fashion->id, 'name' => 'Men\'s Clothing', 'sort_order' => 1, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $fashion->id, 'name' => 'Women\'s Clothing', 'sort_order' => 2, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $fashion->id, 'name' => 'Kids Wear', 'sort_order' => 3, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $fashion->id, 'name' => 'Footwear', 'sort_order' => 4, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $fashion->id, 'name' => 'Accessories', 'sort_order' => 5, 'status' => true, 'image' => 'avatar.jpg'],

            ['category_id' => $groceries->id, 'name' => 'Fruits & Vegetables', 'sort_order' => 1, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $groceries->id, 'name' => 'Dairy Products', 'sort_order' => 2, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $groceries->id, 'name' => 'Beverages', 'sort_order' => 3, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $groceries->id, 'name' => 'Snacks', 'sort_order' => 4, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $groceries->id, 'name' => 'Cooking Essentials', 'sort_order' => 5, 'status' => true, 'image' => 'avatar.jpg'],

            ['category_id' => $homeKitchen->id, 'name' => 'Furniture', 'sort_order' => 1, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $homeKitchen->id, 'name' => 'Kitchenware', 'sort_order' => 2, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $homeKitchen->id, 'name' => 'Home Decor', 'sort_order' => 3, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $homeKitchen->id, 'name' => 'Bedding', 'sort_order' => 4, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $homeKitchen->id, 'name' => 'Lighting', 'sort_order' => 5, 'status' => true, 'image' => 'avatar.jpg'],

            ['category_id' => $beauty->id, 'name' => 'Skincare', 'sort_order' => 1, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $beauty->id, 'name' => 'Makeup', 'sort_order' => 2, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $beauty->id, 'name' => 'Haircare', 'sort_order' => 3, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $beauty->id, 'name' => 'Fragrances', 'sort_order' => 4, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $beauty->id, 'name' => 'Wellness', 'sort_order' => 5, 'status' => true, 'image' => 'avatar.jpg'],

            ['category_id' => $sports->id, 'name' => 'Exercise Equipment', 'sort_order' => 1, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $sports->id, 'name' => 'Sportswear', 'sort_order' => 2, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $sports->id, 'name' => 'Outdoor Gear', 'sort_order' => 3, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $sports->id, 'name' => 'Team Sports', 'sort_order' => 4, 'status' => true, 'image' => 'avatar.jpg'],
            ['category_id' => $sports->id, 'name' => 'Fitness Accessories', 'sort_order' => 5, 'status' => true, 'image' => 'avatar.jpg'],
        ];

        foreach ($subCategories as $subCategory) {
            SubCategory::updateOrCreate(
                [
                    'category_id' => $subCategory['category_id'],
                    'name' => $subCategory['name']
                ],
                $subCategory
            );
        }
    }
}
