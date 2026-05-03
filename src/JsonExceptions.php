<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse;

use Illuminate\Container\Container;
use Illuminate\Foundation\Configuration\Exceptions;

final class JsonExceptions
{
    public static function register(Exceptions $exceptions): void
    {
        Container::getInstance()
            ->make(Renderer::class)
            ->register($exceptions);
    }
}
