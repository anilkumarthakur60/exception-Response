<?php

declare(strict_types=1);

use Anil\ExceptionResponse\Builders\ExceptionPayloadBuilder;
use Anil\ExceptionResponse\Contracts\ExceptionPayloadBuilderInterface;
use Anil\ExceptionResponse\Contracts\ExceptionRendererInterface;
use Anil\ExceptionResponse\Contracts\JsonRequestDetectorInterface;
use Anil\ExceptionResponse\Contracts\StatusCodeResolverInterface;
use Anil\ExceptionResponse\Renderers\ExceptionRenderer;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

function registerExceptionHandling(Application $app): void
{
    /** @var Handler $handler */
    $handler = $app->make(ExceptionHandler::class);
    $detector = $app->make(JsonRequestDetectorInterface::class);
    $renderer = $app->make(ExceptionRendererInterface::class);

    $handler->shouldRenderJsonWhen(
        fn (Request $request): bool => $detector->shouldRenderJson($request),
    );

    $handler->renderable(
        fn (Throwable $e, Request $request) => $detector->shouldRenderJson($request)
            ? $renderer->render($request, $e)
            : null,
    );
}

function registerRoutes(): void
{
    Route::get('api/users/{id}', fn () => throw new NotFoundHttpException);
    Route::post('api/register', function (): never {
        $validator = Validator::make(
            [],
            ['email' => 'required', 'password' => 'required|min:8'],
        );
        throw new ValidationException($validator);
    });
    Route::get('api/test', fn () => throw new RuntimeException('Something broke'));
    Route::get('web/home', fn () => throw new RuntimeException('Web error'));
}

describe('production mode', function (): void {
    beforeEach(function (): void {
        registerExceptionHandling($this->app);
        registerRoutes();
    });

    it('returns 404 json for NotFoundHttpException on api routes', function (): void {
        $this->getJson('api/users/1')
            ->assertStatus(404)
            ->assertJson([
                'status' => 404,
            ])
            ->assertJsonMissingPath('trace');
    });

    it('returns 422 json with errors for ValidationException on api routes', function (): void {
        $this->postJson('api/register')
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'status',
                'errors' => [
                    'email',
                    'password',
                ],
            ]);
    });

    it('returns 500 json without trace in production mode', function (): void {
        $this->getJson('api/test')
            ->assertStatus(500)
            ->assertJson([
                'message' => 'Something broke',
                'status' => 500,
            ])
            ->assertJsonMissingPath('trace')
            ->assertJsonMissingPath('exception');
    });

    it('does not return json for non-api requests without json accept header', function (): void {
        $response = $this->get('web/home');

        $response->assertStatus(500);
        expect($response->headers->get('Content-Type'))->not->toContain('application/json');
    });
});

describe('debug mode', function (): void {
    beforeEach(function (): void {
        $this->app->singleton(
            ExceptionPayloadBuilderInterface::class,
            fn () => new ExceptionPayloadBuilder(debug: true),
        );
        $this->app->singleton(
            ExceptionRendererInterface::class,
            fn ($app) => new ExceptionRenderer(
                statusCodeResolver: $app->make(StatusCodeResolverInterface::class),
                payloadBuilder: $app->make(ExceptionPayloadBuilderInterface::class),
                debug: true,
                includeExceptionHeader: true,
            ),
        );

        registerExceptionHandling($this->app);
        registerRoutes();
    });

    it('returns 500 json with trace in debug mode', function (): void {
        $this->getJson('api/test')
            ->assertStatus(500)
            ->assertJson([
                'message' => 'Something broke',
                'status' => 500,
                'exception' => 'RuntimeException',
            ])
            ->assertJsonStructure(['file', 'line', 'trace']);
    });

    it('includes X-Exception-Type header in debug mode', function (): void {
        $response = $this->getJson('api/test');

        expect($response->headers->get('X-Exception-Type'))->toBe('RuntimeException');
    });
});
