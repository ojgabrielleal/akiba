<script>
    import { page, usePoll } from "@inertiajs/svelte";
    import { Meta } from "@/config";
    import { syncMediaSessionMetadata } from "@/store";
    import { Layout } from "@/ui/layouts/public";
    import {
        EventCalendarGrid,
        FeaturedGrid,
        LatestReviewsGrid,
        LatestPodcastsGrid,
        MainPlayer,
        MobilePlayer,
        PostListGrid,
    } from "@/ui/widgets/public";

    $: ({
        onair,
        stream,
        featuredPosts,
        latestReviews,
        posts,
        events,
        podcasts,
        flash,
        oauth,
    } = $page.props);
    $: air = onair?.data?.[0] ?? null;
    $: pageUrl = $page.url;
    $: syncMediaSessionMetadata(air, stream);

    usePoll(10 * 1000, {
        only: ["stream"],
    });
</script>

<Meta />
<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <h1 class="sr-only">Akiba Station</h1>
    <div class="bg-blue-night pt-px pb-5">
        <div class="mt-0 lg:mt-28" data-main-player>
            <div class="hidden w-full lg:block">
                <MainPlayer {onair} {stream} {oauth} />
            </div>
            <div class="w-full pb-10 lg:hidden">
                <MobilePlayer {onair} {stream} {oauth} />
            </div>
        </div>
    </div>
    <div class="bg-blue-marinho mt-8 pt-px">
        <div
            class="home-featured-reviews-background pt-px"
            style="--featured-background: url('/img/pages/home/backgrounds/featured.webp'); --reviews-background: url('/img/pages/home/backgrounds/reviews.webp');"
        >
            <FeaturedGrid {featuredPosts} />
            <LatestReviewsGrid {latestReviews} />
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
        background-image: none !important;
    }

    .home-podcasts-background {
        background-image: none !important;
    }

    @media (min-width: 1024px) {
        .home-featured-reviews-background {
            background-image: var(--featured-background), var(--reviews-background) !important;
            background-position:
                top center,
                bottom center;
            background-repeat:
                no-repeat,
                no-repeat;
            background-size:
                100% auto,
                100% auto;
        }

        .home-podcasts-background {
            background-image: var(--podcasts-background) !important;
            background-position: bottom center;
            background-repeat: repeat-x;
            background-size: contain;
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
