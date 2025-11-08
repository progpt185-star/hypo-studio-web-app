<?php

namespace Database\Factories;

use App\Models\ClusterMember;
use App\Models\Cluster;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClusterMemberFactory extends Factory
{
    protected $model = ClusterMember::class;

    public function definition()
    {
        return [
            'cluster_id' => Cluster::factory(),
            'customer_id' => Customer::factory(),
            'cluster_number' => $this->faker->numberBetween(1, 5),
            'frequency' => $this->faker->numberBetween(1, 20),
            'total_spent' => $this->faker->numberBetween(100000, 5000000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}