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
                navigator.serviceWorker.register('PwaWorker.js')
            });
        }
        mount(App, { target: el, props });
    },
});
