<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run()
    {
        $shopOwners = User::where('type', UserType::DELIVERY)->get();

        $shops = [
            [
                'name' => 'Fresh Mart Grocery',
                'email' => 'freshmart@example.com',
                'address' => '123 Main Street, Cityville',
                'owner_name' => 'John Doe',
                'owner_phone' => '9876543211',
                'latitude' => '40.7128',
                'longitude' => '-74.0060',
                'commission_percentage' => 10,
                'status' => true,
                'deactivation_status' => false,
            ],
            [
                'name' => 'Tech Gadgets Store',
                'email' => 'techgadgets@example.com',
                'address' => '456 Tech Park, Tech City',
                'owner_name' => 'Jane Smith',
                'owner_phone' => '9876543213',
                'latitude' => '37.7749',
                'longitude' => '-122.4194',
                'commission_percentage' => 12,
                'status' => true,
                'deactivation_status' => false,
            ],
            [
                'name' => 'Fashion Boutique',
                'email' => 'fashion@example.com',
                'address' => '789 Fashion Ave, Style City',
                'owner_name' => 'Alice Johnson',
                'owner_phone' => '9876543215',
                'latitude' => '34.0522',
                'longitude' => '-118.2437',
                'commission_percentage' => 8,
                'status' => true,
                'deactivation_status' => false,
            ],
            [
                'name' => 'Home Essentials',
                'email' => 'home@example.com',
                'address' => '101 Home Lane, Comfort City',
                'owner_name' => 'Bob Williams',
                'owner_phone' => '9876543217',
                'latitude' => '51.5074',
                'longitude' => '-0.1278',
                'commission_percentage' => 9,
                'status' => true,
                'deactivation_status' => false,
            ],
            [
                'name' => 'Sports Outlet',
                'email' => 'sports@example.com',
                'address' => '202 Sports Road, Active City',
                'owner_name' => 'Charlie Brown',
                'owner_phone' => '9876543219',
                'latitude' => '48.8566',
                'longitude' => '2.3522',
                'commission_percentage' => 11,
                'status' => true,
                'deactivation_status' => false,
            ],
        ];

        foreach ($shops as $index => $shopData) {
            if (isset($shopOwners[$index])) {
                Shop::updateOrCreate(
                    ['email' => $shopData['email']],
                    array_merge($shopData, [
                        'user_id' => $shopOwners[$index]->id,
                        'image' => 'avatar.jpg',
                    ])
                );
            }
        }
    }
}
