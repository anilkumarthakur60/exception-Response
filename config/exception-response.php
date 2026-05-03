<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API Path Prefixes
    |--------------------------------------------------------------------------
    |
    | Requests matching any of these patterns (or sending Accept: application/json)
    | will receive JSON exception responses. Anything else falls through to
    | Laravel's default rendering.
    |
    */
    'api_prefixes' => ['api/*'],

    /*
    |--------------------------------------------------------------------------
    | Include Exception Class
    |--------------------------------------------------------------------------
    |
    | When true, the response payload includes the FQCN of the thrown
    | exception under the "exception" key. Useful for debugging clients.
    |
    */
    'include_exception_class' => false,

    /*
    |--------------------------------------------------------------------------
    | Include Trace While Debugging
    |--------------------------------------------------------------------------
    |
    | When app.debug is true and this is true, a truncated stack trace plus
    | file/line info is added to the JSON payload.
    |
    */
    'include_trace_in_debug' => true,

    /*
    |--------------------------------------------------------------------------
    | Trace Depth
    |--------------------------------------------------------------------------
    |
    | Number of stack frames to include when "include_trace_in_debug" is on.
    |
    */
    'trace_depth' => 10,

    /*
    |--------------------------------------------------------------------------
    | Include Machine-Readable Error Code
    |--------------------------------------------------------------------------
    |
    | When true, the response includes a stable "error_code" field (e.g.
    | "validation_failed", "unauthenticated") that clients can switch on
    | without parsing localized messages.
    |
    */
    'include_error_code' => true,

];
