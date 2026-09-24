<?php

declare(strict_types=1);

use SharpAPI\ResumeMatchScore\ResumeMatchScoreService;

it('reads the polling settings from config', function () {
    config([
        'sharpapi-resume-match-score.api_job_status_polling_wait' => 240,
        'sharpapi-resume-match-score.api_job_status_polling_interval' => 7,
    ]);

    $service = new ResumeMatchScoreService;

    expect($service->getApiJobStatusPollingWait())->toBe(240)
        ->and($service->getApiJobStatusPollingInterval())->toBe(7);
});

it('leaves the custom polling interval off by default', function () {
    expect((new ResumeMatchScoreService)->isUseCustomInterval())->toBeFalse();
});

it('applies the use-polling-interval flag from config', function () {
    config(['sharpapi-resume-match-score.api_job_status_use_polling_interval' => true]);

    expect((new ResumeMatchScoreService)->isUseCustomInterval())->toBeTrue();
});

it('throws a clear exception when the API key is missing', function () {
    config(['sharpapi-resume-match-score.api_key' => null]);

    new ResumeMatchScoreService;
})->throws(InvalidArgumentException::class, 'SHARP_API_KEY');
