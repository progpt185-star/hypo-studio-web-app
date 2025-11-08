<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Customer;
use App\Models\Order;
use App\Services\KMeansService;

class KMeansServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_kmeans_service_runs_and_returns_mapping()
    {
        // create customers with simple orders
        for ($i = 0; $i < 10; $i++) {
            $c = Customer::create([
                'name' => 'Test C' . $i,
                'phone' => '081' . rand(1000000, 9999999),
                'email' => 'test' . $i . '@example.com',
                'address' => 'Address ' . $i,
            ]);
            // one or more orders
            $orders = rand(1, 5);
            for ($o = 0; $o < $orders; $o++) {
                Order::create([
                    'customer_id' => $c->id,
                    'order_date' => now()->subDays(rand(0, 200))->toDateString(),
                    'product_type' => 'Paket A',
                    'quantity' => rand(1,10),
                    'total_price' => rand(50000, 500000),
                ]);
            }
        }

        $svc = new KMeansService();
        $res = $svc->analyze(['k' => 2]);

        $this->assertArrayHasKey('mapping', $res);
        $this->assertCount(10, $res['customerIds']);
        $this->assertCount(10, $res['raw']);
    }
}
