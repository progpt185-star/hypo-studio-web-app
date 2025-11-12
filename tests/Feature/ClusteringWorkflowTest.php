<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Cluster;
use App\Models\ClusterMember;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClusteringWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_full_clustering_workflow()
    {
        $this->actingAs($this->user);

        // 1. Create test data
        $customers = Customer::factory()->count(10)->create();
        foreach ($customers as $customer) {
            Order::factory()->count(rand(2, 5))->create([
                'customer_id' => $customer->id,
                'order_date' => now()->subDays(rand(1, 30)),
                'total_price' => rand(100000, 1000000),
                'product_type' => ['Kaos', 'Kemeja', 'Jaket'][rand(0, 2)]
            ]);
        }

        // 2. Test analysis endpoint
        $response = $this->post('/clustering/analyze', [
            'k' => 3,
            'seed' => 42
        ]);

        $response->assertStatus(302); // Redirect
        $this->assertDatabaseHas('clusters', [
            'k_value' => 3,
            'created_by' => $this->user->id
        ]);

        $cluster = Cluster::latest()->first();
        $this->assertNotNull($cluster);

        // 3. Test results page
        $response = $this->get("/clustering/results/{$cluster->id}");
        $response->assertStatus(200);
        $response->assertViewIs('clustering.results');
        $response->assertViewHas(['cluster', 'statistics', 'groupedMembers', 'productTypes']);

        // 4. Test cluster member assignments
        $this->assertDatabaseHas('cluster_members', [
            'cluster_id' => $cluster->id
        ]);

        // 5. Test label update
        $response = $this->post("/clustering/{$cluster->id}/update-label", [
            'cluster_number' => 1,
            'label' => 'Loyal'
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'label' => 'Loyal'
        ]);

        // 6. Test exports
        $response = $this->get("/clustering/export/{$cluster->id}?format=xlsx");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $response = $this->get("/clustering/export/{$cluster->id}?format=csv");
    $response->assertStatus(200);
    // Accept content-type that contains text/csv (charset may be present)
    $this->assertStringContainsString('text/csv', strtolower($response->headers->get('content-type')));

        $response = $this->get("/clustering/pdf/{$cluster->id}");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        // 7. Test rerun functionality
        $response = $this->get("/clustering/rerun/{$cluster->id}");
        $response->assertStatus(302); // Redirect
        $this->assertDatabaseHas('clusters', [
            'k_value' => 3,
            'created_by' => $this->user->id
        ]);
    }

    public function test_invalid_k_value_is_rejected()
    {
        $this->actingAs($this->user);

        $response = $this->post('/clustering/analyze', [
            'k' => 1 // Invalid: minimum is 2
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('k');
    }

    public function test_missing_data_shows_error()
    {
        $this->actingAs($this->user);

        // No customers/orders in database
        $response = $this->post('/clustering/analyze', [
            'k' => 3
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    public function test_unauthorized_access_is_redirected()
    {
        $cluster = Cluster::factory()->create();

        $response = $this->get("/clustering/results/{$cluster->id}");
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}