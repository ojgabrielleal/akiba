import { page } from "@inertiajs/svelte";
import { get } from "svelte/store";

const STORAGE_KEY = "akiba_oauth_pending_action";
const ACTION_TTL = 5 * 60 * 1000;

export const OAuthAction = Object.freeze({
    OPEN_SONG_REQUEST: "open_song_request",
    OPEN_PROFILE: "open_profile",
});

const allowedActions = new Set(Object.values(OAuthAction));
const listeners = new Map();

let resolveScheduled = false;

const clearPendingOAuthAction = () => {
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch {
        // Authentication must still work when storage is unavailable.
    }
};

const readPendingOAuthAction = () => {
    let storedAction;

    try {
        storedAction = localStorage.getItem(STORAGE_KEY);
    } catch {
        return null;
    }

    if (!storedAction) return null;

    try {
        const pendingAction = JSON.parse(storedAction);
        const validAction = allowedActions.has(pendingAction?.action);
        const validExpiration =
            Number.isFinite(pendingAction?.expiresAt) &&
            pendingAction.expiresAt > Date.now();

        if (validAction && validExpiration) return pendingAction.action;
    } catch {
        // Invalid values are discarded below.
    }

    clearPendingOAuthAction();

    return null;
};

export const consumePendingOAuthAction = () => {
    const action = readPendingOAuthAction();

    if (action) clearPendingOAuthAction();

    return action;
};

const resolvePendingOAuthAction = () => {
    resolveScheduled = false;

    const oauth = get(page).props.oauth;

    if (!oauth?.authenticated) return;

    const action = readPendingOAuthAction();

    if (!action) return;

    if (oauth.is_oauth && !oauth.profile_completed) {
        const profileListeners = listeners.get(OAuthAction.OPEN_PROFILE);

        if (profileListeners?.size) {
            profileListeners.forEach((listener) => listener());
        }

        return;
    }

    const actionListeners = listeners.get(action);

    if (!actionListeners?.size) return;

    clearPendingOAuthAction();
    actionListeners.forEach((listener) => listener());
};

export const rememberOAuthAction = (action) => {
    if (!allowedActions.has(action)) return;

    try {
        localStorage.setItem(
            STORAGE_KEY,
            JSON.stringify({
                action,
                expiresAt: Date.now() + ACTION_TTL,
            }),
        );
    } catch {
        // The OAuth redirect remains usable without the pending UI action.
    }
};

export const listenForOAuthAction = (action, listener) => {
    if (!allowedActions.has(action)) return () => {};

    const actionListeners = listeners.get(action) ?? new Set();
    actionListeners.add(listener);
    listeners.set(action, actionListeners);

    if (!resolveScheduled) {
        resolveScheduled = true;
        setTimeout(resolvePendingOAuthAction);
    }

    return () => {
        actionListeners.delete(listener);

        if (actionListeners.size === 0) listeners.delete(action);
    };
};

export const dispatchOAuthAction = (action) => {
    if (!allowedActions.has(action)) return;

    const actionListeners = listeners.get(action);

    if (!actionListeners?.size) return;

    actionListeners.forEach((listener) => listener());
};
