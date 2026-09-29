import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { defineConfig } from 'vite'

export default defineConfig({
    root: 'app/routes',
    base: './',
    server: { host: '127.0.0.1', port: 5174 },
    build: { outDir: '../../dist/client', emptyOutDir: true },
    plugins: [vue(), tailwindcss()],
})
