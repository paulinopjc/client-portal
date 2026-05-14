import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'

const scssPath = path.resolve(import.meta.dirname, 'resources/assets/scss').replace(/\\/g, '/')

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                loadPaths: [scssPath],
            },
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
})