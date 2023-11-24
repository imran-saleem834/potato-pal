<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
//            $table->text('grower_groups')->nullable();
            $table->text('paddocks')->nullable();
//            $table->text('seed_variety')->nullable();
//            $table->text('seed_generation')->nullable();
//            $table->text('seed_class')->nullable();

            $table->string('tia_sample_id', 100)->nullable();

            $table->string('unloading_status', 40)->default('pending');
            $table->integer('unloading_id')->nullable();
//            $table->text('seed_type')->nullable();
            $table->tinyInteger('oversize_bin_size')->nullable();
            $table->tinyInteger('seed_bin_size')->nullable();


            $table->string('transport', 150)->nullable();
//            $table->text('delivery_type')->nullable();
            $table->string('grower_docket_no', 50)->nullable();
            $table->string('chc_receival_docket_no', 50)->nullable();
//            $table->text('fungicide')->nullable();
            $table->string('driver_name', 80)->nullable();
            $table->string('comments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivals');
    }
};
