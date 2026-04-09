# Changelog

## v1.1.0 - 2026-04-09

- Added optional `context` parameter to `ResumeMatchScoreService::matchResumeToJob()` for steering the scoring engine via the new directive contract (`EMPHASIZE:`, `DEEMPHASIZE:`, `CREDIT:`). Max 5000 characters per request.
- Updated README with a "Context directives" section and usage example.

## v1.0.2 - 2026-02-21

Security: bumped minimum Laravel version to ^10.48.29 to address file validation bypass vulnerability (CVE). Dropped Laravel 9 support (EOL since Feb 2024).

## May 1, 2025—v1.0.0