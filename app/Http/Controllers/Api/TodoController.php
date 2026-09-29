<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TodoController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        return response()->json($project->todos()->create($data), 201);
    }

    public function update(Request $request, Project $project, Todo $todo): JsonResponse
    {
        $todo->update($request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'is_completed' => ['sometimes', 'required', 'boolean'],
            'due_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ]));

        return response()->json($todo);
    }

    public function destroy(Project $project, Todo $todo): Response
    {
        $todo->delete();

        return response()->noContent();
    }
}
