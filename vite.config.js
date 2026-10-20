import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/custom.css',
                'resources/js/navbar.js',
                'resources/js/app.js',
            ],
            publicDirectory: 'public_html',
            refresh: true,
        }),
        tailwindcss(),
    ]
});