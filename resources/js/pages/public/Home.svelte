<script>
    import { page, usePoll } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Section } from "@/lib/components/public";
    import { syncMediaSessionMetadata } from "@/lib/stores";
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

    $: ({
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
    $: air = onair?.data?.[0] ?? null;
    $: pageUrl = $page.url;
    $: featuredPostList = Array.isArray(featuredPosts) ? featuredPosts : featuredPosts?.data ?? [];
    $: poll = latestPoll?.data ?? null;
    $: hasFeaturedPosts = featuredPostList.length > 0;
    $: syncMediaSessionMetadata(air, stream);

    usePoll(10 * 1000, {
        only: ["stream"],
    });
</script>

<Meta />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <h1 class="sr-only">Akiba Station</h1>
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
        {#if poll}
            <Section title="A Akiba pergunta" styles="container-page mb-10">
                <LatestPollCard {poll} {oauth} />
            </Section>
        {/if}
        <div
            class={["home-featured-reviews-background pt-px", hasFeaturedPosts && "has-featured-posts"]}
            style="--featured-background: url('/img/pages/home/backgrounds/featured.webp'); --featured-mobile-background: url('/img/pages/home/backgrounds/featured-mobile.webp'); --reviews-background: url('/img/pages/home/backgrounds/reviews.webp');"
        >
            <FeaturedGrid {featuredPosts} />
            <div class="home-reviews-mobile-background">
                <ReviewListGrid title="Últimas reviews" reviews={latestReviews} />
            </div>
        </div>
        <PostListGrid title="Últimas matérias" {posts} />
        <EventCalendarGrid {events} />
        <div
            class="home-podcasts-background pt-5"
            style="--podcasts-background: url('/img/pages/home/backgrounds/podcasts.webp');"
        >
            <LatestPodcastsGrid {podcasts} />
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

    .home-featured-reviews-background.has-featured-posts::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: var(--featured-mobile-background);
        background-position: top center;
        background-repeat: no-repeat;
        background-size: contain;
        pointer-events: none;
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
            background-image: var(--reviews-background) !important;
            background-position:
                bottom center;
            background-repeat:
                no-repeat;
            background-size:
                100% auto;
        }

        .home-featured-reviews-background.has-featured-posts::before {
            background-image: var(--featured-background);
            background-size: 100% auto;
            mask-image: linear-gradient(to bottom, #000 0 68%, transparent 86%);
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
