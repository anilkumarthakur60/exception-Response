<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse\Providers;

use Illuminate\Support\ServiceProvider;

class ApiExceptionProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/exception.php', 'exception');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/exception.php' => config_path('exception.php'),
            ], 'exception-response-config');
        }
    }
}
