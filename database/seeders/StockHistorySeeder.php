<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockHistorySeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();
        $users = User::all();

        $types = ['Addition', 'Sale', 'Restock'];

        foreach ($products as $product) {
            $historyCount = rand(3, 8);
            $currentStock = $product->product_stock;

            for ($i = 0; $i < $historyCount; $i++) {
                $type = $types[array_rand($types)];
                $quantity = rand(1, 20);
                // $previousStock = $currentStock;

                switch ($type) {
                    case 'Addition':
                    case 'Restock':
                        $currentStock += $quantity;
                        break;
                    case 'Sale':
                        $currentStock = max(0, $currentStock - $quantity);
                        break;
                }

                // StockHistory::create([
                //     'product_id' => $product->id,
                //     'user_id' => $users->random()->id,
                //     'type' => $type,
                //     'quantity' => $quantity,
                //     'created_at' => now()->subDays(rand(0, 30)),
                // ]);
            }
        }
    }
}
