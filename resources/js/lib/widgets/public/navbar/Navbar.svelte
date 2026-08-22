<script>
    import { Link, page, router } from "@inertiajs/svelte";
    import { onMount } from "svelte";
    import { fade, fly } from "svelte/transition";
    import { navbar } from "@/lib/constants";
    import {
        listPushNotifications,
        markPushNotificationAsRead,
        markPushNotificationsAsRead,
        OAuthAction,
        applyPublicTheme,
        requestPushNotificationSubscription,
        resolvePushNotificationPermission,
        dispatchOAuthAction,
        getStoredPublicTheme,
        publicThemes,
        setStoredPublicTheme,
    } from "@/lib/utils";
    import { Button, IconButton, Modal, Tooltip } from "@/lib/components/public";
    import NotificationPanel from "./NotificationPanel.svelte";
    import ThemeSwitcher from "./ThemeSwitcher.svelte";

    export let oauth = {};

    let mobilenavbar = false;
    let loginModalRef;
    let notifications = [];
    let notificationPanelOpen = false;
    let notificationPermission = "unsupported";
    let searchQuery = "";
    let selectedTheme = "akiba";

    $: profile = oauth?.profile;
    $: avatar = profile?.avatar || "/img/placeholders/avatar.webp";
    $: nickname = profile?.nickname || profile?.username || "Perfil";
    $: vapidPublicKey = $page.props.push?.vapid_public_key;
    $: canRequestPushNotifications = Boolean(vapidPublicKey);
    $: hasActiveNotifications = notificationPermission === "granted";
    $: hasUnreadNotifications = notifications.length > 0;
    $: canOpenProfile = oauth?.is_oauth || (oauth?.is_member && oauth?.can_view_profile && oauth?.can_update_profile);

    const closeMobileNavbar = () => {
        mobilenavbar = false;
    };

    const closeNotificationPanel = () => {
        notificationPanelOpen = false;
    };

    const loadNotifications = async () => {
        notifications = await listPushNotifications();
    };

    const openOAuthLogin = () => {
        closeMobileNavbar();
        closeNotificationPanel();
        loginModalRef.open();
    };

    const requestProfile = () => {
        closeMobileNavbar();
        closeNotificationPanel();

        if (oauth?.is_member && !oauth?.can_view_profile) {
            return;
        }

        dispatchOAuthAction(OAuthAction.OPEN_PROFILE);
    };

    const loginProviders = [
        {
            name: "google",
            label: "Entrar com Google",
            icon: "/svg/google.svg",
            class: "border border-blue-night/10 bg-neutral-white text-blue-night shadow-sm hover:bg-neutral-white",
        },
        {
            name: "discord",
            label: "Entrar com Discord",
            icon: "/svg/discord.svg",
            class: "bg-[#5865f2] text-neutral-white shadow-[0_0.75rem_1.5rem_rgba(88,101,242,0.28)] hover:brightness-110",
        },
    ];

    const submitSearch = () => {
        const normalizedQuery = searchQuery.trim();

        closeMobileNavbar();
        router.get("/buscar", normalizedQuery ? { q: normalizedQuery } : {});
    };

    const requestNotifications = async () => {
        const permission = await requestPushNotificationSubscription(vapidPublicKey);

        notificationPermission = permission;

        if (permission === "granted") {
            await loadNotifications();
        }
    };

    const handleNotificationClick = async (event = null) => {
        event?.stopPropagation();

        closeMobileNavbar();

        notificationPermission = resolvePushNotificationPermission();

        if (notificationPermission === "granted") {
            await loadNotifications();
            notificationPanelOpen = !notificationPanelOpen;
            return;
        }

        closeNotificationPanel();
        await requestNotifications();
    };

    const markNotificationAsRead = async (event) => {
        await markPushNotificationAsRead(event.detail.id);
        await loadNotifications();
    };

    const markAllNotificationsAsRead = async () => {
        await markPushNotificationsAsRead(notifications.map((notification) => notification.id));
        await loadNotifications();
        closeNotificationPanel();
    };

    const handleServiceWorkerMessage = (event) => {
        if (event.data?.type === "akiba:push-notification-received") {
            loadNotifications();
        }
    };

    const handleVisibilityChange = () => {
        if (document.visibilityState === "visible" && notificationPermission === "granted") {
            loadNotifications();
        }
    };

    const themes = publicThemes;

    const providerIconStyle = (provider) => `mask-image: url('${provider.icon}'); -webkit-mask-image: url('${provider.icon}');`;

    const syncSelectedThemeFromStorage = (event = null) => {
        selectedTheme = event?.detail?.theme ?? getStoredPublicTheme();
    };

    const selectTheme = (theme) => {
        selectedTheme = setStoredPublicTheme(theme);
        applyPublicTheme(selectedTheme);
    };

    onMount(() => {
        syncSelectedThemeFromStorage();
        applyPublicTheme(selectedTheme);

        notificationPermission = resolvePushNotificationPermission();

        if (notificationPermission === "granted") {
            loadNotifications();
        }

        navigator.serviceWorker?.addEventListener("message", handleServiceWorkerMessage);
        window.addEventListener("focus", loadNotifications);
        document.addEventListener("visibilitychange", handleVisibilityChange);
        const notificationInterval = window.setInterval(() => {
            if (notificationPermission === "granted") {
                loadNotifications();
            }
        }, 5000);

        return () => {
            navigator.serviceWorker?.removeEventListener("message", handleServiceWorkerMessage);
            window.removeEventListener("focus", loadNotifications);
            document.removeEventListener("visibilitychange", handleVisibilityChange);
            window.clearInterval(notificationInterval);
        };
    });

</script>

<nav aria-label="Navegação principal">
    <div class="container-page flex items-center justify-between gap-4 pt-10 pb-6 md:pb-8 lg:grid lg:grid-cols-[auto_minmax(0,1fr)_auto] lg:pb-8">
        <Link href="/site" class="group/logo w-52 shrink-0 focus-visible:outline-none" aria-label="Página inicial">
            <img
                src="/img/brand/logo.webp"
                alt="Akiba Station"
                class="w-full transition duration-300 ease-out group-hover/logo:scale-[1.02] group-focus-visible/logo:scale-[1.02] motion-reduce:transform-none motion-reduce:transition-none"
            />
        </Link>
        <ul class="mt-1 hidden w-full min-w-0 items-center justify-center lg:flex lg:justify-self-center">
            {#each navbar.public as item}
                <li class="flex h-6 items-center border-l border-neutral-gray/35 px-3 first:border-none first:pl-0 xl:px-4">
                    <Link
                        href={item.address}
                        aria-label={item.name}
                        class="group/item relative flex items-center gap-1 whitespace-nowrap font-noto-sans text-[0.825rem] font-extrabold uppercase italic text-neutral-gray transition-colors hover:text-orange-amber"
                    >
                        <img
                            src={item.icon}
                            alt=""
                            aria-hidden="true"
                            class="size-[1.3125rem] filter-neutral-gray group-hover/item:filter-orange-amber"
                        />
                        {item.name}
                        <span
                            class="absolute -bottom-2 left-0 h-0.5 w-full origin-left scale-x-0 rounded-full bg-orange-amber transition-transform duration-300 group-hover/item:scale-x-100"
                            aria-hidden="true"
                        ></span>
                    </Link>
                </li>
            {/each}
        </ul>

        <div class="hidden shrink-0 items-center justify-end gap-2 lg:flex">
            <form class="flex items-center" on:submit|preventDefault={submitSearch}>
                <label for="desktop-global-search" class="sr-only">Buscar</label>
                <input
                    id="desktop-global-search"
                    type="search"
                    name="q"
                    class="h-9 w-0 rounded-full border-0 bg-suspense-aurora/10 px-0 font-noto-sans text-sm text-suspense-aurora outline-none transition-all duration-300 placeholder:text-suspense-aurora/45 focus:w-56 focus:border focus:border-orange-amber focus:px-4"
                    placeholder="Buscar"
                    bind:value={searchQuery}
                />
                <IconButton
                    label="Buscar"
                    icon="/svg/search.svg"
                    tone="light"
                    surface="transparent"
                    size="sm"
                    iconClass="public-navbar-utility-icon"
                    tooltipPosition="bottom"
                    type="submit"
                />
            </form>
            {#if canRequestPushNotifications}
                <div class="relative">
                    {#if hasActiveNotifications}
                        <button
                            type="button"
                            class="relative flex size-8 cursor-pointer items-center justify-center rounded-full bg-transparent transition hover:brightness-110 active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
                            aria-label="Notificações"
                            on:click={handleNotificationClick}
                        >
                            <img
                                src="/svg/bell.svg"
                                alt=""
                                aria-hidden="true"
                                class="public-navbar-utility-icon size-4 filter-suspense-aurora"
                            />
                            {#if hasUnreadNotifications}
                                <span class="absolute right-1 top-1 size-2.5 rounded-full bg-orange-morning ring-2 ring-blue-night"></span>
                            {/if}
                        </button>
                    {:else}
                        <Tooltip position="bottom">
                            <button
                                type="button"
                                class="relative flex size-8 cursor-pointer items-center justify-center rounded-full bg-transparent transition hover:brightness-110 active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
                                aria-label="Ativar notificações"
                                on:click|stopPropagation={handleNotificationClick}
                            >
                                <img
                                    src="/svg/bell.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="public-navbar-utility-icon size-4 filter-suspense-aurora"
                                />
                            </button>
                            <span slot="content">Ativar notificações</span>
                        </Tooltip>
                    {/if}
                    <NotificationPanel
                        class="absolute right-0 top-11 hidden lg:block"
                        open={notificationPanelOpen}
                        {notifications}
                        on:close={closeNotificationPanel}
                        on:markRead={markNotificationAsRead}
                        on:markAllRead={markAllNotificationsAsRead}
                    />
                </div>
            {/if}
            {#if oauth?.authenticated && canOpenProfile}
                <Tooltip position="bottom">
                    <button
                        type="button"
                        aria-label={`Editar perfil de ${nickname}`}
                        class="public-navbar-avatar ml-1 flex size-12 shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-full border-2 border-suspense-aurora bg-suspense-aurora focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora"
                        on:click={requestProfile}
                    >
                        <img
                            src={avatar}
                            alt={nickname}
                            class="h-full w-full object-cover object-top scale-125"
                        />
                    </button>
                    <span slot="content">Editar perfil</span>
                </Tooltip>
            {:else if oauth?.authenticated}
                <div
                    class="public-navbar-avatar ml-1 flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-suspense-aurora bg-suspense-aurora"
                    aria-label={nickname}
                >
                    <img
                        src={avatar}
                        alt={nickname}
                        class="h-full w-full object-cover object-top scale-125"
                    />
                </div>
            {:else}
                <Button
                    size="sm"
                    shape="pill"
                    class="ml-1 transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 motion-reduce:transform-none motion-reduce:transition-none"
                    on:click={openOAuthLogin}
                >
                    <img
                        src="/svg/profile.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-4 filter-blue-marinho"
                    />
                    Entrar
                </Button>
            {/if}
            <ThemeSwitcher
                class="ml-1"
                {themes}
                {selectedTheme}
                onSelect={selectTheme}
            />
        </div>
        <div class="flex shrink-0 items-center gap-1 lg:hidden">
            {#if canRequestPushNotifications}
                <div class="relative">
                    {#if hasActiveNotifications}
                        <IconButton
                            label="Notificações"
                            icon="/svg/bell.svg"
                            tone="light"
                            surface="transparent"
                            size="md"
                            iconClass="public-navbar-utility-icon !size-4"
                            tooltipPosition="bottom"
                            on:click={handleNotificationClick}
                        />
                        {#if hasUnreadNotifications}
                            <span class="pointer-events-none absolute right-2 top-2 size-2.5 rounded-full bg-orange-morning ring-2 ring-blue-night"></span>
                        {/if}
                    {:else}
                        <IconButton
                            label="Ativar notificações"
                            icon="/svg/bell.svg"
                            tone="light"
                            surface="transparent"
                            size="md"
                            iconClass="public-navbar-utility-icon !size-4"
                            tooltipPosition="bottom"
                            on:click={handleNotificationClick}
                        />
                    {/if}
                </div>
            {/if}
            <IconButton
                label="Abrir menu de navegação"
                icon="/svg/menu.svg"
                tone="light"
                surface="transparent"
                size="md"
                iconClass="public-navbar-utility-icon"
                on:click={() => (mobilenavbar = true)}
            />
        </div>
    </div>

    {#if mobilenavbar}
        <div class="fixed inset-0 z-100 overflow-hidden lg:hidden">
            <button
                type="button"
                aria-label="Fechar menu de navegação"
                class="absolute inset-0 bg-blue-night/60 backdrop-blur-sm"
                transition:fade={{ duration: 250 }}
                on:click={closeMobileNavbar}
            ></button>
            <aside
                class="absolute right-0 top-0 h-dvh w-[min(17rem,86vw)] overflow-hidden bg-suspense-aurora shadow-2xl"
                transition:fly={{ x: 288, duration: 300 }}
                aria-label="Navegação mobile"
            >
                <div class="flex h-full min-h-0 flex-col">
                    <ul class="flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto px-4 pt-6 pb-3">
                        {#each navbar.public as item}
                            <li>
                                <Link
                                    href={item.address}
                                    aria-label={item.name}
                                    class="group/item flex min-h-9 items-center gap-2.5 rounded-md px-2 py-1 font-noto-sans text-[0.8125rem] font-extrabold uppercase italic text-blue-night transition duration-200 hover:text-orange-amber motion-reduce:transition-none"
                                    on:click={closeMobileNavbar}
                                >
                                    <img
                                        src={item.icon}
                                        alt=""
                                        aria-hidden="true"
                                        class="size-5 filter-blue-marinho group-hover/item:filter-orange-amber"
                                    />
                                    {item.name}
                                </Link>
                            </li>
                        {/each}
                    </ul>
                    <div class="border-t border-blue-night/10 px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                        <form class="mb-3 flex items-center gap-2" on:submit|preventDefault={submitSearch}>
                            <label for="mobile-global-search" class="sr-only">Buscar</label>
                            <input
                                id="mobile-global-search"
                                type="search"
                                name="q"
                                class="h-10 min-w-0 flex-1 rounded-full border border-blue-night/10 bg-neutral-white px-4 font-noto-sans text-sm text-blue-night outline-none transition focus:border-orange-amber"
                                placeholder="Buscar"
                                bind:value={searchQuery}
                            />
                            <IconButton
                                label="Buscar"
                                icon="/svg/search.svg"
                                tone="dark"
                                surface="transparent"
                                size="sm"
                                type="submit"
                            />
                        </form>
                        <div class="flex items-center justify-between gap-2">
                            {#if oauth?.authenticated && canOpenProfile}
                                <button
                                    type="button"
                                    class="flex min-w-0 items-center gap-2 text-left"
                                    on:click={requestProfile}
                                >
                                    <div class="size-10 shrink-0 overflow-hidden rounded-full border-2 border-suspense-aurora bg-suspense-aurora shadow">
                                        <img
                                            src={avatar}
                                            alt={nickname}
                                            class="h-full w-full object-cover object-top scale-125"
                                        />
                                    </div>
                                    <span class="min-w-0 truncate font-noto-sans text-xs font-extrabold text-blue-night">
                                        {nickname}
                                    </span>
                                </button>
                            {:else if oauth?.authenticated}
                                <div class="flex min-w-0 items-center gap-2 text-left">
                                    <div class="size-10 shrink-0 overflow-hidden rounded-full border-2 border-suspense-aurora bg-suspense-aurora shadow">
                                        <img
                                            src={avatar}
                                            alt={nickname}
                                            class="h-full w-full object-cover object-top scale-125"
                                        />
                                    </div>
                                    <span class="min-w-0 truncate font-noto-sans text-xs font-extrabold text-blue-night">
                                        {nickname}
                                    </span>
                                </div>
                            {:else}
                                <Button
                                    size="sm"
                                    shape="pill"
                                    class="transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 motion-reduce:transform-none motion-reduce:transition-none"
                                    on:click={openOAuthLogin}
                                >
                                    <img
                                        src="/svg/profile.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="size-4 filter-blue-marinho"
                                    />
                                    Entrar
                                </Button>
                            {/if}
                            <ThemeSwitcher
                                size="md"
                                {themes}
                                {selectedTheme}
                                onSelect={selectTheme}
                            />
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    {/if}
</nav>

<NotificationPanel
    class="fixed right-4 top-20 lg:hidden"
    open={notificationPanelOpen}
    {notifications}
    on:close={closeNotificationPanel}
    on:markRead={markNotificationAsRead}
    on:markAllRead={markAllNotificationsAsRead}
/>

{#if !oauth?.authenticated}
    <Modal
        bind:this={loginModalRef}
        label="Escolha uma opção de login"
        size="sm"
    >
        <div class="px-1 py-2 text-center">
            <div class="mb-5 flex flex-col items-center font-noto-sans">
                <span class="mb-3 flex size-8 items-center justify-center text-blue-night">
                    <span
                        class="block size-7 bg-current [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]"
                        style="mask-image: url('/svg/profile.svg'); -webkit-mask-image: url('/svg/profile.svg');"
                    ></span>
                </span>
                <p class="text-base font-extrabold uppercase italic text-blue-night">
                    Entre para continuar
                </p>
                <p class="mx-auto mt-1 max-w-64 text-sm font-normal leading-snug text-blue-night/70">
                    Use sua conta para comentar, reagir, pedir músicas e participar da Akiba.
                </p>
            </div>
            <div class="grid gap-3">
                {#each loginProviders as provider}
                    <a
                        href={`/oauth/${provider.name}/redirect`}
                        class={[
                            "group/provider relative flex min-h-[3.25rem] items-center justify-center gap-3 overflow-hidden rounded-md px-5 py-3 text-center font-noto-sans text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                            provider.class,
                        ]}
                    >
                        <span
                            aria-hidden="true"
                            class="block size-4 shrink-0 bg-current transition group-hover/provider:scale-110 [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]"
                            style={providerIconStyle(provider)}
                        ></span>
                        <span class="relative">{provider.label}</span>
                    </a>
                {/each}
            </div>
        </div>
    </Modal>
{/if}
