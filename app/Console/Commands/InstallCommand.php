<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class InstallCommand extends Command
{
    protected $signature = 'moments:install';

    protected $description = 'Create the initial user account';

    public function handle(): int
    {
        $name = text(
            label: 'Name',
            required: true,
        );

        $email = text(
            label: 'Email address',
            required: true,
            validate: function (string $value) {
                if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return 'Please enter a valid email address.';
                }
                if (User::where('email', $value)->exists()) {
                    return 'A user with that email already exists.';
                }

                return null;
            },
        );

        $password = password(
            label: 'Password',
            required: true,
            validate: fn (string $value) => strlen($value) < 8
                ? 'Password must be at least 8 characters.'
                : null,
        );

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        info('User created successfully.');

        return self::SUCCESS;
    }
}
