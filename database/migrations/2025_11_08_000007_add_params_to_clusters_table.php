<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clusters', function (Blueprint $table) {
            $table->json('params')->nullable()->after('description');
            $table->json('labels')->nullable()->after('params');
            $table->integer('seed')->nullable()->after('k_value');
        });
    }

    public function down(): void
    {
        Schema::table('clusters', function (Blueprint $table) {
            $table->dropColumn(['params', 'labels', 'seed']);
        });
    }
};
