<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/config";
    import { syncMediaSessionMetadata } from "@/store";
    import { Layout } from "@/ui/layouts/public";
    import {
        MainPlayer,
        MobilePlayer,
        FeaturedGrid,
        ReviewGrid,
        PostGrid,
        CalendarGrid,
    } from "@/ui/widgets/public";

    $: ({ onair: { data: [air] }, stream } = $page.props);
    $: syncMediaSessionMetadata(air, stream);
</script>

<Meta />
<Layout>
    <div class="bg-blue-night lg:h-179 hidden xl:block pt-25 lg:pt-50">
        <MainPlayer />
    </div>
    <div class="bg-blue-night xl:hidden pt-30 pb-10">
        <MobilePlayer />
    </div>
    <div class="bg-blue-night pt-10 xl:pt-30">
        <FeaturedGrid title="Destaques da Akiba" />
        <ReviewGrid title="Reviews da Akiba" variant="home" />
        <PostGrid title="Últimas matérias da Akiba" variant="home" />
    </div>
</Layout>
