import { defineConfig, loadEnv } from "vite";
import { svelte, vitePreprocess } from "@sveltejs/vite-plugin-svelte";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        server: {
            host: env.VITE_HOST,
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
            svelte({
                preprocess: vitePreprocess(),
            }),
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
