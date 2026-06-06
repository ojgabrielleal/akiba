<script>
    import { page, Link } from "@inertiajs/svelte";
    import { hasPermission } from "@/utils";
    import { navbar } from "@/data";
    import { Tooltip } from "@/ui/components/private";

    $: ({ user } = $page.props);

    let mobilenavbar = false;
</script>

<nav class="container-page flex items-center justify-between">
    <div class="w-8 lg:w-60">
        <img
            src="/img/brand/logo.webp"
            alt="Logo"
            class="hidden lg:block"
        />
        <img
            src="/favicon.ico"
            alt="Logo"
            class="block lg:hidden rounded-sm"
        />
    </div>
    <button
        type="button"
        aria-label=""
        class="w-6 lg:hidden"
        on:click={() => (mobilenavbar = !mobilenavbar)}
    >
        <img
            src="/svg/menu.svg"
            alt="Menu"
            class="filter-orange-citric"
        />
    </button>
    <ul class="hidden lg:flex flex-1 justify-center">
        {#each navbar.private as item}
            {#if hasPermission(item.permission)}
                <li class="px-5 first:pl-0 border-l first:border-none border-neutral-gray/50 group/item">
                    <Tooltip position="bottom">
                        <Link
                            title=""
                            aria-label={item.name}
                            href={item.address}
                        >
                            <img
                                src={item.icon}
                                alt=""
                                aria-hidden="true"
                                class="w-5 h-5 inline-block mr-1 filter-neutral-gray group-hover/item:filter-orange-citric"
                            />
                        </Link>
                        <span slot="content">
                            {item.name}
                        </span>
                    </Tooltip>
                </li>
            {/if}
        {/each}
    </ul>
    <div class="w-60 hidden lg:flex justify-end">
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-1 text-sm font-noto-sans text-green-500">
                Online
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
            </span>
            <div class="relative group/avatar">
                <button
                    type="button"
                    aria-label="Abrir menu do usuario"
                    class="bg-suspense-aurora w-10 h-10 rounded-full flex items-center justify-center overflow-hidden"
                >
                    <img
                        src={user.avatar}
                        alt={user.nickname}
                        class="w-full h-full object-cover object-top scale-200 "
                    />
                </button>
                <div class="absolute right-0 top-full pt-3 invisible opacity-0 translate-y-1 group-hover/avatar:visible group-hover/avatar:opacity-100 group-hover/avatar:translate-y-0 group-focus-within/avatar:visible group-focus-within/avatar:opacity-100 group-focus-within/avatar:translate-y-0 transition-all duration-200 z-50">
                    <div class="w-40 rounded-md bg-suspense-aurora shadow-xl border border-neutral-gray/20 py-2">
                        <Link
                            href={`/panel/profile/${user.uuid}`}
                            class="block px-4 py-2 text-sm font-noto-sans font-medium text-neutral-gray hover:text-orange-citric hover:bg-neutral-gray/10"
                        >
                            Meu perfil
                        </Link>
                        <Link
                            href="/panel/logout"
                            method="post"
                            as="button"
                            class="cursor-pointer w-full text-left block px-4 py-2 text-sm font-noto-sans font-medium text-neutral-gray hover:text-orange-citric hover:bg-neutral-gray/10"
                        >
                            Desconectar
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navbar -->
    <div class={["lg:hidden fixed inset-0 z-100 transition-opacity duration-300",
        { "opacity-100 pointer-events-auto": mobilenavbar },
        { "opacity-0 pointer-events-none": !mobilenavbar },
    ]}>
        <button
            type="button"
            aria-label="Fechar menu de navegacao"
            class="absolute inset-0 bg-blue-night/40 backdrop-blur-sm"
            on:click={() => (mobilenavbar = false)}>
        </button>
        <aside class={["absolute top-0 right-0 h-screen w-[min(15rem,85vw)] bg-suspense-aurora shadow-xl transition-transform duration-300",
            { "translate-x-0": mobilenavbar },
            { "translate-x-full": !mobilenavbar },
        ]}>
            <div class="h-full flex flex-col">
                <ul class="flex-1 overflow-y-auto p-6 flex flex-col gap-4">
                    {#each navbar.private as item}
                        {#if hasPermission(item.permission)}
                            <li>
                                <Link
                                    title=""
                                    aria-label={item.name}
                                    href={item.address}
                                    class="group/item flex items-center gap-3 text-neutral-gray font-noto-sans font-extrabold italic uppercase hover:text-orange-citric"
                                    on:click={() => (mobilenavbar = false)}
                                >
                                    <img
                                        src={item.icon}
                                        alt=""
                                        aria-hidden="true"
                                        class="w-5 h-5 filter-neutral-gray group-hover/item:filter-orange-citric"
                                    />
                                    {item.name}
                                </Link>
                            </li>
                        {/if}
                    {/each}
                </ul>
                <div class="p-6 border-t border-neutral-gray/20 flex items-center justify-between gap-4">
                    <Link
                        href={`/panel/profile/${user.uuid}`}
                        class="min-w-0 flex items-center gap-3 group/profile"
                        on:click={() => (mobilenavbar = false)}
                    >
                        <div class="bg-suspense-aurora w-7 h-7 rounded-full flex items-center justify-center overflow-hidden shadow shrink-0">
                            <img
                                src={user.avatar}
                                alt={user.nickname}
                                class="w-full h-full object-cover object-top scale-200"
                            />
                        </div>
                        <div class="min-w-0">
                            <span class="block truncate text-sm font-noto-sans font-extrabold text-neutral-gray group-hover/profile:text-orange-citric">
                                {user.nickname}
                            </span>
                            <span class="flex items-center gap-1 text-xs font-noto-sans text-green-500">
                                Online
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            </span>
                        </div>
                    </Link>
                    <Link
                        title=""
                        href="/panel/logout"
                        method="post"
                        as="button"
                        aria-label="Desconectar"
                        class="cursor-pointer w-10 h-10 rounded-full flex items-center justify-center bg-neutral-gray/10 hover:bg-orange-citric/10 group/logout shrink-0"
                        on:click={() => (mobilenavbar = false)}
                    >
                        <img
                            src="/svg/logout.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-5 h-5 filter-neutral-gray mr-1 group-hover/logout:filter-orange-citric"
                        />
                    </Link>
                </div>
            </div>
        </aside>
    </div>
</nav>
