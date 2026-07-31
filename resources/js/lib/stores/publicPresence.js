import { get } from "svelte/store";
import { player } from "@/lib/stores";

const HEARTBEAT_URL = "/online-visitors/heartbeat";
const HEARTBEAT_INTERVAL = 25 * 1000;
const VISITOR_ID_KEY = "akiba_public_visitor_id";

function getVisitorId() {
    let visitorId = localStorage.getItem(VISITOR_ID_KEY);

    if (!visitorId) {
        visitorId = crypto.randomUUID();
        localStorage.setItem(VISITOR_ID_KEY, visitorId);
    }

    return visitorId;
}

function currentPath() {
    return `${window.location.pathname}${window.location.search}`;
}

async function sendHeartbeat() {
    await window.axios.post(HEARTBEAT_URL, {
        visitor_id: getVisitorId(),
        path: currentPath(),
        is_listening: get(player).playing,
    });
}

export function startPublicPresenceHeartbeat() {
    if (typeof window === "undefined") {
        return () => {};
    }

    sendHeartbeat().catch(() => {});

    const interval = window.setInterval(() => {
        sendHeartbeat().catch(() => {});
    }, HEARTBEAT_INTERVAL);

    return () => {
        window.clearInterval(interval);
    };
}
