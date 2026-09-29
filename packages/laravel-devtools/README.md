# @barbapapazes/laravel-devtools

Route Inspector and Model Inspector for Laravel applications in Vite DevTools and Nuxt DevTools. The companion Composer package exposes the data feeds; the npm package displays them in your frontend development tools.

## Install

In your Laravel application, install the backend package:

```sh
composer require --dev barbapapazes/laravel-devtools
```

In the frontend project, install this package:

```sh
pnpm add -D @barbapapazes/laravel-devtools
```

The backend requires PHP 8.3+ and Laravel 13. It registers its endpoints only in the Laravel `local` environment.

## Vite

Enable Vite DevTools and add the plugin in `vite.config.ts` alongside your existing plugins:

```ts
import { defineConfig } from 'vite'
import laravelDevtools from '@barbapapazes/laravel-devtools/vite'

export default defineConfig({
  devtools: { apply: 'serve' },
  plugins: [laravelDevtools()],
})
```

## Nuxt

Enable Nuxt DevTools and add the module in `nuxt.config.ts`:

```ts
export default defineNuxtConfig({
  devtools: { enabled: true },
  modules: ['@barbapapazes/laravel-devtools/nuxt'],
})
```

Start the Laravel backend and the Vite or Nuxt development server. In DevTools, open the Laravel group and choose Routes or Models.

Both integrations default to `http://127.0.0.1:8000` for the backend; set `LARAVEL_URL` or provide an explicit URL if your Laravel server runs elsewhere:

```ts
// Vite
laravelDevtools({ backendUrl: 'http://127.0.0.1:8080' })

// Nuxt: add this property to defineNuxtConfig(...)
laravelDevtools: { backendUrl: 'http://127.0.0.1:8080' }
```

The package also exports individual inspector integrations under `./routes/vite` and `./models/vite`.

Source and release instructions: [Laravel Devtools](https://github.com/Barbapapazes/laravel-devtools).
