<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse;

use BadMethodCallException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(
            static fn(Request $request): bool => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(static function (ValidationException $e, Request $request): ?JsonResponse {
            if (!self::wantsJson($request)) {
                return null;
            }

            return new JsonResponse([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status);
        });

        $exceptions->render(static function (AuthenticationException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 401);
        });

        $exceptions->render(static function (AuthorizationException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 403);
        });

        $exceptions->render(static function (ModelNotFoundException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 404, 'Resource not found.');
        });

        $exceptions->render(static function (NotFoundHttpException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 404, 'The requested endpoint does not exist.');
        });

        $exceptions->render(static function (MethodNotAllowedHttpException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 405);
        });

        $exceptions->render(static function (ThrottleRequestsException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 429, 'Too many requests.');
        });

        $exceptions->render(static function (TokenMismatchException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 419, 'CSRF token mismatch.');
        });

        $exceptions->render(static function (PostTooLargeException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 413, 'Payload too large.');
        });

        $exceptions->render(static function (QueryException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 500, 'Database error.');
        });

        $exceptions->render(static function (BadMethodCallException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 500);
        });

        $exceptions->render(static function (InvalidArgumentException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 400);
        });

        $exceptions->render(static function (BindingResolutionException $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, 500);
        });

        $exceptions->render(static function (HttpExceptionInterface $e, Request $request): ?JsonResponse {
            return self::respond($request, $e, $e->getStatusCode());
        });
    }

    protected static function respond(Request $request, Throwable $e, int $status, ?string $fallbackMessage = null): ?JsonResponse
    {
        if (!self::wantsJson($request)) {
            return null;
        }

        $message = $e->getMessage() !== ''
            ? $e->getMessage()
            : ($fallbackMessage ?? 'Server error.');

        $payload = ['message' => $message];

        if (config('exception.include_exception_class', false)) {
            $payload['exception'] = $e::class;
        }

        if (config('app.debug') && config('exception.include_trace_in_debug', true)) {
            $payload['file'] = $e->getFile();
            $payload['line'] = $e->getLine();
            $payload['trace'] = collect($e->getTrace())->take(10)->all();
        }

        return new JsonResponse($payload, $status);
    }

    protected static function wantsJson(Request $request): bool
    {
        $prefixes = (array)config('exception.api_prefixes', ['api/*']);

        foreach ($prefixes as $prefix) {
            if ($request->is($prefix)) {
                return true;
            }
        }

        return $request->expectsJson();
    }
}
