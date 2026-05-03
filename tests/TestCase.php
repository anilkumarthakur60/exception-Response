<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Tests;

use AnilKumarThakur\ExceptionResponse\ExceptionResponseServiceProvider;
use AnilKumarThakur\ExceptionResponse\JsonExceptions;
use Illuminate\Foundation\Configuration\Exceptions;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ExceptionResponseServiceProvider::class,
        ];
    }

    protected function defineExceptions(Exceptions $exceptions): void
    {
        JsonExceptions::register($exceptions);
    }
}
