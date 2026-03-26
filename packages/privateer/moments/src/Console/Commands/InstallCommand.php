<?php

namespace Privateer\Moments\Console\Commands;

use Illuminate\Console\Command;
use Privateer\Moments\Support\Moments as MomentsSupport;

use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class InstallCommand extends Command
{
    protected $signature = 'moments:install';

    protected $description = 'Create the initial user account';

    public function handle(): int
    {
        $userModel = MomentsSupport::userModel();

        $name = text(
            label: 'Name',
            required: true,
        );

        $email = text(
            label: 'Email address',
            required: true,
            validate: function (string $value) use ($userModel): ?string {
                if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return 'Please enter a valid email address.';
                }

                if ($userModel::query()->where('email', $value)->exists()) {
                    return 'A user with that email already exists.';
                }

                return null;
            },
        );

        $plainTextPassword = password(
            label: 'Password',
            required: true,
            validate: fn (string $value): ?string => strlen($value) < 8
                ? 'Password must be at least 8 characters.'
                : null,
        );

        $userModel::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $plainTextPassword,
        ]);

        $this->call('moments:glide-key', ['--force' => true]);

        info('User created successfully.');

        return self::SUCCESS;
    }
}
