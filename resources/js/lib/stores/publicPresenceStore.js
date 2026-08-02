import { get } from "svelte/store";
import { player } from "./playerStore.js";

const HEARTBEAT_INTERVAL = 90 * 1000;
const VISITOR_TOKEN_KEY = "akiba_public_visitor_token";

let interval;
let unsubscribePlayer;
let oauthState = {};
let heartbeatInFlight = false;
let lastHeartbeatAt = 0;
let lastListeningState = null;

const handleVisibilityChange = () => sendPublicPresenceHeartbeat({ force: true });

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

export const sendPublicPresenceHeartbeat = ({ force = false } = {}) => {
    if (typeof window === "undefined") return;

    const playerState = get(player);
    const isHiddenAndIdle = document.visibilityState === "hidden" && !playerState.playing && !playerState.loading;
    const listening = Boolean(playerState.playing);
    const now = Date.now();

    if (isHiddenAndIdle) return;
    if (heartbeatInFlight) return;
    if (!force && now - lastHeartbeatAt < HEARTBEAT_INTERVAL) return;

    heartbeatInFlight = true;
    lastHeartbeatAt = now;
    lastListeningState = listening;

    window.axios.post("/public-presence/heartbeat", {
        visitor_token: visitorToken(),
        url: window.location.href,
        path: window.location.pathname,
        title: document.title,
        referrer: document.referrer,
        listening,
        player_loading: Boolean(playerState.loading),
        identity: publicIdentity(),
    }).catch(() => {}).finally(() => {
        heartbeatInFlight = false;
    });
};

export const startPublicPresence = (oauth = {}) => {
    oauthState = oauth ?? {};

    if (interval) return;

    sendPublicPresenceHeartbeat({ force: true });

    interval = setInterval(sendPublicPresenceHeartbeat, HEARTBEAT_INTERVAL);
    unsubscribePlayer = player.subscribe((state) => {
        const listening = Boolean(state.playing);

        if (listening !== lastListeningState || state.loading) {
            sendPublicPresenceHeartbeat({ force: true });
        }
    });

    document.addEventListener("visibilitychange", handleVisibilityChange);
};

export const stopPublicPresence = () => {
    if (interval) {
        clearInterval(interval);
        interval = undefined;
    }

    unsubscribePlayer?.();
    unsubscribePlayer = undefined;
    document.removeEventListener("visibilitychange", handleVisibilityChange);
};
