import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        // Force IPv4 loopback — Node's default "localhost" resolution on this
        // machine binds Vite to [::1] (IPv6), which some Windows browser/
        // network configs can't reach even though 127.0.0.1 works fine. That
        // mismatch makes every asset request from the page silently fail.
        host: '127.0.0.1',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
