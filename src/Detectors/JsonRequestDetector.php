<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Detectors;

use Anil\ExceptionResponse\Contracts\JsonRequestDetectorInterface;
use Illuminate\Http\Request;

/**
 * Detects whether a request should receive a JSON exception response
 * based on URL patterns and the Accept header.
 */
final class JsonRequestDetector implements JsonRequestDetectorInterface
{
    /**
     * @param  list<string>  $patterns  URL patterns that should trigger JSON responses.
     */
    public function __construct(
        private readonly array $patterns,
    ) {}

    public function shouldRenderJson(Request $request): bool
    {
        if ($request->expectsJson()) {
            return true;
        }

        return $request->is(...$this->patterns);
    }
}
