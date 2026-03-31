<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Tests;

use Anil\ExceptionResponse\ExceptionResponseServiceProvider;
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
}
