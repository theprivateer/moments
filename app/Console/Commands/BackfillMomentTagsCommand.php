<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Privateer\Moments\Services\SyncMomentTags;
use Privateer\Moments\Support\Moments;

#[Signature('moments:backfill-tags')]
#[Description('Backfill inline hashtags for existing moments')]
class BackfillMomentTagsCommand extends Command
{
    public function handle(SyncMomentTags $syncMomentTags): int
    {
        $momentModel = Moments::momentModel();
        $total = $momentModel::query()->count();

        $this->info("Backfilling hashtags for {$total} moments...");

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $processed = 0;

        $momentModel::query()
            ->orderBy('id')
            ->cursor()
            ->each(function ($moment) use (&$processed, $progressBar, $syncMomentTags): void {
                $syncMomentTags->sync($moment);
                $processed++;
                $progressBar->advance();
            });

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Processed {$processed} moments.");

        return self::SUCCESS;
    }
}
