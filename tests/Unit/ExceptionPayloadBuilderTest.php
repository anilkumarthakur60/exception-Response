<?php

declare(strict_types=1);

use Anil\ExceptionResponse\Builders\ExceptionPayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

it('builds production payload without debug information', function (): void {
    $builder = new ExceptionPayloadBuilder(debug: false);
    $request = Request::create('/api/test');
    $exception = new RuntimeException('Something went wrong.');

    $payload = $builder->build($request, $exception, 500);

    expect($payload)
        ->toHaveKey('message', 'Something went wrong.')
        ->toHaveKey('status', 500)
        ->not->toHaveKey('exception')
        ->not->toHaveKey('file')
        ->not->toHaveKey('line')
        ->not->toHaveKey('trace');
});

it('builds debug payload with exception details', function (): void {
    $builder = new ExceptionPayloadBuilder(debug: true);
    $request = Request::create('/api/test');
    $exception = new RuntimeException('Debug error.');

    $payload = $builder->build($request, $exception, 500);

    expect($payload)
        ->toHaveKey('message', 'Debug error.')
        ->toHaveKey('status', 500)
        ->toHaveKey('exception', 'RuntimeException')
        ->toHaveKey('file')
        ->toHaveKey('line')
        ->toHaveKey('trace');

    expect($payload['trace'])->toBeArray();
});

it('includes validation errors for ValidationException', function (): void {
    $builder = new ExceptionPayloadBuilder(debug: false);
    $request = Request::create('/api/register', 'POST');
    $validator = Validator::make(
        [],
        ['email' => 'required', 'password' => 'required|min:8'],
    );
    $exception = new ValidationException($validator);

    $payload = $builder->build($request, $exception, 422);

    expect($payload)
        ->toHaveKey('status', 422)
        ->toHaveKey('errors');

    expect($payload['errors'])->toHaveKey('email')->toHaveKey('password');
});

it('includes exception code when non-zero', function (): void {
    $builder = new ExceptionPayloadBuilder(debug: false);
    $request = Request::create('/api/test');
    $exception = new RuntimeException('Error with code.', 42);

    $payload = $builder->build($request, $exception, 500);

    expect($payload)->toHaveKey('code', 42);
});

it('omits exception code when zero', function (): void {
    $builder = new ExceptionPayloadBuilder(debug: false);
    $request = Request::create('/api/test');
    $exception = new RuntimeException('Error without code.');

    $payload = $builder->build($request, $exception, 500);

    expect($payload)->not->toHaveKey('code');
});

it('provides a default message for empty exception messages', function (): void {
    $builder = new ExceptionPayloadBuilder(debug: false);
    $request = Request::create('/api/test');
    $exception = new RuntimeException('');

    $payload = $builder->build($request, $exception, 404);

    expect($payload)->toHaveKey('message', 'Record not found.');
});
