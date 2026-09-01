import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/utils.css',
                'resources/css/index.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: process.env.VITE_HOST || 'localhost',
        port: 5173,
        hmr: process.env.VITE_HMR_HOST
            ? { host: process.env.VITE_HMR_HOST }
            : true,
    },
});
