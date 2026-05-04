# Changelog

All notable changes to `anilkumarthakur/laravel-exception-response` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Full rewrite for Laravel 11, 12, 13 slim application skeleton.
- `AnilKumarThakur\ExceptionResponse\JsonExceptions::register()` static entry point for `bootstrap/app.php`.
- `Renderer` covering validation, auth, 404/405, throttling, CSRF, payload-too-large, query, and any `HttpExceptionInterface`.
- **Translations:** English + Spanish ship out of the box; publish with `vendor:publish --tag=exception-response-lang`.
- **Machine-readable `error_code`:** stable string per exception (e.g. `validation_failed`, `unauthenticated`); toggle via `include_error_code`.
- **`ExceptionRendered` event** dispatched after every JSON response — for logging, Sentry tagging, request-id correlation.
- Configurable `api_prefixes`, `include_exception_class`, `include_trace_in_debug`, `trace_depth`, `include_error_code`.
- PHPStan level 10 (with larastan), Pint, PHPUnit, Testbench tooling.
- GitHub Actions matrix CI for PHP 8.2/8.3/8.4 × Laravel 11/12/13.
- Coverage workflow uploading to Codecov.
- `composer qa` script that runs format-check, analyse, and tests in sequence.
- Issue templates (bug report, feature request) and PR template.
- Dependabot config for Composer + GitHub Actions weekly updates.
- `SECURITY.md`, `CONTRIBUTING.md`, `UPGRADE.md`.

### Changed (BREAKING — v2.0)
- `ExceptionResponse` → `JsonExceptions` (public entry; update calls in `bootstrap/app.php`).
- `ApiExceptionRenderer` → `Renderer`.
- `Support\JsonRequestDetector` → `Support\RequestMatcher`; method `wantsJson()` → `matches()`.
- `Support\JsonResponsePayload` → `Support\Payload`; method `build()` → `make()`.
- Default response now includes `error_code` field. Disable via `include_error_code => false` if you need the previous shape.

### Removed
- Legacy `Anil\ExceptionResponse\Traits\ApiExceptionResponse` trait (incompatible with the slim skeleton).
- Legacy `Anil\ExceptionResponse\Providers\ApiExceptionProvider`.
- Bundled `vendor/` directory and `composer.lock` from version control.
