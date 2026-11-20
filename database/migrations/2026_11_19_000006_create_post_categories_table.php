<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Promote the free-text `posts.category` column to a proper `post_categories`
 * table so the blog can filter, count and link categories by slug.
 * Existing category names are carried over (backfilled) before the column is dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('post_category_id')
                ->nullable()
                ->after('slug')
                ->constrained('post_categories')
                ->nullOnDelete();
        });

        $now = now();

        DB::table('posts')->select('category')->distinct()->orderBy('category')->pluck('category')
            ->filter()
            ->each(function (string $name) use ($now) {
                $categoryId = DB::table('post_categories')->insertGetId([
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('posts')->where('category', $name)->update(['post_category_id' => $categoryId]);
            });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('category')->default('')->after('slug');
        });

        DB::table('post_categories')->get(['id', 'name'])->each(
            fn (object $category) => DB::table('posts')
                ->where('post_category_id', $category->id)
                ->update(['category' => $category->name]),
        );

        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('post_category_id');
        });

        Schema::dropIfExists('post_categories');
    }
};
