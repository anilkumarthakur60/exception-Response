<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Contracts;

use Illuminate\Http\Request;
use Throwable;

/**
 * Builds the array payload for a JSON exception response.
 */
interface ExceptionPayloadBuilderInterface
{
    /**
     * Build the JSON error response payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @param  Throwable  $e  The exception to build a payload for.
     * @param  int  $statusCode  The resolved HTTP status code.
     * @return array<string, mixed> The structured error payload.
     */
    public function build(Request $request, Throwable $e, int $statusCode): array;
}
