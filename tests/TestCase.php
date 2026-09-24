<?php

declare(strict_types=1);

namespace SharpAPI\ResumeMatchScore\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use SharpAPI\ResumeMatchScore\ResumeMatchScoreProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [ResumeMatchScoreProvider::class];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('sharpapi-resume-match-score.api_key', 'test-key');
    }
}
