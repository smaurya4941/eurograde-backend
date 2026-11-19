<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            /** ISO 3166-1 alpha-2, e.g. "DE". */
            $table->char('iso_code', 2)->unique();
            $table->string('flag_path')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->string('excerpt', 500)->nullable();
            $table->text('overview')->nullable();
            $table->text('part_time_work')->nullable();
            $table->text('post_study_work')->nullable();
            /** FaqCategory value whose questions belong to this country (e.g. "germany"). */
            $table->string('faq_category', 50)->nullable();
            $table->string('meta_title', 160)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
