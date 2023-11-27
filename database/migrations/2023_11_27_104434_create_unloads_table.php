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
            $table->integer('total_seed_bins')->default(0);
            $table->string('weight_seed_bins', 50)->nullable();
            $table->integer('total_oversize_bins')->default(0);
            $table->string('weight_oversize_bins', 50)->nullable();
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
