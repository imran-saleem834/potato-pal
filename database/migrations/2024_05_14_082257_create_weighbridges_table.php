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
        Schema::create('weighbridges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unload_id');
            $table->string('channel', 30)->nullable();
            $table->float('bin_size')->nullable();
            $table->tinyInteger('system')->nullable();
            $table->float('no_of_bins')->nullable();
            $table->float('weight')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weighbridges');
    }
};
