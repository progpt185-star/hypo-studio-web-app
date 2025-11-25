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
        // safe: only create if not exists to avoid duplicate-migration failures
        if (!Schema::hasTable('gudangs')) {
            Schema::create('gudangs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('code')->nullable()->unique();
                $table->text('address')->nullable();
                $table->integer('stock')->default(0);
                $table->integer('capacity')->nullable();
                $table->timestamps();
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
        // only drop if this migration actually created it (defensive)
        if (Schema::hasTable('gudangs')) {
            // do not drop existing table to avoid data loss in case it was created by another migration
            // keep as no-op to be safe
        }
    }
};
