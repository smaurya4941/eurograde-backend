<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // What EuroRank offers (home services carousel, footer, future Services pages).
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 160)->unique();
            $table->string('excerpt', 500);
            /** Full page content (sanitised HTML) for the service detail page. */
            $table->longText('body')->nullable();
            $table->string('icon_path')->nullable();
            $table->string('image_path')->nullable();
            /** Optional link to the destination this service is about (Study in Germany → Germany). */
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('meta_title', 160)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });

        // Partner universities, grouped by country.
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->string('city', 100)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('website_url')->nullable();
            /** Public / private institution. */
            $table->string('type', 20)->default('public');
            /** Shown in the "partner universities" logo wall. */
            $table->boolean('is_partner')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'is_partner', 'sort_order']);
            $table->index(['country_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
        Schema::dropIfExists('services');
    }
};
