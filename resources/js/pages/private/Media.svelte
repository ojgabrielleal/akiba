<script>
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { ListenerGalleryGrid, PollGrid } from "@/ui/widgets/private";
</script>

<Meta meta={{ title: "Mídias" } } />
<Layout>
    <h1 class="sr-only">Midias</h1>
    <PollGrid title="Enquetes" />
    <ListenerGalleryGrid title="Galeria dos ouvintes" />
</Layout>
