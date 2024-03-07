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
        Schema::create('tia_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receival_id');
            $table->float('processor')->nullable();
            $table->string('inspection_no', 20)->nullable();
            $table->date('inspection_date')->nullable();
            $table->string('size', 50)->nullable();
            $table->string('tubers', 255)->nullable();
            $table->string('dry_rot', 255)->nullable();
            $table->string('black_scurf', 255)->nullable();
            $table->string('powdery_scab', 255)->nullable();
            $table->string('root_knot_nematode', 255)->nullable();
            $table->string('soft_rots', 255)->nullable();
            $table->string('pink_rot', 255)->nullable();
            $table->string('sub_total', 255)->nullable();
            $table->string('common_scab', 255)->nullable();
            $table->string('total_disease', 255)->nullable();
            $table->string('black_scurf_scatter', 255)->nullable();
            $table->string('insect_damage', 255)->nullable();
            $table->string('malformed_tubers', 255)->nullable();
            $table->string('mechanical_damage', 255)->nullable();
            $table->string('stem_end_discolour', 255)->nullable();
            $table->string('foreign_cultivars', 255)->nullable();
            $table->string('misc_frost', 255)->nullable();
            $table->string('total_defects', 255)->nullable();
            $table->string('minimal_insect_feeding', 255)->nullable();
            $table->string('oversize', 255)->nullable();
            $table->string('undersize', 255)->nullable();
            $table->tinyInteger('disease_scoring')->nullable();
            $table->string('disease_powdery_scab', 255)->nullable();
            $table->string('disease_root_knot_nematode', 255)->nullable();
            $table->string('disease_common_scab', 255)->nullable();
            $table->boolean('excessive_dirt')->default(false);
            $table->boolean('minor_skin_cracking')->default(false);
            $table->boolean('skinning')->default(false);
            $table->boolean('regarding')->default(false);
            $table->string('comment', 255)->nullable();
            $table->text('images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tia_samples');
    }
};
