<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Registrars;

use Anil\ExceptionResponse\Contracts\ExceptionHandlerRegistrarInterface;
use Anil\ExceptionResponse\Contracts\ExceptionRendererInterface;
use Anil\ExceptionResponse\Contracts\JsonRequestDetectorInterface;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Throwable;

/**
 * Bridges the package with Laravel's exception handling system
 * by registering JSON rendering callbacks via withExceptions().
 */
final class ExceptionHandlerRegistrar implements ExceptionHandlerRegistrarInterface
{
    public function __construct(
        private readonly JsonRequestDetectorInterface $detector,
        private readonly ExceptionRendererInterface $renderer,
    ) {}

    public function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $this->detector->shouldRenderJson($request),
        );

        $exceptions->render(
            fn (Throwable $e, Request $request) => $this->detector->shouldRenderJson($request)
                ? $this->renderer->render($request, $e)
                : null,
        );
    }
}
