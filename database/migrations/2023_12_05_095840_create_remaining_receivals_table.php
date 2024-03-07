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
        Schema::create('remaining_receivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grower_id');
            $table->string('unique_key', 100)->nullable();
            $table->text('receival_id')->nullable();
            $table->text('unload_id')->nullable();
            $table->float('no_of_bins')->default(0);
            $table->float('weight')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remaining_receivals');
    }
};
