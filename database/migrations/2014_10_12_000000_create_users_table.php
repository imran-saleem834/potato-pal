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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('email', 90)->unique()->nullable();
            $table->string('username', 50)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('role', 50)->nullable();
            $table->string('access')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('grower_name', 50)->nullable();
            $table->text('grower_tags')->nullable();
            $table->string('buyer_name', 50)->nullable();
            $table->text('buyer_tags')->nullable();
            $table->text('paddocks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
