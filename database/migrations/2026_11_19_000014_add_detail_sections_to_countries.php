<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Content for the country detail page sections: "Why Choose {Country}?" (intro, image and
 * reasons), "Top Cities" (intro + per-city description) and the FAQ intro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->text('why_choose_intro')->nullable()->after('overview');
            $table->string('why_choose_image_path')->nullable()->after('why_choose_intro');
            $table->text('cities_intro')->nullable()->after('why_choose_image_path');
            $table->text('faq_intro')->nullable()->after('faq_category');
        });

        Schema::table('country_cities', function (Blueprint $table) {
            $table->string('description', 500)->nullable()->after('name');
        });

        Schema::create('country_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('description', 500);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['country_id', 'title']);
            $table->index(['country_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_reasons');

        Schema::table('country_cities', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['why_choose_intro', 'why_choose_image_path', 'cities_intro', 'faq_intro']);
        });
    }
};
