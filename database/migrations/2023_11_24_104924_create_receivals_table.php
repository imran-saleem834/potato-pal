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
            $table->string('paddocks')->nullable();
            $table->string('grower_docket_no', 50)->nullable();
            $table->string('chc_receival_docket_no', 50)->nullable();
            $table->string('driver_name', 80)->nullable();
            $table->string('comments')->nullable();
            $table->string('status', 20)->nullable();
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
