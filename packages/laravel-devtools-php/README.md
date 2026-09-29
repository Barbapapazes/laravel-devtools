# barbapapazes/laravel-devtools

Local-only Laravel data feeds for the Route Inspector and Model Inspector in [`@barbapapazes/laravel-devtools`](https://www.npmjs.com/package/@barbapapazes/laravel-devtools).

## Install

Requires PHP 8.3+ and Laravel 13. Install in your Laravel application:

```sh
composer require --dev barbapapazes/laravel-devtools
```

Laravel discovers the service provider automatically. When `APP_ENV=local`, it registers `GET /__route-inspector/routes` and `GET /__model-inspector/models`. These feeds are not registered outside the local environment. Start your Laravel backend and configure the npm Vite plugin or Nuxt module to reach its URL (default: `http://127.0.0.1:8000`).

For frontend setup, see the [npm package documentation](https://github.com/Barbapapazes/laravel-devtools/tree/main/packages/laravel-devtools#readme).
