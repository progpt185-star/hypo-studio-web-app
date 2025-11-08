<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Cluster;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClusteringStatsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $cluster;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        // Create test data with known patterns
        $this->createTestData();
        
        // Create cluster
        $this->actingAs($this->user);
        $response = $this->post('/clustering/analyze', [
            'k' => 2,
            'seed' => 42
        ]);
        $this->cluster = Cluster::latest()->first();
    }

    protected function createTestData()
    {
        // Create high value customers
        for ($i = 0; $i < 3; $i++) {
            $customer = Customer::factory()->create();
            Order::factory()->count(5)->create([
                'customer_id' => $customer->id,
                'total_price' => 1000000,
                'order_date' => Carbon::today()->subDays(rand(1, 7)),
                'product_type' => 'Premium'
            ]);
        }

        // Create low value customers
        for ($i = 0; $i < 2; $i++) {
            $customer = Customer::factory()->create();
            Order::factory()->create([
                'customer_id' => $customer->id,
                'total_price' => 100000,
                'order_date' => Carbon::today()->subDays(30),
                'product_type' => 'Basic'
            ]);
        }
    }

    public function test_cluster_statistics()
    {
        $this->actingAs($this->user);
        $response = $this->get("/clustering/results/{$this->cluster->id}");
        
        $response->assertStatus(200);
        $response->assertViewHas('statistics');
        
        $statistics = $response->viewData('statistics');
        
        // Check basic stats structure
        $this->assertArrayHasKey('averageOrderValue', $statistics);
        $this->assertArrayHasKey('memberCount', $statistics);
        $this->assertArrayHasKey('productTypes', $statistics);
        
        // Verify member count
        $this->assertEquals(5, array_sum($statistics['memberCount']));
    }

    public function test_chart_data_structure()
    {
        $this->actingAs($this->user);
        $response = $this->get("/clustering/charts/{$this->cluster->id}");
        
        $response->assertStatus(200);
        $data = $response->json();
        
        // Check main chart data
        $this->assertArrayHasKey('scatter', $data);
        $this->assertArrayHasKey('rfm', $data);
        
        // Check scatter plot data
        foreach ($data['scatter'] as $dataset) {
            $this->assertArrayHasKey('label', $dataset);
            $this->assertArrayHasKey('data', $dataset);
            $this->assertIsArray($dataset['data']);
        }
        
        // Check RFM chart data
        foreach ($data['rfm'] as $cluster) {
            $this->assertArrayHasKey('recency', $cluster);
            $this->assertArrayHasKey('frequency', $cluster);
            $this->assertArrayHasKey('monetary', $cluster);
        }
    }

    public function test_product_type_distribution()
    {
        $this->actingAs($this->user);
        $response = $this->get("/clustering/results/{$this->cluster->id}");
        
        $response->assertStatus(200);
        $statistics = $response->viewData('statistics');
        
        // Check product type stats
        $this->assertArrayHasKey('productTypes', $statistics);
        $productTypes = $statistics['productTypes'];
        
        // Verify we have both Premium and Basic products
        $this->assertContains('Premium', array_keys($productTypes));
        $this->assertContains('Basic', array_keys($productTypes));
    }

    public function test_cluster_performance_metrics()
    {
        $this->actingAs($this->user);
        $response = $this->get("/clustering/metrics/{$this->cluster->id}");
        
        $response->assertStatus(200);
        $metrics = $response->json();
        
        // Check key metrics
        $this->assertArrayHasKey('silhouette', $metrics);
        $this->assertArrayHasKey('inertia', $metrics);
        $this->assertArrayHasKey('distortion', $metrics);
        
        // Validate metric ranges
        $this->assertIsFloat($metrics['silhouette']);
        $this->assertGreaterThanOrEqual(-1, $metrics['silhouette']);
        $this->assertLessThanOrEqual(1, $metrics['silhouette']);
        
        $this->assertIsFloat($metrics['inertia']);
        $this->assertGreaterThanOrEqual(0, $metrics['inertia']);
    }
}