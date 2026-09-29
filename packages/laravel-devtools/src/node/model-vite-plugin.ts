import { createPluginFromDevframe } from '@vitejs/devtools-kit/node'
import { createModelInspector } from './model-inspector.js'

export default function modelInspectorPlugin(options: { backendUrl?: string } = {}): ReturnType<typeof createPluginFromDevframe> {
    const modelInspector = createModelInspector(options)
    return createPluginFromDevframe(modelInspector, {
        setup(ctx) {
            const viteServer = ctx.viteServer
            const clientAssets = modelInspector.clientAssets
            if (viteServer && typeof clientAssets === 'string') {
                viteServer.watcher.add(clientAssets)
                viteServer.watcher.on('change', (path) => {
                    if (path.startsWith(`${clientAssets}/`)) {
                        viteServer.ws.send({ type: 'full-reload' })
                    }
                })
            }
        },
    })
}