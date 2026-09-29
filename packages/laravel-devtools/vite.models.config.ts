import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { defineConfig } from 'vite'

export default defineConfig({
    root: 'app/models',
    base: './',
    build: { outDir: '../../dist/models-client', emptyOutDir: true },
    plugins: [vue(), tailwindcss()],
})
