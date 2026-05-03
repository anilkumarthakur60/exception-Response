<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Support;

use AnilKumarThakur\ExceptionResponse\Events\ExceptionRendered;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

readonly class Payload
{
    public function __construct(
        private RequestMatcher $matcher,
        private Dispatcher $events,
        private bool $includeExceptionClass,
        private bool $includeTraceInDebug,
        private bool $includeErrorCode,
        private bool $debug,
        private int $traceDepth,
    ) {}

    /**
     * @param  array<string, mixed>  $extras
     */
    public function make(
        Request $request,
        Throwable $exception,
        int $status,
        string $message,
        ?string $errorCode = null,
        array $extras = [],
    ): ?JsonResponse {
        if (! $this->matcher->matches($request)) {
            return null;
        }

        /** @var array<string, mixed> $payload */
        $payload = ['message' => $message];

        if ($this->includeErrorCode && $errorCode !== null) {
            $payload['error_code'] = $errorCode;
        }

        if ($extras !== []) {
            $payload = array_merge($payload, $extras);
        }

        if ($this->includeExceptionClass) {
            $payload['exception'] = $exception::class;
        }

        if ($this->debug && $this->includeTraceInDebug) {
            $payload['file'] = $exception->getFile();
            $payload['line'] = $exception->getLine();
            $payload['trace'] = array_slice($exception->getTrace(), 0, max(0, $this->traceDepth));
        }

        $response = new JsonResponse($payload, $status);

        $this->events->dispatch(new ExceptionRendered($request, $exception, $response));

        return $response;
    }
}
