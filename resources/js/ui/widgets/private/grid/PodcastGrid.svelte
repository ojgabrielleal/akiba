<script>
    export let title;

    import { router, page } from "@inertiajs/svelte";
    import { EmptyState, GridList, IconButton, Pagination, Section } from "@/ui/components/private/";
    import { podcastPermissions, resolvePlaceholderImage } from "@/utils";

    $: ({ podcasts } = $page.props);

    let can = podcastPermissions();

    const requestDeactivate = (item) => {
        router.patch(`/panel/podcast/${item.uuid}/deactivate`, {},
            { preserveScroll: true },
        );
    };
</script>

{#if podcasts}
     <Section {title}>
        {#if podcasts.data.length > 0}
        <GridList preset="content">
            {#each podcasts.data as item}
                <li class="w-full h-65 bg-blue-ocean rounded-md overflow-hidden relative">
                    <article>
                    <img 
                        src={item.image}
                        class="w-full h-65 object-cover"
                        alt={item.title}
                    />
                    <div class="grid grid-cols-[0.4fr_1fr_0.6fr] items-center bg-blue-cerulean absolute bottom-0 w-full py-1 px-4">
                        <div class="flex items-center gap-2 font-noto-sans font-extrabold italic uppercase text-md text-suspense-aurora truncate">
                            <img
                                src="/svg/eye.svg"
                                alt=""
                                aria-hidden="true"
                                class="w-4 filter-suspense-aurora"
                                loading="lazy"
                            />
                            {item.views ?? 0}
                        </div>
                        <div class="mt-[0.1rem] w-full font-noto-sans font-extrabold text-sm text-center text-suspense-aurora italic uppercase truncate">
                            S{item.season} - Ep{item.episode}
                        </div>
                        <div class="flex gap-1 justify-end mt-1">
                            {#if can.deactivate}
                                <IconButton
                                    variant="trash"
                                    label="Desativar"
                                    size="sm"
                                    surface="dark"
                                    on:click={() => requestDeactivate(item)}
                                />
                            {/if}
                            {#if can.update}
                                <IconButton
                                    variant="edit"
                                    label="Atualizar"
                                    href={`/panel/podcast/${item.uuid}`}
                                    size="sm"
                                    surface="dark"
                                />
                            {/if}
                        </div>
                    </div>
                    </article>
                </li>
            {/each}
        </GridList>
        {:else}
            <EmptyState
                title="Nenhum podcast encontrado"
                description="Os podcasts cadastrados aparecerão aqui."
            />
        {/if}
        <Pagination pages={podcasts} only={["podcasts"]} />
    </Section>
{/if}
