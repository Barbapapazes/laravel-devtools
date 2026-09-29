<?php

use App\Http\Controllers\Api\ProjectController;
use Devtools\LaravelDevtools\Laravel\LaravelDevtoolsServiceProvider;
use Illuminate\Support\Facades\Route;

test('the local API lists Laravel routes from the installed package', function () {
    app()->detectEnvironment(fn (): string => 'local');
    app()->register(LaravelDevtoolsServiceProvider::class, force: true);
    Route::post('/example/{id}/{slug?}', fn () => 'ok')->middleware('throttle:api')->name('example.store');

    $response = $this->getJson('/__route-inspector/routes')->assertOk();
    $route = collect($response->json())->firstWhere('name', 'example.store');

    expect($route['uri'])->toBe('example/{id}/{slug?}');
    expect($route['methods'])->toBe(['POST']);
    expect($route['parameters'])->toBe([['name' => 'id', 'optional' => false], ['name' => 'slug', 'optional' => true]]);
    expect($route['middleware'])->toBe(['throttle:api']);
    expect($route['resolvedMiddleware'])->toHaveCount(1);
    expect($route['resolvedMiddleware'][0])->toContain('ThrottleRequests');
    expect($route['source']['file'])->toBe('tests/Feature/RouteInspectorApiTest.php');
    expect($route['source']['line'])->toBeInt();
});

test('the route feed resolves controller handler locations and middleware order', function () {
    app()->detectEnvironment(fn (): string => 'local');
    app()->register(LaravelDevtoolsServiceProvider::class, force: true);
    Route::get('/inspect-controller', ProjectController::class.'@index')
        ->middleware(['throttle:api', 'auth'])
        ->name('inspect.controller');

    $response = $this->getJson('/__route-inspector/routes')->assertOk();
    $route = collect($response->json())->firstWhere('name', 'inspect.controller');

    expect($route['source']['file'])->toBe('app/Http/Controllers/Api/ProjectController.php');
    expect($route['resolvedMiddleware'])->toHaveCount(2);
    expect($route['resolvedMiddleware'][0])->toContain('Authenticate');
    expect($route['resolvedMiddleware'][1])->toContain('ThrottleRequests');
});

test('the route feed is not available outside local development', function () {
    app()->detectEnvironment(fn (): string => 'production');
    app()->register(LaravelDevtoolsServiceProvider::class, force: true);

    expect(Route::getRoutes()->getByName('route-inspector.routes'))->toBeNull();
    expect(Route::getRoutes()->getByName('model-inspector.models'))->toBeNull();

    $this->getJson('/__route-inspector/routes')->assertNotFound();
});

test('the route feed rejects non-loopback requests', function () {
    app()->detectEnvironment(fn (): string => 'local');
    app()->register(LaravelDevtoolsServiceProvider::class, force: true);

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.1'])
        ->getJson('/__route-inspector/routes')
        ->assertNotFound();
});
