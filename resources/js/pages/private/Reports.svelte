<script>
    import { Deferred, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { Section } from "@/lib/components/private";
    import {
        AudienceCarousel,
        AudienceHistoryChart,
        InternalRankingCarousel,
        ProgramHistoryTable,
        PostAccessTable,
    } from "@/lib/widgets/private";

    $: ({ postAccess, audience, audienceHistory, onair, ranking_interno: ranking } = $page.props);

    const skeletonItems = (count) => Array.from({ length: count }, (_, index) => index);
</script>

<Meta meta={{ title: "Relatórios" } } />
<Layout>
    <h1 class="sr-only">Relatórios</h1>
    <Deferred data="audience">
        <AudienceCarousel title="Concorrência" {audience} />
        <svelte:fragment slot="fallback">
            <Section title="Concorrência">
                <div class="flex gap-3 overflow-hidden">
                    {#each skeletonItems(6) as item}
                        <div class="h-48 w-36 shrink-0 animate-pulse rounded-md bg-blue-ocean/55 sm:w-40" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
        </svelte:fragment>
    </Deferred>
    <Deferred data="audienceHistory">
        <AudienceHistoryChart title="Histórico de audiência" history={audienceHistory?.data ?? null} />
        <svelte:fragment slot="fallback">
            <Section title="Histórico de audiência">
                <div class="h-80 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
            </Section>
        </svelte:fragment>
    </Deferred>
    <Deferred data="ranking_interno">
        <InternalRankingCarousel title="Ranking interno" {ranking} />
        <svelte:fragment slot="fallback">
            <Section title="Ranking interno">
                <div class="flex gap-3 overflow-hidden">
                    {#each skeletonItems(6) as item}
                        <div class="h-57 w-42 shrink-0 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
        </svelte:fragment>
    </Deferred>
    <Deferred data="postAccess">
        <PostAccessTable posts={postAccess} />
        <svelte:fragment slot="fallback">
            <Section title="Acessos às postagens">
                <div class="h-80 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
            </Section>
        </svelte:fragment>
    </Deferred>
    <Deferred data="onair">
        <ProgramHistoryTable title="Histórico de programação" {onair} />
        <svelte:fragment slot="fallback">
            <Section title="Histórico de programação">
                <div class="grid gap-3">
                    {#each skeletonItems(5) as item}
                        <div class="h-20 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
        </svelte:fragment>
    </Deferred>
</Layout>
