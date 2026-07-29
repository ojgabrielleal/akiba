<script>
    import { Link } from "@inertiajs/svelte";
    import { navbar } from "@/lib/constants";

    const socials = [
        { name: "Discord", icon: "/svg/discord.svg", href: "https://discord.akiba.com.br" },
    ];

    const signals = ["オンエア", "アニメ", "音楽", "レビュー", "イベント"];
    const currentYear = new Date().getFullYear();
</script>

<footer class="bg-blue-night pt-12 pb-24">
    <div class="container-page border-t border-orange-amber pt-8">
        <div class="mb-8 flex flex-wrap items-center gap-x-4 gap-y-2 overflow-hidden font-noto-sans text-xs font-black tracking-[0.18em] text-blue-skywave/70 uppercase italic">
            {#each signals as item}
                <span class="transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-citric motion-reduce:transform-none motion-reduce:transition-none">
                    {item}
                </span>
                <span class="text-orange-amber" aria-hidden="true">•</span>
            {/each}
            <span>Rede Akiba</span>
        </div>

        <div class="grid gap-8 lg:grid-cols-[1.2fr_1fr_0.8fr]">
            <div class="min-w-0">
                <Link href="/site" class="group/logo block w-48 focus-visible:outline-none" aria-label="Página inicial">
                    <img
                        src="/img/brand/logo.webp"
                        alt="Rede Akiba"
                        class="w-full transition duration-300 ease-out group-hover/logo:scale-[1.02] group-focus-visible/logo:scale-[1.02] motion-reduce:transform-none motion-reduce:transition-none"
                    />
                </Link>
                <p class="mt-4 max-w-md font-noto-sans text-sm font-bold leading-relaxed text-suspense-aurora/75 uppercase italic">
                    Rede Akiba, o paraíso dos otakus.
                </p>
                <p class="mt-2 font-noto-sans text-lg font-black text-orange-amber uppercase italic">
                    ラジオ・ニュース・カルチャー
                </p>
            </div>

            <nav aria-label="Links do rodapé" class="min-w-0">
                <h2 class="flex items-center gap-2 font-noto-sans text-lg font-black text-orange-amber uppercase italic">
                    <img
                        src="/svg/star.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-5 filter-orange-amber"
                    />
                    Navegue
                </h2>
                <ul class="mt-4 grid grid-cols-2 gap-2">
                    {#each navbar.public as item}
                        <li class="min-w-0">
                            <Link
                                href={item.address}
                                class="group/item flex min-h-8 items-center gap-2 rounded-md font-noto-sans text-sm font-extrabold text-suspense-aurora/70 uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-citric focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                            >
                                <img
                                    src={item.icon}
                                    alt=""
                                    aria-hidden="true"
                                    class="size-5 shrink-0 filter-suspense-aurora group-hover/item:filter-orange-citric group-focus-visible/item:filter-orange-citric"
                                />
                                <span class="truncate">{item.name}</span>
                            </Link>
                        </li>
                    {/each}
                </ul>
            </nav>

            <div class="min-w-0">
                <h2 class="flex items-center gap-2 font-noto-sans text-lg font-black text-orange-amber uppercase italic">
                    <img
                        src="/svg/onair.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-5 filter-orange-amber"
                    />
                    Comunidade
                </h2>
                <ul class="mt-4 flex gap-3">
                    {#each socials as item}
                        <li>
                            <a
                                href={item.href}
                                aria-label={item.name}
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex size-10 items-center justify-center rounded-md bg-blue-ocean transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                            >
                                <img
                                    src={item.icon}
                                    alt=""
                                    aria-hidden="true"
                                    class="size-5 filter-suspense-aurora"
                                />
                            </a>
                        </li>
                    {/each}
                </ul>
                <div class="mt-5 rounded-md border border-blue-skywave/20 bg-blue-ocean/20 px-3 py-2 font-noto-sans uppercase italic">
                    <p class="text-[0.65rem] font-black tracking-[0.18em] text-blue-skywave">
                        またね!
                    </p>
                    <p class="text-sm font-black text-suspense-aurora">
                        Nos vemos no próximo programa.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-2 border-t border-blue-skywave/15 pt-4 font-noto-sans text-xs font-bold text-suspense-aurora/45 uppercase italic sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-2">
                <span>© 2016 - {currentYear} Rede Akiba</span>
                <span class="hidden text-blue-skywave/35 sm:inline" aria-hidden="true">|</span>
                <span>Todas as imagens de animes, mangás e etc. são marcas registradas dos seus respectivos proprietários.</span>
            </div>
            <span>Made in the otaku frequency</span>
        </div>
    </div>
</footer>
