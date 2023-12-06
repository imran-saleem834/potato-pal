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
        Schema::create('remaining_receivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grower_id');
            $table->string('unique_key', 80)->nullable();
            $table->text('receival_id')->nullable();
            $table->float('oversize_bin_size')->default(0);
            $table->float('seed_bin_size')->default(0);
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
