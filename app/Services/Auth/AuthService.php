<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data, Session $session): User
    {
        $user = DB::transaction(fn (): User => User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed by the model cast
        ]));

        event(new Registered($user));

        Auth::guard('web')->login($user);
        $session->regenerate();

        return $user;
    }

    /**
     * @param  array{email: string, password: string}  $credentials
     *
     * @throws ValidationException
     */
    public function login(array $credentials, bool $remember, Session $session, string $throttleKey): User
    {
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            event(new Lockout(request()));

            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($throttleKey), 'minutes' => 1]),
            ])->status(429);
        }

        if (! Auth::guard('web')->attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        $session->regenerate();

        /** @var User */
        return Auth::guard('web')->user();
    }

    public function logout(Session $session): void
    {
        Auth::guard('web')->logout();

        $session->invalidate();
        $session->regenerateToken();
    }
}
