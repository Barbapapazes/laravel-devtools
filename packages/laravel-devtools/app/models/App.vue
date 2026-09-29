<script setup lang="ts">
import { connectDevframe } from 'devframe/client'
import type { DevframeScopedClientContext } from 'devframe/client'
import { computed, onMounted, ref } from 'vue'
import type { Model } from '../../src/node/models'

const models = ref<Model[]>([])
const search = ref('')
const status = ref('Connecting to Devframe…')
const loading = ref(false)
const connected = ref(false)
const selectedClass = ref<string | null>(null)
let inspector: DevframeScopedClientContext<'model-inspector'> | undefined

const filteredModels = computed(() => {
    const filter = search.value.trim().toLowerCase()

    return models.value.filter(model => [model.class, model.table, model.connection, model.key, ...model.fields.map(field => field.name), ...model.relationships.map(relation => relation.name)]
        .some(value => value?.toLowerCase().includes(filter)))
})

const selectedModel = computed(() => models.value.find(model => model.class === selectedClass.value))
const message = computed(() => {
    if (status.value) return status.value
    if (filteredModels.value.length) return ''
    return search.value.trim() ? 'No models match your filter.' : 'No application models found.'
})

async function loadModels() {
  if (!inspector) return

    loading.value = true
    status.value = 'Loading models…'

    try {
        models.value = await inspector.rpc.call('get-models') as Model[]
        if (!models.value.some(model => model.class === selectedClass.value)) {
            selectedClass.value = models.value[0]?.class ?? null
        }
        status.value = ''
    } catch (error) {
        models.value = []
        selectedClass.value = null
        status.value = `Unable to load models: ${error instanceof Error ? error.message : String(error)}`
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    try {
        const client = await connectDevframe()
        inspector = client.scope('model-inspector')
        connected.value = true
        await loadModels()
    } catch (error) {
        status.value = `Unable to connect to Devframe: ${error instanceof Error ? error.message : String(error)}`
    }
})
</script>

<template>
  <main class="mx-auto flex min-h-screen max-w-[1540px] flex-col bg-[#f7f8f4] px-5 font-sans text-[#20261e] antialiased min-[701px]:px-[5%]">
    <header class="flex h-[86px] items-center justify-between gap-4 border-b border-[#dfe3da]">
      <div class="flex items-center gap-3 font-mono text-[11px] font-medium tracking-[.09em]"><span class="grid size-[30px] place-items-center bg-[#c9ee6d] font-sans text-[19px]">◇</span><span>DEVTOOLS <span class="mx-[5px] text-[#acb4a3]">/</span> MODELS</span></div>
      <span class="flex items-center gap-[9px] font-mono text-[11px] font-medium tracking-[.09em] text-[#66705f]"><span class="size-[7px] rounded-full bg-[#71a942] shadow-[0_0_0_4px_#e8f4d8]"></span> LOCAL DEVELOPMENT</span>
    </header>
    <section class="pt-11 pb-8 min-[701px]:pt-[68px] min-[701px]:pb-[42px]">
      <p class="flex items-center gap-3 font-mono text-[11px] font-medium tracking-[.09em] text-[#718660]">APPLICATION STRUCTURE <span class="inline-block h-px w-[33px] bg-[#a7c478]"></span> 02 / INSPECTOR</p>
      <div class="flex flex-col items-start justify-between gap-6 min-[701px]:flex-row min-[701px]:items-end">
        <div><h1 class="my-4 mb-2 text-[clamp(42px,5vw,68px)] leading-[1.1] font-semibold tracking-[-.055em]">Model inspector<span class="text-[#8dbd4d]">.</span></h1><p class="text-[15px] text-[#6d7767]">Explore the Eloquent models behind your Laravel application.</p></div>
        <button class="w-full cursor-pointer whitespace-nowrap rounded-[5px] border border-[#ccd4c5] bg-white px-[17px] py-[11px] text-[13px] text-[#34402c] hover:border-[#759456] hover:bg-[#f0f8e8] disabled:cursor-wait disabled:opacity-55 min-[701px]:w-auto" type="button" :disabled="!connected || loading" @click="loadModels">↻ <span>Refresh models</span></button>
      </div>
    </section>
    <section class="rounded-[7px] border border-[#dfe3da] bg-white shadow-[0_8px_32px_#20301608]" aria-label="Application models">
      <div class="flex min-h-[76px] flex-col items-stretch justify-between gap-4 p-4 min-[701px]:flex-row min-[701px]:items-center min-[701px]:px-6">
        <div class="font-mono text-[11px] font-medium tracking-[.09em] text-[#66705f]"><span id="count" class="mr-2 text-[17px] text-[#293523]">{{ connected ? models.length : '—' }}</span> APPLICATION MODELS</div>
        <label class="flex w-full items-center gap-[9px] rounded border border-[#e1e5db] px-3 py-[9px] text-[#7e8b75] focus-within:border-[#7da852] min-[701px]:w-[280px]"><span class="text-[22px] leading-[15px]">⌕</span><input v-model="search" class="w-full border-0 bg-transparent text-xs text-[#293523] outline-none" type="search" placeholder="Filter models..." aria-label="Filter models" /></label>
      </div>
      <div v-if="message" class="px-6 py-9 text-center text-[13px] text-[#71806c]" role="status">{{ message }}</div>
      <div v-else class="grid min-h-[420px] grid-cols-1 border-t border-[#edf0e9] min-[701px]:grid-cols-[minmax(220px,30%)_minmax(0,1fr)]">
        <div class="max-h-60 overflow-y-auto border-b border-[#edf0e9] p-3 min-[701px]:max-h-[650px] min-[701px]:border-r min-[701px]:border-b-0" aria-label="Models">
          <button v-for="model in filteredModels" :key="model.class" type="button" class="flex w-full cursor-pointer items-center gap-[13px] rounded-[5px] border p-[13px] text-left text-[#293523]" :class="selectedClass === model.class ? 'border-[#dbeacb] bg-[#eff6e7] hover:bg-[#eff6e7]' : 'border-transparent hover:bg-[#f4f8ee]'" @click="selectedClass = model.class">
            <span class="grid size-[34px] shrink-0 place-items-center rounded bg-[#e2f0d1] font-mono text-sm font-medium text-[#578231]"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/></svg></span><span class="grid min-w-0 gap-[3px]"><strong class="overflow-hidden text-ellipsis text-[13px] font-semibold">{{ model.name }}</strong><small class="font-mono text-[11px] text-[#819078]">{{ model.table }}</small></span><span class="ml-auto font-mono text-[11px] text-[#819078]">↗</span>
          </button>
        </div>
        <div v-if="selectedModel" class="min-w-0 px-[22px] py-[30px] min-[701px]:px-11 min-[701px]:pt-9 min-[701px]:pb-12">
          <p class="flex items-center gap-[10px] font-mono text-[11px] font-medium tracking-[.08em] text-[#788c6a]">ELOQUENT MODEL <span class="inline-block h-px w-[33px] bg-[#a7c478]"></span> {{ selectedModel.name }}</p>
          <h2 class="mt-[18px] mb-1 text-[clamp(30px,3vw,46px)] font-semibold tracking-[-.045em]">{{ selectedModel.name }}<span class="text-[#8dbd4d]">.</span></h2>
          <p class="mb-9 font-mono text-xs text-[#7d8c75] wrap-anywhere">{{ selectedModel.class }}</p>
          <div class="grid grid-cols-2 border-t border-l border-[#e8ece3]">
            <div v-for="(value, label) in { TABLE: selectedModel.table, CONNECTION: selectedModel.connection, 'PRIMARY KEY': selectedModel.key, TIMESTAMPS: selectedModel.timestamps ? 'Enabled' : 'Disabled', 'SOFT DELETES': selectedModel.softDeletes ? 'Enabled' : 'Disabled' }" :key="label" class="grid min-w-0 gap-[10px] border-r border-b border-[#e8ece3] p-5"><span class="font-mono text-[11px] font-medium tracking-[.08em] text-[#788c6a]">{{ label }}</span><strong class="font-mono text-[13px] font-medium text-[#34472c] wrap-anywhere">{{ value }}</strong></div>
          </div>
          <h3 class="mt-9 mb-4 font-mono text-[11px] font-medium tracking-[.08em] text-[#293523]">Database fields <span class="ml-[6px] text-[#82a463]">{{ selectedModel.fields.length }}</span></h3>
          <div v-if="selectedModel.fields.length" class="divide-y divide-[#edf0e9] rounded border border-[#e8ece3] font-mono text-xs">
            <div v-for="field in selectedModel.fields" :key="field.name" class="flex justify-between gap-3 px-4 py-3"><span class="wrap-anywhere">{{ field.name }}</span><code class="text-right text-[#609037] wrap-anywhere">{{ field.type }}{{ field.nullable ? ' · nullable' : '' }}<template v-if="field.cast"> · {{ field.cast }}</template></code></div>
          </div>
          <p v-else class="text-[13px] text-[#819078]">No database fields found.</p>
          <h3 class="mt-9 mb-4 font-mono text-[11px] font-medium tracking-[.08em] text-[#293523]">Relationships <span class="ml-[6px] text-[#82a463]">{{ selectedModel.relationships.length }}</span></h3>
          <div v-if="selectedModel.relationships.length" class="divide-y divide-[#edf0e9] rounded border border-[#e8ece3] font-mono text-xs">
            <div v-for="relation in selectedModel.relationships" :key="relation.name" class="flex justify-between gap-3 px-4 py-3"><span class="wrap-anywhere">{{ relation.name }} <small class="text-[#819078]">· {{ relation.type }}</small></span><code class="text-right text-[#609037] wrap-anywhere">{{ relation.related }}</code></div>
          </div>
          <p v-else class="text-[13px] text-[#819078]">No typed relationships found.</p>
          <h3 class="mt-9 mb-4 font-mono text-[11px] font-medium tracking-[.08em] text-[#293523]">Indexes <span class="ml-[6px] text-[#82a463]">{{ selectedModel.indexes.length }}</span></h3>
          <div v-if="selectedModel.indexes.length" class="divide-y divide-[#edf0e9] rounded border border-[#e8ece3] font-mono text-xs">
            <div v-for="index in selectedModel.indexes" :key="index.name" class="flex justify-between gap-3 px-4 py-3"><span class="wrap-anywhere">{{ index.name }} <small v-if="index.primary || index.unique" class="text-[#819078]">· {{ index.primary ? 'primary' : 'unique' }}</small></span><code class="text-right text-[#609037] wrap-anywhere">{{ index.columns.join(', ') }}</code></div>
          </div>
          <p v-else class="text-[13px] text-[#819078]">No indexes found.</p>
          <h3 class="mt-9 mb-4 font-mono text-[11px] font-medium tracking-[.08em] text-[#293523]">Foreign keys <span class="ml-[6px] text-[#82a463]">{{ selectedModel.foreignKeys.length }}</span></h3>
          <div v-if="selectedModel.foreignKeys.length" class="divide-y divide-[#edf0e9] rounded border border-[#e8ece3] font-mono text-xs">
            <div v-for="(key, index) in selectedModel.foreignKeys" :key="index" class="flex justify-between gap-3 px-4 py-3"><span class="wrap-anywhere">{{ key.columns.join(', ') }}</span><code class="text-right text-[#609037] wrap-anywhere">→ {{ key.foreignTable }} ({{ key.foreignColumns.join(', ') }})<template v-if="key.onDelete"> · on delete {{ key.onDelete }}</template></code></div>
          </div>
          <p v-else class="text-[13px] text-[#819078]">No foreign keys found.</p>
        </div>
        <div v-else class="grid min-w-0 place-items-center px-[22px] py-[30px] text-[13px] text-[#819078] min-[701px]:px-11 min-[701px]:pt-9 min-[701px]:pb-12">Select a model to inspect its structure.</div>
      </div>
    </section>
    <footer class="mt-auto flex flex-col justify-between gap-5 py-[30px] font-mono text-[10px] font-medium tracking-[.09em] text-[#879282] min-[701px]:flex-row"><span>MODEL INSPECTOR <span class="mx-[5px] text-[#acb4a3]">/</span> POWERED BY DEVFRAME</span><span>READ ONLY · LOCAL ACCESS</span></footer>
  </main>
</template>
