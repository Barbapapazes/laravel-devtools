<?php

namespace Devtools\LaravelDevtools\Laravel;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelDevtoolsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->environment('local')) {
            return;
        }

        Route::get('/__route-inspector/routes', Http\Controllers\RouteIndexController::class)
            ->name('route-inspector.routes');

        Route::get('/__model-inspector/models', Http\Controllers\ModelIndexController::class)
            ->name('model-inspector.models');
    }
}
