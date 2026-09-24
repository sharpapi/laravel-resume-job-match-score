## SharpAPI Resume Job Match Score (sharpapi/laravel-resume-job-match-score); details in the `sharpapi-resume-job-match-score` skill
- Run `matchResumeToJob()` and `fetchResults()` only in a queued job with `$timeout` above SHARP_API_JOB_STATUS_POLLING_WAIT (180 s) and `$tries = 1`: a retry re-uploads the file and burns quota.
- `fetchResults()` already blocks and polls until success or failed: call it once, never in a `while (pending)` loop.
- A failed job does not throw: check `$job->status === SharpApiJobStatusEnum::SUCCESS->value` (`SharpAPI\Core\Enums`).
- `matchResumeToJob()` needs a local file path the worker can read, not a Storage/S3 key or an `UploadedFile`.
