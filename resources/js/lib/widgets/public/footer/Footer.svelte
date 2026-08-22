<script>
    import { Link } from "@inertiajs/svelte";
    import { navbar } from "@/lib/constants";

    const socials = [
        { name: "Instagram", icon: "/svg/instagram.svg", href: "https://www.instagram.com/redeakiba/" },
        { name: "Facebook", icon: "/svg/facebook.svg", href: "https://www.facebook.com/RedeAkiba" },
        { name: "X / Twitter", icon: "/svg/x.svg", href: "https://x.com/RedeAkiba" },
        { name: "Discord", icon: "/svg/discord.svg", href: "https://discord.akiba.com.br" },
        { name: "YouTube", icon: "/svg/youtube.svg", href: "https://www.youtube.com/@RedeAkiba" },
        { name: "Apple Podcasts", icon: "/svg/apple-podcasts.svg", href: "https://podcasts.apple.com/us/podcast/akibacast/id1508240222" },
        { name: "Spotify", icon: "/svg/spotify.svg", href: "https://open.spotify.com/show/21MR4fnrOnKW7gNGYiWx37" },
        { name: "Amazon Music", icon: "/svg/amazon-music.svg", iconClass: "w-7 filter-suspense-aurora", href: "https://music.amazon.com.au/podcasts/fafe997e-b911-4a8e-8e38-dbbc6b285d83/akibacast" },
    ];

    const currentYear = new Date().getFullYear();
</script>

<footer class="public-footer pt-10 bg-blue-night pb-24">
    <div class="container-page border-t border-orange-citric pt-8">
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
                    Rede Akiba - O Paraíso dos Otakus
                </p>
                <p class="mt-2 font-noto-sans text-lg font-black text-orange-citric uppercase italic">
                    ラジオ・ニュース・カルチャー
                </p>
            </div>

            <nav aria-label="Links do rodapé" class="min-w-0">
                <h2 class="flex items-center gap-2 font-noto-sans text-lg font-black text-orange-citric uppercase italic">
                    Navegue
                </h2>
                <ul class="mt-4 grid grid-cols-3 gap-2">
                    {#each navbar.public as item}
                        <li class="min-w-0">
                            <Link
                                href={item.address}
	                                class="public-footer-link group/item flex min-h-8 items-center gap-2 rounded-md font-noto-sans text-sm font-extrabold text-suspense-aurora/70 uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-amber focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                            >
                                <img
                                    src={item.icon}
                                    alt=""
                                    aria-hidden="true"
	                                    class="public-footer-link-icon size-5 shrink-0 filter-suspense-aurora group-hover/item:filter-orange-amber group-focus-visible/item:filter-orange-amber"
                                />
                                <span class="truncate">{item.name}</span>
                            </Link>
                        </li>
                    {/each}
                </ul>
            </nav>

            <div class="min-w-0">
                <h2 class="flex items-center gap-2 font-noto-sans text-lg font-black text-orange-citric uppercase italic">
                    <img
                        src="/svg/onair.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-5 filter-orange-citric"
                    />
                    Comunidade
                </h2>
                <ul class="mt-4 flex flex-wrap gap-3">
                    {#each socials as item}
                        <li>
                            <a
                                href={item.href}
                                aria-label={item.name}
                                target="_blank"
                                rel="noopener noreferrer"
	                                class="public-footer-social flex size-10 items-center justify-center rounded-md bg-blue-ocean transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                            >
                                <img
                                    src={item.icon}
                                    alt=""
                                    aria-hidden="true"
	                                    class={["public-footer-social-icon", item.iconClass ?? "size-5 filter-suspense-aurora"]}
                                />
                            </a>
                        </li>
                    {/each}
                </ul>
                <div class="public-footer-note mt-5 rounded-md border border-blue-skywave/20 bg-blue-ocean/20 px-3 py-2 font-noto-sans uppercase italic">
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
                <span>© Rede Akiba 2016 - {currentYear}</span>
                <span class="hidden text-blue-skywave/35 sm:inline" aria-hidden="true">|</span>
                <span>Todas as imagens de animes, mangás e etc. são marcas registradas dos seus respectivos proprietários.</span>
            </div>
            <span>Feito com o coração!</span>
        </div>
    </div>
</footer>
