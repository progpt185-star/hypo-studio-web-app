<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('gudangs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('product_type')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('category')->nullable(); // lengan panjang / lengan pendek
            $table->integer('qty')->default(0);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('gudangs');
    }
};
