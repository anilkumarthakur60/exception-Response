<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Renders an exception as a JSON HTTP response.
 */
interface ExceptionRendererInterface
{
    /**
     * Render the given exception as a JSON response.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @param  Throwable  $e  The exception to render.
     * @return JsonResponse The JSON error response.
     */
    public function render(Request $request, Throwable $e): JsonResponse;
}
