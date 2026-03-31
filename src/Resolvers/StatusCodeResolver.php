<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Resolvers;

use Anil\ExceptionResponse\Contracts\StatusCodeResolverInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/**
 * Resolves HTTP status codes from exceptions using a priority-ordered mapping.
 */
final class StatusCodeResolver implements StatusCodeResolverInterface
{
    /** @var array<class-string<Throwable>, int> */
    private readonly array $mergedMap;

    /**
     * @param  array<class-string<Throwable>, int>  $customMap  User-defined exception-to-status-code mappings.
     */
    public function __construct(
        array $customMap = [],
    ) {
        $this->mergedMap = array_replace($this->defaultMap(), $customMap);
    }

    public function resolve(Throwable $e): int
    {
        foreach ($this->mergedMap as $exceptionClass => $statusCode) {
            if ($e instanceof $exceptionClass) {
                return $statusCode;
            }
        }

        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        return 500;
    }

    /**
     * @return array<class-string<Throwable>, int>
     */
    private function defaultMap(): array
    {
        return [
            ValidationException::class => 422,
            AuthenticationException::class => 401,
            AuthorizationException::class => 403,
            ModelNotFoundException::class => 404,
            NotFoundHttpException::class => 404,
            MethodNotAllowedHttpException::class => 405,
            TokenMismatchException::class => 419,
            TooManyRequestsHttpException::class => 429,
        ];
    }
}
