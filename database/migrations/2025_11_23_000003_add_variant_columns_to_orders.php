<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('color')->nullable()->after('product_type');
            $table->string('size')->nullable()->after('color');
            $table->string('category')->nullable()->after('size');
        });
    }
    public function down() {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'category')) $table->dropColumn('category');
            if (Schema::hasColumn('orders', 'size')) $table->dropColumn('size');
            if (Schema::hasColumn('orders', 'color')) $table->dropColumn('color');
        });
    }
};
