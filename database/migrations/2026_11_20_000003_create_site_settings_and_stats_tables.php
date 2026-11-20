<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Site-wide key/value settings: contact details, social links, map embed…
        // Keys are dotted and grouped by their prefix, e.g. "contact.phone", "social.facebook".
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            /** Exposed by the public API (secrets/internal values must set this to false). */
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        // Headline numbers animated by counters ("80+ Expert Counselors").
        Schema::create('site_stats', function (Blueprint $table) {
            $table->id();
            /** Stable identifier the frontend can pick a stat by, e.g. "counselors". */
            $table->string('key', 50)->unique();
            $table->string('label', 100);
            $table->unsignedInteger('value');
            $table->string('suffix', 10)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_stats');
        Schema::dropIfExists('site_settings');
    }
};
