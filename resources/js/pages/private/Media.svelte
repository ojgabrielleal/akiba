<script>
    import { Deferred, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { Section } from "@/lib/components/private";
    import { ListenerGalleryGrid, EnigmaGameGrid, PollGrid } from "@/lib/widgets/private";

    $: ({ polls, latestPoll, listenerGalleries, enigmagames } = $page.props);

    const skeletonItems = (count) => Array.from({ length: count }, (_, index) => index);
</script>

<Meta meta={{ title: "Mídias" } } />
<Layout>
    <h1 class="sr-only">Midias</h1>
    <Deferred data={["polls", "latestPoll"]}>
        <PollGrid title="Enquetes" {polls} {latestPoll} />
        <svelte:fragment slot="fallback">
            <Section title="Enquetes">
                <div class="h-64 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {#each skeletonItems(8) as item}
                        <div class="h-40 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
        </svelte:fragment>
    </Deferred>
    <Deferred data="enigmagames">
        <EnigmaGameGrid title="Enigma Otaku" {enigmagames} />
        <svelte:fragment slot="fallback">
            <Section title="Enigma Otaku">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {#each skeletonItems(4) as item}
                        <div class="h-56 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
        </svelte:fragment>
    </Deferred>
    <Deferred data="listenerGalleries">
        <ListenerGalleryGrid title="Galeria dos ouvintes" {listenerGalleries} />
        <svelte:fragment slot="fallback">
            <Section title="Galeria dos ouvintes">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    {#each skeletonItems(10) as item}
                        <div class="h-48 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
        </svelte:fragment>
    </Deferred>
</Layout>
