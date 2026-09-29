<?php

namespace Devtools\LaravelDevtools\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;

class RouteIndexController
{
    public function __invoke(Request $request, Router $router): JsonResponse
    {
        abort_unless(app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1'], true), 404);

        $routes = collect($router->getRoutes()->getRoutes())
            ->map(fn (Route $route): array => [
                'methods' => array_values(array_diff($route->methods(), ['HEAD'])),
                'uri' => $route->uri(),
                'domain' => $route->getDomain(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->gatherMiddleware(),
                'resolvedMiddleware' => array_map(fn ($middleware): string => is_string($middleware) ? $middleware : 'Closure', $router->gatherRouteMiddleware($route)),
                'parameters' => array_map(fn (string $name): array => [
                    'name' => $name,
                    'optional' => str_contains($route->uri(), '{'.$name.'?}') || str_contains($route->getDomain() ?? '', '{'.$name.'?}'),
                ], $route->parameterNames()),
                'source' => $this->source($route),
            ])
            ->sortBy(fn (array $route): string => $route['uri'].'|'.implode(',', $route['methods']))
            ->values()
            ->all();

        return response()->json($routes);
    }

    private function source(Route $route): ?array
    {
        try {
            if ($route->getActionName() !== 'Closure') {
                [$controller, $method] = Str::parseCallback($route->getActionName(), '__invoke');
                $reflection = new ReflectionMethod($controller, $method);
            } else {
                $reflection = new ReflectionFunction($route->getAction('uses'));
            }

            $file = $reflection->getFileName();

            return $file ? ['file' => str_replace(base_path().DIRECTORY_SEPARATOR, '', $file), 'line' => $reflection->getStartLine()] : null;
        } catch (Throwable) {
            return null;
        }
    }
}
