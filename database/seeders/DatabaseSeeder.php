<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🧍‍♂️ Seed Admin User
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Hypo Studio',
                'email' => 'admin@hypo-studio.local',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 👥 Seed 10 Dummy Customers
        $customers = [
            ['name' => 'Ahmad Wijaya',  'phone' => '081234567890', 'email' => 'ahmad@example.com',  'address' => 'Jl. Merdeka No. 10, Jakarta'],
            ['name' => 'Budi Santoso',  'phone' => '081234567891', 'email' => 'budi@example.com',   'address' => 'Jl. Sudirman No. 20, Jakarta'],
            ['name' => 'Citra Dewi',    'phone' => '081234567892', 'email' => 'citra@example.com',  'address' => 'Jl. Gatot Subroto No. 30, Jakarta'],
            ['name' => 'Doni Hermawan', 'phone' => '081234567893', 'email' => 'doni@example.com',   'address' => 'Jl. Diponegoro No. 40, Jakarta'],
            ['name' => 'Eka Putri',     'phone' => '081234567894', 'email' => 'eka@example.com',    'address' => 'Jl. Ahmad Yani No. 50, Jakarta'],
            ['name' => 'Farhan Akbar',  'phone' => '081234567895', 'email' => 'farhan@example.com', 'address' => 'Jl. Imam Bonjol No. 60, Bandung'],
            ['name' => 'Gita Pratiwi',  'phone' => '081234567896', 'email' => 'gita@example.com',   'address' => 'Jl. Siliwangi No. 70, Bandung'],
            ['name' => 'Hendra Saputra','phone' => '081234567897', 'email' => 'hendra@example.com', 'address' => 'Jl. Asia Afrika No. 80, Bandung'],
            ['name' => 'Intan Lestari', 'phone' => '081234567898', 'email' => 'intan@example.com',  'address' => 'Jl. Braga No. 90, Bandung'],
            ['name' => 'Joko Purnomo',  'phone' => '081234567899', 'email' => 'joko@example.com',   'address' => 'Jl. Cihampelas No. 100, Bandung'],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(['phone' => $customer['phone']], $customer);
        }

        // 🧾 Seed Dummy Orders (lebih realistis)
        $customers = Customer::all();
        $products = ['Paket A', 'Paket B', 'Paket C', 'Paket Premium', 'Custom Order'];

        foreach ($customers as $customer) {
            // Setiap pelanggan punya 5–12 pesanan acak
            $orderCount = rand(5, 12);

            for ($i = 0; $i < $orderCount; $i++) {
                $orderDate = Carbon::now()->subDays(rand(0, 180));
                $productType = $products[array_rand($products)];
                $qty = rand(1, 10);
                $price = rand(75000, 350000);
                $total = $qty * $price;

                Order::create([
                    'customer_id' => $customer->id,
                    'order_date' => $orderDate,
                    'product_type' => $productType,
                    'quantity' => $qty,
                    'total_price' => $total,
                ]);
            }
        }

        $this->command->info('✅ Database seeding completed successfully! (10 pelanggan & pesanan acak)');

        // 🔄 Jika ada ConvectionSeeder, jalankan — kalau tidak ada, lewati saja
        if (class_exists(\Database\Seeders\ConvectionSeeder::class)) {
            $this->call(\Database\Seeders\ConvectionSeeder::class);
        }
    }
}
