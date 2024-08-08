<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('allocation_items', function (Blueprint $table) {
            $table->id();
            $table->morphs('allocatable');
            $table->nullableMorphs('foreignable');
            $table->foreignId('returned_id')->nullable();
            $table->float('weight')->nullable();
            $table->float('from_half_tonnes')->nullable();
            $table->float('from_one_tonnes')->nullable();
            $table->float('from_two_tonnes')->nullable();
            $table->float('half_tonnes')->nullable();
            $table->float('one_tonnes')->nullable();
            $table->float('two_tonnes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocation_items');
    }
};
