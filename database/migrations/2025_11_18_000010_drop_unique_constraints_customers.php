<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('customers', function (Blueprint $table) {
            // Hapus unique constraint jika ada
            $table->dropUnique(['email']);
            $table->dropUnique(['phone']);
            // Jika ada index unique lain, tambahkan di sini
            // Contoh: $table->dropUnique(['address']);
        });
    }
    public function down() {
        Schema::table('customers', function (Blueprint $table) {
            // Restore unique constraint jika perlu
            $table->unique('email');
            $table->unique('phone');
            // Jika ada index unique lain, tambahkan di sini
        });
    }
};
