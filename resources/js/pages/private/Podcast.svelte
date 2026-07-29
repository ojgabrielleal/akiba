<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { PodcastForm, PodcastGrid } from "@/lib/widgets/private";

    $: ({ podcast, podcasts } = $page.props);
</script>

<Meta meta={{ title: "Podcasts" } } />
<Layout>
    <h1 class="sr-only">Podcasts</h1>
    <PodcastForm {podcast} />
    <PodcastGrid title="Todos os podcasts" {podcasts} />
</Layout>
