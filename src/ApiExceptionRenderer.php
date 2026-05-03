<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse;

use AnilKumarThakur\ExceptionResponse\Support\JsonRequestDetector;
use AnilKumarThakur\ExceptionResponse\Support\JsonResponsePayload;
use BadMethodCallException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
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

final class ApiExceptionRenderer
{
    public function __construct(
        private readonly JsonRequestDetector $detector,
        private readonly JsonResponsePayload $payload,
    ) {}

    public function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $this->detector->wantsJson($request),
        );

        $exceptions->render($this->renderValidation(...));
        $exceptions->render($this->renderAuthentication(...));
        $exceptions->render($this->renderAuthorization(...));
        $exceptions->render($this->renderModelNotFound(...));
        $exceptions->render($this->renderNotFound(...));
        $exceptions->render($this->renderMethodNotAllowed(...));
        $exceptions->render($this->renderThrottle(...));
        $exceptions->render($this->renderTokenMismatch(...));
        $exceptions->render($this->renderPostTooLarge(...));
        $exceptions->render($this->renderQuery(...));
        $exceptions->render($this->renderBadMethod(...));
        $exceptions->render($this->renderInvalidArgument(...));
        $exceptions->render($this->renderBindingResolution(...));
        $exceptions->render($this->renderHttp(...));
    }

    private function renderValidation(ValidationException $e, Request $request): ?JsonResponse
    {
        if (! $this->detector->wantsJson($request)) {
            return null;
        }

        return new JsonResponse([
            'message' => $e->getMessage(),
            'errors' => $e->errors(),
        ], $e->status);
    }

    private function renderAuthentication(AuthenticationException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 401, 'Unauthenticated.');
    }

    private function renderAuthorization(AuthorizationException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 403, 'This action is unauthorized.');
    }

    /**
     * @param  ModelNotFoundException<Model>  $e
     */
    private function renderModelNotFound(ModelNotFoundException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 404, 'Resource not found.');
    }

    private function renderNotFound(NotFoundHttpException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 404, 'The requested endpoint does not exist.');
    }

    private function renderMethodNotAllowed(MethodNotAllowedHttpException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 405, 'Method not allowed.');
    }

    private function renderThrottle(ThrottleRequestsException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 429, 'Too many requests.');
    }

    private function renderTokenMismatch(TokenMismatchException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 419, 'CSRF token mismatch.');
    }

    private function renderPostTooLarge(PostTooLargeException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 413, 'Payload too large.');
    }

    private function renderQuery(QueryException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 500, 'Database error.');
    }

    private function renderBadMethod(BadMethodCallException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 500, 'Bad method call.');
    }

    private function renderInvalidArgument(InvalidArgumentException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 400, 'Invalid argument.');
    }

    private function renderBindingResolution(BindingResolutionException $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, 500, 'Container binding could not be resolved.');
    }

    private function renderHttp(HttpExceptionInterface $e, Request $request): ?JsonResponse
    {
        return $this->payload->build($request, $e, $e->getStatusCode());
    }
}
