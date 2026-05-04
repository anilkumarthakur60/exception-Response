<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Events;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final readonly class ExceptionRendered
{
    public function __construct(
        public Request $request,
        public Throwable $exception,
        public JsonResponse $response,
    ) {}
}
