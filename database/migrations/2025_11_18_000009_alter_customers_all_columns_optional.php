<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name')->nullable()->default('')->change();
            $table->string('email')->nullable()->default('')->change();
            $table->string('phone')->nullable()->default('')->change();
            $table->string('address')->nullable()->default('')->change();
            // Tambahkan kolom lain sesuai struktur tabel jika ada, misal:
            // $table->integer('some_number')->nullable()->default(0)->change();
            // $table->date('some_date')->nullable()->default(null)->change();
            // $table->boolean('some_flag')->nullable()->default(false)->change();
        });
    }
    public function down() {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name')->nullable(false)->default(null)->change();
            $table->string('email')->nullable(false)->default(null)->change();
            $table->string('phone')->nullable(false)->default(null)->change();
            $table->string('address')->nullable(false)->default(null)->change();
            // Kembalikan kolom lain ke state awal jika ada
        });
    }
};
