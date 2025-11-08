<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Cluster;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClusteringParametersTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $baseData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        // Create base test data
        $this->setupBaseData();
    }

    protected function setupBaseData()
    {
        // Create consistent test data
        for ($i = 0; $i < 5; $i++) {
            $customer = Customer::factory()->create();
            Order::factory()->count(3)->create([
                'customer_id' => $customer->id,
                'total_price' => 100 * ($i + 1),
                'order_date' => Carbon::today()->subDays($i * 7)
            ]);
        }
    }

    public function test_reproducible_results_with_same_parameters()
    {
        $this->actingAs($this->user);
        
        // First analysis
        $response1 = $this->post('/clustering/analyze', [
            'k' => 2,
            'seed' => 42,
            'features' => ['recency', 'frequency', 'monetary']
        ]);
        $cluster1 = Cluster::latest()->first();
        
        // Second analysis with same parameters
        $response2 = $this->post('/clustering/analyze', [
            'k' => 2,
            'seed' => 42,
            'features' => ['recency', 'frequency', 'monetary']
        ]);
        $cluster2 = Cluster::latest()->first();
        
        // Get results for both analyses
        $results1 = $this->get("/clustering/results/{$cluster1->id}")->viewData('groupedMembers');
        $results2 = $this->get("/clustering/results/{$cluster2->id}")->viewData('groupedMembers');
        
        // Compare results
        $this->assertEquals(
            $this->normalizeResults($results1),
            $this->normalizeResults($results2)
        );
    }

    protected function normalizeResults($results)
    {
        // Sort members within each cluster for consistent comparison
        $normalized = [];
        foreach ($results as $clusterNum => $members) {
            $memberIds = collect($members)->pluck('customer_id')->sort()->values()->toArray();
            $normalized[$clusterNum] = $memberIds;
        }
        ksort($normalized);
        return $normalized;
    }

    public function test_different_features_give_different_results()
    {
        $this->actingAs($this->user);
        
        // Analysis with all features
        $response1 = $this->post('/clustering/analyze', [
            'k' => 2,
            'seed' => 42,
            'features' => ['recency', 'frequency', 'monetary']
        ]);
        $cluster1 = Cluster::latest()->first();
        
        // Analysis with only monetary
        $response2 = $this->post('/clustering/analyze', [
            'k' => 2,
            'seed' => 42,
            'features' => ['monetary']
        ]);
        $cluster2 = Cluster::latest()->first();
        
        // Get results
        $results1 = $this->get("/clustering/results/{$cluster1->id}")->viewData('groupedMembers');
        $results2 = $this->get("/clustering/results/{$cluster2->id}")->viewData('groupedMembers');
        
        // Results should be different
        $this->assertNotEquals(
            $this->normalizeResults($results1),
            $this->normalizeResults($results2)
        );
    }

    public function test_parameter_validation()
    {
        $this->actingAs($this->user);
        
        // Test invalid k
        $response = $this->post('/clustering/analyze', [
            'k' => 0
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('k');
        
        // Test invalid features
        $response = $this->post('/clustering/analyze', [
            'k' => 2,
            'features' => ['invalid_feature']
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('features');
        
        // Test k > number of customers
        $response = $this->post('/clustering/analyze', [
            'k' => 10 // We only have 5 customers
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('k');
    }

    public function test_rerun_analysis_with_same_parameters()
    {
        $this->actingAs($this->user);
        
        // Initial analysis
        $response = $this->post('/clustering/analyze', [
            'k' => 2,
            'seed' => 42
        ]);
        $cluster = Cluster::latest()->first();
        
        // Rerun analysis
        $response = $this->get("/clustering/rerun/{$cluster->id}");
        $response->assertStatus(302);
        
        $newCluster = Cluster::latest()->first();
        
        // Check parameters were copied
        $this->assertEquals($cluster->k_value, $newCluster->k_value);
        $this->assertEquals($cluster->seed, $newCluster->seed);
        $this->assertEquals($cluster->features, $newCluster->features);
        
        // Results should match
        $results1 = $this->get("/clustering/results/{$cluster->id}")->viewData('groupedMembers');
        $results2 = $this->get("/clustering/results/{$newCluster->id}")->viewData('groupedMembers');
        
        $this->assertEquals(
            $this->normalizeResults($results1),
            $this->normalizeResults($results2)
        );
    }
}