export const defaultAdClient = "ca-pub-8675050263618388";

let adsenseScriptPromise = null;
const pendingAdElements = new WeakSet();
const initializedAdElements = new WeakSet();

const canLoadGoogleAdsense = () => {
    if (typeof window === "undefined" || typeof document === "undefined") return false;

    return ["akiba.com.br", "www.akiba.com.br"].includes(window.location.hostname);
};

export const loadGoogleAdsense = (adClient = defaultAdClient) => {
    if (!canLoadGoogleAdsense()) return Promise.resolve(false);
    if (adsenseScriptPromise) return adsenseScriptPromise;

    window.adsbygoogle = window.adsbygoogle || [];

    adsenseScriptPromise = new Promise((resolve, reject) => {
        const existingScript = document.querySelector('script[src*="pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"]');
        const script = existingScript || document.createElement("script");

        if (existingScript && (script.dataset.adsenseLoaded === "true" || window.adsbygoogle.loaded)) {
            resolve(true);
            return;
        }

        const cleanup = () => {
            script.removeEventListener("load", onLoad);
            script.removeEventListener("error", onError);
        };
        const onLoad = () => {
            cleanup();
            script.dataset.adsenseLoaded = "true";
            resolve(true);
        };
        const onError = () => {
            cleanup();
            script.remove();
            reject(new Error("Não foi possível carregar o script do Google AdSense."));
        };

        script.addEventListener("load", onLoad);
        script.addEventListener("error", onError);

        if (!existingScript) {
            script.async = true;
            script.crossOrigin = "anonymous";
            script.src = `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${encodeURIComponent(adClient)}`;
            document.head.appendChild(script);
        }
    }).catch((error) => {
        adsenseScriptPromise = null;
        throw error;
    });

    return adsenseScriptPromise;
};

const canInitialize = (element, signal) => (
    !signal?.aborted
    && element.isConnected
    && element.offsetWidth > 0
    && !initializedAdElements.has(element)
    && !element.hasAttribute("data-adsbygoogle-status")
);

export const pushGoogleAdSlot = async (element, { signal } = {}) => {
    if (!canLoadGoogleAdsense() || !element || pendingAdElements.has(element) || !canInitialize(element, signal)) return false;

    pendingAdElements.add(element);

    try {
        const loaded = await loadGoogleAdsense(element.dataset.adClient);
        if (!loaded || !canInitialize(element, signal)) return false;

        // The queue is global; never enqueue twice for the same DOM element.
        initializedAdElements.add(element);
        window.adsbygoogle.push({});
        return true;
    } finally {
        pendingAdElements.delete(element);
    }
};

// Svelte action: every new <ins> owns its observer and cancellation signal.
export const initializeGoogleAdSlot = (element) => {
    const controller = new AbortController();
    let observer;
    let started = false;

    const initialize = () => {
        if (started || !canLoadGoogleAdsense() || !canInitialize(element, controller.signal)) return;
        started = true;
        observer?.disconnect();

        pushGoogleAdSlot(element, { signal: controller.signal }).catch((error) => {
            if (controller.signal.aborted) return;
            console.error("Google AdSense: falha ao inicializar bloco de anúncio.", {
                adClient: element.dataset.adClient,
                adSlot: element.dataset.adSlot,
                error,
            });
        });
    };

    if (canLoadGoogleAdsense()) {
        observer = new ResizeObserver(initialize);
        observer.observe(element);
        // Actions can run before insertion; defer the first check until after mounting.
        queueMicrotask(initialize);
    }

    return {
        destroy() {
            controller.abort();
            observer?.disconnect();
        },
    };
};
