import { defineDevframe } from 'devframe'
import { fileURLToPath } from 'node:url'
import { getRoutes } from './routes.js'

export function createRouteInspector({ backendUrl = process.env.LARAVEL_URL ?? 'http://127.0.0.1:8000' }: { backendUrl?: string } = {}) {
    return defineDevframe({
        id: 'route-inspector',
        name: 'Route Inspector',
        icon: 'lucide:route',
        version: '0.1.0',
        packageName: '@barbapapazes/laravel-devtools',
        description: 'Inspect registered Laravel routes.',
        homepage: 'https://devfra.me/',
        importMetaUrl: import.meta.url,
        clientAssets: fileURLToPath(new URL('../client', import.meta.url)),
        setup(ctx) {
            ctx.scope('route-inspector').rpc.register({
                name: 'get-routes',
                type: 'query',
                jsonSerializable: true,
                agent: {
                    title: 'List Laravel routes',
                    description: 'List registered Laravel routes, including methods, URIs, names, actions, and middleware. Use this to inspect the application routing before changing routes or middleware.',
                },
                handler: () => getRoutes(backendUrl),
            })
        },
    })
}
