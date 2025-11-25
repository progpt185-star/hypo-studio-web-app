<?php

namespace Database\Factories;

use App\Models\Cluster;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClusterFactory extends Factory
{
    protected $model = Cluster::class;

    public function definition()
    {
        return [
            'k_value' => $this->faker->numberBetween(2, 5),
            'name' => "K-Means Analysis (K={$this->faker->numberBetween(2, 5)})",
            'description' => json_encode([]),
            'created_by' => User::factory(),
            'analysis_date' => now(),
            'params' => json_encode([
                // Default features: use orders count as a reliable feature
                'features' => ['orders'],
                'seed' => $this->faker->randomNumber(),
            ]),
            'labels' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}