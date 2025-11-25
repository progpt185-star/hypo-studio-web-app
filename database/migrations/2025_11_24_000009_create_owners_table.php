<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('owners')) {
            Schema::create('owners', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('company_name')->nullable();
                $table->string('contact')->nullable();
                $table->string('phone')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // defensive: avoid dropping table here to prevent accidental data loss
        if (Schema::hasTable('owners')) {
            // no-op
        }
    }
};
