<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Tests\Feature;

use AnilKumarThakur\ExceptionResponse\Tests\TestCase;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Routing\Router;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

final class ApiExceptionRendererTest extends TestCase
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

        $router->get('web/missing', static function (): void {
            abort(404);
        });
    }

    #[Test]
    public function it_returns_json_for_api_404(): void
    {
        $this->getJson('api/missing-model')
            ->assertStatus(404)
            ->assertExactJson(['message' => 'No such record.']);
    }

    #[Test]
    public function it_returns_json_for_authentication_exception(): void
    {
        $this->getJson('api/unauthenticated')
            ->assertStatus(401)
            ->assertJson(['message' => 'You shall not pass.']);
    }

    #[Test]
    public function it_returns_validation_errors(): void
    {
        $this->postJson('api/validate')
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['email']]);
    }

    #[Test]
    public function it_does_not_intercept_web_routes(): void
    {
        $response = $this->get('web/missing');
        $response->assertStatus(404);
        self::assertStringNotContainsString('"message"', (string) $response->getContent());
    }
}
