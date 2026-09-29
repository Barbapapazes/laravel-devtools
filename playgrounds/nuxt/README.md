# Nuxt playground

This Nuxt app exercises the Route Inspector and Model Inspector against the Laravel app at the repository root. It uses the local npm package from `../../packages/laravel-devtools`.

From the repository root, install the Laravel dependencies, configure `.env` from `.env.example`, and start the Laravel server:

```sh
composer install
php artisan serve --host=127.0.0.1 --port=8000
```

Build the local npm package, then install and run the Nuxt playground:

```sh
pnpm --dir packages/laravel-devtools install
pnpm --dir packages/laravel-devtools build
pnpm --dir playgrounds/nuxt install
pnpm --dir playgrounds/nuxt dev
```

Open `http://localhost:3000`, open Nuxt DevTools, and select the Laravel group to inspect routes and models. The Laravel server must be running in the `local` environment for its data endpoints to be available. By default, Nuxt connects to `http://127.0.0.1:8000`; set `LARAVEL_URL` before starting Nuxt to use a different backend URL.

See the [npm package README](../../packages/laravel-devtools/README.md) for integration instructions.
