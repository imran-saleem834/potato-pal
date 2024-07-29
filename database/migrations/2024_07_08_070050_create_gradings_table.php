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
        Schema::create('gradings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->morphs('gradeable');
            $table->string('bins_tipped')->nullable();
            $table->string('bins_graded')->nullable();
            $table->float('weight')->nullable();
            $table->float('waste')->nullable();
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
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
        Schema::dropIfExists('gradings');
    }
};
