<script>
    import { onMount } from "svelte";
    import { Deferred, page, router } from "@inertiajs/svelte";
    import { publicAnimations } from "@/lib/constants/public/animation";
    import { Meta } from "@/lib/components/shared";
    import { Section } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import {
        EventCalendarGrid,
        FeaturedGrid,
        LatestPodcastsGrid,
        MainPlayer,
        MobilePlayer,
        PostListGrid,
        ReviewListGrid,
        LatestPollCard,
    } from "@/lib/widgets/public";

    let availableVersion = null;
    let refreshing = false;
    let refreshError = false;
    let refreshGeneration = 0;

    $: ({
        contentVersion,
        onair,
        stream,
        featuredPosts,
        latestReviews,
        posts,
        events,
        podcasts,
        latestPoll,
        flash,
        oauth,
    } = $page.props);
    $: pageUrl = $page.url;
    $: featuredPostList = Array.isArray(featuredPosts) ? featuredPosts : featuredPosts?.data ?? [];
    $: poll = latestPoll?.data ?? null;
    $: hasFeaturedPosts = featuredPostList.length > 0;
    $: hasUpdates = availableVersion !== null && availableVersion !== contentVersion;

    const skeletonItems = (count) => Array.from({ length: count }, (_, index) => index);

    function refreshContent() {
        if (refreshing) return;
        refreshing = true;
        refreshGeneration += 1;
        refreshError = false;
        router.reload({
            only: ["contentVersion", "featuredPosts", "posts", "latestReviews", "events"],
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => { availableVersion = null; },
            onError: () => { refreshError = true; },
            onFinish: () => { refreshing = false; },
        });
    }

    onMount(() => {
        let controller = null;
        let disposed = false;

        async function checkForUpdates() {
            if (document.hidden || controller || refreshing || disposed) return;
            controller = new AbortController();
            const generation = refreshGeneration;
            const timeout = setTimeout(() => controller?.abort(), 10000);
            try {
                const response = await fetch("/home/content-version", {
                    signal: controller.signal,
                    cache: "no-store",
                    headers: { Accept: "application/json" },
                });
                if (!response.ok) return;
                const data = await response.json();
                if (!disposed && !refreshing && generation === refreshGeneration && typeof data.version === "string") {
                    availableVersion = data.version;
                }
            } catch {
                // A temporary connection failure is retried on the next interval.
            } finally {
                clearTimeout(timeout);
                controller = null;
            }
        }

        function handleVisibility() {
            if (document.hidden) controller?.abort();
            else checkForUpdates();
        }

        const interval = setInterval(checkForUpdates, 30000);
        document.addEventListener("visibilitychange", handleVisibility);
        return () => {
            disposed = true;
            clearInterval(interval);
            controller?.abort();
            document.removeEventListener("visibilitychange", handleVisibility);
        };
    });
</script>

<Meta />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <h1 class="sr-only">Akiba Station</h1>
    {#if hasUpdates}
        <div class="fixed inset-x-4 top-24 z-40 flex justify-center pointer-events-none" role="status" aria-live="polite">
            <button
                type="button"
                class={`pointer-events-auto flex size-11 items-center justify-center rounded-full bg-orange-citric text-blue-night shadow-lg hover:bg-orange-citric/90 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-citric disabled:opacity-70 ${publicAnimations.iconButtonInteractive}`}
                disabled={refreshing}
                on:click={refreshContent}
                aria-label={refreshing ? "Atualizando conteúdo da home" : refreshError ? "Não foi possível atualizar. Tentar novamente" : "Atualizar novos conteúdos da home"}
                title={refreshError ? "Não foi possível atualizar. Tente novamente." : "Novos conteúdos disponíveis"}
            >
                {#if refreshing}
                    <span class="size-5 animate-spin rounded-full border-2 border-current border-t-transparent motion-reduce:animate-none" aria-hidden="true"></span>
                {:else}
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 11a8.1 8.1 0 0 0-15.5-2M4 4v5h5" />
                        <path d="M4 13a8.1 8.1 0 0 0 15.5 2M20 20v-5h-5" />
                    </svg>
                {/if}
            </button>
        </div>
    {/if}
    <div class="home-player-background bg-blue-night pt-px pb-5">
        <div class="mt-0 lg:mt-10" data-main-player>
            <div class="hidden w-full lg:block">
                <MainPlayer {onair} {stream} {oauth} />
            </div>
            <div class="w-full pb-10 lg:hidden">
                <MobilePlayer {onair} {stream} {oauth} />
            </div>
        </div>
    </div>
    <div class="home-content-background bg-blue-marinho pt-10">
        <Deferred data="latestPoll">
            {#if poll}
                <Section title="A Akiba pergunta" styles="container-page mb-10">
                    <LatestPollCard {poll} {oauth} />
                </Section>
            {/if}
            <svelte:fragment slot="fallback">
                <Section title="A Akiba pergunta" styles="container-page mb-10">
                    <div class="h-40 animate-pulse rounded-md bg-blue-ocean/45" aria-hidden="true"></div>
                </Section>
            </svelte:fragment>
        </Deferred>
        <div
            class={["home-featured-reviews-background pt-px", hasFeaturedPosts && "has-featured-posts"]}
            style="--featured-background: url('/img/pages/home/backgrounds/featured.svg'); --featured-mobile-background: url('/img/pages/home/backgrounds/featured_mobile.svg'); --reviews-background: url('/img/pages/home/backgrounds/reviews.svg');"
        >
            <FeaturedGrid {featuredPosts} />
            <Deferred data="latestReviews">
                <div class="home-reviews-mobile-background">
                    <ReviewListGrid title="Últimas reviews" reviews={latestReviews} />
                </div>
                <svelte:fragment slot="fallback">
                    <Section title="Últimas reviews" styles="container-page mb-8 pt-2 pb-4 lg:mb-10 lg:pt-4 lg:pb-10">
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                            {#each skeletonItems(5) as item}
                                <div class="h-48 animate-pulse rounded-md bg-blue-ocean/45" aria-hidden="true"></div>
                            {/each}
                        </div>
                    </Section>
                </svelte:fragment>
            </Deferred>
        </div>
        <Deferred data="posts">
            <PostListGrid title="Últimas matérias" {posts} />
            <svelte:fragment slot="fallback">
                <Section title="Últimas matérias" styles="container-page mb-10">
                    <div class="grid grid-cols-1 gap-x-10 gap-y-8 lg:grid-cols-2">
                        {#each skeletonItems(6) as item}
                            <div class="h-40 animate-pulse rounded-md bg-blue-ocean/45" aria-hidden="true"></div>
                        {/each}
                    </div>
                </Section>
            </svelte:fragment>
        </Deferred>
        <Deferred data="events">
            <EventCalendarGrid {events} />
            <svelte:fragment slot="fallback">
                <Section title="Agenda" styles="container-page mb-10">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                        {#each skeletonItems(5) as item}
                            <div class="h-36 animate-pulse rounded-md bg-blue-ocean/45" aria-hidden="true"></div>
                        {/each}
                    </div>
                </Section>
            </svelte:fragment>
        </Deferred>
        <div
            class="home-podcasts-background pt-5"
            style="--podcasts-background: url('/img/pages/home/backgrounds/podcasts.svg');"
        >
            <Deferred data="podcasts">
                <LatestPodcastsGrid {podcasts} />
                <svelte:fragment slot="fallback">
                    <Section title="Últimos podcasts" styles="container-page pb-12">
                        <div class="grid gap-5 md:grid-cols-3">
                            {#each skeletonItems(3) as item}
                                <div class="h-56 animate-pulse rounded-md bg-blue-ocean/45" aria-hidden="true"></div>
                            {/each}
                        </div>
                    </Section>
                </svelte:fragment>
            </Deferred>
        </div>
    </div>
</Layout>

<style>
    .home-featured-reviews-background {
        position: relative;
        background-image: none !important;
    }

    .home-featured-reviews-background.has-featured-posts {
        background-position: top center;
        background-repeat: no-repeat;
        background-size: contain;
    }

    .home-featured-reviews-background.has-featured-posts::before,
    .home-featured-reviews-background.has-featured-posts::after {
        content: "";
        position: absolute;
        inset: 0;
        background-position: top center;
        background-repeat: no-repeat;
        background-size: contain;
        filter: var(--public-background-texture-filter);
        pointer-events: none;
    }

    .home-featured-reviews-background.has-featured-posts::before {
        background-image: var(--featured-mobile-background);
    }

    .home-featured-reviews-background.has-featured-posts::after {
        background-image: none;
    }

    .home-featured-reviews-background.has-featured-posts > :global(*) {
        position: relative;
        z-index: 1;
    }

    .home-podcasts-background {
        position: relative;
        background-image: none !important;
    }

    .home-podcasts-background::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: var(--podcasts-background);
        background-position: bottom center;
        background-repeat: no-repeat;
        background-size: cover;
        filter: var(--public-background-texture-filter);
        opacity: 0.5;
        pointer-events: none;
    }

    .home-podcasts-background > :global(*) {
        position: relative;
        z-index: 1;
    }

    .home-reviews-mobile-background {
        position: relative;
        background-image: none !important;
    }

    .home-reviews-mobile-background::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: var(--reviews-background);
        background-position: bottom center;
        background-repeat: no-repeat;
        background-size: cover;
        filter: var(--public-background-texture-filter);
        pointer-events: none;
    }

    .home-reviews-mobile-background > :global(*) {
        position: relative;
        z-index: 1;
    }

    @media (min-width: 600px) {
        .home-featured-reviews-background.has-featured-posts {
            background-size: cover;
        }

        .home-featured-reviews-background.has-featured-posts::before {
            background-size: cover;
        }
    }

    @media (min-width: 1024px) {
        .home-featured-reviews-background.has-featured-posts {
            background-image: none !important;
        }

        .home-featured-reviews-background.has-featured-posts::before {
            background-image: var(--featured-background);
            background-size: 100% auto;
            mask-image: linear-gradient(to bottom, #000 0 68%, transparent 86%);
        }

        .home-featured-reviews-background.has-featured-posts::after {
            background-image: var(--reviews-background);
            background-position: bottom center;
            background-repeat: no-repeat;
            background-size: 100% auto;
        }

        .home-podcasts-background {
            background-image: none !important;
        }

        .home-podcasts-background::before {
            background-position: bottom center;
            background-repeat: repeat-x;
            background-size: contain;
        }

        .home-reviews-mobile-background {
            background-image: none !important;
        }

        .home-reviews-mobile-background::before {
            background-image: none;
        }
    }

    @media (min-width: 2001px) {
        .home-featured-reviews-background {
            background-repeat:
                repeat-x,
                repeat-x;
            background-size:
                contain,
                contain;
        }
    }
</style>
