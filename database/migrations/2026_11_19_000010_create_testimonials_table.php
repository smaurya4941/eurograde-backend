<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('avatar_path')->nullable();
            $table->string('category', 20);
            /** e.g. "Master’s Student", "IELTS Preparation". */
            $table->string('program');
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            /** e.g. "Band 8", "B1 Level" — shown when there is no origin → destination. */
            $table->string('achievement')->nullable();
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->default(5);
            /** "Case overview" tags shown on featured case-study cards. */
            $table->json('highlights')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'category', 'sort_order']);
            $table->index(['is_active', 'is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
