<script>
    import { page, usePoll } from "@inertiajs/svelte";
    import { Meta } from "@/config";
    import { syncMediaSessionMetadata } from "@/store";
    import { Layout } from "@/ui/layouts/public";
    import { FeaturedGrid, LatestReviewsGrid, MainPlayer, MobilePlayer } from "@/ui/widgets/public";

    $: ({ onair: { data: [air] }, stream } = $page.props);
    $: syncMediaSessionMetadata(air, stream);

    usePoll(10 * 1000, {
        only: ["stream"],
    });
</script>

<Meta />
<Layout>
    <h1 class="sr-only">Akiba Station</h1>
    <div class="bg-blue-night pt-px pb-5">
        <div class="mt-0 lg:mt-28">
            <div class="hidden w-full lg:block">
                <MainPlayer />
            </div>
            <div class="w-full pb-10 lg:hidden">
                <MobilePlayer />
            </div>
        </div>
    </div>
    <div class="bg-blue-marinho mt-8 pt-px">
        <FeaturedGrid />
        <LatestReviewsGrid />
    </div>
</Layout>
