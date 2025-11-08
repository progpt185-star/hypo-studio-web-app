<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cluster_members', function (Blueprint $table) {
            $table->integer('cluster_number')->default(1)->after('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('cluster_members', function (Blueprint $table) {
            $table->dropColumn('cluster_number');
        });
    }
};
