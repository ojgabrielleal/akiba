import { get } from "svelte/store";
import { player } from "./playerStore.js";

const HEARTBEAT_INTERVAL = 90 * 1000;
const VISITOR_TOKEN_KEY = "akiba_public_visitor_token";

let interval;
let unsubscribePlayer;
let oauthState = {};

const visitorToken = () => {
    const stored = localStorage.getItem(VISITOR_TOKEN_KEY);

    if (stored) return stored;

    const token = window.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(36).slice(2)}`;
    localStorage.setItem(VISITOR_TOKEN_KEY, token);

    return token;
};

const publicIdentity = () => ({
    type: oauthState?.type ?? null,
    authenticated: Boolean(oauthState?.authenticated),
    profile: oauthState?.profile ? {
        uuid: oauthState.profile.uuid,
        provider: oauthState.profile.provider,
        username: oauthState.profile.username,
        nickname: oauthState.profile.nickname,
    } : null,
});

export const sendPublicPresenceHeartbeat = () => {
    if (typeof window === "undefined") return;

    const playerState = get(player);
    const isHiddenAndIdle = document.visibilityState === "hidden" && !playerState.playing && !playerState.loading;

    if (isHiddenAndIdle) return;

    window.axios.post("/public-presence/heartbeat", {
        visitor_token: visitorToken(),
        url: window.location.href,
        path: window.location.pathname,
        title: document.title,
        referrer: document.referrer,
        listening: Boolean(playerState.playing),
        player_loading: Boolean(playerState.loading),
        identity: publicIdentity(),
    }).catch(() => {});
};

export const startPublicPresence = (oauth = {}) => {
    oauthState = oauth ?? {};

    if (interval) return;

    sendPublicPresenceHeartbeat();

    interval = setInterval(sendPublicPresenceHeartbeat, HEARTBEAT_INTERVAL);
    unsubscribePlayer = player.subscribe((state) => {
        if (state.playing || state.loading) {
            sendPublicPresenceHeartbeat();
        }
    });

    document.addEventListener("visibilitychange", sendPublicPresenceHeartbeat);
};

export const stopPublicPresence = () => {
    if (interval) {
        clearInterval(interval);
        interval = undefined;
    }

    unsubscribePlayer?.();
    unsubscribePlayer = undefined;
    document.removeEventListener("visibilitychange", sendPublicPresenceHeartbeat);
};
