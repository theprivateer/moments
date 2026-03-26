<?php

namespace Privateer\Moments\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GlideKeyCommand extends Command
{
    protected $signature = 'moments:glide-key {--force : Overwrite an existing non-empty key}';

    protected $description = 'Generate a Glide image signing key and write it to .env';

    public function handle(): int
    {
        if (filled(config('moments.glide_sign_key')) && ! $this->option('force')) {
            $this->components->warn('Glide signing key already set. Use --force to overwrite.');

            return self::SUCCESS;
        }

        $key = Str::random(32);

        $path = $this->laravel->environmentFilePath();
        $contents = file_get_contents($path);

        $updated = preg_replace('/^GLIDE_SIGN_KEY=.*/m', 'GLIDE_SIGN_KEY='.$key, $contents);

        if ($updated === null) {
            $this->components->error('Failed to update the .env file.');

            return self::FAILURE;
        }

        if ($updated === $contents) {
            $updated = rtrim($contents).PHP_EOL.PHP_EOL.'GLIDE_SIGN_KEY='.$key.PHP_EOL;
        }

        file_put_contents($path, $updated);

        config(['moments.glide_sign_key' => $key]);

        $this->components->info('Glide signing key set successfully.');

        return self::SUCCESS;
    }
}
