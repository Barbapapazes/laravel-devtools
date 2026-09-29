<script setup lang="ts">
interface Todo {
  id: number
  project_id: number
  title: string
  is_completed: boolean
  due_date: string | null
}

interface Project {
  id: number
  name: string
  description: string | null
  todos: Todo[]
}

const apiBaseUrl = `${useRuntimeConfig().public.laravelUrl.replace(/\/$/, '')}/api`
const api = $fetch.create({ baseURL: apiBaseUrl })
const { data: projects, status, error, refresh } = await useFetch<Project[]>('/projects', { baseURL: apiBaseUrl, default: () => [] })
const selectedId = ref<number | null>(null)
const newProject = ref('')
const newTodo = ref('')
const dueDate = ref('')
const filter = ref<'all' | 'open' | 'done'>('all')
const busy = ref(false)
const message = ref('')

const selected = computed(() => projects.value.find(project => project.id === selectedId.value) ?? projects.value[0])
const remaining = computed(() => selected.value?.todos.filter(todo => !todo.is_completed).length ?? 0)
const visibleTodos = computed(() => (selected.value?.todos ?? []).filter(todo =>
  filter.value === 'all' || (filter.value === 'done' ? todo.is_completed : !todo.is_completed),
))

function apiError(cause: unknown): string {
  if (cause && typeof cause === 'object' && 'data' in cause) {
    const data = (cause as { data?: { message?: string; errors?: Record<string, string[]> } }).data
    return Object.values(data?.errors ?? {}).flat()[0] ?? data?.message ?? 'Something went wrong.'
  }

  return 'Could not reach Laravel. Make sure the API is running on port 8000.'
}

async function mutate(action: () => Promise<void>) {
  busy.value = true
  message.value = ''
  try {
    await action()
    await refresh()
  } catch (cause) {
    message.value = apiError(cause)
  } finally {
    busy.value = false
  }
}

async function addProject() {
  const name = newProject.value.trim()
  if (!name) return
  await mutate(async () => {
    const project = await api<Project>('/projects', { method: 'POST', body: { name } })
    selectedId.value = project.id
    newProject.value = ''
  })
}

async function renameProject() {
  if (!selected.value) return
  const name = prompt('Project name', selected.value.name)?.trim()
  if (!name || name === selected.value.name) return
  const id = selected.value.id
  await mutate(async () => { await api(`/projects/${id}`, { method: 'PATCH', body: { name } }) })
}

async function removeProject() {
  if (!selected.value || !confirm(`Delete "${selected.value.name}" and all its tasks?`)) return
  const id = selected.value.id
  await mutate(async () => {
    await api(`/projects/${id}`, { method: 'DELETE' })
    selectedId.value = null
  })
}

async function addTodo() {
  const title = newTodo.value.trim()
  if (!selected.value || !title) return
  const id = selected.value.id
  await mutate(async () => {
    await api(`/projects/${id}/todos`, { method: 'POST', body: { title, due_date: dueDate.value || null } })
    newTodo.value = ''
    dueDate.value = ''
  })
}

async function toggleTodo(todo: Todo, is_completed: boolean | 'indeterminate') {
  if (typeof is_completed !== 'boolean') return
  await mutate(async () => {
    await api(`/projects/${todo.project_id}/todos/${todo.id}`, { method: 'PATCH', body: { is_completed } })
  })
}

async function renameTodo(todo: Todo) {
  const title = prompt('Task name', todo.title)?.trim()
  if (!title || title === todo.title) return
  await mutate(async () => {
    await api(`/projects/${todo.project_id}/todos/${todo.id}`, { method: 'PATCH', body: { title } })
  })
}

async function removeTodo(todo: Todo) {
  await mutate(async () => {
    await api(`/projects/${todo.project_id}/todos/${todo.id}`, { method: 'DELETE' })
  })
}

useHead({ title: 'Your workspace · Laravel Devtools' })
</script>

<template>
  <div class="min-h-screen bg-default text-default">
    <header class="border-b border-default bg-elevated">
      <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 py-4">
        <NuxtLink to="/" class="flex items-center gap-3 font-semibold tracking-tight">
          <span class="flex size-9 items-center justify-center rounded-xl bg-primary-500 text-white"><UIcon name="i-lucide-check-check" class="size-5" /></span>
          <span>Daylist <span class="font-normal text-gray-400">/ playground</span></span>
        </NuxtLink>
        <div class="flex items-center gap-3 text-sm text-muted">
          <span class="hidden sm:inline">Laravel + Nuxt UI</span>
          <NuxtLink to="/about" class="hover:text-primary-500">About</NuxtLink>
          <UColorModeButton />
        </div>
      </div>
    </header>

    <main class="mx-auto grid max-w-6xl gap-6 px-5 py-10 lg:grid-cols-[280px_minmax(0,1fr)]">
      <aside class="flex flex-col gap-5">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-primary">Workspace</p>
          <h1 class="mt-2 text-3xl font-bold tracking-tight">Make room for what matters.</h1>
          <p class="mt-2 text-sm leading-6 text-muted">A real little app to explore your Laravel models and routes in Nuxt DevTools.</p>
        </div>
        <UCard>
          <template #header>
            <div class="flex items-center justify-between"><h2 class="font-semibold">Projects</h2><UBadge color="neutral" variant="subtle">{{ projects.length }}</UBadge></div>
          </template>
          <div class="flex flex-col gap-1">
            <UButton v-for="project in projects" :key="project.id" :color="selected?.id === project.id ? 'primary' : 'neutral'"
              :variant="selected?.id === project.id ? 'soft' : 'ghost'" icon="i-lucide-folder" block class="justify-start"
              @click="selectedId = project.id">{{ project.name }}</UButton>
            <p v-if="!projects.length" class="py-3 text-sm text-gray-500">Create your first project below.</p>
          </div>
          <template #footer>
            <form class="flex gap-2" @submit.prevent="addProject">
              <UInput v-model="newProject" aria-label="New project name" placeholder="New project" maxlength="255" class="min-w-0 flex-1" :disabled="busy" />
              <UButton icon="i-lucide-plus" type="submit" aria-label="Add project" :disabled="busy || !newProject.trim()" />
            </form>
          </template>
        </UCard>
        <div class="rounded-xl border border-dashed border-default p-4 text-xs leading-5 text-muted">
          <UIcon name="i-lucide-info" class="mr-1 inline size-4 align-middle" /> Open Nuxt DevTools to inspect the <strong>Project</strong> and <strong>Todo</strong> models and their API routes.
        </div>
      </aside>

      <section class="min-w-0" aria-label="Tasks">
        <UAlert v-if="error || message" color="error" variant="soft" icon="i-lucide-circle-alert" class="mb-5"
          :title="message || 'Could not load projects'" :description="error ? 'Start the Laravel server on 127.0.0.1:8000, then try again.' : undefined" />
        <UCard class="min-h-96">
          <template #header>
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div>
                <p class="text-xs font-medium uppercase tracking-widest text-gray-400">{{ selected ? 'Current project' : 'Your next chapter' }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight">{{ selected?.name ?? 'Start with a project' }}</h2>
                <p class="mt-1 text-sm text-muted">{{ selected ? `${remaining} open of ${selected.todos.length} tasks` : 'Give your ideas a place to land.' }}</p>
              </div>
              <div v-if="selected" class="flex gap-2">
                <UButton color="neutral" variant="ghost" icon="i-lucide-pencil" aria-label="Rename project" :disabled="busy" @click="renameProject" />
                <UButton color="error" variant="ghost" icon="i-lucide-trash-2" aria-label="Delete project" :disabled="busy" @click="removeProject" />
              </div>
            </div>
          </template>

          <div v-if="status === 'pending'" class="py-16 text-center text-gray-500">Loading projects…</div>
          <div v-else-if="!selected" class="flex flex-col items-center gap-3 py-16 text-center text-gray-500">
            <UIcon name="i-lucide-list-todo" class="size-10 text-primary-400" /><p>Create a project to start collecting your tasks.</p>
          </div>
          <template v-else>
            <form class="flex flex-col gap-3 rounded-xl bg-elevated p-4 sm:flex-row" @submit.prevent="addTodo">
              <UInput v-model="newTodo" aria-label="New task title" placeholder="What needs to get done?" icon="i-lucide-plus" maxlength="255" class="min-w-0 flex-1" :disabled="busy" />
              <UInput v-model="dueDate" aria-label="Due date" type="date" class="sm:w-40" :disabled="busy" />
              <UButton type="submit" icon="i-lucide-plus" :disabled="busy || !newTodo.trim()">Add task</UButton>
            </form>
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-b border-default pb-4">
              <h3 class="text-sm font-semibold">Tasks <span class="ml-1 text-gray-400">{{ selected.todos.length }}</span></h3>
              <div class="flex gap-1" aria-label="Filter tasks">
                <UButton v-for="option in (['all', 'open', 'done'] as const)" :key="option" size="sm"
                  :variant="filter === option ? 'soft' : 'ghost'" :color="filter === option ? 'primary' : 'neutral'"
                  @click="filter = option">{{ option === 'all' ? 'All' : option === 'open' ? 'To do' : 'Done' }}</UButton>
              </div>
            </div>
            <ul v-if="visibleTodos.length" class="divide-y divide-default">
              <li v-for="todo in visibleTodos" :key="todo.id" class="flex items-center gap-3 py-4">
                <UCheckbox :model-value="todo.is_completed" :aria-label="`${todo.is_completed ? 'Reopen' : 'Complete'} ${todo.title}`" :disabled="busy" @update:model-value="value => toggleTodo(todo, value)" />
                <div class="min-w-0 flex-1">
                  <p class="truncate text-sm font-medium" :class="todo.is_completed ? 'text-gray-400 line-through' : ''">{{ todo.title }}</p>
                  <p v-if="todo.due_date" class="mt-1 text-xs text-gray-500">Due {{ todo.due_date }}</p>
                </div>
                <UButton color="neutral" variant="ghost" size="sm" icon="i-lucide-pencil" :aria-label="`Rename ${todo.title}`" :disabled="busy" @click="renameTodo(todo)" />
                <UButton color="error" variant="ghost" size="sm" icon="i-lucide-x" :aria-label="`Delete ${todo.title}`" :disabled="busy" @click="removeTodo(todo)" />
              </li>
            </ul>
            <p v-else class="py-14 text-center text-sm text-gray-500">{{ selected.todos.length ? 'No tasks in this view.' : 'Nothing here yet. Add your first task above.' }}</p>
          </template>
        </UCard>
      </section>
    </main>
  </div>
</template>
