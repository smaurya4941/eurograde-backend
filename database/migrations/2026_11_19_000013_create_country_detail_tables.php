<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ordered child records of a country (shown on its card and detail page).
 * All cascade on delete so a country's content is removed with it.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Key figures, e.g. "Tuition Fee (Yearly)" → "$1,500 (Public)".
        Schema::create('country_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('label', 100);
            $table->string('value', 150);
            /** Shown on the country card in the listing. */
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['country_id', 'sort_order']);
        });

        // Admission & visa requirements checklist.
        Schema::create('country_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['country_id', 'sort_order']);
        });

        // Visa types, e.g. "Student Visa (Visum zu Studienzwecken)" — national D visa, €75.
        Schema::create('country_visas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('visa_type', 150)->nullable();
            $table->string('fee', 100)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['country_id', 'sort_order']);
        });

        // Monthly living expense breakdown, e.g. "Rent" → "€300 to €700".
        Schema::create('country_living_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('label', 100);
            $table->string('amount', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['country_id', 'sort_order']);
        });

        // Popular student cities.
        Schema::create('country_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('universities_count')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['country_id', 'name']);
            $table->index(['country_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_cities');
        Schema::dropIfExists('country_living_costs');
        Schema::dropIfExists('country_visas');
        Schema::dropIfExists('country_requirements');
        Schema::dropIfExists('country_stats');
    }
};
