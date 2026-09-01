import Cookies from "js-cookie";

const publicThemeCookieKey = "akiba_public_theme";

export const publicThemes = [
    { name: "light", label: "Modo claro", icon: "/svg/dawn.svg" },
    { name: "akiba", label: "Modo Akiba", icon: "/svg/akiba.svg" },
    { name: "night", label: "Modo escuro", icon: "/svg/night.svg" },
];

export const defaultPublicTheme = "akiba";

const themeVariables = {
    light: {
        "--color-neutral-black": "#ffffff",
        "--color-neutral-ink": "#000014",
        "--color-neutral-white": "#000014",
        "--color-neutral-gray": "#5d6575",
        "--color-suspense-aurora": "#000014",
        "--color-suspense-sandstone": "#e8edf7",
        "--color-suspense-honeycream": "#fff0d6",
        "--color-red-crimson": "#d82932",
        "--color-red-blood": "#8f120b",
        "--color-orange-morning": "#ffd29f",
        "--color-orange-citric": "#ffaa35",
        "--color-orange-amber": "#ff8000",
        "--color-orange-copper": "#f65600",
        "--color-blue-ocean": "#0059c0",
        "--color-blue-marinho": "#ffffff",
        "--color-blue-skywave": "#006fd1",
        "--color-blue-cerulean": "#0059c0",
        "--color-blue-night": "#fffdf8",
        "--color-purple-mystic": "#9b2ce0",
        "--color-purple-lilac": "#875bd6",
        "--color-green-mint": "#008f58",
        "--color-green-forest": "#087a35",
        "--color-green-pine": "#095428",
        "--gradient-blue-ocean-cerulean": "linear-gradient(50deg, var(--color-orange-citric) 0%, var(--color-orange-morning) 50%, var(--color-orange-citric) 100%)",
        "--gradient-blue-cerulean-glow": "var(--gradient-orange-morning-aurora)",
        "--gradient-featured-card-night": "linear-gradient(110deg, var(--color-blue-night) 0%, var(--color-blue-marinho) 100%)",
        "--gradient-blue-ocean-skywave": "var(--gradient-orange-morning-aurora)",
        "--gradient-orange-morning-aurora": "linear-gradient(50deg, var(--color-orange-citric) 0%, var(--color-orange-morning) 100%)",
        "--gradient-green-forest-pine": "linear-gradient(270deg, var(--color-green-forest) 0%, var(--color-green-pine) 50%, var(--color-green-forest) 100%)",
        "--gradient-green-pine-mint": "linear-gradient(110deg, var(--color-green-pine) 0%, var(--color-green-mint) 50%, var(--color-green-pine) 100%)",
        "--gradient-red-crimson-blood": "linear-gradient(270deg, var(--color-red-crimson) 0%, var(--color-red-blood) 50%, var(--color-red-crimson) 100%)",
        "--gradient-red-blood-crimson": "linear-gradient(110deg, var(--color-red-blood) 0%, var(--color-red-crimson) 50%, var(--color-red-blood) 100%)",
    },
    night: {
        "--color-neutral-black": "#000000",
        "--color-neutral-ink": "#03030a",
        "--color-neutral-white": "#ffffff",
        "--color-neutral-gray": "#8b93a7",
        "--color-suspense-aurora": "#fffaf3",
        "--color-suspense-sandstone": "#d8d0c8",
        "--color-suspense-honeycream": "#ffe2aa",
        "--color-red-crimson": "#ff3b42",
        "--color-red-blood": "#4a0200",
        "--color-orange-morning": "#ffc171",
        "--color-orange-citric": "#ffaa35",
        "--color-orange-amber": "#ff8000",
        "--color-orange-copper": "#f65600",
        "--color-blue-ocean": "#00145c",
        "--color-blue-marinho": "#000014",
        "--color-blue-skywave": "#26a2ff",
        "--color-blue-cerulean": "#00418f",
        "--color-blue-night": "#000006",
        "--color-purple-mystic": "#c84dff",
        "--color-purple-lilac": "#BD87FF",
        "--color-green-mint": "#00b86b",
        "--color-green-forest": "#008c35",
        "--color-green-pine": "#003314",
        "--gradient-blue-ocean-cerulean": "linear-gradient(50deg, var(--color-blue-ocean) 0%, var(--color-blue-marinho) 50%, var(--color-blue-ocean) 100%)",
        "--gradient-blue-cerulean-glow": "linear-gradient(50deg, var(--color-blue-marinho) 0%, var(--color-blue-cerulean) 100%)",
        "--gradient-featured-card-night": "linear-gradient(110deg, var(--color-blue-night) 0%, var(--color-blue-marinho) 100%)",
        "--gradient-blue-ocean-skywave": "linear-gradient(110deg, var(--color-blue-ocean) 0%, var(--color-blue-skywave) 50%, var(--color-blue-ocean) 100%)",
        "--gradient-orange-morning-aurora": "linear-gradient(50deg, var(--color-orange-citric) 0%, var(--color-orange-morning) 100%)",
        "--gradient-green-forest-pine": "linear-gradient(270deg, var(--color-green-forest) 0%, var(--color-green-pine) 50%, var(--color-green-forest) 100%)",
        "--gradient-green-pine-mint": "linear-gradient(110deg, var(--color-green-pine) 0%, var(--color-green-mint) 50%, var(--color-green-pine) 100%)",
        "--gradient-red-crimson-blood": "linear-gradient(270deg, var(--color-red-crimson) 0%, var(--color-red-blood) 50%, var(--color-red-crimson) 100%)",
        "--gradient-red-blood-crimson": "linear-gradient(110deg, var(--color-red-blood) 0%, var(--color-red-crimson) 50%, var(--color-red-blood) 100%)",
    },
};

const themeVariableNames = Object.keys(themeVariables.light);

const fixedThemeClasses = {
    text: {
        "suspense-aurora": "text-[#fffaf3]",
        "suspense-aurora/45": "text-[color-mix(in_srgb,#fffaf3_45%,transparent)]",
        "suspense-aurora/60": "text-[color-mix(in_srgb,#fffaf3_60%,transparent)]",
        "suspense-aurora/75": "text-[color-mix(in_srgb,#fffaf3_75%,transparent)]",
        "blue-night": "text-[#000014]",
        "blue-marinho": "text-[#000036]",
    },
    bg: {
        "suspense-aurora": "bg-[#fffaf3]",
        "blue-night": "bg-[#000014]",
        "blue-marinho": "bg-[#000036]",
        "neutral-light": "bg-[#e8e8e8]",
    },
    border: {
        "suspense-aurora": "border-[#fffaf3]",
        "blue-night": "border-[#000014]",
    },
    placeholder: {
        "suspense-aurora": "placeholder:text-[#fffaf3]",
        "blue-night": "placeholder:text-[#000014]",
    },
};

const themedThemeClasses = {
    light: {
        text: {
            "blue-night": "[[data-public-theme=light]_&]:text-[#000014]",
            "blue-marinho": "[[data-public-theme=light]_&]:text-[#000036]",
            "suspense-aurora": "[[data-public-theme=light]_&]:text-[#fffaf3]",
            "suspense-aurora/45": "[[data-public-theme=light]_&]:text-[color-mix(in_srgb,#fffaf3_45%,transparent)]",
            "suspense-aurora/75": "[[data-public-theme=light]_&]:text-[color-mix(in_srgb,#fffaf3_75%,transparent)]",
            "orange-citric": "[[data-public-theme=light]_&]:text-orange-citric",
            "blue-cerulean": "[[data-public-theme=light]_&]:text-blue-cerulean",
            "blue-night/70": "[[data-public-theme=light]_&]:text-[color-mix(in_srgb,#000014_70%,transparent)]",
        },
        bg: {
            "suspense-aurora": "[[data-public-theme=light]_&]:bg-[#fffaf3]",
            "neutral-light": "[[data-public-theme=light]_&]:bg-[#e8e8e8]",
            "orange-morning": "[[data-public-theme=light]_&]:bg-orange-morning",
            "orange-amber": "[[data-public-theme=light]_&]:bg-orange-amber",
            "blue-cerulean": "[[data-public-theme=light]_&]:bg-blue-cerulean",
        },
        "after:bg": {
            "blue-cerulean": "[[data-public-theme=light]_&]:after:bg-blue-cerulean",
        },
    },
};

const resolveThemeClass = (kind, token, options = {}) => {
    if (options.fixed) {
        return fixedThemeClasses[kind]?.[token] ?? "";
    }

    return `${kind}-${token}`;
};

export const themeClass = (kind, token, options = {}) => {
    if (options.theme) {
        return themedThemeClasses[options.theme]?.[kind]?.[token] ?? "";
    }

    const className = resolveThemeClass(kind, token, options);

    return className;
};

export const normalizePublicTheme = (theme) => (
    publicThemes.some((item) => item.name === theme) ? theme : defaultPublicTheme
);

export const getStoredPublicTheme = () => {
    return normalizePublicTheme(Cookies.get(publicThemeCookieKey));
};

export const setStoredPublicTheme = (theme) => {
    const selected = normalizePublicTheme(theme);

    Cookies.set(publicThemeCookieKey, selected, {
        expires: 365,
        sameSite: "lax",
    });

    return selected;
};

export const applyPublicTheme = (theme) => {
    const selected = normalizePublicTheme(theme);

    document.querySelectorAll("[data-public-theme-scope]").forEach((element) => {
        element.dataset.publicTheme = selected;

        themeVariableNames.forEach((name) => {
            element.style.removeProperty(name);
        });

        Object.entries(themeVariables[selected] ?? {}).forEach(([name, value]) => {
            element.style.setProperty(name, value);
        });
    });

    return selected;
};
