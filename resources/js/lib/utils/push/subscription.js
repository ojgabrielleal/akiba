import { router } from "@inertiajs/svelte";

const base64UrlToUint8Array = (base64Url) => {
    const padding = "=".repeat((4 - base64Url.length % 4) % 4);
    const base64 = (base64Url + padding).replace(/-/g, "+").replace(/_/g, "/");
    const raw = window.atob(base64);

    return Uint8Array.from([...raw].map((character) => character.charCodeAt(0)));
};

export const canUsePushNotifications = () => (
    "serviceWorker" in navigator &&
    "PushManager" in window &&
    "Notification" in window
);

export const resolvePushNotificationPermission = () => (
    canUsePushNotifications() ? Notification.permission : "unsupported"
);

const subscribeToPushNotifications = async (publicKey) => {
    if (!publicKey || !canUsePushNotifications()) {
        return null;
    }

    const registration = await navigator.serviceWorker.register("/push-worker.js");
    const currentSubscription = await registration.pushManager.getSubscription();
    const subscription = currentSubscription ?? await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: base64UrlToUint8Array(publicKey),
    });

    router.post("/panel/push-subscription", subscription.toJSON(), {
        preserveScroll: true,
    });

    return subscription;
};

export const requestPushNotificationSubscription = async (publicKey) => {
    if (!publicKey || !canUsePushNotifications()) {
        return "unsupported";
    }

    const permission = await Notification.requestPermission();

    if (permission !== "granted") {
        return permission;
    }

    await subscribeToPushNotifications(publicKey);

    return permission;
};
