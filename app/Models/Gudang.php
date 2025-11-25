<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    use HasFactory;

    protected $table = 'gudangs';

    protected $fillable = [
        'product_type',
        'color',
        'size',
        'category',
        'qty',
    ];

    public $timestamps = true;

    // Decrease stock safely
    public function decreaseQty(int $amount = 1)
    {
        $this->qty = max(0, $this->qty - $amount);
        $this->save();
    }
}
