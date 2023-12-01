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
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id');
            $table->foreignId('grower_id')->nullable();
            $table->float('allocated_bins')->nullable();
            $table->float('allocated_tonnes')->nullable();
            $table->float('tonnes_available_receivals')->nullable();
            $table->float('bins_before_cutting')->nullable();
            $table->float('tonnes_before_cutting')->nullable();
            $table->dateTime('cutting_date')->nullable();
            $table->float('bins_after_cutting')->nullable();
            $table->float('tonnes_after_cutting')->nullable();
            $table->foreignId('reallocated_buyer_id')->nullable();
            $table->float('tonnes_reallocated')->nullable();
            $table->float('bins_reallocated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
