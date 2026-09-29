import { createPluginFromDevframe } from '@vitejs/devtools-kit/node'
import { createRouteInspector } from './route-inspector.js'

export default function routeInspectorPlugin(options: { backendUrl?: string } = {}): ReturnType<typeof createPluginFromDevframe> {
    return createPluginFromDevframe(createRouteInspector(options))
}
