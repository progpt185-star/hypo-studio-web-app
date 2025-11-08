<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'k_value',
        'created_by',
        'analysis_date',
        'params',
        'labels',
        'seed'
    ];

    protected $casts = [
        'analysis_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'params' => 'array',
        'labels' => 'array',
        'seed' => 'integer',
    ];

    public function clusterMembers()
    {
        return $this->hasMany(ClusterMember::class);
    }

    public function getStatisticsAttribute()
    {
        return [
            'total_customers' => $this->clusterMembers()->count(),
            'avg_frequency' => $this->clusterMembers()->avg('frequency'),
            'avg_spending' => $this->clusterMembers()->avg('total_spent'),
            'total_transactions' => $this->clusterMembers()->sum('frequency'),
        ];
    }
}
