<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Content for the service detail page (/services/{slug}): overview, "What We Offer" (heading,
 * intro, checklist, photo gallery), "Why Us" (heading, intro, progress-bar metrics, closing
 * text) and the FAQ topic shown at the bottom.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            /** Paragraphs separated by a blank line. */
            $table->text('overview')->nullable()->after('excerpt');
            $table->string('offer_title', 150)->nullable()->after('overview');
            /** Part of offer_title shown in brand blue ("End-to-End *Immigration* Solutions"). */
            $table->string('offer_highlight', 100)->nullable()->after('offer_title');
            $table->text('offer_intro')->nullable()->after('offer_highlight');
            $table->string('why_title', 150)->nullable()->after('offer_intro');
            $table->string('why_highlight', 100)->nullable()->after('why_title');
            $table->text('why_intro')->nullable()->after('why_highlight');
            $table->text('why_outro')->nullable()->after('why_intro');
            /** FAQ topic (App\Enums\FaqCategory) listed at the bottom of the page. */
            $table->string('faq_category', 50)->nullable()->after('why_outro');
            $table->text('faq_intro')->nullable()->after('faq_category');
        });

        // "What We Offer" checklist.
        Schema::create('service_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('description', 500);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_id', 'sort_order']);
        });

        // Photo gallery (opens in a lightbox).
        Schema::create('service_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('alt', 200);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_id', 'sort_order']);
        });

        // "Why Us" progress bars (label + percentage).
        Schema::create('service_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('label', 150);
            /** Percentage, 0–100. */
            $table->unsignedTinyInteger('value');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_id', 'label']);
            $table->index(['service_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_metrics');
        Schema::dropIfExists('service_images');
        Schema::dropIfExists('service_features');

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'overview', 'offer_title', 'offer_highlight', 'offer_intro', 'why_title', 'why_highlight',
                'why_intro', 'why_outro', 'faq_category', 'faq_intro',
            ]);
        });
    }
};
