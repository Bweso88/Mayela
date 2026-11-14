<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structure du système éducatif d'un pays, stable d'une année sur l'autre :
 *
 * PAYS → ORDRE D'ENSEIGNEMENT (préscolaire, primaire, secondaire général, technique)
 *      → CYCLE (collège, lycée — facultatif) → CLASSE
 *      → SÉRIE / SPÉCIALITÉ (rattachées aux classes via grade_track)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('name');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['country_id', 'code']);
        });

        Schema::create('education_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_stage_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('name');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['education_stage_id', 'code']);
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('education_cycle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->unsignedSmallInteger('position')->default(0);
            // Univers visuel de l'application : junior, college, lycee.
            $table->string('universe', 16);
            // Classe proposée aux élèves dans l'application (périmètre actuel : CE1 → Terminale).
            $table->boolean('is_available')->default(false);
            $table->timestamps();
            $table->unique(['education_stage_id', 'code']);
            $table->index(['is_available', 'position']);
        });

        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_stage_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 16); // series | specialty
            $table->string('code', 40);
            $table->string('name');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['education_stage_id', 'code']);
        });

        Schema::create('grade_track', function (Blueprint $table) {
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->primary(['grade_id', 'track_id']);
            $table->index('track_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_track');
        Schema::dropIfExists('tracks');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('education_cycles');
        Schema::dropIfExists('education_stages');
    }
};
