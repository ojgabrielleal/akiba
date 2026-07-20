import { router } from "@inertiajs/svelte";

const listeners = new Set();

let channel;
let refreshing = false;

const connect = () => {
    if (channel || typeof BroadcastChannel === "undefined") return;

    channel = new BroadcastChannel("akiba_oauth");
    channel.onmessage = ({ data }) => {
        if (data?.type !== "authenticated" || refreshing) return;

        refreshing = true;
        router.reload({
            only: ["oauth"],
            onSuccess: () => listeners.forEach((listener) => listener()),
            onFinish: () => (refreshing = false),
        });
    };
};

export const listenForOAuth = (listener) => {
    listeners.add(listener);
    connect();

    return () => {
        listeners.delete(listener);

        if (listeners.size === 0) {
            channel?.close();
            channel = null;
        }
    };
};
