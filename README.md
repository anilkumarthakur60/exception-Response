# Exception Response for Laravel

Structured JSON exception responses for Laravel APIs. Automatically converts exceptions into clean, consistent JSON responses for API routes.

## Requirements

- PHP 8.2+
- Laravel 11.0+

## Installation

```bash
composer require anil/exception-response
```

The package auto-discovers its service provider. No manual registration needed.

## Setup

Register the exception handler in your `bootstrap/app.php`:

```php
use Anil\ExceptionResponse\Contracts\ExceptionHandlerRegistrarInterface;
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    ->withExceptions(function (Exceptions $exceptions): void {
        app(ExceptionHandlerRegistrarInterface::class)->register($exceptions);
    })
    ->create();
```

That's it. All exceptions on `api/*` routes will now return structured JSON responses.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=exception-response-config
```

This creates `config/exception-response.php` with the following options:

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `json_request_patterns` | `string[]` | `['api/*']` | URL patterns that trigger JSON responses. Supports wildcards. |
| `debug` | `bool` | `env('APP_DEBUG')` | Include debug info (class, file, line, trace) in responses. |
| `status_code_map` | `array` | `[]` | Custom exception class => HTTP status code mappings. |
| `include_exception_header` | `bool` | `true` | Add `X-Exception-Type` header in debug mode. |
| `bindings` | `array` | `[]` | Override default contract implementations. |

## JSON Response Examples

### Production - 404 Not Found

```json
{
    "message": "Record not found.",
    "status": 404
}
```

### Production - 422 Validation Error

```json
{
    "message": "The given data was invalid.",
    "status": 422,
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### Debug - 500 Server Error

```json
{
    "message": "Something went wrong.",
    "status": 500,
    "exception": "RuntimeException",
    "file": "/var/www/app/Http/Controllers/UserController.php",
    "line": 42,
    "trace": ["..."]
}
```

## Custom Status Code Mappings

Add custom exception-to-status-code mappings in the config file:

```php
// config/exception-response.php
'status_code_map' => [
    \App\Exceptions\PaymentFailedException::class => 402,
    \App\Exceptions\ConflictException::class => 409,
],
```

These merge with and can override the built-in mappings.

### Built-in Mappings

| Exception | Status Code |
|-----------|-------------|
| `ValidationException` | 422 |
| `AuthenticationException` | 401 |
| `AuthorizationException` | 403 |
| `ModelNotFoundException` | 404 |
| `NotFoundHttpException` | 404 |
| `MethodNotAllowedHttpException` | 405 |
| `TokenMismatchException` | 419 |
| `TooManyRequestsHttpException` | 429 |
| Any `HttpException` | Uses `getStatusCode()` |
| All others | 500 |

## Customization

### Swapping Implementations via Config

Replace any default implementation in `config/exception-response.php`:

```php
'bindings' => [
    \Anil\ExceptionResponse\Contracts\StatusCodeResolverInterface::class
        => \App\Exceptions\MyStatusCodeResolver::class,

    \Anil\ExceptionResponse\Contracts\ExceptionPayloadBuilderInterface::class
        => \App\Exceptions\MyPayloadBuilder::class,
],
```

### Swapping Implementations via Service Container

In your `AppServiceProvider`:

```php
use Anil\ExceptionResponse\Contracts\ExceptionPayloadBuilderInterface;
use App\Exceptions\CustomPayloadBuilder;

public function register(): void
{
    $this->app->singleton(
        ExceptionPayloadBuilderInterface::class,
        CustomPayloadBuilder::class,
    );
}
```

### Available Contracts

| Contract | Responsibility |
|----------|---------------|
| `JsonRequestDetectorInterface` | Determines if a request should get a JSON error response |
| `StatusCodeResolverInterface` | Maps exceptions to HTTP status codes |
| `ExceptionPayloadBuilderInterface` | Builds the JSON error payload array |
| `ExceptionRendererInterface` | Orchestrates rendering and returns `JsonResponse` |
| `ExceptionHandlerRegistrarInterface` | Registers with Laravel's exception handler |

## Testing

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
