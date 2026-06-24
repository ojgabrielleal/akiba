import { defineConfig } from "vite";
import { svelte } from "@sveltejs/vite-plugin-svelte";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import path from "path";
import { fileURLToPath } from "url";
import svelteConfig from "./svelte.config.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig(() => {
    return {
        server: {
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            origin: 'http://localhost:5173',
            watch: {
                usePolling: true,
                interval: 100,
            },
            cors: {
                origin: [
                    'http://localhost:8000',
                    'http://127.0.0.1:8000',
                ],
            },
            hmr: {
                host: 'localhost',
                protocol: 'ws',
                clientPort: 5173,
            },
        },
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
        plugins: [
            laravel({
                input: [
                    "resources/js/app.js",
                    "resources/js/css/app.css",
                    "resources/js/css/custom.css",
                    "resources/js/css/quill.css",
                ],
                refresh: true,
            }),
            tailwindcss(),
            svelte(svelteConfig),
        ],
        build: {
            rollupOptions: {
                output: {
                    manualChunks(id) {
                        if (id.includes('node_modules')) {
                            return 'vendor';
                        }
                    }
                }
            }
        }
    };
});
