<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Contracts;

use Illuminate\Foundation\Configuration\Exceptions;

/**
 * Registers exception handling callbacks with Laravel's exception handler.
 */
interface ExceptionHandlerRegistrarInterface
{
    /**
     * Register JSON exception rendering with Laravel's exception handler.
     *
     * @param  Exceptions  $exceptions  The Laravel exceptions configuration instance.
     */
    public function register(Exceptions $exceptions): void;
}
