# Laravel Exception Response

[![tests](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/tests.yml/badge.svg)](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/tests.yml)
[![static analysis](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/static-analysis.yml)
[![code style](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/code-style.yml/badge.svg)](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/code-style.yml)
[![coverage](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/coverage.yml/badge.svg)](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/coverage.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)

Drop-in JSON exception responses for **Laravel 11, 12, and 13** APIs — built for the slim application skeleton (no `app/Exceptions/Handler.php`).

- ✅ Uniform JSON shape for every exception
- ✅ Translatable messages (English + Spanish included; bring your own locales)
- ✅ Stable machine-readable `error_code` for clients
- ✅ Debug-mode trace info gated by `app.debug`
- ✅ `ExceptionRendered` event for logging / Sentry tagging
- ✅ Web routes untouched

---

## Requirements

- PHP **8.2+**
- Laravel **11.x**, **12.x**, or **13.x**

## Installation

```bash
composer require anilkumarthakur/laravel-exception-response
```

The service provider is auto-discovered.

## Usage

Register the renderers inside `bootstrap/app.php`:

```php
use AnilKumarThakur\ExceptionResponse\JsonExceptions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        JsonExceptions::register($exceptions);
    })
    ->create();
```

Any request matching `api/*` or sending `Accept: application/json` will get a uniform JSON payload:

```json
{
  "message": "Resource not found.",
  "error_code": "not_found"
}
```

Web routes still render the standard Laravel error pages.

## Response shape

| Field | Always present | Notes |
| --- | --- | --- |
| `message` | yes | Localized; uses the exception's own message when set, otherwise the translated default |
| `error_code` | when `include_error_code` (default `true`) | Stable string like `unauthenticated`, `validation_failed` |
| `errors` | only on `ValidationException` | Field-level validation errors |
| `exception` | when `include_exception_class` is `true` | FQCN of the thrown exception |
| `file`, `line`, `trace` | when `app.debug` and `include_trace_in_debug` are both `true` | `trace` is truncated to `trace_depth` frames |

## Handled exceptions

| Exception | Status | `error_code` |
| --- | --- | --- |
| `ValidationException` | 422 | `validation_failed` (with `errors`) |
| `AuthenticationException` | 401 | `unauthenticated` |
| `AuthorizationException` | 403 | `unauthorized` |
| `ModelNotFoundException` | 404 | `model_not_found` |
| `NotFoundHttpException` | 404 | `not_found` |
| `MethodNotAllowedHttpException` | 405 | `method_not_allowed` |
| `ThrottleRequestsException` | 429 | `too_many_requests` |
| `TokenMismatchException` | 419 | `csrf_token_mismatch` |
| `PostTooLargeException` | 413 | `payload_too_large` |
| `QueryException` | 500 | `query_error` |
| `BadMethodCallException` | 500 | `bad_method` |
| `InvalidArgumentException` | 400 | `invalid_argument` |
| `BindingResolutionException` | 500 | `binding_resolution` |
| Any `HttpExceptionInterface` | exception's status | mapped from status when known |

## Configuration

Publish the config:

```bash
php artisan vendor:publish --tag=exception-response-config
```

`config/exception-response.php`:

```php
return [
    'api_prefixes'             => ['api/*'],
    'include_exception_class'  => false,
    'include_trace_in_debug'   => true,
    'trace_depth'              => 10,
    'include_error_code'       => true,
];
```

## Translations

Default messages live under `lang/{locale}/messages.php`. English and Spanish ship out of the box. Switch locale anywhere in your app (`App::setLocale('es')`) and responses follow.

Publish the lang files to override or add languages:

```bash
php artisan vendor:publish --tag=exception-response-lang
```

The translation keys (also used as `error_code` values):

```
unauthenticated, unauthorized, model_not_found, not_found, method_not_allowed,
too_many_requests, csrf_token_mismatch, payload_too_large, query_error,
bad_method, invalid_argument, binding_resolution, validation_failed, server_error
```

> When the thrown exception has a **custom** message (e.g. `abort(404, 'No such record.')` or `throw new AuthenticationException('Token expired.')`), that message wins. Translations are used only when the exception carries the framework's default message or no message at all.

## Events

Every JSON response dispatches `AnilKumarThakur\ExceptionResponse\Events\ExceptionRendered`:

```php
use AnilKumarThakur\ExceptionResponse\Events\ExceptionRendered;
use Illuminate\Support\Facades\Event;

Event::listen(function (ExceptionRendered $event) {
    logger()->warning('API error', [
        'status'    => $event->response->getStatusCode(),
        'exception' => $event->exception::class,
        'path'      => $event->request->path(),
    ]);
});
```

Useful for Sentry / Bugsnag tagging, request-id correlation, or per-tenant alerting.

## Extending

Register your own renderer alongside this one. Add it inside `withExceptions()` after `JsonExceptions::register()` — first-registered wins for matching exception types, so put the more specific callback first:

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\App\Exceptions\PaymentFailed $e) {
        return response()->json([
            'message'    => $e->getMessage(),
            'error_code' => 'payment_failed',
        ], 402);
    });

    JsonExceptions::register($exceptions);
})
```

## Development

```bash
composer install
composer test         # PHPUnit
composer analyse      # PHPStan level 10
composer format       # Pint (apply)
composer format:check # Pint (verify)
composer qa           # everything above
```

## Architecture

```
src/
├── JsonExceptions.php                # public static API entry point
├── Renderer.php                      # registers per-exception render callbacks
├── ExceptionResponseServiceProvider.php
├── Events/
│   └── ExceptionRendered.php         # dispatched after every JSON response
└── Support/
    ├── RequestMatcher.php            # decides if a request wants JSON
    └── Payload.php                   # builds the response body
config/
└── exception-response.php
lang/
├── en/messages.php
└── es/messages.php
tests/
├── TestCase.php
├── Feature/
└── Unit/
```

## Versioning, security, contributions

- Follows [Semantic Versioning](https://semver.org/). Breaking changes ship as a major version with notes in [UPGRADE.md](UPGRADE.md).
- Security issues: see [SECURITY.md](SECURITY.md). Please **do not** file public issues for vulnerabilities.
- Contributions welcome — see [CONTRIBUTING.md](CONTRIBUTING.md).

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT — see [LICENSE.md](LICENSE.md).
