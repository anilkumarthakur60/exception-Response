<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Contracts;

use Illuminate\Http\Request;

/**
 * Determines whether a given HTTP request should receive a JSON error response.
 */
interface JsonRequestDetectorInterface
{
    /**
     * Determine if the request should receive a JSON-formatted exception response.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return bool True if the request should receive a JSON error response.
     */
    public function shouldRenderJson(Request $request): bool;
}
