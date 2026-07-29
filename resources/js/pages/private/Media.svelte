<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { ListenerGalleryGrid, PollGrid } from "@/lib/widgets/private";

    $: ({ polls, latestPoll, listenerGalleries } = $page.props);
</script>

<Meta meta={{ title: "Mídias" } } />
<Layout>
    <h1 class="sr-only">Midias</h1>
    <PollGrid title="Enquetes" {polls} {latestPoll} />
    <ListenerGalleryGrid title="Galeria dos ouvintes" {listenerGalleries} />
</Layout>
