<?php

declare(strict_types=1);

namespace SharpAPI\ResumeMatchScore;

use Illuminate\Support\ServiceProvider;

class ResumeMatchScoreProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sharpapi-resume-match-score.php' => config_path('sharpapi-resume-match-score.php'),
            ], 'sharpapi-resume-match-score');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sharpapi-resume-match-score.php', 'sharpapi-resume-match-score'
        );
    }
}