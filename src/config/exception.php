<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API Path Prefixes
    |--------------------------------------------------------------------------
    |
    | Requests matching any of these patterns (or that send Accept: application/json)
    | will receive JSON exception responses. Anything else falls through to
    | Laravel's default rendering.
    |
    */
    'api_prefixes' => ['api/*'],

    /*
    |--------------------------------------------------------------------------
    | Include Exception Class Name
    |--------------------------------------------------------------------------
    |
    | When true, the response payload will include the FQCN of the thrown
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

];
