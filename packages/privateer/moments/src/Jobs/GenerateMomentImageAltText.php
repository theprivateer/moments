<?php

namespace Privateer\Moments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Privateer\Moments\Services\GenerateMomentImageAltText as GenerateMomentImageAltTextService;
use Privateer\Moments\Support\Moments as MomentsSupport;
use Throwable;

class GenerateMomentImageAltText implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;

    public function __construct(public int $momentImageId) {}

    public function handle(GenerateMomentImageAltTextService $generator): void
    {
        $momentImageModel = MomentsSupport::momentImageModel();
        $momentImage = $momentImageModel::query()->find($this->momentImageId);

        if ($momentImage === null) {
            return;
        }

        $generator->store($momentImage);
    }

    public function failed(Throwable $exception): void
    {
        report($exception);
    }
}
