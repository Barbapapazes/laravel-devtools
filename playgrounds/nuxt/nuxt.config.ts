const laravelUrl = process.env.LARAVEL_URL ?? 'http://127.0.0.1:8000'

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  fonts: { devtools: false },
  runtimeConfig: {
    public: { laravelUrl },
  },
  modules: [
    '@nuxt/devtools',
    '@nuxt/ui',
    '@barbapapazes/laravel-devtools/nuxt',
  ],
  laravelDevtools: {
    backendUrl: laravelUrl,
  },
  css: ['~/assets/css/main.css'],
})
