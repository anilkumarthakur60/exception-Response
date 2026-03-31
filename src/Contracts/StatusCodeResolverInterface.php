<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Contracts;

use Throwable;

/**
 * Resolves an HTTP status code from a given exception.
 */
interface StatusCodeResolverInterface
{
    /**
     * Resolve the appropriate HTTP status code for the given exception.
     *
     * @param  Throwable  $e  The exception to resolve a status code for.
     * @return int The HTTP status code.
     */
    public function resolve(Throwable $e): int;
}
