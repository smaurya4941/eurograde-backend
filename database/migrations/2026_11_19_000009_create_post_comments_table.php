<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            // Replies are removed together with the comment they answer.
            $table->foreignId('parent_id')->nullable()->constrained('post_comments')->cascadeOnDelete();
            $table->string('author_name', 100);
            $table->string('author_email');
            $table->string('author_website')->nullable();
            $table->text('body');
            $table->string('status', 20)->default('pending');
            $table->timestamp('approved_at')->nullable();
            // Kept for moderation and spam handling only; never exposed by the API.
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['post_id', 'status', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_comments');
    }
};
