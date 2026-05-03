<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse;

use AnilKumarThakur\ExceptionResponse\Support\Payload;
use AnilKumarThakur\ExceptionResponse\Support\RequestMatcher;
use BadMethodCallException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Model;
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

final class Renderer
{
    private const STATUS_ERROR_CODES = [
        400 => 'bad_request',
        401 => 'unauthenticated',
        403 => 'unauthorized',
        405 => 'method_not_allowed',
        409 => 'conflict',
        410 => 'gone',
        413 => 'payload_too_large',
        415 => 'unsupported_media_type',
        419 => 'csrf_token_mismatch',
        422 => 'validation_failed',
        429 => 'too_many_requests',
        500 => 'server_error',
        502 => 'bad_gateway',
        503 => 'service_unavailable',
        504 => 'gateway_timeout',
    ];

    public function __construct(
        private readonly RequestMatcher $matcher,
        private readonly Payload $payload,
        private readonly Translator $translator,
    ) {}

    public function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $this->matcher->matches($request),
        );

        $exceptions->render($this->renderValidation(...));
        $exceptions->render($this->renderAuthentication(...));
        $exceptions->render($this->renderNotFound(...));
        $exceptions->render($this->renderMethodNotAllowed(...));
        $exceptions->render($this->renderThrottle(...));
        $exceptions->render($this->renderPostTooLarge(...));
        $exceptions->render($this->renderQuery(...));
        $exceptions->render($this->renderBadMethod(...));
        $exceptions->render($this->renderInvalidArgument(...));
        $exceptions->render($this->renderBindingResolution(...));
        $exceptions->render($this->renderHttp(...));
    }

    private function renderValidation(ValidationException $e, Request $request): ?JsonResponse
    {
        $message = $this->resolveMessage($e, 'validation_failed', 'The given data was invalid.');

        return $this->payload->make($request, $e, $e->status, $message, 'validation_failed', ['errors' => $e->errors()]);
    }

    private function renderAuthentication(AuthenticationException $e, Request $request): ?JsonResponse
    {
        $message = $this->resolveMessage($e, 'unauthenticated', 'Unauthenticated.');

        return $this->payload->make($request, $e, 401, $message, 'unauthenticated');
    }

    private function renderNotFound(NotFoundHttpException $e, Request $request): ?JsonResponse
    {
        $previous = $e->getPrevious();

        if ($previous instanceof ModelNotFoundException) {
            $message = $this->resolveMessage($previous, 'model_not_found', 'Resource not found.');

            return $this->payload->make($request, $e, 404, $message, 'model_not_found');
        }

        $message = $this->resolveMessage($e, 'not_found', 'The requested endpoint does not exist.');

        return $this->payload->make($request, $e, 404, $message, 'not_found');
    }

    private function renderMethodNotAllowed(MethodNotAllowedHttpException $e, Request $request): ?JsonResponse
    {
        $message = $this->resolveMessage($e, 'method_not_allowed', 'Method not allowed.');

        return $this->payload->make($request, $e, 405, $message, 'method_not_allowed');
    }

    private function renderThrottle(ThrottleRequestsException $e, Request $request): ?JsonResponse
    {
        $message = $this->resolveMessage($e, 'too_many_requests', 'Too many requests.');

        return $this->payload->make($request, $e, 429, $message, 'too_many_requests');
    }

    private function renderPostTooLarge(PostTooLargeException $e, Request $request): ?JsonResponse
    {
        $message = $this->resolveMessage($e, 'payload_too_large', 'Payload too large.');

        return $this->payload->make($request, $e, 413, $message, 'payload_too_large');
    }

    private function renderQuery(QueryException $e, Request $request): ?JsonResponse
    {
        $message = $this->resolveMessage($e, 'query_error', 'Database error.');

        return $this->payload->make($request, $e, 500, $message, 'query_error');
    }

    private function renderBadMethod(BadMethodCallException $e, Request $request): ?JsonResponse
    {
        $message = $this->resolveMessage($e, 'bad_method', 'Bad method call.');

        return $this->payload->make($request, $e, 500, $message, 'bad_method');
    }

    private function renderInvalidArgument(InvalidArgumentException $e, Request $request): ?JsonResponse
    {
        $message = $this->resolveMessage($e, 'invalid_argument', 'Invalid argument.');

        return $this->payload->make($request, $e, 400, $message, 'invalid_argument');
    }

    private function renderBindingResolution(BindingResolutionException $e, Request $request): ?JsonResponse
    {
        $message = $this->resolveMessage($e, 'binding_resolution', 'Container binding could not be resolved.');

        return $this->payload->make($request, $e, 500, $message, 'binding_resolution');
    }

    private function renderHttp(HttpExceptionInterface $e, Request $request): ?JsonResponse
    {
        $previous = $e->getPrevious();

        if ($previous instanceof AuthorizationException) {
            $message = $this->resolveMessage($previous, 'unauthorized', 'This action is unauthorized.');

            return $this->payload->make($request, $e, $e->getStatusCode(), $message, 'unauthorized');
        }

        if ($previous instanceof TokenMismatchException) {
            $message = $this->resolveMessage($previous, 'csrf_token_mismatch', 'CSRF token mismatch.');

            return $this->payload->make($request, $e, 419, $message, 'csrf_token_mismatch');
        }

        $status = $e->getStatusCode();
        $errorCode = self::STATUS_ERROR_CODES[$status] ?? null;

        if ($errorCode !== null) {
            $defaultEnglish = $e->getMessage() !== '' ? $e->getMessage() : ($this->translate($errorCode) ?? 'Server error.');
            $message = $this->resolveMessage($e, $errorCode, $defaultEnglish);

            return $this->payload->make($request, $e, $status, $message, $errorCode);
        }

        $message = $e->getMessage() !== '' ? $e->getMessage() : ($this->translate('server_error') ?? 'Server error.');

        return $this->payload->make($request, $e, $status, $message);
    }

    private function resolveMessage(Throwable $exception, string $translationKey, string $frameworkDefault): string
    {
        $message = $exception->getMessage();

        if ($message === '' || $message === $frameworkDefault) {
            return $this->translate($translationKey) ?? $frameworkDefault;
        }

        return $message;
    }

    private function translate(string $key): ?string
    {
        $line = $this->translator->get('exception-response::messages.'.$key);

        if (! is_string($line) || str_starts_with($line, 'exception-response::')) {
            return null;
        }

        return $line;
    }
}
