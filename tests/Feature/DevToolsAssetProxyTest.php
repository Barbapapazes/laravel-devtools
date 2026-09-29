<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Vite;

test('it serves DevTools SVG assets from the active Vite server locally', function () {
    app()->detectEnvironment(fn (): string => 'local');
    Vite::shouldReceive('isRunningHot')->once()->andReturn(true);
    Vite::shouldReceive('devServerUrl')->once()->andReturn('http://127.0.0.1:5173');
    Http::preventStrayRequests();
    Http::fake(['127.0.0.1:5173/__devtools-assets/vite-plus.svg' => Http::response('<svg>logo</svg>', 200, ['Content-Type' => 'image/svg+xml'])]);

    $response = $this->get('/__devtools-assets/vite-plus.svg');

    $response->assertOk()->assertHeader('Content-Type', 'image/svg+xml')->assertContent('<svg>logo</svg>');
    Http::assertSent(fn ($request): bool => $request->url() === 'http://127.0.0.1:5173/__devtools-assets/vite-plus.svg');
});

test('it forwards upstream missing assets without serving an HTML fallback', function () {
    app()->detectEnvironment(fn (): string => 'local');
    Vite::shouldReceive('isRunningHot')->once()->andReturn(true);
    Vite::shouldReceive('devServerUrl')->once()->andReturn('http://127.0.0.1:5173');
    Http::preventStrayRequests();
    Http::fake(['127.0.0.1:5173/__devtools-assets/missing.svg' => Http::response('Not Found', 404)]);

    $this->get('/__devtools-assets/missing.svg')->assertNotFound();
    Http::assertSentCount(1);
});

test('it proxies assets when Laravel Vite advertises the IPv6 loopback origin', function () {
    app()->detectEnvironment(fn (): string => 'local');
    Vite::shouldReceive('isRunningHot')->once()->andReturn(true);
    Vite::shouldReceive('devServerUrl')->once()->andReturn('http://[::1]:5173');
    Http::preventStrayRequests();
    Http::fake(['[::1]:5173/__devtools-assets/vite-plus.svg' => Http::response('<svg>ipv6</svg>', 200, ['Content-Type' => 'image/svg+xml'])]);

    $this->get('/__devtools-assets/vite-plus.svg')->assertOk()->assertContent('<svg>ipv6</svg>');

    Http::assertSent(fn ($request): bool => $request->url() === 'http://[::1]:5173/__devtools-assets/vite-plus.svg');
});

test('it hides DevTools assets outside the local environment', function () {
    app()->detectEnvironment(fn (): string => 'production');
    Http::preventStrayRequests();

    $this->get('/__devtools-assets/vite-plus.svg')->assertNotFound();

    Http::assertNothingSent();
});

test('it rejects non-loopback Vite origins', function () {
    app()->detectEnvironment(fn (): string => 'local');
    Vite::shouldReceive('isRunningHot')->once()->andReturn(true);
    Vite::shouldReceive('devServerUrl')->once()->andReturn('http://example.com:5173');
    Http::preventStrayRequests();

    $this->get('/__devtools-assets/vite-plus.svg')->assertNotFound();

    Http::assertNothingSent();
});

test('it rejects non-SVG paths', function () {
    app()->detectEnvironment(fn (): string => 'local');
    Http::preventStrayRequests();

    $this->get('/__devtools-assets/embedded.js')->assertNotFound();
    $this->get('/__devtools-assets/../vite-plus.svg')->assertNotFound();

    Http::assertNothingSent();
});
