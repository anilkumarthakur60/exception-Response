<?php

declare(strict_types=1);

use Anil\ExceptionResponse\Resolvers\StatusCodeResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

it('resolves NotFoundHttpException to 404', function (): void {
    $resolver = new StatusCodeResolver;

    expect($resolver->resolve(new NotFoundHttpException))->toBe(404);
});

it('resolves AuthenticationException to 401', function (): void {
    $resolver = new StatusCodeResolver;

    expect($resolver->resolve(new AuthenticationException))->toBe(401);
});

it('resolves AuthorizationException to 403', function (): void {
    $resolver = new StatusCodeResolver;

    expect($resolver->resolve(new AuthorizationException))->toBe(403);
});

it('resolves ValidationException to 422', function (): void {
    $resolver = new StatusCodeResolver;
    $validator = Validator::make([], ['email' => 'required']);
    $exception = new ValidationException($validator);

    expect($resolver->resolve($exception))->toBe(422);
});

it('resolves ModelNotFoundException to 404', function (): void {
    $resolver = new StatusCodeResolver;

    expect($resolver->resolve(new ModelNotFoundException))->toBe(404);
});

it('resolves MethodNotAllowedHttpException to 405', function (): void {
    $resolver = new StatusCodeResolver;

    expect($resolver->resolve(new MethodNotAllowedHttpException([])))->toBe(405);
});

it('resolves TokenMismatchException to 419', function (): void {
    $resolver = new StatusCodeResolver;

    expect($resolver->resolve(new TokenMismatchException))->toBe(419);
});

it('resolves TooManyRequestsHttpException to 429', function (): void {
    $resolver = new StatusCodeResolver;

    expect($resolver->resolve(new TooManyRequestsHttpException))->toBe(429);
});

it('resolves generic HttpException using getStatusCode', function (): void {
    $resolver = new StatusCodeResolver;

    expect($resolver->resolve(new HttpException(503)))->toBe(503);
});

it('resolves unknown exceptions to 500', function (): void {
    $resolver = new StatusCodeResolver;

    expect($resolver->resolve(new RuntimeException('test')))->toBe(500);
});

it('allows custom map to override built-in mappings', function (): void {
    $resolver = new StatusCodeResolver([
        NotFoundHttpException::class => 410,
    ]);

    expect($resolver->resolve(new NotFoundHttpException))->toBe(410);
});

it('allows custom map to add new exception mappings', function (): void {
    $resolver = new StatusCodeResolver([
        RuntimeException::class => 503,
    ]);

    expect($resolver->resolve(new RuntimeException('test')))->toBe(503);
});
