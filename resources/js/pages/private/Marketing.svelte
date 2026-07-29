<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { MarketingGrid } from "@/lib/widgets/private";

    $: ({ repositories } = $page.props);
</script>

<Meta meta={{ title: "Marketing" } } />
<Layout>
    <h1 class="sr-only">Marketing</h1>
    <MarketingGrid {repositories} />
</Layout>
