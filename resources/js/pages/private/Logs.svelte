<script>
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { AudienceCarousel, ProgramHistoryTable } from "@/ui/widgets/private";
</script>

<Meta meta={{ title: "Log's" } } />
<Layout>
    <h1 class="sr-only">Logs</h1>
    <AudienceCarousel title="Estatísticas da concorrência" />
    <ProgramHistoryTable title="Histórico de transmissões" />
</Layout>
