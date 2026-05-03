# Laravel Exception Response

[![tests](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/tests.yml/badge.svg)](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/tests.yml)
[![static analysis](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/static-analysis.yml)
[![code style](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/code-style.yml/badge.svg)](https://github.com/anilkumarthakur60/laravel-exception-response/actions/workflows/code-style.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)

Drop-in JSON exception responses for **Laravel 11, 12, and 13** APIs — built for the slim application skeleton (no `app/Exceptions/Handler.php`).

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

That's it. Any request matching `api/*` or sending `Accept: application/json` will get a uniform JSON error payload:

```json
{ "message": "Resource not found." }
```

Web routes still render the standard Laravel error pages.

## Handled exceptions

| Exception | Status |
| --- | --- |
| `ValidationException` | 422 (with `errors`) |
| `AuthenticationException` | 401 |
| `AuthorizationException` | 403 |
| `ModelNotFoundException` | 404 |
| `NotFoundHttpException` | 404 |
| `MethodNotAllowedHttpException` | 405 |
| `ThrottleRequestsException` | 429 |
| `TokenMismatchException` | 419 |
| `PostTooLargeException` | 413 |
| `QueryException` | 500 |
| `BadMethodCallException` | 500 |
| `InvalidArgumentException` | 400 |
| `BindingResolutionException` | 500 |
| Anything implementing `HttpExceptionInterface` | exception's own status |

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
];
```

## Extending

You can register your own renderer alongside this one. Add it inside `withExceptions()` after `JsonExceptions::register()` — last-registered wins for matching exception types:

```php
->withExceptions(function (Exceptions $exceptions) {
    JsonExceptions::register($exceptions);

    $exceptions->render(function (\App\Exceptions\PaymentFailed $e) {
        return response()->json(['message' => $e->getMessage(), 'code' => 'PAYMENT_FAILED'], 402);
    });
})
```

## Development

```bash
composer install
composer test         # PHPUnit
composer analyse      # PHPStan level 10
composer format       # Pint (apply)
composer format:check # Pint (verify)
```

## Architecture

```
src/
├── JsonExceptions.php                # public static API entry point
├── Renderer.php                      # registers per-exception render callbacks
├── ExceptionResponseServiceProvider.php
└── Support/
    ├── RequestMatcher.php            # decides if a request wants JSON
    └── Payload.php                   # builds the response body
config/
└── exception-response.php
tests/
├── TestCase.php
├── Feature/
└── Unit/
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT — see [LICENSE.md](LICENSE.md).
