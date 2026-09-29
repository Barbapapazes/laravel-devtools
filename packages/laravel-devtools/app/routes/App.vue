<script setup lang="ts">
import { connectDevframe } from 'devframe/client'
import type { DevframeScopedClientContext } from 'devframe/client'
import { computed, onMounted, ref } from 'vue'
import type { Route } from '../../src/node/routes'

const routes = ref<Route[]>([])
const search = ref('')
const methodFilter = ref('')
const middlewareFilter = ref('')
const status = ref('Connecting to Devframe…')
const loading = ref(false)
const connected = ref(false)
const selectedRoute = ref<Route | null>(null)
let inspector: DevframeScopedClientContext<'route-inspector'> | undefined

const methods = computed(() => [...new Set(routes.value.flatMap(route => route.methods))].sort())
const middlewares = computed(() => [...new Set(routes.value.flatMap(route => route.middleware))].sort())
const filteredRoutes = computed(() => {
    const filter = search.value.trim().toLowerCase()

  return routes.value.filter(route => (!methodFilter.value || route.methods.includes(methodFilter.value))
    && (!middlewareFilter.value || route.middleware.includes(middlewareFilter.value))
    && [route.methods.join(' '), route.uri, route.name, route.action, ...route.middleware]
      .some(value => value?.toLowerCase().includes(filter)))
})

const message = computed(() => {
    if (status.value) return status.value
    if (filteredRoutes.value.length) return ''
  return search.value.trim() || methodFilter.value || middlewareFilter.value ? 'No routes match your filters.' : 'No routes registered.'
})

function selectRoute(route: Route) {
  selectedRoute.value = selectedRoute.value === route ? null : route
}

async function loadRoutes() {
  if (!inspector) return

    loading.value = true
    status.value = 'Loading routes…'

    try {
        routes.value = await inspector.rpc.call('get-routes') as Route[]
        selectedRoute.value = null
        status.value = ''
    } catch (error) {
        routes.value = []
        selectedRoute.value = null
        status.value = `Unable to load routes: ${error instanceof Error ? error.message : String(error)}`
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    try {
        const client = await connectDevframe()
        inspector = client.scope('route-inspector')
        connected.value = true
        await loadRoutes()
    } catch (error) {
        status.value = `Unable to connect to Devframe: ${error instanceof Error ? error.message : String(error)}`
    }
})
</script>

<template>
  <main class="mx-auto flex min-h-screen max-w-[1540px] flex-col bg-[#f7f8f4] px-5 font-sans text-[#20261e] antialiased min-[701px]:px-[5%]">
    <header class="flex h-[86px] items-center justify-between gap-4 border-b border-[#dfe3da]">
      <div class="flex items-center gap-3 font-mono text-[11px] font-medium tracking-[.09em]"><span class="grid size-[30px] place-items-center bg-[#c9ee6d] font-sans text-[19px]">↗</span><span>DEVTOOLS <span class="mx-[5px] text-[#acb4a3]">/</span> ROUTES</span></div>
      <span class="flex items-center gap-[9px] font-mono text-[11px] font-medium tracking-[.09em] text-[#66705f]"><span class="size-[7px] rounded-full bg-[#71a942] shadow-[0_0_0_4px_#e8f4d8]"></span> LOCAL DEVELOPMENT</span>
    </header>
    <section class="pt-11 pb-8 min-[701px]:pt-[68px] min-[701px]:pb-[42px]">
      <p class="flex items-center gap-3 font-mono text-[11px] font-medium tracking-[.09em] text-[#718660]">APPLICATION TOPOLOGY <span class="inline-block h-px w-[33px] bg-[#a7c478]"></span> 01 / INSPECTOR</p>
      <div class="flex flex-col items-start justify-between gap-6 min-[701px]:flex-row min-[701px]:items-end">
        <div><h1 class="my-4 mb-2 text-[clamp(42px,5vw,68px)] leading-[1.1] font-semibold tracking-[-.055em]">Route inspector<span class="text-[#8dbd4d]">.</span></h1><p class="text-[15px] text-[#6d7767]">A clear view of every path your Laravel application knows.</p></div>
        <button class="w-full cursor-pointer whitespace-nowrap rounded-[5px] border border-[#ccd4c5] bg-white px-[17px] py-[11px] text-[13px] text-[#34402c] hover:border-[#759456] hover:bg-[#f0f8e8] disabled:cursor-wait disabled:opacity-55 min-[701px]:w-auto" type="button" :disabled="!connected || loading" @click="loadRoutes">↻ <span>Refresh routes</span></button>
      </div>
    </section>
    <section class="rounded-[7px] border border-[#dfe3da] bg-white shadow-[0_8px_32px_#20301608]" aria-label="Registered routes">
      <div class="flex min-h-[76px] flex-col items-stretch justify-between gap-4 p-4 min-[701px]:flex-row min-[701px]:items-center min-[701px]:px-6">
        <div class="font-mono text-[11px] font-medium tracking-[.09em] text-[#66705f]"><span id="count" class="mr-2 text-[17px] text-[#293523]">{{ connected ? filteredRoutes.length : '—' }}</span> / {{ routes.length }} REGISTERED ROUTES</div>
        <div class="flex flex-wrap items-center gap-[10px]">
          <label class="flex items-center gap-2 font-mono text-[10px] tracking-[.05em] text-[#71806c]">METHOD <select v-model="methodFilter" class="max-w-[220px] rounded border border-[#e1e5db] bg-white px-2 py-[9px] font-sans text-xs text-[#34472c]" aria-label="Filter by method"><option value="">All methods</option><option v-for="method in methods" :key="method" :value="method">{{ method }}</option></select></label>
          <label class="flex items-center gap-2 font-mono text-[10px] tracking-[.05em] text-[#71806c]">MIDDLEWARE <select v-model="middlewareFilter" class="max-w-[220px] rounded border border-[#e1e5db] bg-white px-2 py-[9px] font-sans text-xs text-[#34472c]" aria-label="Filter by middleware"><option value="">All middleware</option><option v-for="middleware in middlewares" :key="middleware" :value="middleware">{{ middleware }}</option></select></label>
          <label class="flex w-full items-center gap-[9px] rounded border border-[#e1e5db] px-3 py-[9px] text-[#7e8b75] focus-within:border-[#7da852] min-[701px]:w-[280px]"><span class="text-[22px] leading-[15px]">⌕</span><input v-model="search" class="w-full border-0 bg-transparent text-xs text-[#293523] outline-none" type="search" placeholder="Filter routes..." aria-label="Filter routes" /></label>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
          <thead class="bg-[#f4f6f1] font-mono text-[10px] font-medium tracking-[.09em] text-[#83907b]"><tr><th class="px-6 py-4 whitespace-nowrap">METHOD</th><th class="px-6 py-4 whitespace-nowrap">URI</th><th class="px-6 py-4 whitespace-nowrap">NAME</th><th class="px-6 py-4 whitespace-nowrap">ACTION</th><th class="px-6 py-4 whitespace-nowrap">MIDDLEWARE</th><th class="px-6 py-4 whitespace-nowrap">DETAILS</th></tr></thead>
          <tbody>
            <template v-for="(route, index) in filteredRoutes" :key="`${route.domain}:${route.uri}:${route.methods.join(',')}:${index}`">
              <tr class="text-xs hover:bg-[#fafcf7] [&>td]:border-t [&>td]:border-[#edf0e9] [&>td]:px-6 [&>td]:py-5 [&>td]:whitespace-nowrap">
                <td><span v-for="method in route.methods" :key="method" class="mr-1 inline-block rounded-[3px] px-2 py-[5px] font-mono text-[10px]" :class="method === 'POST' ? 'bg-[#e6effb] text-[#3969a2]' : method === 'PUT' || method === 'PATCH' ? 'bg-[#fbf0dd] text-[#9b722b]' : method === 'DELETE' ? 'bg-[#fae7e2] text-[#aa5447]' : 'bg-[#eaf5db] text-[#4f7b2c]'">{{ method }}</span></td>
                <td class="font-mono font-medium text-[#34472c]">{{ route.uri }}</td>
                <td class="font-mono text-[#75816e]">{{ route.name || '—' }}</td>
                <td class="font-mono text-[#556351]">{{ route.action || '—' }}</td>
                <td class="font-mono text-[#75816e]">{{ route.middleware.join(', ') || '—' }}</td>
                <td><button type="button" class="cursor-pointer rounded border border-[#dbeacb] bg-[#f4f8ee] px-3 py-[7px] text-xs text-[#4f7b2c] hover:bg-[#eaf5db]" :aria-expanded="selectedRoute === route" @click="selectRoute(route)">{{ selectedRoute === route ? 'Hide' : 'Inspect' }}</button></td>
              </tr>
              <tr v-if="selectedRoute === route"><td colspan="6" class="border-t border-[#edf0e9] bg-[#fafcf7]">
                <div class="grid grid-cols-1 gap-6 p-6 text-xs text-[#71806c] min-[901px]:grid-cols-3 [&_h2]:mb-3 [&_h2]:font-mono [&_h2]:text-[11px] [&_h2]:font-medium [&_h2]:tracking-[.08em] [&_h2]:text-[#34472c] [&_h2]:uppercase [&_p]:mb-[10px] [&_ul]:list-disc [&_ol]:list-decimal [&_ul]:pl-5 [&_ol]:pl-5 [&_li]:mb-2 [&_li]:wrap-anywhere [&_code]:font-mono [&_code]:text-xs [&_code]:text-[#34472c] [&_code]:wrap-anywhere">
                  <div><h2>Route parameters</h2><p v-if="!route.parameters.length">No parameters.</p><ul v-else><li v-for="parameter in route.parameters" :key="parameter.name"><code>{{ parameter.name }}</code> <span class="ml-[6px] text-[#819078]">{{ parameter.optional ? 'optional' : 'required' }}</span></li></ul></div>
                  <div><h2>Middleware execution order</h2><p v-if="!route.resolvedMiddleware.length">No route middleware.</p><ol v-else><li v-for="(middleware, order) in route.resolvedMiddleware" :key="order"><code>{{ middleware }}</code></li></ol></div>
                  <div><h2>Handler location</h2><p v-if="route.source"><code>{{ route.source.file }}:{{ route.source.line }}</code></p><p v-else>Location unavailable.</p><p v-if="route.domain">Domain: <code>{{ route.domain }}</code></p></div>
                </div>
              </td></tr>
            </template>
          </tbody>
        </table>
      </div>
      <div v-if="message" class="px-6 py-9 text-center text-[13px] text-[#71806c]" role="status">{{ message }}</div>
    </section>
    <footer class="mt-auto flex flex-col justify-between gap-5 py-[30px] font-mono text-[10px] font-medium tracking-[.09em] text-[#879282] min-[701px]:flex-row"><span>ROUTE INSPECTOR <span class="mx-[5px] text-[#acb4a3]">/</span> POWERED BY DEVFRAME</span><span>READ ONLY · LOCAL ACCESS</span></footer>
  </main>
</template>
