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
        Schema::create('bulk_baggings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id');
            $table->foreignId('allocation_id');
            $table->string('bins_tipped')->nullable();
            $table->float('weight')->nullable();
            $table->float('no_of_bulk_bags_out')->nullable();
            $table->float('net_weight_bags_out')->nullable();
            $table->time('start')->nullable();
            $table->time('end')->nullable();
            $table->integer('no_of_crew')->nullable();
            $table->string('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_baggings');
    }
};
