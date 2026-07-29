<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import {
        AudienceCarousel,
        AudienceHistoryChart,
        InternalRankingCarousel,
        ProgramHistoryTable,
    } from "@/lib/widgets/private";

    $: ({ audience, audienceHistory, onair, ranking_interno: ranking } = $page.props);
</script>

<Meta meta={{ title: "Relatórios" } } />
<Layout>
    <h1 class="sr-only">Relatórios</h1>
    <AudienceCarousel title="Concorrência" {audience} />
    <AudienceHistoryChart title="Histórico de audiência" history={audienceHistory?.data ?? null} />
    <InternalRankingCarousel title="Ranking interno" {ranking} />
    <ProgramHistoryTable title="Histórico de programação" {onair} />
</Layout>
