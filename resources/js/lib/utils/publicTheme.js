import Cookies from "js-cookie";

const publicThemeCookieKey = "akiba_public_theme";

export const publicThemes = [
    { name: "light", label: "Modo claro", icon: "/svg/dawn.svg" },
    { name: "akiba", label: "Modo Akiba", icon: "/svg/akiba.svg" },
    { name: "night", label: "Modo escuro", icon: "/svg/night.svg" },
];

export const defaultPublicTheme = "akiba";

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
    });

    return selected;
};
