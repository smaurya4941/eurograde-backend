<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureModels();
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    private function configureModels(): void
    {
        // Catch lazy loading (N+1), silently discarded attributes and
        // missing attributes during development; relaxed in production.
        Model::shouldBeStrict(! $this->app->isProduction());
    }

    private function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // Block migrate:fresh / db:wipe etc. against production.
        DB::prohibitDestructiveCommands($this->app->isProduction());

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(8)->letters()->mixedCase()->numbers()->uncompromised()
            : Password::min(8));
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        // Public website forms (contact, newsletter): curb spam per visitor.
        RateLimiter::for('forms', fn (Request $request) => [
            Limit::perMinute(5)->by('forms-minute|'.$request->ip()),
            Limit::perDay(50)->by('forms-day|'.$request->ip()),
        ]);
    }
}
