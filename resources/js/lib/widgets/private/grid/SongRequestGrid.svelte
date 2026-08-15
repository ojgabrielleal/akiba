<script>
    import { page } from "@inertiajs/svelte";
    import { router } from "@inertiajs/svelte";
    import { onDestroy, onMount } from "svelte";

    import { EmptyState, IconButton, Section } from "@/lib/components/private/";
    import {
        requestPushNotificationSubscription,
        resolvePushNotificationPermission,
        resolvePlaceholderImage,
        songRequestPermissions,
    } from "@/lib/utils";

    export let title;
    export let onair = null;
    export let songRequests = null;

    const can = songRequestPermissions();
    let notificationPermission = resolvePushNotificationPermission();
    let refreshQueued = false;
    
    $: vapidPublicKey = $page.props.push?.vapid_public_key;

    $: actions = [
        {
            title: "Ativar notificações de pedidos",
            icon: "/svg/bell.svg",
            background: "bg-blue-skywave",
            textColor: "text-suspense-aurora",
            filter: "filter-suspense-aurora",
            permission: Boolean(vapidPublicKey) && notificationPermission === "default",
            onClick: () => requestNotifications(),
        },
        {
            title: "Encerrar programa",
            icon: "/svg/bloqued.svg",
            background: "bg-red-crimson",
            textColor: "text-suspense-aurora",
            filter: "filter-suspense-aurora",
            permission: can.locution.finish,
            onClick: () => requestFinishlocution(),
        },
        {
            title: onair.data.allows_song_requests ? "Fechar pedidos" : "Abrir Pedidos",
            icon: onair.data.allows_song_requests ? "/svg/messagesBloqued.svg" : "/svg/alerts.svg",
            permission: can.toggle,
            onClick: () => requestToggleSongRequest(),
        },
    ];

    function requestToggleSongRequest() {
        router.patch("/panel/locution/song-request/box/toggle", {}, {
            preserveScroll: true,
        });
    }

    async function requestNotifications() {
        notificationPermission = await requestPushNotificationSubscription(vapidPublicKey, "/panel/push-notification");
    }

    async function ensurePanelNotificationsSubscription() {
        if (vapidPublicKey && notificationPermission === "granted") {
            await requestPushNotificationSubscription(vapidPublicKey, "/panel/push-notification?silent=1");
        }
    }

    function refreshSongRequests() {
        if (refreshQueued) {
            return;
        }

        refreshQueued = true;

        router.reload({
            only: ["songRequests"],
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                refreshQueued = false;
            },
        });
    }

    function handlePushNotificationReceived(event) {
        const notification = event.data?.notification;
        const notificationPath = notification?.url
            ? new URL(notification.url, window.location.origin).pathname
            : null;

        if (
            event.data?.type === "akiba:push-notification-received" &&
            notification?.audience === "user" &&
            notificationPath === "/panel/locution"
        ) {
            refreshSongRequests();
        }
    }

    function markToReproduced(songrequest) {
        router.patch(`/panel/locution/song-request/${songrequest}/played`, {}, {
            preserveScroll: true,
        });
    }

    function markToCanceled(songrequest) {
        router.patch(`/panel/locution/song-request/${songrequest}/canceled`, {}, {
            preserveScroll: true,
        });
    }

    function requestFinishlocution() {
        router.patch(`/panel/locution/finish`);
    }

    function isFinished(item) {
        return item.type === "message"
            ? item.was_read || item.was_dismissed
            : item.was_reproduced || item.was_canceled;
    }

    function isSuccess(item) {
        return item.type === "message" ? item.was_read : item.was_reproduced;
    }

    function isRejected(item) {
        return item.type === "message" ? item.was_dismissed : item.was_canceled;
    }

    onMount(() => {
        ensurePanelNotificationsSubscription();
        navigator.serviceWorker?.addEventListener("message", handlePushNotificationReceived);
    });

    onDestroy(() => {
        navigator.serviceWorker?.removeEventListener("message", handlePushNotificationReceived);
    });

</script>

{#if can.list}
    <Section {title} {actions}>
        {#if songRequests?.data?.length}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                {#each songRequests.data as item}
                <article class={["relative w-full rounded-md p-3",
                    { "bg-gradient-green-pine-mint": isSuccess(item) },
                    { "bg-gradient-red-blood-crimson": isRejected(item) },
                    { "bg-gradient-blue-ocean-skywave": !isFinished(item) },
                ]}>
                    <div class="flex w-full min-w-0 items-center gap-1.5 font-noto-sans text-[1.2rem] font-extrabold italic text-suspense-aurora">
                        <img
                            src="/svg/profile.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-5 filter-suspense-aurora"
                            loading="lazy"
                        />
                        <span class="truncate">
                            {item.name}
                        </span>
                    </div>
                    {#if item.address}
                        <div class="w-full mt-1 flex gap-1.5 text-suspense-aurora text-[1rem] font-noto-sans font-light">
                            <img
                                src="/svg/location.svg"
                                alt=""
                                aria-hidden="true"
                                class="w-5 filter-suspense-aurora"
                                loading="lazy"
                            />
                            <span class="truncate">
                                {item.address}
                            </span>
                        </div>
                    {/if}
                    {#if item.birth_date}
                        <div class="mt-1 flex w-full min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-suspense-aurora text-[1rem] font-noto-sans font-light leading-none">
                            <div class="flex min-w-0 items-center gap-1.5">
                                <img
                                    src="/svg/cake.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="block h-5 w-5 shrink-0 filter-suspense-aurora"
                                    loading="lazy"
                                />
                                <span class="truncate leading-none">
                                    Aniversário: {item.birth_date.date}
                                </span>
                            </div>
                            {#if item.birth_date.is_birthday}
                                <span class="shrink-0 rounded-full bg-orange-amber px-2 py-0.5 text-[0.65rem] font-black uppercase italic text-blue-night">
                                    Aniversariante hoje
                                </span>
                            {/if}
                        </div>
                    {/if}
                    {#if item.music}
                        <div class="flex items-center justify-center w-full mt-5 mb-5">
                            <div class="relative w-full">
                                <div class="absolute left-0 w-2/5 h-[0.1rem] bg-orange-amber rounded-full top-1/2 -translate-y-1/2"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <img
                                        src="/svg/music.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-6 rotate-180 filter-orange-amber"
                                        loading="lazy"
                                    />
                                </div>
                                <div class="absolute right-0 w-2/5 h-[0.1rem] bg-orange-amber rounded-full top-1/2 -translate-y-1/2"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 min-w-0">
                            <img
                                src={resolvePlaceholderImage(item.music.image, "placeholder")}
                                alt={`Capa do anime ${item.music.production}`}
                                class="w-15 h-15 rounded-md object-cover object-top shrink-0"
                                loading="lazy"
                            />
                            <div class="min-w-0 flex-1">
                                <div class="w-full block text-suspense-aurora text-sm font-noto-sans truncate">
                                    <span class="font-light">
                                        Anime:
                                    </span>
                                    {item.music.production}
                                </div>
                                <div class="w-full block text-suspense-aurora text-sm font-noto-sans truncate">
                                    <span class="font-light">
                                        Artista:
                                    </span>
                                    {item.music.artist}
                                </div>
                                <div class="w-full block text-suspense-aurora text-sm font-noto-sans truncate">
                                    <span class="font-light">
                                        Música:
                                    </span>
                                    {item.music.name}
                                </div>
                            </div>
                        </div>
                    {/if}
                    <div class="flex items-center justify-center w-full mt-5 mb-5">
                        <div class="relative w-full">
                            <div class="absolute left-0 w-2/5 h-[0.1rem] bg-orange-amber rounded-full top-1/2 -translate-y-1/2"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <img
                                    src="/svg/telegram.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-7 filter-orange-amber"
                                    loading="lazy"
                                />
                            </div>
                            <div class="absolute right-0 w-2/5 h-[0.1rem] bg-orange-amber rounded-full top-1/2 -translate-y-1/2"></div>
                        </div>
                    </div>
                    <div class="h-25 line-clamp-3 text-suspense-aurora text-sm font-noto-sans mb-7">
                        {item.message}
                    </div>
                    <div class="absolute bottom-2 left-3 flex items-center gap-1 text-suspense-aurora text-sm font-noto-sans font-extrabold italic">
                        <img
                            src="/svg/clock.svg"
                            alt=""
                            aria-hidden="true"
                            class="block h-4 w-4 shrink-0 filter-suspense-aurora"
                            loading="lazy"
                        />
                        {item.created_at}
                    </div>
                    <div class="absolute bottom-2 right-3">
                        {#if !isFinished(item)}
                            <div class="flex gap-1">
                                {#if can.cancel}
                                    <IconButton
                                        variant="close"
                                        label={item.type === "message" ? "Dispensar recado" : "Marcar como cancelado"}
                                        size="sm"
                                        surface="default"
                                        tone="danger"
                                        on:click={() => markToCanceled(item.uuid)}
                                    />
                                {/if}
                                {#if can.reproduce}
                                    <IconButton
                                        variant="verify"
                                        label={item.type === "message" ? "Marcar como lido" : "Marcar como atendido"}
                                        size="sm"
                                        surface="default"
                                        tone="accent"
                                        on:click={() => markToReproduced(item.uuid)}
                                    />
                                {/if}
                            </div>
                        {/if}
                    </div>
                </article>
                {/each}
            </div>
        {:else}
            <EmptyState
                title="Nenhum pedido musical"
                description="Os pedidos dos ouvintes aparecerão aqui."
            />
        {/if}
    </Section>
{/if}
