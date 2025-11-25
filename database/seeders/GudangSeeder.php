<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gudang;

class GudangSeeder extends Seeder
{
    public function run()
    {
        Gudang::create([
            'product_type' => 'Cotton Combed 24s DTF',
            'color' => 'White',
            'size' => 'M',
            'category' => 'lengan pendek',
            'qty' => 100,
        ]);

        Gudang::create([
            'product_type' => 'CVC Lacoste 24s',
            'color' => 'Black',
            'size' => 'L',
            'category' => 'lengan panjang',
            'qty' => 50,
        ]);
    }
}
