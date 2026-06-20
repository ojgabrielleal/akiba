<script>
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { RankingGrid, ListenerMonthGrid, ProgramGrid } from "@/ui/widgets/private";
</script>

<Meta meta={{ title: "Rádio" } } />
<Layout>
    <h1 class="sr-only">Radio</h1>
    <ProgramGrid title="Programas & Programação" />
    <RankingGrid title="Akiba Ranking" />
    <ListenerMonthGrid title="Ouvinte do mês" />
</Layout>
