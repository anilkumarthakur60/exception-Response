<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Tests\Feature;

use AnilKumarThakur\ExceptionResponse\Tests\TestCase;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Routing\Router;
use PHPUnit\Framework\Attributes\Test;

final class TranslationTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        assert($router instanceof Router);

        $router->get('api/silent-401', static function (): void {
            throw new AuthenticationException;
        });
    }

    #[Test]
    public function it_uses_english_messages_by_default(): void
    {
        $this->getJson('api/silent-401')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function it_translates_messages_when_locale_changes(): void
    {
        $this->app?->setLocale('es');

        $this->getJson('api/silent-401')
            ->assertStatus(401)
            ->assertJson(['message' => 'No autenticado.']);
    }

    #[Test]
    public function it_falls_back_to_english_for_unknown_locale(): void
    {
        $this->app?->setLocale('xx');
        $this->app?->setFallbackLocale('en');

        $this->getJson('api/silent-401')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }
}
