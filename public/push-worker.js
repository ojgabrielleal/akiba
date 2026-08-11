self.addEventListener("push", (event) => {
    const data = event.data?.json() ?? {};
    const title = data.title ?? "Akiba";

    event.waitUntil(Promise.all([
        data.audience === "all" ? storeNotification({ ...data, title }).then(notifyClients) : Promise.resolve(),
        self.registration.showNotification(title, {
            body: data.body,
            icon: data.icon ?? "/favicon.ico",
            image: data.banner ?? data.image,
            data: {
                url: data.url ?? "/panel/locution",
            },
        }),
    ]));
});

const openNotificationDatabase = () => new Promise((resolve, reject) => {
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

const storeNotification = async (notification) => {
    const database = await openNotificationDatabase();

    await new Promise((resolve, reject) => {
        const transaction = database.transaction("notifications", "readwrite");
        const store = transaction.objectStore("notifications");

        store.add({
            title: notification.title ?? "Akiba",
            body: notification.body ?? "",
            icon: notification.icon ?? "/favicon.ico",
            icon_type: notification.icon_type ?? "icon",
            banner: notification.banner ?? notification.image ?? null,
            url: notification.url ?? "/",
            created_at: new Date().toISOString(),
            read_at: null,
        });

        transaction.oncomplete = resolve;
        transaction.onerror = () => reject(transaction.error);
    });

    database.close();
};

const notifyClients = async () => {
    const clients = await self.clients.matchAll({
        type: "window",
        includeUncontrolled: true,
    });

    clients.forEach((client) => {
        client.postMessage({
            type: "akiba:push-notification-received",
        });
    });
};

self.addEventListener("notificationclick", (event) => {
    event.notification.close();

    const url = event.notification.data?.url ?? "/";

    event.waitUntil(
        self.clients.matchAll({ type: "window", includeUncontrolled: true })
            .then((clients) => {
                const existing = clients.find((client) => client.url === url);

                if (existing) {
                    return existing.focus();
                }

                return self.clients.openWindow(url);
            }),
    );
});
