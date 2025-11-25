<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_date',
        'product_type',
        'color',
        'size',
        'category',
        'quantity',
        'total_price'
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    public function scopeByMonth($query, $month, $year)
    {
        return $query->whereYear('order_date', $year)
                     ->whereMonth('order_date', $month);
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
}
