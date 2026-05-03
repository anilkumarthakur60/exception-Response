<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Tests\Feature;

use AnilKumarThakur\ExceptionResponse\Tests\TestCase;
use BadMethodCallException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Routing\Router;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use PDOException;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class RendererTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        assert($router instanceof Router);

        $router->get('api/missing-model', static function (): void {
            abort(404, 'No such record.');
        });

        $router->get('api/unauthenticated', static function (): void {
            throw new AuthenticationException('You shall not pass.');
        });

        $router->post('api/validate', static function (): void {
            throw ValidationException::withMessages([
                'email' => ['The email field is required.'],
            ]);
        });

        $router->get('api/unauthorized', static function (): void {
            throw new AuthorizationException;
        });

        $router->get('api/model-missing', static function (): void {
            $exception = new ModelNotFoundException;
            /** @var class-string<Model> $model */
            $model = Model::class;
            $exception->setModel($model);

            throw $exception;
        });

        $router->match(['get', 'post'], 'api/method-not-allowed', static function (): void {
            //
        })->name('api.method-not-allowed');

        $router->get('api/throttled', static function (): void {
            throw new ThrottleRequestsException;
        });

        $router->post('api/csrf-mismatch', static function (): void {
            throw new TokenMismatchException;
        });

        $router->post('api/payload-too-large', static function (): void {
            throw new PostTooLargeException;
        });

        $router->get('api/query-error', static function (): void {
            throw new QueryException('mysql', 'SELECT 1', [], new PDOException('boom'));
        });

        $router->get('api/bad-method', static function (): void {
            throw new BadMethodCallException;
        });

        $router->get('api/invalid-argument', static function (): void {
            throw new InvalidArgumentException;
        });

        $router->get('api/binding-resolution', static function (): void {
            throw new BindingResolutionException;
        });

        $router->get('api/teapot', static function (): void {
            throw new HttpException(418, "I'm a teapot.");
        });

        $router->get('web/missing', static function (): void {
            abort(404);
        });
    }

    #[Test]
    public function it_returns_json_for_api_404(): void
    {
        $this->getJson('api/missing-model')
            ->assertStatus(404)
            ->assertJson(['message' => 'No such record.', 'error_code' => 'not_found']);
    }

    #[Test]
    public function it_returns_json_for_authentication_exception(): void
    {
        $this->getJson('api/unauthenticated')
            ->assertStatus(401)
            ->assertJson(['message' => 'You shall not pass.', 'error_code' => 'unauthenticated']);
    }

    #[Test]
    public function it_returns_validation_errors(): void
    {
        $this->postJson('api/validate')
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['email'], 'error_code'])
            ->assertJson(['error_code' => 'validation_failed']);
    }

    #[Test]
    public function it_renders_authorization_exception(): void
    {
        $this->getJson('api/unauthorized')
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.', 'error_code' => 'unauthorized']);
    }

    #[Test]
    public function it_renders_model_not_found_exception(): void
    {
        $this->getJson('api/model-missing')
            ->assertStatus(404)
            ->assertJson(['error_code' => 'model_not_found']);
    }

    #[Test]
    public function it_renders_method_not_allowed(): void
    {
        $this->call('PUT', 'api/method-not-allowed', server: ['HTTP_ACCEPT' => 'application/json'])
            ->assertStatus(405)
            ->assertJson(['error_code' => 'method_not_allowed']);
    }

    #[Test]
    public function it_renders_throttle_exception(): void
    {
        $this->getJson('api/throttled')
            ->assertStatus(429)
            ->assertJson(['error_code' => 'too_many_requests']);
    }

    #[Test]
    public function it_renders_token_mismatch_exception(): void
    {
        $this->postJson('api/csrf-mismatch')
            ->assertStatus(419)
            ->assertJson(['error_code' => 'csrf_token_mismatch']);
    }

    #[Test]
    public function it_renders_post_too_large_exception(): void
    {
        $this->postJson('api/payload-too-large')
            ->assertStatus(413)
            ->assertJson(['error_code' => 'payload_too_large']);
    }

    #[Test]
    public function it_renders_query_exception(): void
    {
        $this->getJson('api/query-error')
            ->assertStatus(500)
            ->assertJson(['error_code' => 'query_error']);
    }

    #[Test]
    public function it_renders_bad_method_call_exception(): void
    {
        $this->getJson('api/bad-method')
            ->assertStatus(500)
            ->assertJson(['error_code' => 'bad_method', 'message' => 'Bad method call.']);
    }

    #[Test]
    public function it_renders_invalid_argument_exception(): void
    {
        $this->getJson('api/invalid-argument')
            ->assertStatus(400)
            ->assertJson(['error_code' => 'invalid_argument', 'message' => 'Invalid argument.']);
    }

    #[Test]
    public function it_renders_binding_resolution_exception(): void
    {
        $this->getJson('api/binding-resolution')
            ->assertStatus(500)
            ->assertJson(['error_code' => 'binding_resolution']);
    }

    #[Test]
    public function it_renders_generic_http_exception_with_its_status(): void
    {
        $this->getJson('api/teapot')
            ->assertStatus(418)
            ->assertJson(['message' => "I'm a teapot."])
            ->assertJsonMissing(['error_code' => 'teapot']);
    }

    #[Test]
    public function it_does_not_intercept_web_routes(): void
    {
        $response = $this->get('web/missing');
        $response->assertStatus(404);
        self::assertStringNotContainsString('"message"', (string) $response->getContent());
    }

    #[Test]
    public function it_omits_error_code_when_config_disables_it(): void
    {
        $this->withConfig(['exception-response.include_error_code' => false]);

        $this->getJson('api/unauthenticated')
            ->assertStatus(401)
            ->assertJsonMissingPath('error_code');
    }
}
