<?php

declare(strict_types=1);

namespace AnilKumarThakur\ExceptionResponse\Support;

use Illuminate\Http\Request;

readonly class JsonRequestDetector
{
    /**
     * @param list<string> $apiPrefixes
     */
    public function __construct(
        private array $apiPrefixes,
    ) {
    }

    public function wantsJson(Request $request): bool
    {
        foreach ($this->apiPrefixes as $prefix) {
            if ($request->is($prefix)) {
                return true;
            }
        }

        return $request->expectsJson();
    }
}
