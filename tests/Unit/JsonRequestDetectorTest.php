<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Tests\Unit;

use AnilKumarThakur\ExceptionResponse\Support\JsonRequestDetector;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonRequestDetectorTest extends TestCase
{
    #[Test]
    public function it_matches_configured_api_prefixes(): void
    {
        $detector = new JsonRequestDetector(['api/*']);

        $request = Request::create('/api/users', 'GET');

        self::assertTrue($detector->wantsJson($request));
    }

    #[Test]
    public function it_falls_through_to_expects_json_when_path_does_not_match(): void
    {
        $detector = new JsonRequestDetector(['api/*']);

        $request = Request::create('/dashboard', 'GET');
        $request->headers->set('Accept', 'application/json');

        self::assertTrue($detector->wantsJson($request));
    }

    #[Test]
    public function it_returns_false_for_a_plain_web_request(): void
    {
        $detector = new JsonRequestDetector(['api/*']);

        $request = Request::create('/dashboard', 'GET');

        self::assertFalse($detector->wantsJson($request));
    }

    #[Test]
    public function it_supports_multiple_prefixes(): void
    {
        $detector = new JsonRequestDetector(['api/*', 'webhooks/*']);

        self::assertTrue($detector->wantsJson(Request::create('/webhooks/stripe', 'POST')));
        self::assertTrue($detector->wantsJson(Request::create('/api/v1/users', 'GET')));
        self::assertFalse($detector->wantsJson(Request::create('/admin', 'GET')));
    }
}
