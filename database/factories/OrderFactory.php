<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        $productTypes = ['Kaos', 'Kemeja', 'Jaket', 'Celana', 'Topi'];
        return [
            'customer_id' => Customer::factory(),
            'order_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'total_price' => $this->faker->numberBetween(50000, 1000000),
            'product_type' => $this->faker->randomElement($productTypes),
            'created_at' => now(),
              'updated_at' => now(),
              'quantity' => $this->faker->numberBetween(1, 10),
        ];
    }
}