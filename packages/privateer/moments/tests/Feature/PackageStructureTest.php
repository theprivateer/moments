<?php

namespace Privateer\Moments\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Privateer\Moments\MomentsServiceProvider;

class PackageStructureTest extends TestCase
{
    public function test_service_provider_exists(): void
    {
        $this->assertTrue(class_exists(MomentsServiceProvider::class));
    }
}
