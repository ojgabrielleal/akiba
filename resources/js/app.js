import "./bootstrap";
import { createInertiaApp } from "@inertiajs/svelte";
import { mount } from "svelte";

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./pages/**/*.svelte", { eager: true });
        return pages[`./pages/${name}.svelte`];
    },
    setup({ el, App, props, plugin }) {
        if('serviceWorker' in navigator) {
            // PWA 
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('workers/pwa/PwaWorker.js')
                    .then(reg => console.log('Service Worker registrado'))
                    .catch(err => console.log('Erro ao registrar service worker'));
            });

            // One Signal Push
        }

        mount(App, { target: el, props });
    },
});
