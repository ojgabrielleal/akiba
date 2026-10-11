const defaultAdClient = "ca-pub-8675050263618388";

let adsenseScriptPromise = null;

const logGoogleAdsenseError = (message, details = {}) => {
    console.error("Google AdSense:", message, details);
};

const canLoadGoogleAdsense = () => {
    if (typeof window === "undefined" || typeof document === "undefined") return false;

    return ["akiba.com.br", "www.akiba.com.br"].includes(window.location.hostname);
};

export const loadGoogleAdsense = (adClient = defaultAdClient) => {
    if (!canLoadGoogleAdsense()) return Promise.resolve(false);

    window.adsbygoogle = window.adsbygoogle || [];

    const scriptSrc = `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${adClient}`;
    const scriptSelector = `script[src*="pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${adClient}"]`;
    if (document.querySelector(scriptSelector)) return Promise.resolve(true);

    if (adsenseScriptPromise) return adsenseScriptPromise;

    adsenseScriptPromise = new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.async = true;
        script.crossOrigin = "anonymous";
        script.src = scriptSrc;
        script.onload = () => resolve(true);
        script.onerror = (event) => {
            const error = new Error(`Não foi possível carregar o script do Google AdSense: ${scriptSrc}`);

            logGoogleAdsenseError("falha ao carregar o script.", {
                adClient,
                src: scriptSrc,
                event,
            });

            adsenseScriptPromise = null;
            reject(error);
        };
        document.head.appendChild(script);
    });

    return adsenseScriptPromise;
};

export const pushGoogleAdSlot = async (adClient = defaultAdClient, adSlot = null) => {
    if (typeof window === "undefined") return false;

    try {
        await loadGoogleAdsense(adClient);
        window.adsbygoogle = window.adsbygoogle || [];
        window.adsbygoogle.push({});
    } catch (error) {
        logGoogleAdsenseError("falha ao inicializar bloco de anúncio.", {
            adClient,
            adSlot,
            error,
        });

        throw error;
    }

    return true;
};
