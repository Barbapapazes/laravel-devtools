<?php

namespace Devtools\LaravelDevtools\Laravel\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

class ModelIndexController
{
    public function __invoke(Request $request, Filesystem $files): JsonResponse
    {
        abort_unless(app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1'], true), 404);

        $directory = app_path('Models');

        if (! $files->isDirectory($directory)) {
            return response()->json([]);
        }

        $models = collect($files->allFiles($directory))
            ->map(fn ($file): string => 'App\\Models\\'.str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname()))
            ->filter(fn (string $class): bool => class_exists($class) && is_subclass_of($class, Model::class) && ! (new ReflectionClass($class))->isAbstract())
            ->map(function (string $class): array {
                /** @var Model $model */
                $model = new $class;
                $casts = $model->getCasts();
                $schema = $model->getConnection()->getSchemaBuilder();

                return [
                    'class' => $class,
                    'name' => class_basename($class),
                    'table' => $model->getTable(),
                    'connection' => $model->getConnectionName() ?? config('database.default'),
                    'key' => $model->getKeyName(),
                    'timestamps' => $model->usesTimestamps(),
                    'casts' => $casts,
                    'fields' => array_map(fn (array $column): array => [
                        'name' => $column['name'],
                        'type' => $column['type'],
                        'nullable' => $column['nullable'],
                        'cast' => $casts[$column['name']] ?? null,
                    ], $schema->getColumns($model->getTable())),
                    'indexes' => array_map(fn (array $index): array => [
                        'name' => $index['name'],
                        'columns' => $index['columns'],
                        'unique' => $index['unique'],
                        'primary' => $index['primary'],
                    ], $schema->getIndexes($model->getTable())),
                    'foreignKeys' => array_map(fn (array $foreignKey): array => [
                        'columns' => $foreignKey['columns'],
                        'foreignTable' => $foreignKey['foreign_table'],
                        'foreignColumns' => $foreignKey['foreign_columns'],
                        'onDelete' => $foreignKey['on_delete'],
                    ], $schema->getForeignKeys($model->getTable())),
                    'relationships' => $this->relationships($model),
                    'softDeletes' => in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses_recursive($class), true),
                ];
            })
            ->sortBy('class')
            ->values()
            ->all();

        return response()->json($models);
    }

    private function relationships(Model $model): array
    {
        return collect((new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(function (ReflectionMethod $method) use ($model): bool {
                $returnType = $method->getReturnType();

                return $method->getDeclaringClass()->getName() === $model::class
                    && $method->getNumberOfRequiredParameters() === 0
                    && $returnType instanceof \ReflectionNamedType
                    && is_a($returnType->getName(), Relation::class, true);
            })
            ->map(function (ReflectionMethod $method) use ($model): ?array {
                try {
                    /** @var Relation $relation */
                    $relation = Relation::noConstraints(fn (): Relation => $method->invoke($model));

                    return [
                        'name' => $method->getName(),
                        'type' => class_basename($relation),
                        'related' => $relation->getRelated()::class,
                    ];
                } catch (Throwable) {
                    return null;
                }
            })
            ->filter()
            ->sortBy('name')
            ->values()
            ->all();
    }
}
