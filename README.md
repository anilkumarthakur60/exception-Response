# Exception Response for Laravel APIs

Drop-in JSON exception responses for Laravel **11**, **12**, and **13** — built for the slim application skeleton (no `app/Exceptions/Handler.php`).

## Installation

```bash
composer require anil/exception-response
```

The service provider is auto-discovered.

## Usage (Laravel 11 / 12 / 13)

Register the handlers inside `bootstrap/app.php`:

```php
use Anil\ExceptionResponse\ApiExceptionHandler;
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
        ApiExceptionHandler::register($exceptions);
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

## Configuration (optional)

Publish the config:

```bash
php artisan vendor:publish --tag=exception-response-config
```

`config/exception.php`:

```php
return [
    'api_prefixes' => ['api/*'],          // request paths that should get JSON responses
    'include_exception_class' => false,   // add the exception FQCN to the payload
    'include_trace_in_debug' => true,     // include file/line/trace when APP_DEBUG=true
];
```

## Requirements

- PHP **8.2+**
- Laravel **11.x**, **12.x**, or **13.x**

## License

MIT
