<?php

namespace Privateer\Moments\Console\Commands;

use Illuminate\Console\Command;
use Privateer\Moments\Services\GenerateMomentImageAltText as GenerateMomentImageAltTextService;
use Privateer\Moments\Support\Moments as MomentsSupport;
use Throwable;

class GenerateAltTextCommand extends Command
{
    protected $signature = 'moments:generate-alt-text {--force : Regenerate alt text for every image}';

    protected $description = 'Generate AI alt text for moment images';

    public function handle(GenerateMomentImageAltTextService $generator): int
    {
        $momentImageModel = MomentsSupport::momentImageModel();
        $force = (bool) $this->option('force');

        $baseQuery = $momentImageModel::query();
        $candidateQuery = $momentImageModel::query()
            ->when(! $force, function ($query): void {
                $query->where(function ($query): void {
                    $query->whereNull('alt_text')
                        ->orWhere('alt_text', '');
                });
            })
            ->orderBy('id');

        $totalImages = (clone $baseQuery)->count();
        $targetImages = (clone $candidateQuery)->count();
        $skipped = $force ? 0 : max($totalImages - $targetImages, 0);
        $processed = 0;
        $failed = 0;

        foreach ($candidateQuery->cursor() as $momentImage) {
            try {
                $generator->store($momentImage);
                $processed++;
            } catch (Throwable $exception) {
                $failed++;

                report($exception);
                $this->components->warn("Failed to generate alt text for image {$momentImage->id}.");
            }
        }

        $this->components->info("Processed {$processed} image(s).");
        $this->components->info("Skipped {$skipped} image(s).");
        $this->components->info("Failed {$failed} image(s).");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
