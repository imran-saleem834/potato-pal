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
        Schema::create('reallocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id');
            $table->foreignId('allocation_buyer_id');
            $table->foreignId('allocation_id');
            $table->float('no_of_bins')->nullable();
            $table->float('weight')->nullable();
            $table->string('comment', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reallocations');
    }
};
