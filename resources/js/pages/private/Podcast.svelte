<script>
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { PodcastForm, PodcastGrid } from "@/ui/widgets/private";
</script>

<Meta meta={{ title: "Podcasts" } } />
<Layout>
    <h1 class="sr-only">Podcasts</h1>
    <PodcastForm />
    <PodcastGrid title="Todos os podcasts" />
</Layout>
