---
name: sharpapi-resume-job-match-score
description: Score how well a resume / CV file matches a job description with sharpapi/laravel-resume-job-match-score (ResumeMatchScoreService::matchResumeToJob, fetchResults, SharpApiJob, match_scores, context directives, config/sharpapi-resume-match-score.php). Use when building CV-to-job matching or candidate ranking, steering the score with EMPHASIZE/DEEMPHASIZE/CREDIT directives, running the match in a queued job, handling a failed or timed-out match, or mocking the service in tests.
---

# SharpAPI Resume / Job Match Score

## When to use this skill

- Scoring a candidate's resume file against a job description (overall match plus about 20 per-factor scores with explanations).
- Steering the score with context directives.
- Writing the queued job that submits the file and waits for the result, possibly next to `sharpapi/laravel-resume-parser`.
- Handling a failed match, a polling timeout or a rate limit.
- Mocking the service in feature tests.

Supported resume files (per the README): DOC, DOCX, TXT, RTF, PDF, JPG, JPEG, JPE, PNG, TIFF, TIF, including image-only PDFs. The README states no file size limit; the `$context` string is limited to 5000 characters (HTTP 422 above that).

## Install / config

1. `composer require sharpapi/laravel-resume-job-match-score`. The provider `SharpAPI\ResumeMatchScore\ResumeMatchScoreProvider` is auto-discovered. Requires `sharpapi/php-core` ≥ 1.4.1 (pulled in automatically).
2. `.env`:
   ```dotenv
   SHARP_API_KEY=your-api-key
   # optional
   SHARP_API_BASE_URL=https://sharpapi.com/api/v1
   SHARP_API_JOB_STATUS_POLLING_WAIT=180          # max seconds fetchResults() blocks
   SHARP_API_JOB_STATUS_POLLING_INTERVAL=10       # seconds between polls...
   SHARP_API_JOB_STATUS_USE_POLLING_INTERVAL=false # ...only used when true; otherwise the API's Retry-After wins
   ```
3. Optional: `php artisan vendor:publish --tag=sharpapi-resume-match-score` publishes `config/sharpapi-resume-match-score.php` (keys `api_key`, `base_url`, `api_job_status_polling_wait`, `api_job_status_polling_interval`, `api_job_status_use_polling_interval`). Note the config slug is `sharpapi-resume-match-score`, not the package name.

There is no facade and no container binding. `ResumeMatchScoreService` has a no-argument constructor that reads the config, so inject it (preferred, mockable) or `new` it. A missing key throws `InvalidArgumentException` when the service is constructed.

## API

```php
use SharpAPI\ResumeMatchScore\ResumeMatchScoreService;

public function matchResumeToJob(
    string $cvFilePath,
    string $jobDescription,
    string $language = 'English',
    ?string $context = null,
): string // returns a status URL
```

- `$cvFilePath`: a local, readable resume file path. The client reads it with `file_get_contents()` and uploads it as multipart, sending `basename($cvFilePath)` as the filename.
- `$jobDescription`: the job description as plain text (sent as the `content` field).
- `$language`: output language, as a full English name (`'English'`, `'Spanish'`), not an ISO code. Defaults to `'English'`.
- `$context`: optional scoring directives, one per line, mixed freely; `null` omits the field. Max 5000 characters.
  - `EMPHASIZE: <topic>` raises that metric's weight by one step.
  - `DEEMPHASIZE: <topic>` lowers it by one step.
  - `CREDIT: <skill | tool | certification>` treats the item as a plausible requirement even if the job description omits it.
- Returns the job's status URL, not the result. Get the result with `fetchResults($statusUrl)`.

`fetchResults(string $statusUrl): SharpAPI\Core\DTO\SharpApiJob` (inherited from `SharpAPI\Core\Client\SharpApiClient`) polls until the job is `success` or `failed`. The DTO has `id`, `type` (`'hr_resume_job_match_score'`), `status` (string) and `result` (`?stdClass`), plus `getResultJson()`, `getResultArray()`, `getResultObject()`, `toArray()`. `quota()` and `ping()` are also inherited.

Result shape (from the README example, trimmed; scores are 0–100):

```json
{
  "match_scores": {
    "overall_match": 88,
    "skills_match": 92,
    "experience_match": 85,
    "education_match": 80,
    "certifications_match": 70,
    "job_title_relevance": 84,
    "technical_stack_match": 90,
    "soft_skills_match": 88,
    "language_proficiency_match": 95,
    "location_preference_match": 100,
    "cultural_fit_potential": 85,
    "stability_score": 77
  },
  "explanations": {
    "skills_match": "Candidate lists React, Node.js, and JavaScript with strong proficiency.",
    "experience_match": "5+ years experience in a similar role within a tech startup.",
    "language_proficiency_match": "Fluent in English as required."
  }
}
```

The full README example also has `industry_experience_match`, `project_experience_match`, `methodologies_match`, `remote_work_flexibility`, `certifications_training_relevance`, `years_experience_weighting`, `recent_role_relevance` and `management_experience_match`. `explanations` does not cover every score, so read keys with `?? null`.

## Recipe: queued job

```php
namespace App\Jobs;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SharpAPI\Core\Enums\SharpApiJobStatusEnum;
use SharpAPI\ResumeMatchScore\ResumeMatchScoreService;

class ScoreApplication implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;              // a retry re-uploads the CV and burns quota
    public int $timeout;
    public bool $failOnTimeout = true;

    public function __construct(public Application $application)
    {
        // fetchResults() may block for the whole polling wait; add headroom for the upload.
        $this->timeout = (int) config('sharpapi-resume-match-score.api_job_status_polling_wait', 180) + 60;
    }

    public function handle(ResumeMatchScoreService $matcher): void
    {
        $statusUrl = $matcher->matchResumeToJob(
            Storage::disk('local')->path($this->application->cv_path), // local and readable by the worker
            $this->application->jobPosting->description,
            'English',
            "EMPHASIZE: backend scalability\nCREDIT: AWS",
        );

        $job = $matcher->fetchResults($statusUrl); // blocks until success or failed

        if ($job->status !== SharpApiJobStatusEnum::SUCCESS->value) {
            throw new RuntimeException("SharpAPI match scoring failed (job {$job->id}).");
        }

        $data = json_decode($job->getResultJson(), true);

        $this->application->update([
            'match_score' => $data['match_scores']['overall_match'] ?? null,
            'match_result' => $data,
        ]);
    }
}
```

- Resume text instead of a file: write it to a local `.txt` file first (TXT is supported) and delete it in a `finally` block.
- Files on S3 or another remote disk: copy to a local temp file, keeping the original extension in its name (the basename is the upload filename).
- Parsing and scoring in the same job (`ResumeParserService::parseResume()` + `matchResumeToJob()`): each `fetchResults()` can block for its own polling wait, so set `$timeout` to the sum of both waits plus headroom.
- Need retries? Save `$statusUrl` on the model right after `matchResumeToJob()`. On a retry, skip the upload and call only `fetchResults()` on the saved URL; it only reads the job status.
- The queue connection's `retry_after` must be greater than the job `$timeout`, and a Horizon supervisor's `timeout` at least as large, or the job runs twice.

## Gotchas

1. **`fetchResults()` already blocks and polls** (up to `api_job_status_polling_wait`, honouring the API's `Retry-After`). Call it once. Never wrap it in `while ($job->status === 'pending')`: it only returns on `success` or `failed`, and each extra call starts a new wait.
2. **A failed job does not throw.** It returns a `SharpApiJob` with `status === 'failed'` and no usable `result`. Compare `$job->status` with `SharpAPI\Core\Enums\SharpApiJobStatusEnum::SUCCESS->value`.
3. **Never call `fetchResults()` in an HTTP request.** Use a queued job whose `$timeout` exceeds the polling wait, with low `$tries`.
4. **Use `json_decode($job->getResultJson(), true)` for arrays.** `getResultArray()` casts only the top level. Whether `match_scores` and `explanations` come back as arrays or `stdClass` depends on the status URL form php-core received, so the `json_decode` form is the only one that is always plain arrays.
5. **Language is a full name** (`'English'`), not `'en'`.
6. **Local readable path only.** A Storage key, an S3 path or an `UploadedFile` does not work, and the request's temp upload is gone by the time a queued job runs. A missing file surfaces as an `ErrorException` from `file_get_contents()`.
7. Errors: a `$context` over 5000 characters fails with HTTP 422 (`GuzzleHttp\Exception\ClientException`); a polling timeout throws `SharpAPI\Core\Exceptions\ApiException` ("Polling timed out ..."); a 429 on submit is tried 3 times in total, then throws `ApiException` with code 429. Other 4xx/5xx surface as Guzzle exceptions.

## Testing

php-core sends requests with its own Guzzle client, so **`Http::fake()` does not intercept them**. Mock the service instead (works when the code resolves it from the container, e.g. `handle(ResumeMatchScoreService $matcher)`):

```php
use Mockery\MockInterface;
use SharpAPI\Core\DTO\SharpApiJob;
use SharpAPI\ResumeMatchScore\ResumeMatchScoreService;

it('stores the match score', function () {
    $this->mock(ResumeMatchScoreService::class, function (MockInterface $mock) {
        $mock->shouldReceive('matchResumeToJob')->once()->andReturn('https://sharpapi.com/api/v1/job/status/job-1');
        $mock->shouldReceive('fetchResults')->once()->andReturn(new SharpApiJob(
            id: 'job-1',
            type: 'hr_resume_job_match_score',
            status: 'success',
            result: (object) ['match_scores' => ['overall_match' => 88], 'explanations' => []],
        ));
    });

    // dispatch the job synchronously and assert on the model
});
```

Cover the failed path with `status: 'failed', result: new stdClass`. `$this->mock()` does not run the constructor, so tests need no API key; code that resolves the real service does (`config(['sharpapi-resume-match-score.api_key' => 'test-key'])`).
