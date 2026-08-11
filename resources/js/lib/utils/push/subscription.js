import axios from "axios";

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

const openNotificationDatabase = () => new Promise((resolve, reject) => {
    if (!("indexedDB" in window)) {
        resolve(null);
        return;
    }

    const request = indexedDB.open("akiba-notifications", 1);

    request.onupgradeneeded = () => {
        const database = request.result;

        if (!database.objectStoreNames.contains("notifications")) {
            database.createObjectStore("notifications", {
                keyPath: "id",
                autoIncrement: true,
            });
        }
    };

    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
});

export const listPushNotifications = async () => {
    const database = await openNotificationDatabase();

    if (!database) {
        return [];
    }

    const notifications = await new Promise((resolve, reject) => {
        const transaction = database.transaction("notifications", "readonly");
        const store = transaction.objectStore("notifications");
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result ?? []);
        request.onerror = () => reject(request.error);
    });

    database.close();

    return notifications
        .filter((notification) => !notification.read_at)
        .sort((first, second) => new Date(second.created_at) - new Date(first.created_at))
        .slice(0, 10);
};

export const markPushNotificationAsRead = async (notificationId) => {
    const database = await openNotificationDatabase();

    if (!database) {
        return;
    }

    await new Promise((resolve, reject) => {
        const transaction = database.transaction("notifications", "readwrite");
        const store = transaction.objectStore("notifications");
        const request = store.get(notificationId);

        request.onsuccess = () => {
            const notification = request.result;

            if (notification) {
                store.put({
                    ...notification,
                    read_at: new Date().toISOString(),
                });
            }
        };
        request.onerror = () => reject(request.error);
        transaction.oncomplete = resolve;
        transaction.onerror = () => reject(transaction.error);
    });

    database.close();
};

export const markPushNotificationsAsRead = async (notificationIds) => {
    const database = await openNotificationDatabase();

    if (!database) {
        return;
    }

    await new Promise((resolve, reject) => {
        const transaction = database.transaction("notifications", "readwrite");
        const store = transaction.objectStore("notifications");
        const readAt = new Date().toISOString();

        notificationIds.forEach((notificationId) => {
            const request = store.get(notificationId);

            request.onsuccess = () => {
                const notification = request.result;

                if (notification) {
                    store.put({
                        ...notification,
                        read_at: readAt,
                    });
                }
            };
        });

        transaction.oncomplete = resolve;
        transaction.onerror = () => reject(transaction.error);
    });

    database.close();
};

const subscribeToPushNotifications = async (publicKey, endpoint) => {
    if (!publicKey || !canUsePushNotifications()) {
        return null;
    }

    const registration = await navigator.serviceWorker.register("/push-worker.js");
    const currentSubscription = await registration.pushManager.getSubscription();
    const subscription = currentSubscription ?? await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: base64UrlToUint8Array(publicKey),
    });

    await axios.post(endpoint, subscription.toJSON());

    return subscription;
};

export const requestPushNotificationSubscription = async (publicKey, endpoint = "/push-notification") => {
    if (!publicKey || !canUsePushNotifications()) {
        return "unsupported";
    }

    const permission = await Notification.requestPermission();

    if (permission !== "granted") {
        return permission;
    }

    await subscribeToPushNotifications(publicKey, endpoint);

    return permission;
};
