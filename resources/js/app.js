import "./bootstrap";
import { createInertiaApp } from "@inertiajs/svelte";
import { mount } from "svelte";
import PageTransitionLoader from "@/lib/components/public/feedback/PageTransitionLoader.svelte";
import PwaInstallPrompt from "@/lib/components/public/feedback/PwaInstallPrompt.svelte";
import { loadGoogleAdsense } from "@/lib/utils/adsense";

loadGoogleAdsense().catch(() => {});

if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/service-worker.js").catch(() => {});
    });
}

createInertiaApp({
    progress: {
        color: "#0091ff",
    },
    resolve: (name) => {
        const pages = import.meta.glob("./pages/**/*.svelte");
        return pages[`./pages/${name}.svelte`]();
    },
    setup({ el, App, props, plugin }) {
        mount(App, { target: el, props });
        mount(PageTransitionLoader, { target: document.body });
        mount(PwaInstallPrompt, { target: document.body });
    },
});
