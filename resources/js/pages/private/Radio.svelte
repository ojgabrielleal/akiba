<script>
    import { Deferred, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { Section } from "@/lib/components/private";
    import { RankingGrid, ListenerMonthGrid, ProgramGrid } from "@/lib/widgets/private";

    $: ({ listenerMonth, programs, ranking, users } = $page.props);

    const skeletonItems = (count) => Array.from({ length: count }, (_, index) => index);
</script>

<Meta meta={{ title: "Rádio" } } />
<Layout>
    <h1 class="sr-only">Radio</h1>
    <Deferred data={["programs", "users"]}>
        <ProgramGrid title="Programas & Programação" {programs} {users} />
        <svelte:fragment slot="fallback">
            <Section title="Programas & Programação">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {#each skeletonItems(8) as item}
                        <div class="h-56 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
        </svelte:fragment>
    </Deferred>
    <Deferred data={["ranking", "listenerMonth"]}>
        <RankingGrid title="Akiba Ranking" {ranking} />
        <ListenerMonthGrid title="Ouvinte do mês" {listenerMonth} />
        <svelte:fragment slot="fallback">
            <Section title="Akiba Ranking">
                <div class="grid gap-4 md:grid-cols-3">
                    {#each skeletonItems(3) as item}
                        <div class="h-52 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
            <Section title="Ouvinte do mês">
                <div class="h-72 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
            </Section>
        </svelte:fragment>
    </Deferred>
</Layout>
