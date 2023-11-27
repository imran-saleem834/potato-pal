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
            $table->foreignId('user_id');
            $table->string('paddocks')->nullable();
            $table->string('tia_sample_status', 40)->default('pending');
            $table->string('tia_sample_id', 100)->nullable();
            $table->string('unloading_status', 40)->default('pending');
            $table->string('unloading_id')->nullable();
            $table->string('oversize_bin_size', 20)->nullable();
            $table->string('seed_bin_size', 20)->nullable();
            $table->string('grower_docket_no', 50)->nullable();
            $table->string('chc_receival_docket_no', 50)->nullable();
            $table->string('attachment', 50)->nullable();
            $table->string('driver_name', 80)->nullable();
            $table->string('comments')->nullable();
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
