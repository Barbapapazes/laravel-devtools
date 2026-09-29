# Laravel Devtools

Inspect registered Laravel routes and Eloquent models from Vite DevTools or Nuxt DevTools. This repository contains the npm integration, a Laravel package that serves the inspector data, and local playgrounds.

## Packages

| Package | Purpose |
| --- | --- |
| [`@barbapapazes/laravel-devtools`](packages/laravel-devtools/README.md) | Vite and Nuxt integrations with Route Inspector and Model Inspector panels. |
| [`barbapapazes/laravel-devtools`](packages/laravel-devtools-php/README.md) | Laravel endpoints that provide the route and model data in local environments. |

Install both packages in your Laravel project (and its Vite or Nuxt frontend):

```sh
composer require --dev barbapapazes/laravel-devtools
pnpm add -D @barbapapazes/laravel-devtools
```

In `vite.config.ts`, add the plugin to your existing Vite plugins:

```ts
import { defineConfig } from 'vite'
import laravelDevtools from '@barbapapazes/laravel-devtools/vite'

export default defineConfig({
  devtools: { apply: 'serve' },
  plugins: [laravelDevtools()],
})
```

For Nuxt, add `@barbapapazes/laravel-devtools/nuxt` to `modules` in `nuxt.config.ts` and enable Nuxt DevTools. See the [npm package README](packages/laravel-devtools/README.md) for Nuxt configuration and backend URL options. The Laravel feeds are available only when the Laravel app runs in its `local` environment. Start Laravel and the frontend development server, then open the Laravel group in DevTools.

## Develop locally

The root Laravel app is the Vite playground. Install the root Composer and pnpm dependencies, configure `.env` from `.env.example`, then run the backend and frontend development servers:

```sh
composer install
pnpm install
pnpm dev
```

In another terminal:

```sh
php artisan serve --host=127.0.0.1 --port=8000
```

The [Nuxt playground](playgrounds/nuxt/README.md) runs separately on port 3000. Both playgrounds use the local packages in this repository rather than registry releases.

## Releasing

The [publish workflow](.github/workflows/publish.yml) runs on a `vX.Y.Z` tag. It checks the npm version, tests and builds the packages, pushes a split of the PHP package to [`barbapapazes/laravel-devtools-php`](https://github.com/Barbapapazes/laravel-devtools-php), and publishes the npm package. Configure npm trusted publishing for this repository and `PHP_SPLIT_TOKEN` with write access to the PHP repository before tagging. The split repository's tags are used for Composer/Packagist releases.
