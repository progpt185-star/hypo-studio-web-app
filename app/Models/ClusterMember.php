<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClusterMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'cluster_id',
        'customer_id',
        'cluster_number',
        'frequency',
        'total_spent'
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
