import { defineDevframe } from 'devframe'
import { fileURLToPath } from 'node:url'
import { getModels } from './models.js'

export function createModelInspector({ backendUrl = process.env.LARAVEL_URL ?? 'http://127.0.0.1:8000' }: { backendUrl?: string } = {}) {
    return defineDevframe({
        id: 'model-inspector',
        name: 'Model Inspector',
        icon: 'lucide:database',
        version: '0.1.0',
        packageName: '@barbapapazes/laravel-devtools',
        description: 'Inspect Laravel Eloquent models.',
        homepage: 'https://devfra.me/',
        importMetaUrl: import.meta.url,
        clientAssets: fileURLToPath(new URL('../models-client', import.meta.url)),
        setup(ctx) {
            ctx.scope('model-inspector').rpc.register({
                name: 'get-models',
                type: 'query',
                jsonSerializable: true,
                agent: {
                    title: 'List Laravel models',
                    description: 'List Laravel Eloquent models and their table, connection, key, casts, and timestamp metadata. Use this to inspect model structure before changing models or database code.',
                },
                handler: () => getModels(backendUrl),
            })
        },
    })
}
