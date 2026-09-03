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
            rolldownOptions: {
                checks: {
                    pluginTimings: false,
                },
            },
            rollupOptions: {
                output: {
                    manualChunks(id) {
                        if (id.includes('node_modules/chart.js')) {
                            return 'chart';
                        }

                        if (id.includes('node_modules/quill')) {
                            return 'editor';
                        }

                        if (id.includes('node_modules/@inertiajs') || id.includes('node_modules/svelte')) {
                            return 'app-runtime';
                        }

                        if (id.includes('node_modules/axios') || id.includes('node_modules/js-cookie') || id.includes('node_modules/svelte-hot-french-toast')) {
                            return 'app-utils';
                        }

                        if (id.includes('node_modules')) {
                            return 'vendor';
                        }

                        if (id.includes('/resources/js/pages/private/')) {
                            return 'private-pages';
                        }

                        if (id.includes('/resources/js/pages/public/')) {
                            return 'public-pages';
                        }
                    }
                }
            }
        }
    };
});
