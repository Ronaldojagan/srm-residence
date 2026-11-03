<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // First, ensure we have subcategories
        if (SubCategory::count() === 0) {
            $this->command->warn('No subcategories found. Creating some default subcategories first...');
            $this->createDefaultSubcategories();
        }

        $shops = Shop::all();
        $brands = Brand::all();
        $categories = Category::all();

        $products = [];

        for ($i = 1; $i <= 50; $i++) {
            $shop = $shops->random();
            $brand = $brands->random();
            $category = $categories->random();

            // Get a subcategory for this category
            $subCategory = SubCategory::where('category_id', $category->id)->inRandomOrder()->first();

            if (!$subCategory) {
                $subCategory = SubCategory::create([
                    'category_id' => $category->id,
                    'name' => 'Default ' . $category->name,
                    'sort_order' => 1,
                    'status' => true,
                    'image' => 'avatar.jpg'
                ]);
            }

            $price = rand(100, 10000);
            $discountPercentage = rand(0, 30);
            $discountAmount = floor($price * $discountPercentage / 100);

            $products[] = [
                'name' => $this->generateProductName($category->name, $brand->name, $i),
                'description' => $this->generateProductDescription($category->name, $brand->name),
                'price' => $price,
                // 'discount_amount' => $discountAmount,
                // 'discount_percentage' => $discountPercentage,
                // 'product_stock' => rand(10, 200),
                // 'quantity' => 1,
                // 'unit_type' => 'each',

                // FIXED: Keywords as JSON array (not string)
                // 'keywords' => json_encode($this->generateKeywords($category->name, $brand->name)),

                // 'approved' => true,
                'is_active' => true,
                // 'max_product_stock_alert' => rand(5, 20),
                // 'shop_id' => $shop->id,
                // 'brand_id' => $brand->id,
                // 'category_id' => $category->id,
                // 'sub_category_id' => $subCategory->id,
                'product_no' => 'PROD' . strtoupper(Str::random(8)),
                'image' => 'avatar.jpg',
                // 'created_by' => $shop->user_id,

                // FIXED: Additional info with correct structure for accordion
                // 'additional_info' => json_encode($this->generateAdditionalInfo($category->name)),
            ];
        }

        foreach (array_chunk($products, 10) as $chunk) {
            foreach ($chunk as $productData) {
                Product::updateOrCreate(
                    ['product_no' => $productData['product_no']],
                    $productData
                );
            }
        }

        $this->command->info('Products seeded successfully!');
    }

    /**
     * Create default subcategories if none exist
     */
    private function createDefaultSubcategories()
    {
        $categories = Category::all();
        foreach ($categories as $category) {
            SubCategory::create([
                'category_id' => $category->id,
                'name' => 'General ' . $category->name,
                'sort_order' => 1,
                'status' => true,
                'image' => 'avatar.jpg'
            ]);
        }
    }

    /**
     * Generate product name
     */
    private function generateProductName($category, $brand, $index)
    {
        $names = [
            'Electronics' => ["{$brand} Smartphone", "{$brand} Laptop", "{$brand} Headphones", "{$brand} Smart Watch"],
            'Fashion' => ["{$brand} T-Shirt", "{$brand} Jeans", "{$brand} Shoes", "{$brand} Jacket"],
            'Groceries' => ["Organic Apples", "Fresh Milk", "Whole Wheat Bread", "Organic Eggs"],
            'Home & Kitchen' => ["Kitchen Knife Set", "Non-Stick Pan", "Blender", "Coffee Maker"],
            'Beauty & Health' => ["Face Cream", "Shampoo", "Perfume", "Makeup Kit"],
            'Sports & Fitness' => ["Yoga Mat", "Dumbbell Set", "Running Shoes", "Sports Bottle"],
            'Books & Stationery' => ["Notebook Set", "Pen Collection", "Office Organizer", "Desk Lamp"],
            'Toys & Games' => ["Building Blocks", "Board Game", "Remote Car", "Educational Toy"],
        ];

        $categoryNames = $names[$category] ?? ["{$brand} {$category} Product"];
        return $categoryNames[array_rand($categoryNames)] . " - Model {$index}";
    }

    /**
     * Generate product description
     */
    private function generateProductDescription($category, $brand)
    {
        $descriptions = [
            "Premium quality {$category} from {$brand}. Built to last with superior craftsmanship.",
            "Experience excellence with this {$brand} {$category}. Perfect for everyday use.",
            "High-performance {$category} designed for modern lifestyle. Features advanced technology.",
            "Eco-friendly and durable {$category} from the trusted brand {$brand}.",
            "Stylish and functional {$category} that combines innovation with practical design.",
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Generate keywords as array (will be JSON encoded)
     */
    private function generateKeywords($category, $brand)
    {
        $baseKeywords = [
            strtolower($category),
            strtolower($brand),
            'product',
            'quality',
            'premium',
        ];

        // Add category-specific keywords
        $categoryKeywords = [
            'Electronics' => ['electronics', 'gadget', 'tech', 'device', 'digital'],
            'Fashion' => ['fashion', 'style', 'clothing', 'wear', 'apparel'],
            'Groceries' => ['groceries', 'food', 'fresh', 'organic', 'natural'],
            'Home & Kitchen' => ['home', 'kitchen', 'utensils', 'appliances', 'decor'],
            'Beauty & Health' => ['beauty', 'health', 'care', 'skincare', 'wellness'],
            'Sports & Fitness' => ['sports', 'fitness', 'exercise', 'workout', 'active'],
            'Books & Stationery' => ['books', 'stationery', 'office', 'study', 'education'],
            'Toys & Games' => ['toys', 'games', 'fun', 'play', 'entertainment'],
        ];

        $specificKeywords = $categoryKeywords[$category] ?? [];

        // Merge and take random 3-5 keywords
        $allKeywords = array_merge($baseKeywords, $specificKeywords);
        shuffle($allKeywords);

        return array_slice($allKeywords, 0, rand(3, 5));
    }

    /**
     * Generate additional info with correct structure for accordion
     */
    private function generateAdditionalInfo($category)
    {
        $sections = [];

        // Always have specifications section
        $sections[] = [
            'heading' => 'Specifications',
            'items' => $this->generateSpecifications($category)
        ];

        // Add features section (75% chance)
        if (rand(1, 100) <= 75) {
            $sections[] = [
                'heading' => 'Features',
                'items' => $this->generateFeatures($category)
            ];
        }

        // Add warranty/support section (50% chance)
        if (rand(1, 100) <= 50) {
            $sections[] = [
                'heading' => 'Warranty & Support',
                'items' => $this->generateWarrantyInfo()
            ];
        }

        return $sections;
    }

    /**
     * Generate specifications items
     */
    private function generateSpecifications($category)
    {
        $specs = [
            [
                'title' => 'Brand',
                'value' => $this->getRandomBrand()
            ],
            [
                'title' => 'Material',
                'value' => $this->getRandomMaterial($category)
            ],
            [
                'title' => 'Color',
                'value' => $this->getRandomColor()
            ],
            [
                'title' => 'Size/Dimensions',
                'value' => $this->getRandomDimensions($category)
            ],
            [
                'title' => 'Weight',
                'value' => rand(50, 5000) . ' grams'
            ],
        ];

        // Add category-specific specs
        if ($category === 'Electronics') {
            $specs[] = ['title' => 'Battery Life', 'value' => rand(8, 48) . ' hours'];
            $specs[] = ['title' => 'Connectivity', 'value' => 'Bluetooth 5.0, Wi-Fi'];
        } elseif ($category === 'Fashion') {
            $specs[] = ['title' => 'Fit', 'value' => ['Regular', 'Slim', 'Loose'][rand(0, 2)]];
            $specs[] = ['title' => 'Care Instructions', 'value' => 'Machine wash cold'];
        }

        return array_slice($specs, 0, rand(4, 6));
    }

    /**
     * Generate features items
     */
    private function generateFeatures($category)
    {
        $features = [
            ['title' => 'Premium Quality', 'value' => 'Made with high-grade materials for durability'],
            ['title' => 'Easy to Use', 'value' => 'User-friendly design with intuitive controls'],
            ['title' => 'Eco-Friendly', 'value' => 'Environmentally conscious manufacturing process'],
            ['title' => 'Multi-Purpose', 'value' => 'Versatile functionality for various uses'],
        ];

        // Category-specific features
        $categoryFeatures = [
            'Electronics' => [
                ['title' => 'Fast Charging', 'value' => 'Quick charge technology'],
                ['title' => 'Water Resistant', 'value' => 'IP67 waterproof rating'],
                ['title' => 'High Resolution', 'value' => 'Crystal clear display'],
            ],
            'Fashion' => [
                ['title' => 'Breathable Fabric', 'value' => 'Keeps you cool and comfortable'],
                ['title' => 'Stretchable', 'value' => 'Flexible material for better fit'],
                ['title' => 'Wrinkle Resistant', 'value' => 'Maintains shape after washing'],
            ],
            'Groceries' => [
                ['title' => 'Organic Certified', 'value' => '100% organic ingredients'],
                ['title' => 'Freshly Packed', 'value' => 'Packed within hours of harvest'],
                ['title' => 'No Preservatives', 'value' => 'Free from artificial additives'],
            ],
        ];

        $allFeatures = array_merge($features, $categoryFeatures[$category] ?? []);
        shuffle($allFeatures);

        return array_slice($allFeatures, 0, rand(3, 5));
    }

    /**
     * Generate warranty information
     */
    private function generateWarrantyInfo()
    {
        $warrantyPeriods = ['1 year', '2 years', '3 years', '5 years', 'Lifetime'];

        return [
            [
                'title' => 'Warranty Period',
                'value' => $warrantyPeriods[rand(0, 4)]
            ],
            [
                'title' => 'Customer Support',
                'value' => '24/7 email and phone support'
            ],
            [
                'title' => 'Return Policy',
                'value' => '30-day money back guarantee'
            ],
            [
                'title' => 'Service Centers',
                'value' => 'Available in all major cities'
            ],
        ];
    }

    /**
     * Helper methods
     */
    private function getRandomBrand()
    {
        $brands = ['Apple', 'Samsung', 'Nike', 'Adidas', 'Sony', 'LG', 'Levi\'s', 'Puma'];
        return $brands[array_rand($brands)];
    }

    private function getRandomMaterial($category)
    {
        $materials = [
            'Electronics' => ['Aluminum', 'Glass', 'Plastic', 'Stainless Steel'],
            'Fashion' => ['Cotton', 'Polyester', 'Denim', 'Leather', 'Wool'],
            'Home & Kitchen' => ['Stainless Steel', 'Ceramic', 'Wood', 'Glass', 'Marble'],
            'Sports & Fitness' => ['Rubber', 'Foam', 'Nylon', 'Mesh', 'Silicone'],
        ];

        return $materials[$category][array_rand($materials[$category] ?? ['Premium Material'])] ?? 'High-Quality Material';
    }

    private function getRandomColor()
    {
        $colors = ['Red', 'Blue', 'Black', 'White', 'Silver', 'Gold', 'Gray', 'Green', 'Brown'];
        return $colors[array_rand($colors)];
    }

    private function getRandomDimensions($category)
    {
        if ($category === 'Electronics') {
            return rand(4, 15) . ' inches';
        } elseif ($category === 'Fashion') {
            return ['S', 'M', 'L', 'XL', 'XXL'][rand(0, 4)];
        } else {
            return rand(10, 100) . ' × ' . rand(10, 100) . ' × ' . rand(5, 50) . ' cm';
        }
    }
}
