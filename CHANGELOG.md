# Changelog

## v1.2.0 - 2026-09-24

- Fixed: `SHARP_API_JOB_STATUS_USE_POLLING_INTERVAL` / `api_job_status_use_polling_interval` had no effect because the service never passed it to the client. When `true`, `fetchResults()` now polls at `SHARP_API_JOB_STATUS_POLLING_INTERVAL` instead of the API's `Retry-After`.
- Requires `sharpapi/php-core` ^1.4.1: a failed job with a null result now returns a `SharpApiJob` instead of raising a `TypeError`, and a missing API key throws a clear `InvalidArgumentException`.
- Ships Laravel Boost resources: a short core guideline (`resources/boost/guidelines/core.blade.php`) and the `sharpapi-resume-job-match-score` skill.
- Added a Pest + Orchestra Testbench test suite.
- README: the config publish tag is `sharpapi-resume-match-score` (the README said `sharpapi-resume-job-match-score`).

## v1.1.0 - 2026-04-09

- Added optional `context` parameter to `ResumeMatchScoreService::matchResumeToJob()` for steering the scoring engine via the new directive contract (`EMPHASIZE:`, `DEEMPHASIZE:`, `CREDIT:`). Max 5000 characters per request.
- Updated README with a "Context directives" section and usage example.

## v1.0.2 - 2026-02-21

Security: bumped minimum Laravel version to ^10.48.29 to address file validation bypass vulnerability (CVE). Dropped Laravel 9 support (EOL since Feb 2024).

## May 1, 2025—v1.0.0