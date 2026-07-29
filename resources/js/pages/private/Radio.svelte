<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { RankingGrid, ListenerMonthGrid, ProgramGrid } from "@/lib/widgets/private";

    $: ({ listenerMonth, programs, ranking, users } = $page.props);
</script>

<Meta meta={{ title: "Rádio" } } />
<Layout>
    <h1 class="sr-only">Radio</h1>
    <ProgramGrid title="Programas & Programação" {programs} {users} />
    <RankingGrid title="Akiba Ranking" {ranking} />
    <ListenerMonthGrid title="Ouvinte do mês" {listenerMonth} />
</Layout>
