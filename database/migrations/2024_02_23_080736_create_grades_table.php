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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unload_id');
            $table->string('category')->nullable();
            $table->string('bins_tipped')->nullable();
            $table->string('whole_seed')->nullable();
            $table->string('oversize')->nullable();
            $table->string('round')->nullable();
            $table->string('cut_sets')->nullable();
            $table->float('waste')->nullable();
            $table->float('no_of_bulk_bags_out')->nullable();
            $table->float('net_weight_bags_out')->nullable();
            $table->boolean('fungicide')->default(false);
            $table->float('fungicide_used')->nullable();
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
        Schema::dropIfExists('grades');
    }
};
