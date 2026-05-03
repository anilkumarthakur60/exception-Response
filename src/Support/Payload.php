<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

readonly class Payload
{
    public function __construct(
        private RequestMatcher $matcher,
        private bool $includeExceptionClass,
        private bool $includeTraceInDebug,
        private bool $debug,
        private int $traceDepth,
    ) {}

    public function make(
        Request $request,
        Throwable $exception,
        int $status,
        ?string $fallbackMessage = null,
    ): ?JsonResponse {
        if (! $this->matcher->matches($request)) {
            return null;
        }

        $message = $exception->getMessage() !== ''
            ? $exception->getMessage()
            : ($fallbackMessage ?? 'Server error.');

        /** @var array<string, mixed> $payload */
        $payload = ['message' => $message];

        if ($this->includeExceptionClass) {
            $payload['exception'] = $exception::class;
        }

        if ($this->debug && $this->includeTraceInDebug) {
            $payload['file'] = $exception->getFile();
            $payload['line'] = $exception->getLine();
            $payload['trace'] = array_slice($exception->getTrace(), 0, max(0, $this->traceDepth));
        }

        return new JsonResponse($payload, $status);
    }
}
