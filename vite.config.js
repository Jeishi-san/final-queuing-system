import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js', 'resources/css/vue.js',
                'resources/js/notification-manager.js', // Make sure this is here
            ],
            refresh: true,
        }),
        vue(),
    ],
});
