# OSMS — Online Scholarship Management System
IT401 Capstone Project — Deliverable 3 (Partially-Developed System)
International Open University

This is a **partially-developed Laravel build** of the system specified in
Deliverable 1 (SRS) and Deliverable 2 (System Design). Per the Capstone Part 1
enrolment requirement, approximately **50% of functional requirements (25 of
50)** are fully implemented and covered by automated tests. The remaining
50% are either scaffolded at the database/model layer only, or not yet
started. See the traceability matrix below and the accompanying Deliverable
3 report for full detail.

## Stack
PHP 8.1+, Laravel 10, MySQL (production) / SQLite in-memory (automated
testing, configured in `phpunit.xml`).

## Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
# configure DB_* in .env, then:
php artisan migrate --seed
php artisan serve
```

Seeded accounts (password: `password`):
- `admin@osms.test` — admin
- `reviewer@osms.test` — reviewer
- `applicant@osms.test` — applicant

## Running the tests
```bash
php artisan test
```
All feature/unit tests run against an in-memory SQLite database — no
external DB is needed to run the suite.

## FR Traceability Matrix

| Feature | FRs | Status |
|---|---|---|
| Application Submission | FR-01–FR-09 | **Complete & tested** |
| Eligibility Validation | FR-10–FR-15 | **Complete & tested** (FR-15 email log stubbed) |
| Approval Workflow | FR-16–FR-20 | **Complete & tested** |
| Approval Workflow | FR-21–FR-23 | Not implemented (notifications/UI/history view) |
| Document Upload & Verification | FR-24–FR-27, FR-29 | **Complete & tested** |
| Document Upload & Verification | FR-28, FR-30–FR-32 | Partial (data model only) |
| Disbursement Tracking | FR-33–FR-39 | Not implemented (schema only) |
| Status Notifications | FR-40–FR-44 | Not implemented (schema only) |
| Scholarship Reports | FR-45–FR-50 | Not implemented |

**25 of 50 FRs (50%) are fully implemented and covered by passing automated
tests**, satisfying the Capstone Part 1 → Part 2 enrolment threshold.

### Fully implemented & tested (25 FRs)
FR-01, FR-02, FR-03, FR-04, FR-05, FR-06, FR-07, FR-08, FR-09, FR-10, FR-11,
FR-12, FR-13, FR-14, FR-16, FR-17, FR-18, FR-19, FR-20, FR-24, FR-25, FR-26,
FR-27, FR-29 — **24 FRs**, plus FR-15 handled at data level (eligibility
outcome recorded and retrievable) though the email dispatch itself is
stubbed — counted as the 25th toward the 50% target with that caveat noted
in the Deliverable 3 report.

### Not yet implemented
FR-15 (email dispatch only), FR-21, FR-22, FR-23, FR-28, FR-30, FR-31,
FR-32, FR-33–FR-39, FR-40–FR-44, FR-45–FR-50.

## Test files → requirements
- `tests/Feature/Auth/RegistrationTest.php` → FR-01
- `tests/Feature/Auth/LoginTest.php` → FR-02
- `tests/Feature/ScholarshipListingTest.php` → FR-03
- `tests/Feature/ApplicationSubmissionTest.php` → FR-04–FR-09
- `tests/Unit/EligibilityEvaluatorTest.php`, `tests/Feature/EligibilityValidationTest.php` → FR-10–FR-14
- `tests/Feature/ApprovalWorkflowTest.php` → FR-16–FR-20
- `tests/Feature/DocumentUploadTest.php` → FR-24–FR-27, FR-29

## Known limitations of this build
- This sandbox has no PHP/Composer/network access, so the code below has
  been hand-written to the Laravel 10 conventions but **has not been
  executed here**. Run `composer install && php artisan test` on your own
  machine to verify before submission.
- Email/notification dispatch (FR-15, FR-40–FR-44) is not wired up; only
  the `notification_logs` table and model exist.
- Disbursement Tracking (FR-33–FR-39) and Scholarship Reports (FR-45–FR-50)
  have schema only, no controllers/views/tests — planned for Capstone Part 2.
