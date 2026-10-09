const defaultAdClient = "ca-pub-8675050263618388";

let adsenseScriptPromise = null;

const canLoadGoogleAdsense = () => {
    if (typeof window === "undefined" || typeof document === "undefined") return false;

    return ["akiba.com.br", "www.akiba.com.br"].includes(window.location.hostname);
};

export const loadGoogleAdsense = (adClient = defaultAdClient) => {
    if (!canLoadGoogleAdsense()) return Promise.resolve(false);

    window.adsbygoogle = window.adsbygoogle || [];

    const scriptSelector = `script[src*="pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${adClient}"]`;
    if (document.querySelector(scriptSelector)) return Promise.resolve(true);

    if (adsenseScriptPromise) return adsenseScriptPromise;

    adsenseScriptPromise = new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.async = true;
        script.crossOrigin = "anonymous";
        script.src = `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${adClient}`;
        script.onload = () => resolve(true);
        script.onerror = reject;
        document.head.appendChild(script);
    });

    return adsenseScriptPromise;
};

export const pushGoogleAdSlot = async (adClient = defaultAdClient) => {
    if (typeof window === "undefined") return false;

    await loadGoogleAdsense(adClient);
    window.adsbygoogle = window.adsbygoogle || [];
    window.adsbygoogle.push({});

    return true;
};
