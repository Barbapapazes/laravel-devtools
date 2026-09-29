<script setup lang="ts">
useHead({ title: 'About · Daylist' })
</script>

<template>
  <main class="flex min-h-screen items-center justify-center bg-default px-5">
    <UCard class="w-full max-w-xl">
      <template #header>
        <div class="flex items-center gap-3">
          <UIcon name="i-lucide-list-todo" class="size-7 text-primary-500" />
          <h1 class="text-xl font-bold">About Daylist</h1>
        </div>
      </template>
      <div class="flex flex-col gap-4 text-sm leading-6 text-muted">
        <p>This playground is a small todo app powered by a Laravel API and Nuxt UI. Projects contain tasks, each with a completion state and optional due date.</p>
        <p>Use Nuxt DevTools to inspect the Laravel API routes and the relationships between the Project and Todo models. Playground data is available only in local development.</p>
      </div>
      <template #footer>
        <UButton to="/" icon="i-lucide-arrow-left" variant="soft">Back to tasks</UButton>
      </template>
    </UCard>
  </main>
</template>
