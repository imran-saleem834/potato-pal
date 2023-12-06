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
        Schema::create('receivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grower_id');
            $table->string('unique_key', 80)->nullable();
            $table->string('paddocks')->nullable();
            $table->tinyInteger('oversize_bin_size')->nullable();
            $table->tinyInteger('seed_bin_size')->nullable();
            $table->string('grower_docket_no', 50)->nullable();
            $table->string('chc_receival_docket_no', 50)->nullable();
            $table->string('driver_name', 80)->nullable();
            $table->string('comments')->nullable();
            $table->text('images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivals');
    }
};
