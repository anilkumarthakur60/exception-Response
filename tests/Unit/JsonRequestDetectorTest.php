<?php

declare(strict_types=1);

use Anil\ExceptionResponse\Detectors\JsonRequestDetector;
use Illuminate\Http\Request;

it('matches requests to api/* paths', function (): void {
    $detector = new JsonRequestDetector(['api/*']);
    $request = Request::create('/api/users/1');

    expect($detector->shouldRenderJson($request))->toBeTrue();
});

it('does not match non-api paths without json accept header', function (): void {
    $detector = new JsonRequestDetector(['api/*']);
    $request = Request::create('/web/home');

    expect($detector->shouldRenderJson($request))->toBeFalse();
});

it('matches requests that expect json via accept header', function (): void {
    $detector = new JsonRequestDetector(['api/*']);
    $request = Request::create('/web/home');
    $request->headers->set('Accept', 'application/json');

    expect($detector->shouldRenderJson($request))->toBeTrue();
});

it('supports multiple patterns', function (): void {
    $detector = new JsonRequestDetector(['api/*', 'webhook/*']);
    $request = Request::create('/webhook/stripe');

    expect($detector->shouldRenderJson($request))->toBeTrue();
});

it('does not match when no patterns and no json accept header', function (): void {
    $detector = new JsonRequestDetector([]);
    $request = Request::create('/api/users');

    expect($detector->shouldRenderJson($request))->toBeFalse();
});
