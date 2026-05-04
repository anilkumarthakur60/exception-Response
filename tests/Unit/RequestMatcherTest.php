<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Tests\Unit;

use AnilKumarThakur\ExceptionResponse\Support\RequestMatcher;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RequestMatcherTest extends TestCase
{
    #[Test]
    public function it_matches_configured_api_prefixes(): void
    {
        $matcher = new RequestMatcher(['api/*']);

        $request = Request::create('/api/users', 'GET');

        self::assertTrue($matcher->matches($request));
    }

    #[Test]
    public function it_falls_through_to_expects_json_when_path_does_not_match(): void
    {
        $matcher = new RequestMatcher(['api/*']);

        $request = Request::create('/dashboard', 'GET');
        $request->headers->set('Accept', 'application/json');

        self::assertTrue($matcher->matches($request));
    }

    #[Test]
    public function it_returns_false_for_a_plain_web_request(): void
    {
        $matcher = new RequestMatcher(['api/*']);

        $request = Request::create('/dashboard', 'GET');

        self::assertFalse($matcher->matches($request));
    }

    #[Test]
    public function it_supports_multiple_prefixes(): void
    {
        $matcher = new RequestMatcher(['api/*', 'webhooks/*']);

        self::assertTrue($matcher->matches(Request::create('/webhooks/stripe', 'POST')));
        self::assertTrue($matcher->matches(Request::create('/api/v1/users', 'GET')));
        self::assertFalse($matcher->matches(Request::create('/admin', 'GET')));
    }

    #[Test]
    public function it_matches_xhr_requests_via_expects_json(): void
    {
        $matcher = new RequestMatcher(['api/*']);

        $request = Request::create('/dashboard', 'GET');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');

        self::assertTrue($matcher->matches($request));
    }

    #[Test]
    public function it_matches_head_requests_to_api_prefixes(): void
    {
        $matcher = new RequestMatcher(['api/*']);

        $request = Request::create('/api/users', 'HEAD');

        self::assertTrue($matcher->matches($request));
    }

    #[Test]
    public function empty_prefix_list_only_matches_explicit_json_requests(): void
    {
        $matcher = new RequestMatcher([]);

        $jsonRequest = Request::create('/anything', 'GET');
        $jsonRequest->headers->set('Accept', 'application/json');

        $webRequest = Request::create('/anything', 'GET');

        self::assertTrue($matcher->matches($jsonRequest));
        self::assertFalse($matcher->matches($webRequest));
    }
}
