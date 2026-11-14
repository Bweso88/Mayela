<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Programme scolaire versionné par année :
 *
 * PAYS → ANNÉE SCOLAIRE (+ trimestres) → PROGRAMME → MATIÈRE (par classe, séries)
 *      → CHAPITRE (rattaché à un trimestre)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('label', 20); // ex. 2026-2027
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            $table->unique(['country_id', 'label']);
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('number');
            $table->string('name', 40);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();
            $table->unique(['academic_year_id', 'number']);
        });

        Schema::create('curricula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            // Référence du texte officiel (arrêté, document du ministère…), obligatoire pour publier.
            $table->string('official_reference')->nullable();
            $table->string('status', 16)->default('draft'); // draft | published | archived
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_id')->constrained('curricula')->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->restrictOnDelete();
            $table->string('code', 40);
            $table->string('name');
            $table->string('color', 9)->nullable();
            $table->string('icon', 40)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['curriculum_id', 'grade_id', 'code']);
            $table->index(['grade_id', 'position']);
        });

        // Matière spécifique à certaines séries (sans ligne ici : commune à toutes les séries de la classe).
        Schema::create('subject_track', function (Blueprint $table) {
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->decimal('coefficient', 4, 1)->nullable();
            $table->primary(['subject_id', 'track_id']);
            $table->index('track_id');
        });

        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('status', 16)->default('draft'); // draft | in_review | published | archived
            $table->boolean('is_free')->default(false); // accessible en compte gratuit
            $table->boolean('is_demo')->default(false); // contenu de démonstration (mention « DÉMO »)
            $table->timestamps();
            $table->softDeletes();
            $table->index(['subject_id', 'position']);
            $table->index('status');
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->after('country_id')->constrained()->nullOnDelete();
            $table->foreignId('track_id')->nullable()->after('grade_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('track_id');
            $table->dropConstrainedForeignId('grade_id');
        });
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('subject_track');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('curricula');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('academic_years');
    }
};
