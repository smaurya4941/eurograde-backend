<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Production hardening of the existing content tables:
 *  - natural-key unique indexes (the seeder and admin tools match rows by these keys),
 *  - soft deletes for content that other rows reference (posts ← comments, team members ← posts),
 *  - SEO fields and a listing index for posts.
 */
return new class extends Migration
{
    /** @var array<string, list<string>> table => natural-key columns */
    private const UNIQUE_KEYS = [
        'faqs' => ['category', 'question'],
        'country_stats' => ['country_id', 'label'],
        'country_requirements' => ['country_id', 'title'],
        'country_visas' => ['country_id', 'name'],
        'country_living_costs' => ['country_id', 'label'],
    ];

    public function up(): void
    {
        foreach (self::UNIQUE_KEYS as $table => $columns) {
            $duplicates = DB::table($table)->select($columns)->groupBy($columns)->havingRaw('COUNT(*) > 1')->count();
            if ($duplicates > 0) {
                throw new RuntimeException("Cannot add unique key to {$table}: {$duplicates} duplicate group(s) on (".implode(', ', $columns).'). Remove them first.');
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                $blueprint->unique($columns, "{$table}_natural_unique");
            });
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->string('meta_title', 160)->nullable()->after('image_path');
            $table->string('meta_description', 320)->nullable()->after('meta_title');
            $table->softDeletes();
            // Category archive pages: WHERE post_category_id = ? ORDER BY published_at DESC.
            $table->index(['post_category_id', 'published_at'], 'posts_category_published_index');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('posts', function (Blueprint $table) {
            // MySQL dropped the FK's implicit index once the composite index could serve it,
            // so give the foreign key its own index back before removing the composite one.
            $table->index('post_category_id', 'posts_post_category_id_index');
            $table->dropIndex('posts_category_published_index');
            $table->dropSoftDeletes();
            $table->dropColumn(['meta_title', 'meta_description']);
        });

        foreach (array_keys(self::UNIQUE_KEYS) as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropUnique("{$table}_natural_unique");
            });
        }
    }
};
