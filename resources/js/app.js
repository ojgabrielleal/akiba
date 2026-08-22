import "./bootstrap";
import { createInertiaApp } from "@inertiajs/svelte";
import { mount } from "svelte";
import PageTransitionLoader from "@/lib/components/public/feedback/PageTransitionLoader.svelte";

if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/push-worker.js").catch(() => {});
    });
}

createInertiaApp({
    progress: {
        color: "#0091ff",
    },
    resolve: (name) => {
        const pages = import.meta.glob("./pages/**/*.svelte", { eager: true });
        return pages[`./pages/${name}.svelte`];
    },
    setup({ el, App, props, plugin }) {
        mount(App, { target: el, props });
        mount(PageTransitionLoader, { target: document.body });
    },
});
