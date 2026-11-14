<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Identity : pays (racine de la hiérarchie pédagogique), profils parent / élève,
 * rattachement parent ↔ enfant et consentements (protection des mineurs).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->char('code', 2)->unique(); // ISO 3166-1 alpha-2
            $table->string('name');
            $table->string('default_locale', 8)->default('fr');
            $table->char('currency', 3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('parent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->date('birth_date');
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->string('school_name')->nullable();
            // Identifiant de connexion simplifié pour les enfants (code + PIN), sans e-mail.
            $table->string('login_code', 16)->unique();
            $table->string('pin_hash')->nullable();
            $table->unsignedSmallInteger('level')->default(1);
            $table->unsignedInteger('xp')->default(0);
            $table->string('avatar', 64)->nullable();
            $table->timestamps();
            $table->softDeletes();
            // grade_id, school_series_id, academic_year_id : ajoutés en phase 2 (module Curriculum).
        });

        Schema::create('parent_student', function (Blueprint $table) {
            $table->foreignId('parent_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->primary(['parent_profile_id', 'student_profile_id']);
            $table->index('student_profile_id');
        });

        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            // Null lorsque l'élève a l'âge de consentir lui-même.
            $table->foreignId('parent_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32);
            $table->string('version', 16);
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['student_profile_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('parent_profiles');
        Schema::dropIfExists('countries');
    }
};
