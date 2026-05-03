<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Tests;

use AnilKumarThakur\ExceptionResponse\ExceptionResponseServiceProvider;
use AnilKumarThakur\ExceptionResponse\JsonExceptions;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Exceptions\Handler;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var array<string, mixed> */
    protected array $configOverrides = [];

    protected function setUp(): void
    {
        $this->afterApplicationCreated(fn () => $this->registerExceptionHandlers());

        parent::setUp();
    }

    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ExceptionResponseServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        foreach ($this->configOverrides as $key => $value) {
            $app['config']->set($key, $value);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function withConfig(array $overrides): void
    {
        $this->configOverrides = array_merge($this->configOverrides, $overrides);
        $this->refreshApplication();
        $this->registerExceptionHandlers();
    }

    private function registerExceptionHandlers(): void
    {
        if ($this->app === null) {
            return;
        }

        $handler = $this->app->make(ExceptionHandler::class);

        if ($handler instanceof Handler) {
            JsonExceptions::register(new Exceptions($handler));
        }
    }
}
