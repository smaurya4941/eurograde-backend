<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photo behind the dark page banner (breadcrumb + title) of country and service detail pages.
 * Seeded with the banners the old eurogrades.com pages used; null falls back to the default banner.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('banner_image_path')->nullable()->after('cover_image_path');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('banner_image_path')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('banner_image_path');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('banner_image_path');
        });
    }
};
