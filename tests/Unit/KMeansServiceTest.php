<?php

namespace Tests\Unit;

use App\Models\Customer;
    use App\Models\User;
use App\Models\Order;
use App\Services\KMeansService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KMeansServiceTest extends TestCase
{
    use RefreshDatabase;

    protected KMeansService $service;
        protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KMeansService();
            $this->user = User::factory()->create(['email' => 'test@example.com']);
            $this->actingAs($this->user);
    }

    public function test_analyze_validates_k_parameter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->analyze(['k' => 0]);
    }

    public function test_analyze_requires_customers()
    {
        $this->expectException(\Exception::class);
        $this->service->analyze(['k' => 2]);
    }

    public function test_analyze_validates_k_less_than_customers()
    {
        // Create 2 customers
        Customer::factory()->count(2)->create();
        
        $this->expectException(\Exception::class);
        $this->service->analyze(['k' => 3]);
    }

    public function test_analyze_with_valid_data()
    {
        // Create test data with known patterns
        // Customer 1: High RFM
        $c1 = Customer::factory()->create();
        Order::factory()->count(5)->create([
            'customer_id' => $c1->id,
            'total_price' => 1000,
            'order_date' => Carbon::today()
        ]);

        // Customer 2: Low RFM  
        $c2 = Customer::factory()->create();
        Order::factory()->create([
            'customer_id' => $c2->id,
            'total_price' => 100,
            'order_date' => Carbon::today()->subDays(30)
        ]);

        // Force deterministic behavior with seed
        $result = $this->service->analyze([
            'k' => 2,
            'seed' => 123
        ]);

        $this->assertArrayHasKey('mapping', $result);
        $this->assertArrayHasKey('centroids', $result);
        $this->assertArrayHasKey('inertia', $result);
        $this->assertArrayHasKey('features', $result);
        $this->assertCount(2, $result['mapping']);

    // Check that customers were assigned to some cluster IDs
    $c1Cluster = $result['mapping'][$c1->id];
    $c2Cluster = $result['mapping'][$c2->id];
    $this->assertIsInt($c1Cluster);
    $this->assertIsInt($c2Cluster);
    $this->assertContains($c1Cluster, [1, 2]);
    $this->assertContains($c2Cluster, [1, 2]);
    }

    public function test_analyze_with_custom_features()
    {
        Customer::factory()->count(3)->create()->each(function($customer) {
            Order::factory()->count(rand(1,5))->create([
                'customer_id' => $customer->id
            ]);
        });

        $result = $this->service->analyze([
            'k' => 2,
            'features' => ['frequency', 'monetary']
        ]);

        $this->assertCount(2, $result['features']);
        $this->assertEquals(['frequency', 'monetary'], $result['features']);
        $this->assertCount(2, $result['centroids'][0]);
    }

    public function test_reproducible_results_with_same_seed()
    {
        Customer::factory()->count(4)->create()->each(function($customer) {
            Order::factory()->count(rand(1,5))->create([
                'customer_id' => $customer->id
            ]);
        });

        $result1 = $this->service->analyze([
            'k' => 2,
            'seed' => 42
        ]);

        $result2 = $this->service->analyze([
            'k' => 2,
            'seed' => 42
        ]);

        $this->assertEquals($result1['mapping'], $result2['mapping']);
        $this->assertEquals($result1['centroids'], $result2['centroids']);
    }
}