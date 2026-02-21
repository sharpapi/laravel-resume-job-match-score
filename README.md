![SharpAPI GitHub cover](https://sharpapi.com/sharpapi-github-laravel-bg.jpg "SharpAPI Laravel Client")

# Laravel Resume/CV and Job Description Match Score

## 🎯 Instantly evaluate how well a candidate's resume aligns with your job descriptions — powered by SharpAPI AI.

Supported resume files - **11 file formats**: **DOC, DOCX, TXT, RTF, PDF, JPG, JPEG, JPE, PNG, TIFF, TIF**

And yes - it handles those **flattened PDFs** where the entire resume is just images instead of text.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sharpapi/laravel-resume-job-match-score.svg?style=flat-square)](https://packagist.org/packages/sharpapi/laravel-resume-job-match-score)
[![Total Downloads](https://img.shields.io/packagist/dt/sharpapi/laravel-resume-job-match-score.svg?style=flat-square)](https://packagist.org/packages/sharpapi/laravel-resume-job-match-score)

Check the full documentation on the [Resume/CV Job Match Scoring API](https://sharpapi.com/en/catalog/ai/hr-tech/resume-cv-job-match-score) page.

---

## Requirements

- PHP >= 8.1
- Laravel >= 10.48.29

---

## Installation

```bash
composer require sharpapi/laravel-resume-job-match-score --prefer-dist

```

Then, in your `.env` file:

```bash
SHARP_API_KEY=your_api_key_here
```

Optionally, publish the config if you want to tweak the default settings:

```bash
php artisan vendor:publish --tag=sharpapi-resume-job-match-score
```

---

## What it does

This package sends a resume file and a job description to the SharpAPI AI endpoint and returns a structured JSON scoring output based on 20+ compatibility factors:

- Skills match
- Experience and industry relevance
- Education and certifications
- Soft skills, language, cultural fit
- Stability score and more

Perfect for recruiters, ATS platforms, and job-matching tools.

---

## Usage

Inject the `ResumeMatchScoreService` into your controller or command and call `matchResumeToJob()`:

```php
use SharpAPI\ResumeMatchScore\ResumeMatchScoreService;
use GuzzleHttp\Exception\GuzzleException;

$resumePath     = storage_path('resume.pdf');      // make sure this file exists
$jobDescription = 'We are hiring a PHP Developer with Laravel experience…';
$language       = 'English';

$service = new ResumeMatchScoreService();

try {
    $statusUrl = $service->matchResumeToJob(
        $resumePath,
        $jobDescription,
        $language          // optional – English is default
    );

    $result = $service->fetchResults($statusUrl)->toArray();
    print_r($result);
} catch (GuzzleException $e) {
    // Handle SharpAPI or network errors
    echo $e->getMessage();
}
```

---

## Example Response

```json
{
  "data": {
    "type": "api_job_result",
    "id": "5a113c4d-38e9-43e5-80f4-ec3fdea3420e",
    "attributes": {
      "status": "success",
      "type": "hr_resume_job_match_score",
      "result": {
        "match_scores": {
          "overall_match": 88,
          "skills_match": 92,
          "experience_match": 85,
          "education_match": 80,
          "certifications_match": 70,
          "job_title_relevance": 84,
          "industry_experience_match": 75,
          "project_experience_match": 78,
          "technical_stack_match": 90,
          "soft_skills_match": 88,
          "methodologies_match": 82,
          "language_proficiency_match": 95,
          "location_preference_match": 100,
          "remote_work_flexibility": 90,
          "certifications_training_relevance": 70,
          "years_experience_weighting": 80,
          "recent_role_relevance": 83,
          "management_experience_match": 60,
          "cultural_fit_potential": 85,
          "stability_score": 77
        },
        "explanations": {
          "skills_match": "Candidate lists React, Node.js, and JavaScript with strong proficiency.",
          "experience_match": "5+ years experience in a similar role within a tech startup.",
          "education_match": "Bachelor's degree in Computer Science aligns with job requirements.",
          "certifications_match": "Has partial certification coverage (e.g., Scrum Master).",
          "language_proficiency_match": "Fluent in English as required."
        }
      }
    }
  }
}

```

---

### Do you think our API is missing some obvious functionality?

- [Please let us know via GitHub »](https://github.com/sharpapi/laravel-resume-job-match-score/issues)
- or [Join our Telegram Group »](https://t.me/sharpapi_community)

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

---

## Credits

- [A2Z WEB LTD](https://github.com/a2zwebltd)
- [Dawid Makowski](https://github.com/makowskid)
- Boost your [Laravel AI](https://sharpapi.com/) capabilities!

---

## License

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


---
## Social Media

🚀 For the latest news, tutorials, and case studies, don't forget to follow us on:
- [SharpAPI X (formerly Twitter)](https://x.com/SharpAPI)
- [SharpAPI YouTube](https://www.youtube.com/@SharpAPI)
- [SharpAPI Vimeo](https://vimeo.com/SharpAPI)
- [SharpAPI LinkedIn](https://www.linkedin.com/products/a2z-web-ltd-sharpapicom-automate-with-aipowered-api/)
- [SharpAPI Facebook](https://www.facebook.com/profile.php?id=61554115896974)
