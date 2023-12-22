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
        Schema::create('cutting_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cutting_id');
            $table->foreignId('allocation_id');
            $table->float('no_of_bins_before_cutting');
            $table->float('weight_before_cutting');
            $table->float('no_of_bins_after_cutting')->nullable();
            $table->float('weight_after_cutting')->nullable();
            $table->float('bin_size_after_cutting')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cutting_allocations');
    }
};
