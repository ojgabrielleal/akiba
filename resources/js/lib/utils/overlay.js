    let scrollLockCount = 0;
    let previousOverflow = [];

    function lockPageScroll() {
        if (scrollLockCount++ > 0) return;

        previousOverflow = [document.documentElement, document.body].map((element) => ({
            element,
            properties: ["overflow", "overflow-x", "overflow-y"].map((name) => ({
                name,
                value: element.style.getPropertyValue(name),
                priority: element.style.getPropertyPriority(name),
            })),
        }));

        for (const { element } of previousOverflow) {
            element.style.setProperty("overflow", "hidden", "important");
        }
    }

    function unlockPageScroll() {
        if (--scrollLockCount > 0) return;

        for (const { element, properties } of previousOverflow) {
            element.style.removeProperty("overflow");
            element.style.removeProperty("overflow-x");
            element.style.removeProperty("overflow-y");
            for (const { name, value, priority } of properties) {
                if (value) element.style.setProperty(name, value, priority);
            }
        }
        previousOverflow = [];
    }

export function overlay(node, { desktopQuery, close } = {}) {
    document.body.appendChild(node);
    lockPageScroll();

    const media = desktopQuery ? window.matchMedia(desktopQuery) : null;
    const handleResize = () => {
        if (media?.matches) close?.();
    };
    const handleKeydown = (event) => {
        if (event.key === "Escape") close?.();
    };
    media?.addEventListener("change", handleResize);
    window.addEventListener("keydown", handleKeydown);
    handleResize();

    return {
        destroy() {
            media?.removeEventListener("change", handleResize);
            window.removeEventListener("keydown", handleKeydown);
            node.remove();
            unlockPageScroll();
        },
    };
}
