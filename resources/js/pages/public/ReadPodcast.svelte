<script>
    import { Link, page } from "@inertiajs/svelte";

    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/public";
    import { CommentSection } from "@/lib/widgets/public";
    import { publicAnimations } from "@/lib/constants";
    import { resolvePlaceholderImage } from "@/lib/utils";

    $: ({ flash, oauth, onair, stream, podcast, comments, relatedPodcasts } = $page.props);
    $: pageUrl = $page.url;
    $: item = podcast?.data ?? {};
    $: relatedList = relatedPodcasts?.data ?? [];
    $: spotifyEmbedUrl = resolveSpotifyEmbedUrl(item.audio);

    const episodeCode = (podcastItem) => {
        const season = String(podcastItem.season ?? 1).padStart(2, "0");
        const episode = String(podcastItem.episode ?? 0).padStart(2, "0");

        return `S${season}-EP${episode}`;
    };

    function resolveSpotifyEmbedUrl(url) {
        if (!url) return null;

        try {
            const parsedUrl = new URL(url);

            if (parsedUrl.hostname !== "open.spotify.com") return url;
            if (parsedUrl.pathname.startsWith("/embed/episode/")) return url;
            if (!parsedUrl.pathname.startsWith("/episode/")) return url;

            parsedUrl.pathname = `/embed${parsedUrl.pathname}`;
            return parsedUrl.toString();
        } catch {
            return url;
        }
    }
</script>

<Meta meta={{ title: item.title ?? "Podcast" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <section class="public-read-content-background bg-blue-marinho pt-10 pb-16 text-suspense-aurora">
        <div class="container-page grid gap-8 lg:grid-cols-[minmax(0,1fr)_15rem]">
            <article class="min-w-0 font-noto-sans">
                <header class="mb-6 rounded-md bg-orange-amber px-3 py-2 text-blue-night sm:px-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                        <h1 class="shrink-0 text-4xl font-black uppercase italic leading-none lg:text-5xl">
                            {episodeCode(item)}
                        </h1>
                        <div class="hidden min-h-12 w-px bg-blue-night/25 sm:block" aria-hidden="true"></div>
                        <p class="max-w-3xl text-xl font-black italic leading-tight sm:text-2xl">
                            {item.title}
                        </p>
                    </div>
                </header>

                {#if item.description}
                    <section class="mb-8">
                        <div class="public-read-body text-base leading-relaxed lg:text-lg">
                            {@html item.description}
                        </div>
                    </section>
                {/if}

                {#if spotifyEmbedUrl}
                    <iframe
                        data-testid="embed-iframe"
                        style="border-radius:12px"
                        title={`Player do podcast ${item.title}`}
                        src={spotifyEmbedUrl}
                        width="100%"
                        height="152"
                        frameBorder="0"
                        allowfullscreen=""
                        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                        loading="lazy"
                    ></iframe>
                {:else if item.audio}
                    <a
                        href={item.audio}
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex min-h-20 items-center justify-center rounded-md border border-suspense-aurora/20 bg-blue-night px-4 text-center font-noto-sans text-base font-black uppercase italic text-orange-amber transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                    >
                        Abrir episódio
                    </a>
                {/if}

                <CommentSection commentable={item} {oauth} {comments} commentBasePath={`/podcast/${item.slug}`} />
            </article>

            {#if relatedList.length > 0}
                <aside class="min-w-0 font-noto-sans" aria-label="Outros podcasts">
                    <ul class="grid gap-5">
                        {#each relatedList as related (related.uuid)}
                            <li class="border-t border-suspense-aurora/45 pt-5 first:border-t-0 first:pt-0">
                                <Link href={related.href} class={["group block rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber", publicAnimations.cardInteractive]}>
                                    <div class="aspect-[4/3] overflow-hidden rounded-md bg-neutral-gray">
                                        <img
                                            src={resolvePlaceholderImage(related.image, "placeholder")}
                                            alt=""
                                            aria-hidden="true"
                                            class={["h-full w-full object-cover", publicAnimations.imageZoom]}
                                            loading="lazy"
                                        />
                                    </div>
                                    <h2 class="mt-2 line-clamp-4 font-noto-sans text-base font-black leading-tight text-orange-amber uppercase italic">
                                        {related.title}
                                    </h2>
                                </Link>
                            </li>
                        {/each}
                    </ul>
                </aside>
            {/if}
        </div>
    </section>
</Layout>
