<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Tests\Feature;

use AnilKumarThakur\ExceptionResponse\Tests\TestCase;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Routing\Router;
use PHPUnit\Framework\Attributes\Test;

final class PayloadShapeTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        assert($router instanceof Router);

        $router->get('api/boom', static function (): void {
            throw new AuthenticationException('You shall not pass.');
        });
    }

    #[Test]
    public function production_mode_returns_minimal_payload(): void
    {
        $this->withConfig([
            'app.debug' => false,
            'exception-response.include_exception_class' => false,
            'exception-response.include_trace_in_debug' => true,
        ]);

        $response = $this->getJson('api/boom')->assertStatus(401);

        $response->assertJsonMissingPath('exception');
        $response->assertJsonMissingPath('file');
        $response->assertJsonMissingPath('line');
        $response->assertJsonMissingPath('trace');
    }

    #[Test]
    public function debug_mode_includes_file_line_and_trace(): void
    {
        $this->withConfig([
            'app.debug' => true,
            'exception-response.include_trace_in_debug' => true,
            'exception-response.trace_depth' => 3,
        ]);

        $response = $this->getJson('api/boom')->assertStatus(401);

        $response->assertJsonStructure(['file', 'line', 'trace']);

        $body = $response->json();
        assert(is_array($body));
        assert(is_array($body['trace']));
        self::assertLessThanOrEqual(3, count($body['trace']));
    }

    #[Test]
    public function debug_mode_omits_trace_when_disabled(): void
    {
        $this->withConfig([
            'app.debug' => true,
            'exception-response.include_trace_in_debug' => false,
        ]);

        $response = $this->getJson('api/boom')->assertStatus(401);

        $response->assertJsonMissingPath('file');
        $response->assertJsonMissingPath('line');
        $response->assertJsonMissingPath('trace');
    }

    #[Test]
    public function exception_class_is_disclosed_when_configured(): void
    {
        $this->withConfig(['exception-response.include_exception_class' => true]);

        $this->getJson('api/boom')
            ->assertStatus(401)
            ->assertJson(['exception' => AuthenticationException::class]);
    }
}
