<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Tests\Unit;

use AnilKumarThakur\ExceptionResponse\Support\JsonRequestDetector;
use AnilKumarThakur\ExceptionResponse\Support\JsonResponsePayload;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JsonResponsePayloadTest extends TestCase
{
    #[Test]
    public function it_returns_null_for_non_json_requests(): void
    {
        $payload = $this->makePayload();

        $response = $payload->build(
            Request::create('/web', 'GET'),
            new RuntimeException('boom'),
            500,
        );

        self::assertNull($response);
    }

    #[Test]
    public function it_builds_a_minimal_payload_in_production_mode(): void
    {
        $payload = $this->makePayload(debug: false);

        $response = $payload->build(
            Request::create('/api/users', 'GET'),
            new RuntimeException('boom'),
            500,
        );

        self::assertNotNull($response);
        self::assertSame(500, $response->getStatusCode());

        $body = json_decode((string) $response->getContent(), true);
        self::assertIsArray($body);
        self::assertSame(['message' => 'boom'], $body);
    }

    #[Test]
    public function it_falls_back_to_provided_message_when_exception_is_blank(): void
    {
        $payload = $this->makePayload();

        $response = $payload->build(
            Request::create('/api/users', 'GET'),
            new RuntimeException(''),
            404,
            'Resource not found.',
        );

        self::assertNotNull($response);
        $body = json_decode((string) $response->getContent(), true);
        self::assertIsArray($body);
        self::assertSame('Resource not found.', $body['message'] ?? null);
    }

    #[Test]
    public function it_includes_class_when_configured(): void
    {
        $payload = $this->makePayload(includeExceptionClass: true);

        $response = $payload->build(
            Request::create('/api/users', 'GET'),
            new RuntimeException('boom'),
            500,
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

        $response = $payload->build(
            Request::create('/api/users', 'GET'),
            new RuntimeException('boom'),
            500,
        );

        self::assertNotNull($response);
        $body = json_decode((string) $response->getContent(), true);
        self::assertIsArray($body);
        self::assertArrayHasKey('file', $body);
        self::assertArrayHasKey('line', $body);
        self::assertArrayHasKey('trace', $body);
    }

    private function makePayload(
        bool $includeExceptionClass = false,
        bool $includeTraceInDebug = false,
        bool $debug = false,
        int $traceDepth = 5,
    ): JsonResponsePayload {
        return new JsonResponsePayload(
            detector: new JsonRequestDetector(['api/*']),
            includeExceptionClass: $includeExceptionClass,
            includeTraceInDebug: $includeTraceInDebug,
            debug: $debug,
            traceDepth: $traceDepth,
        );
    }
}
