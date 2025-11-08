<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;

class ConvectionSeeder extends Seeder
{
    public function run(): void
    {
        $products = ['Paket A', 'Paket B', 'Paket C', 'Paket Premium', 'Custom Order'];

        // Create several behavioral groups
        $groups = [
            ['name' => 'loyal', 'count' => 30, 'minOrders' => 20, 'maxOrders' => 40, 'minPrice' => 200000, 'maxPrice' => 800000],
            ['name' => 'regular', 'count' => 60, 'minOrders' => 8, 'maxOrders' => 20, 'minPrice' => 50000, 'maxPrice' => 300000],
            ['name' => 'sporadic', 'count' => 80, 'minOrders' => 2, 'maxOrders' => 8, 'minPrice' => 30000, 'maxPrice' => 200000],
            ['name' => 'churn', 'count' => 30, 'minOrders' => 5, 'maxOrders' => 15, 'minPrice' => 30000, 'maxPrice' => 200000, 'older' => true],
        ];

        foreach ($groups as $gIdx => $g) {
            for ($i = 0; $i < $g['count']; $i++) {
                $name = ucfirst($g['name']) . ' Customer ' . ($i + 1) . ' G' . ($gIdx + 1);
                $customer = Customer::create([
                    'name' => $name,
                    'phone' => '08' . rand(100000000, 999999999),
                    'email' => 'customer+' . $gIdx . $i . '@example.com',
                    'address' => 'Jl. Contoh No. ' . rand(1, 200),
                ]);

                $orderCount = rand($g['minOrders'], $g['maxOrders']);
                for ($o = 0; $o < $orderCount; $o++) {
                    $daysAgo = $g['older'] ?? false ? rand(200, 720) : rand(0, 365);
                    Order::create([
                        'customer_id' => $customer->id,
                        'order_date' => Carbon::now()->subDays($daysAgo)->toDateString(),
                        'product_type' => $products[array_rand($products)],
                        'quantity' => rand(1, 30),
                        'total_price' => rand($g['minPrice'], $g['maxPrice']),
                    ]);
                }
            }
        }
    }
}
