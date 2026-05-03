<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse;

use AnilKumarThakur\ExceptionResponse\Support\Payload;
use AnilKumarThakur\ExceptionResponse\Support\RequestMatcher;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class ExceptionResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/exception-response.php', 'exception-response');

        $this->app->singleton(RequestMatcher::class, static function (Application $app): RequestMatcher {
            $config = $app->make('config');
            assert($config instanceof Repository);

            $prefixes = $config->get('exception-response.api_prefixes', ['api/*']);
            assert(is_array($prefixes));

            /** @var list<string> $normalised */
            $normalised = array_values(array_filter(
                array_map(static fn (mixed $value): string => is_string($value) ? $value : '', $prefixes),
                static fn (string $value): bool => $value !== '',
            ));

            return new RequestMatcher($normalised);
        });

        $this->app->singleton(Payload::class, static function (Application $app): Payload {
            $config = $app->make('config');
            assert($config instanceof Repository);

            $traceDepth = $config->get('exception-response.trace_depth', 10);

            return new Payload(
                matcher: $app->make(RequestMatcher::class),
                includeExceptionClass: (bool) $config->get('exception-response.include_exception_class', false),
                includeTraceInDebug: (bool) $config->get('exception-response.include_trace_in_debug', true),
                debug: (bool) $config->get('app.debug', false),
                traceDepth: is_int($traceDepth) ? $traceDepth : 10,
            );
        });

        $this->app->singleton(Renderer::class, static function (Application $app): Renderer {
            return new Renderer(
                matcher: $app->make(RequestMatcher::class),
                payload: $app->make(Payload::class),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/exception-response.php' => config_path('exception-response.php'),
            ], 'exception-response-config');
        }
    }
}
