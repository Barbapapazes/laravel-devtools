<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Vite;

class DevToolsAssetController
{
    public function __invoke(string $asset): Response
    {
        abort_unless(app()->environment('local') && Vite::isRunningHot(), 404);

        $origin = Vite::devServerUrl();
        $host = parse_url($origin, PHP_URL_HOST);

        abort_unless(
            parse_url($origin, PHP_URL_SCHEME) === 'http'
            && in_array($host, ['localhost', '127.0.0.1', '[::1]'], true),
            404,
        );

        try {
            $upstream = Http::connectTimeout(1)
                ->timeout(3)
                ->get(rtrim($origin, '/').'/__devtools-assets/'.$asset);
        } catch (ConnectionException) {
            return response('Vite DevTools is unavailable.', 502);
        }

        return response($upstream->body(), $upstream->status())
            ->header('Content-Type', $upstream->header('Content-Type') ?? 'image/svg+xml')
            ->header('Cache-Control', 'no-store');
    }
}
