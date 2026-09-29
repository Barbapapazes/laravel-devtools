<?php

use App\Models\Project;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('projects include their todos and exclude unrelated records', function () {
    $project = Project::factory()->create(['name' => 'Work']);
    Todo::factory()->for($project)->create(['title' => 'Ship feature', 'is_completed' => true]);
    Project::factory()->create(['name' => 'Home']);

    $this->getJson('/api/projects')->assertOk()
        ->assertJsonCount(2)
        ->assertJsonFragment(['name' => 'Work', 'title' => 'Ship feature', 'is_completed' => true]);
});

test('projects can be created renamed and deleted with their todos', function () {
    $projectId = $this->postJson('/api/projects', ['name' => 'Work', 'description' => 'Important work'])
        ->assertCreated()->assertJsonPath('name', 'Work')->json('id');
    $project = Project::findOrFail($projectId);
    Todo::factory()->for($project)->create();

    $this->patchJson("/api/projects/{$projectId}", ['name' => 'Personal'])
        ->assertOk()->assertJsonPath('name', 'Personal');
    $this->deleteJson("/api/projects/{$projectId}")->assertNoContent();

    $this->assertDatabaseMissing('projects', ['id' => $projectId]);
    $this->assertDatabaseMissing('todos', ['project_id' => $projectId]);
});

test('todo creation and updates preserve the project relationship', function () {
    $project = Project::factory()->create();

    $todoId = $this->postJson("/api/projects/{$project->id}/todos", [
        'title' => 'Write tests', 'due_date' => '2026-10-01',
    ])->assertCreated()->assertJsonPath('project_id', $project->id)
        ->assertJsonPath('is_completed', false)->json('id');

    $this->patchJson("/api/projects/{$project->id}/todos/{$todoId}", [
        'title' => 'Write more tests', 'is_completed' => true,
    ])->assertOk()->assertJsonPath('is_completed', true)->assertJsonPath('due_date', '2026-10-01');

    $this->assertDatabaseHas('todos', ['id' => $todoId, 'project_id' => $project->id, 'is_completed' => true]);
    $this->deleteJson("/api/projects/{$project->id}/todos/{$todoId}")->assertNoContent();
    $this->assertDatabaseMissing('todos', ['id' => $todoId]);
});

test('todos cannot be changed from another project', function () {
    $project = Project::factory()->create();
    $otherProject = Project::factory()->create();
    $todo = Todo::factory()->for($otherProject)->create(['title' => 'Private task']);

    $this->patchJson("/api/projects/{$project->id}/todos/{$todo->id}", ['title' => 'Changed'])->assertNotFound();
    $this->deleteJson("/api/projects/{$project->id}/todos/{$todo->id}")->assertNotFound();
    $this->assertDatabaseHas('todos', ['id' => $todo->id, 'title' => 'Private task']);
});

test('invalid projects and todos are rejected', function () {
    $project = Project::factory()->create();

    $this->postJson('/api/projects', [])->assertUnprocessable()->assertJsonValidationErrors('name');
    $this->postJson("/api/projects/{$project->id}/todos", ['title' => '', 'due_date' => 'tomorrow'])
        ->assertUnprocessable()->assertJsonValidationErrors(['title', 'due_date']);
    $todo = Todo::factory()->for($project)->create();
    $this->patchJson("/api/projects/{$project->id}/todos/{$todo->id}", ['is_completed' => 'perhaps'])
        ->assertUnprocessable()->assertJsonValidationErrors('is_completed');
});

test('playground writes are unavailable outside local loopback', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.1'])
        ->postJson('/api/projects', ['name' => 'Blocked'])->assertNotFound();
    $this->assertDatabaseCount('projects', 0);
});
