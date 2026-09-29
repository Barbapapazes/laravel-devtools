<?php

use Devtools\LaravelDevtools\Laravel\LaravelDevtoolsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the local API lists application model structure without querying records', function () {
    app()->detectEnvironment(fn (): string => 'local');
    app()->register(LaravelDevtoolsServiceProvider::class, force: true);

    $response = $this->getJson('/__model-inspector/models')
        ->assertOk()
        ->assertJsonFragment([
            'class' => 'App\\Models\\User',
            'name' => 'User',
            'table' => 'users',
            'key' => 'id',
            'timestamps' => true,
            'softDeletes' => false,
        ])
        ->assertJsonFragment(['password' => 'hashed']);

    $user = collect($response->json())->firstWhere('class', 'App\\Models\\User');

    expect($user['fields'])->toContain(
        ['name' => 'name', 'type' => 'varchar', 'nullable' => false, 'cast' => null],
        ['name' => 'email_verified_at', 'type' => 'datetime', 'nullable' => true, 'cast' => 'datetime'],
        ['name' => 'password', 'type' => 'varchar', 'nullable' => false, 'cast' => 'hashed'],
    );
});

test('the model feed includes database fields without explicit casts', function () {
    app()->detectEnvironment(fn (): string => 'local');
    app()->register(LaravelDevtoolsServiceProvider::class, force: true);

    $response = $this->getJson('/__model-inspector/models')->assertOk();
    $project = collect($response->json())->firstWhere('class', 'App\\Models\\Project');

    expect($project['fields'])->toContain(
        ['name' => 'name', 'type' => 'varchar', 'nullable' => false, 'cast' => null],
        ['name' => 'description', 'type' => 'text', 'nullable' => true, 'cast' => null],
    );
});

test('the model feed exposes relationships indexes and foreign keys', function () {
    app()->detectEnvironment(fn (): string => 'local');
    app()->register(LaravelDevtoolsServiceProvider::class, force: true);

    $response = $this->getJson('/__model-inspector/models')->assertOk();
    $models = collect($response->json());
    $project = $models->firstWhere('class', 'App\\Models\\Project');
    $todo = $models->firstWhere('class', 'App\\Models\\Todo');

    expect($project['relationships'])->toContain(['name' => 'todos', 'type' => 'HasMany', 'related' => 'App\\Models\\Todo']);
    expect($todo['relationships'])->toContain(['name' => 'project', 'type' => 'BelongsTo', 'related' => 'App\\Models\\Project']);
    expect($todo['indexes'])->toContain(['name' => 'primary', 'columns' => ['id'], 'unique' => true, 'primary' => true]);
    expect($todo['foreignKeys'])->toContain(['columns' => ['project_id'], 'foreignTable' => 'projects', 'foreignColumns' => ['id'], 'onDelete' => 'cascade']);
});

test('the model feed returns 404 outside local development', function () {
    $this->getJson('/__model-inspector/models')->assertNotFound();
});

test('the model feed returns 404 for non-loopback requests', function () {
    app()->detectEnvironment(fn (): string => 'local');
    app()->register(LaravelDevtoolsServiceProvider::class, force: true);

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.1'])
        ->getJson('/__model-inspector/models')
        ->assertNotFound();
});
