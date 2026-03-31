<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Renderers;

use Anil\ExceptionResponse\Contracts\ExceptionPayloadBuilderInterface;
use Anil\ExceptionResponse\Contracts\ExceptionRendererInterface;
use Anil\ExceptionResponse\Contracts\StatusCodeResolverInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Orchestrates exception rendering by resolving the status code,
 * building the payload, and returning a JSON response.
 */
final class ExceptionRenderer implements ExceptionRendererInterface
{
    public function __construct(
        private readonly StatusCodeResolverInterface $statusCodeResolver,
        private readonly ExceptionPayloadBuilderInterface $payloadBuilder,
        private readonly bool $debug,
        private readonly bool $includeExceptionHeader,
    ) {}

    public function render(Request $request, Throwable $e): JsonResponse
    {
        $statusCode = $this->statusCodeResolver->resolve($e);
        $payload = $this->payloadBuilder->build($request, $e, $statusCode);

        $response = new JsonResponse($payload, $statusCode);

        if ($this->debug && $this->includeExceptionHeader) {
            $response->headers->set('X-Exception-Type', $e::class);
        }

        return $response;
    }
}
