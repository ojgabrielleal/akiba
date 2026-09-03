<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { ListenerGalleryGrid, MysteryGrid, PollGrid } from "@/lib/widgets/private";

    $: ({ polls, latestPoll, listenerGalleries, mysteries } = $page.props);
</script>

<Meta meta={{ title: "Mídias" } } />
<Layout>
    <h1 class="sr-only">Midias</h1>
    <PollGrid title="Enquetes" {polls} {latestPoll} />
    <MysteryGrid title="Enigma da Akiba" {mysteries} />
    <ListenerGalleryGrid title="Galeria dos ouvintes" {listenerGalleries} />
</Layout>
