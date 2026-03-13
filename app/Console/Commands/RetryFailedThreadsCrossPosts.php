<?php

namespace App\Console\Commands;

use App\Jobs\PublishMomentToThreads;
use App\Models\Moment;
use Illuminate\Console\Command;

class RetryFailedThreadsCrossPosts extends Command
{
    protected $signature = 'moments:threads:retry {--limit=50 : Max number of moments to enqueue}';

    protected $description = 'Re-enqueue failed Threads cross-post attempts.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $moments = Moment::query()
            ->where('threads_status', 'failed')
            ->whereNotNull('body')
            ->latest('id')
            ->limit($limit)
            ->get();

        foreach ($moments as $moment) {
            $moment->forceFill([
                'threads_status' => 'pending',
                'threads_last_error' => null,
            ])->save();

            PublishMomentToThreads::dispatch($moment->id)->afterCommit();
        }

        $this->info("Enqueued {$moments->count()} failed moment(s) for Threads retry.");

        return self::SUCCESS;
    }
}
