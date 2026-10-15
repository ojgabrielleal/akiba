<script>
    import { Link, page, router } from "@inertiajs/svelte";
    import { onMount } from "svelte";
    import { fade, fly } from "svelte/transition";
    import { navbar, publicAnimations } from "@/lib/constants";
    import {
        listPushNotifications,
        scheduleAfterPageLoad,
        markPushNotificationAsRead,
        markPushNotificationsAsRead,
        OAuthAction,
        applyPublicTheme,
        requestPushNotificationSubscription,
        defaultBrandLogo,
        resolvePushNotificationPermission,
        resolveCommemorativeLogo,
        dispatchOAuthAction,
        getStoredPublicTheme,
        publicThemes,
        setStoredPublicTheme,
        themeClass,
    } from "@/lib/utils";
    import { IconButton, MaskIcon, Tooltip } from "@/lib/components/public";
    import NotificationPanel from "./NotificationPanel.svelte";
    import ThemeSwitcher from "./ThemeSwitcher.svelte";

    export let oauth = {};

    let mobilenavbar = false;
    let notifications = [];
    let notificationPanelOpen = false;
    let notificationPermission = "unsupported";
    let searchQuery = "";
    let selectedTheme = "akiba";
    let brandLogo = resolveCommemorativeLogo();
    let pushPromptDismissed = true;
    let pushPromptReady = false;
    let cookieConsentVisible = true;
    let cookieConsentHeight = 0;
    let playerBarVisible = false;
    let requestingNotifications = false;
    let pushError = "";

    $: profile = oauth?.profile;
    $: avatar = profile?.avatar || "/img/placeholders/avatar.webp";
    $: nickname = profile?.nickname || profile?.username || "Perfil";
    $: vapidPublicKey = $page.props.push?.vapid_public_key;
    $: canRequestPushNotifications = Boolean(vapidPublicKey);
    $: hasActiveNotifications = notificationPermission === "granted";
    $: hasUnreadNotifications = notifications.length > 0;
    $: canShowPushPrompt = pushPromptReady && canRequestPushNotifications && notificationPermission === "default" && !pushPromptDismissed;
    $: canOpenProfile = oauth?.is_oauth || (oauth?.is_member && oauth?.can_view_profile && oauth?.can_update_profile);
    $: currentUrl = $page.url ?? "/";
    $: loginRedirect = `/entrar?reason=default&redirect=${encodeURIComponent(currentUrl)}`;

    const closeMobileNavbar = () => {
        mobilenavbar = false;
    };

    const closeNotificationPanel = () => {
        notificationPanelOpen = false;
    };

    const loadNotifications = async () => {
        notifications = await listPushNotifications();
    };

    const prepareLoginNavigation = () => {
        closeMobileNavbar();
        closeNotificationPanel();
    };

    const requestProfile = () => {
        closeMobileNavbar();
        closeNotificationPanel();

        if (oauth?.is_member && !oauth?.can_view_profile) {
            return;
        }

        dispatchOAuthAction(OAuthAction.OPEN_PROFILE);
    };

    const submitSearch = () => {
        const normalizedQuery = searchQuery.trim();

        closeMobileNavbar();
        router.get("/buscar", normalizedQuery ? { q: normalizedQuery } : {});
    };

    const isActiveNavItem = (item) => {
        const currentPath = new URL($page.url, "http://akiba.local").pathname;

        if (item.address === "/") {
            return currentPath === item.address;
        }

        return currentPath === item.address || currentPath.startsWith(`${item.address}/`);
    };

    const dismissPushPrompt = () => {
        pushPromptDismissed = true;

        try {
            localStorage.setItem("akiba_push_prompt_dismissed", "1");
        } catch {}
    };

    const requestNotifications = async () => {
        if (requestingNotifications) return;

        requestingNotifications = true;
        pushError = "";

        try {
            notificationPermission = await requestPushNotificationSubscription(vapidPublicKey);
            pushPromptDismissed = true;

            if (notificationPermission === "granted") {
                await loadNotifications();
            }
        } catch {
            pushError = "Não foi possível ativar. Tente novamente.";
        } finally {
            requestingNotifications = false;
        }
    };

    const handlePlayerBarVisibility = (event) => {
        playerBarVisible = Boolean(event.detail?.visible);
    };

    const handleCookieConsentVisibility = (event) => {
        cookieConsentVisible = Boolean(event.detail?.visible);
        cookieConsentHeight = Number(event.detail?.height) || 0;
    };

    const handleNotificationClick = async (event = null) => {
        event?.stopPropagation();

        closeMobileNavbar();

        notificationPermission = resolvePushNotificationPermission();

        try {
            pushPromptDismissed = localStorage.getItem("akiba_push_prompt_dismissed") === "1";
        } catch {
            pushPromptDismissed = false;
        }

        if (notificationPermission !== "default") {
            pushPromptDismissed = true;
        }

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

    const syncSelectedThemeFromStorage = (event = null) => {
        selectedTheme = event?.detail?.theme ?? getStoredPublicTheme();
    };

    const selectTheme = (theme) => {
        selectedTheme = setStoredPublicTheme(theme);
        applyPublicTheme(selectedTheme);
    };

    onMount(() => {
        const stopPushPromptTimer = scheduleAfterPageLoad(() => {
            notificationPermission = resolvePushNotificationPermission();
            pushPromptReady = true;
        });

        syncSelectedThemeFromStorage();
        applyPublicTheme(selectedTheme);

        notificationPermission = resolvePushNotificationPermission();

        try {
            pushPromptDismissed = localStorage.getItem("akiba_push_prompt_dismissed") === "1";
        } catch {
            pushPromptDismissed = false;
        }

        window.addEventListener("akiba:cookie-consent-visibility", handleCookieConsentVisibility);
        window.addEventListener("akiba:player-bar-visibility", handlePlayerBarVisibility);

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
            stopPushPromptTimer();
            window.removeEventListener("akiba:cookie-consent-visibility", handleCookieConsentVisibility);
            window.removeEventListener("akiba:player-bar-visibility", handlePlayerBarVisibility);
            navigator.serviceWorker?.removeEventListener("message", handleServiceWorkerMessage);
            window.removeEventListener("focus", loadNotifications);
            document.removeEventListener("visibilitychange", handleVisibilityChange);
            window.clearInterval(notificationInterval);
        };
    });

</script>

<nav aria-label="Navegação principal">
    <div class="container-page flex items-center justify-between gap-4 pt-10 pb-6 md:pb-8 lg:grid lg:grid-cols-[auto_minmax(0,1fr)_auto] lg:pb-8">
        <Link href="/" class="group/logo w-52 shrink-0 focus-visible:outline-none" aria-label="Página inicial">
            <img
                src={brandLogo.src}
                alt={brandLogo.alt}
                class="w-full transition duration-300 ease-out group-hover/logo:scale-[1.02] group-focus-visible/logo:scale-[1.02] motion-reduce:transform-none motion-reduce:transition-none"
                on:error={() => brandLogo = defaultBrandLogo}
            />
        </Link>
        <ul class="mt-1 hidden w-full min-w-0 items-center justify-center lg:flex lg:justify-self-center">
            {#each navbar.public as item}
                {@const active = isActiveNavItem(item)}
                <li class="flex h-6 items-center gap-1 border-l border-neutral-gray/35 px-2.5 first:border-none first:pl-0 xl:px-3.5">
                    <Link
                        href={item.address}
                        aria-label={item.name}
                        class={[
                            "group/item relative flex items-center gap-1 whitespace-nowrap font-noto-sans text-xs font-extrabold uppercase italic transition-colors hover:text-orange-amber",
                            active ? "text-orange-amber" : "text-neutral-gray",
                        ]}
                    >
                        <MaskIcon
                            icon={item.icon}
                            class={[
                                "size-[1.125rem] transition-colors group-hover/item:text-orange-amber",
                                active ? "text-orange-amber" : "text-neutral-gray",
                            ]}
                        />
                        {item.name}
                        <span
                            class={[
                                "absolute -bottom-2 left-0 h-0.5 w-full origin-left rounded-full bg-orange-amber transition-transform duration-300 group-hover/item:scale-x-100",
                                active ? "scale-x-100" : "scale-x-0",
                            ]}
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
                    tone="neutral"
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
                            <MaskIcon icon="/svg/bell.svg" class="public-navbar-utility-icon size-4 text-neutral-gray" />
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
                                <MaskIcon icon="/svg/bell.svg" class="public-navbar-utility-icon size-4 text-neutral-gray" />
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
                        class="public-navbar-avatar ml-1 flex size-12 shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-full border-2 border-neutral-gray/35 bg-transparent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
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
                    class="public-navbar-avatar ml-1 flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-neutral-gray/35 bg-transparent"
                    aria-label={nickname}
                >
                    <img
                        src={avatar}
                        alt={nickname}
                        class="h-full w-full object-cover object-top scale-125"
                    />
                </div>
            {:else}
                <Link
                    href={loginRedirect}
                    class="ml-1 inline-flex min-h-8 items-center justify-center gap-2 rounded-full bg-orange-amber px-3 py-1 font-noto-sans text-xs font-extrabold uppercase italic !text-white transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                    on:click={prepareLoginNavigation}
                >
                    <MaskIcon icon="/svg/profile.svg" class="size-4 !text-white" />
                    Entrar
                </Link>
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
                    <ul class="public-themed-scrollbar flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto px-4 pt-6 pb-3">
                        {#each navbar.public as item}
                            {@const active = isActiveNavItem(item)}
                            <li>
                                <Link
                                    href={item.address}
                                    aria-label={item.name}
                                    class={[
                                        "group/item flex min-h-9 items-center gap-2.5 rounded-md px-2 py-1 font-noto-sans text-[0.8125rem] font-extrabold uppercase italic transition duration-200 hover:text-orange-amber motion-reduce:transition-none",
                                        active ? "text-orange-amber" : "text-blue-night",
                                    ]}
                                    on:click={closeMobileNavbar}
                                >
                                    <MaskIcon
                                        icon={item.icon}
                                        class={[
                                            "size-5 transition-colors group-hover/item:text-orange-amber",
                                            active ? "text-orange-amber" : "text-blue-marinho",
                                        ]}
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
                                    <div class="size-10 shrink-0 overflow-hidden rounded-full border-2 border-neutral-gray/35 bg-transparent shadow">
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
                                    <div class="size-10 shrink-0 overflow-hidden rounded-full border-2 border-neutral-gray/35 bg-transparent shadow">
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
                                <Link
                                    href={loginRedirect}
                                    class="inline-flex min-h-8 items-center justify-center gap-2 rounded-full bg-orange-amber px-3 py-1 font-noto-sans text-xs font-extrabold uppercase italic !text-white transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                                    on:click={prepareLoginNavigation}
                                >
                                    <MaskIcon icon="/svg/profile.svg" class="size-4 !text-white" />
                                    Entrar
                                </Link>
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

{#if canShowPushPrompt}
    <aside
        aria-label="Ativar notificações da Akiba"
        style:--push-bottom={`calc(${playerBarVisible ? "5.5rem" : "1rem"} + env(safe-area-inset-bottom))`}
        style:--cookie-offset={`${cookieConsentVisible ? cookieConsentHeight + 12 : 0}px`}
        class={[
            "push-prompt fixed bottom-[calc(var(--push-bottom)+var(--cookie-offset))] 2xl:bottom-[var(--push-bottom)] left-4 right-4 z-[130] max-w-sm rounded-2xl border border-suspense-aurora/10 bg-blue-night/95 p-4 font-noto-sans text-suspense-aurora shadow-2xl shadow-blue-night/30 backdrop-blur-md [[data-public-theme=light]_&]:bg-white/95 [[data-public-theme=light]_&]:text-blue-night sm:right-auto sm:w-96 transition-[bottom] duration-300 ease-out motion-reduce:transition-none",
        ]}
    >
        <button
            type="button"
            aria-label="Fechar aviso de notificações"
            class={["absolute right-2 top-2 flex size-8 cursor-pointer items-center justify-center rounded-full text-orange-citric hover:bg-orange-citric/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric", publicAnimations.iconButtonInteractive]}
            on:click={dismissPushPrompt}
        >
            <MaskIcon icon="/svg/close.svg" class="size-3" />
        </button>
        <div class="flex items-start gap-3">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-orange-morning/10 text-orange-morning" aria-hidden="true">
                <MaskIcon icon="/svg/bell.svg" class="size-5" />
            </span>
            <div class="min-w-0 flex-1">
                <p class="pr-5 text-sm font-bold leading-snug">
                    Não perca nenhuma novidade da Akiba
                </p>
                <p class="mt-1.5 text-xs leading-relaxed text-suspense-aurora/65 [[data-public-theme=light]_&]:text-blue-night/65">
                    Receba avisos sobre novas matérias, programas ao vivo e novidades da Akiba.
                </p>
                <button
                    type="button"
                    disabled={requestingNotifications}
                    aria-busy={requestingNotifications}
                    class={["mt-3 inline-flex min-h-9 cursor-pointer items-center justify-center rounded-full bg-orange-citric px-4 py-2 text-xs font-bold text-blue-night hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric focus-visible:ring-offset-2 focus-visible:ring-offset-blue-night disabled:cursor-wait disabled:opacity-60", publicAnimations.buttonInteractive]}
                    on:click={requestNotifications}
                >
                    {requestingNotifications ? "Ativando…" : "Ativar notificações"}
                </button>
                {#if pushError}
                    <p role="alert" class="mt-2 text-xs text-orange-morning">{pushError}</p>
                {/if}
            </div>
        </div>
    </aside>
{/if}

<style>
    @keyframes push-prompt-enter {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .push-prompt {
        animation: push-prompt-enter 300ms ease-out;
    }

    @media (prefers-reduced-motion: reduce) {
        .push-prompt { animation: none; }
    }
</style>
