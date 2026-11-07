<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@srmresidence.in'],
            [
                'name' => 'SRM Admin',
                'type' => UserType::SUPERADMIN,
                'phone' => '9876543210',
                'status' => true,
                'image' => 'avatar.jpg',
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'type' => UserType::SUPERADMIN,
                'phone' => '9876543210',
                'status' => true,
                'image' => 'avatar.jpg',
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]
        );

        for ($i = 1; $i < 10; $i++) {
            User::updateOrCreate(
                ['email' => "user{$i}@gmail.com"],
                [
                    'name' => "User {$i}",
                    'type' => UserType::USER,
                    'phone' => '98765432' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'status' => true,
                    'image' => 'avatar.jpg',
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                ]

            );

        }

        for ($i = 1; $i <= 5; $i++) {
            User::updateOrCreate(
                ['email' => "shop{$i}@gmail.com"],
                [
                    'name' => "Shop Owner {$i}",
                    'type' => UserType::DELIVERY,
                    'phone' => '98765433' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'status' => true,
                    'image' => 'avatar.jpg',
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                ]
            );
        }

    }
}
