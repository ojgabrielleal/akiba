<script>
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { AudienceCarousel, AudienceHistoryChart } from "@/ui/widgets/private";
</script>

<Meta meta={{ title: "Relatórios" } } />
<Layout>
    <h1 class="sr-only">Relatórios</h1>
    <AudienceCarousel title="Concorrência" />
    <AudienceHistoryChart title="Histórico de audiência" />
</Layout>
