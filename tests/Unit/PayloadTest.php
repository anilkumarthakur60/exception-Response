<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Tests\Unit;

use AnilKumarThakur\ExceptionResponse\Events\ExceptionRendered;
use AnilKumarThakur\ExceptionResponse\Support\Payload;
use AnilKumarThakur\ExceptionResponse\Support\RequestMatcher;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PayloadTest extends TestCase
{
    #[Test]
    public function it_returns_null_for_non_json_requests(): void
    {
        $payload = $this->makePayload();

        $response = $payload->make(
            Request::create('/web', 'GET'),
            new RuntimeException('boom'),
            500,
            'boom',
        );

        self::assertNull($response);
    }

    #[Test]
    public function it_builds_a_minimal_payload_in_production_mode(): void
    {
        $payload = $this->makePayload(debug: false);

        $response = $payload->make(
            Request::create('/api/users', 'GET'),
            new RuntimeException('boom'),
            500,
            'boom',
        );

        self::assertNotNull($response);
        self::assertSame(500, $response->getStatusCode());

        $body = json_decode((string) $response->getContent(), true);
        self::assertIsArray($body);
        self::assertSame(['message' => 'boom'], $body);
    }

    #[Test]
    public function it_includes_exception_class_when_configured(): void
    {
        $payload = $this->makePayload(includeExceptionClass: true);

        $response = $payload->make(
            Request::create('/api/users', 'GET'),
            new RuntimeException('boom'),
            500,
            'boom',
        );

        self::assertNotNull($response);
        $body = json_decode((string) $response->getContent(), true);
        self::assertIsArray($body);
        self::assertSame(RuntimeException::class, $body['exception'] ?? null);
    }

    #[Test]
    public function it_includes_trace_in_debug_mode(): void
    {
        $payload = $this->makePayload(debug: true, includeTraceInDebug: true);

        $response = $payload->make(
            Request::create('/api/users', 'GET'),
            new RuntimeException('boom'),
            500,
            'boom',
        );

        self::assertNotNull($response);
        $body = json_decode((string) $response->getContent(), true);
        self::assertIsArray($body);
        self::assertArrayHasKey('file', $body);
        self::assertArrayHasKey('line', $body);
        self::assertArrayHasKey('trace', $body);
    }

    #[Test]
    public function it_includes_error_code_when_configured(): void
    {
        $payload = $this->makePayload(includeErrorCode: true);

        $response = $payload->make(
            Request::create('/api/users', 'GET'),
            new RuntimeException('boom'),
            401,
            'Unauthenticated.',
            'unauthenticated',
        );

        self::assertNotNull($response);
        $body = json_decode((string) $response->getContent(), true);
        self::assertIsArray($body);
        self::assertSame('unauthenticated', $body['error_code'] ?? null);
    }

    #[Test]
    public function it_omits_error_code_when_disabled(): void
    {
        $payload = $this->makePayload(includeErrorCode: false);

        $response = $payload->make(
            Request::create('/api/users', 'GET'),
            new RuntimeException('boom'),
            401,
            'Unauthenticated.',
            'unauthenticated',
        );

        self::assertNotNull($response);
        $body = json_decode((string) $response->getContent(), true);
        self::assertIsArray($body);
        self::assertArrayNotHasKey('error_code', $body);
    }

    #[Test]
    public function it_merges_extras_into_payload(): void
    {
        $payload = $this->makePayload();

        $response = $payload->make(
            Request::create('/api/users', 'GET'),
            new RuntimeException('boom'),
            422,
            'The given data was invalid.',
            'validation_failed',
            ['errors' => ['email' => ['required']]],
        );

        self::assertNotNull($response);
        $body = json_decode((string) $response->getContent(), true);
        self::assertIsArray($body);
        self::assertSame(['email' => ['required']], $body['errors'] ?? null);
    }

    #[Test]
    public function it_dispatches_exception_rendered_event(): void
    {
        $events = new Dispatcher;

        $captured = null;
        $events->listen(ExceptionRendered::class, static function (ExceptionRendered $event) use (&$captured): void {
            $captured = $event;
        });

        $payload = $this->makePayload(events: $events);
        $exception = new RuntimeException('boom');

        $payload->make(
            Request::create('/api/users', 'GET'),
            $exception,
            500,
            'boom',
        );

        self::assertInstanceOf(ExceptionRendered::class, $captured);
        self::assertSame($exception, $captured->exception);
    }

    private function makePayload(
        bool $includeExceptionClass = false,
        bool $includeTraceInDebug = false,
        bool $includeErrorCode = false,
        bool $debug = false,
        int $traceDepth = 5,
        ?Dispatcher $events = null,
    ): Payload {
        return new Payload(
            matcher: new RequestMatcher(['api/*']),
            events: $events ?? new Dispatcher,
            includeExceptionClass: $includeExceptionClass,
            includeTraceInDebug: $includeTraceInDebug,
            includeErrorCode: $includeErrorCode,
            debug: $debug,
            traceDepth: $traceDepth,
        );
    }
}
