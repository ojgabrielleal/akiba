self.addEventListener("push", (event) => {
    const data = event.data?.json() ?? {};
    const title = data.title ?? "Akiba";

    event.waitUntil(
        self.registration.showNotification(title, {
            body: data.body,
            icon: data.icon ?? "/favicon.ico",
            data: {
                url: data.url ?? "/panel/locution",
            },
        }),
    );
});

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
