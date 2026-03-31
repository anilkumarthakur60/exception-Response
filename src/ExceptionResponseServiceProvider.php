<?php

declare(strict_types=1);

namespace Anil\ExceptionResponse;

use Anil\ExceptionResponse\Builders\ExceptionPayloadBuilder;
use Anil\ExceptionResponse\Contracts\ExceptionHandlerRegistrarInterface;
use Anil\ExceptionResponse\Contracts\ExceptionPayloadBuilderInterface;
use Anil\ExceptionResponse\Contracts\ExceptionRendererInterface;
use Anil\ExceptionResponse\Contracts\JsonRequestDetectorInterface;
use Anil\ExceptionResponse\Contracts\StatusCodeResolverInterface;
use Anil\ExceptionResponse\Detectors\JsonRequestDetector;
use Anil\ExceptionResponse\Registrars\ExceptionHandlerRegistrar;
use Anil\ExceptionResponse\Renderers\ExceptionRenderer;
use Anil\ExceptionResponse\Resolvers\StatusCodeResolver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Registers all package bindings and publishes configuration.
 */
final class ExceptionResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'exception-response');

        $this->registerCustomBindings();

        $this->app->singletonIf(
            JsonRequestDetectorInterface::class,
            function (Application $app): JsonRequestDetectorInterface {
                /** @var list<string> $patterns */
                $patterns = $app->make('config')->get('exception-response.json_request_patterns', ['api/*']);

                return new JsonRequestDetector($patterns);
            },
        );

        $this->app->singletonIf(
            StatusCodeResolverInterface::class,
            function (Application $app): StatusCodeResolverInterface {
                /** @var array<class-string<\Throwable>, int> $customMap */
                $customMap = $app->make('config')->get('exception-response.status_code_map', []);

                return new StatusCodeResolver($customMap);
            },
        );

        $this->app->singletonIf(
            ExceptionPayloadBuilderInterface::class,
            function (Application $app): ExceptionPayloadBuilderInterface {
                /** @var bool $debug */
                $debug = $app->make('config')->get('exception-response.debug', $app->hasDebugModeEnabled());

                return new ExceptionPayloadBuilder($debug);
            },
        );

        $this->app->singletonIf(
            ExceptionRendererInterface::class,
            function (Application $app): ExceptionRendererInterface {
                /** @var bool $debug */
                $debug = $app->make('config')->get('exception-response.debug', $app->hasDebugModeEnabled());

                /** @var bool $includeHeader */
                $includeHeader = $app->make('config')->get('exception-response.include_exception_header', true);

                return new ExceptionRenderer(
                    statusCodeResolver: $app->make(StatusCodeResolverInterface::class),
                    payloadBuilder: $app->make(ExceptionPayloadBuilderInterface::class),
                    debug: $debug,
                    includeExceptionHeader: $includeHeader,
                );
            },
        );

        $this->app->singletonIf(
            ExceptionHandlerRegistrarInterface::class,
            function (Application $app): ExceptionHandlerRegistrarInterface {
                return new ExceptionHandlerRegistrar(
                    detector: $app->make(JsonRequestDetectorInterface::class),
                    renderer: $app->make(ExceptionRendererInterface::class),
                );
            },
        );
    }

    public function boot(): void
    {
        $this->publishes(
            [$this->configPath() => $this->app->configPath('exception-response.php')],
            'exception-response-config',
        );
    }

    private function configPath(): string
    {
        return dirname(__DIR__).'/config/exception-response.php';
    }

    private function registerCustomBindings(): void
    {
        /** @var array<class-string, class-string> $bindings */
        $bindings = $this->app->make('config')->get('exception-response.bindings', []);

        foreach ($bindings as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }
    }
}
