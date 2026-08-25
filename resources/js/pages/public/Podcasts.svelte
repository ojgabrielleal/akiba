<script>
    import { Link, page } from "@inertiajs/svelte";

    import { Meta } from "@/lib/components/shared";
    import { EditorialTitle, MinimalEmptyState, Pagination } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { publicAnimations } from "@/lib/constants";
    import { resolvePlaceholderImage } from "@/lib/utils";

    $: ({ flash, oauth, onair, stream, podcasts } = $page.props);
    $: pageUrl = $page.url;
    $: podcastList = Array.isArray(podcasts) ? podcasts : podcasts?.data ?? [];
    $: activeSort = $page.props.activeSort ?? new URL(pageUrl, "http://akiba.local").searchParams.get("sort") ?? "lancamento";

    const podcastOptions = [
        { key: "lancamento", label: "Lançamento", icon: "/svg/calendar.svg" },
        { key: "melhor-avaliados", label: "Melhor avaliados", icon: "/svg/bestAvaliable.svg" },
    ];

    const episodeCode = (podcast) => {
        const season = String(podcast.season ?? 1).padStart(2, "0");
        const episode = String(podcast.episode ?? 0).padStart(2, "0");

        return `S${season}-EP${episode}`;
    };

    const optionHref = (option) => {
        const url = new URL(pageUrl, "http://akiba.local");

        url.searchParams.set("sort", option.key);
        url.searchParams.delete("page");

        return `${url.pathname}${url.search}`;
    };
</script>

<Meta meta={{ title: "Podcasts" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <main class="public-podcast-page public-page-background flow-root bg-blue-night text-suspense-aurora">
        <EditorialTitle title="Podcasts" listLabel="Ordenação de podcasts">
            {#each podcastOptions as option}
                <li class="flex h-7 items-center border-l border-neutral-gray/35 px-3 first:border-none first:pl-0 xl:px-5">
                    <Link
                        href={optionHref(option)}
                        only={["podcasts", "activeSort"]}
                        preserveScroll
                        class={[
                            "group/category relative flex items-center gap-2 whitespace-nowrap rounded-md font-noto-sans text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-amber focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none",
                            activeSort === option.key ? "text-orange-amber" : "text-neutral-gray",
                        ]}
                    >
                        <img
                            src={option.icon}
                            alt=""
                            aria-hidden="true"
                            class={[
                                "size-5 group-hover/category:scale-105 group-hover/category:filter-orange-amber group-focus-visible/category:scale-105 group-focus-visible/category:filter-orange-amber motion-reduce:transform-none",
                                activeSort === option.key ? "filter-orange-amber" : "filter-neutral-gray",
                            ]}
                        />
                        {option.label}
                    </Link>
                </li>
            {/each}
        </EditorialTitle>

        <section class="bg-blue-marinho py-7 sm:py-9 lg:py-10" aria-labelledby="podcasts-title">
            <div class="container-page">
                <h1 id="podcasts-title" class="sr-only">Lista de podcasts</h1>

                {#if podcastList.length > 0}
                    <ol class="grid gap-7">
                        {#each podcastList as podcast (podcast.uuid)}
                            <li>
                            <Link
                                href={podcast.href}
                                aria-label={`Ouvir podcast: ${podcast.title}`}
                                class={["public-podcast-item group grid gap-4 rounded-md p-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-5 lg:grid-cols-[13rem_minmax(0,1fr)] xl:grid-cols-[14rem_minmax(0,1fr)]", publicAnimations.cardInteractive]}
                            >
                                    <div class="block overflow-hidden rounded-md bg-neutral-gray">
                                        <img
                                            src={resolvePlaceholderImage(podcast.image, "placeholder")}
                                            alt=""
                                            aria-hidden="true"
                                            class={["aspect-square w-full object-cover", publicAnimations.imageZoom]}
                                            loading="lazy"
                                        />
                                    </div>

                                    <article class="min-w-0 self-start font-noto-sans">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:gap-4">
                                            <span class="public-podcast-code shrink-0 text-4xl font-black uppercase italic leading-none text-orange-amber lg:text-5xl">
                                                {episodeCode(podcast)}
                                            </span>
                                            <div class="public-podcast-divider hidden min-h-11 w-px bg-suspense-aurora/25 sm:block" aria-hidden="true"></div>
                                            <h2 class="public-podcast-title max-w-3xl text-xl font-black italic leading-tight text-orange-amber sm:text-2xl lg:text-3xl">
                                                {podcast.title}
                                            </h2>
                                        </div>

                                        {#if podcast.summary}
                                            <p class="mt-3 line-clamp-3 text-base font-medium leading-tight text-suspense-aurora lg:text-lg">
                                                {podcast.summary}
                                            </p>
                                        {/if}
                                    </article>
                            </Link>
                            </li>
                        {/each}
                    </ol>

                    <Pagination pages={podcasts} only={["podcasts"]} />
                {:else}
                    <MinimalEmptyState
                        title="Nenhum podcast publicado"
                        message="Os episódios do AkibaCast aparecem aqui assim que entrarem no ar."
                    />
                {/if}
            </div>
        </section>
    </main>
</Layout>
