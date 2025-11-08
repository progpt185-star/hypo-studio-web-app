<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Admin User
        User::firstOrCreate([
            'username' => 'admin',
        ], [
            'name' => 'Admin Hypo Studio',
            'email' => 'admin@hypo-studio.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Seed Dummy Customers
        $customers = [
            ['name' => 'Ahmad Wijaya', 'phone' => '081234567890', 'email' => 'ahmad@example.com', 'address' => 'Jl. Merdeka No. 10, Jakarta'],
            ['name' => 'Budi Santoso', 'phone' => '081234567891', 'email' => 'budi@example.com', 'address' => 'Jl. Sudirman No. 20, Jakarta'],
            ['name' => 'Citra Dewi', 'phone' => '081234567892', 'email' => 'citra@example.com', 'address' => 'Jl. Gatot Subroto No. 30, Jakarta'],
            ['name' => 'Doni Hermawan', 'phone' => '081234567893', 'email' => 'doni@example.com', 'address' => 'Jl. Diponegoro No. 40, Jakarta'],
            ['name' => 'Eka Putri', 'phone' => '081234567894', 'email' => 'eka@example.com', 'address' => 'Jl. Ahmad Yani No. 50, Jakarta'],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(['phone' => $customer['phone']], $customer);
        }

        // Seed Dummy Orders
        $customers = Customer::all();
        $products = ['Paket A', 'Paket B', 'Paket C', 'Paket Premium', 'Custom Order'];

        foreach ($customers as $customer) {
            // Generate 5-15 orders per customer
            $orderCount = rand(5, 15);
            
            for ($i = 0; $i < $orderCount; $i++) {
                Order::create([
                    'customer_id' => $customer->id,
                    'order_date' => Carbon::now()->subDays(rand(0, 180)),
                    'product_type' => $products[array_rand($products)],
                    'quantity' => rand(1, 10),
                    'total_price' => rand(50000, 500000),
                ]);
            }
        }

        $this->command->info('Database seeding completed successfully!');

        // Additional realistic convection data
        $this->call(\Database\Seeders\ConvectionSeeder::class);
    }
}
