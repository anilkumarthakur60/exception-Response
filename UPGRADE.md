# Upgrade Guide

## From 1.x to 2.0

### High-level

Version 2.0 is a clean rewrite for the **Laravel 11/12/13 slim application skeleton**. It targets `bootstrap/app.php` and no longer relies on `app/Exceptions/Handler.php` (which was removed from the skeleton in Laravel 11).

If you are still on the legacy skeleton, stay on 1.x.

### Required changes

#### 1. Update `bootstrap/app.php`

The public entry point was renamed.

```diff
-use AnilKumarThakur\ExceptionResponse\ExceptionResponse;
+use AnilKumarThakur\ExceptionResponse\JsonExceptions;

 ->withExceptions(function (Exceptions $exceptions) {
-    ExceptionResponse::register($exceptions);
+    JsonExceptions::register($exceptions);
 })
```

#### 2. Class renames (only if you reference these classes directly)

Most users don't need to touch these  they're internal. If you've extended or directly resolved them from the container, update the references:

| 1.x | 2.0 |
| --- | --- |
| `AnilKumarThakur\ExceptionResponse\ExceptionResponse` | `AnilKumarThakur\ExceptionResponse\JsonExceptions` |
| `AnilKumarThakur\ExceptionResponse\ApiExceptionRenderer` | `AnilKumarThakur\ExceptionResponse\Renderer` |
| `AnilKumarThakur\ExceptionResponse\Support\JsonRequestDetector` | `AnilKumarThakur\ExceptionResponse\Support\RequestMatcher` |
| `AnilKumarThakur\ExceptionResponse\Support\JsonResponsePayload` | `AnilKumarThakur\ExceptionResponse\Support\Payload` |

#### 3. Method renames

| 1.x | 2.0 |
| --- | --- |
| `JsonRequestDetector::wantsJson($request)` | `RequestMatcher::matches($request)` |
| `JsonResponsePayload::build(...)` | `Payload::make(...)` |

### New in 2.0

- **Translatable messages.** Default messages now go through Laravel's translator (`exception-response::messages.*`). Publish overrides with:

  ```bash
  php artisan vendor:publish --tag=exception-response-lang
  ```

- **Machine-readable error code.** When `include_error_code` is enabled (default), responses include a stable `error_code` key (e.g. `validation_failed`, `unauthenticated`).

- **`ExceptionRendered` event.** Dispatched after every JSON response is built  wire up logging, Sentry tagging, or correlation-ID injection from a single listener.

- **Full request matcher.** `RequestMatcher` now also matches `X-Requested-With: XMLHttpRequest` via Laravel's `expectsJson()` fallback.
