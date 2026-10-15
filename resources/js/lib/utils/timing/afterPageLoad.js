const DELAY = 5 * 1000;

export const scheduleAfterPageLoad = (onReady) => {
    let timer;

    const schedule = () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(onReady, DELAY);
    };

    if (document.readyState === "complete") {
        schedule();
    } else {
        window.addEventListener("load", schedule, { once: true });
    }

    return () => {
        window.clearTimeout(timer);
        window.removeEventListener("load", schedule);
    };
};
