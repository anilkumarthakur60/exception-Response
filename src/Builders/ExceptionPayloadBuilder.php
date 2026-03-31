<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Builders;

use Anil\ExceptionResponse\Contracts\ExceptionPayloadBuilderInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Builds the JSON payload array for an exception response,
 * including debug information when enabled.
 */
final class ExceptionPayloadBuilder implements ExceptionPayloadBuilderInterface
{
    public function __construct(
        private readonly bool $debug,
    ) {}

    public function build(Request $request, Throwable $e, int $statusCode): array
    {
        $payload = [
            'message' => $this->resolveMessage($e, $statusCode),
            'status' => $statusCode,
        ];

        if ($e->getCode() !== 0) {
            $payload['code'] = $e->getCode();
        }

        if ($e instanceof ValidationException) {
            $payload['errors'] = $e->errors();
        }

        if ($this->debug) {
            $payload['exception'] = $e::class;
            $payload['file'] = $e->getFile();
            $payload['line'] = $e->getLine();
            $payload['trace'] = collect($e->getTrace())->map(function (array $frame): string {
                $location = ($frame['file'] ?? '').':'.($frame['line'] ?? '');
                $call = ($frame['class'] ?? '').($frame['type'] ?? '').$frame['function'];

                return trim("{$location} {$call}");
            })->all();
        }

        return $payload;
    }

    private function resolveMessage(Throwable $e, int $statusCode): string
    {
        $message = $e->getMessage();

        if ($message !== '') {
            return $message;
        }

        return match ($statusCode) {
            401 => 'Unauthenticated.',
            403 => 'Forbidden.',
            404 => 'Record not found.',
            405 => 'Method not allowed.',
            419 => 'Page expired.',
            422 => 'The given data was invalid.',
            429 => 'Too many requests.',
            500 => 'Server error.',
            default => 'An error occurred.',
        };
    }
}
