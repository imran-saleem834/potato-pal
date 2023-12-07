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
        Schema::create('unloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receival_id');
            $table->float('total_seed_bins')->nullable();
            $table->float('weight_seed_bins')->nullable();
            $table->float('total_oversize_bins')->nullable();
            $table->float('weight_oversize_bins')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unloads');
    }
};
