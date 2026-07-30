<script>
    import { Link } from "@inertiajs/svelte";
    import { onMount } from "svelte";
    import { fade, fly } from "svelte/transition";
    import { navbar } from "@/lib/constants";
    import {
        listenForOAuthAction,
        OAuthAction,
    } from "@/lib/utils";
    import { Button, IconButton, Modal, Tooltip } from "@/lib/components/public";
    import ProfileForm from "../form/ProfileForm.svelte";
    import ThemeSwitcher from "./ThemeSwitcher.svelte";

    export let oauth = {};

    let mobilenavbar = false;
    let profileModalRef;
    let selectedTheme = "akiba";

    $: profile = oauth?.profile;
    $: avatar = profile?.avatar || "/img/placeholders/avatar.webp";
    $: nickname = profile?.nickname || profile?.username || "Perfil";

    const closeMobileNavbar = () => {
        mobilenavbar = false;
    };

    const openOAuthLogin = () => {
        window.location.assign("/oauth/discord/redirect");
    };

    const themes = [
        { name: "light", label: "Modo claro", icon: "/svg/dawn.svg" },
        { name: "akiba", label: "Modo Akiba", icon: "/svg/akiba.svg" },
        { name: "night", label: "Modo escuro", icon: "/svg/night.svg" },
    ];

    onMount(() =>
        listenForOAuthAction(
            OAuthAction.OPEN_PROFILE,
            () => profileModalRef.open(),
        ),
    );

</script>

<nav aria-label="Navegação principal">
    <div class="container-page flex items-center justify-between gap-4 pt-10 lg:grid lg:grid-cols-[auto_minmax(0,1fr)_auto]">
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
                        class="group/item relative flex items-center gap-1 whitespace-nowrap font-noto-sans text-[0.825rem] font-extrabold uppercase italic text-neutral-gray transition-colors hover:text-orange-citric"
                    >
                        <img
                            src={item.icon}
                            alt=""
                            aria-hidden="true"
                            class="size-[1.3125rem] filter-neutral-gray group-hover/item:filter-orange-citric"
                        />
                        {item.name}
                        <span
                            class="absolute -bottom-2 left-0 h-0.5 w-full origin-left scale-x-0 rounded-full bg-orange-citric transition-transform duration-300 group-hover/item:scale-x-100"
                            aria-hidden="true"
                        ></span>
                    </Link>
                </li>
            {/each}
        </ul>

        <div class="hidden shrink-0 items-center justify-end gap-2 lg:flex">
            {#if false}
                <IconButton
                    label="Buscar"
                    icon="/svg/search.svg"
                    tone="light"
                    surface="transparent"
                    size="sm"
                    tooltipPosition="bottom"
                />
                <IconButton
                    label="Notificações"
                    icon="/svg/bell.svg"
                    tone="light"
                    surface="transparent"
                    size="sm"
                    tooltipPosition="bottom"
                />
            {/if}
            {#if oauth?.authenticated}
                <Tooltip position="bottom">
                    <button
                        type="button"
                        aria-label={`Editar perfil de ${nickname}`}
                        class="ml-1 flex size-10 shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-full border-2 border-suspense-aurora bg-suspense-aurora shadow transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-lg focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                        on:click={() => profileModalRef.open()}
                    >
                        <img
                            src={avatar}
                            alt={nickname}
                            class="h-full w-full object-cover object-top"
                        />
                    </button>
                    <span slot="content">Editar perfil</span>
                </Tooltip>
            {:else}
                <Button
                    size="sm"
                    shape="pill"
                    class="ml-1"
                    on:click={openOAuthLogin}
                >
                    <img
                        src="/svg/discord.svg"
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
                on:select={(event) => (selectedTheme = event.detail)}
            />
        </div>
        <IconButton
            label="Abrir menu de navegação"
            icon="/svg/menu.svg"
            tone="light"
            surface="transparent"
            size="md"
            class="lg:hidden"
            on:click={() => (mobilenavbar = true)}
        />
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
                                    class="group/item flex min-h-9 items-center gap-2.5 rounded-md px-2 py-1 font-noto-sans text-[0.8125rem] font-extrabold uppercase italic text-blue-night transition duration-200 hover:translate-x-1 hover:bg-orange-citric/10 hover:text-orange-copper motion-reduce:transform-none motion-reduce:transition-none"
                                    on:click={closeMobileNavbar}
                                >
                                    <img
                                        src={item.icon}
                                        alt=""
                                        aria-hidden="true"
                                        class="size-5 filter-blue-marinho group-hover/item:filter-orange-citric"
                                    />
                                    {item.name}
                                </Link>
                            </li>
                        {/each}
                    </ul>
                    <div class="border-t border-blue-night/10 px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                        <div class="flex items-center justify-between gap-2">
                            {#if oauth?.authenticated}
                                <button
                                    type="button"
                                    class="flex min-w-0 items-center gap-2 text-left"
                                    on:click={() => {
                                        closeMobileNavbar();
                                        profileModalRef.open();
                                    }}
                                >
                                    <div class="size-8 shrink-0 overflow-hidden rounded-full border-2 border-blue-night/10">
                                        <img
                                            src={avatar}
                                            alt={nickname}
                                            class="h-full w-full object-cover object-top"
                                        />
                                    </div>
                                    <span class="min-w-0 truncate font-noto-sans text-xs font-extrabold text-blue-night">
                                        {nickname}
                                    </span>
                                </button>
                            {:else}
                                <Button
                                    size="sm"
                                    shape="pill"
                                    on:click={openOAuthLogin}
                                >
                                    <img
                                        src="/svg/discord.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="size-4 filter-blue-marinho"
                                    />
                                    Entrar
                                </Button>
                            {/if}
                            {#if false}
                                <div class="flex gap-1">
                                    <IconButton
                                        label="Buscar"
                                        icon="/svg/search.svg"
                                        tone="dark"
                                        surface="transparent"
                                        size="sm"
                                    />
                                    <IconButton
                                        label="Notificações"
                                        icon="/svg/bell.svg"
                                        tone="dark"
                                        surface="transparent"
                                        size="sm"
                                    />
                                </div>
                            {/if}
                            <ThemeSwitcher
                                size="md"
                                {themes}
                                {selectedTheme}
                                on:select={(event) => (selectedTheme = event.detail)}
                            />
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    {/if}
</nav>

{#if oauth?.authenticated}
    <Modal
        bind:this={profileModalRef}
        title="Meu perfil"
        label={`Perfil de ${nickname}`}
        size="md"
    >
        <ProfileForm
            {profile}
            close={() => profileModalRef.close()}
        />
    </Modal>
{/if}
