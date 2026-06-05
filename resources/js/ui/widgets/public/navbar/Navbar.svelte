<script>
    import { Link } from "@inertiajs/svelte";
    import { navbar } from "@/data";

    let theme = "night";
    let mobilenavbar = false;
</script>

<nav class="w-full relative">
    <div class="container-page h-24 xl:h-[2.57rem] relative xl:top-15 flex xl:justify-between items-center">
        <button type="button"
            aria-label="Abrir menu"
            class="xl:hidden p-1 filter-suspense-aurora"
            on:click={() => (mobilenavbar = !mobilenavbar)}
        >
            <img
                src="/svg/menu.svg"
                alt=""
                class="w-5 h-5"
                aria-hidden="true"
            />
        </button>

        <div class="ml-5">
            <img
                src="/img/brand/logo.webp"
                alt="Logo"
                class="w-30 xl:w-35"
            />
        </div>

        <ul class="hidden xl:flex">
            {#each navbar.public as item}
                <li class="pr-5 pl-5 first:pl-0 border-l first:border-none border-neutral-gray/50 h-6 flex items-center">
                    <Link
                        title=""
                        href={item.address}
                        aria-label={item.name}
                        class="relative flex items-center gap-1 text-lg font-noto-sans font-extrabold text-neutral-gray hover:text-orange-amber italic uppercase group/item"
                    >
                        <img
                            src={item.icon}
                            alt=""
                            aria-hidden="true"
                            class="w-5 h-5 filter-neutral-gray group-hover/item:filter-orange-amber"
                            loading="lazy"
                        />
                        {item.name}
                        <span class="absolute -bottom-1 left-0 h-px rounded-full w-full bg-orange-amber scale-x-0 group-hover/item:scale-x-100 origin-left transition-transform duration-300"></span>
                    </Link>
                </li>
            {/each}
        </ul>

        <div class="w-35">
            <div class="hidden xl:flex justify-center items-center w-22 h-8 gap-1 bg-blue-skywave rounded-full">
                <button type="button"
                    aria-label="Modo Claro"
                    class={["cursor-pointer shrink-0 p-1",
                        { "bg-orange-morning rounded-full": theme === "light" },
                    ]}
                    on:click={() => (theme = "light")}
                >
                    <img
                        src="/svg/dawn.svg"
                        alt=""
                        aria-hidden="true"
                        class={["w-4 h-4", { "filter-orange-amber": theme === "light" }, { "filter-suspense-aurora": theme !== "light" }, ]}
                    />
                </button>
                <button type="button"
                    aria-label="Modo Akiba"
                    class={["cursor-pointer shrink-0 p-1",
                        { "bg-blue-night rounded-full": theme === "akiba" },
                    ]}
                    on:click={() => (theme = "akiba")}
                >
                    <img
                        src="/svg/akiba.svg"
                        alt=""
                        aria-hidden="true"
                        class={["w-4 h-4", { "filter-orange-amber": theme === "akiba" }, { "filter-suspense-aurora": theme !== "akiba" }, ]}
                    />
                </button>
                <button type="button"
                    aria-label="Modo Ecuro"
                    class={["cursor-pointer shrink-0 p-1",
                        { "bg-blue-night rounded-full": theme === "night" },
                    ]}
                    on:click={() => (theme = "night")}
                >
                    <img
                        src="/svg/night.svg"
                        alt=""
                        aria-hidden="true"
                        class={["w-4 h-4", { "filter-orange-morning": theme === "night" }, { "filter-suspense-aurora": theme !== "night" }, ]}
                    />
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navbar -->
    <div class={["fixed inset-0 z-150 transition-opacity duration-300 xl:hidden",
        { "opacity-100 pointer-events-auto": mobilenavbar },
        { "opacity-0 pointer-events-none": !mobilenavbar },
    ]}>
        <button aria-label=""
            type="button"
            aria-hidden="true"
            class="absolute inset-0 bg-blue-night/40 backdrop-blur-sm"
            on:click={mobilenavbar = false}>
        </button>

        <!-- Sidebar        -->
        <div class={["absolute left-0 top-0 h-screen w-72 bg-suspense-aurora shadow-2xl transition-transform duration-300 ease-out",
            { "translate-x-0": mobilenavbar },
            { "-translate-x-full": !mobilenavbar },
        ]}>
            <div class="p-6 border-b border-blue-night/10 flex items-center justify-between">
                <img
                    src="/img/brand/logo.webp"
                    alt="Logo"
                    class="w-30"
                />
                <button type="button"
                    aria-label="Fechar menu"
                    class="text-blue-night"
                    on:click={() => (mobilenavbar = false)}
                >
                    <img
                        src="/svg/x.svg"
                        alt=""
                        class="w-6 h-6"
                        aria-hidden="true"
                    />
                </button>
            </div>

            <nav class="p-6 overflow-y-auto h-[calc(100%-12rem)]">
                <ul class="space-y-5">
                    {#each navbar.public as item}
                        <li>
                            <Link
                                title=""
                                aria-label=""
                                href={item.address}
                                class="flex items-center gap-2 text-md font-noto-sans font-extrabold italic uppercase text-blue-night"
                                on:click={() => (mobilenavbar = false)}
                            >
                                <img
                                    src={item.icon}
                                    alt=""
                                    aria-hidden="true"
                                    class="w-5 h-5 filter-blue-night"
                                />
                                {item.name}
                            </Link>
                        </li>
                    {/each}
                </ul>
            </nav>

            <div class="absolute bottom-0 left-0 w-full p-6 border-t border-blue-night/10 bg-suspense-aurora z-10">
                <span class="w-full block text-[0.6rem] font-extrabold uppercase tracking-widest text-blue-night text-center opacity-40 mb-3">
                    Temas da Akiba
                </span>
                <div class="w-25 h-8 mx-auto flex justify-center items-center gap-1 bg-blue-skywave p-1 rounded-full">
                    <button aria-label="" type="button" class={["flex-1 flex justify-center items-center transition-all h-full",
                        { "bg-orange-morning rounded-full": theme === "light" },
                    ]} on:click={() => (theme = "light")}>
                        <img
                            src="/svg/dawn.svg"
                            alt="Claro"
                            class={["w-4 h-4", { "filter-orange-amber": theme === "light" }, { "filter-suspense-aurora": theme !== "light" }, ]}
                        />
                    </button>
                    <button aria-label="" type="button" class={["flex-1 flex justify-center items-center transition-all h-full",
                        { "bg-blue-night rounded-full": theme === "akiba" },
                    ]} on:click={() => (theme = "akiba")}>
                        <img
                            src="/svg/akiba.svg"
                            alt="Akiba"
                            class={["w-4 h-4", { "filter-orange-amber": theme === "akiba" }, { "filter-suspense-aurora": theme !== "akiba" }, ]}
                        />
                    </button>
                    <button aria-label="" type="button" class={["flex-1 flex justify-center items-center transition-all h-full",
                        { "bg-blue-night rounded-full": theme === "night" },
                    ]} on:click={() => (theme = "night")}>
                        <img
                            src="/svg/night.svg"
                            alt="Escuro"
                            class={["w-4 h-4", { "filter-orange-morning": theme === "night" }, { "filter-suspense-aurora": theme !== "night" }, ]}
                        />
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>
