<script>
    import { page, Link } from "@inertiajs/svelte";
    import { fade, fly } from "svelte/transition";
    import { hasPermission, resolvePlaceholderImage } from "@/lib/utils";
    import { navbar } from "@/lib/constants";
    import { IconButton } from "@/lib/components/private";

    $: ({ user } = $page.props);

    let mobilenavbar = false;
</script>

<nav class="container-page flex items-center justify-between" aria-label="Navegacao principal">
    <div class="w-8 xl:w-60">
        <img
            src="/img/brand/logo.webp"
            alt="Logo"
            class="hidden xl:block"
        />
        <img
            src="/favicon.ico"
            alt="Logo"
            class="block rounded-sm xl:hidden"
        />
    </div>
    <IconButton
        label="Abrir menu de navegacao"
        icon="/svg/menu.svg"
        tone="accent"
        surface="transparent"
        size="lg"
        class="xl:hidden"
        on:click={() => (mobilenavbar = true)}
    />
    <ul class="hidden flex-1 justify-center xl:flex">
        {#each navbar.private as item}
            {#if hasPermission(item.permission)}
                <li class="border-l border-neutral-gray/50 px-5 first:border-none group/item">
                    <IconButton
                        href={item.address}
                        label={item.name}
                        icon={item.icon}
                        tone="neutral"
                        iconClass="group-hover/item:filter-orange-amber"
                        surface="transparent"
                        size="navbar"
                        tooltipPosition="bottom"
                    />
                </li>
            {/if}
        {/each}
    </ul>
    <div class="hidden w-40 justify-end xl:flex">
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-1 text-sm font-noto-sans text-green-500">
                Online
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
            </span>
            <div class="relative group/avatar">
                <button
                    type="button"
                    aria-label="Abrir menu do usuario"
                    class="flex size-12 items-center justify-center overflow-hidden rounded-full border-2 border-neutral-gray shadow-md shadow-blue-night/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-gray"
                >
                    <img
                        src={resolvePlaceholderImage(user.avatar, "avatar", user.gender)}
                        alt={user.nickname}
                        class="h-full w-full object-cover object-top scale-125"
                    />
                </button>
                <div class="absolute right-0 top-full pt-3 invisible opacity-0 translate-y-1 group-hover/avatar:visible group-hover/avatar:opacity-100 group-hover/avatar:translate-y-0 group-focus-within/avatar:visible group-focus-within/avatar:opacity-100 group-focus-within/avatar:translate-y-0 transition-all duration-200 z-50">
                    <div class="w-40 rounded-md bg-suspense-aurora shadow-xl border border-neutral-gray/20 py-2">
                        <Link
                            href={`/panel/profile/${user.uuid}`}
                            class="block px-4 py-2 text-sm font-noto-sans font-medium text-neutral-gray hover:text-orange-amber hover:bg-neutral-gray/10"
                        >
                            Meu perfil
                        </Link>
                        <Link
                            href="/panel/logout"
                            method="post"
                            as="button"
                            class="cursor-pointer w-full text-left block px-4 py-2 text-sm font-noto-sans font-medium text-neutral-gray hover:text-orange-amber hover:bg-neutral-gray/10"
                        >
                            Desconectar
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navbar -->
    {#if mobilenavbar}
        <div class="fixed inset-0 z-100 overflow-hidden xl:hidden">
            <button
                type="button"
                aria-label="Fechar menu de navegacao"
                class="absolute inset-0 bg-blue-night/40 backdrop-blur-sm"
                transition:fade={{ duration: 300 }}
                on:click={() => (mobilenavbar = false)}
            ></button>
            <aside
                class="absolute top-0 right-0 h-dvh max-h-dvh w-[min(15rem,85vw)] bg-suspense-aurora shadow-xl"
                transition:fly={{ x: 240, duration: 300 }}
            >
                <div class="flex h-full flex-col">
                    <ul class="flex flex-1 flex-col gap-4 overflow-y-auto p-6">
                    {#each navbar.private as item}
                        {#if hasPermission(item.permission)}
                            <li>
                                <Link
                                    aria-label={item.name}
                                    href={item.address}
                                    class="group/item flex items-center gap-3 text-neutral-gray font-noto-sans font-extrabold italic uppercase hover:text-orange-amber"
                                    on:click={() => (mobilenavbar = false)}
                                >
                                    <img
                                        src={item.icon}
                                        alt=""
                                        aria-hidden="true"
                                        class="w-5 h-5 filter-neutral-gray group-hover/item:filter-orange-amber"
                                    />
                                    {item.name}
                                </Link>
                            </li>
                        {/if}
                    {/each}
                    </ul>
                    <div class="flex items-center justify-between gap-4 border-t border-neutral-gray/20 p-6 pb-[max(1.5rem,env(safe-area-inset-bottom))]">
                    <Link
                        href={`/panel/profile/${user.uuid}`}
                        class="min-w-0 flex items-center gap-3 group/profile"
                        on:click={() => (mobilenavbar = false)}
                    >
                        <div class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-neutral-gray shadow">
                            <img
                                src={resolvePlaceholderImage(user.avatar, "avatar", user.gender)}
                                alt={user.nickname}
                                class="h-full w-full object-cover object-top scale-125"
                            />
                        </div>
                        <div class="min-w-0">
                            <span class="block truncate text-sm font-noto-sans font-extrabold text-neutral-gray group-hover/profile:text-orange-amber">
                                {user.nickname}
                            </span>
                            <span class="flex items-center gap-1 text-xs font-noto-sans text-green-500">
                                Online
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            </span>
                        </div>
                    </Link>
                    <IconButton
                        href="/panel/logout"
                        method="post"
                        label="Desconectar"
                        icon="/svg/logout.svg"
                        tone="neutral"
                        surface="transparent"
                        size="lg"
                        class="rounded-full bg-neutral-gray/10 hover:bg-orange-amber/10"
                        on:click={() => (mobilenavbar = false)}
                    />
                    </div>
                    </div>
                </aside>
            </div>
    {/if}
</nav>
