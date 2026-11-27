<?php

declare(strict_types=1);

use App\Support\ContentCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('site_settings')
            ->where('key', 'contact.email')
            ->where('value', 'contact@eurogrades.com')
            ->update(['value' => 'contact@eurorank.com']);

        ContentCache::flush();
    }

    public function down(): void
    {
        DB::table('site_settings')
            ->where('key', 'contact.email')
            ->where('value', 'contact@eurorank.com')
            ->update(['value' => 'contact@eurogrades.com']);

        ContentCache::flush();
    }
};
