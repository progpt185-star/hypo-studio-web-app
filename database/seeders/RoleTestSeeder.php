<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleTestSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // Owner user
        $ownerId = DB::table('users')->insertGetId([
            'name' => 'Owner Test',
            'email' => 'owner@test.local',
            'password' => Hash::make('secret'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('owners')->insert([
            'user_id' => $ownerId,
            'contact' => 'owner@test.local',
            'notes' => 'Seeded owner for testing',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Gudang user
        $gudangId = DB::table('users')->insertGetId([
            'name' => 'Gudang Test',
            'email' => 'gudang@test.local',
            'password' => Hash::make('secret'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('gudangs')->insert([
            'user_id' => $gudangId,
            'name' => 'Gudang A',
            'product_type' => 'Sample',
            'color' => 'Red',
            'size' => 'M',
            'category' => 'Clothing',
            'qty' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->command->info('Seeded test owner and gudang users: owner@test.local / gudang@test.local (password: secret)');
    }
}
