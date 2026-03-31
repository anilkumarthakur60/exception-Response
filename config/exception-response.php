<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | JSON Request Patterns
    |--------------------------------------------------------------------------
    |
    | URL patterns that should trigger JSON exception responses.
    | Supports wildcards. Any request matching these patterns will receive
    | a structured JSON error response instead of an HTML error page.
    |
    */
    'json_request_patterns' => ['api/*'],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Whether to include debug information (exception class, file, line, trace)
    | in the JSON response. Defaults to the application's APP_DEBUG value.
    |
    */
    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Status Code Map
    |--------------------------------------------------------------------------
    |
    | Additional exception-to-status-code mappings.
    | Use the fully qualified class name as the key and the HTTP status code
    | as the value. These merge with and can override the built-in mappings.
    |
    */
    'status_code_map' => [
        // \App\Exceptions\PaymentFailedException::class => 402,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exception Type Header
    |--------------------------------------------------------------------------
    |
    | Whether to include the 'X-Exception-Type' header in debug mode.
    | This header contains the fully qualified class name of the exception.
    |
    */
    'include_exception_header' => true,

    /*
    |--------------------------------------------------------------------------
    | Custom Bindings
    |--------------------------------------------------------------------------
    |
    | Replace any default contract implementation with your own.
    | Use the contract interface FQCN as the key and your implementation
    | class FQCN as the value.
    |
    */
    'bindings' => [
        // \Anil\ExceptionResponse\Contracts\StatusCodeResolverInterface::class
        //     => \App\Exceptions\MyStatusCodeResolver::class,
    ],
];
