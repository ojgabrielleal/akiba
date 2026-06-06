<script>
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { AudienceCarrousel, ProgramHistoryTable } from "@/ui/widgets/private";
</script>

<Meta meta={{ title: "Log's" } } />
<Layout>
    <AudienceCarrousel title="Estatísticas da concorrência" />
    <ProgramHistoryTable title="Histórico de transmissões" />
</Layout>
