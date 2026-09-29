import { createPluginFromDevframe } from '@vitejs/devtools-kit/node'
import { installLaravelDevtools } from './laravel-devtools.js'
import { createModelInspector } from './model-inspector.js'
import { createRouteInspector } from './route-inspector.js'

export default function laravelDevtoolsPlugin(options: { backendUrl?: string } = {}): ReturnType<typeof createPluginFromDevframe> {
    return {
        name: 'laravel-devtools',
        devtools: {
            async setup(ctx) {
                await installLaravelDevtools(ctx, options)
                const viteServer = ctx.viteServer
                const clientAssets = [createRouteInspector(options).clientAssets, createModelInspector(options).clientAssets]
                    .filter((assets): assets is string => typeof assets === 'string')

                if (viteServer) {
                    viteServer.watcher.add(clientAssets)
                    viteServer.watcher.on('change', (path) => {
                        if (clientAssets.some(assets => path.startsWith(`${assets}/`))) {
                            viteServer.ws.send({ type: 'full-reload' })
                        }
                    })
                }
            },
        },
    }
}
