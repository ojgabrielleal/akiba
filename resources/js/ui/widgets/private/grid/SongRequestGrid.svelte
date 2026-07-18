<script>
    export let title;

    import { onMount } from "svelte";
    import { page, router } from "@inertiajs/svelte";
    import { IconButton, Section } from "@/ui/components/private/";
    import { resolvePlaceholderImage, songRequestPermissions } from "@/utils";

    $: ({ onair, songRequests } = $page.props);

    let can = songRequestPermissions();

    $: actions = [
        {
            title: "Ativar notificações de pedidos",
            icon: "/svg/bell.svg",
            background: "bg-blue-skywave",
            textColor: "text-suspense-aurora",
            filter: "filter-suspense-aurora",
            permission: notificationPermission === "default",
            onClick: () => syncNotificationPermission(true),
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

    const requestToggleSongRequest = () => {
        router.patch("/panel/locution/song-request/box/toggle", {}, {
            preserveScroll: true,
        });
    };

    const markToReproduced = (songrequest) => {
        router.patch(`/panel/locution/song-request/${songrequest}/played`, {}, {
            preserveScroll: true,
        });
    };

    const markToCanceled = (songrequest) => {
        router.patch(`/panel/locution/song-request/${songrequest}/canceled`, {}, {
            preserveScroll: true,
        });
    };

    const requestFinishlocution = () => {
        router.patch(`/panel/locution/finish`);
    };

    // -------------- Notificações de PEDIDOS para os locutores --------------

    let mounted = false;
    let songRequestLength = 0;
    let notificationPermission = "unsupported";

    const syncNotificationPermission = async (shouldRequest = false) => {
        if (!("Notification" in window)) {
            notificationPermission = "unsupported";
            return;
        }

        notificationPermission = shouldRequest
            ? await Notification.requestPermission()
            : Notification.permission;
    };

    const notifyNewSongRequest = (songRequest) => {
        if (!("Notification" in window) || Notification.permission !== "granted") {
            return;
        }

        new Notification(`Novo Pedido DJ ${songRequest.name}`, {
            body: `O ouvinte ${songRequest.name} pediu uma música, Vá ver!`,
            icon: "/img/notifications/songRequestNotification.webp",
        });
    };

    const watchNewSongRequests = () => {
        const currentLength = songRequests.data.length;

        if (mounted && currentLength > songRequestLength) {
            notifyNewSongRequest(songRequests.data[0]);
        }

        songRequestLength = currentLength;
    };

    onMount(async () => {
        mounted = true;
        songRequestLength = songRequests.data.length;
        syncNotificationPermission();
    });

    $: songRequests, watchNewSongRequests();
</script>

{#if can.list}
    <Section {title} {actions}>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            {#each songRequests.data as item}
                <article class={["relative w-full rounded-md p-3",
                    { "bg-gradient-green-pine-mint": item.was_reproduced },
                    { "bg-gradient-red-blood-crimson": item.was_canceled },
                    { "bg-gradient-blue-ocean-skywave": !item.was_reproduced && !item.was_canceled },
                ]}>
                    <div class="w-70 flex items-center gap-1.5 text-suspense-aurora text-[1.2rem] font-noto-sans font-extrabold italic">
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
                    <div class="flex items-center justify-center w-full mt-5 mb-5">
                        <div class="relative w-full">
                            <div class="absolute left-0 w-2/5 h-[0.1rem] bg-orange-amber rounded-full top-1/2 -translate-y-1/2"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <img
                                    src="/svg/music.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-6 filter-orange-amber"
                                    loading="lazy"
                                />
                            </div>
                            <div class="absolute right-0 w-2/5 h-[0.1rem] bg-orange-amber rounded-full top-1/2 -translate-y-1/2"></div>
                        </div>
                    </div>
                    {#if item.music}
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
                    {:else}
                        <div class="text-suspense-aurora/60 text-sm font-noto-sans italic">
                            Música não disponível
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
                        {#if !item.was_reproduced && !item.was_canceled}
                            <div class="flex gap-1">
                                {#if can.cancel}
                                    <IconButton
                                        variant="close"
                                        label="Marcar como cancelado"
                                        size="sm"
                                        surface="default"
                                        tone="danger"
                                        on:click={() => markToCanceled(item.uuid)}
                                    />
                                {/if}
                                {#if can.reproduce}
                                    <IconButton
                                        variant="verify"
                                        label="Marcar como atendido"
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
    </Section>
{/if}
