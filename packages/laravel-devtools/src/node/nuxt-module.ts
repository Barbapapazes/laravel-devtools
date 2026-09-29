import { onDevtoolsReady } from '@nuxt/devtools-kit'
import { defineNuxtModule } from '@nuxt/kit'
import { installLaravelDevtools } from './laravel-devtools.js'

export interface LaravelDevtoolsNuxtOptions {
    backendUrl?: string
}

export default defineNuxtModule<LaravelDevtoolsNuxtOptions>({
    meta: {
        name: '@barbapapazes/laravel-devtools',
        configKey: 'laravelDevtools',
    },
    defaults: {
        backendUrl: process.env.LARAVEL_URL ?? 'http://127.0.0.1:8000',
    },
    setup(options, nuxt) {
        if (!nuxt.options.dev) {
            return
        }

        onDevtoolsReady((kit) => installLaravelDevtools(kit, options), nuxt)
    },
})
