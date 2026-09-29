<?php

use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Middleware\EnsureLocalPlayground;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['status' => 'ok']));

Route::middleware(EnsureLocalPlayground::class)->scopeBindings()->group(function (): void {
    Route::apiResource('projects', ProjectController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('projects.todos', TodoController::class)->only(['store', 'update', 'destroy']);
});
