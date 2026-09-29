import type { KitNodeContext } from '@vitejs/devtools-kit/node'
import { createModelInspector } from './model-inspector.js'
import { createRouteInspector } from './route-inspector.js'

export { createModelInspector } from './model-inspector.js'
export { createRouteInspector } from './route-inspector.js'

export async function installLaravelDevtools(ctx: Pick<KitNodeContext, 'install' | 'docks' | 'commands'>, options: { backendUrl?: string } = {}): Promise<void> {
    ctx.docks.register({
        id: 'laravel-devtools:inspectors',
        type: 'group',
        title: 'Laravel',
        icon: 'logos:laravel',
        category: 'framework',
    })

    await ctx.install(createRouteInspector(options), { dock: { groupId: 'laravel-devtools:inspectors' } })
    await ctx.install(createModelInspector(options), { dock: { groupId: 'laravel-devtools:inspectors' } })

    ctx.commands.register({
        id: 'laravel-devtools:inspect',
        title: 'Inspect Laravel',
        icon: 'logos:laravel',
        children: [
            {
                id: 'laravel-devtools:inspect-routes',
                title: 'Routes',
                icon: 'lucide:route',
                handler: () => ctx.docks.activate('route-inspector'),
            },
            {
                id: 'laravel-devtools:inspect-models',
                title: 'Models',
                icon: 'lucide:database',
                handler: () => ctx.docks.activate('model-inspector'),
            },
        ],
    })
}
