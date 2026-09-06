<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { ListenerGalleryGrid, EnigmaGameGrid, PollGrid } from "@/lib/widgets/private";

    $: ({ polls, latestPoll, listenerGalleries, enigmagames } = $page.props);
</script>

<Meta meta={{ title: "Mídias" } } />
<Layout>
    <h1 class="sr-only">Midias</h1>
    <PollGrid title="Enquetes" {polls} {latestPoll} />
    <EnigmaGameGrid title="Enigma Game" {enigmagames} />
    <ListenerGalleryGrid title="Galeria dos ouvintes" {listenerGalleries} />
</Layout>
