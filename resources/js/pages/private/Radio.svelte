<script>
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { ProgramScheduleGrid, MusicRankingGrid, ListenerMonthGrid, ProgramGrid } from "@/ui/widgets/private";
</script>

<Meta meta={{ title: "Rádio" } } />
<Layout>
    <ProgramGrid title="Programas & Programação" />
    <!--
    <ProgramScheduleGrid title="Grade de programação" />
    <MusicRankingGrid title="Ranking de música" />
    <ListenerMonthGrid title="Ouvinte do mês" />
    -->
</Layout>
