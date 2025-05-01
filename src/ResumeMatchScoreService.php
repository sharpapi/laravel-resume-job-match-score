<?php

declare(strict_types=1);

namespace SharpAPI\ResumeMatchScore;

use GuzzleHttp\Exception\GuzzleException;
use SharpAPI\Core\Client\SharpApiClient;

class ResumeMatchScoreService extends SharpApiClient
{
    public function __construct()
    {
        parent::__construct(config('sharpapi-resume-match-score.api_key'));
        $this->setApiBaseUrl(config('sharpapi-resume-match-score.base_url'));
        $this->setApiJobStatusPollingInterval((int) config('sharpapi-resume-match-score.api_job_status_polling_interval', 10));
        $this->setApiJobStatusPollingWait((int) config('sharpapi-resume-match-score.api_job_status_polling_wait', 180));
        $this->setUserAgent('SharpAPILaravelMatchScore/1.0.0');
    }

    /**
     * @api
     * @throws GuzzleException
     */
    public function matchResumeToJob(
        string $cvFilePath,
        string $jobDescription,
        string $language = 'English'
    ): string {
        $response = $this->makeRequest(
            'POST',
            '/hr/resume_job_match_score',
            [
                'language' => $language,
                'content' => $jobDescription
            ],
            $cvFilePath
        );

        return $this->parseStatusUrl($response);
    }
}
