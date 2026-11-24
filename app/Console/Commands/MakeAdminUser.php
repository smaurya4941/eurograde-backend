<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/** Creates or promotes a user to admin. Idempotent: running it again updates the same user. */
final class MakeAdminUser extends Command
{
    /**
     * @var string
     */
    protected $signature = 'admin:create {email} {name} {--password=}';

    /**
     * @var string
     */
    protected $description = 'Create (or promote) an admin user for the CMS panel';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $name = trim((string) $this->argument('name'));
        $password = $this->option('password') ?: $this->secret('Password (min 8 characters)');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            ['email' => ['required', 'email'], 'name' => ['required', 'string'], 'password' => ['required', 'string', 'min:8']],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password)],
        );
        $user->forceFill(['is_admin' => true])->save();

        $this->info("Admin user ready: {$user->email}");

        return self::SUCCESS;
    }
}
